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
//  Path: /modules/phreedom/pages/admin/pre_process.php
//
ini_set('memory_limit','256M');  // Set this big for memory exhausted errors
$security_level = validate_user(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreeform');
if (defined('MODULE_PHREEFORM_STATUS')) { 
  require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
  require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');
}
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
require_once(DIR_FS_WORKING . 'classes/backup.php');
require_once(DIR_FS_WORKING . 'classes/install.php');
require_once(DIR_FS_WORKING . 'classes/currency.php');
/**************   page specific initialization  *************************/
$error  = false; 
$core_modules = array('phreedom','phreeform','phreebooks','contacts','inventory','phreehelp','payment'); // phreeform first!
// see if installing or removing a module
if (substr($_REQUEST['action'], 0, 8) == 'install_') {
  $method = substr($_REQUEST['action'], 8);
  $_REQUEST['action'] = 'install';
} elseif (substr($_REQUEST['action'], 0, 7) == 'remove_') {
  $method = substr($_REQUEST['action'], 7);
  $_REQUEST['action'] = 'remove';
}
$install   = new phreedom_admin();
$currency  = new currency();
// load other module admin information
$page_list = array();
if ($dir = @dir(DIR_FS_MODULES)) {
  while ($file = $dir->read()) {
    if (is_dir(DIR_FS_MODULES . $file) && $file <> '.' && $file <> '..') {
	  if (file_exists(DIR_FS_MODULES . $file . '/config.php') && $file <> 'phreedom') {
		gen_pull_language($file, 'admin');
		$page_list[$file] = constant('MODULE_' . strtoupper($file) . '_TITLE');
	  }
    }
  }
  $dir->close();
}
asort($page_list);
// load the current statuses
$status_fields = array();
$result = $db->Execute("show fields from " . TABLE_CURRENT_STATUS);
while (!$result->EOF) {
  if ($result->fields['Field'] <> 'id') $status_fields[] = $result->fields['Field']; 
  $result->MoveNext();
}
$status_values = $db->Execute("select * from " . TABLE_CURRENT_STATUS);
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'install':
  case 'update':
  	validate_security($security_level, 4);
	// load the module installation class
	if (!file_exists(DIR_FS_MODULES . $method . '/classes/install.php')) {
	  $messageStack->add(sprintf('Looking for the installation script for module %s, but could not locate it. The module cannot be installed!', $method),'error');
	  break;
	}
	gen_pull_language($method, 'admin');
	gen_pull_language($method);
	require_once(DIR_FS_MODULES . $method . '/config.php'); // config is not loaded yet since module is not installed.
	require_once(DIR_FS_MODULES . $method . '/classes/install.php');
	$cName = $method . '_admin';
	$mInstall = new $cName();
	if ($_REQUEST['action'] == 'install') {
	  if (admin_check_versions($method, $mInstall->prerequisites)) { // Check for version levels
	    $error = true;
	  } elseif (admin_install_dirs($mInstall->dirlist, DIR_FS_MY_FILES.$_SESSION['company'].'/')) {
	    $error = true;
	  } elseif (admin_install_tables($mInstall->tables)) { // Create the tables
	    $error = true; 
	  } else {
	    // Load the installed module version into db
	    write_configure('MODULE_' . strtoupper($method) . '_STATUS', constant('MODULE_' . strtoupper($method) . '_VERSION'));
	    // Load the remaining configuration constants
	    foreach ($mInstall->keys as $key => $value) write_configure($key, $value);
	    if ($demo) $error = $mInstall->load_demo(); // load demo data
		$mInstall->load_reports($method);
		admin_add_reports($method);
	  }
	  if ($mInstall->install($method)) $error = true; // install any special stuff
	} else {
	  if ($mInstall->update($method)) $error = true;
	}
	if ($error) break; 
	if (sizeof($mInstall->notes) > 0) foreach ($mInstall->notes as $note) $messageStack->add($note, 'caution');
	gen_add_audit_log(sprintf(GEN_LOG_INSTALL_SUCCESS, $method) . (($_REQUEST['action'] == 'install') ? TEXT_INSTALL : TEXT_UPDATE), constant('MODULE_' . strtoupper($method) . '_VERSION'));
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'remove':
  	validate_security($security_level, 4);
	// load the module installation class
	if (!file_exists(DIR_FS_MODULES . $method . '/classes/install.php')) {
	  $messageStack->add(sprintf('Looking for the installation script for module %s, but could not locate it. The module cannot be installed!', $method),'error');
	  break;
	}
	require_once(DIR_FS_MODULES . $method . '/classes/install.php');
	$cName = $method . '_admin';
	$mInstall = new $cName();
	foreach ($mInstall->keys as $key => $value) remove_configure($key);
	remove_configure('MODULE_' . strtoupper($method) . '_STATUS');
	if (admin_remove_tables(array_keys($mInstall->tables))) $error = true;
	if (admin_remove_dirs($mInstall->dirlist, DIR_FS_MY_FILES.$_SESSION['company'].'/')) $error = true;
	if ($mInstall->remove($method)) $error = true;
	gen_add_audit_log(sprintf(AUDIT_LOG_REMOVE_SUCCESS, $method));
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'save':
  	validate_security($security_level, 3);
	// save general tab
	foreach ($install->keys as $key => $default) {
	  $field = strtolower($key);
      if (isset($_POST[$field])) write_configure($key, db_prepare_input($_POST[$field]));
	  // special case for field COMPANY_NAME to update company config file
	  if ($key == 'COMPANY_NAME' && $_POST[$field] <> COMPANY_NAME) {
	    if (!install_build_co_config_file($_SESSION['company'], $_SESSION['company'] . '_TITLE', db_prepare_input($_POST[$field]))) $error = true;
	  }
    }
	$messageStack->add(GENERAL_CONFIG_SAVED,'success');
//	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	$default_tab_id = 'company';
    break;
  case 'delete':
  	validate_security($security_level, 4);
  	$subject = $_POST['subject'];
    $id      = $_POST['rowSeq'];
	if (!$subject || !$id) break;
    if ($$subject->btn_delete($id)) $close_popup = true;
	break;
  case 'copy_co':
	$db_server = db_prepare_input($_POST['db_server']);
	$db_name   = db_prepare_input($_POST['db_name']);
	$db_user   = db_prepare_input($_POST['db_user']);
	$db_pw     = db_prepare_input($_POST['db_pw']);
	$co_name   = db_prepare_input($_POST['co_name']);
	// error check company name and company full name
	if (!$db_name || !$co_name)           $error = $messageStack->add(SETUP_CO_MGR_ERROR_EMPTY_FIELD,'error');
	if ($db_name == $_SESSION['company']) $error = $messageStack->add(SETUP_CO_MGR_DUP_DB_NAME,'error');
	// check for database already exists
	$db_orig = new queryFactory;
	if (!$db_orig->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE)) {
	  die('Problem connecting to original DB!');
	}
	$db = new queryFactory;
	if (!$db->connect($db_server, $db_user, $db_pw, $db_name)) $error = $messageStack->add(SETUP_CO_MGR_CANNOT_CONNECT,'error');

	if (!$error) { // write the db config.php in the company directory
	  if (!file_exists(DIR_FS_ADMIN . PATH_TO_MY_FILES . $db_name)) {
	    if (!@mkdir   (DIR_FS_ADMIN . PATH_TO_MY_FILES . $db_name)) $error = $messageStack->add(sprintf(MSG_ERROR_CREATE_MY_FILES, DIR_FS_ADMIN . PATH_TO_MY_FILES),'error');
	  }
	  if (!install_build_co_config_file($db_name, $db_name . '_TITLE',  $co_name))   $error = true;
	  if (!install_build_co_config_file($db_name, 'DB_SERVER_USERNAME', $db_user))   $error = true;
	  if (!install_build_co_config_file($db_name, 'DB_SERVER_PASSWORD', $db_pw))     $error = true;
	  if (!install_build_co_config_file($db_name, 'DB_SERVER_HOST',     $db_server)) $error = true;
	}

	$copy_modules = $core_modules;
	foreach ($loaded_modules as $entry) if (isset($_POST[$entry])) $copy_modules[] = $entry;
	$backup = new backup;
    $backup->source_dir  = DIR_FS_MY_FILES . $db_name . '/temp/';
    $backup->source_file = 'temp.sql';
	if (!$error) {
	  foreach ($copy_modules as $entry) gen_pull_language($entry, 'admin');
	  foreach ($copy_modules as $entry) {
		require_once (DIR_FS_MODULES . $entry . '/classes/install.php');
		$classname   = $entry . '_admin';
		$install_mod = new $classname;
	    $task        = $_POST[$entry . '_action']; 
		if ($entry == 'phreedom') $task = 'data'; // force complete copy of phreedom module
	    switch ($task) {
		  case 'core':
		  case 'demo':
			if (admin_install_dirs($install_mod->dirlist, DIR_FS_MY_FILES . $db_name . '/')) {
			  $error = true;
			} elseif (admin_install_tables($install_mod->tables)) { // Create the tables
			  $error = true; 
			} else {
			  // Load the installed module version into db
			  write_configure('MODULE_' . strtoupper($entry) . '_STATUS', constant('MODULE_' . strtoupper($entry) . '_VERSION'));
			  // Load the remaining configuration constants
			  foreach ($install_mod->keys as $key => $value) write_configure($key, $value);
			  if ($task == 'demo') if ($install_mod->load_demo()) $error = true; // load demo data
			  if ($entry <> 'phreedom') $install_mod->load_reports($entry);
			  if ($entry == 'phreeform') {
			  	$temp_mod = new phreedom_admin;
	  			$temp_mod->load_reports('phreedom');
			  }
			}
			if ($install_mod->install($entry)) $error = true; // install any special stuff
			if ($error) $messageStack->add(sprintf(MSG_ERROR_MODULE_INSTALL, $entry), 'error');
		    break;
		  default:
		  case 'data':
			$table_list = array();
		    if (is_array($install_mod->tables)) {
			  foreach ($install_mod->tables as $table => $create_sql) $table_list[] = $table;
			  $backup->copy_db_table($db_orig, $table_list, $type = 'both', $params = '');
	  		}
			if (is_array($install_mod->dirlist)) foreach($install_mod->dirlist as $source_dir) {
		      $dir_source = DIR_FS_MY_FILES . $_SESSION['company'] . '/' . $source_dir . '/';
		      $dir_dest   = DIR_FS_MY_FILES . $db_name             . '/' . $source_dir . '/';
			  @mkdir(DIR_FS_MY_FILES . $db_name . '/' . $source_dir);
			  $backup->copy_dir($dir_source, $dir_dest);
		    }
			break;
		  default: // skip, should not happen
	    }
	  }
	  // install reports now that categories are set up
	  if ($_POST['phreeform_action'] <> 'data') { // if=data reports have been copied, else load basic reports
	    foreach ($copy_modules as $entry) admin_add_reports($entry, DIR_FS_MY_FILES . $db_name . '/phreeform/');
	  }
	}
	if (!$error && $_POST['phreebooks_action'] <> 'data') { // install fiscal year if the phreebooks data is not copied
	  $db->Execute("TRUNCATE TABLE " . TABLE_ACCOUNTING_PERIODS);
	  require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
	  $dates = gen_get_dates();
	  validate_fiscal_year($dates['ThisYear'], '1', $dates['ThisYear'] . '-' . $dates['ThisMonth'] . '-01');
	  build_and_check_account_history_records();
	  gen_auto_update_period(false);
	}
	if (!$error) { // reset SESSION['company'] to new company and redirect to install->store_setup
	  $db->Execute("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $co_name . "' 
	    where configuration_key = 'COMPANY_NAME'");
	  $messageStack->add(SETUP_CO_MGR_CREATE_SUCCESS,'success');
	  gen_add_audit_log(SETUP_CO_MGR_LOG . TEXT_COPY, $db_name);
	  $_SESSION['db_server'] = $db_server;
	  $_SESSION['company']   = $db_name;
	  $_SESSION['db_user']   = $db_user;
	  $_SESSION['db_pw']     = $db_pw;
      gen_redirect(html_href_link(FILENAME_DEFAULT, $get_parmas, ENABLE_SSL_ADMIN ? 'SSL' : 'NONSSL'));
	} else { // restore db connection
	  $db = new queryFactory;
	  $db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
	$default_tab_id = 'manager';
	break;
  case 'delete_co':
	$db_name = $_SESSION['companies'][$_POST['del_company']];
	// Failsafe to prevent current company from being deleted accidently
	$backup = new backup;
	if ($db_name == 'none') {
	  $error = $messageStack->add(SETUP_CO_MGR_NO_SELECTION,'error');
	}
	if (!$error && $db_name <> $_SESSION['company']) {
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
	  $del_db = new queryFactory;
	  if (!$del_db->connect($db_server, $db_user, $db_pw, $db_name)) $error = $messageStack->add(SETUP_CO_MGR_CANNOT_CONNECT,'error');
	  if (!$error) {
	    $tables = array();
	    $table_list = $del_db->Execute("show tables");
	    while (!$table_list->EOF) {
		  $tables[] = array_shift($table_list->fields);
		  $table_list->MoveNext();
	    }
	    if (is_array($tables)) foreach ($tables as $table) $del_db->Execute("drop table " . $table);
	    $backup->delete_dir(DIR_FS_MY_FILES . $db_name);
	    unset($_SESSION['companies'][$_POST['del_company']]);
	    gen_add_audit_log(SETUP_CO_MGR_LOG . TEXT_DELETE, $db_name);
	    $messageStack->add(SETUP_CO_MGR_DELETE_SUCCESS, 'success');
	  }
	}
	$default_tab_id = 'manager';
	break;
  case 'ordr_nums':
  	validate_security($security_level, 3);
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
	  $messageStack->add(GEN_ADM_TOOLS_POST_SEQ_SUCCESS,'success');
	  gen_add_audit_log(GEN_ADM_TOOLS_AUDIT_LOG_SEQ);
	}
	$default_tab_id = 'tools';
	break;
  case 'clean_security':
	$clean_date = gen_db_date($_POST['clean_date']);
	if (!$clean_date) break;
	$result = $db->Execute("delete from ".TABLE_DATA_SECURITY." where exp_date < '".$clean_date."'");
	$messageStack->add(sprintf(TEXT_CLEAN_SECURITY_SUCCESS, $result->AffectedRows()), 'success');
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
  array('id' => '1', 'text' => TEXT_SINGLE_MODE),
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
  'default'   => gen_locale_date(date('Y-m-d')),
  'params'    => array('align' => 'left'),
);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_GENERAL_ADMIN);

?>