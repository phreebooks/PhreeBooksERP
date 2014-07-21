<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008-2014 PhreeSoft                               |
// | http://www.PhreeSoft.com                                        |
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
//  Path: /modules/shipping/methods/ups/ups.php
//

define('NL', chr(10).chr(13));
define('MODULE_SHIPPING_UPS_RATE_URL',          'https://www.ups.com/ups.app/xml/Rate');
define('MODULE_SHIPPING_UPS_RATE_URL_TEST',     'https://wwwcie.ups.com/ups.app/xml/Rate');
define('MODULE_SHIPPING_UPS_TNT_URL',           'https://www.ups.com/ups.app/xml/TimeInTransit');
define('MODULE_SHIPPING_UPS_TNT_URL_TEST',      'https://wwwcie.ups.com/ups.app/xml/TimeInTransit');
define('MODULE_SHIPPING_UPS_SHIP_URL',          'https://www.ups.com/ups.app/xml/ShipConfirm');
define('MODULE_SHIPPING_UPS_SHIP_URL_TEST',     'https://wwwcie.ups.com/ups.app/xml/ShipConfirm');
define('MODULE_SHIPPING_UPS_LABEL_URL',         'https://www.ups.com/ups.app/xml/ShipAccept');
define('MODULE_SHIPPING_UPS_LABEL_URL_TEST',    'https://wwwcie.ups.com/ups.app/xml/ShipAccept');
define('MODULE_SHIPPING_UPS_VOID_SHIPMENT',     'https://www.ups.com/ups.app/xml/Void');
define('MODULE_SHIPPING_UPS_VOID_SHIPMENT_TEST','https://wwwcie.ups.com/ups.app/xml/Void');
define('MODULE_SHIPPING_UPS_QUANTUM_VIEW',      'https://www.ups.com/ups.app/xml/QVEvents');
define('MODULE_SHIPPING_UPS_QUANTUM_VIEW_TEST', 'https://wwwcie.ups.com/ups.app/xml/QVEvents');
//define('','http://wwwcie.ups.com/webservices/ShipBinding');
//define('MODULE_SHIPPING_UPS_LTL_RATE_URL',      'https://www.ups.com/webservices/FreightRate');
//define('MODULE_SHIPPING_UPS_LTL_RATE_URL_TEST', 'https://onlinetools.ups.com/webservices/FreightRate');

// Set the UPS tracking URL
define('UPS_TRACKING_URL','http://wwwapps.ups.com/etracking/tracking.cgi?tracknums_displayed=5&TypeOfInquiryNumber=T&HTMLVersion=4.0&sort_by=status&InquiryNumber1=');
// constants used in rate screen to match carrier descriptions
define('ups_1DEam', MODULE_SHIPPING_UPS_1DM);
define('ups_1Dam',  MODULE_SHIPPING_UPS_1DA);
define('ups_1Dpm',  MODULE_SHIPPING_UPS_1DP);
define('ups_2Dam',  MODULE_SHIPPING_UPS_2DM);
define('ups_2Dpm',  MODULE_SHIPPING_UPS_2DP);
define('ups_3Dpm',  MODULE_SHIPPING_UPS_3DS);
define('ups_GND',   MODULE_SHIPPING_UPS_GND);
define('ups_I2DEam',MODULE_SHIPPING_UPS_XDM);
define('ups_I2Dam', MODULE_SHIPPING_UPS_XPR);
define('ups_I3D',   MODULE_SHIPPING_UPS_XPD);
define('ups_IGND',  MODULE_SHIPPING_UPS_STD);
//define('ups_GndFrt',MODULE_SHIPPING_UPS_GDF);
// UPS WSDL paths
//define('PATH_TO_TEST_RATE_WSDL',  DIR_FS_WORKING . 'methods/ups/TestRateService_v7.wsdl');
//define('PATH_TO_TEST_SHIP_WSDL',  DIR_FS_WORKING . 'methods/ups/TestShipService_v7.wsdl');
//define('PATH_TO_TEST_CLOSE_WSDL', DIR_FS_WORKING . 'methods/ups/TestCloseService_v2.wsdl');
define('PATH_TO_RATE_WSDL',       DIR_FS_WORKING . 'methods/ups/RateWS.wsdl');
define('PATH_TO_LTL_RATE_WSDL',   DIR_FS_WORKING . 'methods/ups/FreightRate.wsdl');
define('PATH_TO_SHIP_WSDL',       DIR_FS_WORKING . 'methods/ups/Ship.wsdl');
define('PATH_TO_LTL_SHIP_WSDL',   DIR_FS_WORKING . 'methods/ups/FreightShip.wsdl');
define('PATH_TO_VOID_WSDL',       DIR_FS_WORKING . 'methods/ups/Void.wsdl');
//define('PATH_TO_TRACK_WSDL',      DIR_FS_WORKING . 'methods/ups/TrackService_v4.wsdl');
//define('PATH_TO_CLOSE_WSDL',      DIR_FS_WORKING . 'methods/ups/CloseService_v2.wsdl');
ini_set("soap.wsdl_cache_enabled", "0");

class ups {
  // UPS Time in Transit code map (US Only)
  var $UPSTnTCodes = array(
	'1DM'=>'1DEam',
	'1DA'=>'1Dam',
	'1DP'=>'1Dpm',
	'2DA'=>'2Dpm',
	'3DS'=>'3Dpm',
	'GND'=>'GND',
	'01' =>'I2Dam',
	'05' =>'I3D',
	'03' =>'IGND',
  );

// UPS Rate code map (US Origin)
  var $UPSRateCodes = array(	
	'14'=>'1DEam',
	'01'=>'1Dam',
	'13'=>'1Dpm',
	'59'=>'2Dam',
	'02'=>'2Dpm',
	'12'=>'3Dpm',
	'03'=>'GND',
	'54'=>'I2DEam',
	'07'=>'I2Dam',
	'08'=>'I3D',
	'11'=>'IGND',
  );
/* 
// For Canada Origin
  var $UPSRateCodes = array(	
	'01'=>'1Dam',
	'02'=>'2Dpm',
	'07'=>'I2Dam',
	'08'=>'I3D',
	'11'=>'IGND',
	'12'=>'3Dpm',
	'13'=>'1Dpm',
	'14'=>'1DEam',
	'54'=>'I2DEam',
  );
// For EU Origin
  var $UPSRateCodes = array(	
	'07'=>'I2Dam',
	'11'=>'IGND',
	'54'=>'I2DEam',
	'65'=>'I3D',
  );

// See UPS Service Code specification for Puerto Rico, Mexico, and all other origins
*/
  function ups() {
    $this->code    = 'ups';
    $this->version = '1.0';
  }

  function keys() {
    return array(
	  array('key' => 'MODULE_SHIPPING_UPS_TITLE',          'default' => 'UPS'),
	  array('key' => 'MODULE_SHIPPING_UPS_SHIPPER_NUMBER', 'default' => ''),
	  array('key' => 'MODULE_SHIPPING_UPS_USER_ID',        'default' => ''),
	  array('key' => 'MODULE_SHIPPING_UPS_PASSWORD',       'default' => ''),
	  array('key' => 'MODULE_SHIPPING_UPS_ACCESS_KEY',     'default' => ''),
	  array('key' => 'MODULE_SHIPPING_UPS_TEST_MODE',      'default' => 'Test'),
	  array('key' => 'MODULE_SHIPPING_UPS_PRINTER_TYPE',   'default' => 'GIF'),
	  array('key' => 'MODULE_SHIPPING_UPS_PRINTER_NAME',   'default' => 'zebra'),
	  array('key' => 'MODULE_SHIPPING_UPS_LABEL_SIZE',     'default' => '6'),
	  array('key' => 'MODULE_SHIPPING_UPS_TYPES',          'default' => '1DEam,1Dam,1Dpm,2Dam,2Dpm,3Dpm,GND,I2DEam,I2Dam,I3D,IGND'),
	  array('key' => 'MODULE_SHIPPING_UPS_SORT_ORDER',     'default' => '15'),
	);
  }

