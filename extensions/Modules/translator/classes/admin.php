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
//  Path: /modules/translator/classes/admin.php
//
namespace translator\classes;
require_once ('/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'translator';
	public $description = MODULE_TRANSLATOR_DESCRIPTION;
	public $version		= '3.3';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_TRANSLATOR);
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
			  translation text collate utf8_unicode_ci NOT NULL DEFAULT '',
			  translated enum('0','1') NOT NULL default '0',
			  PRIMARY KEY (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	    );
	    parent::__construct();
	}

}
?>