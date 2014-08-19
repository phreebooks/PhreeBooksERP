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
//  Path: /modules/phreehelp/defaults.php
//

// Set the path to the root directory of the help files, specific language if available
if (file_exists(DIR_FS_MODULES . 'phreedom/language/' . $_SESSION['langauge'] . '/manual/welcome.php')) {
  define('DOC_ROOT_URL', DIR_WS_MODULES . 'phreedom/language/' . $_SESSION['langauge'] . '/manual/welcome.html');
} else {
  define('DOC_ROOT_URL', DIR_WS_MODULES . 'phreedom/language/en_us/manual/welcome.html');
}
// URL to the phreesoft help site, BBS, application website, usergroup, etc.
define('DOC_WWW_HELP','http://www.phreesoft.com/documentation.php');
// Extensions allowed for inclusion (separated by commas, no spaces)
define('VALID_EXTENSIONS','html,htm,php,asp');
// Set the maximum title length before truncated to keep displays on a single row
define('PH_DEFAULT_TRIM_LENGTH', 25);

?>