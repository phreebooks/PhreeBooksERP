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
//  Path: /modules/phreedom/pages/admin/pre_process.php
//
ini_set('memory_limit','256M');  // Set this big for memory exhausted errors
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
if (defined('MODULE_PHREEFORM_STATUS')) {
  require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
  require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');
}
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
/**************   page specific initialization  *************************/
// see if installing or removing a module
if (substr($_REQUEST['action'], 0, 8) == 'install_') {
  $method = substr($_REQUEST['action'], 8);
  $_REQUEST['action'] = 'install';
} elseif (substr($_REQUEST['action'], 0, 7) == 'remove_') {
  $method = substr($_REQUEST['action'], 7);
  $_REQUEST['action'] = 'remove';
}
// load the current statuses
$status_fields = array();
$result = $admin->DataBase->query("show fields from " . TABLE_CURRENT_STATUS);
while (!$result->EOF) {
  if ($result->fields['Field'] <> 'id') $status_fields[] = $result->fields['Field'];
  $result->MoveNext();
}
$status_values = $admin->DataBase->query("select * from " . TABLE_CURRENT_STATUS);
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  	case 'install':
  	case 'update':
  		try{
	  		\core\classes\user::validate_security($security_level, 4);
			// load the module installation class
			if (!array_key_exists($method, $admin->classes)) throw new \core\classes\userException(sprintf('Looking for the installation script for module %s, but could not locate it. The module cannot be installed!', $method));
			$admin->DataBase->transStart();
			require_once(DIR_FS_MODULES . $method . '/config.php'); // config is not loaded yet since module is not installed.
			if ($_REQUEST['action'] == 'install') {
		  		$admin->classes[$method]->install(DIR_FS_MY_FILES.$_SESSION['company'].'/', false);
		  		$admin->DataBase->write_configure('MODULE_' . strtoupper($admin->classes[$method]->id) . '_STATUS', $admin->classes[$method]->version);
 				gen_add_audit_log(sprintf(TEXT_MODULE_ARGS, $admin->classes[$method]->text) . TEXT_INSTALL , $admin->classes[$method]->version);
 				\core\classes\messageStack::add(sprintf(TEXT_MODULE_ARGS, $admin->classes[$method]->text). TEXT_INSTALL . $admin->classes[$method]->version, 'success');
			} else {
		  		$admin->classes[$method]->update();
		  		$admin->DataBase->write_configure('MODULE_' . strtoupper($admin->classes[$method]->id) . '_STATUS', $admin->classes[$method]->version);
 				gen_add_audit_log(sprintf(TEXT_MODULE_ARGS, $admin->classes[$method]->text) . TEXT_UPDATE, $admin->classes[$method]->version);
	   			\core\classes\messageStack::add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $admin->classes[$method]->id, $admin->classes[$method]->version), 'success');
			}
			if (sizeof($admin->classes[$method]->notes) > 0) foreach ($admin->classes[$method]->notes as $note) \core\classes\messageStack::add($note, 'caution');
			$admin->DataBase->transCommit();
			break;
  		}catch (Exception $e){
  			$admin->DataBase->transRollback();
  			\core\classes\messageStack::add($e->getMessage());
  			break;
  		}
  	case 'remove':
  		try{
		  	\core\classes\user::validate_security($security_level, 4);
			// load the module installation class
			if (!array_key_exists($method, $admin->classes)) throw new \core\classes\userException(sprintf('Looking for the delete script for module %s, but could not locate it. The module cannot be deleted!', $method));
			$admin->DataBase->transStart();
			$admin->classes[$method]->delete(DIR_FS_MY_FILES.$_SESSION['company'].'/');
			$admin->DataBase->transCommit();
			if (sizeof($admin->classes[$method]->notes) > 0) foreach ($admin->classes[$method]->notes as $note) \core\classes\messageStack::add($note, 'caution');
			gen_add_audit_log(sprintf(TEXT_UNINSTALLED_MODULE, $admin->classes[$method]->text));
			break;
  		}catch (Exception $e){
  			$admin->DataBase->transRollback();
  			\core\classes\messageStack::add($e->getMessage());
  			break;
  		}
  	case 'save':
  		try{
		  	\core\classes\user::validate_security($security_level, 3);
			// save general tab
			$admin->DataBase->transStart();
			foreach ($admin->classes['phreedom']->keys as $key => $default) {
				$field = strtolower($key);
		     	if (isset($_POST[$field])) $admin->DataBase->write_configure($key, db_prepare_input($_POST[$field]));
				// special case for field COMPANY_NAME to update company config file
				if ($key == 'COMPANY_NAME' && $_POST[$field] <> COMPANY_NAME) {
					install_build_co_config_file($_SESSION['company'], $_SESSION['company'] . '_TITLE', db_prepare_input($_POST[$field]));
				}
			}
		    $admin->DataBase->transCommit();
			\core\classes\messageStack::add(TEXT_CONFIGURATION_VALUES_HAVE_BEEN_SAVED,'success');
			$default_tab_id = 'company';
		    break;
  		}catch (Exception $e){
  			$admin->DataBase->transRollback();
  			\core\classes\messageStack::add($e->getMessage());
  			break;
  		}
  	case 'delete':
  		try{
  			$admin->DataBase->transStart();
  			\core\classes\user::validate_security($security_level, 4);
  			$subject = $_POST['subject'];
    		$id      = $_POST['rowSeq'];
			if (!$subject || !$id) break;
    		if ($$subject->btn_delete($id)) $close_popup = true;
    		$admin->DataBase->transCommit();
		   	break;
  		}catch (Exception $e){
  			$admin->DataBase->transRollback();
  			\core\classes\messageStack::add($e->getMessage());
  			break;
  		}
  	case 'copy_co':
  		try{
			$db_server = db_prepare_input($_POST['db_server']);
			$db_name   = db_prepare_input($_POST['db_name']);
			$db_user   = db_prepare_input($_POST['db_user']);
			$db_pw     = db_prepare_input($_POST['db_pw']);
			$co_name   = db_prepare_input($_POST['co_name']);
			// error check company name and company full name
			if (!$db_name || !$co_name)           throw new \core\classes\userException(SETUP_CO_MGR_ERROR_EMPTY_FIELD);
			if ($db_name == $_SESSION['company']) throw new \core\classes\userException(SETUP_CO_MGR_DUP_DB_NAME);
			// check for database already exists
			$db_orig = new queryFactory;//@todo pdo
			if (!$db_orig->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE)) trigger_error('Problem connecting to original DB!', E_USER_ERROR);
			$db = new queryFactory;//@todo pdo
			if (!$admin->DataBase->connect($db_server, $db_user, $db_pw, $db_name)) throw new \core\classes\userException(SETUP_CO_MGR_CANNOT_CONNECT);
			// write the db config.php in the company directory
			validate_path(DIR_FS_ADMIN . PATH_TO_MY_FILES . $db_name);
			install_build_co_config_file($db_name, $db_name . '_TITLE',  $co_name);
			install_build_co_config_file($db_name, 'DB_SERVER_USERNAME', $db_user);
			install_build_co_config_file($db_name, 'DB_SERVER_PASSWORD', $db_pw);
			install_build_co_config_file($db_name, 'DB_SERVER_HOST',     $db_server);

			$backup = new \phreedom\classes\backup;
		    $backup->source_dir  = DIR_FS_MY_FILES . $db_name . '/temp/';
		    $backup->source_file = 'temp.sql';
		    foreach ($admin->classes as $key => $class) {
				if (!$class->core && !isset($_POST[$key])) continue;
			    $task        = $_POST[$key . '_action'];
				if ($key == 'phreedom') $task = 'data'; // force complete copy of phreedom module
			    switch ($task) {
				  	case 'core':
				  	case 'demo':
				  		$class->install(DIR_FS_MY_FILES . $db_name . '/', $task == 'demo');
						$admin->DataBase->write_configure('MODULE_' . strtoupper($class->id) . '_STATUS', $class->version);
 						gen_add_audit_log(sprintf(TEXT_MODULE_ARGS, $class->text) . TEXT_INSTALL , $class->version);
 						\core\classes\messageStack::add(sprintf(TEXT_MODULE_ARGS, $class->text). TEXT_INSTALL . $class->version, 'success');
 						if (sizeof($admin->classes[$method]->notes) > 0) foreach ($class->notes as $note) \core\classes\messageStack::add($note, 'caution');
				    	break;
				  	case 'data':
						$table_list = array();
				    	if (is_array($class->tables)) {
					 		foreach ($class->tables as $table => $create_sql) $table_list[] = $table;
					  		$backup->copy_db_table($db_orig, $table_list, $type = 'both', $params = '');
			  			}
						if (is_array($class->dirlist)) foreach($class->dirlist as $source_dir) {
				      		$dir_source = DIR_FS_MY_FILES . $_SESSION['company'] . '/' . $source_dir . '/';
				      		$dir_dest   = DIR_FS_MY_FILES . $db_name             . '/' . $source_dir . '/';
					  		validate_path(DIR_FS_MY_FILES . "$db_name/$source_dir");
					  		$backup->copy_dir($dir_source, $dir_dest);
				    	}
						break;
				  	default: // skip, should not happen
			    }
			}
			// install reports now that categories are set up
			if ($_POST['phreeform_action'] <> 'data') { // if=data reports have been copied, else load basic reports
			    foreach ($admin->classes as $key => $class) admin_add_reports($key, DIR_FS_MY_FILES . $db_name . '/phreeform/');
			}
			if ($_POST['phreebooks_action'] <> 'data') { // install fiscal year if the phreebooks data is not copied
			  	$admin->DataBase->query("TRUNCATE TABLE " . TABLE_ACCOUNTING_PERIODS);
			  	require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
			  	$date = new \core\classes\DateTime($date_from);
			  	$date->modify("-{$date->format('j')} day");
			  	validate_fiscal_year($date->format('Y'), '1', $date->format('Y-m-d'));
			  	build_and_check_account_history_records();
			  	gen_auto_update_period(false);
			}
			// reset SESSION['company'] to new company and redirect to install->store_setup
			$admin->DataBase->query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $co_name . "'
			  where configuration_key = 'COMPANY_NAME'");
			\core\classes\messageStack::add(TEXT_SUCCESSFULY_CREATED_NEW_COMPANY,'success');
			gen_add_audit_log(sprintf(TEXT_MANAGER_ARGS, TEXT_COMPANY). ' - ' . TEXT_COPY, $db_name);
			$_SESSION['db_server'] = $db_server;
			$_SESSION['company']   = $db_name;
			$_SESSION['db_user']   = $db_user;
			$_SESSION['db_pw']     = $db_pw;
		    gen_redirect(html_href_link(FILENAME_DEFAULT, $get_parmas, ENABLE_SSL_ADMIN ? 'SSL' : 'NONSSL'));
			$default_tab_id = 'manager';
  			$admin->DataBase->transCommit();
		   	break;
  		}catch (Exception $e){
  			$admin->DataBase->transRollback();
  			$db = new queryFactory;//@todo pdo
			$admin->DataBase->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
  			\core\classes\messageStack::add($e->getMessage());
  			break;
  		}
  case 'delete_co':
	$db_name = $_POST['del_company'];
	// Failsafe to prevent current company from being deleted accidently
	$backup = new backup;
	if ($db_name == 'none') throw new \core\classes\userException(TEXT_NO_COMPANY_WAS_SELECTED_TO_DELETE .'!');
	if ($db_name <> $_SESSION['company']) {
		// connect to other company, retrieve login info
	  	$config = file(DIR_FS_MY_FILES . $db_name . '/config.php');
	  	foreach ($config as $line) {
	    	if     (strpos($line, 'DB_SERVER_USERNAME')) $db_user   = substr($line, strpos($line,",")+1, strpos($line,")")-strpos($line,",")-1);
	    	elseif (strpos($line, 'DB_SERVER_PASSWORD')) $db_pw     = substr($line, strpos($line,",")+1, strpos($line,")")-strpos($line,",")-1);
	    	elseif (strpos($line, 'DB_SERVER_HOST'))     $db_server = substr($line, strpos($line,",")+1, strpos($line,")")-strpos($line,",")-1);
	  	}
	  	$db_user   = str_replace("'", "", $db_user);
	  	$db_pw     = str_replace("'", "", $db_pw);
	  	$db_server = str_replace("'", "", $db_server);
	  	$del_db = new queryFactory;//@todo pdo
	  	if (!$del_db->connect($db_server, $db_user, $db_pw, $db_name)) throw new \core\classes\userException(SETUP_CO_MGR_CANNOT_CONNECT);
	  	$tables = array();
	  	$table_list = $del_db->query("show tables");
	  	while (!$table_list->EOF) {
	  		$tables[] = array_shift($table_list->fields);
			$table_list->MoveNext();
	    }
	    if (is_array($tables)) foreach ($tables as $table) $del_db->query("drop table " . $table);
	    $backup->delete_dir(DIR_FS_MY_FILES . $db_name);
	    unset($basis->user->companies[$_POST['del_company']]);
	    gen_add_audit_log(sprintf(TEXT_MANAGER_ARGS, TEXT_COMPANY). ' - ' . TEXT_DELETE, $db_name);
	    \core\classes\messageStack::add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_DELETED, TEXT_COMPANY, $_POST['del_company']), 'success');
	}
	$default_tab_id = 'manager';
	break;
  case 'ordr_nums':
  	\core\classes\user::validate_security($security_level, 3);
	// read in the requested status values
	$sequence_array = array();
	foreach ($status_fields as $status_field) {
	  if (db_prepare_input($_POST[$status_field]) <> $status_values->fields[$status_field]) {
	    $sequence_array[$status_field] = db_prepare_input($_POST[$status_field]);
		$status_values->fields[$status_field] = $sequence_array[$status_field];
	  }
	}
	// post them to the current_status table
	if (sizeof($sequence_array) > 0) {
	  $result = db_perform(TABLE_CURRENT_STATUS, $sequence_array, 'update', 'id > 0');
	  \core\classes\messageStack::add(TEXT_SUCCESSFULLY_POSTED_THE_CURRENT_ORDER_NUMBER_CHANGES,'success');
	  gen_add_audit_log(GEN_ADM_TOOLS_AUDIT_LOG_SEQ);
	}
	$default_tab_id = 'tools';
	break;
  case 'clean_security':
	$clean_date = \core\classes\DateTime::db_date_format($_POST['clean_date']);
	if (!$clean_date) break;
	$result = $admin->DataBase->exec("delete from ".TABLE_DATA_SECURITY." where exp_date < '".$clean_date."'");
	\core\classes\messageStack::add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_DELETED, $result->AffectedRows(), TEXT_DATA_SECURITY_RECORDS), 'success');
	break;
  default:
}

