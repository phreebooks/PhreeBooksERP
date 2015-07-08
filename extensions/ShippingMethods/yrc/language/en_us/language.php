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
//  Path: /modules/shipping/methods/yrc/language/en_us/language.php
//

define('MODULE_SHIPPING_YRC_TEXT_TITLE', 'YRC Freight');
define('MODULE_SHIPPING_YRC_TITLE_SHORT', 'YRC');
define('MODULE_SHIPPING_YRC_TEXT_DESCRIPTION', 'YRC Freight Lines');
define('MODULE_SHIPPING_YRC_TEXT_INTRODUCTION', '<h3>YRC Freight (Quotes only)</h3>
This method provides discounted quotes based on your YRC contracted account. The credentials (user name, password, and BusId) must be applied for through your local YRC Representative.');
// key descriptions
define('MODULE_SHIPPING_YRC_TITLE_DESC','Title to use for display purposes on shipping with YRC.');
define('MODULE_SHIPPING_YRC_USER_ID_DESC','Enter the YRC user name provided to you by YRC.');
define('MODULE_SHIPPING_YRC_PASSWORD_DESC','Enter the YRC password provided to you by YRC.');
define('MODULE_SHIPPING_YRC_BUSID_DESC','Enter the YRC BusID provided to you by YRC.');
define('MODULE_SHIPPING_YRC_SORT_ORDER_DESC','Sort order of display. Lowest is displayed first.');
// Delivery Methods
define('MODULE_SHIPPING_YRC_GDF','Standard LTL Ground Freight');
define('MODULE_SHIPPING_YRC_2DF','Guaranteed Standard by Noon');
define('MODULE_SHIPPING_YRC_3DF','Guaranteed Standard by 5PM');
// General defines
define('SHIPPING_YRC_SHIPMENTS_ON','YRC Shipments on ');
define('SHIPPING_YRC_CURL_ERROR','There was an error communicating with the YRC server:');
define('SHIPPING_YRC_RATE_ERROR','YRC rate response error: ');
define('SHIPPING_YRC_RATE_CITY_MATCH','City doesn\'t match zip code.');

?>