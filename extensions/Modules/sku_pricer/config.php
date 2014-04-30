<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/sku_pricer/config.php
//

// Release History
// 1.0 => 2011-05-26 - Initial Release
// Module software version information
// Menu Sort Positions
// Security id's
define('SECURITY_ID_SKU_PRICER', 999);
// New Database Tables
// Menu Locations
if (defined('MODULE_SKU_PRICER_STATUS')) {
  $mainmenu["inventory"]['submenu']['sku_pricer'] = array(
    'text'        => TEXT_SKU_PRICER,
    'order'       => 159,
    'security_id' => SECURITY_ID_SKU_PRICER,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=sku_pricer&amp;page=main', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
}
?>