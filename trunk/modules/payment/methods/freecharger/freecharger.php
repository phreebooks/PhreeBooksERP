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
//  Path: /modules/payment/methods/freecharger/freecharger.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_PAYMENT_FREECHARGER_VERSION','3.3');
require_once(DIR_FS_MODULES . 'payment/classes/payment.php');
class freecharger extends payment {
  public $code        = 'freecharger'; // needs to match class name
  public $title       = MODULE_PAYMENT_FREECHARGER_TEXT_TITLE;
  public $description = MODULE_PAYMENT_FREECHARGER_TEXT_DESCRIPTION;
  public $sort_order  = 45;
  
  public function __construct(){
  	parent::__construct();
	$this->payment_fields = '';
  }
}
?>