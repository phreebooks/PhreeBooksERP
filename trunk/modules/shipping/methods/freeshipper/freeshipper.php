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
//  Path: /modules/shipping/methods/freeshipper/freeshipper.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_SHIPPING_FREESHIPPER_VERSION','3.2');

class freeshipper {
  function __construct() {
    $this->code = 'freeshipper';
  }

  function keys() {
    return array(
      array('key' => 'MODULE_SHIPPING_FREESHIPPER_TITLE',      'default' => MODULE_SHIPPING_FREESHIPPER_TITLE_SHORT),
      array('key' => 'MODULE_SHIPPING_FREESHIPPER_COST',       'default' => '0.00', 'properties' => 'size="10" style="text-align:right"'),
      array('key' => 'MODULE_SHIPPING_FREESHIPPER_HANDLING',   'default' => '0.00', 'properties' => 'size="10" style="text-align:right"'),
      array('key' => 'MODULE_SHIPPING_FREESHIPPER_SORT_ORDER', 'default' => '25'),
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
	    $arrRates[$this->code][$key]['quote'] = MODULE_SHIPPING_FREESHIPPER_COST + MODULE_SHIPPING_FREESHIPPER_HANDLING;
	    $arrRates[$this->code][$key]['cost']  = '';
	  }
	}
	return array('result' => 'success', 'rates' => $arrRates);
  }
}
?>