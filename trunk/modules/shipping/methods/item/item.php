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
//  Path: /modules/shipping/methods/item/item.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_SHIPPING_ITEM_VERSION','3.2');

class item {
  function __construct() {
    $this->code = 'item';
  }

  function keys() {
    return array(
	  array('key' => 'MODULE_SHIPPING_ITEM_TITLE',      'default' => 'Item Shipping'),
	  array('key' => 'MODULE_SHIPPING_ITEM_COST',       'default' => '2.50'),
	  array('key' => 'MODULE_SHIPPING_ITEM_HANDLING',   'default' => '0.00'),
	  array('key' => 'MODULE_SHIPPING_ITEM_SORT_ORDER', 'default' => '30'),
	);
  }

  function update() {
    foreach ($this->keys() as $key) {
	  $field = strtolower($key['key']);
	  if (isset($_POST[$field])) write_configure($key['key'], $_POST[$field]);
	}
  }

  function quote($pkg = '') {
	if (!$pkg->pkg_item_count) $pkg->pkg_item_count = 1;
	$arrRates = array();
	$arrRates[$this->code]['GND']['book']  = '';
	$arrRates[$this->code]['GND']['quote'] = ($pkg->pkg_item_count * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING;
	$arrRates[$this->code]['GND']['cost']  = '';
	return array('result' => 'success', 'rates' => $arrRates);
  }
}
?>