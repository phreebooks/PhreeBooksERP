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
//  Path: /modules/phreebooks/pages/bills/pre_process.php
//

/**************   Check user security   *****************************/
$jID  = (int)$_GET['jID'];
$type = isset($_GET['type']) ? $_GET['type'] : $_POST['type'];

switch ($jID) {
  case 18:	// Cash Receipts Journal
	define('JOURNAL_ID',18);
	$security_token = ($type == 'v') ? SECURITY_ID_VENDOR_RECEIPTS : SECURITY_ID_CUSTOMER_RECEIPTS;
	break;
  case 20:	// Cash Disbursements Journal
	define('JOURNAL_ID',20);
	$security_token = ($type == 'c') ? SECURITY_ID_CUSTOMER_PAYMENTS : SECURITY_ID_PAY_BILLS;
	break;
}
$security_level = validate_user($security_token);
/**************  include page specific files    *********************/
gen_pull_language('contacts');
gen_pull_language('payment');
require_once(DIR_FS_MODULES . 'payment/defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'classes/gen_ledger.php');
require_once(DIR_FS_WORKING . 'classes/banking.php');
/**************   page specific initialization  *************************/
// check to see if we need to make a payment for a specific order
$oID               = isset($_GET['oID']) ? (int)$_GET['oID'] : false;
$post_date         = ($_POST['post_date']) ? gen_db_date($_POST['post_date']) : date('Y-m-d', time());
$period            = gen_calculate_period($post_date);
if (!$period) { // bad post_date was submitted
  $_REQUEST['action']    = '';
  $post_date = date('Y-m-d');
  $period    = 0;
}
$gl_acct_id        = ($_POST['gl_acct_id']) ? db_prepare_input($_POST['gl_acct_id']) : AP_PURCHASE_INVOICE_ACCOUNT;
$post_success      = false;
$error             = false;
$payment_modules   = array();

switch (JOURNAL_ID) {
  case 18:	// Cash Receipts Journal
	define('GL_TYPE','pmt');
	define('POPUP_FORM_TYPE','bnk:rcpt');
	define('AUDIT_LOG_DESC',ORD_TEXT_18_WINDOW_TITLE);
	define('AUDIT_LOG_DEL_DESC',ORD_TEXT_18_WINDOW_TITLE . '-' . TEXT_DELETE);
	$account_type = ($type == 'v') ? 'v' : 'c';
	$payment_modules = load_all_methods('payment');
	foreach ($payment_modules as $pmt_class) {
	  $class  = $pmt_class['id'];
	  $$class = new $class;
	}
	break;
  case 20:	// Cash Disbursements Journal
	define('GL_TYPE','chk');
	define('POPUP_FORM_TYPE','bnk:chk');
	define('AUDIT_LOG_DESC',ORD_TEXT_20_WINDOW_TITLE);
	define('AUDIT_LOG_DEL_DESC',ORD_TEXT_20_WINDOW_TITLE . '-' . TEXT_DELETE);
	$account_type = ($type == 'c') ? 'c' : 'v';
	break;
  default: // this should never happen
	$messageStack->add('No valid journal id found (module bills), Journal ID needs to be passed to this script to identify the action', 'error');
	gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
}

$order  = new banking();
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/bills/extra_actions.php';
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
	$order->bill_primary_name   = $_POST['bill_primary_name'] <> GEN_PRIMARY_NAME ? db_prepare_input($_POST['bill_primary_name']) : '';
	$order->bill_contact        = $_POST['bill_contact'] <> GEN_CONTACT ? db_prepare_input($_POST['bill_contact']) : '';
	$order->bill_address1       = $_POST['bill_address1'] <> GEN_ADDRESS1 ? db_prepare_input($_POST['bill_address1']) : '';
	$order->bill_address2       = $_POST['bill_address2'] <> GEN_ADDRESS2 ? db_prepare_input($_POST['bill_address2']) : '';
	$order->bill_city_town      = $_POST['bill_city_town'] <> GEN_CITY_TOWN ? db_prepare_input($_POST['bill_city_town']) : '';
	$order->bill_state_province = $_POST['bill_state_province'] <> GEN_STATE_PROVINCE ? db_prepare_input($_POST['bill_state_province']) : '';
	$order->bill_postal_code    = $_POST['bill_postal_code'] <> GEN_POSTAL_CODE ? db_prepare_input($_POST['bill_postal_code']) : '';
	$order->bill_country_code   = db_prepare_input($_POST['bill_country_code']);
	$order->bill_email          = db_prepare_input($_POST['bill_email']);

	// load journal main data
	$order->id                  = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	$order->admin_id            = $_SESSION['admin_id'];
	$order->rep_id              = db_prepare_input($_POST['rep_id']);
	$order->journal_id          = JOURNAL_ID;
	$order->post_date           = $post_date;
	$order->period              = $period;
	if (!$order->period) break;	// bad post_date was submitted
	$order->store_id            = db_prepare_input($_POST['store_id']);
	if ($order->store_id == '') $order->store_id = 0;
	$order->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);	// PhreeBooks order/invoice ID
	$order->shipper_code        = db_prepare_input($_POST['shipper_code']);  // store payment method in shipper_code field
	$order->purch_order_id      = db_prepare_input($_POST['purch_order_id']);  // customer PO/Ref number
	$order->description         = sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_' . JOURNAL_ID . '_' . strtoupper($type) . '_WINDOW_TITLE'));

	$order->total_amount        = $currencies->clean_value(db_prepare_input($_POST['total']), DEFAULT_CURRENCY);
	$order->gl_acct_id          = $gl_acct_id;
	$order->gl_disc_acct_id     = db_prepare_input($_POST['gl_disc_acct_id']);
	$order->payment_id          = db_prepare_input($_POST['payment_id']);
	$order->save_payment        = isset($_POST['save_payment']) ? true : false;

	// load item row data
	$x = 1;
	while (isset($_POST['id_' . $x])) { // while there are invoice rows to read in
	  if (isset($_POST['pay_' . $x])) {
		$order->item_rows[] = array(
		  'id'      => db_prepare_input($_POST['id_' . $x]),
		  'gl_type' => GL_TYPE,
		  'amt'     => $currencies->clean_value(db_prepare_input($_POST['amt_' . $x])),
		  'desc'    => db_prepare_input($_POST['desc_' . $x]),
		  'dscnt'   => $currencies->clean_value(db_prepare_input($_POST['dscnt_' . $x])),
		  'total'   => $currencies->clean_value(db_prepare_input($_POST['total_' . $x])),
		  'inv'     => db_prepare_input($_POST['inv_' . $x]),
		  'prcnt'   => db_prepare_input($_POST['prcnt_' . $x]),
		  'early'   => db_prepare_input($_POST['early_' . $x]),
		  'due'     => db_prepare_input($_POST['due_' . $x]),
		  'pay'     => isset($_POST['pay_' . $x]) ? true : false,
		  'acct'    => db_prepare_input($_POST['acct_' . $x]),
		);
	  }
	  $x++;
	}

	// error check input
	if (!$order->bill_acct_id) { // no account was selected, error
	  $contact_type = $type=='c' ? TEXT_LC_CUSTOMER : TEXT_LC_VENDOR;
	  $error = $messageStack->add(sprintf(ERROR_NO_CONTACT_SELECTED, $contact_type, $contact_type, ORD_ADD_UPDATE), 'error');
	}
	if (!$order->item_rows) $error = $messageStack->add(GL_ERROR_NO_ITEMS, 'error');
	// check to make sure the payment method is valid
	if (JOURNAL_ID == 18) {	
	  $payment_module = $order->shipper_code;
	  $processor = new $payment_module;
	  if ($processor->pre_confirmation_check()) $error = true;	
	}

