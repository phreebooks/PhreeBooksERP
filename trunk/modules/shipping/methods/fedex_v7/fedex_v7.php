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
//  Path: /modules/shipping/methods/fedex_v7/fedex_v7.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_SHIPPING_FEDEX_V7_VERSION','3.2');

define('FEDEX_V7_COST_OFFSET',3.00); // constant to add to cost for reconciliation notifications
define('FEDEX_V7_COST_FACTOR',0.10); // percent of allowed cost over actual charge
define('FEDEX_V7_MAX_SINGLE_BOX_WEIGHT', 150); // maximum single box weight for small package in pounds
define('FEDEX_MAX_SMART_POST_WEIGHT',7); // maximum weight to use Smart Post service
define('FEDEX_SMARTPOST_HUB_ID','5802'); // 5802 for Denver, CO
define('FEDEX_V7_TRACKING_URL','http://www.fedex.com/Tracking?ascend_header=1&amp;clienttype=dotcom&amp;cntry_code=us&amp;language=english&amp;tracknumbers=');
// Set the defaults for Thermal printing
define('LABELORIENTATION_THERMAL', 'STOCK_4X6.75_LEADING_DOC_TAB'); 
define('LABELORIENTATION_PDF', 'PAPER_8.5X11_TOP_HALF_LABEL'); 
/* PAPER_4X6, PAPER_4X8, PAPER_4X9, PAPER_7X4.75, PAPER_8.5X11_BOTTOM_HALF_LABEL, PAPER_8.5X11_TOP_HALF_LABEL,
STOCK_4X6, STOCK_4X6.75_LEADING_DOC_TAB, STOCK_4X6.75_TRAILING_DOC_TAB, STOCK_4X8,
STOCK_4X9_LEADING_DOC_TAB, STOCK_4X9_TRAILING_DOC_TAB */
//define('DOCTABLOCATION', 'TOP'); // only valid for thermal labels (TOP, BOTTOM) - as it comes out of the printer
define('DOCTABCONTENT', 'Zone001'); // (Zone001, Barcoded)
// constants used in rate screen to match carrier descrptions
define('fedex_v7_1DEam', MODULE_SHIPPING_FEDEX_V7_1DM);
define('fedex_v7_1Dam',  MODULE_SHIPPING_FEDEX_V7_1DA);
define('fedex_v7_1Dpm',  MODULE_SHIPPING_FEDEX_V7_1DP);
define('fedex_v7_2Dam',  MODULE_SHIPPING_FEDEX_V7_2DA);
define('fedex_v7_2Dpm',  MODULE_SHIPPING_FEDEX_V7_2DP);
define('fedex_v7_3Dam',  MODULE_SHIPPING_FEDEX_V7_3DA);
define('fedex_v7_3Dpm',  MODULE_SHIPPING_FEDEX_V7_3DS);
define('fedex_v7_GND',   MODULE_SHIPPING_FEDEX_V7_GND);
define('fedex_v7_GDR',   MODULE_SHIPPING_FEDEX_V7_GDR);
define('fedex_v7_I2DEam',MODULE_SHIPPING_FEDEX_V7_XDM);
define('fedex_v7_I2Dam', MODULE_SHIPPING_FEDEX_V7_XPR);
define('fedex_v7_I3D',   MODULE_SHIPPING_FEDEX_V7_XPD);
define('fedex_v7_1DFrt', MODULE_SHIPPING_FEDEX_V7_1DF);
define('fedex_v7_2DFrt', MODULE_SHIPPING_FEDEX_V7_2DF);
define('fedex_v7_3DFrt', MODULE_SHIPPING_FEDEX_V7_3DF);
define('fedex_v7_GndFrt',MODULE_SHIPPING_FEDEX_V7_GDF);
define('fedex_v7_EcoFrt',MODULE_SHIPPING_FEDEX_V7_ECF);
// FedEx WSDL paths
define('MODULE_SHIPPING_FEDEX_RATE_WSDL_VERSION','10');
define('PATH_TO_TEST_RATE_WSDL',  DIR_FS_WORKING . 'methods/fedex_v7/TestRateService_v10.wsdl');
define('PATH_TO_RATE_WSDL',       DIR_FS_WORKING . 'methods/fedex_v7/RateService_v10.wsdl');

define('MODULE_SHIPPING_FEDEX_SHIP_WSDL_VERSION','10');
define('PATH_TO_TEST_SHIP_WSDL',  DIR_FS_WORKING . 'methods/fedex_v7/TestShipService_v10.wsdl');
define('PATH_TO_SHIP_WSDL',       DIR_FS_WORKING . 'methods/fedex_v7/ShipService_v10.wsdl');

define('PATH_TO_TEST_CLOSE_WSDL', DIR_FS_WORKING . 'methods/fedex_v7/TestCloseService_v2.wsdl');
define('PATH_TO_TRACK_WSDL',      DIR_FS_WORKING . 'methods/fedex_v7/TrackService_v4.wsdl');
define('PATH_TO_CLOSE_WSDL',      DIR_FS_WORKING . 'methods/fedex_v7/CloseService_v2.wsdl');
ini_set("soap.wsdl_cache_enabled", "0");
// guaranteed time delivery times
define('GUAR_TIME_A1','08:00:00'); // First Overnight
define('GUAR_TIME_A2','08:30:00');
define('GUAR_TIME_A4','09:00:00');
define('GUAR_TIME_A5','10:00:00');
define('GUAR_TIME_P1','10:30:00'); // Priority Overnight
define('GUAR_TIME_PR','12:00:00'); 
define('GUAR_TIME_CM','15:00:00'); // Standard Overnight
define('GUAR_TIME_2D','16:30:00'); // 2 Day and Saver
define('GUAR_TIME_GD','17:00:00'); // Ground
define('GUAR_TIME_RE','19:00:00'); // Residential

class fedex_v7 {
  // FedEx Rate code maps
  var $FedExRateCodes = array(	
	'FIRST_OVERNIGHT'        => '1DEam',
	'PRIORITY_OVERNIGHT'     => '1Dam',
	'STANDARD_OVERNIGHT'     => '1Dpm',
	'FEDEX_2_DAY_AM'         => '2Dam',
    'FEDEX_2_DAY'            => '2Dpm',
	'SMART_POST'             => '3Dam',
    'FEDEX_EXPRESS_SAVER'    => '3Dpm',
	'FEDEX_GROUND'           => 'GND',
	'GROUND_HOME_DELIVERY'   => 'GDR',
	'INTERNATIONAL_FIRST'    => 'I2DEam',
	'INTERNATIONAL_PRIORITY' => 'I2Dam',
	'INTERNATIONAL_ECONOMY'  => 'I3D',
	'FEDEX_1_DAY_FREIGHT'    => '1DFrt',
	'FEDEX_2_DAY_FREIGHT'    => '2DFrt',
	'FEDEX_3_DAY_FREIGHT'    => '3DFrt',
	'FEDEX_FREIGHT_PRIORITY' => 'GndFrt',
//	'FEDEX_FREIGHT_ECONOMY'  => 'EcoFrt',
	// new options
//	'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
//	'INTERNATIONAL_ECONOMY_FREIGHT',
//	'INTERNATIONAL_PRIORITY_FREIGHT',
  );

  var $FedExPickupMap = array(
	'01' => 'REGULAR_PICKUP',
	'06' => 'REQUEST_COURIER',
	'19' => 'DROP_BOX',
	'20' => 'BUSINESS_SERVICE_CENTER',
	'03' => 'STATION',
  );

  var $ReturnServiceMap = array(
//	''  => 'NONRETURN',
	'1' => 'PRINTRETURNLABEL',
//	''  => 'EMAILABEL',
	'2' => 'FEDEXTAG',
  );

  var $PackageMap = array(
	'01' => 'FEDEX_ENVELOPE',
	'02' => 'YOUR_PACKAGING',
	'03' => 'FEDEX_TUBE',
	'04' => 'FEDEX_PAK',
	'21' => 'FEDEX_BOX',
	'25' => 'FEDEX_10KG_BOX',
	'24' => 'FEDEX_25KG_BOX',
  );

  var $CODMap = array(
	'4' => 'ANY',
	'3' => 'GUARANTEED_FUNDS',	// money order
	'2' => 'GUARANTEED_FUNDS',
	'1' => 'GUARANTEED_FUNDS',	// check not a great match, but the best
	'0' => 'CASH',
  );

  var $HandlingMap = array(
	'' => 'FIXED_AMOUNT',
	'' => 'PERCENTAGE_OF_BASE',
	'' => 'PERCENTAGE_OF_NET',
	'' => 'PERCENTAGE_OF_NET_EXCL_TAXES',
  );

  var $PaymentMap = array(
	'0' => 'SENDER',
	'1' => 'RECIPIENT',
	'2' => 'THIRD_PARTY',
//	'3' => 'COLLECT',
  );

  var $SignatureMap = array(
    '1' => 'DELIVERYWITHOUTSIGNATURE',
    '2' => 'INDIRECT',	// closest match to signature required (other: DIRECT which requires the exact person)
    '3' => 'ADULT',
  );

