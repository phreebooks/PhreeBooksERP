<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
// 1.3 added xsd validation
// Module software version information
// Menu Sort Positions

// Menu Security id's
define('SECURITY_ID_AUDIT',      500);
// New Database Tables

// Set the menus
if (defined('MODULE_AUDIT_STATUS')) {
	$mainmenu["gl"]['submenu']["audit"] = array(
	  	'order'		  => 80,
		'text'        => TEXT_AUDIT_EXPORT_XAF,
		'show_in_users_settings' => true,
	    'security_id' => SECURITY_ID_AUDIT,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=audit&amp;page=main', 'SSL'),
		'params'	  => '',
	);
	if(\core\classes\user::security_level(SECURITY_ID_CONFIGURATION) > 0){
  		gen_pull_language('audit', 'admin');
  		$mainmenu["company"]['submenu']["configuration"]['submenu']["audit"] = array(
			'order'	      => TEXT_AUDIT,
			'text'        => TEXT_AUDIT,
			'security_id' => SECURITY_ID_CONFIGURATION,
			'link'        => html_href_link(FILENAME_DEFAULT, 'module=audit&amp;page=admin', 'SSL'),
    		'show_in_users_settings' => false,
			'params'      => '',
  		);
	}

}

?>