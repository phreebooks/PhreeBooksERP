<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |

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
//  Path: /modules/phreebooks/classes/beg_balances_imp.php
//

class beg_bal_import {
  function __construct() {
  }

  function processCSV($upload_name = '') {
	global $coa, $db, $currencies, $messageStack;
	if (!$this->cyberParse($upload_name)) return false;  // parse the submitted string, check for csv errors
//echo 'parsed string = '; print_r($this->records); echo '<br />';
	$row_id = 0;
	while ($row_id < count($this->records)) {
	  $current_order = $this->records[$row_id];
	  // pre-process and check for errors
	  if (!in_array($current_order['gl_acct'], $coa) || !in_array($current_order['inv_gl_acct'], $coa)) {
		$messageStack->add(GL_BEG_BAL_ERROR_1 . ($row_id + 1), 'error');
		return false;
	  }
	  if (!$current_order['order_id']) {
		switch (JOURNAL_ID) {
		  case 6:
			$messageStack->add(sprintf(GL_BEG_BAL_ERROR_2, ($row_id + 1)),'caution');
			$this->records[$row_id]['waiting'] = 1;
			break;
		  default:
			$messageStack->add(GL_BEG_BAL_ERROR_3 . ($row_id + 1), 'error');
			return false;
		}
	  }
	  $this->records[$row_id]['post_date'] = gen_db_date($current_order['post_date']); // from mm/dd/yyyy to YYYY-MM-DD
	  if (!validate_db_date($this->records[$row_id]['post_date'])) {
		$messageStack->add(sprintf(GL_BEG_BAL_ERROR_4, ($row_id + 1)) . DATE_FORMAT, 'error');
		return false;
	  }
	  switch (JOURNAL_ID) { // total amount is calculated for PO/SOs
		case  6:
		case 12:
		  $this->records[$row_id]['total_amount'] = $currencies->clean_value($current_order['total_amount']);
		  if ($current_order['total_amount'] == 0) {
			$messageStack->add(GL_BEG_BAL_ERROR_5 . ($row_id + 1),'caution');
			$this->records[$row_id]['skip_this_record'] = 1;
		  }
		default:
	  }
	  // TBD check for duplicate so/po/invoice numbers
	  $row_id++;
	}
	if (is_array($this->records)) {
	  // *************** START TRANSACTION *************************
	  $db->transStart();
	  if (!$this->submitJournalEntry()) {
		$db->transRollback();
	    if (DEBUG) $messageStack->write_debug();
		return false;
	  }
	  $db->transCommit();	// post the chart of account values
	  if (DEBUG) $messageStack->write_debug();
	  // *************** END TRANSACTION *************************
	}
	return true;
  }

  function cyberParse($upload_name) {
	$lines = file($_FILES[$upload_name]['tmp_name']);
	if(!$lines) return false;
	$title_line = trim(array_shift($lines));	// pull header and remove extra white space characters
	$titles = explode(",", str_replace('"', '', $title_line));
	$records = array();
	foreach ($lines as $line_num => $line) {
	  $parsed_array = $this->csv_string_to_array(trim($line));
	  $fields = array();
	  for ($field_num = 0; $field_num < count($titles); $field_num++) {
		$fields[$titles[$field_num]] = $parsed_array[$field_num];
	  }
	  $records[] = $fields;
	}
	$this->records = $records;
	return true;
  }

  function csv_string_to_array($str) {
	$results = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", trim($str));
	return preg_replace("/^\"(.*)\"$/", "$1", $results);
  }

