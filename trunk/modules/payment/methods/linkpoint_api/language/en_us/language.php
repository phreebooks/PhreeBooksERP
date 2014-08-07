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
//  Author: Harry Lu @ 2009/08/01
//  Path: /modules/payment/methods/linkpoint_api/language/en_us/language.php
//
 
define('MODULE_PAYMENT_LINKPOINT_API_TEXT_TITLE', 'LinkPoint Gateway');
define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DESCRIPTION','Accept credit card payments through the LinkPoint/YourPay API payment gateway');

//    define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DESCRIPTION', '<a target="_blank" href="https://secure.linkpt.net/lpcentral/servlet/LPCLogin">Linkpoint/YourPay Merchant Login</a>' . (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE != 'LIVE: Production' ? '<br /><br /><strong>Linkpoint/YourPay API Test Card Numbers:</strong><br /><strong>Visa:</strong> 4111111111111111<br /><strong>MasterCard:</strong> 5419840000000003<br /><strong>Amex:</strong> 371111111111111<br /><strong>Discover:</strong> 6011111111111111' : '') . '<br />');
define('MODULE_PAYMENT_LINKPOINT_API_TEXT_INTRODUCTION', '<a target="_blank" href="http://www.zen-cart.com/index.php?main_page=infopages&pages_id=30">Click Here to Sign Up for an Account</a><br /><br /><a target="_blank" href="https://secure.linkpt.net/lpcentral/servlet/LPCLogin">Linkpoint/YourPay API Merchant Area</a><br /><br /><a target="_blank" href="http://tutorials.zen-cart.com/index.php?article=298">Click here for <strong>SETUP/Troubleshooting Instructions</strong></a><br /><br /><strong>Requirements:</strong><br /><hr />*<strong>LinkPoint or YourPay Account</strong> (see link above to signup)<br />*<strong>cURL is required </strong>and MUST be compiled into PHP by your hosting company<br />*<strong>Port 1129</strong> is used for bidirectional communication with the gateway, so must be open on your host\'s router/firewall<br />*<strong>PEM RSA Key File </strong>Digital Certificate:<br />To obtain and upload your Digital Certificate (.PEM) key:<br />- Log in to your LinkPoint/Yourpay account on their website<br />- Click on "Support" in the Main Menu Bar.<br />- Click on the word "Download Center" under Downloads in the Side Menu Box.<br />- Click on the word "download" beside the "Store PEM File" section on the right-hand side of the page.<br />- Key in necessary information to start download. You will need to supply your actual SSN or Tax ID which you submitted during the merchant account boarding process.<br />- Upload this file to includes/modules/payment/linkpoint_api/XXXXXX.pem (provided by LinkPoint - xxxxxx is your store id)');

define('MODULE_PAYMENT_LINKPOINT_API_LOGIN_DESC','Please enter your Linkpoint/YourPay Merchant ID.<br />This is the same as the number in the PEM digital certificate filename for your account.');
define('MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE_DESC','<strong>Production:</strong> Use this for live stores.<br />or, select these options if you wish to test the module:<br /><strong>Successful:</strong> Use to TEST by forcing a Successful transaction<br /><strong>Decline:</strong> Use to TEST forcing a Failed transaction');
define('MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE_DESC','Do you want submitted credit card transactions to be authorized only, or immediately charged/captured?<br />In most cases you will want to do an <strong>Immediate Charge</strong> to capture payment immediately. In some situations, you may prefer to simply <strong>Authorize</strong> transactions, and then manually use your Merchant Terminal to formally capture the payments (esp if payment amounts may fluctuate between placing the order and shipping it)');
define('MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT_DESC','Do you want to be notified by email of suspected fraudulent Credit Card activity?<br />(sends to Store Owner Email Address)');
define('MODULE_PAYMENT_LINKPOINT_API_STORE_DATA_DESC','If you enable this option, extended details of each transaction will be stored, enabling you to more effectively conduct audits of fraudulent activity or even track/match order information between Zen Cart and your LinkPoint records. You can view this data in Admin->Customers->Linkpoint CC Review.');
define('MODULE_PAYMENT_LINKPOINT_API_DEBUG_DESC','Would you like to enable debug mode?  Choosing Alert mode will email logs of failed transactions to the store owner.');
define('MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_DESC','Transaction mode used for processing orders');

