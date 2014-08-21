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
//  Path: /modules/phreewiki/config.php
//

// Release History
// 1.2 => 2011-05-01 - Created same as ccTiddly

// Module software version information
// Menu Sort Positions
define('BOX_PHREEWIKI_MODULE_ORDER',  100);
// Menu Security id's
define('SECURITY_PHREEWIKI_MGT',      775);
// New Database Tables
define('TABLE_PHREEWIKI',              DB_PREFIX . 'phreewiki');
define('TABLE_PHREEWIKI_VERSION',      DB_PREFIX . 'phreewiki_version');

// Set the menus
if (defined('MODULE_PHREEWIKI_STATUS')) {
	$mainmenu["tools"]['submenu']['wiki'] = array(
    	'text'        => BOX_PHREEWIKI_MODULE,
    	'order'       => BOX_PHREEWIKI_MODULE_ORDER,
    	'security_id' => SECURITY_PHREEWIKI_MGT,
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreewiki&amp;page=ccTiddly', 'SSL'),
	  	'show_in_users_settings' => true,
  	);


  if (\core\classes\user::security_level(SECURITY_ID_CONFIGURATION) > 0){
	  gen_pull_language('phreewiki', 'admin');
	  $mainmenu["company"]['submenu']["configuration"]['submenu']["phreewiki"] = array(
		'order'	      => MODULE_PHREEWIKI_TITLE,
		'text'        => MODULE_PHREEWIKI_TITLE,
		'security_id' => SECURITY_ID_CONFIGURATION,
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreewiki&amp;page=admin', 'SSL'),
	    'show_in_users_settings' => false,
		'params'      => '',
	  );
  }
}



?>