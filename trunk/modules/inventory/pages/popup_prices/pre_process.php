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
//  Path: /modules/inventory/pages/popup_prices/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
require(DIR_FS_WORKING . 'defaults.php');
require(DIR_FS_WORKING . 'functions/inventory.php');
/**************   page specific initialization  *************************/
$sku   = $_GET['sku'];
$type  = isset($_GET['type']) ? $_GET['type'] : 'c';
$rowId = $_GET['rowId'];
// retrieve some inventory item details
$inventory_details = $db->Execute("select id, sku, description_short, item_cost, full_price, item_weight 
	 from " . TABLE_INVENTORY . " where sku = '$sku'");
$id = $inventory_details->fields['id'];

if ($id) { // then the sku was valid, get item information, cost and full price
  $sql = "select id, sheet_name, revision, default_sheet, default_levels from " . TABLE_PRICE_SHEETS . " 
	where inactive = '0' and type = '$type' and
	(expiration_date is null or expiration_date = '0000-00-00' or expiration_date >= '" . date('Y-m-d') . "') 
	order by sheet_name";
  $price_sheets = $db->Execute($sql);
  // retrieve special pricing for this inventory item
  $result = $db->Execute("select price_sheet_id, price_levels 
	from " . TABLE_INVENTORY_SPECIAL_PRICES . " where inventory_id = " . $id);
  $special_prices = array();
  while (!$result->EOF) {
	$special_prices[$result->fields['price_sheet_id']] = $result->fields['price_levels'];
	$result->MoveNext();
  }
}

/***************   Act on the action request  *************************/

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_prics/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

switch ($_REQUEST['action']) {
  default:
}
/*****************   prepare to display templates  *************************/
// some preliminary information
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', $type == 'v' ? BOX_PURCHASE_PRICE_SHEETS : BOX_SALES_PRICE_SHEETS);
?>