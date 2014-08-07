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
//  Path: /modules/phreepos/language/en_us/language.php
//

// Page Titles
define('TEXT_POINT_OF_SALE_RETURN','Point of Sale - Return');
define('TEXT_ENTER_PAYMENT','Enter Payment');

// General Text
define('BNK_19_AMOUNT_PAID','Amt Rcvd');
define('TEXT_ADD_UPDATE','Add/Update');
define('TEXT_SUBTOTAL','Subtotal');
define('TEXT_AMOUNT_PAID','Amount Paid');
define('TEXT_BALANCE_DUE','Balance Due');
define('TEXT_PROCESS_RETURN','Process Return');
define('TEXT_DISCOUNT_PERCENT','Discount Perc.');
define('TEXT_DISCOUNT_AMOUNT_SHORT','Discount Amt.');
define('TEXT_REFUND','Refund');
define('TEXT_REFUND_METHOD','Refund Method');
define('TEXT_ENTRIES','Entries');
define('TEXT_SELECT_CUSTOMER','Select Customer');

define('PHREEPOS_ITEM_NOTES','Cursor must be in the SKU box for bar code scanners to record an item.<br><b>ESC</b> = Clear Screen.<br><b>Alt+R</b> = Switch between returns and normal sales.<br><b>F7</b> = Show inventory pop-up.<br><b>F8</b> = Show customer pop-up.<br><b>F9</b> = Show payment pop-up.');
define('PHREEPOS_PAYMENT_NOTES','<b>ESC</b> = Close pop-up.<br><b>F11</b>  = Save.<br><b>F12</b>  = Print<br><b>arrow up & down<b> = different paymenttype');
define('TEXT_ARE_YOU_SURE_YOU_WANT_TO_VOID_AND_DELETE_THIS_POS_ENTRY','Are you sure you want to void/delete this POS entry?');

define('TEXT_A_POS_SALE_CANNOT_BE_DELETED_IF_IT_IS_CLOSED','A possale cannot be deleted if it is closed!');
define('TEXT_TILL','Till');
define('TEXT_OPEN_DRAWER','Open Drawer');
define('TEXT_PRINT_PREVIOUS_RECEIPT','Print Previous Receipt');
define('TEXT_ORIGINAL_AMOUNT_SHORT','Orig Amount');
define('TEXT_SALES','Sales');
define('TEXT_POSTED_CASH_DIFFERENCE_IN_TILL','Posted Cash difference in till');
define('TEXT_DAY_CLOSING','Day Closing');
define('NEW_BALANCE','New Till Balance');
define('TEXT_PAYMENTS_SHOULD_BE','Payments Should Be');
define('TEXT_PAYMENTS_RECEIVED','Payments Received');
define('TEXT_START_BALANCE_TILL','Start balance Till');
define('TEXT_DIFFERENCE','Difference');
define('TEXT_SHOW_COUNT_HELP','Show count help');
define('EXCEED_MAX_DISCOUNT','You have exceded the maximum discount of %s percentage.');
define('EXCEED_MAX_DISCOUNT_SKU','You have exceded the maximum discount of %s percentage for sku %s.');
define('TEXT_OTHER_OPTIONS','Other Options');
define('TEXT_PRINT_OTHER_RECEIPT','Print Other Receipt');

define('TEXT_CASH_IN','Cash In');
define('TEXT_CASH_OUT','Cash Out');
define('TEXT_EXPENSES','Expenses');
define('TEXT_TYPE_OF_TRANSACTION','Type transaction');
define('ERROR_NO_PAYMENT_METHODES','Can not open POS because there are no payment methodes. Goto company > module administration > payment module and set payments to show in pos and add gl accounts to payments');
?>
