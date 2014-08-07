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
//  Path: /modules/payment/methods/paypal_nvp/language/en_us/language.php
//
// Admin Configuration Items
define('MODULE_PAYMENT_PAYPAL_NVP_TEXT_TITLE', 'PayPal Payment Pro');
define('MODULE_PAYMENT_PAYPAL_NVP_TEXT_DESCRIPTION','Accept credit card payments through the PayPal NVP payment gateway');
define('MODULE_PAYMENT_PAYPAL_NVP_TEXT_INTRODUCTION', 'When in test mode, cards return a success code but are not processed.');
define('MODULE_PAYMENT_PAYPAL_NVP_USER_ID_DESC','The PayPal user name:');
define('MODULE_PAYMENT_PAYPAL_NVP_PW_DESC','Password used for the PayPal Payment Pro service:');
define('MODULE_PAYMENT_PAYPAL_NVP_SIG_DESC','Signature issued by PayPal for access to the API.');
define('MODULE_PAYMENT_PAYPAL_NVP_TESTMODE_DESC','Transaction mode used for processing orders');
define('MODULE_PAYMENT_PAYPAL_NVP_AUTHORIZATION_TYPE_DESC','Do you want submitted credit card transactions to be authorized only, or authorized and captured?');
// General
define('MODULE_PAYMENT_PAYPAL_NVP_TEXT_CREDIT_CARD_OWNER','First / Last Name');
// PayPal urls, wsdls
define('MODULE_PAYMENT_PAYPAL_NVP_SANDBOX_SIG_URL','https://api-3t.sandbox.paypal.com/nvp');
define('MODULE_PAYMENT_PAYPAL_NVP_SANDBOX_CERT_URL','https://api.sandbox.paypal.com/nvp');
//define('MODULE_PAYMENT_PAYPAL_NVP_SANDBOX_WSDL','https://www.sandbox.paypal.com/wsdl/PayPalSvc.wsdl');
define('MODULE_PAYMENT_PAYPAL_NVP_LIVE_SIG_URL','https://api-3t.paypal.com/nvp');
define('MODULE_PAYMENT_PAYPAL_NVP_LIVE_CERT_URL','https://api.paypal.com/nvp');
//define('MODULE_PAYMENT_PAYPAL_NVP_LIVE_WDSL','https://www.paypal.com/wsdl/PayPalSvc.wsdl');
// Catalog Items
define('MODULE_PAYMENT_PAYPAL_NVP_SUCCESSE_CODE','%s - Transaction ID: %s --> CVV2 results: %s');
define('MODULE_PAYMENT_PAYPAL_NVP_DECLINE_CODE','Decline Code #');
?>
