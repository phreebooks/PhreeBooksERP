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
//  Path: /modules/magento/classes/install.php
//
namespace magento\classes;
class admin extends \core\classes\admin {
	public $id 			= 'magento';
	public $text		= MODULE_MAGENTO_TITLE;
	public $description = MODULE_MAGENTO_DESCRIPTION;
	public $version		= '1';
	
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	
	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.1,
		  'contacts'   => 3.4,
		  'inventory'  => 3.1,
		  'payment'    => 3.1,
		  'phreebooks' => 3.1,
		  'shipping'   => 3.1,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'MAGENTO_URL'               => 'http://',
		  'MAGENTO_USERNAME'          => '',
		  'MAGENTO_PASSWORD'          => '',
		  'MAGENTO_PRODUCT_TAX_CLASS' => '',
		  'MAGENTO_USE_PRICE_SHEETS'  => '0',
		  'MAGENTO_PRICE_SHEET'       => '',
		  'MAGENTO_STATUS_CONFIRM_ID' => '',
		  'MAGENTO_STATUS_PARTIAL_ID' => '',
		);
	}

	function install($path_my_files, $demo = false) {
	    global $db, $messageStack;
		RETURN TRUE; //@TODO NOG VERDER BEWERKEN.
		parent::install($path_my_files, $demo);
		if (!db_field_exists(TABLE_INVENTORY, 'catalog')) { // setup new tab in table inventory
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_name'    => 'Magento',
		    'description' => 'Magento Catalog',
		    'sort_order'  => '49',
		  );
		  db_perform(TABLE_EXTRA_TABS, $sql_data_array);
		  $tab_id = db_insert_id();
		  gen_add_audit_log(MAGENTO_LOG_TABS . TEXT_ADD, 'magento');
		  // setup extra fields for inventory
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'check_box',
		    'field_name'  => 'magneto_catalog',
		    'description' => MAGENTO_CATALOG_ADD,
		    'params'      => serialize(array('type' => 'check_box', 'select' => '0')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `catalog` enum('0','1') default '0'");
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'multi_check_box',
		    'field_name'  => 'magneto_category_id',
		    'description' => MAGENTO_CATALOG_CATEGORY_ID,
		    'params'      => serialize(array('type' => 'text', 'default' => '')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `category_id` 'text' default ''");
		  
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'text',
		    'field_name'  => 'manufacturer',
		    'description' => MAGENTO_CATALOG_MANUFACTURER,
		    'params'      => serialize(array('type' => 'text', 'length' => '64', 'default' => '')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `manufacturer` varchar(64) default ''");
		  
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'text',
		    'field_name'  => 'ProductURL',
		    'description' => MAGENTO_CATALOG_URL,
		    'params'      => serialize(array('type' => 'text', 'length' => '64', 'default' => '')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `ProductURL` varchar(64) default ''");
		  
		  $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'text',
		    'field_name'  => 'ProductModel',
		    'description' => MAGENTO_CATALOG_MODEL,
		    'params'      => serialize(array('type' => 'text', 'length' => '64', 'default' => '')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `ProductModel` varchar(64) default ''");
		  gen_add_audit_log(MAGENTO_LOG_FIELDS . TEXT_NEW, 'magento - catalog');
		}
	}

  function initialize() {
  	global $db, $messageStack;
  	RETURN TRUE; //@TODO EVT NOG BEWERKEN OF VERWIJDEREN.
  	gen_pull_language('inventory');
  	require_once(DIR_FS_MODULES . 'magento/functions/magento.php');
	require_once(DIR_FS_MODULES . 'magento/language/'.$_SESSION['language'].'/language.php');
    require_once(DIR_FS_MODULES . 'inventory/defaults.php');
  	require_once(DIR_FS_MODULES . 'inventory/functions/inventory.php');

	if(defined('MODULE_MAGENTO_LAST_UPDATE') && MODULE_MAGENTO_LAST_UPDATE <> '') $where = " and ( last_update >'" . MODULE_MAGENTO_LAST_UPDATE . "' or last_journal_date >'" . MODULE_MAGENTO_LAST_UPDATE . "')";
	$result = $db->Execute("select id from " . TABLE_INVENTORY . " where catalog = '1' " . $where);
	$cnt    = 0;
	if($result->RecordCount() == 0)	return true;
	while(!$result->EOF) {
	  	$prodXML = new \magento\classes\magento();
	  	$prodXML->submitXML($result->fields['id'], 'product_ul', true, true);
	  	$cnt++;
	  	$result->MoveNext();
	}
	$messageStack->add(sprintf(MAGENTO_BULK_UPLOAD_SUCCESS, $cnt), 'success');
	gen_add_audit_log(MAGENTO_BULK_UPLOAD);
	write_configure('MODULE_MAGENTO_LAST_UPDATE', date('Y-m-d H:i:s'));
  }

  function upgrade() {
    global $db, $messageStack;
    RETURN TRUE; //@TODO EVT BEWERKEN ANDERS VERWIJDEREN.
    if (version_compare($this->status, '3.4', '<') ) {
		write_configure('MODULE_MAGENTO_LAST_UPDATE', date('0000-00-00 00:00:00'));
	}
	$result = $db->Execute("select tab_id from " . TABLE_EXTRA_FIELDS . " where field_name = 'category_id'");
	if ($result->RecordCount() == 0) throw new \Exception('can not find tab_name Magento');
	else $tab_id = $result->fields['tab_id'];	
	if (!db_field_exists(TABLE_INVENTORY, 'ProductURL')){
		 $sql_data_array = array(
		    'module_id'   => 'inventory',
		    'tab_id'      => $tab_id,
		    'entry_type'  => 'text',
		    'field_name'  => 'ProductURL',
		    'description' => MAGENTO_CATALOG_URL,
		    'params'      => serialize(array('type' => 'text', 'length' => '64', 'default' => '')),
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
		    'description' => MAGENTO_CATALOG_MODEL,
		    'params'      => serialize(array('type' => 'text', 'length' => '64', 'default' => '')),
		  );
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
		  $db->Execute("alter table " . TABLE_INVENTORY . " add column `ProductModel` varchar(64) default ''");
	}
  }
}
?>