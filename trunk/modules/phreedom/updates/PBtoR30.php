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
//  Path: /modules/phreedom/updates/PBtoR10.php
//

// This script updates/converts PhreeBooks Release 2.1 to Phreedom Release 3.0

// *************************** IMPORTANT UPDATE INFORMATION *********************************//

//********************************* END OF IMPORTANT ****************************************//
// change some dashboard field names
if (!db_field_exists(TABLE_USERS_PROFILES, 'dashboard_id')) {
  $db->Execute("ALTER TABLE " . TABLE_USERS_PROFILES . " CHANGE page_id menu_id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
  $db->Execute("ALTER TABLE " . TABLE_USERS_PROFILES . " CHANGE module_id dashboard_id VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
  $db->Execute("ALTER TABLE " . TABLE_USERS_PROFILES . " ADD module_id VARCHAR(24) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER menu_id");
  // relocate dashboard modules
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreebooks' WHERE dashboard_id = 'po_status'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreebooks' WHERE dashboard_id = 'so_status'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreebooks' WHERE dashboard_id = 'open_inv'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreebooks' WHERE dashboard_id = 'mini_financial'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreebooks' WHERE dashboard_id = 'todays_orders'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreebooks' WHERE dashboard_id = 'todays_sales'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreedom'   WHERE dashboard_id = 'company_links'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreedom'   WHERE dashboard_id = 'my_notes'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreedom'   WHERE dashboard_id = 'personal_links'");
  $db->Execute("UPDATE " . TABLE_USERS_PROFILES  . " SET module_id = 'phreeform'  WHERE dashboard_id = 'favorite_reports'");
}

// check for modules installed, set status
                                                       write_configure('MODULE_CONTACTS_STATUS',   '0.1');
                                                       write_configure('MODULE_INVENTORY_STATUS',  '0.1');
                                                       write_configure('MODULE_PAYMENT_STATUS',    '0.1');
                                                       write_configure('MODULE_PHREEBOOKS_STATUS', '0.1');
                                                       write_configure('MODULE_PHREECRM_STATUS',   '0.1');
                                                       write_configure('MODULE_PHREEHELP_STATUS',  '0.1');
                                                       write_configure('MODULE_SHIPPING_STATUS',   '0.1');
if (db_table_exists(DB_PREFIX . 'assets'))             write_configure('MODULE_ASSETS_STATUS',     '0.1');
if (db_table_exists(DB_PREFIX . 'capa_module'))        write_configure('MODULE_CP_ACTION_STATUS',  '0.1');
if (db_table_exists(DB_PREFIX . 'doc_ctl_document'))   write_configure('MODULE_DOC_CTL_STATUS',    '0.1');
if (db_table_exists(DB_PREFIX . 'receiving_module'))   write_configure('MODULE_RECEIVING_STATUS',  '0.1');
if (db_table_exists(DB_PREFIX . 'rma_module'))         write_configure('MODULE_RMA_STATUS',        '0.1');
if (db_table_exists(DB_PREFIX . 'translate_files'))    write_configure('MODULE_TRANSLATOR_STATUS', '0.1');
if (db_table_exists(DB_PREFIX . 'wo_main'))            write_configure('MODULE_WORK_ORDERS_STATUS','0.1');
if (db_field_exists(DB_PREFIX.'inventory', 'catalog')) write_configure('MODULE_ZENCART_STATUS',    '0.1');

// check installed payment and shipping methods and update
if (defined('MODULE_PAYMENT_AUTHORIZENET_ORDER'))      write_configure('MODULE_PAYMENT_AUTHORIZENET_STATUS', '0.1');
if (defined('MODULE_PAYMENT_COD_SORT_ORDER'))          write_configure('MODULE_PAYMENT_COD_STATUS',          '0.1');
if (defined('MODULE_PAYMENT_DIRECTDEBIT_SORT_ORDER'))  write_configure('MODULE_PAYMENT_DIRECTDEBIT_STATUS',  '0.1');
if (defined('MODULE_PAYMENT_NOVA_XML_SORT_ORDER'))     write_configure('MODULE_PAYMENT_NOVA_XML_STATUS',     '0.1');
if (defined('MODULE_PAYMENT_FIRSTDATA_SORT_ORDER'))    write_configure('MODULE_PAYMENT_FIRSTDATA_STATUS',    '0.1');
if (defined('MODULE_PAYMENT_FREECHARGER_SORT_ORDER'))  write_configure('MODULE_PAYMENT_FREECHARGER_STATUS',  '0.1');
if (defined('MODULE_PAYMENT_LINKPOINT_API_SORT_ORDER'))write_configure('MODULE_PAYMENT_LINKPOINT_API_STATUS','0.1');
if (defined('MODULE_PAYMENT_MONEYORDER_SORT_ORDER'))   write_configure('MODULE_PAYMENT_MONEYORDER_STATUS',   '0.1');
if (defined('MODULE_PAYMENT_PAYPAL_NVP_SORT_ORDER'))   write_configure('MODULE_PAYMENT_PAYPAL_NVP_STATUS',   '0.1');

if (defined('MODULE_SHIPPING_FEDEX_SORT_ORDER'))       write_configure('MODULE_SHIPPING_FEDEX_STATUS',       '0.1');
if (defined('MODULE_SHIPPING_FEDEX_V7_SORT_ORDER'))    write_configure('MODULE_SHIPPING_FEDEX_V7_STATUS',    '0.1');
if (defined('MODULE_SHIPPING_FLAT_SORT_ORDER'))        write_configure('MODULE_SHIPPING_FLAT_STATUS',        '0.1');
if (defined('MODULE_SHIPPING_FREESHIPPER_SORT_ORDER')) write_configure('MODULE_SHIPPING_FREESHIPPER_STATUS', '0.1');
if (defined('MODULE_SHIPPING_ITEM_SORT_ORDER'))        write_configure('MODULE_SHIPPING_ITEM_STATUS',        '0.1');
if (defined('MODULE_SHIPPING_STOREPICKUP_SORT_ORDER')) write_configure('MODULE_SHIPPING_STOREPICKUP_STATUS', '0.1');
if (defined('MODULE_SHIPPING_TABLE_SORT_ORDER'))       write_configure('MODULE_SHIPPING_TABLE_STATUS',       '0.1');
if (defined('MODULE_SHIPPING_UPS_SORT_ORDER'))         write_configure('MODULE_SHIPPING_UPS_STATUS',         '0.1');
if (defined('MODULE_SHIPPING_USPS_SORT_ORDER'))        write_configure('MODULE_SHIPPING_USPS_STATUS',        '0.1');
// load the phreeform module
gen_pull_language('phreeform', 'admin');
require_once (DIR_FS_MODULES . 'phreeform/config.php');
require_once (DIR_FS_MODULES . 'phreeform/defaults.php');
require_once (DIR_FS_MODULES . 'phreeform/classes/install.php');
$mInstall = new phreeform_admin;
admin_install_dirs($mInstall->dirlist, DIR_FS_MY_FILES.$_SESSION['company'].'/');
admin_install_tables($mInstall->tables);
write_configure('MODULE_PHREEFORM_STATUS', constant('MODULE_PHREEFORM_VERSION'));
foreach ($mInstall->keys as $key => $value) write_configure($key, $value);
$mInstall->load_reports('phreeform');
admin_add_reports('phreeform');
$mInstall->install('phreeform');
// load installed modules and build report folders
$contents = scandir(DIR_FS_MODULES);
foreach ($contents as $entry) { // load the configuration files to load version info
  if ($entry <> '.' && $entry <> '..' && is_dir(DIR_FS_MODULES . $entry)) {
    if (defined('MODULE_' . strtoupper($entry) . '_STATUS') && $entry <> 'phreeform') { // build the directories
	  gen_pull_language($entry, 'admin');
	  require_once (DIR_FS_MODULES . $entry . '/config.php');
	  require_once (DIR_FS_MODULES . $entry . '/classes/install.php');
	  $classname   = $entry . '_admin';
	  $install_mod = new $classname;
	  $install_mod->load_reports($entry);
    }
  }
}

// reload pages array since it doesn't exist at the start of the update
global $loaded_modules;
$loaded_modules = array();
$dirs = scandir(DIR_FS_MODULES);
foreach ($dirs as $dir) { // first pull all module language files, loaded or not
  if ($dir == '.' || $dir == '..') continue;
  if (is_dir(DIR_FS_MODULES . $dir)) gen_pull_language($dir, 'menu'); 
}
foreach ($dirs as $dir) {
  if ($dir == '.' || $dir == '..') continue;
  if (defined('MODULE_' . strtoupper($dir) . '_STATUS')) { // module is loaded
    $loaded_modules[] = $dir;
    require_once(DIR_FS_MODULES . $dir . '/config.php');
  }
}

write_configure('DATE_FORMAT',     defined('DATE_FORMAT')      ? DATE_FORMAT      : 'm/d/Y');
write_configure('DATE_DELIMITER',  defined('DATE_DELIMITER')   ? DATE_DELIMITER   : '/');
write_configure('DATE_TIME_FORMAT',defined('DATE_TIME_FORMAT') ? DATE_TIME_FORMAT : 'm/d/Y h:i:s a');

?>