  function submitJournalEntry() {
	global $db, $currencies, $messageStack;
	$entry_count = 0;
	$row_cnt = 0;
	while($row_cnt < count($this->records)) {
	  $order = $this->records[$row_cnt];
	  $glEntry = new journal();
	  // determine if date is within a known period, if date is before period 1 use period = 0 (and enter beginning balances)
	  $glEntry->period = gen_calculate_period($order['post_date'], $hide_error = true); // date format YYYY-MM-DD
	  if (!$glEntry->period) $glEntry->period = 1; // if out of range default to first period (required to be valid period or it won't post)
	  // build journal main entry
	  $glEntry->journal_id          = JOURNAL_ID;
	  $glEntry->post_date           = $order['post_date'];
	  $glEntry->description         = sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE'));
	  $glEntry->short_name          = $order['account_id'];
	  $glEntry->ship_short_name     = $order['account_id'];
	  $glEntry->gl_acct_id          = $order['gl_acct'];
	  $glEntry->total_amount        = $order['total_amount'];
	  $glEntry->currencies_code     = DEFAULT_CURRENCY;
	  $glEntry->purchase_invoice_id = $order['order_id'];
	  $glEntry->admin_id            = $_SESSION['admin_id'];	// set imported dept rep id to current logged in user
	  if ($order['waiting']) $glEntry->waiting = '1';
	  $glEntry->bill_primary_name   = $order['bill_primary_name'];
	  $glEntry->bill_contact        = $order['bill_contact'];
	  $glEntry->bill_address1       = $order['bill_address1'];
	  $glEntry->bill_address2       = $order['bill_address2'];
	  $glEntry->bill_city_town      = $order['bill_city_town'];
	  $glEntry->bill_state_province = $order['bill_state_province'];
	  $glEntry->bill_postal_code    = $order['bill_postal_code'];
	  $glEntry->bill_country_code   = $order['bill_country_code'];
	  $glEntry->bill_telephone1     = $order['telephone1'];
	  $glEntry->bill_telephone2     = $order['telephone2'];
	  $glEntry->bill_fax            = $order['fax'];
	  $glEntry->bill_email          = $order['email'];
	  $glEntry->bill_website        = $order['website'];
	  switch (JOURNAL_ID) {
		case 4:
		case 6:
		  $glEntry->ship_primary_name   = COMPANY_NAME;
		  $glEntry->ship_address1       = COMPANY_ADDRESS1;
		  $glEntry->ship_address2       = COMPANY_ADDRESS2;
		  $glEntry->ship_city_town      = COMPANY_CITY_TOWN;
		  $glEntry->ship_state_province = COMPANY_ZONE;
		  $glEntry->ship_postal_code    = COMPANY_POSTAL_CODE;
		  $glEntry->ship_country_code   = COMPANY_COUNTRY;
		  break;
		default:
		  $glEntry->ship_primary_name   = $order['ship_primary_name'];
		  $glEntry->ship_contact        = $order['ship_contact'];
		  $glEntry->ship_address1       = $order['ship_address1'];
		  $glEntry->ship_address2       = $order['ship_address2'];
		  $glEntry->ship_city_town      = $order['ship_city_town'];
		  $glEntry->ship_state_province = $order['ship_state_province'];
		  $glEntry->ship_postal_code    = $order['ship_postal_code'];
		  $glEntry->ship_country_code   = $order['ship_country_code'];
	  }
	  $glEntry->journal_main_array      = $glEntry->build_journal_main_array();
	  $glEntry->journal_main_array['purchase_invoice_id'] = $order['order_id'];  // skip validating the invoice ID, just set it
	  // Create the account (or update it)
	  $glEntry->bill_acct_id = $glEntry->add_account(BB_ACCOUNT_TYPE . 'b', 0, 0, true);
	  switch (JOURNAL_ID) {
		default: // update the shipping address
		  $glEntry->ship_acct_id = $glEntry->add_account(BB_ACCOUNT_TYPE . 's', 0, 0, true);
		  break;
		case 4: // skip for purchases (assume default company address)
		case 6:
	  }
	  // build journal row entries (2) one for the AP/AR account and the other for the beg bal equity account
	  $glEntry->journal_rows = array();
	  $total_amount = 0;
	  while(true) {
		$credit_debit = false;
		switch (JOURNAL_ID) {
		  case  4:                     $credit_debit = 'debit_amount';  // for journal_id = 4
		  case 10: if (!$credit_debit) $credit_debit = 'credit_amount'; // for journal_id = 10
			$glEntry->journal_rows[] = array(
				'gl_type'     => BB_GL_TYPE,
				'qty'         => $currencies->clean_value($order['quantity']),
				'sku'         => $order['sku'],
				'description' => $order['description'],
				'gl_account'  => $order['inv_gl_acct'],
				'taxable'     => $order['taxable'] ? $order['taxable'] : 0,
				$credit_debit => $currencies->clean_value($order['total_cost']),
				'post_date'   => $order['post_date'],
			);
			break;
		  case  6:                     $credit_debit = 'debit_amount';  // for journal_id = 6
		  case 12: if (!$credit_debit) $credit_debit = 'credit_amount'; // for journal_id = 12
			$glEntry->journal_rows[] = array(
				'gl_type'      => BB_GL_TYPE,
				'qty'          => '1',
				'description'  => constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE') . '-' . TEXT_IMPORT,
				'gl_account'   => $order['inv_gl_acct'],
				'taxable'      => $order['taxable'] ? $order['taxable'] : 0,
				$credit_debit  => $order['total_amount'],
				'post_date'    => $order['post_date'],
			);
			break;
		  }
		  $total_amount += $currencies->clean_value($order['total_cost']);
		  $next_order    = $this->records[$row_cnt + 1]['order_id'];
		  if ((JOURNAL_ID == 4 || JOURNAL_ID == 10) && $order['order_id'] == $next_order) { // more line items
			$row_cnt++;
			$order = $this->records[$row_cnt];
		  } else { // end of this order, break from while(true) loop
			break;
		  }
		}
		// build the total journal_item row
		switch (JOURNAL_ID) {
		  case  6: $total_amount = $order['total_amount']; // and continue
		  case  4: $debit_credit = 'credit_amount';        break;
		  case 12: $total_amount = $order['total_amount']; // and continue
		  case 10: $debit_credit = 'debit_amount';         break;
		}
		$glEntry->journal_rows[] = array(
		  'gl_type'     => 'ttl',
		  'description' => constant('ORD_TEXT_' . $glEntry->journal_id . '_WINDOW_TITLE') . '-' . TEXT_TOTAL,
		  'gl_account'  => $order['gl_acct'],
		  $debit_credit => $total_amount,
		  'post_date'   => $order['post_date'],
		);
		$glEntry->journal_main_array['total_amount'] = $total_amount;
		if (!$glEntry->Post('insert')) return false;
		$entry_count++;
		$row_cnt++;
	}
	$this->line_count = $entry_count;
	return true;
  }