/*****************   prepare to display templates  *************************/
// build some general pull down arrays
$sel_yes_no = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_YES),
);
$sel_transport = array(
  array('id' => 'PHP',        'text' => 'PHP'),
  array('id' => 'sendmail',   'text' => 'sendmail'),
  array('id' => 'sendmail-f', 'text' => 'sendmail-f'),
  array('id' => 'smtp',       'text' => 'smtp'),
  array('id' => 'smtpauth',   'text' => 'smtpauth'),
  array('id' => 'Qmail',      'text' => 'Qmail'),
);
$sel_linefeed = array(
  array('id' => 'LF',   'text' => 'LF'),
  array('id' => 'CRLF', 'text' => 'CRLF'),
);
$sel_format = array(
  array('id' => 'TEXT', 'text' => 'TEXT'),
  array('id' => 'HTML', 'text' => 'HTML'),
);
$sel_order_lines = array(
  array('id' => '0', 'text' => TEXT_DOUBLE_MODE),
  array('id' => '1', 'text' => TEXT_SINGLE_LINE_ENTRY),
);
$sel_ie_method = array(
  array('id' => 'l', 'text' => TEXT_LOCAL),
  array('id' => 'd', 'text' => TEXT_DOWNLOAD),
);
$cal_clean = array(
  'name'      => 'cleanDate',
  'form'      => 'admin',
  'fieldname' => 'clean_date',
  'imagename' => 'btn_date_1',
  'default'   => \core\classes\DateTime::createFromFormat(DATE_FORMAT, date('Y-m-d')),
  'params'    => array('align' => 'left'),
);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_GENERAL_SETTINGS);

?>