  function __construct() {
    $this->code     = 'fedex_v7';
    $this->rate_url = MODULE_SHIPPING_FEDEX_V7_TEST_MODE == 'Test' ? FEDEX_V7_EXPRESS_TEST_RATE_URL : FEDEX_V7_EXPRESS_RATE_URL;	  
  }

  function keys() {
    return array(
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_TITLE',              'default' => 'FedEx'),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER',     'default' => ''),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_LTL_ACCOUNT_NUMBER', 'default' => ''),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_NAT_ACCOUNT_NUMBER', 'default' => ''),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_AUTH_KEY',           'default' => ''),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_AUTH_PWD',           'default' => ''),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_METER_NUMBER',       'default' => ''),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_TEST_MODE',          'default' => 'Test'),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_PRINTER_TYPE',       'default' => 'PDF'),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_PRINTER_NAME',       'default' => 'zebra'),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_TYPES',              'default' => '1DEam,1Dam,1Dpm,1DFrt,2Dam,2Dpm,2DFrt,3Dpm,3DFrt,GND,GDR,GndFrt,EcoFrt,I2DEam,I2Dam,I3D,IGND'),
	  array('key' => 'MODULE_SHIPPING_FEDEX_V7_SORT_ORDER',         'default' => '10'),
	);
  }

  function configure($key) {
    switch ($key) {
	  case 'MODULE_SHIPPING_FEDEX_V7_TEST_MODE':
	    $temp = array(
		  array('id' => 'Test',       'text' => TEXT_TEST),
		  array('id' => 'Production', 'text' => TEXT_PRODUCTION),
	    );
	    $html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
	    break;
	  case 'MODULE_SHIPPING_FEDEX_V7_PRINTER_TYPE':
	    $temp = array(
		  array('id' => 'PDF',     'text' => TEXT_PDF),
		  array('id' => 'Thermal', 'text' => TEXT_THERMAL),
	    );
	    $html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
	    break;
	  case 'MODULE_SHIPPING_FEDEX_V7_TYPES':
	    $temp = array(
		  array('id' => '1DEam', 'text' => MODULE_SHIPPING_FEDEX_V7_1DM),
		  array('id' => '1Dam',  'text' => MODULE_SHIPPING_FEDEX_V7_1DA),
		  array('id' => '1Dpm',  'text' => MODULE_SHIPPING_FEDEX_V7_1DP),
		  array('id' => '1DFrt', 'text' => MODULE_SHIPPING_FEDEX_V7_1DF),
		  array('id' => '2Dpm',  'text' => MODULE_SHIPPING_FEDEX_V7_2DP),
		  array('id' => '2DFrt', 'text' => MODULE_SHIPPING_FEDEX_V7_2DF),
		  array('id' => '3Dpm',  'text' => MODULE_SHIPPING_FEDEX_V7_3DS),
		  array('id' => '3Dam',  'text' => MODULE_SHIPPING_FEDEX_V7_3DA),
		  array('id' => '3DFrt', 'text' => MODULE_SHIPPING_FEDEX_V7_3DF),
		  array('id' => 'GND',   'text' => MODULE_SHIPPING_FEDEX_V7_GND),
		  array('id' => 'GDR',   'text' => MODULE_SHIPPING_FEDEX_V7_GDR),
		  array('id' => 'GndFrt','text' => MODULE_SHIPPING_FEDEX_V7_GDF),
		  array('id' => 'EcoFrt','text' => MODULE_SHIPPING_FEDEX_V7_ECF),
		  array('id' => 'I2DEam','text' => MODULE_SHIPPING_FEDEX_V7_XDM),
		  array('id' => 'I2Dam', 'text' => MODULE_SHIPPING_FEDEX_V7_XPR),
		  array('id' => 'I3D',   'text' => MODULE_SHIPPING_FEDEX_V7_XPD),
	    );
	    $choices = array();
	    foreach ($temp as $value) {
		  $choices[] = html_checkbox_field(strtolower($key) . '[]', $value['id'], ((strpos(constant($key), $value['id']) === false) ? false : true), '', $parameters = '') . ' ' . $value['text'];
	    }
	    $html = implode('<br />' . chr(10), $choices);
	    break;
	  default:
	    $html .= html_input_field(strtolower($key), constant($key), '');
    }
    return $html;
  }

  function update() {
    foreach ($this->keys() as $key) {
	  $field = strtolower($key['key']);
	  switch ($key['key']) {
	    case 'MODULE_SHIPPING_FEDEX_V7_TYPES': // read the checkboxes
		  write_configure($key['key'], implode(',', $_POST[$field]));
		  break;
		default:  // just write the value
		  if (isset($_POST[$field])) write_configure($key['key'], $_POST[$field]);
	  }
	}
  }

// ***************************************************************************************************************
//								FEDEX RATE AND SERVICE REQUEST
// ***************************************************************************************************************
  function quote($pkg) {
	global $messageStack, $currencies;
	if ($pkg->pkg_weight == 0) {
	  $messageStack->add(SHIPPING_ERROR_WEIGHT_ZERO, 'error');
	  return false;
	}
	if ($pkg->ship_to_postal_code == '') {
	  $messageStack->add(SHIPPING_FEDEX_V7_ERROR_POSTAL_CODE, 'error');
	  return false;
	}

	$FedExQuote = array();	// Initialize the Response Array
	$user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_FEDEX_V7_TYPES));  
	$this->package = $pkg->split_shipment($pkg);
	if (!$this->package) {
	  $messageStack->add(SHIPPING_FEDEX_V7_PACKAGE_ERROR . $pkg->pkg_weight, 'error');
	  return false;
	}

	if (MODULE_SHIPPING_FEDEX_V7_TEST_MODE == 'Test') {
	  $client = new SoapClient(PATH_TO_TEST_RATE_WSDL, array('trace' => 1));
	} else {
	  $client = new SoapClient(PATH_TO_RATE_WSDL, array('trace' => 1));
	}
	// fisrt check if small package
	if ($pkg->split_large_shipments || ($pkg->num_packages == 1 && $pkg->pkg_weight <= FEDEX_V7_MAX_SINGLE_BOX_WEIGHT)) {
	  $arrRates = $this->queryFedEx($client, $pkg, $user_choices, $pkg->num_packages);
	} else {
	  $arrRates = array();
	}
	// now check if freight (assume a single pallet shipment) express is differenet than ground LTL
	if ($pkg->pkg_weight > FEDEX_V7_MAX_SINGLE_BOX_WEIGHT) {
	  $freightRates = $this->queryFedEx($client, $pkg, $user_choices, '1');
	  if (is_array($arrRates) && is_array($freightRates)) {
		$arrRates = array_merge_recursive($arrRates, $freightRates);
	  } elseif (is_array($freightRates)) {
		$arrRates = $freightRates;
	  }
	}
	// now check ground freight (assume a single pallet shipment)
	if ($pkg->pkg_weight > FEDEX_V7_MAX_SINGLE_BOX_WEIGHT) {
	  $freightRates = $this->queryFedEx($client, $pkg, $user_choices, '1', $ltl = true);
	  if (is_array($arrRates) && is_array($freightRates)) {
		$arrRates = array_merge_recursive($arrRates, $freightRates);
	  } elseif (is_array($freightRates)) {
		$arrRates = $freightRates;
	  }
	}
	// All calculations finished, return
	$FedExQuote['result'] = 'success';
	$FedExQuote['rates']  = $arrRates;
	return $FedExQuote;
  }

  function queryFedEx($client, $pkg, $user_choices, $num_packages, $ltl = false) {
	global $messageStack, $currencies;
	$arrRates = array();
	$request = $this->FormatFedExRateRequest($pkg, $num_packages, $ltl);
//if ($ltl) { echo 'FedEx Express XML Submit String:<br />'; print_r($request); echo '<br />'; }
	try {
	  $response = $client->getRates($request);
//echo 'Request <pre>'  . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
//if ($ltl) { echo 'rate response array = '; print_r($response->RateReplyDetails); echo '<br />'; }
	  if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
		if ($response->HighestSeverity == 'NOTE' || $response->HighestSeverity == 'WARNING') {
		  $message .= ' (' . $response->Notifications->Code . ') ' . $response->Notifications->Message;
		  $messageStack->add('FedEx Note: ' . $message, 'caution');
		}
		if (is_object($response->RateReplyDetails)) $response->RateReplyDetails = array($response->RateReplyDetails);
		if (is_array($response->RateReplyDetails)) foreach ($response->RateReplyDetails as $rateReply) {
		  $service = $this->FedExRateCodes[$rateReply->ServiceType];
//echo 'rateReply->ServiceType = '; print_r($rateReply->ServiceType); echo '<br>';
		  if ($service == '3Dam' && $pkg->pkg_weight > FEDEX_MAX_SMART_POST_WEIGHT) continue;
		  if (in_array($service, $user_choices)) {
			$temp = array(); // ground and freight are not in an array so convert
			if (is_array($rateReply->RatedShipmentDetails)) {
			  foreach ($rateReply->RatedShipmentDetails as $details) $temp[] = $details;
			} else {
			  $temp[] = $rateReply->RatedShipmentDetails;
			}
			foreach ($temp as $details) { 
			  switch ($details->ShipmentRateDetail->RateType) {
				default:
				case 'PAYOR_ACCOUNT':
				  $arrRates[$this->code][$service]['cost']   = $details->ShipmentRateDetail->TotalNetCharge->Amount;
				  if (isset($rateReply->CommitDetails->CommitTimestamp)) {
					$arrRates[$this->code][$service]['note'] = 'Commit: ' . date("D M j g:i a", strtotime($rateReply->CommitDetails->CommitTimestamp));
				  } elseif (isset($rateReply->CommitDetails->MaximumTransitTime)) {
					$arrRates[$this->code][$service]['note'] = ' Commit: ' . date("D M j g:i a", strtotime($this->calculateDelivery($rateReply->CommitDetails->MaximumTransitTime, $pkg->residential_address)));
				  } elseif (isset($rateReply->CommitDetails->TransitTime)) {
					$arrRates[$this->code][$service]['note'] = ' Commit: ' . date("D M j g:i a", strtotime($this->calculateDelivery($rateReply->CommitDetails->TransitTime, $pkg->residential_address)));
				  } else {
					$arrRates[$this->code][$service]['note'] = '';
				  }
				  // fall through as book and quote are the same for both types
				case 'RATED_ACCOUNT':
				  $surcharges = $details->ShipmentRateDetail->TotalSurcharges->Amount;
				  $baserate   = $details->ShipmentRateDetail->TotalBaseCharge->Amount;
				  $arrRates[$this->code][$service]['book']  = $baserate + $surcharges;
				  $arrRates[$this->code][$service]['quote'] = $arrRates[$this->code][$service]['book'];
				  break;
				case 'PAYOR_MULTIWEIGHT':
				  break;
			  }
			}
			if (function_exists('fedex_shipping_rate_calc')) {
			  $arrRates[$this->code][$service]['quote'] = fedex_shipping_rate_calc($arrRates[$this->code][$service]['book'], $arrRates[$this->code][$service]['cost'], $service);
			}
		  }
		}
	  } else {
		  foreach ($response->Notifications as $notification) {
			if (is_object($notification)) {
			  $message .= ' (' . $notification->Severity . ') ' . $notification->Message;
			} else {
			  $message .= ' - ' . $notification;
			}
		  }
		  $messageStack->add(SHIPPING_FEDEX_V7_RATE_ERROR . $message, 'error');
		  return false;
	  }
	} catch (SoapFault $exception) {
//echo 'Request <pre>'  . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  $message = " [soap fault] ({$exception->faultcode}) {$exception->faultstring}";
	  $messageStack->add(SHIPPING_FEDEX_CURL_ERROR . $message, 'error');
	  return false;
	}
