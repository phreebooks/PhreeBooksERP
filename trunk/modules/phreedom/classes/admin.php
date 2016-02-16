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
//  Path: /modules/phreedom/classes/admin.php
//
namespace phreedom\classes;
require_once (DIR_FS_ADMIN . 'modules/phreedom/config.php');
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
		$this->mainmenu["tools"]->submenu   ["debug"]  			= new \core\classes\menuItem (-1, 	TEXT_DOWNLOAD_DEBUG_FILE,	'module=phreedom&amp;page=admin', 				SECURITY_ID_CONFIGURATION);
		$this->mainmenu["tools"]->submenu   ["debug"]->required_module = 'DEBUG';
		$this->mainmenu["tools"]->submenu   ["encryption"]  	= new \core\classes\menuItem ( 1, 	TEXT_DATA_ENCRYPTION,		'module=phreedom&amp;page=encryption', 			SECURITY_ID_ENCRYPTION);
		$this->mainmenu["tools"]->submenu   ["encryption"]->required_module = 'ENABLE_ENCRYPTION';
		$this->mainmenu["tools"]->submenu   ["import_export"]  	= new \core\classes\menuItem (50, 	TEXT_IMPORT_OR_EXPORT,		'module=phreedom&amp;page=import_export', 		SECURITY_ID_IMPORT_EXPORT);
		$this->mainmenu["tools"]->submenu   ["backup"]  		= new \core\classes\menuItem (95, 	TEXT_COMPANY_BACKUP,		'module=phreedom&amp;page=backup', 				SECURITY_ID_BACKUP);
		
		$this->mainmenu["company"]->submenu ["profile"] 		= new \core\classes\menuItem ( 5, 	TEXT_MY_PROFILE,			'module=phreedom&amp;page=profile', 			SECURITY_ID_MY_PROFILE);
		$this->mainmenu["company"]->submenu ["roles"]  			= new \core\classes\menuItem (85, 	TEXT_ROLES,					'module=phreedom&amp;page=roles&amp;list=1',	SECURITY_ID_ROLES);
		$this->mainmenu["company"]->submenu ["users"] 		 	= new \core\classes\menuItem (90, 	TEXT_USERS,					'action=LoadUsersPage&amp;list=1', 				SECURITY_ID_USERS);
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
		$admin->DataBase->write_configure('DEFAULT_CURRENCY', 'USD');
		// Enter some data into table current status
		$admin->DataBase->exec("TRUNCATE TABLE " . TABLE_CURRENT_STATUS);
		$admin->DataBase->exec("insert into " . TABLE_CURRENT_STATUS . " set id = 1");
	}

	function after_ValidateUser(\core\classes\basis &$basis) {
		global $messageStack;
	    //load the latest currency exchange rates
		if ($this->web_connected(false) && AUTO_UPDATE_CURRENCY && ENABLE_MULTI_CURRENCY) {
				$basis->currencies->btn_update();
		}
		// Fix for change to audit log for upgrade to R3.6 causes perpertual crashing when writing audit log
		if (!$basis->DataBase->field_exists(TABLE_AUDIT_LOG, 'stats')) $basis->DataBase->exec("ALTER TABLE ".TABLE_AUDIT_LOG." ADD `stats` VARCHAR(32) NOT NULL AFTER `ip_address`");
		if ($this->web_connected(false) && CFG_AUTO_UPDATE_CHECK && (SECURITY_ID_CONFIGURATION > 3)) { // check for software updates
			if (($this->revisions = @file_get_contents(VERSION_CHECK_URL)) === false) throw new \core\classes\userException("can not open ". VERSION_CHECK_URL);
		}
		if ($this->revisions && CFG_AUTO_UPDATE_CHECK && (SECURITY_ID_CONFIGURATION > 3)) { // compaire software versions
			$versions = xml_to_object($this->revisions);
			$latest  = $versions->Revisions->Phreedom->Current;
			if (version_compare($basis->classes['phreedom']->version, $latest, '<'))  \core\classes\messageStack::add(sprintf(TEXT_VERSION_CHECK_NEW_VER, $basis->classes['phreedom']->version, $latest), 'caution');
			// load installed modules and initialize them
			foreach ($basis->classes as $key => $module_class) {
				if ($key == 'phreedom') continue; // skip this module
				$latest  = $versions->Revisions->Modules->$key->Current;
				if (version_compare($module_class->version, $latest , '<'))  \core\classes\messageStack::add(sprintf(TEXT_VERSION_CHECK_NEW_MOD_VER, $module_class->text, $module_class->version, $latest), 'caution');
			}
		}
		// Make sure the install directory has been moved/removed
		if (is_dir(DIR_FS_ADMIN . 'install')) \core\classes\messageStack::add(TEXT_INSTALL_DIR_PRESENT, 'caution');
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
		  	if (!$basis->DataBase->field_exists(TABLE_USERS, 'is_role')) $basis->DataBase->exec("ALTER TABLE ".TABLE_USERS." ADD is_role ENUM('0','1') NOT NULL DEFAULT '0' AFTER admin_id");
		}
		if (version_compare($this->status, '3.4', '<') ) {
		  	if (!$basis->DataBase->field_exists(TABLE_DATA_SECURITY, 'exp_date')) $basis->DataBase->exec("ALTER TABLE ".TABLE_DATA_SECURITY." ADD exp_date DATE NOT NULL DEFAULT '2049-12-31' AFTER enc_value");
		  	if (!$basis->DataBase->field_exists(TABLE_AUDIT_LOG, 'ip_address'))   $basis->DataBase->exec("ALTER TABLE ".TABLE_AUDIT_LOG    ." ADD ip_address VARCHAR(15) NOT NULL AFTER user_id");
	    }
	    if (version_compare($this->status, '3.5', '<') ) {
		  	if (!$basis->DataBase->field_exists(TABLE_EXTRA_FIELDS, 'group_by'))  $basis->DataBase->exec("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD group_by varchar(64) NOT NULL default ''");
		  	if (!$basis->DataBase->field_exists(TABLE_EXTRA_FIELDS, 'sort_order'))$basis->DataBase->exec("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD sort_order varchar(64) NOT NULL default ''");
		  	if (!$basis->DataBase->field_exists(TABLE_AUDIT_LOG, 'stats'))        $basis->DataBase->exec("ALTER TABLE ".TABLE_AUDIT_LOG." ADD `stats` VARCHAR(32) NOT NULL AFTER `ip_address`");
	  	}
	  	if (!$basis->DataBase->field_exists(TABLE_EXTRA_FIELDS, 'required'))  $basis->DataBase->exec("ALTER TABLE ".TABLE_EXTRA_FIELDS." ADD required enum('0','1') NOT NULL DEFAULT '0'");
	  	if (version_compare($this->status, '4.0.1', '<') ) { //updating dashboards to store the namespaces.
	  		$basis->DataBase->exec ("ALTER TABLE ".TABLE_USERS_PROFILES." CHANGE dashboard_id dashboard_id VARCHAR( 255 ) NOT NULL DEFAULT ''");
	  		$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_USERS_PROFILES." WHERE module_id <> '' ");
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
				\core\classes\messageStack::debug_log("started validating if dashboard {$result['dashboard_id']} if it exists in module {$result['module_id']}");
				if ( array_key_exists( $result['dashboard_id'] , $basis->classes[ $result['module_id'] ]->dashboards) ) {
					\core\classes\messageStack::debug_log("updating dashboard {$result['dashboard_id']} in module {$result['module_id']}");
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->menu_id			= $result['menu_id'];
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->user_id			= $result['user_id'];
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->default_params	= unserialize( $result['params'] );
					$basis->classes[ $result['module_id'] ]->dashboards[ $result['dashboard_id'] ]->install( $result['column_id'], $result['row_id'] );
				} else {
					\core\classes\messageStack::debug_log("removing dashboard {$result['dashboard_id']} because it doesn't exist in module {$result['module_id']}");
					$basis->DataBase->exec("DELETE from " . TABLE_USERS_PROFILES . " WHERE user_id = {$result['user_id']} and menu_id = '{$result['menu_id']}' and dashboard_id = '{$result['dashboard_id']}'");
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
		\core\classes\messageStack::debug_log("database type ".$basis->DataBase->getAttribute(\PDO::ATTR_DRIVER_NAME));
		if (!$basis->DataBase instanceof \PDO) throw new \core\classes\userException("Database isn't created");
		try{
			$sql = $basis->DataBase->prepare("SELECT admin_id, admin_name, inactive, display_name, admin_email, admin_pass, account_id, admin_prefs, admin_security
			  FROM " . TABLE_USERS . " WHERE admin_name = '{$basis->cInfo->admin_name}'");
			$sql->execute();
			$result = $sql->fetch(\PDO::FETCH_LAZY);
			if (!$result || $result['inactive']) throw new \core\classes\userException(TEXT_YOU_ENTERED_THE_WRONG_USERNAME_OR_PASSWORD, 'LoadLogIn');
			\core\classes\encryption::validate_password($basis->cInfo->admin_pass, $result['admin_pass']);
		}catch (\Exception $e){
			\core\classes\messageStack::debug_log($e);
			\core\classes\messageStack::add(TEXT_YOU_ENTERED_THE_WRONG_USERNAME_OR_PASSWORD);
			$_SESSION['user']->LoadLogIn();
		}
		$_SESSION['user']->admin_id       = $result['admin_id'];
		$_SESSION['user']->display_name   = $result['display_name'];
		$_SESSION['user']->admin_email    = $result['admin_email'];
		$_SESSION['user']->admin_prefs    = unserialize($result['admin_prefs']);
		$_SESSION['user']->account_id     = $result['account_id'];
		$_SESSION['user']->admin_security = \core\classes\user::parse_permissions($result['admin_security']);
		
		// load init functions for each module and execute
		foreach ($basis->classes as $key => $module_class) $module_class->should_update($basis);
		if (defined('TABLE_CONTACTS')) {
			$sql = $basis->DataBase->prepare("select dept_rep_id from " . TABLE_CONTACTS . " where id = " . $result['account_id']);
			$sql->execute();
			$dept = $sql->fetch(\PDO::FETCH_LAZY);
			$_SESSION['user']->department = $dept['dept_rep_id'];
		}
		gen_add_audit_log(TEXT_USER_LOGIN . " -> id: {$_SESSION['user']->admin_id} name: {$_SESSION['user']->display_name}");
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
		$basis->observer->send_menu($basis);
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->cInfo->menu_id  =  isset($basis->cInfo->mID) ? $basis->cInfo->mID : 'index'; // default to index unless heading is passed
		$current_column = 1;
		require (DIR_FS_ADMIN . "modules/phreedom/pages/main/js_include.php");
		// include hidden fields
		echo html_hidden_field('action', '') . chr(10);
		echo html_hidden_field('dashboard_id', '') . chr(10);
		?>
		<script type='text/javascript'>	document.title = '<?php echo COMPANY_NAME.' - '.TEXT_PHREEBOOKS_ERP; ?>';</script>
		<div><a href="<?php echo html_href_link(FILENAME_DEFAULT, 'amp;mID=' . $basis->cInfo->menu_id, 'SSL'); ?>"><?php echo TEXT_ADD_DASHBOARD_ITEMS_TO_THIS_PAGE; ?></a></div>
		<table style="width:100%;margin-left:auto;margin-right:auto;">
		  <tr>
		  </tr>
		  <tr>
		    <td width="33%" valign="top">
		      <div id="col_<?php echo $current_column; ?>" style="position:relative;">
		<?php
		$sql = $basis->DataBase->prepare("SELECT dashboard_id, id, user_id, menu_id, column_id, row_id, params FROM ".TABLE_USERS_PROFILES." WHERE user_id = '{$_SESSION['user']->admin_id}' and menu_id = '{$basis->cInfo->menu_id}' ORDER BY column_id, row_id");
		$sql->execute();
		while ($box = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE)) {
			if($box->column_id <> $current_column) {
				$box->row_started = true;
				while ($box->column_id <> $current_column) {
					$current_column++;
					echo '      </div>' . chr(10);
					echo '    </td>' . chr(10);
					echo '    <td width="33%" valign="top">' . chr(10);
					echo "      <div id='col_{$current_column}' style='position:relative;'>" . chr(10);
				}
			}
		 	echo $box->output();
		}
		while (MAX_CP_COLUMNS <> $current_column) { // fill remaining columns with blank space
		  	$current_column++;
		  	echo '      </div>' . chr(10);
		  	echo '    </td>' . chr(10);
		  	echo '    <td width="33%" valign="top">' . chr(10);
		  	echo "      <div id='col_{$current_column}' style='position:relative;'>" . chr(10);
		}
		?>
		      </div>
		    </td>
		  </tr>
		</table><?php
		$basis->observer->send_footer($basis);
	}

	/**
	 * logout of phreebooks
	 * @param \core\classes\basis $basis
	 */
	function logout (\core\classes\basis &$basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$_SESSION['user']->logout();
	}

	/**
	 * load varibles for login page
	 * @param \core\classes\basis $basis
	 */
	function LoadLogIn (\core\classes\basis &$basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
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
		$basis->page_title		= TEXT_PHREEBOOKS_ERP;
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_pw_lost';
	}

	function LoadCrash (\core\classes\basis $basis){
		global $messageStack;
		$basis->observer->send_menu($basis);
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$messageStack->write_debug();
		$basis->observer->send_footer($basis);
		$basis->module			= 'phreedom';
		$basis->page			= 'main';
		$basis->template 		= 'template_crash';
		$basis->page_title		=  TEXT_PHREEBOOKS_ERP;
	}

	/**
	 * is for loading users page.
	 * @param \core\classes\basis $basis
	 * @throws \core\classes\userException
	 */
	function LoadUsersPage (\core\classes\basis &$basis){ //@todo
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->observer->send_menu($basis);
		$basis->cInfo->menu_id  =  isset($basis->cInfo->mID) ? $basis->cInfo->mID : 'index'; // default to index unless heading is passed
		$basis->page_title 	= TEXT_USERS;
		$basis->module		= 'phreedom';
		$basis->page		= 'users';
		$basis->template 	= 'template_main';

		// build the list header
		$heading_array = array(
				'admin_name'   => TEXT_USERNAME,
				'inactive'     => TEXT_INACTIVE,
				'display_name' => TEXT_DISPLAY_NAME,
				'admin_email'  => TEXT_EMAIL,
		);
		$result      = html_heading_bar($heading_array);
		$list_header = $result['html_code'];
		$disp_order  = $result['disp_order'];
		// build the list for the page selected
		if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
			$search_fields = array('admin_name', 'admin_email', 'display_name');
			// hook for inserting new search fields to the query criteria.
			if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
			$search = ' and (' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
		} else {
			$search = '';
		}
		$field_list = array('admin_id', 'inactive', 'display_name', 'admin_name', 'admin_email');
		// hook to add new fields to the query return results
		if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
		$query_raw    = "SELECT SQL_CALC_FOUND_ROWS " . implode(', ', $field_list) . " FROM " . TABLE_USERS . " WHERE is_role = '0'{$search} ORDER BY {$disp_order}";
		$sql = $basis->DataBase->prepare($query_raw);
		$sql->execute();
		$sql->fetch(\PDO::FETCH_NUM);
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		$query_result = $basis->DataBase->query($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
		$query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
		history_save('users');
		$basis->observer->send_footer($basis);
	}

	function SendLostPassWord (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("SELECT admin_id, admin_name, admin_email FROM " . TABLE_USERS . " WHERE admin_email = '{$basis->admin_email}'");
		$sql->execute();
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		if ($basis->admin_email == '' || $basis->admin_email <> $result['admin_email']) throw new \core\classes\userException(TEXT_YOU_ENTERED_THE_WRONG_EMAIL_ADDRESS);
		$new_password = \core\classes\encryption::random_password(ENTRY_PASSWORD_MIN_LENGTH);
		$admin_pass   = \core\classes\encryption::password($new_password);
		$basis->DataBase->exec("UPDATE " . TABLE_USERS . " SET admin_pass = '$admin_pass' WHERE admin_id = " . $result['admin_id']);
		$html_msg['EMAIL_CUSTOMERS_NAME'] = $result['admin_name'];
		$html_msg['EMAIL_MESSAGE_HTML']   = sprintf(TEXT_EMAIL_MESSAGE, COMPANY_NAME, $new_password);
		validate_send_mail($result['admin_name'], $result['admin_email'], TEXT_EMAIL_SUBJECT, $html_msg['EMAIL_MESSAGE_HTML'], COMPANY_NAME, EMAIL_FROM, $html_msg);
		\core\classes\messageStack::add(SUCCESS_PASSWORD_SENT, 'success');
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
		header_remove();
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