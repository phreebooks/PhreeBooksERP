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
//  Path: /modules/shipping/methods/flat/flat.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace shipping\methods\flat;
class flat extends \shipping\classes\shipping {
	public $id				= 'flat'; // needs to match class name
  	public $text			= MODULE_SHIPPING_FLAT_TITLE;
  	public $description		= MODULE_SHIPPING_FLAT_TEXT_DESCRIPTION;
  	public $sort_order		= 20;
  	public $shipping_cost	= 5.00;
  	public $version			= '3.2';

  	function quote($pkg = '') {
    	global $shipping_defaults;
		$arrRates = array();
		foreach ($shipping_defaults['service_levels'] as $key => $value) {
	  		if (defined($this->id.'_'.$key)) {
	    		$arrRates[$this->id][$key]['book']  = '';
	    		$arrRates[$this->id][$key]['quote'] = MODULE_SHIPPING_FLAT_COST;
	    		$arrRates[$this->id][$key]['cost']  = '';
	  		}
		}
		return array('result' => 'success', 'rates' => $arrRates);
  	}
}
?>