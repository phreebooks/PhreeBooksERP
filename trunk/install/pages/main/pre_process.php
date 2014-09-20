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
//  Path: /install/pages/main/pre_process.php
//
define('DEBUG',true);
session_start();
/**************  include page specific files    *********************/
// calculate server path info
$virtual_path   = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/install/')+1);
$server_path    = $_SERVER['SCRIPT_FILENAME'];
if (empty($server_path)) $server_path = $_SERVER['PATH_TRANSLATED'];
$server_path    = str_replace(array('\\','//'), '/', $server_path);
$dir_root       = substr($server_path, 0, strrpos($server_path, '/install/')+1);
define('DIR_WS_ADMIN', $virtual_path);
define('DIR_WS_ICONS',  DIR_WS_ADMIN . 'themes/default/icons/');
define('DIR_FS_ADMIN', $dir_root);
//echo 'server = '; print_r($_SERVER); echo '<br>';
define('DB_TYPE','mysql');
define('DEFAULT_LANGUAGE','en_us');
define('PATH_TO_MY_FILES','my_files/'); // for now since it is in the release
define('DIR_FS_MODULES','../modules/');
define('DIR_FS_MY_FILES','../' . PATH_TO_MY_FILES);
// Set the default chart to load
$default_chart = DIR_FS_MODULES . 'phreebooks/language/en_us/charts/USA_Retail.xml';

