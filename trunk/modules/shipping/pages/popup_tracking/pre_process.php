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
//  Path: /modules/shipping/pages/popup_tracking/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files  *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/shipping.php');

/**************   page specific initialization  *************************/
$close_popup = false;
$methods     = load_all_methods('shipping');
$sID         = $_GET['sID']    ? $_GET['sID']    : '';
$method      = $_GET['method'] ? $_GET['method'] : '';
$ship_date   = date('Y-m-d');
if ($method) $subject_module = new $method();

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'shipping/custom/pages/popup_tracking/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
    if (!$method) break;
	$sql_data_array = array(
		'carrier'      => db_prepare_input($_POST['carrier']),
		'ref_id'       => db_prepare_input($_POST['ref_id']),
		'method'       => db_prepare_input($_POST['method']),
		'ship_date'    => gen_db_date($_POST['ship_date']),
		'deliver_date' => gen_db_date($_POST['deliver_date']),
		'tracking_id'  => db_prepare_input($_POST['tracking_id']),
		'cost'         => $currencies->clean_value($_POST['cost']),
	);
	if (!$sID) { // it's a new entry
	  $result = $db->Execute("select next_shipment_num from " . TABLE_CURRENT_STATUS);
	  $sql_data_array['shipment_id'] = $result->fields['next_shipment_num'];
	  db_perform(TABLE_SHIPPING_LOG, $sql_data_array, 'insert');
	  $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_shipment_num = next_shipment_num + 1");
      gen_add_audit_log(SHIPPING_SHIPMENT_DETAILS . ' - ' . TEXT_INSERT, $sID);
	} else { // update
	  db_perform(TABLE_SHIPPING_LOG, $sql_data_array, 'update', "id = " . $sID);
      gen_add_audit_log(SHIPPING_SHIPMENT_DETAILS . ' - ' . TEXT_UPDATE, $sID);
	}
	$close_popup = true;
    break;
  default:
}

/*****************   prepare to display templates  *************************/
$js_methods = build_js_methods($methods);

if ($sID) {
  $sql = "select id, shipment_id, carrier, ref_id, method, ship_date, deliver_date, tracking_id, cost 
	from " . TABLE_SHIPPING_LOG . " where id = " . (int)$sID;
  $result = $db->Execute($sql);
  $cInfo = new objectInfo($result->fields);
  // need to build the methods pull down
  $carrier_methods = array();
  foreach ($shipping_defaults['service_levels'] as $key => $value) {
    if (defined($cInfo->carrier . '_' . $key)) {
	  $carrier_methods[] = array(
	    'id'   => $key,
		'text' => constant($cInfo->carrier . '_' . $key),
	  );
	}
  }
} else {
  $cInfo = new objectInfo(array(
	'shipment_id' => $sID, 
	'carrier'     => $carrier, 
	'method'      => $method, 
	'ship_date'   => $ship_date,
  ));
}

$cal_ship = array(
  'name'      => 'ship_cal',
  'form'      => 'popup_tracking',
  'fieldname' => 'ship_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($cInfo->ship_date),
  'params'    => array('align' => 'left'),
);
$cal_del = array(
  'name'      => 'cal',
  'form'      => 'popup_tracking',
  'fieldname' => 'deliver_date',
  'imagename' => 'btn_date_2',
  'default'   => gen_locale_date($cInfo->deliver_date),
  'params'    => array('align' => 'left'),
);

$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', $subject_module->title);

?>