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
    public $debug_info 	= NULL;

    function __construct(){
    	error_log("/***** restarting messageStack *****/". PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
    }

    function add($message, $type = 'error') {
    	echo '<script type="text/javascript">';
    	echo "MessageStackAdd('". gen_js_encode($message)."', '{$error}');";
    	echo "<script>";
      	if (DEBUG) error_log("messageStack error:".$message . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
	  	return true;
    }

    function reset() {
      unset($_SESSION['messageToStack']);
    }

    function output() {
		$output = NULL;
	  	if (! isset($_SESSION['messageToStack'])) return '';
	  	$output .= '<table style="border-collapse:collapse;width:100%">' . chr(10);
		foreach ($_SESSION['messageToStack'] as $value) {
			$output .= '<tr><td ' . $value['params'] . ' style="width:100%">' . $value['text'] . '</td></tr>' . chr(10);
	  	}
	  	$output .= '</table>' . chr(10);
	  	$this->reset();
      	return $output;
    }

  	function output_xml() {
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
	  	$this->reset();
      	return $xml;
    }

    Static function debug_log ($txt){
    	error_log("date: " . date('Y-m-d H:i:s') . " company:" .\core\classes\user::get_company(). " user: ".\core\classes\user::get('display_name'). ' ' . $txt . PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
    }

	function debug($txt) {
	  	global $admin;
	  	error_log("date: " . date('Y-m-d H:i:s') . " company:" .\core\classes\user::get_company(). " user: ".\core\classes\user::get('display_name'). ' ' . substr($txt, 1) . PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
	  	if (substr($txt, 0, 1) == "\n") {
//echo "\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $admin->DataBase->count_queries . " SQLs " . (int)($admin->DataBase->total_query_time * 1000)." ms => " . substr($txt, 1) . '<br>';
	    	$this->debug_info .= "\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $admin->DataBase->count_queries . " SQLs " . (int)($admin->DataBase->total_query_time * 1000)." ms => ";
	    	$this->debug_info .= substr($txt, 1);
	  	} else {
	    	$this->debug_info .= $txt;

	  	}
	}

	function write_debug() {
		$filename = DIR_FS_MY_FILES."/development.log";
        if (!$handle = @fopen($filename, 'rb'))             throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, $filename));
        // send the right headers
        header_remove();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename='" . basename($filename) . "'");
        header("Content-Length: " . filesize($filename));
        readfile($file);
        $this->add("Successfully created $filename file.","success");
	}

	function __destruct(){
		error_log("/***** ending messageStack *****/". PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
	}
}