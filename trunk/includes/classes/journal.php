<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /includes/classes/journal.php
//
namespace core\classes;
abstract class journal {
	public 	$affected_accounts	= array();
	public 	$repost_ids			= array();
	public 	$cogs_entry			= array();
	public  $journal_rows 		= array();
	public  $first_period		= 0;
	public 	$popup_form_type;

	public function __construct( $id = 0, $verbose = true) {
		global $admin;
		if ($id != 0) {
			$sql = $admin->DataBase->prepare("SELECT * FROM " . TABLE_JOURNAL_MAIN . " WHERE id = $id");
			$sql->execute();
			$this = $sql->fetch(\PDO::FETCH_LAZY);
			// make sure we have a record or die (there's a problem that needs to be fixed)
		  	if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException(TEXT_DIED_TRYING_TO_BUILD_A_JOURNAL_ENTRY_WITH_ID . ' = ' . $id);
		  	foreach ($result as $key => $value) $this->$key = $value;
		  	$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
		  	$sql = $admin->DataBase->prepare("SELECT * FROM " . TABLE_JOURNAL_ITEM . " WHERE ref_id = " . (int)$id);
		  	$sql->execute();
		  	$this->journal_rows = $sql->fetchAll();
		}
	}

/*******************************************************************************************************************/
// START Post Journal Function
/*******************************************************************************************************************/
  	public function Post($action = 'insert', $skip_balance = false) {
		global $admin;
		if (!isset($this->id) || $this->id == '') $this->id = 0;
		$this->unpost_ids = $this->check_for_re_post();
		if ($action == 'edit') {
			$orig_post = new journal($this->id); // read in the original journal entry to get post order
			$idx = substr($orig_post->post_date, 0, 10).':'.str_pad($this->id, 8, '0', STR_PAD_LEFT);
			$this->unpost_ids[$idx] = $this->id;
		}
		$idx = substr($this->post_date, 0, 10).':'.str_pad($this->id, 8, '0', STR_PAD_LEFT);
		$this->post_ids[$idx] = clone $this; // save variables for later to post
		$this->first_period = $this->period;
		// start unposting all affected records
		if (sizeof($this->unpost_ids) > 0) {
	  		krsort($this->unpost_ids); // unpost in reverse order
	  		$admin->messageStack->debug("\nStarting to unPost reverse sorted id array = " . print_r($this->unpost_ids, true));
		  	while (true) {
				if (!$id = array_shift($this->unpost_ids)) break; // no more to unPost, exit loop
				$admin->messageStack->debug("\n/********* unPosting journal_main id = $id");
				$unPost = new journal($id, false);
				if (!isset($unPost->id)) continue; // already has been unposted, skip
				if ($this->id <> $id) { // re-queue to post if not current entry
					$idx = substr($unPost->post_date, 0, 10).':'.str_pad($id, 8, '0', STR_PAD_LEFT);
					$this->post_ids[$idx] = clone $unPost;
				}
				$unPost->unPost('edit', true);
				$this->first_period      = min($this->first_period, $unPost->period);
				$this->affected_accounts = gen_array_key_merge($this->affected_accounts, $unPost->affected_accounts);
				// add the new post_ids to the arrays, one for now, one for re-post loop later
				$this->unpost_ids += $unPost->unpost_ids;
				$admin->messageStack->debug("\n  unPosting array now looks like = " . print_r($this->unpost_ids, true));
				$admin->messageStack->debug("\n  re-Posting array keys now looks like = " . print_r(array_keys($this->post_ids), true));
//				$unPost->post_ids = array(); // clear nested unPost to zero, so it doesn't re-post
	  		}
		}
		// Post entry and rePost any journal entries unPosted
		ksort($this->post_ids); // re-post in post_date/record_id ascending order
		$admin->messageStack->debug("\nStarting to Post indexes to be Posted = " . print_r(array_keys($this->post_ids), true));
		while ($glEntry = array_shift($this->post_ids)) {
			$admin->messageStack->debug("\n/********* Posting Journal main ... id = $glEntry->id and journal class = ".get_class($glEntry));
			$this->repost_ids = $glEntry->check_for_re_post();
			$glEntry->remove_cogs_rows(); // they will be regenerated during the post
			$admin->messageStack->debug("\n  journal_main array = " . print_r($glEntry->journal_main_array, true));
			db_perform(TABLE_JOURNAL_MAIN, $glEntry->journal_main_array, 'insert');
			if (!$glEntry->id) $glEntry->id = \core\classes\PDO::lastInsertId('id');
			// post journal rows
			$admin->messageStack->debug("\n  Posting Journal rows ...");
			for ($i = 0; $i < count($glEntry->journal_rows); $i++) {
		  		$admin->messageStack->debug("\n  journal_rows = " . print_r($glEntry->journal_rows[$i], true));
		  		$glEntry->journal_rows[$i]['ref_id'] = $glEntry->id;	// link the rows to the journal main id
		  		db_perform(TABLE_JOURNAL_ITEM, $glEntry->journal_rows[$i], 'insert');
		  		if (!$glEntry->journal_rows[$i]['id']) $glEntry->journal_rows[$i]['id'] = \core\classes\PDO::lastInsertId('id');
			}
			$admin->messageStack->debug("\nStarting auxilliary post functions ...");
	  		// Inventory needs to be posted first because function may add additional journal rows for COGS
			$glEntry->Post_inventory();
			$glEntry->Post_chart_balances();
			$glEntry->Post_account_sales_purchases();
			$this->affected_accounts = gen_array_key_merge($this->affected_accounts, $glEntry->affected_accounts);
			$this->first_period = min($this->first_period, $glEntry->period);
			if (sizeof($this->repost_ids) > 0) {
				ksort($this->repost_ids); // repost by post date
				$admin->messageStack->debug("\nStarting to rePost entries queued from first pass, sorted id array = ".print_r($this->repost_ids, true));
				while (true) {
					if (!$id = array_shift($this->repost_ids)) break; // no more to unPost, exit loop
					$admin->messageStack->debug("\n/********* rePosting journal_main id = $id");
					if ($this->id == $id) continue; // don't repost current post
					$rePost = new journal($id, false);
					$rePost->Post('edit', true);
					// add the new post_ids to the arrays, one for now, one for re-post loop later
					$this->repost_ids += $rePost->repost_ids;
					$admin->messageStack->debug("\n  rePosting array now looks like = " . print_r($this->repost_ids, true));
				}
			}
		}
		if (!$skip_balance) $this->update_chart_history_periods($this->first_period);
		$this->check_for_closed_po_so('Post');
		$admin->messageStack->debug("\n*************** end Posting Journal ******************* id = $this->id\n");
		return true;
  	}

