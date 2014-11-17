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
//  Path: /modules/work_orders/functions/work_orders.php
//

function get_user_name($id = 0) {
  global $admin;
  if ($id == 0) return '';
  $result = $admin->DataBase->query("select display_name from " . TABLE_USERS . " where admin_id = " . $id);
  return ($result->rowCount() < 1) ? '' : $result->fields['display_name'];
}

function wo_build_users() {
  global $admin;
  $result = $admin->DataBase->query("select admin_id, display_name from " . TABLE_USERS . " where inactive = '0'");
  $user_list = array(array('id' => '', 'text' => TEXT_PLEASE_SELECT));
  while (!$result->EOF) {
	$user_list[] = array('id' => $result->fields['admin_id'], 'text' => $result->fields['display_name']);
	$result->MoveNext();
  }
  return $user_list;
}

function allocation_adjustment($sku_id, $qty = 0, $old_qty = 0) {
  global $admin;
  $result = $admin->DataBase->query("select sku, qty from ".TABLE_INVENTORY_ASSY_LIST." where ref_id = $sku_id");
  while (!$result->EOF) {
	$total = ($qty - $old_qty) * $result->fields['qty'];
	if ($total <> 0) $inv = $admin->DataBase->query("update " . TABLE_INVENTORY . "
	  set quantity_on_allocation = quantity_on_allocation + " . $total . " where sku = '" . $result->fields['sku'] . "'");
    $result->MoveNext();
  }
}

?>