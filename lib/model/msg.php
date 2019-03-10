<?php
/*
 * User message methods and debug messages
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-02-06
 * @filesource /lib/model/msg.php
 */

namespace bizuno;

final class messageStack
{
    var $size  = 0;
    var $error = [];
    var $debug_file = 'trace.txt';
    var $trap  = false; // when set to true, writes a debug trace file

    /**
     * Initializes the trace string, sets up other variables.
     */
    function __construct()
    {
        if (!defined('SCRIPT_START_TIME')) { define('SCRIPT_START_TIME', microtime(true)); }
        $version = defined('MODULE_BIZUNO_VERSION') ? MODULE_BIZUNO_VERSION : 'unknown';
        $portal  = explode('.', $_SERVER['SERVER_ADDR']);
        $this->trace  = "Trace information for debug purposes. Bizuno release $version, portal {$portal[3]} - generated ".date('Y-m-d H:i:s')."\n";
        $this->trace .= "Trace Start Time: ".(int)(1000 * (microtime(true) - SCRIPT_START_TIME))." ms\n\n";
        $this->trace .= "GET Vars = " .print_r($_GET, true)."\n";
        $this->trace .= "POST Vars = ".print_r($_POST,true)."\n";
        set_error_handler("\bizuno\myErrorHandler");
        set_exception_handler("\bizuno\myExceptionHandler");
    }

    /**
     * Adds a message to the log.
     * @param String $message The message that is displayed in the log
     * @param String $level What kind of error, types are 'info', 'error','caution','warning','success'. default is 'error'
     * @return boolean returns true always
     */
    function add($message, $level='error', $title='')
    {
        switch ($level) {
            case 'trap':    $this->trap = true;
            default:
            case 'error':   $this->error['error'][]  = ['text'=>$message]; break;
            case 'caution':
            case 'warning': $this->error['warning'][]= ['text'=>$message]; break;
            case 'info':    $this->error['info'][]   = ['text'=>$message, 'title'=>!empty($title) ? $title : lang('information')]; break;
            case 'success': $this->error['success'][]= ['text'=>$message]; break;
        }
        $this->debug("\nAdding to msgStack, level $level, msg: $message");
        return true;
    }

    /**
     * Adds a log entry to the table audit_log
     * @param string $log_entry - Message to add to the log
     * @return boolean false
     */
    function log($log_entry='')
    {
        if (!$log_entry) { return; }
        $fields = [
            'user_id'   => getUserCache('profile', 'admin_id', false, 0),
            'module_id' => isset($GLOBALS['bizunoModule']) ? $GLOBALS['bizunoModule'] : 'N/A',
            'ip_address'=> $_SERVER['REMOTE_ADDR'],
            'log_entry' => substr($log_entry, 0, 256)];
        if (defined('BIZUNO_DB_PREFIX')) { dbWrite(BIZUNO_DB_PREFIX.'audit_log', $fields); }
    }

    /**
     * Adds a line to the debug string to aid in debugging the code, need to set $trap to write file at end of script
     * @global object $db - the connected database, used to track # of sql's
     * @param string $text - string to add to the debug string, preceed with \n (newline) to time stamp and display current stats
     */
    function debug($text, $trap=false)
    {
        global $db;
        if (is_array($text)) { $text = "\n".print_r($text, true); }
        $dbSQLs = !empty($db->connected) ? $db->total_count : 0;
        $dbTime = !empty($db->connected) ? number_format($db->total_time * 1000, 2) : 0;
        if (substr($text, 0, 1) == "\n") { // newline character at first position will trigger timestamp
            $this->trace .= "\nTime: ".(int)(1000 * (microtime(true) - SCRIPT_START_TIME))." ms, $dbSQLs SQLs $dbTime ms => ".substr($text, 1);
        } else {
            $this->trace .= $text;
        }
        if ($trap=='trap') { $this->trap = true; }
    }

