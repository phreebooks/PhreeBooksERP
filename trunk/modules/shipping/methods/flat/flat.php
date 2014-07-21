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
define('MODULE_SHIPPING_FLAT_VERSION','3.2');

class flat {
  function __construct() {
    $this->code = 'flat';
  }

  function keys() {
    return array(
      array('key' => 'MODULE_SHIPPING_FLAT_TITLE',      'default' => 'Flat Shipping'),
      array('key' => 'MODULE_SHIPPING_FLAT_COST',       'default' => '5.00'),
	  array('key' => 'MODULE_SHIPPING_FLAT_SORT_ORDER', 'default' => '20'),
	);
  }

  function update() {
    foreach ($this->keys() as $key) {
	  $field = strtolower($key['key']);
	  if (isset($_POST[$field])) write_configure($key['key'], $_POST[$field]);
	}
  }

  function quote($pkg = '') {
    global $shipping_defaults;
	$arrRates = array();
	foreach ($shipping_defaults['service_levels'] as $key => $value) {
	  if (defined($this->code.'_'.$key)) {
	    $arrRates[$this->code][$key]['book']  = '';
	    $arrRates[$this->code][$key]['quote'] = MODULE_SHIPPING_FLAT_COST;
	    $arrRates[$this->code][$key]['cost']  = '';
	  }
	}
	return array('result' => 'success', 'rates' => $arrRates);
  }

}
?>