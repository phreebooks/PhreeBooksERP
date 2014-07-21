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
gen_pull_language('phreedom', 'menu');
// Release History
// 1 16-10-2012 created.
// Module software version information
define('MODULE_AUDIT_VERSION',  '1');
// Menu Sort Positions

// Menu Security id's
define('SECURITY_ID_AUDIT',      500);
// New Database Tables

// Set the menus
if (defined('MODULE_AUDIT_STATUS')) {
	$mainmenu["gl"]['submenu']["audit"] = array(
	  	'order'		  => 80,
		'text'        => BOX_AUDIT_MODULE,  
		'show_in_users_settings' => true,
	    'security_id' => SECURITY_ID_AUDIT, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=audit&amp;page=main', 'SSL'),
		'params'	  => '',
	);
	if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
  		gen_pull_language('audit', 'admin');
  		$mainmenu["company"]['submenu']["configuration"]['submenu']["audit"] = array(
			'order'	      => MODULE_AUDIT_TITLE,
			'text'        => MODULE_AUDIT_TITLE,
			'security_id' => SECURITY_ID_CONFIGURATION, 
			'link'        => html_href_link(FILENAME_DEFAULT, 'module=audit&amp;page=admin', 'SSL'),
    		'show_in_users_settings' => false,
			'params'      => '',
  		);
	}
	
}

?>