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
//  Path: /modules/phreeform/config.php
//

// Release History
// 3.0 => 2011-01-15 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes, moved custom operation to modules
// 3.2 => 2011-08-01 - Bug fixes
// 3.3 => 2011-11-15 - Bug fixes, themeroller changes
// 3.4 => 2012-02-15 - bug fixes, added dynamic images, dynamic bar codes to forms
// 3.5 => 2012-10-01 - bug fixes
// 3.6 => 2013-06-30 - bug fixes
// 3.7 => 2014-07-21 - bug fixes
// Module software version information
define('MODULE_PHREEFORM_VERSION',  3.7);
// Menu Sort Positions
// Menu Security id's (refer to master doc to avoid security setting overlap)
define('SECURITY_ID_PHREEFORM', 3); // same as SECURITY_ID_REPORTS
// New Database Tables
define('TABLE_PHREEFORM', DB_PREFIX . 'phreeform');

if (defined('MODULE_PHREEFORM_STATUS')) {
  // Set the title menu
  // Set the menus
  $mainmenu["tools"]['submenu']['reports'] = array(
  	'text'        => TEXT_REPORTS,  
  	'order'       => 25, 
    'security_id' => SECURITY_ID_PHREEFORM,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main', 'SSL'),
    'params'      => '',
  );
  if (defined('MODULE_CONTACTS_STATUS')) { // add reports menus
  	$mainmenu["customers"]['submenu']['reports'] = array(
  		'text'        => TEXT_REPORTS,  
  		'order'       => 99,
  	  	'security_id' => SECURITY_ID_PHREEFORM,
	  	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main&amp;tab=cust', 'SSL'),
  	  	'show_in_users_settings' => false,
	  	'params'      => '',
	);
	$mainmenu["employees"]['submenu']['reports'] = array(
  		'text'        => TEXT_REPORTS,  
  		'order'       => 99,
  	  	'security_id' => SECURITY_ID_PHREEFORM,
	  	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main&amp;tab=hr', 'SSL'),
	  	'show_in_users_settings' => false,
	  	'params'      => '',
	);
	$mainmenu["vendors"]['submenu']['reports'] = array(
  		'text'        => TEXT_REPORTS,  
  		'order'       => 99,
  	  	'security_id' => SECURITY_ID_PHREEFORM, 
	  	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main&amp;tab=vend', 'SSL'),
	  	'show_in_users_settings' => false,
	  	'params'      => '',
	);
  }
  if (defined('MODULE_INVENTORY_STATUS')) {
  	$mainmenu["inventory"]['submenu']['reports'] = array(
  		'text'        => TEXT_REPORTS,  
  		'order'       => 99,
  	  	'security_id' => SECURITY_ID_PHREEFORM,
	  	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main&amp;tab=inv', 'SSL'),
  	  	'show_in_users_settings' => false,
	  	'params'      => '',
	);
  }
  if (defined('MODULE_PHREEBOOKS_STATUS')) {
  	$mainmenu["banking"]['submenu']['reports'] = array(
  		'text'        => TEXT_REPORTS,  
  		'order'       => 99,
  	  	'security_id' => SECURITY_ID_PHREEFORM,
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main&amp;tab=bnk', 'SSL'),
  	  	'show_in_users_settings' => false,
	  	'params'      => '',
	);
	$mainmenu["gl"]['submenu']['reports'] = array(
  		'text'        => TEXT_REPORTS,  
  		'order'       => 99,
  	  	'security_id' => SECURITY_ID_PHREEFORM,
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main&amp;tab=gl', 'SSL'),
	  	'show_in_users_settings' => false,
	  	'params'      => '',
	);
  }
  if(defined('MODULE_CP_ACTION_STATUS') ||defined('MODULE_DOC_CTL_STATUS')){
  	$mainmenu["quality"]['submenu']["reports"] = array(
 		'order' 	  => 99,
  		'text'        => TEXT_REPORTS,
  		'security_id' => SECURITY_ID_PHREEFORM,
  		'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreeform&amp;page=main', 'SSL'),
  	  	'show_in_users_settings' => false,
  		'params'      => '',
	);
  }
  
}

?>