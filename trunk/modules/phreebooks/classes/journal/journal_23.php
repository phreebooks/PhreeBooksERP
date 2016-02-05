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
//  Path: /modules/phreebooks/classes/journal/journal_23.php
//
// Cash Dist Journal (23) for vendor payments see 20
namespace phreebooks\classes\journal;
class journal_23 extends \core\classes\journal {
	public $save_payment        = false;
	public $gl_type             = 'chk';
	public $popup_form_type		= 'bnk:chk';
	public $account_type		= 'c';
	public $search              = TEXT_SEARCH;
	public $bill_primary_name   = TEXT_NAME_OR_COMPANY;
	public $bill_contact        = TEXT_ATTENTION;
	public $bill_address1       = TEXT_ADDRESS1;
	public $bill_address2       = TEXT_ADDRESS2;
	public $bill_city_town      = TEXT_CITY_TOWN;
	public $bill_state_province = TEXT_STATE_PROVINCE;
	public $bill_postal_code    = TEXT_POSTAL_CODE;
	public $bill_country_code   = COMPANY_COUNTRY;
	public $bill_email          = TEXT_EMAIL;
	public $gl_acct_id			= AP_PURCHASE_INVOICE_ACCOUNT;
	public $gl_disc_acct_id     = AP_DISCOUNT_PURCHASE_ACCOUNT;
	public $error_6				= GENERAL_JOURNAL_23_ERROR_6;
	public $description 		= TEXT_CUSTOMER_REFUNDS;
	public $id_field_name 		= TEXT_RECEIPT_NUMBER;

	function __construct( $id = 0, $verbose = true) {
		global $admin;
		if (isset($_SESSION['admin_prefs']['def_cash_acct'])) $this->gl_acct_id = $_SESSION['admin_prefs']['def_cash_acct'];
		$result = $admin->DataBase->query("select next_check_num from " . TABLE_CURRENT_STATUS);
		$this->purchase_invoice_id = $result['next_check_num'];
		parent::__construct( $id, $verbose);
	}

	/*******************************************************************************************************************/
	// START re-post Functions
	/*******************************************************************************************************************/
	function check_for_re_post() {
		global $admin;
		\core\classes\messageStack::debug_log("\n  Checking for re-post records ... ");
		\core\classes\messageStack::debug_log(" end check for Re-post with no action.");
		return array();
	}

	/*******************************************************************************************************************/
	// START Chart of Accout Functions
	/*******************************************************************************************************************/
	function Post_chart_balances() {
		global $admin;
		\core\classes\messageStack::debug_log("\n  Posting Chart Balances...");
		$accounts = array();
		$precision = $this->currencies[DEFAULT_CURRENCY]['decimal_places'] + 2;
		if (sizeof($this->journal_rows) > 0) foreach ($this->journal_rows as $value) {
			$credit_amount = ($value['credit_amount']) ? $value['credit_amount'] : '0';
			$debit_amount  = ($value['debit_amount'])  ? $value['debit_amount']  : '0';
			if  (round($credit_amount, $precision) <> 0 || round($debit_amount, $precision) <> 0) {
				$accounts[$value['gl_account']]['credit'] += $credit_amount;
				$accounts[$value['gl_account']]['debit']  += $debit_amount;
				$this->affected_accounts[$value['gl_account']] = 1;
			}
		}
		if (sizeof($accounts) > 0) foreach ($accounts as $gl_acct => $values) {
			if  (round($values['credit'], $precision) <> 0 || round($values['debit'], $precision) <> 0) {
				$sql = "UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET credit_amount = credit_amount + {$values['credit']}, debit_amount = debit_amount + {$values['debit']},
				last_update = '$this->post_date' WHERE account_id = '$gl_acct' AND period = $this->period";
				\core\classes\messageStack::debug_log("\n    Post chart balances: credit_amount = {$values['credit']}, debit_amount = {$values['debit']}, acct = $gl_acct, period = $this->period");
				$result = $admin->DataBase->exec($sql);
				if ($result->AffectedRows() <> 1) throw new \core\classes\userException(TEXT_ERROR_POSTING_CHART_OF_ACCOUNT_BALANCES_TO_ACCOUNT_ID .": " . ($gl_acct ? $gl_acct : TEXT_NOT_SPECIFIED));
			}
		}
		\core\classes\messageStack::debug_log("\n  end Posting Chart Balances.");
	}