    /**
     * Write the debug file to the users home folder
     * @global object $db - connected database
     * @param sting $filename - [default ''] filename to write, if left blank, the default filename will be written
     * @param boolean $append - [default false] Whether to append the debug to the current trace file or erase it and save just the current information
     * @param boolean $force  - [default false] Forces the debug file to be written in all cases
     * @return boolean false, problems are contained in the messageStack
     */
    function debugWrite($filename=false, $append=false, $force=false)
    {
        global $db;
        if (!$force && (!$this->trap || strlen($this->trace) < 1)) { return; }
        $dbCnt = !empty($db->total_count)? $db->total_count : 0;
        $dbTime= !empty($db->total_time) ? (int)($db->total_time * 1000) : 0;
        msgDebug("\nMessageStack array contains: ".print_r($this->error, true));
        msgDebug("\nPage trace stats: Execution Time: ".(int)(1000 * (microtime(true) - SCRIPT_START_TIME))." ms, $dbCnt queries taking $dbTime ms");
        $dest = $filename ? $filename : $this->debug_file;
        $io = new \bizuno\io();
        $io->fileWrite($this->trace, $dest, true, $append, true);
    }
}

/**
 * Wrapper to add a message to the response stack
 * @param string $msg - the message to add to the stack
 * @param string $level - [default: error] The alert level of the message, choices are success, caution, or error
 * @param string $title - For type Info, this will set the window title from the default 'Information'
 */
function msgAdd($msg, $level='error', $title='')
{
    global $msgStack;
    if (is_object($msgStack)) { $msgStack->add($msg, $level, $title); }
}

/**
 * Merges the current message stack with a new message stack, retaining error levels
 * @param string $msg - the message to merge to the stack
 */
function msgMerge($msg=[])
{
    global $msgStack;
    if (is_object($msgStack)) {
        $msgStack->error = array_merge_recursive($msgStack->error, $msg);
    }
}

/**
 * Stores the msgStack in a session variable to be displayed at a later time. This function is designed to hold any messages when a page load is performed in multiple steps.
 * The messages will be included in the next html page reload.
 * @global type $msgStack
 */
function msgSession()
{
    global $msgStack;
    setUserCache('msgStack', false, array_merge_recursive(getUserCache('msgStack'), $msgStack->error));
    $msgStack->error = [];
}

/**
 * Wrapper to add a message to the audit log in the db
 */
function msgLog($msg)
{
    global $msgStack;
    $msgStack->log($msg);
}

/**
 * Wrapper to add a message to the debug trace file
 */
function msgDebug($msg, $trap=false)
{
    global $msgStack;
    $msgStack->debug($msg, $trap=false);
}

/**
 * Wrapper to force the writing of the trace file
 * @param filename - [default: false] set a full path from myFolder to change from writing file at myFolder/trace.txt
 * @param append - [default: false] set to true to append to current file
 */
function msgDebugWrite($filename=false, $append=false, $force=false)
{
    global $msgStack;
    $msgStack->debugWrite($filename, $append, $force);
}

/**
 * Sets the messageStack trap flag to capture the debug trace file
 */
function msgTrap()
{
    global $msgStack;
    msgDebug("\nTRACE msgTrap SET");
    $msgStack->trap = true; //$capture;
}

/**
 * Returns the number of errors in the messageStack
 * @return Number of entries in stack with the tag error, 0 if array is empty
 */
function msgErrors()
{
    global $msgStack;
    return isset($msgStack->error['error']) ? sizeof($msgStack->error['error']) : 0;
}

/**
 * Wrapper to write a value to the message temporary variable
 * @global type $msgStack
 * @param type $idx
 * @param type $value
 */
function msgTempWrite($idx='idx', $value='')
{
    global $msgStack;
    if (!isset($msgStack->temp)) { $msgStack->temp = []; }
    $msgStack->temp[$idx] = $value;
}

/**
 * Wrapper to read a value from the message temporary variable
 * @global type $msgStack
 * @param type $idx
 * @return boolean
 */
function msgTempRead($idx='idx')
{
    global $msgStack;
    if (!isset($msgStack->temp[$idx])) { return $msgStack->temp[$idx]; }
}

/**
 * Writes system messages for a certain type indexed to prevent duplication
 * @param type $msgs
 */
function msgSysWrite($msgs=[])
{
    foreach ($msgs as $row) {
        // check db to see if the index exists
        $found = dbGetValue(BIZUNO_DB_PREFIX.'phreemsg', 'id', "msg_id='{$row['msg_id']}'");
        $data = ['msg_id'=>$row['msg_id'], 'subject'=>$row['subject']];
        if (!$found) { $data['post_date'] = date('Y-m-d h:i:s'); }
        dbWrite(BIZUNO_DB_PREFIX.'phreemsg', $data, $found?'update':'insert', "id=$found");
    }
}
