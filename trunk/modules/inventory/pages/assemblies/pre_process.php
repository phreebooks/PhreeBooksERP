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
$security_level = \core\classes\user::validate(SECURITY_ID_ASSEMBLE_INVENTORY);
/**************  include page specific files    *********************/
gen_pull_language('phreebooks');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/inventory.php');
/**************   page specific initialization  *************************/
define('JOURNAL_ID', 14); // Inventory Assemblies Journal
define('GL_TYPE', '');
$glEntry             = new \core\classes\journal();
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
  		try{
			\core\classes\user::validate_security($security_level, 2); // security check
			// 	retrieve and clean input values
			$glEntry->admin_id            = $_SESSION['admin_id'];
			$glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
			$sku                          = db_prepare_input($_POST['sku_1']);
			$qty                          = db_prepare_input($_POST['qty_1']);
			$desc                         = db_prepare_input($_POST['desc_1']);
			$stock                        = db_prepare_input($_POST['stock_1']);
			$serial                       = db_prepare_input($_POST['serial_1']);
			// check for errors and prepare extra values
			$glEntry->period              = gen_calculate_period($glEntry->post_date);
			if (!$glEntry->period) throw new \core\classes\userException("period isn't set");
			// if unbuild, test for stock to go negative
			$result = $admin->DataBase->query("select account_inventory_wage, quantity_on_hand
	  		  from " . TABLE_INVENTORY . " where sku = '" . $sku . "'");
			$sku_inv_acct = $result->fields['account_inventory_wage'];
			if (!$result->rowCount()) throw new \core\classes\userException(INV_ERROR_SKU_INVALID);
			if ($qty < 0 && ($result->fields['quantity_on_hand'] + $qty) < 0 ) throw new \core\classes\userException(INV_ERROR_NEGATIVE_BALANCE);
			if (!$qty) throw new \core\classes\userException(JS_ASSY_VALUE_ZERO);
			// finished checking errors, reload if any errors found
			$cInfo = new \core\classes\objectInfo($_POST);
			// 	process the request, build main record
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
			$admin->DataBase->transStart();
			$glEntry->Post($glEntry->id ? 'edit' : 'insert');
	  		$admin->DataBase->transCommit();	// post the chart of account values
	  		gen_add_audit_log(TEXT_INVENTORY_ASSEMBLY . ' - ' . ($_REQUEST['action']=='save' ? TEXT_SAVE : TEXT_EDIT), $sku, $qty);
	  		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_ASSEMBLED, TEXT_SKU , $sku), 'success');
	  		if (DEBUG) $messageStack->write_debug();
	  		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
			// *************** END TRANSACTION *************************
		}catch(Exception $e){
			$admin->DataBase->transRollback();
			$messageStack->add($e->getMessage(), $e->getCode());
			$cInfo = new \core\classes\objectInfo($_POST);
			if (DEBUG) $messageStack->write_debug();
		}
		break;
  	case 'delete':
	  	try{
			\core\classes\user::validate_security($security_level, 4); // security check
			if (!$glEntry->id) throw new \core\classes\userException(TEXT_THERE_WERE_ERRORS_DURING_PROCESSING . ' ' . TEXT_THE_RECORD_WAS_NOT_DELETED);
			$delAssy = new \core\classes\journal($glEntry->id); // load the posted record based on the id submitted
			// *************** START TRANSACTION *************************
		  	$admin->DataBase->transStart();
		  	if ($delAssy->unPost('delete')) {	// unpost the prior assembly
				$admin->DataBase->transCommit(); // if not successful rollback will already have been performed
				gen_add_audit_log(TEXT_INVENTORY_ASSEMBLY . ' - ' . TEXT_DELETE, $delAssy->journal_rows[0]['sku'], $delAssy->journal_rows[0]['qty']);
				if (DEBUG) $messageStack->write_debug();
				gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
				// *************** END TRANSACTION *************************
		  	}
		}catch(Exception $e){
			$admin->DataBase->transRollback();
			$messageStack->add($e->getMessage(), $e->getCode());
			$cInfo = new \core\classes\objectInfo($_POST);
			if (DEBUG) $messageStack->write_debug();
		}
		break;
  	case 'edit':
  		try{
			\core\classes\user::validate_security($security_level, 2); // security check
    		$oID = (int)$_GET['oID'];
			$cInfo = new \core\classes\objectInfo(array());
    	}catch(Exception $e){
			$admin->DataBase->transRollback();
			$messageStack->add($e->getMessage(), $e->getCode());
			$cInfo = new \core\classes\objectInfo(array());
			if (DEBUG) $messageStack->write_debug();
		}
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
define('PAGE_TITLE', TEXT_ASSEMBLE_DISASSEMBLE_INVENTORY);

?>