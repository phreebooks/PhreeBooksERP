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
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_HELP);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom' => 4.0,
		);
		$temp = new \core\classes\menuItem (1, TEXT_HELP,	'action=loadHelpScreen');
		$temp->params = 'target="_blank"';
		$this->mainmenu["company"]->submenu ["help"]  = $temp;
	    parent::__construct();
	}
	
	function upgrade(\core\classes\basis &$basis) {
		parent::upgrade($basis);
		$basis->DataBase->remove_configure('PHREEHELP_FORCE_RELOAD');
		if (!$basis->DataBase->table_exists(DB_PREFIX.'phreehelp')) $basis->DataBase->query("drop table ".DB_PREFIX.'phreehelp');
	}
	
	function loadHelpScreen (\core\classes\basis $basis){
		$basis->observer->send_menu($basis);
		\core\classes\messageStack::debug_log("executing ".__METHOD__ ); 
		//@todo change because cross domain isn't allowed.
		?>
		<div data-options="region:'center',href:'https://www.phreesoft.com/'"></div>
		<?php 
		$basis->observer->send_footer($basis);
	}
}
?>