  	public function unPost($action = 'delete', $skip_balance = false) {
		global $admin;
		$admin->messageStack->debug("\nunPosting Journal... id = {$this->id} and action = $action and journal class = ".get_class($this));
		$this->unpost_ids = $this->check_for_re_post();
		$this->unPost_account_sales_purchases();	// unPost the customer/vendor history
		// unPost_chart_balances needs to be unPosted before inventory because inventory may remove journal rows (COGS)
		$this->unPost_chart_balances();	// unPost the chart of account values
		$this->unPost_inventory();
		$admin->messageStack->debug("\n  Deleting Journal main and rows as part of unPost ...");
		$result = $admin->DataBase->exec("DELETE FROM " . TABLE_JOURNAL_MAIN . " WHERE id = " . $this->id);
		if ($result->AffectedRows() <> 1) throw new \core\classes\userException(GL_ERROR_CANNOT_DELETE_MAIN);
		$result = $admin->DataBase->exec("DELETE FROM " . TABLE_JOURNAL_ITEM . " WHERE ref_id = " . $this->id);
		if ($result->AffectedRows() == 0 ) throw new \core\classes\userException(printf(GL_ERROR_CANNOT_DELETE_ITEM, $this->id));
		if ($action == 'delete') { // re-post affected entries unless edited (which is after the entry is reposted)
	  		if (is_array($this->unpost_ids)) { // rePost any journal entries unPosted to rollback COGS calculation
	  			ksort($this->unpost_ids);
				while ($id = array_shift($this->unpost_ids)) {
					$admin->messageStack->debug("\nRe-posting as part of unPost - Journal main id = " . $id);
			  		$rePost = new journal($id, false);
			  		if (!isset($rePost->id)) continue; // already has been unposted, skip
			  		$rePost->remove_cogs_rows(); // they will be regenerated during the re-post
			  		$rePost->Post('edit', true);
			  		$this->affected_accounts = gen_array_key_merge($this->affected_accounts, $rePost->affected_accounts);
			  		$this->first_period = min($this->first_period, $rePost->first_period);
				}
		  	}
		}
		if (!$skip_balance) $this->update_chart_history_periods($this->period);
		$this->check_for_closed_po_so('unPost'); // check to re-open predecessor entry
		$admin->messageStack->debug("\nend unPosting Journal.\n\n");
		return true;
  	}

/*******************************************************************************************************************/
// END Post Journal Function
/*******************************************************************************************************************/
// START re-post Functions
/*******************************************************************************************************************/
  	abstract function check_for_re_post();

/*******************************************************************************************************************/
// START Chart of Accout Functions
/*******************************************************************************************************************/
  	abstract function Post_chart_balances();
	/**
	 * this function will un do the changes to the chart_of_account_history table
	 */
  	abstract function unPost_chart_balances();

