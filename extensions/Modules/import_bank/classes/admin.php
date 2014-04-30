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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/import_bank/classes/admin.php
//
namespace import_bank\classes;
class admin extends \core\classes\admin {
	public $id 			= 'import_bank';
	public $text		= TEXT_IMPORT_BANK_STATEMENT;
	public $description = MODULE_IMPORT_BANK_DESCRIPTION;
	public $version		= '2.1';

	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.0,
		  'payment'    => 3.0,
		  'phreebooks' => 3.0,
		  'contacts'   => 3.1,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'QUESTION_POSTS'               => '2200',
	      'DEBIT_CREDIT_DESCRIPTION'	 => 'crediting',
		);
		// Load tables
		$this->tables = array(
			TABLE_IMPORT_BANK => "CREATE TABLE " . TABLE_IMPORT_BANK . " (
	  			kt_id 					int(11) NOT NULL auto_increment,
	  			description         	varchar(64)   NOT NULL default '',
	  			gl_acct_id          	varchar(15)   NOT NULL default '',
	  			bank_account        	varchar(64)   NOT NULL default '',
	  			PRIMARY KEY (kt_id)
	  		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	    );
	    parent::__construct();
	}

  	function install($path_my_files, $demo = false) {
    	global $db, $messageStack;
    	parent::install($path_my_files, $demo);
		if (!db_field_exists(TABLE_CONTACTS, 'bank_account_1')) {
			$sql = "select id from " . TABLE_EXTRA_FIELDS . " where module_id = 'contacts' and field_name = 'bank_account'";
			$result = $db->Execute($sql);
			if ( $result->RecordCount() == 0 ){
				$result = $db->Execute("select id from " . TABLE_EXTRA_TABS . " where module_id='contacts' and tab_name = 'import_banking'");
				if ( $result->RecordCount() == 0 ){
					$entry = array(	'module_id'	=> 'contacts',
					 				'tab_name'	=> 'import_banking',
									'sort_order'=> '100' );
					db_perform(TABLE_EXTRA_TABS, $entry, 'insert');
					$tab_id = $db->insert_ID();
				}else {
					$tab_id = $result->fields['id'];
				}
				$entry = array(	'module_id'	  => 'contacts',
					 			'tab_id'	  => $tab_id,
								'entry_type'  => 'text',
								'field_name'  => 'bank_account_1',
								'description' => 'Bank Account',
								'params'	  => 'a:4:{s:4:"type";s:4:"text";s:12:"contact_type";s:16:"customer:vendor:";s:6:"length";i:32;s:7:"default";s:0:"";}');
				db_perform(TABLE_EXTRA_FIELDS, $entry, 'insert');
				//$db->Execute("INSERT INTO " . TABLE_EXTRA_FIELDS . " VALUES ('', 'contacts', ". $tab_id .",'text', 'bank_account', 'Bank Account','c:v:', );");
				$db->Execute("ALTER TABLE ".TABLE_CONTACTS." ADD bank_account_1 varchar(32) default NULL");
			}
		}
  	}

	function upgrade() {
	  	global $db;
	  	parent::upgrade();
	  	$sql = "select id from " . TABLE_EXTRA_FIELDS . " where module_id = 'contacts' and field_name = 'bank_account'";
		$result = $db->Execute($sql);
		if ( $result->RecordCount() == 0 ){
			$result = $db->Execute("select id from " . TABLE_EXTRA_TABS . " where module_id='contacts' and tab_name = 'import_banking'");
			if ( $result->RecordCount() == 0 ){
				$db->Execute("INSERT INTO " . TABLE_EXTRA_TABS . " VALUES('', 'contacts','import_banking','100')");
				$tab_id = $db->insert_ID();
			}else {
				$tab_id = $result->fields['id'];
			}
			$db->Execute("INSERT INTO " . TABLE_EXTRA_FIELDS . " VALUES ('', 'contacts', ". $tab_id .",'text', 'bank_account', 'Bank Account','c:v:', 'a:4:{s:4:'type';s:4:'text';s:12:'contact_type';s:16:'customer:vendor:';s:6:'length';i:32;s:7:'default';s:0:'';}' );");
		}
	}

}
?>