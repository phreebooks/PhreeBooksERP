<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/zencart/config.php
//

// Release History
// 3.0 => 2011-01-25 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes
// 3.2 => 2011-05-27 - Patch for shared field change in Phreedom 3.1
// 3.5 => 2013-08-08 - added product fields 
// 3.6 => 2014-01-15 - updated for move back to phreebooks and menu changes
// Module software version information
define('MODULE_ZENCART_VERSION',      3.6);
// Set the menu order, if using ZenCart title menu option (after Customers and before Vendors)
define('MENU_HEADING_ZENCART_ORDER',     15);
// Security id's
define('SECURITY_ID_ZENCART_INTERFACE', 200);
// New Database Tables
if (defined('MODULE_ZENCART_STATUS')) {
/*
  $pb_headings[MENU_HEADING_ZENCART_ORDER] = array(
    'text' => MENU_HEADING_ZENCART, 
    'link' => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=index&amp;mID=cat_zencart', 'SSL'),
  );
*/
  // Menu Locations
  $mainmenu["tools"]['submenu']['zencart'] = array(
    'text'        => BOX_ZENCART_MODULE, 
    'rank'        => 31, 
    'security_id' => SECURITY_ID_ZENCART_INTERFACE,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=zencart&amp;page=main', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
  if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
	  gen_pull_language('zencart', 'admin');
	  $mainmenu["company"]['submenu']["configuration"]['submenu']["zencart"] = array(
		'order'	      => MODULE_ZENCART_TITLE,
		'text'        => MODULE_ZENCART_TITLE,
		'security_id' => SECURITY_ID_CONFIGURATION, 
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=zencart&amp;page=admin', 'SSL'),
	    'show_in_users_settings' => false,
		'params'      => '',
	  );
  }
  
}
?>