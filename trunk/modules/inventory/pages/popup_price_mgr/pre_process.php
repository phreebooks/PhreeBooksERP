<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/inventory/pages/popup_price_mgr/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_PRICE_SHEET_MANAGER);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'inventory/functions/inventory.php');
require_once(DIR_FS_MODULES . 'inventory/defaults.php');
/**************   page specific initialization  *************************/
$id         = (int)$_GET['iID'];
$full_price = $_GET['price'];
$type       = isset($_GET['type']) ? $_GET['type'] : 'c';
// retrieve some item details
$inventory_details = $admin->DataBase->query("select sku, description_short, quantity_on_hand, quantity_on_order, item_cost,
	quantity_on_allocation, quantity_on_sales_order from " . TABLE_INVENTORY . " where id = " . $id);
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_price_mgr/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	\core\classes\user::validate_security($security_level, 2);
  	$tab_id = 1;
	while (true) {
	  if (!isset($_POST['id_' . $tab_id])) break;
	  $sheet_id = (int)$_POST['id_' . $tab_id];
	  $default_checked = isset($_POST['def_' . $tab_id]) ? true : false;
	  if ($default_checked) {
		$admin->DataBase->exec("delete from " . TABLE_INVENTORY_SPECIAL_PRICES . "
			where inventory_id = " . $id . " and price_sheet_id = " . $sheet_id);
	  } else {
		$encoded_prices = array();
		for ($i=0, $j=1; $i < MAX_NUM_PRICE_LEVELS; $i++, $j++) {
		  $level_data  =       $admin->currencies->clean_value($_POST['price_'   . $tab_id . '_' . $j]);
		  $level_data .= ':' . db_prepare_input        ($_POST['qty_'     . $tab_id . '_' . $j]);
		  $level_data .= ':' . db_prepare_input        ($_POST['src_'     . $tab_id . '_' . $j]);
		  $level_data .= ':' . db_prepare_input        ($_POST['adj_'     . $tab_id . '_' . $j]);
		  $level_data .= ':' . $admin->currencies->clean_value($_POST['adj_val_' . $tab_id . '_' . $j]);
		  $level_data .= ':' . db_prepare_input        ($_POST['rnd_'     . $tab_id . '_' . $j]);
		  $level_data .= ':' . $admin->currencies->clean_value($_POST['rnd_val_' . $tab_id . '_' . $j]);
		  $encoded_prices[] = $level_data;
		}
		$price_levels = implode(';', $encoded_prices);
		$result = $admin->DataBase->query("select id from " . TABLE_INVENTORY_SPECIAL_PRICES . "
			where inventory_id = " . $id . " and price_sheet_id = " . $sheet_id);
		if ($result->fetch(\PDO::FETCH_NUM) == 0) {
		  $admin->DataBase->query("insert into " . TABLE_INVENTORY_SPECIAL_PRICES . "
			set inventory_id = " . $id . ", price_sheet_id = " . $sheet_id . ", price_levels = '" . $price_levels . "'");
		} else {
		  $admin->DataBase->query("update " . TABLE_INVENTORY_SPECIAL_PRICES . " set price_levels = '" . $price_levels . "'
			where inventory_id = " . $id . " and price_sheet_id = " . $sheet_id);
		}
		$sql_data_array = array();
		if($type == 'v')  $sql_data_array ['price_sheet_v'] = $_POST['sheet_name_'.$tab_id ];
		else 			  $sql_data_array ['price_sheet']   = $_POST['sheet_name_'.$tab_id ];
		$sql_data_array['last_update'] = date('Y-m-d');
		db_perform(TABLE_INVENTORY, $sql_data_array, 'update', "id = " . $id);
	  }
	  $tab_id++;
	}
	gen_add_audit_log(($type == 'v' ? TEXT_VENDOR_PRICE_SHEETS : TEXT_CUSTOMER_PRICE_SHEETS) . TEXT_UPDATE, $inventory_details->fields['sku'] . ' - ' . $inventory_details->fields['description_short']);
	break;
  default:
}

/*****************   prepare to display templates  *************************/
$temp = inv_calculate_sales_price(1, $id, 0, 'v');
$item_cost = $temp['price'];

// some preliminary information
$sql = "select id, sheet_name, revision, default_sheet, default_levels from " . TABLE_PRICE_SHEETS . "
	where inactive = '0' and type = '" . $type . "' and
	(expiration_date is null or expiration_date = '0000-00-00' or expiration_date >= '" . date('Y-m-d') . "')
	order by sheet_name";
$price_sheets = $admin->DataBase->query($sql);
// retrieve special pricing for this inventory item
$result = $admin->DataBase->query("select price_sheet_id, price_levels
	from " . TABLE_INVENTORY_SPECIAL_PRICES . " where inventory_id = " . $id);
$special_prices = array();
while (!$result->EOF) {
	$special_prices[$result->fields['price_sheet_id']] = $result->fields['price_levels'];
	$result->MoveNext();
}
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', $type == 'v' ? TEXT_VENDOR_PRICE_SHEETS : TEXT_CUSTOMER_PRICE_SHEETS);
?>