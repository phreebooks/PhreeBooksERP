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
//  Path: /modules/shipping/pages/admin/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreedom', 'admin');
gen_pull_language('contacts');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/shipping.php');
/**************   page specific initialization  *************************/
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
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'install':
  	\core\classes\user::validate_security($security_level, 4);
	$admin_classes['shipping']->methods[$method]->install(); 
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'remove';
  	\core\classes\user::validate_security($security_level, 4);
	$admin_classes['shipping']->methods[$method]->delete(); // handle special case removal, db, files, etc
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'save':
  	\core\classes\user::validate_security($security_level, 3);
    // foreach method if enabled, save info
	foreach ($admin_classes['shipping']->methods as $method) {
	  	if ($method->installed) $method->update();
	}
	// save general tab
	foreach ($admin_classes['shipping']->keys as $key => $default) {
	  	$field = strtolower($key);
      	if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
    }
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
    break;
  case 'signup':
  	\core\classes\user::validate_security($security_level, 4);
	if (method_exists($admin_classes['shipping']->methods[$method], 'signup')) $admin_classes['shipping']->methods[$method]->signup();
//	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'backup':
    $carrier   = db_prepare_input($_POST['carrier']);
	$fy_month  = db_prepare_input($_POST['fy_month']);
	$fy_year   = db_prepare_input($_POST['fy_year']);
  	$conv_type = db_prepare_input($_POST['conv_type']);
	// set execution time limit to a large number to allow extra time 
	if (ini_get('max_execution_time') < 20000) set_time_limit(20000);
	$backup              = new \phreedom\classes\backup;
	$backup->source_dir  = DIR_FS_MY_FILES . $_SESSION['company'].'/shipping/labels/'.$carrier.'/'.$fy_year.'/'.$fy_month.'/';
	$backup->dest_dir    = DIR_FS_MY_FILES . 'backups/';
	switch ($conv_type) {
		case 'bz2': 
			$backup->dest_file = 'ship_' . $carrier . '_' . $fy_year . $fy_month . '.tar.bz2';
		    $backup->make_bz2('dir');
			break;
		default:
		case 'zip': 
			$backup->dest_file = 'ship_' . $carrier . '_' . $fy_year . $fy_month . '.zip';
			$backup->make_zip('dir');
			break;
	}
	gen_add_audit_log(GEN_DB_DATA_BACKUP, TABLE_AUDIT_LOG);
	$backup->download($backup->dest_dir, $backup->dest_file); // will not return if successful
	$default_tab_id = 'tools';
    break;
  case 'clean':
    $carrier   = db_prepare_input($_POST['carrier']);
	$fy_month  = db_prepare_input($_POST['fy_month']);
	$fy_year   = db_prepare_input($_POST['fy_year']);
  	$conv_type = db_prepare_input($_POST['conv_type']);
	$backup    = new \phreedom\classes\backup;
	$backup->source_dir  = DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/' . $carrier . '/' . $fy_year . '/' . $fy_month . '/';
    $backup->delete_dir($backup->source_dir, $recursive = true);
	gen_add_audit_log(GEN_FILE_DATA_CLEAN);
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
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', MODULE_SHIPPING_TITLE);
?>