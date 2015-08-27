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
//  Path: /modules/phreebooks/classes/journal/journal_7.php
//
// Vendor / Purchase Credit Memo Journal (7)
namespace phreebooks\classes\journal;
class journal_07 extends \core\classes\journal {
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
	public $gl_type             = 'por';
	public $popup_form_type		= 'vend:cm';
	public $account_type		= 'v';
	public $gl_acct_id          = AP_DEFAULT_PURCHASE_ACCOUNT;
	public $text_contact_id		= TEXT_VENDOR_ID;
	public $text_account		= TEXT_AP_ACCOUNT;
	public $text_column_1_title	= TEXT_RECEIVED;
	public $text_column_2_title	= TEXT_RETURNED;
	public $text_order_closed	= TEXT_CREDIT_TAKEN;
	public $item_col_1_enable 	= false;				// allow/disallow entry of item columns
	public $item_col_2_enable 	= true;
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
	public $disc_gl_acct_id     = AP_DISCOUNT_PURCHASE_ACCOUNT;
	public $ship_gl_acct_id     = AP_DEF_FREIGHT_ACCT;
	public $ship_primary_name   = COMPANY_NAME;
	public $ship_contact        = AP_CONTACT_NAME;
	public $ship_address1       = COMPANY_ADDRESS1;
	public $ship_address2       = COMPANY_ADDRESS2;
	public $ship_city_town      = COMPANY_CITY_TOWN;
	public $ship_state_province = COMPANY_ZONE;
	public $ship_postal_code    = COMPANY_POSTAL_CODE;
	public $ship_telephone1     = COMPANY_TELEPHONE1;
	public $ship_email          = COMPANY_EMAIL;
	public $error_6 			= GENERAL_JOURNAL_7_ERROR_6;
	public $description 		= TEXT_VENDOR_CREDIT_MEMOS;
	public $id_field_name 		= TEXT_CREDIT_MEMO;

	function __construct( $id = 0, $verbose = true){
		if (isset($_SESSION['admin_prefs']['def_ap_acct'])) $this->gl_acct_id =  $_SESSION['admin_prefs']['def_ap_acct'];
		parent::__construct( $id, $verbose);
	}

