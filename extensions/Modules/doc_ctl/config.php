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
//  Path: /modules/doc_ctl/config.php
//

define('NAME_TRIM_LENGTH','24'); // TBD - needs to move to admin constant

// 0.1 => 2010-09-01 - Converted from stand-alone PhreeBooks release
// 1.0 => 2011-11-15 - Initial module release, themeroller compatible
// 3.6 => bug fixes and compatibility issues with PhreeBooks R3.6, sync rev with PhreeBooks 
// Module software version information
// Menu Sort Positions
define('BOX_DOC_CTL_ORDER',          10);
// Security id's
define('SECURITY_ID_DOC_CONTROL',   210);
// New Database Tables
define('TABLE_DC_DOCUMENT', DB_PREFIX . 'doc_ctl');
// Set the title menu

// Menu Locations
$mainmenu["quality"]['submenu']["doc_ctl"] = array(
  'order' 		=> BOX_DOC_CTL_ORDER,
  'text'        => BOX_DOC_CTL_MODULE,
  'security_id' => SECURITY_ID_DOC_CONTROL,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=doc_ctl&amp;page=main', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => '',
);

?>