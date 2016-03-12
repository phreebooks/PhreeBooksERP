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
//  Path: /includes/classes/userException.php
//
// this class will allow us to catch user errors and return them to theire pevious page.
namespace core\classes;
class userException extends \Exception {
	public $action = "LoadCrash";

	function __construct ($message, $action = NULL, $code = 0, Exception $previous = NULL){
		\core\classes\messageStack::debug_log(" There was a error $message -> action = $action" );
		error_log("userException ".$message . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
		log_trace();
		if ($action) $this->action = $action;
		parent::__construct($message, $code, $previous);
	}

	function __destruct(){
		//print_r($this);
	}
}
?>