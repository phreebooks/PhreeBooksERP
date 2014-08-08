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
//  Path: /modules/assets/config.php
//
// Release History
// 3.0 => 2011-01-14 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes
// 3.3 => 2011-11-15 - Theme conversion
// 3.6 => 2013-09-23 - Updates for PhreeBooks R3.6
// 3.7 => 2014-07-18 - bug fix
// Module software version information
gen_pull_language('phreebooks', 'menu');
define('MODULE_ASSETS_VERSION',       3.7);
// Menu sort positions
define('MENU_HEADING_ASSETS_ORDER',      77);
define('BOX_ASSETS_MODULE_ORDER',        90);
// Menu security id's
define('SECURITY_ASSETS_MGT',            170);
// New database tables
define('TABLE_ASSETS', DB_PREFIX.'assets');
if (defined('MODULE_ASSETS_STATUS')) {
	if(defined(ASSETS_OWN_HEADING) && ASSETS_OWN_HEADING == TRUE){ // Set the title menu
	  $mainmenu["assets"] = array(
	  	'order' => MENU_HEADING_ASSETS_ORDER,
	    'text'  => MENU_HEADING_ASSETS, 
	    'link'  => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_assets', 'SSL'),
	  );
	  $mainmenu["assets"]['submenu']["new_asset"] = array(
	    'order'		  => 1,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, BOX_ASSET_MODULE),  
	    'security_id' => SECURITY_ASSETS_MGT, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=assets&amp;page=main&amp;action=new', 'SSL'),
	  	'show_in_users_settings' => false,
		'params'	  => '',
	  );
	  $mainmenu["assets"]['submenu']["asset_mgr"] = array(
	  	'order'		  => 2,
	    'text'        => sprintf(BOX_STATUS_MGR, BOX_ASSET_MODULE),
	    'security_id' => SECURITY_ASSETS_MGT,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=assets&amp;page=main', 'SSL'),
	  	'show_in_users_settings' => true,
		'params'	  => '',
	  );
	}else{
	  $mainmenu["company"]['submenu']["assets"] = array(
	  	'order'		  => BOX_ASSETS_MODULE_ORDER,
		'text'        => BOX_ASSET_MODULE,  
	    'security_id' => SECURITY_ASSETS_MGT, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=assets&amp;page=main', 'SSL'),
	  	'show_in_users_settings' => false,
		'params'	  => '',
	  );	
	  $mainmenu["company"]['submenu']["assets"]['submenu']["new_asset"] = array(
	  	'order'		  => 1,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, BOX_ASSET_MODULE),  
	    'security_id' => SECURITY_ASSETS_MGT, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=assets&amp;page=main&amp;action=new', 'SSL'),
	  	'show_in_users_settings' => false,
		'params'	  => '',
	  );
	  // Set the menus
	  $mainmenu["company"]['submenu']["assets"]['submenu']["asset_mgr"]= array(
	  	'order'		  => 2,
	    'text'        => sprintf(BOX_STATUS_MGR, BOX_ASSET_MODULE),  
	    'security_id' => SECURITY_ASSETS_MGT, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=assets&amp;page=main', 'SSL'),
	  	'show_in_users_settings' => true,
		'params'	  => '',
	  );
	}
  	if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
  		gen_pull_language('assets', 'admin');
		$mainmenu["company"]['submenu']["configuration"]['submenu']["asset"] = array(
	  		'order'		  => MODULE_ASSETS_TITLE,
	  		'text'        => MODULE_ASSETS_TITLE,
	  		'security_id' => SECURITY_ID_CONFIGURATION, 
	  		'link'        => html_href_link(FILENAME_DEFAULT, 'module=assets&amp;page=admin', 'SSL'),
			'show_in_users_settings' => false,
	  		'params'      => '',
		);
  	}
}

?>