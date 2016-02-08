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
// Module software version information
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
?>