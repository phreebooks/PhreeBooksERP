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
//  Path: /modules/work_orders/ajax/load_bom_list.php
//
/**************   Check user security   *****************************/
$security_level = \core\classes\user::validate();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$xml    = NULL;
$sku_id = $_GET['skuID'];
$qty    = $_GET['qty'];
if (!$sku_id || !$qty) die;

$result = $admin->DataBase->Execute("select sku, description, qty from " . TABLE_INVENTORY_ASSY_LIST . " where ref_id = '" . $sku_id . "'");
$short = array();
while (!$result->EOF) {
  $stock = $admin->DataBase->Execute("select quantity_on_hand, quantity_on_sales_order, quantity_on_allocation
    from " . TABLE_INVENTORY . " where sku = '" . $result->fields['sku'] . "' limit 1");
  $qty_available = $stock->fields['quantity_on_hand'] - $stock->fields['quantity_on_sales_order'] - $stock->fields['quantity_on_allocation'];
  if ($qty_available < ($qty * $result->fields['qty'])) {
    $short[] = sprintf(TEXT_PARTS_SHORTAGE_ARGS, $qty_available, $qty * $result->fields['qty'], $result->fields['sku'], $result->fields['description']);
  }
  $result->MoveNext();
}
$shortage = (sizeof($short) == 0) ? 'none' : implode(chr(10), $short);
echo createXmlHeader() . xmlEntry("shortage", $shortage) . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>