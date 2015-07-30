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
//  Path: /modules/phreepos/classes/journal/journal_21.php
//
// POP Journal (21)
// Inventory Direct Purchase Journal (POP)
namespace phreepos\classes\journal;
class journal_21 extends \core\classes\journal {
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
	public $save_payment        = false;
    
    public $journal_id          = 21;
	public $closed 				= '0';
    public $gl_type             = GL_TYPE;
	public $gl_acct_id          = DEF_GL_ACCT;
    public $currencies_code     = DEFAULT_CURRENCY;
    public $currencies_value    = '1.0';
    public $gl_disc_acct_id     = AR_DISCOUNT_SALES_ACCOUNT;
    public $bill_acct_id		= '';
    public $bill_address_id		= '';
    public $bill_add_update		= false;
    public $bill_primary_name   = TEXT_NAME_OR_COMPANY;
    public $bill_contact        = TEXT_ATTENTION;
    public $bill_address1       = TEXT_ADDRESS1;
    public $bill_address2       = TEXT_ADDRESS2;
    public $bill_city_town      = TEXT_CITY_TOWN;
    public $bill_state_province = TEXT_STATE_PROVINCE;
    public $bill_postal_code    = TEXT_POSTAL_CODE;
    public $bill_country_code   = COMPANY_COUNTRY;
    public $bill_telephone1		= '';
    public $bill_email			= '';
    public $journal_rows        = array();	// initialize ledger row(s) array
	public $opendrawer			= false;
	public $printed				= false;
	public $post_date			= '';
	public $store_id			= 0;
	public $till_id				= 0;
	public $rep_id				= 0;
	public $subtotal			= 0;
	public $disc_percent		= 0;
	public $discount			= 0;
	public $sales_tax			= 0;
	public $rounded_of			= 0;
	public $total_amount		= 0;
	public $pmt_recvd			= 0;
	public $bal_due				= 0;
	public $shipper_code		= '';
	public $so_po_ref_id		= '';
	// shipping defaults
	public $ship_short_name     = '';
	public $ship_add_update     = 0;
	public $ship_acct_id        = 0;
	public $ship_address_id     = 0;
	public $ship_country_code   = COMPANY_COUNTRY;
	public $shipper_code        = '';
	public $drop_ship           = 0;
	public $freight             = 0;

    function __construct( $id = 0, $verbose = true) {
    	global $admin;
		$this->error_6 = GENERAL_JOURNAL_21_ERROR_6;
    	$result = $admin->DataBase->query("select next_check_num from " . TABLE_CURRENT_STATUS);
    	$this->purchase_invoice_id = $result['next_check_num'];
    	$this->gl_acct_id          = $_SESSION['admin_prefs']['def_cash_acct'] ? $_SESSION['admin_prefs']['def_cash_acct'] : AP_PURCHASE_INVOICE_ACCOUNT;
		parent::__construct( $id, $verbose);
	}

