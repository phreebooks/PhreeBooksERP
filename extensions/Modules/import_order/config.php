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
//  Path: /modules/import_order/config.php
//

// Release History
// 1.0 => 2014-07-21 - Initial Release
// Module software version information
define('MODULE_IMPORT_ORDER_VERSION', 1.0);
// Menu Sort Positions
// Security id's
define('SECURITY_ID_IMPORT_ORDER', 777);
// New Database Tables
// Menu Locations
if (defined('MODULE_IMPORT_ORDER_STATUS')) {
  $mainmenu["tools"]['submenu']['import_order'] = array(
    'text'        => BOX_IMPORT_ORDER_TITLE, 
    'order'       => 77, 
    'security_id' => SECURITY_ID_IMPORT_ORDER, 
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=import_order&amp;page=main', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
}
?>