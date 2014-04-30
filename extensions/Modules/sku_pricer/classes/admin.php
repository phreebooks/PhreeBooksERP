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
//  Path: /modules/sku_pricer/classes/admin.php
//
namespace sku_pricer\classes;
class admin extends \core\classes\admin {
	public $id 			= 'sku_pricer';
	public $text		= TEXT_SKU_PRICER;
	public $description = MODULE_SKU_PRICER_DESCRIPTION;
	public $version		= '1.0';

  function __construct() {
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'phreedom'   => 3.0,
	  'phreebooks' => 3.0,
	);
	parent::__construct();
  }
}
?>