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
//  Path: /modules/payment/methods/cod/cod.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace payment\methods\cod;
class cod extends \payment\classes\payment {
	public $id        	= 'cod'; // needs to match class name
  	public $text		= TEXT_CASH_ON_DELIVERY;
  	public $description = MODULE_PAYMENT_COD_TEXT_DESCRIPTION;
  	public $sort_order  = 40;
  	public $version		= '3.3';

  	public function __construct(){
		parent::__construct();
  		$this->payment_fields = implode(':', array($_POST['bill_primary_name']));
  	}
}
?>