//if ($ltl) { echo 'arrRates array = '; print_r($arrRates); echo '<br /><br />'; }
	return $arrRates;
  }

  function FormatFedExRateRequest($pkg, $num_packages, $ltl = false) {
	global $messageStack, $debug;
	$request = array();
	$request['WebAuthenticationDetail'] = array(
		'UserCredential' => array(
		  'Key'      => MODULE_SHIPPING_FEDEX_V7_AUTH_KEY,
		  'Password' => MODULE_SHIPPING_FEDEX_V7_AUTH_PWD,
		),
	); 
	$request['ClientDetail'] = array(
	  'AccountNumber' => MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER,
	  'MeterNumber'   => MODULE_SHIPPING_FEDEX_V7_METER_NUMBER,
	);
	$request['TransactionDetail'] = array(
	  'CustomerTransactionId' => '*** Rate Available Services Request v7 ***',
	);
	$request['Version'] = array( // version 7
	  'ServiceId'    => 'crs', 
	  'Major'        => MODULE_SHIPPING_FEDEX_RATE_WSDL_VERSION, 
	  'Intermediate' => '0', 
	  'Minor'        => '0',
	);
	$request['ReturnTransitAndCommit']             = '1';
	$request['RequestedShipment']['DropoffType']   = 'REGULAR_PICKUP';
	// check the ship date to see if it is within range
	$ship_date = date('c', strtotime($pkg->ship_date));
	$today = date('Y-m-d');
	if ($ship_date < $today) {
	  $messageStack->add(SHIPPING_BAD_QUOTE_DATE, 'caution');
	  $ship_date = date('c');
	}
	$request['RequestedShipment']['ShipTimestamp'] = $ship_date;
	$request['RequestedShipment']['Shipper'] = array(
	  'Address' => array(
//		'StreetLines'         => COMPANY_ADDRESS1,
		'City'                => $pkg->ship_city_town,
		'StateOrProvinceCode' => $pkg->ship_state_province,
		'PostalCode'          => $pkg->ship_postal_code,
		'CountryCode'         => $pkg->ship_from_country_iso2,
	  ),
	);
	$request['RequestedShipment']['Recipient'] = array(
	  'Address' => array (
		'City'                => $pkg->ship_to_city,
		'StateOrProvinceCode' => $pkg->ship_to_state,
		'PostalCode'          => $pkg->ship_to_postal_code,
		'CountryCode'         => $pkg->ship_to_country_iso2,
	  ),
	);
	if ($pkg->residential_address) $request['RequestedShipment']['Recipient']['Address']['Residential'] = 1;
	$request['RequestedShipment']['ShippingChargesPayment'] = array(
	  'PaymentType' => 'SENDER',
	  'Payor'       => array(
		'AccountNumber' => MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER, // '510087801',
		'CountryCode'   => $pkg->ship_from_country_iso2,
	  ),
	);
	// SmartPost
	$request['RequestedShipment']['SmartPostDetail'] = array(
	  'Indicia' => $pkg->pkg_weight < 0.1 ? 'PRESORTED_STANDARD' : 'PARCEL_SELECT',
	  'HubId'   => FEDEX_SMARTPOST_HUB_ID, // 5802 for Denver, CO
	);
	$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; // choices are ACCOUNT or LIST
	if ($ltl && MODULE_SHIPPING_FEDEX_V7_LTL_ACCOUNT_NUMBER) {
	  $request['RequestedShipment']['FreightShipmentDetail'] = array(
		'FedExFreightAccountNumber'  => MODULE_SHIPPING_FEDEX_V7_LTL_ACCOUNT_NUMBER,
		'FedExFreightBillingContactAndAddress' => array(
		  'Contact' => array(
			'PersonName'          => AR_CONTACT_NAME,
			'CompanyName'         => COMPANY_NAME,
			'PhoneNumber'         => COMPANY_TELEPHONE1,
		  ),
		  'Address' => array(
			'StreetLines'         => COMPANY_ADDRESS1,
			'City'                => COMPANY_CITY_TOWN,
			'StateOrProvinceCode' => COMPANY_ZONE,
			'PostalCode'          => COMPANY_POSTAL_CODE,
			'CountryCode'         => gen_get_country_iso_2_from_3(COMPANY_COUNTRY),
		  ),
		),
		'Role'         => 'SHIPPER', // valid values are SHIPPER, THIRD_PARTY, and CONSIGNEE
		'PaymentType'  => 'PREPAID', // valid values are COLLECT and PREPAID
		'PalletWeight' => array(
		  'Units' => substr($pkg->pkg_weight_unit,0,2),
		  'Value' => $pkg->pkg_weight,
		),
		'LineItems' => array( // assume only one skid at a time
		  'FreightClass' => 'CLASS_' . $pkg->ltl_class,
		  'Packaging'    => 'PALLET',
		  'Description'  => 'Product Rate Request',
		  'Weight' => array(
			'Units' => substr($pkg->pkg_weight_unit,0,2),
			'Value' => $pkg->pkg_weight,
		  ),
//		 'Volume' => array(
//		    'Units' => 'CUBIC_FT',
//		    'Value' => 18.0,
//		  ),
		),
	  );
	} else { // provide small package/express freight details
	  $request['RequestedShipment']['PackageCount']  = $num_packages;
	  $request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
	  for ($i = 0; $i < $num_packages; $i++) {
		$weight = ceil($pkg->pkg_weight / $num_packages);
		$request['RequestedShipment']['RequestedPackageLineItems'] = array(
		  'SequenceNumber'    => $i+1,
		  'GroupNumber'       => '1',
		  'GroupPackageCount' => $num_packages,
		  'Weight' => array(
			'Value' => $weight,
			'Units' => substr($pkg->pkg_weight_unit,0,2),
		  ),
		  'Dimensions' => array(
			'Length' => $pkg->pkg_length,
			'Width'  => $pkg->pkg_width,
			'Height' => $pkg->pkg_height,
			'Units'  => $pkg->pkg_dimension_unit,
		  ),
		);
	  }
	}
	return $request;
  }

