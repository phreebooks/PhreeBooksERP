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
//  Path: /modules/contacts/config.php
//
// Release History
// 3.0 => 2011-01-15 - Converted from stand-alone PhreeBooks release
// 3.1 => released by Rene on the forum
// 3.2 => Release by Rene on the forum
// 3.3 => 2011-04-15 - CRM additions, bug fixes
// 3.4 => 2011-08-01 - bug fixes
// 3.5 => 2011-11-15 - bug fixes, attachments, themeroller changes
// 3.6 => 2012-02-15 - bug fixes, improved CRM, clean up forms
// 3.7 => 2012-10-01 - bug fixes, redesign of the classes/methods
// 3.71 => 2013-06-30 - Bug fixes 
// 3.72 => 2014-07-21 - bug fixes
// 3.73 => added contacts_level field 
// Module software version information
gen_pull_language('phreedom', 'menu');
define('MODULE_CONTACTS_VERSION',      3.73);
// Menu Sort Positions
define('MENU_HEADING_CUSTOMERS_ORDER',   10);
define('MENU_HEADING_VENDORS_ORDER',     20);
define('MENU_HEADING_EMPLOYEES_ORDER',   60);
// Menu Security id's (refer to master doc to avoid security setting overlap)
define('SECURITY_ID_MAINTAIN_BRANCH',    15);
define('SECURITY_ID_MAINTAIN_CUSTOMERS', 26);
define('SECURITY_ID_MAINTAIN_EMPLOYEES', 76);
define('SECURITY_ID_MAINTAIN_PROJECTS',  16);
define('SECURITY_ID_PROJECT_PHASES',     36);
define('SECURITY_ID_PROJECT_COSTS',      37);
define('SECURITY_ID_PHREECRM',           49);
define('SECURITY_ID_MAINTAIN_VENDORS',   51);
// New Database Tables
define('TABLE_ADDRESS_BOOK',    DB_PREFIX . 'address_book');
define('TABLE_CONTACTS',        DB_PREFIX . 'contacts');
define('TABLE_CONTACTS_LOG',    DB_PREFIX . 'contacts_log');
define('TABLE_DEPARTMENTS',     DB_PREFIX . 'departments');
define('TABLE_DEPT_TYPES',      DB_PREFIX . 'departments_types');
define('TABLE_PROJECTS_COSTS',  DB_PREFIX . 'projects_costs');
define('TABLE_PROJECTS_PHASES', DB_PREFIX . 'projects_phases');
// defaults for filters
define('DEFAULT_F0_SETTING','1'); // inactive filter set to show inactive contacts, override in phreedom custom language overrides by type, i.e. CONTACTS_F0_C for customers
// Set the title menu
$mainmenu["customers"]['order'] 	= MENU_HEADING_CUSTOMERS_ORDER;
$mainmenu["customers"]['text'] 		= MENU_HEADING_CUSTOMERS;
$mainmenu["customers"]['security_id']= '';
$mainmenu["customers"]['link'] 		= html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_ar', 'SSL');
$mainmenu["customers"]['params']     = '';

$mainmenu["vendors"]['order'] 	 = MENU_HEADING_VENDORS_ORDER;
$mainmenu["vendors"]['text'] 		 = MENU_HEADING_VENDORS;
$mainmenu["vendors"]['security_id']= '';
$mainmenu["vendors"]['link'] 		 = html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_ap', 'SSL');
$mainmenu["vendors"]['params']     = '';

$mainmenu["employees"]['order'] 	 = MENU_HEADING_EMPLOYEES_ORDER;
$mainmenu["employees"]['text'] 		 = MENU_HEADING_EMPLOYEES;
$mainmenu["employees"]['security_id']= '';
$mainmenu["employees"]['link'] 		 = html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_hr', 'SSL');
$mainmenu["employees"]['params']     = '';

