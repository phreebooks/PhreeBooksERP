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
//  Path: /modules/phreebooks/pages/reconciliation/pre_process.php
//
$security_level = validate_user(SECURITY_ID_ACCT_RECONCILIATION);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'classes/gen_ledger.php');
require_once(DIR_FS_WORKING . 'classes/banking.php');

/**************   page specific initialization  *************************/
// retrieve the current status of this periods reconciliation
$period = isset($_REQUEST['search_period']) ? $_REQUEST['search_period'] : CURRENT_ACCOUNTING_PERIOD;
if (!isset($_REQUEST['sf'])) $_REQUEST['sf'] = TEXT_REFERENCE;
if (!isset($_REQUEST['so'])) $_REQUEST['so'] = 'asc';
if ($period == 'all') {
	$messageStack->add(BNK_ERROR_PERIOD_NOT_ALL, 'error');
	$period = CURRENT_ACCOUNTING_PERIOD;
}
$gl_account      = isset($_POST['gl_account']) ? $_POST['gl_account'] : AR_SALES_RECEIPTS_ACCOUNT;
$cleared_items   = array();
$uncleared_items = array();
$all_items       = array();

// build the array of cash accounts
$result = $db->Execute("select id, description from ".TABLE_CHART_OF_ACCOUNTS." where account_type = '0' and heading_only = '0' order by id");
$account_array = array();
$gl_accounts   = array();
while (!$result->EOF) {
  $text_value = $result->fields['id'] . ' : ' . $result->fields['description'];
  $account_array[] = array('id' => $result->fields['id'], 'text' => $text_value);
  $gl_accounts[] = $result->fields['id'];
  $result->MoveNext();
}

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/reconciliation/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	validate_security($security_level, 3);
  	$statement_balance = $currencies->clean_value($_POST['start_balance']);
	if (is_array($_POST['id'])) for ($i = 0; $i < count($_POST['id']); $i++) {
	  $all_items[] = $_POST['id'][$i];
	  if (isset($_POST['chk'][$i])) {
		$cleared_items[]   = $_POST['id'][$i];
	  } else {
		$uncleared_items[] = $_POST['id'][$i];
	  }
	}
	// see if this is an update or new entry
	$sql_data_array = array(
	  'statement_balance' => $statement_balance,
	  'cleared_items'     => serialize($cleared_items),
	);
	$sql = "select id from " . TABLE_RECONCILIATION . " where period = " . $period . " and gl_account = '" . $gl_account . "'";
	$result = $db->Execute($sql);
	if ($result->RecordCount() == 0) {
	  $sql_data_array['period']     = $period;
	  $sql_data_array['gl_account'] = $gl_account;
	  db_perform(TABLE_RECONCILIATION, $sql_data_array, 'insert');
	} else {
	  db_perform(TABLE_RECONCILIATION, $sql_data_array, 'update', "period = " . $period . " and gl_account = '" . $gl_account . "'");
	}
	// set reconciled flag to period for all records that were checked
	if (count($cleared_items)) {
	  $sql = "update " . TABLE_JOURNAL_ITEM . " set reconciled = $period where id in (" . implode(',', $cleared_items) . ")";
	  $result = $db->Execute($sql);
	}
	// set reconciled flag to '0' for all records that were unchecked during this period
	if (count($uncleared_items)) {
	  $sql = "update " . TABLE_JOURNAL_ITEM . " set reconciled = 0 where reconciled = $period and id in (" . implode(',', $uncleared_items) . ")";
	  $result = $db->Execute($sql);
	}
	// check to see if the journal main closed flag should be set or cleared based on all cash accounts
	$mains = array();
	if (count($all_items)) {
	  $result = $db->Execute("select ref_id from " . TABLE_JOURNAL_ITEM . " where id in (" . implode(",", $all_items) . ")");
	  while (!$result->EOF) {
		$mains[] = $result->fields['ref_id'];
		$result->MoveNext();
	  }
	}
	if (count($mains)) { 
	  // closes if any cash records within the journal main that are reconciled
	  $db->Execute("update " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
	    set m.closed = '1' 
		  where i.reconciled > 0 
		  and i.gl_account in ('" . implode("','", $gl_accounts) . "') 
		  and m.id in (" . implode(",", $mains) . ")");
	  // re-opens if any cash records within the journal main that are not reconciled
	  $db->Execute("update " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
	    set m.closed = '0' 
		  where i.reconciled = 0 
		  and i.gl_account in ('" . implode("','", $gl_accounts) . "') 
		  and m.id in (" . implode(",", $mains) . ")");
	}
	$messageStack->add(BNK_RECON_POST_SUCCESS,'success');
	gen_add_audit_log(BNK_LOG_ACCT_RECON . $period, $gl_account);
	break;
  default:
}

/*****************   prepare to display templates  *************************/
$bank_list = array();
$statement_balance = $currencies->format(0);

// load the payments and deposits that are open
$fiscal_dates = gen_calculate_fiscal_dates($period);
$end_date = $fiscal_dates['end_date'];
$sql = "select i.id, m.post_date, i.debit_amount, i.credit_amount, m.purchase_invoice_id, m.bill_primary_name, i.description, i.reconciled, m.journal_id 
	from ".TABLE_JOURNAL_MAIN." m inner join ".TABLE_JOURNAL_ITEM." i on m.id = i.ref_id
	where i.gl_account = '$gl_account' and (i.reconciled = 0 or i.reconciled > $period) and m.post_date <= '".$fiscal_dates['end_date']."'";
$result = $db->Execute($sql);
while (!$result->EOF) {
  $previous_total = $bank_list[$result->fields['id']]['dep_amount'] - $bank_list[$result->fields['id']]['pmt_amount'];
  $new_total      = $previous_total + $result->fields['debit_amount'] - $result->fields['credit_amount'];
  $bank_list[$result->fields['id']] = array(
	'post_date'  => $result->fields['post_date'],
	'reference'  => ($result->fields['journal_id'] == 19)? $result->fields['post_date'] : $result->fields['purchase_invoice_id'],
	'name'       => $result->fields['bill_primary_name'],
	'description'=> $result->fields['description'],
	'dep_amount' => ($new_total < 0) ? ''          : $new_total,
	'pmt_amount' => ($new_total < 0) ? -$new_total : '',
	'payment'    => ($new_total < 0) ? 1           : 0,
	'cleared'    => $result->fields['reconciled'],
  );
  $result->MoveNext();
}

// check to see if in partial reconciliation, if so add checked items
$sql = "select statement_balance, cleared_items from ".TABLE_RECONCILIATION." where period = $period and gl_account = '$gl_account'";
$result = $db->Execute($sql);
if ($result->RecordCount() <> 0) { // there are current cleared items in the present accounting period (edit)
  $statement_balance = $currencies->format($result->fields['statement_balance']);
  $cleared_items     = unserialize($result->fields['cleared_items']);
  // load information from general ledger
  if (count($cleared_items) > 0) {
//	$sql = "select i.id, m.post_date, i.debit_amount, i.credit_amount, m.purchase_invoice_id, m.bill_primary_name, i.description, m.journal_id 
//		from ".TABLE_JOURNAL_MAIN." m inner join ".TABLE_JOURNAL_ITEM." i on m.id = i.ref_id
//		where i.gl_account = '$gl_account' and i.id in (".implode(',', $cleared_items).")";
  	$sql = "select i.id, m.post_date, i.debit_amount, i.credit_amount, m.purchase_invoice_id, m.bill_primary_name, i.description, m.journal_id 
		from ".TABLE_JOURNAL_MAIN." m inner join ".TABLE_JOURNAL_ITEM." i on m.id = i.ref_id
		where i.gl_account = '$gl_account' and i.reconciled =$period";
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  if (isset($bank_list[$result->fields['id']])) { // record exists, mark as cleared (shouldn't happen)
		$bank_list[$result->fields['id']]['cleared'] = $period;
	  } else {
		$previous_total = $bank_list[$result->fields['id']]['dep_amount'] - $bank_list[$result->fields['id']]['pmt_amount'];
		$new_total      = $previous_total + $result->fields['debit_amount'] - $result->fields['credit_amount'];
		$bank_list[$result->fields['id']] = array (
		  'post_date'  => $result->fields['post_date'],
		  'reference'  => ($result->fields['journal_id'] == 19)? $result->fields['post_date'] : $result->fields['purchase_invoice_id'],
		  'name'       => $result->fields['bill_primary_name'],
		  'description'=> $result->fields['description'],
		  'dep_amount' => ($new_total < 0) ? ''          : $new_total,
		  'pmt_amount' => ($new_total < 0) ? -$new_total : '',
		  'payment'    => ($new_total < 0) ? 1           : 0,
		  'cleared'    => $period,
		);
	  }
	  $result->MoveNext();
	} 
  }
}

