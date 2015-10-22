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
//  Path: /modules/magento/config.php
//
// Release History
// Module software version information
// Set the menu order, if using Magento title menu option (after Customers and before Vendors)
define('MENU_HEADING_MAGENTO_ORDER',     15);
// Security id's
define('SECURITY_ID_MAGENTO_INTERFACE', 201);
// New Database Tables
if (defined('MODULE_MAGENTO_STATUS')) {

  // Menu Locations
  	$mainmenu["tools"]['submenu']["magento"] = array(
  		'order'		  => 32,
    	'text'        => BOX_MAGENTO_MODULE,
    	'security_id' => SECURITY_ID_MAGENTO_INTERFACE,
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=magento&amp;page=main', 'SSL'),
  		'show_in_users_settings' => true,
		'params'	  => '',
  	);

	if (\core\classes\user::security_level(SECURITY_ID_CONFIGURATION) > 0){
	  $mainmenu["company"]['submenu']["configuration"]['submenu']["magento"] = array(
		'order'	      => MODULE_MAGENTO_TITLE,
		'text'        => MODULE_MAGENTO_TITLE,
		'security_id' => SECURITY_ID_CONFIGURATION,
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=magento&amp;page=admin', 'SSL'),
	    'show_in_users_settings' => true,
		'params'      => '',
	  );
	}
}
?>