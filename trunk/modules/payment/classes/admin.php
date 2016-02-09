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
//  Path: /modules/payment/classes/admin.php
//
namespace payment\classes;
require_once (DIR_FS_ADMIN . 'modules/payment/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'payment';
	public $text		= TEXT_PAYMENT_MODULE;
	public $description = MODULE_PAYMENT_DESCRIPTION;
	public $core		= true;
	public $sort_order  = 7;
	public $version		= '3.6';

  	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
	  	  'contacts'   => 3.71,
	  	  'phreedom'   => 3.6,
	  	  'phreebooks' => 3.6,
		);
		$this->mainmenu["company"]->submenu ["configuration"]->submenu ["payment"]  = new \core\classes\menuItem (TEXT_PAYMENT_MODULE, TEXT_PAYMENT_MODULE,	'module=payment&amp;page=admin', SECURITY_ID_CONFIGURATION);
		$this->mainmenu["company"]->submenu ["configuration"]->submenu ["payment"]->required_module = 'MODULE_PAYMENT_STATUS';
		parent::__construct();
  	}
}
?>