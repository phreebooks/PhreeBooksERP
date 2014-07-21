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
//  Path: /modules/phreebooks/pages/popup_convert/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'classes/gen_ledger.php');

/**************   page specific initialization  *************************/
$id     = (isset($_GET['oID'])    ? $_GET['oID']    : $_POST['id']);
$error  = false;

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_convert/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
    $selection = $_POST['conv_type'];
	$so_num    = $_POST['so_num'];
	$inv_num   = $_POST['inv_num'];
	$order     = new journal($id);
	switch ($order->journal_id) {
	  case  3: 
	    define('JOURNAL_ID', 4);
		define('GL_TYPE', 'poo');
		$search_gl_type      = 'poo';
		$purchase_invoice_id = $so_num;
		break;
	  default:
	  case  9: 
		if ($selection == 'inv') { // invoice
		  define('JOURNAL_ID',12);
		  define('GL_TYPE', 'sos');
		  $search_gl_type      = 'soo';
		  $purchase_invoice_id = $inv_num;
		} else { // sales order
		  define('JOURNAL_ID',10);
		  define('GL_TYPE', 'soo');
		  $search_gl_type      = 'soo';
		  $purchase_invoice_id = $so_num;
		}
		break;
	}
	// change some values to make it look like a new sales order/invoice
	$order->id            = '';
	$order->journal_id    = JOURNAL_ID;
	$order->post_date     = date('Y-m-d'); // make post date today
	$order->period        = gen_calculate_period($order->post_date);
	$order->terminal_date = $order->post_date;
	$order->journal_main_array['id']          = $order->id;
	$order->journal_main_array['journal_id']  = $order->journal_id;
	$order->journal_main_array['post_date']   = $order->post_date;
	$order->journal_main_array['period']      = $order->period;
	$order->journal_main_array['description'] = sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE'));
	for ($i = 0; $i < sizeof($order->journal_rows); $i++) {
	  $order->journal_rows[$i]['id']                = '';
	  $order->journal_rows[$i]['so_po_item_ref_id'] = '';
	  $order->journal_rows[$i]['post_date']         = $order->post_date;
	  if ($order->journal_rows[$i]['gl_type'] == $search_gl_type) $order->journal_rows[$i]['gl_type'] = GL_TYPE;
	}
	// ***************************** START TRANSACTION *******************************
	$db->transStart();
	if ($purchase_invoice_id) {
	  $order->journal_main_array['purchase_invoice_id'] = $purchase_invoice_id;
	  $order->purchase_invoice_id = $purchase_invoice_id;
	} else {
	  $order->purchase_invoice_id = '';
	  if (!$order->validate_purchase_invoice_id()) {
	  	$error = true;
		break;
	  }
	}
	if (!$order->Post('insert')) $error = true;
    if ($order->purchase_invoice_id == '') {
	  if (!$order->increment_purchase_invoice_id()) $error = true;
	}
	if (!$error) {
	  gen_add_audit_log(constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE') . ' - ' . TEXT_ADD, $order->purchase_invoice_id, $order->total_amount);
	  // set the closed flag on the quote
	  $result = $db->Execute("update " . TABLE_JOURNAL_MAIN . " set closed = '1' where id = " . $id);
	  $db->transCommit();	// finished successfully
	  // ***************************** END TRANSACTION *******************************
	}
	break;
  default:
}

/*****************   prepare to display templates  *************************/
$result       = $db->Execute("select journal_id from " . TABLE_JOURNAL_MAIN . " where id = " . $id);
$jID          = $result->fields['journal_id'];
$account_type = ($jID == 3 ? 'v' : 'c');

$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', $jID == 3 ? ORD_CONVERT_TO_RFQ_PO : ORD_CONVERT_TO_SO_INV);

?>