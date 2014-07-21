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
//  Path: /modules/phreehelp/config.php
//

// Release History
// 3.0 => 2011-01-15 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes
// 3.2 => 2011-08-01 - Bug fixes
// 3.3 => 2011-11-15 - Bug fixes, themeroller changes
// 3.4 => 2012-02-15 - bug fixes
// 3.5 => 2012-10-01 - bug fixes
// 3.6 => 2013-06-30 - bug fixes
// 3.7 => 2014-07-21 - bug fixes
// Module software version information
define('MODULE_PHREEHELP_VERSION',  3.7);
// Menu sort positions
// Menu security id's
// New database tables
define('TABLE_PHREEHELP', DB_PREFIX . 'phreehelp');
// Set the title menu - N/A this is  special module placed into the heading bar
$mainmenu["company"]['submenu']["help"] = array(
  'order' 		=> 1,
  'text'        => TEXT_HELP,
  'security_id' => SECURITY_ID_HELP,
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreehelp&amp;page=main', 'SSL'),
  'show_in_users_settings' => true,
  'params'      => 'target="_blank"',
);
?>