/* This has been commented out to allow customer refunds (negative invoices)
	if ($order->total_amount < 0) $error = $messageStack->add(TEXT_TOTAL_LESS_THAN_ZERO,'error');
*/
	// post the receipt/payment
	if (!$error && $post_success = $order->post_ordr($_REQUEST['action'])) {	// Post the order class to the db
	  if ($_REQUEST['action'] == 'save') {
		gen_add_audit_log(AUDIT_LOG_DESC, $order->purchase_invoice_id, $order->total_amount);
		if (DEBUG) $messageStack->write_debug();
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	  } // else print or print_update, fall through and load javascript to call form_popup and clear form
	  $print_record_id = $order->id; // save id for printing
	  $order  = new banking(); // reset all values
	} else { // else there was a post error, display and re-display form
	  $error = $messageStack->add(GL_ERROR_NO_POST, 'error');
	  if (DEBUG) $messageStack->write_debug();
	  $order = new objectInfo($_POST);
	  $order->post_date = gen_db_date($_POST['post_date']); // fix the date to original format
	  $order->id = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	}
	break;

  case 'delete':
	validate_security($security_level, 4);
  	$id = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing purchase/receive
	if ($id) {
		$delOrd = new banking();
		$delOrd->journal($id); // load the posted record based on the id submitted
		if ($delOrd->delete_payment()) {
			gen_add_audit_log(AUDIT_LOG_DEL_DESC, $order->purchase_invoice_id, $order->total_amount);
			if (DEBUG) $messageStack->write_debug();
			gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		}
	} else {
		$messageStack->add(GL_ERROR_NEVER_POSTED, 'error');
	}
	$messageStack->add(GL_ERROR_NO_DELETE, 'error');
	// if we are here, there was an error, reload page
	$order = new objectInfo($_POST);
	$order->post_date = gen_db_date($_POST['post_date']); // fix the date to original format
	break;

  case 'pmt': // for opening a sales/invoice directly from payment (POS like)
    // fetch the journal_main information
    $sql = "select id, shipper_code, bill_acct_id, bill_address_id, bill_primary_name, bill_contact, bill_address1, 
		bill_address2, bill_city_town, bill_state_province, bill_postal_code, bill_country_code, bill_email, 
		post_date, terms, gl_acct_id, purchase_invoice_id, total_amount from " . TABLE_JOURNAL_MAIN . " 
		where id = " . $oID;
	$result = $db->Execute($sql);
	$account_id = $db->Execute("select short_name from " . TABLE_CONTACTS . " where id = " . $result->fields['bill_acct_id']);
	$due_dates = calculate_terms_due_dates($result->fields['post_date'], $result->fields['terms'], 'AR');
	$pre_paid  = fetch_partially_paid($oID);

	$order->bill_acct_id        = $result->fields['bill_acct_id'];
	$order->bill_primary_name   = $result->fields['bill_primary_name'];
	$order->bill_contact        = $result->fields['bill_contact'];
	$order->bill_address1       = $result->fields['bill_address1'];
	$order->bill_address2       = $result->fields['bill_address2'];
	$order->bill_city_town      = $result->fields['bill_city_town'];
	$order->bill_state_province = $result->fields['bill_state_province'];
	$order->bill_postal_code    = $result->fields['bill_postal_code'];
	$order->bill_country_code   = $result->fields['bill_country_code'];
	$order->bill_email          = $result->fields['bill_email'];
    $order->id_1                = $result->fields['id'];
    $order->inv_1               = $result->fields['purchase_invoice_id'];
    $order->acct_1              = $result->fields['gl_acct_id'];
    $order->early_1             = $due_dates['early_date'];
    $order->due_1               = $due_dates['net_date'];
    $order->prcnt_1             = $due_dates['discount'];
    $order->pay_1               = true;
    $order->amt_1               = $currencies->format($result->fields['total_amount'] - $pre_paid);
    $order->total_1             = $currencies->format($result->fields['total_amount'] - $pre_paid);
    $order->desc_1              = '';
	// reset some particular values
	$order->search = $account_id->fields['short_name']; // set the customer id in the search box
	// show the form
	$payment = $db->Execute("select description from " . TABLE_JOURNAL_ITEM . " 
		where ref_id = " . $oID . " and gl_type = 'ttl'");
	$temp = $payment->fields['description'];
	$temp = strpos($temp, ':') ? substr($temp, strpos($temp, ':') + 1) : '';
	$payment_fields = explode(':', $temp);
	for ($i = 0; $i < sizeof($payment_fields); $i++) {
	  $temp = $result->fields['shipper_code'] . '_field_' . $i;
	  $order->$temp = $payment_fields[$i];
	}
	break;
  case 'edit': // handled in ajax
	break;
  default:
}

/*****************   prepare to display templates  *************************/
// load the gl account beginning balance
$acct_balance = load_cash_acct_balance($post_date, $gl_acct_id, $period);
// load gl accounts
$gl_array_list = gen_coa_pull_down();
// generate address arrays for javascript
$js_arrays = gen_build_company_arrays();

$cal_bills = array(
  'name'      => 'dateOrdered',
  'form'      => 'bills_form',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($order->post_date) ? gen_locale_date($order->post_date) : date(DATE_FORMAT),
  'params'    => array('align' => 'left', 'onchange' => 'loadNewBalance();'),
);

// see if current user points to a employee for sales rep default
$result = $db->Execute("select account_id from " . TABLE_USERS . " where admin_id = " . $_SESSION['admin_id']);
$default_sales_rep = $result->fields['account_id'] ? $result->fields['account_id'] : '0';

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', constant('ORD_TEXT_' . JOURNAL_ID . '_' . strtoupper($type) . '_WINDOW_TITLE'));

?>