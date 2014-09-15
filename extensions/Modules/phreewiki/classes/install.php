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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/import_bank/classes/install.php
// @todo

class phreewiki_admin {
 	function phreewiki_admin() {
		$this->notes = array(); // placeholder for any operational notes
		$this->prerequisites = array( // modules required and rev level for this module to work properly
	  		'phreedom'   => '3.0',
		);
		// Load configuration constants for this module, must match entries in admin tabs
  		$this->keys = array(
 			'PHREEWIKI_REG_EXP_SEARCH' 			=> false,
			'PHREEWIKI_CASE_SENSITIVE_SEARCH'	=> false,
			'PHREEWIKI_ANIMATE'					=> true,
			'PHREEWIKI_GENERATE_RSS'			=> false,
			'PHREEWIKI_OPEN_NEW_WINDOW'			=> true,
			'PHREEWIKI_TROGGLE_LINKS'			=> false,
			'PHREEWIKI_CONFIRM_DELETE'			=> true,
			'PHREEWIKI_INSERT_TABS'				=> false,
			'PHREEWIKI_MAX_EDIT_ROWS'			=> 30,
		);
		// add new directories to store images and data
		$this->dirlist = array(
		);
	// Load tables
	$this->tables = array(
	  TABLE_PHREEWIKI =>"CREATE TABLE ". TABLE_PHREEWIKI ." (
		`id` int(11) NOT NULL auto_increment,
		`title` varchar(255) NOT NULL default '',
		`body` text NOT NULL,
		`fields` text NOT NULL,
		`modified` varchar(128) NOT NULL default '',
		`created` varchar(128) NOT NULL default '',
		`modifier` varchar(255) NOT NULL default '',
		`creator` varchar(255) NOT NULL default '',
		`version` int(11) NOT NULL default '0',
		`tags` varchar(255) NOT NULL default '',
		PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",

	  TABLE_PHREEWIKI_VERSION =>"CREATE TABLE ".TABLE_PHREEWIKI_VERSION." (
		`id` int(11) NOT NULL auto_increment,
		`title` varchar(255) NOT NULL default '',
		`body` text NOT NULL,
		`fields` text NOT NULL,
		`modified` varchar(128) NOT NULL default '',
		`modifier` varchar(255) NOT NULL default '',
		`version` int(11) NOT NULL default '0',
		`tags` varchar(255) NOT NULL default '',
		`oid` INT(11) NOT NULL,
		PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
    );
    $this->pluginArray = array(
      'ArchivedTimeline'     => "ArchivedTimeline",
	  'CommentPlugin'        => "CommentPlugin",
	  'CommentTabPlugin'     => "CommentTabPlugin",
	  'GenRssPlugin'         => "GenRssPlugin",
	  'LoadExtPlugin'        => "LoadExtPlugin",
	  'NestedSlidersPlugin'  => "NestedSlidersPlugin",
	  'RecentTiddlersPlugin' => "RecentTiddlersPlugin",
	  'SelectThemePlugin'    => "SelectThemePlugin",
	  'XMLReader2'           => "XMLReader2",
	  'wikibar'              => "wikibar",

	/*
	  'UploadPlugin' => "UploadPlugin",
	  'BigThemePack' => "BigThemePack",
	  'Breadcrumbs2' => "BreadCrumbs2",
	*/
    );
  }

  function install($module) {
    global $admin, $messageStack;
		$error = false;
		require_once(DIR_FS_MODULES . 'phreewiki/functions/phreewiki.php');
		foreach ($this->pluginArray as $key => $value){
			$error = install_plugin($key, $value);
		}
    return $error;
  }

  function initialize($module) {
  }

  function update($module) {
  	global $admin;
  	write_configure('MODULE_' . strtoupper($module) . '_STATUS', constant('MODULE_' . strtoupper($module) . '_VERSION'));
  }

  function remove($module) {
  }

  function load_reports($module) {
  }

  function load_demo() {
  }
}
?>