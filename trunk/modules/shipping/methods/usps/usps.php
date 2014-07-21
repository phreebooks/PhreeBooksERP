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
//  Path: /modules/shipping/methods/usps/usps.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_SHIPPING_USPS_VERSION','3.2');

define('usps_1DEam', MODULE_SHIPPING_USPS_1DM);
define('usps_1Dam',  MODULE_SHIPPING_USPS_1DA);
define('usps_1Dpm',  MODULE_SHIPPING_USPS_1DP);
define('usps_2Dam',  MODULE_SHIPPING_USPS_2DA);
define('usps_2Dpm',  MODULE_SHIPPING_USPS_2DP);
define('usps_3Dam',  MODULE_SHIPPING_USPS_3DA);
define('usps_3Dpm',  MODULE_SHIPPING_USPS_3DS);
define('usps_GND',   MODULE_SHIPPING_USPS_GND);
define('usps_GDR',   MODULE_SHIPPING_USPS_GDR);

class usps {
  // FedEx Rate code maps
  var $USPSRateCodes = array(	
	'Express Mail'                       => '1DEam',
	'Express Mail Flat Rate Envelope'    => '1Dam',
	'Priority Mail'                      => '1Dpm',
	'Priority Mail Flat Rate Envelope'   => '2Dam',
	'Priority Mail Small Flat Rate Box'  => '2Dpm',
	'Priority Mail Medium Flat Rate Box' => '3Dam',
	'Priority Mail Large Flat Rate Box'  => '3Dpm',
	'Parcel Post'                        => 'GND',
	'Media Mail'                         => 'GDR',
  );

  function __construct() {
    $this->code    = 'usps';
    $this->version = '1.0';
/*
    $this->types = array(
	  'Express'     => MODULE_SHIPPING_USPS_1DA,
//	  'First Class' => 'First-Class Mail',
	  'Priority'    => MODULE_SHIPPING_USPS_3DS,
	  'Parcel'      => MODULE_SHIPPING_USPS_GND,
//	  'Media'       => 'Media Mail',
//	  'BPM'         => 'Bound Printed Material',
//	  'Library'     => 'Library',
	);
*/
/* Not Supported at this time
    $this->intl_types = array(
      'GXG Document'     => 'Global Express Guaranteed Document Service',
      'GXG Non-Document' => 'Global Express Guaranteed Non-Document Service',
      'Express'          => 'Global Express Mail (EMS)',
      'Priority Lg'      => 'Global Priority Mail - Flat-rate Envelope (Large)',
      'Priority Sm'      => 'Global Priority Mail - Flat-rate Envelope (Small)',
      'Priority Var'     => 'Global Priority Mail - Variable Weight (Single)',
      'Airmail Letter'   => 'Airmail Letter-post',
      'Airmail Parcel'   => 'Airmail Parcel Post',
      'Surface Letter'   => 'Economy (Surface) Letter-post',
      'Surface Post'     => 'Economy (Surface) Parcel Post',
	);
*/
  }
  function keys() {
    return array(
	  array('key' => 'MODULE_SHIPPING_USPS_TITLE',      'default' => 'US Postal Service'),
	  array('key' => 'MODULE_SHIPPING_USPS_USERID',     'default' => ''),
	  array('key' => 'MODULE_SHIPPING_USPS_SERVER',     'default' => 'production'),
	  array('key' => 'MODULE_SHIPPING_USPS_MACHINABLE', 'default' => '1'),
	  array('key' => 'MODULE_SHIPPING_USPS_TYPES',      'default' => '1DEam,1Dpm,GND'),
//	  array('key' => 'MODULE_SHIPPING_USPS_TYPES_INTL', 'default' => 'GXG Document, GXG Non-Document, Express, Priority Lg, Priority Sm, Priority Var, Airmail Letter, Airmail Parcel, Surface Letter, Surface Post'),
	  array('key' => 'MODULE_SHIPPING_USPS_SORT_ORDER', 'default' => '15'),
	);
  }

