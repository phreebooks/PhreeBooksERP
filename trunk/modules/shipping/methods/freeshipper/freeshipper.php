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
//  Path: /modules/shipping/methods/freeshipper/freeshipper.php
//
namespace shipping\methods\freeshipper;
class freeshipper extends \shipping\classes\shipping {
	public $id				= 'freeshipper'; // needs to match class name
  	public $text			= MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE;
  	public $description		= MODULE_SHIPPING_FREESHIPPER_TEXT_DESCRIPTION;
  	public $sort_order		= 25;
  	public $version			= '3.2';
  	public $shipping_cost	= 0.00;
  	public $handling_cost	= 1.00;

	function quote($pkg = '') {
    	global $shipping_defaults;
  		$arrRates = array();
		foreach ($shipping_defaults['service_levels'] as $key => $value) {
	  		if (defined($this->id.'_'.$key)) {
				$arrRates[$this->id][$key]['book']  = '';
	    		$arrRates[$this->id][$key]['quote'] = MODULE_SHIPPING_FREESHIPPER_COST + MODULE_SHIPPING_FREESHIPPER_HANDLING;
	    		$arrRates[$this->id][$key]['cost']  = '';
	  		}
		}
		return array('result' => 'success', 'rates' => $arrRates);
  	}
}
?>