	// *********  chart of account support functions  **********
  	abstract function update_chart_history_periods($period = CURRENT_ACCOUNTING_PERIOD);

  	final function validate_balance($period = CURRENT_ACCOUNTING_PERIOD) {
		global $admin;
		$admin->messageStack->debug("\n    Validating trial balance for period: $period ... ");
		$sql = $admin->DataBase->prepare("SELECT sum(debit_amount) as debit, sum(credit_amount) as credit FROM " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " WHERE period = " . $period);
		$sql->execute();
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		// check to see if we are still in balance, round debits and credits and compare
		$admin->messageStack->debug(" debits = {$result['debit']} and credits = {$result['credit']}");
		$debit_total  = round($result['debit'],  $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
		$credit_total = round($result['credit'], $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
		if ($debit_total <> $credit_total) { // Trouble in paradise, fraction of cents adjustment next
		  	$tolerance = 2 * (1 / pow(10, $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places'])); // i.e. 2 cents in USD
		  	$adjustment = $result['credit'] - $result['debit'];
		  	if (abs($adjustment) > $tolerance) throw new \core\classes\userException(sprintf(GL_ERROR_TRIAL_BALANCE, $result['debit'], $result['credit'], $period));
		  	// find the adjustment account
		  	if (!defined('ROUNDING_GL_ACCOUNT') || ROUNDING_GL_ACCOUNT == '') {
				$sql = $admin->DataBase->prepare("SELECT id FROM " . TABLE_CHART_OF_ACCOUNTS . " WHERE account_type = 44 limit 1");
				$sql->execute();
				if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException('Failed trying to locate retained earnings account to make rounding adjustment. There must be one and only one Retained Earnings account in the chart of accounts!');
				$result = $sql->fetch(\PDO::FETCH_LAZY);
				$adj_gl_account = $result['id'];
		  	} else {
				$adj_gl_account = ROUNDING_GL_ACCOUNT;
		  	}
		  	$admin->messageStack->debug("\n      Adjusting balance, adjustment = $adjustment and gl account = $adj_gl_account");
		  	$sql = "UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET debit_amount = debit_amount + $adjustment
			  WHERE period = $period and account_id = '$adj_gl_account'";
		  	$result = $admin->DataBase->exec($sql);
		}
		$admin->messageStack->debug(" ... End Validating trial balance.");
		return true;
  	}

/*******************************************************************************************************************/
// END Chart of Accout Functions
/*******************************************************************************************************************/
// START Customer/Vendor Account Functions
/*******************************************************************************************************************/
// Post the customers/vendors sales/purchases values for the given period
  	abstract function Post_account_sales_purchases() ;

  	/**
  	 * this function will delete the customer/vendor history for this journal
  	 * @throws Exception
  	 */

	abstract function unPost_account_sales_purchases();

/*******************************************************************************************************************/
// END Customer/Vendor Account Functions
/*******************************************************************************************************************/
// START Inventory Functions
/*******************************************************************************************************************/
  	abstract function Post_inventory();

  	abstract function unPost_inventory();


// *********  inventory support functions  **********
	final function update_inventory_status($sku, $field, $adjustment, $item_cost = 0, $desc = '', $full_price = 0) {
		global $admin;
		if (!$sku || $adjustment == 0) return true;
		$admin->messageStack->debug("\n    update_inventory_status, SKU = $sku, field = $field, adjustment = $adjustment, and item_cost = " . $item_cost);
		// catch sku's that are not in the inventory database but have been requested to post
		$sql = $admin->DataBase->prepare("SELECT id, inventory_type FROM " . TABLE_INVENTORY . " WHERE sku = '$sku'");
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) {
		  	if (!INVENTORY_AUTO_ADD) throw new \core\classes\userException(GL_ERROR_UPDATING_INVENTORY_STATUS . $sku);
		  	$id = $this->inventory_auto_add($sku, $desc, $item_cost, $full_price);
			$result['inventory_type'] = 'si';
		}
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		$type = $result['inventory_type'];
		// only update items that are to be tracked in inventory (non-stock are tracked for PO/SO only)
		if (strpos(COG_ITEM_TYPES, $type) !== false || ($type == 'ns' && $field <> 'quantity_on_hand')) {
		  	$raw_sql = "UPDATE " . TABLE_INVENTORY . " SET {$field} = {$field} + {$adjustment}, ";
//		  	if ($item_cost) $sql .= "item_cost = {$item_cost}, ";
		  	$raw_sql .= "last_journal_date = now() WHERE sku = '{$sku}'";
		  	$result = $admin->DataBase->query($raw_sql);
		  	if ($item_cost){
		  		$raw_sql = "UPDATE " . TABLE_INVENTORY_PURCHASE . " SET item_cost = {$item_cost} WHERE sku = '{$sku}' and vendor_id = '{$this->bill_acct_id}'";
		  		$result = $admin->DataBase->query($raw_sql);
		  	}
		}
  	}

  	/**
  	 *
  	 * Enter description here ...
  	 * @param array $item
  	 * @param bool $return_cogs should cogs be returned
  	 * @throws Exception
  	 */
  	abstract function calculate_COGS($item, $return_cogs = false);

  	/**
  	 *
  	 * @param string $sku
  	 * @param number $qty
  	 * @param string $serial_num
  	 */
  	abstract function calculateCost($sku = '', $qty=1, $serial_num='');

  	/**
  	 *
  	 * @param string $sku
  	 * @param number $price
  	 * @param number $qty
  	 */
  	abstract function calculate_avg_cost($sku = '', $price = 0, $qty = 1);

  	/**
  	 *
  	 * @param string $sku
  	 * @param number $qty
  	 */
  	abstract function fetch_avg_cost($sku = '', $qty=1);

	/**
	 * Rolling back cost of goods sold required to unpost an entry involves only re-setting the inventory history.
	 * The cogs records and costing is reversed in the unPost_chart_balances function.
	 */
  	function rollback_COGS() {
		global $admin;
		$admin->messageStack->debug("\n    Rolling back COGS ... ");
		// only calculate cogs for certain inventory_types
		$sql = $admin->DataBase->prepare("Select id, qty, inventory_history_id FROM " . TABLE_INVENTORY_COGS_USAGE . " WHERE journal_main_id = " . $this->id);
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) {
	  		$admin->messageStack->debug(" ...Exiting COGS, no work to be done.");
	  		return true;
		}
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
	  		$admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_HISTORY . " SET remaining = remaining + {$result['qty']} WHERE id = " . $result['inventory_history_id']);
		}
		$admin->messageStack->debug(" ... Finished rolling back COGS");
		return true;
  	}

