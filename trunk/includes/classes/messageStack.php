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
//  Path: /includes/classes/messageStack.php
//
namespace core\classes;
class messageStack {
    public $debug_info 	= NULL;

    function __construct(){
    	error_log("/***** restarting messageStack *****/". PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
    }

    function add($message, $type = 'error') {
      	if ($type == 'error') {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-error"', 'text' => html_icon('emblems/emblem-unreadable.png', TEXT_ERROR) . '&nbsp;' . $message, 'message' => $message);
        	if (DEBUG) error_log("messageStack error:".$message . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
      	} elseif ($type == 'success') {
	    	if (!HIDE_SUCCESS_MESSAGES) $_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-active"', 'text' => html_icon('emotes/face-smile.png', TEXT_SUCCESS) . '&nbsp;' . $message, 'message' => $message);
      	} elseif ($type == 'caution' || $type == 'warning') {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-highlight"', 'text' => html_icon('emblems/emblem-important.png', TEXT_CAUTION) . '&nbsp;' . $message, 'message' => $message);
      	} else {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-error"', 'text' => $message, 'message' => $message);
        	if (DEBUG) error_log("messageStack error:".$message . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
      	}
      	$this->debug("\n On screen displaying '$type' message = $message");
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
    	error_log("date: " . date('Y-m-d H:i:s') . " company:" .\core\classes\user::get_company(). " user: ".\core\classes\user::__get('display_name'). ' ' . $txt . PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
    }

	function debug($txt) {
	  	global $admin;
	  	error_log("date: " . date('Y-m-d H:i:s') . " company:" .\core\classes\user::get_company(). " user: ".\core\classes\user::__get('display_name'). ' ' . substr($txt, 1) . PHP_EOL, 3, DIR_FS_MY_FILES."/development.log");
	  	if (substr($txt, 0, 1) == "\n") {
//echo "\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $db->count_queries . " SQLs " . (int)($db->total_query_time * 1000)." ms => " . substr($txt, 1) . '<br>';
	    	$this->debug_info .= "\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $db->count_queries . " SQLs " . (int)($db->total_query_time * 1000)." ms => ";
	    	$this->debug_info .= substr($txt, 1);
	  	} else {
	    	$this->debug_info .= $txt;

	  	}
	}

	function write_debug() {
	  	global $admin;
	  	$this->debug_header_info .= "Trace information for debug purposes. Phreedom release {$admin->classes['phreedom']->version}, generated " . date('Y-m-d H:i:s') . ".\n\n";
	  	$this->debug_header_info .= "\nGET     Vars = " . arr2string($_GET);
	  	$this->debug_header_info .= "\nPOST    Vars = " . arr2string($_POST);
	  	$this->debug_header_info .= "\nREQUEST Vars = " . arr2string($_REQUEST);
	  	$this->debug_header_info .= "\nSESSION Vars = " . arr2string($_SESSION);
	  	if (strlen($this->debug_info) < 1) return;
	  	$this->debug_info .= "\n\nPage trace stats: Execution Time: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, {$db->count_queries} queries taking " . (int)($db->total_query_time * 1000)." ms";
      	$filename = DIR_FS_MY_FILES . 'trace.txt';
      	if (!$handle = @fopen($filename, 'w')) 				throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, $filename));
      	if (!@fwrite($handle, $this->debug_header_info)) 	throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, $filename));
      	if (!@fwrite($handle, $this->debug_info)) 			throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, $filename));
      	if (!@fclose($handle)) 								throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, $filename));
	  	$this->debug_info = NULL;
	  	$this->add("Successfully created trace.txt file.","success");
	}

	function __destruct(){

	}
}