  function processInventory($upload_name) {
	global $coa, $db, $currencies, $messageStack;
	if (!$this->cyberParse($upload_name)) return false;
	$post_date = gen_specific_date(date('Y-m-d'), $day_offset = -1);
	$glEntry   = new journal();
	$sku_list  = array();
	$coa_list  = array();
	$affected_accounts = array();
	for ($row_id = 0, $j = 2; $row_id < count($this->records); $row_id++, $j++) {
	  $row = $this->records[$row_id];
	  $total_amount = $currencies->clean_value($row['total_amount']);
	  $qty = $currencies->clean_value($row['quantity']);
	  // check for errors and report/exit if error found
	  if (!gen_validate_sku($row['sku'])) {
		$messageStack->add(GL_ERROR_UPDATING_INVENTORY_STATUS . $row['sku'] . GL_BEG_BAL_ERROR_0 . $j, 'error');
		return false;
	  }
	  if (!in_array($row['inv_gl_acct'], $coa) || !in_array($row['gl_acct'], $coa)) {
		$messageStack->add(GL_BEG_BAL_ERROR_6 . $j, 'error');
		return false;
	  }
	  if ($qty == 0) {
		$messageStack->add(GL_BEG_BAL_ERROR_7 . $j,'caution');
	  } else {
		$affected_accounts[$row['inv_gl_acct']] = true;	// need list of accounts to update history
		$affected_accounts[$row['gl_acct']]     = true;	// both credit and debit
		$sku_list[$row['sku']]['qty']          += $qty; // load quantity indexed by sku
		$sku_list[$row['sku']]['total']        += $total_amount; // load total_value indexed by sku
		$coa_list[$row['inv_gl_acct']]         += $total_amount; // add to debit total by coa
		$coa_list[$row['gl_acct']]             -= $total_amount; // add to credit total by coa
	  }
	}
	if (is_array($sku_list)) {
	  $glEntry->affected_accounts = $affected_accounts;
	  // *************** START TRANSACTION *************************
	  $db->transStart();
	  // update inventory balances on hand
	  foreach ($sku_list as $sku => $details) {
		$sql = "update " . TABLE_INVENTORY . " set quantity_on_hand = quantity_on_hand + {$details['qty']} where sku = '$sku'";
		$result = $db->Execute($sql);
		if ($result->AffectedRows() <> 1) {
		  $messageStack->add(sprintf(GL_BEG_BAL_ERROR_8, $sku),'error');
		  $db->transRollback();
		  return false;
		}
		$history_array = array(
		  'ref_id'    => 0,
		  'sku'       => $sku,
		  'qty'       => $details['qty'],
		  'remaining' => $details['qty'],
		  'unit_cost' => ($details['total'] / $details['qty']),
		  'avg_cost'  => ($details['total'] / $details['qty']),
		  'post_date' => $post_date,
		);
		$result = db_perform(TABLE_INVENTORY_HISTORY, $history_array, 'insert');
	  }
	  // update chart of account beginning balances for period 1
	  foreach ($coa_list as $account => $amount) {
		$sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " set beginning_balance = beginning_balance + $amount
			where account_id = '$account' and period = 1";
		$result = $db->Execute($sql);
		if ($result->AffectedRows() <> 1) {
		  $messageStack->add(sprintf(GL_BEG_BAL_ERROR_9, $account),'error');
		  $db->transRollback();
		  return false;
		}
	  }
	  // update the chart of accounts history through the existing periods
	  if (!$glEntry->update_chart_history_periods($period = 1)) {
	    if (DEBUG) $messageStack->write_debug();
		return $glEntry->fail_message(GL_ERROR_UPDATE_COA_HISTORY);
	  }
	  $db->transCommit();	// post the chart of account values
	  if (DEBUG) $messageStack->write_debug();
	  // *************** END TRANSACTION *************************
	}
	$this->line_count = $row_id;
	return true;
  }

}
?>