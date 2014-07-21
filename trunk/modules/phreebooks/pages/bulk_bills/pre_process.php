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
//  Path: /modules/phreebooks/pages/bulk_bills/pre_process.php
//
$security_level = validate_user(SECURITY_ID_SELECT_PAYMENT);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'classes/gen_ledger.php');
require_once(DIR_FS_WORKING . 'classes/banking.php');
/**************   page specific initialization  *************************/
define('JOURNAL_ID',20);
define('GL_TYPE','chk');
define('POPUP_FORM_TYPE','bnk:chk');
define('AUDIT_LOG_DESC',ORD_TEXT_20_WINDOW_TITLE);
$post_success      = false;
$error             = false;
$post_date         = isset($_REQUEST['post_date']) ? gen_db_date($_REQUEST['post_date']) : date('Y-m-d', time());
$_GET['post_date'] = $post_date;
$period = gen_calculate_period($post_date);
if (!$period) { // bad post_date was submitted
  $_REQUEST['action'] = '';
  $post_date = date('Y-m-d', time());
  $period = 0;
}
$invoice_date            = isset($_REQUEST['invoice_date'])  ?   gen_db_date($_REQUEST['invoice_date'])   : date('Y-m-d', time());
$_GET['invoice_date']    = $invoice_date;
$discount_date           = isset($_REQUEST['discount_date']) ?   gen_db_date($_REQUEST['discount_date'])  : date('Y-m-d', time());
$_GET['discount_date']   = $discount_date;
$gl_acct_id              = isset($_REQUEST['gl_acct_id']) ?      db_prepare_input($_REQUEST['gl_acct_id'])      : AP_PURCHASE_INVOICE_ACCOUNT;
$_GET['gl_acct_id']      = $gl_acct_id;
$gl_disc_acct_id         = isset($_REQUEST['gl_disc_acct_id']) ? db_prepare_input($_REQUEST['gl_disc_acct_id']) : AP_DISCOUNT_PURCHASE_ACCOUNT;
$_GET['gl_disc_acct_id'] = $gl_disc_acct_id;
$purch_order_id          = db_prepare_input($_POST['purch_order_id']); // reference text
$purchase_invoice_id     = db_prepare_input($_POST['purchase_invoice_id']);	// PhreeBooks starting check number
if (!$purchase_invoice_id) {
  $result = $db->Execute("select next_check_num from " . TABLE_CURRENT_STATUS);
  $purchase_invoice_id = $result->fields['next_check_num'];
}
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/bills/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'print':
	validate_security($security_level, 2);
  	// read the input data, place into array
	$payment_list = array();
	for ($i=1; $i<count($_POST); $i++) {
	  if (!isset($_POST['id_' . $i])) break; // we're done
	  if (isset($_POST['pay_' . $i])) {
	    $payment_list[$_POST['bill_acct_id_' . $i]][] = array(
		  'id'    => $_POST['id_' . $i],
		  'amt'   => $currencies->clean_value($_POST['amt_' . $i]),
		  'desc'  => $_POST['desc_' . $i],
		  'dscnt' => $currencies->clean_value($_POST['dscnt_' . $i]),
		  'total' => $currencies->clean_value($_POST['total_' . $i]),
		  'acct'  => $currencies->clean_value($_POST['acct_' . $i]),
		  'inv'   => $_POST['inv_' . $i],
		);
	  }
	}
	// error check input
	if (!count($payment_list)) {
		$messageStack->add(GL_ERROR_NO_ITEMS, 'error');
		$error = true;
		break;
	}
	// ***************************** START TRANSACTION *******************************
	$first_payment_ref = $purchase_invoice_id; // first check number, needed for printing
	$db->transStart();
	// post each payment by vendor (save journal record id)
	foreach ($payment_list as $account => $values) {
	  $order = new banking();
	  // load journal main data
	  $order->id = '';
	  $order->journal_id          = JOURNAL_ID;
	  $order->post_date           = $post_date;
	  $order->period              = $period;
	  $order->admin_id            = $_SESSION['admin_id'];
	  $order->purchase_invoice_id = $purchase_invoice_id;	// PhreeBooks payment number
	  $order->shipper_code        = '';
	  $order->purch_order_id      = $purch_order_id;
	  $order->description         = constant('GEN_ADM_TOOLS_J' . JOURNAL_ID);
	  $order->gl_acct_id          = $gl_acct_id;
	  $order->gl_disc_acct_id     = $gl_disc_acct_id;

	  // retrieve billing information
	  $result = $db->Execute("select * from " . TABLE_ADDRESS_BOOK . " where type = 'vm' and ref_id = " . $account);
	  $order->bill_acct_id        = $account;
	  $order->bill_address_id     = $result->fields['address_id'];
	  $order->bill_primary_name   = $result->fields['primary_name'];
	  $order->bill_contact        = $result->fields['contact'];
	  $order->bill_address1       = $result->fields['address1'];
	  $order->bill_address2       = $result->fields['address2'];
	  $order->bill_city_town      = $result->fields['city_town'];
	  $order->bill_state_province = $result->fields['state_province'];
	  $order->bill_postal_code    = $result->fields['postal_code'];
	  $order->bill_country_code   = $result->fields['country_code'];

	  // load item row data
	  $payment_total = 0;
	  for ($x = 0; $x < count($values); $x++) {
		$order->item_rows[] = array(
		  'id'      => $values[$x]['id'],
		  'amt'     => $values[$x]['amt'],
		  'gl_type' => GL_TYPE,
		  'desc'    => $values[$x]['desc'],
		  'acct'    => $values[$x]['acct'],
		  'inv'     => $values[$x]['inv'],
		  'dscnt'   => $values[$x]['dscnt'],
		  'total'   => $values[$x]['total'],
		);
	    $payment_total += $values[$x]['total'];
	  }

	  // Make sure there is a positive balance to pay
	  $order->total_amount = $payment_total;
	  if ($order->total_amount <= 0) {
		$messageStack->add(sprintf(BNK_BULK_PAY_NOT_POSITIVE, $order->bill_primary_name), 'caution');
		continue;
	  }

	  // post the payment
	  if ($post_success = $order->bulk_pay()) {	// Post the order class to the db
		gen_add_audit_log(AUDIT_LOG_DESC, $order->purchase_invoice_id, $order->total_amount);
	  } else { // else there was a post error, display and re-display form
	  	$error = true;
		$messageStack->add(GL_ERROR_NO_POST, 'error');
		break; // exit foreach loop
	  }
	  $last_payment_ref = $purchase_invoice_id;
      $purchase_invoice_id++; // next check number
	}
	$print_crit = array(
	  'min' => $first_payment_ref,
	  'max' => $last_payment_ref,
	);

	if ($error) {
	  $db->transRollback();
	} else {
	  $db->transCommit();	// finished successfully
	}
	// ***************************** END TRANSACTION *******************************
	if (DEBUG) $messageStack->write_debug();
	// send to printer (range of check numbers)
	break;
  case 'search':
  default:
}

