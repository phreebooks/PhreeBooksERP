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
//  Path: /modules/inventory/config.php
//
// Release History
// 3.0 => 2011-01-15 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes
// 3.2 => 2011-08-01 - added vendor price seets, bug fixes
// 3.3 => 2011-11-15 - bug fixes, themeroller changes
// 3.4 => 2012-02-15 - bug fixes
// 3.5 => 2012-10-01 - bug fixes
// 3.6 => 2013-06-30 - bug fixes, rewrite to class, added multiple vendors
// 3.7 => 2014-07-21 - bug fixes
// 3.7.4 => 2014-09-26 - added markup
// Module software version information
gen_pull_language('contacts', 'menu');
define('MODULE_INVENTORY_VERSION', '3.7.5');
// Menu Sort Positions
// Menu Security id's (refer to master doc to avoid security setting overlap)
define('SECURITY_ID_PRICE_SHEET_MANAGER', 88);
define('SECURITY_ID_VEND_PRICE_SHEET_MGR',89);
define('SECURITY_ID_ADJUST_INVENTORY',   152);
define('SECURITY_ID_ASSEMBLE_INVENTORY', 153);
define('SECURITY_ID_MAINTAIN_INVENTORY', 151);
define('SECURITY_ID_TRANSFER_INVENTORY', 156);
// New Database Tables
define('TABLE_INVENTORY',                DB_PREFIX . 'inventory');
define('TABLE_INVENTORY_ASSY_LIST',      DB_PREFIX . 'inventory_assy_list');
define('TABLE_INVENTORY_COGS_OWED',      DB_PREFIX . 'inventory_cogs_owed');
define('TABLE_INVENTORY_COGS_USAGE',     DB_PREFIX . 'inventory_cogs_usage');
define('TABLE_INVENTORY_HISTORY',        DB_PREFIX . 'inventory_history');
define('TABLE_INVENTORY_MS_LIST',        DB_PREFIX . 'inventory_ms_list');
define('TABLE_INVENTORY_PURCHASE',       DB_PREFIX . 'inventory_purchase_details');
define('TABLE_INVENTORY_SPECIAL_PRICES', DB_PREFIX . 'inventory_special_prices');
define('TABLE_PRICE_SHEETS',             DB_PREFIX . 'price_sheets');
// Set the title menu

// Set the menus
$mainmenu["inventory"]["submenu"]["new_inventory"] = array(
  'order' 		=> 1,
  'text' 		=> BOX_INV_NEW,
  'security_id' => SECURITY_ID_MAINTAIN_INVENTORY,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=main&amp;action=new', 'SSL'),
  'show_in_users_settings' => false,
  'params'      => '',
);
$mainmenu["inventory"]["submenu"]["inventory_mgr"] = array(
  'order' 		=> 5,
  'text' 		=> BOX_INV_MAINTAIN,
  'security_id' => SECURITY_ID_MAINTAIN_INVENTORY,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=main&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["inventory"]["submenu"]["adjustment"] = array(
  'text'        => ORD_TEXT_16_WINDOW_TITLE, 
  'order'        => 15, 
  'security_id' => SECURITY_ID_ADJUST_INVENTORY, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=adjustments', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["inventory"]["submenu"]["assemble"] = array(
  'text'        => ORD_TEXT_14_WINDOW_TITLE, 
  'order'        => 20, 
  'security_id' => SECURITY_ID_ASSEMBLE_INVENTORY, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=assemblies', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
if (defined('ENABLE_MULTI_BRANCH') && ENABLE_MULTI_BRANCH){ 
	$mainmenu["inventory"]["submenu"]["transfer"] = array(
	  'text'        => BOX_INV_TRANSFER, 
	  'order'       => 80, 
	  'security_id' => SECURITY_ID_TRANSFER_INVENTORY, 
	  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=transfer', 'SSL'),
	  'show_in_users_settings' => true,
	  'params'      => '',
	);
}
$mainmenu["customers"]["submenu"]["pricesheet"] = array(
  'text'        => BOX_SALES_PRICE_SHEETS,
  'order'       => 65, 
  'security_id' => SECURITY_ID_PRICE_SHEET_MANAGER,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=price_sheets&amp;type=c&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["vendors"]["submenu"]["pricesheet"] = array(
  'text'        => BOX_PURCHASE_PRICE_SHEETS,
  'order'       => 65, 
  'security_id' => SECURITY_ID_VEND_PRICE_SHEET_MGR,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=price_sheets&amp;type=v&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);

if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
  gen_pull_language('inventory', 'admin');
  $mainmenu["company"]['submenu']["configuration"]['submenu']["inventory"] = array(
	'order'	      => MODULE_INVENTORY_TITLE,
	'text'        => MODULE_INVENTORY_TITLE,
	'security_id' => SECURITY_ID_CONFIGURATION, 
	'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=admin', 'SSL'),
    'show_in_users_settings' => false,
	'params'      => '',
  );
}

?>