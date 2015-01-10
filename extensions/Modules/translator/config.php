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
//  Path: /modules/translator/extra_menus/translator.php
//

// Release History
// 3.0 => 2010-09-01 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-01-28 - Added import All and export All features, filters on main
// 3.2 => 2011-07-21 - improved updates from prior versions, select all button
// 3.3 => 2011-11-15 - Bug fixes, themeroller changes
// Module software version information
// Menu Sort Positions
define('BOX_TRANSLATOR_MODULE_ORDER',  670);
// Menu Security id's
define('SECURITY_TRANSLATOR_MGT',      680);
// New Database Tables
define('TABLE_TRANSLATOR', DB_PREFIX.'translator');
// Set the menus
if (defined('MODULE_TRANSLATOR_STATUS')) {
	$mainmenu["tools"]['submenu']['translator'] = array(
    	'text'        => TEXT_TRANSLATOR_ASSISTANT,
    	'order'       => BOX_TRANSLATOR_MODULE_ORDER,
    	'security_id' => SECURITY_TRANSLATOR_MGT,
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=translator&amp;page=main', 'SSL'),
	    'show_in_users_settings' => true,
    	'params'      => '',
  	);
}

?>