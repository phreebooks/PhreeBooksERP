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
//  Path: /modules/phreedom/classes/install.php
//
namespace phreedom\classes;
require_once ('/config.php');
class admin extends \core\classes\admin {
	public $description;
	public $id 			= 'phreedom';
	public $installed	= true;
	public $text;
	public $sort_order  = 1;
	public $version		= '4.0';

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
			  dashboard_id varchar(32) NOT NULL default '',
			  column_id int(3) NOT NULL default '0',
			  row_id int(3) NOT NULL default '0',
			  params text,
			  PRIMARY KEY (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	    );
		$this->mainmenu["company"]['submenu']["profile"] = array(
				'order' 		=> 5,
				'text'        => TEXT_MY_PROFILE,
				'security_id' => SECURITY_ID_MY_PROFILE,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=profile', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);

		$this->mainmenu["company"]['submenu']["configuration"] = array(
				'order' 		=> 10,
				'text'        => TEXT_MODULE_ADMINISTRATION,
				'security_id' => SECURITY_ID_CONFIGURATION,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=admin', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);

		if (defined('DEBUG') && DEBUG == true) $this->mainmenu["tools"]['submenu']["debug"] = array(
				'order' 		=> 0,
				'text'        => TEXT_DOWNLOAD_DEBUG_FILE,
				'security_id' => SECURITY_ID_CONFIGURATION,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;action=debug', 'SSL'),
				'show_in_users_settings' => false,
				'params'      => '',
		);
		if (defined('ENABLE_ENCRYPTION') && ENABLE_ENCRYPTION == true) $this->mainmenu["tools"]['submenu']["encryption"] = array(
				'order' 		=> 1,
				'text'        => TEXT_DATA_ENCRYPTION,
				'security_id' => SECURITY_ID_ENCRYPTION,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=encryption', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		$this->mainmenu["tools"]['submenu']["import_export"] = array(
				'order' 		=> 50,
				'text'        => TEXT_IMPORT_OR_EXPORT,
				'security_id' => SECURITY_ID_IMPORT_EXPORT,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=import_export', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		$this->mainmenu["tools"]['submenu']["backup"] = array(
				'order' 		=> 95,
				'text'        => TEXT_COMPANY_BACKUP,
				'security_id' => SECURITY_ID_BACKUP,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=backup', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		$this->mainmenu["company"]['submenu']["users"] = array(
				'order' 		=> 90,
				'text'        => TEXT_USERS,
				'security_id' => SECURITY_ID_USERS,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=users&amp;list=1', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		$this->mainmenu["company"]['submenu']["roles"] = array(
				'order' 		=> 85,
				'text'        => TEXT_ROLES,
				'security_id' => SECURITY_ID_ROLES,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=roles&amp;list=1', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);

	    parent::__construct();
	}

	function install($path_my_files, $demo = false) {
	    global $admin;
	    parent::install($path_my_files, $demo);
		// load some default currency values
		$admin->DataBase->exec("TRUNCATE TABLE " . TABLE_CURRENCIES);
		$currencies_list = array(
		  array('title' => 'US Dollar', 'code' => 'USD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2', 'decimal_precise' => '2', 'value' => 1.00000000, 'last_updated' => date('Y-m-d H:i:s')),
		  array('title' => 'Euro',      'code' => 'EUR', 'symbol_left' => '€', 'symbol_right' => '', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2', 'decimal_precise' => '2', 'value' => 0.75000000, 'last_updated' => date('Y-m-d H:i:s')),
		);
		foreach($currencies_list as $entry) db_perform(TABLE_CURRENCIES, $entry, 'insert');
		write_configure('DEFAULT_CURRENCY', 'USD');
		// Enter some data into table current status
		$admin->DataBase->exec("TRUNCATE TABLE " . TABLE_CURRENT_STATUS);
		$admin->DataBase->exec("insert into " . TABLE_CURRENT_STATUS . " set id = 1");
	}

	function after_ValidateUser(\core\classes\basis &$basis) {
		global $messageStack, $currencies;
	    //load the latest currency exchange rates
		if ($this->web_connected(false) && AUTO_UPDATE_CURRENCY && ENABLE_MULTI_CURRENCY) {
				$currencies->btn_update();
		}
		// Fix for change to audit log for upgrade to R3.6 causes perpertual crashing when writing audit log
		if (!db_field_exists(TABLE_AUDIT_LOG, 'stats')) $basis->DataBase->exec("ALTER TABLE ".TABLE_AUDIT_LOG." ADD `stats` VARCHAR(32) NOT NULL AFTER `ip_address`");
		if ($this->web_connected(false) && CFG_AUTO_UPDATE_CHECK && (SECURITY_ID_CONFIGURATION > 3)) { // check for software updates
			if (($this->revisions = @file_get_contents(VERSION_CHECK_URL)) === false) throw new \core\classes\userException("can not open ". VERSION_CHECK_URL);
		}
		if ($this->revisions && CFG_AUTO_UPDATE_CHECK && (SECURITY_ID_CONFIGURATION > 3)) { // compaire software versions
			$versions = xml_to_object($this->revisions);
			$latest  = $versions->Revisions->Phreedom->Current;
			if (version_compare($basis->classes['phreedom']->version, $latest, '<'))  $messageStack->add(sprintf(TEXT_VERSION_CHECK_NEW_VER, $basis->classes['phreedom']->version, $latest), 'caution');
			// load installed modules and initialize them
			foreach ($basis->classes as $key => $module_class) {
				if ($key == 'phreedom') continue; // skip this module
				$latest  = $versions->Revisions->Modules->$key->Current;
				if (version_compare($module_class->version, $latest , '<'))  $messageStack->add(sprintf(TEXT_VERSION_CHECK_NEW_MOD_VER, $module_class->text, $module_class->version, $latest), 'caution');
			}
		}
		// Make sure the install directory has been moved/removed
		if (is_dir(DIR_FS_ADMIN . 'install')) $messageStack->add(TEXT_INSTALL_DIR_PRESENT, 'caution');
  	}

	function web_connected($silent = true) {
		if ($this->revisions != '') return;
    	$connected = @fsockopen('www.google.com', 80, $errno, $errstr, 20);
    	if ($connected) {
    		if (!@fclose($connected)) throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, 'www.google.com'));
      		return true;
    	} else {
	  		if (!$silent) throw new \core\classes\userException("You are not connected to the internet. Error: $errno -$errstr");
	  		return false;
		}
  	}

	function upgrade(\core\classes\basis &$basis) {
		parent::upgrade($basis);
		$db_version = defined('MODULE_PHREEDOM_STATUS') ? MODULE_PHREEDOM_STATUS : 0;
		if (version_compare($db_version, MODULE_PHREEDOM_STATUS, '<') ) {
	 	  	$db_version = $this->release_update($this->id, 3.0, DIR_FS_MODULES . 'phreedom/updates/PBtoR30.php');
		  	if (!$db_version) return true;
		}
		if (version_compare($this->status, '3.2', '<') ) {
		  	if (!db_field_exists(TABLE_USERS, 'is_role')) $basis->DataBase->exec("ALTER TABLE ".TABLE_USERS." ADD is_role ENUM('0','1') NOT NULL DEFAULT '0' AFTER admin_id");
		}
		if (version_compare($this->status, '3.4', '<') ) {
		  	if (!db_field_exists(TABLE_DATA_SECURITY, 'exp_date')) $basis->DataBase->exec("ALTER TABLE ".TABLE_DATA_SECURITY." ADD exp_date DATE NOT NULL DEFAULT '2049-12-31' AFTER enc_value");
		  	if (!db_field_exists(TABLE_AUDIT_LOG, 'ip_address'))   $basis->DataBase->exec("ALTER TABLE ".TABLE_AUDIT_LOG    ." ADD ip_address VARCHAR(15) NOT NULL AFTER user_id");
	    }
	    if (version_compare($this->status, '3.5', '<') ) {
		  	if (!db_field_exists(TABLE_EXTRA_FIELDS, 'group_by'))  $basis->DataBase->exec("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD group_by varchar(64) NOT NULL default ''");
		  	if (!db_field_exists(TABLE_EXTRA_FIELDS, 'sort_order'))$basis->DataBase->exec("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD sort_order varchar(64) NOT NULL default ''");
		  	if (!db_field_exists(TABLE_AUDIT_LOG, 'stats'))        $basis->DataBase->exec("ALTER TABLE ".TABLE_AUDIT_LOG." ADD `stats` VARCHAR(32) NOT NULL AFTER `ip_address`");
	  	}
	  	if (!db_field_exists(TABLE_EXTRA_FIELDS, 'required'))  $basis->DataBase->exec("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD required enum('0','1') NOT NULL DEFAULT '0'");
	  	if (version_compare($this->status, '4.0', '<') ) { //updating dashboards to store the namespaces.
	  		$basis->DataBase->exec ("ALTER TABLE ".TABLE_USERS_PROFILES." CHANGE dashboard_id dashboard_id VARCHAR( 255 ) NOT NULL DEFAULT ''");
	  		$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE module_id <> '' ");
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
				if ( !in_array( $result['dashboard_id'] , $basis->classes[ $result['module_id'] ]->dashboards) ) {
					$basis->DataBase->exec("DELETE from " . TABLE_USERS_PROFILES . " WHERE user_id = {$result['user_id']} and menu_id = '{$result['menu_id']}' and dashboard_id = '{$result['dashboard_id']}'");
				} else {
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->menu_id			= $result['menu_id'];
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->user_id			= $result['user_id'];
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->default_params	= unserialize( $result['params'] );
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->install( $result['column_id'], $result['row_id'] );
				}
			}
			$basis->DataBase->exec("DELETE from ".TABLE_USERS_PROFILES . " WHERE module_id != ''");
			$basis->DataBase->exec("ALTER TABLE ".TABLE_USERS_PROFILES . " DROP module_id");
	  	}
	}

