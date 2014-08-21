<?php
// +-----------------------------------------------------------------+
// |                    Phreedom Open Source ERP                     |
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
//  Path: /modules/magento/language/en_us/admin.php
//

// Module information
define('MODULE_MAGENTO_TITLE','Magento Module');
define('MODULE_MAGENTO_DESCRIPTION','The Magento module interfaces Phreedom with a Magento e-store. Functons include upload products, download orders and synchronizing product databases.');
define('MAGENTO_ADMIN_URL','Magento path to Admin (no trailing slash)');
define('MAGENTO_ADMIN_USERNAME','Magento admin username (can be unique to Phreedom Interface');
define('MAGENTO_ADMIN_PASSWORD','Magento admin password (can be unique to Phreedom Interface)');
define('MODULE_MAGENTO_CONFIG_INFO','Please set the configuration values to your Magento e-store.');
define('MAGENTO_TAX_CLASS','Enter the Magento Tax Class Text field (Must match exactly to the entry in Magento if tax is charged)');
define('MAGENTO_USE_PRICES','Do you want to use price sheets?');
define('MAGENTO_TEXT_PRICE_SHEET','Magento Price Sheet to use');
define('MAGENTO_SHIP_ID','Magento numeric status code for Shipped Orders');
define('MAGENTO_PARTIAL_ID','Magento numeric status code for Partially Shipped Orders');
define('MAGENTO_CONFIG_SAVED','Magento configuration values updated/saved.');
define('MAGENTO_CATALOG_ADD','Allow upload to Magento Catalog');
define('MAGENTO_CATALOG_CATEGORY_ID','Magento - category id. Needs to match Magento category where product will be located.');
define('MAGENTO_CATALOG_MANUFACTURER','Magento - product manufacturer. Needs to match with the manufacturer name as defined in Magento.');
// audit log messages
define('MAGENTO_LOG_TABS','Magento Inventory Add Tab');
define('MAGENTO_LOG_FIELDS','Magento Inventory Add Field');

?>