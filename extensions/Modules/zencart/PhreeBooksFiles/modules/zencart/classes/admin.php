<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/zencart/classes/admin.php
//
namespace zencart\classes;
class admin extends \core\classes\admin {
	public $id 			= 'zencart';
	public $text		= MODULE_ZENCART_TITLE;
	public $description = MODULE_ZENCART_DESCRIPTION;
	public $version		= '3.5';

	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.6,
		  'contacts'   => 3.6,
		  'inventory'  => 3.6,
		  'payment'    => 3.6,
		  'phreebooks' => 3.6,
		  'shipping'   => 3.6,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'ZENCART_URL'               => 'http://',
		  'ZENCART_USERNAME'          => '',
		  'ZENCART_PASSWORD'          => '',
		  'ZENCART_PRODUCT_TAX_CLASS' => '',
		  'ZENCART_USE_PRICE_SHEETS'  => '0',
		  'ZENCART_PRICE_SHEET'       => '',
		  'ZENCART_STATUS_CONFIRM_ID' => '',
		  'ZENCART_STATUS_PARTIAL_ID' => '',
		  'MODULE_ZENCART_LAST_UPDATE' => date('Y-m-d H:i:s'),
		);
		parent::__construct();
	}

	function install($path_my_files, $demo = false) {
		global $db;
		parent::install($path_my_files, $demo);
		if (!db_field_exists(TABLE_INVENTORY, 'catalog')) { // setup new tab in table inventory
		  $result = $db->Execute("select id FROM ".TABLE_EXTRA_TABS." WHERE tab_name='ZenCart'");
		  if ($result->RecordCount() == 0) {
		  	$sql_data_array = array(
		      'module_id'   => 'inventory',
		      'tab_name'    => 'ZenCart',
		      'description' => 'ZenCart Catalog',
		      'sort_order'  => '49',
		    );
		    db_perform(TABLE_EXTRA_TABS, $sql_data_array);
		    $tab_id = db_insert_id();
		  } else $tab_id = $result->fields['id'];
		  gen_add_audit_log(ZENCART_LOG_TABS . TEXT_ADD, 'zencart');
		  // setup extra fields for inventory
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'check_box',
		    'field_name'  => 'catalog',
		    'description' => ZENCART_CATALOG_ADD,
		  	'sort_order'  => 10,
		  	'use_in_inventory_filter'=>'1',
		    'params'      => serialize(array('type'=>'check_box', 'select'=>'0', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `catalog` enum('0','1') default '0'");
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'text',
		    'field_name'  => 'category_id',
		    'description' => ZENCART_CATALOG_CATEGORY_ID,
		  	'sort_order'  => 20,
		  	'use_in_inventory_filter'=>'1',
		  	'params'      => serialize(array('type'=>'text', 'length'=>'64', 'default'=>'', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `category_id` varchar(64) default ''");

		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'text',
		    'field_name'  => 'manufacturer',
		    'description' => ZENCART_CATALOG_MANUFACTURER,
		  	'sort_order'  => 30,
		  	'use_in_inventory_filter'=>'1',
		  	'params'      => serialize(array('type'=>'text', 'length'=>'64', 'default'=>'', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `manufacturer` varchar(64) default ''");
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'text',
		    'field_name'  => 'ProductModel',
		    'description' => ZENCART_CATALOG_MODEL,
		  	'sort_order'  => 40,
		  	'use_in_inventory_filter'=>'1',
		  	'params'      => serialize(array('type'=>'text', 'length'=>'64', 'default'=>'', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `ProductModel` varchar(64) default ''");

		  $sql_data_array = array(
		  	'module_id'   => 'inventory',
		  	'tab_id'      => $tab_id,
		  	'entry_type'  => 'text',
		  	'field_name'  => 'ProductURL',
		  	'description' => ZENCART_CATALOG_URL,
		  	'sort_order'  => 50,
		  	'use_in_inventory_filter'=>'1',
		  	'params'      => serialize(array('type'=>'text', 'length'=>'64', 'default'=>'', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `ProductURL` varchar(64) default ''");
		  gen_add_audit_log(ZENCART_LOG_FIELDS . TEXT_NEW, 'zencart - catalog');
		}
	}

	function initialize() {
	  	global $db, $messageStack;
	  	gen_pull_language('inventory');
		require_once(DIR_FS_MODULES . 'zencart/functions/zencart.php');
		require_once(DIR_FS_MODULES . 'zencart/language/'.$_SESSION['language'].'/language.php');
		require_once(DIR_FS_MODULES . 'inventory/defaults.php');
		require_once(DIR_FS_MODULES . 'inventory/functions/inventory.php');
		if(defined('MODULE_ZENCART_LAST_UPDATE') && MODULE_ZENCART_LAST_UPDATE <> '') $where = " and ( last_update >'" . MODULE_ZENCART_LAST_UPDATE . "' or last_journal_date >'" . MODULE_ZENCART_LAST_UPDATE . "')";
		$result = $db->Execute("select id from " . TABLE_INVENTORY . " where catalog = '1' " . $where);
		$cnt    = 0;
		if($result->RecordCount() == 0)	return true;
		$prodXML = new \zencart\classes\zencart();
		while(!$result->EOF) {
		  	$prodXML->submitXML($result->fields['id'], 'product_ul', true, true);
		  	$cnt++;
		  	$result->MoveNext();
		}
		$messageStack->add(sprintf(ZENCART_BULK_UPLOAD_SUCCESS, $cnt), 'success');
		gen_add_audit_log(ZENCART_BULK_UPLOAD);
		write_configure('MODULE_ZENCART_LAST_UPDATE', date('Y-m-d H:i:s'));
		parent::initialize();
	}

	function upgrade() {
	    global $db;
		parent::upgrade();
		if (version_compare($this->status, '3.4', '<') ) {
			write_configure('MODULE_ZENCART_LAST_UPDATE', date('0000-00-00 00:00:00'));
		}
		$result = $db->Execute("select tab_id from " . TABLE_EXTRA_FIELDS . " where field_name = 'category_id'");
		if ($result->RecordCount() == 0) throw new \Exception('can not find tab_name ZenCart');
		else $tab_id = $result->fields['tab_id'];
		if (!db_field_exists(TABLE_INVENTORY, 'ProductURL')){
			 $sql_data_array = array(
			    'module_id'   => 'inventory',
			    'tab_id'      => $tab_id,
			    'entry_type'  => 'text',
			    'field_name'  => 'ProductURL',
			    'description' => ZENCART_CATALOG_URL,
			    'params'      => serialize(array('type'=>'text', 'length'=>'64', 'default'=>'', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
			  );
			  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
			  $db->Execute("alter table " . TABLE_INVENTORY . " add column `ProductURL` varchar(64) default ''");
		}
		if (!db_field_exists(TABLE_INVENTORY, 'ProductModel')){
			$sql_data_array = array(
			    'module_id'   => 'inventory',
			    'tab_id'      => $tab_id,
			    'entry_type'  => 'text',
			    'field_name'  => 'ProductModel',
			    'description' => ZENCART_CATALOG_MODEL,
			    'params'      => serialize(array('type'=>'text', 'length'=>'64', 'default'=>'', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
			  );
			  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
			  $db->Execute("alter table " . TABLE_INVENTORY . " add column `ProductModel` varchar(64) default ''");
		}
	}
}
?>