	// EVENTS PART OF THE CLASS
	/**
	 * method validates user
	 * @param \core\classes\basis $basis
	 * @throws \core\classes\userException
	 */
	function ValidateUser (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		// Errors will happen here if there was a problem logging in, logout and restart
		if ($basis->DataBase == null) throw new \core\classes\userException("Database isn't created");
		$sql = $basis->DataBase->prepare("SELECT admin_id, admin_name, inactive, display_name, admin_email, admin_pass, account_id, admin_prefs, admin_security
		  FROM " . TABLE_USERS . " WHERE admin_name = '{$basis->cInfo->admin_name}'");
		$sql->execute();
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		if (!$result || $basis->cInfo->admin_name <> $result['admin_name'] || $result['inactive']) \core\classes\userException(TEXT_YOU_ENTERED_THE_WRONG_USERNAME_OR_PASSWORD, 'LoadLogIn');
		\core\classes\encryption::validate_password($basis->cInfo->admin_pass, $result['admin_pass']);
		$_SESSION['admin_id']       = $result['admin_id'];
		$_SESSION['display_name']   = $result['display_name'];
		$_SESSION['admin_email']    = $result['admin_email'];
		$_SESSION['admin_prefs']    = unserialize($result['admin_prefs']);
		$_SESSION['account_id']     = $result['account_id'];
		$_SESSION['admin_security'] = \core\classes\user::parse_permissions($result['admin_security']);
		// set some cookies for the next visit to remember the company, language, and theme
		$cookie_exp = 2592000 + time(); // one month
		setcookie('pb_company' , \core\classes\user::get_company(),  $cookie_exp);
		setcookie('pb_language', \core\classes\user::get_language(), $cookie_exp);
		// load init functions for each module and execute
		foreach ($basis->classes as $key => $module_class) $module_class->should_update($basis);
		if (defined('TABLE_CONTACTS')) {
			$dept = $basis->DataBase->query("select dept_rep_id from " . TABLE_CONTACTS . " where id = " . $result['account_id']);
			$_SESSION['department'] = $dept['dept_rep_id'];
		}
		gen_add_audit_log(TEXT_USER_LOGIN . " -> id: {$_SESSION['admin_id']} name: {$_SESSION['display_name']}");
		// check for session timeout to reload to requested page
		/*$get_params = '';
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
		}*/
		// check safe mode is allowed to log in.
		if (get_cfg_var('safe_mode')) throw new \core\classes\userException(SAFE_MODE_ERROR); //@todo is this removed as of php 5.3??
		$basis->addEventToStack("LoadMainPage");
	}

	/**
	 * is for loading main page.
	 * @param \core\classes\basis $basis
	 * @throws \core\classes\userException
	 */
	function LoadMainPage (\core\classes\basis &$basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->cInfo->menu_id  =  isset($basis->cInfo->mID) ? $basis->cInfo->mID : 'index'; // default to index unless heading is passed
		$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE user_id = '{$_SESSION['admin_id']}' and menu_id = '{$basis->cInfo->menu_id}' ORDER BY column_id, row_id");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) {
			$basis->cInfo->cp_boxes[] = $result;
		}
		$basis->page_title 	= COMPANY_NAME.' - '.TEXT_PHREEBOOKS_ERP;
		$basis->module		= 'phreedom';
		$basis->page		= 'main';
		$basis->template 	= 'template_main';
	}

