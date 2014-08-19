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
//  Path: /modules/cp_action/config.php
//

// Release History
// 0.1 => 2010-09-01 - Converted from stand-alone PhreeBooks release
// 3.3 => 2011-11-15 - bug fixes, themeroller changes
// Module software version information
// Menu Sort Positions
define('BOX_CAPA_MODULE_ORDER',      80);
// Menu Security id's
define('SECURITY_CAPA_MGT',         185);
/// New Database Tables
define('TABLE_CAPA', DB_PREFIX . 'capa_module');

$mainmenu["quality"]['submenu']["quality"] = array(
  'order' 		=> BOX_CAPA_MODULE_ORDER,
  'text'        => TEXT_CAPA_ACTION,
  'security_id' => SECURITY_CAPA_MGT,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=cp_action&amp;page=main', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);

?>