	/*******************************************************************************************************************/
	// START re-post Functions
	/*******************************************************************************************************************/
	function check_for_re_post() {
		global $admin;
		$admin->messageStack->debug("\n  Checking for re-post records ... ");
		$repost_ids = array();
		$gl_type 	= NULL;
		switch ($this->journal_id) {
			case  6: // Purchase/Receive Journal
				$skus = array();
				foreach ($this->journal_rows as $row) if ($row['sku'] <> '') $skus[] = $row['sku'];
				if (sizeof($skus) > 0) {
					$sql = $admin->DataBase->prepare("SELECT sku FROM ".TABLE_INVENTORY." WHERE sku IN ('".implode("', '", $skus)."') AND cost_method='a'");
					$sql->execute();
					$askus = $sql->fetchAll();
					if (sizeof($askus) > 0) {
						$admin->messageStack->debug("\n    Finding re-post ids for average sku list = ".print_r($askus, true)." \n and post_date after $this->post_date");
						$sql = $admin->DataBase->prepare("SELECT ref_id, post_date FROM ".TABLE_JOURNAL_ITEM." WHERE sku IN ('".implode("', '", $askus)."') AND post_date > '$this->post_date'");
						$sql->execute();
						while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
							$admin->messageStack->debug("\n    check_for_re_post is queing for average cost record id = ".$result['ref_id']);
							$idx = substr($result['post_date'], 0, 10).':'.str_pad($result['ref_id'], 8, '0', STR_PAD_LEFT);
							$repost_ids[$idx] = $result['ref_id'];
						}
					}
				}
				// continue with more tests
			case  7: // Purchase Credit Memo Journal
			case 12: // Sales/Invoice Journal
			case 13: // Sales Credit Memo Journal
			case 14: // Inventory Assembly Journal
			case 16: // Inventory Adjustment Journal
			case 19: // POS Journal
			case 21: // Inventory Direct Purchase Journal
				if ($this->id) for ($i = 0; $i < count($this->journal_rows); $i++) if ($this->journal_rows[$i]['sku']) {
					// check to see if any future postings relied on this record, queue to re-post if so.
					$sql = $admin->DataBase->prepare("SELECT id FROM ".TABLE_INVENTORY_HISTORY." WHERE ref_id={$this->id} AND sku='{$this->journal_rows[$i]['sku']}'");
					$sql->execute();
					if ($sql->rowCount() > 0) {
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
					if (($row['qty']>0 && in_array($this->journal_id, array(6, 13, 14, 16))) || ($row['qty'] < 0 && in_array($this->journal_id, array(7, 12)))) {
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
				break;
			case  2: // General Journal
			case  3: // Purchase Quote Journal
			case  4: // Purchase Order Journal
			case  9: // Sales Quote Journal
			case 10: // Sales Order Journal
			case 18: // Cash Receipts Journal
			case 20: // Cash Distribution Journal
			default: $admin->messageStack->debug(" end check for Re-post with no action.");
		}
		return $repost_ids;
	}

	/*******************************************************************************************************************/
	// START Chart of Accout Functions
	/*******************************************************************************************************************/
	function Post_chart_balances() {
		global $admin;
		$admin->messageStack->debug("\n  Posting Chart Balances...");
		switch ($this->journal_id) {
			case  2: // General Journal
			case  6: // Purchase/Receive Journal
			case  7: // Purchase Credit Memo Journal
			case 12: // Sales/Invoice Journal
			case 13: // Sales Credit Memo Journal
			case 14: // Inventory Assembly Journal
			case 16: // Inventory Adjustment Journal
			case 18: // Cash Receipts Journal
			case 19: // POS Journal
			case 20: // Cash Distribution Journal
			case 21: // Inventory Direct Purchase Journal
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
				break;
			case  3: // Purchase Quote Journal
			case  4: // Purchase Order Journal
			case  9: // Sales Quote Journal
			case 10: // Sales Order Journal
			default: $admin->messageStack->debug(" end Posting Chart Balances with no action.");
		}
		return true;
	}

	/**
	 * this function will un do the changes to the chart_of_account_history table
	 */
	function unPost_chart_balances() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting Chart Balances...");
		switch ($this->journal_id) {
			case  2: // General Journal
			case  6: // Purchase/Receive Journal
			case  7: // Purchase Credit Memo Journal
			case 12: // Sales/Invoice Journal
			case 13: // Sales Credit Memo Journal
			case 14: // Inventory Assembly Journal
			case 16: // Inventory Adjustment Journal
			case 18: // Cash Receipts Journal
			case 19: // POS Journal
			case 20: // Cash Distribution Journal
			case 21: // Inventory Direct Purchase Journal
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
				break;
			case  3: // Purchase Quote Journal
			case  4: // Purchase Order Journal
			case  9: // Sales Quote Journal
			case 10: // Sales Order Journal
			default:
				$admin->messageStack->debug(" end unPosting Chart Balances with no action.");
		}
	}

	// *********  chart of account support functions  **********
	function update_chart_history_periods($period = CURRENT_ACCOUNTING_PERIOD) {
		global $admin;
		switch ($this->journal_id) {
			case  3: // Purchase Quote
			case  4: // Purchase Order
			case  9: // Sales Quote
			case 10: // Sales Order
				$admin->messageStack->debug("\n    Returning from Update Chart History Periods with no action required.");
				return true;
			default:
		}
		// first find out the last period with data in the system from the current_status table
		$sql = $admin->DataBase->query("SELECT fiscal_year FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE period = " . $period);
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException(GL_ERROR_BAD_ACCT_PERIOD); //@todo gebruiken ipv rowCount
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
				WHERE period = " . ($i + 1) . " and account_id = '{$result->fields['account_id']}'";
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
		switch ($this->journal_id) {
			case 19:
			case 21: if (!$this->bill_acct_id) return true; // no sales history in POS if no bill account id, else continue
			case  6:
			case  7:
			case 12:
			case 13:
			case 18:
			case 20:
				if (!$this->bill_acct_id) throw new \core\classes\userException(TEXT_NO_ACCOUNT_NUMBER_PROVIDED_IN_CORE_JOURNAL_FUNCTION . ': '  . 'post_account_sales_purchases.');
				$purchase_invoice_id = $this->purchase_invoice_id ? $this->purchase_invoice_id : $this->journal_main_array['purchase_invoice_id'];
				$history_array = array(
						'ref_id'              => $this->id,
						'so_po_ref_id'        => $this->so_po_ref_id,
						'acct_id'             => $this->bill_acct_id,
						'journal_id'          => $this->journal_id,
						'purchase_invoice_id' => $purchase_invoice_id,
						'amount'              => $this->total_amount,
						'post_date'           => $this->post_date,
				);
				$result = db_perform(TABLE_ACCOUNTS_HISTORY, $history_array, 'insert');
				if ($result->AffectedRows() <> 1 ) throw new \core\classes\userException(TEXT_ERROR_UPDATING_CONTACT_HISTORY);
				$admin->messageStack->debug(" end Posting account sales and purchases.");
				break;
			case  2:
			case  3:
			case  4:
			case  9:
			case 10:
			case 14:
			case 16:
			default: // nothing required to do
				$admin->messageStack->debug(" end Posting account sales and purchases with no action.");
		}
		return true;
	}

	/**
	 * this function will delete the customer/vendor history for this journal
	 * @throws Exception
	 */

	function unPost_account_sales_purchases() {
		global $admin;
		$admin->messageStack->debug("\n  unPosting account sales and purchases ...");
		switch ($this->journal_id) {
			case 19:
			case 21: if (!$this->bill_acct_id) return true; // no sales history in POS if no bill account id, else continue
			case  6:
			case  7:
			case 12:
			case 13:
			case 18:
			case 20:
				if (!$this->bill_acct_id) throw new \core\classes\userException(TEXT_NO_ACCOUNT_NUMBER_PROVIDED_IN_CORE_JOURNAL_FUNCTION . ': ' . 'unPost_account_sales_purchases.');
				$result = $admin->DataBase->exec("DELETE FROM " . TABLE_ACCOUNTS_HISTORY . " WHERE ref_id = " . $this->id);
				if ($result->AffectedRows() != 1) throw new \core\classes\userException(TEXT_ERROR_DELETING_CUSTOMER_OR_VENDOR_ACCOUNT_HISTORY_RECORD);
				$admin->messageStack->debug(" end unPosting account sales and purchases.");
				break;
			case  2:
			case  3:
			case  4:
			case  9:
			case 10:
			case 14:
			case 16:
			default: // nothing required to do
				$admin->messageStack->debug(" end unPosting account sales and purchases with no action.");
		}

	}

	/*******************************************************************************************************************/
	// END Customer/Vendor Account Functions
	/*******************************************************************************************************************/
	// START Inventory Functions
	/*******************************************************************************************************************/
	function Post_inventory() {
		global $admin;
		$admin->messageStack->debug("\n  Posting Inventory ...");
		switch ($this->journal_id) { // Pre-posting particulars that are journal dependent
			case  4:
				$str_field       = 'quantity_on_order';
				$item_array      = $this->load_so_po_balance($this->id);
				break;
			case  6:
				$str_field       = 'quantity_on_hand';
				$so_po_str_field = 'quantity_on_order';
				$item_array      = $this->load_so_po_balance($this->so_po_ref_id, $this->id);
				break;
			case 10:
				$str_field       = 'quantity_on_sales_order';
				$item_array      = $this->load_so_po_balance($this->id);
				break;
			case 12:
			case 19:
				$str_field       = 'quantity_on_hand';
				$so_po_str_field = 'quantity_on_sales_order';
				$item_array      = $this->load_so_po_balance($this->so_po_ref_id, $this->id);
				break;
			case  7:
			case 13:
			case 14:
			case 16:
			case 21:
				$str_field       = 'quantity_on_hand';
				break;
			case  2:
			case  3:
			case  9:
			case 18:
			case 20:
			default:
				$admin->messageStack->debug(" end Posting Inventory not requiring any action.");
				return true;
		}
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
				switch ($this->journal_id) {
					case 4:
					case 10:
						$adjustment = ($item_array[$inv_list['id']]['processed'] > 0) ? $item_array[$inv_list['id']]['processed'] : 0;
						if ($this->closed) $adjustment = $this->journal_rows[$i]['qty'];
						$item_cost  = ($this->journal_id ==  4) ? $inv_list['price'] : 0;
						$full_price = ($this->journal_id == 10) ? $inv_list['price'] : 0;
						$this->update_inventory_status($inv_list['sku'], $str_field, -$adjustment, $item_cost, $inv_list['description'], $full_price);
						break;
					case 12: // a sale so make quantity negative (pulling from inventory) and continue
					case 19:
						$inv_list['qty'] = -$inv_list['qty'];
					case  6:
					case 21:
						$this->calculate_COGS($inv_list);
						if ($inv_list['so_po_item_ref_id']) { // check for reference to po/so to adjust qty on order/sales order
							// do not allow qty on order to go below zero.
							$bal_before_post = $item_array[$inv_list['so_po_item_ref_id']]['ordered'] - $item_array[$inv_list['so_po_item_ref_id']]['processed'] + $this->journal_rows[$i]['qty'];
							$adjustment = -(min($this->journal_rows[$i]['qty'], $bal_before_post));
							$this->update_inventory_status($inv_list['sku'], $so_po_str_field, $adjustment);
						}
						break;
					case 14:
						$assy_cost = $this->calculate_assembly_list($inv_list); // for assembly parts list
						break;
					case  7: // a vendor credit memo, negate the quantity and process same as customer credit memo
						$inv_list['qty'] = -$inv_list['qty'];
					case 13: // a customer credit memo, qty stays positive
					case 16:
						$this->calculate_COGS($inv_list);
						break;
					default: // nothing
				}
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
			switch ($this->journal_id) {
				case  4:
					if (ENABLE_AUTO_ITEM_COST == 'PO' && $this->journal_rows[$i]['qty']) $item_cost = $this->journal_rows[$i]['debit_amount'] / $this->journal_rows[$i]['qty'];
					break;
				case  6:
				case 21:
					if (ENABLE_AUTO_ITEM_COST == 'PR' && $this->journal_rows[$i]['qty']) $item_cost = $this->journal_rows[$i]['debit_amount'] / $this->journal_rows[$i]['qty'];
					break;
				case 12:
					if ($this->journal_rows[$i]['qty']) $full_price = $this->journal_rows[$i]['credit_amount'] / $this->journal_rows[$i]['qty'];
				case  7:
				case 19:
					$post_qty = -$post_qty;
					break;
				case 14:
					if ($i == 0 && $this->journal_rows[$i]['qty'] > 0) { // only for the item being assembled
						$item_cost = $this->journal_rows[$i]['debit_amount'] / $this->journal_rows[$i]['qty'];
					}
					break;
				default:
			}
			$this->update_inventory_status($this->journal_rows[$i]['sku'], $str_field, $post_qty, $item_cost, $this->journal_rows[$i]['description'], $full_price);
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
		switch ($this->journal_id) {  // journals that don't affect inventory, return now
			case  2:
			case  3:
			case  9:
			case 18:
			case 20:
				$admin->messageStack->debug(" end unPosting Inventory with no action.");
				return true;
			case  6:
			case  7:
			case 12:
			case 13:
			case 14:
			case 16:
			case 19:
			case 21:
				// Delete all owed cogs entries (will be re-added during post)
				$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_COGS_OWED . " WHERE journal_main_id = " . $this->id);
				$this->rollback_COGS();
				break;
			default:  // continue to unPost inventory
		}
		// prepare some variables
		switch ($this->journal_id) {
			case  4:
			case  6:
			case 21:
			case  7:
				$db_field = 'quantity_on_order';
				break;
			default:
				$db_field = 'quantity_on_sales_order';
		}
		for ($i = 0; $i < count($this->journal_rows); $i++) if ($this->journal_rows[$i]['sku']) {
			switch ($this->journal_id) {
				case  4:
				case 10:
					$item_array = $this->load_so_po_balance($this->id, '', false);
					$bal_before_post = $item_array[$this->journal_rows[$i]['id']]['ordered'] - $item_array[$this->journal_rows[$i]['id']]['processed'];
					if (!$this->closed && $bal_before_post > 0) $this->update_inventory_status($this->journal_rows[$i]['sku'], $db_field, -$bal_before_post);
					break;
				case  6:
				case  7:
				case 12:
				case 13:
				case 14:
				case 16:
				case 19:
				case 21:
					switch ($this->journal_id) {
						case  7: // vendor credit memo - negate qty
						case 12: // customer sales - negate quantity
						case 19: // customer POS - negate quantity
							$qty = -$this->journal_rows[$i]['qty'];
							break;
						default:
							$qty = $this->journal_rows[$i]['qty'];
					}
					$this->update_inventory_status($this->journal_rows[$i]['sku'], 'quantity_on_hand', -$qty);
					// adjust po/so inventory, if necessary, based on min of qty on ordered and qty shipped/received
					if ($this->journal_rows[$i]['so_po_item_ref_id']) {
						$item_array = $this->load_so_po_balance($this->so_po_ref_id, $this->id, false);
						$bal_before_post = $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['ordered'] - $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['processed'];
						// do not allow qty on order to go below zero.
						$adjustment = min($this->journal_rows[$i]['qty'], $bal_before_post);
						$this->update_inventory_status($this->journal_rows[$i]['sku'], $db_field, $adjustment);
					}
					break;
				default:
			}
		}
		// remove the inventory history records
		$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_HISTORY . " WHERE ref_id = " . $this->id);
		$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_COGS_USAGE . " WHERE journal_main_id = " . $this->id);
		// remove cost of goods sold records (will be re-calculated if re-posting)
		$this->remove_journal_COGS_entries();
		$admin->messageStack->debug("\n  end unPosting Inventory.");
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
		$admin->messageStack->debug("\n    Calculating COGS, SKU = {$item['sku']} and QTY = {$item['qty']}");
		$cogs = 0;
		// fetch the additional inventory item fields we need
		$raw_sql = "SELECT inactive, inventory_type, account_inventory_wage, account_cost_of_sales, item_cost, cost_method, quantity_on_hand, serialize FROM " . TABLE_INVENTORY . " WHERE sku = '{$item['sku']}'";
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		// catch sku's that are not in the inventory database but have been requested to post, error
		if ($sql->rowCount() == 0) {
			if (!INVENTORY_AUTO_ADD) throw new \core\classes\userException(GL_ERROR_CALCULATING_COGS);
			$item_cost  = 0;
			$full_price = 0;
			switch ($this->journal_id) {
				case  6:
				case  7:
					$item_cost  = $item['price']; break;
				case 12:
				case 13:
					$full_price = $item['price']; break;
				default:
					throw new \core\classes\userException(GL_ERROR_CALCULATING_COGS);
			}
			$id = $this->inventory_auto_add($item['sku'], $item['description'], $item_cost, $full_price);
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
			switch ($this->journal_id) {
				case  6:
					if ($defaults['cost_method'] == 'a') $item['avg_cost'] = $this->calculate_avg_cost($item['sku'], $item['price'], $item['qty']);
					break;
				case 12: // for negative sales/invoices and customer credit memos the price needs to be the last unit_cost,
				case 13: // not the invoice price (customers price)
					$item['price'] = $this->calculateCost($item['sku'], 1, $item['serialize_number']);
					$cogs = -($item['qty'] * $item['price']);
					break;
				case 14: // for un-build assemblies cogs will not be zero
					$cogs = -($item['qty'] * $this->calculateCost($item['sku'], 1, $item['serialize_number'])); // use negative last cost (unbuild assy)
					break;
				default: // for all other journals, use the cost as entered to calculate added inventory
			}
			// 	adjust remaining quantities for inventory history since stock was negative
			$history_array = array(
					'ref_id'     => $this->id,
					'store_id'   => $this->store_id,
					'journal_id' => $this->journal_id,
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
					switch ($this->journal_id) {
						case  7: // vendor credit memo, just need the difference in return price from average price
						case 14: // assembly, just need the difference in assemble price from piece price
							$cost = $avg_cost - $item['price'];
							break;
						default:
							$cost = $avg_cost;
					}
				} else {  // FIFO, LIFO
					switch ($this->journal_id) {
						case  7: // vendor credit memo, just need the difference in return price from purchase price
						case 14: // assembly, just need the difference in assemble price from piece price
							$cost = $result['unit_cost'] - $item['price'];
							break;
						default:
							$cost = $result['unit_cost']; // for the specific history record
					}
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
				$sql = "UPDATE ".TABLE_INVENTORY_HISTORY." SET remaining = remaining - $cost_qty WHERE id=".$result->fields['id'];
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
				switch ($this->journal_id) {
					case  7: // vendor credit memo, just need the difference in return price from purchase price
					case 14: // assembly, just need the difference in assemble price from piece price
						$cost = $defaults['cost_method']=='a' ? ($avg_cost - $item['price']) : ($defaults['item_cost'] - $item['price']);
						break;
					default:
						$cost = $defaults['cost_method']=='a' ? $avg_cost : $defaults['item_cost']; // for the specific history record
				}
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
		$raw_sql .= " ORDER BY id" . ($defaults->fields['cost_method'] == 'l' ? ' DESC' : '');
		$sql = $admin->DataBase->prepare($raw_sql);
		$sql->execute();
		$working_qty = abs($qty);
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) { // loops until either qty is zero and/or inventory history is exhausted
			if ($working_qty <= $result->fields['remaining']) { // this history record has enough to fill request
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
		if ($ref_id) {
			switch ($this->journal_id) {
				case  4:
				case  6:
				case  7:
				case 21: $gl_type = 'poo'; $proc_type = 'por'; break;
				case 10:
				case 12:
				case 13:
				case 19: $gl_type = 'soo'; $proc_type = 'sos'; break;
				default: throw new \core\classes\userException('Error in classes/gen_ledger, function load_so_po_balance. Bad $journal_id for this function.');
			}
			// start by retrieving the po/so item list
			$raw_sql = "SELECT id, sku, qty FROM " . TABLE_JOURNAL_ITEM . " WHERE ref_id = {$ref_id} and gl_type = '{$gl_type}'";
			$sql = $admin->DataBase->prepare($raw_sql);
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				if ($result['sku']) $item_array[$result['id']]['ordered'] = $result['qty'];
			}
			// retrieve the total number of units processed (received/shipped) less this order (may be multiple sales/purchases)
			$raw_sql = "SELECT i.so_po_item_ref_id as id, i.sku, i.qty FROM " . TABLE_JOURNAL_MAIN . " m left join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
			WHERE m.so_po_ref_id = {$ref_id} and i.gl_type = '{$proc_type}'";
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
		switch ($this->journal_id) {
			case  4: $gl_type = 'poo';
			// continue like sales order
			case 10: if (!$gl_type) $gl_type = 'soo';
			// determine if shipped/received items are still outstanding
			$ordr_diff = false;
			if (is_array($this->so_po_balance_array)) {
				foreach($this->so_po_balance_array as $counts) {
					if ($counts['ordered'] > $counts['processed']) $ordr_diff = true;
				}
			}
			// determine if all items quantities have been entered as zero
			$item_rows_all_zero = true;
			for ($i = 0; $i < count($this->journal_rows); $i++) {
				if ($this->journal_rows[$i]['qty'] && $this->journal_rows[$i]['gl_type'] == $gl_type) $item_rows_all_zero = false; // at least one qty is non-zero
			}
			// also close if the 'Close' box was checked
			if (!$ordr_diff || $item_rows_all_zero || $this->closed) $this->close_so_po($this->id, true);
			break;
			case  6:
			case 12:
			case 19:
			case 21:
				if ($this->so_po_ref_id) {	// make sure there is a reference po/so to check
					$ordr_diff = false;
					if (is_array($this->so_po_balance_array)) {
						foreach($this->so_po_balance_array as $key => $counts) {
							if ($counts['ordered'] > $counts['processed']) $ordr_diff = true;
						}
					} else {
						$ordr_diff = true; // force open since balance array is empty
					}
					if ($ordr_diff) { // open it, there are still items to be processed
						$this->close_so_po($this->so_po_ref_id, false);
					} else { // close the order
						$this->close_so_po($this->so_po_ref_id, true);
					}
				}
				// close if the invoice/inv receipt total is zero
				if (round($this->total_amount, $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']) == 0) {
					$this->close_so_po($this->id, true);
				}
				break;
			case 18: //$gl_type = 'pmt';
				// continue like payment
			case 20: //if (!$gl_type) $gl_type = 'chk';
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
						$admin->messageStack->debug("\n    total_billed = {$total_billed} and total_paid = {$total_paid}");
						if ($total_billed == $total_paid) $this->close_so_po($invoices[$i], true);
					}
				} else { // unpost - re-open the purchase/invoices affected
					for ($i = 0; $i < count($this->journal_rows); $i++) {
						if ($this->journal_rows[$i]['so_po_item_ref_id']) {
							$this->close_so_po($this->journal_rows[$i]['so_po_item_ref_id'], false);
						}
					}
				}
				break;
			case  2:
			case  3:
			case  7:
			case  9:
			case 13:
			case 14:
			case 16:
			default:
		}
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
			switch ($this->journal_id) { // allow for duplicates in the following journals
				case 18:
				case 19: // for the deposit part of POS
					$admin->messageStack->debug(" specified ID and dups allowed, returning OK.");
					return true; // allow for duplicate deposit ticket ID's
				default: // continue
			}
			$sql = "SELECT purchase_invoice_id FROM " . TABLE_JOURNAL_MAIN . " WHERE purchase_invoice_id = '{$this->purchase_invoice_id}' and journal_id = '{$this->journal_id}'";
			if ($this->id) $sql .= " and id <> " . $this->id;
			$result = $admin->DataBase->query($sql);
			if ($result->rowCount() > 0) throw new \core\classes\userException(sprintf(TEXT_THE_YOU_ENTERED_IS_A_DUPLICATE,_PLEASE_ENTER_A_NEW_UNIQUE_VALUE_ARGS, $journal_types_list[ $this->journal_id]['id_field_name']));
			$this->journal_main_array['purchase_invoice_id'] = $this->purchase_invoice_id;
			$admin->messageStack->debug(" specified ID but no dups, returning OK. ");
		} else {	// generate a new order/invoice value
			switch ($this->journal_id) { // select the field to fetch the next number
				case  3: $str_field = 'next_ap_quote_num'; break;
				case  4: $str_field = 'next_po_num';       break;
				case  6: $str_field = false;               break; // not applicable
				case  7: $str_field = 'next_vcm_num';      break;
				case  9: $str_field = 'next_ar_quote_num'; break;
				case 10: $str_field = 'next_so_num';       break;
				case 12:
				case 19: $str_field = 'next_inv_num';      break;
				case 13: $str_field = 'next_cm_num';       break;
				case 18: $str_field = 'next_deposit_num';  break;
				case 20:
				case 21: $str_field = 'next_check_num';    break;
			}
			if ($str_field) {
				$result = $admin->DataBase->query("SELECT {$str_field} FROM " . TABLE_CURRENT_STATUS . " LIMIT 1");
				if (!$result) throw new \core\classes\userException(sprintf(GL_ERROR_CANNOT_FIND_NEXT_ID, TABLE_CURRENT_STATUS));
				$this->journal_main_array['purchase_invoice_id'] = $result[$str_field];
			} else {
				$this->journal_main_array['purchase_invoice_id'] = '';
			}
			$admin->messageStack->debug(" generated ID, returning ID# " . $this->journal_main_array['purchase_invoice_id']);
		}
		return true;
	}

	function increment_purchase_invoice_id($force = false) {
		global $admin;
		if ($this->purchase_invoice_id == '' || $force) { // increment the po/so/invoice number
			switch ($this->journal_id) { // select the field to increment the number
				case  3: $str_field = 'next_ap_quote_num'; break;
				case  4: $str_field = 'next_po_num';       break;
				case  6: $str_field = false;               break; // not applicable
				case  7: $str_field = 'next_vcm_num';      break;
				case  9: $str_field = 'next_ar_quote_num'; break;
				case 10: $str_field = 'next_so_num';       break;
				case 12:
				case 19: $str_field = 'next_inv_num';      break;
				case 13: $str_field = 'next_cm_num';       break;
				case 18: $str_field = 'next_deposit_num';  break;
				case 20:
				case 21: $str_field = 'next_check_num';    break;
			}
			if ($str_field) {
				$next_id = string_increment($this->journal_main_array['purchase_invoice_id']);
				$sql = "UPDATE " . TABLE_CURRENT_STATUS . " SET $str_field = '$next_id'";
				if (!$force) $sql .= " WHERE $str_field = '{$this->journal_main_array['purchase_invoice_id']}'";
				$result = $admin->DataBase->exec($sql);
				if ($result->AffectedRows() <> 1) throw new \core\classes\userException(sprintf(TEXT_THERE_WAS_AN_ERROR_INCREMENTING_THE_ARGS, $journal_types_list[ $this->journal_id]['id_field_name']));
			}
		}
		$this->purchase_invoice_id = $this->journal_main_array['purchase_invoice_id'];
		return true;
	}
	/*******************************************************************************************************************/
	//START former Orders Class
	/*******************************************************************************************************************/

	function post_ordr($action) {
		global $admin, $messageStack;
		$debit_total  = 0;
		$credit_total = 0;
	    $debit_total  += $this->add_item_journal_rows(); // read in line items and add to journal row array
	    $debit_total  += $this->add_tax_journal_rows();  // fetch tax rates for tax calculation
		$credit_total += $this->add_discount_journal_row(); // put discount into journal row array
		$debit_total  += $this->add_rounding_journal_rows($credit_total - $debit_total);	// fetch rounding of
	    //$this->adjust_total($debit_total - $credit_total);
	    $credit_total += $this->add_total_journal_row();    // put total value into ledger row array
		$this->journal_main_array = $this->build_journal_main_array(); // build ledger main record

		// ***************************** START TRANSACTION *******************************
		$messageStack->debug("\n  started order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id);
		$admin->DataBase->transStart();
		// *************  Pre-POST processing *************
		// add/update address book
		if ($this->bill_add_update) { // billing address
			$this->bill_acct_id = $this->add_account($this->account_type . 'b', $this->bill_acct_id, $this->bill_address_id);
			if (!$this->bill_acct_id) throw new \core\classes\userException('no customer was selected');
		}
		// ************* POST journal entry *************
		$this->validate_purchase_invoice_id();
		$this->Post($this->id ? 'edit' : 'insert',true);
		// ************* post-POST processing *************
		$this->increment_purchase_invoice_id();
		$messageStack->debug("\n  committed order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id . "\n\n");
		$admin->DataBase->transCommit();
		// ***************************** END TRANSACTION *******************************
		$messageStack->add('Successfully posted ' . TEXT_POINT_OF_SALE . ' Ref # ' . $this->purchase_invoice_id, 'success');
		return true;
	}

  	function refund_ordr() {
    	global $admin, $messageStack;
    	// *************** START TRANSACTION *************************
    	$admin->DataBase->transStart();
    	$this->unPost('delete');
   		$admin->DataBase->transCommit();
    	// *************** END TRANSACTION *************************
    	return true;
  	}

  function add_total_journal_row() {
	global $payment_modules;
	  $total = 0;
	  for ($i = 0; $i < count($this->pmt_rows); $i++) {
		if ($this->pmt_rows[$i]['pmt']) { // make sure the payment line is set and not zero
		  $desc = TEXT_POINT_OF_SALE . '-' . TEXT_TOTAL;
		  $method     = $this->pmt_rows[$i]['meth'];
		  if ($method) {
		    $pay_meth = "\payment\methods\\$method\\$method";
		    $$method    = new $pay_meth;
		    $deposit_id = $$method->def_deposit_id ? $$method->def_deposit_id : ('DP' . date('Ymd'));
			$desc = $this->journal_id . ':' . $method . ':' . $$method->payment_fields;
		  }
		  $total     += $this->pmt_rows[$i]['pmt'];
		  if ($total > $this->total_amount) { // change was returned, adjust amount received for post
			$this->pmt_rows[$i]['pmt'] = $this->pmt_rows[$i]['pmt'] - ($total - $this->total_amount);
		    $total = $this->total_amount;
		  }
		  $desc = ($this->pmt_rows[$i]['desc']) ? $this->pmt_rows[$i]['desc'] : $desc;
		  $this->journal_rows[] = array(
			'gl_type'          => 'ttl',
			'credit_amount'    => $this->pmt_rows[$i]['pmt'],
			'description'      => $desc,
			'gl_account'       => $this->gl_acct_id,
			'serialize_number' => $deposit_id,
			'post_date'        => $this->post_date,
		  );
		}
	  }
	  return $total;
  }

  function add_discount_journal_row() { // put discount into journal row array
	  if ($this->discount <> 0) {
		$this->journal_rows[] = array(
		  'qty'                     => '1',
		  'gl_type'                 => 'dsc',		// code for discount charges
		  'credit_amount' 			=> $this->discount,
		  'description'             => TEXT_POINT_OF_SALE . '-' . TEXT_DISCOUNT,
		  'gl_account'              => $this->disc_gl_acct_id,
		  'taxable'                 => '0',
		  'post_date'               => $this->post_date,
		);
	  }
	  return $this->discount;
  }

  function add_item_journal_rows() {	// read in line items and add to journal row array
	  $total = 0;
	  for ($i = 0; $i < count($this->item_rows); $i++) {
		if ($this->item_rows[$i]['pstd']) { // make sure the quantity line is set and not zero
		  $this->journal_rows[] = array(
			'id'                      => $this->item_rows[$i]['id'],	// retain the db id (used for updates)
			'so_po_item_ref_id'       => 0,	// item reference id for so/po line items
			'gl_type'                 => $this->gl_type,
			'sku'                     => $this->item_rows[$i]['sku'],
			'qty'                     => $this->item_rows[$i]['pstd'],
			'description'             => $this->item_rows[$i]['desc'],
			'debit_amount' 			  => $this->item_rows[$i]['total'],
			'full_price'              => $this->item_rows[$i]['full'],
			'gl_account'              => $this->item_rows[$i]['acct'],
			'taxable'                 => $this->item_rows[$i]['tax'],
			'serialize_number'        => $this->item_rows[$i]['serial'],
			'project_id'              => $this->item_rows[$i]['proj'],
			'post_date'               => $this->post_date,
			'date_1'                  => '',
		  );
		  $total += $this->item_rows[$i]['total'];
		}
	  }
	  return $total;
  }

  function add_tax_journal_rows() {
	global $admin;
	  $total        = 0;
	  $auth_array   = array();
	  $tax_rates    = ord_calculate_tax_drop_down('b');
	  $tax_auths    = gen_build_tax_auth_array();
	  $tax_discount = $this->account_type == 'v' ? AP_TAX_BEFORE_DISCOUNT : AR_TAX_BEFORE_DISCOUNT;
	  // calculate each tax value by authority per line item
	  foreach ($this->journal_rows as $idx => $line_item) {
	    if ($line_item['taxable'] > 0 && ($line_item['gl_type'] == $this->gl_type || $line_item['gl_type'] == 'frt')) {
		  foreach ($tax_rates as $rate) {
		    if ($rate['id'] == $line_item['taxable']) {
			  $auths = explode(':', $rate['auths']);
			  foreach ($auths as $auth) {
			    $line_total = $line_item['debit_amount'] + $line_item['credit_amount']; // one will always be zero
			    if (ENABLE_ORDER_DISCOUNT && $tax_discount == '0') {
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
		  'qty'                     => '1',
		  'gl_type'                 => 'tax',		// code for tax entry
		  'debit_amount' 			=> $amount,
		  'description'             => $tax_auths[$auth]['description_short'],
		  'gl_account'              => $tax_auths[$auth]['account_id'],
		  'post_date'               => $this->post_date,
	    );
	    $total += $amount;
	  }
	  return $total;
  }

  // this function adjusts the posted total to the calculated one to take into account fractions of a cent
  function adjust_total($amount) {
	if ($this->total_amount == $amount) $this->total_amount = $amount;
  }

  function add_rounding_journal_rows($amount) { // put rounding into journal row array
	global $messageStack, $admin;
	if((float)(string)$this->total_amount == (float)(string) $amount) return ;
	$this->rounding_amt = round(($this->total_amount - $amount), $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
	$messageStack->debug("\n calculated total = ".$amount." Posted total = ". $this->total_amount." rounding = ".$this->rounding_amt);
	if ($this->rounding_amt <> 0 ) {
		$this->journal_rows[] = array(
			'qty'            => '1',
			'gl_type'        => 'rnd',		// code for discount charges
			'debit_amount'   => ($this->rounding_amt > 0) ? -$this->rounding_amt : '',
			'credit_amount'  => ($this->rounding_amt < 0) ? $this->rounding_amt  : '',
			'description'    => TEXT_POINT_OF_SALE . '-' . TEXT_ROUNDED_OF,
			'gl_account'     => $this->rounding_gl_acct_id,
			'taxable'        => '0',
			'post_date'      => $this->post_date,
		);
	}
	return $this->rounding_amt;
  }

}
?>