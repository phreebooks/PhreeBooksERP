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
//  Path: /modules/shipping/language/en_us/admin.php
//

// Module information
define('MODULE_SHIPPING_TITLE','Shipping Module');
define('MODULE_SHIPPING_DESCRIPTION','The shipping module is a wrapper for user configurable shipping methods. Some methods are included with the core package and others are available for download from the PhreeSoft website.');

/************************** (Shipping Defaults) ***********************************************/
define('CD_10_01_DESC', 'Sets the default unit of measure for all packages. Valid values are: Pounds, Kilograms');
define('CD_10_02_DESC', 'Default currency to use for shipments. Valid values are: US Dollars, Euros');
define('CD_10_03_DESC', 'Package unit of measure. Valid values are: Inches, Centimeters');
define('CD_10_04_DESC', 'Default residential ship box (unchecked - Commercial, checked - Residential)');
define('CD_10_05_DESC', 'Default package type to use for shipping');
define('CD_10_06_DESC', 'Default type of pickup service for your package service');
define('CD_10_07_DESC', 'Default package dimensions to use for a standard shipment (in units specified above).');
define('CD_10_10_DESC', 'Additional handling charge checkbox');
define('CD_10_14_DESC', 'Shipment insurance selection option.');
define('CD_10_20_DESC', 'Allow heavy shipments to be broken down to use small package service');
define('CD_10_26_DESC', 'Delivery confirmation checkbox');
define('CD_10_32_DESC', 'Additional handling charge checkbox');
define('CD_10_38_DESC', 'Enable the COD checkbox and options');
define('CD_10_44_DESC', 'Saturday pickup checkbox');
define('CD_10_48_DESC', 'Saturday delivery checkbox');
define('CD_10_52_DESC', 'Hazardous material checkbox');
define('CD_10_56_DESC', 'Dry ice checkbox');
define('CD_10_60_DESC', 'Return services checkbox');

define('NEXT_SHIPMENT_NUM_DESC','Next Shipment Number');
define('TEXT_SHIPPING_PREFS','Shipping Address Book Settings');
define('CONTACT_SHIP_FIELD_REQ', 'Whether or not to require field: %s to be entered for a new shipping address');
define('PB_PF_SHIP_METHOD','Ship Method');
define('SHIPPING_METHOD','Select Method:');
define('SHIPPING_MONTH','Select Month:');
define('SHIPPING_YEAR','Select Year:');
define('SHIPPING_TOOLS_TITLE','Shipping Label File Maintenance');
define('SHIPPING_TOOLS_CLEAN_LOG_DESC','This operation creates a downloaded backup of your shipping label files. This will help keep the server storage size down and reduce company backup file sizes. Backing up these files is recommended before cleaning out old files to preserve PhreeBooks transaction history. <br />INFORMATION: Cleaning out the shipping labels will leave the current records in the database shipping manager and logs.');

?>