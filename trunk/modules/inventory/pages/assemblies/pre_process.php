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
//  Path: /modules/inventory/pages/assemblies/pre_process.php
//
$security_level = validate_user(SECURITY_ID_ASSEMBLE_INVENTORY);
/**************  include page specific files    *********************/
gen_pull_language('phreebooks');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
require_once(DIR_FS_WORKING . 'functions/inventory.php');
/**************   page specific initialization  *************************/
$error = false;
define('JOURNAL_ID', 14); // Inventory Assemblies Journal
define('GL_TYPE', '');
$glEntry             = new journal();
$glEntry->id         = ($_POST['id'] <> '')      ? $_POST['id'] : ''; // will be null unless opening an existing gl entry
$glEntry->journal_id = JOURNAL_ID;
$glEntry->store_id   = isset($_POST['store_id']) ? $_POST['store_id'] : 0;
$glEntry->post_date  = $_POST['post_date']       ? gen_db_date($_POST['post_date']) : date('Y-m-d');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/assemblies/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	validate_security($security_level, 2); // security check
	// retrieve and clean input values
	$glEntry->admin_id            = $_SESSION['admin_id'];
	$glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
	$sku                          = db_prepare_input($_POST['sku_1']);
	$qty                          = db_prepare_input($_POST['qty_1']);
	$desc                         = db_prepare_input($_POST['desc_1']);
	$stock                        = db_prepare_input($_POST['stock_1']);
	$serial                       = db_prepare_input($_POST['serial_1']);
	// check for errors and prepare extra values
	$glEntry->period              = gen_calculate_period($glEntry->post_date);
	if (!$glEntry->period) $error = true;
	// if unbuild, test for stock to go negative
	$result = $db->Execute("select account_inventory_wage, quantity_on_hand 
	  from " . TABLE_INVENTORY . " where sku = '" . $sku . "'");
	$sku_inv_acct = $result->fields['account_inventory_wage'];
	if (!$result->RecordCount()) $error = $messageStack->add(INV_ERROR_SKU_INVALID, 'error');
	if ($qty < 0 && ($result->fields['quantity_on_hand'] + $qty) < 0 ) $error = $messageStack->add(INV_ERROR_NEGATIVE_BALANCE, 'error');
	if (!$qty) $error = $messageStack->add(JS_ASSY_VALUE_ZERO, 'error');
	// finished checking errors, reload if any errors found
	if ($error) {
	  $cInfo = new objectInfo($_POST);
	  break; // bail if an input error was found.
	}
	// process the request, build main record
	$glEntry->closed = '1'; // closes by default
	$glEntry->journal_main_array = $glEntry->build_journal_main_array();
	// build journal entry based on adding or subtracting from inventory, debit/credit will be calculated by COGS
	$glEntry->journal_rows[] = array(
	  'gl_type'          => 'asy',
	  'sku'              => $sku,
	  'qty'              => $qty,
	  'serialize_number' => $serial,
	  'gl_account'       => $sku_inv_acct,
	  'description'      => $desc,
	);
	// *************** START TRANSACTION *************************
	$db->transStart();
	if (!$glEntry->Post($glEntry->id ? 'edit' : 'insert')) {
	  $messageStack->add(GL_ERROR_NO_POST, 'error');
	  $cInfo = new objectInfo($_POST);
	} else {
	  $db->transCommit();	// post the chart of account values
	  gen_add_audit_log(INV_LOG_ASSY . ($_REQUEST['action']=='save' ? TEXT_SAVE : TEXT_EDIT), $sku, $qty);
	  $messageStack->add(INV_POST_ASSEMBLY_SUCCESS . $sku, 'success');
	  if (DEBUG) $messageStack->write_debug();
	  gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	  // *************** END TRANSACTION *************************
	}
	break;
  case 'delete':
	validate_security($security_level, 4); // security check
	if (!$error && $glEntry->id) {
	  $delAssy = new journal($glEntry->id); // load the posted record based on the id submitted
	  // *************** START TRANSACTION *************************
	  $db->transStart();
	  if ($delAssy->unPost('delete')) {	// unpost the prior assembly
		$db->transCommit(); // if not successful rollback will already have been performed
		gen_add_audit_log(INV_LOG_ASSY . TEXT_DELETE, $delAssy->journal_rows[0]['sku'], $delAssy->journal_rows[0]['qty']);
		if (DEBUG) $messageStack->write_debug();
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		break;
		// *************** END TRANSACTION *************************
	  }
	}
	$messageStack->add(GL_ERROR_NO_DELETE, 'error');
	$cInfo = new objectInfo($_POST);
	break;
  case 'edit':
	validate_security($security_level, 2); // security check
    $oID = (int)$_GET['oID'];
	$cInfo = new objectInfo(array());
    break;
  default:
}
/*****************   prepare to display templates  *************************/
$cal_assy = array(
  'name'      => 'datePost',
  'form'      => 'inv_assy',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($glEntry->post_date) ? gen_locale_date($glEntry->post_date) : date(DATE_FORMAT),
);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', INV_ASSY_HEADING_TITLE);

?>