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
//  Path: /modules/shipping/config.php
//
// Release History
// 3.0 => 2011-01-15 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes, java label printing, improved xml
// 3.2 => 2011-08-01 - Bug fixes
// 3.3 => 2011-11-15 - Bug fixes, themeroller changes
// 3.4 => 2012-02-15 - bug fixes
// 3.5 => 2012-10-01 - bug fixes
// 3.6 => 2013-06-30 - bug fixes
// 3.7 => 2014-07-21 - bug fixes
// Module software version information
define('MODULE_SHIPPING_VERSION', 3.7);
// Menu Sort Positions
// Menu Security id's (refer to master doc to avoid security setting overlap)
define('SECURITY_ID_SHIPPING_MANAGER', 13);
// New Database Tables
define('TABLE_SHIPPING_LOG', DB_PREFIX . 'shipping_log');
// Set the title menu
// Set the menus
if (defined('MODULE_SHIPPING_STATUS')) {
  $mainmenu["tools"]['submenu']['shipping'] = array(
    	'text'        => BOX_SHIPPING_MANAGER,
    	'order'       => 5,
    	'security_id' => SECURITY_ID_SHIPPING_MANAGER,
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=shipping&amp;page=ship_mgr', 'SSL'),
    	'show_in_users_settings' => true,
  );
  if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
	  gen_pull_language('shipping', 'admin');
	  $mainmenu["company"]['submenu']["configuration"]['submenu']["shipping"] = array(
		'order'	      => MODULE_SHIPPING_TITLE,
		'text'        => MODULE_SHIPPING_TITLE,
		'security_id' => SECURITY_ID_CONFIGURATION, 
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=shipping&amp;page=admin', 'SSL'),
	    'show_in_users_settings' => false,
		'params'      => '',
	  );
  }
  
}

?>