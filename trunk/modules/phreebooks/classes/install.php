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
//  Path: /modules/phreebooks/classes/install.php
//
class phreebooks_admin {
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables

  function __construct() {
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'phreedom'  => 3.6,
	  'contacts'  => 3.71,
	  'inventory' => 3.6,
	  'payment'   => 3.6,
	  'phreeform' => 3.6,
	);
	// Load configuration constants for this module, must match entries in admin tabs
    $this->keys = array(
	  'AUTO_UPDATE_PERIOD'             => '1',
	  'SHOW_FULL_GL_NAMES'             => '2',
	  'ROUND_TAX_BY_AUTH'              => '0',
	  'ENABLE_BAR_CODE_READERS'        => '0',
	  'SINGLE_LINE_ORDER_SCREEN'       => '1',
	  'ENABLE_ORDER_DISCOUNT'          => '0',
	  'ALLOW_NEGATIVE_INVENTORY'       => '1',
	  'AR_DEFAULT_GL_ACCT'             => '1100',
	  'AR_DEF_GL_SALES_ACCT'           => '4000',
	  'AR_SALES_RECEIPTS_ACCOUNT'      => '1020',
	  'AR_DISCOUNT_SALES_ACCOUNT'      => '4900',
	  'AR_DEF_FREIGHT_ACCT'            => '4300',
	  'AR_DEF_DEPOSIT_ACCT'            => '1020',
	  'AR_DEF_DEP_LIAB_ACCT'           => '2400',
	  'AR_USE_CREDIT_LIMIT'            => '1',
	  'AR_CREDIT_LIMIT_AMOUNT'         => '2500.00',
	  'APPLY_CUSTOMER_CREDIT_LIMIT'    => '0',
	  'AR_PREPAYMENT_DISCOUNT_PERCENT' => '0',
	  'AR_PREPAYMENT_DISCOUNT_DAYS'    => '0',
	  'AR_NUM_DAYS_DUE'                => '30',
	  'AR_AGING_HEADING_1'             => '0-30',
	  'AR_ACCOUNT_AGING_START'         => '0',
	  'AR_AGING_HEADING_2'             => '31-60',
	  'AR_AGING_PERIOD_1'              => '30',
	  'AR_AGING_HEADING_3'             => '61-90',
	  'AR_AGING_PERIOD_2'              => '60',
	  'AR_AGING_HEADING_4'             => 'Over 90',
	  'AR_AGING_PERIOD_3'              => '90',
	  'AR_CALCULATE_FINANCE_CHARGE'    => '0',
	  'AR_ADD_SALES_TAX_TO_SHIPPING'   => '0',
	  'AUTO_INC_CUST_ID'               => '0',
	  'AR_SHOW_CONTACT_STATUS'         => '0',
	  'AR_TAX_BEFORE_DISCOUNT'         => '0',
	  'AP_DEFAULT_INVENTORY_ACCOUNT'   => '1200',
	  'AP_DEFAULT_PURCHASE_ACCOUNT'    => '2000',
	  'AP_PURCHASE_INVOICE_ACCOUNT'    => '1020',
	  'AP_DEF_FREIGHT_ACCT'            => '6800',
	  'AP_DISCOUNT_PURCHASE_ACCOUNT'   => '2000',
	  'AP_DEF_DEPOSIT_ACCT'            => '1020',
	  'AP_DEF_DEP_LIAB_ACCT'           => '2400',
	  'AP_USE_CREDIT_LIMIT'            => '1',
	  'AP_CREDIT_LIMIT_AMOUNT'         => '5000.00',
	  'AP_PREPAYMENT_DISCOUNT_PERCENT' => '0',
	  'AP_PREPAYMENT_DISCOUNT_DAYS'    => '0',
	  'AP_NUM_DAYS_DUE'                => '30',
	  'AP_AGING_HEADING_1'             => '0-30',
	  'AP_AGING_START_DATE'            => '0',
	  'AP_AGING_HEADING_2'             => '31-60',
	  'AP_AGING_DATE_1'                => '30',
	  'AP_AGING_HEADING_3'             => '61-90',
	  'AP_AGING_DATE_2'                => '60',
	  'AP_AGING_HEADING_4'             => 'Over 90',
	  'AP_AGING_DATE_3'                => '90',
	  'AP_ADD_SALES_TAX_TO_SHIPPING'   => '0',
	  'AUTO_INC_VEND_ID'               => '0',
	  'AP_SHOW_CONTACT_STATUS'         => '0',
	  'AP_TAX_BEFORE_DISCOUNT'         => '0',
	);
	// add new directories to store images and data
	$this->dirlist = array(
	  'phreebooks',
	  'phreebooks/orders',
	);
	// Load tables
	$this->tables = array(
	  TABLE_ACCOUNTING_PERIODS => "CREATE TABLE " . TABLE_ACCOUNTING_PERIODS . " (
		  period int(11) NOT NULL default '0',
		  fiscal_year int(11) NOT NULL default '0',
		  start_date date NOT NULL default '0000-00-00',
		  end_date date NOT NULL default '0000-00-00',
		  date_added date NOT NULL default '0000-00-00',
		  last_update timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  PRIMARY KEY  (period)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_ACCOUNTS_HISTORY => "CREATE TABLE " . TABLE_ACCOUNTS_HISTORY . " (
		  id int(11) NOT NULL auto_increment,
		  ref_id int(11) NOT NULL default '0',
		  acct_id int(11) NOT NULL default '0',
		  amount double NOT NULL default '0',
		  journal_id int(2) NOT NULL default '0',
		  purchase_invoice_id char(24) default NULL,
		  so_po_ref_id int(11) default NULL,
		  post_date datetime default NULL,
		  PRIMARY KEY  (id),
		  KEY acct_id (acct_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_CHART_OF_ACCOUNTS => "CREATE TABLE " . TABLE_CHART_OF_ACCOUNTS . " (
		  id char(15) NOT NULL default '',
		  description char(64) NOT NULL default '',
		  heading_only enum('0','1') NOT NULL default '0',
		  primary_acct_id char(15) default NULL,
		  account_type tinyint(4) NOT NULL default '0',
		  account_inactive enum('0','1') NOT NULL default '0',
		  PRIMARY KEY (id),
		  KEY type (account_type),
		  KEY heading_only (heading_only)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_CHART_OF_ACCOUNTS_HISTORY => "CREATE TABLE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " (
		  id int(11) NOT NULL auto_increment,
		  period int(11) NOT NULL default '0',
		  account_id char(15) NOT NULL default '',
		  beginning_balance double NOT NULL default '0',
		  debit_amount double NOT NULL default '0',
		  credit_amount double NOT NULL default '0',
		  budget double NOT NULL default '0',
		  last_update date NOT NULL default '0000-00-00',
		  PRIMARY KEY  (id),
		  KEY period (period),
		  KEY account_id (account_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_JOURNAL_ITEM => "CREATE TABLE " . TABLE_JOURNAL_ITEM . " (
		  id int(11) NOT NULL auto_increment,
		  ref_id int(11) NOT NULL default '0',
		  item_cnt int(11) NOT NULL default '0',
		  so_po_item_ref_id int(11) default NULL,
		  gl_type char(3) NOT NULL default '',
		  reconciled int(2) NOT NULL default '0',
		  sku varchar(24) default NULL,
		  qty double NOT NULL default '0',
		  description varchar(255) default NULL,
		  debit_amount double default '0',
		  credit_amount double default '0',
		  gl_account varchar(15) NOT NULL default '',
		  taxable int(11) NOT NULL default '0',
		  full_price DOUBLE NOT NULL default '0',
		  serialize enum('0','1') NOT NULL default '0',
		  serialize_number varchar(24) default NULL,
		  project_id VARCHAR(16) default NULL,
		  purch_package_quantity double default NULL,
		  post_date date NOT NULL default '0000-00-00',
		  date_1 datetime NOT NULL default '0000-00-00 00:00:00',
		  PRIMARY KEY  (id),
		  KEY ref_id (ref_id),
		  KEY so_po_item_ref_id (so_po_item_ref_id),
		  KEY reconciled (reconciled)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_JOURNAL_MAIN => "CREATE TABLE " . TABLE_JOURNAL_MAIN . " (
		  id int(11) NOT NULL auto_increment,
		  period int(2) NOT NULL default '0',
		  journal_id int(2) NOT NULL default '0',
		  post_date date NOT NULL default '0000-00-00',
		  store_id int(11) default '0',
		  description varchar(32) default NULL,
		  closed enum('0','1') NOT NULL default '0',
		  closed_date date NOT NULL default '0000-00-00',
		  printed int(11) NOT NULL default '0',
		  freight double default '0',
		  discount double NOT NULL default '0',
		  shipper_code varchar(20) NOT NULL default '',
		  terms varchar(32) default '0',
		  sales_tax double NOT NULL default '0',
		  tax_auths varchar(16) NOT NULL default '0',
		  total_amount double NOT NULL default '0',
		  currencies_code char(3) NOT NULL DEFAULT '',
		  currencies_value DOUBLE NOT NULL DEFAULT '1.0',
		  so_po_ref_id int(11) NOT NULL default '0',
		  purchase_invoice_id varchar(24) default NULL,
		  purch_order_id varchar(24) default NULL,
		  recur_id int(11) default NULL,
		  admin_id int(11) NOT NULL default '0',
		  rep_id int(11) NOT NULL default '0',
		  waiting enum('0','1') NOT NULL default '0',
		  gl_acct_id varchar(15) default NULL,
		  bill_acct_id int(11) NOT NULL default '0',
		  bill_address_id int(11) NOT NULL default '0',
		  bill_primary_name varchar(32) default NULL,
		  bill_contact varchar(32) default NULL,
		  bill_address1 varchar(32) default NULL,
		  bill_address2 varchar(32) default NULL,
		  bill_city_town varchar(24) default NULL,
		  bill_state_province varchar(24) default NULL,
		  bill_postal_code varchar(10) default NULL,
		  bill_country_code char(3) default NULL,
		  bill_telephone1 varchar(20) default NULL,
		  bill_email varchar(48) default NULL,
		  ship_acct_id int(11) NOT NULL default '0',
		  ship_address_id int(11) NOT NULL default '0',
		  ship_primary_name varchar(32) default NULL,
		  ship_contact varchar(32) default NULL,
		  ship_address1 varchar(32) default NULL,
		  ship_address2 varchar(32) default NULL,
		  ship_city_town varchar(24) default NULL,
		  ship_state_province varchar(24) default NULL,
		  ship_postal_code varchar(24) default NULL,
		  ship_country_code char(3) default NULL,
		  ship_telephone1 varchar(20) default NULL,
		  ship_email varchar(48) default NULL,
		  terminal_date date default NULL,
		  drop_ship enum('0','1') NOT NULL default '0',
		  PRIMARY KEY  (id),
		  KEY period (period),
		  KEY journal_id (journal_id),
		  KEY post_date (post_date),
		  KEY closed (closed),
		  KEY bill_acct_id (bill_acct_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_TAX_AUTH => "CREATE TABLE " . TABLE_TAX_AUTH . " (
		  tax_auth_id int(3) NOT NULL auto_increment,
		  type varchar(1) NOT NULL DEFAULT 'c',
		  description_short char(15) NOT NULL default '',
		  description_long char(64) NOT NULL default '',
		  account_id char(15) NOT NULL default '',
		  vendor_id int(5) NOT NULL default '0',
		  tax_rate float NOT NULL default '0',
		  PRIMARY KEY  (tax_auth_id),
		  KEY description_short (description_short)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_TAX_RATES => "CREATE TABLE " . TABLE_TAX_RATES . " (
		  tax_rate_id int(3) NOT NULL auto_increment,
		  type varchar(1) NOT NULL DEFAULT 'c',
		  description_short varchar(15) NOT NULL default '',
		  description_long varchar(64) NOT NULL default '',
		  rate_accounts varchar(64) NOT NULL default '',
		  freight_taxable enum('0','1') NOT NULL default '0',
		  PRIMARY KEY  (tax_rate_id),
		  KEY description_short (description_short)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_RECONCILIATION => "CREATE TABLE " . TABLE_RECONCILIATION . " (
		  id int(11) NOT NULL auto_increment,
		  period int(11) NOT NULL default '0',
		  gl_account varchar(15) NOT NULL default '',
		  statement_balance double NOT NULL default '0',
		  cleared_items text,
		  PRIMARY KEY  (id),
		  KEY period (period)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;",
    );
  }

  function install($module, $demo = false) {
    global $db, $messageStack;
	$error = false;
	// load some current status values
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_po_num'))       $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_po_num VARCHAR( 16 ) NOT NULL DEFAULT '5000';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_so_num'))       $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_so_num VARCHAR( 16 ) NOT NULL DEFAULT '10000';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_inv_num'))      $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_inv_num VARCHAR( 16 ) NOT NULL DEFAULT '20000';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_check_num'))    $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_check_num VARCHAR( 16 ) NOT NULL DEFAULT '100';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_deposit_num'))  $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_deposit_num VARCHAR( 16 ) NOT NULL DEFAULT '';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_cm_num'))       $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_cm_num VARCHAR( 16 ) NOT NULL DEFAULT 'CM1000';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_vcm_num'))      $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_vcm_num VARCHAR( 16 ) NOT NULL DEFAULT 'VCM1000';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_ap_quote_num')) $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_ap_quote_num VARCHAR( 16 ) NOT NULL DEFAULT 'RFQ1000';");
	if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_ar_quote_num')) $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_ar_quote_num VARCHAR( 16 ) NOT NULL DEFAULT 'QU1000';");
	// copy standard images to phreeform images directory
	$dir_source = DIR_FS_MODULES  . 'phreebooks/images/';
	$dir_dest   = DIR_FS_MY_FILES . $_SESSION['company'] . '/phreeform/images/';
	@copy($dir_source . 'phreebooks_logo.jpg', $dir_dest . 'phreebooks_logo.jpg');
	@copy($dir_source . 'phreebooks_logo.png', $dir_dest . 'phreebooks_logo.png');
	$this->notes[] = MODULE_PHREEBOOKS_NOTES_1;
	$this->notes[] = MODULE_PHREEBOOKS_NOTES_2;
	$this->notes[] = MODULE_PHREEBOOKS_NOTES_3;
	$this->notes[] = MODULE_PHREEBOOKS_NOTES_5;
	$this->notes[] = MODULE_PHREEBOOKS_NOTES_4;
    return $error;
  }

  function initialize($module) {
  	if (AUTO_UPDATE_PERIOD) {
	  require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
	  gen_auto_update_period();
	}
  }

  function update($module) {
    global $db, $messageStack;
	$error = false;
	$db_version = defined('MODULE_PHREEBOOKS_STATUS') ? MODULE_PHREEBOOKS_STATUS : false;
	if (!$db_version || $db_version < 2.1 || $db_version < '2.1') { // For PhreeBooks release 2.1 or lower to update to Phreedom structure
	  require(DIR_FS_MODULES . 'phreebooks/functions/updater.php');
	  if(db_table_exists(TABLE_PROJECT_VERSION)){
		  $result = $db->Execute("select * from " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'PhreeBooks Database'");
		  $db_version = $result->fields['project_version_major'] . '.' . $result->fields['project_version_minor'];
		  if ($db_version < 2.1) $error = execute_upgrade($db_version);
		  $db_version = 2.1;
	  }
	}
	if ($db_version == 2.1 || $db_version == '2.1') {
	  $db_version = $this->release_update($module, 3.0, DIR_FS_MODULES . 'phreebooks/updates/R21toR30.php');
	  if (!$db_version) return true;
      // remove table project_version, no longer needed
      if (!$error) $db->Execute("DROP TABLE " . TABLE_PROJECT_VERSION);
	}
	if ($db_version == 3.0 || $db_version == '3.0') {
	  $db_version = $this->release_update($module, 3.1, DIR_FS_MODULES . 'phreebooks/updates/R30toR31.php');
	  if (!$db_version) return true;
	}
	if ($db_version == 3.1 || $db_version == '3.1') {
	  if (!file_exists($path . $dir)) mkdir(DIR_FS_MY_FILES . $_SESSION['company'] . '/phreebooks/orders/', 0755, true);
	  write_configure('ALLOW_NEGATIVE_INVENTORY', '1');
	  $db_version = 3.2;
	}
  	if ($db_version == 3.2 || $db_version == '3.2') {
	  write_configure('APPLY_CUSTOMER_CREDIT_LIMIT', '0'); // flag for using credit limit to authorize orders
	  $db->Execute("ALTER TABLE ".TABLE_JOURNAL_MAIN." CHANGE shipper_code shipper_code VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
	  require_once(DIR_FS_MODULES . 'phreebooks/defaults.php');
	  if (is_array(glob(DIR_FS_ADMIN.'PHREEBOOKS_DIR_MY_ORDERS*.zip'))) {
	  	foreach (glob(DIR_FS_ADMIN.'PHREEBOOKS_DIR_MY_ORDERS*.zip') as $file) {
	  	  $newfile = str_replace('PHREEBOOKS_DIR_MY_ORDERS', '', $file);
	  	  $newfile = str_replace(DIR_FS_ADMIN, '', $newfile);
	  	  rename($file,PHREEBOOKS_DIR_MY_ORDERS.$newfile);
	    }
	  }
	  $db_version = 3.3;
	}
	if ($db_version < 3.4 || $db_version < '3.4') {
	  if (!db_field_exists(TABLE_JOURNAL_ITEM, 'item_cnt')) $db->Execute("ALTER TABLE ".TABLE_JOURNAL_ITEM." ADD item_cnt INT(11) NOT NULL DEFAULT '0' AFTER ref_id");
	  $db_version = 3.4;
	}
	if ($db_version < 3.51 || $db_version < '3.51') {
		$result = $db->Execute("SELECT id, so_po_ref_id FROM ".TABLE_JOURNAL_MAIN." WHERE journal_id = 16 AND so_po_ref_id > 0");
		while(!$result->EOF) { // to fix transfers to store 0 from any other store
			if ($result->fields['so_po_ref_id'] > $result->fields['id']) {
				$db->Execute("UPDATE ".TABLE_JOURNAL_MAIN." SET so_po_ref_id = -1 WHERE id=".$result->fields['id']);
			}
			$result->MoveNext();
		}
		if (!db_field_exists(TABLE_JOURNAL_ITEM, 'purch_package_quantity')) $db->Execute("ALTER TABLE ".TABLE_JOURNAL_ITEM." ADD purch_package_quantity float default NULL AFTER project_id");
	}
	if (MODULE_PHREEBOOKS_STATUS < 3.7) {
		$db->Execute("ALTER TABLE ".TABLE_JOURNAL_ITEM."
			CHANGE qty qty DOUBLE NOT NULL DEFAULT '0',
			CHANGE purch_package_quantity purch_package_quantity DOUBLE NOT NULL DEFAULT '0'");
	}
	if (!$error) {
	  write_configure('MODULE_'.strtoupper($module).'_STATUS', constant('MODULE_'.strtoupper($module).'_VERSION'));
   	  $messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_'.strtoupper($module).'_VERSION')), 'success');
	}
	return $error;
  }

  function remove($module) {
    global $db;
	$error = false;
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_po_num'))       $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_po_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_so_num'))       $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_so_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_inv_num'))      $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_inv_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_check_num'))    $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_check_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_deposit_num'))  $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_deposit_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_cm_num'))       $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_cm_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_vcm_num'))      $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_vcm_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_ap_quote_num')) $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_ap_quote_num");
    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_ar_quote_num')) $db->Execute("ALTER TABLE ".TABLE_CURRENT_STATUS." DROP next_ar_quote_num");
    return $error;
  }

  function release_update($module, $version, $path = '') {
    global $db, $messageStack;
	$error = false;
	if (file_exists($path)) { include_once ($path); }
	write_configure('MODULE_' . strtoupper($module) . '_STATUS', $version);
	return $error ? false : $version;
  }

  function load_reports($module) {
	$error = false;
	$id = admin_add_report_heading(MENU_HEADING_CUSTOMERS,   'cust');
	if (admin_add_report_folder($id, TEXT_REPORTS,           'cust',      'fr')) $error = true;
	if (admin_add_report_folder($id, PB_PF_CUST_QUOTE,       'cust:quot', 'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_SALES_ORDER,      'cust:so',   'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_INV_PKG_SLIP,     'cust:inv',  'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_CUST_CRD_MEMO,    'cust:cm',   'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_CUST_STATEMENT,   'cust:stmt', 'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_COLLECT_LTR,      'cust:col',  'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_CUST_LABEL,       'cust:lblc', 'ff')) $error = true;
	$id = admin_add_report_heading(MENU_HEADING_VENDORS,     'vend');
	if (admin_add_report_folder($id, TEXT_REPORTS,           'vend',      'fr')) $error = true;
	if (admin_add_report_folder($id, PB_PF_VENDOR_QUOTE,     'vend:quot', 'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_PURCH_ORDER,      'vend:po',   'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_VENDOR_CRD_MEMO,  'vend:cm',   'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_VENDOR_LABEL,     'vend:lblv', 'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_VENDOR_STATEMENT, 'vend:stmt', 'ff')) $error = true;
	$id = admin_add_report_heading(MENU_HEADING_BANKING,     'bnk');
	if (admin_add_report_folder($id, TEXT_REPORTS,           'bnk',       'fr')) $error = true;
	if (admin_add_report_folder($id, PB_PF_DEP_SLIP,         'bnk:deps',  'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_BANK_CHECK,       'bnk:chk',   'ff')) $error = true;
	if (admin_add_report_folder($id, PB_PF_SALES_REC,        'bnk:rcpt',  'ff')) $error = true;
	$id = admin_add_report_heading(MENU_HEADING_GL,          'gl');
	if (admin_add_report_folder($id, TEXT_REPORTS,           'gl',        'fr')) $error = true;
	return $error;
  }

  function load_demo() {
    global $db;
	$error = false;
	// Data for table tax_authorities
	$db->Execute("TRUNCATE TABLE " . TABLE_TAX_AUTH);
	$db->Execute("INSERT INTO " . TABLE_TAX_AUTH . " VALUES (1, 'c', 'City Tax', 'City Tax on Taxable Items', '2312', 0, 2.5);");
	$db->Execute("INSERT INTO " . TABLE_TAX_AUTH . " VALUES (2, 'c', 'State Tax', 'State Sales Tax Payable', '2316', 0, 5.1);");
	$db->Execute("INSERT INTO " . TABLE_TAX_AUTH . " VALUES (3, 'c', 'Special Dist', 'Special District Tax (RTD, etc)', '2316', 0, 1.1);");
	// Data for table tax_rates
	$db->Execute("TRUNCATE TABLE " . TABLE_TAX_RATES);
	$db->Execute("INSERT INTO " . TABLE_TAX_RATES . " VALUES (1, 'c', 'Local Tax', 'Local POS Tax', '1:2:3', '0');");
	$db->Execute("INSERT INTO " . TABLE_TAX_RATES . " VALUES (2, 'c', 'State Only', 'State Only Tax - Shipments', '2', '0');");
	return $error;
  }

}
?>