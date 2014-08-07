<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
define('TEXT_DEFAULT_PACKAGE_TYPE_TO_USE_FOR_SHIPPING', 'Default package type to use for shipping');
define('TEXT_DEFAULT_TYPE_OF_PICKUP_SERVICE_FOR_YOUR_PACKAGE_SERVICE', 'Default type of pickup service for your package service');
define('TEXT_DEFAULT_PACKAGE_DIMENSIONS_TO_USE_FOR_A_STANDARD_SHIPMENT', 'Default package dimensions to use for a standard shipment (in units specified above).');
define('TEXT_ADDITIONAL_HANDLING_CHARGE_CHECKBOX', 'Additional handling charge checkbox');
define('TEXT_SHIPMENT_INSURANCE_SELECTION_OPTION', 'Shipment insurance selection option.');
define('TEXT_ALLOW_HEAVY_SHIPMENTS_TO_BE_BROKEN_DOWN_TO_USE_SMALL_PACKAGE_SERVICE', 'Allow heavy shipments to be broken down to use small package service');
define('TEXT_DELIVERY_CONFIRMATION_CHECKBOX', 'Delivery confirmation checkbox');
define('CD_10_32_DESC', 'Additional handling charge checkbox');
define('TEXT_ENABLE_THE_COD_CHECKBOX_AND_OPTIONS', 'Enable the COD checkbox and options');
define('TEXT_SATURDAY_PICKUP_CHECKBOX', 'Saturday pickup checkbox');
define('TEXT_SATURDAY_DELIVERY_CHECKBOX', 'Saturday delivery checkbox');
define('TEXT_HAZARDOUS_MATERIAL_CHECKBOX', 'Hazardous material checkbox');
define('TEXT_DRY_ICE_CHECKBOX', 'Dry ice checkbox');
define('TEXT_RETURN_SERVICES_CHECKBOX', 'Return services checkbox');

define('NEXT_SHIPMENT_NUM_DESC','Next Shipment Number');
define('TEXT_SHIPPING_ADDRESS_BOOK_SETTINGS','Shipping Address Book Settings');
define('CONTACT_SHIP_FIELD_REQ', 'Whether or not to require field: %s to be entered for a new shipping address');
define('TEXT_SHIP_METHOD','Ship Method');
define('SHIPPING_METHOD','Select Method:');
define('SHIPPING_MONTH','Select Month:');
define('SHIPPING_YEAR','Select Year:');
define('SHIPPING_TOOLS_TITLE','Shipping Label File Maintenance');
define('SHIPPING_TOOLS_CLEAN_LOG_DESC','This operation creates a downloaded backup of your shipping label files. This will help keep the server storage size down and reduce company backup file sizes. Backing up these files is recommended before cleaning out old files to preserve PhreeBooks transaction history. <br />INFORMATION: Cleaning out the shipping labels will leave the current records in the database shipping manager and logs.');

?>