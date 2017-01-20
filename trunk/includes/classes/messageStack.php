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
    
    static function start(){
    	if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
    	error_log("/***** restarting messageStack *****/". PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."development.log");
    }

    static function add($message, $type  = 'error') {
    	if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
    	$title = constant("TEXT_".strtoupper($type));
    	echo "<script type='text/javascript'>";
    	if ($type == 'success') {
			echo "console.info('messageStack = $message');";
    		echo "$.messager.show({title:'$title',msg:'$message',icon:'info'});";
    	} else if ($type == 'caution' || $type == 'warning') {
    		echo "console.info('messageStack = $message');";
    		echo "$.messager.show({title:'$title',msg:'$message',icon:'warning'});";
    	} else {
    		echo "console.error('messageStack = $message');";
    		echo "$.messager.alert('$title','$message','error');";
    	}
    	echo "</script>";
      	if (DEBUG) error_log("messageStack error:".$message . PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."errors.log");
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
	/**
	 * @todo forward to development
	 * @param unknown $txt
	 */
    static function debug_log ($txt){
    	global $admin;
    	if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
    	self::development($txt);
    	if (substr($txt, 0, 1) == "\n") {
    		error_log("\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $admin->DataBase->count_queries . " SQLs " . (int)($admin->DataBase->total_query_time * 1000)." ms => ".substr($txt, 1). PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."debug.log");
    	}else {
    		error_log($txt. PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."debug.log");
	  	}
    }

	function write_debug() {
		if (!DEBUG) return;//@todo needs to be checked
		$filename = DIR_FS_ADMIN . PATH_TO_MY_FILES."debug.log";
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
	
	static function development($txt){
		if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
		$date = new \core\classes\DateTime();
		error_log("date: " . $date->format('Y-m-d H:i:s.u') . " company: {$_SESSION['user']->company} user: {$_SESSION['user']->display_name}  $txt" . PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."development.log");
	}
	
	static function error($txt){
		if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
		self::development($txt);
		$date = new \core\classes\DateTime();
		$text  = $date->format('Y-m-d H:i:s.u') . " User: {$_SESSION['user']->admin_id} Company: {$_SESSION['user']->company} Caught Error: '{$txt}' ";
		error_log($text . PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."/errors.log");
	}
	
	static function end(){
		if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
		error_log("/***** ending messageStack *****/". PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."/development.log");
	}
	
	function __destruct(){
		return;
		if (!defined('PATH_TO_MY_FILES')) define('PATH_TO_MY_FILES','my_files/');
	    $trace = debug_backtrace();
	    $caller = array_shift($trace);
	    $function_name = $caller['function'];
	    error_log(sprintf('%s: Called from %s:%s', $function_name, $caller['file'], $caller['line']) . PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."/errors.log");
	    foreach ($trace as $entry_id => $entry) {
	        $entry['file'] = $entry['file'] ? : '-';
	        $entry['line'] = $entry['line'] ? : '-';
	        if (empty($entry['class'])) {
	            error_log(sprintf('%s %3s. %s() %s:%s', $function_name, $entry_id + 1, $entry['function'], $entry['file'], $entry['line']) . PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."/errors.log");
	        } else {
	            error_log(sprintf('%s %3s. %s->%s() %s:%s', $function_name, $entry_id + 1, $entry['class'], $entry['function'], $entry['file'], $entry['line']) . PHP_EOL, 3, DIR_FS_ADMIN . PATH_TO_MY_FILES."/errors.log");
	        }
	    }
	}
}