// combine by reference number
$combined_list = array();
if (is_array($bank_list)) foreach ($bank_list as $id => $value) {
//	$index = ($value['payment'] ? 'p_' : 'd_') . $value['reference']; // this will separate deposits from payments with the same referenece 
	$index = $value['reference'];
	if (isset($combined_list[$index])) { // the reference already exists
		$combined_list[$index]['dep_amount'] += $value['dep_amount'];
		$combined_list[$index]['pmt_amount'] += $value['pmt_amount'];
		$combined_list[$index]['name']        = $value['payment'] ? TEXT_MULTIPLE_PAYMENTS : TEXT_MULTIPLE_DEPOSITS;
		if ( ($combined_list[$index]['cleared'] && !$value['cleared'])  ||
		    (!$combined_list[$index]['cleared'] &&  $value['cleared'])) {
		  $combined_list[$index]['cleared'] = 0; // uncheck summary box
		  $combined_list[$index]['partial'] = true; // part of the group is cleared, flag warning
		}
	} else {
		$combined_list[$index]['dep_amount']  = $value['dep_amount'];
		$combined_list[$index]['pmt_amount']  = $value['pmt_amount'];
		$combined_list[$index]['name']        = $value['name'];
		$combined_list[$index]['cleared']     = $value['cleared'];
	}
	// How about the name=description rather than source for sub-items?
	$combined_list[$index]['detail'][]  = array(
		'id'         => $id, 
		'post_date'  => $value['post_date'],
		//'name'       => $value['name'],
		//'description'=> $value['description'],
		'name'		 => $value['description'],
		'dep_amount' => $value['dep_amount'],
		'pmt_amount' => $value['pmt_amount'],
		'payment'    => $value['payment'] ? -$value['pmt_amount'] : $value['dep_amount'],
		'cleared'    => $value['cleared'],
	);
	$combined_list[$index]['post_date'] = $value['post_date'];
	$combined_list[$index]['reference'] = $value['reference'];
}

