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
//  Path: /modules/phreepos/pages/deposit/pre_process.php
//

$security_level = \core\classes\user::validate(SECURITY_ID_CUSTOMER_DEPOSITS);
$type           = $_GET['type'];
switch ($type) {
  case 'c': // customers
	define('JOURNAL_ID', 18);
	define('DEF_DEP_GL_ACCT',AR_DEF_DEP_LIAB_ACCT);
	define('PAGE_TITLE', TEXT_CUSTOMER_DEPOSITS);
    break;
  case 'v': // vendors
	define('JOURNAL_ID', 20);
	define('DEF_DEP_GL_ACCT',AP_DEF_DEP_LIAB_ACCT);
	define('PAGE_TITLE', TEXT_VENDOR_DEPOSITS);
    break;
  default:
    throw new \core\classes\userException('Illegal Access type');
}
/************** include page specific files *********************/
gen_pull_language('phreebooks');
gen_pull_language('contacts');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
/**************   page specific initialization  *************************/
$post_success     = false;
$default_dep_acct = JOURNAL_ID == 18 ? AR_DEF_DEPOSIT_ACCT : AP_DEF_DEPOSIT_ACCT;
$order            = new \phreebooks\classes\banking;
$gl_acct_id       = isset($_POST['gl_acct_id'])          ? db_prepare_input($_POST['gl_acct_id'])          : $order->gl_acct_id;
$next_inv_ref     = isset($_POST['purchase_invoice_id']) ? db_prepare_input($_POST['purchase_invoice_id']) : $order->purchase_invoice_id;
$post_date        = isset($_POST['post_date'])           ? gen_db_date($_POST['post_date'])                : date('Y-m-d');
$period           = gen_calculate_period($post_date ,true);
if (!$period) { // bad post_date was submitted
  	$_REQUEST['action']    = '';
  	$post_date = date('Y-m-d');
  	$period    = 0;
}
$order->gl_acct_id = $gl_acct_id;
$order->acct_1     = DEF_DEP_GL_ACCT;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/deposit/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  case 'print':
	\core\classes\user::validate_security($security_level, 2);
  	// create and retrieve customer account (defaults also)
	$order->bill_short_name     = db_prepare_input($_POST['search']);
	$order->bill_acct_id        = db_prepare_input($_POST['bill_acct_id']);
	$order->bill_address_id     = db_prepare_input($_POST['bill_address_id']);
	$order->bill_primary_name   = $_POST['bill_primary_name']   <> TEXT_NAME_OR_COMPANY   ? db_prepare_input($_POST['bill_primary_name'])   : '';
	$order->bill_contact        = $_POST['bill_contact']        <> TEXT_ATTENTION        ? db_prepare_input($_POST['bill_contact'])        : '';
	$order->bill_address1       = $_POST['bill_address1']       <> TEXT_ADDRESS1       ? db_prepare_input($_POST['bill_address1'])       : '';
	$order->bill_address2       = $_POST['bill_address2']       <> TEXT_ADDRESS2       ? db_prepare_input($_POST['bill_address2'])       : '';
	$order->bill_city_town      = $_POST['bill_city_town']      <> TEXT_CITY_TOWN      ? db_prepare_input($_POST['bill_city_town'])      : '';
	$order->bill_state_province = $_POST['bill_state_province'] <> TEXT_STATE_PROVINCE ? db_prepare_input($_POST['bill_state_province']) : '';
	$order->bill_postal_code    = $_POST['bill_postal_code']    <> TEXT_POSTAL_CODE    ? db_prepare_input($_POST['bill_postal_code'])    : '';
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
	$order->description         = sprintf(TEXT_ARGS_ENTRY, JOURNAL_ID==18 ? TEXT_CUSTOMER_DEPOSITS: TEXT_VENDOR_DEPOSITS);
	$order->total_amount        = $admin->currencies->clean_value(db_prepare_input($_POST['total']), DEFAULT_CURRENCY);
	$order->gl_acct_id          = $gl_acct_id;
	$order->payment_id          = db_prepare_input($_POST['payment_id']);
	$order->save_payment        = isset($_POST['save_payment']) ? true : false;
	$order->waiting				= 1;
	// load item row data
	$order->item_rows[] = array(
	  'id'        => db_prepare_input($_POST['id_1']),
	  'gl_type'   => $order->gl_type,
	  'pstd'      => '1',
	  'sku'       => '',
	  'desc'      => db_prepare_input($_POST['desc_1']),
	  'price'     => $admin->currencies->clean_value(db_prepare_input($_POST['total_1'])),
	  'full'      => $full_price,
	  'acct'      => db_prepare_input($_POST['acct_1']),
	  'total'     => $admin->currencies->clean_value(db_prepare_input($_POST['total_1'])),
	);
	// load the payments
	switch (JOURNAL_ID) {
	  case 18:
	  	$pmt_meth = db_prepare_input($_POST['shipper_code']);
	    $admin->classes['payment']->methods[$pmt_meth]->pre_confirmation_check();
		$pmt_amt  = $admin->currencies->clean_value(db_prepare_input($_POST['pmt_' . $x]), $order->currencies_code) / $order->currencies_value;
		$tot_paid += $pmt_amt;
		$order->pmt_rows[] = array(
		  'meth' => $pmt_meth,
		  'pmt'  => $order->total_amount,
		  'desc' => $journal_types_list[18]['text'] . '-' . TEXT_TOTAL . ':' . $admin->classes['payment']->methods[$pmt_meth]->payment_fields,
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
		  'desc' => $journal_types_list[20]['text'] . '-' . TEXT_TOTAL,
		  'pmt'  => $order->total_amount,
		);
	    break;
	}
	// error check input
	if (!$order->period)                throw new \core\classes\userException("Period isn't set");
	if (!$order->bill_acct_id)          throw new \core\classes\userException(sprintf(ERROR_NO_CONTACT_SELECTED, strtolower (TEXT_CUSTOMER), strtolower (TEXT_CUSTOMER), TEXT_ADD_UPDATE));
	if (!$order->item_rows[0]['total']) throw new \core\classes\userException(GL_ERROR_NO_ITEMS);
	// post the receipt/payment
	if (!$error && $post_success = $order->post_ordr($_REQUEST['action'])) {
	  // now create a credit memo to show a credit on customers account
	  $order                      = new \phreebooks\classes\orders();
	  $order->bill_short_name     = db_prepare_input($_POST['search']);
	  $order->bill_acct_id        = db_prepare_input($_POST['bill_acct_id']);
	  $order->bill_address_id     = db_prepare_input($_POST['bill_address_id']);
	  $order->bill_primary_name   = $_POST['bill_primary_name']   <> TEXT_NAME_OR_COMPANY   ? db_prepare_input($_POST['bill_primary_name'])   : '';
	  $order->bill_contact        = $_POST['bill_contact']        <> TEXT_ATTENTION        ? db_prepare_input($_POST['bill_contact'])        : '';
	  $order->bill_address1       = $_POST['bill_address1']       <> TEXT_ADDRESS1       ? db_prepare_input($_POST['bill_address1'])       : '';
	  $order->bill_address2       = $_POST['bill_address2']       <> TEXT_ADDRESS2       ? db_prepare_input($_POST['bill_address2'])       : '';
	  $order->bill_city_town      = $_POST['bill_city_town']      <> TEXT_CITY_TOWN      ? db_prepare_input($_POST['bill_city_town'])      : '';
	  $order->bill_state_province = $_POST['bill_state_province'] <> TEXT_STATE_PROVINCE ? db_prepare_input($_POST['bill_state_province']) : '';
	  $order->bill_postal_code    = $_POST['bill_postal_code']    <> TEXT_POSTAL_CODE    ? db_prepare_input($_POST['bill_postal_code'])    : '';
	  $order->bill_country_code   = db_prepare_input($_POST['bill_country_code']);
	  // load journal main data
	  $order->id                  = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	  $order->journal_id          = (JOURNAL_ID == 18) ? 13 : 7;  // credit memo
	  $order->gl_type             = (JOURNAL_ID == 18) ? 'sos' : 'por';
	  $order->post_date           = $post_date;
	  $order->period              = $period;
	  $order->admin_id            = $_SESSION['admin_id'];
	  $order->purch_order_id      = db_prepare_input($_POST['purch_order_id']);  // customer PO/Ref number
	  $order->description         = sprintf(TEXT_ARGS_ENTRY, $journal_types_list[$order->journal_id]['text']);
	  $order->total_amount        = $admin->currencies->clean_value(db_prepare_input($_POST['total']), DEFAULT_CURRENCY);
	  $order->gl_acct_id          = (JOURNAL_ID == 18) ? AR_DEFAULT_GL_ACCT : AP_DEFAULT_PURCHASE_ACCOUNT;
	  $order->item_rows[0] = array(
		'pstd'  => '1',
		'id'    => '',
		'desc'  => db_prepare_input($_POST['desc_1']),
		'total' => $admin->currencies->clean_value(db_prepare_input($_POST['total_1'])),
		'acct'  => db_prepare_input($_POST['acct_1']),
	  );
	  $post_credit = $order->post_ordr($_REQUEST['action']);
	  $messageStack->add("order id is now: $order->id", 'caution');
	  $oID = $order->id; // need to fetch id for printing
	  if (!$post_credit) {
		$order            = new \core\classes\objectInfo($_POST);
		$order->post_date = gen_db_date($_POST['post_date']); // fix the date to original format
		$order->id        = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	  }
	  gen_add_audit_log(PAGE_TITLE, $order->purchase_invoice_id, $order->total_amount);
	  if (DEBUG) $messageStack->write_debug();
	  if ($_REQUEST['action'] == 'save') {
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	  } // else print or print_update, fall through and load javascript to call form_popup and clear form
	} else { // else there was a post error, display and re-display form
	  $order = new \core\classes\objectInfo($_POST);
	  $order->post_date = gen_db_date($_POST['post_date']); // fix the date to original format
	  $order->id = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
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