  function configure($key) {
    switch ($key) {
	  case 'MODULE_SHIPPING_UPS_TEST_MODE':
	    $temp = array(
		  array('id' => 'Test',       'text' => TEXT_TEST),
		  array('id' => 'Production', 'text' => TEXT_PRODUCTION),
	    );
	    $html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
	    break;
	  case 'MODULE_SHIPPING_UPS_PRINTER_TYPE':
	    $temp = array(
		  array('id' => 'GIF', 'text' => TEXT_PRINTABLE_IMAGE),
		  array('id' => 'EPL', 'text' => TEXT_THERMAL),
	    );
	    $html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
	    break;
	  case 'MODULE_SHIPPING_UPS_TYPES':
	    $temp = array(
		  array('id' => '1DEam', 'text' => MODULE_SHIPPING_UPS_1DM),
		  array('id' => '1Dam',  'text' => MODULE_SHIPPING_UPS_1DA),
		  array('id' => '1Dpm',  'text' => MODULE_SHIPPING_UPS_1DP),
		  array('id' => '2Dpm',  'text' => MODULE_SHIPPING_UPS_2DP),
		  array('id' => '3Dpm',  'text' => MODULE_SHIPPING_UPS_3DS),
		  array('id' => 'GND',   'text' => MODULE_SHIPPING_UPS_GND),
//		  array('id' => 'GndFrt','text' => MODULE_SHIPPING_UPS_GDF),
		  array('id' => 'I2DEam','text' => MODULE_SHIPPING_UPS_XDM),
		  array('id' => 'I2Dam', 'text' => MODULE_SHIPPING_UPS_XPR),
		  array('id' => 'I3D',   'text' => MODULE_SHIPPING_UPS_XPD),
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
	    case 'MODULE_SHIPPING_UPS_TYPES': // read the checkboxes
		  write_configure($key['key'], implode(',', $_POST[$field]));
		  break;
		default:  // just write the value
		  if (isset($_POST[$field])) write_configure($key['key'], $_POST[$field]);
	  }
	}
  }

  function quote($pkg) {
	global $messageStack;
	if ($pkg->pkg_weight == 0) {
	  $messageStack->add(SHIPPING_ERROR_WEIGHT_ZERO, 'error');
	  return false;
	}
	if (!$pkg->split_large_shipments && $pkg->pkg_weight > 150) {
	  $messageStack->add(SHIPPING_UPS_ERROR_WEIGHT_150, 'caution');
	  return false;
	}
	if ($pkg->ship_to_postal_code == '') {
	  $messageStack->add(SHIPPING_UPS_ERROR_POSTAL_CODE, 'error');
	  return false;
	}
	$status = $this->getUPSrates($pkg);
	if ($status['result'] == 'error') {
	  $messageStack->add(SHIPPING_UPS_RATE_ERROR . $status['message'], 'error');
	  return false;
	} elseif ($status['result'] == 'CityMatch') {
	  $messageStack->add(SHIPPING_UPS_RATE_CITY_MATCH, 'error');
	  return false;
	}
	return $status;
  }

	// ***************************************************************************************************************
	//								UPS RATE AND SERVICE REQUEST
	// ***************************************************************************************************************
	function FormatRateRequest() {
		global $pkg;
		$sBody  =      '<?xml version="1.0"?>';
		$sBody .= NL . '<AccessRequest xml:lang="en-US">';
		$sBody .= NL . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= NL . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= NL . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= NL . '</AccessRequest>';
		$sBody .= NL . '<?xml version="1.0"?>';
		$sBody .= NL . '<RatingServiceSelectionRequest xml:lang="en-US">';
		$sBody .= NL . '<Request>';
		$sBody .= NL . '<TransactionReference>';
		$sBody .= NL . '<CustomerContext>Rating and Service</CustomerContext>';
		$sBody .= NL . '<XpciVersion>1.0001</XpciVersion>';
		$sBody .= NL . '</TransactionReference>';
		$sBody .= NL . '<RequestAction>rate</RequestAction>'; // must be rate for tool to work
		$sBody .= NL . '<RequestOption>shop</RequestOption>'; // must be shop to 
		$sBody .= NL . '</Request>';
		$sBody .= NL . '<PickupType><Code>' . $pkg->pickup_service . '</Code></PickupType>';
		$sBody .= NL . '<CustomerClassification><Code>' . '01' . '</Code></CustomerClassification>'; // wholesale (default for PickupType 01)
		$sBody .= NL . '<Shipment>';
		$sBody .= NL . '<Shipper>';
		$sBody .= NL . '<ShipperNumber>' . MODULE_SHIPPING_UPS_SHIPPER_NUMBER . '</ShipperNumber>';
		$sBody .= NL . '<Address>';
		if (COMPANY_CITY_TOWN) $sBody .= NL . '<City>' . COMPANY_CITY_TOWN . '</City>';
		if (COMPANY_ZONE) $sBody .= NL . '<StateProvinceCode>' . COMPANY_ZONE . '</StateProvinceCode>';
		if (COMPANY_POSTAL_CODE) $sBody .= NL . '<PostalCode>' . COMPANY_POSTAL_CODE . '</PostalCode>';
//		$country_name = gen_get_country_iso_2_from_3(COMPANY_COUNTRY);
		$sBody .= NL . '<CountryCode>' . gen_get_country_iso_2_from_3(COMPANY_COUNTRY) . '</CountryCode>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</Shipper>';
		$sBody .= NL . '<ShipTo>';
		$sBody .= NL . '<Address>';
		if ($pkg->ship_to_city) $sBody .= NL.'<City>' . $pkg->ship_to_city . '</City>';
		if ($pkg->ship_to_state) $sBody .= NL.'<StateProvinceCode>' . $pkg->ship_to_state . '</StateProvinceCode>';
		if ($pkg->ship_to_postal_code) $sBody .= NL . '<PostalCode>' . $pkg->ship_to_postal_code . '</PostalCode>';
//		$country_name = gen_get_country_iso_2_from_3($pkg->ship_to_country_code);
		$sBody .= NL . '<CountryCode>' . $pkg->ship_to_country_iso2 . '</CountryCode>';
		if ($pkg->residential_address) $sBody .= NL . '<ResidentialAddress></ResidentialAddress>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</ShipTo>';
		$sBody .= NL . '<ShipFrom>';
		$sBody .= NL . '<Address>';
		if ($pkg->ship_city_town) $sBody .= NL . '<City>' . $pkg->ship_city_town . '</City>';
		if ($pkg->ship_state_province) $sBody .= NL . '<StateProvinceCode>' . $pkg->ship_state_province . '</StateProvinceCode>';
		if ($pkg->ship_postal_code) $sBody .= NL . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
//		$country_name = gen_get_country_iso_2_from_3($pkg->ship_country_code);
		$sBody .= NL . '<CountryCode>' . $pkg->ship_from_country_iso2 . '</CountryCode>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</ShipFrom>';
		$sBody .= NL . '<ShipmentWeight>';
		$sBody .= NL . '<UnitOfMeasurement><Code>' . $pkg->pkg_weight_unit . '</Code></UnitOfMeasurement>';
		$ShipmentWeight = 0;
		foreach ($this->package as $pkgnum) $ShipmentWeight += $pkgnum['weight'];
		$sBody .= NL . '<Weight>' . $ShipmentWeight . '</Weight>';
		$sBody .= NL . '</ShipmentWeight>';
		foreach ($this->package as $pkgnum) { // Enter each package 
			$sBody .= NL . '<Package>';
			$sBody .= NL . '<PackagingType><Code>'.$pkgnum['PackageTypeCode'].'</Code></PackagingType>';
			$sBody .= NL . '<Dimensions>';
			$sBody .= NL . '<UnitOfMeasurement><Code>'.$pkgnum['DimensionUnit'].'</Code></UnitOfMeasurement>';
			$sBody .= NL . '<Length>'.$pkgnum['Length'].'</Length>';
			$sBody .= NL . '<Width>'.$pkgnum['Width'].'</Width>';
			$sBody .= NL . '<Height>'.$pkgnum['Height'].'</Height>';
			$sBody .= NL . '</Dimensions>';
			$sBody .= NL . '<PackageWeight>';
			$sBody .= NL . '<UnitOfMeasurement><Code>'.$pkgnum['WeightUnit'].'</Code></UnitOfMeasurement>';
			$sBody .= NL . '<Weight>'.$pkgnum['Weight'].'</Weight>';
			$sBody .= NL . '</PackageWeight>';
			$temp = '';
			if ($pkgnum['DeliveryConfirmation']) {
				$temp .= NL . '<DeliveryConfirmation>';
				$temp .= NL . '<DCISType>'.$pkgnum['DeliveryConfirmation'].'</DCISType>';
				$temp .= NL . '</DeliveryConfirmation>';
			}
			if ($pkgnum['InsuranceCurrencyCode']) {
				$temp .= NL . '<InsuredValue>';
				$temp .= NL . '<CurrencyCode>'.$pkgnum['InsuranceCurrencyCode'].'</CurrencyCode>';
				$temp .= NL . '<MonetaryValue>'.$pkgnum['InsuranceValue'].'</MonetaryValue>';
				$temp .= NL . '</InsuredValue>';
			}
			if ($temp) $sBody .= NL . '<PackageServiceOptions>' . $temp . NL . '</PackageServiceOptions>';
			if ($pkgnum['AdditionalHandling']) $sBody .= NL . '<AdditionalHandling></AdditionalHandling>';
			$sBody .= NL . '</Package>';
		}
		$temp = '';
		if ($pkg->saturday_pickup) $temp .= NL . '<SaturdayPickupIndicator>' . $pkg->saturday_pickup . '</SaturdayPickupIndicator>';
		if ($pkg->saturday_delivery) $temp .= NL . '<SaturdayDeliveryIndicator>' . $pkg->saturday_delivery . '</SaturdayDeliveryIndicator>';
		if ($pkg->cod) {
			$temp .= NL . '<COD><CODCode>3</CODCode>';
			if ($pkg->cod_payment_type == 1 || $pkg->cod_payment_type == 2 || $pkg->cod_payment_type == 3) {
				$payment_type = '9'; // check, money order, cashier's check
			} else {
				$payment_type = '1'; // cash
			}
			$temp .= '<CODFundsCode>' . $payment_type . '</CODFundsCode>';
			$temp .= '<CODAmount><CurrencyCode>' . $pkg->cod_currency . '</CurrencyCode>';
			$temp .= '<MonetaryValue>' . $pkg->cod_amount . '</MonetaryValue></CODAmount>';
			$temp .= '</COD>';
		}
		if ($temp) $sBody .= NL . '<ShipmentServiceOptions>' . $temp . NL . '</ShipmentServiceOptions>';
		if ($pkg->handling_charge) {
			$sBody .= NL . '<HandlingCharge><FlatRate><CurrencyCode>' . $pkg->handling_charge_currency . '</CurrencyCode>';
			$sBody .= '<MonetaryValue>' . $pkg->handling_charge_value . '</MonetaryValue></FlatRate></HandlingCharge>';
		}
		$sBody .= NL . '<RateInformation>';
		$sBody .= NL . '<NegotiatedRatesIndicator>1</NegotiatedRatesIndicator>';
		$sBody .= NL . '</RateInformation>';
		$sBody .= NL . '</Shipment>';
		$sBody .= NL . '</RatingServiceSelectionRequest>';
		$sBody .= NL;
		return $sBody;
	}
	
