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
//  Path: /modules/rma/classes/admin.php
//
namespace rma\classes;
class admin extends \core\classes\admin {
	public $id 			= 'rma';
	public $text		= MODULE_RMA_TITLE;
	public $description = MODULE_RMA_DESCRIPTION;
	public $version		= '3.6';

	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.3,
		  'inventory'  => 3.3,
		  'phreebooks' => 3.3,
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'rma',
		  'rma/main',
		);
	    // Load tables
		$this->tables = array(
		  TABLE_RMA => "CREATE TABLE " . TABLE_RMA  . " (
			  id int(11) NOT NULL auto_increment,
			  rma_num varchar(16) NOT NULL,
			  entered_by int(11) default NULL,
			  return_code int(11) default NULL,
			  purchase_invoice_id varchar(24) default NULL,
			  purch_order_id varchar(24) default NULL,
			  caller_name varchar(32) NOT NULL default '',
			  caller_telephone1 varchar(32) NOT NULL default '',
			  caller_email varchar(48) default NULL,
			  contact_id varchar(32) default NULL,
			  contact_name varchar(48) default NULL,
			  caller_notes varchar(255) default NULL,
			  `status` varchar(3) NOT NULL default '',
			  received_by int(11) default NULL,
			  receive_carrier varchar(24) default NULL,
			  receive_tracking varchar(32) default NULL,
			  receive_notes varchar(255) default NULL,
			  receive_details text,
			  close_notes varchar(255) default NULL,
			  close_details text,
			  creation_date date NOT NULL default '0000-00-00',
			  invoice_date date NOT NULL default '0000-00-00',
			  receive_date date NOT NULL default '0000-00-00',
			  closed_date date NOT NULL default '0000-00-00',
	          attachments text,
			  PRIMARY KEY  (id)
		    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
		);
		parent::__construct();
	}

	function install($path_my_files, $demo = false) {
	    global $db;
	    parent::install($path_my_files, $demo);
	    // add a current status field for the next rma number
	    if (!db_field_exists(TABLE_CURRENT_STATUS, 'next_rma_num')) {
		  $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_rma_num VARCHAR( 16 ) NOT NULL DEFAULT 'RMA0001';");
	    }
	}

	function upgrade() {
	    global $db;
	    parent::upgrade();
	    if (version_compare($this->status, '3.13', '<') ) {
		  	if (db_field_exists(TABLE_CURRENT_STATUS, 'next_rma_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_rma_desc");
		}
		if (version_compare($this->status, '3.3', '<') ) {
		  	if (!db_field_exists(TABLE_RMA, 'attachments'))       $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD attachments TEXT DEFAULT NULL AFTER closed_date");
		  	if (!db_field_exists(TABLE_RMA, 'contact_id'))        $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD contact_id VARCHAR(32) DEFAULT NULL AFTER caller_email");
		  	if (!db_field_exists(TABLE_RMA, 'contact_name'))      $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD contact_name VARCHAR(48) DEFAULT NULL AFTER contact_id");
		  	if (!db_field_exists(TABLE_RMA, 'purch_order_id'))    $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD purch_order_id VARCHAR(24) DEFAULT NULL AFTER purchase_invoice_id");
		  	if (!db_field_exists(TABLE_RMA, 'receive_details'))   $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD receive_details TEXT DEFAULT NULL AFTER receive_notes");
		  	if (!db_field_exists(TABLE_RMA, 'close_notes'))       $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD close_notes VARCHAR(255) DEFAULT NULL AFTER receive_details");
		  	if (!db_field_exists(TABLE_RMA, 'close_details'))     $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD close_details TEXT DEFAULT NULL AFTER close_notes");
		  	if (!db_field_exists(TABLE_RMA, 'invoice_date'))      $db->Execute("ALTER TABLE " . TABLE_RMA . " ADD invoice_date DATE NOT NULL DEFAULT '0000-00-00' AFTER creation_date");
		  	$result = $db->Execute("select * from " . DB_PREFIX . 'rma_module_item');
		  	$output = array();
		  	while (!$result->EOF) {
			  	$output[$result->fields['ref_id']][] = array(
			  	  'sku'    => $result->fields['sku'],
			  	  'qty'    => $result->fields['qty'],
			  	  'notes'  => $result->fields['item_notes'],
			  	  'action' => $result->fields['item_action'],
			  	);
			  	$result->MoveNext();
		  	}
		  	if (sizeof($output > 0)) foreach ($output as $key => $value) {
		  		db_perform(TABLE_RMA, array('close_details'=>serialize($value)), 'update', 'id = '.$key);
		  	}
		  	if (db_table_exists(DB_PREFIX . 'rma_module_item')) $db->Execute("drop table ".DB_PREFIX.'rma_module_item');
	    }
	}

	function delete($path_my_files) {
	    global $db;
	    parent::delete($path_my_files);
	    if (db_field_exists(TABLE_CURRENT_STATUS, 'next_rma_num'))  $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_rma_num");
	}

}
?>