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
//  Path: /modules/translator/classes/install.php
//

class translator_admin {
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	
  function translator_admin() {
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'phreedom' => 3.3,
	);
	// add new directories to store images and data
	$this->dirlist = array(
	  '../translator', // put it in the my_files directory, not the current company directory
	);
	// Load tables
	$this->tables = array(
	  TABLE_TRANSLATOR => "CREATE TABLE " . TABLE_TRANSLATOR  . " (
		  id smallint(5) unsigned NOT NULL auto_increment,
		  module varchar(48) NOT NULL DEFAULT '',
		  language char(5) NOT NULL DEFAULT '',
		  version char(6) NOT NULL DEFAULT '',
		  pathtofile varchar(255) collate utf8_unicode_ci NOT NULL DEFAULT '/',
		  defined_constant varchar(96) collate utf8_unicode_ci NOT NULL DEFAULT '',
		  translation text collate utf8_unicode_ci DEFAULT '',
		  translated enum('0','1') NOT NULL default '0',
		  PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
    );
  }

  function install($module) {
  }

  function initialize($module) {
  }

  function update($module) {
    global $db, $messageStack;
	$error = false;
	if (!$error) {
	  $admin->DataBase->write_configure('MODULE_'.strtoupper($module).'_STATUS', constant('MODULE_'.strtoupper($module).'_VERSION'));
   	  \core\classes\messageStack::add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_'.strtoupper($module).'_VERSION')), 'success');
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