// ***************************************************************************************************************
//								FEDEX LABEL REQUEST (multipiece compatible) 
// ***************************************************************************************************************
	function retrieveLabel($sInfo) {
		global $messageStack;
		$fedex_results = array();
		if (in_array($sInfo->ship_method, array('I2DEam','I2Dam','I3D'))) { // unsupported ship methods
		  $messageStack->add('The ship method requested is not supported by this tool presently. Please ship the package via a different tool.','error');
		  return false;
		}
		if (MODULE_SHIPPING_FEDEX_V7_TEST_MODE == 'Test') {
			$client = new SoapClient(PATH_TO_TEST_SHIP_WSDL, array('trace' => 1));
		} else {
			$client = new SoapClient(PATH_TO_SHIP_WSDL, array('trace' => 1));
		}
		for ($key = 0; $key < count($sInfo->package); $key++) {
		  $labels = array();
		  $request = $this->FormatFedExShipRequest($sInfo, $key);
//echo 'FedEx Express XML Label Submit String:'; print_r($request); echo '<br />';
		  try {
		    $response = $client->processShipment($request);
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
//echo 'label response array = '; print_r($response); echo '<br />';
		    if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
				if ($key == 0) {
					$sInfo->master_tracking = $response->CompletedShipmentDetail->MasterTrackingId;
				}
				$net_cost  = 0;
				$book_cost = 0;
				$del_date = '';
				if (isset($response->CompletedShipmentDetail->OperationalDetail->DeliveryDate)) {
				  $del_code  = $response->CompletedShipmentDetail->OperationalDetail->DestinationServiceArea;
				  $guar_time = $this->calculateDeliveryTime($sInfo->ship_method, $del_code, $sInfo->residential_address);
				  $del_date  = $response->CompletedShipmentDetail->OperationalDetail->DeliveryDate . ' ' . $guar_time;
				} elseif (isset($response->CompletedShipmentDetail->OperationalDetail->MaximumTransitTime)) {
				  $del_date  = $this->calculateDelivery($response->CompletedShipmentDetail->OperationalDetail->MaximumTransitTime, $sInfo->residential_address);
				} elseif (isset($response->CompletedShipmentDetail->OperationalDetail->TransitTime)) {
				  $del_date  = $this->calculateDelivery($response->CompletedShipmentDetail->OperationalDetail->TransitTime, $sInfo->residential_address);
				}
				if (is_array($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails)) {
				  foreach ($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails as $rate) {
				    switch($rate->RateType) {
				      case 'PAYOR_ACCOUNT_PACKAGE':
				      case 'PAYOR_ACCOUNT_SHIPMENT': $net_cost  = $rate->NetCharge->Amount; break;
					  case 'PAYOR_LIST_SHIPMENT':
					  case 'PAYOR_LIST_PACKAGE':     $book_cost = $rate->NetCharge->Amount; break;
				    }
				  }
				}
				if ($response->CompletedShipmentDetail->CarrierCode == 'FXFR') { // LTL Freight
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';
				  $is_ltl   = true; // special handling for freight, hard coded label types for now
				  $tracking = $response->CompletedShipmentDetail->MasterTrackingId->TrackingNumber;
				  $zone     = '';
				  foreach ($response->CompletedShipmentDetail->ShipmentDocuments as $document) $labels[] = $document->Parts->Image;					
				} else {
				  $is_ltl    = false;
				  $zone      = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails->RateZone;
				  $labels[]  = $response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
				  if (is_array($response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds)) { // Smart Post
					foreach ($response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds as $track_num) {
					  if ($track_num->TrackingIdType == 'GROUND') $tracking = $track_num->TrackingNumber;
					}
				  } else {
					$tracking = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;
				  }
				}
				if (!$tracking) {
				  $messageStack->add('Error - No tracking found in return string.','error');
//echo 'label response array = '; print_r($response); echo '<br />';
				  return false;
				}
				$fedex_results[$key] = array(
					'ref_id'        => $sInfo->purchase_invoice_id . '-' . ($key + 1),
					'tracking'      => $tracking,
					'zone'          => $zone,
					'book_cost'     => $book_cost,
					'net_cost'      => $net_cost,
					'delivery_date' => $del_date,
//					'dim_weight'    => $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber,
//					'billed_weight' => $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber,
				);
				if (sizeof($labels) > 0) {
				  $cnt       = 0;
				  $date      = explode('-',$sInfo->ship_date);
				  $file_path = SHIPPING_DEFAULT_LABEL_DIR.$this->code.'/'.$date[0].'/'.$date[1].'/'.$date[2].'/';
				  validate_path($file_path);
				  foreach ($labels as $label) {
					$this->returned_label = $label;
					// check for label to be for thermal printer or plain paper
					if (MODULE_SHIPPING_FEDEX_V7_PRINTER_TYPE == 'Thermal') { // keep the thermal label encoded for now
						$file_name = $tracking . ($cnt > 0 ? '-'.$cnt : '') . '.lpt'; // thermal printer
						if ($is_ltl && $cnt > 0) $file_name = $tracking . '-' .$cnt . '.pdf'; // BOL must be PDF
					} else {
						$file_name = $tracking . ($cnt > 0 ? '-'.$cnt : '') . '.pdf'; // plain paper
					}
					if (!$handle = fopen($file_path . $file_name, 'w')) { 
						$messageStack->add('Cannot open file (' . $file_path . $file_name . ')','error');
						return false;
					}
					if (fwrite($handle, $label) === false) {
						$messageStack->add('Cannot write to file (' . $file_path . $file_name . ')','error');
						return false;
					}
					fclose($handle);
					$cnt++;
//					$messageStack->add('Successfully retrieved the FedEx shipping label. Tracking # ' . $fedex_results[$key]['tracking'],'success');
				  }
				} else {
					$messageStack->add('Error - No label found in return string.','error');
					return false;				
				}
		    } else {
			  foreach ($response->Notifications as $notification) {
				if (is_object($notification)) {
				  $message .= ' (' . $notification->Severity . ') ' . $notification->Message;
				} else {
				  $message .= ' ' . $notification;
				}
			  }
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';
			  $messageStack->add(SHIPPING_FEDEX_V7_RATE_ERROR . $message, 'error');
			  return false;
		    }
		  } catch (SoapFault $exception) {
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
		    $message = " [label soap fault] ({$exception->faultcode}) {$exception->faultstring}";
		    $messageStack->add(SHIPPING_FEDEX_CURL_ERROR . $message, 'error');
		    return false;
		  }
	    }
		return $fedex_results;
	}

	function FormatFedExShipRequest($pkg, $key) {
		global $ZONE001_DEFINES, $debug, $currencies;
		// process different for freight than express
		$is_freight = (in_array($pkg->ship_method, array('GndFrt','EcoFrt')) && MODULE_SHIPPING_FEDEX_V7_LTL_ACCOUNT_NUMBER) ? true : false;
		$request['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
			  'Key'      => MODULE_SHIPPING_FEDEX_V7_AUTH_KEY,
			  'Password' => MODULE_SHIPPING_FEDEX_V7_AUTH_PWD,
			),
		); 
		$request['ClientDetail'] = array(
		  'AccountNumber' => MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER,
		  'MeterNumber'   => MODULE_SHIPPING_FEDEX_V7_METER_NUMBER,
		);
		$request['TransactionDetail'] = array(
		  'CustomerTransactionId' => '*** FedEx Shipping Request ***',
		);
		$request['Version'] = array( // version 7
		  'ServiceId'    => 'ship', 
		  'Major'        => MODULE_SHIPPING_FEDEX_SHIP_WSDL_VERSION, 
		  'Intermediate' => '0', 
		  'Minor'        => '0',
		);
		$temp = array_flip($this->FedExRateCodes);
		$ship_method = ($pkg->residential_address && ($pkg->ship_method == 'GND')) ? 'GDR' : $pkg->ship_method;
		$request['RequestedShipment'] = array(
		  'ShipTimestamp' => date('c', strtotime($pkg->ship_date)),
		  'DropoffType'   => $this->FedExPickupMap[$pkg->pickup_service],
		  'ServiceType'   => $temp[$ship_method],
		  'PackagingType' => $this->PackageMap[$pkg->pkg_type],
		);
		$request['RequestedShipment']['Shipper'] = array(
		  'Contact' => array(
			'PersonName'          => AR_CONTACT_NAME,
			'CompanyName'         => COMPANY_NAME,
			'PhoneNumber'         => COMPANY_TELEPHONE1,
		  ),
		  'Address' => array(
			'StreetLines' => array(
			  '0' => COMPANY_ADDRESS1,
			  '1' => COMPANY_ADDRESS2,
			),        
			'City'                => COMPANY_CITY_TOWN,
			'StateOrProvinceCode' => COMPANY_ZONE,
			'PostalCode'          => COMPANY_POSTAL_CODE,
			'CountryCode'         => gen_get_country_iso_2_from_3(COMPANY_COUNTRY),
		  ),
		);
		$request['RequestedShipment']['Recipient'] = array(
		  'Contact' => array(
			'PersonName'          => remove_special_chars($pkg->ship_contact),
			'CompanyName'         => remove_special_chars($pkg->ship_primary_name),
			'PhoneNumber'         => strip_alphanumeric($pkg->ship_telephone1),
		  ),
		  'Address' => array (
			'StreetLines' => array(
			  '0' => remove_special_chars($pkg->ship_address1),
			  '1' => remove_special_chars($pkg->ship_address2),
			),        
			'City'                => strtoupper($pkg->ship_city_town),
			'StateOrProvinceCode' => ($pkg->ship_country_code == 'US') ? strtoupper($pkg->ship_state_province) : '',
			'PostalCode'          => strip_alphanumeric($pkg->ship_postal_code),
			'CountryCode'         => gen_get_country_iso_2_from_3($pkg->ship_country_code),
			'Residential'         => $pkg->residential_address ? '1' : '0',
		  ),
		);
		// SmartPost
		if ($pkg->ship_method == '3Dam') {
		  $request['RequestedShipment']['SmartPostDetail'] = array(
		    'Indicia' => $pkg->total_weight < 1 ? 'PRESORTED_STANDARD' : 'PARCEL_SELECT',
		    'HubId'   => FEDEX_SMARTPOST_HUB_ID,
		  );
		}
		$request['RequestedShipment']['ShippingChargesPayment'] = array(
		  'PaymentType'   => $this->PaymentMap[$pkg->bill_charges],
		);
		$pay_acct = MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER;
		if ($pkg->bill_charges == '1' || $pkg->bill_charges == '2') $pay_acct = $pkg->bill_acct;
		$request['RequestedShipment']['ShippingChargesPayment']['Payor'] = array(
		  'AccountNumber' => $pay_acct,
		  'CountryCode'   => 'US',
		);
		if ($is_freight) {
//			$request['ClientDetail']['AccountNumber'] = MODULE_SHIPPING_FEDEX_V7_LTL_ACCOUNT_NUMBER; // causes acct and meter not consistent error
			$request['RequestedShipment']['FreightShipmentDetail'] = array(
				'FedExFreightAccountNumber' => MODULE_SHIPPING_FEDEX_V7_LTL_ACCOUNT_NUMBER,
				'FedExFreightBillingContactAndAddress' => array(
				  'Contact' => array(
					'PersonName'          => AR_CONTACT_NAME,
					'CompanyName'         => COMPANY_NAME,
					'PhoneNumber'         => COMPANY_TELEPHONE1,
				  ),
				  'Address' => array(
					'StreetLines'         => COMPANY_ADDRESS1,
					'City'                => COMPANY_CITY_TOWN,
					'StateOrProvinceCode' => COMPANY_ZONE,
					'PostalCode'          => COMPANY_POSTAL_CODE,
					'CountryCode'         => gen_get_country_iso_2_from_3(COMPANY_COUNTRY),
				  ),
				),
				'PrintedReferences' => array(
				  'Type'  => 'SHIPPER_ID_NUMBER',
				  'Value' => $pkg->purchase_invoice_id,
				),
				'Role'                 => 'SHIPPER', // valid values are SHIPPER, THIRD_PARTY, and CONSIGNEE
				'PaymentType'          => 'PREPAID', // $pkg->bill_charges // valid values are COLLECT and PREPAID
				'CollectTermsType'     => 'STANDARD',
				'DeclaredValuePerUnit' => array(
					'Amount'   => $currencies->clean_value($pkg->package[$key]['value']),
					'Currency' => 'USD',
				),
//				'LiabilityCoverageDetail' => array(
//				  'CoverageType'   => 'NEW',
//				  'CoverageAmount' => array(
//					'Currency' => 'USD',
//					'Amount'   => '50',
//				  ),
//				),
				'TotalHandlingUnits'    => $pkg->ltl_num_pieces,
//				'ClientDiscountPercent' => 0, // should be actual charge
				'PalletWeight' => array(
				  'Units' => substr($pkg->pkg_weight_unit,0,2),
				  'Value' => number_format($pkg->package[$key]['weight'], 0, '.', ''),
				),
				'ShipmentDimensions' => array(
				  'Length' => $pkg->package[$key]['length'] < 32 ? 32 : $pkg->package[$key]['length'],
				  'Width'  => $pkg->package[$key]['width']  < 32 ? 32 : $pkg->package[$key]['width'],
				  'Height' => $pkg->package[$key]['height'] < 16 ? 16 : $pkg->package[$key]['height'],
				  'Units'  => $pkg->pkg_dimension_unit,
				),
				'LineItems' => array( // assume only one skid at a time
				  'FreightClass' => 'CLASS_' . $pkg->ltl_class,
				  'Packaging'    => 'PALLET',
				  'Description'  => $pkg->ltl_description,
//				  'ClassProvidedByCustomer' => false,
				  'HandlingUnits' => $pkg->ltl_num_pieces,
				  'Pieces' => 1,
				  'BillOfLaddingNumber' => $pkg->purchase_invoice_id,
				  'PurchaseOrderNumber' => $pkg->purch_order_id,
				  'Weight' => array(
					'Units' => substr($pkg->pkg_weight_unit,0,2),
					'Value' => number_format($pkg->package[$key]['weight'], 0, '.', ''),
				  ),
				  'Dimensions' => array( // set some minimum dimensions in case defaults are not sent, assume inches for now
					'Length' => $pkg->package[$key]['length'] < 32 ? 32 : $pkg->package[$key]['length'],
					'Width'  => $pkg->package[$key]['width']  < 32 ? 32 : $pkg->package[$key]['width'],
					'Height' => $pkg->package[$key]['height'] < 16 ? 16 : $pkg->package[$key]['height'],
					'Units'  => $pkg->pkg_dimension_unit,
				  ),
//				  'Volume' => array(
//					'Units' => 'CUBIC_FT',
//					'Value' => 30
//				  ),
				),
			);
		} else { // provide small package/express freight details
			$pay_acct = ($pkg->bill_charges == '1' || $pkg->bill_charges == '2') ? $pkg->bill_acct : MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER;
			if ($pkg->cod) {
			  $request['RequestedShipment']['SpecialServicesRequested'] = array(
				'SpecialServiceTypes' => array(
				  'COD',
				),
				'CodDetail' => array(
				  'CollectionType' => $this->CODMap[$pkg->cod_payment_type],
				),
			  );
			}
			if ($pkg->saturday_delivery) {
			  $request['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'SATURDAY_DELIVERY';
			}
			if ($key > 0) { // link to the master package
			  $request['RequestedShipment']['MasterTrackingId'] = $pkg->master_tracking;
			}
			$request['RequestedShipment']['RequestedPackageLineItems'] = array(
			  'SequenceNumber' => $key + 1,
			  'InsuredValue' => array(
				'Amount'   => $currencies->clean_value($pkg->package[$key]['value']),
				'Currency' => 'USD',
			  ),
			  'Weight' => array(
				'Value' => number_format($pkg->package[$key]['weight'], 1, '.', ''),
				'Units' => substr($pkg->pkg_weight_unit,0,2),
			  ),
			  'Dimensions' => array(
				'Length' => $pkg->package[$key]['length'],
				'Width'  => $pkg->package[$key]['width'],
				'Height' => $pkg->package[$key]['height'],
				'Units'  => $pkg->pkg_dimension_unit,
			  ),
			  'CustomerReferences' => array( // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
				'0' => array(
				  'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
				  'Value' => $pkg->purchase_invoice_id . '-' . ($key + 1),
				), 
				'1' => array(
				  'CustomerReferenceType' => 'INVOICE_NUMBER',
				  'Value' => $pkg->purchase_invoice_id,
				),
				'2' => array(
				  'CustomerReferenceType' => 'P_O_NUMBER',
				  'Value' => $pkg->purch_order_id,
				)
			  ),
			);
			if ($pkg->cod) {
			  $request['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested'] = array(
				'CodCollectionAmount' => array(
				  'Amount'   => $pkg->total_amount,
				  'Currency' => 'USD',
				),
				'EMailNotificationDetail' => array(
				  'Shipper' => array(
					'EMailAddress'      => COMPANY_EMAIL,
					'NotifyOnShipment'  => $pkg->email_sndr_ship ? '1' : '0',
					'NotifyOnException' => $pkg->email_sndr_dlvr ? '1' : '0',
					'NotifyOnException' => $pkg->email_sndr_excp ? '1' : '0',
					'Localization"'     => substr($_SESSION['language'], 0, 2),
				  ),
				  'Recipient' => array(
					'EMailAddress'      => $pkg->ship_email,
					'NotifyOnShipment'  => $pkg->email_rcp_ship ? '1' : '0',
					'NotifyOnException' => $pkg->email_rcp_dlvr ? '1' : '0',
					'NotifyOnException' => $pkg->email_rcp_excp ? '1' : '0',
					'Localization"'     => substr($_SESSION['language'], 0, 2),
				  ),
				),
			  );
			}
		}
		$request['RequestedShipment']['PackageCount']  = count($pkg->package);
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';                                                                                
		$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; // valid values ACCOUNT and LIST
		$request['RequestedShipment']['LabelSpecification']['LabelFormatType'] = 'COMMON2D';
		$request['RequestedShipment']['LabelSpecification']['CustomerSpecifiedDetail']['MaskedData'] = 'SHIPPER_ACCOUNT_NUMBER';
		// For thermal labels
		if (!$is_freight && MODULE_SHIPPING_FEDEX_V7_PRINTER_TYPE == 'Thermal') {
		  $request['RequestedShipment']['LabelSpecification']['ImageType'] = 'EPL2';
  		  $request['RequestedShipment']['LabelSpecification']['LabelStockType'] = LABELORIENTATION_THERMAL;
  		  $request['RequestedShipment']['LabelSpecification']['LabelPrintingOrientation'] = 'TOP_EDGE_OF_TEXT_FIRST';
		  if (DOCTABCONTENT == 'Zone001') {
			$request['RequestedShipment']['LabelSpecification']['CustomerSpecifiedDetail']['DocTabContent']['DocTabContentType'] = 'ZONE001';
		    // define the zones and values
			$ZONE001_DEFINES = array( // up to 12 zones can be defined
//			  ''   => array('Header' => TEXT_DATE,   'Value'   => 'REQUEST/SHIPMENT/ShipTimestamp'),
			  '1'  => array('Header' => TEXT_DATE,   'Literal' => date('Y-m-d', strtotime($pkg->ship_date))),
			  '2'  => array('Header' => 'Transit',   'Value'   => 'REPLY/SHIPMENT/RoutingDetail/TransitTime'),
			  '3'  => array('Header' => 'PO Num',    'Literal' => $pkg->purch_order_id),
			  '4'  => array('Header' => 'Inv Num',   'Literal' => $pkg->purchase_invoice_id),
			  '5'  => array('Header' => TEXT_WEIGHT, 'Literal' => number_format($pkg->package[$key]['weight'], 1, '.', '')),
//			  ''   => array('Header' => 'TotalWt',   'Value'   => 'REQUEST/SHIPMENT/TotalWeight/Value'),
			  '7'  => array('Header' => 'DV',        'Literal' => number_format($pkg->package[$key]['value'], 2, '.', '')),
			  '8'  => array('Header' => 'Insured',   'Value'   => 'REQUEST/PACKAGE/InsuredValue/Amount'),

			  '9'  => array('Header' => 'List',      'Value'   => 'REPLY/SHIPMENT/RATES/PAYOR_LIST_PACKAGE/TotalNetCharge/Amount'),
//			  ''   => array('Header' => 'TransId',   'Value'   => 'TRANSACTION/CustomerTransactionId'),
//			  ''   => array('Header' => '',          'Value'   => ''),
			  '12' => array('Header' => 'Net',       'Value'   => 'REPLY/SHIPMENT/RATES/PAYOR_ACCOUNT_PACKAGE/TotalNetCharge/Amount'),
			);
			foreach ($ZONE001_DEFINES as $zone => $settings) {
			  $request['RequestedShipment']['LabelSpecification']['CustomerSpecifiedDetail']['DocTabContent'][DOCTABCONTENT]['DocTabZoneSpecifications'][] = array(
				'ZoneNumber'    => $zone,
				'Header'        => $settings['Header'],
				'DataField'     => $settings['Value'],
 				'LiteralValue'  => $settings['Literal'],
//  			'Justification' => ,
			  );
			}
		  }
		} elseif ($is_freight) {
			$request['RequestedShipment']['LabelSpecification']['LabelFormatType']          = 'FEDEX_FREIGHT_STRAIGHT_BILL_OF_LADING';
			$request['RequestedShipment']['LabelSpecification']['ImageType']                = 'PDF';
			$request['RequestedShipment']['LabelSpecification']['LabelStockType']           = 'PAPER_LETTER';
			$request['RequestedShipment']['LabelSpecification']['LabelPrintingOrientation'] = 'TOP_EDGE_OF_TEXT_FIRST';
			$request['RequestedShipment']['ShippingDocumentSpecification'] = array(
				'ShippingDocumentTypes'     => array('FREIGHT_ADDRESS_LABEL'),
				'FreightAddressLabelDetail' => array(
					'Format' => array(
						'ImageType'          => (MODULE_SHIPPING_FEDEX_V7_PRINTER_TYPE == 'Thermal') ? 'EPL2' : 'PDF',
						'StockType'          => (MODULE_SHIPPING_FEDEX_V7_PRINTER_TYPE == 'Thermal') ? LABELORIENTATION_THERMAL : 'PAPER_4X6',
						'ProvideInstuctions' => '0',
					),
		            'Copies' => '1',
				),
			);
		} else {
			$request['RequestedShipment']['LabelSpecification']['ImageType'] = 'PDF';
			$request['RequestedShipment']['LabelSpecification']['LabelStockType'] = LABELORIENTATION_PDF;
		}
		return $request;
	}