// Set the menus
$mainmenu["customers"]['submenu']["contact"] = array(
	'order'		  => 10,
	'text'        => MENU_HEADING_CUSTOMERS,
	'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=c&amp;list=1', 'SSL'),
	'security_id' => SECURITY_ID_MAINTAIN_CUSTOMERS,
	'show_in_users_settings' => false,
    'params'      => '',
);
$mainmenu["customers"]['submenu']["contact"]['submenu']["new_customer"] = array(
  'text'        => sprintf(BOX_TEXT_NEW_TITLE, TEXT_CUSTOMER), 
  'order'       => 5, 
  'security_id' => SECURITY_ID_MAINTAIN_CUSTOMERS, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;action=new&amp;type=c', 'SSL'),
  'show_in_users_settings' => false,
  'params'	    => '',
);
$mainmenu["customers"]['submenu']["contact"]['submenu']["customer_mgr"] = array(
  'text'        => sprintf(BOX_STATUS_MGR, TEXT_CUSTOMER), 
  'order'       => 10, 
  'security_id' => SECURITY_ID_MAINTAIN_CUSTOMERS, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=c&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'	    => '',
);
$mainmenu["customers"]['submenu']["crm"] = array(
  'text'        => BOX_PHREECRM_MODULE,  
  'order'       => 15, 
  'security_id' => SECURITY_ID_PHREECRM, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=i&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'	    => '',
);
$mainmenu["vendors"]['submenu']["contact"] = array(
  'order'		  => 10,
  'text'        => MENU_HEADING_VENDORS,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=v&amp;list=1', 'SSL'),
  'security_id' => SECURITY_ID_MAINTAIN_VENDORS,
  'show_in_users_settings' => false,
  'params'      => '',
);
$mainmenu["vendors"]['submenu']["contact"]['submenu']["new_vendor"] = array(
  'text'        => sprintf(BOX_TEXT_NEW_TITLE, TEXT_VENDOR), 
  'order'       => 5, 
  'security_id' => SECURITY_ID_MAINTAIN_VENDORS, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;action=new&amp;type=v', 'SSL'),
  'show_in_users_settings' => false,
  'params'      => '',
);
$mainmenu["vendors"]['submenu']["contact"]['submenu']["vendor_mgr"] = array(
  'text'        => sprintf(BOX_STATUS_MGR, TEXT_VENDOR), 
  'order'       => 10, 
  'security_id' => SECURITY_ID_MAINTAIN_VENDORS, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=v&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
$mainmenu["employees"]['submenu']["contact"] = array(
  'order'		  => 10,
  'text'        => MENU_HEADING_EMPLOYEES,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=e&amp;list=1', 'SSL'),
  'security_id' => SECURITY_ID_MAINTAIN_EMPLOYEES,
  'show_in_users_settings' => false,
  'params'      => '',
);
$mainmenu["employees"]['submenu']["contact"]['submenu']["new_employee"] = array(
  'text'        => sprintf(BOX_TEXT_NEW_TITLE, TEXT_EMPLOYEE), 
  'order'       => 5, 
  'security_id' => SECURITY_ID_MAINTAIN_EMPLOYEES, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;action=new&amp;type=e', 'SSL'),
  'show_in_users_settings' => false,
  'params'      => '',
);
$mainmenu["employees"]['submenu']["contact"]['submenu']["employee_mgr"] = array(
  'text'        => sprintf(BOX_STATUS_MGR, TEXT_EMPLOYEE), 
  'order'       => 10, 
  'security_id' => SECURITY_ID_MAINTAIN_EMPLOYEES,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=e&amp;list=1', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);
if (defined('ENABLE_MULTI_BRANCH') && ENABLE_MULTI_BRANCH == true) { // don't show menu if multi-branch is disabled
	$mainmenu["company"]['submenu']["branches"] = array(
		'order'		  => 55,
		'text'        => TEXT_BRANCHES,
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=b&amp;list=1', 'SSL'),
		'security_id' => SECURITY_ID_MAINTAIN_BRANCH,
	    'show_in_users_settings' => false,
    	'params'      => '',
	);
	$mainmenu["company"]['submenu']["branches"]['submenu']["new_branch"] = array(
		'text'        => sprintf(BOX_TEXT_NEW_TITLE, TEXT_BRANCH),  
		'order'        => 55, 
		'security_id' => SECURITY_ID_MAINTAIN_BRANCH, 
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;action=new&amp;type=b', 'SSL'),
	    'show_in_users_settings' => false,
    	'params'      => '',
  	);
  	$mainmenu["company"]['submenu']["branches"]['submenu']["branch_mgr"] = array(
		'text'        => sprintf(BOX_STATUS_MGR, TEXT_BRANCH), 
		'order'       => 56, 
		'security_id' => SECURITY_ID_MAINTAIN_BRANCH, 
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=b&amp;list=1', 'SSL'),
  	    'show_in_users_settings' => true,
    	'params'      => '',
  	);
} // end disable if not looking at branches
$mainmenu["customers"]['submenu']['projects'] = array(
	'order'		  => 60,
	'text'        => TEXT_PROJECTS,
	'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=j&amp;list=1', 'SSL'),
	'security_id' => SECURITY_ID_MAINTAIN_PROJECTS,
    'show_in_users_settings' => false,
    'params'      => '',
);
$mainmenu["customers"]['submenu']['projects']['submenu']["new_project"] = array(
  'text'        => sprintf(BOX_TEXT_NEW_TITLE, TEXT_PROJECT), 
  'order'       => 5, 
  'security_id' => SECURITY_ID_MAINTAIN_PROJECTS, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;action=new&amp;type=j', 'SSL'),
  'show_in_users_settings' => false,
  'params'      => '',
);
$mainmenu["customers"]['submenu']['projects']['submenu']["project_mgr"] = array(
  'text'        => sprintf(BOX_STATUS_MGR, TEXT_PROJECT), 
  'order'       => 10, 
  'security_id' => SECURITY_ID_MAINTAIN_PROJECTS,
  'show_in_users_settings' => true,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=main&amp;type=j&amp;list=1', 'SSL'),
  'params'      => '',
);

if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
  gen_pull_language('contacts', 'admin');
  $mainmenu["company"]['submenu']["configuration"]['submenu']["contacts"] = array(
	'order'	      => MODULE_CONTACTS_TITLE,
	'text'        => MODULE_CONTACTS_TITLE,
	'security_id' => SECURITY_ID_CONFIGURATION, 
	'link'        => html_href_link(FILENAME_DEFAULT, 'module=contacts&amp;page=admin', 'SSL'),
    'show_in_users_settings' => false,
	'params'      => '',
  );
}
?>