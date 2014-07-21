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
//  Path: /modules/phreebooks/functions/phreebooks.php
//

function fetch_item_description($id) {
  global $db;
  $result = $db->Execute("select description from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id . " limit 1");
  return $result->fields['description'];
}

function validate_fiscal_year($next_fy, $next_period, $next_start_date, $num_periods = 12) {
  global $db;
  for ($i = 0; $i < $num_periods; $i++) {
	$fy_array = array(
	  'period'      => $next_period,
	  'fiscal_year' => $next_fy,
	  'start_date'  => $next_start_date,
	  'end_date'    => gen_specific_date($next_start_date, $day_offset = -1, $month_offset = 1),
	  'date_added'  => date('Y-m-d'),
	);
	db_perform(TABLE_ACCOUNTING_PERIODS, $fy_array, 'insert');
	$next_period++;
	$next_start_date = gen_specific_date($next_start_date, $day_offset = 0, $month_offset = 1);
  }
  return $next_period--;
}

function modify_account_history_records($id, $add_acct = true) {
  global $db;
  $result = $db->Execute("select max(period) as period from " . TABLE_ACCOUNTING_PERIODS);
  $max_period = $result->fields['period'];
  if (!$max_period) die ('table: '.TABLE_ACCOUNTING_PERIODS.' is not set, run setup.');
  if ($add_acct) {
    $result = $db->Execute("select heading_only from " . TABLE_CHART_OF_ACCOUNTS . " where id = '" . $id . "'");
	if ($result->fields['heading_only'] <> '1') {
	  for ($i = 0, $j = 1; $i < $max_period; $i++, $j++) {
	    $db->Execute("insert into " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " (account_id, period) values('" . $id . "', '" . $j . "')");
	  }
	}
  } else {
	$result = $db->Execute("delete from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " where account_id = '" . $id . "'");
  }
}

function build_and_check_account_history_records() {
  global $db;
  $result = $db->Execute("select max(period) as period from " . TABLE_ACCOUNTING_PERIODS);
  $max_period = $result->fields['period'];
  if (!$max_period) die ('table: '.TABLE_ACCOUNTING_PERIODS.' is not set, run setup.');
  $result = $db->Execute("select id, heading_only from " . TABLE_CHART_OF_ACCOUNTS . " order by id");
  while (!$result->EOF) {
    if ($result->fields['heading_only'] <> '1') {
	  $account_id = $result->fields['id'];
	  for ($i = 0, $j = 1; $i < $max_period; $i++, $j++) {
	    $record_found = $db->Execute("select id from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " where account_id = '" . $account_id . "' and period = " . $j);
	    if (!$record_found->RecordCount()) {
		  $db->Execute("insert into " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " (account_id, period) values('" . $account_id . "', '" . $j . "')");
		 }
	  }
	}
	$result->MoveNext();
  }
}

function get_fiscal_year_pulldown() {
    global $db;
    $fy_values = $db->Execute("select distinct fiscal_year from " . TABLE_ACCOUNTING_PERIODS . " order by fiscal_year");
    $fy_array = array();
    while (!$fy_values->EOF) {
      $fy_array[] = array('id' => $fy_values->fields['fiscal_year'], 'text' => $fy_values->fields['fiscal_year']);
      $fy_values->MoveNext();
    }
    return $fy_array;
}

function load_coa_types() {
  global $coa_types_list;
  if (!is_array($coa_types_list)) { 
    require_once(DIR_FS_MODULES . 'phreebooks/defaults.php');
  }
  $coa_types = array();
  foreach ($coa_types_list as $value) {
    $coa_types[$value['id']] = array(
	  'id'    => $value['id'],
	  'text'  => $value['text'],
	  'asset' => $value['asset'],
	);
  }
  return $coa_types;
}

function load_coa_info($types = array()) { // includes inactive accounts
  global $db;
  $coa_data = array();
  $sql = "select * from " . TABLE_CHART_OF_ACCOUNTS;
  if (sizeof($types > 0)) $sql .= " where account_type in (" . implode(", ", $types) . ")";
  $result = $db->Execute($sql);
  while (!$result->EOF) {
    $coa_data[$result->fields['id']] = array(
	  'id'              => $result->fields['id'],
	  'description'     => $result->fields['description'],
	  'heading_only'    => $result->fields['heading_only'],
	  'primary_acct_id' => $result->fields['primary_acct_id'],
	  'account_type'    => $result->fields['account_type'],
	);
	$result->MoveNext();
  }
  return $coa_data;
}

function fill_paid_invoice_array($id, $account_id, $type = 'c') {
	// to build this data array, all current open invoices need to be gathered and then the paid part needs
	// to be applied along with discounts taken by row.
	global $db, $currencies;
	$negate = ((JOURNAL_ID == 20 && $type == 'c') || (JOURNAL_ID == 18 && $type == 'v')) ? true : false;
	// first read all currently open invoices and the payments of interest and put into an array
	$paid_indeces = array();
	if ($id > 0) { 
	  $result = $db->Execute("select distinct so_po_item_ref_id from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id);
	  while (!$result->EOF) {
	    if ($result->fields['so_po_item_ref_id']) $paid_indeces[] = $result->fields['so_po_item_ref_id'];
	    $result->MoveNext();
	  }
	}
	switch ($type) {
	  case 'c': $search_journal = '(12, 13)'; break;
	  case 'v': $search_journal = '(6, 7)';   break;
	  default: return false;
	}
	$open_invoices = array();
	$sql = "select id, journal_id, post_date, terms, purch_order_id, purchase_invoice_id, total_amount, gl_acct_id 
	  from " . TABLE_JOURNAL_MAIN . " 
	  where (journal_id in " . $search_journal . " and closed = '0' and bill_acct_id = " . $account_id . ")";
	if (sizeof($paid_indeces) > 0) $sql .= " or (id in (" . implode(',',$paid_indeces) . ") and closed = '0')";
	$sql .= " order by post_date";
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  if ($result->fields['journal_id'] == 7 || $result->fields['journal_id'] == 13) {
	    $result->fields['total_amount'] = -$result->fields['total_amount'];
	  }
	  $result->fields['total_amount'] -= fetch_partially_paid($result->fields['id']);
	  $result->fields['description']   = $result->fields['purch_order_id'];
	  $result->fields['discount']      = '';
	  $result->fields['amount_paid']   = '';
	  $open_invoices[$result->fields['id']] = $result->fields;
	  $result->MoveNext();
	}
	// next read the record of interest and add/adjust open invoice array with amounts
	$sql = "select id, ref_id, so_po_item_ref_id, gl_type, description, debit_amount, credit_amount, gl_account 
	  from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id;
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  $amount = ($result->fields['debit_amount']) ? $result->fields['debit_amount'] : $result->fields['credit_amount'];
	  if ($negate) $amount = -$amount;
	  $index = $result->fields['so_po_item_ref_id'];
	  switch ($result->fields['gl_type']) {
	    case 'dsc': // it's the discount field
		  $open_invoices[$index]['discount']      = $amount;
		  $open_invoices[$index]['amount_paid']  -= $amount;
		  break;
		case 'chk':
	    case 'pmt': // it's the payment field
		  $open_invoices[$index]['total_amount'] += $amount;
		  $open_invoices[$index]['description']   = $result->fields['description'];
		  $open_invoices[$index]['amount_paid']   = $amount;
		  break;
		case 'ttl':
		  $payment_fields = $result->fields['description']; // payment details
		default:
	  }
	  $result->MoveNext();
	}
	ksort($open_invoices);

	$balance   = 0;
	$index     = 0;
	$item_list = array();
	foreach ($open_invoices as $key => $line_item) {
	  // fetch some information about the invoice
	  $sql = "select id, post_date, terms, purchase_invoice_id, purch_order_id, gl_acct_id, waiting  
		from " . TABLE_JOURNAL_MAIN . " where id = " . $key;
	  $result = $db->Execute($sql);
	  $due_dates = calculate_terms_due_dates($result->fields['post_date'], $result->fields['terms'], ($type == 'v' ? 'AP' : 'AR'));
	  if ($negate) {
	    $line_item['total_amount'] = -$line_item['total_amount'];
	    $line_item['discount']     = -$line_item['discount'];
	    $line_item['amount_paid']  = -$line_item['amount_paid'];
	  }
	  $balance += $line_item['total_amount'];
	  $item_list[] = array(
		'id'                  => $result->fields['id'],
		'waiting'             => $result->fields['waiting'],
		'purchase_invoice_id' => $result->fields['purchase_invoice_id'],
		'purch_order_id'      => $result->fields['purch_order_id'],
		'percent'             => $due_dates['discount'],
		'post_date'           => $result->fields['post_date'],
		'early_date'          => gen_locale_date($due_dates['early_date']),
		'net_date'            => gen_locale_date($due_dates['net_date']),
		'total_amount'        => $currencies->format($line_item['total_amount']),
		'gl_acct_id'          => $result->fields['gl_acct_id'],
		'description'         => $line_item['description'],
		'discount'            => $line_item['discount']    ? $currencies->format($line_item['discount']) : '',
		'amount_paid'         => $line_item['amount_paid'] ? $currencies->format($line_item['amount_paid']) : '',
	  );
	  $index++;
	}
	switch(PHREEBOOKS_DEFAULT_BILL_SORT) {
	  case 'due_date': // sort the open invoice array to order by preference
		foreach ($item_list as $key => $row) {
			$net_date[$key]   = $row['net_date'];
			$invoice_id[$key] = $row['purchase_invoice_id'];
		}
		array_multisort($net_date, SORT_ASC, $invoice_id, SORT_ASC, $item_list);
	  default: // sort by invoice number
	}
    return array('balance' => $balance, 'payment_fields' => $payment_fields, 'invoices' => $item_list);
}

function fetch_partially_paid($id) {
  global $db;
  $sql = "select sum(i.debit_amount) as debit, sum(i.credit_amount) as credit 
	from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
	where i.so_po_item_ref_id = $id and m.journal_id in (18, 20) and i.gl_type in ('chk', 'pmt') 
	group by m.journal_id";
  $result = $db->Execute($sql);
  if($result->RecordCount() == 0) return 0;
  if ($result->fields['debit'] || $result->fields['credit']) {
    return $result->fields['debit'] + $result->fields['credit'];
  } else {
    return 0;
  }
}

function calculate_terms_due_dates($post_date, $terms_encoded, $type = 'AR') {
  $terms = explode(':', $terms_encoded);
  $date_details = gen_get_dates($post_date);
  $result = array();
  switch ($terms[0]) {
	default:
	case '0': // Default terms
		$result['discount'] = constant($type . '_PREPAYMENT_DISCOUNT_PERCENT') / 100;
		$result['net_date'] = gen_specific_date($post_date, constant($type . '_NUM_DAYS_DUE'));
		if ($result['discount'] <> 0) {
		  $result['early_date'] = gen_specific_date($post_date, constant($type . '_PREPAYMENT_DISCOUNT_DAYS'));
		} else {
		  $result['early_date'] = $post_date; // move way out
		}
		break;
	case '1': // Cash on Delivery (COD)
	case '2': // Prepaid
		$result['discount']   = 0;
		$result['early_date'] = $post_date;
		$result['net_date']   = $post_date;
		break;
	case '3': // Special terms
		$result['discount']   = $terms[1] / 100;
		$result['early_date'] = gen_specific_date($post_date, $terms[2]);
		$result['net_date']   = gen_specific_date($post_date, $terms[3]);
		break;
	case '4': // Due on day of next month
		$result['discount']   = $terms[1] / 100;
		$result['early_date'] = gen_specific_date($post_date, $terms[2]);
		$result['net_date']   = gen_db_date( $terms[3] );
		break;
	case '5': // Due at end of month
		$result['discount']   = $terms[1] / 100;
		$result['early_date'] = gen_specific_date($post_date, $terms[2]);
		$result['net_date']   = date('Y-m-d', mktime(0, 0, 0, $date_details['ThisMonth'], $date_details['TotalDays'], $date_details['ThisYear']));
		break;
  }
  return $result;
}

function load_cash_acct_balance($post_date, $gl_acct_id, $period) {
  global $db, $messageStack;
  $acct_balance = 0;
  if (!$gl_acct_id) return $acct_balance;
  $sql = "select beginning_balance from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
	where account_id = '" . $gl_acct_id . "' and period = " . $period;
  $result = $db->Execute($sql);
  $acct_balance = $result->fields['beginning_balance'];

  // load the payments and deposits for the current period
  $bank_list = array();
  $sql = "select i.debit_amount, i.credit_amount
	from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
	where m.period = " . $period . " and i.gl_account = '" . $gl_acct_id . "' and m.post_date <= '" . $post_date . "' 
	order by m.post_date, m.journal_id";
  $result = $db->Execute($sql);
  while (!$result->EOF) {
    $acct_balance += $result->fields['debit_amount'] - $result->fields['credit_amount'];
    $result->MoveNext();
  }
  return $acct_balance;
}

  function gen_build_tax_auth_array() {
    global $db;
    $tax_auth_values = $db->Execute("select tax_auth_id, description_short, account_id , tax_rate
      from " . TABLE_TAX_AUTH . " order by description_short");
    if ($tax_auth_values->RecordCount() < 1) {
      return false;
    } else {
		while (!$tax_auth_values->EOF) {
		  $tax_auth_array[$tax_auth_values->fields['tax_auth_id']] = array(
			'description_short' => $tax_auth_values->fields['description_short'],
			'account_id'        => $tax_auth_values->fields['account_id'],
			'tax_rate'          => $tax_auth_values->fields['tax_rate'],
		  );
		  $tax_auth_values->MoveNext();
		}
    	return $tax_auth_array;
    }
  }

  function gen_calculate_tax_rate($tax_authorities_chosen, $tax_auth_array) {
	$chosen_auth_array = explode(':', $tax_authorities_chosen);
	$total_tax_rate = 0;
	while ($chosen_auth = array_shift($chosen_auth_array)) {
	  $total_tax_rate += $tax_auth_array[$chosen_auth]['tax_rate'];
	}
	return $total_tax_rate;
  }

  function ord_calculate_tax_drop_down($type = 'c') {
    global $db;
	$tax_auth_array = gen_build_tax_auth_array();
    $sql = "select tax_rate_id, description_short, rate_accounts from " . TABLE_TAX_RATES;
	switch ($type) {
	  default:
	  case 'c':
	  case 'v': $sql .= " where type = '" . $type . "'"; break;
	  case 'b': // both
	}
	$tax_rates = $db->Execute($sql);
    $tax_rate_drop_down = array();
    $tax_rate_drop_down[] = array('id' => '0', 'rate' => '0', 'text' => TEXT_NONE, 'auths' => '');
	while (!$tax_rates->EOF) {
	  $tax_rate_drop_down[] = array(
	    'id'    => $tax_rates->fields['tax_rate_id'],
		'rate'  => gen_calculate_tax_rate($tax_rates->fields['rate_accounts'], $tax_auth_array), 
		'text'  => $tax_rates->fields['description_short'],
		'auths' => $tax_rates->fields['rate_accounts'],
	  );
	  $tax_rates->MoveNext();
	}
	return $tax_rate_drop_down;
  }

  function ord_get_so_po_num($id = '') {
	global $db;
	$result = $db->Execute("select purchase_invoice_id from " . TABLE_JOURNAL_MAIN . " where id = " . $id);
	return ($result->RecordCount()) ? $result->fields['purchase_invoice_id'] : '';
  }

  function ord_get_projects() {
    global $db;
    $result_array = array();
    $result_array[] = array('id' => '', 'text' => TEXT_NONE);
	// fetch cost structure
	$costs = array();
	$result = $db->Execute("select cost_id, description_short from " . TABLE_PROJECTS_COSTS . " where inactive = '0'");
	while(!$result->EOF) {
	  $costs[$result->fields['cost_id']] = $result->fields['description_short'];
	  $result->MoveNext();
	}
	// fetch phase structure
	$phases = array();
	$result = $db->Execute("select phase_id, description_short, cost_breakdown from " . TABLE_PROJECTS_PHASES . " where inactive = '0'");
	while(!$result->EOF) {
	  $phases[$result->fields['phase_id']] = array(
	  	'text'   => $result->fields['description_short'],
		'detail' => $result->fields['cost_breakdown'],
	  );
	  $result->MoveNext();
	}
	// fetch projects
	$result = $db->Execute("select id, short_name, account_number from " . TABLE_CONTACTS . " where type = 'j' and inactive <> '1'");
	while(!$result->EOF) {
	  $base_id   = $result->fields['id'];
	  $base_text = $result->fields['short_name'];
	  if ($result->fields['account_number'] == '1' && sizeof($phases) > 0) { // use phases
		foreach ($phases as $phase_id => $phase) {
		  $phase_base = $base_id   . ':' . $phase_id;
		  $phase_text = $base_text . ' -> ' . $phase['text'];
		  if ($phase['detail'] == '1' && sizeof($costs) > 0) {
		    foreach ($costs as $cost_id => $cost) {
              $result_array[] = array('id' => $phase_base . ':' . $cost_id, 'text' => $phase_text . ' -> ' . $cost);
			}
		  } else {
            $result_array[] = array('id' => $phase_base, 'text' => $phase_text);
		  }
		}
	  } else {
        $result_array[] = array('id' => $base_id, 'text' => $base_text);
	  }
	  $result->MoveNext();
	}
    return $result_array;
  }

  function gen_auto_update_period($show_message = true) {
	global $db, $messageStack;
	$period = gen_calculate_period(date('Y-m-d'), true);
	if ($period == CURRENT_ACCOUNTING_PERIOD && defined('CURRENT_ACCOUNTING_PERIOD')) return; // we're in the current period
	if (!$period) { // we're outside of the defined fiscal years
	  if ($show_message) $messageStack->add(ERROR_MSG_POST_DATE_NOT_IN_FISCAL_YEAR,'error');
	} else { // update CURRENT_ACCOUNTING_PERIOD constant with this new period
	  $result = $db->Execute("select start_date, end_date from " . TABLE_ACCOUNTING_PERIODS . " where period = " . $period);
	  write_configure('CURRENT_ACCOUNTING_PERIOD',       $period);
	  write_configure('CURRENT_ACCOUNTING_PERIOD_START', $result->fields['start_date']);
	  write_configure('CURRENT_ACCOUNTING_PERIOD_END',   $result->fields['end_date']);
	  gen_add_audit_log(GEN_LOG_PERIOD_CHANGE);
	  if ($show_message) {
	    $messageStack->add(sprintf(ERROR_MSG_ACCT_PERIOD_CHANGE, $period),'success');
	  }
	}
  }

  function build_search_sql($fields, $id, $id_from = '', $id_to = '') {
    $crit = array();
    foreach ($fields as $field) {	
	  $output = '';
	  switch ($id) {
	    default:
		case 'all':  break;
		case 'eq':   if ($id_from) $output .= $field . " = '" . $id_from . "'";      break;
		case 'neq':  if ($id_from) $output .= $field . " <> '" . $id_from . "'";     break;
		case 'like': if ($id_from) $output .= $field . " like '%" . $id_from . "%'"; break;
		case 'rng':
		  if ($id_from)          $output .= $field . " >= '" . $id_from . "'";
		  if ($output && $id_to) $output .= " and ";
		  if ($id_to)            $output .= $field . " <= '" . $id_to . "'";
	  }
	  if ($output) $crit[] = $output;
    }
    return ($crit) ? ('(' . implode(' or ', $crit) . ')') : '';
  }

  function repost_journals($journals, $start_date, $end_date) {
	global $db, $messageStack;
	if (sizeof($journals) > 0) {
	  $sql = "SELECT id FROM ".TABLE_JOURNAL_MAIN." WHERE journal_id IN (".implode(',', $journals).") 
		AND post_date>= '$start_date' AND post_date<'".gen_specific_date($end_date, 1)."' ORDER BY post_date, id";
	  $result = $db->Execute($sql);
	  $cnt = 0;
	  $db->transStart();
	  while (!$result->EOF) {
	    $gl_entry = new journal($result->fields['id']);
	    $gl_entry->remove_cogs_rows(); // they will be regenerated during the re-post
	    if (!$gl_entry->Post('edit', true)) {
		  $db->transRollback();
		  $messageStack->add('<br /><br />Failed Re-posting the journals, try a smaller range. The record that failed was # '.$gl_entry->id,'error');
		  return false;
	    }
		$cnt++;
	    $result->MoveNext();
	  }
      $db->transCommit();
	  return $cnt;
	}
  }

  function calculate_aging($id, $type = 'c', $special_terms = '0') {
  	global $db;
  	$output = array();
  	if (!$id) return $output;
  	$today         = date('Y-m-d');
  	$terms         = explode(':', $special_terms);
  	$credit_limit  = $terms[4] ? $terms[4] : constant(($type=='v'?'AP':'AR').'_CREDIT_LIMIT_AMOUNT');
	$due_days      = $terms[3] ? $terms[3] : constant(($type=='v'?'AP':'AR').'_NUM_DAYS_DUE');
	$due_date      = gen_specific_date($today, -$due_days);
	$late_30 = gen_specific_date($today, ($type == 'v') ? -AP_AGING_DATE_1 : -AR_AGING_PERIOD_1);
	$late_60 = gen_specific_date($today, ($type == 'v') ? -AP_AGING_DATE_2 : -AR_AGING_PERIOD_2);
	$late_90 = gen_specific_date($today, ($type == 'v') ? -AP_AGING_DATE_3 : -AR_AGING_PERIOD_3);
	$output = array(
	  'balance_0'  => '0',
	  'balance_30' => '0',
	  'balance_60' => '0',
	  'balance_90' => '0',
	);
	$inv_jid = ($type == 'v') ? '6, 7' : '12, 13';
	$pmt_jid = ($type == 'v') ? '20' : '18';
	$total_outstanding = 0;
	$past_due          = 0;
	$sql = "select id from " . TABLE_JOURNAL_MAIN . " 
		where bill_acct_id = " . $id . " and journal_id in (" . $inv_jid . ") and closed = '0'";
	$open_inv = $db->Execute($sql);
	while(!$open_inv->EOF) {
	  $result = $db->Execute("select debit_amount, credit_amount from " . TABLE_JOURNAL_ITEM . " where gl_type = 'ttl' and ref_id = " . $open_inv->fields['id']);
	  $result2 = $db->Execute("select journal_id, post_date from " . TABLE_JOURNAL_MAIN . " where id = " . $open_inv->fields['id']);
	  $total_billed = $result->fields['debit_amount'] - $result->fields['credit_amount'];
	  $post_date = $result2->fields['post_date'];
	  $result = $db->Execute("select sum(debit_amount) as debits, sum(credit_amount) as credits 
	    from " . TABLE_JOURNAL_ITEM . " where so_po_item_ref_id = '" . $open_inv->fields['id'] . "' and gl_type in ('pmt', 'chk')");
	  $total_paid = $result->fields['credits'] - $result->fields['debits'];
	  $balance = $total_billed - $total_paid;
	  if ($type == 'v') $balance = -$balance;
	  // start the placement in aging array
	  if ($post_date < $due_date) $past_due += $balance;
	  if ($post_date < $late_90) {
		$output['balance_90'] += $balance;
	    $total_outstanding += $balance;
	  } elseif ($post_date < $late_60) {
		$output['balance_60'] += $balance;
	    $total_outstanding += $balance;
	  } elseif ($post_date < $late_30) {
		$output['balance_30'] += $balance;
	    $total_outstanding += $balance;
	  } elseif ($post_date <= $today) {
		$output['balance_0']  += $balance;
	    $total_outstanding += $balance;
	  } // else it's in the future
	  $open_inv->MoveNext();
	}
	$output['total']        = $total_outstanding;
	$output['past_due']     = $past_due;
	$output['credit_limit'] = $credit_limit;
	$output['terms_lang']   = gen_terms_to_language($special_terms, false, ($type=='v'?'AP':'AR'));
	return $output;
  }

?>