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
//  Path: /modules/phreeform/classes/admin.php
//
namespace phreeform\classes;
class admin extends \core\classes\admin {
	public $id 			= 'phreeform';
	public $text		= MODULE_PHREEFORM_TITLE;
	public $description = MODULE_PHREEFORM_DESCRIPTION;
	public $sort_order  = 8;
	public $version		= '3.6';
	
	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'  => 3.6,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'PF_DEFAULT_COLUMN_WIDTH' => '25',
		  'PF_DEFAULT_MARGIN'       => '8',
		  'PF_DEFAULT_TITLE1'       => '%reportname%',
		  'PF_DEFAULT_TITLE2'       => 'Report Generated %date%',
		  'PF_DEFAULT_PAPERSIZE'    => 'Letter:216:282',
		  'PF_DEFAULT_ORIENTATION'  => 'P',
		  'PF_DEFAULT_TRIM_LENGTH'  => '25',
		  'PF_DEFAULT_ROWSPACE'     => '2',
		  'PDF_APP'                 => 'TCPDF', // other options: FPDF
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'phreeform',
		  'phreeform/images',
		);
		// Load tables
		$this->tables = array(
		  TABLE_PHREEFORM => "CREATE TABLE " . TABLE_PHREEFORM . " (
				id int(10) unsigned NOT NULL auto_increment,
				parent_id int(11) NOT NULL default '0',
				doc_type enum('0','c','s') NOT NULL default 's',
				doc_title varchar(64) default '',
				doc_group varchar(9) default NULL,
				doc_ext varchar(3) default NULL,
				security varchar(255) default 'u:0;g:0',
				create_date date default NULL,
				last_update date default NULL,
				PRIMARY KEY (id)
			  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	    );
	    parent::__construct();
	}

  	function install($path_my_files, $demo = false) {
		global $admin_classes;
		parent::install($path_my_files, $demo);
		if (is_array($admin_classes)) foreach ($admin_classes as $module) {
		  	gen_pull_language($module, 'admin');
	  		$module->load_reports();
		}
  	}

  	function upgrade() {
    	global $db, $messageStack;
    	parent::upgrade();
		if (MODULE_PHREEFORM_STATUS == '3.0') write_configure('PDF_APP', 'TCPDF');
		if (MODULE_PHREEFORM_STATUS < '3.5') {
//			$id = $this->add_report_heading(TEXT_MISC, 'cust');
//			if ($this->add_report_folder($id, TEXT_LETTERS, 'cust:ltr', 'fl')) $error = true;
//			$id = $this->add_report_heading(TEXT_MISC, 'vend');
//			if ($this->add_report_folder($id, TEXT_LETTERS, 'vend:ltr', 'fl')) $error = true;
		}
	}

	function load_reports() {
		$id = $this->add_report_heading(TEXT_MISC, 'misc');
		$this->add_report_folder($id, TEXT_REPORTS, 'misc',      'fr');
		$this->add_report_folder($id, TEXT_FORMS,   'misc:misc', 'ff');
		parent::load_reports();
	}

}
?>