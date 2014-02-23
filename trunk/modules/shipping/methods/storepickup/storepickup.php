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
//  Path: /modules/shipping/methods/storepickup/storepickup.php
//
namespace shipping\methods\storepickup;
// Revision history
// 2011-07-01 - Added version number for revision control
class storepickup extends \shipping\classes\shipping {
	public $id				= 'storepickup'; // needs to match class name
  	public $text			= MODULE_SHIPPING_STOREPICKUP_TEXT_TITLE;
  	public $description		= MODULE_SHIPPING_STOREPICKUP_TEXT_DESCRIPTION;
  	public $sort_order		= 35;
  	public $version			= '3.2';
  	public $shipping_cost	= 0.00;
  	public $handling_cost;

	function quote($pkg = '') {
		$arrRates = array();
		$arrRates[$this->id]['GND']['book']  = '';
		$arrRates[$this->id]['GND']['quote'] = MODULE_SHIPPING_STOREPICKUP_COST;
		$arrRates[$this->id]['GND']['cost']  = '';
		return array('result' => 'success', 'rates' => $arrRates);
	}

}
?>