// ***************************************************************************************************************
//								FEDEX DELETE LABEL REQUEST
// ***************************************************************************************************************
  function deleteLabel($method = 'FDXE', $tracking_number = '') {
	global $db, $messageStack;
	if (!$tracking_number) {
	  $messageStack->add('Cannot delete shipment, tracking number was not provided!','error');
	  return false;
	}
	$result = array();
	if (MODULE_SHIPPING_FEDEX_V7_TEST_MODE == 'Test') {
	  $client = new SoapClient(PATH_TO_TEST_SHIP_WSDL, array('trace' => 1));
	} else {
	  $client = new SoapClient(PATH_TO_SHIP_WSDL, array('trace' => 1));
	}
	  $request = $this->FormatFedExDeleteRequest($method, $tracking_number);
//echo 'request = '; print_r($request); echo '<br />';  
	  try {
		$response = $client->deleteShipment($request);
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
		if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
		  $messageStack->add(SHIPPING_FEDEX_V7_DEL_SUCCESS. $tracking_number, 'success');
		} else {
		  foreach ($response->Notifications as $notification) {
			if (is_object($notification)) {
			  $message .= ' (' . $notification->Severity . ') ' . $notification->Message;
			} else {
			  $message .= ' ' . $notification;
			}
		  }
		  $messageStack->add(SHIPPING_FEDEX_V7_DEL_ERROR . $message, 'error');
		  return false;
		}
	  } catch (SoapFault $exception) {
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
		$message = " ({$exception->faultcode}) {$exception->faultstring}";
		$messageStack->add(SHIPPING_FEDEX_CURL_ERROR . $message, 'error');
		return false;
	  }
	return true;
  }

  function FormatFedExDeleteRequest($method, $tracking_number) {
	$request['WebAuthenticationDetail'] = array(
	  'UserCredential' => array(
	    'Key'      => MODULE_SHIPPING_FEDEX_V7_AUTH_KEY,
	    'Password' => MODULE_SHIPPING_FEDEX_V7_AUTH_PWD,
	  ),
	); 
	$request['ClientDetail'] = array(
	  'AccountNumber' => MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER,
	  'MeterNumber'   => MODULE_SHIPPING_FEDEX_V7_METER_NUMBER,
	);
	$request['TransactionDetail'] = array(
	  'CustomerTransactionId' => '*** FedEx Delete Label Request ***',
	);
	$request['Version'] = array(
	  'ServiceId'    => 'ship', 
	  'Major'        => MODULE_SHIPPING_FEDEX_SHIP_WSDL_VERSION, 
	  'Intermediate' => '0', 
	  'Minor'        => '0',
	);
	switch($method) {
	  case 'GND':
	  case 'GDR':    $trackingType = 'GROUND';  break;
	  case 'GndFrt':
	  case 'EcoFrt': $trackingType = 'FREIGHT'; break;
	  default:       $trackingType = 'EXPRESS'; break;
	}
	$request['TrackingId']['TrackingIdType'] = $trackingType;
	$request['TrackingId']['TrackingNumber'] = $tracking_number;
	$request['DeletionControl'] = 'DELETE_ONE_PACKAGE';
	return $request;
  }

