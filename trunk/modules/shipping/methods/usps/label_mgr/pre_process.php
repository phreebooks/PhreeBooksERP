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
//  Path: /modules/shipping/methods/usps/label_mgr/pre_process.php
//

$shipping_module = 'usps';

/**************  include page specific files    *********************/
load_method_language(DEFAULT_MOD_DIR, $shipping_module);
require(DIR_FS_WORKING . 'functions/shipping.php');
require(DIR_FS_WORKING . 'classes/shipping.php');
require(DIR_FS_WORKING . 'methods/' . $shipping_module . '/' . $shipping_module . '.php');

/**************   page specific initialization  *************************/
$error = false;
$sInfo = new shipment();	// load defaults
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	$sInfo->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
	$sInfo->ship_method = db_prepare_input($_POST['ship_method']);
	$sInfo->ship_date = gen_db_date($_POST['ship_date']);
	$sInfo->deliver_date = gen_db_date($_POST['deliver_date']);
	$sInfo->tracking_id = db_prepare_input($_POST['tracking_id']);
	$sInfo->cost = $currencies->clean_value($_POST['cost']);

	$temp = $db->Execute("select next_shipment_num from " . TABLE_CURRENT_STATUS);
	$sql_array = array(
		'ref_id' => $sInfo->purchase_invoice_id,
		'shipment_id' => $temp->fields['next_shipment_num'],
		'carrier' => $shipping_module,
		'method' => $sInfo->ship_method,
		'ship_date' => $sInfo->ship_date,
		'deliver_date' => $sInfo->deliver_date,
		'tracking_id' => $sInfo->tracking_id,
		'cost' => $sInfo->cost);
	db_perform(TABLE_SHIPPING_LOG, $sql_array, 'insert');
	$db->Execute("update " . TABLE_CURRENT_STATUS . " set next_shipment_num = next_shipment_num + 1");
	gen_add_audit_log(SHIPPING_LOG_LABEL_PRINTED, $sInfo->purchase_invoice_id);
	break;

  case 'delete':
	$shipment_id = db_prepare_input($_GET['sID']);
	$result = $db->Execute("select method, ship_date from " . TABLE_SHIPPING_LOG . " where shipment_id = " . (int)$shipment_id);
	$ship_method = $result->fields['method'];
	if ($result->RecordCount() == 0 || !$shipment_id) {
		$messageStack->add(SHIPPING_DELETE_ERROR,'error');
		$error = true;
		break;
	}

	if ($result->fields['ship_date'] < date('Y-m-d', time())) { // only allow delete if shipped today or in future
		$messageStack->add(SHIPPING_CANNOT_DELETE,'error');
		$error = true;
		break;
	}

	$db->Execute("delete from " . TABLE_SHIPPING_LOG . " where shipment_id = " . $shipment_id);
	gen_add_audit_log(SHIPPING_LABEL_DELETED, $tracking_id);
	break;

  default:
	$oID = db_prepare_input($_GET['oID']);
	$sql = "select shipper_code, purchase_invoice_id   
		from " . TABLE_JOURNAL_MAIN . " where id = " . (int)$oID;
	$result = $db->Execute($sql);
	$sInfo->purchase_invoice_id = $result->fields['purchase_invoice_id'];
	$temp = explode(':', $result->fields['shipper_code']);
	$sInfo->ship_method = $temp[1];
}

/*****************   prepare to display templates  *************************/
// translate shipping terms in the carriers language, style
$shipping_methods = array();
foreach ($shipping_defaults['service_levels'] as $key => $value) {
	if (defined($shipping_module . '_' . $key)) {
		$shipping_methods[$key] = constant($shipping_module . '_' . $key);
	}
}

$include_header = false; // include header flag
$include_footer = false; // include footer flag
$include_template = 'template_main.php'; // include display template (required)
define('PAGE_TITLE', SHIPPING_POPUP_WINDOW_TITLE);

?>