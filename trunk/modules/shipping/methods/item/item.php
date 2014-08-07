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
//  Path: /modules/shipping/methods/item/item.php
//
namespace shipping\methods\item;
class item extends \shipping\classes\shipping {
	public $id				= 'item'; // needs to match class name
  	public $text			= MODULE_SHIPPING_ITEM_TEXT_TITLE;
  	public $description		= MODULE_SHIPPING_ITEM_TEXT_DESCRIPTION;
  	public $sort_order		= 30;
  	public $version			= '3.2';
  	public $shipping_cost	= 0.00;
  	public $handling_cost	= 1.00;

	function __construct(){
		//@todo
 		$this->service_levels[] = array('id' => 'GND', 		'text' => item_GND, 		'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
//		$this->service_levels[] = array('id' => 'GDR', 		'text' => TEXT_GROUND_RESIDENTIAL, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => 'GndFrt', 	'text' => SHIPPING_GNDFRT, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => 'EcoFrt', 	'text' => SHIPPING_ECOFRT, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
 		$this->service_levels[] = array('id' => '1DEam', 	'text' => SHIPPING_1DEAM, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
 		$this->service_levels[] = array('id' => '1Dam', 	'text' => SHIPPING_1DAM, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
 		$this->service_levels[] = array('id' => '1Dpm', 	'text' => SHIPPING_1DPM, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => '1DFrt',	'text' => SHIPPING_1DFRT,	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => '2Dam', 	'text' => SHIPPING_2DAM, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
 		$this->service_levels[] = array('id' => '2Dpm', 	'text' => SHIPPING_2DPM, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => '2DFrt', 	'text' => SHIPPING_2DFRT, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => '3Dam', 	'text' => SHIPPING_3DAM, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
 		$this->service_levels[] = array('id' => '3Dpm', 	'text' => SHIPPING_3DPM, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => 'I2DEam', 	'text' => TEXT_WORLDWIDE_EARLY_EXPRESS, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => 'I2Dam', 	'text' => TEXT_WORLDWIDE_EXPRESS, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => 'I3D', 		'text' => TEXT_WORLDWIDE_EXPEDITED, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
// 		$this->service_levels[] = array('id' => 'IGND', 	'text' => SHIPPING_IGND, 	'quote' => ('pkg_item_count' * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING, 'book' => '', 'cost' => '', 'note' => '');
  		parent::__construct();
  	}

	function quote($pkg = '') {
		if (!$pkg->pkg_item_count) $pkg->pkg_item_count = 1;
		$arrRates = array();
		$arrRates[$this->id]['GND']['book']  = '';
		$arrRates[$this->id]['GND']['quote'] = ($pkg->pkg_item_count * MODULE_SHIPPING_ITEM_COST) + MODULE_SHIPPING_ITEM_HANDLING;
		$arrRates[$this->id]['GND']['cost']  = '';
		return array('result' => 'success', 'rates' => $arrRates);
  	}
}
?>