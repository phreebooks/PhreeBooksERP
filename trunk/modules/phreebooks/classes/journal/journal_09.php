<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/phreebooks/classes/journal/journal_9.php
//
// Sales Quote Journal (9)
namespace phreebooks\classes\journal;
class journal_09 extends \core\classes\journal {
	public $id;
	public $recur_id;
	public $recur_frequency;
	public $so_po_ref_id;
	public $bill_acct_id;
	public $bill_address_id;
	public $terms;
	public $item_count;
	public $weight;
	public $printed 			= false;
	public $waiting 			= false;
	public $purchase_invoice_id;
	public $bill_add_update		= false;
	public $closed 				= false;
	public $gl_type             = 'soo';
	public $popup_form_type		= 'cust:quot';
	public $account_type		= 'c';
	public $gl_acct_id          = AR_DEFAULT_GL_ACCT;
	public $text_contact_id		= TEXT_CUSTOMER_ID;
	public $text_account		= TEXT_AR_ACCOUNT;
	public $text_column_1_title	= TEXT_QUANTITY;
	public $text_column_2_title	= TEXT_INVOICED;
	public $text_order_closed	= TEXT_CLOSE;
	public $item_col_1_enable 	= true;				// allow/disallow entry of item columns
	public $item_col_2_enable 	= false;
	public $currencies_code     = DEFAULT_CURRENCY;
	public $currencies_value    = '1.0';
	public $bill_primary_name   = TEXT_NAME_OR_COMPANY;
	public $bill_contact        = TEXT_ATTENTION;
	public $bill_address1       = TEXT_ADDRESS1;
	public $bill_address2       = TEXT_ADDRESS2;
	public $bill_city_town      = TEXT_CITY_TOWN;
	public $bill_state_province = TEXT_STATE_PROVINCE;
	public $bill_postal_code    = TEXT_POSTAL_CODE;
	public $bill_country_code   = COMPANY_COUNTRY;
	// shipping defaults
	public $ship_short_name     = '';
	public $ship_add_update     = 0;
	public $ship_acct_id        = 0;
	public $ship_address_id     = 0;
	public $ship_country_code   = COMPANY_COUNTRY;
	public $shipper_code        = '';
	public $drop_ship           = 0;
	public $freight             = 0;
	public $disc_gl_acct_id     = AR_DISCOUNT_SALES_ACCOUNT;
	public $ship_gl_acct_id     = AR_DEF_FREIGHT_ACCT;
	public $ship_primary_name   = TEXT_NAME_OR_COMPANY;
	public $ship_contact        = TEXT_ATTENTION;
	public $ship_address1       = TEXT_ADDRESS1;
	public $ship_address2       = TEXT_ADDRESS2;
	public $ship_city_town      = TEXT_CITY_TOWN;
	public $ship_state_province = TEXT_STATE_PROVINCE;
	public $ship_postal_code    = TEXT_POSTAL_CODE;
	public $ship_telephone1     = TEXT_TELEPHONE;
	public $ship_email          = TEXT_EMAIL;
	public $error_6 			= GENERAL_JOURNAL_9_ERROR_6;

	function __construct( $id = 0, $verbose = true){
		if (isset($_SESSION['admin_prefs']['def_ar_acct'])) $this->gl_acct_id =  $_SESSION['admin_prefs']['def_ar_acct'];
		parent::__construct( $id, $verbose);
	}

	/*******************************************************************************************************************/
	// START re-post Functions
	/*******************************************************************************************************************/
	function check_for_re_post() {
		global $admin;
		$admin->messageStack->debug("\n  nothing to re-post for sales quotes ");
		return array();
	}

	/*******************************************************************************************************************/
	// START Chart of Accout Functions
	/*******************************************************************************************************************/
	function Post_chart_balances() {
		global $admin;
		$admin->messageStack->debug("\n  Not changing the chart of account balances for sales qoutes");
	}