// ***************************************************************************************************************
//								FEDEX TRACK REQUEST
// ***************************************************************************************************************
	function trackPackages($track_date = '0000-00-00', $log_id = false) {
		global $db, $messageStack;
		$result = array();
		if (MODULE_SHIPPING_FEDEX_V7_TEST_MODE == 'Test') {
			$messageStack->add('Tracking only works on the FedEx production server!','error');
			return false;
		} else {
			$client = new SoapClient(PATH_TO_TRACK_WSDL, array('trace' => 1));
		}
		if ($log_id) {
			$shipments  = $db->Execute("select id, ref_id, deliver_date, actual_date, tracking_id, notes 
				from " . TABLE_SHIPPING_LOG . " 
				where carrier = '" . $this->code . "' and id = '" . $log_id . "'");
		} else {
			$start_date = $track_date;
			$end_date   = gen_specific_date($track_date, $day_offset =  1);
			$shipments  = $db->Execute("select id, ref_id, deliver_date, actual_date, tracking_id, notes 
				from " . TABLE_SHIPPING_LOG . " 
				where carrier = '" . $this->code . "' 
					and ship_date >= '" . $start_date . "' and ship_date < '" . $end_date . "'");
		}
		while (!$shipments->EOF) {
			$tracking_number = $shipments->fields['tracking_id'];
			if ($shipments->fields['actual_date'] <> '0000-00-00 00:00:00') { // skip if already tracked
			  $shipments->MoveNext();
			  continue;
			}
			$request = $this->FormatFedExTrackRequest($shipments->fields['tracking_id']);
			if (!$request) continue;
//echo 'request = '; print_r($request); echo '<br />';  
			try {
				$response = $client->track($request);
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
				if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
					$actual_date = str_replace('T', ' ', $response->TrackDetails->ActualDeliveryTimestamp);
					$actual_date = substr($actual_date, 0, -6);
					// see if the package was late, flag if so
					$late = '0'; // no data
					if ($shipments->fields['deliver_date'] < $actual_date) {
					  $late = 'L';
//					  $messageStack->add(SHIPPING_FEDEX_V7_TRACK_FAIL . $shipments->fields['ref_id'], 'error');
					} elseif ($response->TrackDetails->StatusCode <> 'DL') {
					  $late = 'T';
					  $messageStack->add(sprintf(SHIPPING_FEDEX_V7_TRACK_STATUS, $shipments->fields['ref_id'], $response->TrackDetails->StatusCode, $response->TrackDetails->StatusDescription), 'caution');
					}
					// update the log file with the actual delivery timestamp, append notes
					$db->Execute("update " . TABLE_SHIPPING_LOG . " 
					  set actual_date = '" . $actual_date . "', deliver_late = '" . $late . "' where id = " . $shipments->fields['id']);
//					$messageStack->add(SHIPPING_FEDEX_V7_TRACK_SUCCESS . $response->TrackDetails->ActualDeliveryTimestamp, 'success');
				} else {
					foreach ($response->Notifications as $notification) {
						if (is_object($notification)) {
				  			$message .= ' (' . $notification->Severity . ') ' . $notification->Message;
						} else {
							$message .= ' ' . $notification;
						}
			  		}
					$messageStack->add(SHIPPING_FEDEX_V7_TRACK_ERROR . $message, 'error');
					return false;
				}
			} catch (SoapFault $exception) {
//echo 'Error Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo 'Error Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
				$message = " ({$exception->faultcode}) {$exception->faultstring}";
				$messageStack->add(SHIPPING_FEDEX_CURL_ERROR . $message, 'error');
				return false;
			}
			$shipments->MoveNext();
		}
		return true;
	}

	function FormatFedExTrackRequest($tracking_id = '') {
		global $debug, $currencies;
		if (!$tracking_id) return false;
		$request = array();
		$request['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
			  'Key'      => MODULE_SHIPPING_FEDEX_V7_AUTH_KEY,
			  'Password' => MODULE_SHIPPING_FEDEX_V7_AUTH_PWD,
			),
		); 
		$request['ClientDetail'] = array(
		  'AccountNumber' => MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER,
		  'MeterNumber'   => MODULE_SHIPPING_FEDEX_V7_METER_NUMBER,
		  'Localization'  => array(
		    'LanguageCode' => 'EN',
		  ),
		);
		$request['TransactionDetail'] = array(
		  'CustomerTransactionId' => '*** TC030_WSVC_Track_v4 _POS ***',
		  'Localization'  => array(
		    'LanguageCode' => 'EN',
		  ),
		);
		$request['Version'] = array( // version 4
		  'ServiceId'    => 'trck', 
		  'Major'        => '4', 
		  'Intermediate' => '0', 
		  'Minor'        => '0',
		);
		$request['PackageIdentifier'] = array( 
		  'Value' => $tracking_id, 
		  'Type'  => 'TRACKING_NUMBER_OR_DOORTAG', 
		);
		return $request;
	}

// ***************************************************************************************************************
//								FEDEX CLOSE REQUEST - **************  NOT USED ********************
// ***************************************************************************************************************
	function closeFedEx($close_date = '', $report_only = false, $report_type = 'MANIFEST') {
		global $messageStack;
		if (MODULE_SHIPPING_FEDEX_V7_TEST_MODE == 'Test') {
			$client = new SoapClient(PATH_TO_TEST_CLOSE_WSDL, array('trace' => 1));
		} else {
			$client = new SoapClient(PATH_TO_CLOSE_WSDL, array('trace' => 1));
		}
		$today = date('c');
		if (!$close_date) $close_date = $today;
		$report_only = ($close_date == $today) ? false : true;
		$date = explode('-', $close_date);
		$error = false;
		$request = $this->FormatFedExCloseRequest($close_date, $report_only, $report_type);
//echo 'request = '; print_r($request); echo '<br />';  
		try {
			$response = $client->groundClose($request);
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
//echo 'close response array = '; print_r($response); echo '<br />';
			if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
				// Fetch the FedEx reports
				$file_path   = DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/reports/' . $this->code . '/' . $date[0] . '/' . $date[1] . '/';
				validate_path($file_path);
				$file_name    = $date[2] . '-' . $response->Manifest->FileName . '.txt';
				$closeReport  = base64_decode($response->Manifest->File);
				$mwReport     = base64_decode($response->MultiweightReport);
				$codReport    = base64_decode($response->CODReport);
//				$hazMatReport = base64_decode($response->HazMatCertificate);
//echo 'file_path = '   . $file_path   . ' and file_name = '   . $file_name   . '<br />';
				if (!$handle = fopen($file_path . $file_name, 'w')) {
					echo 'Cannot open file (' . $file_path . $file_name . ')';
					$error = true;
					continue;
				}
				if (fwrite($handle, $closeReport) === false) {
					$messageStack->add('Cannot write close report to file (' . $file_path . $file_name . ')','error');
					$error = true;
					continue;
				}
				if (fwrite($handle, $mwReport) === false) {
					$messageStack->add('Cannot write multi-weight report to file (' . $file_path . $file_name . ')','error');
					$error = true;
					continue;
				}
				if (fwrite($handle, $codReport) === false) {
					$messageStack->add('Cannot write COD report to file (' . $file_path . $file_name . ')','error');
					$error = true;
					continue;
				}
/*
				if (fwrite($handle, $hazMatReport) === false) {
					$messageStack->add('Cannot write Hazmat report to file (' . $file_path . $file_name . ')','error');
					$error = true;
					continue;
				}
*/
				fclose($handle);
				if (!$error) {
					$messageStack->add(SHIPPING_FEDEX_V7_CLOSE_SUCCESS,'success');
					return true;
				}
			} else {
				foreach ($response->Notifications as $notification) {
					if (is_object($notification)) {
						$message .= ' (' . $notification->Severity . ') ' . $notification->Message;
					} else {
						$message .= ' ' . $notification;
					}
				}
				$messageStack->add(SHIPPING_FEDEX_V7_DEL_ERROR . $message, 'error');
			} 
		} catch (SoapFault $exception) {
//echo 'Error Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
//echo ' Error Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
			$message = " ({$exception->faultcode}) {$exception->faultstring}";
			$messageStack->add(SHIPPING_FEDEX_CURL_ERROR . $message, 'error');
		}
		return false;
	}

	function FormatFedExCloseRequest($date, $report_only = false, $report_type) {
		$request['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
			  'Key'      => MODULE_SHIPPING_FEDEX_V7_AUTH_KEY,
			  'Password' => MODULE_SHIPPING_FEDEX_V7_AUTH_PWD,
			),
		); 
		$request['ClientDetail'] = array(
		  'AccountNumber' => MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER,
		  'MeterNumber'   => MODULE_SHIPPING_FEDEX_V7_METER_NUMBER,
		);
		$request['TransactionDetail'] = array(
		  'CustomerTransactionId' => '*** FedEx Close Request - V2 ***',
		);
		$request['Version'] = array(
		  'ServiceId'    => 'clos', 
		  'Major'        => '2', 
		  'Intermediate' => '0', 
		  'Minor'        => '0',
		);
		if ($report_only) { // if reprinting reports, add these
		  $request['ReportDate']      = $date;
		  $request['CloseReportType'] = $report_type;
		} else { // close today's shipments
		  $request['TimeUpToWhichShipmentsAreToBeClosed'] = $date;
		}
		return $request;
	}

