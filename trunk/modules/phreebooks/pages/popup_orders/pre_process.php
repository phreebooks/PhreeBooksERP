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
//  Path: /modules/phreebooks/pages/popup_orders/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files  *********************/
gen_pull_language('contacts');
require(DIR_FS_WORKING . 'functions/phreebooks.php');

/**************   page specific initialization  *************************/
define('JOURNAL_ID',(int)$_GET['jID']);

switch (JOURNAL_ID) {
	case  3:	// Purchase Quote Journal
	case  4:	// Purchase Order Journal
		define('GL_TYPE','poo');
		break;
	case  6:	// Purchase Journal
	case  7:	// Vendor Credit Memo Journal
	case 21:	// Point of Purchase Journal
		define('GL_TYPE','por');
		break;
	case  9:	// Sales Quote Journal
	case 10:	// Sales Order Journal
		define('GL_TYPE','soo');
		break;
	case 12:	// Sales/Invoice Journal
	case 13:	// Custoemr Credit Memo Journal
	case 19:	// Point of Sale (receipts)
		define('GL_TYPE','sos');
		break;
	case 18:	// Cash Receipts Journal
		define('GL_TYPE','swr');	// sale with receipt
		break;
	case 20:	// Purchases (direct pay)
		define('GL_TYPE','pwp');	// purchase with payment
		break;
	default:
		die('No valid journal id found (filename: modules/phreebooks/popup.php), Journal ID needs to be passed to this script to identify the correct procedure.');
}
history_filter('pb_pop_orders');
$date        = gen_db_date($_REQUEST['search_date']);
$acct_period = $_REQUEST['search_period'];
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_orders/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
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
$heading_array['post_date'] = TEXT_DATE;
$heading_array['purchase_invoice_id'] = constant('ORD_HEADING_NUMBER_' . JOURNAL_ID);
switch (JOURNAL_ID) {
  case  6:
  case  7: 
	$heading_array['so_po_ref_id']   = ORD_HEADING_NUMBER_4;
	$heading_array['waiting']        = ORD_WAITING;
	break;
  case 12:
  case 13: 
	$heading_array['so_po_ref_id']   = ORD_HEADING_NUMBER_10;
	$heading_array['closed']         = TEXT_PAID;
	break;
  case 19:
	$heading_array['so_po_ref_id']   = ORD_HEADING_NUMBER_10;
	$heading_array['closed']         = TEXT_CLOSED; break;
  default: 
	$heading_array['closed']         = TEXT_CLOSED;
}
$heading_array['bill_primary_name'] = in_array(JOURNAL_ID, array(12,13)) ? ORD_CUSTOMER_NAME : ORD_VENDOR_NAME;
$heading_array['total_amount']      = TEXT_AMOUNT;
$result      = html_heading_bar($heading_array, array());
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];
if ($disp_order == 'post_date') $disp_order .= ', purchase_invoice_id';
if (!$date == false){
	$period_filter = (" and post_date = '$date'");
	$acct_period   = '';
}else{
	if ($acct_period == false) $acct_period = CURRENT_ACCOUNTING_PERIOD;
	$period_filter = ($acct_period == 'all') ? '' : (' and period = ' . $acct_period);
	$date = '';
}
// build the list for the page selected
if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
  $search_fields = array('bill_primary_name', 'purchase_invoice_id', 'purch_order_id', 'store_id');
  // hook for inserting new search fields to the query criteria.
  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  $search = ' and (' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
} else {
  $search = '';
}

$field_list = array('id', 'journal_id', 'post_date', 'purchase_invoice_id', 'purch_order_id', 'so_po_ref_id', 
  'store_id', 'closed', 'waiting', 'bill_primary_name', 'total_amount', 'currencies_code', 'currencies_value');
		
// hook to add new fields to the query return results
if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);

$query_raw = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list) . " from " . TABLE_JOURNAL_MAIN . " 
  where journal_id = " . JOURNAL_ID . $period_filter . $search . " order by $disp_order";
$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
$query_split  = new splitPageResults($_REQUEST['list'], '');
if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
   	$_REQUEST['list'] = $query_split->current_page_number;
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
	$query_split  = new splitPageResults($_REQUEST['list'], '');
}

$cal_date = array(
  'name'      => 'searchdate',
  'form'      => 'pos_mgr',
  'fieldname' => 'search_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($date) ? gen_locale_date($date): '',
  'params'    => array('align' => 'left'),
);
history_save('pb_pop_orders');
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', GEN_HEADING_PLEASE_SELECT);

?>