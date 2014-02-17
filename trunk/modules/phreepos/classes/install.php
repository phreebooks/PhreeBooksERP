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
//  Path: /modules/phreepos/classes/install.php
//
class phreepos_admin {
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	
	
  function phreepos_admin() {
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'contacts'  => 3.71,
	  'inventory' => 3.6,
	  'phreebooks'=> 3.6,
	  'phreedom'  => 3.6,
	  'payment'   => 3.6,
	  'phreeform' => 3.6,
	);
	// Load configuration constants for this module, must match entries in admin tabs
    $this->keys = array(
	  'PHREEPOS_REQUIRE_ADDRESS'              => '0',
	  'PHREEPOS_RECEIPT_PRINTER_NAME'         => '', // i.e. Epson
      'PHREEPOS_RECEIPT_PRINTER_STARTING_LINE'=> '', // code that should be placed in the header
      'PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE' => '', // code for opening the drawer or cutting of the paper.
      'PHREEPOS_RECEIPT_PRINTER_OPEN_DRAWER'  => '', // code for opening the drawer payment dependent
      'PHREEPOS_DISPLAY_WITH_TAX'			  => '1',// if prices on screen should be net or not
      'PHREEPOS_DISCOUNT_OF'                  => '0',// should the discount be of the total or subtotal.
      'PHREEPOS_ROUNDING'					  => '0',// should the endtotal be rounded.
      'PHREEPOS_ENABLE_DIRECT_PRINTING'       => 1,  // this enables or disables direct printing.
	);
	// add new directories to store images and data
	$this->dirlist = array(
	);
	// Load tables
	$this->tables = array(
		TABLE_PHREEPOS_TILLS => "CREATE TABLE " . TABLE_PHREEPOS_TILLS . " (
  			till_id 				int(11) NOT NULL auto_increment,
  			store_id            	int(11)                default '0',
  			description         	varchar(64)   NOT NULL default '',
  			gl_acct_id          	varchar(15)   NOT NULL default '',
  			rounding_gl_acct_id 	varchar(15)   NOT NULL default '',
  			dif_gl_acct_id      	varchar(15)   NOT NULL default '',
  			currencies_code    		varchar(3)    NOT NULL default '',
  			restrict_currency   	enum('0','1') NOT NULL default '0',
  			printer_name        	varchar(64)   NOT NULL default '',
  			printer_starting_line	varchar(255)  NOT NULL default '',
  			printer_closing_line    varchar(255)  NOT NULL default '',
  			printer_open_drawer     varchar(255)  NOT NULL default '',
  			balance					double 				   default '0',
  			max_discount        	varchar(64)   NOT NULL default '',
  			tax_id 					INT(11) 			   default '-1',
  			PRIMARY KEY (till_id)
  		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
  		TABLE_PHREEPOS_OTHER_TRANSACTIONS => "CREATE TABLE " .TABLE_PHREEPOS_OTHER_TRANSACTIONS . " (
  			ot_id	 				int(11) NOT NULL auto_increment,
  			till_id          	  	int(11)                default '0',
  			description         	varchar(64)   NOT NULL default '',
  			gl_acct_id          	varchar(15)   NOT NULL default '',
  			type				   	varchar(15)   NOT NULL default '0',
  			use_tax  			 	enum('0','1') NOT NULL default '0',
  			taxable 				int(11) 	  NOT NULL default '0',
  			PRIMARY KEY (ot_id)
  		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
    );
  }

  function install($module, $demo = false) {
    global $db;
	$error = false;
//	$this->notes[] = MODULE_PHREEPOS_NOTES_1;
    return $error;
  }

  function initialize($module) {
  }

  function update($module) {
    global $db, $messageStack;
	$error = false;
	if (MODULE_PHREEPOS_STATUS < 3.2) {
	  if(!defined('PHREEPOS_RECEIPT_PRINTER_STARTING_LINE')) write_configure('PHREEPOS_RECEIPT_PRINTER_STARTING_LINE', '');
	  if(!defined('PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE'))  write_configure('PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE', '');
	}
	if (MODULE_PHREEPOS_STATUS < 3.3) {
	  if(!defined('PHREEPOS_RECEIPT_PRINTER_OPEN_DRAWER'))   write_configure('PHREEPOS_RECEIPT_PRINTER_OPEN_DRAWER', '');
	  if(!defined('PHREEPOS_DISPLAY_WITH_TAX'))  			 write_configure('PHREEPOS_DISPLAY_WITH_TAX',		     '1');
      if(!defined('PHREEPOS_DISCOUNT_OF'))   				 write_configure('PHREEPOS_DISCOUNT_OF',                 '0');
      if(!defined('PHREEPOS_ROUNDING'))   				     write_configure('PHREEPOS_ROUNDING',                    '0');
	}
	if (!db_table_exists(TABLE_PHREEPOS_TILLS)) {
	    foreach ($this->tables as $table => $sql) {
		  if ($table == TABLE_PHREEPOS_TILLS) admin_install_tables(array($table => $sql));
		  foreach (gen_get_store_ids() as $store){
		  	$sql_data_array = array(
		  		'store_id'    		  	=> $store['id'],
		  		'gl_acct_id'  		  	=> AR_SALES_RECEIPTS_ACCOUNT,
		  		'description' 		  	=> $store['text'],
		  	    'rounding_gl_acct_id' 	=> AR_SALES_RECEIPTS_ACCOUNT,
			  	'dif_gl_acct_id'	  	=> AR_SALES_RECEIPTS_ACCOUNT,
				'printer_name'		  	=> PHREEPOS_RECEIPT_PRINTER_NAME,
				'printer_starting_line' => PHREEPOS_RECEIPT_PRINTER_STARTING_LINE,
				'printer_closing_line' 	=> PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE,
				'printer_open_drawer' 	=> '',
		  	);
		  	db_perform(TABLE_PHREEPOS_TILLS, $sql_data_array);
		  }
		  if(defined('PHREEPOS_RECEIPT_PRINTER_NAME')) 			remove_configure('PHREEPOS_RECEIPT_PRINTER_NAME');
		  if(defined('PHREEPOS_RECEIPT_PRINTER_STARTING_LINE')) remove_configure('PHREEPOS_RECEIPT_PRINTER_STARTING_LINE');
		  if(defined('PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE'))  remove_configure('PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE');
		}
	}
	foreach ($this->tables as $table => $sql) {
	  if ($table == TABLE_PHREEPOS_OTHER_TRANSACTIONS) admin_install_tables(array($table => $sql));
		
	}
	if(!defined('PHREEPOS_ENABLE_DIRECT_PRINTING')) write_configure('PHREEPOS_ENABLE_DIRECT_PRINTING', 1);
	if (!db_field_exists(TABLE_PHREEPOS_TILLS, 'tax_id')) $db->Execute("ALTER TABLE " . TABLE_PHREEPOS_TILLS . " ADD tax_id INT(11) default '-1' AFTER max_discount");
	if (!$error) {
	  write_configure('MODULE_' . strtoupper($module) . '_STATUS', constant('MODULE_' . strtoupper($module) . '_VERSION'));
   	  $messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_' . strtoupper($module) . '_VERSION')), 'success');
	}
	return $error;
  }

  function remove($module) {
    global $db;
    $error = false;
    // Don't allow delete if there is activity
	$sql = "select id from " . TABLE_JOURNAL_MAIN . " where journal_id = '19'";
	$result = $db->Execute($sql);
	if ($result->RecordCount() <> 0 ) {
	  $messageStack->add(ERROR_CANT_DELETE, 'error');
	  return true;
	}
    foreach ($this->tables as $table => $sql) {
		  admin_remove_tables(array($table => $sql));
    }
    return $error;
  }

  function load_reports($module) {
	$error = false;
	$id = admin_add_report_heading(MENU_HEADING_PHREEPOS, 'pos');
	if (admin_add_report_folder($id, TEXT_REPORTS,        'pos',      'fr')) $error = true;
	if (admin_add_report_folder($id, TEXT_RECEIPTS,       'pos:rcpt', 'ff')) $error = true;
	return $error;
  }

  function load_demo() {
    global $db;
	$error = false;

	return $error;
  }
  
}
?>