// ***************************************************************************************************************
//								Invoice Reconciliation
// ***************************************************************************************************************
// This function takes a csv file downloaded for FedEx and processes reconciles the invoice to the shippping log
// The format must be from the Invoice Summary format found on the Download Invoice link from FedEx My Account.
  function reconcileInvoice() {
	global $db, $messageStack, $currencies;
	$reconciled = array();
	$count      = 0;
	// first verify the file was uploaded ok
	$upload_name = 'file_name';
	if (!validate_upload($upload_name, 'text', 'csv')) return false;
	$lines_array = file($_FILES[$upload_name]['tmp_name']);
	if (!$shipments = $this->fedExParse($lines_array)) return false;
	$inv_num  = $shipments[0]['Invoice Number'];
	$inv_date = $shipments[0]['Invoice Date'];
	$output   = SHIPPING_FEDEX_RECON_TITLE . date('Y-m-d') . "\n";
	$output  .= sprintf(SHIPPING_FEDEX_RECON_INTRO, $inv_num, $inv_date) . "\n\n";
	foreach ($shipments as $record) {
	  // pull the reference number from the invoice (Original Customer Reference)
	  $ref_num   = $record['Original Customer Reference'];
	  $payor_id  = $record['Payor'];
	  $track_num = trim($record['Ground Tracking ID Prefix'] . ' ' . $record['Express or Ground Tracking ID']);
	  $rcv_name  = $record['Recipient Company'];
	  $ship_name = $record['Shipper Company'];
	  $ship_date = $record['Shipment Date'];
	  $cost      = $record['Net Charge Amount'];
	  if (!$payor_id) continue; // weekly service charge and other non-shipment related.
	  if ($ref_num) {
	    $result = $db->Execute("select cost from " . TABLE_SHIPPING_LOG . " where ref_id = '" . $ref_num . "'");
	    if ($result->RecordCount() == 0) {
	      $output .= sprintf(SHIPPING_FEDEX_RECON_NO_RECORDS, $ship_date, $ref_num, $track_num, $ship_name, $rcv_name, $cost) . "\n";
	      continue;
	    } elseif ($result->recordCount() > 1) {
	      $output .= sprintf(SHIPPING_FEDEX_RECON_TOO_MANY, $ship_date, $ref_num, $track_num, $ship_name, $rcv_name, $cost) . "\n";
	      continue;
	    }
	  } else {
	    $output .= sprintf(SHIPPING_FEDEX_RECON_NO_RECORDS, $ship_date, $ref_num, $track_num, $ship_name, $rcv_name, $cost) . "\n";
	    continue;
	  }
	  $estimate = ($result->fields['cost'] + FEDEX_V7_COST_OFFSET) * (1 + FEDEX_V7_COST_FACTOR);
	  if ($cost > $estimate) {
	  	$output .= sprintf(SHIPPING_FEDEX_RECON_COST_OVER, $ship_date, $ref_num, $track_num, $cost, $result->fields['cost']) . "\n";
	  }
	  $inv_num = strpos($ref_num, '-') ? substr($ref_num, 0, strpos($ref_num, '-')) : $ref_num;
	  $result = $db->Execute("select freight from ".TABLE_JOURNAL_MAIN." where purchase_invoice_id = '$inv_num'");
	  $invoiced = ($result->RecordCount() == 0) ? 0 : $result->fields['freight'];
	  $estimate = ($invoiced + FEDEX_V7_COST_OFFSET) * (1 + FEDEX_V7_COST_FACTOR);
	  if ($cost > $estimate) {
	    $output .= sprintf(SHIPPING_FEDEX_RECON_COST_OVER_INV, $ship_date, $ref_num, $track_num, $cost, $invoiced) . "\n";
	  }
	  $reconciled[] = $ref_num;
	  $count++;
	}
	$output .= "\n" . sprintf(SHIPPING_FEDEX_RECON_SUMMARY, $count) . "\n";
	// set the reconciled flag
	if (sizeof($reconciled) > 0) {
	  $db->Execute("update " . TABLE_SHIPPING_LOG . " set reconciled = '1' where ref_id in ('" . implode("','", $reconciled) . "')");
	}
	// output results
	gen_add_audit_log('FedEx Reconciliation Report', 'Records: ' . $count);
	header("Content-type: plain/txt");
	header("Content-disposition: attachment; filename=FedEx-" . $inv_num . ".txt; size=" . strlen($output));
	header('Pragma: cache');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header('Connection: close');
	header('Expires: ' . date('r', time()+60*60));
	header('Last-Modified: ' . date('r'));
	print $output;
	die;
  }

