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
//  Path: /modules/doc_ctl/classes/admin.php
//
namespace doc_ctl\classes;
require_once ('/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'doc_ctl';
	public $description = MODULE_DOC_CTL_DESCRIPTION;
	public $version		= '3.6';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_DOCUMENT_CONTROLE);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => '3.3',
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'doc_ctl',
		  'doc_ctl/docs',
		);
		// Load tables
		$this->tables = array(
			TABLE_DC_DOCUMENT => "CREATE TABLE " . TABLE_DC_DOCUMENT . " (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  parent_id bigint(20) unsigned NOT NULL,
			  `position` bigint(20) unsigned NOT NULL,
			  `left` bigint(20) unsigned NOT NULL,
			  `right` bigint(20) unsigned NOT NULL,
			  level bigint(20) unsigned NOT NULL,
			  title varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
			  type varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci default NULL,
			  file_name varchar(255) collate utf8_unicode_ci default NULL,
			  doc_ext varchar(6) collate utf8_unicode_ci default 'txt',
			  doc_size int(11)  NOT NULL default '0',
			  doc_owner int(11) NOT NULL default '0',
			  lock_id int(11) NOT NULL default '0',
			  checkout_id int(11) NOT NULL default '0',
			  description varchar(255) collate utf8_unicode_ci default NULL,
			  revision int(8) NOT NULL default '0',
			  security varchar(255) collate utf8_unicode_ci default NULL,
			  bookmarks varchar(255) collate utf8_unicode_ci default NULL,
			  create_date date default NULL,
			  last_update date default NULL,
			  params text collate utf8_unicode_ci default NULL,
			  PRIMARY KEY (`id`),
			  FULLTEXT KEY title (file_name, description)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	    );
	    parent::__construct();
	}

  	function install($path_my_files, $demo = false) {
		global $db;
		parent::install($path_my_files, $demo);
		require_once(DIR_FS_MODULES . 'doc_ctl/defaults.php');
		$right = (INSTALL_NUMBER_OF_DRIVES+1)*2;
		$db->Execute("TRUNCATE TABLE " . TABLE_DC_DOCUMENT);
		$db->Execute("INSERT INTO " . TABLE_DC_DOCUMENT . " (`id`, `parent_id`, `position`, `left`, `right`, `level`, `title`, `type`)
			VALUES (1, 0, 0, 1, " . $right . ", 0, 'ROOT', '')");
		for ($i = 0; $i < INSTALL_NUMBER_OF_DRIVES; $i++) {
		  $id    = $i+2;
		  $left  = ($i+1)*2;
		  $right = $left+1;
		  $title = $i==0 ? TEXT_HOME : (TEXT_DRIVE.$i);
		  $db->Execute("INSERT INTO " . TABLE_DC_DOCUMENT . " (`id`, `parent_id`, `position`, `left`, `right`, `level`, `title`, `type`)
		  	VALUES (" . $id . ", 1, 0, " . $left . ", " . $right . ", 1, '" . $title . "', 'drive')");
		}
	}
}
?>