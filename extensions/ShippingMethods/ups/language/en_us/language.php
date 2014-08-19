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
//  Path: /modules/shipping/methods/ups/language/en_us/language.php
//

// Set the UPS tracking URL
define('UPS_TRACKING_URL','http://wwwapps.ups.com/etracking/tracking.cgi?tracknums_displayed=5&TypeOfInquiryNumber=T&HTMLVersion=4.0&sort_by=status&InquiryNumber1=');
// http://wwwapps.ups.com/etracking/tracking.cgi?tracknums_displayed=5&TypeOfInquiryNumber=T&HTMLVersion=4.0&sort_by=status&InquiryNumber1=1Z56V9500344364895"

define('MODULE_SHIPPING_UPS_TEXT_TITLE', 'United Parcel Service');
define('MODULE_SHIPPING_UPS_TITLE_SHORT', 'UPS');
define('MODULE_SHIPPING_UPS_TEXT_DESCRIPTION', 'United Parcel Service');

define('MODULE_SHIPPING_UPS_RATE_URL','https://www.ups.com/ups.app/xml/Rate');
define('MODULE_SHIPPING_UPS_RATE_URL_TEST','https://wwwcie.ups.com/ups.app/xml/Rate');
define('MODULE_SHIPPING_UPS_TNT_URL','https://www.ups.com/ups.app/xml/TimeInTransit');
define('MODULE_SHIPPING_UPS_TNT_URL_TEST','https://wwwcie.ups.com/ups.app/xml/TimeInTransit');
define('MODULE_SHIPPING_UPS_SHIP_URL','https://www.ups.com/ups.app/xml/ShipConfirm');
define('MODULE_SHIPPING_UPS_SHIP_URL_TEST','https://wwwcie.ups.com/ups.app/xml/ShipConfirm');
define('MODULE_SHIPPING_UPS_LABEL_URL','https://www.ups.com/ups.app/xml/ShipAccept');
define('MODULE_SHIPPING_UPS_LABEL_URL_TEST','https://wwwcie.ups.com/ups.app/xml/ShipAccept');
define('MODULE_SHIPPING_UPS_VOID_SHIPMENT','https://www.ups.com/ups.app/xml/Void');
define('MODULE_SHIPPING_UPS_VOID_SHIPMENT_TEST','https://wwwcie.ups.com/ups.app/xml/Void');
define('MODULE_SHIPPING_UPS_QUANTUM_VIEW','https://www.ups.com/ups.app/xml/QVEvents');
define('MODULE_SHIPPING_UPS_QUANTUM_VIEW_TEST','https://wwwcie.ups.com/ups.app/xml/QVEvents');

// Key descriptions
define('MODULE_SHIPPING_UPS_TITLE_DESC', 'Title to use for display purposes on shipping rate estimator');
define('MODULE_SHIPPING_UPS_SHIPPER_NUMBER_DESC', 'Enter the UPS shipper number to use for rate estimates');
define('MODULE_SHIPPING_UPS_TEST_MODE_DESC', 'Test mode used for testing shipping labels');
define('MODULE_SHIPPING_UPS_USER_ID_DESC', 'Enter the UPS account ID registered with UPS to access rate estimator');
define('MODULE_SHIPPING_UPS_PASSWORD_DESC', 'Enter the password used to access your UPS account');
define('MODULE_SHIPPING_UPS_ACCESS_KEY_DESC', 'Enter the XML Access Key supplied to you from UPS.');
define('MODULE_SHIPPING_UPS_PRINTER_TYPE_DESC', 'Type of printer to use for printing labels. GIF for plain paper, Thermal for UPS 2442 Thermal Label Printer (See Help file before selecting Thermal printer)');
define('MODULE_SHIPPING_UPS_LABEL_SIZE_DESC', 'Size of label to use for thermal label printing, valid values are 6 or 8 inches');
define('MODULE_SHIPPING_UPS_TYPES_DESC', 'Select the UPS services to be offered by default.');
define('MODULE_SHIPPING_UPS_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');

// Shipping Methods
define('ups_GND',   'Ground');
define('ups_1DEam', 'Next Day Air Early AM');
define('ups_1Dam',  'Next Day Air');
define('ups_1Dpm',  'Next Day Air Saver');
define('ups_2Dam',  '2nd Day Air Early AM');
define('ups_2Dpm',  '2nd Day Air');
define('ups_3Dpm',  '3 Day Select');
define('ups_I2DEam','Worldwide Express Plus');
define('ups_I2Dam', 'Worldwide Express');
define('ups_I3D',   'Worldwide Expedited');
define('ups_IGND',  'Standard (Canada)');

define('SHIPPING_UPS_VIEW_REPORTS','View Reports for ');
define('SHIPPING_UPS_CLOSE_REPORTS','Closing Report');
define('SHIPPING_UPS_MULTIWGHT_REPORTS','Multiweight Report');
define('SHIPPING_UPS_HAZMAT_REPORTS','Hazmat Report');

define('SHIPPING_UPS_RATE_ERROR','UPS rate response error: ');
define('SHIPPING_UPS_RATE_CITY_MATCH','City doesn\'t match zip code.');
define('SHIPPING_UPS_RATE_TRANSIT',' Day(s) Transit, arrives ');
define('SHIPPING_UPS_TNT_ERROR',' UPS Time in Transit Error # ');

// Ship manager Defines
define('SRV_SHIP_UPS','Ship a Package');
define('SRV_SHIP_UPS_RECP_INFO','Recepient Information');
define('SRV_SHIP_UPS_EMAIL_NOTIFY','Email Notifications');
define('SRV_SHIP_UPS_BILL_DETAIL','Billing Details');

define('SHIPPING_UPS_CURL_ERROR','cURL Error: ');
define('SHIPPING_UPS_PACKAGE_ERROR','Died having trouble splitting the shipment into pieces. The shipment weight was: ');
define('SHIPPING_UPS_ERROR_WEIGHT_150','Single shipment weight cannot be greater than 150 lbs to use the UPS module.');
define('SHIPPING_UPS_ERROR_POSTAL_CODE','Postal Code is required to use the UPS module');

?>