	/**
	 * logout of phreebooks
	 * @param \core\classes\basis $basis
	 */
	function logout (\core\classes\basis &$basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		gen_add_audit_log(TEXT_USER_LOGOFF . " -> id: {$_SESSION['admin_id']} name: {$_SESSION['display_name']}");
		session_destroy();
		$basis->addEventToStack("LoadLogIn");
	}

	/**
	 * load varibles for login page
	 * @param \core\classes\basis $basis
	 */
	function LoadLogIn (\core\classes\basis &$basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->include_header  = false;
		$basis->include_footer  = false;
		$basis->page_title		= TEXT_PHREEBOOKS_ERP;
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_login';
		//@todo js not working jet
		$basis->js .= "
				$(window).load(function() {
					$( \"#admin_name\" ).select();
				});";
	}

	function LoadLostPassword (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->companies       = load_company_dropdown();
		$basis->single_company  = sizeof($companies) == 1 ? true : false;
		$basis->languages       = load_language_dropdown();
		$basis->single_language = sizeof($languages) == 1 ? true : false;
		$basis->include_header  = false;
		$basis->include_footer  = false;
		$basis->page_title		= TEXT_PHREEBOOKS_ERP;
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_pw_lost';
	}

	function LoadCrash (\core\classes\basis $basis){
		global $messageStack;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$messageStack->write_debug();
		$basis->include_header  = false;
		$basis->include_footer  = false;
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_crash';
		$basis->page_title		=  TEXT_PHREEBOOKS_ERP;
	}

	function SendLostPassWord (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("select admin_id, admin_name, admin_email from " . TABLE_USERS . " where admin_email = '{$basis->admin_email}'");
		$sql->execute();
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		if ($basis->admin_email == '' || $basis->admin_email <> $result['admin_email']) throw new \core\classes\userException(TEXT_YOU_ENTERED_THE_WRONG_EMAIL_ADDRESS);
		$new_password = \core\classes\encryption::random_password(ENTRY_PASSWORD_MIN_LENGTH);
		$admin_pass   = \core\classes\encryption::password($new_password);
		$basis->DataBase->exec("UPDATE " . TABLE_USERS . " SET admin_pass = '$admin_pass' WHERE admin_id = " . $result['admin_id']);
		$html_msg['EMAIL_CUSTOMERS_NAME'] = $result['admin_name'];
		$html_msg['EMAIL_MESSAGE_HTML']   = sprintf(TEXT_EMAIL_MESSAGE, COMPANY_NAME, $new_password);
		validate_send_mail($result['admin_name'], $result['admin_email'], TEXT_EMAIL_SUBJECT, $html_msg['EMAIL_MESSAGE_HTML'], COMPANY_NAME, EMAIL_FROM, $html_msg);
		$messageStack->add(SUCCESS_PASSWORD_SENT, 'success');
		gen_add_audit_log(TEXT_RE-SENT_PASSWORD_TO_EMAIL . ' -> ' . $basis->admin_email);
		$basis->addEventToStack("LoadLogIn");
	}

	function DownloadDebug (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
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

	function removeDashboard (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE id = '{$basis->cInfo->id}'");
		$sql->execute();
		while ($result = $sql->fetch (\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) $result->remove();
		$basis->addEventToStack("LoadMainPage");
	}

	function moveDashboardLeft (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE id = '{$basis->cInfo->dashboard_id}'");
		$sql->execute();
		while ($result = $sql->fetch (\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) $result->move_left();
		$basis->addEventToStack("LoadMainPage");
	}

	function moveDashboardRight (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE id = '{$basis->cInfo->dashboard_id}'");
		$sql->execute();
		while ($result = $sql->fetch (\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) $result->move_right();
		$basis->addEventToStack("LoadMainPage");
	}

	function moveDashboardUp (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare ("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE id = '{$basis->cInfo->dashboard_id}'");
		$sql->execute();
		while ($result = $sql->fetch (\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) $result->move_up();
		$basis->addEventToStack("LoadMainPage");
	}

	function moveDashboardDown (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare ("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE id = '{$basis->cInfo->dashboard_id}'");
		$sql->execute();
		while ($result = $sql->fetch (\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) $result->move_down();
		$basis->addEventToStack("LoadMainPage");
	}

	function saveDashboard (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare ("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE id = '{$basis->cInfo->dashboard_id}'");
		$sql->execute();
		while ($result = $sql->fetch (\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) {
			$dashboard->menu_id	= isset($_GET['mID']) ? $_GET['mID'] : 'index';
			$result->update();
		}
		$basis->addEventToStack("LoadMainPage");
	}

	function showPHPinfo (\core\classes\basis $basis){
		die(phpinfo());
	}
}
?>