/*****************   prepare to display templates  *************************/
// load the gl account beginning balance
$acct_balance = load_cash_acct_balance($post_date, $gl_acct_id, $period);
// load gl accounts
$gl_array_list = gen_coa_pull_down();
// build the list header
$heading_array = array(
  'post_date'           => BNK_INVOICE_DATE,
  'bill_primary_name'   => BNK_VENDOR_NAME,
  'purchase_invoice_id' => BNK_INVOICE_NUM,
  'total_amount'        => BNK_AMOUNT_DUE,
);
$result      = html_heading_bar($heading_array, array(TEXT_NOTES, BNK_DUE_DATE, TEXT_DISCOUNT, BNK_20_AMOUNT_PAID, TEXT_PAY));
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];
if (!$disp_order) $disp_order = 'post_date';
// build the list for the page selected
$field_list = array('m.id', 'm.journal_id', 'm.post_date', 'm.total_amount', 'm.terms', 'm.gl_acct_id',  
	'm.purchase_invoice_id', 'm.purch_order_id', 'm.bill_acct_id', 'm.bill_primary_name', 'm.waiting');
// hook to add new fields to the query return results
if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
$query_raw = "select " . implode(', ', $field_list) . " 
	from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_CONTACTS . " a on m.bill_acct_id = a.id 
	where a.type = 'v' and m.journal_id in (6, 7) and m.closed = '0' 
	order by $disp_order, post_date";
$query_result = $db->Execute($query_raw);
$cal_bills0   = array(
  'name'      => 'datePosted',
  'form'      => 'bulk_bills',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($post_date),
  'params'    => array('align' => 'left', 'onchange' => 'loadNewBalance();'),
);
$cal_bills1 = array(
  'name'      => 'dateInvoice',
  'form'      => 'bulk_bills',
  'fieldname' => 'invoice_date',
  'imagename' => 'btn_date_2',
  'default'   => gen_locale_date($invoice_date),
  'params'    => array('align' => 'left'),
);
$cal_bills2 = array(
  'name'      => 'dateDiscount',
  'form'      => 'bulk_bills',
  'fieldname' => 'discount_date',
  'imagename' => 'btn_date_3',
  'default'   => gen_locale_date($discount_date),
  'params'    => array('align' => 'left'),
);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', ORD_TEXT_20_V_WINDOW_TITLE);

?>