  	abstract function load_so_po_balance($ref_id, $id = '', $post = true);

  	final function remove_journal_COGS_entries() {
		$temp_array = $this->journal_rows;
		$this->journal_rows = array();
		for ($i=0; $i<count($temp_array); $i++) {
	  		if ($temp_array[$i]['gl_type'] == 'cog') continue; // skip row - they are re-calculated later
	  		if ($temp_array[$i]['gl_type'] == 'asi') continue; // skip row - they are re-calculated later
	  		$this->journal_rows[] = $temp_array[$i];
		}
  	}

  	final function calculate_assembly_list($inv_list) {
		global $admin;
		$admin->messageStack->debug("\n    Calculating Assembly item list, SKU = " . $inv_list['sku']);
		$sku = $inv_list['sku'];
		$qty = $inv_list['qty'];
		$sql = $admin->DataBase->prepare("SELECT id FROM " . TABLE_INVENTORY . " WHERE sku = '$sku'");
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException(TEXT_THE_SKU_ENTERED_COULD_NOT_BE_FOUND);
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		$sku_id = $result['id'];
		$raw_sql = "SELECT a.sku, a.description, a.qty, i.inventory_type, i.quantity_on_hand, i.account_inventory_wage, i.item_cost as price
		  FROM " . TABLE_INVENTORY_ASSY_LIST . " a inner join " . TABLE_INVENTORY . " i on a.sku = i.sku
		  WHERE a.ref_id = " . $sku_id;
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException(GL_ERROR_SKU_NOT_ASSY . $sku);
		$assy_cost = 0;
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
		  	if ($result['quantity_on_hand'] < ($qty * $result['qty']) && strpos(COG_ITEM_TYPES, $result['inventory_type']) !== false) {
				$admin->messageStack->debug("\n    Not enough of SKU = {$result['sku']} needed " . ($qty * $result['qty']) . " and had " . $result['quantity_on_hand']);
				throw new \core\classes\userException(GL_ERROR_NOT_ENOUGH_PARTS . $result['sku']);
		  	}
			$result['qty'] = -($qty * $result['qty']);
			$result['id']  = $this->journal_rows[0]['id'];  // placeholder ref_id
			if (strpos(COG_ITEM_TYPES, $result['inventory_type']) === false) {
		    	$item_cost = -$result['qty'] * $result['price'];
		  	} else {
		    	if ($qty > 0) $result['price'] = 0; // remove unit_price for builds, leave for unbuilds (to calc delta COGS)
		    	$item_cost = $this->calculate_COGS($result->fields, true);
		  	}
	  		if ($item_cost === false) return false; // error in cogs calculation
		  	$assy_cost += $item_cost;
		  	// generate inventory assembly part record and insert into db
		  	$temp_array = array(
			  'ref_id'      => $this->id,
			  'gl_type'     => 'asi',	// assembly item code
			  'sku'         => $result['sku'],
			  'qty'         => $result['qty'],
			  'description' => $result['description'],
			  'gl_account'  => $result['account_inventory_wage'],
		 	  'post_date'   => $this->post_date);
		  	if ($qty < 0) {
				$temp_array['debit_amount'] = -$item_cost;
		 	 } else {
				$temp_array['credit_amount'] = $item_cost;
		  	}
		  	db_perform(TABLE_JOURNAL_ITEM, $temp_array, 'insert');
		  	$temp_array['id'] = \core\classes\PDO::lastInsertId('id');
		  	$this->journal_rows[] = $temp_array;
		  	if ($qty < 0) { // unbuild assy, update ref_id pointer in inventory history record of newly added item (just like a receive)
				$admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_HISTORY . " SET ref_id = {$temp_array['id']} WHERE sku = '{$temp_array['sku']}' and ref_id = {$result['id']}");
		  	}
		}
		// update assembled item with total cost
		$id = $this->journal_rows[0]['id'];
		if ($qty < 0) { // the item to assemble should be the first item record
		  	$this->journal_rows[0]['credit_amount'] = -$assy_cost;
		  	$fields = array('credit_amount' => -$assy_cost);
		} else {
		  	$this->journal_rows[0]['debit_amount'] = $assy_cost;
		  	$fields = array('debit_amount' => $assy_cost);
		}
		$result = db_perform(TABLE_JOURNAL_ITEM, $fields, 'update', "id = " . (int)$id);
		$inv_list['price'] = $assy_cost / $qty; // insert the assembly cost of materials - unit price
		// Adjust inventory levels for assembly, if unbuild, also calcuate COGS differences
		$this->calculate_COGS($inv_list, $return_cogs = ($qty < 0) ? false : true);
		return true;
  	}

  	final function branch_qty_on_hand($sku, $current_qty_in_stock = 0) {
		global $admin;
		$store_bal = $admin->DataBase->query("SELECT sum(remaining) as remaining FROM " . TABLE_INVENTORY_HISTORY . " WHERE store_id = {$this->store_id} and sku = '{$sku}'");
		$qty_owed  = $admin->DataBase->query("SELECT sum(qty) as qty FROM " . TABLE_INVENTORY_COGS_OWED . " WHERE store_id = {$this->store_id} and sku = '{$sku}'");
		return ($store_bal['remaining'] - $qty_owed['qty']);
  	}

  	final function inventory_auto_add($sku, $desc, $item_cost = 0, $full_price = 0) {
		$sql_array = array(
		  'sku'                    => $sku,
		  'inventory_type'         => 'si',
		  'description_short'      => $desc,
		  'description_purchase'   => $desc,
		  'description_sales'      => $desc,
		  'account_sales_income'   => INV_STOCK_DEFAULT_SALES,
		  'account_inventory_wage' => INV_STOCK_DEFAULT_INVENTORY,
		  'account_cost_of_sales'  => INV_STOCK_DEFAULT_COS,
		  'item_taxable'           => INVENTORY_DEFAULT_TAX,
		  'purch_taxable'          => INVENTORY_DEFAULT_PURCH_TAX,
		  'item_cost'              => $item_cost,
		  'cost_method'            => INV_STOCK_DEFAULT_COSTING,
		  'full_price'             => $full_price,
		  'creation_date'          => date('Y-m-d h:i:s'),
		);
		$result = db_perform(TABLE_INVENTORY, $sql_array, 'insert');
		return \core\classes\PDO::lastInsertId('id');
  	}

