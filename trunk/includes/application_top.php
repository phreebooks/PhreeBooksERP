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
//  Path: /includes/application_top.php
//
define('PAGE_EXECUTION_START_TIME', microtime(true));
if (!get_cfg_var('safe_mode')) {
	if (ini_get('max_execution_time') < 60) set_time_limit(60);
}
$currencies        = ''; 
$force_reset_cache = false;
// set php_self in the local scope
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
// Check for application configuration parameters
if     (file_exists('includes/configure.php')) { require('includes/configure.php'); } 
elseif (file_exists('install/index.php')) { header('Location: install/index.php'); exit(); }
else   die('Phreedom cannot find the configuration file. Aborting!');
// Load some path constants
$path = (ENABLE_SSL_ADMIN == 'true' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_ADMIN;
if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
define('DIR_WS_FULL_PATH', $path);	// full http path (or https if secure)
define('DIR_WS_MODULES',   'modules/');
define('DIR_WS_MY_FILES',  PATH_TO_MY_FILES);
// load some file system constants
define('DIR_FS_INCLUDES',  DIR_FS_ADMIN . 'includes/');
define('DIR_FS_MODULES',   DIR_FS_ADMIN . 'modules/');
define('DIR_FS_MY_FILES',  DIR_FS_ADMIN . PATH_TO_MY_FILES);
define('DIR_FS_THEMES',    DIR_FS_ADMIN . 'themes/');
define('FILENAME_DEFAULT', 'index');
// set the type of request (secure or not)
$request_type = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1' || strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']),'SSL') || strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']),'SSL'))) ? 'SSL' : 'NONSSL';
// define the inventory types that are tracked in cost of goods sold
define('COG_ITEM_TYPES','si,sr,ms,mi,ma,sa');
@ini_set('session.gc_maxlifetime', (SESSION_TIMEOUT_ADMIN < 360 ? 360 : SESSION_TIMEOUT_ADMIN));
$_REQUEST = array_merge($_GET, $_POST);
// The line below breaks the $_REQUEST string as array_map expects 2 parameters and the second array is null thus erasing all the values in $_REQUEST while keeping the keys.
//$_REQUEST = array_map('mysql_real_escape_string', $_REQUEST); // this should pas all variables in the $_REQUEST pas the db_prepare_input function
session_start();
$session_started = true;
// set the language
if   (isset($_GET['language'])) { $_SESSION['language'] = $_GET['language']; } 
elseif (!$_SESSION['language']) { $_SESSION['language'] = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en_us'; }
// see if the user is logged in
$user_validated = isset($_SESSION['admin_id']) ? true : false;
// load general language translation, Check for global define overrides first
$path = DIR_FS_MODULES . 'phreedom/custom/language/' . $_SESSION['language'] . '/language.php';
if (file_exists($path)) { require_once($path); }
$path = DIR_FS_MODULES . 'phreedom/language/' . $_SESSION['language'] . '/language.php';
if (file_exists($path)) { require_once($path); } 
else { require_once(DIR_FS_MODULES . 'phreedom/language/en_us/language.php'); }
// define general functions and classes used application-wide
require_once(DIR_FS_MODULES  . 'phreedom/defaults.php');
require_once(DIR_FS_INCLUDES . 'common_functions.php');
require_once(DIR_FS_INCLUDES . 'common_classes.php');
set_error_handler("PhreebooksErrorHandler");
set_exception_handler('PhreebooksExceptionHandler');
// pull in the custom language over-rides for this module/page
$custom_path = DIR_FS_MODULES . $module . '/custom/pages/' . $page . '/extra_defines.php';
if (file_exists($custom_path)) { include($custom_path); }
gen_pull_language($module);
define('DIR_WS_THEMES', 'themes/' . (isset($_SESSION['admin_prefs']['theme']) ? $_SESSION['admin_prefs']['theme'] : DEFAULT_THEME) . '/');
define('MY_COLORS',isset($_SESSION['admin_prefs']['colors'])?$_SESSION['admin_prefs']['colors']:DEFAULT_COLORS);
define('MY_MENU',  isset($_SESSION['admin_prefs']['menu'])  ?$_SESSION['admin_prefs']['menu']  :DEFAULT_MENU);
define('DIR_WS_IMAGES', DIR_WS_THEMES . 'images/');
if (file_exists(DIR_WS_THEMES . 'icons/')) { define('DIR_WS_ICONS',  DIR_WS_THEMES . 'icons/'); }
else { define('DIR_WS_ICONS', 'themes/default/icons/'); } // use default
$messageStack = new messageStack;
$toolbar      = new toolbar;
// determine what company to connect to
$db_company = (isset($_SESSION['company'])) ? $_SESSION['company'] : $_SESSION['companies'][$_POST['company']];
if ($db_company && file_exists(DIR_FS_MY_FILES . $db_company . '/config.php')) {
  define('DB_DATABASE', $db_company);
  require_once(DIR_FS_MY_FILES . $db_company . '/config.php');
  define('DB_SERVER_HOST',DB_SERVER); // for old PhreeBooks installs
  // Load queryFactory db classes
  require_once(DIR_FS_INCLUDES . 'db/' . DB_TYPE . '/query_factory.php');
  $db = new queryFactory();
  $db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
  // set application wide parameters for phreebooks module
  $result = $db->Execute_return_error("select configuration_key, configuration_value from " . DB_PREFIX . "configuration");
  if ($db->error_number != '' || $result->RecordCount() == 0) trigger_error(LOAD_CONFIG_ERROR, E_USER_ERROR);
  while (!$result->EOF) {
	define($result->fields['configuration_key'], $result->fields['configuration_value']);
	$result->MoveNext();
  }
  // search the list modules and load configuration files and language files
  gen_pull_language('phreedom', 'menu');
  gen_pull_language('phreebooks', 'menu');
  require_once(DIR_FS_MODULES . 'phreedom/config.php');
  $messageStack->debug_header();
  $loaded_modules = array();
  $dirs = scandir(DIR_FS_MODULES);
  foreach ($dirs as $dir) { // first pull all module language files, loaded or not
    if ($dir == '.' || $dir == '..') continue;
	if (is_dir(DIR_FS_MODULES . $dir)) gen_pull_language($dir, 'menu');
  	if (defined('MODULE_' . strtoupper($dir) . '_STATUS')) { // module is loaded
	  $loaded_modules[] = $dir;
      require_once(DIR_FS_MODULES . $dir . '/config.php');
    } 
  }
  // pull in the custom language over-rides for this module (to pre-define the standard language)
  $path = DIR_FS_MODULES . $module . '/custom/pages/' . $page . '/extra_menus.php';
  if (file_exists($path)) { include($path); }
  $currencies = new currencies();
}
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > (SESSION_TIMEOUT_ADMIN < 360 ? 360 : SESSION_TIMEOUT_ADMIN))) {
	$_SESSION = array('language'=>$_SESSION['language'], 'companies'=>$_SESSION['companies']);
	gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
}
$_SESSION['LAST_ACTIVITY'] = time();
$prefered_type = ENABLE_SSL_ADMIN == 'true' ? 'SSL' : 'NONSSL';
if ($request_type <> $prefered_type) gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL')); // re-direct if SSL request not matching actual request
if ($user_validated && !defined('DEFAULT_CURRENCY')) $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error'); // check for default currency defined

?>