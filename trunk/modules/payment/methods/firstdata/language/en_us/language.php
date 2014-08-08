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
//  Path: /modules/payment/methods/firstdata/language/en_us/language.php
//

// Admin Configuration Items
define('MODULE_PAYMENT_FIRSTDATA_TEXT_TITLE', 'FirstData Gateway'); // Payment option title as displayed in the admin
define('MODULE_PAYMENT_FIRSTDATA_TEXT_DESCRIPTION','Accept credit card payments through the FirstData Global payment gateway');
define('MODULE_PAYMENT_FIRSTDATA_TEXT_INTRODUCTION', 'When in test mode, cards return an Approved code but are not processed.<br /><br />');

define('MODULE_PAYMENT_FIRSTDATA_CONFIG_FILE_DESC', 'The Store ID used for the FirstData Network service.');
define('MODULE_PAYMENT_FIRSTDATA_KEY_FILE_DESC', 'The PEM key file with path used for the FirstData Network service. File must be placed in the directory modules/firstdata. Typipcally the store ID with the .pem extension.');
define('MODULE_PAYMENT_FIRSTDATA_HOST_DESC', 'The host used for the FirstData Network service. Provided in welcome packet. Should be something like: https://secure.linkpt.net');
define('MODULE_PAYMENT_FIRSTDATA_PORT_DESC', 'The port used for the FirstData Network service. Provided in welcome packet.');
define('MODULE_PAYMENT_FIRSTDATA_TESTMODE_DESC', 'Transaction mode used for processing orders');
define('MODULE_PAYMENT_FIRSTDATA_AUTHORIZATION_TYPE_DESC', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?');

?>