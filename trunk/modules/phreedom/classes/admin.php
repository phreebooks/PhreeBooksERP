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
//  Path: /modules/phreedom/classes/install.php
//
namespace phreedom\classes;
class admin extends \core\classes\admin {
	public $sort_order  = 1;
	public $id 			= 'phreedom';
	public $text;
	public $description;
	public $version		= '3.6';
	public $installed	= true;

  function __construct() {
	// Load configuration constants for this module, must match entries in admin tabs
    $this->keys = array(
	  'COMPANY_ID'                 => 'HQ',
	  'COMPANY_NAME'               => 'My Company',
	  'AR_CONTACT_NAME'            => 'AR Contact',
	  'AP_CONTACT_NAME'            => 'AP Contact',
	  'COMPANY_ADDRESS1'           => '100 Main St.',
	  'COMPANY_ADDRESS2'           => '',
	  'COMPANY_CITY_TOWN'          => 'Anytown',
	  'COMPANY_ZONE'               => 'CA',
	  'COMPANY_POSTAL_CODE'        => '90001',
	  'COMPANY_COUNTRY'            => 'USA',
	  'COMPANY_TELEPHONE1'         => '',
	  'COMPANY_TELEPHONE2'         => '',
	  'COMPANY_FAX'                => '',
	  'COMPANY_EMAIL'              => 'webmaster@mycompany.com',
	  'COMPANY_WEBSITE'            => '',
	  'TAX_ID'                     => '',
	  'ENABLE_MULTI_BRANCH'        => '0',
	  'ENABLE_MULTI_CURRENCY'      => '0',
	  'ENABLE_ENCRYPTION'          => '0',
	  'ENTRY_PASSWORD_MIN_LENGTH'  => '5',
	  'MAX_DISPLAY_SEARCH_RESULTS' => '20',
	  'CFG_AUTO_UPDATE_CHECK'      => '0',
	  'HIDE_SUCCESS_MESSAGES'      => '0',
	  'AUTO_UPDATE_CURRENCY'       => '1',
	  'LIMIT_HISTORY_RESULTS'      => '20',
	  'SESSION_TIMEOUT_ADMIN'      => '3600',
	  'SESSION_AUTO_REFRESH'       => '0',
	  'DEBUG'                      => '0',
	  'IE_RW_EXPORT_PREFERENCE'    => 'Download',
	  'EMAIL_TRANSPORT'            => 'smtp',
	  'EMAIL_LINEFEED'             => 'LF',
	  'EMAIL_USE_HTML'             => '0',
	  'STORE_OWNER_EMAIL_ADDRESS'  => '',
	  'EMAIL_FROM'                 => '',
	  'ADMIN_EXTRA_EMAIL_FORMAT'   => 'TEXT',
	  'EMAIL_SMTPAUTH_MAILBOX'     => '',
	  'EMAIL_SMTPAUTH_PASSWORD'    => '',
	  'EMAIL_SMTPAUTH_MAIL_SERVER' => '',
	  'EMAIL_SMTPAUTH_MAIL_SERVER_PORT' => '25',
	  'CURRENCIES_TRANSLATIONS'    => '&pound;,?:&euro;,?',
      'DATE_FORMAT'                => 'm/d/Y', // this is used for date(), use only values: Y, m and d (case sensitive)
      'DATE_DELIMITER'             => '/', // must match delimiter used in DATE_FORMAT
      'DATE_TIME_FORMAT'           => 'm/d/Y h:i:s a',
	);
	// add new directories to store images and data
	$this->dirlist = array(
	  '../backups', // goes in root my_files directory
	  'images',
	  'temp',
	);
	// Load tables
	$this->tables = array(
	  TABLE_AUDIT_LOG => "CREATE TABLE " . TABLE_AUDIT_LOG . " (
		  id int(15) NOT NULL auto_increment,
		  action_date timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  user_id int(11) NOT NULL default '0',
		  ip_address varchar(15) NOT NULL default '0.0.0.0',
		  stats varchar(32) NOT NULL,
		  reference_id varchar(32) NOT NULL default '',
		  action varchar(64) default NULL,
		  amount float(10,2) NOT NULL default '0.00',
		  PRIMARY KEY (id),
		  KEY idx_page_accessed_zen (reference_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;",
	  TABLE_CONFIGURATION => "CREATE TABLE " . TABLE_CONFIGURATION . " (
		  configuration_key varchar(64) NOT NULL default '',
		  configuration_value text,
		  PRIMARY KEY (configuration_key)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_CURRENCIES => "CREATE TABLE " . TABLE_CURRENCIES . " (
		  currencies_id int(11) NOT NULL auto_increment,
		  title varchar(32) NOT NULL default '',
		  code char(3) NOT NULL default '',
		  symbol_left varchar(24) default NULL,
		  symbol_right varchar(24) default NULL,
		  decimal_point char(1) default NULL,
		  thousands_point char(1) default NULL,
		  decimal_places char(1) default NULL,
		  decimal_precise char(1) NOT NULL default '2',
		  value float(13,8) default NULL,
		  last_updated datetime default NULL,
		  PRIMARY KEY  (currencies_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_CURRENT_STATUS => "CREATE TABLE " . TABLE_CURRENT_STATUS . " (
		  id int(11) NOT NULL auto_increment,
		  PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_DATA_SECURITY => "CREATE TABLE " . TABLE_DATA_SECURITY . " (
		  id int(11) NOT NULL auto_increment,
		  module varchar(32) NOT NULL DEFAULT '',
		  ref_1 int(11) NOT NULL DEFAULT '0',
		  ref_2 int(11) NOT NULL DEFAULT '0',
		  hint varchar(255) NOT NULL DEFAULT '',
		  enc_value varchar(255) NOT NULL DEFAULT '',
		  exp_date date NOT NULL DEFAULT '2049-12-31',
		  PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_EXTRA_FIELDS => "CREATE TABLE " . TABLE_EXTRA_FIELDS . " (
		  id int(10) NOT NULL auto_increment,
		  module_id varchar(32) NOT NULL default '',
		  tab_id int(11) NOT NULL default '0',
		  entry_type varchar(20) NOT NULL default '',
		  field_name varchar(32) NOT NULL default '',
		  description varchar(64) NOT NULL default '',
		  sort_order varchar(64) NOT NULL default '',
		  group_by varchar(64) NOT NULL default '',
		  use_in_inventory_filter enum('0','1') NOT NULL DEFAULT '0',
	  	  required enum('0','1') NOT NULL DEFAULT '0',
		  params text,
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_EXTRA_TABS => "CREATE TABLE " . TABLE_EXTRA_TABS . " (
		  id int(3) NOT NULL auto_increment,
		  module_id varchar(32) NOT NULL default '',
		  tab_name varchar(32) NOT NULL default '',
		  description varchar(80) NOT NULL default '',
		  sort_order int(2) NOT NULL default '0',
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_USERS => "CREATE TABLE " . TABLE_USERS . " (
		  admin_id int(11) NOT NULL auto_increment,
		  is_role enum('0','1') NOT NULL default '0',
		  admin_name varchar(32) NOT NULL default '',
		  inactive enum('0','1') NOT NULL default '0',
		  display_name varchar(32) NOT NULL default '',
		  admin_email varchar(96) NOT NULL default '',
		  admin_pass varchar(40) NOT NULL default '',
		  account_id int(11) NOT NULL default '0',
		  admin_store_id int(11) NOT NULL default '0',
		  admin_prefs text,
		  admin_security text,
		  PRIMARY KEY (admin_id),
		  KEY idx_admin_name_zen (admin_name)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;",
	  TABLE_USERS_PROFILES => "CREATE TABLE " . TABLE_USERS_PROFILES . " (
		  id int(11) NOT NULL auto_increment,
		  user_id int(11) NOT NULL default '0',
		  menu_id varchar(32) NOT NULL default '',
		  module_id varchar(32) NOT NULL default '',
		  dashboard_id varchar(32) NOT NULL default '',
		  column_id int(3) NOT NULL default '0',
		  row_id int(3) NOT NULL default '0',
		  params text,
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
    );
    parent::__construct();
  }

	function install($path_my_files, $demo = false) {
	    global $db;
	    parent::install($path_my_files, $demo);
		// load some default currency values
		$db->Execute("TRUNCATE TABLE " . TABLE_CURRENCIES);
		$currencies_list = array(
		  array('title' => 'US Dollar', 'code' => 'USD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2', 'decimal_precise' => '2', 'value' => 1.00000000, 'last_updated' => date('Y-m-d H:i:s')),
		  array('title' => 'Euro',      'code' => 'EUR', 'symbol_left' => '€', 'symbol_right' => '', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2', 'decimal_precise' => '2', 'value' => 0.75000000, 'last_updated' => date('Y-m-d H:i:s')),
		);
		foreach($currencies_list as $entry) db_perform(TABLE_CURRENCIES, $entry, 'insert');
		write_configure('DEFAULT_CURRENCY', 'USD');
		// Enter some data into table current status
		$db->Execute("TRUNCATE TABLE " . TABLE_CURRENT_STATUS);
		$db->Execute("insert into " . TABLE_CURRENT_STATUS . " set id = 1");
	}

	function initialize() {
		global $db, $messageStack, $currencies, $admin_classes;
	    //load the latest currency exchange rates
		if ($this->web_connected(false) && AUTO_UPDATE_CURRENCY && ENABLE_MULTI_CURRENCY) {
				$currencies->btn_update();
		}
		// Fix for change to audit log for upgrade to R3.6 causes perpertual crashing when writing audit log
		if (!db_field_exists(TABLE_AUDIT_LOG, 'stats')) $db->Execute("ALTER TABLE ".TABLE_AUDIT_LOG." ADD `stats` VARCHAR(32) NOT NULL AFTER `ip_address`");
		if ($this->web_connected(false) && CFG_AUTO_UPDATE_CHECK && (SECURITY_ID_CONFIGURATION > 3)) { // check for software updates
			if (($revisions = @file_get_contents(VERSION_CHECK_URL)) === false) throw new \core\classes\userException("can not open ". VERSION_CHECK_URL);
		  	if ($revisions) {
		   		$versions = xml_to_object($revisions);
				$latest  = $versions->Revisions->Phreedom->Current;
				if (version_compare($admin_classes['phreedom']->version, $latest, '<'))  $messageStack->add(sprintf(TEXT_VERSION_CHECK_NEW_VER, $admin_classes['phreedom']->version, $latest), 'caution');
			}
		}
		// load installed modules and initialize them
		if (is_array($admin_classes)) foreach ($admin_classes as $key => $module_class) {
		  	if ($key == 'phreedom') continue; // skip this module
		  	if ($revisions) {
		  		$latest  = $versions->Revisions->Modules->$key->Current;
		  		if (version_compare($module_class->version, $latest , '<'))  $messageStack->add(sprintf(TEXT_VERSION_CHECK_NEW_MOD_VER, $module_class->text, $module_class->version, $latest), 'caution');
		  	}
		}
		// Make sure the install directory has been moved/removed
		if (is_dir(DIR_FS_ADMIN . 'install')) $messageStack->add(TEXT_INSTALL_DIR_PRESENT, 'caution');
  		return true;
  	}

	function web_connected($silent = true) {
    	$connected = @fsockopen('www.google.com', 80, $errno, $errstr, 20);
    	if ($connected) {
    		if (!@fclose($connected)) throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, 'www.google.com'));
      		return true;
    	} else {
	  		if (!$silent) throw new \core\classes\userException("You are not connected to the internet. Error: $errno -$errstr");
	  		return false;
		}
  	}

	function upgrade() {
	    global $db, $messageStack;
		parent::upgrade();
		$db_version = defined('MODULE_PHREEDOM_STATUS') ? MODULE_PHREEDOM_STATUS : 0;
		if (version_compare($db_version, MODULE_PHREEDOM_STATUS, '<') ) {
	 	  	$db_version = $this->release_update($this->id, 3.0, DIR_FS_MODULES . 'phreedom/updates/PBtoR30.php');
		  	if (!$db_version) return true;
		}
		if (version_compare($this->status, '3.2', '<') ) {
		  	if (!db_field_exists(TABLE_USERS, 'is_role')) $db->Execute("ALTER TABLE ".TABLE_USERS." ADD is_role ENUM('0','1') NOT NULL DEFAULT '0' AFTER admin_id");
		}
		if (version_compare($this->status, '3.4', '<') ) {
		  	if (!db_field_exists(TABLE_DATA_SECURITY, 'exp_date')) $db->Execute("ALTER TABLE ".TABLE_DATA_SECURITY." ADD exp_date DATE NOT NULL DEFAULT '2049-12-31' AFTER enc_value");
		  	if (!db_field_exists(TABLE_AUDIT_LOG, 'ip_address'))   $db->Execute("ALTER TABLE ".TABLE_AUDIT_LOG    ." ADD ip_address VARCHAR(15) NOT NULL AFTER user_id");
	    }
	    if (version_compare($this->status, '3.5', '<') ) {
		  	if (!db_field_exists(TABLE_EXTRA_FIELDS, 'group_by'))  $db->Execute("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD group_by varchar(64) NOT NULL default ''");
		  	if (!db_field_exists(TABLE_EXTRA_FIELDS, 'sort_order'))$db->Execute("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD sort_order varchar(64) NOT NULL default ''");
		  	if (!db_field_exists(TABLE_AUDIT_LOG, 'stats'))        $db->Execute("ALTER TABLE ".TABLE_AUDIT_LOG." ADD `stats` VARCHAR(32) NOT NULL AFTER `ip_address`");
	  	}
	  	if (!db_field_exists(TABLE_EXTRA_FIELDS, 'required'))  $db->Execute("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD required enum('0','1') NOT NULL DEFAULT '0'");
	}

	// EVENTS PART OF THE CLASS
	/**
	 * method validates user
	 * @param \core\classes\basis $basis
	 * @throws \core\classes\userException
	 */
	function ValidateUser (\core\classes\basis $basis){
		global $db;
		// Errors will happen here if there was a problem logging in, logout and restart
		if (!is_object($db)) throw new \core\classes\userException("Database isn't created");
		$sql = "select admin_id, admin_name, inactive, display_name, admin_email, admin_pass, account_id, admin_prefs, admin_security
		  from " . TABLE_USERS . " where admin_name = '{$basis->admin_name}'";
		if ($db->db_connected) $result = $db->Execute($sql);
		if (!$result || $basis->admin_name <> $result->fields['admin_name'] || $result->fields['inactive']) throw new \core\classes\userException(sprintf(GEN_LOG_LOGIN_FAILED, ERROR_WRONG_LOGIN));
		\core\classes\encryption::validate_password($basis->admin_pass, $result->fields['admin_pass']);
		$_SESSION['admin_id']       = $result->fields['admin_id'];
		$_SESSION['display_name']   = $result->fields['display_name'];
		$_SESSION['admin_email']    = $result->fields['admin_email'];
		$_SESSION['admin_prefs']    = unserialize($result->fields['admin_prefs']);
		$_SESSION['account_id']     = $result->fields['account_id'];
		$_SESSION['admin_security'] = \core\classes\user::parse_permissions($result->fields['admin_security']);
		// set some cookies for the next visit to remember the company, language, and theme
		$cookie_exp = 2592000 + time(); // one month
		setcookie('pb_company' , \core\classes\user::get_company(),  $cookie_exp);
		setcookie('pb_language', \core\classes\user::get_language(), $cookie_exp);
		// load init functions for each module and execute
		foreach ($admin_classes->ReturnAdminClasses() as $key => $module_class) {
			if ($module_class->installed && $module_class->should_update()) $module_class->update();
		}
		foreach ($admin_classes->ReturnAdminClasses() as $key => $module_class) {
			if ($module_class->installed) $module_class->initialize();
		}
		if (defined('TABLE_CONTACTS')) {
			$dept = $db->Execute("select dept_rep_id from " . TABLE_CONTACTS . " where id = " . $result->fields['account_id']);
			$_SESSION['department'] = $dept->fields['dept_rep_id'];
		}
		gen_add_audit_log(TEXT_USER_LOGIN .' -->' . $basis->admin_name);
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
		if (get_cfg_var('safe_mode')) throw new \core\classes\userException(SAFE_MODE_ERROR); //@todo is this removed asof php 5.3??
		$basis->fireEvent("LoadMainPage");
	}

	/**
	 * is for loading main page.
	 * @param \core\classes\basis $basis
	 * @throws \core\classes\userException
	 */
	function LoadMainPage (\core\classes\basis $basis){
		global $db;
		$menu_id      = isset($basis->mID) ? $basis->mID : 'index'; // default to index unless heading is passed
		if (!class_exists('queryFactory')) { // Errors will happen here if there was a problem logging in, logout and restart
			session_destroy();
			throw new \core\classes\userException("class queryFactory doesn't exist");
		}
		$basis->cp_boxes 	= $db->Execute("select * from ".TABLE_USERS_PROFILES." where user_id = '{$_SESSION['admin_id']}' and menu_id = '$menu_id' order by column_id, row_id");
		$basis->template 	= 'template_main';
		$basis->page_title 	= COMPANY_NAME.' - '.TITLE;
	}

	/**
	 * logout of phreebooks
	 * @param \core\classes\basis $basis
	 */
	function logout (\core\classes\basis $basis){
		global $db;
		$result = $db->Execute("select admin_name from " . TABLE_USERS . " where admin_id = " . $_SESSION['admin_id']);
		gen_add_audit_log(GEN_LOG_LOGOFF . $result->fields['admin_name']);
		session_destroy();
		$basis->fireEvent("LoadLogIn");
	}

	/**
	 * load varibles for login page
	 * @param \core\classes\basis $basis
	 */
	function LoadLogIn (\core\classes\basis $basis){
		$basis->companies       = load_company_dropdown();
		$basis->single_company  = sizeof($companies) == 1 ? true : false;
		$basis->languages       = load_language_dropdown();
		$basis->single_language = sizeof($languages) == 1 ? true : false;
		$basis->include_header  = false;
		$basis->include_footer  = false;
		$basis->page_title		= TITLE;
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_login';
		$basis->notify();//final line
	}

	function LoadLostPassword (\core\classes\basis $basis){
		$basis->companies       = load_company_dropdown();
		$basis->single_company  = sizeof($companies) == 1 ? true : false;
		$basis->languages       = load_language_dropdown();
		$basis->single_language = sizeof($languages) == 1 ? true : false;
		$basis->include_header  = false;
		$basis->include_footer  = false;
		$basis->page_title		= TITLE;
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_pw_lost';
		$basis->notify();//final line
	}

	function LoadCrash (\core\classes\basis $basis){
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_crash.php';
		$basis->page_title		=  TITLE;
		$basis->notify();//final line
	}

	function SendLostPassWord (\core\classes\basis $basis){
		global $db;
		$result = $db->Execute("select admin_id, admin_name, admin_email from " . TABLE_USERS . " where admin_email = '{$basis->admin_email}'");
		if ($basis->admin_email == '' || $basis->admin_email <> $result->fields['admin_email']) throw new \core\classes\userException(ERROR_WRONG_EMAIL);
		$new_password = \core\classes\encryption::random_password(ENTRY_PASSWORD_MIN_LENGTH);
		$admin_pass   = \core\classes\encryption::password($new_password);
		$db->Execute("update " . TABLE_USERS . " set admin_pass = '$admin_pass' where admin_id = " . $result->fields['admin_id']);
		$html_msg['EMAIL_CUSTOMERS_NAME'] = $result->fields['admin_name'];
		$html_msg['EMAIL_MESSAGE_HTML']   = sprintf(TEXT_EMAIL_MESSAGE, COMPANY_NAME, $new_password);
		validate_send_mail($result->fields['admin_name'], $result->fields['admin_email'], TEXT_EMAIL_SUBJECT, $html_msg['EMAIL_MESSAGE_HTML'], COMPANY_NAME, EMAIL_FROM, $html_msg);
		$messageStack->add(SUCCESS_PASSWORD_SENT, 'success');
		gen_add_audit_log(GEN_LOG_RESEND_PW . $basis->admin_email);
		$basis->fireEvent("LoadLogIn");
	}

	function DownloadDebug (\core\classes\basis $basis){
		$filename = 'trace.txt';
		if (!$handle = @fopen(DIR_FS_MY_FILES . $filename, "r")) 					throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, DIR_FS_MY_FILES . $filename));
		if (!$contents = @fread($handle, filesize(DIR_FS_MY_FILES . $filename)))	throw new \core\classes\userException(sprintf(ERROR_READ_FILE, 		DIR_FS_MY_FILES . $filename));
		if (!@fclose($handle)) 														throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, 	DIR_FS_MY_FILES . $filename));
		$file_size = strlen($contents);
		header('Content-type: text/html; charset=utf-8');
		header("Content-disposition: attachment; filename=$filename; size=$file_size");
		header('Pragma: cache');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Connection: close');
		header('Expires: ' . date('r', time() + 60 * 60));
		header('Last-Modified: ' . date('r', time()));
		print $contents;
		ob_end_flush();
	    session_write_close();
	    die;
	}

}
?>