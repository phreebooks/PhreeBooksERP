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
//  Path: /modules/xml_builder/config.php
//

// Release History
// 1.0 => 2011-11-15 - Initial release
// Module software version information
// Menu Sort Positions
// Security id's
define('SECURITY_ID_XML_BUILDER', 499);
// New Database Tables
// Menu Locations
if (defined('MODULE_XML_BUILDER_STATUS')) {
	$mainmenu["tools"]['submenu']['xml_builder'] = array(
    	'text'        => TEXT_XML_BUILDER,
    	'order'       => 99,
    	'security_id' => SECURITY_ID_XML_BUILDER,
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=xml_builder&amp;page=main', 'SSL'),
	    'show_in_users_settings' => true,
  	);
}
?>