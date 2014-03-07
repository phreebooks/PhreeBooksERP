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
//  Path: /modules/shipping/methods/ups/language/en_us/language.php
//
define('MODULE_SHIPPING_UPS_TEXT_TITLE',        'United Parcel Service');
define('MODULE_SHIPPING_UPS_TITLE_SHORT',       'UPS');
define('MODULE_SHIPPING_UPS_TEXT_DESCRIPTION',  'United Parcel Service - This method currently only supports labels for US domestic shipments. No LTL freight quotes or labels are supported. <b>(UPS doesn\'t support PHP, send them an email in protest!)</b>');
define('MODULE_SHIPPING_UPS_TEXT_INTRODUCTION', 'Step 1: Create and account at www.ups.com.<br />Step 2: Request an access key.<br />Step 3: Apply for production access<br />(Note: There are more steps, please help complete this procedure by posting to the phreesoft forum or email the project admin.)');

// key descriptions
define('MODULE_SHIPPING_UPS_TITLE_DESC','Title to use for display purposes on shipping rate estimator');
define('MODULE_SHIPPING_UPS_SHIPPER_NUMBER_DESC','Enter the UPS shipper number to use for rate estimates');
define('MODULE_SHIPPING_UPS_USER_ID_DESC','Enter the UPS account ID registered with UPS to access rate estimator');
define('MODULE_SHIPPING_UPS_PASSWORD_DESC','Enter the password used to access your UPS account');
define('MODULE_SHIPPING_UPS_ACCESS_KEY_DESC','Enter the XML Access Key supplied to you from UPS.');
define('MODULE_SHIPPING_UPS_TEST_MODE_DESC','Test mode used for testing shipping labels');
define('MODULE_SHIPPING_UPS_PRINTER_TYPE_DESC','Type of printer to use for printing labels. GIF for plain paper, Thermal for UPS 2442 Thermal Label Printer (See Help file before selecting Thermal printer)');
define('MODULE_SHIPPING_UPS_PRINTER_NAME_DESC','Sets then name of the printer to use for printing labels as defined in the printer preferences for the local workstation.');
define('MODULE_SHIPPING_UPS_LABEL_SIZE_DESC', 'Size of label to use for thermal label printing, valid values are 6 or 8 inches');
define('MODULE_SHIPPING_UPS_TYPES_DESC','Select the UPS services to be offered by default.');
define('MODULE_SHIPPING_UPS_SORT_ORDER_DESC','Sort order of display. Lowest is displayed first.');

define('MODULE_SHIPPING_UPS_GND', 'Ground');
define('MODULE_SHIPPING_UPS_GDF', 'Ground Freight');
define('MODULE_SHIPPING_UPS_1DM', 'Next Day Air Early AM');
define('MODULE_SHIPPING_UPS_1DA', 'Next Day Air');
define('MODULE_SHIPPING_UPS_1DP', 'Next Day Air Saver');
define('MODULE_SHIPPING_UPS_2DM', '2nd Day Air Early AM');
define('MODULE_SHIPPING_UPS_2DP', '2nd Day Air');
define('MODULE_SHIPPING_UPS_3DS', '3 Day Select');
define('MODULE_SHIPPING_UPS_XDM', 'Worldwide Express Plus');
define('MODULE_SHIPPING_UPS_XPR', 'Worldwide Express');
define('MODULE_SHIPPING_UPS_XPD', 'Worldwide Expedited');
define('MODULE_SHIPPING_UPS_STD', 'Standard (Canada)');

define('SHIPPING_UPS_VIEW_REPORTS','View Reports for ');
define('SHIPPING_UPS_CLOSE_REPORTS','Closing Report');
define('SHIPPING_UPS_MULTIWGHT_REPORTS','Multiweight Report');
define('SHIPPING_UPS_HAZMAT_REPORTS','Hazmat Report');
define('SHIPPING_UPS_SHIPMENTS_ON','UPS Shipments on ');
define('TEXT_PRINTABLE_IMAGE','Printable Image');

define('SHIPPING_UPS_RATE_ERROR','UPS rate response error: ');
define('SHIPPING_UPS_RATE_CITY_MATCH','City doesn\'t match zip code.');
define('SHIPPING_UPS_RATE_TRANSIT',' Day(s) Transit, arrives ');
define('SHIPPING_UPS_TNT_ERROR',' UPS Time in Transit Error # ');
// Ship Manager Defines
define('SRV_SHIP_UPS','Ship a Package');
define('SHIPPING_UPS_CURL_ERROR','cURL Error: ');
define('SHIPPING_UPS_PACKAGE_ERROR','Died having trouble splitting the shipment into pieces. The shipment weight was: ');
define('SHIPPING_UPS_ERROR_WEIGHT_150','Single shipment weight cannot be greater than 150 lbs to use the UPS module.');
define('SHIPPING_UPS_ERROR_POSTAL_CODE','Postal Code is required to use the UPS module');

?>