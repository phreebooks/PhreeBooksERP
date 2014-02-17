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
//  Path: /modules/inventory/classes/install.php
//
class inventory_admin {
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	
  function __construct() {
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'contacts'   => 3.71,
	  'phreedom'   => 3.6,
	  'phreebooks' => 3.6,
	);
	// Load configuration constants for this module, must match entries in admin tabs
    $this->keys = array(
	  'INV_STOCK_DEFAULT_SALES'            => '4000',
	  'INV_STOCK_DEFAULT_INVENTORY'        => '1200',
	  'INV_STOCK_DEFAULT_COS'              => '5000',
	  'INV_STOCK_DEFAULT_COSTING'          => 'f',
	  'INV_MASTER_STOCK_DEFAULT_SALES'     => '4000',
	  'INV_MASTER_STOCK_DEFAULT_INVENTORY' => '1200',
	  'INV_MASTER_STOCK_DEFAULT_COS'       => '5000',
	  'INV_MASTER_STOCK_DEFAULT_COSTING'   => 'f',
	  'INV_ASSY_DEFAULT_SALES'             => '4000',
	  'INV_ASSY_DEFAULT_INVENTORY'         => '1200',
	  'INV_ASSY_DEFAULT_COS'               => '5000',
	  'INV_ASSY_DEFAULT_COSTING'           => 'f',
	  'INV_SERIALIZE_DEFAULT_SALES'        => '4000',
	  'INV_SERIALIZE_DEFAULT_INVENTORY'    => '1200',
	  'INV_SERIALIZE_DEFAULT_COS'          => '5000',
	  'INV_SERIALIZE_DEFAULT_COSTING'      => 'f',
	  'INV_NON_STOCK_DEFAULT_SALES'        => '4000',
	  'INV_NON_STOCK_DEFAULT_INVENTORY'    => '1200',
	  'INV_NON_STOCK_DEFAULT_COS'          => '5000',
	  'INV_SERVICE_DEFAULT_SALES'          => '4000',
	  'INV_SERVICE_DEFAULT_INVENTORY'      => '1200',
	  'INV_SERVICE_DEFAULT_COS'            => '5000',
	  'INV_LABOR_DEFAULT_SALES'            => '4000',
	  'INV_LABOR_DEFAULT_INVENTORY'        => '1200',
	  'INV_LABOR_DEFAULT_COS'              => '5000',
	  'INV_ACTIVITY_DEFAULT_SALES'         => '4000',
	  'INV_CHARGE_DEFAULT_SALES'           => '4000',
	  'INVENTORY_DEFAULT_TAX'              => '0',
	  'INVENTORY_DEFAULT_PURCH_TAX'        => '0',
	  'INVENTORY_AUTO_ADD'                 => '0',
	  'INVENTORY_AUTO_FILL'                => '0',
	  'ORD_ENABLE_LINE_ITEM_BAR_CODE'      => '0',
	  'ORD_BAR_CODE_LENGTH'                => '12',
	  'ENABLE_AUTO_ITEM_COST'              => '0',
	);
	// add new directories to store images and data
	$this->dirlist = array(
	  'inventory',
	  'inventory/images',
	  'inventory/attachments',
	);
	// Load tables
	$this->tables = array(
	  TABLE_INVENTORY => "CREATE TABLE " . TABLE_INVENTORY . " (
		  id int(11) NOT NULL auto_increment,
		  sku varchar(24) NOT NULL default '',
		  inactive enum('0','1') NOT NULL default '0',
		  inventory_type char(2) NOT NULL default 'si',
		  description_short varchar(32) NOT NULL default '',
		  description_purchase varchar(255) default NULL,
		  description_sales varchar(255) default NULL,
		  image_with_path varchar(255) default NULL,
		  account_sales_income varchar(15) default NULL,
		  account_inventory_wage varchar(15) default '',
		  account_cost_of_sales varchar(15) default NULL,
		  item_taxable int(11) NOT NULL default '0',
		  purch_taxable int(11) NOT NULL default '0',
		  item_cost float NOT NULL default '0',
		  cost_method enum('a','f','l') NOT NULL default 'f',
		  price_sheet varchar(32) default NULL,
		  price_sheet_v varchar(32) default NULL,
		  full_price float NOT NULL default '0',
		  full_price_with_tax float NOT NULL default '0',
		  margin float NOT NULL default '0',
		  item_weight float NOT NULL default '0',
		  quantity_on_hand float NOT NULL default '0',
		  quantity_on_order float NOT NULL default '0',
		  quantity_on_sales_order float NOT NULL default '0',
		  quantity_on_allocation float NOT NULL default '0',
		  minimum_stock_level float NOT NULL default '0',
		  reorder_quantity float NOT NULL default '0',
		  vendor_id int(11) NOT NULL default '0',
		  lead_time int(3) NOT NULL default '1',
		  upc_code varchar(13) NOT NULL DEFAULT '',
		  serialize enum('0','1') NOT NULL default '0',
		  creation_date datetime NOT NULL default '0000-00-00 00:00:00',
		  last_update datetime NOT NULL default '0000-00-00 00:00:00',
		  last_journal_date datetime NOT NULL default '0000-00-00 00:00:00',
		  attachments text,
		  PRIMARY KEY (id),
		  INDEX (sku)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_ASSY_LIST => "CREATE TABLE " . TABLE_INVENTORY_ASSY_LIST . " (
		  id int(11) NOT NULL auto_increment,
		  ref_id int(11) NOT NULL default '0',
		  sku varchar(24) NOT NULL default '',
		  description varchar(32) NOT NULL default '',
		  qty float NOT NULL default '0',
		  PRIMARY KEY (id),
		  KEY ref_id (ref_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_COGS_OWED => "CREATE TABLE " . TABLE_INVENTORY_COGS_OWED . " (
		  id int(11) NOT NULL auto_increment,
		  journal_main_id int(11) NOT NULL default '0',
		  store_id int(11) NOT NULL default '0',
		  sku varchar(24) NOT NULL default '',
		  qty float NOT NULL default '0',
		  post_date date NOT NULL default '0000-00-00',
		  PRIMARY KEY (id),
		  KEY sku (sku),
		  INDEX (store_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_COGS_USAGE => "CREATE TABLE " . TABLE_INVENTORY_COGS_USAGE . " (
		  id int(11) NOT NULL auto_increment,
		  journal_main_id int(11) NOT NULL default '0',
		  qty float NOT NULL default '0',
		  inventory_history_id int(11) NOT NULL default '0',
		  PRIMARY KEY (id),
		  INDEX (journal_main_id, inventory_history_id) 
		) ENGINE=innodb DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_HISTORY => "CREATE TABLE " . TABLE_INVENTORY_HISTORY . " (
		  id int(11) NOT NULL auto_increment,
		  ref_id int(11) NOT NULL default '0',
		  store_id int(11) NOT NULL default '0',
		  journal_id int(2) NOT NULL default '6',
		  sku varchar(24) NOT NULL default '',
		  qty float NOT NULL default '0',
		  serialize_number varchar(24) NOT NULL default '',
		  remaining float NOT NULL default '0',
		  unit_cost float NOT NULL default '0',
		  avg_cost float NOT NULL default '0',
	  	  post_date datetime default NULL,
		  PRIMARY KEY (id),
		  KEY sku (sku),
		  KEY ref_id (ref_id),
		  KEY remaining (remaining),
		  INDEX (store_id),
		  INDEX (journal_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_INVENTORY_MS_LIST => "CREATE TABLE " . TABLE_INVENTORY_MS_LIST . " (
		  id int(11) NOT NULL auto_increment,
		  sku varchar(24) NOT NULL default '',
		  attr_name_0 varchar(16) NULL,
		  attr_name_1 varchar(16) NULL,
		  attr_0 varchar(255) NULL,
		  attr_1 varchar(255) NULL, 
		  PRIMARY KEY (id)
		) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_INVENTORY_PURCHASE => "CREATE TABLE " . TABLE_INVENTORY_PURCHASE . " (
		  id int(11) NOT NULL auto_increment,
		  sku varchar(24) NOT NULL default '',
		  vendor_id int(11) NOT NULL default '0',
		  description_purchase varchar(255) default NULL,
		  purch_package_quantity float NOT NULL default '1',
		  purch_taxable int(11) NOT NULL default '0',
		  item_cost float NOT NULL default '0', 
		  price_sheet_v varchar(32) default NULL,
		  PRIMARY KEY (id),
		  INDEX (sku)
		) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	  TABLE_INVENTORY_SPECIAL_PRICES => "CREATE TABLE " . TABLE_INVENTORY_SPECIAL_PRICES . " (
		  id int(11) NOT NULL auto_increment,
		  inventory_id int(11) NOT NULL default '0',
		  price_sheet_id int(11) NOT NULL default '0',
		  price_levels varchar(255) NOT NULL default '',
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	  TABLE_PRICE_SHEETS => "CREATE TABLE " . TABLE_PRICE_SHEETS . " (
		  id int(11) NOT NULL auto_increment,
		  sheet_name varchar(32) NOT NULL default '',
		  type char(1) NOT NULL default 'c',
		  inactive enum('0','1') NOT NULL default '0',
		  revision float NOT NULL default '0',
		  effective_date date NOT NULL default '0000-00-00',
		  expiration_date date default NULL,
		  default_sheet enum('0','1') NOT NULL default '0',
		  default_levels varchar(255) NOT NULL default '',
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
    );
  }

  function install($module) {
	global $db;
	$error = false;
	$this->notes[] = MODULE_INVENTORY_NOTES_1;
	require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
	xtra_field_sync_list('inventory', TABLE_INVENTORY);
	$result = $db->Execute("select * from " . TABLE_EXTRA_FIELDS ." where module_id = 'inventory' and tab_id = '0'");
	while (!$result->EOF) {
		$temp = unserialize($result->fields['params']);
		switch($result->fields['field_name']){
			case 'serialize':
				$temp['inventory_type'] = 'sa:sr';
				break;
			case 'account_sales_income':
			case 'item_taxable':
			case 'purch_taxable':
			case 'item_cost':
			case 'price_sheet':
			case 'price_sheet_v':
			case 'full_price':
			case 'full_price_with_tax':
			case 'product_margin':
				$temp['inventory_type'] = 'ci:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
				break;
			case 'image_with_path':
				$temp['inventory_type'] = 'ia:ma:mb:mi:ms:ns:sa:si:sr';
				break;
			case 'account_inventory_wage':
			case 'account_cost_of_sales':
				$temp['inventory_type'] = 'ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
				break;
			case 'cost_method':
				$temp['inventory_type'] = 'ia:ma:mb:mi:ms:ns:si';
				break;
			case 'item_weight':
				$temp['inventory_type'] = 'ia:ma:mb:mi:ms:ns:sa:si:sr';
				break;
			case 'quantity_on_hand':
			case 'minimum_stock_level':
			case 'reorder_quantity':
				$temp['inventory_type'] = 'ia:ma:mi:ns:sa:si:sr';
				break;
			case 'quantity_on_order':
	  		case 'quantity_on_allocation':
				$temp['inventory_type'] = 'ia:mi:sa:si:sr';
				break;
			case 'quantity_on_sales_order':
				$temp['inventory_type'] = 'ia:ma:mi:sa:si:sr';
				break;
			case 'lead_time':
				$temp['inventory_type'] = 'ai:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
				break;
			case 'upc_code':
				$temp['inventory_type'] = 'ia:ma:mi:ns:sa:si:sr';
				break;
			default:
				$temp['inventory_type'] = 'ai:ci:ds:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
		}
		$updateDB = $db->Execute("update " . TABLE_EXTRA_FIELDS . " set params = '" . serialize($temp) . "' where id = '".$result->fields['id']."'");
		$result->MoveNext();
	}
	// set the fields to view in the inventory field filters 
	$haystack = array('attachments', 'account_sales_income', 'item_taxable', 'purch_taxable', 'image_with_path', 'account_inventory_wage', 'account_cost_of_sales', 'cost_method', 'lead_time');
	$result = $db->Execute("update " . TABLE_EXTRA_FIELDS . " set entry_type='check_box' where field_name='inactive'");
	$result = $db->Execute("select * from " . TABLE_EXTRA_FIELDS ." where module_id = 'inventory'");
	while (!$result->EOF) {
		$use_in_inventory_filter = '1';
		if(in_array($result->fields['field_name'], $haystack)) $use_in_inventory_filter = '0';
		$updateDB = $db->Execute("update " . TABLE_EXTRA_FIELDS . " set use_in_inventory_filter = '".$use_in_inventory_filter."' where id = '".$result->fields['id']."'");
		$result->MoveNext();
	}
	
    return $error;
  }

  function initialize($module) {
  }

  function update($module) {
    global $db, $messageStack, $currencies;
	$error = false;
    if (MODULE_INVENTORY_STATUS < 3.1) {
	  $tab_map = array('0' => '0');
	  if(db_table_exists(DB_PREFIX . 'inventory_categories')){
		  $result = $db->Execute("select * from " . DB_PREFIX . 'inventory_categories');
		  while (!$result->EOF) {
		    $updateDB = $db->Execute("insert into " . TABLE_EXTRA_TABS . " set 
			  module_id = 'inventory',
			  tab_name = '"    . $result->fields['category_name']        . "',
			  description = '" . $result->fields['category_description'] . "',
			  sort_order = '"  . $result->fields['sort_order']           . "'");
		    $tab_map[$result->fields['category_id']] = db_insert_id();
		    $result->MoveNext();
		  }
		  $db->Execute("DROP TABLE " . DB_PREFIX . "inventory_categories");
	  }
	  if(db_table_exists(DB_PREFIX . 'inventory_categories')){
		  $result = $db->Execute("select * from " . DB_PREFIX . 'inventory_fields');
		  while (!$result->EOF) {
		    $updateDB = $db->Execute("insert into " . TABLE_EXTRA_FIELDS . " set 
			  module_id = 'inventory',
			  tab_id = '"      . $tab_map[$result->fields['category_id']] . "',
			  entry_type = '"  . $result->fields['entry_type']  . "',
			  field_name = '"  . $result->fields['field_name']  . "',
			  description = '" . $result->fields['description'] . "',
			  params = '"      . $result->fields['params']      . "'");
		    $result->MoveNext();
		  }
		  $db->Execute("DROP TABLE " . DB_PREFIX . "inventory_fields");
	  }
	  xtra_field_sync_list('inventory', TABLE_INVENTORY);
	}
    if (MODULE_INVENTORY_STATUS < 3.2) {
	  if (!db_field_exists(TABLE_PRICE_SHEETS, 'type')) $db->Execute("ALTER TABLE " . TABLE_PRICE_SHEETS . " ADD type char(1) NOT NULL default 'c' AFTER sheet_name");
	  if (!db_field_exists(TABLE_INVENTORY, 'price_sheet_v')) $db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD price_sheet_v varchar(32) default NULL AFTER price_sheet");
	  xtra_field_sync_list('inventory', TABLE_INVENTORY);
	}
	if (MODULE_INVENTORY_STATUS < 3.6) {
		$db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD INDEX ( `sku` )"); 
		if (!db_field_exists(TABLE_INVENTORY, 'attachments')) $db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD attachments text AFTER last_journal_date");
		if (!db_field_exists(TABLE_INVENTORY, 'full_price_with_tax')) $db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD full_price_with_tax FLOAT NOT NULL DEFAULT '0' AFTER full_price");
		if (!db_field_exists(TABLE_INVENTORY, 'product_margin')) $db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD product_margin FLOAT NOT NULL DEFAULT '0' AFTER full_price_with_tax");
		if (!db_field_exists(TABLE_EXTRA_FIELDS , 'use_in_inventory_filter')) $db->Execute("ALTER TABLE " . TABLE_EXTRA_FIELDS . " ADD use_in_inventory_filter ENUM( '0', '1' ) NOT NULL DEFAULT '0'");
		$db->Execute("alter table " . TABLE_INVENTORY . " CHANGE `inactive` `inactive` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
		xtra_field_sync_list('inventory', TABLE_INVENTORY);
		$db->Execute("update " . TABLE_INVENTORY . " set inventory_type = 'ma' where inventory_type = 'as'");
		$result = $db->Execute("select * from " . TABLE_EXTRA_FIELDS ." where module_id = 'inventory'"); 
		while (!$result->EOF) {
			$temp = unserialize($result->fields['params']);
			switch($result->fields['field_name']){
				case 'serialize':
					$temp['inventory_type'] = 'sa:sr'; 	
					break;
				case 'account_sales_income':
				case 'item_taxable':
		  		case 'purch_taxable':
		  		case 'item_cost':
		  		case 'price_sheet':
		  		case 'price_sheet_v':
		  		case 'full_price':
		  		case 'full_price_with_tax':
		  		case 'product_margin':
					$temp['inventory_type'] = 'ci:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
		  			break;
		  		case 'image_with_path':
		  			$temp['inventory_type'] = 'ia:ma:mb:mi:ms:ns:sa:si:sr';
		  			break;
		  		case 'account_inventory_wage':
		  		case 'account_cost_of_sales':
		  			$temp['inventory_type'] = 'ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
		  			break;
		  		case 'cost_method':
		  			$temp['inventory_type'] = 'ia:ma:mb:mi:ms:ns:si';
		  			break;
		  		case 'item_weight':
		  			$temp['inventory_type'] = 'ia:ma:mb:mi:ms:ns:sa:si:sr';
		  			break;
		  		case 'quantity_on_hand':
		  		case 'minimum_stock_level':
		  		case 'reorder_quantity':
		  			$temp['inventory_type'] = 'ia:ma:mi:ns:sa:si:sr';
		  			break;
		  		case 'quantity_on_order':
		  		case 'quantity_on_allocation':
		  			$temp['inventory_type'] = 'ia:mi:sa:si:sr';
		  			break;
		  		case 'quantity_on_sales_order':
		  			$temp['inventory_type'] = 'ia:ma:mi:sa:si:sr';
		  			break;
		  		case 'lead_time':
		  			$temp['inventory_type'] = 'ai:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
		  			break;
		  		case 'upc_code':
		  			$temp['inventory_type'] = 'ia:ma:mi:ns:sa:si:sr';
		  			break; 
		  		default:
		  			$temp['inventory_type'] = 'ai:ci:ds:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
			}
	    	$updateDB = $db->Execute("update " . TABLE_EXTRA_FIELDS . " set params = '" . serialize($temp) . "' where id = '".$result->fields['id']."'");
	    	$result->MoveNext();
	  	}
	  	$haystack = array('attachments', 'account_sales_income', 'item_taxable', 'purch_taxable', 'image_with_path', 'account_inventory_wage', 'account_cost_of_sales', 'cost_method', 'lead_time');
	  	$result = $db->Execute("update " . TABLE_EXTRA_FIELDS . " set entry_type = 'check_box' where field_name = 'inactive'");
	  	$result = $db->Execute("select * from " . TABLE_EXTRA_FIELDS ." where module_id = 'inventory'");
	  	while (!$result->EOF) {
	  		$use_in_inventory_filter = '1';
			if(in_array($result->fields['field_name'], $haystack)) $use_in_inventory_filter = '0';
			$updateDB = $db->Execute("update " . TABLE_EXTRA_FIELDS . " set use_in_inventory_filter = '".$use_in_inventory_filter."' where id = '".$result->fields['id']."'");
			$result->MoveNext();
	  	}
		if(!db_table_exists(TABLE_INVENTORY_PURCHASE)){ 
			foreach ($this->tables as $table => $sql) {
				if ($table == TABLE_INVENTORY_PURCHASE) admin_install_tables(array($table => $sql));
	  		}
		  	if (db_field_exists(TABLE_INVENTORY, 'purch_package_quantity')){
	  			$result = $db->Execute("insert into ".TABLE_INVENTORY_PURCHASE." ( sku, vendor_id, description_purchase, purch_package_quantity, purch_taxable, item_cost, price_sheet_v ) select sku, vendor_id, description_purchase, purch_package_quantity, purch_taxable, item_cost, price_sheet_v  from " . TABLE_INVENTORY);
	  			$db->Execute("ALTER TABLE " . TABLE_INVENTORY . " DROP `purch_package_quantity`");
	  		}else{
	  			$result = $db->Execute("insert into ".TABLE_INVENTORY_PURCHASE." ( sku, vendor_id, description_purchase, purch_package_quantity, purch_taxable, item_cost, price_sheet_v ) select sku, vendor_id, description_purchase, 1, purch_taxable, item_cost, price_sheet_v  from " . TABLE_INVENTORY);
	  		}
		}
		require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
		$tax_rates = ord_calculate_tax_drop_down('c');
		$result = $db->Execute("SELECT id, item_taxable, full_price, item_cost FROM ".TABLE_INVENTORY);
		while(!$result->EOF){
			$sql_data_array = array();
			$sql_data_array['full_price_with_tax'] = round((1 +($tax_rates[$result->fields['item_taxable']]['rate']/100))  * $result->fields['full_price'], $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
			if($result->fields['item_cost'] <> '' && $result->fields['item_cost'] > 0) $sql_data_array['product_margin'] = round($sql_data_array['full_price_with_tax'] / $result->fields['item_cost'], $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
			db_perform(TABLE_INVENTORY, $sql_data_array, 'update', "id = " . $result->fields['id']);
			$result->MoveNext();
		}
	  	mkdir(DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/attachments/', 0755, true);
	}
	if (MODULE_INVENTORY_STATUS < 3.7) {
		if (!db_field_exists(TABLE_INVENTORY_HISTORY, 'avg_cost')) $db->Execute("ALTER TABLE ".TABLE_INVENTORY_HISTORY." ADD avg_cost FLOAT NOT NULL DEFAULT '0' AFTER unit_cost");
	}
	if (!$error) {
		xtra_field_sync_list('inventory', TABLE_INVENTORY);
	  	write_configure('MODULE_' . strtoupper($module) . '_STATUS', constant('MODULE_' . strtoupper($module) . '_VERSION'));
   	  	$messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_' . strtoupper($module) . '_VERSION')), 'success');
	}
	return $error;
  }

  function remove($module) {
    global $db, $messageStack;
	$error = false;
	$db->Execute("delete from " . TABLE_EXTRA_FIELDS . " where module_id = 'inventory'");
	$db->Execute("delete from " . TABLE_EXTRA_TABS   . " where module_id = 'inventory'");
	return $error;
  }

  function load_reports($module) {
	$error = false;
	$id    = admin_add_report_heading(MENU_HEADING_INVENTORY, 'inv');
	if (admin_add_report_folder($id, TEXT_REPORTS, 'inv', 'fr')) $error = true;
	return $error;
  }

  function load_demo() {
    global $db;
	$error = false;
	// Data for table `inventory`
	$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY);
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (1, 'AMD-3600-CPU', '0', 'si', 'AMD 3600+ Athlon CPU', 'AMD 3600+ Athlon CPU', 'AMD 3600+ Athlon CPU', 'demo/athlon.jpg', '4000', '1200', '5000', '1', '0', 100, 'f', '', '', 150, 150, 1.5, 1, 0, 0, 0, 0, 0, 0, 3, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (2, 'ASSY-BB', '0', 'lb', 'Labor - BB Computer Assy', 'Labor Cost - Assemble Bare Bones Computer', 'Labor - BB Computer Assy', '', '4000', '6000', '5000', '1', '0', 25, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (3, 'BOX-TW-322', '0', 'ns', 'TW-322 Shipping Box', 'TW-322 Shipping Box - 12 x 12 x 12', 'TW-322 Shipping Box', '', '4000', '6800', '5000', '1', '0', 1.35, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 15, 25, 0, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (4, 'BOX-TW-553', '0', 'ns', 'TW-533 Shipping Box', 'TW-533 Shipping Box - 24 x 12 x 12', 'TW-533 Shipping Box', '', '4000', '6800', '5000', '1', '0', 1.75, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (5, 'CASE-ALIEN', '0', 'si', 'Alien Case - Red', 'Closed Cases - Red Full Tower ATX case w/o power supply', 'Alien Case - Red', 'demo/red_alien.jpg', '4000', '1200', '5000', '1', '0', 47, 'f', '', '', 98.26, 98.26, 1.5, 11, 0, 0, 0, 0, 2, 1, 13, 5, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (6, 'DESC-WARR', '0', 'ds', 'Warranty Template', 'Warranty Template', 'Warranty Template', '', '1000', '1000', '1000', '1', '0', 0, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (7, 'DVD-RW', '0', 'si', 'DVD RW with Lightscribe', 'DVD RW with Lightscribe - 8x', 'DVD RW with Lightscribe', 'demo/lightscribe.jpg', '4000', '1200', '5000', '1', '0', 23.6, 'f', '', '', 45, 45, 1.5, 2, 0, 0, 0, 0, 3, 1, 15, 14, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (8, 'HD-150GB', '0', 'si', '150GB SATA Hard Drive', '150GB SATA Hard Drive - 7200 RPM', '150GB SATA Hard Drive', 'demo/150gb_sata.jpg', '4000', '1200', '5000', '1', '0', 27, 'f', '', '', 56, 56, 1.5, 2, 0, 0, 0, 0, 10, 15, 15, 30, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (9, 'KB-128-ERGO', '0', 'si', 'KeysRus ergonomic keyboard', 'KeysRus ergonomic keyboard - Lighted for Gaming', 'KeysRus ergonomic keyboard', 'demo/ergo_key.jpg', '4000', '1200', '5000', '0', '1', 23.51, 'f', '', '', 56.88, 56.88, 1.5, 0, 0, 0, 0, 0, 5, 10, 11, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (10, 'LCD-21-WS', '0', 'si', 'LCDisplays 21\" LCD Monitor', 'LCDisplays 21\" LCD Monitor - wide screen w/anti-glare finish, Black', 'LCDisplays 21\" LCD Monitor', 'demo/monitor.jpg', '4000', '1200', '5000', '1', '0', 145.01, 'f', '', '', 189.99, 189.99, 1.50, 0, 0, 0, 0, 0, 2, 1, 5, 3, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (11, 'MB-ATI-K8', '0', 'si', 'ATI K8 Motherboard', 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors', 'ATI K8 Motherboard', 'demo/mobo.jpg', '4000', '1200', '5000', '1', '0', 125, 'f', '', '', 155.25, 155.25, 1.5, 1, 0, 0, 0, 0, 5, 10, 3, 3, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (12, 'MB-ATI-K8N', '0', 'si', 'ATI K8 Motherboard w/network', 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors with network ports', 'ATI K8 Motherboard w/network', 'demo/mobo.jpg', '4000', '1200', '5000', '1', '0', 135, 'f', '', '', 176.94, 176.94, 1.50, 1.2, 0, 0, 0, 0, 3, 10, 3, 3, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (13, 'Mouse-S', '0', 'si', 'Serial Mouse - 300 DPI', 'Serial Mouse - 300 DPI', 'Serial Mouse - 300 DPI', 'demo/serial_mouse.jpg', '4000', '1200', '5000', '1', '0', 4.85, 'f', '', '', 13.99, 13.99, 1.5, 0.6, 0, 0, 0, 0, 15, 25, 11, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (14, 'PC-2GB-120GB-21', '0', 'ma', 'Computer 2GB-120GB-21', 'Fully assembled computer AMD/ATI 2048GB Ram/1282 GB HD/Red Case/ Monitor/ Keyboard/ Mouse', 'Computer 2GB-120GB-21', 'demo/complete_computer.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 750, 750, 1.50, 21.3, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (15, 'PS-450W', '0', 'si', '450 Watt Silent Power Supply', '850 Watt Silent Power Supply - for use with Intel or AMD processors', '450 Watt Silent Power Supply', 'demo/power_supply.jpg', '4000', '1200', '5000', '1', '0', 86.26, 'f', '', '', 124.5, 124.5, 1.5, 4.7, 0, 0, 0, 0, 10, 6, 14, 5, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (16, 'RAM-2GB-0.2', '0', 'si', '2GB SDRAM', '2 GB PC3200 Memory Modules - for Athlon processors', '2GB SDRAM', 'demo/2gbram.jpg', '4000', '1200', '5000', '1', '0', 56.25, 'f', '', '', 89.65, 89.65, 1.5, 0, 0, 0, 0, 0, 8, 10, 3, 2, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (17, 'VID-NV-512MB', '0', 'si', 'nVidia 512 MB Video Card', 'nVidea 512 MB Video Card - with SLI support', 'nVidia 512 MB Video Card', 'demo/nvidia_512.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 300, 300, 1.50, 0.7, 0, 0, 0, 0, 4, 5, 1, 4, '', '0', now(), '', '', '');");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY . " VALUES (18, 'PC-BB-512', '0', 'ma', 'Bare Bones Computer 2600+/2GB', 'Fully assembled bare bones computer AMD/ATI 512MB/2GB/Red Case', 'Bare Bones Computer 2600+/2GB', 'demo/barebones.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 750, 750, 1.5, 21.3, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
	// Data for table `inventory_assy_list`
	$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY_ASSY_LIST);
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (1, 14, 'LCD-21-WS', 'LCDisplays 21', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (2, 14, 'HD-150GB', '150GB SATA Hard Drive', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (3, 14, 'DVD-RW', 'DVD RW with Lightscribe', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (4, 14, 'VID-NV-512MB', 'nVidea 512 MB Video Card', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (5, 14, 'RAM-2GB-0.2', '2GB SDRAM', 2);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (6, 14, 'AMD-3600-CPU', 'AMD 3600+ Athlon CPU', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (7, 14, 'MB-ATI-K8N', 'ATI K8 Motherboard w/network', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (8, 14, 'CASE-ALIEN', 'Alien Case - Red', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (9, 14, 'Mouse-S', 'Serial Mouse - 300 DPI', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (10, 14, 'KB-128-ERGO', 'KeysRus ergonomic keyboard', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (11, 18, 'RAM-2GB-0.2', '2GB SDRAM', 2);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (12, 18, 'AMD-3600-CPU', 'AMD 3600+ Athlon CPU', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (13, 18, 'MB-ATI-K8N', 'ATI K8 Motherboard w/network', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (14, 18, 'CASE-ALIEN', 'Alien Case - Red', 1);");
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (15, 18, 'VID-NV-512MB', 'nVidea 512 MB Video Card', 1);");
	// data for table inventory_purchase_details
	$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY_PURCHASE);
	$db->Execute("INSERT INTO " . TABLE_INVENTORY_PURCHASE . " (`id`, `sku`, `vendor_id`, `description_purchase`, `purch_taxable`, `item_cost`, `price_sheet_v`) VALUES
(1, 'AMD-3600-CPU', 3, 'AMD 3600+ Athlon CPU', 0, 100, ''),
(2, 'ASSY-BB', 0, 'Labor Cost - Assemble Bare Bones Computer', 0, 25, ''),
(3, 'BOX-TW-322', 0, 'TW-322 Shipping Box - 12 x 12 x 12', 0, 1.35, ''),
(4, 'BOX-TW-553', 0, 'TW-533 Shipping Box - 24 x 12 x 12', 0, 1.75, ''),
(5, 'CASE-ALIEN', 13, 'Closed Cases - Red Full Tower ATX case w/o power supply', 0, 47, ''),
(6, 'DESC-WARR', 0, 'Warranty Template', 0, 0, ''),
(7, 'DVD-RW', 15, 'DVD RW with Lightscribe - 8x', 0, 23.6, ''),
(8, 'HD-150GB', 15, '150GB SATA Hard Drive - 7200 RPM', 0, 27, ''),
(9, 'KB-128-ERGO', 11, 'KeysRus ergonomic keyboard - Lighted for Gaming', 1, 23.51, ''),
(10, 'LCD-21-WS', 5, 'LCDisplays 21\" LCD Monitor - wide screen w/anti-glare finish, Black', 0, 145.01, ''),
(11, 'MB-ATI-K8', 3, 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors', 0, 125, ''),
(12, 'MB-ATI-K8N', 3, 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors with network ports', 0, 135, ''),
(13, 'Mouse-S', 11, 'Serial Mouse - 300 DPI', 0, 4.85, ''),
(14, 'PC-2GB-120GB-21', 0, 'Fully assembled computer AMD/ATI 2048GB Ram/1282 GB HD/Red Case/ Monitor/ Keyboard/ Mouse', 0, 0, ''),
(15, 'PS-450W', 14, '850 Watt Silent Power Supply - for use with Intel or AMD processors', 0, 86.26, ''),
(16, 'RAM-2GB-0.2', 3, '2 GB PC3200 Memory Modules - for Athlon processors', 0, 56.25, ''),
(17, 'VID-NV-512MB', 1, 'nVidea 512 MB Video Card - with SLI support', 0, 0, ''),
(18, 'PC-BB-512', 0, 'Fully assembled bare bones computer AMD/ATI 512MB/2GB/Red Case', 0, 0, '');
	");
	
	// copy the demo images
	require(DIR_FS_MODULES . 'phreedom/classes/backup.php');
	$backups = new backup;
	if (!is_dir(DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/demo')) {
		if (!@mkdir(DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/demo')) $error = true;
	}
	$dir_source = DIR_FS_MODULES  . 'inventory/images/demo/';
	$dir_dest   = DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/demo/';
	$backups->copy_dir($dir_source, $dir_dest);
	return $error;
  }

}
?>