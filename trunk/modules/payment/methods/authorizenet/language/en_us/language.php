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
//  Path: /modules/payment/methods/authorizenet/language/en_us/language.php
//

// Admin Configuration Items
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TITLE', 'Authorize.net'); // Payment option title as displayed in the admin
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION','Accept credit card payments through the Authorize.net payment gateway');
define('MODULE_PAYMENT_AUTHORIZENET_LOGIN_DESC', 'The API Login ID used for the Authorize.net service');
define('MODULE_PAYMENT_AUTHORIZENET_TXNKEY_DESC', 'Transaction Key used for encrypting TP data<br />(See your Authorizenet Account->Security Settings->API Login ID and Transaction Key for details.)');
define('MODULE_PAYMENT_AUTHORIZENET_MD5HASH_DESC', 'Encryption key used for validating received transaction data (MAX 20 CHARACTERS, exactly as you entered in Authorize.net account settings). Or leave blank.');
define('MODULE_PAYMENT_AUTHORIZENET_TESTMODE_DESC', 'Transaction mode used for processing orders');
define('MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE_DESC', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?');
define('MODULE_PAYMENT_AUTHORIZENET_USE_CVV_DESC', 'Do you want to ask the customer for the card\'s CVV number');
define('MODULE_PAYMENT_AUTHORIZENET_DEBUGGING_DESC', 'Would you like to enable debug mode?  A complete detailed log of failed transactions may be emailed to the store owner.');
// General defines


define('MODULE_PAYMENT_AUTHORIZENET_TEXT_AUTHENTICITY_WARNING', 'WARNING: Security hash problem. Please contact store-owner immediately. Your order has *not* been fully authorized.');
define('MODULE_PAYMENT_AUTHORIZENET_ENTRY_REFUND_BUTTON_TEXT', 'Do Refund');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_REFUND_CONFIRM_ERROR', 'Error: You requested to do a refund but did not check the Confirmation box.');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_INVALID_REFUND_AMOUNT', 'Error: You requested a refund but entered an invalid amount.');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CC_NUM_REQUIRED_ERROR', 'Error: You requested a refund but didn\'t enter the last 4 digits of the Credit Card number.');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_REFUND_INITIATED', 'Refund Initiated. Transaction ID: %s - Auth Code: %s');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TRANS_ID_REQUIRED_ERROR', 'Error: You need to specify a Transaction ID.');
define('MODULE_PAYMENT_AUTHORIZENET_ENTRY_VOID_BUTTON_TEXT', 'Do Void');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_VOID_CONFIRM_ERROR', 'Error: You requested a Void but did not check the Confirmation box.');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_VOID_INITIATED', 'Void Initiated. Transaction ID: %s - Auth Code: %s ');

?>