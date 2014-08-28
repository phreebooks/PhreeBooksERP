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
//  Path: /modules/import_bank/config.php
//

// Release History
// 0.1 01-03-2011 created.
// 0.2 04-03-2011 location of xml changed and sample added.
// 0.3 22-03-2011 Added install functions for contacts 3.1
// 0.4 31-01-2011 removed bugg from install class 
// 1   15-01-2013 added the function so that multiple bank could be attached to one contact and added iban support.
// 1.1 27-1-2013  added the transaction templates (aka known transactions). plus support for payment of multiple invoices.
// 2   28-1-2013  	complete rewrite reduced the number of sql calles.
//					added function to find invoicenumber in description for transactions that are not connected to a bank or iban account
// 2.1 27-09-2013 fixed bug that it would try to find a contact for known_transactions.
// 2.2 28-08-2014 fixed bug that it would try to find a contact for known_transactions.
gen_pull_language('phreedom', 'menu');
// Module software version information
define('MODULE_IMPORT_BANK_VERSION',  2.2);
// Menu Sort Positions

// Menu Security id's
define('SECURITY_ID_IMPORT_BANK',      980);
// New Database Tables
define('TABLE_IMPORT_BANK',    			DB_PREFIX . 'import_bank');
// Set the menus
gen_pull_language('phreebooks');
if (defined('MODULE_IMPORT_BANK_STATUS')) {
	$mainmenu["banking"]['submenu']['import_banking'] = array(
    	'text'        => BOX_IMPORT_BANK_MODULE,
    	'order'       => 55,
    	'security_id' => SECURITY_ID_IMPORT_BANK,
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=import_bank&amp;page=main', 'SSL'),
		'show_in_users_settings' => true,
    	'params'      => '',
  	);
  
	if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
  		gen_pull_language('import_bank', 'admin');
  		$mainmenu["company"]['submenu']["configuration"]['submenu']["import_bank"] = array(
			'order'	      => MODULE_IMPORT_BANK_TITLE,
			'text'        => MODULE_IMPORT_BANK_TITLE,
			'security_id' => SECURITY_ID_CONFIGURATION, 
			'link'        => html_href_link(FILENAME_DEFAULT, 'module=import_bank&amp;page=admin', 'SSL'),
    		'show_in_users_settings' => false,
			'params'      => '',
  		);
	}
}

?>