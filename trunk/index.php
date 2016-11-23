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
try{
	require_once('includes/common_functions.php');
	set_error_handler("PhreebooksErrorHandler");
	set_exception_handler('PhreebooksExceptionHandler');
	spl_autoload_register('Phreebooks_autoloader', true, false);
	require_once('includes/application_top.php');
   	if ($admin->cInfo->action) $admin->removeEventsAndAddNewEvent($admin->cInfo->action);
   	$admin->startProcessingEvents();
	ob_end_flush();
   	session_write_close();
   	\core\classes\messageStack::end();
}catch (\Exception $e) {
	\core\classes\messageStack::add($e->getMessage());
	if (is_object($admin->DataBase)) gen_add_audit_log($e->getMessage());
	\core\classes\messageStack::debug_log(" ".$e->getMessage());
	\core\classes\messageStack::debug_log(" fire event : $e->action");
	\core\classes\messageStack::debug_log("\n\n\n".$e->getTraceAsString());
	if( $basis->cInfo->contentType == "application/json"){
		$temp->error_message = $e->getMessage();
		echo json_encode($temp);
	}else{
		if (is_object($admin) && !empty($e->action)) {
			// maybe redirect user to template error method.
			$admin->removeEventsAndAddNewEvent($e->action); //@todo werkt nog niet altijd
			$admin->startProcessingEvents();
		} else {
			echo "Sorry but there was a unforseen error <br/> <b>".$e->getMessage()."</b><br/><br/>".$e->getTraceAsString();
		}
	}
	\core\classes\messageStack::end();
}
?>