// ***************************************************************************************************************
//								Support Functions
// ***************************************************************************************************************
	function calculateDelivery($transit_time, $res = true) {
	  $today       = getdate();
	  $today_date  = date('Y-m-d');
	  $day_of_week = $today['wday'] - 1;  // 0 - Monday thru 6 - Sunday
	  $holidays = array(
		date('Y-m-d', strtotime('1st January')),              // New Years Day
		date('Y-m-d', strtotime('Last Monday May')),          // Memorial Day
		date('Y-m-d', strtotime('4th July')),                 // Independence Day
		date('Y-m-d', strtotime('First Monday September')),   // Labor Day
		date('Y-m-d', strtotime('Fourth Thursday November')), // Thanksgiving Day
		date('Y-m-d', strtotime('25th December')),            // Christmas Day
	  );
	  switch ($transit_time) {
		case 'ONE_DAY':   $offset = 1; break;
		case 'TWO_DAYS':  $offset = 2; break;
		case 'THREE_DAYS':$offset = 3; break;
		case 'FOUR_DAYS': $offset = 4; break;
		case 'FIVE_DAYS': $offset = 5; break;
		case 'SIX_DAYS':  $offset = 6; break;
		case 'SEVEN_DAYS':$offset = 7; break;
		case 'EIGHT_DAYS':$offset = 8; break;
		case 'NINE_DAYS': $offset = 9; break;
	  }
	  if     (($day_of_week + $offset) > 9) { $offset = $offset + 4; } // passed through two weekends
	  elseif (($day_of_week + $offset) > 4) { $offset = $offset + 2; } // passed through one weekend
	  $delivery = date('Y-m-d', strtotime("+$offset days"));
	  // check for holidays
	  foreach ($holidays as $holiday) {
	    if ($today_date <= $holiday && $delivery >= $holiday) {
		  $offset++;
	      $delivery = date('Y-m-d', strtotime("+$offset days"));
		}
	  }
	  $d = getdate(strtotime("+$offset days"));
	  $delivery = date('Y-m-d', mktime(0, 0, 0, $d['mon'], $d['mday'], $d['year']));
	  return $delivery . ' ' . ($res ? GUAR_TIME_RE : GUAR_TIME_GD);
	}

    function calculateDeliveryTime($method, $code, $res = true) {
	  switch ($method) {
	    default:
	    case 'I3D':
	    case '2Dpm':
	    case '3Dpm':   $guar_time = $res ? GUAR_TIME_RE : GUAR_TIME_2D; break;
	    case 'GND': // handled in calculateDelivery
	    case 'GDR': // handled in calculateDelivery
		case 'EcoFrt':
	    case 'GndFrt': $guar_time = GUAR_TIME_CM; break;
	    case 'I2Dam':
	    case 'I2DEam':
	    case '1DFrt':
	    case '2DFrt':
	    case '3DFrt':  $guar_time = GUAR_TIME_PR; break;
	    case '1Dpm':  
		  switch ($code) {
		    default:
			case 'A1': 
			case 'A2':
			case 'AA':
			case 'A4': $guar_time = GUAR_TIME_CM; break;
			case 'A3':
			case 'A5':
			case 'AM': $guar_time = GUAR_TIME_PR; break;
		  } 
		  break;
	    case '1Dam':  
		  switch ($code) {
		    default:
			case 'A1': 
			case 'A2':
			case 'AA':
			case 'A4': $guar_time = GUAR_TIME_P1; break;
			case 'A3':
			case 'A5':
			case 'AM': $guar_time = GUAR_TIME_PR; break;
			case 'RM':
			case 'PM':
			case 'A6': $guar_time = GUAR_TIME_CM; break;
		  } 
		  break;
	    case '1DEam':  
		  switch ($code) {
		    default:
			case 'A1': $guar_time = GUAR_TIME_A1; break;
			case 'A2':
			case 'A3': $guar_time = GUAR_TIME_A2; break;
			case 'A4': $guar_time = GUAR_TIME_A4; break;
			case 'A5':
			case 'A6': $guar_time = GUAR_TIME_A5; break;
		  } 
		  break;
	  }
	  return $guar_time;
	}

	function fedExParse($lines) { // csv parse with all fields enclosed in double quotes
	  if (!$lines) return false;
	  $title_line = trim(array_shift($lines)); // pull header
	  if (strlen($title_line) < 10) $title_line = trim(array_shift($lines)); // for blank first line
	  $title_line = substr($title_line, 1, strlen($title_line) - 2); // strip the starting and ending double quote
	  $titles = explode('";"', $title_line);
	  $records = array();
	  foreach ($lines as $line_num => $line) {
	    $line = substr($line, 1, strlen($line) - 2); // strip the starting and ending double quote
	    $parsed_array = explode('";"', $line);
	    $fields = array();
	    for ($field_num = 0; $field_num < count($titles); $field_num++) {
		  $fields[$titles[$field_num]] = $parsed_array[$field_num];
	    }
	    $records[] = $fields;
	  }
	  return $records;
	}

} // end class
?>