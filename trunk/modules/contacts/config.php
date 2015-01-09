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
// 3.7.1 => 2013-06-30 - Bug fixes
// 3.7.2 => 2014-07-21 - bug fixes
// Module software version information
gen_pull_language('phreedom', 'menu');
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

?>