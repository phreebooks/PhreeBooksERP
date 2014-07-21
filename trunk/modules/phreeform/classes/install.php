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
//  Path: /modules/phreeform/classes/install.php
//

class phreeform_admin {
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	
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
  }

  function install($module) {
	$error = false;
    // load all the active modules and load directory structure, reports
	global $loaded_modules;
	if (is_array($loaded_modules)) foreach ($loaded_modules as $method) {
	  require_once(DIR_FS_MODULES . $method . '/classes/install.php');
	  gen_pull_language($method, 'admin');
	  $cName    = $method . '_admin';
	  $mInstall = new $cName();
	  $mInstall->load_reports($method);
	}
	return $error;
  }

  function initialize($module) {
  }

  function update($module) {
    global $db, $messageStack;
	$error = false;
	if (MODULE_PHREEFORM_STATUS == '3.0') write_configure('PDF_APP', 'TCPDF');
	if (MODULE_PHREEFORM_STATUS < '3.5') {
//		$id = admin_add_report_heading(TEXT_MISC, 'cust');
//		if (admin_add_report_folder($id, TEXT_LETTERS, 'cust:ltr', 'fl')) $error = true;
//		$id = admin_add_report_heading(TEXT_MISC, 'vend');
//		if (admin_add_report_folder($id, TEXT_LETTERS, 'vend:ltr', 'fl')) $error = true;
	}
	if (!$error) {
	  write_configure('MODULE_' . strtoupper($module) . '_STATUS', constant('MODULE_' . strtoupper($module) . '_VERSION'));
   	  $messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_' . strtoupper($module) . '_VERSION')), 'success');
	}
	return $error;
  }

  function remove($module) {
  }

  function load_reports($module) {
	$error = false;
	$id = admin_add_report_heading(TEXT_MISC, 'misc');
	if (admin_add_report_folder($id, TEXT_REPORTS, 'misc',      'fr')) $error = true;
	if (admin_add_report_folder($id, TEXT_FORMS,   'misc:misc', 'ff')) $error = true;
	return $error;
  }

  function load_demo() {
  }

}
?>