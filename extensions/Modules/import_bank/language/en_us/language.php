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
//  Path: /modules/import_bank/language/en_us/language.php
//
define('MODULE_IMPORT_BANK_TITLE',  'Import Bank Statement');
define('HEADING_MODULE_IMPORT_BANK','Import Bank Statement');
define('GEN_BANK_IMPORT_MESSAGE',	'Select the. csv file to import and click Import <br> If there is no bank account column in your .csv then select a bank account from the drop down box.');
define('TEXT_IMPORT',				'Import');
define('SAMPLE_CSV',				'Sample CSV');
define('TEXT_REQUIRED',				'REQUIRED');

define('TEXT_BIMP_ERMSG1','ouwer bank account is empty');
define('TEXT_BIMP_ERMSG2','There are two or more gl accounts with the description : ');
define('TEXT_BIMP_ERMSG3','The other bank account is empty');
define('TEXT_BIMP_ERMSG4','There are two or more accounts with the same bank account : ');
define('TEXT_BIMP_ERMSG5','There is no gl accounts with the description :  ');
define('GENERAL_JOURNAL_7_DESC','Credit Memo');
define('TEXT_NEW_BANK','Found a new bank number. You could add Banknumber = %s to Contact = %s ');
define('TEXT_NEW_IBAN','Found a new iban number. You could add Iban = %s to Contact = %s ');
?>