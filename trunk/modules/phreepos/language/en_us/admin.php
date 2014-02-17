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
//  Path: /modules/phreepos/language/en_us/admin.php
//

// Module information
define('MODULE_PHREEPOS_TITLE','PhreePOS Module');
define('MODULE_PHREEPOS_DESCRIPTION','The PhreePOS module provides a Point of Sale interface. This module is a supplement to the phreebooks module and is not a replacement for it.');
define('TEXT_PHREEPOS_SETTINGS', 'PhreePOS Module Settings');
// Headings
define('BOX_PHREEPOS_ADMIN','Point of Sale Administration');
// General Defines
define('PHREEPOS_REQUIRE_ADDRESS_DESC','Require address information for every POS/POP Sale?');
define('PHREEPOS_RECEIPT_PRINTER_NAME_DESC','Sets then name of the printer to use for printing receipts as defined in the printer preferences for the local workstation.');
define('PHREEPOS_RECEIPT_PRINTER_STARTING_LINE_DESC','Here you can add code to that should be in the header paper of the receipt.<br>Seperate the codes by a : and lines by a , like: <i>27:112:48:55:121,27:109</i><br>The codes are a numbers of the chr ie chr(13) is 13<br><b>Only put in code no text this could result in errors.</b> view your printer documentation for the right codes.');
define('PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE_DESC','Here you can add code to open you drawer and /or cut the receipt.<br>Seperate the codes by a : and lines by a , like: <i>27:112:48:55:121,27:109</i><br>The codes are a numbers of the chr ie chr(13) is 13<br><b>Only put in code no text this could result in errors.</b> ');
define('PHREEPOS_ENABLE_DIRECT_PRINTING_DESC','Should direct printing be enabled? by disableling it you will no longer get pop-ups asking to allow java.');
//3.3
define('PHREEPOS_RECEIPT_PRINTER_OPEN_DRAWER_DESC','Here you can add code to open you drawer payment dependent (set the open drawer option in the payment module).<br> If one of the selected payment has the option \'open cash drawer \' set too true the cashdrawer will be openend.<br>Seperate the codes by a : and lines by a , like: <i>27:112:48:55:121,27:109</i><br>The codes are a numbers of the chr ie chr(13) is 13<br><b>Only put in code no text this could result in errors.</b>');
define('TEXT_ENTER_NEW_TILL','New Till');
define('TEXT_EDIT_TILL','Edit Till');
define('TEXT_TILLS','Tills');
define('TEXT_ENTER_NEW_OTHER_TRANSACTION','New Other Transaction');
define('TEXT_EDIT_OTHER_TRANSACTION','Edit Other Transaction');
define('SETUP_TILL_DELETE_INTRO','Do you whis to delete this till?');
define('SETUP_OT_DELETE_INTRO','Do you whis to delete this other transaction?');
define('PHREEPOS_DISPLAY_WITH_TAX_DESC','Do you wish to show the prices onscrean with tax<br> (if you select no prices will be shown without tax)');
define('PHREEPOS_DISCOUNT_OF_DESC','Do you wish that the discount will be calculated from the total<br> ( if you select no then it will be calculated from the subtotal) ');
define('PHREEPOS_ROUNDING_DESC','How do you wish that the end total is rounded.<br> <b>NO</b> means that the end total will not be rounded.<br><b>INTEGER</b> means:  to the benefit of the customer everything smaller than a dollar will be ignored.<br><b>10 CENTS</b> means:  to the benefit of the customer everything smaller than 10 cents will be ignored.<br><b>NEUTRAL</b> there will be rounded to the nearest 0, 5 or 10 cents (1,2,6,7 go down 3,4,8,9 go up)');
define('TEXT_INTEGER','Integer');
define('TEXT_10_CENTS','10 Cents');
define('TEXT_NEUTRAL','Neutral');
define('TEXT_ROUNDING_OF','Rounded of');
define('TEXT_GL_ACCOUNT_ROUNDING','GL account for rounding of:');
define('TEXT_DIF_GL_ACCOUNT','GL account for end of the day differences:');

define('TEXT_RESTRICT_CURRENCY','Restrict till to this currency');
define('TEXT_DRAWER_CODES','open drawer codes');
define('TEXT_MAX_DISCOUNT', 'set the maximum amount of discount is allowed to be given at this till, this excludes discounts set in the price lists.<br> leave empty is you do not whis to set this');
define('TEXT_PHREEPOS_TRANSACTION_TYPE','Select the transaction type');
define('TEXT_USE_TAX','Can tax be used');
define('TEXT_TAX','default tax');
//3.4
define('TEXT_OTHER_TRANS','Other Transactions');

?>