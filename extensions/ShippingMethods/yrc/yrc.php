<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/shipping/methods/yrc/yrc.php
//@todo update to release 4.0
// Revision history
// 2012-09-01 - New Release
define('MODULE_SHIPPING_YRC_VERSION','1.0');

define('YRC_COST_OFFSET',3.00); // constant to add to cost for reconciliation notifications
define('YRC_COST_FACTOR',0.10); // percent of allowed cost over actual charge
define('YRC_MIN_SINGLE_BOX_WEIGHT', 150); // maximum single box weight for small package in pounds
define('YRC_RATE_URL','https://my.yrc.com/dynamic/national/servlet');
// constants used in rate screen to match carrier descrptions
define('yrc_GndFrt',MODULE_SHIPPING_YRC_GDF);
define('yrc_2DFrt', MODULE_SHIPPING_YRC_2DF);
define('yrc_3DFrt', MODULE_SHIPPING_YRC_3DF);

class yrc {
  var $YRCRateCodes = array( // YRC Rate code maps
	'YRC_FREIGHT_PRIORITY' => 'GndFrt',
  );

  function __construct() {
    $this->code = 'yrc';
  }

  function keys() {
    return array(
	  array('key' => 'MODULE_SHIPPING_YRC_TITLE',     'default' => 'YRC'),
	  array('key' => 'MODULE_SHIPPING_YRC_USER_ID',   'default' => ''),
	  array('key' => 'MODULE_SHIPPING_YRC_PASSWORD',  'default' => ''),
      array('key' => 'MODULE_SHIPPING_YRC_BUSID',     'default' => ''),
	  array('key' => 'MODULE_SHIPPING_YRC_SORT_ORDER','default' => '80'),
	);
  }

  function configure($key) {
    return html_input_field(strtolower($key), constant($key), '');
  }

  function update() {
    foreach ($this->keys() as $key) {
	  $field = strtolower($key['key']);
	  if (isset($_POST[$field])) $admin->DataBase->write_configure($key['key'], $_POST[$field]);
	}
  }

// ***************************************************************************************************************
//								YRC RATE AND SERVICE REQUEST
// ***************************************************************************************************************
    function quote($pkg) {
		global $messageStack;
		if ($pkg->pkg_weight == 0) throw new \core\classes\userException(TEXT_SHIPMENT_WEIGHT_CANNOT_BE_ZERO);
		if ($pkg->ship_to_postal_code == '') throw new \core\classes\userException(SHIPPING_YRC_ERROR_POSTAL_CODE);
		$status = $this->getYRCRates($pkg);
		if ($status['result'] == 'error') throw new \core\classes\userException(SHIPPING_YRC_RATE_ERROR . $status['message']);
		return $status;
    }