  function configure($key) {
	switch ($key) {
	  case 'MODULE_SHIPPING_USPS_SERVER':
		$temp = array(
		  array('id' => 'test',       'text' => TEXT_TEST),
		  array('id' => 'production', 'text' => TEXT_PRODUCTION),
		);
		$html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
		break;
	  case 'MODULE_SHIPPING_USPS_MACHINABLE':
		$temp = array(
		  array('id' => '1', 'text' => TEXT_YES),
		  array('id' => '0', 'text' => TEXT_NO),
		);
		$html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
		break;
	  case 'MODULE_SHIPPING_USPS_TYPES':
		  $temp = array(
			array('id' => '1DEam', 'text' => MODULE_SHIPPING_USPS_1DM),
			array('id' => '1Dam',  'text' => MODULE_SHIPPING_USPS_1DA),
			array('id' => '1Dpm',  'text' => MODULE_SHIPPING_USPS_1DP),
			array('id' => '2Dam',  'text' => MODULE_SHIPPING_USPS_2DA),
			array('id' => '2Dpm',  'text' => MODULE_SHIPPING_USPS_2DP),
			array('id' => '3Dam',  'text' => MODULE_SHIPPING_USPS_3DA),
			array('id' => '3Dpm',  'text' => MODULE_SHIPPING_USPS_3DS),
			array('id' => 'GND',   'text' => MODULE_SHIPPING_USPS_GND),
			array('id' => 'GDR',   'text' => MODULE_SHIPPING_USPS_GDR),
		  );
		  $choices = array();
		  foreach ($temp as $value) {
		    $choices[] = html_checkbox_field(strtolower($key) . '[]', $value['id'], ((strpos(constant($key), $value['id']) === false) ? false : true), '', $parameters = '') . ' ' . $value['text'];
		  }
		  $html = implode('<br />', $choices);
		break;
/*
	  case 'MODULE_SHIPPING_USPS_TYPES_INTL':
		  $temp = array(
			array('id' => 'GXG Document',     'text' => 'GXG Document'),
			array('id' => 'GXG Non-Document', 'text' => 'GXG Non-Document'),
			array('id' => 'Express',          'text' => 'Int Express'),
			array('id' => 'Priority Lg',      'text' => 'Int Priority Lg'),
			array('id' => 'Priority Sm',      'text' => 'Int Priority Sm'),
			array('id' => 'Priority Var',     'text' => 'Int Priority Var'),
			array('id' => 'Airmail Letter',   'text' => 'Airmail Letter'),
			array('id' => 'Airmail Parcel',   'text' => 'Airmail Parcel'),
			array('id' => 'Surface Letter',   'text' => 'Surface Letter'),
			array('id' => 'Surface Post',     'text' => 'Surface Post'),
		  );
		  $choices = array();
		  foreach ($temp as $value) {
		    $choices[] = html_checkbox_field(strtolower($key) . '[]', $value['id'], ((strpos(constant($key), $value['id']) === false) ? false : true), '', $parameters = '') . ' ' . $value['text'];
		  }
		  $html = implode('<br />', $choices);
		break;
*/
	  default:
		$html .= html_input_field(strtolower($key), constant($key), '');
	}
	return $html;
  }

  function update() {
    foreach ($this->keys() as $key) {
	  $field = strtolower($key['key']);
	  switch ($key['key']) {
	    case 'MODULE_SHIPPING_USPS_TYPES':
	    case 'MODULE_SHIPPING_USPS_TYPES_INTL':
		  write_configure($key['key'], implode(',', $_POST[$field]));
		  break;
		default:  // just write the value
		  if (isset($_POST[$field])) write_configure($key['key'], $_POST[$field]);
	  }
	}
  }

// ***************************************************************************************************************
//								USPS RATE AND SERVICE REQUEST
// ***************************************************************************************************************
    function quote($pkg) {
		global $messageStack;
		if ($pkg->pkg_weight == 0) {
			$messageStack->add(SHIPPING_ERROR_WEIGHT_ZERO, 'error');
			return false;
		}
		if ($pkg->ship_to_postal_code == '') {
			$messageStack->add(SHIPPING_USPS_ERROR_POSTAL_CODE, 'error');
			return false;
		}
		$status = $this->getUSPSRates($pkg);
		if ($status['result'] == 'error') {
			$messageStack->add(SHIPPING_USPS_RATE_ERROR . $status['message'], 'error');
			return false;
		}
		return $status;
    }

	function production_FormatUSPSRateRequest($pkg, $num_packages) {
		global $debug;
// TBD this is for domestic only, international needs to be added based on country selected...
		$sBody = '<RateV2Request USERID="' . MODULE_SHIPPING_USPS_USERID . '">';
// TBD for multiple requests, loop with package number entered
		$sBody .= '<Package ID="1">';
		$sBody .= '<Service>All</Service>';
		$sBody .= '<ZipOrigination>' . $pkg->ship_postal_code . '</ZipOrigination>';
		$sBody .= '<ZipDestination>' . $pkg->ship_to_postal_code . '</ZipDestination>';
		$sBody .= '<Pounds>' . floor($pkg->pkg_weight) . '</Pounds>';
		$sBody .= '<Ounces>' . ceil(($pkg->pkg_weight % 1) * 16). '</Ounces>';
// TBD for special packages flat rate env, flat rate box, etc. (see spec)
//		$sBody .= '<Container>' . $pkg->pkg_weight . '</Container>';
// TBD use LxWxH to calculate the size parameter (Regular, Large, Oversize)
		$sBody .= '<Size>Regular</Size>';
		$sBody .= '<Machinable>True</Machinable>';
		$sBody .= '</Package>';
		$sBody .= '</RateV2Request>';
		return $sBody;
	}

