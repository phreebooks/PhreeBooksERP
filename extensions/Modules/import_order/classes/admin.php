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
//  Path: /modules/import_order/classes/install.php
//@todo
namespace import_order\classes;
require_once (DIR_FS_ADMIN . 'modules/import_order/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'import_order';
	public $text		= MODULE_IMPORT_ORDER_TITLE;
	public $description = MODULE_IMPORT_ORDER_DESCRIPTION;
	public $version		= '1.0';

  	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.7,
	  	  'phreebooks' => 3.7,
		);
  	}

}
?>