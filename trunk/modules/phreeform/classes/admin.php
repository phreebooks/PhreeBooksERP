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
//  Path: /modules/phreeform/classes/admin.php
//
namespace phreeform\classes;
require_once (DIR_FS_ADMIN . 'modules/phreeform/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'phreeform';
	public $description = MODULE_PHREEFORM_DESCRIPTION;
	public $sort_order  = 8;
	public $version		= '3.6';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_PHREEFORM);
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
		// Set the title menu
		// Set the menus
		$this->mainmenu["tools"]->submenu ["reports"]  		= new \core\classes\menuItem (25, 	TEXT_REPORTS,	'module=phreeform&amp;page=main', 				SECURITY_ID_PHREEFORM,	'MODULE_PHREEFORM_STATUS');
		$this->mainmenu["customers"]->submenu ["reports"]  	= new \core\classes\menuItem (999, 	TEXT_REPORTS,	'module=phreeform&amp;page=main&amp;tab=cust', 	SECURITY_ID_PHREEFORM,	array('MODULE_CONTACTS_STATUS',  'MODULE_PHREEFORM_STATUS'));
		$this->mainmenu["employees"]->submenu ["reports"]  	= new \core\classes\menuItem (999, 	TEXT_REPORTS,	'module=phreeform&amp;page=main&amp;tab=hr', 	SECURITY_ID_PHREEFORM,	array('MODULE_CONTACTS_STATUS',  'MODULE_PHREEFORM_STATUS'));
		$this->mainmenu["vendors"]->submenu ["reports"]  	= new \core\classes\menuItem (999, 	TEXT_REPORTS,	'module=phreeform&amp;page=main&amp;tab=vend', 	SECURITY_ID_PHREEFORM,	array('MODULE_CONTACTS_STATUS',  'MODULE_PHREEFORM_STATUS'));
		$this->mainmenu["inventory"]->submenu ["reports"]  	= new \core\classes\menuItem (999, 	TEXT_REPORTS,	'module=phreeform&amp;page=main&amp;tab=inv', 	SECURITY_ID_PHREEFORM,	array('MODULE_INVENTORY_STATUS', 'MODULE_PHREEFORM_STATUS'));
		$this->mainmenu["banking"]->submenu ["reports"]  	= new \core\classes\menuItem (999, 	TEXT_REPORTS,	'module=phreeform&amp;page=main&amp;tab=bnk', 	SECURITY_ID_PHREEFORM,	array('MODULE_PHREEBOOKS_STATUS', 'MODULE_PHREEFORM_STATUS'));
		$this->mainmenu["gl"]->submenu ["reports"]  		= new \core\classes\menuItem (999, 	TEXT_REPORTS,	'module=phreeform&amp;page=main&amp;tab=gl', 	SECURITY_ID_PHREEFORM,	array('MODULE_PHREEBOOKS_STATUS', 'MODULE_PHREEFORM_STATUS'));
		$this->mainmenu["quality"]->submenu ["reports"] 	= new \core\classes\menuItem (999, 	TEXT_REPORTS,	'module=phreeform&amp;page=main', 				SECURITY_ID_PHREEFORM,	array('MODULE_CP_ACTION_STATUS', 'MODULE_DOC_CTL_STATUS', 'MODULE_PHREEFORM_STATUS'));
	    parent::__construct();
	}

  	function install($path_my_files, $demo = false) {
		global $admin;
		parent::install($path_my_files, $demo);
		foreach ($admin->classes as $module_class) {
	  		$module_class->load_reports();
		}
  	}

  	function upgrade(\core\classes\basis &$basis) {
    	global $admin, $messageStack;
    	parent::upgrade($basis);
    	if (version_compare($this->status, '3.3', '==') ) $admin->DataBase->write_configure('PDF_APP', 'TCPDF');
    	if (version_compare($this->status, '3.5', '<') ) {
//			$id = $this->add_report_heading(TEXT_MISCELLANEOUS, 'cust');
//			$this->add_report_folder($id, TEXT_LETTERS, 'cust:ltr', 'fl');
//			$id = $this->add_report_heading(TEXT_MISCELLANEOUS, 'vend');
//			$this->add_report_folder($id, TEXT_LETTERS, 'vend:ltr', 'fl');
		}
	}

	function load_reports() {
		$id = $this->add_report_heading(TEXT_MISCELLANEOUS, 'misc');
		$this->add_report_folder($id, TEXT_REPORTS, 'misc',      'fr');
		$this->add_report_folder($id, TEXT_FORMS,   'misc:misc', 'ff');
		parent::load_reports();
	}

}
?>