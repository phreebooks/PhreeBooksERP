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
//  Path: /modules/phreehelp/classes/admin.php
//
namespace phreehelp\classes;
require_once ('/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'phreehelp';
	public $description = MODULE_PHREEHELP_DESCRIPTION;
	public $core		= true;
	public $sort_order  = 6;
	public $version		= '3.6';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_PHREEHELP);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom' => 3.6,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'PHREEHELP_FORCE_RELOAD' => '1',
		);
		// Load tables
		$this->tables = array(
		  TABLE_PHREEHELP => "CREATE TABLE " . TABLE_PHREEHELP . " (
			  id int(10) unsigned NOT NULL auto_increment,
			  parent_id int(11) NOT NULL default '0',
			  doc_type enum('0','d') collate utf8_unicode_ci NOT NULL default 'd',
			  doc_lang char(5) collate utf8_unicode_ci default 'en_us',
			  doc_pos varchar(64) collate utf8_unicode_ci default NULL,
			  doc_url varchar(255) collate utf8_unicode_ci default NULL,
			  doc_index varchar(255) collate utf8_unicode_ci default NULL,
			  doc_title varchar(255) collate utf8_unicode_ci default NULL,
			  doc_text text collate utf8_unicode_ci,
			  PRIMARY KEY (id),
			  FULLTEXT KEY doc_title (doc_title, doc_text)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	    );
	    parent::__construct();
	}

	function upgrade(\core\classes\basis &$basis) {
	    parent::upgrade( $basis);
		write_configure(PHREEHELP_FORCE_RELOAD, '1');
	}
}
?>