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
//  Path: /modules/phreedom/pages/main/pre_process.php
//
/**************   Check user security   *****************************/
// Not here because may not be logged in
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
/**************   page specific initialization  *************************/
$error        = false; 
$menu_id      = isset($_GET['mID']) ? $_GET['mID'] : 'index'; // default to index unless heading is passed
if (isset($_GET['req']) && $_GET['req'] == 'pw_lost_sub') $_REQUEST['action'] = 'pw_lost_sub';
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'validate':
    $admin_name     = db_prepare_input($_POST['admin_name']);
    $admin_pass     = db_prepare_input($_POST['admin_pass']);
    $admin_company  = $_SESSION['companies'][$_POST['company']];
    $admin_language = db_prepare_input($_POST['language']);
    $sql = "select admin_id, admin_name, inactive, display_name, admin_email, admin_pass, account_id, admin_prefs, admin_security 
		from " . TABLE_USERS . " where admin_name = '" . db_input($admin_name) . "'";
	$result = false;
    if ($db->db_connected) $result = $db->Execute($sql);
	if (!$result || $admin_name <> $result->fields['admin_name'] || $result->fields['inactive']) {
      $error = true;
      $messageStack->add(ERROR_WRONG_LOGIN, 'error');
    }
    if (!pw_validate_password($admin_pass, $result->fields['admin_pass'])) {
      $error = true;
      $messageStack->add(ERROR_WRONG_LOGIN, 'error');
    }
    if (!$error) {
      $_SESSION['admin_id']       = $result->fields['admin_id'];
      $_SESSION['display_name']   = $result->fields['display_name'];
      $_SESSION['admin_email']    = $result->fields['admin_email'];
	  $_SESSION['admin_prefs']    = unserialize($result->fields['admin_prefs']);
	  $_SESSION['company']        = $admin_company;
      $_SESSION['language']       = $admin_language;
	  $_SESSION['account_id']     = $result->fields['account_id'];
      $_SESSION['admin_security'] = gen_parse_permissions($result->fields['admin_security']);
	  // set some cookies for the next visit to remember the company, language, and theme
	  $cookie_exp = 2592000 + time(); // one month
	  setcookie('pb_company' , $admin_company,  $cookie_exp);
	  setcookie('pb_language', $admin_language, $cookie_exp);
	  // load init functions for each module and execute
	  require(DIR_FS_MODULES . 'phreedom/classes/install.php');
	  $phreedom = new phreedom_admin();
	  if (MODULE_PHREEDOM_STATUS <> MODULE_PHREEDOM_VERSION) $phreedom->update('phreedom');
	  $phreedom->initialize($loaded_modules);
	  if (defined('TABLE_CONTACTS')) {
	    $dept = $db->Execute("select dept_rep_id from " . TABLE_CONTACTS . " where id = " . $result->fields['account_id']);
	    $_SESSION['department'] = $dept->fields['dept_rep_id'];
	  }
	  gen_add_audit_log(GEN_LOG_LOGIN . $admin_name);
	  // check for session timeout to reload to requested page
	  $get_params = '';
	  if (isset($_SESSION['pb_cat']) && $_SESSION['pb_cat']) {
	    $get_params  = 'module='    . $_SESSION['pb_cat'];
	    $get_params .= '&amp;page=' . $_SESSION['pb_module'];
	    if (isset($_SESSION['pb_jID']) && $_SESSION['pb_jID'])  $get_params .= '&amp;jID='  . $_SESSION['pb_jID'];
	    if (isset($_SESSION['pb_type']) && $_SESSION['pb_type']) $get_params .= '&amp;type=' . $_SESSION['pb_type'];
	  }
	  // check safe mode
	  if (get_cfg_var('safe_mode')) $messageStack->add_session(SAFE_MODE_ERROR, 'error');
      gen_redirect(html_href_link(FILENAME_DEFAULT, $get_params, 'SSL'));
    } else {
	  // Note: This is assigned to admin id = 1 since the user is not logged in.
	  gen_add_audit_log(GEN_LOG_LOGIN_FAILED . $admin_name);
	}
	$_REQUEST['action'] = 'login';
	break;
  case 'pw_lost_sub':
    $admin_email = db_prepare_input($_POST['admin_email']);
    $result = $db->Execute("select admin_id, admin_name, admin_email 
	  from " . TABLE_USERS . " where admin_email = '" . db_input($admin_email) . "'");
    if (!$admin_email || $admin_email <> $result->fields['admin_email']) {
      $error = true;
      $messageStack->add(ERROR_WRONG_EMAIL, 'error');
    }
    $_SESSION['company'] = $_SESSION['companies'][$_POST['company']];
    if (!$error) {
      $new_password = pw_create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
      $admin_pass   = pw_encrypt_password($new_password);
      $db->Execute("update " . TABLE_USERS . " set admin_pass = '" . db_input($admin_pass) . "' where admin_id = " . $result->fields['admin_id']);
      $html_msg['EMAIL_CUSTOMERS_NAME'] = $result->fields['admin_name'];
      $html_msg['EMAIL_MESSAGE_HTML']   = sprintf(TEXT_EMAIL_MESSAGE, COMPANY_NAME, $new_password);
      validate_send_mail($result->fields['admin_name'], $result->fields['admin_email'], TEXT_EMAIL_SUBJECT, $html_msg['EMAIL_MESSAGE_HTML'], COMPANY_NAME, EMAIL_FROM, $html_msg);
      $messageStack->add(SUCCESS_PASSWORD_SENT, 'success');
	  gen_add_audit_log(GEN_LOG_RESEND_PW . $admin_email);
    }
    break;
  case 'logout':
	$result = $db->Execute("select admin_name from " . TABLE_USERS . " where admin_id = " . $_SESSION['admin_id']);
	gen_add_audit_log(GEN_LOG_LOGOFF . $result->fields['admin_name']);
	session_destroy();
	gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
	break;
  case 'save':
	$dashboard_id = db_prepare_input($_POST['dashboard_id']);
	// since we don't know where the module is, go find it.
	if (!isset($dirs)) $dirs = scandir(DIR_FS_MODULES);
	$module_id = '';
	foreach ($dirs as $dir) {
	  if (defined('MODULE_'.strtoupper($dir).'_STATUS') && file_exists(DIR_FS_MODULES . "$dir/dashboards/")) {
		$choices = scandir(DIR_FS_MODULES . "$dir/dashboards/");
		foreach ($choices as $name) if ($name == $dashboard_id) { $module_id = $dir; break; }
	  }
	  if ($module_name <> '') break;
	}
    load_method_language(DIR_FS_MODULES . "$module_id/dashboards/$dashboard_id");
	require_once (DIR_FS_MODULES . "$module_id/dashboards/$dashboard_id/$dashboard_id.php");
    $new_box 				= new $dashboard_id;
	$new_box->dashboard_id 	= $dashboard_id;
	$new_box->menu_id      	= $menu_id;
	$new_box->params        = array();
	$new_box->Update();
	break;
  case 'delete':
	$dashboard_id = db_prepare_input($_POST['dashboard_id']);
	$result = $db->Execute("delete from " . TABLE_USERS_PROFILES . " 
	  where user_id=".$_SESSION['admin_id']." and menu_id='$menu_id' and dashboard_id='$dashboard_id'");
	break;
  case 'move_up': 
  case 'move_down':
	$dashboard_id = db_prepare_input($_POST['dashboard_id']);
	$sql = "select column_id, row_id from " . TABLE_USERS_PROFILES . " 
		where user_id=".$_SESSION['admin_id']." and menu_id='$menu_id' and dashboard_id='$dashboard_id'";
	$result         = $db->Execute($sql);
	$current_row    = $result->fields['row_id'];
	$current_column = $result->fields['column_id'];
	$new_row        = ($_REQUEST['action'] == 'move_up') ? ($current_row - 1) : ($current_row + 1);
	$sql = "select max(row_id) as max_row from " . TABLE_USERS_PROFILES . " 
		where user_id=".$_SESSION['admin_id']." and menu_id='$menu_id' and column_id='$current_column'";
	$result         = $db->Execute($sql);
	$max_row        = $result->fields['max_row'];
	if (($new_row >= 1 && $_REQUEST['action'] == 'move_up') || ($new_row <= $max_row && $_REQUEST['action'] == 'move_down')) {
	  $sql = "update " . TABLE_USERS_PROFILES . " set row_id = 0 
		where user_id=".$_SESSION['admin_id']." and menu_id='$menu_id' and column_id=$current_column and row_id='$current_row'";
	  $db->Execute($sql);
	  $sql = "update " . TABLE_USERS_PROFILES . " set row_id=$current_row 
		where user_id=".$_SESSION['admin_id']." and menu_id='$menu_id' and column_id=$current_column and row_id='$new_row'";
	  $db->Execute($sql);
	  $sql = "update " . TABLE_USERS_PROFILES . " set row_id=$new_row   
		where user_id=".$_SESSION['admin_id']." and menu_id='$menu_id' and column_id=$current_column and row_id=0";
	  $db->Execute($sql);
	}
	break;
  case 'move_left':
  case 'move_right':
	$dashboard_id = db_prepare_input($_POST['dashboard_id']);
	$sql = "select column_id, row_id from " . TABLE_USERS_PROFILES . " 
		where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "' and dashboard_id = '" . $dashboard_id . "'";
	$result         = $db->Execute($sql);
	$current_row    = $result->fields['row_id'];
	$current_column = $result->fields['column_id'];
	$new_col = ($_REQUEST['action'] == 'move_left') ? ($current_column - 1) : ($current_column + 1);
	if (($new_col >= 1 && $_REQUEST['action'] == 'move_left') || ($new_col <= MAX_CP_COLUMNS && $_REQUEST['action'] == 'move_right')) {
	  $sql = "select max(row_id) as max_row from " . TABLE_USERS_PROFILES . " 
		where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "' and column_id = '" . $new_col . "'";
	  $result = $db->Execute($sql);
	  $new_max_row = $result->fields['max_row'] + 1;
	  $sql = "update  " . TABLE_USERS_PROFILES . " set column_id = " . $new_col . ", row_id = " . $new_max_row . " 
		where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "' and dashboard_id = '" . $dashboard_id . "'";
	  $db->Execute($sql);
	  $sql = "update  " . TABLE_USERS_PROFILES . " set row_id = row_id - 1 
		where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "' 
		and column_id = " . $current_column . " and row_id >= '" . $current_row . "'";
	  $db->Execute($sql);
	}
	break;
  case 'debug':
	$file_name = 'trace.txt';
	if (!$handle = fopen(DIR_FS_MY_FILES . $file_name, "r")) {
	  $messageStack->add(DEBUG_TRACE_MISSING, 'error');
	  gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
	}
	$contents = fread($handle, filesize(DIR_FS_MY_FILES . $file_name));
	fclose($handle);
	$file_size = strlen($contents);
	header('Content-type: text/html; charset=utf-8');
	header("Content-disposition: attachment; filename=" . $file_name . "; size=" . $file_size);
	header('Pragma: cache');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header('Connection: close');
	header('Expires: ' . date('r', time() + 60 * 60));
	header('Last-Modified: ' . date('r', time()));
	print $contents;
	exit();
  case 'php_info':
  	die(phpinfo());
  default:
}

/*****************   prepare to display templates  *************************/
// prepare to display form
$include_header = true;
$include_footer = true;

switch ($_REQUEST['action']) {
  case 'login':
  case 'pw_lost_sub':
  case 'pw_lost_req':
  	$companies       = load_company_dropdown();
  	$single_company  = sizeof($companies)==1 ? true : false;
  	$languages       = load_language_dropdown();
	$single_language = sizeof($languages)==1 ? true : false;
	if (isset($_POST['company'])) { // find default company
	  $company_index = $_POST['company'];
	} else {
	  $default_company = defined('DEFAULT_COMPANY') ? DEFAULT_COMPANY : '';
	  if (isset($_COOKIE['pb_company'])) $default_company = $_COOKIE['pb_company'];
	  foreach ($_SESSION['companies'] as $key => $value) {
		if ($value == $default_company) $company_index = $key;
	  }
	}
	if (isset($_POST['language'])) { // find default language
	  $language_index = $_POST['language'];
	} else {
	  $default_language = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en_us';
	  if (isset($_COOKIE['pb_language'])) $default_language = $_COOKIE['pb_language'];
	  foreach ($languages as $value) {
		if ($value['id'] == $default_language) $language_index = $value['id'];
	  }
	}
	$include_template = $_REQUEST['action'] == 'pw_lost_req' ? 'template_pw_lost.php' : 'template_login.php';
  	$include_header   = false;
	$include_footer   = false;
	define('PAGE_TITLE', TITLE);
    break;
  case 'crash':
	$include_template = 'template_crash.php';
	define('PAGE_TITLE', TITLE);
	break;
  default: // prepare to display templates
	if (!class_exists('queryFactory')) { // Errors will happen here if there was a problem logging in, logout and restart
	  session_destroy();
	  gen_redirect(html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;action=login', 'SSL'));	
	}
	$cp_boxes = $db->Execute("select * from ".TABLE_USERS_PROFILES." 
		where user_id = '".$_SESSION['admin_id']."' and menu_id = '$menu_id' order by column_id, row_id");
	$include_template = 'template_main.php';
	define('PAGE_TITLE', COMPANY_NAME.' - '.TITLE);
	break;
}

?>