	// ***************************************************************************************************************
	//								UPS LTL RATE REQUEST
	// ***************************************************************************************************************
	function FormatLTLRateRequest() {
		global $pkg;
		$sBody  =      '<?xml version="1.0"?>';
		$sBody .= NL . '<AccessRequest xml:lang="en-US">';
		$sBody .= NL . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= NL . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= NL . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= NL . '</AccessRequest>';
		$sBody .= NL . '<?xml version="1.0"?>';
		$sBody .= NL . '<FreightRateRequest xml:lang="en-US">';
		$sBody .= NL . '<Request>';
//		$sBody .= NL . '<RequestOption>' . 'shop' . '</RequestOption>'; // must be shop to 
		$sBody .= NL . '<TransactionReference>';
		$sBody .= NL . '<CustomerContext>LTL Rating</CustomerContext>';
//		$sBody .= NL . '<XpciVersion>1.0001</XpciVersion>';
		$sBody .= NL . '</TransactionReference>';
//		$sBody .= NL . '<RequestAction>' . 'rate' . '</RequestAction>'; // must be rate for tool to work
		$sBody .= NL . '</Request>';
//		$sBody .= NL . '<PickupType><Code>' . $pkg->pickup_service . '</Code></PickupType>';
//		$sBody .= NL . '<CustomerClassification><Code>' . '01' . '</Code></CustomerClassification>'; // wholesale (default for PickupType 01)
//		$sBody .= NL . '<Shipment>';

		$sBody .= NL . '<ShipFrom>';
		$sBody .= NL . '<Address>';
		if ($pkg->ship_city_town)      $sBody .= NL . '<City>' . $pkg->ship_city_town . '</City>';
		if ($pkg->ship_state_province) $sBody .= NL . '<StateProvinceCode>' . $pkg->ship_state_province . '</StateProvinceCode>';
		if ($pkg->ship_postal_code)    $sBody .= NL . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
//		$country_name = gen_get_country_iso_2_from_3($pkg->ship_country_code);
		$sBody .= NL . '<CountryCode>' . $pkg->ship_from_country_iso2 . '</CountryCode>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</ShipFrom>';

		$sBody .= NL . '<ShipTo>';
		$sBody .= NL . '<Address>';
		if ($pkg->ship_to_city)        $sBody .= NL.'<City>' . $pkg->ship_to_city . '</City>';
		if ($pkg->ship_to_state)       $sBody .= NL.'<StateProvinceCode>' . $pkg->ship_to_state . '</StateProvinceCode>';
		if ($pkg->ship_to_postal_code) $sBody .= NL . '<PostalCode>' . $pkg->ship_to_postal_code . '</PostalCode>';
//		$country_name = gen_get_country_iso_2_from_3($pkg->ship_to_country_code);
		$sBody .= NL . '<CountryCode>' . $pkg->ship_to_country_iso2 . '</CountryCode>';
		if ($pkg->residential_address) $sBody .= NL . '<ResidentialAddress></ResidentialAddress>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</ShipTo>';

		$sBody .= NL . '<PaymentInformation>';
		$sBody .= NL . '<Payer>';
		$sBody .= NL . '<Address>';
		if (COMPANY_CITY_TOWN)   $sBody .= NL . '<City>' . COMPANY_CITY_TOWN . '</City>';
		if (COMPANY_ZONE)        $sBody .= NL . '<StateProvinceCode>' . COMPANY_ZONE . '</StateProvinceCode>';
		if (COMPANY_POSTAL_CODE) $sBody .= NL . '<PostalCode>' . COMPANY_POSTAL_CODE . '</PostalCode>';
//		$country_name = gen_get_country_iso_2_from_3(COMPANY_COUNTRY);
		$sBody .= NL . '<CountryCode>' . gen_get_country_iso_2_from_3(COMPANY_COUNTRY) . '</CountryCode>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '<ShipperNumber>' . MODULE_SHIPPING_UPS_SHIPPER_NUMBER . '</ShipperNumber>';
		$sBody .= NL . '</Payer>';
		$sBody .= NL . '</PaymentInformation>';

		$sBody .= NL . '<ShipmentBillingOption>';
		$sBody .= NL . '<Code>10</Code>';
		$sBody .= NL . '</ShipmentBillingOption>';

		$sBody .= NL . '<Service>';
		$sBody .= NL . '<Code>308</Code>'; // 308 - LTL; 309 - LTL Guaranteed
		$sBody .= NL . '</Service>';

		$sBody .= NL . '<Commodity>';
		$sBody .= NL . '<Description>' . 'Goods' . '</Description>';
		$sBody .= NL . '<Weight>';
		$ShipmentWeight = 0;
		foreach ($this->package as $pkgnum) $ShipmentWeight += $pkgnum['weight'];
		$sBody .= NL . '<Value>' . $ShipmentWeight . '</Value>';
		$sBody .= NL . '<UnitOfMeasurement><Code>' . $pkg->pkg_weight_unit . '</Code></UnitOfMeasurement>';
		$sBody .= NL . '</Weight>';
		$sBody .= NL . '<Dimensions>';
		$sBody .= NL . '<UnitOfMeasurement><Code>' . $pkgnum['DimensionUnit'] . '</Code></UnitOfMeasurement>';
		$sBody .= NL . '<Length>' . $pkgnum['Length'] . '</Length>';
		$sBody .= NL . '<Width>'  . $pkgnum['Width']  . '</Width>';
		$sBody .= NL . '<Height>' . $pkgnum['Height'] . '</Height>';
		$sBody .= NL . '</Dimensions>';
		$sBody .= NL . '<NumberOfPieces>' . sizeof($this->package) . '</NumberOfPieces>';
		$sBody .= NL . '<PackagingType><Code>' . $pkgnum['PackageTypeCode'] . '</Code></PackagingType>';
//		$sBody .= NL . '<DangerousGoodsIndicator>' . $pkgnum['tbd'] . '</DangerousGoodsIndicator>';
		$sBody .= NL . '<FreightClass>' . $pkg->ltl_class . '</FreightClass>';
		$sBody .= NL . '</Commodity>';

		$temp = '';
		if ($pkg->residential_address) $temp .= '<ResidentialDeliveryIndicator />';
		if ($pkg->cod) {
			$temp .= NL . '<COD>';
			if ($pkg->cod_payment_type == 1 || $pkg->cod_payment_type == 2 || $pkg->cod_payment_type == 3) {
				$payment_type = '9'; // check, money order, cashier's check
			} else {
				$payment_type = '1'; // cash
			}
			$temp .= '<CODValue><CurrencyCode>' . $pkg->cod_currency . '</CurrencyCode>';
			$temp .= '<MonetaryValue>' . $pkg->cod_amount . '</MonetaryValue></CODValue>';
			$temp .= '<CODPaymentMethod><Code>' . $payment_type . '</Code></CODPaymentMethod>';
			$temp .= '</COD>';
		}

/*
		if (gen_not_null($pkgnum['DeliveryConfirmation'])) {
			$temp .= NL . '<DeliveryConfirmation>';
			$temp .= NL . '<DCISType>' . $pkgnum['DeliveryConfirmation'] . '</DCISType>';
			$temp .= NL . '</DeliveryConfirmation>';
		}
		if (gen_not_null($pkgnum['InsuranceCurrencyCode'])) {
			$temp .= NL . '<InsuredValue>';
			$temp .= NL . '<CurrencyCode>' . $pkgnum['InsuranceCurrencyCode'] . '</CurrencyCode>';
			$temp .= NL . '<MonetaryValue>' . $pkgnum['InsuranceValue'] . '</MonetaryValue>';
			$temp .= NL . '</InsuredValue>';
		}
*/
		if ($temp) $sBody .= NL . '<PackageServiceOptions>' . $temp . NL . '</PackageServiceOptions>';
/*
//		if ($pkgnum['AdditionalHandling']) $sBody .= NL . '<AdditionalHandling></AdditionalHandling>';
		$temp = '';
		if ($pkg->saturday_pickup)   $temp .= NL . '<SaturdayPickupIndicator>' . $pkg->saturday_pickup . '</SaturdayPickupIndicator>';
		if ($pkg->saturday_delivery) $temp .= NL . '<SaturdayDeliveryIndicator>' . $pkg->saturday_delivery . '</SaturdayDeliveryIndicator>';
		if ($temp) $sBody .= NL . '<ShipmentServiceOptions>' . $temp . NL . '</ShipmentServiceOptions>';
		if ($pkg->handling_charge) {
			$sBody .= NL . '<HandlingCharge><FlatRate><CurrencyCode>' . $pkg->handling_charge_currency . '</CurrencyCode>';
			$sBody .= '<MonetaryValue>' . $pkg->handling_charge_value . '</MonetaryValue></FlatRate></HandlingCharge>';
		}
		$sBody .= NL . '<RateInformation>';
		$sBody .= NL . '<NegotiatedRatesIndicator>1</NegotiatedRatesIndicator>';
		$sBody .= NL . '</RateInformation>';
*/
//		$sBody .= NL . '</Shipment>';
		$sBody .= NL . '</FreightRateRequest>';
		$sBody .= NL;
		return $sBody;
	}
	
