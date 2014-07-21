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
//  Path: /modules/shipping/pages/admin/pre_process.php
//
$security_level = validate_user(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreedom', 'admin');
gen_pull_language('contacts');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/shipping.php');
require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
require_once(DIR_FS_WORKING . 'classes/install.php');
/**************   page specific initialization  *************************/
$error      = false; 
$method_dir = DIR_FS_WORKING . 'methods/';
$install    = new shipping_admin();
// see if installing or removing a method
if (substr($_REQUEST['action'], 0, 8) == 'install_') {
  $method = substr($_REQUEST['action'], 8);
  $_REQUEST['action'] = 'install';
} elseif (substr($_REQUEST['action'], 0, 7) == 'remove_') {
  $method = substr($_REQUEST['action'], 7);
  $_REQUEST['action'] = 'remove';
} elseif (substr($_REQUEST['action'], 0, 7) == 'signup_') {
  $method = substr($_REQUEST['action'], 7);
  $_REQUEST['action'] = 'signup';
}
// load the available methods
$methods = array();
$contents = scandir($method_dir);
foreach ($contents as $choice) {
  if ($choice <> '.' && $choice <> '..') {
	load_method_language($method_dir, $choice);
	$methods[] = $choice;
  }
}
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'install':
  	validate_security($security_level, 4);
	require_once($method_dir . $method . '/' . $method . '.php');
	$properties = new $method();
	write_configure('MODULE_SHIPPING_' . strtoupper($method) . '_STATUS', '1');
	foreach ($properties->keys() as $key) write_configure($key['key'], $key['default']);
	if (method_exists($properties, 'install')) $properties->install(); // handle special case install, db, files, etc
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'remove';
  	validate_security($security_level, 4);
  	require_once($method_dir . $method . '/' . $method . '.php');
	$properties = new $method();
	if (method_exists($properties, 'remove')) $properties->remove(); // handle special case removal, db, files, etc
	foreach ($properties->keys() as $key) { // remove all of the keys from the configuration table
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key = '" . $key['key'] . "'");
	}
	remove_configure('MODULE_SHIPPING_' . strtoupper($method) . '_STATUS');
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'save':
  	validate_security($security_level, 3);
    // foreach method if enabled, save info
	if (sizeof($methods) > 0) foreach ($methods as $shipper) {
	  if (defined('MODULE_SHIPPING_' . strtoupper($shipper) . '_STATUS')) {
	    require_once($method_dir . $shipper . '/' . $shipper . '.php');
	    $properties = new $shipper;
	    $properties->update();
	  }
	}
	// save general tab
	foreach ($install->keys as $key => $default) {
	  $field = strtolower($key);
      if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
    }
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
    break;
  case 'signup':
  	validate_security($security_level, 4);
	require_once($method_dir . $method.'/'.$method.'.php');
	$properties = new $method();
	if (method_exists($properties, 'signup')) $properties->signup();
//	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'backup':
    $carrier   = db_prepare_input($_POST['carrier']);
	$fy_month  = db_prepare_input($_POST['fy_month']);
	$fy_year   = db_prepare_input($_POST['fy_year']);
  	$conv_type = db_prepare_input($_POST['conv_type']);
	// set execution time limit to a large number to allow extra time 
	if (ini_get('max_execution_time') < 20000) set_time_limit(20000);
	$backup              = new backup;
	$backup->source_dir  = DIR_FS_MY_FILES . $_SESSION['company'].'/shipping/labels/'.$carrier.'/'.$fy_year.'/'.$fy_month.'/';
	$backup->dest_dir    = DIR_FS_MY_FILES . 'backups/';
	switch ($conv_type) {
	  case 'bz2': 
		$backup->dest_file = 'ship_' . $carrier . '_' . $fy_year . $fy_month . '.tar.bz2';
	    if ($backup->make_bz2('dir')) $error = true;
		break;
	  default:
	  case 'zip': 
		$backup->dest_file = 'ship_' . $carrier . '_' . $fy_year . $fy_month . '.zip';
		if ($backup->make_zip('dir')) $error = true;
		break;
	}
	if (!$error) {
	  gen_add_audit_log(GEN_DB_DATA_BACKUP, TABLE_AUDIT_LOG);
	  $backup->download($backup->dest_dir, $backup->dest_file); // will not return if successful
	}
	$default_tab_id = 'tools';
    break;
  case 'clean':
    $carrier   = db_prepare_input($_POST['carrier']);
	$fy_month  = db_prepare_input($_POST['fy_month']);
	$fy_year   = db_prepare_input($_POST['fy_year']);
  	$conv_type = db_prepare_input($_POST['conv_type']);
	$backup    = new backup;
	$backup->source_dir  = DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/' . $carrier . '/' . $fy_year . '/' . $fy_month . '/';
    if ($backup->delete_dir($backup->source_dir, $recursive = true)) $error = true;
	if (!$error) gen_add_audit_log(GEN_FILE_DATA_CLEAN);
	$default_tab_id = 'tools';
	break;
  default:
}
/*****************   prepare to display templates  *************************/
// build some general pull down arrays
$sel_yes_no = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_YES),
);
$sel_checked = array(
 array('id' => '0', 'text' => TEXT_UNCHECKED),
 array('id' => '1', 'text' => TEXT_CHECKED),
);
$sel_show = array(
 array('id' => '0', 'text' => TEXT_HIDE),
 array('id' => '1', 'text' => TEXT_SHOW),
);
$sel_fy_month = array(
  array('id' => '01', 'text'=> TEXT_JAN),
  array('id' => '02', 'text'=> TEXT_FEB),
  array('id' => '03', 'text'=> TEXT_MAR),
  array('id' => '04', 'text'=> TEXT_APR),
  array('id' => '05', 'text'=> TEXT_MAY),
  array('id' => '06', 'text'=> TEXT_JUN),
  array('id' => '07', 'text'=> TEXT_JUL),
  array('id' => '08', 'text'=> TEXT_AUG),
  array('id' => '09', 'text'=> TEXT_SEP),
  array('id' => '10', 'text'=> TEXT_OCT),
  array('id' => '11', 'text'=> TEXT_NOV),
  array('id' => '12', 'text'=> TEXT_DEC),
);
$sel_fy_year = array();
for ($i = 0; $i < 8; $i++) {
  $sel_fy_year[] = array('id' => date('Y')-$i, 'text' => date('Y')-$i);
}
$sel_method = array();
$sel_method[] = array('id' => '', 'text' => GEN_HEADING_PLEASE_SELECT);
foreach ($methods as $value) {
  if (defined('MODULE_SHIPPING_' . strtoupper($value) . '_STATUS')) {
    $sel_method[] = array('id' => $value, 'text' => constant('MODULE_SHIPPING_' . strtoupper($value) . '_TEXT_TITLE'));
  }
}
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', MODULE_SHIPPING_TITLE);
?>