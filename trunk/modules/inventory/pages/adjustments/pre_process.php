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
//  Path: /modules/inventory/pages/adjustments/pre_process.php
//
$security_level = validate_user(SECURITY_ID_ADJUST_INVENTORY);
/**************  include page specific files    *********************/
gen_pull_language('phreebooks');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
/**************   page specific initialization  *************************/
define('JOURNAL_ID',16);	// Adjustment Journal
define('GL_TYPE', '');
$post_date           = isset($_POST['post_date'])? gen_db_date($_POST['post_date']) : date('Y-m-d');
$error               = false;
$glEntry             = new journal();
$glEntry->id         = isset($_POST['id'])       ? $_POST['id']       : '';
$glEntry->journal_id = JOURNAL_ID;
$glEntry->store_id   = isset($_POST['store_id']) ? $_POST['store_id'] : 0;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/adjustments/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	validate_security($security_level, 2);
	// retrieve and clean input values
	$glEntry->post_date           = $post_date;
	$glEntry->period              = gen_calculate_period($post_date);
	$glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
	$glEntry->admin_id            = $_SESSION['admin_id'];
	$glEntry->closed              = '1'; // closes by default
	$glEntry->closed_date         = $post_date;
	$glEntry->currencies_code     = DEFAULT_CURRENCY;
	$glEntry->currencies_value    = 1;
	$adj_reason                   = db_prepare_input($_POST['adj_reason']);
	$adj_account                  = db_prepare_input($_POST['gl_acct']);
 	// process the request
	$glEntry->journal_main_array  = $glEntry->build_journal_main_array();
	// build journal entry based on adding or subtracting from inventory
	$rowCnt    = 1;
	$adj_total = 0;
	$adj_lines = 0;
	while (true) {
	  if (!isset($_POST['sku_'.$rowCnt])) break;
	  $sku              = db_prepare_input($_POST['sku_'.$rowCnt]);
	  $qty              = db_prepare_input($_POST['qty_'.$rowCnt]);
	  $serialize_number = db_prepare_input($_POST['serial_'.$rowCnt]);
	  $desc             = db_prepare_input($_POST['desc_'.$rowCnt]);
	  $acct             = db_prepare_input($_POST['acct_'.$rowCnt]);
	  $price            = $currencies->clean_value($_POST['price_'.$rowCnt]);
	  if ($qty > 0) $adj_total += $qty * $price;
	  if ($qty && $sku <> '' && $sku <> TEXT_SEARCH) { // ignore blank rows
	    $glEntry->journal_rows[] = array(
		  'sku'              => $sku,
		  'qty'              => $qty,
		  'gl_type'          => 'adj',
		  'serialize_number' => $serialize_number,
		  'gl_account'       => $acct,
		  'description'      => $desc,
		  'credit_amount'    => 0,
		  'debit_amount'     => $qty > 0 ? $qty * $price : 0,
		  'post_date'        => $post_date,
	    );
		$adj_lines++;
	  }
	  $rowCnt++;
	}
	if ($adj_lines > 0) {
	  $glEntry->journal_main_array['total_amount'] = $adj_total;
	  $glEntry->journal_rows[] = array(
	    'sku'           => '',
	    'qty'           => '',
	    'gl_type'       => 'ttl',
	    'gl_account'    => $adj_account,
	    'description'   => $adj_reason,
	    'debit_amount'  => 0,
	    'credit_amount' => $adj_total,
		'post_date'     => $post_date,
      );
	  // *************** START TRANSACTION *************************
	  $db->transStart();
	  $glEntry->override_cogs_acct = $adj_account; // force cogs account to be users specified account versus default inventory account
	  if ($glEntry->Post($glEntry->id ? 'edit' : 'insert')) {
	    $db->transCommit();	// post the chart of account values
	    gen_add_audit_log(INV_LOG_ADJ . ($_REQUEST['action']=='save' ? TEXT_SAVE : TEXT_EDIT), $sku, $qty);
	    $messageStack->add(INV_POST_SUCCESS . $glEntry->purchase_invoice_id, 'success');
	    if (DEBUG) $messageStack->write_debug();
	    gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	  }
	  // *************** END TRANSACTION *************************
	} else {
	  $messageStack->add(INV_ADJ_QTY_ZERO, 'error');
	}
	$error = $messageStack->add(GL_ERROR_NO_POST, 'error');
	$cInfo = new objectInfo($_POST);
	break;

  case 'delete':
	validate_security($security_level, 4); // security check
	if ($glEntry->id) {
	  $delOrd = new journal();
	  $delOrd->journal($glEntry->id); // load the posted record based on the id submitted
	  // *************** START TRANSACTION *************************
	  $db->transStart();
	  if ($delOrd->unPost('delete')) {
		$db->transCommit(); // if not successful rollback will already have been performed
		gen_add_audit_log(INV_LOG_ADJ . TEXT_DELETE, $delOrd->journal_rows[0]['sku'], $delOrd->journal_rows[0]['qty']);
		if (DEBUG) $messageStack->write_debug();
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		break;
	  }
	}
	$error = $messageStack->add(GL_ERROR_NO_DELETE, 'error');
	$cInfo = new objectInfo($_POST);
	break;

  case 'edit':
	validate_security($security_level, 2); // security check
    $oID = (int)$_GET['oID'];
	// fall through like default
  default:
	$cInfo = new objectInfo();
	$cInfo->gl_acct = INV_STOCK_DEFAULT_COS;
	break;
}
/*****************   prepare to display templates  *************************/
$gl_array_list = gen_coa_pull_down(); // load gl accounts
$cal_adj = array(
  'name'      => 'dateReference',
  'form'      => 'inv_adj',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($post_date),
);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', INV_POPUP_ADJ_WINDOW_TITLE);

?>