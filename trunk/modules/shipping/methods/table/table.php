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
//  Path: /modules/shipping/methods/table/table.php
//
namespace shipping\methods\table;
class table  extends \shipping\classes\shipping {
	public $id				= 'table'; // needs to match class name
  	public $text			= MODULE_SHIPPING_TABLE_TEXT_TITLE;
  	public $description		= MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION;
  	public $sort_order		= 40;
  	public $version			= '3.2';
  	public $shipping_cost	= '25:8.50,50:5.50,10000:0.00';
  	public $handling_cost	= 1.00;
  	
  	function __construct() {
  		$this->keys[] = array('key' => 'MODULE_SHIPPING_TABLE_MODE',		'default' => 'weight',  'text' => MODULE_SHIPPING_TABLE_MODE_DESC);
    	parent::__construct();
  	}

	function configure($key) {
		switch ($key) {
	  		case 'MODULE_SHIPPING_TABLE_MODE':
				$temp = array(
		  		  array('id' => 'price',  'text' => TEXT_PRICE),
		  		  array('id' => 'weight', 'text' => TEXT_WEIGHT),
				);
				$html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
				break;
	  		default:
				$html = parent::configure($key);
		}
		return $html;
  	}


	function quote($pkg = '') {
	    $order_total = (MODULE_SHIPPING_TABLE_MODE == 'price') ? $pkg->pkg_total : $pkg->pkg_weight;
	    $table_cost  = split("[:,]", MODULE_SHIPPING_TABLE_COST);
	    $size        = sizeof($table_cost);
	    for ($i= 0, $n = $size; $i < $n; $i += 2) {
	    	if ($order_total <= $table_cost[$i]) {
	        	$shipping = $table_cost[$i+1];
	        	break;
	      	}
	    }
		$arrRates  = array();
		$arrRates[$this->id]['GND']['book']  = '';
		$arrRates[$this->id]['GND']['quote'] = $shipping + MODULE_SHIPPING_TABLE_HANDLING;
		$arrRates[$this->id]['GND']['cost']  = '';
		return array('result' => 'success', 'rates' => $arrRates);
	}
}
?>