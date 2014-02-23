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
//  Path: /modules/contacts/classes/admin.php
//
namespace contacts\classes;
class admin extends \core\classes\admin {
	public $sort_order  = 3;
	public $id 			= 'contacts';
	public $text		= MODULE_CONTACTS_TITLE;
	public $description = MODULE_CONTACTS_DESCRIPTION;
	public $core		= true;
	public $version		= '3.71';
	
	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.6,
		  'phreebooks' => 3.6,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'ADDRESS_BOOK_CONTACT_REQUIRED'        => '0',
		  'ADDRESS_BOOK_ADDRESS1_REQUIRED'       => '1',
		  'ADDRESS_BOOK_ADDRESS2_REQUIRED'       => '0',
		  'ADDRESS_BOOK_CITY_TOWN_REQUIRED'      => '1',
		  'ADDRESS_BOOK_STATE_PROVINCE_REQUIRED' => '1',
		  'ADDRESS_BOOK_POSTAL_CODE_REQUIRED'    => '1',
		  'ADDRESS_BOOK_TELEPHONE1_REQUIRED'     => '0',
		  'ADDRESS_BOOK_EMAIL_REQUIRED'          => '0',
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'contacts',
		  'contacts/main',
		);
		// Load tables
		$this->tables = array(
		  TABLE_ADDRESS_BOOK => "CREATE TABLE " . TABLE_ADDRESS_BOOK . " (
			  address_id int(11) NOT NULL auto_increment,
			  ref_id int(11) NOT NULL default '0',
			  type char(2) NOT NULL default '',
			  primary_name varchar(32) NOT NULL default '',
			  contact varchar(32) NOT NULL default '',
			  address1 varchar(32) NOT NULL default '',
			  address2 varchar(32) NOT NULL default '',
			  city_town varchar(24) NOT NULL default '',
			  state_province varchar(24) NOT NULL default '',
			  postal_code varchar(10) NOT NULL default '',
			  country_code char(3) NOT NULL default '',
			  telephone1 VARCHAR(20) NULL DEFAULT '',
			  telephone2 VARCHAR(20) NULL DEFAULT '',
			  telephone3 VARCHAR(20) NULL DEFAULT '',
			  telephone4 VARCHAR(20) NULL DEFAULT '',
			  email VARCHAR(48) NULL DEFAULT '',
			  website VARCHAR(48) NULL DEFAULT '',
			  notes text,
			  PRIMARY KEY (address_id),
			  KEY customer_id (ref_id,type)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_CONTACTS => "CREATE TABLE " . TABLE_CONTACTS . " (
			  id int(11) NOT NULL auto_increment,
			  type char(1) NOT NULL default 'c',
			  short_name varchar(32) NOT NULL default '',
			  inactive enum('0','1') NOT NULL default '0',
			  contact_first varchar(32) default NULL,
			  contact_middle varchar(32) default NULL,
			  contact_last varchar(32) default NULL,
			  store_id varchar(15) NOT NULL default '',
			  gl_type_account varchar(15) NOT NULL default '',
			  gov_id_number varchar(16) NOT NULL default '',
			  dept_rep_id varchar(16) NOT NULL default '',
			  account_number varchar(16) NOT NULL default '',
			  special_terms varchar(32) NOT NULL default '0',
			  price_sheet varchar(32) default NULL,
	          tax_id INT(11) default '-1',
	          attachments text,
			  first_date date NOT NULL default '0000-00-00',
			  last_update date default NULL,
			  last_date_1 date default NULL,
			  last_date_2 date default NULL,
			  PRIMARY KEY (id),
			  KEY type (type),
			  KEY short_name (short_name)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_CONTACTS_LOG => "CREATE TABLE " . TABLE_CONTACTS_LOG . " (
			  log_id int(11) NOT NULL auto_increment,
			  contact_id int(11) NOT NULL default '0',
			  entered_by int(11) NOT NULL default '0',
			  log_date datetime NOT NULL default '0000-00-00',
			  action varchar(32) NOT NULL default '',
			  notes text,
			  PRIMARY KEY (log_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_DEPARTMENTS => "CREATE TABLE " . TABLE_DEPARTMENTS . " (
			  id int(11) NOT NULL auto_increment,
			  description_short varchar(30) NOT NULL default '',
			  description varchar(30) NOT NULL default '',
			  subdepartment enum('0','1') NOT NULL default '0',
			  primary_dept_id int(11) NOT NULL default '0',
			  department_type tinyint(4) NOT NULL default '0',
			  department_inactive enum('0','1') NOT NULL default '0',
			  PRIMARY KEY (id),
			  KEY type (department_type)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_DEPT_TYPES => "CREATE TABLE " . TABLE_DEPT_TYPES . " (
			  id int(11) NOT NULL auto_increment,
			  description varchar(30) NOT NULL default '',
			  PRIMARY KEY (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_PROJECTS_COSTS => "CREATE TABLE " . TABLE_PROJECTS_COSTS . " (
			  cost_id int(8) NOT NULL auto_increment,
			  description_short varchar(16) collate utf8_unicode_ci NOT NULL default '',
			  description_long varchar(64) collate utf8_unicode_ci NOT NULL default '',
			  cost_type varchar(3) collate utf8_unicode_ci default NULL,
			  inactive enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  PRIMARY KEY (cost_id),
			  KEY description_short (description_short)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_PROJECTS_PHASES => "CREATE TABLE " . TABLE_PROJECTS_PHASES . " (
			  phase_id int(8) NOT NULL auto_increment,
			  description_short varchar(16) collate utf8_unicode_ci NOT NULL default '',
			  description_long varchar(64) collate utf8_unicode_ci NOT NULL default '',
			  cost_type varchar(3) collate utf8_unicode_ci default NULL,
			  cost_breakdown enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  inactive enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  PRIMARY KEY (phase_id),
			  KEY description_short (description_short)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	    );
	    parent::__construct();
	}

	function install($path_my_files, $demo = false) {
	    global $db;
	    parent::install($path_my_files, $demo);
		if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_num')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_cust_id_num VARCHAR( 16 ) NOT NULL DEFAULT 'C10000';");
		if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_num')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_vend_id_num VARCHAR( 16 ) NOT NULL DEFAULT 'V10000';");
		if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_num'))  $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_crm_id_num VARCHAR( 16 ) NOT NULL DEFAULT '10000';");
		require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
		xtra_field_sync_list('contacts', TABLE_CONTACTS);
	}

  	function upgrade() {
    	global $db, $messageStack;
    	parent::upgrade();
    	if (MODULE_CONTACTS_STATUS < 3.3) {
	  		$db->Execute("ALTER TABLE " . TABLE_CONTACTS . " CHANGE short_name short_name VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
		  	if (db_table_exists(DB_PREFIX . 'contacts_extra_fields')) {
		    	// first create a new tab
				if (!defined('SETUP_TITLE_EXTRA_FIELDS')) define('SETUP_TITLE_EXTRA_FIELDS','New Tab');
		    	$updateDB = $db->Execute("insert into " . TABLE_EXTRA_TABS . " set 
			  	  module_id = 'contacts',
			  	  tab_name = '"    . SETUP_TITLE_EXTRA_FIELDS . "',
			  	  description = '" . SETUP_TITLE_EXTRA_FIELDS . "',
			  	  sort_order = '20'");
				$tab_id = db_insert_id();
		    	$result = $db->Execute("select * from " . DB_PREFIX . 'contacts_extra_fields');
		    	while (!$result->EOF) {
			  		$params = unserialize($result->fields['params']); // need to insert contact_type
			  		$params['contact_type'] = $result->fields['contact_type'];
		      		$updateDB = $db->Execute("insert into " . TABLE_EXTRA_FIELDS . " set 
			    	  module_id = 'contacts',
			    	  tab_id = '"      . $tab_id . "',
			    	  entry_type = '"  . $result->fields['entry_type']  . "',
			    	  field_name = '"  . $result->fields['field_name']  . "',
			    	  description = '" . $result->fields['description'] . "',
			    	  params = '"      . serialize($params) . "'");
		      		$result->MoveNext();
		    	}
		    	$db->Execute("DROP TABLE " . DB_PREFIX . "contacts_extra_fields");
		  	}
		  	xtra_field_sync_list('contacts', TABLE_CONTACTS);
		}
    	if (MODULE_CONTACTS_STATUS < 3.5) {
	  		if ( db_field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_desc");
	  		if ( db_field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_desc");
	  		if (!db_field_exists(TABLE_CONTACTS, 'attachments')) $db->Execute("ALTER TABLE " . TABLE_CONTACTS . " ADD attachments TEXT NOT NULL AFTER tax_id");
    	}
    	if (MODULE_CONTACTS_STATUS < 3.7) {
      		if (!db_field_exists(TABLE_CONTACTS_LOG, 'entered_by')) $db->Execute("ALTER TABLE " . TABLE_CONTACTS_LOG . " ADD entered_by INT(11) NOT NULL DEFAULT '0' AFTER contact_id");
    	}
		if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_num')){
    		$result = $db->Execute("Select MAX(short_name + 1) AS new  FROM " . TABLE_CONTACTS . " WHERE TYPE = 'i'");
			$db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_crm_id_num VARCHAR( 16 ) NOT NULL DEFAULT '".$result->fields['new']."';");
		}

  	}

	function delete() {
	    global $db;
	    parent::delete();
	    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_num'))  $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_num");
		if (db_field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_desc");
	    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_num'))  $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_num");
		if (db_field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_desc");
		if (db_field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_crm_id_desc");
		$db->Execute("delete from " . TABLE_EXTRA_FIELDS . " where module_id = 'contacts'");
		$db->Execute("delete from " . TABLE_EXTRA_TABS   . " where module_id = 'contacts'");
	}
	
	function load_reports() {
		$id = $this->add_report_heading(MENU_HEADING_CUSTOMERS,   'cust');
		$this->add_report_folder($id, TEXT_REPORTS,           'cust', 'fr');
		$id = $this->add_report_heading(MENU_HEADING_EMPLOYEES,   'hr');
		$this->add_report_folder($id, TEXT_REPORTS,           'hr',   'fr');
		$id = $this->add_report_heading(MENU_HEADING_VENDORS,     'vend');
		$this->add_report_folder($id, TEXT_REPORTS,           'vend', 'fr');
		parent::load_reports();
	}
	
	function load_demo() {
	    global $db;
		// Data for table `address_book`
		$db->Execute("TRUNCATE TABLE " . TABLE_ADDRESS_BOOK);
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (1, 1, 'vm', 'Obscure Video', '', '1354 Triple A Ave', '', 'Chatsworth', 'CA', '93245', 'USA', '800.345.5678', '', '', '', 'obsvid@obscurevideo.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (2, 2, 'cm', 'CompuHouse Computer Systems', '', '8086 Intel Ave', '', 'San jose', 'CA', '94354', 'USA', '800-555-1234', '', '', '', 'sales@compuhouse.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (3, 3, 'vm', 'Speedy Electronics, Inc.', '', '777 Lucky Street', 'Unit #2B', 'San Jose', 'CA', '92666', 'USA', '802-555-9876', '', '', '', 'custserv@speedyelec.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (4, 4, 'cm', 'Computer Repair Services', '', '12932 136th Ave.', 'Suite A', 'Denver', 'CO', '80021', 'USA', '303-555-5469', '', '', '', 'servive@comprepair.net', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (5, 5, 'vm', 'LCDisplays Corp.', '', '28973 Pixel Place', '', 'Los Angeles', 'CA', '90001', 'USA', '800-555-5548', '', '', '', 'cs@lcdisplays.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (6, 6, 'vm', 'Big Box Corp', '', '11 Square St', '', 'Longmont', 'CO', '80501', 'USA', '303-555-9652', '', '', '', 'big.box@yahoo.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (7, 7, 'cm', 'John Smith Jr.', '', '13546 Euclid Ave', '', 'Ontario', 'CA', '92775', 'USA', '818-555-1000', '', '', '', 'jsmith@aol.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (8, 8, 'cm', 'Jim Baker', '', '995 Maple Street', 'Unit #56', 'Northglenn', 'CO', '80234', 'USA', 'unlisted', '', '', '', 'jb@hotmail.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (9, 9, 'cm', 'Lisa Culver', '', '1005 Gillespie Dr', '', 'Boulder', 'CO', '80303', 'USA', '303-555-6677', '', '', '', 'lisa@myveryownemailaddress.net', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (10, 10, 'cm', 'Parts Locator LLC', '', '55 Sydney Hwy', '', 'Deerfield Beach', 'FL', '33445', 'USA', '215-555-0987', '', '', '', 'parts@partslocator.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (11, 11, 'vm', 'Accurate Input, LLC', '', '1111 Stuck Key Ave', '', 'Burbank', 'CA', '91505', 'USA', '800-555-1267', '', '818-555-5555', '', 'sales@accurate.com', 'www.AccurateInput.com', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (12, 12, 'vm', 'BackMeUp Systems, Inc', '', '1324 44th Ave.', '', 'New York', 'NY', '10019', 'USA', '212-555-9854', '', '', '', 'sales@backmeup.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (13, 13, 'vm', 'Closed Cases', 'Fernando', '23 Frontage Rd', '', 'New York', 'NY', '10019', 'USA', '888-555-6322', '800-555-5716', '', '', 'custserv@closedcases.net', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (14, 14, 'vm', 'MegaWatts Power Supplies', '', '11 Joules St.', '', 'Denver', 'CO', '80234', 'USA', '303-222-5617', '', '', '', 'help@hotmail.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (15, 15, 'vm', 'Slipped Disk Corp.', 'Accts. Receivable', '1234 Main St', 'Suite #1', 'La Verne', 'CA', '91750', 'USA', '714-555-0001', '', '', '', 'sales@slippedisks.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (16, 16, 'em', 'John Smith', '', '123 Birch Ave', 'Apt 12', 'Anytown', 'CO', '80234', 'USA', '303-555-3451', '', '', '', 'john@mycompany.com', '', '');");
		$db->Execute("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (17, 17, 'em', 'Mary Johnson', '', '6541 First St', '', 'Anytown', 'CO', '80234', 'USA', '303-555-7426', '', '', '', 'nary@mycomapny.com', '', '');");
		// Data for table `contacts`
		$db->Execute("TRUNCATE TABLE " . TABLE_CONTACTS);
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (1, 'v', 'Obscure Video', '0', '', '', '', '', '2000', '', '', '', '3:1:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (2, 'c', 'CompuHouse', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (3, 'v', 'Speedy Electronics', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (4, 'c', 'Computer Repair', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (5, 'v', 'LCDisplays', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (6, 'v', 'Big Box', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (7, 'c', 'Smith, John', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (8, 'c', 'JimBaker', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (9, 'c', 'Culver', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (10, 'c', 'PartsLocator', '0', '', '', '', '', '4000', '', '', '', '3:0:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (11, 'v', 'Accurate Input', '0', '', '', '', '', '2000', '', '', 'SK200706', '3:0:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (12, 'v', 'BackMeUp', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (13, 'v', 'Closed Cases', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (14, 'v', 'MegaWatts', '0', '', '', '', '', '2000', '', '', 'MW20070301', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (15, 'v', 'Slipped Disk', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (16, 'e', 'John', '0', 'John', '', 'Smith', '', 'b', '', 'Sales', '', '::::', '', '0', '', now(), NULL, NULL, NULL);");
		$db->Execute("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (17, 'e', 'Mary', '0', 'Mary', '', 'Johnson', '', 'e', '', 'Accounting', '', '::::', '', '0', '', now(), NULL, NULL, NULL);");
		// Data for table `departments`
		$db->Execute("TRUNCATE TABLE " . TABLE_DEPARTMENTS);
		$db->Execute("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('1', 'Sales', 'Sales', '0', '', 2, '0');");
		$db->Execute("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('2', 'Administration', 'Administration and Operations', '0', '', 1, '0');");
		$db->Execute("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('3', 'Accounting', 'Accounting and Finance', '0', '', 1, '0');");
		$db->Execute("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('4', 'Shipping', 'Shipping Operation', '0', '', 4, '0');");
		$db->Execute("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('5', 'Warehouse', 'Materials Receiving', '0', '', 4, '0');");
		// Data for table `departments_types`
		$db->Execute("TRUNCATE TABLE " . TABLE_DEPT_TYPES);
		$db->Execute("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (1, 'Administration');");
		$db->Execute("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (2, 'Sales and Marketing');");
		$db->Execute("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (3, 'Manufacturing');");
		$db->Execute("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (4, 'Shipping & Receiving');");
		parent::load_demo();
	}

}
?>