require_once('functions/install.php');
$lang = $_GET['lang'] ? $_GET['lang'] : DEFAULT_LANGUAGE;
$_SESSION['language'] = $lang;
install_pull_language($lang);
install_lang('phreedom',  $lang, 'language'); // install general language file
install_lang('phreeform', $lang, 'language'); // install general language file
require_once('defaults.php');
require_once(DIR_FS_MODULES . 'phreedom/defaults.php');
require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
require_once('../includes/common_functions.php');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
/**************   page specific initialization  *************************/
$error   = false;
$caution = false;
$messageStack = new \core\classes\messageStack();
/***************   act on the action request   *************************/
switch ($_REQUEST['action']) {
	default:
	case 'welcome':
		if (isset($_POST['license_consent']) && $_POST['license_consent'] == 'disagree') {
		header('location: index.php');
	}
	$include_template = 'template_welcome.php';
	define('PAGE_TITLE', TITLE_WELCOME);
	break;
	case 'inspect':
		// check for decline
		if ($_POST['license'] == 'disagree') {
			header('location: http://www.google.com');
		}
		// start the checks for minimum requirements
		//PHP Version Check
		if (version_compare(PHP_VERSION, '5.2.0', '<')) {
	  		$error = $messageStack->add(INSTALL_ERROR_PHP_VERSION, 'error');
		}
		// Check Register Globals
		$register_globals = ini_get("register_globals");
		if ($register_globals <> '' && $register_globals <> '0' && strtoupper($register_globals) <> 'OFF') {
	  		$error = $messageStack->add(INSTALL_ERROR_REGISER_GLOBALS, 'error');
		}
		// SAFE MODE check
		if (ini_get("safe_mode")) {
			$error = $messageStack->add(INSTALL_ERROR_SAFE_MODE, 'error');
		}
		// Support for Sessions check
		if (@!extension_loaded('session')) {
			$error = $messageStack->add(INSTALL_ERROR_SESSION_SUPPORT, 'error');
		}
		//Check for OpenSSL support (only relevant for Apache
		if (@!extension_loaded('openssl')) {
			$caution = $messageStack->add(INSTALL_ERROR_OPENSSL, 'caution');
		}
		//Check for cURL support (ie: for payment/shipping gateways)
		if (@!extension_loaded('curl')) {
			$error = $messageStack->add(INSTALL_ERROR_CURL, 'error');
		}
		//Check for upload support built in to PHP
		if (@!ini_get('file_uploads')) {
			$caution = $messageStack->add(INSTALL_ERROR_UPLOADS, 'caution');
		}
		//Upload TMP dir setting
		if (!ini_get("upload_tmp_dir")) {
			$caution = $messageStack->add(INSTALL_ERROR_UPLOAD_DIR, 'caution');
		}
		//Check for XML Support
		if (!function_exists('xml_parser_create')) {
			$caution = $messageStack->add(INSTALL_ERROR_XML, 'caution');
		}
		//Check for FTP support built in to PHP (for manual sending of configure.php files to server if applicable)
		if (@!extension_loaded('ftp')) {
			$caution = $messageStack->add(INSTALL_ERROR_FTP, 'caution');
		}
		// check for /includes writeable
		if (!is_writable('../includes')) {
			$error = $messageStack->add(INSTALL_ERROR_INCLUDES_DIR, 'error');
		}
		// check for configure.php already exists
		if (file_exists('../includes/configure.php')) {
			$error = $messageStack->add(MSG_ERROR_CONFIGURE_EXISTS, 'error');
		}
		// check for /my_files writeable
		if (!is_writable('../' . PATH_TO_MY_FILES)) {
			$error = $messageStack->add(INSTALL_ERROR_MY_FILES_DIR, 'error');
		}
		if ((!$error && !$caution) || (!$error && isset($_POST['btn_install']))) {
			$include_template = 'template_install.php';
			define('PAGE_TITLE', TITLE_INSTALL);
		} else {
			$include_template = 'template_inspect.php';
			define('PAGE_TITLE', TITLE_INSPECT);
		}
		break;
	case 'install':
		$company_name    = db_prepare_input($_POST['company_name']);
		$company_demo    = db_prepare_input($_POST['company_demo']);
		$user_username   = db_prepare_input($_POST['user_username']);
		$user_password   = db_prepare_input($_POST['user_password']);
		$user_pw_confirm = db_prepare_input($_POST['user_pw_confirm']);
		$user_email      = db_prepare_input($_POST['user_email']);
		$srvr_http       = db_prepare_input($_POST['srvr_http']);
		$use_ssl         = $_POST['use_ssl'] ? 'true' : 'false';
		$srvr_https      = db_prepare_input($_POST['srvr_https']);
		$db_host         = db_prepare_input($_POST['db_host']);
		$db_prefix       = db_prepare_input($_POST['db_prefix']);
		$db_name         = db_prepare_input($_POST['db_name']);
		$db_username     = db_prepare_input($_POST['db_username']);
		$db_password     = db_prepare_input($_POST['db_password']);
		$fy_month        = db_prepare_input($_POST['fy_month']);
		$fy_year         = db_prepare_input($_POST['fy_year']);

		// error check input, user info
		if (strlen($company_name) < 1)  $error = $messageStack->add(ERROR_TEXT_ADMIN_COMPANY_ISEMPTY, 'error');
		if (strlen($user_username) < 1) $error = $messageStack->add(ERROR_TEXT_ADMIN_USERNAME_ISEMPTY, 'error');
		if (strlen($user_email) < 1)    $error = $messageStack->add(ERROR_TEXT_ADMIN_EMAIL_ISEMPTY, 'error');
		if (strlen($user_password) < 1) $error = $messageStack->add(ERROR_TEXT_LOGIN_PASS_ISEMPTY, 'error');
		if ($user_password <> $user_pw_confirm) $error = $messageStack->add(ERROR_TEXT_LOGIN_PASS_NOTEQUAL, 'error');
		// database info
		if (preg_match('/a-z0-9_/i', $db_prefix) > 0) $error = $messageStack->add(ERROR_TEXT_DB_PREFIX_NODOTS, 'error');
		if (strlen($db_host)     < 1) $error = $messageStack->add(ERROR_TEXT_DB_HOST_ISEMPTY,     'error');
		if (strlen($db_name)     < 1) $error = $messageStack->add(ERROR_TEXT_DB_NAME_ISEMPTY,     'error');
		if (strlen($db_username) < 1) $error = $messageStack->add(ERROR_TEXT_DB_USERNAME_ISEMPTY, 'error');
		if (strlen($db_password) < 1) $error = $messageStack->add(ERROR_TEXT_DB_PASSWORD_ISEMPTY, 'error');

		// define some things so the install can use existing functions
		define('DB_PREFIX', $db_prefix);
		$_SESSION['company']  = $db_name;
		$_SESSION['language'] = $lang;
		// create the company directory
		if (DEBUG) $messageStack->debug("\n  creating the company directory");
		validate_path(DIR_FS_ADMIN . PATH_TO_MY_FILES . $db_name);
		if (!$error) { // write the db config.php in the company directory
		  if (!install_build_co_config_file($db_name, $db_name . '_TITLE',  $company_name)) $error = true;
		  if (!install_build_co_config_file($db_name, 'DB_SERVER_USERNAME', $db_username))  $error = true;
		  if (!install_build_co_config_file($db_name, 'DB_SERVER_PASSWORD', $db_password))  $error = true;
		  if (!install_build_co_config_file($db_name, 'DB_SERVER_HOST',     $db_host))      $error = true;
		}
		if (!$error) { // try to connect to db
		  require('../includes/db/' . DB_TYPE . '/query_factory.php');
		  $db = new queryFactory();//@todo pdo
		  if (!$admin->DataBase->connect($db_host, $db_username, $db_password, $db_name)) {
		  	$error = $messageStack->add(MSG_ERROR_CANNOT_CONNECT_DB . $admin->DataBase->show_error(), 'error');
		  } else { // test for InnoDB support
		  	$result = $admin->DataBase->Execute("show engines");
		  	$innoDB_enabled = false;
		  	while (!$result->EOF) {
		  		if ($result->fields['Engine'] == 'InnoDB') $innoDB_enabled = true;
		  		$result->MoveNext();
		  	}
		  	if (!$innoDB_enabled) $error = $messageStack->add(MSG_ERROR_INNODB_NOT_ENABLED, 'error');
		  }
		}
		if (!$error) {
	  		$params   = array();
	  		$contents = @scandir(DIR_FS_MODULES);
	  		if($contents === false) throw new \core\classes\userException("couldn't read or find directory ". DIR_FS_MODULES);
			// fake the install status of all modules found to 1, so all gets installed
	  		foreach ($contents as $entry) define('MODULE_' . strtoupper($entry) . '_STATUS','1');
	  		require_once (DIR_FS_MODULES . 'phreedom/config.php'); // needed here to avoid breaking menu array
	  		foreach ($contents as $entry) {
	  			// load the configuration files to load version info
	  			if ($entry <> 'phreedom' && $entry <> '.' && $entry <> '..' && is_dir(DIR_FS_MODULES . $entry)) {
	  				if (file_exists(DIR_FS_MODULES . $entry . '/config.php')) {
	  					install_lang($entry, $lang, 'menu');
	  					install_lang($entry, $lang, 'admin');
	  					require_once (DIR_FS_MODULES . $entry . '/config.php');
	  				}
	  			}
	  		}
	  	}
		// install core modules first
	  	foreach ($admin->classes as $module_class) {
	  		if ($module_class->core) {
	  			if (DEBUG) $messageStack->debug("\n  installing core module = " . $module_class->id);
	  			$module_class->install(DIR_FS_MY_FILES.$_SESSION['company'].'/', $company_demo);
	  		}
	  	}
		// load phreedom reports now since table exists
	  	if (DEBUG) $messageStack->debug("\n  installing phreedom.");
		foreach ($admin->classes as $module_class) {
	  		if (!$module_class->core) {
	  			if (DEBUG) $messageStack->debug("\n  installing core module = {$module_class->id}");
	  			$module_class->install(DIR_FS_MY_FILES.$_SESSION['company'].'/', $company_demo);
	  		}
	  	}
		if (!$error) { // input admin username record, clear the tables first
			if (DEBUG) $messageStack->debug("\n  installing users");
		  	$admin->DataBase->Execute("TRUNCATE TABLE " . TABLE_USERS);
		  	$admin->DataBase->Execute("TRUNCATE TABLE " . TABLE_USERS_PROFILES);
		  	$security = load_full_access_security();
		  	$admin->DataBase->Execute($sql = "insert into " . TABLE_USERS . " set
		      admin_name  = '" . $user_username . "',
			  admin_email = '" . $user_email . "',
		  	  admin_pass  = '" . \core\classes\encryption::password($user_password) . "',
			  admin_security = '" . $security . "'");
		  	$user_id = $admin->DataBase->insert_ID();
		  	if (sizeof($params) > 0) {
		  		// create My Notes dashboard entries
		  		$admin->DataBase->Execute("insert into " . TABLE_USERS_PROFILES . " set user_id = " . $user_id . ",
				  menu_id = 'index', module_id = 'phreedom', dashboard_id = 'to_do', column_id = 1, row_id = 1,
			  	  params = '" . serialize($params) . "'");
		  	}
		}
		if (!$error) { // install fiscal year, default chart of accounts
			if (DEBUG) $messageStack->debug("\n  installing fiscal year.");
		  	require_once('../modules/phreebooks/functions/phreebooks.php');
		  	$admin->DataBase->Execute("TRUNCATE TABLE " . TABLE_ACCOUNTING_PERIODS);
		  	$current_year = date('Y');
		  	$start_year   = $fy_year;
		  	$start_period = 1;
		  	$runaway = 0;
		  	while ($start_year <= $current_year) {
		  		validate_fiscal_year($start_year, $start_period, $start_year.'-'.$fy_month.'-01');
		  		$start_year++;
		  		$start_period = $start_period + 12;
		  		$runaway++;
		  		if ($runaway > 10) break;
		  	}
		  	if (DEBUG) $messageStack->debug("\n  loading chart of accounts");
			// load the retail chart as default if the chart of accounts table is empty
		  	$result = $admin->DataBase->Execute("select id from " . TABLE_JOURNAL_MAIN . " limit 1");
		  	$entries_exist = $result->RecordCount() > 0 ? true : false;
		  	$result = $admin->DataBase->Execute("select id from " . TABLE_CHART_OF_ACCOUNTS . " limit 1");
		  	$chart_exists = $result->RecordCount() > 0 ? true : false;
		  	if (!$entries_exist && !$chart_exists) {
		  		if (($temp = @file_get_contents($default_chart)) === false) throw new \core\classes\userException(sprintf(ERROR_READ_FILE, $default_chart));
		  		$accounts = xml_to_object($temp);
		  		if (is_object($accounts->ChartofAccounts)) $accounts = $accounts->ChartofAccounts; // just pull the first one
		  		if (is_object($accounts->account)) $accounts->account = array($accounts->account); // in case of only one chart entry
		  		if (is_array($accounts->account)) foreach ($accounts->account as $account) {
		  			$sql_data_array = array(
			    		'id'              => $account->id,
			    		'description'     => $account->description,
			    		'heading_only'    => $account->heading,
			    		'primary_acct_id' => $account->primary,
			    		'account_type'    => $account->type,
		  			);
		  			db_perform(TABLE_CHART_OF_ACCOUNTS, $sql_data_array, 'insert');
		  		}
		  	}
		  	if (DEBUG) $messageStack->debug("\n  building and checking chart history");
		  	build_and_check_account_history_records();
		  	if (DEBUG) $messageStack->debug("\n  updating current period");
		  	gen_auto_update_period(false);
		}
		if (!$error) { // write the includes/configure.php file
		  if (DEBUG) $messageStack->debug("\n  writing configure.php file");
		  $config_contents = str_replace('DEFAULT_HTTP_SERVER',      $srvr_http,   $config_contents);
		  $config_contents = str_replace('DEFAULT_HTTPS_SERVER',     $srvr_https,  $config_contents);
		  $config_contents = str_replace('DEFAULT_ENABLE_SSL_ADMIN', $use_ssl,     $config_contents);
		  $config_contents = str_replace('DEFAULT_DIR_WS_ADMIN',     DIR_WS_ADMIN, $config_contents);
		  $config_contents = str_replace('DEFAULT_DIR_FS_ADMIN',     DIR_FS_ADMIN, $config_contents);
		  $config_contents = str_replace('DEFAULT_DEFAULT_LANGUAGE', $lang,        $config_contents);
		  $config_contents = str_replace('DEFAULT_DB_TYPE',          DB_TYPE,      $config_contents);
		  $config_contents = str_replace('DEFAULT_DB_PREFIX',        DB_PREFIX,    $config_contents);
		  if (file_exists('../includes/configure.php'))				throw new \core\classes\userException(MSG_ERROR_CONFIGURE_EXISTS);
		  if (!$handle = @fopen('../includes/configure.php', 'w'))	throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, 'includes/configure.php'));
		  if (!@fwrite($handle, $config_contents))					throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, 'includes/configure.php'));
		  if (!@fclose($handle))									throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, 'includes/configure.php'));
		  if (!@chmod('../includes/configure.php', 0444))			$messageStack->add("Was not able to mark configure file as read only", "caution");
		}
		if (!$error) { // set the session variables so they can log in
		  $_SESSION['admin_id']       = $user_id;
		  $_SESSION['admin_prefs']    = '';
		  $_SESSION['language']       = $lang;
		  $_SESSION['account_id']     = '';
		  $_SESSION['admin_security'] = \core\classes\user::parse_permissions($security);
		  $include_template = 'template_finish.php';
		  define('PAGE_TITLE', TITLE_FINISH);
		  if (DEBUG) $messageStack->write_debug();
		} else {
		  $include_template = 'template_install.php';
		  define('PAGE_TITLE', TITLE_INSTALL);
		}
		break;
	case 'finish':
		$include_template = 'template_finish.php';
		define('PAGE_TITLE', INSTALL_TITLE_FINISH);
		break;
	case 'open_company':
		require('../includes/configure.php');
		$path = (ENABLE_SSL_ADMIN == 'true' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_ADMIN;
		define('DIR_WS_FULL_PATH', $path);	// full http path (or https if secure)
		gen_redirect(html_href_link('index.php', '', 'SSL'));
		break;
}