	// ***************************************************************************************************************
	//								UPS TIME IN TRANSIT REQUEST
	// ***************************************************************************************************************
	function FormatTnTRequest() {
		global $pkg;
		$sBody =       '<?xml version="1.0"?>';
		$sBody .= NL . '<AccessRequest xml:lang="en-US">';
		$sBody .= NL . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= NL . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= NL . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= NL . '</AccessRequest>';
		$sBody .= NL . '<?xml version="1.0"?>';
		$sBody .= NL . '<TimeInTransitRequest xml:lang="en-US">';
		$sBody .= NL . '<Request>';
		$sBody .= NL . '<TransactionReference>';
		$sBody .= NL . '<CustomerContext>Time in Transit Request</CustomerContext>';
		$sBody .= NL . '<XpciVersion>1.0002</XpciVersion>';
		$sBody .= NL . '</TransactionReference>';
		$sBody .= NL . '<RequestAction>' . 'TimeInTransit' . '</RequestAction>';	// pre-set to TimeInTransit
		$sBody .= NL . '</Request>';
		$sBody .= NL . '<TransitFrom>';
		$sBody .= NL . '<AddressArtifactFormat>';
		// PoliticalDivision2 required for outside US shipments
		if ($pkg->ship_city_town) $sBody .= NL . '<PoliticalDivision2>' . $pkg->ship_city_town . '</PoliticalDivision2>';
		if ($pkg->ship_state_province) $sBody .= NL . '<PoliticalDivision1>' . $pkg->ship_state_province . '</PoliticalDivision1>';
		if ($pkg->ship_postal_code) $sBody .= NL . '<PostcodePrimaryLow>' . $pkg->ship_postal_code . '</PostcodePrimaryLow>';
//		$country_name = gen_get_country_iso_2_from_3($pkg->ship_country_code);
		$sBody .= NL . '<CountryCode>' . $pkg->ship_from_country_iso2 . '</CountryCode>';
		$sBody .= NL . '</AddressArtifactFormat>';
		$sBody .= NL . '</TransitFrom>';
		$sBody .= NL . '<TransitTo>';
		$sBody .= NL . '<AddressArtifactFormat>';
		// PoliticalDivision2 required for outside US shipments
		if ($pkg->ship_to_city) $sBody .= NL . '<PoliticalDivision2>' . $pkg->ship_to_city . '</PoliticalDivision2>';
		if ($pkg->ship_to_state) $sBody .= NL.'<PoliticalDivision1>' . $pkg->ship_to_state . '</PoliticalDivision1>';
		if ($pkg->ship_to_postal_code) $sBody .= NL.'<PostcodePrimaryLow>' . $pkg->ship_to_postal_code . '</PostcodePrimaryLow>';
//		$country_name = gen_get_country_iso_2_from_3($pkg->ship_to_country_code);
		$sBody .= NL . '<CountryCode>' . $pkg->ship_to_country_iso2 . '</CountryCode>';
		if ($pkg->residential_address) $sBody .= NL . '<ResidentialAddressIndicator/>';
		$sBody .= NL . '</AddressArtifactFormat>';
		$sBody .= NL . '</TransitTo>';
		$sBody .= NL . '<PickupDate>' . date('Ymd', strtotime($pkg->ship_date)) . '</PickupDate>';
		$sBody .= NL . '</TimeInTransitRequest>';
		$sBody .= NL;
		return $sBody;
	}
	
	// ***************************************************************************************************************
	//								Parse function to retrieve UPS rates
	// ***************************************************************************************************************
	function getUPSrates($pkg) {
	  global $messageStack, $currencies, $UPSRateCodes, $shipping_defaults;
	  $user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_UPS_TYPES));  
	  $UPSQuote     = array();
	  $arrRates     = array();
	  $this->package = $pkg->split_shipment($pkg);
	  if (!$this->package) {
		$messageStack->add(SHIPPING_UPS_PACKAGE_ERROR . $pkg->pkg_weight, 'error');
		return false;
	  }
	  if ($shipping_defaults['TnTEnable'] && gen_get_country_iso_2_from_3($pkg->ship_to_country_code) == 'US') {
		// Use UPS time in transit to get shipment time
		$strXML = $this->FormatTnTRequest();
		$url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_TNT_URL_TEST : MODULE_SHIPPING_UPS_TNT_URL;
		$SubmitXML = GetXMLString($strXML, $url, "POST");
		// Check for XML request errors
		if ($SubmitXML['result'] == 'error') return $SubmitXML;
		$ResponseXML = xml_to_object($SubmitXML['xmlString']);
		// Check for errors
		if (!$ResponseXML->TimeInTransitResponse->Response->ResponseStatusCode) {
		  $XMLErrorType = $ResponseXML->TimeInTransitResponse->Response->Error->ErrorCode;
		  $XMLErrorDesc = $ResponseXML->TimeInTransitResponse->Response->Error->ErrorDescription;
		  $messageStack->add(SHIPPING_UPS_TNT_ERROR . $XMLErrorType . ' - ' . $XMLErrorDesc, 'error');
		  return false;
		}
		// See if service list returned or candidate city list is returned.
		$XMLService = $ResponseXML->TimeInTransitResponse->TransitResponse->ServiceSummary->Service->Code;
		if (!$XMLService) {	// fetch the candidate list city and matching postal codes in case bad zip provided
		  $XMLStart = 'TimeInTransitResponse:TransitResponse:TransitToList:Candidate';
		  $XMLIndexName = '';	// needs to be null to create single dimension array of cities
		  $TagsToFind = array('index' => 'AddressArtifactFormat:PoliticalDivision2');	// use 'index' to create non-associate array
		  $CityCodes['City'] = GetNodeArray($ResponseXML, $XMLStart, $XMLIndexName, $TagsToFind);
		  $TagsToFind = array('index' => 'AddressArtifactFormat:PostcodePrimaryLow');	// use 'index' to create non-associate array
		  $CityCodes['PostalCode'] = GetNodeArray($ResponseXML, $XMLStart, $XMLIndexName, $TagsToFind);
		  $UPSQuote['validcities'] = $CityCodes;
		  $UPSQuote['result'] = 'CityMatch';
		  return $UPSQuote;
		} else {	// fetch the service list
		  $XMLStart = 'TimeInTransitResponse:TransitResponse:ServiceSummary';	// base location in the XML string (repeated)
		  $XMLIndexName = 'Service:Code';	// name of the index in array
		  $TagsToFind = array();
		  $TagsToFind['DeliveryDOW']  = 'EstimatedArrival:DayOfWeek';	//index name and path from XMLStart to get data
		  $TagsToFind['DeliveryTime'] = 'EstimatedArrival:Time';
		  $TagsToFind['TransitDays']  = 'EstimatedArrival:BusinessTransitDays';
		  $Services = GetNodeArray($ResponseXML, $XMLStart, $XMLIndexName, $TagsToFind);
		  // Fetch the Ship to state to insert if left blank
		  $defaults['ShipToStateProv'] = $ResponseXML->TimeInTransitResponse->TransitResponse->TransitTo->AddressArtifactFormat->PoliticalDivision1;
		  // Fetch the Ship to City
		  $XMLPath = '';	// Get Ship To State
		  $CityCodes['City'][0] =  $ResponseXML->TimeInTransitResponse->TransitResponse->TransitTo->AddressArtifactFormat->PoliticalDivision2;
		  $defaults['City'] = $CityCodes['City'][0];
		  $CityCodes['PostalCode'][0] = '';
		}
		foreach ($this->UPSTnTCodes as $key => $value) {
		  if (isset($Services[$key]) && in_array($value, $user_choices)) {
			$arrRates[$this->code][$value]['notes'] = $Services[$key]['TransitDays'] . SHIPPING_UPS_RATE_TRANSIT . $Services[$key]['DeliveryDOW'];
		  }
		}
	  }
	  // *******************************************************************************************
	  // Fetch the book rates from UPS
	  $strXML = $this->FormatRateRequest();
