<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
$menu_id      = isset($_GET['mID']) ? $_GET['mID'] : 'index'; // default to index unless heading is passed
if (isset($_GET['req']) && $_GET['req'] == 'pw_lost_sub') $_REQUEST['action'] = 'pw_lost_sub';
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  	case 'validate':
	  	try{
	  		// Errors will happen here if there was a problem logging in, logout and restart
	 	 	if (!is_object($db)) throw new \ErrorException("Database isn't created", '',E_USER_ERROR);
		    $admin_name     = db_prepare_input($_POST['admin_name']);
		    $admin_pass     = db_prepare_input($_POST['admin_pass']);
		    $admin_company  = db_prepare_input($_POST['company']);
		    $admin_language = db_prepare_input($_POST['language']);
		    $sql = "select admin_id, admin_name, inactive, display_name, admin_email, admin_pass, account_id, admin_prefs, admin_security
				from " . TABLE_USERS . " where admin_name = '" . db_input($admin_name) . "'";
		    if ($db->db_connected) $result = $db->Execute($sql);
			if (!$result || $admin_name <> $result->fields['admin_name'] || $result->fields['inactive']) throw new \Exception(ERROR_WRONG_LOGIN);
		    \core\classes\encryption::validate_password($admin_pass, $result->fields['admin_pass']);
		    $_SESSION['admin_id']       = $result->fields['admin_id'];
		    $_SESSION['display_name']   = $result->fields['display_name'];
		    $_SESSION['admin_email']    = $result->fields['admin_email'];
			$_SESSION['admin_prefs']    = unserialize($result->fields['admin_prefs']);
			$_SESSION['company']        = $admin_company;
		    $_SESSION['language']       = $admin_language;
			$_SESSION['account_id']     = $result->fields['account_id'];
		    $_SESSION['admin_security'] = \core\classes\user::parse_permissions($result->fields['admin_security']);
			// set some cookies for the next visit to remember the company, language, and theme
			$cookie_exp = 2592000 + time(); // one month
			setcookie('pb_company' , $admin_company,  $cookie_exp);
			setcookie('pb_language', $admin_language, $cookie_exp);
			// load init functions for each module and execute
			foreach ($admin_classes as $key => $module_class) {
			  	if ($module_class->should_update()) $module_class->update();
			}
	  		foreach ($admin_classes as $key => $module_class) {
			  	$module_class->initialize();
			}
			if (defined('TABLE_CONTACTS')) {
			    $dept = $db->Execute("select dept_rep_id from " . TABLE_CONTACTS . " where id = " . $result->fields['account_id']);
			    $_SESSION['department'] = $dept->fields['dept_rep_id'];
			}
			gen_add_audit_log(GEN_LOG_LOGIN . $admin_name);
			// check for session timeout to reload to requested page
			$get_params = '';
			if (isset($_SESSION['pb_module']) && $_SESSION['pb_module']) {
				$get_params  = 'module='    . $_SESSION['pb_module'];
			    if (isset($_SESSION['pb_page']) && $_SESSION['pb_page']) $get_params .= '&amp;page=' . $_SESSION['pb_page'];
			    if (isset($_SESSION['pb_jID'])  && $_SESSION['pb_jID'])  $get_params .= '&amp;jID='  . $_SESSION['pb_jID'];
			    if (isset($_SESSION['pb_type']) && $_SESSION['pb_type']) $get_params .= '&amp;type=' . $_SESSION['pb_type'];
			    if (isset($_SESSION['pb_list']) && $_SESSION['pb_list']) $get_params .= '&amp;list=' . $_SESSION['pb_list'];
			    unset($_SESSION['pb_module']);
  				unset($_SESSION['pb_page']);
  				unset($_SESSION['pb_jID']);
  				unset($_SESSION['pb_type']);
  				unset($_SESSION['pb_list']);
			    gen_redirect(html_href_link(FILENAME_DEFAULT, $get_params, 'SSL'));
			}
			// check safe mode is allowed to log in.
			if (get_cfg_var('safe_mode')) throw new \Exception(SAFE_MODE_ERROR);
		    $_REQUEST['action'] = '';
		    break;
		}catch(\Exception $e) {
			$messageStack->add($e->getMessage(), $e->getCode());
			// Note: This is assigned to admin id = 1 since the user is not logged in.
			gen_add_audit_log(sprintf(GEN_LOG_LOGIN_FAILED, $e->getMessage(), $admin_name));
			$_REQUEST['action'] = 'login';
		  	break;
		}
  	case 'pw_lost_sub':
  		try{
	    	$admin_email = db_prepare_input($_POST['admin_email']);
	    	$result = $db->Execute("select admin_id, admin_name, admin_email
			  from " . TABLE_USERS . " where admin_email = '" . db_input($admin_email) . "'");
	    	if (!$admin_email || $admin_email <> $result->fields['admin_email']) throw new \Exception(ERROR_WRONG_EMAIL);
	    	$_SESSION['company'] = $_POST['company'];
	       	$new_password = \core\classes\encryption::random_password(ENTRY_PASSWORD_MIN_LENGTH);
	      	$admin_pass   = \core\classes\encryption::password($new_password);
	      	$db->Execute("update " . TABLE_USERS . " set admin_pass = '" . db_input($admin_pass) . "' where admin_id = " . $result->fields['admin_id']);
	      	$html_msg['EMAIL_CUSTOMERS_NAME'] = $result->fields['admin_name'];
	      	$html_msg['EMAIL_MESSAGE_HTML']   = sprintf(TEXT_EMAIL_MESSAGE, COMPANY_NAME, $new_password);
	      	validate_send_mail($result->fields['admin_name'], $result->fields['admin_email'], TEXT_EMAIL_SUBJECT, $html_msg['EMAIL_MESSAGE_HTML'], COMPANY_NAME, EMAIL_FROM, $html_msg);
	      	$messageStack->add(SUCCESS_PASSWORD_SENT, 'success');
		  	gen_add_audit_log(GEN_LOG_RESEND_PW . $admin_email);
	    }catch(Exception $e) {
			$messageStack->add($e->getMessage());
			// Note: This is assigned to admin id = 1 since the user is not logged in.
			gen_add_audit_log(sprintf(GEN_LOG_LOGIN_FAILED, $e->getMessage(), $admin_name));
			$_REQUEST['action'] = 'login';
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
		foreach ($admin_classes as $module_class) {
	  		foreach ($module_class->dashboards as $dashboard){
		  		if ($dashboard->id == $dashboard_id) {
		  			load_method_language(DIR_FS_MODULES . "{$module_class->id}/dashboards/{$dashboard->id}");
		  			$dashboard->menu_id	= $menu_id;
					$dashboard->params	= array();
					$dashboard->update();
		  		}
  			}
		}
		break;
  	case 'delete':
		$dashboard_id = db_prepare_input($_POST['dashboard_id']);
		//since we don't know where the module is, go find it.
		foreach ($admin_classes as $module_class) {
	  		foreach ($module_class->dashboards as $dashboard){
		  		if ($dashboard->id == $dashboard_id) {
		  			load_method_language(DIR_FS_MODULES . "{$module_class->id}/dashboards/{$dashboard->id}");
		  			$dashboard->menu_id	= $menu_id;
					$dashboard->remove();
		  		}
  			}
		}
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
  		try{
			$filename = 'trace.txt';
			if (!$handle = @fopen(DIR_FS_MY_FILES . $filename, "r")) throw new \Exception(sprintf(ERROR_ACCESSING_FILE, DIR_FS_MY_FILES . $filename));
			$contents = fread($handle, filesize(DIR_FS_MY_FILES . $filename));
			if (!@fclose($handle)) throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, $filename));
			$file_size = strlen($contents);
			header('Content-type: text/html; charset=utf-8');
			header("Content-disposition: attachment; filename=$filename; size=$file_size");
			header('Pragma: cache');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Connection: close');
			header('Expires: ' . date('r', time() + 60 * 60));
			header('Last-Modified: ' . date('r', time()));
			print $contents;
  		}catch(Exception $e){
  			$messageStack->add($e->getMessage(), $e->getCode);
  			gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
  		}
  		exit();
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
  	$single_company  = sizeof($companies) == 1 ? true : false;
  	$languages       = load_language_dropdown();
	$single_language = sizeof($languages) == 1 ? true : false;
	if (isset($_POST['company'])) { // find default company
	  $company_index = $_POST['company'];
	} else {
	  $company_index = defined('DEFAULT_COMPANY') ? DEFAULT_COMPANY : '';
	  if (isset($_COOKIE['pb_company'])) $company_index = $_COOKIE['pb_company'];
	}
	if (isset($_POST['language'])) { // find default language
	  $language_index = $_POST['language'];
	} else {
	  $language_index = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en_us';
	  if (isset($_COOKIE['pb_language'])) $language_index = $_COOKIE['pb_language'];
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
		trigger_error("class queryFactory doesn't exist", E_USER_ERROR);
	}
	$cp_boxes = $db->Execute("select * from ".TABLE_USERS_PROFILES."
	  where user_id = '{$_SESSION['admin_id']}' and menu_id = '$menu_id' order by column_id, row_id");
	$include_template = 'template_main.php';
	define('PAGE_TITLE', COMPANY_NAME.' - '.TITLE);
	break;
}

?>