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
//  Path: /includes/classes/messageStack.php
//
namespace core\classes;
class messageStack {
    
    function start(){
    	error_log("/***** restarting messageStack *****/". PHP_EOL, 3, DIR_FS_MY_FILES."development.log");
    }

    static function add($message, $type  = 'error') {
//    	if ($type == '') $type = 'error';
    	echo "<script type='text/javascript'>";
    	echo "MessageStackAdd('". gen_js_encode($message)."', '{$type}');";
    	echo "</script>";
      	if (DEBUG) error_log("messageStack error:".$message . PHP_EOL, 3, DIR_FS_MY_FILES."errors.log");
	  	return true;
    }

  	function output_xml() { //@todo this needs to be replaced 
		$xml = "<messageStack>\n";
	  	if (! isset($_SESSION['messageToStack'])) return '';
		foreach ($_SESSION['messageToStack'] as $value) {
			if ($value['type'] == 'error') {
				foreach (explode("\n",$value['message']) as $temp){
					if($temp != '') $xml .= xmlEntry("messageStack_error", gen_js_encode($temp));
				}
      		} elseif ($value['type'] == 'success') {
	    		if (!HIDE_SUCCESS_MESSAGES) foreach (explode("\n",$value['message']) as $temp){
					if($temp != '') $xml .= xmlEntry("messageStack_msg", gen_js_encode($temp));
				}
      		} elseif ($value['type'] == 'caution' || $type == 'warning') {
      			foreach (explode("\n",$value['message']) as $temp){
					if($temp != '') $xml .= xmlEntry("messageStack_caution", gen_js_encode($temp));
				}
      		} else {
      			foreach (explode("\n",$value['message']) as $temp){
					if($temp != '') $xml .= xmlEntry("messageStack_error", gen_js_encode($temp));
				}
      		}
	  	}
	  	$xml .= "</messageStack>\n ";
      	return $xml;
    }

    Static function debug_log ($txt){
    	global $admin;
    	$date = new \core\classes\DateTime();
    	error_log("date: " . $date->format('Y-m-d H:i:s.u') . " company: {$_SESSION['user']->company} user: {$_SESSION['user']->display_name}  $txt" . PHP_EOL, 3, DIR_FS_MY_FILES."development.log");
    	if (substr($txt, 0, 1) == "\n") {
    		error_log("\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $admin->DataBase->count_queries . " SQLs " . (int)($admin->DataBase->total_query_time * 1000)." ms => ".substr($txt, 1). PHP_EOL, 3, DIR_FS_MY_FILES."debug.log");
    	}else {
    		error_log($txt. PHP_EOL, 3, DIR_FS_MY_FILES."debug.log");
	  	}
    }

	function write_debug() {
		if (!DEBUG) return;//@todo needs to be checked
		$filename = DIR_FS_MY_FILES."debug.log";
        if (!$handle = @fopen($filename, 'rb'))             throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, $filename));
        // send the right headers
        header_remove();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename='" . basename($filename) . "'");
        header("Content-Length: " . filesize($filename));
        readfile($filename);
        self::add("Successfully created $filename file.","success");
	}

	function end(){
		error_log("/***** ending messageStack *****/". PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
	}
}