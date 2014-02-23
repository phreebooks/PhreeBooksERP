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
//  Path: /modules/shipping/methods/usps/language/en_us/language.php
//
define('MODULE_SHIPPING_USPS_TEXT_TITLE', 'United States Postal Service');
define('MODULE_SHIPPING_USPS_TITLE_SHORT', 'USPS');
define('MODULE_SHIPPING_USPS_TEXT_DESCRIPTION', 'This method currently only supports rate estimation for domestic Express, Priority, and Parcel Post mailings.');
define('MODULE_SHIPPING_USPS_TEXT_INTRODUCTION', 'You will need to have registered an account with USPS at http://www.uspsprioritymail.com/et_regcert.html to use this module. USPS expects you to use pounds as weight measure for your products.');
define('MODULE_SHIPPING_USPS_TRACKING_URL','http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do');

// Key descriptions
define('MODULE_SHIPPING_USPS_TITLE_DESC', 'Title to use for display purposes on shipping rate estimator');
define('MODULE_SHIPPING_USPS_USERID_DESC', 'Enter the USPS USERID assigned to you.');
define('MODULE_SHIPPING_USPS_SERVER_DESC', 'An account at USPS is needed to use the Production server');
define('MODULE_SHIPPING_USPS_MACHINABLE_DESC', 'Are all products shipped machinable based on C700 Package Services 2.0 Nonmachinable PARCEL POST USPS Rules and Regulations?<br /><strong>Note: Nonmachinable packages will usually result in a higher Parcel Post Rate Charge.<br />Packages 35lbs or more, or less than 6 ounces (.375), will be overridden and set to False</strong>');
define('MODULE_SHIPPING_USPS_TYPES_DESC', 'Select the USPS domestic services to be offered by default.');
//define('MODULE_SHIPPING_USPS_TYPES_INTL_DESC', 'Select the international services to be offered:');
define('MODULE_SHIPPING_USPS_SORT_ORDER_DESC', 'Sort order of display. Determines the order which this method appears on all generted lists.');

// Shipping Methods
define('MODULE_SHIPPING_USPS_1DM', 'Express Mail');
define('MODULE_SHIPPING_USPS_1DA', 'Express Flat Rate Env');
define('MODULE_SHIPPING_USPS_1DP', 'Priority Mail');
define('MODULE_SHIPPING_USPS_2DA', 'Priority Flat Rate Env');
define('MODULE_SHIPPING_USPS_2DP', 'Priority Sml Flat Rate Box');
define('MODULE_SHIPPING_USPS_3DA', 'Priority Med Flat Rate Box');
define('MODULE_SHIPPING_USPS_3DS', 'Priority Lrg Flat Rate Box');
define('MODULE_SHIPPING_USPS_GND', 'Parcel Post');
define('MODULE_SHIPPING_USPS_GDR', 'Media Mail');

?>
