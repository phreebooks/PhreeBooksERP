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
//  Path: /modules/inventory/pages/popup_serial/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$snDef = $_GET['def'];
$sku   = $_GET['sku'];
$store = isset($_GET['storeID']) ? (int)$_GET['store_id'] : 0;
$rowID = $_GET['rowID'];
// retrieve some inventory item details
$result = $db->Execute("SELECT description_short FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
$description = $result->fields['description_short'];
$sql = "SELECT serialize_number FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' AND remaining>0";
if ($store) $sql .= " AND store_id='$store'";
$result = $db->Execute($sql);
$snValues = array();
while (!$result->EOF) {
	$snValues[] = array('id'=>$result->fields['serialize_number'], 'text'=>$result->fields['serialize_number']);
	$result->MoveNext();	
}
/***************   Act on the action request  *************************/
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_serial/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/*****************   prepare to display templates  *************************/
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', INV_SERIAL_POPUP_TITLE);
?>