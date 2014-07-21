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
//  Path: /modules/phreebooks/pages/popup_convert_po/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$id     = (isset($_GET['oID']) ? $_GET['oID'] : $_POST['id']);
$error  = false;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_convert_po/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	$purchase_invoice_id = $_POST['po_num'];
	$drop_ship = isset($_POST['drop_ship']) ? '1' : '0';
	require_once(DIR_FS_WORKING . 'classes/gen_ledger.php');
	define('JOURNAL_ID',4);
	define('GL_TYPE','poo');
	// Load the existing sales order
	$order = new journal($id);
	// replace some settings with the new values and re-post
	$order->id = '';
	$order->journal_id = JOURNAL_ID;
	$order->post_date = date('Y-m-d',time()); // make post date today
	$order->period = gen_calculate_period($order->post_date);
	$order->closed = '0';
	$order->gl_acct_id = AP_DEFAULT_PURCHASE_ACCOUNT;
    $order->purchase_invoice_id = $purchase_invoice_id;
	$order->admin_id = $_SESSION['admin_id'];
	$order->terminal_date = $order->post_date; // make ship date the same as post date
	$order->description = sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE'));
	$order->drop_ship = $drop_ship;

	$temp_rows = $order->journal_rows;
	$order->journal_rows = array(); // clean out the journal items rows to be re-generated
	$vendor_id = false;
	$total_amount = 0;
	for ($i = 0; $i < sizeof($temp_rows); $i++) {
	  if ($temp_rows[$i]['gl_type'] <> 'soo') continue; // remove all rows except valid order lines
	  // fetch the sku information
	  if ($temp_rows[$i]['sku']) {
	    $result = $db->Execute("select description_purchase, item_cost, account_inventory_wage, vendor_id
	      from " . TABLE_INVENTORY . " where sku = '" . $temp_rows[$i]['sku'] . "'"); 
		if ($result->fields['vendor_id'] > 0) $vendor_id = $result->fields['vendor_id']; // save preferred vendor (takes last one)
		$order->journal_rows[] = array(
		  'gl_type'      => GL_TYPE,
		  'sku'          => $temp_rows[$i]['sku'],
		  'qty'          => $temp_rows[$i]['qty'],
		  'description'  => $result->fields['description_purchase'],
		  'debit_amount' => $temp_rows[$i]['qty'] * $result->fields['item_cost'],
		  'gl_account'   => $result->fields['account_inventory_wage'],
		  'taxable'      => $temp_rows[$i]['taxable'],
		  'post_date'    => $order->post_date,
		  'date_1'       => $temp_rows[$i]['date_1'],
		);
		$total_amount += $temp_rows[$i]['qty'] * $result->fields['item_cost'];
      }
	}
	// add total row
	$order->journal_rows[] = array(
	  'gl_type'       => 'ttl',
	  'description'   => constant('ORD_TEXT_' . $order->journal_id . '_WINDOW_TITLE') . '-' . TEXT_TOTAL,
	  'credit_amount' => $total_amount,
	  'gl_account'    => AP_DEFAULT_PURCHASE_ACCOUNT,
	);
	$order->total_amount = $total_amount;

	if (!$vendor_id) {
	  $error = $messageStack->add(PB_ERROR_NO_PREFERRED_VENDOR, 'error');
      break;
	}
	$result = $db->Execute("select * from " . TABLE_ADDRESS_BOOK . " where ref_id = " . $vendor_id . " and type = 'vm'");
	if ($result->recordCount() > 0) {
	  $order->bill_acct_id        = $vendor_id;
	  $order->bill_address_id     = $result->fields['address_id'];
	  $order->bill_primary_name   = $result->fields['primary_name'];
	  $order->bill_contact        = $result->fields['contact'];
	  $order->bill_address1       = $result->fields['address1'];
	  $order->bill_address2       = $result->fields['address2'];
	  $order->bill_city_town      = $result->fields['city_town'];
	  $order->bill_state_province = $result->fields['state_province'];
	  $order->bill_country_code   = $result->fields['country_code'];
	  $order->bill_postal_code    = $result->fields['postal_code'];
	  $order->bill_telephone1     = $result->fields['telephone1'];
	  $order->bill_email          = $result->fields['email'];
	} else {
	  $messageStack->add('No valid vendors were found!','error');
	  break;
	}
	$result = $db->Execute("select special_terms from " . TABLE_CONTACTS . " where id = " . $vendor_id);
	$order->terms = $result->fields['terms'];

	// determine whether to ship to customer or to company main address
	if (!$drop_ship) {
	  $order->ship_acct_id        = '0';
	  $order->ship_address_id     = '0';
	  $order->ship_primary_name   = COMPANY_NAME;
	  $order->ship_contact        = AP_CONTACT_NAME;
	  $order->ship_address1       = COMPANY_ADDRESS1;
	  $order->ship_address2       = COMPANY_ADDRESS2;
	  $order->ship_city_town      = COMPANY_CITY_TOWN;
	  $order->ship_state_province = COMPANY_ZONE;
	  $order->ship_country_code   = COMPANY_COUNTRY;
	  $order->ship_postal_code    = COMPANY_POSTAL_CODE;
	  $order->ship_telephone1     = COMPANY_TELEPHONE1;
	  $order->ship_email          = COMPANY_EMAIL;
	} else { // load the customer information from the SO
	  $order->ship_acct_id        = $order->journal_main_array['ship_acct_id'];
	  $order->ship_address_id     = $order->journal_main_array['ship_address_id'];
	  $order->ship_primary_name   = $order->journal_main_array['ship_primary_name'];
	  $order->ship_contact        = $order->journal_main_array['ship_contact'];
	  $order->ship_address1       = $order->journal_main_array['ship_address1'];
	  $order->ship_address2       = $order->journal_main_array['ship_address2'];
	  $order->ship_city_town      = $order->journal_main_array['ship_city_town'];
	  $order->ship_state_province = $order->journal_main_array['ship_state_province'];
	  $order->ship_country_code   = $order->journal_main_array['ship_country_code'];
	  $order->ship_postal_code    = $order->journal_main_array['ship_postal_code'];
	  $order->ship_telephone1     = $order->journal_main_array['ship_telephone1'];
	  $order->ship_email          = $order->journal_main_array['ship_email'];
	}

	$order->journal_main_array = $order->build_journal_main_array();	// build ledger main record
	// ***************************** START TRANSACTION *******************************
	$db->transStart();
	if (!$order->validate_purchase_invoice_id()) break;
	if (!$order->Post('insert')) break;
    if ($order->purchase_invoice_id == '') {	// it's a new record, increment the po/so/inv to next number
	  if (!$order->increment_purchase_invoice_id()) break;
	}
	gen_add_audit_log(constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE') . ' - ' . TEXT_ADD, $order->purchase_invoice_id, $order->total_amount);
	$db->transCommit();	// finished successfully
	// ***************************** END TRANSACTION *******************************
	break;
  default:
}

/*****************   prepare to display templates  *************************/
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', ORD_CONVERT_TO_PO);

?>