//echo 'Ship Request xmlString = ' . htmlspecialchars($strXML) . '<br />';
	  $url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_RATE_URL_TEST : MODULE_SHIPPING_UPS_RATE_URL;
	  $SubmitXML = GetXMLString($strXML, $url, "POST");
//echo 'Ship Request response string = ' . htmlspecialchars($SubmitXML['xmlString']) . '<br />';
	  // Check for XML request errors
	  if ($SubmitXML['result'] == 'error') {
		$messageStack->add(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message'], 'error');
		return false;
	  }
	  $ResponseXML = xml_to_object($SubmitXML['xmlString']);
//echo 'Ship Request response object = '; print_r($ResponseXML); echo '<br />'; exit();
	  // Check for errors returned from UPS
	  if (!$ResponseXML->RatingServiceSelectionResponse->Response->ResponseStatusCode) {	// fetch the error code
		$XMLErrorType = $ResponseXML->RatingServiceSelectionResponse->Response->Error->ErrorCode;
		$XMLErrorDesc = $ResponseXML->RatingServiceSelectionResponse->Response->Error->ErrorDescription;
		$messageStack->add(SHIPPING_UPS_RATE_ERROR . $XMLErrorType . ' - ' . $XMLErrorDesc, 'error');
		return false;
	  }
	  // Fetch the UPS Rates
	  foreach ($ResponseXML->RatingServiceSelectionResponse->RatedShipment as $rate) {
	    $code = $rate->Service->Code;
		if (!$code) continue;
		$value    = $this->UPSRateCodes[$code];
		$del_time = $rate->GuaranteedDaysToDelivery . ' days by ' . $rate->ScheduledDeliveryTime;
		$book     = $rate->TotalCharges->MonetaryValue;
		$cost     = $rate->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
		if (in_array($value, $user_choices)) { 
		  if ($book <> "") $arrRates[$this->code][$value]['book'] = $currencies->clean_value($book);
		  if ($cost <> "") $arrRates[$this->code][$value]['cost'] = $currencies->clean_value($cost);
		  $arrRates[$this->code][$value]['note'] = '';
		  if ($rate->GuaranteedDaysToDelivery  <> "") $arrRates[$this->code][$value]['note'] .= $rate->GuaranteedDaysToDelivery;
		  $arrRates[$this->code][$value]['note'] .= ($rate->ScheduledDeliveryTime <> "") ? ' by ' . $rate->ScheduledDeliveryTime : ' by End of Day';
		  if (function_exists('ups_shipping_rate_calc')) {
			$arrRates[$this->code][$value]['quote'] = ups_shipping_rate_calc($arrRates[$this->code][$value]['book'], $arrRates[$this->code][$value]['cost'], $value);
		  } else {
			if ($book <> "") $arrRates[$this->code][$value]['quote'] = $book;
		  }
	    }
	  }
/* The LTL needs debugging, UPS doesn't support PHP and doesn't provide examples for their web services, gotta love them
	  // now check Ground LTL
      if ($pkg->pkg_weight >= 150) {
	    $client = new SoapClient(PATH_TO_LTL_RATE_WSDL, array('trace' => 1));
	    $strXML = $this->FormatLTLRateRequest($pkg);
	    try {
echo 'UPS LTL Freight XML Submit String:<pre>' . ($strXML) . '</pre><br />';
		  $response = $client->ProcessFreightRate($strXML);
echo 'Request <pre>'  . htmlspecialchars($client->__getLastRequest())  . '</pre>';
echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
echo 'Rate response array = '; print_r($response); echo '<br />';
	      if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
	      } else { // error returned
	      }
	    } catch (SoapFault $exception) {
echo 'Fault Request <pre>'  . htmlspecialchars($client->__getLastRequest()) . '</pre>';  
echo 'Fault Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	      $message = " [soap fault] ({$exception->faultcode}) {$exception->faultstring}";
	      $messageStack->add(SHIPPING_UPS_CURL_ERROR . $message, 'error');
	      return false;
	    }
//echo 'arrRates array = '; print_r($arrRates); echo '<br /><br />';
	  }
*/
	  $UPSQuote['result'] = 'success';
	  $UPSQuote['rates']  = $arrRates;
	  return $UPSQuote;
	}	// End UPS Rate Function

