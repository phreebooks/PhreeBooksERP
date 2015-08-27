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
//  Path: /modules/phreebooks/classes/journal/journal_16.php
//
// Inventory Adjustment Journal (16)
namespace phreebooks\classes\journal;
class journal_16 extends \core\classes\journal {
	public $description 		= TEXT_INVENTORY_ADJUSTMENTS;
	public $id_field_name 		= TEXT_ADJUSTMENT_NUMBER;


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
			if ($row['qty'] > 0) {
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
		// nothing required to do
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
		// prepare some variables
		for ($i = 0; $i < count($this->journal_rows); $i++) if ($this->journal_rows[$i]['sku']) {
			$qty = $this->journal_rows[$i]['qty'];
			$this->update_inventory_status($this->journal_rows[$i]['sku'], 'quantity_on_hand', -$qty);
			// adjust po/so inventory, if necessary, based on min of qty on ordered and qty shipped/received
			if ($this->journal_rows[$i]['so_po_item_ref_id']) {
				$item_array = $this->load_so_po_balance($this->so_po_ref_id, $this->id, false);
				$bal_before_post = $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['ordered'] - $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['processed'];
				// do not allow qty on order to go below zero.
				$adjustment = min($this->journal_rows[$i]['qty'], $bal_before_post);
				$this->update_inventory_status($this->journal_rows[$i]['sku'], 'quantity_on_sales_order', $adjustment);
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
		if ($sql->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException(GL_ERROR_CALCULATING_COGS);
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
					'journal_id' => 16,
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
		if ($ref_id) throw new \core\classes\userException('Error in classes/gen_ledger, function load_so_po_balance. Bad $journal_id for this function.');
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
			$sql = "SELECT purchase_invoice_id FROM " . TABLE_JOURNAL_MAIN . " WHERE purchase_invoice_id = '{$this->purchase_invoice_id}' and journal_id = '16'";
			if ($this->id) $sql .= " and id <> " . $this->id;
			$result = $admin->DataBase->query($sql);
			if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(sprintf(TEXT_THE_YOU_ENTERED_IS_A_DUPLICATE,_PLEASE_ENTER_A_NEW_UNIQUE_VALUE_ARGS, $journal_types_list[16]['id_field_name']));
			$this->journal_main_array['purchase_invoice_id'] = $this->purchase_invoice_id;
			$admin->messageStack->debug(" specified ID but no dups, returning OK. ");
		} else {	// generate a new order/invoice value
			$this->journal_main_array['purchase_invoice_id'] = '';
			$admin->messageStack->debug(" generated ID, returning ID# " . $this->journal_main_array['purchase_invoice_id']);
		}
		return true;
	}

	function increment_purchase_invoice_id($force = false) {
		global $admin;
		$this->purchase_invoice_id = $this->journal_main_array['purchase_invoice_id'];
		return true;
	}

} // end class journal
?>