// sort by user choice for display
$sort_value = explode('-',$_REQUEST['list_order']);
switch ($_REQUEST['sf']) {
	case BNK_DEPOSIT_CREDIT: define('RECON_SORT_KEY','dep_amount'); break;
	case BNK_CHECK_PAYMENT:  define('RECON_SORT_KEY','pmt_amount'); break;
	case TEXT_DATE:          define('RECON_SORT_KEY','post_date');  break;
	default:
	case TEXT_REFERENCE:     define('RECON_SORT_KEY','reference');  break;
}
define('RECON_SORT_DESC', $_REQUEST['so']=='desc' ? true : false);
function my_sort($a, $b) {
    if ($a[RECON_SORT_KEY] == $b[RECON_SORT_KEY]) return 0;
	if (RECON_SORT_DESC) {
    	return ($a[RECON_SORT_KEY] > $b[RECON_SORT_KEY]) ? -1 : 1;
	} else {
    	return ($a[RECON_SORT_KEY] < $b[RECON_SORT_KEY]) ? -1 : 1;
	}
}
usort($combined_list, "my_sort");
// load the gl account end of period balance
$sql = "select beginning_balance + debit_amount - credit_amount as gl_balance 
	from ".TABLE_CHART_OF_ACCOUNTS_HISTORY." where account_id = '$gl_account' and period = $period";
$result = $db->Execute($sql);
$gl_balance = $currencies->format($result->fields['gl_balance']);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_BANKING_ACCOUNT_RECONCILIATION);

?>