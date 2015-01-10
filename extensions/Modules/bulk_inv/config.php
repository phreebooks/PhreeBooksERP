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
//  Path: /modules/bulk_inv/config.php
//
// Release History
// 1.0 => 2012-10-10 - Initial release
// Module software version information
gen_pull_language('bulk_inv', 'admin');
// Menu Sort Positions
// Security id's
define('SECURITY_ID_MAINTAIN_INVENTORY', 151);
// New Database Tables
// Menu Locations

$mainmenu["inventory"]["submenu"]["bulk_inventory"] = array(
  	'order'		  => 95,
    'text'        => TEXT_BULK_INVENTORY_TOOL,
    'security_id' => SECURITY_ID_MAINTAIN_INVENTORY,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=bulk_inv&amp;page=bulk_inv', 'SSL'),
	'show_in_users_settings' => false, // set with inventory permissions
	'params'	  => '',
);


?>