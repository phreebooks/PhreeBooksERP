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
//  Path: /modules/shipping/methods/endicia/language/en_us/language.php
//
define('MODULE_SHIPPING_ENDICIA_TEXT_TITLE', 'US Postal Service');
define('MODULE_SHIPPING_ENDICIA_TEXT_DESCRIPTION', 'US Postal Service (powered by Endicia)');
define('MODULE_SHIPPING_ENDICIA_TEXT_INTRODUCTION', '<h3>Step 1. Register for an account</h3>
<p>Open the Endicia registration application form and follow the instructions to create an account. Usually the basic service is all that is needed (about $15.95 per month). <a href="http://www.endicia.com/labelserver/login.cfm?partid=lpst" target="_blank"><b>Click HERE to Signup</b></a></p>
<h3>Step 2. Wait for Confirmation</h3><p>Check you email for a confirmation from Endicia with your account number. Enter it in the field below, save the changes. Also enter your temporary Pass Phrase you used during registration.</p>
<h3>Step 3. Change Pass Phrase to Activate Account</h3><p>Navigate to Tools -> Shipping Manager -> US Postal Service tab. Click the <b>Change Pass Phrase</b> button to change your temporary pass phrase used to sign up for the account to a new pass phrase. The new pass phrase will be automatically updated in the field below.</p>
<h3>Step 4. Buy Postage</h3><p>Navigate to Tools -> Shipping Manager -> US Postal Service tab. Select an amount and click the <b>Buy Postage</b> button to add a balance to your postage account.</p>
<h3>Step 5. Configure Options</h3><p>Make sure the mode is set to Production for rate estimation and label printing. Contact PhreeSoft for the Dial-A-Zip password as it is a shared account with PhreeSoft and we would like to know who is using this service. You should now be able to retrieve rates, and print labels using the service. NOTE: PhreeSoft has performed all label testing on a Zebra ZP 505 printer. Older Eltron/Zebra 2442 and 2844 printers don\'t support the bar code types necessary for USPS postage printing.</p>');
// key descriptions
define('MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER_DESC','Enter the Endicia account number to use');
define('MODULE_SHIPPING_ENDICIA_PASS_PHRASE_DESC','Enter your Endicia account pass phrase');
// Delivery Methods
define('MODULE_SHIPPING_ENDICIA_GND','Parcel Post');
define('MODULE_SHIPPING_ENDICIA_GDR','Parcel Select');
define('MODULE_SHIPPING_ENDICIA_1DM','Priority');
define('MODULE_SHIPPING_ENDICIA_1DA','Express');
define('MODULE_SHIPPING_ENDICIA_1DP','First Class');
define('MODULE_SHIPPING_ENDICIA_2DA','Critical Mail');
define('MODULE_SHIPPING_ENDICIA_2DP','Library Mail');
define('MODULE_SHIPPING_ENDICIA_3DA','Standard Mail');
define('MODULE_SHIPPING_ENDICIA_3DS','Media Mail');
//define('MODULE_SHIPPING_ENDICIA_XDM','Express Mail Int');
//define('MODULE_SHIPPING_ENDICIA_XPR','Priority Mail Int');
//define('MODULE_SHIPPING_ENDICIA_X3D','First Class Mail Int');
// General defines
define('TEXT_PASSPHRASE_NOW','Enter Current Passphrase');
define('TEXT_PASSPHRASE','Enter New Passphrase');
define('TEXT_PASSPHRASE_DUP','Re-enter Passphrase');
define('TEXT_PARTNER_ID','Partner ID');
define('SHIPPING_ENDICIA_LABEL_STATUS','Successfully retrieved the USPS label, tracking # %s. Your postage balance is: %s');
define('SHIPPING_ENDICIA_ERROR_TOO_HEAVY','The package weight exceeds the maximum supported by this carrier!');
define('SHIPPING_ENDICIA_PURCHASE_SUCCESS_MSG','Your purchase was successful, your balance is now %s (transaction reference %s)');
define('SHIPPING_ENDICIA_PASSPHRASE_CHANGE_DESC','Pass Phrase must be at least 5 characters long with a maximum of 64 characters. For added security, the Pass Phrase should be at least 10 characters long and include more than one word, use at least one uppercase and lowercase letter, one number and one non-text character (for example, punctuation). A Pass Phrase which has been used previously will be rejected.');
define('SHIPPING_ENDICIA_PASSPHRASE_OLD_NOT_MATCH','Your current Pass Phrase does not match what is stored in the system!');
define('SHIPPING_ENDICIA_PASSPHRASE_NEW_NOT_MATCH','Your new Pass Phrase does not match the confirmed Pass Phrase!');
define('SHIPPING_ENDICIA_PASSPHRASE_SUCCESS_MSG','Your passphrase was successfully changed!');
define('SHIPPING_ENDICIA_REFUND_MSG','Endicia tracking # %s refund approved: %s - %s');
define('SHIPPING_ENDICIA_ERROR_POSTAL_CODE','Postal Code is required to use the Endicia module!');
define('SHIPPING_ENDICIA_TRACK_STATUS','Tracking results from USPS for shipment id %s, tracking # %s is: %s');
define('SHIPPING_ENDICIA_SIGNUP_STATUS','Signup confirmation from Endicia servers: %s. You will receive an email shortly to complete your activation.');
define('SHIPPING_ENDICIA_ADD_VAL_ERROR','Dial-A-Zip error (%s) %s. The address must be corrected before a label can be generated.');
// Ship Manager
define('ENDICIA_CHANGE_PASSPHRASE','Change Pass Phrase');
define('ENDICIA_BUY_POSTAGE','Buy Postage');
// Buy Postage Amounts
define('TEXT_0010_DOLLARS','$10.00');
define('TEXT_0025_DOLLARS','$25.00');
define('TEXT_0100_DOLLARS','$100.00');
define('TEXT_0250_DOLLARS','$250.00');
define('TEXT_0500_DOLLARS','$500.00');
define('TEXT_1000_DOLLARS','$1000.00');
// Mail Piece Shapes
define('MPS_01','Card');
define('MPS_02','Letter');
define('MPS_03','Flat');
define('MPS_04','Parcel');
define('MPS_05','Large Parcel');
define('MPS_06','Irregular Parcel');
define('MPS_07','Oversized Parcel');
define('MPS_08','Flat Rate Envelope');
define('MPS_09','Flat Rate Legal Envelope');
define('MPS_10','Flat Rate Padded Envelope');
define('MPS_11','Flat Rate Gift Card Envelope');
define('MPS_12','Flat Rate Window Envelope');
define('MPS_13','Flat Rate Cardboard Envelope');
define('MPS_14','Small Flat Rate Envelope');
define('MPS_15','Small Flat Rate Box');
define('MPS_16','Medium Flat Rate Box');
define('MPS_17','Large Flat Rate Box');
define('MPS_18','DVD Flat Rate Box');
define('MPS_19','Large Video Flat Rate Box');
define('MPS_20','Regional Rate Box A');
define('MPS_21','Regional Rate Box B');

?>