// general defines
define('TEXT_TEST_SUCCESS','Test - Return Success');
define('TEXT_TEST_FAIL','Test - Return Decline');
define('TEXT_DEV_TEST','Development - Test');
define('TEXT_OFF','Off');
define('TEXT_FAIL_ALERTS','Failure Alerts Only');
define('TEXT_LOG_FILE','Log File');
define('TEXT_LOG_EMAIL','Log and Email');


  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DECLINED_AVS_MESSAGE', 'Invalid Billing Address.  Please re-enter your card information, try another card, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DECLINED_GENERAL_MESSAGE', 'Your card has been declined.  Please re-enter your card information, try another card, or contact the store owner for assistance.');
  
  define('ALERT_LINKPOINT_API_PREAUTH_TRANS', '***AUTHORIZATION ONLY -- CHARGES WILL BE SETTLED LATER BY THE ADMINISTRATOR.***' . "\n");
  define('ALERT_LINKPOINT_API_TEST_FORCED_SUCCESSFUL', 'NOTE: This was a TEST transaction...forced to return a SUCCESS response.');
  define('ALERT_LINKPOINT_API_TEST_FORCED_DECLINED', 'NOTE: This was a TEST transaction...forced to return a DECLINED response.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_NOT_CONFIGURED', '<b>&nbsp;(NOTE: Module is not configured yet)</b>');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_PEMFILE_MISSING', '<b>&nbsp;The xyzxyz.pem certificate file cannot be found.</b>');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_ERROR_CURL_NOT_FOUND', 'CURL functions not found - required for Linkpoint API payment module');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DUPLICATE_MESSAGE', 'You have posted duplicate transaction');
  

  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_FAILURE_MESSAGE', 'We apologize for the inconvenience, but we are presently unable to contact the Credit Card company for authorization. Please contact the Store Owner for payment alternatives.');
  // note: the above error can occur as a result of:
     // - port 1129 not open for bidirectional communication 
     // - CURL is not installed or not functioning
     // - incorrect or invalid or "no" .PEM file found in modules/payment/linkpoint_api folder
     // - In general it means that there was no valid connection made to the gateway... it was stopped before it got outside your server
  
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_GENERAL_ERROR', 'We are sorry. There was a system error while processing your card. Your information is safe. Please notify the Store Owner to arrange alternate payment options.');
  // note: the above error is a general error message which is reported when serious and known error conditions occur. Further details are given immediately following the display of this message. If database storage is enabled, details can be found there too.
  
  
  // Admin definitions

  define('MODULE_PAYMENT_LINKPOINT_API_LINKPOINT_ORDER_ID', 'Linkpoint Order ID:');
  define('MODULE_PAYMENT_LINKPOINT_API_AVS_RESPONSE', 'AVS Response:');
  define('MODULE_PAYMENT_LINKPOINT_API_MESSAGE', 'Response Message:');
  define('MODULE_PAYMENT_LINKPOINT_API_APPROVAL_CODE', 'Approval Code:');
  define('MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_REFERENCE_NUMBER', 'Reference Number:');
  define('MODULE_PAYMENT_LINKPOINT_API_FRAUD_SCORE', 'Fraud Score:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_TEST_MODE', '<b>&nbsp;(NOTE: Module is in testing mode)</b>');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_ORDERTYPE', 'Order Type:');


// admin tools:
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND', 'Error: Could not find transaction details for the record specified.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_BUTTON_TEXT', 'Do Refund');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_CONFIRM_ERROR', 'Error: You requested to do a refund but did not check the Confirmation box.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_REFUND_AMOUNT', 'Error: You requested a refund but entered an invalid amount.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CC_NUM_REQUIRED_ERROR', 'Error: You requested a refund but didn\'t enter the last 4 digits of the Credit Card number.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_INITIATED', 'Refund Initiated. Transaction ID: %s - Order ID: %s');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPTURE_CONFIRM_ERROR', 'Error: You requested to do a capture but did not check the Confirmation box.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_BUTTON_TEXT', 'Do Capture');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_CAPTURE_AMOUNT', 'Error: You requested a capture but need to enter an amount.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_TRANS_ID_REQUIRED_ERROR', 'Error: You need to specify a Transaction ID.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPT_INITIATED', 'Funds Capture initiated. Amount: %s.  Transaction ID: %s - AuthCode: %s');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_BUTTON_TEXT', 'Do Void');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_CONFIRM_ERROR', 'Error: You requested a Void but did not check the Confirmation box.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_INITIATED', 'Void Initiated. Transaction ID: %s - Order ID: %s ');

  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TITLE', '<strong>Refund Transactions</strong>');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND', 'You may refund money to the customer\'s original credit card here.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_CONFIRM_CHECK', 'Check this box to confirm your intent: ');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_AMOUNT_TEXT', 'Enter the amount you wish to refund');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_DEFAULT_TEXT', 'enter Trans.ID');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_CC_NUM_TEXT', 'Enter the last 4 digits of the Credit Card you are refunding.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TRANS_ID', 'Enter the original Transaction ID <em>(which usually looks like this: <strong>1193684363</strong>)</em>:');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TEXT_COMMENTS', 'Notes (will show on Order History):');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_DEFAULT_MESSAGE', 'Refund Issued');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_SUFFIX', 'You may refund an order up to the amount already captured. You must supply the last 4 digits of the credit card number used on the initial order.<br />Refunds cannot be issued if the card has expired. To refund an expired card, issue a credit using the merchant terminal instead.');

  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TITLE', '<strong>Capture Transactions</strong>');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE', 'You may capture previously-authorized funds here:');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_AMOUNT_TEXT', 'Enter the amount to Capture: ');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPTURE_CONFIRM_CHECK', 'Check this box to confirm your intent: ');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TRANS_ID', 'Enter the original Order Number <em>(ie: <strong>5138-i4wcYM</strong>)</em> : ');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_DEFAULT_TEXT', 'enter Order Number');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TEXT_COMMENTS', 'Notes (will show on Order History):');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_DEFAULT_MESSAGE', 'Settled previously-authorized funds.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_SUFFIX', 'Captures must be performed within 2-10 days of the original authorization depending on your merchant bank requirement. You may  capture an order ONLY ONCE. <br />Please be sure the amount specified is correct.<br />If you leave the amount blank, the original amount will be used instead.');

  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_TITLE', '<strong>Voiding Transactions</strong>');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID', 'You may void a transaction (preauth/capture/refund) which has not yet been settled. Please enter the original Transaction ID <em>(usually looks like this: <strong>1193684363</strong>)</em>:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_CONFIRM_CHECK', 'Check this box to confirm your intent:');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_DEFAULT_TEXT', 'enter Trans.ID');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_TEXT_COMMENTS', 'Notes (will show on Order History):');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_DEFAULT_MESSAGE', 'Transaction Canceled');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_SUFFIX', 'Voids must be completed before the original transaction is settled in the daily batch, which occurs at 7:00PM Pacific Time.');


?>