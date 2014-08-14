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
//  Path: /modules/phreedom/config.php
//
// Release History
// 3.0 => 2011-01-15 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes
// 3.2 => 2011-08-01 - Bug fixes, added roles
// 3.3 => 2011-11-15 - Bug fixes, theme re-design, jqueryUI integration
// 3.4 => 2012-02-15 - bug fixes, Google Chart support
// 3.5 => 2012-10-01 - bug fixes
// 3.6 => 2013-06-30 - bug fixes
// 3.7 => 2014-07-21 - bug fixes
// 3.7.1 => 2014-08-14 - added php_info to the admin panel
// Module software version information
define('MODULE_PHREEDOM_VERSION',  '3.7.1');
// Menu Sort Positions
define('MENU_HEADING_INVENTORY_ORDER',  30);
define('MENU_HEADING_BANKING_ORDER',    40);
define('MENU_HEADING_GL_ORDER',         50);
define('MENU_HEADING_TOOLS_ORDER',      70);
define('MENU_HEADING_QUALITY_ORDER', 	75);
define('MENU_HEADING_COMPANY_ORDER', 	90);
// Menu Security id's (refer to master doc to avoid security setting overlap)
define('SECURITY_ID_USERS',            1);
define('SECURITY_ID_IMPORT_EXPORT',    2);
define('SECURITY_ID_ROLES',            5);
define('SECURITY_ID_HELP',             6);
define('SECURITY_ID_MY_PROFILE',       7);
define('SECURITY_ID_CONFIGURATION',   11); // admin for all modules
define('SECURITY_ID_BACKUP',          18);
define('SECURITY_ID_ENCRYPTION',      20);
// New Database Tables
define('TABLE_AUDIT_LOG',      DB_PREFIX . 'audit_log');
define('TABLE_CONFIGURATION',  DB_PREFIX . 'configuration');
define('TABLE_CURRENCIES',     DB_PREFIX . 'currencies');
define('TABLE_CURRENT_STATUS', DB_PREFIX . 'current_status');
define('TABLE_DATA_SECURITY',  DB_PREFIX . 'data_security');
define('TABLE_EXTRA_FIELDS',   DB_PREFIX . 'xtra_fields');
define('TABLE_EXTRA_TABS',     DB_PREFIX . 'xtra_tabs');
define('TABLE_USERS',          DB_PREFIX . 'users');
define('TABLE_USERS_PROFILES', DB_PREFIX . 'users_profiles');
// TBD Tables no longer in use, but need to verify conversion before delete
define('TABLE_IMPORT_EXPORT',  DB_PREFIX . 'import_export');
define('TABLE_REPORTS',        DB_PREFIX . 'reports');
define('TABLE_REPORT_FIELDS',  DB_PREFIX . 'report_fields');
define('TABLE_PROJECT_VERSION',DB_PREFIX . 'project_version');
// Set the title menu
$mainmenu["home"] = array(
  'order' => 0,
  'text'  => TEXT_HOME,
  'link'  => html_href_link(FILENAME_DEFAULT),
  'icon'  => html_icon('actions/go-home.png', TEXT_HOME, 'small'),
);

// @todo BEWARE OF THIS SETTING! this config must be loaded before any other menu as the heading settings will erase prior set submenus for these headings. Especially modules alphabetically before phreedom.
$mainmenu["inventory"] = array(
  'order' 		=> MENU_HEADING_INVENTORY_ORDER,
  'text' 		=> MENU_HEADING_INVENTORY,
  'security_id' => '',
  'link' 		=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_inv', 'SSL'),
  'params'      => '',
);
$mainmenu["banking"] = array(
  'order'			=> MENU_HEADING_BANKING_ORDER,
  'text' 			=> MENU_HEADING_BANKING, 
  'security_id' 	=> '',
  'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_bnk', 'SSL'),
  'params'      	=> '',
);
$mainmenu["gl"] = array(
  'order'			=> MENU_HEADING_GL_ORDER,
  'text' 			=> MENU_HEADING_GL, 
  'security_id' 	=> '',
  'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_gl', 'SSL'),
  'params'      	=> '',
);
$mainmenu["tools"] = array(
  'order'			=> MENU_HEADING_TOOLS_ORDER,
  'text' 			=> MENU_HEADING_TOOLS, 
  'security_id' 	=> '',
  'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_tools', 'SSL'),
  'params'      	=> '',
);
$mainmenu["company"] = array(
  'order' 		=> MENU_HEADING_COMPANY_ORDER,
  'text' 		=> MENU_HEADING_COMPANY,
  'security_id' => '',
  'link' 		=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_company', 'SSL'),
  'params'      => '',
);
if (defined('MODULE_CP_ACTION_STATUS') || defined('MODULE_DOC_CTL_STATUS')) $mainmenu["quality"] = array(
  'order' 		=> MENU_HEADING_QUALITY_ORDER,
  'text'  		=> MENU_HEADING_QUALITY,
  'security_id' => '',
  'link' 		=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_qa', 'SSL'),
  'params'      => '',
);
$mainmenu["logout"] = array(
  'order' => 999,
  'text'  => TEXT_LOGOUT,
  'link'  => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;action=logout', 'SSL'),
  'icon'  => html_icon('actions/system-log-out.png', TEXT_LOGOUT, 'small'),
);
// Set the menus
$mainmenu["company"]['submenu']["profile"] = array(
  'order' 		=> 5,
  'text'        => BOX_HEADING_PROFILE,
  'security_id' => SECURITY_ID_MY_PROFILE,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=profile', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);

$mainmenu["company"]['submenu']["configuration"] = array(
  'order' 		=> 10,
  'text'        => BOX_HEADING_CONFIGURATION,
  'security_id' => SECURITY_ID_CONFIGURATION, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=admin', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);

if (defined('DEBUG') && DEBUG == true) $mainmenu["tools"]['submenu']["debug"] = array(
  'order' 		=> 0,
  'text'        => BOX_HEADING_DEBUG_DL,
  'security_id' => SECURITY_ID_CONFIGURATION,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;action=debug', 'SSL'),
  'show_in_users_settings' => false,
  'params'      => '',
);
if (defined('ENABLE_ENCRYPTION') && ENABLE_ENCRYPTION == true) $mainmenu["tools"]['submenu']["encryption"] = array(
  'order' 		=> 1,
  'text'        => BOX_HEADING_ENCRYPTION,
  'security_id' => SECURITY_ID_ENCRYPTION,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=encryption', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["tools"]['submenu']["import_export"] = array(
  'order' 		=> 50,
  'text'        => BOX_IMPORT_EXPORT, 
  'security_id' => SECURITY_ID_IMPORT_EXPORT, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=import_export', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["tools"]['submenu']["backup"] = array(
  'order' 		=> 95,
  'text'        => BOX_HEADING_BACKUP,
  'security_id' => SECURITY_ID_BACKUP, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=backup', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["company"]['submenu']["users"] = array(
  'order' 		=> 90,
  'text'        => BOX_HEADING_USERS,
  'security_id' => SECURITY_ID_USERS, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=users&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["company"]['submenu']["roles"] = array(
  'order' 		=> 85,
  'text'        => BOX_HEADING_ROLES,
  'security_id' => SECURITY_ID_ROLES, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=roles&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);

?>