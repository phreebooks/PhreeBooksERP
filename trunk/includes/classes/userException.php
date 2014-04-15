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
//  Path: /includes/classes/userException.php
//
// this class will allow us to catch user errors and return them to theire pevious page.
namespace core\classes;
class userException extends \Exception {
	public $ReturnToModule;
	public $ReturnToPage;
	public $ReturnToTemplate;

	function __construct ($message = "",  $Module = "phreedom", $Page = "main", $template = "template_crash", $code = 0,  Exception $previous = NULL){
		$this->ReturnToModule 	= $Module;
		$this->ReturnToPage 	= $Page;
		$this->ReturnToTemplate = $template;
		parent::__construct($message, $code, $previous);
	}

	function __destruct(){
		//print_r($this);
	}
}
?>