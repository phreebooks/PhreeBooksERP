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
//  Path: /modules/phreemail/config.php
//
// Release History
// 1.0 - created
//
// Module software version information
// Menu Sort Positions
define('BOX_PHREEMAIL_MODULE_ORDER', 70);
// Menu Security id's
define('SECURITY_PHREEMAIL_MGT',    180);
// New Database Tables
define('TABLE_PHREEMAIL',			DB_PREFIX . 'phreemail');//emailtodb_email
define('TABLE_PHREEMAIL_DIR',		DB_PREFIX . 'phreemail_dir');//emailtodb_dir
define('TABLE_PHREEMAIL_LIST',		DB_PREFIX . 'phreemail_list'); //emailtodb_list
define('TABLE_PHREEMAIL_LOG',		DB_PREFIX . 'phreemail_log'); //emailtodb_log
define('TABLE_PHREEMAIL_WORDS',		DB_PREFIX . 'phreemail_words'); //emailtodb_words
define('TABLE_PHREEMAIL_ATTACH',	DB_PREFIX . 'phreemail_attach'); //emailtodb_attach

// directory
$db_company = (isset($_SESSION['company'])) ? $_SESSION['company'] : $basis->user->companies[$_POST['company']];
define('PHREEMAIL_DIR_ATTACHMENTS',  DIR_FS_MY_FILES . $db_company . '/phreemail/attachments/');

if (defined('MODULE_PHREEMAIL_STATUS')) {

  // Set the menu
  $mainmenu["customers"]['submenu']["email"] = array(
  	'order' 	  => BOX_PHREEMAIL_MODULE_ORDER,
  	'text'        => BOX_PHREEMAIL_MODULE,
    'security_id' => SECURITY_PHREEMAIL_MGT,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreemail&amp;page=main', 'SSL'),
    'show_in_users_settings' => false, //@todo this has to become true when module is done.
    'params'      => '',
  );
}

?>