	/**
	 * this function will un do the changes to the chart_of_account_history table
	 */
	function unPost_chart_balances() {
		global $admin;
		\core\classes\messageStack::debug_log("\n  unPosting Chart Balances...");
		for ($i=0; $i<count($this->journal_rows); $i++) {
			// 	Update chart of accounts history
			$sql = "UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET credit_amount = credit_amount - {$this->journal_rows[$i]['credit_amount']},
			debit_amount = debit_amount - {$this->journal_rows[$i]['debit_amount']}
			WHERE account_id = '{$this->journal_rows[$i]['gl_account']}' and period = " . $this->period;
			\core\classes\messageStack::debug_log("\n    unPost chart balances: credit_amount = {$this->journal_rows[$i]['credit_amount']}, debit_amount = {$this->journal_rows[$i]['debit_amount']}, acct = {$this->journal_rows[$i]['gl_account']}, period = " . $this->period);
			$admin->DataBase->exec($sql);
			$this->affected_accounts[$this->journal_rows[$i]['gl_account']] = 1;
		}
		\core\classes\messageStack::debug_log("\n  end unPosting Chart Balances.");
	}

	// *********  chart of account support functions  **********
	function update_chart_history_periods($period = CURRENT_ACCOUNTING_PERIOD) {
		global $admin;
		// first find out the last period with data in the system from the current_status table
		$sql = $admin->DataBase->query("SELECT fiscal_year FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE period = " . $period);
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException(GL_ERROR_BAD_ACCT_PERIOD);
		$fiscal_year = $sql->fetch(\PDO::FETCH_LAZY);
		$sql = "SELECT max(period) as period FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE fiscal_year = " . $fiscal_year;
		$result = $admin->DataBase->query($sql);
		$max_period = $result['period'];
		$affected_acct_string = (is_array($this->affected_accounts)) ? implode("', '", array_keys($this->affected_accounts)) : '';
		\core\classes\messageStack::debug_log("\n  Updating chart history for fiscal year: $fiscal_year and period: $period for accounts: ('$affected_acct_string')");
		for ($i = $period; $i <= $max_period; $i++) {
			$this->validate_balance($i);//will throw exceptions
			// update future months
			$sql = "SELECT account_id, beginning_balance + debit_amount - credit_amount as beginning_balance FROM " . TABLE_CHART_OF_ACCOUNTS_HISTORY . "
			WHERE account_id in ('$affected_acct_string') and period = " . $i;
			$sql = $admin->DataBase->prepare($sql);
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				$sql = "UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET beginning_balance = {$result['beginning_balance']}
				WHERE period = " . ($i + 1) . " and account_id = '{$result['account_id']}'";
				$admin->DataBase->exec($sql);
			}
		}
		// see if there is another fiscal year to roll into
		$result = $admin->DataBase->query("SELECT fiscal_year FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE period = " . ($max_period + 1));
		if ($result->fetch(\PDO::FETCH_NUM) > 0) { // close balances for end of this fiscal year and roll post into next fiscal year
			// select retained earnings account
			$result = $admin->DataBase->query("SELECT id FROM " . TABLE_CHART_OF_ACCOUNTS . " WHERE account_type = 44");
			if ($result->fetch(\PDO::FETCH_NUM) <> 1) throw new \core\classes\userException(GL_ERROR_NO_RETAINED_EARNINGS_ACCOUNT);
			$retained_earnings_acct = $result['id'];
			$this->affected_accounts[$retained_earnings_acct] = 1;
			// select list of accounts that need to be closed, adjusted
			$sql = $admin->DataBase->prepare("SELECT id FROM " . TABLE_CHART_OF_ACCOUNTS . " WHERE account_type in (30, 32, 34, 42, 44)");
			$sql->execute();
			$acct_list = $sql->fetchAll();
			$acct_string = implode("','",$acct_list);
			// fetch the totals for the closed accounts
			$sql = "SELECT sum(beginning_balance + debit_amount - credit_amount) as retained_earnings
			  FROM " . TABLE_CHART_OF_ACCOUNTS_HISTORY . "
				  WHERE account_id in ('$acct_string') and period = " . $max_period;
			$result = $admin->DataBase->query($sql);
			$retained_earnings = $result['retained_earnings'];
			// clear out the expense, sales, cogs, and other year end accounts that need to be closed
			// needs to be before writing retained earnings account, since retained earnings is part of acct_string
			$result = $admin->DataBase->exec("UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET beginning_balance = 0 WHERE account_id in ('$acct_string') and period = " . ($max_period + 1));
			// update the retained earnings account
			$result = $admin->DataBase->exec("UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET beginning_balance = $retained_earnings WHERE account_id = '$retained_earnings_acct' and period = " . ($max_period + 1));
			// now continue rolling in current post into next fiscal year
			$this->update_chart_history_periods($max_period + 1);
		}
		// all historical chart of account balances from period on should be OK at this point.
		\core\classes\messageStack::debug_log("\n  end Updating chart history periods. Fiscal Year: " . $fiscal_year);;
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
		\core\classes\messageStack::debug_log("\n  Posting account sales and purchases ...");
		if (!$this->bill_acct_id) throw new \core\classes\userException(TEXT_NO_ACCOUNT_NUMBER_PROVIDED_IN_CORE_JOURNAL_FUNCTION . ': '  . 'post_account_sales_purchases.');
		$purchase_invoice_id = $this->purchase_invoice_id ? $this->purchase_invoice_id : $this->journal_main_array['purchase_invoice_id'];
		$history_array = array(
				'ref_id'              => $this->id,
				'so_po_ref_id'        => $this->so_po_ref_id,
				'acct_id'             => $this->bill_acct_id,
				'journal_id'          => 23,
				'purchase_invoice_id' => $purchase_invoice_id,
				'amount'              => $this->total_amount,
				'post_date'           => $this->post_date,
		);
		$result = db_perform(TABLE_ACCOUNTS_HISTORY, $history_array, 'insert');
		if ($result->AffectedRows() <> 1 ) throw new \core\classes\userException(TEXT_ERROR_UPDATING_CONTACT_HISTORY);
		\core\classes\messageStack::debug_log(" end Posting account sales and purchases.");
		return true;
	}

	/**
	 * this function will delete the customer/vendor history for this journal
	 * @throws Exception
	 */

	function unPost_account_sales_purchases() {
		global $admin;
		\core\classes\messageStack::debug_log("\n  unPosting account sales and purchases ...");
		if (!$this->bill_acct_id) throw new \core\classes\userException(TEXT_NO_ACCOUNT_NUMBER_PROVIDED_IN_CORE_JOURNAL_FUNCTION . ': ' . 'unPost_account_sales_purchases.');
		$result = $admin->DataBase->exec("DELETE FROM " . TABLE_ACCOUNTS_HISTORY . " WHERE ref_id = " . $this->id);
		if ($result->AffectedRows() != 1) throw new \core\classes\userException(TEXT_ERROR_DELETING_CUSTOMER_OR_VENDOR_ACCOUNT_HISTORY_RECORD);
		\core\classes\messageStack::debug_log(" end unPosting account sales and purchases.");
	}

		/*******************************************************************************************************************/
	// END Customer/Vendor Account Functions
		/*******************************************************************************************************************/
	// START Inventory Functions
		/*******************************************************************************************************************/
	function Post_inventory() {
		global $admin;
		\core\classes\messageStack::debug_log("\n  Posting Inventory ...");
		\core\classes\messageStack::debug_log(" end Posting Inventory not requiring any action.");
		return true;
	}

	function unPost_inventory() {
		global $admin;
		\core\classes\messageStack::debug_log("\n  unPosting Inventory ...");
		// if remaining <> qty then some items have been sold; reduce qty and remaining by original qty (qty will be 0)
		// and keep record. Quantity may go negative because it was used in a COGS calculation but will be corrected when
		// new inventory has been received and the associated cost applied. If the quantity is changed, the new remaining
		// value will be calculated when the updated purchase/receive is posted.
		\core\classes\messageStack::debug_log(" end unPosting Inventory with no action.");
		return true;
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
		\core\classes\messageStack::debug_log("\n    Calculating COGS, SKU = {$item['sku']} and QTY = {$item['qty']}");
		$cogs = 0;
		// fetch the additional inventory item fields we need
		$raw_sql = "SELECT inactive, inventory_type, account_inventory_wage, account_cost_of_sales, item_cost, cost_method, quantity_on_hand, serialize FROM " . TABLE_INVENTORY . " WHERE sku = '{$item['sku']}'";
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		// catch sku's that are not in the inventory database but have been requested to post, error
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException(GL_ERROR_CALCULATING_COGS);
		$defaults = $sql->fetch(\PDO::FETCH_LAZY);
		// only calculate cogs for certain inventory_types
		if (strpos(COG_ITEM_TYPES, $defaults['inventory_type']) === false) {
			\core\classes\messageStack::debug_log(". Exiting COGS, no work to be done with this SKU.");
			return true;
		}
		if (ENABLE_MULTI_BRANCH) $defaults['quantity_on_hand'] = $this->branch_qty_on_hand($item['sku'], $defaults['quantity_on_hand']);
		// catch sku's that are serialized and the quantity is not one, error
		if ($defaults['serialize'] && abs($item['qty']) <> 1) throw new \core\classes\userException(GL_ERROR_SERIALIZE_QUANTITY);
		if ($defaults['serialize'] && !$item['serialize_number']) throw new \core\classes\userException(GL_ERROR_SERIALIZE_EMPTY);
		if ($item['qty'] > 0) { // for positive quantities, inventory received, customer credit memos, unbuild assembly
			// if insert, enter SYSTEM ENTRY COGS cost only if inv on hand is negative
			// update will never happen because the entries are removed during the unpost operation.
			// 	adjust remaining quantities for inventory history since stock was negative
			$history_array = array(
					'ref_id'     => $this->id,
					'store_id'   => $this->store_id,
					'journal_id' => 23,
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
				if ($sql->fetch(\PDO::FETCH_NUM) <> 0) throw new \core\classes\userException(GL_ERROR_SERIALIZE_COGS);
				$history_array['serialize_number'] = $item['serialize_number'];
			}
			\core\classes\messageStack::debug_log("\n      Inserting into inventory history = " . print_r($history_array, true));
			$result = db_perform(TABLE_INVENTORY_HISTORY, $history_array, 'insert');
			if ($result->AffectedRows() <> 1) throw new \core\classes\userException(TEXT_ERROR_POSTING_INVENTORY_HISTORY);
		} else { // for negative quantities, i.e. sales, negative inv adjustments, assemblies, vendor credit memos
			// if insert, calculate COGS pulling from one or more history records (inv may go negative)
			// update should never happen because COGS is backed out during the unPost inventory function
			$working_qty = -$item['qty']; // quantity needs to be positive
			$history_ids = array(); // the id's used to calculated cogs from the inventory history table
			$queue_sku = false;
			if ($defaults['serialize']) { // there should only be one record with one remaining quantity
				$raw_sql = "SELECT id, remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY."
				WHERE sku='{$item['sku']}' AND remaining > 0 AND serialize_number='{$item['serialize_number']}'";
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
				if ($sql->fetch(\PDO::FETCH_NUM) <> 1) throw new \core\classes\userException(GL_ERROR_SERIALIZE_COGS);
				$result_array = $sql->fetchAll();
			} elseif ($defaults['cost_method'] == 'a') {
				$raw_sql = "SELECT SUM(remaining) as remaining FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='{$item['sku']}' AND remaining > 0";
				if (ENABLE_MULTI_BRANCH) $raw_sql .= " AND store_id='{$this->store_id}'";
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
				$result_array = $sql->fetchAll();
				if ($result_array[0]['remaining'] < $working_qty) $queue_sku = true; // not enough of this SKU so just queue it up until stock arrives
				$avg_cost = $this->fetch_avg_cost($item['sku'], $working_qty);
			} else {
				$raw_sql = "SELECT id, remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY."
				WHERE sku='{$item['sku']}' AND remaining > 0"; // AND post_date <= '$this->post_date 23:59:59'"; // causes re-queue to owed table for negative inventory posts and rcv after sale date
				if (ENABLE_MULTI_BRANCH) $raw_sql .= " AND store_id='{$this->store_id}'";
				$raw_sql .= " ORDER BY ".($defaults['cost_method']=='l' ? 'post_date DESC, id DESC' : 'post_date, id');
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
				$result_array = $sql->fetchAll();
			}
			if ($queue_sku == false) foreach ($result_array as $key => $result) { // loops until either qty is zero and/or inventory history is exhausted
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
				\core\classes\messageStack::debug_log("\n    Adding inventory_cogs_owed, SKU = {$item['sku']}, qty = " . $working_qty);
				db_perform(TABLE_INVENTORY_COGS_OWED, $sql_data_array, 'insert');
			}
		}

		$this->sku_cogs = $cogs;
		if ($return_cogs) return $cogs; // just calculate cogs and adjust inv history
		\core\classes\messageStack::debug_log("\n    Adding COGS to array (if not zero), sku = {$item['sku']} with calculated value = $cogs");
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
		\core\classes\messageStack::debug_log(" ... Finished calculating COGS.");
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
		\core\classes\messageStack::debug_log("\n    Calculating SKU cost, SKU = $sku and QTY = $qty");
		$cogs = 0;
		$sql = $admin->DataBase->prepare("SELECT inventory_type, item_cost, cost_method, serialize FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
		$sql->execute();
		$defaults = $sql->fetch(\PDO::FETCH_LAZY);
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) return $cogs; // not in inventory, return no cost
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
		\core\classes\messageStack::debug_log(" ... Finished calculating cost: $cogs");
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
		\core\classes\messageStack::debug_log("\n      Entering fetch_avg_cost for sku: $sku and qty: $qty ... ");
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
				\core\classes\messageStack::debug_log("Exiting early with history post_date = $post_date fetch_avg_cost with cost = ".($last_qty > 0 ? $result['avg_cost'] : $last_cost));
				return $last_qty > 0 ? $result['avg_cost'] : $last_cost;
			}
			$last_cost = $result['avg_cost'];
			$last_qty = $qty; // not finished yet, get next average cost
		}
		\core\classes\messageStack::debug_log("Exiting fetch_avg_cost with cost = $last_cost");
		return $last_cost;
	}

	/**
	 * Rolling back cost of goods sold required to unpost an entry involves only re-setting the inventory history.
	 * The cogs records and costing is reversed in the unPost_chart_balances function.
	 */
	function rollback_COGS() {
		global $admin;
		\core\classes\messageStack::debug_log("\n    Rolling back COGS ... ");
		// only calculate cogs for certain inventory_types
		$sql = $admin->DataBase->prepare("Select id, qty, inventory_history_id FROM " . TABLE_INVENTORY_COGS_USAGE . " WHERE journal_main_id = " . $this->id);
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) {
			\core\classes\messageStack::debug_log(" ...Exiting COGS, no work to be done.");
			return true;
		}
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_HISTORY . " SET remaining = remaining + {$result['qty']} WHERE id = " . $result['inventory_history_id']);
		}
		\core\classes\messageStack::debug_log(" ... Finished rolling back COGS");
		return true;
	}

	function load_so_po_balance($ref_id, $id = '', $post = true) {
		global $admin;
		\core\classes\messageStack::debug_log("\n    Starting to load SO/PO balances ...");
		$item_array = array();
		if ($ref_id) throw new \core\classes\userException('Error in classes/gen_ledger, function load_so_po_balance. Bad $journal_id for this function.');
		$this->so_po_balance_array = $item_array;
		\core\classes\messageStack::debug_log(" Finished loading SO/PO balances = " . print_r($item_array, true));
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
		\core\classes\messageStack::debug_log("\n  Checking for closed entry. action = " . $action);
		if ($action == 'Post') {
			$temp = array();
			for ($i = 0; $i < count($this->journal_rows); $i++) { // fetch the list of paid invoices
				if ($this->journal_rows[$i]['so_po_item_ref_id']) {
					$temp[$this->journal_rows[$i]['so_po_item_ref_id']] = true;
				}
			}
			$invoices = array_keys($temp);
			for ($i = 0; $i < count($invoices); $i++) {
				$result = $admin->DataBase->query("SELECT sum(i.debit_amount) as debits, sum(i.credit_amount) as credits
		  		  FROM " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
					WHERE m.id = {$invoices[$i]} and i.gl_type <> 'ttl'");
				$total_billed = $admin->currencies->format($result['credits'] - $result['debits']);
				$result = $admin->DataBase->query("SELECT sum(i.debit_amount) as debits, sum(i.credit_amount) as credits
		  		  FROM " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
						WHERE i.so_po_item_ref_id = {$invoices[$i]} and i.gl_type in ('pmt', 'chk')");
				$total_paid = $admin->currencies->format($result['credits'] - $result['debits']);
				\core\classes\messageStack::debug_log("\n    total_billed = {$total_billed} and total_paid = {$total_paid}");
				if ($total_billed == $total_paid) $this->close_so_po($invoices[$i], true);
			}
		} else { // unpost - re-open the purchase/invoices affected
			for ($i = 0; $i < count($this->journal_rows); $i++) {
				if ($this->journal_rows[$i]['so_po_item_ref_id']) {
					$this->close_so_po($this->journal_rows[$i]['so_po_item_ref_id'], false);
				}
			}
		}
		return true;
	}

	/**
	 * checks if the invoice nr is valid if allowed it will create a new one when empty
	 * @throws Exception
	 */
	function validate_purchase_invoice_id() {
		global $admin;
		\core\classes\messageStack::debug_log("\n  Start validating purchase_invoice_id ... ");
		if ($this->purchase_invoice_id <> '') {	// entered a so/po/invoice value, check for dups
			$sql = "SELECT purchase_invoice_id FROM " . TABLE_JOURNAL_MAIN . " WHERE purchase_invoice_id = '{$this->purchase_invoice_id}' and journal_id = '23'";
			if ($this->id) $sql .= " and id <> " . $this->id;
			$result = $admin->DataBase->query($sql);
			if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(sprintf(TEXT_THE_YOU_ENTERED_IS_A_DUPLICATE,_PLEASE_ENTER_A_NEW_UNIQUE_VALUE_ARGS, $this->id_field_name));
			$this->journal_main_array['purchase_invoice_id'] = $this->purchase_invoice_id;
			\core\classes\messageStack::debug_log(" specified ID but no dups, returning OK. ");
		} else {	// generate a new order/invoice value
			$result = $admin->DataBase->query("SELECT next_check_num FROM " . TABLE_CURRENT_STATUS . " LIMIT 1");
			if (!$result) throw new \core\classes\userException(sprintf(GL_ERROR_CANNOT_FIND_NEXT_ID, TABLE_CURRENT_STATUS));
			$this->journal_main_array['purchase_invoice_id'] = $result['next_check_num'];
			\core\classes\messageStack::debug_log(" generated ID, returning ID# " . $this->journal_main_array['purchase_invoice_id']);
		}
		return true;
	}

	function increment_purchase_invoice_id($force = false) {
		global $admin;
		if ($this->purchase_invoice_id == '' || $force) { // increment the po/so/invoice number
			$next_id = string_increment($this->journal_main_array['purchase_invoice_id']);
			$sql = "UPDATE " . TABLE_CURRENT_STATUS . " SET next_check_num = '$next_id'";
			if (!$force) $sql .= " WHERE next_check_num = '{$this->journal_main_array['purchase_invoice_id']}'";
			$result = $admin->DataBase->exec($sql);
			if ($result->AffectedRows() <> 1) throw new \core\classes\userException(sprintf(TEXT_THERE_WAS_AN_ERROR_INCREMENTING_THE_ARGS, $this->id_field_name));
		}
		$this->purchase_invoice_id = $this->journal_main_array['purchase_invoice_id'];
		return true;
	}
	/*******************************************************************************************************************/
	//START former banking Class
	/*******************************************************************************************************************/
	function post_ordr($action) {
		global $admin, $messageStack;
		$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
		$this->journal_rows = array();	// initialize ledger row(s) array
		$this->add_item_journal_rows();	// read in line items and add to journal row array
		$this->add_discount_journal_row();
		$this->add_total_journal_row();
		// ***************************** START TRANSACTION *******************************
		$admin->DataBase->transStart();
		// *************  Pre-POST processing *************
		$this->validate_purchase_invoice_id();

		// ************* POST journal entry *************
		if ($this->id) {	// it's an edit, first unPost record, then rewrite
			$this->Post($new_post = 'edit');
			\core\classes\messageStack::add(BNK_REPOST_PAYMENT,'caution');
		} else {
			$this->Post($new_post = 'insert');
		}

		// ************* post-POST processing *************
		if ($new_post == 'insert') { // only increment if posting a new payment
			$this->increment_purchase_invoice_id(true);
		}

		$admin->DataBase->transCommit();	// finished successfully
		// ***************************** END TRANSACTION *******************************
		\core\classes\messageStack::add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_POSTED, $this->id_field_name, $this->purchase_invoice_id), 'success');
		return true;
	}

	function bulk_pay() {
		$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
		$this->journal_rows       = array();	// initialize ledger row(s) array

		$this->add_item_journal_rows();	// read in line items and add to journal row array
		$this->add_discount_journal_row();
		$this->add_total_journal_row();

		// *************  Pre-POST processing *************
		$this->validate_purchase_invoice_id();
		// ************* POST journal entry *************
		$this->Post('insert'); // all bulk pay are new posts, cannot edit
		// ************* post-POST processing *************
		for ($i = 0; $i < count($this->item_rows); $i++) {
			$total_paid = $this->item_rows[$i]['total'] + $this->item_rows[$i]['dscnt'];
			if ($total_paid == $this->item_rows[$i]['amt']) {
				$this->close_so_po($this->item_rows[$i]['id'], true);
			}
		}
		$this->increment_purchase_invoice_id(true);
		return true;
	}

	function delete_payment() {
		global $admin;
		// verify no item rows have been acted upon (accounts reconciliation)
		$result = $admin->DataBase->query("select closed from " . TABLE_JOURNAL_MAIN . " where id = " . $this->id);
		if ($result['closed'] == '1') throw new \core\classes\userException($this->error_6);
		// *************** START TRANSACTION *************************
		$admin->DataBase->transStart();
		$this->unPost('delete');
		$admin->DataBase->transCommit();
		// *************** END TRANSACTION *************************
		\core\classes\messageStack::add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_DELETED, $this->id_field_name, $this->purchase_invoice_id), 'success');
		return true;
	}

	function add_total_journal_row() {	// put total value into ledger row array
		global $admin;
		$this->journal_rows[] = array( // record for accounts receivable
				'gl_type'       => 'ttl',
				'credit_amount' => $this->total_amount,
				'description'   => $this->description . '-' . TEXT_TOTAL,
				'gl_account'    => $this->gl_acct_id,
				'post_date'     => $this->post_date,
		);
		return $amount;
	}

	function add_discount_journal_row() {	// put total value into ledger row array
		$discount = 0;
		for ($i=0; $i<count($this->item_rows); $i++) {
			if ($this->item_rows[$i]['dscnt'] <> 0) {
				$this->journal_rows[] = array(
						'so_po_item_ref_id' => $this->item_rows[$i]['id'],
						'gl_type'           => 'dsc',
						'description'       => TEXT_DISCOUNT,
						'gl_account'        => $this->gl_disc_acct_id,
						'serialize_number'  => $this->item_rows[$i]['inv'],
						'credit_amount' 	=> $this->item_rows[$i]['dscnt']);
				$discount += $this->item_rows[$i]['dscnt'];
			}
		}
		return $discount;
	}

	function add_item_journal_rows() {	// read in line items and add to journal row array
		$result = array('discount' => 0, 'total' => 0);
		for ($i=0; $i<count($this->item_rows); $i++) {
			$total_paid = $this->item_rows[$i]['dscnt'] + $this->item_rows[$i]['total'];
			$this->journal_rows[] = array(
					'so_po_item_ref_id' => $this->item_rows[$i]['id'], // link purch/rec id here for multi-id payments
					'gl_type'           => $this->item_rows[$i]['gl_type'],
					'description'       => $this->item_rows[$i]['desc'],
					'debit_amount'      => $total_paid,
					'gl_account'        => $this->item_rows[$i]['acct'],
					'serialize_number'  => $this->item_rows[$i]['inv'],
					'post_date'         => $this->post_date,
			);
			$result['total'] += $total_paid;
			$result['discount'] += $this->item_rows[$i]['dscnt'];
		}
		$this->total_amount = $result['total'] - $result['discount'];
		return;
	}

	function encrypt_payment($method, $card_key_pos = false) {
		$cc_info = array();
		$cc_info['name']    = isset($_POST[$method.'_field_0']) ? db_prepare_input($_POST[$method.'_field_0']) : '';
		$cc_info['number']  = isset($_POST[$method.'_field_1']) ? db_prepare_input($_POST[$method.'_field_1']) : '';
		$cc_info['exp_mon'] = isset($_POST[$method.'_field_2']) ? db_prepare_input($_POST[$method.'_field_2']) : '';
		$cc_info['exp_year']= isset($_POST[$method.'_field_3']) ? db_prepare_input($_POST[$method.'_field_3']) : '';
		$cc_info['cvv2']    = isset($_POST[$method.'_field_4']) ? db_prepare_input($_POST[$method.'_field_4']) : '';
		$cc_info['alt1']    = isset($_POST[$method.'_field_5']) ? db_prepare_input($_POST[$method.'_field_5']) : '';
		$cc_info['alt2']    = isset($_POST[$method.'_field_6']) ? db_prepare_input($_POST[$method.'_field_6']) : '';
		$enc_value = \core\classes\encryption::encrypt_cc($cc_info);
		$payment_array = array(
				'hint'      => $enc_value['hint'],
				'module'    => 'contacts',
				'enc_value' => $enc_value['encoded'],
				'ref_1'     => $this->bill_acct_id,
				'ref_2'     => $this->bill_address_id,
				'exp_date'  => $enc_value['exp_date'],
		);
		db_perform(TABLE_DATA_SECURITY, $payment_array, $this->payment_id ? 'update' : 'insert', 'id = '.$this->payment_id);
		return true;
	}

} // end class journal
?>