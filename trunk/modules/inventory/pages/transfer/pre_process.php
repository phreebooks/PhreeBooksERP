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
//  Path: /modules/inventory/pages/transfer/pre_process.php
//
$security_level = validate_user(SECURITY_ID_TRANSFER_INVENTORY);
/**************  include page specific files    *********************/
gen_pull_language('phreebooks');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/inventory.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
/**************   page specific initialization  *************************/
define('JOURNAL_ID',16);	// Adjustment Journal
define('GL_TYPE', '');
$error     = false;
$post_date = ($_POST['post_date']) ? gen_db_date($_POST['post_date']) : date('Y-m-d');
$period    = gen_calculate_period($post_date);
if (!$period) $error = true;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/transfer/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	validate_security($security_level, 2); // security check
	// retrieve and clean input values
	$source_store_id = $_POST['source_store_id'];
	$dest_store_id   = $_POST['dest_store_id'];
	$skus            = array();
	$rowCnt          = 1;
	while (true) {
	  if (!isset($_POST['sku_'.$rowCnt])) break;
	  $sku   = db_prepare_input($_POST['sku_'.$rowCnt]);
	  $qty   = db_prepare_input($_POST['qty_'.$rowCnt]);
	  $stock = db_prepare_input($_POST['stock_'.$rowCnt]);
	  if ($stock < $qty) {
	    $error = $messageStack->add(sprintf(INV_XFER_ERROR_NOT_ENOUGH_SKU, $sku), 'error');
		$qty = 0;
	  }
	  if ($qty && $sku <> '' && $sku <> TEXT_SEARCH) {
	    $skus[] = array(
		  'qty'     => $qty,
		  'serial'  => db_prepare_input($_POST['serial_'.$rowCnt]),
		  'sku'     => $sku,
		  'desc'    => db_prepare_input($_POST['desc_'.$rowCnt]),
		  'gl_acct' => db_prepare_input($_POST['acct_'.$rowCnt]),
	    );
	  }
	  $rowCnt++;
	}
	// test for errors
	if ($source_store_id == $dest_store_id) $error = $messageStack->add(INV_XFER_ERROR_SAME_STORE_ID, 'error');
	// process the request, first subtract from the source store
	if (!$error) {
	  $glEntry                      = new journal();
	  $glEntry->id                  = isset($_POST['id']) ? $_POST['id'] : '';
	  $glEntry->so_po_ref_id        = '-1'; // first of 2 adjustments
	  $glEntry->journal_id          = JOURNAL_ID;
	  $glEntry->post_date           = $post_date;
	  $glEntry->period              = gen_calculate_period($post_date);
	  $glEntry->store_id            = $source_store_id;
	  $glEntry->bill_acct_id        = $dest_store_id;
	  $glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
	  $glEntry->admin_id            = $_SESSION['admin_id'];
	  $glEntry->closed              = '1'; // closes by default
	  $glEntry->closed_date         = $post_date;
	  $glEntry->currencies_code     = DEFAULT_CURRENCY;
	  $glEntry->currencies_value    = 1;
	  $adj_reason                   = db_prepare_input($_POST['adj_reason']);
//	  $adj_account                  = db_prepare_input($_POST['gl_acct']);
	  // process the request
	  $glEntry->journal_main_array  = $glEntry->build_journal_main_array();
	  $rowCnt    = 1;
	  $adj_total = 0;
	  $adj_lines = 0;
	  $tot_amount= 0;
	  while (true) {
	    if (!isset($_POST['sku_'.$rowCnt]) || $_POST['sku_'.$rowCnt] == TEXT_SEARCH) break;
	    $sku              = db_prepare_input($_POST['sku_'.$rowCnt]);
	    $qty              = db_prepare_input($_POST['qty_'.$rowCnt]);
	    $serialize_number = db_prepare_input($_POST['serial_'.$rowCnt]);
	    $desc             = db_prepare_input($_POST['desc_'.$rowCnt]);
//	    $acct             = db_prepare_input($_POST['acct_'.$rowCnt]);
	    $result = $db->Execute("select account_inventory_wage, account_cost_of_sales FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
	    $_POST['acct_'     .$rowCnt] = $result->fields['account_inventory_wage'];
	    $_POST['cogs_acct_'.$rowCnt] = $result->fields['account_cost_of_sales'];
	  	$_POST['total_'    .$rowCnt] = $glEntry->calculateCost($sku, $qty, $serialize_number);
	  	if ($sku && $sku <> TEXT_SEARCH) {
	      $glEntry->journal_rows[] = array(
		    'sku'              => $sku,
		    'qty'              => -$qty,
		    'gl_type'          => 'adj',
		    'serialize_number' => $serialize_number,
		    'gl_account'       => $result->fields['account_inventory_wage'],
		    'description'      => $desc,
		    'credit_amount'    => 0,
		    'debit_amount'     => 0,
		    'post_date'        => $post_date,
	      );
		  $adj_lines++;
	    }
	    $tot_amount += $cost;
	    $rowCnt++;
	  }
	  if ($adj_lines > 0) {
	    $glEntry->journal_main_array['total_amount'] = $tot_amount;
	    $glEntry->journal_rows[] = array(
	      'sku'           => '',
	      'qty'           => '',
	      'gl_type'       => 'ttl',
	      'gl_account'    => $result->fields['account_inventory_wage'],
	      'description'   => BOX_INV_TRANSFER .' - '. $adj_reason,
	      'debit_amount'  => 0,
	      'credit_amount' => 0,
		  'post_date'     => $post_date,
        );
	    // *************** START TRANSACTION *************************
	    $db->transStart();
//	    $glEntry->override_cogs_acct = $adj_account; // force cogs account to be users specified account versus default inventory account
	    if ($glEntry->Post($glEntry->id ? 'edit' : 'insert')) {
		  $first_id = $glEntry->id;
	      $glEntry                      = new journal();
	  	  $glEntry->id                  = isset($_POST['ref_id']) ? $_POST['ref_id'] : '';
	      $glEntry->so_po_ref_id        = $first_id; // id of original adjustment
	      $glEntry->journal_id          = JOURNAL_ID;
	      $glEntry->post_date           = $post_date;
	      $glEntry->period              = $period;
	      $glEntry->store_id            = $dest_store_id;
	      $glEntry->bill_acct_id        = $source_store_id;
	      $glEntry->admin_id            = $_SESSION['admin_id'];
	      $glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
	      $glEntry->closed              = '1'; // closes by default
	      $glEntry->closed_date         = $post_date;
	      $glEntry->currencies_code     = DEFAULT_CURRENCY;
	      $glEntry->currencies_value    = 1;
	      $glEntry->journal_main_array  = $glEntry->build_journal_main_array();
		  $rowCnt     = 1;
		  $tot_amount = 0;
		  while (true) {
			if (!isset($_POST['sku_'.$rowCnt])) break;
			$sku              = db_prepare_input($_POST['sku_'.$rowCnt]);
			$qty              = db_prepare_input($_POST['qty_'.$rowCnt]);
			$serialize_number = db_prepare_input($_POST['serial_'.$rowCnt]);
			$desc             = db_prepare_input($_POST['desc_'.$rowCnt]);
//			$acct             = db_prepare_input($_POST['acct_'.$rowCnt]);
			$cost             = db_prepare_input($_POST['total_'.$rowCnt]);
			if ($sku && $sku <> TEXT_SEARCH) {
			  $glEntry->journal_rows[] = array(
				'sku'              => $sku,
				'qty'              => $qty,
				'gl_type'          => 'adj',
				'serialize_number' => $serialize_number,
				'gl_account'       => $_POST['acct_'.$rowCnt],
				'description'      => $desc,
				'debit_amount'     => $cost,
			  	'credit_amount'    => 0,
				'post_date'        => $post_date,
			  );
		      $glEntry->journal_rows[] = array(
		        'sku'           => '',
		        'qty'           => '',
		        'gl_type'       => 'ttl',
		        'gl_account'    => $_POST['cogs_acct_'.$rowCnt],
		        'description'   => BOX_INV_TRANSFER .' - '. $adj_reason,
		        'debit_amount'  => 0,
		        'credit_amount' => $cost,
			    'post_date'     => $post_date,
	          );
			  $tot_amount += $cost;
			}
			$rowCnt++;
		  }
	      $glEntry->journal_main_array['total_amount'] = $tot_amount;
	      if (!$glEntry->Post($glEntry->id ? 'edit' : 'insert')) $error = true;
		  // Now, link first record to second record so edits can be made else will crash when entry is edited.
		  $db->Execute("UPDATE ".TABLE_JOURNAL_MAIN." SET so_po_ref_id=$glEntry->id WHERE id=$first_id");
	      if (!$error) $db->transCommit();	// post the chart of account values
	      // *************** END TRANSACTION *************************
		  gen_add_audit_log(sprintf(INV_LOG_TRANSFER, $source_store_id, $dest_store_id), $sku, $qty);
	      $messageStack->add(INV_POST_SUCCESS . $glEntry->purchase_invoice_id, 'success');
	      if (DEBUG) $messageStack->write_debug();
	      gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	    } else {
		  $error = true;
		}
	  } else {
	    $error = $messageStack->add(INV_ADJ_QTY_ZERO, 'error');
	  }
	}
	if (DEBUG) $messageStack->write_debug();
	$db->transRollback();
	$messageStack->add(GL_ERROR_NO_POST, 'error');
	$cInfo = new objectInfo($_POST);
	break;

  case 'delete':
	validate_security($security_level, 4); // security check
	if ($id = $_POST['id']) {
	  $delOrd = new journal($id);
	  $result = $db->Execute("SELECT id FROM ".TABLE_JOURNAL_MAIN." WHERE so_po_ref_id = $delOrd->id");
	  $xfer_to_id = $result->fields['id']; // save the matching adjust ID
	  if (!$xfer_to_id) $error = $messageStack('cannot deltete there is no offsetting record to delete!','error');
	  if (!$error) {
	    // *************** START TRANSACTION *************************
	    $db->transStart();
	    if ($delOrd->unPost('delete')) {
		  $delOrd = new journal($xfer_to_id);
		  if ($delOrd->unPost('delete')) {
		    $db->transCommit(); // if not successful rollback will already have been performed
		    gen_add_audit_log(INV_LOG_ADJ . TEXT_DELETE, $delOrd->journal_rows[0]['sku'], $delOrd->journal_rows[0]['qty']);
		    if (DEBUG) $messageStack->write_debug();
		    gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		    break;
	      } else { $db->transRollback(); }
		} else { $db->transRollback(); }
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
}
/*****************   prepare to display templates  *************************/
$cal_xfr = array(
  'name'      => 'dateReference',
  'form'      => 'inv_xfer',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($post_date),
);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_INV_TRANSFER);

?>