	function FormatUSPSRateRequest($pkg, $num_packages) { // Example 1. To use for the test server (cannot change any values)
		global $debug;
		$sBody = '<RateV2Request USERID="' . MODULE_SHIPPING_USPS_USERID . '">';
		$sBody .= '<Package ID="0">';
		$sBody .= '<Service>PRIORITY</Service>';
		$sBody .= '<ZipOrigination>10022</ZipOrigination>';
		$sBody .= '<ZipDestination>20008</ZipDestination>';
		$sBody .= '<Pounds>10</Pounds>';
		$sBody .= '<Ounces>5</Ounces>';
		$sBody .= '<Container>Flat Rate Box</Container>';
		$sBody .= '<Size>Regular</Size>';
		$sBody .= '</Package>';
		$sBody .= '</RateV2Request>';
		return $sBody;
	}

// ***************************************************************************************************************
//								Parse function to retrieve USPS rates
// ***************************************************************************************************************
	function getUSPSRates($pkg) {
		global $messageStack, $pkg;
		$user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_USPS_TYPES));
		$USPSQuote = array();	// Initialize the Response Array

		$this->package = $pkg->split_shipment($pkg);
		if (!$this->package) {
			$messageStack->add(SHIPPING_USPS_PACKAGE_ERROR . $pkg->pkg_weight, 'error');
			return false;
		}
// TBD convert weight to pounds if in KGs
		if ($pkg->split_large_shipments || ($pkg->num_packages == 1 && $pkg->pkg_weight <= 70)) {
			$arrRates = $this->queryUSPS($pkg, $user_choices, $pkg->num_packages);
		} else {
			$arrRates = false;
		}
		if (!$arrRates) return array();
		$USPSQuote['result'] = 'success';
		$USPSQuote['rates'] = $arrRates;
		return $USPSQuote;
	}	// End USPS Rate Function

	function queryUSPS($pkg, $user_choices, $num_packages) {		// Fetch the book rates from USPS
	  global $messageStack;
	  $arrRates = array();
	  if (MODULE_SHIPPING_USPS_SERVER == 'production') {
		$strXML = 'API=RateV2&XML=' . $this->production_FormatUSPSRateRequest($pkg, $num_packages);
		$RateURL = 'http://Production.ShippingAPIs.com/ShippingAPI.dll';
	  } else {
		$strXML = 'API=RateV2&XML=' . $this->FormatUSPSRateRequest($pkg, $num_packages);
		$RateURL = 'http://testing.shippingapis.com/ShippingAPITest.dll';
	  }
	  $SubmitXML = GetXMLString($strXML, $RateURL, "GET");
	  // Check for XML request errors
	  if ($SubmitXML['result']=='error') {
		$messageStack->add(SHIPPING_USPS_CURL_ERROR . $SubmitXML['message'], 'error');
		return false;
	  }
	  $ResponseXML = xml_to_object($SubmitXML['xmlString']);
	  // Check for errors
	  $XMLFail = $ResponseXML->Error->Number;
	  if ($XMLFail) {	// fetch the error code
		$XMLErrorDesc = $ResponseXML->Error->Description;
		$messageStack->add($this->code . ' - ' . $XMLFail . ' - ' . $XMLErrorDesc, 'error');
		return false;
	  }
	  // Fetch the USPS Rates
	  return $this->GetUSPSRateArray($ResponseXML);
	}

	function GetUSPSRateArray($SearchObj) {
	  $user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_USPS_TYPES));  
	  $arrXML = array();
	  foreach ($SearchObj->RateV2Response->Package->Postage as $postage) { 
		foreach ($this->USPSRateCodes as $key => $service) {
		  if ($postage->MailService == $key && in_array($service, $user_choices)) {
		    $arrXML[$this->code][$service]['book']  = $postage->Rate;
		    $arrXML[$this->code][$service]['quote'] = $postage->Rate;
		    $arrXML[$this->code][$service]['cost']  = $postage->Rate;
		    $arrXML[$this->code][$service]['note']  = '';
		  }
		}
	  }
	  return $arrXML;
	}
  }
?>