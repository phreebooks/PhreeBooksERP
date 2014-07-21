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
//  Path: /modules/phreepos/pages/pos_mgr/pre_process.php
//
$security_level = validate_user(SECURITY_ID_POS_MGR);
define('JOURNAL_ID','19');
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
if (file_exists(DIR_FS_MODULES . 'phreepos/custom/classes/journal/journal_'.JOURNAL_ID.'.php')) { 
	require_once(DIR_FS_MODULES . 'phreepos/custom/classes/journal/journal_'.JOURNAL_ID.'.php') ; 
}else{
    require_once(DIR_FS_MODULES . 'phreepos/classes/journal/journal_'.JOURNAL_ID.'.php'); // is needed here for the defining of the class and retriving the security_token
}
$class = 'journal_'.JOURNAL_ID;
/**************   page specific initialization  *************************/
define('POPUP_FORM_TYPE','pos:rcpt');
$error      = false;
history_filter('pos_mgr');
/***************   hook for custom actions  ***************************/
$date        = gen_db_date($_REQUEST['search_date']);
$acct_period = $_REQUEST['search_period'];
$oid		 = isset($_GET['oID']) ? $_GET['oID'] : false;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/pos_mgr/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'delete':
    $id = db_prepare_input($_POST['rowSeq']);
	if ($id) {
	  $delOrd = new $class($id);
	  if ($_SESSION['admin_prefs']['restrict_period'] && $delOrd->period <> CURRENT_ACCOUNTING_PERIOD) {
	    $error = $messageStack->add(ORD_ERROR_DEL_NOT_CUR_PERIOD, 'error');
	    break;
	  }
	  // verify no item rows have been acted upon (accounts reconciliation)
	  $result = $db->Execute("select closed from " . TABLE_JOURNAL_MAIN . " where id = " . $id);
	  if ($result->fields['closed'] == '1') $error = $delOrd ->fail_message(constant('GENERAL_JOURNAL_' . $delOrd ->journal_id . '_ERROR_6'));
	  if (!$error) {	
	    // *************** START TRANSACTION *************************
	    $db->transStart();
	    if (!$delOrd->unPost('delete')) {
	      $error = $messageStack->add(GL_ERROR_NO_POST, 'error');
		  $db->transRollback();
		  break;
	    } else { // delete the payments
		  $payment_modules = load_all_methods('payment');
		  foreach ($delOrd->journal_rows as $value) {
		    if ($value['gl_type'] <> 'ttl') continue;
		    $pmt_fields  = explode(':', $value['description']);
			$pmt_method  = $pmt_fields[1]; // payment method
			$pmt_field_0 = $pmt_fields[2]; // cardholder name/reference
			$pmt_field_1 = $pmt_fields[3]; // card number
			$pmt_field_2 = $pmt_fields[4]; // exp month
			$pmt_field_3 = $pmt_fields[5]; // exp year
			$pmt_field_4 = $pmt_fields[6]; // cvv2
			if (method_exists($$pmt_method, 'refund')) {
		      $result = $$pmt_method->refund($value['debit_amount'], $reference, $pmt_field_0, $pmt_field_1);
		    } else {
			  $messageStack->add(sprintf('The payment method (%s) was not refunded with the processor. The refund in the amount of %s needs to be credited with the processor manually.', $pmt_method, $currencies->format_full($value['debit_amount'])), 'caution');
			}
	      }
		  $db->transCommit();
	    }
	    // *************** END TRANSACTION *************************
	  }
	  if (DEBUG) $messageStack->write_debug();
	  if (!$error) {
	    gen_add_audit_log(TEXT_JID_ENTRY, JOURNAL_ID==19 ? BOX_CUSTOMER_DEPOSITS: BOX_VENDOR_DEPOSITS . ' - ' . TEXT_DELETE, $delOrd->purchase_invoice_id, $delOrd->total_amount);
	    gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	  }
	} else {
	  $messageStack->add(GL_ERROR_NEVER_POSTED, 'error');
	}
    break;
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}
/*****************   prepare to display templates  *************************/
// build the list header
if (!isset($_REQUEST['sf'])) $_REQUEST['sf'] = 'post_date'; 
if (!isset($_REQUEST['so'])) $_REQUEST['so'] = 'desc';// default to descending by postdate

if (ENABLE_MULTI_CURRENCY){
	$heading_array = array(
	  'post_date'           => TEXT_DATE,
	  'purchase_invoice_id' => TEXT_INVOICE,
	  'total_amount'        => TEXT_AMOUNT,
	  'new_total_amount'    => TEXT_AMOUNT_ORIGINAL_CURRENCY,
	  'bill_primary_name'   => GEN_PRIMARY_NAME,
	);
}else{
	$heading_array = array(
	  'post_date'           => TEXT_DATE,
	  'purchase_invoice_id' => TEXT_INVOICE,
	  'total_amount'        => TEXT_AMOUNT,
	  'bill_primary_name'   => GEN_PRIMARY_NAME,
	);
}
$result      = html_heading_bar($heading_array);
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];
// build the list for the page selected
if (!$date == false){
	$period_filter = (" and post_date = '$date'");
	$acct_period   = '';
}else{
	if ($acct_period == false) $acct_period = CURRENT_ACCOUNTING_PERIOD;
	$period_filter = ($acct_period == 'all') ? '' : (' and period = ' . $acct_period);
	$date = '';
}
if ($oid == true){
	$search = " and id = $oid";
	$period_filter = null;
}else if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
	$search_fields = array('bill_primary_name', 'purchase_invoice_id', 'purch_order_id', 'bill_postal_code', 'ship_primary_name', 'total_amount');
	// hook for inserting new search fields to the query criteria.
  	if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  	$search = ' and (' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
} else {
  	$search = '';
}
$field_list = array('id', 'post_date', 'shipper_code', 'purchase_invoice_id', 'total_amount', 'bill_primary_name', 'journal_id', 'currencies_code', 'currencies_value','total_amount as new_total_amount');
// hook to add new fields to the query return results
if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
$query_raw = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list) . " from " . TABLE_JOURNAL_MAIN . " 
		where journal_id in (19,21) $period_filter $search order by $disp_order, purchase_invoice_id DESC";
$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
// the splitPageResults should be run directly after the query that contains SQL_CALC_FOUND_ROWS
$query_split  = new splitPageResults($_REQUEST['list'], '');
if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
	$_REQUEST['list'] = $query_split->current_page_number;
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
	$query_split  = new splitPageResults($_REQUEST['list'], '');
}
history_save('pos_mgr');

$cal_date = array(
  'name'      => 'searchdate',
  'form'      => 'pos_mgr',
  'fieldname' => 'search_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($date) ? gen_locale_date($date): '',
  'params'    => array('align' => 'left'),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_POS_MGR);

?>