    function getYRCRates($pkg) {
    	global $messageStack;
    	$arrRates = array();
    	$user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_YRC_TYPES));
    	$YRCQuote = array();
    	$this->package = $pkg->split_shipment($pkg);
    	if (!$this->package) throw new \core\classes\userException(SHIPPING_YRC_PACKAGE_ERROR . $pkg->pkg_weight);
    	if ($pkg->pkg_weight > YRC_MIN_SINGLE_BOX_WEIGHT) $arrRates = $this->queryYRC($pkg);
    	return $YRCQuote = array('result' => 'success', 'rates' => $arrRates);
    }

	function queryYRC($pkg) {
	  global $messageStack;
	  $arrRates = array();
	  $strXML = $this->FormatYRCRateRequest($pkg);
//echo 'sending to url:'.YRC_RATE_URL.' the string: '.htmlspecialchars($strXML).'<br>';
	  $response = simplexml_load_file(YRC_RATE_URL.'?'.$strXML);
	  $attributes = $response->attributes();
//echo 'returned string = '; print_r($response); echo '<br>';
	  // Check for errors
	  if ($attributes['ReturnCode'] <> 0) {	// fetch the error code
			throw new \core\classes\userException("YRC Error # {$attributes['ReturnCode']} - {$attributes['ReturnText']}");
	  }
	  // Fetch the YRC Rates
	  $rates = $response->BodyMain->RateQuote->QuoteMatrix->TransitOptions;
	  $user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_YRC_TYPES));
	  $arrRates = array();
	  foreach ($rates as $value) {
	  	$delDate = $value->DeliveryDate;
	  	$attr = $value->PriceOption->attributes();
		switch ($attr['Class']) {
		  default:
		  case 'ST':
			$dispDate = date('l n/j', mktime(17, 0, 0, substr($delDate,4,2), substr($delDate,6,2), substr($delDate,0,4)));
			$dispDate = 'Delivered: ' . $dispDate;
			$service  = 'GndFrt';
			break;
		  case 'GD':
			$dispDate = date('l n/j', mktime(17, 0, 0, substr($delDate,4,2), substr($delDate,6,2), substr($delDate,0,4)));
			$dispDate = 'Guaranteed: ' . $dispDate;
			$service  = '2DFrt';
		  	break;
		  case 'GM':
			$dispDate = date('l n/j', mktime(12, 0, 0, substr($delDate,4,2), substr($delDate,6,2), substr($delDate,0,4)));
			$dispDate = 'Guaranteed noon: ' . $dispDate;
			$service  = '3DFrt';
		  	break;
		}
		$charges = $value->PriceOption->TotalCharges;
	  	$dispCharges = substr($charges,0,-2).'.'.substr($charges,-2);
		$arrRates[$this->code][$service]['book']  = '';
		$arrRates[$this->code][$service]['quote'] = $dispCharges;
		$arrRates[$this->code][$service]['cost']  = $dispCharges;
		$arrRates[$this->code][$service]['note']  = $dispDate;
		if (function_exists('yrc_shipping_rate_calc')) {
			$arrRates[$this->code][$service]['quote'] = yrc_shipping_rate_calc($arrRates[$this->code][$service]['cost']);
		}

	  }
	  return $arrRates;
	}

	function GetYRCRateArray($SearchObj) {
	}

  function FormatYRCRateRequest($pkg) {
	global $messageStack, $debug;
  	// check the ship date to see if it is within range
	$ship_date = date('Ymd', strtotime($pkg->ship_date));
	$today = date('Ymd');
	if ($ship_date < $today) {
	  \core\classes\messageStack::add(SHIPPING_BAD_QUOTE_DATE, 'caution');
	  $ship_date = date('Ymd');
	}
	$output = array();
	$output['CONTROLLER']           = 'com.rdwy.ec.rexcommon.proxy.http.controller.ProxyApiController';
	$output['redir']                = '/tfq561';
	$output['LOGIN_USERID']         = MODULE_SHIPPING_YRC_USER_ID;
	$output['LOGIN_PASSWORD']       = MODULE_SHIPPING_YRC_PASSWORD;
	$output['BusId']                = MODULE_SHIPPING_YRC_BUSID;
	$output['BusRole']              = 'Shipper';
	$output['PaymentTerms']         = 'Prepaid';
	$output['OrigCityName']         = urlencode($pkg->ship_city_town);
	$output['OrigStateCode']        = urlencode($pkg->ship_state_province);
	$output['OrigZipCode']          = $pkg->ship_postal_code;
	$output['OrigNationCode']       = $pkg->ship_country_code;
	$output['DestCityName']         = urlencode($pkg->ship_to_city);
	$output['DestStateCode']        = urlencode($pkg->ship_to_state);
	$output['DestZipCode']          = $pkg->ship_to_postal_code;
	$output['DestNationCode']       = $pkg->ship_to_country_code;
	$output['ServiceClass']         = 'STD';
	$output['PickupDate']           = $ship_date;
	$output['TypeQuery']            = 'MATRX';
	$output['LineItemCount']        = '1';
	$output['LineItemPackageCode1'] = 'PLT';
	$output['LineItemWeight1']      = $pkg->pkg_weight;
	$output['LineItemNmfcClass1']   = intval($pkg->ltl_class);
	$cnt = 0;
	if ($pkg->residential_address) { $output['AccOption1'] = 'HOMD'; $cnt++; }
	if ($cnt > 0) $output['AccOptionCount'] = $cnt;

	$response = array();
	foreach ($output as $key => $value) $response[] = $key.'='.$value;
	return implode('&', $response);
  }

}
?>