// ***************************************************************************************************************
//								UPS LABEL REQUEST (multipiece compatible) 
// ***************************************************************************************************************
  function retrieveLabel($sInfo, $key = 0) {
	global $messageStack;
	$ups_results = array();
	if (in_array($sInfo->ship_method, array('I2DEam','I2Dam','I3D','GndFrt','EcoFrt'))) { // unsupported ship methods
		$messageStack->add('The ship method requested is not supported by this tool presently. Please ship the package via a different tool.','error');
		return false;
	}
	$strXML = $this->FormatUPSShipRequest($sInfo);
//echo 'Ship Request xmlString = <pre>' . $strXML . '</pre><br />';
	$url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_SHIP_URL_TEST : MODULE_SHIPPING_UPS_SHIP_URL;
	$SubmitXML = GetXMLString($strXML, $url, "POST");
//echo 'Ship Request response string = ' . htmlspecialchars($SubmitXML['xmlString']) . '<br />';
	// Check for XML request errors
	if ($SubmitXML['result'] == 'error') {
		$messageStack->add(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message'], 'error');
		return false;
	}
	$ResponseXML = xml_to_object($SubmitXML['xmlString']);
	$XMLFail = $ResponseXML->ShipmentConfirmResponse->Response->Error->ErrorCode;
	$XMLWarn = $ResponseXML->ShipmentConfirmResponse->Response->Error->ErrorSeverity;
	if ($XMLFail && $XMLWarn == 'Warning') { // soft error, report it and continue
		$messageStack->add('UPS Label Request Warning # ' . $XMLFail . ' - ' . $ResponseXML->ShipmentConfirmResponse->Response->Error->ErrorDescription, 'caution');
	} elseif ($XMLFail && $XMLWarn <> 'Warning') { // hard error - return with bad news
		$messageStack->add('UPS Label Request Error # ' . $XMLFail . ' - ' . $ResponseXML->ShipmentConfirmResponse->Response->Error->ErrorDescription, 'error');
		return false;
	}

	$digest = $ResponseXML->ShipmentConfirmResponse->ShipmentDigest;
	// Now resend request with digest to get the label
	$strXML = $this->FormatUPSAcceptRequest($digest);
//echo 'Accept Request xmlString = ' . htmlspecialchars($strXML) . '<br />';
	$url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_LABEL_URL_TEST : MODULE_SHIPPING_UPS_LABEL_URL;
	$SubmitXML = GetXMLString($strXML, $url, "POST");
//echo 'Accept Response response string = ' . htmlspecialchars($SubmitXML['xmlString']) . '<br />';
	// Check for XML request errors
	if ($SubmitXML['result'] == 'error') {
		$messageStack->add(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message'], 'error');
		return false;
	}
	$ResponseXML = xml_to_object($SubmitXML['xmlString']);
	$XMLFail = $ResponseXML->ShipmentAcceptResponse->Response->Error->ErrorCode;
	$XMLWarn = $ResponseXML->ShipmentAcceptResponse->Response->Error->ErrorSeverity;
	if ($XMLFail && $XMLWarn == 'Warning') { // soft error, report it and continue
	  $messageStack->add('UPS Label Retrieval Warning # ' . $XMLFail . ' - ' . $ResponseXML->ShipmentAcceptResponse->Response->Error->ErrorDescription, 'caution');
	} elseif ($XMLFail && $XMLWarn <> 'Warning') { // hard error - return with bad news
	  $messageStack->add('UPS Label Retrieval Error # ' . $XMLFail . ' - ' . $ResponseXML->ShipmentAcceptResponse->Response->Error->ErrorDescription, 'error');
	  return false;
	}

	// Fetch the UPS shipment information information
	$ups_results = array(
//			'tracking_number' => $ResponseXML->ShipmentAcceptResponse->ShipmentResults->ShipmentIdentificationNumber,
		'ref_id'        => $sInfo->purchase_invoice_id . '-' . ($key + 1),
		'dim_weight'    => $ResponseXML->ShipmentAcceptResponse->ShipmentResults->BillingWeight->Weight,
		'zone'          => 'N/A',
		'billed_weight' => $ResponseXML->ShipmentAcceptResponse->ShipmentResults->BillingWeight->Weight,
		'net_cost'      => $ResponseXML->ShipmentAcceptResponse->ShipmentResults->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue,
		'book_cost'     => $ResponseXML->ShipmentAcceptResponse->ShipmentResults->ShipmentCharges->TotalCharges->MonetaryValue,
		'delivery_date' => 'Not Provided',
	);
	// Fetch the package information and label
	$returnArray = array();
	if (!$ResponseXML->ShipmentAcceptResponse->ShipmentResults->PackageResults) {
	  $messageStack->add('Error - No label found in return string.','error');
	  return false;				
	} else {
	  if (!is_array($ResponseXML->ShipmentAcceptResponse->ShipmentResults->PackageResults)) {
		$ResponseXML->ShipmentAcceptResponse->ShipmentResults->PackageResults = array($ResponseXML->ShipmentAcceptResponse->ShipmentResults->PackageResults);
	  }
	  foreach ($ResponseXML->ShipmentAcceptResponse->ShipmentResults->PackageResults as $label) {
		$returnArray[] = $ups_results + array('tracking' => $label->TrackingNumber);
		$date = explode('-', $sInfo->ship_date); // date format YYYY-MM-DD
		$file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/' . $this->code . '/' . $date[0] . '/' . $date[1] . '/' . $date[2] . '/';
		validate_path($file_path);
		$output_label = base64_decode($label->LabelImage->GraphicImage);
		$file_name = (MODULE_SHIPPING_UPS_PRINTER_TYPE == 'EPL') ? $label->TrackingNumber.'.lpt' : $label->TrackingNumber.'.gif';
		if (!$handle = fopen($file_path . $file_name, 'w')) { 
			$messageStack->add('Cannot open file (' . $file_path . $file_name . ')','error');
			return false;
		}
		if (fwrite($handle, $output_label) === false) {
			$messageStack->add('Cannot write to file (' . $file_path . $file_name . ')','error');
			return false;
		}
		fclose($handle);
	  }
	  $messageStack->add_session('Successfully retrieved the UPS shipping label. Tracking # ' . $ups_results[$key]['tracking'],'success');
	}
	return $returnArray;
  }

	function FormatUPSShipRequest($pkg) {
		$sBody =       '<?xml version="1.0"?>';
		$sBody .= NL . '<AccessRequest xml:lang="en-US">';
		$sBody .= NL . '  <AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= NL . '  <UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= NL . '  <Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= NL . '</AccessRequest>';

		$sBody .= NL . '<?xml version="1.0"?>';
		$sBody .= NL . '  <ShipmentConfirmRequest>';
		$sBody .= NL . '  <Request>';
		$sBody .= NL . '    <TransactionReference>';
		$sBody .= NL . '      <CustomerContext>Shipment Label Request</CustomerContext>';
		$sBody .= NL . '      <XpciVersion>1.0001</XpciVersion>';
		$sBody .= NL . '    </TransactionReference>';
		$sBody .= NL . '    <RequestAction>' . 'ShipConfirm' . '</RequestAction>'; // must be ShipConfirm for tool to work
		$sBody .= NL . '    <RequestOption>' . 'validate' . '</RequestOption>'; // 'validate' or 'nonvalidate' address
		$sBody .= NL . '  </Request>';
		$sBody .= NL . '  <Shipment>';
	
		$sBody .= NL . '    <Shipper>';
		$sBody .= NL . '      <Name>' . COMPANY_NAME . '</Name>';
		$sBody .= NL . '      <ShipperNumber>' . MODULE_SHIPPING_UPS_SHIPPER_NUMBER . '</ShipperNumber>';
		if (COMPANY_TELEPHONE1) $sBody .= NL . '<PhoneNumber>' . COMPANY_TELEPHONE1 . '</PhoneNumber>';
		if (COMPANY_FAX)      $sBody .= NL . '<FaxNumber>' . COMPANY_FAX . '</FaxNumber>';
		if (COMPANY_EMAIL)    $sBody .= NL . '<EMailAddress>' . COMPANY_EMAIL . '</EMailAddress>';
		$sBody .= NL . '<Address>';
		if (COMPANY_ADDRESS1) $sBody .= NL . '<AddressLine1>' . COMPANY_ADDRESS1 . '</AddressLine1>';
		if (COMPANY_ADDRESS2) $sBody .= NL . '<AddressLine2>' . COMPANY_ADDRESS2 . '</AddressLine2>';
//		if (COMPANY_ADDRESS3) $sBody .= NL . '<AddressLine3>' . COMPANY_ADDRESS3 . '</AddressLine3>'; // Not used in Current System
		if (COMPANY_CITY_TOWN) $sBody .= NL . '<City>' . COMPANY_CITY_TOWN . '</City>';
		if (COMPANY_ZONE) $sBody .= NL . '<StateProvinceCode>' . COMPANY_ZONE . '</StateProvinceCode>';
		if (COMPANY_POSTAL_CODE) $sBody .= NL . '<PostalCode>' . COMPANY_POSTAL_CODE . '</PostalCode>';
		$sBody .= NL . '<CountryCode>' . gen_get_country_iso_2_from_3(COMPANY_COUNTRY) . '</CountryCode>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</Shipper>';
	
		$sBody .= NL . '<ShipTo>';
		$sBody .= NL . '<CompanyName>' . $pkg->ship_primary_name . '</CompanyName>';
		if ($pkg->ship_contact) $sBody .= NL . '<AttentionName>' . $pkg->ship_contact . '</AttentionName>';
		if ($pkg->ship_telephone1) $sBody .= NL . '<PhoneNumber>' . $pkg->ship_telephone1 . '</PhoneNumber>';
		if ($pkg->fax) $sBody .= NL . '<FaxNumber>' . $pkg->fax . '</FaxNumber>';
		if ($pkg->ship_email) $sBody .= NL . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
		$sBody .= NL . '<Address>';
		if ($pkg->ship_address1) $sBody .= NL . '<AddressLine1>' . $pkg->ship_address1 . '</AddressLine1>';
		if ($pkg->ship_address2) $sBody .= NL . '<AddressLine2>' . $pkg->ship_address2 . '</AddressLine2>';
//		if ($pkg->ship_address3) $sBody .= NL . '<AddressLine3>' . $pkg->ship_address3 . '</AddressLine3>'; // Not used
		if ($pkg->ship_city_town) $sBody .= NL . '<City>' . $pkg->ship_city_town . '</City>';
		if ($pkg->ship_state_province) $sBody .= NL . '<StateProvinceCode>' . strtoupper($pkg->ship_state_province) . '</StateProvinceCode>';
		if ($pkg->ship_postal_code) $sBody .= NL . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
		$sBody .= NL . '<CountryCode>' . $pkg->ship_country_code . '</CountryCode>';
		if ($pkg->residential_address) $sBody .= NL . '<ResidentialAddress />';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</ShipTo>';

/* TBD assume ship from is the same as shipper
		$sBody .= NL . '<ShipFrom>';
		$sBody .= NL . '<CompanyName>' . COMPANY_NAME . '</CompanyName>';
		if (COMPANY_TELEPHONE1) $sBody .= NL . '<PhoneNumber>' . COMPANY_TELEPHONE1 . '</PhoneNumber>';
		if (COMPANY_FAX) $sBody .= NL . '<FaxNumber>' . COMPANY_FAX . '</FaxNumber>';
		$sBody .= NL . '<Address>';
		if (COMPANY_ADDRESS1) $sBody .= NL . '<AddressLine1>' . COMPANY_ADDRESS1 . '</AddressLine1>';
		if (COMPANY_ADDRESS2) $sBody .= NL . '<AddressLine2>' . COMPANY_ADDRESS2 . '</AddressLine2>';
//		if (COMPANY_ADDRESS3) $sBody .= NL . '<AddressLine3>' . TBD . '</AddressLine3>'; // Not used in Current System
		if (COMPANY_CITY_TOWN) $sBody .= NL . '<City>' . COMPANY_CITY_TOWN . '</City>';
		if (COMPANY_ZONE) $sBody .= NL . '<StateProvinceCode>' . COMPANY_ZONE . '</StateProvinceCode>';
		if (COMPANY_POSTAL_CODE) $sBody .= NL . '<PostalCode>' . COMPANY_POSTAL_CODE . '</PostalCode>';
		$sBody .= NL . '<CountryCode>' . gen_get_country_iso_2_from_3(COMPANY_COUNTRY) . '</CountryCode>';
		$sBody .= NL . '</Address>';
		$sBody .= NL . '</ShipFrom>';
*/
		$sBody .= NL . '<Service>';
		$temp = array_flip($this->UPSRateCodes);
		$sBody .= NL . '<Code>' . $temp[$pkg->ship_method] . '</Code>';
		$sBody .= NL . '</Service>';

		$sBody .= NL . '<PaymentInformation>';
		switch ($pkg->bill_charges) {
			default:
			case '0': // bill sender
				$sBody .= NL . '<Prepaid>';
				$sBody .= NL . '<BillShipper>';
				$sBody .= NL . '<AccountNumber>' . MODULE_SHIPPING_UPS_SHIPPER_NUMBER . '</AccountNumber>'; // only bill account (no credit card)
				$sBody .= NL . '</BillShipper>';
				$sBody .= NL . '</Prepaid>';
				break;
			case '1': // bill recepient
				$sBody .= NL . '<FreightCollect>';
				$sBody .= NL . '<BillReceiver>';
				$sBody .= NL . '<AccountNumber>' . $pkg->bill_acct . '</AccountNumber>'; // only bill accounts (no addresses)
				$sBody .= NL . '<Address>';
				$sBody .= NL . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
				$sBody .= NL . '</Address>';
				$sBody .= NL . '</BillReceiver>';
				$sBody .= NL . '</FreightCollect>';
				break;
			case '2': // bill third party
				$sBody .= NL . '<BillThirdParty>';
				$sBody .= NL . '<BillThirdPartyShipper>';
				$sBody .= NL . '<AccountNumber>' . $pkg->bill_acct . '</AccountNumber>'; // only bill accounts (no addresses)
				$sBody .= NL . '<ThirdParty>';
				$sBody .= NL . '<Address>';
				$sBody .= NL . '<PostalCode>' . $pkg->third_party_zip . '</PostalCode>';
				$sBody .= NL . '<CountryCode>' . $pkg->ship_country_code . '</CountryCode>';
				$sBody .= NL . '</Address>';
				$sBody .= NL . '</ThirdParty>';
				$sBody .= NL . '</BillThirdPartyShipper>';
				$sBody .= NL . '</BillThirdParty>';
				break;
			case '3': // COD - NOT allowed for UPS
				return false;
		}
		$sBody .= NL . '</PaymentInformation>';

		$sBody .= NL . '<RateInformation>';
		$sBody .= NL . '<NegotiatedRatesIndicator />';
		$sBody .= NL . '</RateInformation>';

		$sBody .= NL . '<ShipmentServiceOptions>';
		if ($pkg->saturday_delivery) $sBody .= NL . '<SaturdayDelivery></SaturdayDelivery>';
		if ($pkg->email_sndr_ship || $pkg->email_sndr_excp || $pkg->email_sndr_dlvr || $pkg->email_rcp_ship || $pkg->email_rcp_excp || $pkg->email_rcp_dlvr) {
			if ($pkg->email_sndr_ship || $pkg->email_sndr_excp || $pkg->email_sndr_dlvr) {
				if ($pkg->email_sndr_ship) {
					$sBody .= NL . '<ShipmentNotification>';
					$sBody .= NL . '<NotificationCode>6</NotificationCode>';
					$sBody .= NL . '<EMailMessage>';
					$sBody .= NL . '<EMailAddress>' . $pkg->sender_email_address . '</EMailAddress>';
					$sBody .= NL . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= NL . '<SubjectCode>01</SubjectCode>';
					$sBody .= NL . '</EMailMessage>';
					$sBody .= NL . '</ShipmentNotification>';
				}
				if ($pkg->email_sndr_excp) {
					$sBody .= NL . '<ShipmentNotification>';
					$sBody .= NL . '<NotificationCode>7</NotificationCode>';
					$sBody .= NL . '<EMailMessage>';
					$sBody .= NL . '<EMailAddress>' . $pkg->sender_email_address . '</EMailAddress>';
					$sBody .= NL . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= NL . '<SubjectCode>01</SubjectCode>';
					$sBody .= NL . '</EMailMessage>';
					$sBody .= NL . '</ShipmentNotification>';
				}
				if ($pkg->email_sndr_dlvr) {
					$sBody .= NL . '<ShipmentNotification>';
					$sBody .= NL . '<NotificationCode>8</NotificationCode>';
					$sBody .= NL . '<EMailMessage>';
					$sBody .= NL . '<EMailAddress>' . $pkg->sender_email_address . '</EMailAddress>';
					$sBody .= NL . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= NL . '<SubjectCode>01</SubjectCode>';
					$sBody .= NL . '</EMailMessage>';
					$sBody .= NL . '</ShipmentNotification>';
				}
			}
			if ($pkg->email_rcp_ship || $pkg->email_rcp_excp || $pkg->email_rcp_dlvr) {
				if ($pkg->email_rcp_ship) {
					$sBody .= NL . '<ShipmentNotification>';
					$sBody .= NL . '<NotificationCode>6</NotificationCode>';
					$sBody .= NL . '<EMailMessage>';
					$sBody .= NL . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
					$sBody .= NL . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= NL . '<SubjectCode>01</SubjectCode>';
					$sBody .= NL . '</EMailMessage>';
					$sBody .= NL . '</ShipmentNotification>';
				}
				if ($pkg->email_rcp_excp) {
					$sBody .= NL . '<ShipmentNotification>';
					$sBody .= NL . '<NotificationCode>7</NotificationCode>';
					$sBody .= NL . '<EMailMessage>';
					$sBody .= NL . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
					$sBody .= NL . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= NL . '<SubjectCode>01</SubjectCode>';
					$sBody .= NL . '</EMailMessage>';
					$sBody .= NL . '</ShipmentNotification>';
				}
				if ($pkg->email_rcp_dlvr) {
					$sBody .= NL . '<ShipmentNotification>';
					$sBody .= NL . '<NotificationCode>8</NotificationCode>';
					$sBody .= NL . '<EMailMessage>';
					$sBody .= NL . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
					$sBody .= NL . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= NL . '<SubjectCode>01</SubjectCode>';
					$sBody .= NL . '</EMailMessage>';
					$sBody .= NL . '</ShipmentNotification>';
				}
			}
		}
		$sBody .= NL . '</ShipmentServiceOptions>';

		foreach ($pkg->package as $pkgnum) { // Enter each package 
			$sBody .= NL . '<Package>';
			$sBody .= NL . '<PackagingType><Code>' . $pkg->pkg_type . '</Code></PackagingType>';
			$sBody .= NL . '<Dimensions>';

			$sBody .= NL . '<UnitOfMeasurement><Code>' . $pkg->pkg_dimension_unit . '</Code></UnitOfMeasurement>';
			$sBody .= NL . '<Length>' . ceil($pkgnum['length']) . '</Length>';
			$sBody .= NL . '<Width>' . ceil($pkgnum['width']) . '</Width>';
			$sBody .= NL . '<Height>' . ceil($pkgnum['height']) . '</Height>';
			$sBody .= NL . '</Dimensions>';
			$sBody .= NL . '<PackageWeight>';
			$sBody .= NL . '<UnitOfMeasurement><Code>' . $pkg->pkg_weight_unit . '</Code></UnitOfMeasurement>';
			$sBody .= NL . '<Weight>' . $pkgnum['weight'] . '</Weight>';
			$sBody .= NL . '</PackageWeight>';

			$sBody .= NL . '<ReferenceNumber>';
			$sBody .= NL . '<Code>PO</Code>'; // Purchase Order #
			$sBody .= NL . '<Value>' . $pkg->so_po_ref_id . '</Value>';
			$sBody .= NL . '</ReferenceNumber>';
			$sBody .= NL . '<ReferenceNumber>';
			$sBody .= NL . '<Code>IK</Code>'; // Invoice #
			$sBody .= NL . '<Value>' . $pkg->purchase_invoice_id . '</Value>';
			$sBody .= NL . '</ReferenceNumber>';

			if ($pkg->additional_handling) $sBody .= NL . '<AdditionalHandling></AdditionalHandling>';

			$temp = '';
			if ($pkg->delivery_confirmation) {
				$temp .= NL . '<DeliveryConfirmation>';
				$temp .= NL . '<DCISType>' . $pkg->delivery_confirmation_type . '</DCISType>';
				$temp .= NL . '</DeliveryConfirmation>';
			}
			if ($pkg->insurance) {
				$temp .= NL . '<InsuredValue>';
				$temp .= NL . '<CurrencyCode>' . $pkg->insurance_currency . '</CurrencyCode>';
				$temp .= NL . '<MonetaryValue>' . $pkgnum['value'] . '</MonetaryValue>';
				$temp .= NL . '</InsuredValue>';
			}
			if ($pkg->cod) {
				$temp .= NL . '<COD>';
				$temp .= NL . '<CODCode>3</CODCode>';
				if ($pkg->cod_payment_type == 1 || $pkg->cod_payment_type == 2 || $pkg->cod_payment_type == 3) {
					$payment_type = '9'; // check, money order, cashier's check
				} else {
					$payment_type = '1'; // cash
				}
				$temp .= '<CODFundsCode>' . $payment_type . '</CODFundsCode>';
				$temp .= '<CODAmount>';
				$temp .= '<CurrencyCode>' . $pkg->cod_currency . '</CurrencyCode>';
				$temp .= '<MonetaryValue>' . $pkg->total_amount . '</MonetaryValue>';
				$temp .= '</CODAmount>';
				$temp .= '</COD>';
			}
/* VerbalConfirmation */
/* ShipperReleaseindicator */
			if ($temp) $sBody .= NL . '<PackageServiceOptions>' . NL. $temp . NL . '</PackageServiceOptions>';
			$sBody .= NL . '</Package>';
		}
		$sBody .= NL . '</Shipment>';

		$sBody .= NL . '<LabelSpecification>';
		$sBody .= NL . '<LabelPrintMethod><Code>' . ((MODULE_SHIPPING_UPS_PRINTER_TYPE == 'GIF') ? 'GIF' : 'EPL') . '</Code></LabelPrintMethod>'; // valid values are GIF, EPL, SPL
		$sBody .= NL . '<HTTPUserAgent>' . 'Mozilla/4.5' . '</HTTPUserAgent>'; // Default Value
		if (MODULE_SHIPPING_UPS_PRINTER_TYPE <> 'GIF') {
			$sBody .= NL . '<LabelStockSize>';
			$sBody .= NL . '<UnitOfMeasurement>IN</UnitOfMeasurement>';
			$sBody .= NL . '<Width>' . MODULE_SHIPPING_UPS_LABEL_SIZE . '</Width>'; // valid values are 6 and 8
			$sBody .= NL . '<Height>4</Height>'; // must be 4
			$sBody .= NL . '</LabelStockSize>'; // valid values are 4x6 and 4x8
		}
		$sBody .= NL . '<LabelImageFormat><Code>' . ((MODULE_SHIPPING_UPS_PRINTER_TYPE == 'GIF') ? 'GIF' : 'EPL2') . '</Code></LabelImageFormat>';
		$sBody .= NL . '</LabelSpecification>';

		$sBody .= NL . '</ShipmentConfirmRequest>';
		$sBody .= NL;
		return $sBody;
	}

	function FormatUPSAcceptRequest($digest) {
		$sBody = '<?xml version="1.0"?>';
		$sBody .= NL . '<AccessRequest xml:lang="en-US">';
		$sBody .= NL . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= NL . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= NL . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= NL . '</AccessRequest>';

		$sBody .= NL . '<?xml version="1.0"?>';
		$sBody .= NL . '  <ShipmentAcceptRequest>';
		$sBody .= NL . '    <Request>';
		$sBody .= NL . '      <TransactionReference>';
		$sBody .= NL . '        <CustomerContext>Shipment Label Accept</CustomerContext>';
		$sBody .= NL . '        <XpciVersion>1.0001</XpciVersion>';
		$sBody .= NL . '      </TransactionReference>';
		$sBody .= NL . '      <RequestAction>' . 'ShipAccept' . '</RequestAction>'; // must be ShipAccept for tool to work
		$sBody .= NL . '    </Request>';
		$sBody .= NL . '    <ShipmentDigest>' . $digest . '</ShipmentDigest>';
		$sBody .= NL . '  </ShipmentAcceptRequest>';
		return $sBody;
	}
// ***************************************************************************************************************
//								UPS DELETE LABEL REQUEST
// ***************************************************************************************************************
	function deleteLabel($method = '', $shipment_id = '') {
	  global $db, $messageStack;
	  if (!$shipment_id) {
		$messageStack->add('Cannot delete shipment, shipment ID was not provided!','error');
		return false;
	  }
	  $shipments = $db->Execute("select ship_date, tracking_id from " . TABLE_SHIPPING_LOG . " where shipment_id = " . $shipment_id);
	  $tracking_number = $shipments->fields['tracking_id'];
	  $strXML = $this->FormatUPSDeleteRequest($tracking_number);
	  $url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_VOID_SHIPMENT_TEST : MODULE_SHIPPING_UPS_VOID_SHIPMENT;
	  $SubmitXML = GetXMLString($strXML, $url, "POST");
	  // Check for XML request errors
	  if ($SubmitXML['result'] == 'error') {
		$messageStack->add(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message'], 'error');
		return false;
	  }
	  $ResponseXML = xml_to_object($SubmitXML['xmlString']);
	  $XMLFail = $ResponseXML->VoidShipmentResponse->Response->Error->ErrorCode;
	  $XMLWarn = $ResponseXML->VoidShipmentResponse->Response->Error->ErrorSeverity;
	  if ($XMLFail && $XMLWarn == 'Warning') { // soft error, report it and continue
		$messageStack->add('Label Delete Warning # ' . $XMLFail . ' - ' . $ResponseXML->VoidShipmentResponse->Response->Error->ErrorDescription, 'caution');
	  } elseif ($XMLFail && $XMLWarn <> 'Warning') { // hard error - return with bad news
		$messageStack->add('Label Delete Error # ' . $XMLFail . ' - ' . $ResponseXML->VoidShipmentResponse->Response->Error->ErrorDescription, 'error');
		return false;
	  }
	  // delete the label file
	  $date = explode('-', $shipments->fields['ship_date']);
	  $file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/' . $this->code . '/' . $date[0] . '/' . $date[1] . '/' . $date[2] . '/';
	  if (file_exists($file_path . $shipments->fields['tracking_id'] . '.lpt')) {
		$file_name = $shipments->fields['tracking_id'] . '.lpt';
	  } elseif (file_exists($file_path . $shipments->fields['tracking_id'] . '.gif')) {
		$file_name = $shipments->fields['tracking_id'] . '.gif';
	  } else {
		$file_name = false; // file does not exist, skip
	  }
	  if ($file_name) if (!unlink($file_path . $file_name)) {
		$messageStack->add_session('Trouble deleting label file (' . $file_path . $file_name . ')', 'caution');
	  }
	  // if we are here the delete was successful, the lack of an error indicates success
	  $messageStack->add_session('Successfully deleted the shipping label. Tracking # ' . $tracking_number, 'success');
	  return true;
	}

  function FormatUPSDeleteRequest($tracking_number) {
	$sBody =       '<?xml version="1.0"?>';
	$sBody .= NL . '<AccessRequest xml:lang="en-US">';
	$sBody .= NL . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
	$sBody .= NL . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
	$sBody .= NL . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
	$sBody .= NL . '</AccessRequest>';
	$sBody .= NL . '<?xml version="1.0"?>';
	$sBody .= NL . '<VoidShipmentRequest>';
	$sBody .= NL . '<Request>';
	$sBody .= NL . '<TransactionReference>';
	$sBody .= NL . '<CustomerContext>Shipment Label Delete</CustomerContext>';
	$sBody .= NL . '<XpciVersion>1.0001</XpciVersion>';
	$sBody .= NL . '</TransactionReference>';
	$sBody .= NL . '<RequestAction>' . 'Void' . '</RequestAction>'; // must be ShipAccept for tool to work
	$sBody .= NL . '</Request>';
	$sBody .= NL . '<ExpandedVoidShipment>';
	$sBody .= NL . '<ShipmentIdentificationNumber>' . $tracking_number . '</ShipmentIdentificationNumber>';
	$sBody .= NL . '</ExpandedVoidShipment>';
	$sBody .= NL . '</VoidShipmentRequest>';
	return $sBody;
  }

}
?>