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
//  Path: /modules/phreepos/pages/deposit/pre_process.php
//

$security_level = validate_user(SECURITY_ID_CUSTOMER_DEPOSITS);
$type           = $_GET['type'];
switch ($type) {
  case 'c': // customers
	define('JOURNAL_ID', 18);
	define('GL_TYPE','pmt');
	define('POPUP_FORM_TYPE','cust:cm');
	define('AUDIT_LOG_DESC',BOX_CUSTOMER_DEPOSITS);
	define('DEF_DEP_GL_ACCT',AR_DEF_DEP_LIAB_ACCT);
	define('PAGE_TITLE', BOX_CUSTOMER_DEPOSITS);
    break;
  case 'v': // vendors
	define('JOURNAL_ID', 20);
	define('GL_TYPE','chk');
	define('POPUP_FORM_TYPE','bnk:chk');
	define('AUDIT_LOG_DESC',BOX_VENDOR_DEPOSITS);
	define('DEF_DEP_GL_ACCT',AP_DEF_DEP_LIAB_ACCT);
	define('PAGE_TITLE', BOX_VENDOR_DEPOSITS);
    break;
  default:
    die('Illegal Access type');
}
/************** include page specific files *********************/
gen_pull_language('phreebooks');
gen_pull_language('contacts');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/banking.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/orders.php');
//if (file_exists(DIR_FS_MODULES . 'phreepos/custom/classes/journal/journal_'.JOURNAL_ID.'.php')) { 
//	require_once(DIR_FS_MODULES . 'phreepos/custom/classes/journal/journal_'.JOURNAL_ID.'.php') ; 
//}else{
//    require_once(DIR_FS_MODULES . 'phreepos/classes/journal/journal_'.JOURNAL_ID.'.php'); // is needed here for the defining of the class and retriving the security_token
//}
//$class = 'journal_'.JOURNAL_ID;
/**************   page specific initialization  *************************/
$error            = false;
$post_success     = false;
$default_dep_acct = JOURNAL_ID == 18 ? AR_DEF_DEPOSIT_ACCT : AP_DEF_DEPOSIT_ACCT;
//$order          = new $class();
$order            = new banking;
$gl_acct_id       = isset($_POST['gl_acct_id'])          ? db_prepare_input($_POST['gl_acct_id'])          : $order->gl_acct_id;
$next_inv_ref     = isset($_POST['purchase_invoice_id']) ? db_prepare_input($_POST['purchase_invoice_id']) : $order->purchase_invoice_id;
$post_date        = isset($_POST['post_date'])           ? gen_db_date($_POST['post_date'])                : date('Y-m-d');
$period           = gen_calculate_period($post_date);
if (!$period) { // bad post_date was submitted
  $_REQUEST['action']    = '';
  $post_date = date('Y-m-d');
  $period    = 0;
}
$order->gl_acct_id = $gl_acct_id;
$order->acct_1     = DEF_DEP_GL_ACCT;
// load available payment modules, receipts only
$payment_modules   = array();
if (JOURNAL_ID == 18) {
  $payment_modules = load_all_methods('payment');
  foreach ($payment_modules as $pmt_class) {
	$class  = $pmt_class['id'];
	$$class = new $class;
  }
}
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/deposit/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  case 'print':
	validate_security($security_level, 2);
  	// create and retrieve customer account (defaults also)
	$order->bill_short_name     = db_prepare_input($_POST['search']);
	$order->bill_acct_id        = db_prepare_input($_POST['bill_acct_id']);
	$order->bill_address_id     = db_prepare_input($_POST['bill_address_id']);
	$order->bill_primary_name   = $_POST['bill_primary_name']   <> GEN_PRIMARY_NAME   ? db_prepare_input($_POST['bill_primary_name'])   : '';
	$order->bill_contact        = $_POST['bill_contact']        <> GEN_CONTACT        ? db_prepare_input($_POST['bill_contact'])        : '';
	$order->bill_address1       = $_POST['bill_address1']       <> GEN_ADDRESS1       ? db_prepare_input($_POST['bill_address1'])       : '';
	$order->bill_address2       = $_POST['bill_address2']       <> GEN_ADDRESS2       ? db_prepare_input($_POST['bill_address2'])       : '';
	$order->bill_city_town      = $_POST['bill_city_town']      <> GEN_CITY_TOWN      ? db_prepare_input($_POST['bill_city_town'])      : '';
	$order->bill_state_province = $_POST['bill_state_province'] <> GEN_STATE_PROVINCE ? db_prepare_input($_POST['bill_state_province']) : '';
	$order->bill_postal_code    = $_POST['bill_postal_code']    <> GEN_POSTAL_CODE    ? db_prepare_input($_POST['bill_postal_code'])    : '';
	$order->bill_country_code   = db_prepare_input($_POST['bill_country_code']);
	// load journal main data
	$order->id                  = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	$order->post_date           = $post_date;
	$order->period              = $period;
	$order->journal_id          = JOURNAL_ID;
	$order->admin_id            = $_SESSION['admin_id'];
	$order->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);	// PhreeBooks order/invoice ID
	$order->shipper_code        = db_prepare_input($_POST['shipper_code']);  // store payment method in shipper_code field
	$order->purch_order_id      = db_prepare_input($_POST['purch_order_id']);  // customer PO/Ref number
	$order->description         = sprintf(TEXT_JID_ENTRY, JOURNAL_ID==18 ? BOX_CUSTOMER_DEPOSITS: BOX_VENDOR_DEPOSITS);
	$order->total_amount        = $currencies->clean_value(db_prepare_input($_POST['total']), DEFAULT_CURRENCY);
	$order->gl_acct_id          = $gl_acct_id;
	$order->payment_id          = db_prepare_input($_POST['payment_id']);
	$order->save_payment        = isset($_POST['save_payment']) ? true : false;
	$order->waiting				= 1;
	// load item row data
	$order->item_rows[] = array(
	  'id'        => db_prepare_input($_POST['id_1']),
	  'gl_type'   => GL_TYPE,
	  'pstd'      => '1',
	  'sku'       => '',
	  'desc'      => db_prepare_input($_POST['desc_1']),
	  'price'     => $currencies->clean_value(db_prepare_input($_POST['total_1'])),
	  'full'      => $full_price,
	  'acct'      => db_prepare_input($_POST['acct_1']),
	  'total'     => $currencies->clean_value(db_prepare_input($_POST['total_1'])),
	);
	// load the payments
	switch (JOURNAL_ID) {
	  case 18:
	    $payment_module = $order->shipper_code; 
	    $processor      = new $payment_module;
	    if ($$payment_module->pre_confirmation_check()) $error = true;	
		$pmt_meth = db_prepare_input($_POST['shipper_code']);
		$pmt_amt  = $currencies->clean_value(db_prepare_input($_POST['pmt_' . $x]), $order->currencies_code) / $order->currencies_value;
		$tot_paid += $pmt_amt;
		$order->pmt_rows[] = array(
		  'meth' => $pmt_meth,
		  'pmt'  => $order->total_amount,
		  'desc' => GEN_ADM_TOOLS_J18 . '-' . TEXT_TOTAL . ':' . $processor->payment_fields,
		  'f0'   => db_prepare_input($_POST[$pmt_meth . '_field_0']),
		  'f1'   => db_prepare_input($_POST[$pmt_meth . '_field_1']),
		  'f2'   => db_prepare_input($_POST[$pmt_meth . '_field_2']),
		  'f3'   => db_prepare_input($_POST[$pmt_meth . '_field_3']),
		  'f4'   => db_prepare_input($_POST[$pmt_meth . '_field_4']),
		);
		$order->shipper_code = $pmt_meth;  // store last payment method in shipper_code field
	    break;
	  case 20:
		$order->pmt_rows[] = array(
		  'meth' => '',
		  'desc' => GEN_ADM_TOOLS_J20 . '-' . TEXT_TOTAL,
		  'pmt'  => $order->total_amount,
		);
	    break;
	}
	// error check input
	if (!$order->period)                break;
	if (!$order->bill_acct_id)          $error = $messageStack->add(sprintf(ERROR_NO_CONTACT_SELECTED, TEXT_LC_CUSTOMER, TEXT_LC_CUSTOMER, ORD_ADD_UPDATE), 'error');
	if (!$order->item_rows[0]['total']) $error = $messageStack->add(GL_ERROR_NO_ITEMS, 'error');
	// post the receipt/payment
	if (!$error && $post_success = $order->post_ordr($_REQUEST['action'])) {
	  // now create a credit memo to show a credit on customers account
	  $order                      = new orders();
	  $order->bill_short_name     = db_prepare_input($_POST['search']);
	  $order->bill_acct_id        = db_prepare_input($_POST['bill_acct_id']);
	  $order->bill_address_id     = db_prepare_input($_POST['bill_address_id']);
	  $order->bill_primary_name   = $_POST['bill_primary_name']   <> GEN_PRIMARY_NAME   ? db_prepare_input($_POST['bill_primary_name'])   : '';
	  $order->bill_contact        = $_POST['bill_contact']        <> GEN_CONTACT        ? db_prepare_input($_POST['bill_contact'])        : '';
	  $order->bill_address1       = $_POST['bill_address1']       <> GEN_ADDRESS1       ? db_prepare_input($_POST['bill_address1'])       : '';
	  $order->bill_address2       = $_POST['bill_address2']       <> GEN_ADDRESS2       ? db_prepare_input($_POST['bill_address2'])       : '';
	  $order->bill_city_town      = $_POST['bill_city_town']      <> GEN_CITY_TOWN      ? db_prepare_input($_POST['bill_city_town'])      : '';
	  $order->bill_state_province = $_POST['bill_state_province'] <> GEN_STATE_PROVINCE ? db_prepare_input($_POST['bill_state_province']) : '';
	  $order->bill_postal_code    = $_POST['bill_postal_code']    <> GEN_POSTAL_CODE    ? db_prepare_input($_POST['bill_postal_code'])    : '';
	  $order->bill_country_code   = db_prepare_input($_POST['bill_country_code']);
	  // load journal main data
	  $order->id                  = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	  $order->journal_id          = (JOURNAL_ID == 18) ? 13 : 7;  // credit memo
	  $order->gl_type             = (JOURNAL_ID == 18) ? 'sos' : 'por';
	  $order->post_date           = $post_date;
	  $order->period              = $period;
	  $order->admin_id            = $_SESSION['admin_id'];
	  $order->purch_order_id      = db_prepare_input($_POST['purch_order_id']);  // customer PO/Ref number
	  $order->description         = sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_' . $order->journal_id . '_WINDOW_TITLE'));
	  $order->total_amount        = $currencies->clean_value(db_prepare_input($_POST['total']), DEFAULT_CURRENCY);
	  $order->gl_acct_id          = (JOURNAL_ID == 18) ? AR_DEFAULT_GL_ACCT : AP_DEFAULT_PURCHASE_ACCOUNT;
	  $order->item_rows[0] = array(
		'pstd'  => '1',
		'id'    => '',
		'desc'  => db_prepare_input($_POST['desc_1']),
		'total' => $currencies->clean_value(db_prepare_input($_POST['total_1'])),
		'acct'  => db_prepare_input($_POST['acct_1']),
	  );
	  $post_credit = $order->post_ordr($_REQUEST['action']);
	  $messageStack->add("order id is now: $order->id", 'caution');
	  $oID = $order->id; // need to fetch id for printing
	  if (!$post_credit) {
		$order            = new objectInfo($_POST);
		$order->post_date = gen_db_date($_POST['post_date']); // fix the date to original format
		$order->id        = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
		$messageStack->add(GL_ERROR_NO_POST, 'error');
	  }
	  gen_add_audit_log(AUDIT_LOG_DESC, $order->purchase_invoice_id, $order->total_amount);
	  if (DEBUG) $messageStack->write_debug();
	  if ($_REQUEST['action'] == 'save') {
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	  } // else print or print_update, fall through and load javascript to call form_popup and clear form
	} else { // else there was a post error, display and re-display form
	  $order = new objectInfo($_POST);
	  $order->post_date = gen_db_date($_POST['post_date']); // fix the date to original format
	  $order->id = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	  $messageStack->add(GL_ERROR_NO_POST, 'error');
	}
	break;
  default:
}

/*****************   prepare to display templates  *************************/
$acct_balance  = load_cash_acct_balance($post_date, $gl_acct_id, $period);
$gl_array_list = gen_coa_pull_down();
$js_gl_array   = 'var js_gl_array = new Array(' . count($gl_array_list) . ');' . chr(10);
for ($i = 0; $i < count($gl_array_list); $i++) {
  $js_gl_array .= 'js_gl_array[' . $i . '] = new dropDownData("' . $gl_array_list[$i]['id'] . '", "' . $gl_array_list[$i]['text'] . '");' . chr(10);
}
$js_arrays = gen_build_company_arrays();
$cal_bills = array(
  'name'      => 'dateOrdered',
  'form'      => 'bills_deposit',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($order->post_date) ? gen_locale_date($order->post_date) : date(DATE_FORMAT),
  'params'    => array('align' => 'left'),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';

?>