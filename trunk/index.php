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
		require_once('includes/application_top.php');
    	$admin_classes->attach(new \core\classes\outputPage);
    	$admin_classes->attach(new \core\classes\outputXml);
    	$admin_classes->attach(new \core\classes\outputJson);
    	$messageStack->debug("\n checking if user is validated");
    	\core\classes\user::is_validated($admin_classes);
    	$admin_classes->fireEvent($admin_classes->action);
   	}catch (\core\classes\userException $e) {
   		$messageStack->add($e->getMessage());
   		if (is_object($db)) gen_add_audit_log($e->getMessage());
   		$messageStack->debug("\n\n".$e->getTraceAsString());
   		if($e->action){
   			$admin_classes->fireEvent($e->action);
  		} else{
	  		$admin_classes->fireEvent("loadCrashPage");
  		}
	}
}catch (\Exception $e) {
	$messageStack->add("other Exception ".$e->getMessage());
	$messageStack->debug("\n\n".$e->getTraceAsString());
	$admin_classes->fireEvent("loadCrashPage");
}
$messageStack->write_debug();
$admin_classes->dataBaseConnection = null;
?>