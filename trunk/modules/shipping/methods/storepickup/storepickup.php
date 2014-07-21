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
//  Path: /modules/shipping/methods/storepickup/storepickup.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_SHIPPING_STOREPICKUP_VERSION','3.2');

class storepickup {
  function __construct() {
    $this->code = 'storepickup';
  }

  function keys() {
    return array(
	  array('key' => 'MODULE_SHIPPING_STOREPICKUP_TITLE',      'default' => 'Store Pickup'),
	  array('key' => 'MODULE_SHIPPING_STOREPICKUP_COST',       'default' => '0.00'),
	  array('key' => 'MODULE_SHIPPING_STOREPICKUP_SORT_ORDER', 'default' => '35'),
	);
  }

  function update() {
    foreach ($this->keys() as $key) {
	  $field = strtolower($key['key']);
	  if (isset($_POST[$field])) write_configure($key['key'], $_POST[$field]);
	}
  }

  function quote($pkg = '') {
	$arrRates = array();
	$arrRates[$this->code]['GND']['book']  = '';
	$arrRates[$this->code]['GND']['quote'] = MODULE_SHIPPING_STOREPICKUP_COST;
	$arrRates[$this->code]['GND']['cost']  = '';
	return array('result' => 'success', 'rates' => $arrRates);
  }

}
?>