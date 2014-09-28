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
//  Path: /modules/amazon/classes/install.php
//

class amazon_admin {
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables

  function __construct() {
	$this->notes = array(); // placeholder for any operational notes
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'phreedom'   => '3.6',
	  'phreebooks' => '3.6',
	);
  }

  function install($module) {
      global $db;
      // add field amazon_confirm
      if (!db_field_exists(TABLE_SHIPPING_LOG, 'amazon_confirm')) $db->Execute("ALTER TABLE ".TABLE_SHIPPING_LOG." ADD amazon_confirm ENUM('0', '1') NOT NULL DEFAULT '0'");
      if (!db_field_exists(TABLE_INVENTORY, 'amazon')) { // setup new tab in table inventory
          $result = $db->Execute("SELECT id FROM ".TABLE_EXTRA_TABS." WHERE tab_name='Amazon'");
          if ($result->RecordCount() == 0) {
              $sql_data_array = array(
                  'module_id'   => 'inventory',
                  'tab_name'    => 'Amazon',
                  'description' => 'Amazon Inventory Settings',
                  'sort_order'  => '49',
              );
              db_perform(TABLE_EXTRA_TABS, $sql_data_array);
              $tab_id = db_insert_id();
          } else $tab_id = $result->fields['id'];
          // setup extra fields for inventory
          $sql_data_array = array(
              'module_id'   => 'inventory',
              'tab_id'      => $tab_id,
              'entry_type'  => 'check_box',
              'field_name'  => 'amazon',
              'description' => 'Add to Amazon prduct upload feed.',
              'sort_order'  => 50,
              'use_in_inventory_filter'=>'1',
              'params'      => serialize(array('type'=>'check_box', 'select'=>'0', 'inventory_type'=>'ai:ci:ds:sf:ma:ia:lb:mb:ms:mi:ns:sa:sr:sv:si:')),
          );
          db_perform(TABLE_EXTRA_FIELDS, $sql_data_array);
          $db->Execute("ALTER TABLE ".TABLE_INVENTORY." ADD COLUMN amazon enum('0','1') DEFAULT '0'");
      }
  }

  function initialize($module) {
  }

  function update($module) {
    global $db, $messageStack;
	$error = false;
	if (!$error) {
	  write_configure('MODULE_' . strtoupper($module) . '_STATUS', constant('MODULE_' . strtoupper($module) . '_VERSION'));
   	  $messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_' . strtoupper($module) . '_VERSION')), 'success');
	}
	return $error;
  }

  function remove($module) {
  }

  function load_reports($module) {
  }

  function load_demo() {
  }

}
?>