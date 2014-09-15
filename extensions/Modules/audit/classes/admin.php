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
//  Path: /modules/import_bank/classes/admin.php
//
namespace audit\classes;
require_once ('/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'audit';
	public $text		= TEXT_AUDIT;
	public $description = MODULE_AUDIT_DESCRIPTION;
	public $version		= '1.0';

  	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
	  	  'phreedom'   => 3.0,
	  	  'phreebooks' => 3.0,
	  	  'contacts'   => 3.1,
		);
		// Load configuration constants for this module, must match entries in admin tabs
    	$this->keys = array(
    	  'AUDIT_DEBIT_NUMBER'               => ''
    	);
    	parent::__construct();
  	}
}
?>