	/*******************************************************************************************************************/
	// START re-post Functions
	/*******************************************************************************************************************/
	function check_for_re_post() {
		global $admin;
		$admin->messageStack->debug("\n  Checking for re-post records ... ");
		$repost_ids = array();
		if ($this->id) for ($i = 0; $i < count($this->journal_rows); $i++) if ($this->journal_rows[$i]['sku']) {
			// check to see if any future postings relied on this record, queue to re-post if so.
			$sql = $admin->DataBase->prepare("SELECT id FROM ".TABLE_INVENTORY_HISTORY." WHERE ref_id={$this->id} AND sku='{$this->journal_rows[$i]['sku']}'");
			$sql->execute();
			if ($sql->fetch(\PDO::FETCH_NUM) > 0) {
				$result = $sql->fetch(\PDO::FETCH_LAZY);
				$sql = $admin->DataBase->prepare("SELECT journal_main_id FROM ".TABLE_INVENTORY_COGS_USAGE." WHERE inventory_history_id=".$result['id']);
				$sql->execute();
				while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
					if ($result['journal_main_id'] <> $this->id) {
						$admin->messageStack->debug("\n    check_for_re_post is queing for cogs usage id = " . $result['journal_main_id']);
						$p_date = $admin->DataBase->query("SELECT post_date FROM ".TABLE_JOURNAL_MAIN." WHERE id=".$result['journal_main_id']);
						$idx = substr($p_date['post_date'], 0, 10).':'.str_pad($result['journal_main_id'], 8, '0', STR_PAD_LEFT);
						$repost_ids[$idx] = $result['journal_main_id'];
					}
				}
			}
		}
		// 	find if any COGS owed for items
		foreach ($this->journal_rows as $row) if ($row['sku']) {
			if ( $row['qty'] < 0 ) {
				$inv_qoh = $admin->DataBase->query("SELECT SUM(remaining) as remaining FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='{$row['sku']}' AND remaining>0");
				$working_qty = $row['qty'] + $inv_qoh['remaining'];
				$raw_sql = "SELECT id, journal_main_id, qty, post_date FROM ".TABLE_INVENTORY_COGS_OWED." WHERE sku='{$row['sku']}'";
				if (ENABLE_MULTI_BRANCH) $raw_sql .= " AND store_id = " . $this->store_id;
				$raw_sql .= " ORDER BY post_date, id";
				$sql = $admin->DataBase->prepare($raw_sql);
				$sql->execute();
				while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
					if ($working_qty >= $result['qty']) { // repost this journal entry and remove the owed record since we will repost all the negative quantities necessary
						if ($result['journal_main_id'] <> $this->id) { // prevent infinite loop
							$admin->messageStack->debug("\n    check_for_re_post is queing for cogs owed, id = {$result['journal_main_id']} to re-post.");
							$idx = substr($result['post_date'], 0, 10).':'.str_pad($result['journal_main_id'], 8, '0', STR_PAD_LEFT);
							$repost_ids[$idx] = $result['journal_main_id'];
						}
						$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_COGS_OWED . " WHERE id = " . $result['id']);
					}
					$working_qty -= $result['qty'];
					if ($working_qty <= 0) break;
				}
			}
		}
		// Check for payments or receipts made to this record that will need to be re-posted.
		if ($this->id) {
			$sql = $admin->DataBase->query("SELECT ref_id, post_date FROM ".TABLE_JOURNAL_ITEM." WHERE so_po_item_ref_id = $this->id AND gl_type in ('chk', 'pmt')");
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				$admin->messageStack->debug("\n    check_for_re_post is queing for payment id = " . $result['ref_id']);
				$idx = substr($result['post_date'], 0, 10).':'.str_pad($result['ref_id'], 8, '0', STR_PAD_LEFT);
				$repost_ids[$idx] = $result['ref_id'];
			}
		}
		$admin->messageStack->debug(" end Checking for Re-post.");
		return $repost_ids;
	}

	/*******************************************************************************************************************/
	// START Chart of Accout Functions
	/*******************************************************************************************************************/
	function Post_chart_balances() {
		global $admin;
		$admin->messageStack->debug("\n  Posting Chart Balances...");
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
				$admin->messageStack->debug("\n    Post chart balances: credit_amount = {$values['credit']}, debit_amount = {$values['debit']}, acct = $gl_acct, period = $this->period");
				$result = $admin->DataBase->exec($sql);
				if ($result->AffectedRows() <> 1) throw new \core\classes\userException(TEXT_ERROR_POSTING_CHART_OF_ACCOUNT_BALANCES_TO_ACCOUNT_ID .": " . ($gl_acct ? $gl_acct : TEXT_NOT_SPECIFIED));
			}
		}
		$admin->messageStack->debug("\n  end Posting Chart Balances.");
	}

	/**
	 * this function will un do the changes to the chart_of_account_history table
	 */
	function unPost_chart_balances() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting Chart Balances...");
		for ($i=0; $i<count($this->journal_rows); $i++) {
			// 	Update chart of accounts history
			$sql = "UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET credit_amount = credit_amount - {$this->journal_rows[$i]['credit_amount']},
			debit_amount = debit_amount - {$this->journal_rows[$i]['debit_amount']}
			WHERE account_id = '{$this->journal_rows[$i]['gl_account']}' and period = " . $this->period;
			$admin->messageStack->debug("\n    unPost chart balances: credit_amount = {$this->journal_rows[$i]['credit_amount']}, debit_amount = {$this->journal_rows[$i]['debit_amount']}, acct = {$this->journal_rows[$i]['gl_account']}, period = " . $this->period);
			$admin->DataBase->exec($sql);
			$this->affected_accounts[$this->journal_rows[$i]['gl_account']] = 1;
		}
		$admin->messageStack->debug("\n  end unPosting Chart Balances.");
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
		$admin->messageStack->debug("\n  Updating chart history for fiscal year: $fiscal_year and period: $period for accounts: ('$affected_acct_string')");
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
		$admin->messageStack->debug("\n  end Updating chart history periods. Fiscal Year: " . $fiscal_year);;
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
		if (!$this->bill_acct_id) throw new \core\classes\userException(TEXT_NO_ACCOUNT_NUMBER_PROVIDED_IN_CORE_JOURNAL_FUNCTION . ': '  . 'post_account_sales_purchases.');
		$purchase_invoice_id = $this->purchase_invoice_id ? $this->purchase_invoice_id : $this->journal_main_array['purchase_invoice_id'];
		$history_array = array(
				'ref_id'              => $this->id,
				'so_po_ref_id'        => $this->so_po_ref_id,
				'acct_id'             => $this->bill_acct_id,
				'journal_id'          => 7,
				'purchase_invoice_id' => $purchase_invoice_id,
				'amount'              => $this->total_amount,
				'post_date'           => $this->post_date,
		);
		$result = db_perform(TABLE_ACCOUNTS_HISTORY, $history_array, 'insert');
		if ($result->AffectedRows() <> 1 ) throw new \core\classes\userException(TEXT_ERROR_UPDATING_CONTACT_HISTORY);
		$admin->messageStack->debug(" end Posting account sales and purchases.");
		return true;
	}

	/**
	 * this function will delete the customer/vendor history for this journal
	 * @throws Exception
	 */

	function unPost_account_sales_purchases() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting account sales and purchases ...");
		if (!$this->bill_acct_id) throw new \core\classes\userException(TEXT_NO_ACCOUNT_NUMBER_PROVIDED_IN_CORE_JOURNAL_FUNCTION . ': ' . 'unPost_account_sales_purchases.');
		$result = $admin->DataBase->exec("DELETE FROM " . TABLE_ACCOUNTS_HISTORY . " WHERE ref_id = " . $this->id);
		if ($result->AffectedRows() != 1) throw new \core\classes\userException(TEXT_ERROR_DELETING_CUSTOMER_OR_VENDOR_ACCOUNT_HISTORY_RECORD);
		$admin->messageStack->debug(" end unPosting account sales and purchases.");
	}

	/*******************************************************************************************************************/
	// END Customer/Vendor Account Functions
	/*******************************************************************************************************************/
	// START Inventory Functions
	/*******************************************************************************************************************/
	function Post_inventory() {
		global $admin;
		$admin->messageStack->debug("\n  Posting Inventory ...");
		// adjust inventory stock status levels (also fills inv_list array)
		$item_rows_to_process = count($this->journal_rows); // NOTE: variable needs to be here because journal_rows may grow within for loop (COGS)
		for ($i = 0; $i < $item_rows_to_process; $i++) {
			if ($this->journal_rows[$i]['sku']) {
				if ($this->journal_rows[$i]['debit_amount'])  $price = $this->journal_rows[$i]['debit_amount']  / $this->journal_rows[$i]['qty'];
				if ($this->journal_rows[$i]['credit_amount']) $price = $this->journal_rows[$i]['credit_amount'] / $this->journal_rows[$i]['qty'];
				$inv_list = array(
						'id'                => $this->journal_rows[$i]['id'],
						'gl_type'           => $this->journal_rows[$i]['gl_type'],
						'so_po_item_ref_id' => $this->journal_rows[$i]['so_po_item_ref_id'],
						'sku'               => $this->journal_rows[$i]['sku'],
						'description'       => $this->journal_rows[$i]['description'],
						'serialize_number'  => $this->journal_rows[$i]['serialize_number'],
						'qty'               => $this->journal_rows[$i]['qty'],
						'price'             => $price,
						'store_id'          => $this->store_id,
						'post_date'         => $this->post_date,
				);
				// a vendor credit memo, negate the quantity and process same as customer credit memo
				$inv_list['qty'] = -$inv_list['qty'];
				$this->calculate_COGS($inv_list);
			}
		}
		// build the cogs rows
		if (sizeof($this->cogs_entry) > 0) foreach ($this->cogs_entry as $gl_acct => $values) {
			$temp_array = array(
					'ref_id'        => $this->id,
					'gl_type'       => 'cog',		// code for cost of goods charges
					'description'   => TEXT_COST_OF_GOODS_SOLD,
					'gl_account'    => $gl_acct,
					'credit_amount' => $values['credit'] ? $values['credit'] : 0,
					'debit_amount'  => $values['debit']  ? $values['debit']  : 0,
					'post_date'     => $this->post_date,
			);
			db_perform(TABLE_JOURNAL_ITEM, $temp_array, 'insert');
			$temp_array['id']     = \core\classes\PDO::lastInsertId('id');
			$this->journal_rows[] = $temp_array;
		}
		// update inventory status
		for ($i = 0; $i < count($this->journal_rows); $i++) {
			$post_qty   = $this->journal_rows[$i]['qty'];
			$item_cost  = 0;
			$full_price = 0;
			$post_qty = -$post_qty;
			$this->update_inventory_status($this->journal_rows[$i]['sku'], 'quantity_on_hand', $post_qty, $item_cost, $this->journal_rows[$i]['description'], $full_price);
		}
		$admin->messageStack->debug("\n  end Posting Inventory.");
		return true;
	}

	function unPost_inventory() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting Inventory ...");
		// if remaining <> qty then some items have been sold; reduce qty and remaining by original qty (qty will be 0)
		// and keep record. Quantity may go negative because it was used in a COGS calculation but will be corrected when
		// new inventory has been received and the associated cost applied. If the quantity is changed, the new remaining
		// value will be calculated when the updated purchase/receive is posted.
		// Delete all owed cogs entries (will be re-added during post)
		$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_COGS_OWED . " WHERE journal_main_id = " . $this->id);
		$this->rollback_COGS();
		for ($i = 0; $i < count($this->journal_rows); $i++) if ($this->journal_rows[$i]['sku']) {
			// vendor credit memo - negate qty
			$qty = -$this->journal_rows[$i]['qty'];
			$this->update_inventory_status($this->journal_rows[$i]['sku'], 'quantity_on_hand', -$qty);
			// adjust po/so inventory, if necessary, based on min of qty on ordered and qty shipped/received
			if ($this->journal_rows[$i]['so_po_item_ref_id']) {
				$item_array = $this->load_so_po_balance($this->so_po_ref_id, $this->id, false);
				$bal_before_post = $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['ordered'] - $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['processed'];
				// do not allow qty on order to go below zero.
				$adjustment = min($this->journal_rows[$i]['qty'], $bal_before_post);
				$this->update_inventory_status($this->journal_rows[$i]['sku'], 'quantity_on_order', $adjustment);
			}
		}
		// remove the inventory history records
		$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_HISTORY . " WHERE ref_id = " . $this->id);
		$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_COGS_USAGE . " WHERE journal_main_id = " . $this->id);
		// remove cost of goods sold records (will be re-calculated if re-posting)
		$this->remove_journal_COGS_entries();
		$admin->messageStack->debug("\n  end unPosting Inventory.");
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
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) {
			if (!INVENTORY_AUTO_ADD) throw new \core\classes\userException(GL_ERROR_CALCULATING_COGS);
			$id = $this->inventory_auto_add($item['sku'], $item['description'], $item['price'], 0);
			$result = $admin->DataBase->query($sql); // re-load now that item was created
		}
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
			// 	adjust remaining quantities for inventory history since stock was negative
			$history_array = array(
					'ref_id'     => $this->id,
					'store_id'   => $this->store_id,
					'journal_id' => 7,
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
			$admin->messageStack->debug("\n      Inserting into inventory history = " . print_r($history_array, true));
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
					// vendor credit memo, just need the difference in return price from average price
					$cost = $avg_cost - $item['price'];
				} else {  // FIFO, LIFO
					// vendor credit memo, just need the difference in return price from purchase price
					$cost = $result['unit_cost'] - $item['price'];
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
				// vendor credit memo, just need the difference in return price from purchase price
				$cost = $defaults['cost_method']=='a' ? ($avg_cost - $item['price']) : ($defaults['item_cost'] - $item['price']);
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

	function load_so_po_balance($ref_id, $id = '', $post = true) {
		global $admin;
		$admin->messageStack->debug("\n    Starting to load SO/PO balances ...");
		$item_array = array();
		if ($ref_id) {
			// start by retrieving the po/so item list
			$raw_sql = "SELECT id, sku, qty FROM " . TABLE_JOURNAL_ITEM . " WHERE ref_id = {$ref_id} and gl_type = 'poo'";
			$sql = $admin->DataBase->prepare($raw_sql);
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				if ($result['sku']) $item_array[$result['id']]['ordered'] = $result['qty'];
			}
			// retrieve the total number of units processed (received/shipped) less this order (may be multiple sales/purchases)
			$raw_sql = "SELECT i.so_po_item_ref_id as id, i.sku, i.qty FROM " . TABLE_JOURNAL_MAIN . " m left join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
			WHERE m.so_po_ref_id = {$ref_id} and i.gl_type = '{$this->gl_type}'";
			if (!$post && $id) $raw_sql .= " and m.id <> " . $id; // unposting so don't include current id (journal_id = 6 or 12)
			$sql = $admin->DataBase->prepare($raw_sql);
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				if ($result['sku']) $item_array[$result['id']]['processed'] += $result['qty'];
			}
		}
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
			$sql = "SELECT purchase_invoice_id FROM " . TABLE_JOURNAL_MAIN . " WHERE purchase_invoice_id = '{$this->purchase_invoice_id}' and journal_id = '7'";
			if ($this->id) $sql .= " and id <> " . $this->id;
			$result = $admin->DataBase->query($sql);
			if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(sprintf(TEXT_THE_YOU_ENTERED_IS_A_DUPLICATE,_PLEASE_ENTER_A_NEW_UNIQUE_VALUE_ARGS, $journal_types_list[7]['id_field_name']));
			$this->journal_main_array['purchase_invoice_id'] = $this->purchase_invoice_id;
			$admin->messageStack->debug(" specified ID but no dups, returning OK. ");
		} else {	// generate a new order/invoice value
			$result = $admin->DataBase->query("SELECT next_vcm_num FROM " . TABLE_CURRENT_STATUS . " LIMIT 1");
			if (!$result) throw new \core\classes\userException(sprintf(GL_ERROR_CANNOT_FIND_NEXT_ID, TABLE_CURRENT_STATUS));
			$this->journal_main_array['purchase_invoice_id'] = $result['next_vcm_num'];
			$admin->messageStack->debug(" generated ID, returning ID# " . $this->journal_main_array['purchase_invoice_id']);
		}
		return true;
	}

	function increment_purchase_invoice_id($force = false) {
		global $admin;
		if ($this->purchase_invoice_id == '' || $force) { // increment the po/so/invoice number
			$next_id = string_increment($this->journal_main_array['purchase_invoice_id']);
			$sql = "UPDATE " . TABLE_CURRENT_STATUS . " SET next_vcm_num = '$next_id'";
			if (!$force) $sql .= " WHERE next_vcm_num = '{$this->journal_main_array['purchase_invoice_id']}'";
			$result = $admin->DataBase->exec($sql);
			if ($result->AffectedRows() <> 1) throw new \core\classes\userException(sprintf(TEXT_THERE_WAS_AN_ERROR_INCREMENTING_THE_ARGS, $journal_types_list[7]['id_field_name']));
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
		$this->closed = 0; // force the inv/cm open since it will be closed by the system, if necessary
		$credit_total += $this->add_item_journal_rows(); // read in line items and add to journal row array
		$credit_total += $this->add_freight_journal_row();	// put freight into journal row array
		$credit_total += $this->add_tax_journal_rows();	// fetch tax rates for tax calculation
		$debit_total  += $this->add_discount_journal_row(); // put discount into journal row array
		$this->total_amount = $credit_total - $debit_total;
		$debit_total  += $this->add_total_journal_row();	// put total value into ledger row array
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
		// it's a new record, increment the po/so/inv to next number
		if ($this->purchase_invoice_id == '') $this->increment_purchase_invoice_id();
		$messageStack->debug("\n  committed order post purchase_invoice_id = {$this->purchase_invoice_id} and id = {$this->id}");
		$admin->DataBase->transCommit();	// finished successfully
		//echo 'committed transaction - bailing!'; exit();
		// ***************************** END TRANSACTION *******************************
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_POSTED, $journal_types_list[7]['id_field_name'], $this->purchase_invoice_id), 'success');
		return true;
	}

	function unPost($action = 'delete', $skip_balance = false) {
		global $admin;
		// verify no item rows have been acted upon (received, shipped, paid, etc.)
		// first check for main entries that refer to delete id (credit memos)
		$result = $admin->DataBase->query("select id from " . TABLE_JOURNAL_MAIN . " where so_po_ref_id = " . $this->id);
		if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException($this->error_6);
		// next check for payments that link to deleted id (payments)
		$result = $admin->DataBase->query("select id from " . TABLE_JOURNAL_ITEM . "
			where gl_type = 'pmt' and so_po_item_ref_id = " . $this->id);
		if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException($this->error_6);
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

	function add_total_journal_row() {	// put total value into ledger row array
		$this->journal_rows[] = array( // record for accounts receivable
				'gl_type'      => 'ttl',
				'debit_amount' => $this->total_amount,
				'description'  => $journal_types_list[7]['text'] . ' - ' . TEXT_TOTAL,
				'gl_account'   => $this->gl_acct_id,
				'post_date'    => $this->post_date,
		);
		return $this->total_amount;
	}

	function add_discount_journal_row() { // put discount into journal row array
		if ($this->discount <> 0) {
			$this->journal_rows[] = array(
					'qty'          => '1',
					'gl_type'      => 'dsc',		// code for discount charges
					'debit_amount' => $this->discount,
					'description'  => $journal_types_list[7]['text'] . ' - ' . TEXT_DISCOUNT,
					'gl_account'   => $this->disc_gl_acct_id,
					'taxable'      => '0',
					'post_date'    => $this->post_date,
			);
		}
		return $this->discount;
	}

	function add_freight_journal_row() {	// put freight into journal row array
		//if no line items are charged tax, do not charge tax on shipping. ADDED 2014-04-28 by Dave
		$tax_freight = false;
		foreach ($this->journal_rows as $line_item) {
			if ($line_item['taxable'] > 0 && $line_item['gl_type'] == $this->gl_type) $tax_freight = true;
		}
		$freight_tax_id = $tax_freight ? AP_ADD_SALES_TAX_TO_SHIPPING : 0;
		if ($this->freight) { // calculate freight charges
			$this->journal_rows[] = array(
					'qty'           => '1',
					'gl_type'       => 'frt',		// code for shipping/freight charges
					'credit_amount' => $this->freight,
					'description'   => $journal_types_list[7]['text'] . ' - ' . TEXT_SHIPPING,
					'gl_account'    => $this->ship_gl_acct_id,
					'taxable'       => $freight_tax_id,
					'post_date'     => $this->post_date,
			);
		}
		return $this->freight;
	}

	function add_item_journal_rows() {	// read in line items and add to journal row array
		$total = 0;
		for ($i=0; $i<count($this->item_rows); $i++) {
			if ($this->item_rows[$i]['pstd']) { // make sure the quantity line is set and not zero
				$this->journal_rows[] = array(
						'id'                      => $this->item_rows[$i]['id'],	// retain the db id (used for updates)
						'item_cnt'                => $this->item_rows[$i]['item_cnt'],
						'so_po_item_ref_id'       => $this->item_rows[$i]['so_po_item_ref_id'],	// item reference id for so/po line items
						'gl_type'                 => $this->gl_type,
						'sku'                     => $this->item_rows[$i]['sku'],
						'qty'                     => $this->item_rows[$i]['pstd'],
						'description'             => $this->item_rows[$i]['desc'],
						'credit_amount' 		  => $this->item_rows[$i]['total'],
						'full_price'              => $this->item_rows[$i]['full'],
						'gl_account'              => $this->item_rows[$i]['acct'],
						'taxable'                 => $this->item_rows[$i]['tax'],
						'serialize_number'        => $this->item_rows[$i]['serial'],
						'project_id'              => $this->item_rows[$i]['proj'],
						'purch_package_quantity'  => $this->item_rows[$i]['purch_package_quantity'],
						'post_date'               => $this->post_date,
						'date_1'                  => $this->item_rows[$i]['date_1'] ? $this->item_rows[$i]['date_1'] : $this->post_date,
				);
				$total += $this->item_rows[$i]['total'];
			}
		}
		return $total;
	}

	function add_tax_journal_rows() {
		global $admin;
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
							$auth_array[$auth] += ($tax_auths[$auth]['tax_rate'] / 100) * $line_total;
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
					'qty'           => '1',
					'gl_type'       => 'tax',		// code for tax entry
					'credit_amount' => $amount,
					'description'   => $tax_auths[$auth]['description_short'],
					'gl_account'    => $tax_auths[$auth]['account_id'],
					'post_date'     => $this->post_date,
			);
			$total += $amount;
		}
		$this->sales_tax = $total;
		return $total;
	}
} // end class journal
?>