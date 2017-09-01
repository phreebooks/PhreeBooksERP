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
//  Path: /includes/application_top.php
//
namespace core\application_top;
define('PAGE_EXECUTION_START_TIME', microtime(true));
if(extension_loaded('apc')) ini_set('apc.enabled','1');
$force_reset_cache = false;
// Check for application configuration parameters
if     (file_exists('includes/configure.php')) { require('includes/configure.php'); }
elseif (file_exists('install/index.php')) {
	ob_end_flush();
  	session_write_close();
	exit(header('Location: install/index.php')); }
else  trigger_error('Phreedom cannot find the configuration file. Aborting!', E_USER_ERROR);
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
define('FILENAME_DEFAULT', 'index');//@todo drop after update
\core\classes\messageStack::start();
define('APC_EXTENSION_LOADED', extension_loaded('apc') && ini_get('apc.enabled'));
// define the inventory types that are tracked in cost of goods sold
define('COG_ITEM_TYPES','si,sr,ms,mi,ma,sa');
//start session functions
session_start();
@ini_set('session.gc_maxlifetime', (SESSION_TIMEOUT_ADMIN < 360 ? 360 : SESSION_TIMEOUT_ADMIN));
session_set_cookie_params((SESSION_TIMEOUT_ADMIN < 360 ? 360 : SESSION_TIMEOUT_ADMIN),'/',$path);
//end session
$_REQUEST = array_merge($_GET, $_POST);
// define general functions and classes used application-wide
require_once(DIR_FS_MODULES  . 'phreedom/defaults.php');
define('DIR_WS_THEMES', 'themes/' . $_SESSION['user']->theme . '/');
if (file_exists(DIR_WS_THEMES . 'icons/')) { define('DIR_WS_ICONS',  DIR_WS_THEMES . 'icons/'); }
else { define('DIR_WS_ICONS', 'themes/default/icons/'); } // use default
$messageStack 	= new \core\classes\messageStack;
$admin 	= APC_EXTENSION_LOADED ? apc_fetch("admin")	: false;
if ($admin == false){
	$admin = new \core\classes\basis;
	if (APC_EXTENSION_LOADED) apc_add("admin", $admin, 600);
}
// set the type of request (secure or not)
$request_type = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1' || strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']),'SSL') || strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']),'SSL'))) ? 'SSL' : 'NONSSL';
$prefered_type = ENABLE_SSL_ADMIN == 'true' ? 'SSL' : 'NONSSL';
if ($request_type <> $prefered_type) gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL')); // re-direct if SSL request not matching actual request
?>