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
//  Path: /soap/application_top.php
//

// Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
// set php_self in the local scope
  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
// Check for application configuration parameters, may be preloaded in running from a subdirectory, so skip
// currently scripts run from top level (index.php only) and second level, so we only need to check these two levels
if (!defined('DIR_FS_ADMIN')) {
  if (file_exists('../includes/configure.php')) {
	require_once('../includes/configure.php');
  } else {
	die('ERROR: includes/configure.php file not found on PhreeBooks Server.');
  }
}

// load some file system constants
define('DIR_FS_MODULES',   DIR_FS_ADMIN . 'modules/');
define('DIR_FS_MY_FILES',  DIR_FS_ADMIN . 'my_files/');
define('FILENAME_DEFAULT', 'index');

// define the inventory types that are tracked in cost of goods sold
define('COG_ITEM_TYPES','si,sr,ms,mi,ma');
  
// set the type of request (secure or not)
$request_type = (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1' || strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']),'SSL') || strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']),'SSL'))  ? 'SSL' : 'NONSSL';

require_once(DIR_FS_ADMIN . 'includes/common_functions.php');
require_once(DIR_FS_ADMIN . 'includes/common_classes.php');

// set the session name and save path
$http_domain  = gen_get_top_level_domain(HTTP_SERVER);
$https_domain = gen_get_top_level_domain(HTTPS_SERVER);
$current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);

// set the session cookie parameters
session_start();
session_set_cookie_params(0, '/', (gen_not_null($current_domain) ? $current_domain : ''));

// determine what company to connect to
$db_name = $_GET['db'];
if (!$db_name || $db_name == 'auto') { // get the first company and use that db, for single company installs only!
	$folders = scandir(DIR_FS_MY_FILES);
	foreach ($folders as $folder) if ($folder <> '.' && $folder <> '..' && is_dir(DIR_FS_MY_FILES . $folder)) {
		if (file_exists(DIR_FS_MY_FILES."$folder/config.php")) $db_name = $folder; break;
	}
}
if ($db_name && file_exists(DIR_FS_MY_FILES . $db_name . '/config.php')) {
  define('DB_DATABASE', $db_name);
  require(DIR_FS_MY_FILES . $db_name . '/config.php');
  define('DB_SERVER_HOST',DB_SERVER); // for old PhreeBooks installs
} else die("No database name passed! Cannot determine which company to connect to!");

// set the language
$_SESSION['language'] = $_GET['lang'] ? $_GET['lang'] : 'en_us';
define('LANGUAGE',$_SESSION['language']);
gen_pull_language('phreedom');
require_once(DIR_FS_ADMIN . 'soap/language/'.LANGUAGE.'/language.php');

// include the database functions
// Load queryFactory db classes
require_once(DIR_FS_ADMIN . 'includes/db/'.DB_TYPE.'/query_factory.php');
$db = new queryFactory();
if (!$db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE)) die ('cannot connec to db!');

// set application wide parameters for phreebooks module
$configuration = $db->Execute("select configuration_key, configuration_value from " . DB_PREFIX . "configuration");
while (!$configuration->EOF) {
  define($configuration->fields['configuration_key'], $configuration->fields['configuration_value']);
  $configuration->MoveNext();
}

// load general language translation
gen_pull_language('phreedom', 'menu');
  require_once(DIR_FS_MODULES . 'phreedom/config.php');
  $dirs = scandir(DIR_FS_MODULES);
  foreach ($dirs as $dir) { // first pull all module language files, loaded or not
    if ($dir == '.' || $dir == '..') continue;
	if (is_dir(DIR_FS_MODULES . $dir)) gen_pull_language($dir, 'menu'); 
  }
  foreach ($dirs as $dir) {
    if ($dir == '.' || $dir == '..') continue;
    if (defined('MODULE_' . strtoupper($dir) . '_STATUS')) { // module is loaded
      require_once(DIR_FS_MODULES . $dir . '/config.php');
    }
  }

$currencies   = new currencies;
$messageStack = new messageStack;
if (get_cfg_var('safe_mode')) echo 'Operating in Safe Mode. (This is bad!)';
// check if a default currency is set
if (!defined('DEFAULT_CURRENCY')) $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error');
// include the password crypto functions

?>