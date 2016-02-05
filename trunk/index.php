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
// Path: /index.php
//
ob_start();
ini_set('log_errors','1');
ini_set('display_errors', '1');
ini_set('max_input_vars', '3000');
error_reporting(E_ALL & ~E_NOTICE);//@todo set to excelude e_notice
if (isset($_POST['module']))    $module = $_POST['module'];
elseif (isset($_GET['module'])) $module = $_GET['module'];
else                            $module = 'phreedom';
if (isset($_POST['page']))      $page = $_POST['page'];
elseif (isset($_GET['page']))   $page = $_GET['page'];
else                     		$page = 'main';
try{
	require_once('includes/application_top.php');
   	$messageStack->debug("\n checking if user is validated");
   	if ($admin->cInfo->action) $admin->removeEventsAndAddNewEvent($admin->cInfo->action);
   	$admin->startProcessingEvents();
	ob_end_flush();
   	$messageStack->write_debug();
   	session_write_close();
}catch (\core\classes\userException $e) {
	\core\classes\messageStack::add($e->getMessage());
	if (is_object($admin->DataBase)) gen_add_audit_log($e->getMessage());
	$messageStack->debug(" ".$e->getMessage());
	$messageStack->debug(" fire event : $e->action");
	$messageStack->debug("\n\n\n".$e->getTraceAsString());
	if (is_object($admin)) {
		$admin->removeEventsAndAddNewEvent($e->action); //@todo werkt nog niet altijd
		$admin->startProcessingEvents();
	} else {
		echo "sorry but there was a unforseen error <br/> <b>{$e->getMessage()}</b><br/><br/>".$e->getTraceAsString();
	}
}
$admin->DataBase = null;
?>