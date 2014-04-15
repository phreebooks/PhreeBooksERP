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
error_reporting(E_ALL^E_NOTICE);
if (isset($_POST['module']))    $module = $_POST['module'];
elseif (isset($_GET['module'])) $module = $_GET['module'];
else                            $module = 'phreedom';
if (isset($_POST['page']))      $page = $_POST['page'];
elseif (isset($_GET['page']))   $page = $_GET['page'];
else                     		$page = 'main';
try{
	try{
		$cInfo = null;
		require_once('includes/application_top.php');
		$messageStack->debug("\n starting new page");
		$page_template = new \core\classes\page();
		$messageStack->debug("\n checking if user is validated");
		\core\classes\user::is_validated();
    	$ActionBefore  = "{$_REQUEST['module']}_.{$_REQUEST['page']}_before_{$_REQUEST['action']}";
    	foreach ($admin_classes as $module_class){
    		if ($module_class->installed && method_exists($module_class, $ActionBefore)) {
    			$messageStack->debug("class {$admin_classes->id} has action method $ActionBefore");
    			$module_class->$ActionBefore();
    		}
    	}
    	$Action = "{$_REQUEST['module']}_{$_REQUEST['page']}_{$_REQUEST['action']}";
    	if ($admin_classes[$_REQUEST['module']]->installed === false ) 				throw new \core\classes\userException("module {$admin_classes[$_REQUEST['module']]->id} isn't installed");
    	if (method_exists($admin_classes[$_REQUEST['module']], $Action) === false)	throw new \core\classes\userException("module {$admin_classes[$_REQUEST['module']]->id} hasn't got action method $Action ");
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
    		foreach (get_object_vars($cInfo) as $key => $value) echo xmlEntry($key, $value);
    		echo createXmlFooter();
		} else if ($_REQUEST['page'] == 'json'){
			header('Content-Type: application/json');
			echo json_encode($cInfo);
		}
   	}catch (\core\classes\userException $e) {
   		if (!isset($page_template)) $page_template = new \core\classes\page();
   		if ($_REQUEST['page'] == 'ajax'){
   			echo createXmlHeader();
   			echo xmlEntry("messageStack_error", $e->getMessage());
   			echo createXmlFooter();
   		} else if ($_REQUEST['page'] == 'json'){
   			$temp["messageStack_error"] = $e->getMessage();
   			header('Content-Type: application/json');
   			echo json_encode($temp);
   		} else{
   			$messageStack->add($e->getMessage());
   			if (is_object($db)) gen_add_audit_log($e->getMessage());
   			$messageStack->debug("\n\n".$e->getTraceAsString());
   			if ($e->ReturnToTemplate) {
  				$page_template->loadPage($e->ReturnToModule, $e->ReturnToPage, $e->ReturnToTemplate);
  			} else{
	  			$page_template->loadPage("phreedom", "main", "template_crash");
  			}
  		}
	}
}catch (\Exception $e) {
	if (!isset($page_template)) $page_template = new \core\classes\page();
	$messageStack->add("other Exception ".$e->getMessage());
	$messageStack->debug("\n\n".$e->getTraceAsString());
	$page_template->loadPage("phreedom","main","template_main");
}
if (DEBUG) $messageStack->write_debug();
require('includes/template_index.php');
ob_end_flush();
session_write_close();
die;
?>