/*****************   prepare to display templates  *************************/
$sel_yes_no = array(
	array('id' => '0', 'text' => TEXT_NO),
	array('id' => '1', 'text' => TEXT_YES),
);

$sel_fy_month = array(
	array('id' => '01', 'text'=> TEXT_JAN),
	array('id' => '02', 'text'=> TEXT_FEB),
	array('id' => '03', 'text'=> TEXT_MAR),
	array('id' => '04', 'text'=> TEXT_APR),
	array('id' => '05', 'text'=> TEXT_MAY),
	array('id' => '06', 'text'=> TEXT_JUN),
	array('id' => '07', 'text'=> TEXT_JUL),
	array('id' => '08', 'text'=> TEXT_AUG),
	array('id' => '09', 'text'=> TEXT_SEP),
	array('id' => '10', 'text'=> TEXT_OCT),
	array('id' => '11', 'text'=> TEXT_NOV),
	array('id' => '12', 'text'=> TEXT_DEC),
);

$sel_fy_year = array();
for ($i = 0; $i < 6; $i++) $sel_fy_year[] = array('id' => date('Y')+$i-5, 'text' => date('Y')+$i-5);
// Determine http path
$srvr_http  = 'http://'  . $_SERVER['HTTP_HOST'];
$srvr_https = 'https://' . $_SERVER['HTTP_HOST'];
// find the license
if (file_exists('../modules/phreedom/language/' . $lang . '/manual/ch01-Introduction/license.html')) {
	$license_path = '../modules/phreedom/language/' . $lang . '/manual/ch01-Introduction/license.html';
} else {
	$license_path = '../modules/phreedom/language/en_us/manual/ch01-Introduction/license.html';
}
?>
