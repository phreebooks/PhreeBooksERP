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
class admin extends \core\classes\admin {
	public $id 			= 'phreehelp';
	public $description = MODULE_PHREEHELP_DESCRIPTION;
	public $core		= true;
	public $sort_order  = 6;
	public $version		= '4.0-dev';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_PHREEHELP);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom' => 4.0,
		);
		$this->mainmenu["company"]['submenu']["help"] = array(
				'order' 		=> 1,
				'text'        => TEXT_HELP,
				'security_id' => SECURITY_ID_HELP,
				'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreehelp&amp;page=main', 'SSL'),
				'show_in_users_settings' => true,
				'params'      => 'target="_blank"',
		);
		
	    parent::__construct();
	}
	
	function upgrade(\core\classes\basis &$basis) {
		parent::upgrade($basis);
		$basis->DataBase->remove_configure('PHREEHELP_FORCE_RELOAD');
		if (!$basis->DataBase->table_exists(DB_PREFIX.'phreehelp')) $basis->DataBase->query("drop table ".DB_PREFIX.'phreehelp');
	}
	
	function loadHelpScreen (\core\classes\basis $basis){
		echo " <iframe src='http://www.phreebooks.com/documentation/{$basis->cInfo['idx']}'></iframe>";
		ob_flush();
	}
}
?>