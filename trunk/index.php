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
// Path: /index.php
//
ob_start();
ini_set('log_errors','1');
ini_set('display_errors', '1');
error_reporting(E_ALL);
if (isset($_POST['module']))    $module = $_POST['module'];
elseif (isset($_GET['module'])) $module = $_GET['module'];
else                            $module = 'phreedom';
if (isset($_POST['page']))      $page = $_POST['page'];
elseif (isset($_GET['page']))   $page = $_GET['page'];
else                     		$page = 'main';
$include_template = null;
$cInfo = null;

require_once('includes/application_top.php');
if (!\core\classes\user::is_validated()) {
  	if ($page == 'ajax'){
		echo createXmlHeader() . xmlEntry('error', SORRY_YOU_ARE_LOGGED_OUT) . createXmlFooter();
		ob_end_flush();
		session_write_close();
		die;
  	}
  	if (isset($_REQUEST['module'])	&& !$_SESSION['pb_module'])	$_SESSION['pb_module']	= $_REQUEST['module'];
  	if (isset($_REQUEST['page']) 	&& !$_SESSION['pb_page']) 	$_SESSION['pb_page'] 	= $_REQUEST['page'];
  	if (isset($_REQUEST['jID']) 	&& !$_SESSION['pb_jID'])	$_SESSION['pb_jID']		= $_REQUEST['jID'];
  	if (isset($_REQUEST['type']) 	&& !$_SESSION['pb_type'])	$_SESSION['pb_type']	= $_REQUEST['type'];
  	if (isset($_REQUEST['list'])	&& !$_SESSION['pb_list'])	$_SESSION['pb_list']	= $_REQUEST['list'];
	$module = 'phreedom';
	$page   = 'main';
  	if (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('validate','pw_lost_sub','pw_lost_req'))){
   		$_REQUEST['action'] = 'login';
  	}
}

try{
	try{
		$page_template = new \core\classes\page();
    	$ActionBefore  = "{$_REQUEST['module']}_.{$_REQUEST['page']}_before_{$_REQUEST['action']}";
    	foreach ($admin_classes as $module_class){
    		if ($module_class->installed && method_exists($module_class, $ActionBefore)) {
    			$messageStack->debug("class {$admin_classes->id} has action method $ActionBefore");
    			$module_class->$ActionBefore();
    		}
    	}
    	$Action = "{$_REQUEST['module']}_.{$_REQUEST['page']}_{$_REQUEST['action']}";
    	if ($admin_classes[$_REQUEST['module']]->installed == false )throw new \Exception("module {$admin_classes[$_REQUEST['module']]->id} isn't installed");
    	if (method_exists($admin_classes[$_REQUEST['module']], $Action) == false) throw new \Exception("module {$admin_classes[$_REQUEST['module']]->id} hasn't got action method $Action ");
    	$messageStack->debug("class {$admin_classes[$_REQUEST['module']]->id} has action method $Action");
    	$cInfo = $admin_classes[$_REQUEST['module']]->$Action();
    	$ActionAfter  = "{$_REQUEST['module']}_.{$_REQUEST['page']}_after_{$_REQUEST['action']}";
    	foreach ($admin_classes as $module_class) {
    		if ($module_class->installed && method_exists($admin_classes, $ActionAfter)) {
    			$messageStack->debug("class {$admin_classes->id} has action method $ActionAfter");
    			$admin_classes->$ActionAfter();
    		}
    	}
    	// handle ajax and json
    	if ($_REQUEST['page'] == 'ajax'){
    		echo createXmlHeader();
    		foreach (get_object_vars($cInfo) as $key => $value) xmlEntry($key, $value);
    		echo createXmlFooter();
		} else if ($_REQUEST['page'] == 'json'){
			header('Content-Type: application/json');
			echo json_encode($cInfo);
		} else {
			$page_template->display();
		}
		ob_end_flush();
		session_write_close();
		die;
   	}catch (Exception $e) {
   		switch (get_class($e)) {
   			case "\core\classes\userException":
   			case "\soapException":
   				$messageStack->add($e->getMessage(), $e->getCode());
  				if (method_exists($class, $_REQUEST['display'])){
  					$class->$_REQUEST['display']();
  				}else{
  					throw $e;
  				}
  			default:
  				throw $e;
   		}
	}
}catch (Exception $e) {
	$messageStack->add($e->getMessage(), $e->getCode());
	$page_template = new \core\classes\page();
}

if ($page == 'ajax') {
  	$custom_pre_process_path = DIR_FS_MODULES . $module . "custom/ajax/{$_GET['op']}.php";
  	$pre_process_path = DIR_FS_MODULES . $module . "/ajax/{$_GET['op']}.php";
  	if (file_exists($custom_pre_process_path)) {
  		require($custom_pre_process_path);
  		ob_end_flush();
		session_write_close();
  		die;
  	}elseif (file_exists($pre_process_path)) {
	  	require($pre_process_path);
	  	ob_end_flush();
		session_write_close();
	  	die;
  	}
  	trigger_error("cant find ajax page {$_GET['op']} in module $module", E_USER_ERROR);
}else if (stristr($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
	header('Content-Type: application/json; charset=utf-8');
  	$pre_process_path = DIR_FS_MODULES . $module . 'custom/json/' . $_GET['op'] . '.php';
  	if (file_exists($pre_process_path)) { require($pre_process_path); die; }
  	$pre_process_path = DIR_FS_MODULES . $module . '/json/' . $_GET['op'] . '.php';
  	if (file_exists($pre_process_path)) { require($pre_process_path); die; }
 	trigger_error("No json file, looking for the file: $pre_process_path", E_USER_ERROR);
}
$custom_html      = false;
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
$pre_process_path = DIR_FS_MODULES . $module . '/pages/' . $page . '/pre_process.php';
try{
	if ( file_exists($pre_process_path)) {
		define('DIR_FS_WORKING', DIR_FS_MODULES . $module . '/');
	} else {
		trigger_error("No pre_process file, looking for the file: $pre_process_path", E_USER_ERROR);
	}
	require($pre_process_path);
	if ( file_exists(DIR_FS_WORKING . "custom/pages/$page/$include_template")) {
		$template_path = DIR_FS_WORKING . "custom/pages/$page/$include_template";
	} else {
		$template_path = DIR_FS_WORKING . "pages/$page/$include_template";
	}
}catch(\Exception $e){
	$include_header = true;
	$include_footer = true;
	if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != ''){
		$template_path = $_SERVER['HTTP_REFERER'];
	}else{
		$template_path = DIR_FS_MODULES . "phreedom/pages/main/template_main.php";
	}
}

require($pre_process_path);
if ( file_exists(DIR_FS_WORKING . "custom/pages/$page/$include_template")) {
  	$template_path = DIR_FS_WORKING . "custom/pages/$page/$include_template";
} else {
  	$template_path = DIR_FS_WORKING . "pages/$page/$include_template";
}
require('includes/template_index.php');
require('includes/application_bottom.php');
ob_end_flush();
session_encode();
session_write_close();
?>