	/**
	 * this function will un do the changes to the chart_of_account_history table
	 */
	function unPost_chart_balances() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting Chart Balances...");
		$admin->messageStack->debug(" end unPosting Chart Balances with no action.");
	}

	// *********  chart of account support functions  **********
	function update_chart_history_periods($period = CURRENT_ACCOUNTING_PERIOD) {
		global $admin;
		$admin->messageStack->debug("\n    Returning from Update Chart History Periods with no action required.");
		return true;
	}

	/*******************************************************************************************************************/
	// END Chart of Accout Functions
	/*******************************************************************************************************************/
	// START Customer/Vendor Account Functions
	/*******************************************************************************************************************/
	// Post the customers/vendors sales/purchases values for the given period
	function Post_account_sales_purchases() {
		global $admin;
		$admin->messageStack->debug("\n  Posting account sales and purchases ...");
		$admin->messageStack->debug(" end Posting account sales and purchases with no action.");
		return true;
	}

	/**
	 * this function will delete the customer/vendor history for this journal
	 * @throws Exception
	 */

	function unPost_account_sales_purchases() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting account sales and purchases ...");
		// nothing required to do
		$admin->messageStack->debug(" end unPosting account sales and purchases with no action.");
	}

	/*******************************************************************************************************************/
	// END Customer/Vendor Account Functions
	/*******************************************************************************************************************/
	// START Inventory Functions
	/*******************************************************************************************************************/
	function Post_inventory() {
		global $admin;
		$admin->messageStack->debug("\n  Posting Inventory ...");
		$admin->messageStack->debug(" end Posting Inventory not requiring any action.");
		return true;
	}

	function unPost_inventory() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting Inventory ...");
		// if remaining <> qty then some items have been sold; reduce qty and remaining by original qty (qty will be 0)
		// and keep record. Quantity may go negative because it was used in a COGS calculation but will be corrected when
		// new inventory has been received and the associated cost applied. If the quantity is changed, the new remaining
		// value will be calculated when the updated purchase/receive is posted.
		$admin->messageStack->debug(" end unPosting Inventory with no action.");
	}


	// *********  inventory support functions  **********

	/**
	 *
	 * Enter description here ...
	 * @param array $item
	 * @param bool $return_cogs should cogs be returned
	 * @throws Exception
	 */
	function calculate_COGS($item, $return_cogs = false) {
		global $admin;
		$admin->messageStack->debug("\n    Calculating COGS, SKU = {$item['sku']} and QTY = {$item['qty']}");
		$cogs = 0;
		// fetch the additional inventory item fields we need
		$raw_sql = "SELECT inactive, inventory_type, account_inventory_wage, account_cost_of_sales, item_cost, cost_method, quantity_on_hand, serialize FROM " . TABLE_INVENTORY . " WHERE sku = '{$item['sku']}'";
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		// catch sku's that are not in the inventory database but have been requested to post, error
		if ($sql->rowCount() == 0) throw new \core\classes\userException(GL_ERROR_CALCULATING_COGS);
		$defaults = $sql->fetch(\PDO::FETCH_LAZY);
		// only calculate cogs for certain inventory_types
		if (strpos(COG_ITEM_TYPES, $defaults['inventory_type']) === false) {
			$admin->messageStack->debug(". Exiting COGS, no work to be done with this SKU.");
			return true;
		}
		if (ENABLE_MULTI_BRANCH) $defaults['quantity_on_hand'] = $this->branch_qty_on_hand($item['sku'], $defaults['quantity_on_hand']);
		// catch sku's that are serialized and the quantity is not one, error
		if ($defaults['serialize'] && abs($item['qty']) <> 1) throw new \core\classes\userException(GL_ERROR_SERIALIZE_QUANTITY);
		if ($defaults['serialize'] && !$item['serialize_number']) throw new \core\classes\userException(GL_ERROR_SERIALIZE_EMPTY);
		if ($item['qty'] > 0) { // for positive quantities, inventory received, customer credit memos, unbuild assembly
			// if insert, enter SYSTEM ENTRY COGS cost only if inv on hand is negative
			// update will never happen because the entries are removed during the unpost operation.
			// for all other journals, use the cost as entered to calculate added inventory
			// 	adjust remaining quantities for inventory history since stock was negative
			$history_array = array(
					'ref_id'     => $this->id,
					'store_id'   => $this->store_id,
					'journal_id' => 9,
					'sku'        => $item['sku'],
					'qty'        => $item['qty'],
					'remaining'  => $item['qty'],
					'unit_cost'  => $item['price'],
					'avg_cost'   => $item['avg_cost'],
					'post_date'  => $this->post_date,
			);
			if ($defaults['serialize']) { // check for duplicate serial number
				$raw_sql = "SELECT id, remaining, unit_cost FROM " . TABLE_INVENTORY_HISTORY . "
				WHERE sku = '{$item['sku']}' and remaining > 0 and serialize_number = '{$item['serialize_number']}'";
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
				if ($sql->rowCount() <> 0) throw new \core\classes\userException(GL_ERROR_SERIALIZE_COGS);
				$history_array['serialize_number'] = $item['serialize_number'];
			}
			$admin->messageStack->debug("\n      Inserting into inventory history = " . print_r($history_array, true));
			$result = db_perform(TABLE_INVENTORY_HISTORY, $history_array, 'insert');
			if ($result->AffectedRows() <> 1) throw new \core\classes\userException(TEXT_ERROR_POSTING_INVENTORY_HISTORY);
		} else { // for negative quantities, i.e. sales, negative inv adjustments, assemblies, vendor credit memos
			// if insert, calculate COGS pulling from one or more history records (inv may go negative)
			// update should never happen because COGS is backed out during the unPost inventory function
			$working_qty = -$item['qty']; // quantity needs to be positive
			$history_ids = array(); // the id's used to calculated cogs from the inventory history table
			$queue_sku = false;
			if ($defaults['cost_method'] == 'a') {
				$raw_sql = "SELECT SUM(remaining) as remaining FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='{$item['sku']}' AND remaining > 0";
				if (ENABLE_MULTI_BRANCH) $raw_sql .= " AND store_id='{$this->store_id}'";
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
				$result = $sql->fetch(\PDO::FETCH_LAZY);//@todo work around
				if ($result['remaining'] < $working_qty) $queue_sku = true; // not enough of this SKU so just queue it up until stock arrives
				$avg_cost = $this->fetch_avg_cost($item['sku'], $working_qty);
			}
			if ($defaults['serialize']) { // there should only be one record with one remaining quantity
				$raw_sql = "SELECT id, remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY."
				WHERE sku='{$item['sku']}' AND remaining > 0 AND serialize_number='{$item['serialize_number']}'";
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
				if ($sql->rowCount() <> 1) throw new \core\classes\userException(GL_ERROR_SERIALIZE_COGS);
			} else {
				$raw_sql = "SELECT id, remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY."
				WHERE sku='{$item['sku']}' AND remaining > 0"; // AND post_date <= '$this->post_date 23:59:59'"; // causes re-queue to owed table for negative inventory posts and rcv after sale date
				if (ENABLE_MULTI_BRANCH) $raw_sql .= " AND store_id='{$this->store_id}'";
				$raw_sql .= " ORDER BY ".($defaults['cost_method']=='l' ? 'post_date DESC, id DESC' : 'post_date, id');
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
			}
			if (!$queue_sku) while ($result = $sql->fetch(\PDO::FETCH_LAZY)) { // loops until either qty is zero and/or inventory history is exhausted
				if ($defaults['cost_method'] == 'a') { // Average cost
					$cost = $avg_cost;
				} else {  // FIFO, LIFO
					$cost = $result['unit_cost']; // for the specific history record
				}
				// 	Calculate COGS and adjust remaining levels based on costing method and history
				// 	  there are two possibilities, inventory is in stock (deduct from inventory history)
				// 	  or inventory is out of stock (balance goes negative, COGS to be calculated later)
				if ($working_qty <= $result['remaining']) { // this history record has enough to fill request
					$cost_qty = $working_qty;
					$working_qty = 0;
					$exit_loop = true;
				} else { // qty will span more than one history record, just calculate for this record
					$cost_qty = $result['remaining'];
					$working_qty -= $result['remaining'];
					$exit_loop = false;
				}
				// save the history record id used along with the quantity for roll-back purposes
				$history_ids[] = array('id' => $result['id'], 'qty' => $cost_qty); // how many from what id
				$cogs += $cost * $cost_qty;
				$sql = "UPDATE ".TABLE_INVENTORY_HISTORY." SET remaining = remaining - $cost_qty WHERE id=".$result['id'];
				$admin->DataBase->exec($sql);
				if ($exit_loop) break;
			}
			for ($i = 0; $i < count($history_ids); $i++) {
				$sql_data_array = array(
						'inventory_history_id' => $history_ids[$i]['id'],
						'qty'                  => $history_ids[$i]['qty'],
						'journal_main_id'      => $this->id,
				);
				db_perform(TABLE_INVENTORY_COGS_USAGE, $sql_data_array, 'insert');
			}
			// see if there is quantity left to account for but nothing left in inventory (less than zero inv balance)
			if ($working_qty > 0) {
				if (!ALLOW_NEGATIVE_INVENTORY) throw new \core\classes\userException(GL_ERROR_POSTING_NEGATIVE_INV);
				// for now, estimate the cost based on the unit_price of the item, will be re-posted (corrected) when product arrives
				$cost = $defaults['cost_method']=='a' ? $avg_cost : $defaults['item_cost']; // for the specific history record
				$cogs += $cost * $working_qty;
				// queue the journal_main_id to be re-posted later after inventory is received
				$sql_data_array = array(
						'journal_main_id' => $this->id,
						'sku'             => $item['sku'],
						'qty'             => $working_qty,
						'post_date'       => $this->post_date,
						'store_id'        => $this->store_id,
				);
				$admin->messageStack->debug("\n    Adding inventory_cogs_owed, SKU = {$item['sku']}, qty = " . $working_qty);
				db_perform(TABLE_INVENTORY_COGS_OWED, $sql_data_array, 'insert');
			}
		}

		$this->sku_cogs = $cogs;
		if ($return_cogs) return $cogs; // just calculate cogs and adjust inv history
		$admin->messageStack->debug("\n    Adding COGS to array (if not zero), sku = {$item['sku']} with calculated value = $cogs");
		if ($cogs) {
			// credit inventory cost of inventory
			$cogs_acct = $defaults['account_inventory_wage'];
			if ($cogs >= 0 ) {
				$this->cogs_entry[$cogs_acct]['credit'] += $cogs;
			} else {
				$this->cogs_entry[$cogs_acct]['debit']  += -$cogs;
			}
			// debit cogs account for income statement
			$cogs_acct = $this->override_cogs_acct ? $this->override_cogs_acct : $defaults['account_cost_of_sales'];
			if ($cogs >= 0 ) {
				$this->cogs_entry[$cogs_acct]['debit']  += $cogs;
			} else {
				$this->cogs_entry[$cogs_acct]['credit'] += -$cogs;
			}
		}
		$admin->messageStack->debug(" ... Finished calculating COGS.");
		return true;
	}

	/**
	 *
	 * @param string $sku
	 * @param number $qty
	 * @param string $serial_num
	 */
	function calculateCost($sku = '', $qty=1, $serial_num='') {
		global $admin;
		$admin->messageStack->debug("\n    Calculating SKU cost, SKU = $sku and QTY = $qty");
		$cogs = 0;
		$sql = $admin->DataBase->prepare("SELECT inventory_type, item_cost, cost_method, serialize FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
		$sql->execute();
		$defaults = $sql->fetch(\PDO::FETCH_LAZY);
		if ($sql->rowCount() == 0) return $cogs; // not in inventory, return no cost
		if (strpos(COG_ITEM_TYPES, $defaults['inventory_type']) === false) return $cogs; // this type not tracked in cog, return no cost
		if ($defaults['cost_method'] == 'a') return $qty * $this->fetch_avg_cost($sku, $qty);
		if ($defaults['serialize']) { // there should only be one record
			$result = $admin->DataBase->query("SELECT unit_cost FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' AND serialize_number='$serial_num'");
			return $result['unit_cost'];
		}
		$raw_sql = "SELECT remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' AND remaining>0";
		if (ENABLE_MULTI_BRANCH) $raw_sql .= " AND store_id='$this->store_id'";
		$raw_sql .= " ORDER BY id" . ($defaults['cost_method'] == 'l' ? ' DESC' : '');
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		$working_qty = abs($qty);
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) { // loops until either qty is zero and/or inventory history is exhausted
			if ($working_qty <= $result['remaining']) { // this history record has enough to fill request
				$cogs += $result['unit_cost'] * $working_qty;
				$working_qty = 0;
				break; // exit loop
			}
			$cogs += $result['unit_cost'] * $result['remaining'];
			$working_qty -= $result['remaining'];
		}
		if ($working_qty > 0) $cogs += $defaults['item_cost'] * $working_qty; // leftovers, use default cost
		$admin->messageStack->debug(" ... Finished calculating cost: $cogs");
		return $cogs;
	}

	/**
	 *
	 * @param string $sku
	 * @param number $price
	 * @param number $qty
	 */
	function calculate_avg_cost($sku = '', $price = 0, $qty = 1) {
		global $admin;
		$raw_sql = "SELECT avg_cost, remaining FROM ".TABLE_INVENTORY_HISTORY." WHERE ref_id<>{$this->id} AND sku='{$sku}' AND remaining>0 AND post_date<='{$this->post_date}'";
		if ($this->store_id > 0) $raw_sql .= " AND store_id='{$this->store_id}'";
		$raw_sql .= " ORDER BY post_date, id";
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		$total_stock = 0;
		$last_cost   = 0;
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$total_stock += $result['remaining'];
			$last_cost    = $result['avg_cost']; // just keep the cost from the last record as this keeps the avg value of the post date
		}
		if ($total_stock == 0 && $qty == 0) return 0;
		$avg_cost = (($last_cost * $total_stock) + ($price * $qty)) / ($total_stock + $qty);
		return $avg_cost;
	}

	/**
	 *
	 * @param string $sku
	 * @param number $qty
	 */
	function fetch_avg_cost($sku = '', $qty=1) {
		global $admin;
		$admin->messageStack->debug("\n      Entering fetch_avg_cost for sku: $sku and qty: $qty ... ");
		$raw_sql = "SELECT avg_cost, remaining, post_date FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' AND remaining>0";
		if (ENABLE_MULTI_BRANCH) $raw_sql .= " AND store_id='$this->store_id'";
		$raw_sql .= " ORDER BY post_date";
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		$temp = $sql->fetchAll();
		$last_cost = isset($temp[0]['avg_cost']) ? $temp[0]['avg_cost'] : 0;
		$last_qty = 0;
		$ready_to_exit = false;
		foreach( $temp as $key => $result) {
			$qty -= $result['remaining'];
			$post_date = substr($result['post_date'], 0, 10);
			if ($qty <= 0) $ready_to_exit = true;
			if ($ready_to_exit && $post_date > $this->post_date) { // will get the last purchase cost before the sale post date
				$admin->messageStack->debug("Exiting early with history post_date = $post_date fetch_avg_cost with cost = ".($last_qty > 0 ? $result['avg_cost'] : $last_cost));
				return $last_qty > 0 ? $result['avg_cost'] : $last_cost;
			}
			$last_cost = $result['avg_cost'];
			$last_qty = $qty; // not finished yet, get next average cost
		}
		$admin->messageStack->debug("Exiting fetch_avg_cost with cost = $last_cost");
		return $last_cost;
	}

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
		if ($sql->rowCount() == 0) {
			$admin->messageStack->debug(" ...Exiting COGS, no work to be done.");
			return true;
		}
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_HISTORY . " SET remaining = remaining + {$result['qty']} WHERE id = " . $result['inventory_history_id']);
		}
		$admin->messageStack->debug(" ... Finished rolling back COGS");
		return true;
	}

	function load_so_po_balance($ref_id, $id = '', $post = true) {
		global $admin;
		$admin->messageStack->debug("\n    Starting to load SO/PO balances ...");
		$item_array = array();
		if ($ref_id) throw new \core\classes\userException('Error in classes/gen_ledger, function load_so_po_balance. Bad 09 for this function.');
		$this->so_po_balance_array = $item_array;
		$admin->messageStack->debug(" Finished loading SO/PO balances = " . print_r($item_array, true));
		return $item_array;
	}


	/*******************************************************************************************************************/
	// END Inventory Functions
	/*******************************************************************************************************************/
	// START General Functions
	/*******************************************************************************************************************/

	function check_for_closed_po_so($action = 'Post') {
		global $admin;
		// closed can occur many ways including:
		//   forced closure through so/po form (from so/po journal - adjust qty on so/po)
		//   all quantities are reduced to zero (from so/po journal - should be deleted instead but it's possible)
		//   editing quantities on po/so to match the number received (from po/so journal)
		//   receiving all (or more) po/so items through one or more purchases/sales (from purchase/sales journal)
		$admin->messageStack->debug("\n  Checking for closed entry. action = " . $action);
		return true;
	}

	/**
	 * checks if the invoice nr is valid if allowed it will create a new one when empty
	 * @throws Exception
	 */
	function validate_purchase_invoice_id() {
		global $admin;
		$admin->messageStack->debug("\n  Start validating purchase_invoice_id ... ");
		if ($this->purchase_invoice_id <> '') {	// entered a so/po/invoice value, check for dups
			$sql = "SELECT purchase_invoice_id FROM " . TABLE_JOURNAL_MAIN . " WHERE purchase_invoice_id = '{$this->purchase_invoice_id}' and journal_id = '9'";
			if ($this->id) $sql .= " and id <> " . $this->id;
			$result = $admin->DataBase->query($sql);
			if ($result->rowCount() > 0) throw new \core\classes\userException(sprintf(TEXT_THE_YOU_ENTERED_IS_A_DUPLICATE,_PLEASE_ENTER_A_NEW_UNIQUE_VALUE_ARGS, $journal_types_list[9]['id_field_name']));
			$this->journal_main_array['purchase_invoice_id'] = $this->purchase_invoice_id;
			$admin->messageStack->debug(" specified ID but no dups, returning OK. ");
		} else {	// generate a new order/invoice value
			$result = $admin->DataBase->query("SELECT next_ar_quote_num FROM " . TABLE_CURRENT_STATUS . " LIMIT 1");
			if (!$result) throw new \core\classes\userException(sprintf(GL_ERROR_CANNOT_FIND_NEXT_ID, TABLE_CURRENT_STATUS));
			$this->journal_main_array['purchase_invoice_id'] = $result['next_ar_quote_num'];
			$admin->messageStack->debug(" generated ID, returning ID# " . $this->journal_main_array['purchase_invoice_id']);
		}
		return true;
	}

	function increment_purchase_invoice_id($force = false) {
		global $admin;
		if ($this->purchase_invoice_id == '' || $force) { // increment the po/so/invoice number
			$next_id = string_increment($this->journal_main_array['purchase_invoice_id']);
			$sql = "UPDATE " . TABLE_CURRENT_STATUS . " SET next_ar_quote_num = '$next_id'";
			if (!$force) $sql .= " WHERE next_ar_quote_num = '{$this->journal_main_array['purchase_invoice_id']}'";
			$result = $admin->DataBase->exec($sql);
			if ($result->AffectedRows() <> 1) throw new \core\classes\userException(sprintf(TEXT_THERE_WAS_AN_ERROR_INCREMENTING_THE_ARGS, $journal_types_list[9]['id_field_name']));
		}
		$this->purchase_invoice_id = $this->journal_main_array['purchase_invoice_id'];
		return true;
	}
	/*******************************************************************************************************************/
	//START former Orders Class
	/*******************************************************************************************************************/

	function post_ordr($action) {
		global $admin, $messageStack;
		$this->journal_rows = array();	// initialize ledger row(s) array
		$debit_total  = 0;
		$credit_total = 0;
		$credit_total += $this->add_item_journal_rows('credit'); // read in line items and add to journal row array
		$credit_total += $this->add_freight_journal_row('credit');	// put freight into journal row array
		$credit_total += $this->add_tax_journal_rows('credit');	// fetch tax rates for tax calculation
		$debit_total  += $this->add_discount_journal_row('debit'); // put discount into journal row array
		$this->total_amount = $credit_total - $debit_total;
		$debit_total  += $this->add_total_journal_row('debit');	// put total value into ledger row array
		$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record

		// ***************************** START TRANSACTION *******************************
		$messageStack->debug("\n  started order post purchase_invoice_id = {$this->purchase_invoice_id} and id = " . $this->id);
		$admin->DataBase->transStart();
		// *************  Pre-POST processing *************
		// add/update address book
		if ($this->bill_add_update) { // billing address
			$this->bill_acct_id = $this->add_account($this->account_type . 'b', $this->bill_acct_id, $this->bill_address_id);
			if (!$this->bill_acct_id)  throw new \core\classes\userException('no contact was selected');
		}
		if ($this->ship_add_update) { // shipping address
			if (!$this->ship_acct_id) $this->ship_acct_id = $this->bill_acct_id; // set to bill if adding contact and id not set.
			if ($this->bill_address_id == $this->ship_address_id) $this->ship_address_id = ''; // force create new ship address, here from copy button
			$this->ship_acct_id = $this->add_account($this->account_type . 's', $this->ship_acct_id, $this->ship_address_id);
			if (!$this->ship_acct_id)  throw new \core\classes\userException('no shipping contact was selected');
		}
		// set the ship account id to bill account id if null (new accounts with ship update box not checked)
		// basically defaults the shipping same as billing if not specified
		if (!$this->journal_main_array['ship_acct_id'])    $this->journal_main_array['ship_acct_id']    = $this->journal_main_array['bill_acct_id'];
		if (!$this->journal_main_array['ship_address_id']) $this->journal_main_array['ship_address_id'] = $this->journal_main_array['bill_address_id'];

		// ************* POST journal entry *************
		if ($this->recur_id > 0) { // if new record, will contain count, if edit will contain recur_id
			$first_id                  = $this->id;
			$first_post_date           = $this->post_date;
			$first_purchase_invoice_id = $this->purchase_invoice_id;
			$first_terminal_date       = $this->first_terminal_date;
			if ($this->id) { // it's an edit, fetch list of affected records to update if roll is enabled
				$affected_ids = $this->get_recur_ids($this->recur_id, $this->id);
				for ($i = 0; $i < count($affected_ids); $i++) {
					$this->id = $affected_ids[$i]['id'];
					$this->journal_main_array['id'] = $affected_ids[$i]['id'];
					$this->remove_cogs_rows();
					if ($i > 0) { // Remove row id's for future posts, keep if re-posting single entry
						for ($j = 0; $j < count($this->journal_rows); $j++) $this->journal_rows[$j]['id'] = '';
						$this->post_date           = $affected_ids[$i]['post_date'];
						$this->terminal_date       = $affected_ids[$i]['terminal_date'];
						$this->purchase_invoice_id = $affected_ids[$i]['purchase_invoice_id'];
					} else { // for first entry, post date may be changed, use $_POST value
						$this->post_date           = $first_post_date;
						$this->terminal_date       = $first_terminal_date;
						$this->purchase_invoice_id = $first_purchase_invoice_id;
					}
					$this->period        = gen_calculate_period($this->post_date, true);
					$this->journal_main_array['post_date']     = $this->post_date;
					$this->journal_main_array['period']        = $this->period;
					$this->journal_main_array['terminal_date'] = $this->terminal_date;
					$this->validate_purchase_invoice_id();
					$messageStack->debug("\n\n  re-posting recur id = " . $this->id);
					$this->Post('edit');
					// test for single post versus rolling into future posts, terminate loop if single post
					if (!$this->recur_frequency) break;
				}
			} else { // it's an insert
				// fetch the next recur id
				$this->journal_main_array['recur_id'] = time();
				$day_offset   = 0;
				$month_offset = 0;
				$year_offset  = 0;
				$post_date    = $this->post_date;
				for ($i = 0; $i < $this->recur_id; $i++) {
					$this->validate_purchase_invoice_id();
					$this->Post('insert');
					$this->id = '';
					$this->journal_main_array['id'] = $this->id;
					$this->remove_cogs_rows();
					for ($j = 0; $j < count($this->journal_rows); $j++) $this->journal_rows[$j]['id'] = '';
					switch ($this->recur_frequency) {
						default:
						case '1': $day_offset   = ($i+1)*7;  break; // Weekly
						case '2': $day_offset   = ($i+1)*14; break; // Bi-weekly
						case '3': $month_offset = ($i+1)*1;  break; // Monthly
						case '4': $month_offset = ($i+1)*3;  break; // Quarterly
						case '5': $year_offset  = ($i+1)*1;  break; // Yearly
					}
					$this->post_date     = gen_specific_date($post_date, $day_offset, $month_offset, $year_offset);
					if ($this->terminal_date) $this->terminal_date = gen_specific_date($this->terminal_date, $day_offset, $month_offset, $year_offset);
					$this->period        = gen_calculate_period($this->post_date, true);
					if (!$this->period && $i < ($this->recur_id - 1)) { // recur falls outside of available periods, ignore last calculation
						throw new \core\classes\userException(ORD_PAST_LAST_PERIOD);
					}
					$this->journal_main_array['post_date']     = $this->post_date;
					$this->journal_main_array['period']        = $this->period;
					$this->journal_main_array['terminal_date'] = $this->terminal_date;
					$this->purchase_invoice_id = string_increment($this->journal_main_array['purchase_invoice_id']);
				}
			}
			$this->id                  = $first_id;
			$this->post_date           = $first_post_date;
			$this->purchase_invoice_id = $first_purchase_invoice_id;
			$this->terminal_date       = $first_terminal_date;
			$this->journal_main_array['id']                  = $first_id;
			$this->journal_main_array['post_date']           = $first_post_date;
			$this->journal_main_array['purchase_invoice_id'] = $first_purchase_invoice_id;
			$this->journal_main_array['terminal_date']       = $first_terminal_date;
		} else {
			$this->validate_purchase_invoice_id();
			$this->Post($this->id ? 'edit' : 'insert');
		}
		// ************* post-POST processing *************
		if ($this->purchase_invoice_id == '') {	// it's a new record, increment the po/so/inv to next number
			$this->increment_purchase_invoice_id();
		}
		$messageStack->debug("\n  committed order post purchase_invoice_id = {$this->purchase_invoice_id} and id = {$this->id}");
		$admin->DataBase->transCommit();	// finished successfully
		//echo 'committed transaction - bailing!'; exit();
		// ***************************** END TRANSACTION *******************************
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_POSTED, $journal_types_list[9]['id_field_name'], $this->purchase_invoice_id), 'success');
		return true;
	}

	function unPost($action = 'delete', $skip_balance = false) {
		global $admin;
		// verify no item rows have been acted upon (received, shipped, paid, etc.)
		// *************** START TRANSACTION *************************
		$recur_id        = $this->recur_id;
		$recur_frequency = $this->recur_frequency;
		if ($recur_id > 0) { // will contain recur_id
			$affected_ids = $this->get_recur_ids($recur_id, $this->id);
			for ($i = 0; $i < count($affected_ids); $i++) {
				$this->id = $affected_ids[$i]['id'];
				$this->journal($this->id); // load the posted record based on the id submitted
				parent::unPost('delete');
				// test for single post versus rolling into future posts, terminate loop if single post
				if (!$recur_frequency) break;
			}
		} else {
			parent::unPost('delete');
		}
		// *************** END TRANSACTION *************************
		return true;
	}

	function add_total_journal_row($debit_credit) {	// put total value into ledger row array
		if ($debit_credit != 'debit' && $debit_credit != 'credit') throw new \core\classes\userException(sprintf("bad parameter passed to ",__METHOD__ ) );
			$this->journal_rows[] = array( // record for accounts receivable
					'gl_type'                 => 'ttl',
					$debit_credit . '_amount' => $this->total_amount,
					'description'             => $journal_types_list[9]['text'] . ' - ' . TEXT_TOTAL,
					'gl_account'              => $this->gl_acct_id,
					'post_date'               => $this->post_date,
			);
			return $this->total_amount;
	}

	function add_discount_journal_row($debit_credit) { // put discount into journal row array
		if ($debit_credit != 'debit' && $debit_credit != 'credit') throw new \core\classes\userException(sprintf("bad parameter passed to ",__METHOD__ ) );
			if ($this->discount <> 0) {
				$this->journal_rows[] = array(
						'qty'                     => '1',
						'gl_type'                 => 'dsc',		// code for discount charges
						$debit_credit . '_amount' => $this->discount,
						'description'             => $journal_types_list[9]['text'] . ' - ' . TEXT_DISCOUNT,
						'gl_account'              => $this->disc_gl_acct_id,
						'taxable'                 => '0',
						'post_date'               => $this->post_date,
				);
			}
			return $this->discount;
	}

	function add_freight_journal_row($debit_credit) {	// put freight into journal row array
		if ($debit_credit != 'debit' && $debit_credit != 'credit') throw new \core\classes\userException(sprintf("bad parameter passed to ",__METHOD__ ) );
			// if no line items are charged tax, do not charge tax on shipping. ADDED 2014-04-28 by Dave
			$tax_freight = false;
			foreach ($this->journal_rows as $line_item) {
				if ($line_item['taxable'] > 0 && $line_item['gl_type'] == $this->gl_type) $tax_freight = true;
			}

			$freight_tax_id = $tax_freight ? AR_ADD_SALES_TAX_TO_SHIPPING : 0;
			if ($this->freight) { // calculate freight charges
				$this->journal_rows[] = array(
						'qty'                     => '1',
						'gl_type'                 => 'frt',		// code for shipping/freight charges
						$debit_credit . '_amount' => $this->freight,
						'description'             => $journal_types_list[9]['text'] . ' - ' . TEXT_SHIPPING,
						'gl_account'              => $this->ship_gl_acct_id,
						'taxable'                 => $freight_tax_id,
						'post_date'               => $this->post_date,
				);
			}
			return $this->freight;
	}

	function add_item_journal_rows($debit_credit) {	// read in line items and add to journal row array
		if ($debit_credit != 'debit' && $debit_credit != 'credit') throw new \core\classes\userException(sprintf("bad parameter passed to ",__METHOD__ ) );
			$total = 0;
			for ($i=0; $i<count($this->item_rows); $i++) {
				if ($this->item_rows[$i]['qty']) { // make sure the quantity line is set and not zero
					$this->journal_rows[] = array(
							'id'                      => $this->item_rows[$i]['id'],	// retain the db id (used for updates)
							'item_cnt'                => $this->item_rows[$i]['item_cnt'],
							'so_po_item_ref_id'       => $this->item_rows[$i]['so_po_item_ref_id'],	// item reference id for so/po line items
							'gl_type'                 => $this->gl_type,
							'sku'                     => $this->item_rows[$i]['sku'],
							'qty'                     => $this->item_rows[$i]['qty'],
							'description'             => $this->item_rows[$i]['desc'],
							$debit_credit . '_amount' => $this->item_rows[$i]['total'],
							'full_price'              => $this->item_rows[$i]['full'],
							'gl_account'              => $this->item_rows[$i]['acct'],
							'taxable'                 => $this->item_rows[$i]['tax'],
							'serialize_number'        => $this->item_rows[$i]['serial'],
							'project_id'              => $this->item_rows[$i]['proj'],
							'purch_package_quantity'  => $this->item_rows[$i]['purch_package_quantity'],
							'post_date'               => $this->post_date,
							'date_1'                  => $this->item_rows[$i]['date_1'] ? $this->item_rows[$i]['date_1'] : $this->terminal_date,
					);
					$total += $this->item_rows[$i]['total'];
				}
			}
			return $total;
	}

	function add_tax_journal_rows($debit_credit) {
		global $admin;
		if ($debit_credit != 'debit' && $debit_credit != 'credit') throw new \core\classes\userException(sprintf("bad parameter passed to ",__METHOD__ ) );
			$total          = 0;
			$auth_array     = array();
			$tax_rates      = ord_calculate_tax_drop_down('b');
			$tax_auths      = gen_build_tax_auth_array();
			$tax_discount   = $this->account_type == 'v' ? AP_TAX_BEFORE_DISCOUNT : AR_TAX_BEFORE_DISCOUNT;
			// calculate each tax value by authority per line item
			foreach ($this->journal_rows as $idx => $line_item) {
				if ($line_item['taxable'] > 0 && ($line_item['gl_type'] == $this->gl_type || $line_item['gl_type'] == 'frt')) {
					foreach ($tax_rates as $rate) {
						if ($rate['id'] == $line_item['taxable']) {
							$auths = explode(':', $rate['auths']);
							foreach ($auths as $auth) {
								$line_total = $line_item['debit_amount'] + $line_item['credit_amount']; // one will always be zero
								if (ENABLE_ORDER_DISCOUNT && $tax_discount == '0' && $line_item['gl_type'] <> 'frt') {
									$line_total = $line_total * (1 - $this->disc_percent);
								}
								//				this is wrong this is rounding per orderline not per tax auth. moved this to the next foreach.
								//				if (ROUND_TAX_BY_AUTH) {
								//				  $auth_array[$auth] += number_format(($tax_auths[$auth]['tax_rate'] / 100) * $line_total, $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places'], '.', '');
								//				} else {
								$auth_array[$auth] += ($tax_auths[$auth]['tax_rate'] / 100) * $line_total;
								//				}
							}
						}
					}
				}
			}
			// calculate each tax total by authority and put into journal row array
			foreach ($auth_array as $auth => $auth_tax_collected) {
				if ($auth_tax_collected == '' && $tax_auths[$auth]['account_id'] == '') continue;
				if( ROUND_TAX_BY_AUTH == true ){
					$amount = number_format($auth_tax_collected, $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places'], '.', '');
				}else {
					$amount = $auth_tax_collected;
				}
				$this->journal_rows[] = array( // record for specific tax authority
						'qty'                     => '1',
						'gl_type'                 => 'tax',		// code for tax entry
						$debit_credit . '_amount' => $amount,
						'description'             => $tax_auths[$auth]['description_short'],
						'gl_account'              => $tax_auths[$auth]['account_id'],
						'post_date'               => $this->post_date,
				);
				$total += $amount;
			}
			$this->sales_tax = $total;
			return $total;
	}
} // end class journal
?>