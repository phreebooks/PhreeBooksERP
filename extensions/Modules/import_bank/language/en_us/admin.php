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
//  Path: /modules/import_bank/language/en_us/admin.php
//
define('MODULE_IMPORT_BANK_TITLE','Import Bank Statement');
define('MODULE_IMPORT_BANK_DESCRIPTION','With this module you can import .csv bank statements. The list is then checked for any outstanding bill or previous bookings with the same description.');
define('BOX_BANK_IMPORT_ADMIN','Bank Import Settings');
define('TEXT_BANK_IMPORT_SETTINGS','Bank Import Settings');
define('MODULE_BANK_IMPORT_CONFIG_INFO','Please Set your configuration value');
define('BANK_IMPORT_QUESTION_POSTS','To ensure that bookings are not booked by the script as a last resort the entries are stored in the ledger question items <B> This must be completed otherwise the script does not work</B>');
define('BANK_IMPORT_DEBIT_CREDIT','The description when amounts are credited to your account <br> <B> this is for the xml field debit_credit</B>');
define('IMPORT_BANK_CONFIG_SAVED','Saved import_bank config');
define('TEXT_BANK_ACCOUNT','Bank account or iban');
define('TEXT_ENTER_TRANSACTION','Enter new transaction template');
define('TEXT_EDIT_TRANSACTION','Edit transaction template');
define('TEXT_KNOWN_TRANSACTION','Transaction template');
?>