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
//  Path: /modules/phreepos/classes/admin.php
//
namespace phreepos\classes;
class admin extends \core\classes\admin {
	public $id 			= 'phreepos';
	public $text		= MODULE_PHREEPOS_TITLE;
	public $description = MODULE_PHREEPOS_DESCRIPTION;
	public $version		= '3.9';

	function __construct(){
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'contacts'  => '3.7.1',
		  'inventory' => '3.6',
		  'phreebooks'=> '3.6',
		  'phreedom'  => '3.6',
		  'payment'   => '3.6',
		  'phreeform' => '3.6',
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
		  'PHREEPOS_ENABLE_DIRECT_PRINTING'       => 0,  // this enables or disables direct printing.
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
	    parent::__construct();
	}

	function install($path_my_files, $demo = false){
		global $db;
		parent::install($path_my_files, $demo);
		foreach (gen_get_store_ids() as $store){
		  	$sql_data_array = array(
		  		'store_id'    		  	=> $store['id'],
		  		'gl_acct_id'  		  	=> AR_SALES_RECEIPTS_ACCOUNT,
		  		'description' 		  	=> $store['text'],
		  	    'rounding_gl_acct_id' 	=> AR_SALES_RECEIPTS_ACCOUNT,
			  	'dif_gl_acct_id'	  	=> AR_SALES_RECEIPTS_ACCOUNT,
				'printer_name'		  	=> '',
				'printer_starting_line' => '',
				'printer_closing_line' 	=> '',
				'printer_open_drawer' 	=> '',
		  	);
		  	db_perform(TABLE_PHREEPOS_TILLS, $sql_data_array);
	  	}
	}

	function upgrade() {
	    global $db;
		parent::upgrade();
		if (version_compare($this->status, '3.4', '<') ) {
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
		if (!db_field_exists(TABLE_PHREEPOS_TILLS, 'tax_id')) $db->Execute("ALTER TABLE " . TABLE_PHREEPOS_TILLS . " ADD tax_id INT(11) default '-1' AFTER max_discount");
  	}

	function delete($path_my_files) {
	    global $db;
	    parent::delete($path_my_files);
	    // Don't allow delete if there is activity
		$sql = "select id from " . TABLE_JOURNAL_MAIN . " where journal_id = '19'";
		$result = $db->Execute($sql);
		if ($result->RecordCount() <> 0 ) throw new \core\classes\userException(ERROR_CANT_DELETE);
	}

	function load_reports() {
		$id = $this->add_report_heading(MENU_HEADING_PHREEPOS, 'pos');
		$this->add_report_folder($id, TEXT_REPORTS,        'pos',      'fr');
		$this->add_report_folder($id, TEXT_RECEIPTS,       'pos:rcpt', 'ff');
		parent::load_reports();
	}

}
?>