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
//  Path: /modules/inventory/classes/admin.php
//
namespace inventory\classes;
require_once (DIR_FS_ADMIN . 'modules/inventory/config.php');
class admin extends \core\classes\admin {
	public $sort_order  = 4;
	public $id 			= 'inventory';
	public $description = MODULE_INVENTORY_DESCRIPTION;
	public $core		= true;
	public $version		= '4.0-dev';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_INVENTORY);
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
		  'INV_DESC_DEFAULT_SALES'             => '4000',
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
			  class VARCHAR( 255 ) NOT NULL DEFAULT '',
			  id int(11) NOT NULL auto_increment,
			  sku varchar(24) NOT NULL default '',
			  inactive enum('0','1') NOT NULL default '0',
			  inventory_type char(2) NOT NULL default 'si',
			  description_short varchar(32) NOT NULL default '',
			  description_sales varchar(255) default NULL,
			  image_with_path varchar(255) default NULL,
			  account_sales_income varchar(15) default NULL,
			  account_inventory_wage varchar(15) default '',
			  account_cost_of_sales varchar(15) default NULL,
			  item_taxable int(11) NOT NULL default '0',
			  cost_method enum('a','f','l') NOT NULL default 'f',
			  price_sheet varchar(32) default NULL,
			  full_price float NOT NULL default '0',
			  full_price_with_tax float NOT NULL default '0',
			  margin float NOT NULL default '0',
			  item_weight float NOT NULL default '0',
			  quantity_on_hand float NOT NULL default '0',
			  quantity_on_order float NOT NULL default '0',
			  quantity_on_sales_order float NOT NULL default '0',
			  quantity_on_allocation float NOT NULL default '0',
			  minimum_stock_level float NOT NULL default '0',
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

		$this->mainmenu["inventory"]["submenu"]["new_inventory"] = array(
				'order' 		=> 1,
				'text' 		=> sprintf(TEXT_NEW_ARGS, TEXT_INVENTORY_ITEM),
				'security_id' => SECURITY_ID_MAINTAIN_INVENTORY,
				'link'        => html_href_link(FILENAME_DEFAULT, 'action=LoadNewInventoryItem', 'SSL'),
				'show_in_users_settings' => false,
				'params'      => '',
		);
		$this->mainmenu["inventory"]["submenu"]["inventory_mgr"] = array(
				'order' 		=> 5,
				'text' 		=> sprintf(TEXT_MANAGER_ARGS, TEXT_INVENTORY),
				'security_id' => SECURITY_ID_MAINTAIN_INVENTORY,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=main&amp;list=1', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		$this->mainmenu["inventory"]["submenu"]["adjustment"] = array(
				'text'        => TEXT_ADJUSTMENTS,
				'order'        => 15,
				'security_id' => SECURITY_ID_ADJUST_INVENTORY,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=adjustments', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		$this->mainmenu["inventory"]["submenu"]["assemble"] = array(
				'text'        => TEXT_ASSEMBLIES,
				'order'        => 20,
				'security_id' => SECURITY_ID_ASSEMBLE_INVENTORY,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=assemblies', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		if (defined('ENABLE_MULTI_BRANCH') && ENABLE_MULTI_BRANCH){
			$this->mainmenu["inventory"]["submenu"]["transfer"] = array(
			  'text'        => TEXT_TRANSFER_INVENTORY,
			  'order'       => 80,
			  'security_id' => SECURITY_ID_TRANSFER_INVENTORY,
			  'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=transfer', 'SSL'),
			  'show_in_users_settings' => true,
			  'params'      => '',
			);
		}
		$this->mainmenu["customers"]["submenu"]["pricesheet"] = array(
				'text'        => TEXT_CUSTOMER_PRICE_SHEETS,
				'order'       => 65,
				'security_id' => SECURITY_ID_PRICE_SHEET_MANAGER,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=price_sheets&amp;type=c&amp;list=1', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);
		$this->mainmenu["vendors"]["submenu"]["pricesheet"] = array(
				'text'        => TEXT_VENDOR_PRICE_SHEETS,
				'order'       => 65,
				'security_id' => SECURITY_ID_VEND_PRICE_SHEET_MGR,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=price_sheets&amp;type=v&amp;list=1', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => '',
		);

		if (\core\classes\user::security_level(SECURITY_ID_CONFIGURATION) > 0){
			$this->mainmenu["company"]['submenu']["configuration"]['submenu']["inventory"] = array(
					'order'	      => sprintf(TEXT_MODULE_ARGS, TEXT_INVENTORY),
					'text'        => sprintf(TEXT_MODULE_ARGS, TEXT_INVENTORY),
					'security_id' => SECURITY_ID_CONFIGURATION,
					'link'        => html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=admin', 'SSL'),
					'show_in_users_settings' => false,
					'params'      => '',
			);
		}

	    parent::__construct();
	}

	function install($path_my_files, $demo = false) {
		global $admin;
		parent::install($path_my_files, $demo);
		$this->notes[] = MODULE_INVENTORY_NOTES_1;
		require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
		\core\classes\fields::sync_fields('inventory', TABLE_INVENTORY);
		$sql = $admin->DataBase->prepare("SELECT * FROM " . TABLE_EXTRA_FIELDS ." WHERE module_id = 'inventory' and tab_id = '0'");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$temp = unserialize($result['params']);
			switch($result['field_name']){
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
			$updateDB = $admin->DataBase->query("UPDATE " . TABLE_EXTRA_FIELDS . " SET params = '" . serialize($temp) . "' WHERE id = '{$result['id']}'");
		}
		// set the fields to view in the inventory field filters
		$haystack = array('attachments', 'account_sales_income', 'item_taxable', 'purch_taxable', 'image_with_path', 'account_inventory_wage', 'account_cost_of_sales', 'cost_method', 'lead_time');
		$result = $admin->DataBase->query("UPDATE " . TABLE_EXTRA_FIELDS . " SET entry_type='check_box' WHERE field_name='inactive'");
		$sql = $admin->DataBase->prepare("SELECT * FROM " . TABLE_EXTRA_FIELDS ." WHERE module_id = 'inventory'");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$use_in_inventory_filter = '1';
			if(in_array($result['field_name'], $haystack)) $use_in_inventory_filter = '0';
			$updateDB = $admin->DataBase->query("update " . TABLE_EXTRA_FIELDS . " set use_in_inventory_filter = '".$use_in_inventory_filter."' where id = '".$result['id']."'");
		}
		\core\classes\fields::sync_fields('inventory', TABLE_INVENTORY);
	}

  	function upgrade(\core\classes\basis &$basis) {
    	parent::upgrade($basis);
    	if (version_compare($this->status, '3.1', '<') ) {
	  		$tab_map = array('0' => '0');
	  		if($basis->DataBase->table_exists(DB_PREFIX . 'inventory_categories')){
		  		$sql = $basis->DataBase->prepare("SELECT * FROM " . DB_PREFIX . 'inventory_categories');
		  		$sql->execute();
		  		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
		    		$updateDB = $basis->DataBase->query("INSERT INTO " . TABLE_EXTRA_TABS . " SET module_id = 'inventory',
			  		  tab_name = '{$result['category_name']}', description = '{$result['category_description']}', sort_order = '{$result['sort_order']}'");
		    		$tab_map[$result['category_id']] = \core\classes\PDO::lastInsertId('id');
		  		}
		  		$basis->DataBase->query("DROP TABLE " . DB_PREFIX . "inventory_categories");
	  		}
	  		if($basis->DataBase->table_exists(DB_PREFIX . 'inventory_categories')){
		  		$sql = $basis->DataBase->prepare("SELECT * FROM " . DB_PREFIX . 'inventory_fields');
		  		$sql->execute();
		  		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
		    		$updateDB = $basis->DataBase->query("INSERT INTO " . TABLE_EXTRA_FIELDS . " SET module_id = 'inventory',
			  		  tab_id = '{$tab_map[$result['category_id']]}', entry_type = '{$result['entry_type']}', field_name = '{$result['field_name']}',
			  		  description = '{$result['description']}', params = '{$result['params']}'");
		  		}
		  		$basis->DataBase->query("DROP TABLE " . DB_PREFIX . "inventory_fields");
	  		}
		}
		if (version_compare($this->status, '3.2', '<') ) {
	  		if (!$basis->DataBase->field_exists(TABLE_PRICE_SHEETS, 'type')) $basis->DataBase->query("ALTER TABLE " . TABLE_PRICE_SHEETS . " ADD type char(1) NOT NULL default 'c' AFTER sheet_name");
	  		if (!$basis->DataBase->field_exists(TABLE_INVENTORY, 'price_sheet_v')) $basis->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " ADD price_sheet_v varchar(32) default NULL AFTER price_sheet");
		}
		if (version_compare($this->status, '3.6', '<') ) {
			$basis->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " ADD INDEX ( `sku` )");
			if (!$basis->DataBase->field_exists(TABLE_INVENTORY, 'attachments')) $basis->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " ADD attachments text AFTER last_journal_date");
			if (!$basis->DataBase->field_exists(TABLE_INVENTORY, 'full_price_with_tax')) $basis->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " ADD full_price_with_tax FLOAT NOT NULL DEFAULT '0' AFTER full_price");
			if (!$basis->DataBase->field_exists(TABLE_INVENTORY, 'product_margin')) $basis->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " ADD product_margin FLOAT NOT NULL DEFAULT '0' AFTER full_price_with_tax");
			if (!$basis->DataBase->field_exists(TABLE_EXTRA_FIELDS , 'use_in_inventory_filter')) $basis->DataBase->query("ALTER TABLE " . TABLE_EXTRA_FIELDS . " ADD use_in_inventory_filter ENUM( '0', '1' ) NOT NULL DEFAULT '0'");
			$basis->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " CHANGE `inactive` `inactive` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
			\core\classes\fields::sync_fields('inventory', TABLE_INVENTORY);
			$basis->DataBase->query("UPDATE " . TABLE_INVENTORY . " SET inventory_type = 'ma' WHERE inventory_type = 'as'");
			$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_EXTRA_FIELDS ." WHERE module_id = 'inventory'");
			$sql->execute();
		  	while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				$temp = unserialize($result['params']);
				switch($result['field_name']){
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
		    	$updateDB = $basis->DataBase->query("UPDATE " . TABLE_EXTRA_FIELDS . " SET params = '" . serialize($temp) . "' where id = '{$result['id']}'");
		  	}
		  	$haystack = array('attachments', 'account_sales_income', 'item_taxable', 'purch_taxable', 'image_with_path', 'account_inventory_wage', 'account_cost_of_sales', 'cost_method', 'lead_time');
		  	$result = $basis->DataBase->query("UPDATE " . TABLE_EXTRA_FIELDS . " SET entry_type = 'check_box' WHERE field_name = 'inactive'");
		  	$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_EXTRA_FIELDS ." WHERE module_id = 'inventory'");
		  	$sql->execute();
		  	while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
		  		$use_in_inventory_filter = '1';
				if(in_array($result['field_name'], $haystack)) $use_in_inventory_filter = '0';
				$updateDB = $basis->DataBase->query("UPDATE " . TABLE_EXTRA_FIELDS . " SET use_in_inventory_filter = '{$use_in_inventory_filter}' where id = '{$result['id']}'");
		  	}
			if ($basis->DataBase->field_exists(TABLE_INVENTORY, 'purch_package_quantity')){
		  		$result = $basis->DataBase->query("INSERT INTO ".TABLE_INVENTORY_PURCHASE." ( sku, vendor_id, description_purchase, purch_package_quantity, purch_taxable, item_cost, price_sheet_v ) select sku, vendor_id, description_purchase, purch_package_quantity, purch_taxable, item_cost, price_sheet_v  from " . TABLE_INVENTORY);
		  		$basis->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " DROP `purch_package_quantity`");
		  	}else{
		  		$result = $basis->DataBase->query("INSERT INTO ".TABLE_INVENTORY_PURCHASE." ( sku, vendor_id, description_purchase, purch_package_quantity, purch_taxable, item_cost, price_sheet_v ) select sku, vendor_id, description_purchase, 1, purch_taxable, item_cost, price_sheet_v  from " . TABLE_INVENTORY);
			}
			require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
			$tax_rates = ord_calculate_tax_drop_down('c');
			$sql = $basis->DataBase->prepare("SELECT id, item_taxable, full_price, item_cost FROM ".TABLE_INVENTORY);
			$sql->execute();
		  	while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				$sql_data_array = array();
				$sql_data_array['full_price_with_tax'] = round((1 +($tax_rates[$result['item_taxable']]['rate']/100))  * $result['full_price'], $basis->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
				if($result['item_cost'] <> '' && $result['item_cost'] > 0) $sql_data_array['product_margin'] = round($sql_data_array['full_price_with_tax'] / $result['item_cost'], $basis->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
				db_perform(TABLE_INVENTORY, $sql_data_array, 'update', "id = " . $result['id']);
			}
		  	validate_path(DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/attachments/', 0755);
		}
		if (version_compare($this->status, '3.7.1', '<') ) {
			if (!$basis->DataBase->field_exists(TABLE_INVENTORY_HISTORY, 'avg_cost')) {
				$basis->DataBase->query("ALTER TABLE ".TABLE_INVENTORY_HISTORY." ADD avg_cost FLOAT NOT NULL DEFAULT '0' AFTER unit_cost");
				$basis->DataBase->query("UPDATE ".TABLE_INVENTORY_HISTORY." SET avg_cost = unit_cost");
			}
			$result = $basis->DataBase->query("SELECT id, params FROM ".TABLE_EXTRA_FIELDS." WHERE module_id = 'inventory' AND field_name = 'account_cost_of_sales'");
			$temp = unserialize($result['params']);
			$temp['inventory_type'] = 'ai:ci:ds:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv';
			$updateDB = $basis->DataBase->query("update ".TABLE_EXTRA_FIELDS." set params='".serialize($temp)."' where id='{$result['id']}'");
		}
		if (version_compare($this->status, '4.0', '<') ) {
			if (!$basis->DataBase->field_exists(TABLE_INVENTORY, 'class')) $basis->DataBase->exec("ALTER TABLE ".TABLE_INVENTORY." ADD class VARCHAR( 255 ) NOT NULL DEFAULT '' FIRST");
			$basis->DataBase->exec("UPDATE ".TABLE_INVENTORY." SET class = CONCAT('inventory\\\\classes\\\\type\\\\', inventory_type) WHERE class = '' ");
		}
		\core\classes\fields::sync_fields('inventory', TABLE_INVENTORY);
	}

  	function delete($path_my_files) {
    	global $admin;
    	parent::delete($path_my_files);
		$admin->DataBase->exec("DELETE FROM " . TABLE_EXTRA_FIELDS . " WHERE module_id = 'inventory'");
		$admin->DataBase->exec("DELETE FROM " . TABLE_EXTRA_TABS   . " WHERE module_id = 'inventory'");
  	}

	function load_reports() {
		parent::load_reports();
		$id    = $this->add_report_heading(TEXT_INVENTORY, 'inv');
		$this->add_report_folder($id, TEXT_REPORTS, 'inv', 'fr');
	}

	function load_demo() {
	    global $admin;
		// Data for table `inventory` @todo add fields
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_INVENTORY);
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (1, 'AMD-3600-CPU', '0', 'si', 'AMD 3600+ Athlon CPU', 'AMD 3600+ Athlon CPU', 'AMD 3600+ Athlon CPU', 'demo/athlon.jpg', '4000', '1200', '5000', '1', '0', 100, 'f', '', '', 150, 150, 1.5, 1, 0, 0, 0, 0, 0, 0, 3, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (2, 'ASSY-BB', '0', 'lb', 'Labor - BB Computer Assy', 'Labor Cost - Assemble Bare Bones Computer', 'Labor - BB Computer Assy', '', '4000', '6000', '5000', '1', '0', 25, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (3, 'BOX-TW-322', '0', 'ns', 'TW-322 Shipping Box', 'TW-322 Shipping Box - 12 x 12 x 12', 'TW-322 Shipping Box', '', '4000', '6800', '5000', '1', '0', 1.35, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 15, 25, 0, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (4, 'BOX-TW-553', '0', 'ns', 'TW-533 Shipping Box', 'TW-533 Shipping Box - 24 x 12 x 12', 'TW-533 Shipping Box', '', '4000', '6800', '5000', '1', '0', 1.75, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (5, 'CASE-ALIEN', '0', 'si', 'Alien Case - Red', 'Closed Cases - Red Full Tower ATX case w/o power supply', 'Alien Case - Red', 'demo/red_alien.jpg', '4000', '1200', '5000', '1', '0', 47, 'f', '', '', 98.26, 98.26, 1.5, 11, 0, 0, 0, 0, 2, 1, 13, 5, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (6, 'DESC-WARR', '0', 'ds', 'Warranty Template', 'Warranty Template', 'Warranty Template', '', '1000', '1000', '1000', '1', '0', 0, 'f', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (7, 'DVD-RW', '0', 'si', 'DVD RW with Lightscribe', 'DVD RW with Lightscribe - 8x', 'DVD RW with Lightscribe', 'demo/lightscribe.jpg', '4000', '1200', '5000', '1', '0', 23.6, 'f', '', '', 45, 45, 1.5, 2, 0, 0, 0, 0, 3, 1, 15, 14, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (8, 'HD-150GB', '0', 'si', '150GB SATA Hard Drive', '150GB SATA Hard Drive - 7200 RPM', '150GB SATA Hard Drive', 'demo/150gb_sata.jpg', '4000', '1200', '5000', '1', '0', 27, 'f', '', '', 56, 56, 1.5, 2, 0, 0, 0, 0, 10, 15, 15, 30, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (9, 'KB-128-ERGO', '0', 'si', 'KeysRus ergonomic keyboard', 'KeysRus ergonomic keyboard - Lighted for Gaming', 'KeysRus ergonomic keyboard', 'demo/ergo_key.jpg', '4000', '1200', '5000', '0', '1', 23.51, 'f', '', '', 56.88, 56.88, 1.5, 0, 0, 0, 0, 0, 5, 10, 11, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (10, 'LCD-21-WS', '0', 'si', 'LCDisplays 21\" LCD Monitor', 'LCDisplays 21\" LCD Monitor - wide screen w/anti-glare finish, Black', 'LCDisplays 21\" LCD Monitor', 'demo/monitor.jpg', '4000', '1200', '5000', '1', '0', 145.01, 'f', '', '', 189.99, 189.99, 1.50, 0, 0, 0, 0, 0, 2, 1, 5, 3, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (11, 'MB-ATI-K8', '0', 'si', 'ATI K8 Motherboard', 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors', 'ATI K8 Motherboard', 'demo/mobo.jpg', '4000', '1200', '5000', '1', '0', 125, 'f', '', '', 155.25, 155.25, 1.5, 1, 0, 0, 0, 0, 5, 10, 3, 3, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (12, 'MB-ATI-K8N', '0', 'si', 'ATI K8 Motherboard w/network', 'ATI-K8-TW AMD socket 939 Motherboard for Athlon Processors with network ports', 'ATI K8 Motherboard w/network', 'demo/mobo.jpg', '4000', '1200', '5000', '1', '0', 135, 'f', '', '', 176.94, 176.94, 1.50, 1.2, 0, 0, 0, 0, 3, 10, 3, 3, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (13, 'Mouse-S', '0', 'si', 'Serial Mouse - 300 DPI', 'Serial Mouse - 300 DPI', 'Serial Mouse - 300 DPI', 'demo/serial_mouse.jpg', '4000', '1200', '5000', '1', '0', 4.85, 'f', '', '', 13.99, 13.99, 1.5, 0.6, 0, 0, 0, 0, 15, 25, 11, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (14, 'PC-2GB-120GB-21', '0', 'ma', 'Computer 2GB-120GB-21', 'Fully assembled computer AMD/ATI 2048GB Ram/1282 GB HD/Red Case/ Monitor/ Keyboard/ Mouse', 'Computer 2GB-120GB-21', 'demo/complete_computer.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 750, 750, 1.50, 21.3, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (15, 'PS-450W', '0', 'si', '450 Watt Silent Power Supply', '850 Watt Silent Power Supply - for use with Intel or AMD processors', '450 Watt Silent Power Supply', 'demo/power_supply.jpg', '4000', '1200', '5000', '1', '0', 86.26, 'f', '', '', 124.5, 124.5, 1.5, 4.7, 0, 0, 0, 0, 10, 6, 14, 5, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (16, 'RAM-2GB-0.2', '0', 'si', '2GB SDRAM', '2 GB PC3200 Memory Modules - for Athlon processors', '2GB SDRAM', 'demo/2gbram.jpg', '4000', '1200', '5000', '1', '0', 56.25, 'f', '', '', 89.65, 89.65, 1.5, 0, 0, 0, 0, 0, 8, 10, 3, 2, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (17, 'VID-NV-512MB', '0', 'si', 'nVidia 512 MB Video Card', 'nVidea 512 MB Video Card - with SLI support', 'nVidia 512 MB Video Card', 'demo/nvidia_512.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 300, 300, 1.50, 0.7, 0, 0, 0, 0, 4, 5, 1, 4, '', '0', now(), '', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY . " VALUES (18, 'PC-BB-512', '0', 'ma', 'Bare Bones Computer 2600+/2GB', 'Fully assembled bare bones computer AMD/ATI 512MB/2GB/Red Case', 'Bare Bones Computer 2600+/2GB', 'demo/barebones.jpg', '4000', '1200', '5000', '1', '0', 0, 'f', '', '', 750, 750, 1.5, 21.3, 0, 0, 0, 0, 0, 0, 0, 1, '', '0', now(), '', '', '');");
		// Data for table `inventory_assy_list`
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_INVENTORY_ASSY_LIST);
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (1, 14, 'LCD-21-WS', 'LCDisplays 21', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (2, 14, 'HD-150GB', '150GB SATA Hard Drive', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (3, 14, 'DVD-RW', 'DVD RW with Lightscribe', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (4, 14, 'VID-NV-512MB', 'nVidea 512 MB Video Card', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (5, 14, 'RAM-2GB-0.2', '2GB SDRAM', 2);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (6, 14, 'AMD-3600-CPU', 'AMD 3600+ Athlon CPU', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (7, 14, 'MB-ATI-K8N', 'ATI K8 Motherboard w/network', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (8, 14, 'CASE-ALIEN', 'Alien Case - Red', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (9, 14, 'Mouse-S', 'Serial Mouse - 300 DPI', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (10, 14, 'KB-128-ERGO', 'KeysRus ergonomic keyboard', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (11, 18, 'RAM-2GB-0.2', '2GB SDRAM', 2);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (12, 18, 'AMD-3600-CPU', 'AMD 3600+ Athlon CPU', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (13, 18, 'MB-ATI-K8N', 'ATI K8 Motherboard w/network', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (14, 18, 'CASE-ALIEN', 'Alien Case - Red', 1);");
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_ASSY_LIST . " VALUES (15, 18, 'VID-NV-512MB', 'nVidea 512 MB Video Card', 1);");
		// data for table inventory_purchase_details
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_INVENTORY_PURCHASE);
		$admin->DataBase->query("INSERT INTO " . TABLE_INVENTORY_PURCHASE . " (`id`, `sku`, `vendor_id`, `description_purchase`, `purch_taxable`, `item_cost`, `price_sheet_v`) VALUES
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

		// copy the demo image
		$backups = new \phreedom\classes\backup;
		validate_path(DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/demo');
		$dir_source = DIR_FS_MODULES  . 'inventory/images/demo/';
		$dir_dest   = DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/demo/';
		$backups->copy_dir($dir_source, $dir_dest);
		parent::load_demo();
	}

	/**
	 * This function will check if the sku field is filled and unique
	 * @param string $sku
	 * @throws Exception
	 */
	function validate_name($sku){
		global $admin;
		if (!$sku) throw new \core\classes\userException(TEXT_THE_ID_FIELD_WAS_EMPTY);
		$result = $admin->DataBase->query("SELECT id FROM " . TABLE_INVENTORY . " WHERE sku = '$sku'");
		if ($result->fetch(\PDO::FETCH_NUM) <> 0) throw new \core\classes\userException(sprintf(TEXT_THE_ID_IS_NOT_UNIQUE_ARGS, $name));
	}

	// functions part

	/**
	 * this will delete a inventory item
	 * @param \core\classes\basis $basis
	 */
	function DeleteInventoryItem (\core\classes\basis $basis){
		\core\classes\user::validate_security_by_token(SECURITY_ID_MAINTAIN_INVENTORY, 4); // security check
		if (!isset($basis->cInfo['inventory_type'])) throw new \core\classes\userException();//@todo
		$temp = '\inventory\classes\type\\'. $basis->cInfo['inventory_type'];
		$cInfo = new $temp;
		$cInfo->check_remove($basis->cInfo['id']);
		$basis->cInfo = null;
		$basis->fireEvent("LoadInventoryManager");
	}

	function RenameInventoryItem (\core\classes\basis $basis){
		\core\classes\user::validate_security_by_token(SECURITY_ID_MAINTAIN_INVENTORY, 4); // security check
		if (!isset($basis->cInfo['inventory_type'])) throw new \core\classes\userException();//@todo
		$temp = '\inventory\classes\type\\'. $basis->cInfo['inventory_type'];
		$cInfo = new $temp;
		$cInfo->rename($basis->cInfo['id'], $basis->cInfo['sku']);
		$basis->cInfo = null;
		$basis->fireEvent("LoadInventoryManager");
	}

	function CopyInventoryItem (\core\classes\basis $basis){
		\core\classes\user::validate_security_by_token(SECURITY_ID_MAINTAIN_INVENTORY, 2); // security check
		if (!isset($basis->cInfo['inventory_type'])) throw new \core\classes\userException();//@todo
		$temp = '\inventory\classes\type\\'. $basis->cInfo['inventory_type'];
		$cInfo = new $temp;
		$cInfo->copy($basis->cInfo['id'], $basis->cInfo['sku']);
		$basis->cInfo = null;
		$basis->fireEvent("LoadInventoryPage");
	}

	/**
	 * will create new inventory depending on type
	 * @param unknown $basis
	 */
	function CreateInventoryItem (\core\classes\basis &$basis) {
		if (!isset($basis->cInfo->inventory_type)) throw new \core\classes\userException("inventory type isn't submitted.");
		$temp = "\\inventory\\classes\\type\\{$basis->cInfo->inventory_type}";
		$inventory = new $temp();
		\core\classes\user::validate_security($inventory->security_level, 2);
		$cInfo->check_create_new();
		$sql = $basis->DataBase->prepare("INSERT INTO ".TABLE_INVENTORY." (class, inventory_type ) VALUES ('" . addcslashes(get_class($inventory), '\\') . "', '{$inventory->type}')");
		$sql->execute();
		$basis->cInfo->iID =  $basis->DataBase->lastInsertId('id');
		$basis->fireEvent("LoadInventoryPage");
	}

	/**
	 * this function will load the inventory page
	 */
	function LoadInventoryPage (\core\classes\basis $basis){
		$basis->observer->send_menu($basis);
		if ( isset($basis->cInfo->rowSeq)) $basis->cInfo->iID = $basis->cInfo->rowSeq;
		if ($basis->cInfo->iID == '') throw new \core\classes\userException("iID variable isn't set can't execute method LoadInventoryPage ");
		$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_INVENTORY . " WHERE id = {$basis->cInfo->iID}");
		$sql->execute();
		$basis->cInfo->inventory = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE);
		$basis->page_title		= sprintf(TEXT_MANAGER_ARGS, TEXT_INVENTORY);
		$basis->module			= 'inventory';
		$basis->page			= 'main';
		$basis->template 		= 'template_detail';
	}

	/**
	 * this function will call LoadInventoryPage but deactivate menu and footer.
	 * @param \core\classes\basis $basis
	 */
  	function LoadInventoryPopUp (\core\classes\basis $basis){
  		$this->LoadInventoryPage ($basis);
		$basis->include_footer	= false;
  	}

  	function LoadNewInventoryItem (\core\classes\basis $basis){
  		$basis->observer->send_menu($basis);
  		$basis->security_level	= \core\classes\user::validate(SECURITY_ID_MAINTAIN_INVENTORY);
  		$basis->page_title		= sprintf(TEXT_NEW_ARGS, TEXT_INVENTORY_ITEM);
  		$basis->module			= 'inventory';
  		$basis->page			= 'main';
  		$basis->template 		= 'template_id';
  	}

  	function LoadInventoryManager (\core\classes\basis $basis){
  		$basis->observer->send_menu($basis);
  		$basis->security_level	= \core\classes\user::validate(SECURITY_ID_MAINTAIN_INVENTORY);
  		//building filter criteria
  		$_SESSION['filter_field'] 	 = isset( $basis->cInfo['filter_field']) 	?  $basis->cInfo['filter_field'] : $_SESSION['filter_field'];
  		$_SESSION['filter_criteria'] = isset( $basis->cInfo['filter_criteria']) ?  $basis->cInfo['filter_criteria'] : $_SESSION['filter_criteria'];
  		$_SESSION['filter_value'] 	 = isset( $basis->cInfo['filter_value']) 	?  $basis->cInfo['filter_value'] : $_SESSION['filter_value'];
  		$filter_criteria = Array(" = "," != "," LIKE "," NOT LIKE "," > "," < ");
  		$x = 0;
  		history_filter('inventory');
  		while (isset($_SESSION['filter_field'][$x])) {
  			if(      $filter_criteria[$_SESSION['filter_criteria'][$x]] == " LIKE " || $_SESSION['filter_criteria'][$x] == TEXT_CONTAINS){
  				if ( $_SESSION['filter_value'][$x] <> '' ) $criteria[] = "{$_SESSION['filter_field'][$x]} Like '%{$_SESSION['filter_value'][$x]}%' ";
  			}elseif( $filter_criteria[$_SESSION['filter_criteria'][$x]] == " NOT LIKE "){
  				if ( $_SESSION['filter_value'][$x] <> '' ) $criteria[] = "{$_SESSION['filter_field'][$x]} Not Like '%{$_SESSION['filter_value'][$x]}%' ";
  			}elseif( $filter_criteria[$_SESSION['filter_criteria'][$x]] == " = "  && $_SESSION['filter_value'][$x] == ''){
  				if ( $_SESSION['filter_field'][$x] == 'a.sku' && $_SESSION['filter_value'][$x] == '' ) { $x++; continue; }
  				$criteria[] = "({$_SESSION['filter_field'][$x]} {$filter_criteria[$_SESSION['filter_criteria'][$x]]} '{$_SESSION['filter_value'][$x]}' or '{$_SESSION['filter_field'][$x]}' IS NULL ) ";
  			}elseif( $filter_criteria[$_SESSION['filter_criteria'][$x]] == " != " && $_SESSION['filter_value'][$x] == ''){
  				$criteria[] = "({$_SESSION['filter_field'][$x]} {$filter_criteria[$_SESSION['filter_criteria'][$x]]} '{$_SESSION['filter_value'][$x]}' or '{$_SESSION['filter_field'][$x]}' IS NOT NULL ) ";
  			}else{
  				$criteria[] = $_SESSION['filter_field'][$x] . $filter_criteria[$_SESSION['filter_criteria'][$x]]. ' "' . $_SESSION['filter_value'][$x] . '" ';
  			}
  			$x++;
  		}

  		// build the list header
  		$heading_array = array(
  				'a.sku'                     => TEXT_SKU,
  				'a.inactive'                => TEXT_INACTIVE,
  				'a.description_short'       => TEXT_DESCRIPTION,
  				'a.quantity_on_hand'        => TEXT_QUANTITY_ON_HAND_SHORT,
  				'a.quantity_on_sales_order' => INV_HEADING_QTY_ON_SO,
  				'a.quantity_on_allocation'  => INV_HEADING_QTY_ON_ALLOC,
  				'a.quantity_on_order'       => TEXT_QUANTITY_ON_ORDER_SHORT,
  		);
  		$result      = html_heading_bar($heading_array);
  		$list_header = $result['html_code'];
  		$disp_order  = $result['disp_order'];
  		//	if ($disp_order == 'a.sku ASC') $disp_order ='LPAD(a.sku,'.MAX_INVENTORY_SKU_LENGTH.',0) ASC';
  		//	if ($disp_order == 'a.sku DESC')$disp_order ='LPAD(a.sku,'.MAX_INVENTORY_SKU_LENGTH.',0) DESC';
  		// build the list for the page selected
  		if (isset($basis->cInfo['search_text']) && $basis->cInfo['search_text'] <> '') {
  			$search_fields = array('a.sku', 'a.description_short', 'a.description_sales', 'p.description_purchase');
  			// hook for inserting new search fields to the query criteria.
  			if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  			$criteria[] = '(' . implode(" like '%{$basis->cInfo['search_text']}%' or ", $search_fields) . " like '%{$_REQUEST['search_text']}%')";
  		}
  		// build search filter string
  		$search = (sizeof($criteria) > 0) ? (' where ' . implode(' and ', $criteria)) : '';
  		$field_list = array('a.id as id', 'a.sku as sku', 'inactive', 'inventory_type', 'description_short', 'full_price',
  				'quantity_on_hand', 'quantity_on_order', 'quantity_on_sales_order', 'quantity_on_allocation', 'last_journal_date');
  		// hook to add new fields to the query return results
  		if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
  		$query_raw    = "SELECT SQL_CALC_FOUND_ROWS DISTINCT " . implode(', ', $field_list)  . " FROM " . TABLE_INVENTORY ." a LEFT JOIN " . TABLE_INVENTORY_PURCHASE . " p on a.sku = p.sku ". $search . " order by $disp_order ";
  		//check if sql is executed before otherwise retrieve from memorie.
  		$sql = $basis->DataBase->prepare($query_raw);
  		$sql->execute();
  		$basis->cInfo->inventory_list = $sql->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE) ;
/* @todo look at this
 *   		if (isset($basis->sqls[$query_raw])) $query_result = $basis->sqls[$query_raw];
  		else $query_result = $admin->DataBase->query($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($basis->cInfo['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
  		$query_split  = new \core\classes\splitPageResults($basis->cInfo['list'], '');
  		$basis->sqls[$query_raw] = $query_result; // storing data into cache memory*/
  		history_save('inventory');
  		// the following should save loading time.
  		if ($basis->cInfo->FirstValue == '' || $basis->cInfo->FirstId  == '' || $basis->cInfo->SecondField  == '' || $basis->cInfo->SecondFieldValue  == '' || $basis->cInfo->SecondFieldId	 == '' ) $this->LoadInventoryFilter();
  		//end building array's for filter dropdown selection
  		$basis->page_title		= sprintf(TEXT_MANAGER_ARGS, TEXT_INVENTORY);
  		$basis->module			= 'inventory';
  		$basis->page			= 'main';
  		$basis->template 		= 'template_main';
  	}

  	function LoadInventoryFilter(){
  		global $admin;
  		//building array's for filter dropdown selection
  		$i=0;
  		$result = $admin->DataBase->prepare("SELECT * FROM " . TABLE_EXTRA_FIELDS ." WHERE module_id = 'inventory' AND use_in_inventory_filter = '1' ORDER BY description ASC");
  		$basis->cInfo->FirstValue 		= 'var FirstValue = new Array();' 		. chr(10);
  		$basis->cInfo->FirstId 			= 'var FirstId = new Array();' 			. chr(10);
  		$basis->cInfo->SecondField 		= 'var SecondField = new Array();' 		. chr(10);
  		$basis->cInfo->SecondFieldValue	= 'var SecondFieldValue = new Array();'	. chr(10);
  		$basis->cInfo->SecondFieldId	= 'var SecondFieldId = new Array();' 	. chr(10);
  		$sql->execute();
  		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
  			if(in_array($result['field_name'], array('vendor_id','description_purchase','item_cost','purch_package_quantity','purch_taxable','price_sheet_v')) ){
  				$append 	= 'p.';
  			}else{
  				$append 	= 'a.';
  			}
  			$basis->cInfo->FirstValue 	.= "FirstValue[$i] = '{$result['description']}';" . chr(10);
  			$basis->cInfo->FirstId 		.= "FirstId[$i] = '{$append}{$result['field_name']}';" . chr(10);
  			Switch($result['field_name']){
  				case 'vendor_id':
  					$contacts = gen_get_contact_array_by_type('v');
  					$tempValue  ='Array("'  ;
  					$tempId 	='Array("' ;
  					while ($contact = array_shift($contacts)) {
  						$tempValue .= $contact['id'].'","';
  						$tempId    .= str_replace( array("/","'",chr(34),) , ' ', $contact['text']).'","';
  					}
  					$tempValue  .='")' ;
  					$tempId 	.='")' ;
  					$basis->cInfo->SecondField		.= "SecondField['{$append}{$result['field_name']}'] = 'drop_down';" . chr(10);
  					$basis->cInfo->SecondFieldValue	.= "SecondFieldValue['{$append}{$result['field_name']}] = $tempValue;" . chr(10);
  					$basis->cInfo->SecondFieldId	.= "SecondFieldId['{$append}{$result['field_name']}'] = $tempId;" . chr(10);
  					break;

  				case'inventory_type':
  					$tempValue 	='Array("'  ;
  					$tempId 	='Array("' ;
  					foreach ($inventory_types_plus as $key => $value){
  						$tempValue .= $key.'","';
  						$tempId    .= $value.'","';
  					}
  					$tempValue 	.='")' ;
  					$tempId 	.='")' ;
  					$basis->cInfo->SecondField		.= "SecondField['{$append}{$result['field_name']}'] = 'drop_down';" . chr(10);
  					$basis->cInfo->SecondFieldValue	.= "SecondFieldValue['{$append}{$result['field_name']}'] = $tempValue;" . chr(10);
  					$basis->cInfo->SecondFieldId	.= "SecondFieldId['{$append}{$result['field_name']}'] = $tempId;" . chr(10);
  					break;
  				case'cost_method':
  					$tempValue 	='Array("'  ;
  					$tempId 	='Array("' ;
  					foreach ($cost_methods as $key => $value){
  						$tempValue .= $key.'","';
  						$tempId    .= $value.'","';
  					}
  					$tempValue .='")' ;
  					$tempId .='")' ;
  					$basis->cInfo->SecondField		.= "SecondField['{$append}{$result['field_name']}'] = 'drop_down';" . chr(10);
  					$basis->cInfo->SecondFieldValue	.= "SecondFieldValue['{$append}{$result['field_name']}'] = $tempValue;" . chr(10);
  					$basis->cInfo->SecondFieldId	.= "SecondFieldId['{$append}{$result['field_name']}'] = $tempId;" . chr(10);
  					break;
  				default:
  					$basis->cInfo->SecondField.= "SecondField['{$append}{$result['field_name']}'] ='{$result['entry_type']}';" . chr(10);
  					if(in_array($result['entry_type'], array('drop_down','radio','multi_check_box','data_list'))){
  						$tempValue 	='Array("';
  						$tempId 	='Array("' ;
  						//explode params and splits value form id
  						$params  = unserialize($result['params']);
  						$choices = explode(',',$params['default']);
  						while ($choice = array_shift($choices)) {
  							$values 	 = explode(':',$choice);
  							$tempValue	.= $values[0].'","';
  							$tempId		.= $values[1].'","';
  						}
  						$tempValue 	.='")' ;
  						$tempId 	.='")' ;
  						$basis->cInfo->SecondFieldValue	.= "SecondFieldValue['{$append}{$result['field_name']}'] = $tempValue;" . chr(10);
  						$basis->cInfo->SecondFieldId	.= "SecondFieldId['{$append}{$result['field_name']}'] = $tempId;" . chr(10);
  					}
  			}
  			$i++;
  		}
  	}

  	function SaveInventoryAdjustment (\core\classes\basis $basis){
  		\core\classes\user::validate_security_by_token(SECURITY_ID_ADJUST_INVENTORY, 2);
  		$post_date           = isset($_POST['post_date'])? \core\classes\DateTime::db_date_format($_POST['post_date']) : date('Y-m-d');
  		$glEntry             = new \core\classes\journal();
  		$glEntry->id         = isset($_POST['id'])       ? $_POST['id']       : '';
  		$glEntry->journal_id = 16;
  		$glEntry->store_id   = isset($_POST['store_id']) ? $_POST['store_id'] : 0;
  		// retrieve and clean input values
  		$glEntry->post_date           = $post_date;
  		$glEntry->period              = \core\classes\DateTime::period_of_date($post_date);
  		$glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
  		$glEntry->admin_id            = $_SESSION['admin_id'];
  		$glEntry->closed              = '1'; // closes by default
  		$glEntry->closed_date         = $post_date;
  		$glEntry->currencies_code     = DEFAULT_CURRENCY;
  		$glEntry->currencies_value    = 1;
  		$adj_reason                   = db_prepare_input($_POST['adj_reason']);
  		$adj_account                  = db_prepare_input($_POST['gl_acct']);
  		// process the request
  		$glEntry->journal_main_array  = $glEntry->build_journal_main_array();
  		// build journal entry based on adding or subtracting from inventory
  		$rowCnt    = 1;
  		$adj_total = 0;
  		$adj_lines = 0;
  		while (true) {
  			if (!isset($_POST['sku_'.$rowCnt])) break;
  			$sku              = db_prepare_input($_POST['sku_'.$rowCnt]);
  			$qty              = db_prepare_input($_POST['qty_'.$rowCnt]);
  			$serialize_number = db_prepare_input($_POST['serial_'.$rowCnt]);
  			$desc             = db_prepare_input($_POST['desc_'.$rowCnt]);
  			$acct             = db_prepare_input($_POST['acct_'.$rowCnt]);
  			$price            = $admin->currencies->clean_value($_POST['price_'.$rowCnt]);
  			if ($qty > 0) $adj_total += $qty * $price;
  			if ($qty && $sku <> '' && $sku <> TEXT_SEARCH) { // ignore blank rows
  				$glEntry->journal_rows[] = array(
  				  'sku'              => $sku,
  				  'qty'              => $qty,
  				  'gl_type'          => 'adj',
  				  'serialize_number' => $serialize_number,
  				  'gl_account'       => $acct,
  				  'description'      => $desc,
  				  'credit_amount'    => 0,
  				  'debit_amount'     => $qty > 0 ? $qty * $price : 0,
  				  'post_date'        => $post_date,
  				);
  				$adj_lines++;
  			}
  			$rowCnt++;
  		}
  		if ($adj_lines == 0) throw new \core\classes\userException(TEXT_CANNOT_ADJUST_INVENTORY_WITH_A_ZERO_QUANTITY);
  		$glEntry->journal_main_array['total_amount'] = $adj_total;
  		$glEntry->journal_rows[] = array(
  				'sku'           => '',
  				'qty'           => '',
  				'gl_type'       => 'ttl',
  				'gl_account'    => $adj_account,
  				'description'   => $adj_reason,
  				'debit_amount'  => 0,
  				'credit_amount' => $adj_total,
  				'post_date'     => $post_date,
  		);
  		// *************** START TRANSACTION *************************
  		$admin->DataBase->transStart();
  		$glEntry->override_cogs_acct = $adj_account; // force cogs account to be users specified account versus default inventory account
  		if ($glEntry->Post($glEntry->id ? 'edit' : 'insert')) {
  			$admin->DataBase->transCommit();	// post the chart of account values
  			gen_add_audit_log(TEXT_INVENTORY_ADJUSTMENT . ' - ' . ($_REQUEST['action']=='save' ? TEXT_SAVE : TEXT_EDIT), $sku, $qty);
  			$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_POSTED, TEXT_INVENTORY_ADJUSTMENT, $glEntry->purchase_invoice_id), 'success');
  			if (DEBUG) $messageStack->write_debug();
  			$basis->cInfo = null;
  			$basis->fireEvent("LoadInventoryAdjustments");
  		}
  		// *************** END TRANSACTION *************************
  	}

  	function DeleteInventoryAdjustment (\core\classes\basis $basis){
  		\core\classes\user::validate_security_by_token(SECURITY_ID_ADJUST_INVENTORY, 4); // security check
  		if (!$glEntry->id) throw new \core\classes\userException(TEXT_THERE_WERE_ERRORS_DURING_PROCESSING . ' ' . TEXT_THE_RECORD_WAS_NOT_DELETED);
  		$delOrd = new \core\classes\journal();
  		$delOrd->journal($glEntry->id); // load the posted record based on the id submitted
  		// *************** START TRANSACTION *************************
  		$admin->DataBase->transStart();
  		if ($delOrd->unPost('delete')) {
  			$admin->DataBase->transCommit(); // if not successful rollback will already have been performed
  			gen_add_audit_log(TEXT_INVENTORY_ADJUSTMENT . ' - ' . TEXT_DELETE, $delOrd->journal_rows[0]['sku'], $delOrd->journal_rows[0]['qty']);
  			if (DEBUG) $messageStack->write_debug();
  			$basis->cInfo = null;
  			$basis->fireEvent("LoadInventoryAdjustments");
  		}
  	}

  	function EditInventoryAdjustment (\core\classes\basis $basis){
  		\core\classes\user::validate_security_by_token(SECURITY_ID_ADJUST_INVENTORY, 2); // security check
  		$basis->cInfo = null;
  		$basis->fireEvent("LoadInventoryAdjustments");
  	}

  	function LoadInventoryAdjustments (\core\classes\basis $basis){
  		$basis->observer->send_menu($basis);
  		$basis->security_level = \core\classes\user::validate(SECURITY_ID_ADJUST_INVENTORY);
  		$gl_array_list = gen_coa_pull_down(); // load gl accounts
  		$cal_adj = array(
  				'name'      => 'dateReference',
  				'form'      => 'inv_adj',
  				'fieldname' => 'post_date',
  				'imagename' => 'btn_date_1',
  				'default'   => \core\classes\DateTime::createFromFormat(DATE_FORMAT, $post_date),
  		);
  		$basis->cInfo->gl_acct	= INV_STOCK_DEFAULT_COS;
  		$basis->page_title		= TEXT_INVENTORY_ADJUSTMENTS;
  		$basis->module			= 'inventory';
  		$basis->page			= 'adjustments';
  		$basis->template 		= 'template_main';
  	}

  	function SaveInventoryAssemblies (\core\classes\basis $basis){
  		\core\classes\user::validate_security_by_token(SECURITY_ID_ASSEMBLE_INVENTORY, 2); // security check
  		// 	retrieve and clean input values
  		$glEntry             = new \core\classes\journal();
  		$glEntry->id         = ($_POST['id'] <> '')      ? $_POST['id'] : ''; // will be null unless opening an existing gl entry
  		$glEntry->journal_id = 14;
  		$glEntry->store_id   = isset($_POST['store_id']) ? $_POST['store_id'] : 0;
  		$glEntry->post_date  = $_POST['post_date']       ? \core\classes\DateTime::db_date_format($_POST['post_date']) : date('Y-m-d');
  		$glEntry->admin_id            = $_SESSION['admin_id'];
  		$glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
  		$sku                          = db_prepare_input($_POST['sku_1']);
  		$qty                          = db_prepare_input($_POST['qty_1']);
  		$desc                         = db_prepare_input($_POST['desc_1']);
  		$stock                        = db_prepare_input($_POST['stock_1']);
  		$serial                       = db_prepare_input($_POST['serial_1']);
  		// check for errors and prepare extra values
  		$glEntry->period              = \core\classes\DateTime::period_of_date($glEntry->post_date);
  		if (!$glEntry->period) throw new \core\classes\userException("period isn't set");
  		// if unbuild, test for stock to go negative
  		$result = $admin->DataBase->query("select account_inventory_wage, quantity_on_hand
	  		  from " . TABLE_INVENTORY . " where sku = '$sku'");
  		$sku_inv_acct = $result['account_inventory_wage'];
  		if (!$result->fetch(\PDO::FETCH_NUM)) throw new \core\classes\userException(INV_ERROR_SKU_INVALID);
  		if ($qty < 0 && ($result['quantity_on_hand'] + $qty) < 0 ) throw new \core\classes\userException(INV_ERROR_NEGATIVE_BALANCE);
  		if (!$qty) throw new \core\classes\userException(JS_ASSY_VALUE_ZERO);
  		// finished checking errors, reload if any errors found
  		$cInfo = new \core\classes\objectInfo($_POST);
  		// 	process the request, build main record
  		$glEntry->closed = '1'; // closes by default
  		$glEntry->journal_main_array = $glEntry->build_journal_main_array();
  		// build journal entry based on adding or subtracting from inventory, debit/credit will be calculated by COGS
  		$glEntry->journal_rows[] = array(
  				'gl_type'          => 'asy',
  				'sku'              => $sku,
  				'qty'              => $qty,
  				'serialize_number' => $serial,
  				'gl_account'       => $sku_inv_acct,
  				'description'      => $desc,
  		);
  		// *************** START TRANSACTION *************************
  		$admin->DataBase->transStart();
  		$glEntry->Post($glEntry->id ? 'edit' : 'insert');
  		$admin->DataBase->transCommit();	// post the chart of account values
  		gen_add_audit_log(TEXT_INVENTORY_ASSEMBLY . ' - ' . ($_REQUEST['action']=='save' ? TEXT_SAVE : TEXT_EDIT), $sku, $qty);
  		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_ASSEMBLED, TEXT_SKU , $sku), 'success');
  		if (DEBUG) $messageStack->write_debug();
  		$basis->cInfo = null;
  		$basis->fireEvent("LoadInventoryAssemblies");
  	}

  	function DeleteInventoryAssemblies (\core\classes\basis $basis){
  		\core\classes\user::validate_security_by_token(SECURITY_ID_ASSEMBLE_INVENTORY, 4); // security check
  		$glEntry             = new \core\classes\journal();
  		$glEntry->id         = ($_POST['id'] <> '')      ? $_POST['id'] : ''; // will be null unless opening an existing gl entry
  		$glEntry->journal_id = 14;
  		$glEntry->store_id   = isset($_POST['store_id']) ? $_POST['store_id'] : 0;
  		$glEntry->post_date  = $_POST['post_date']       ? \core\classes\DateTime::db_date_format($_POST['post_date']) : date('Y-m-d');
  		if (!$glEntry->id) throw new \core\classes\userException(TEXT_THERE_WERE_ERRORS_DURING_PROCESSING . ' ' . TEXT_THE_RECORD_WAS_NOT_DELETED);
  		$delAssy = new \core\classes\journal($glEntry->id); // load the posted record based on the id submitted
  		// *************** START TRANSACTION *************************
  		$admin->DataBase->transStart();
  		if ($delAssy->unPost('delete')) {	// unpost the prior assembly
  			$admin->DataBase->transCommit(); // if not successful rollback will already have been performed
  			gen_add_audit_log(TEXT_INVENTORY_ASSEMBLY . ' - ' . TEXT_DELETE, $delAssy->journal_rows[0]['sku'], $delAssy->journal_rows[0]['qty']);
  			if (DEBUG) $messageStack->write_debug();
  			gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
  			// *************** END TRANSACTION *************************
  		}
  	}

  	function EditInventoryAssemblies (\core\classes\basis $basis){
  		\core\classes\user::validate_security_by_token(SECURITY_ID_ASSEMBLE_INVENTORY, 2); // security check
  		$basis->cInfo = null;
  	}

  	function LoadInventoryAssemblies (\core\classes\basis $basis){
  		$basis->observer->send_menu($basis);
  		$basis->security_level = \core\classes\user::validate(SECURITY_ID_ASSEMBLE_INVENTORY);
  		$cal_assy = array(
		  'name'      => 'datePost',
		  'form'      => 'inv_assy',
		  'fieldname' => 'post_date',
		  'imagename' => 'btn_date_1',
		  'default'   => isset($glEntry->post_date) ? \core\classes\DateTime::createFromFormat(DATE_FORMAT, $glEntry->post_date) : date(DATE_FORMAT),
		);
  		$basis->cInfo->gl_acct	= INV_STOCK_DEFAULT_COS;
  		$basis->page_title		= TEXT_ASSEMBLE_DISASSEMBLE_INVENTORY;
  		$basis->module			= 'inventory';
  		$basis->page			= 'assemblies';
  		$basis->template 		= 'template_main';
  	}
}
?>