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
//  Path: /modules/xml_builder/classes/admin.php
//
namespace xml_builder\classes;
class admin extends \core\classes\admin{
	public $id 			= 'xml_builder';
	public $description = TEXT_THIS_MODULE_BUILDS_THE_XML_INFORMATION_FILES;
	public $version		= '1.0';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_XML_BUILDER);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => '3.3',
		  'phreebooks' => '3.3',
		);
		parent::__construct();
	}

}
?>