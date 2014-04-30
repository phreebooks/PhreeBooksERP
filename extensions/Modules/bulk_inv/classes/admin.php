<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
//  Path: /modules/bulk_inv/classes/admin.php
//
namespace bulk_inv\classes;
class admin extends \core\classes\admin {
	public $id 			= 'bulk_inv';
	public $text		= TEXT_BULK_INVENTORY_TOOL;
	public $description = MODULE_BULK_INV_DESCRIPTION;
	public $version		= '1.0';

	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
	  	  'phreedom'  => '3.3',
	  	  'inventory' => '3.3',
		);
		parent::__construct();
  	}
}
?>