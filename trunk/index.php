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
// Path: /index.php
//
ob_start();
ini_set('log_errors','1');
ini_set('display_errors', '1');
ini_set('max_input_vars', '3000');
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
    	$admin->attach(new \core\classes\outputPage);
    	$admin->attach(new \core\classes\outputXml);
    	$admin->attach(new \core\classes\outputJson);
    	$admin->attach(new \core\classes\outputMobile);
    	$messageStack->debug("\n checking if user is validated");
    	\core\classes\user::is_validated($admin);
    	$admin->fireEvent($admin->action);
   	}catch (\core\classes\userException $e) {
   		$messageStack->add($e->getMessage());
   		if (is_object($db)) gen_add_audit_log($e->getMessage());
   		$messageStack->debug("\n\n".$e->getTraceAsString());
   		if($e->action){
   			$admin->fireEvent($e->action);//@todo replace fireEvent.
  		} else{
	  		$admin->fireEvent("loadCrashPage");
  		}
	}
}catch (\Exception $e) {
	$messageStack->add("other Exception ".$e->getMessage());
	$messageStack->debug("\n\n".$e->getTraceAsString());
	$admin->fireEvent("loadCrashPage");
}
$messageStack->write_debug();
$admin->dataBaseConnection = null;
?>