/*******************************************************************************************************************/
// END Inventory Functions
/*******************************************************************************************************************/
// START General Functions
/*******************************************************************************************************************/
  	final function build_journal_main_array() { // maps/prepares the fields to the journal_main fields
		$main_record = array();
		if (isset($this->id)) if ($this->id)   $main_record['id']                  = $this->id; // retain id if known for re-post references
		if (isset($this->period))              $main_record['period']              = $this->period;
		if (isset($this->journal_id))          $main_record['journal_id']          = $this->journal_id;//@todo replace for class
		if (isset($this->post_date))           $main_record['post_date']           = $this->post_date;
		if (isset($this->store_id))            $main_record['store_id']            = $this->store_id;
		$main_record['description'] = (isset($this->description)) ? $this->description : sprintf(TEXT_ARGS_ENTRY, $journal_types_list[$this->journal_id]['text']); //@todo set in subclass
		if (isset($this->closed))              $main_record['closed']              = $this->closed;
		if (isset($this->closed_date))         $main_record['closed_date']         = $this->closed_date;
		if (isset($this->freight))             $main_record['freight']             = $this->freight;
		if (isset($this->discount))            $main_record['discount']            = $this->discount;
		if (isset($this->shipper_code))        $main_record['shipper_code']        = $this->shipper_code;
		if (isset($this->terms))               $main_record['terms']               = $this->terms;
		if (isset($this->sales_tax))           $main_record['sales_tax']           = $this->sales_tax;
		if (isset($this->total_amount))        $main_record['total_amount']        = $this->total_amount;
		if (isset($this->currencies_code))     $main_record['currencies_code']     = $this->currencies_code;
		if (isset($this->currencies_value))    $main_record['currencies_value']    = $this->currencies_value;
		if (isset($this->so_po_ref_id))        $main_record['so_po_ref_id']        = $this->so_po_ref_id;
		if (isset($this->purchase_invoice_id)) $main_record['purchase_invoice_id'] = $this->purchase_invoice_id;
		if (isset($this->purch_order_id))      $main_record['purch_order_id']      = $this->purch_order_id;
		if (isset($this->admin_id))            $main_record['admin_id']            = $this->admin_id;
		if (isset($this->rep_id))              $main_record['rep_id']              = $this->rep_id;
		if (isset($this->waiting))             $main_record['waiting']             = $this->waiting;
		if (isset($this->gl_acct_id))          $main_record['gl_acct_id']          = $this->gl_acct_id;
		if (isset($this->bill_acct_id))        $main_record['bill_acct_id']        = $this->bill_acct_id;
		if (isset($this->bill_address_id))     $main_record['bill_address_id']     = $this->bill_address_id;
		if (isset($this->bill_primary_name))   $main_record['bill_primary_name']   = $this->bill_primary_name;
		if (isset($this->bill_contact))        $main_record['bill_contact']        = $this->bill_contact;
		if (isset($this->bill_address1))       $main_record['bill_address1']       = $this->bill_address1;
		if (isset($this->bill_address2))       $main_record['bill_address2']       = $this->bill_address2;
		if (isset($this->bill_city_town))      $main_record['bill_city_town']      = $this->bill_city_town;
		if (isset($this->bill_state_province)) $main_record['bill_state_province'] = $this->bill_state_province;
		if (isset($this->bill_postal_code))    $main_record['bill_postal_code']    = $this->bill_postal_code;
		if (isset($this->bill_country_code))   $main_record['bill_country_code']   = $this->bill_country_code;
		if (isset($this->bill_telephone1))     $main_record['bill_telephone1']     = $this->bill_telephone1;
		if (isset($this->bill_email))          $main_record['bill_email']          = $this->bill_email;
		if (isset($this->ship_acct_id))        $main_record['ship_acct_id']        = $this->ship_acct_id;
		if (isset($this->ship_address_id))     $main_record['ship_address_id']     = $this->ship_address_id;
		if (isset($this->ship_primary_name))   $main_record['ship_primary_name']   = $this->ship_primary_name;
		if (isset($this->ship_contact))        $main_record['ship_contact']        = $this->ship_contact;
		if (isset($this->ship_address1))       $main_record['ship_address1']       = $this->ship_address1;
		if (isset($this->ship_address2))       $main_record['ship_address2']       = $this->ship_address2;
		if (isset($this->ship_city_town))      $main_record['ship_city_town']      = $this->ship_city_town;
		if (isset($this->ship_state_province)) $main_record['ship_state_province'] = $this->ship_state_province;
		if (isset($this->ship_postal_code))    $main_record['ship_postal_code']    = $this->ship_postal_code;
		if (isset($this->ship_country_code))   $main_record['ship_country_code']   = $this->ship_country_code;
		if (isset($this->ship_telephone1))     $main_record['ship_telephone1']     = $this->ship_telephone1;
		if (isset($this->ship_email))          $main_record['ship_email']          = $this->ship_email;
		if (isset($this->terminal_date))       $main_record['terminal_date']       = $this->terminal_date;
		if (isset($this->drop_ship))           $main_record['drop_ship']           = $this->drop_ship;
		if (isset($this->recur_id))            $main_record['recur_id']            = $this->recur_id;
		return $main_record;
  	}

  	final function remove_cogs_rows() {
		global $admin;
		$admin->messageStack->debug("\n  Removing system generated gl rows. Started with " . count($this->journal_rows) . " rows ");
		// remove these types of rows since they are regenerated as part of the Post
		$removal_gl_types = array('cog', 'asi');
		$temp_rows = array();
		foreach ($this->journal_rows as $key => $value) {
	  		if (!in_array($value['gl_type'], $removal_gl_types)) $temp_rows[] = $value;
		}
		$this->journal_rows = $temp_rows;
		$admin->messageStack->debug(" and ended with " . count($this->journal_rows) . " rows.");
  	}

  	abstract function check_for_closed_po_so($action = 'Post');

  	final function close_so_po($id, $closed) {
    	global $admin;
		$sql_data_array = array(
	  	  'closed'      => ($closed) ? '1' : '0',
		  'closed_date' => ($closed) ? $this->post_date : '0000-00-00',
		);
		db_perform(TABLE_JOURNAL_MAIN, $sql_data_array, 'update', 'id = ' . $id);
		$admin->messageStack->debug("\n  Record ID: {$this->id} " . (($closed) ? "Closed Record ID: " : "Opened Record ID: ") . $id);
		return;
  	}

  	/**
   	 * checks if the invoice nr is valid if allowed it will create a new one when empty
  	 * @throws Exception
  	 */
  	abstract function validate_purchase_invoice_id();

  	abstract function increment_purchase_invoice_id($force = false);

  	final function add_account($type, $acct_id = 0, $address_id = 0, $allow_overwrite = false) {
		global $admin;
		$acct_type = substr($type, 0, 1);
		switch (substr($type, 1, 1)) {
			case 'b':
			case 'm': $add_type = 'bill'; break;
			case 's': $add_type = 'ship'; break;
			default: throw new \core\classes\userException("Bad account type: {$type} passed to gen_ledger/classes/gen_ledger.php (add_account)");
		}
		if ($add_type == 'bill' || $this->drop_ship) { // update or insert new account record, else skip to add address
	  		$short_name = ($add_type == 'bill') ? $this->short_name : $this->ship_short_name;
	  		$auto_type      = false;
	  		$auto_field     = '';
	  		if (!$short_name && (AUTO_INC_CUST_ID || AUTO_INC_VEND_ID)) {
				switch ($acct_type) {
		  			case 'c': // customers
						$auto_type      = AUTO_INC_CUST_ID;
						$auto_field     = 'next_cust_id_num';
						break;
		  			case 'v': // vendors
						$auto_type      = AUTO_INC_VEND_ID;
						$auto_field     = 'next_vend_id_num';
						break;
				}
				if ($auto_type) {
					$result = $admin->DataBase->query("SELECT {$auto_field} FROM " . TABLE_CURRENT_STATUS);
					$short_name = $result[$auto_field];
				}
	  		}
	  		if (!$short_name) throw new \core\classes\userException(ACT_ERROR_NO_ACCOUNT_ID);
	  		// it id exists, fetch the data, else check for duplicates
	  		$sql = "SELECT id, store_id, dept_rep_id FROM " . TABLE_CONTACTS . " WHERE ";
	  		$sql .= ($acct_id) ? ("id = " . (int)$acct_id) : ("short_name = '{$short_name}' and type = '{$acct_type}'");
	  		$result = $admin->DataBase->query($sql);
	  		if (!$acct_id && $result->fetch(\PDO::FETCH_NUM) > 0 && !$allow_overwrite) {  // duplicate ID w/o allow_overwrite
		 		throw new \core\classes\userException(ACT_ERROR_DUPLICATE_ACCOUNT);
	  		}
	  		$acct_id = $result['id']; // will only change if no id was passed and allow_overwrite is true
	  		$sql_data_array = array();
	  		$sql_data_array['last_update'] = 'now()';
	  		$sql_data_array['store_id']    = isset($this->store_id) ? $this->store_id : $result['store_id'];
	  		$sql_data_array['dept_rep_id'] = isset($this->dept_rep_id) ? $this->dept_rep_id : $result['dept_rep_id'];

	  		if ($result->fetch(\PDO::FETCH_NUM) == 0) { // new account
				$sql_data_array['type']            = $acct_type;
				$sql_data_array['short_name']      = $short_name;
				$sql_data_array['gl_type_account'] = DEF_INV_GL_ACCT;//@todo
				$sql_data_array['first_date']      = 'now()';
				db_perform(TABLE_CONTACTS, $sql_data_array, 'insert');
				$acct_id = \core\classes\PDO::lastInsertId('id');
				$force_mail_address = true;
				if ($auto_type) {
		  			$contact_id = $admin->DataBase->query("SELECT {$auto_field} FROM " . TABLE_CURRENT_STATUS);
		  			$auto_id = $contact_id[$auto_field];
		  			if ($auto_id == $short_name) { // increment the ID value
						$next_id = string_increment($auto_id);
						$admin->DataBase->query("UPDATE " . TABLE_CURRENT_STATUS . " SET $auto_field = '$next_id'");
		  			}
				}
	  		} else { // duplicate ID with allow_overwrite
				db_perform(TABLE_CONTACTS, $sql_data_array, 'update', 'id = ' . (int)$acct_id);
				$force_mail_address = false;
	  		}
		}

		// address book fields
		$sql_data_array = array();
		if (!$address_id) { // check for the address already there using criteria_fields to match
			$criteria_fields = array('primary_name', 'address1', 'postal_code');

			$sql = "SELECT address_id FROM " . TABLE_ADDRESS_BOOK . " WHERE ";
			foreach ($criteria_fields as $name) {
				$field_to_test = $add_type . '_' . $name;
				$sql .= $name . " = '" . db_input($this->$field_to_test) . "' and ";
			}
			$sql .= "ref_id = " . $acct_id;
			$result = $admin->DataBase->query($sql);
			$address_id = ($result->fetch(\PDO::FETCH_NUM) > 0) ? $result['address_id'] : '';
		}

		$add_fields = array('primary_name', 'contact', 'address1', 'address2', 'city_town',
			'state_province', 'postal_code', 'country_code', 'telephone1', 'telephone2',
			'telephone3', 'telephone4', 'email', 'website');
		foreach ($add_fields as $name) {
			$field_to_test = $add_type . '_' . $name;
			if (isset($this->$field_to_test)) $sql_data_array[$name] = $this->$field_to_test;
		}

		$sql_data_array['ref_id'] = $acct_id;
		if (!$address_id) { // create new address
	  		$sql_data_array['type'] = ($force_mail_address) ? ($acct_type . 'm') : $type;
	  		db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'insert');
	  		$address_id = \core\classes\PDO::lastInsertId('id');
		} else { // then update address
	  		db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', 'address_id = ' . (int)$address_id);
		}
		// update the journal_main array since we could have new id's
		switch ($add_type) {
	  		case 'mail':
	  		case 'bill':
				$this->journal_main_array['bill_acct_id']    = $acct_id;
				$this->journal_main_array['bill_address_id'] = $address_id;
				break;
	  		case 'ship':
				$this->journal_main_array['ship_acct_id']    = $acct_id;
				$this->journal_main_array['ship_address_id'] = $address_id;
				break;
	  		default:
		}
		return $acct_id; // should be either passed id or new id if record was created
  	}

  	final function get_recur_ids($recur_id, $id) {
		global $admin;
		// special case when re-posting and the post date is changed, need to fetch original post date
		// from orginal record to include in original transaction
		$result = $admin->DataBase->query("SELECT post_date FROM " . TABLE_JOURNAL_MAIN . " WHERE id = " . $id);
		$post_date = $result['post_date'];
		$output = array();
		$sql = $admin->DataBase->prepare("SELECT id, post_date, purchase_invoice_id, terminal_date FROM " . TABLE_JOURNAL_MAIN . " WHERE recur_id = $recur_id and post_date >= '$post_date' order by post_date");
		$sql->execute();
		return $sql->fetchAll(\PDO::FETCH_LAZY);
  	}
} // end class journal
?>