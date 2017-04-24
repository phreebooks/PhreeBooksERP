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
//  Path: /modules/shipping/methods/ups/ups.php
//
namespace shipping\methods\ups;
class ups extends \shipping\classes\shipping {
	public $id				= 'ups'; // needs to match class name
  	public $text			= MODULE_SHIPPING_UPS_TEXT_TITLE;
  	public $description		= MODULE_SHIPPING_UPS_TEXT_DESCRIPTION;
  	public $sort_order		= 5;
  	public $version			= '3.2';
  	public $shipping_cost;
  	public $handling_cost;
	// UPS Time in Transit code map (US Only)
	public $UPSTnTCodes = array(
	  '1DM'=>'1DEam',
	  '1DA'=>'1Dam',
	  '1DP'=>'1Dpm',
	  '2DA'=>'2Dpm',
	  '3DS'=>'3Dpm',
	  'GND'=>'GND',
	  '01'=>'I2Dam',
	  '05'=>'I3D',
	  '03'=>'IGND');

// UPS Rate code map (US Origin)
	public $UPSRateCodes = array(
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
	  '11'=>'IGND');

/*
// For Canada Origin
	public $UPSRateCodes = array(
	  '01'=>'1Dam',
	  '02'=>'2Dpm',
	  '07'=>'I2Dam',
	  '08'=>'I3D',
	  '11'=>'IGND',
	  '12'=>'3Dpm',
	  '13'=>'1Dpm',
	  '14'=>'1DEam',
	  '54'=>'I2DEam');

// For EU Origin
	public $UPSRateCodes = array(
	  '07'=>'I2Dam',
	  '11'=>'IGND',
	  '54'=>'I2DEam',
	  '65'=>'I3D');
// See UPS Service Code specification for Puerto Rico, Mexico, and all other origins
*/

	function __construct() {
    	$this->rate_url = MODULE_SHIPPING_FEDEX_V7_TEST_MODE == 'Test' ? FEDEX_V7_EXPRESS_TEST_RATE_URL : FEDEX_V7_EXPRESS_RATE_URL;
    	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_SHIPPER_NUMBER',		'default' => '',										'text' => MODULE_SHIPPING_UPS_SHIPPER_NUMBER_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_USER_ID',			'default' => '',										'text' => MODULE_SHIPPING_UPS_USER_ID_DESC);
      	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_PASSWORD',   		'default' => '',										'text' => MODULE_SHIPPING_UPS_PASSWORD_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_ACCESS_KEY',  		'default' => '',										'text' => MODULE_SHIPPING_UPS_ACCESS_KEY_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_LABEL_SIZE',  		'default' => 6,											'text' => MODULE_SHIPPING_UPS_LABEL_SIZE_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_TEST_MODE',     		'default' => 'Test',									'text' => TEXT_PRODUCTION_OR_TEST_MODE_USED_FOR_TESTING_SHIPPING_LABELS);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_PRINTER_TYPE',  		'default' => 'GIF',										'text' => MODULE_SHIPPING_UPS_PRINTER_TYPE_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_UPS_TYPES',         		'default' => '1DEam, 1Dam, 1Dpm, 2Dam, 2Dpm, 3Dpm, GND, I2DEam, I2Dam, I3D, IGND',	'text' => TEXT_SELECT_THE_SERVICES_TO_BE_OFFERED_BY_DEFAULT);
	  	parent::__construct();
  	}

  function quote($pkg) {
	global $messageStack;
	if ($pkg->pkg_weight == 0) throw new \core\classes\userException(TEXT_SHIPMENT_WEIGHT_CANNOT_BE_ZERO);
	if (!$pkg->split_large_shipments && $pkg->pkg_weight > 150) throw new \core\classes\userException(SHIPPING_UPS_ERROR_WEIGHT_150);
	if ($pkg->ship_to_postal_code == '') throw new \core\classes\userException(SHIPPING_UPS_ERROR_POSTAL_CODE);
	$status = $this->getUPSrates($pkg);
	if ($status['result'] == 'error') {
		throw new \core\classes\userException(SHIPPING_UPS_RATE_ERROR . $status['message']);
	} elseif ($status['result'] == 'CityMatch') {
	  	throw new \core\classes\userException(SHIPPING_UPS_RATE_CITY_MATCH);
	}
	return $status;
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
		  array('id' => 'GIF',     'text' => TEXT_GIF),
		  array('id' => 'Thermal', 'text' => TEXT_THERMAL),
		);
		$html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
		break;
	  case 'MODULE_SHIPPING_UPS_LABEL_SIZE':
		$temp = array(
		  array('id' => '6', 'text' => '6'),
		  array('id' => '8', 'text' => '8'),
		);
		$html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
		break;
	  case 'MODULE_SHIPPING_UPS_TYPES':
		$temp = array(
		  array('id' => '1DEam', 'text' => MODULE_SHIPPING_UPS_1DM),
		  array('id' => '1Dam',  'text' => MODULE_SHIPPING_UPS_1DA),
		  array('id' => '1Dpm',  'text' => MODULE_SHIPPING_UPS_1DP),
		  array('id' => '2Dam',  'text' => MODULE_SHIPPING_UPS_2DM),
		  array('id' => '2Dpm',  'text' => MODULE_SHIPPING_UPS_2DP),
		  array('id' => '3Dpm',  'text' => MODULE_SHIPPING_UPS_3DS),
		  array('id' => 'GND',   'text' => MODULE_SHIPPING_UPS_GND),
		  array('id' => 'IGND',  'text' => MODULE_SHIPPING_UPS_STD),
		  array('id' => 'I2DEam','text' => MODULE_SHIPPING_UPS_XDM),
		  array('id' => 'I2Dam', 'text' => MODULE_SHIPPING_UPS_XPR),
		  array('id' => 'I3D',   'text' => MODULE_SHIPPING_UPS_XPD),
		);
		$choices = array();
		foreach ($temp as $value) {
		  $choices[] = html_checkbox_field(strtolower($key) . '[]', $value['id'], ((strpos(constant($key), $value['id']) === false) ? false : true), '', $parameters = '') . ' ' . $value['text'];
		}
		$html = implode('<br />', $choices);
		break;
	  default:
		$html .= parent::configure($key);
	}
	return $html;
  }

  function update() {
    foreach ($this->keys as $key) {
	  $field = strtolower($key['key']);
	  switch ($key['key']) {
	    case 'MODULE_SHIPPING_UPS_TYPES': // read the checkboxes
		  $admin->DataBase->write_configure($key['key'], implode(',', $_POST[$field]));
		  break;
		default:  // just write the value
		  if (isset($_POST[$field])) $admin->DataBase->write_configure($key['key'], $_POST[$field]);
	  }
	}
  }


// ***************************************************************************************************************
//								UPS RATE AND SERVICE REQUEST
// ***************************************************************************************************************
	function FormatRateRequest() {
		global $pkg;
		$crlf = chr(13) . chr(10);

		$sBody = '<?xml version="1.0"?>';
		$sBody .= $crlf . '<AccessRequest xml:lang="en-US">';
		$sBody .= $crlf . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= $crlf . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= $crlf . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= $crlf . '</AccessRequest>';
		$sBody .= $crlf . '<?xml version="1.0"?>';
		$sBody .= $crlf . '<RatingServiceSelectionRequest xml:lang="en-US">';
		$sBody .= $crlf . '<Request>';
		$sBody .= $crlf . '<TransactionReference>';
		$sBody .= $crlf . '<CustomerContext>Rating and Service</CustomerContext>';
		$sBody .= $crlf . '<XpciVersion>1.0001</XpciVersion>';
		$sBody .= $crlf . '</TransactionReference>';
		$sBody .= $crlf . '<RequestAction>' . 'rate' . '</RequestAction>'; // must be rate for tool to work
		$sBody .= $crlf . '<RequestOption>' . 'shop' . '</RequestOption>'; // must be shop to
		$sBody .= $crlf . '</Request>';
		$sBody .= $crlf . '<PickupType><Code>' . $pkg->pickup_service . '</Code></PickupType>';
		$sBody .= $crlf . '<CustomerClassification><Code>' . '01' . '</Code></CustomerClassification>'; // wholesale (default for PickupType 01)
		$sBody .= $crlf . '<Shipment>';
		$sBody .= $crlf . '<Shipper>';
		$sBody .= $crlf . '<ShipperNumber>' . MODULE_SHIPPING_UPS_SHIPPER_NUMBER . '</ShipperNumber>';
		$sBody .= $crlf . '<Address>';
		if (COMPANY_CITY_TOWN) $sBody .= $crlf . '<City>' . COMPANY_CITY_TOWN . '</City>';
		if (COMPANY_ZONE) $sBody .= $crlf . '<StateProvinceCode>' . COMPANY_ZONE . '</StateProvinceCode>';
		if (COMPANY_POSTAL_CODE) $sBody .= $crlf . '<PostalCode>' . COMPANY_POSTAL_CODE . '</PostalCode>';
//		$country_name = $_SESSION['language']->get_country_iso_2_from_3(COMPANY_COUNTRY);
		$sBody .= $crlf . '<CountryCode>' . $_SESSION['language']->get_country_iso_2_from_3(COMPANY_COUNTRY) . '</CountryCode>';
		$sBody .= $crlf . '</Address>';
		$sBody .= $crlf . '</Shipper>';
		$sBody .= $crlf . '<ShipTo>';
		$sBody .= $crlf . '<Address>';
		if ($pkg->ship_to_city) $sBody .= $crlf.'<City>' . $pkg->ship_to_city . '</City>';
		if ($pkg->ship_to_state) $sBody .= $crlf.'<StateProvinceCode>' . strtoupper($pkg->ship_to_state) . '</StateProvinceCode>';
		if ($pkg->ship_to_postal_code) $sBody .= $crlf . '<PostalCode>' . $pkg->ship_to_postal_code . '</PostalCode>';
//		$country_name = $_SESSION['language']->get_country_iso_2_from_3($pkg->ship_to_country_code);
		$sBody .= $crlf . '<CountryCode>' . $pkg->ship_to_country_iso2 . '</CountryCode>';
		if ($pkg->residential_address) $sBody .= $crlf . '<ResidentialAddress></ResidentialAddress>';
		$sBody .= $crlf . '</Address>';
		$sBody .= $crlf . '</ShipTo>';
		$sBody .= $crlf . '<ShipFrom>';
		$sBody .= $crlf . '<Address>';
		if ($pkg->ship_city_town) $sBody .= $crlf . '<City>' . $pkg->ship_city_town . '</City>';
		if ($pkg->ship_state_province) $sBody .= $crlf . '<StateProvinceCode>' . strtoupper($pkg->ship_state_province) . '</StateProvinceCode>';
		if ($pkg->ship_postal_code) $sBody .= $crlf . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
//		$country_name = $_SESSION['language']->get_country_iso_2_from_3($pkg->ship_country_code);
		$sBody .= $crlf . '<CountryCode>' . $pkg->ship_from_country_iso2 . '</CountryCode>';
		$sBody .= $crlf . '</Address>';
		$sBody .= $crlf . '</ShipFrom>';
		$sBody .= $crlf . '<ShipmentWeight>';
		$sBody .= $crlf . '<UnitOfMeasurement><Code>' . $pkg->pkg_weight_unit . '</Code></UnitOfMeasurement>';
		$ShipmentWeight = 0;
		foreach ($this->package as $pkgnum) $ShipmentWeight += $pkgnum['weight'];
		$sBody .= $crlf . '<Weight>' . $ShipmentWeight . '</Weight>';
		$sBody .= $crlf . '</ShipmentWeight>';
		foreach ($this->package as $pkgnum) { // Enter each package
			$sBody .= $crlf . '<Package>';
			$sBody .= $crlf . '<PackagingType><Code>'.$pkgnum['PackageTypeCode'].'</Code></PackagingType>';
			$sBody .= $crlf . '<Dimensions>';
			$sBody .= $crlf . '<UnitOfMeasurement><Code>'.$pkgnum['DimensionUnit'].'</Code></UnitOfMeasurement>';
			$sBody .= $crlf . '<Length>'.$pkgnum['Length'].'</Length>';
			$sBody .= $crlf . '<Width>'.$pkgnum['Width'].'</Width>';
			$sBody .= $crlf . '<Height>'.$pkgnum['Height'].'</Height>';
			$sBody .= $crlf . '</Dimensions>';
			$sBody .= $crlf . '<PackageWeight>';
			$sBody .= $crlf . '<UnitOfMeasurement><Code>'.$pkgnum['WeightUnit'].'</Code></UnitOfMeasurement>';
			$sBody .= $crlf . '<Weight>'.$pkgnum['Weight'].'</Weight>';
			$sBody .= $crlf . '</PackageWeight>';
			$temp = '';
			if ($pkg->delivery_confirmation) {
				$temp .= $crlf . '<DeliveryConfirmation>';
				$temp .= $crlf . '<DCISType>' . $pkg->delivery_confirmation_type . '</DCISType>';
				$temp .= $crlf . '</DeliveryConfirmation>';
			}
			if (gen_not_null($pkgnum['InsuranceCurrencyCode'])) {
				$temp .= $crlf . '<InsuredValue>';
				$temp .= $crlf . '<CurrencyCode>'.$pkgnum['InsuranceCurrencyCode'].'</CurrencyCode>';
				$temp .= $crlf . '<MonetaryValue>'.$pkgnum['InsuranceValue'].'</MonetaryValue>';
				$temp .= $crlf . '</InsuredValue>';
			}
			if ($temp) $sBody .= $crlf . '<PackageServiceOptions>' . $temp . $crlf . '</PackageServiceOptions>';
			if ($pkgnum['AdditionalHandling']) $sBody .= $crlf . '<AdditionalHandling></AdditionalHandling>';
			$sBody .= $crlf . '</Package>';
		}
		$temp = '';
		if ($pkg->saturday_pickup) $temp .= $crlf . '<SaturdayPickupIndicator>' . $pkg->saturday_pickup . '</SaturdayPickupIndicator>';
		if ($pkg->saturday_delivery) $temp .= $crlf . '<SaturdayDeliveryIndicator>' . $pkg->saturday_delivery . '</SaturdayDeliveryIndicator>';
		if ($pkg->cod) {
			$temp .= $crlf . '<COD><CODCode>3</CODCode>';
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
		if ($temp) $sBody .= $crlf . '<ShipmentServiceOptions>' . $temp . $crlf . '</ShipmentServiceOptions>';
		if ($pkg->handling_charge) {
			$sBody .= $crlf . '<HandlingCharge><FlatRate><CurrencyCode>' . $pkg->handling_charge_currency . '</CurrencyCode>';
			$sBody .= '<MonetaryValue>' . $pkg->handling_charge_value . '</MonetaryValue></FlatRate></HandlingCharge>';
		}
		$sBody .= $crlf . '<RateInformation>';
		$sBody .= $crlf . '<NegotiatedRatesIndicator>1</NegotiatedRatesIndicator>';
		$sBody .= $crlf . '</RateInformation>';
		$sBody .= $crlf . '</Shipment>';
		$sBody .= $crlf . '</RatingServiceSelectionRequest>';
		$sBody .= $crlf;
		return $sBody;
	}

	// ***************************************************************************************************************
	//								UPS TIME IN TRANSIT REQUEST
	// ***************************************************************************************************************
	function FormatTnTRequest() {
		global $pkg;
		$crlf = chr(13).chr(10);

		$sBody = '<?xml version="1.0"?>';
		$sBody .= $crlf . '<AccessRequest xml:lang="en-US">';
		$sBody .= $crlf . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= $crlf . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= $crlf . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= $crlf . '</AccessRequest>';
		$sBody .= $crlf . '<?xml version="1.0"?>';
		$sBody .= $crlf . '<TimeInTransitRequest xml:lang="en-US">';
		$sBody .= $crlf . '<Request>';
		$sBody .= $crlf . '<TransactionReference>';
		$sBody .= $crlf . '<CustomerContext>Time in Transit Request</CustomerContext>';
		$sBody .= $crlf . '<XpciVersion>1.0002</XpciVersion>';
		$sBody .= $crlf . '</TransactionReference>';
		$sBody .= $crlf . '<RequestAction>' . 'TimeInTransit' . '</RequestAction>';	// pre-set to TimeInTransit
		$sBody .= $crlf . '</Request>';
		$sBody .= $crlf . '<TransitFrom>';
		$sBody .= $crlf . '<AddressArtifactFormat>';
		// PoliticalDivision2 required for outside US shipments
		if ($pkg->ship_city_town) $sBody .= $crlf . '<PoliticalDivision2>' . $pkg->ship_city_town . '</PoliticalDivision2>';
		if ($pkg->ship_state_province) $sBody .= $crlf . '<PoliticalDivision1>' . strtoupper($pkg->ship_state_province) . '</PoliticalDivision1>';
		if ($pkg->ship_postal_code) $sBody .= $crlf . '<PostcodePrimaryLow>' . $pkg->ship_postal_code . '</PostcodePrimaryLow>';
//		$country_name = $_SESSION['language']->get_country_iso_2_from_3($pkg->ship_country_code);
		$sBody .= $crlf . '<CountryCode>' . $pkg->ship_from_country_iso2 . '</CountryCode>';
		$sBody .= $crlf . '</AddressArtifactFormat>';
		$sBody .= $crlf . '</TransitFrom>';
		$sBody .= $crlf . '<TransitTo>';
		$sBody .= $crlf . '<AddressArtifactFormat>';
		// PoliticalDivision2 required for outside US shipments
		if ($pkg->ship_to_city) $sBody .= $crlf . '<PoliticalDivision2>' . $pkg->ship_to_city . '</PoliticalDivision2>';
		if ($pkg->ship_to_state) $sBody .= $crlf.'<PoliticalDivision1>' . strtoupper($pkg->ship_to_state) . '</PoliticalDivision1>';
		if ($pkg->ship_to_postal_code) $sBody .= $crlf.'<PostcodePrimaryLow>' . $pkg->ship_to_postal_code . '</PostcodePrimaryLow>';
//		$country_name = $_SESSION['language']->get_country_iso_2_from_3($pkg->ship_to_country_code);
		$sBody .= $crlf . '<CountryCode>' . $pkg->ship_to_country_iso2 . '</CountryCode>';
		if ($pkg->residential_address) $sBody .= $crlf . '<ResidentialAddressIndicator/>';
		$sBody .= $crlf . '</AddressArtifactFormat>';
		$sBody .= $crlf . '</TransitTo>';
		$sBody .= $crlf . '<PickupDate>' . date('Ymd', strtotime($pkg->ship_date)) . '</PickupDate>';
		$sBody .= $crlf . '</TimeInTransitRequest>';
		$sBody .= $crlf;
		return $sBody;
	}

	// ***************************************************************************************************************
	//								Parse function to retrieve UPS rates
	// ***************************************************************************************************************
	function getUPSrates($pkg) {
		global $messageStack, $UPSRateCodes, $shipping_defaults;

		// Retrieve the user choices for services to rate shop
// TBD - defaults per user db choice for now
		$user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_UPS_TYPES));

		$UPSQuote = array();	// Initialize the Response Array
		$arrRates = array();	// Initialize the Rate Output array

		$this->package = $pkg->split_shipment($pkg);
		if (!$this->package) throw new \core\classes\userException(SHIPPING_UPS_PACKAGE_ERROR . $pkg->pkg_weight);
		if ($shipping_defaults['TnTEnable'] && $_SESSION['language']->get_country_iso_2_from_3($pkg->ship_to_country_code) == 'US') {
			// Use UPS time in transit to get shipment time
			$strXML = $this->FormatTnTRequest();
			$url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_TNT_URL_TEST : MODULE_SHIPPING_UPS_TNT_URL;
			$SubmitXML = GetXMLString($strXML, $url, "POST");
			// Check for XML request errors
			if ($SubmitXML['result'] == 'error') return $SubmitXML;
			$ResponseXML = $SubmitXML['xmlString'];
			// Check for errors
			$XMLPath = 'TimeInTransitResponse:Response:ResponseStatusCode';
			$XMLSuccess = GetNodeData($ResponseXML, $XMLPath);
			if (!$XMLSuccess) {	// fetch the error code
//				$XMLPath = 'TimeInTransitResponse:Response:Error:ErrorSeverity';
//				$XMLErrorSev = GetNodeData($ResponseXML, $XMLPath);
				$XMLPath = 'TimeInTransitResponse:Response:Error:ErrorCode';
				$XMLErrorType = GetNodeData($ResponseXML, $XMLPath);
				$XMLPath = 'TimeInTransitResponse:Response:Error:ErrorDescription';
				$XMLErrorDesc = GetNodeData($ResponseXML, $XMLPath);
				throw new \core\classes\userException((SHIPPING_UPS_TNT_ERROR . $XMLErrorType . ' - ' . $XMLErrorDesc);
			}

			// See if service list returned or candidate city list is returned.
			$XMLPath = 'TimeInTransitResponse:TransitResponse:ServiceSummary:Service:Code';	// name of the index in array
			$XMLService = GetNodeData($ResponseXML, $XMLPath);
			if (!$XMLService) {	// fetch the candidate list city and matching postal codes in case bad zip provided
				$XMLStart = 'TimeInTransitResponse:TransitResponse:TransitToList:Candidate';
				$XMLIndexName = '';	// needs to be null to create single dimension array of cities
				$TagsToFind = array('index'=>'AddressArtifactFormat:PoliticalDivision2');	// use 'index' to create non-associate array
				$CityCodes['City'] = GetNodeArray($ResponseXML, $XMLStart, $XMLIndexName, $TagsToFind);
				$TagsToFind = array('index'=>'AddressArtifactFormat:PostcodePrimaryLow');	// use 'index' to create non-associate array
				$CityCodes['PostalCode'] = GetNodeArray($ResponseXML, $XMLStart, $XMLIndexName, $TagsToFind);
				$UPSQuote['validcities'] = $CityCodes;
				$UPSQuote['result'] = 'CityMatch';
				return $UPSQuote;
			} else {	// fetch the service list
				$XMLStart = 'TimeInTransitResponse:TransitResponse:ServiceSummary';	// base location in the XML string (repeated)
				$XMLIndexName = 'Service:Code';	// name of the index in array
				$TagsToFind = array();
				$TagsToFind['DeliveryDOW'] = 'EstimatedArrival:DayOfWeek';	//index name and path from XMLStart to get data
				$TagsToFind['DeliveryTime'] = 'EstimatedArrival:Time';
				$TagsToFind['TransitDays'] = 'EstimatedArrival:BusinessTransitDays';
				$Services = GetNodeArray($ResponseXML, $XMLStart, $XMLIndexName, $TagsToFind);
				// Fetch the Ship to state to insert if left blank
				$XMLPath = 'TimeInTransitResponse:TransitResponse:TransitTo:AddressArtifactFormat:PoliticalDivision1';	// Get Ship To State
				$defaults['ShipToStateProv'] = GetNodeData($ResponseXML, $XMLPath);
				// Fetch the Ship to City
				$XMLPath = 'TimeInTransitResponse:TransitResponse:TransitTo:AddressArtifactFormat:PoliticalDivision2';	// Get Ship To State
				$CityCodes['City'][0] =  GetNodeData($ResponseXML, $XMLPath);
				$defaults['City'] = $CityCodes['City'][0];
				$CityCodes['PostalCode'][0] = '';
			}
			foreach ($this->UPSTnTCodes as $key => $value) {
				if (isset($Services[$key]) && in_array($value, $user_choices)) {
					$arrRates[$this->id][$value]['notes'] = $Services[$key]['TransitDays'] . SHIPPING_UPS_RATE_TRANSIT . $Services[$key]['DeliveryDOW'];
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
		if ($SubmitXML['result']=='error') throw new \core\classes\userException(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message']);
		$ResponseXML = $SubmitXML['xmlString'];
		// Check for errors returned from UPS
		$XMLPath = 'RatingServiceSelectionResponse:Response:ResponseStatusCode';
		$XMLSuccess = GetNodeData($ResponseXML, $XMLPath);
		if (!$XMLSuccess) {	// fetch the error code
//			$XMLPath = 'RatingServiceSelectionResponse:Response:Error:ErrorSeverity';
//			$XMLErrorSev = GetNodeData($ResponseXML, $XMLPath);
			$XMLPath = 'RatingServiceSelectionResponse:Response:Error:ErrorCode';
			$XMLErrorType = GetNodeData($ResponseXML, $XMLPath);
			$XMLPath = 'RatingServiceSelectionResponse:Response:Error:ErrorDescription';
			$XMLErrorDesc = GetNodeData($ResponseXML, $XMLPath);
			throw new \core\classes\userException(SHIPPING_UPS_RATE_ERROR . $XMLErrorType . ' - ' . $XMLErrorDesc);
		}

		// Fetch the UPS Rates
		$XMLStart     = 'RatingServiceSelectionResponse:RatedShipment';
		$XMLIndexName = 'Service:Code';	// name of the index in array
		$TagsToFind   = array();
		$TagsToFind   = array(
			'TransitDays'  => 'GuaranteedDaysToDelivery',
			'TransitTime'  => 'ScheduledDeliveryTime',
			'ShipmentCost' => 'NegotiatedRates:NetSummaryCharges:GrandTotal:MonetaryValue',
			'BookCharges'  => 'TotalCharges:MonetaryValue',
		);
		$UPSRates = GetNodeArray($ResponseXML, $XMLStart, $XMLIndexName, $TagsToFind);
		foreach ($this->UPSRateCodes as $key => $value) {
			if (isset($UPSRates[$key]) && in_array($value, $user_choices)) {
				if ($UPSRates[$key]['BookCharges'] <> "")  $arrRates[$this->id][$value]['book'] = $admin->currencies->clean_value($UPSRates[$key]['BookCharges']);
				if ($UPSRates[$key]['ShipmentCost'] <> "") $arrRates[$this->id][$value]['cost'] = $admin->currencies->clean_value($UPSRates[$key]['ShipmentCost']);
				$arrRates[$this->id][$value]['note'] = '';
				if ($UPSRates[$key]['TransitDays'] <> "") $arrRates[$this->id][$value]['note'] .= $UPSRates[$key]['TransitDays'] . ' Day(s) Transit. ';
				$arrRates[$this->id][$value]['note'] .= ($UPSRates[$key]['TransitTime'] <> "") ? 'by ' . $UPSRates[$key]['TransitTime'] : 'by End of Day';
				if (function_exists('ups_shipping_rate_calc')) {
					$arrRates[$this->id][$value]['quote'] = ups_shipping_rate_calc($arrRates[$this->id][$value]['book'], $arrRates[$this->id][$value]['cost'], $value);
				} else {
					if ($UPSRates[$key]['BookCharges'] <> "") $arrRates[$this->id][$value]['quote'] = $UPSRates[$key]['BookCharges'];
				}
			}
		}

		// All calculations finished, return
		$UPSQuote['result'] = 'success';
		$UPSQuote['rates']  = $arrRates;
		return $UPSQuote;
	}	// End UPS Rate Function

// ***************************************************************************************************************
//								UPS LABEL REQUEST (multipiece compatible)
// ***************************************************************************************************************
	function retrieveLabel($sInfo) {
		global $messageStack;
		$ups_results = array();
		if (in_array($sInfo->ship_method, array('I2DEam','I2Dam','I3D','GndFrt'))) { // unsupported ship methods
			throw new \core\classes\userException("The ship method {$sInfo->ship_method} is not supported by this tool presently. Please ship the package via a different tool.");
		}
		$strXML = $this->FormatUPSShipRequest($sInfo, $key);
		\core\classes\messageStack::debug_log('Ship Request xmlString = ' . htmlspecialchars($strXML));
		$this->labelRequest = $strXML;
//echo 'Ship Request xmlString = ' . htmlspecialchars($strXML) . '<br />';
		$url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_SHIP_URL_TEST : MODULE_SHIPPING_UPS_SHIP_URL;
		$SubmitXML = GetXMLString($strXML, $url, "POST");
		\core\classes\messageStack::debug_log('Ship Request response string = ' . htmlspecialchars($SubmitXML['xmlString']));
		$this->labelResponse = $SubmitXML['xmlString'];
//echo 'Ship Request response string = ' . htmlspecialchars($SubmitXML['xmlString']) . '<br />';
		// Check for XML request errors
		if ($SubmitXML['result'] == 'error') throw new \core\classes\userException(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message']);
		$ResponseXML = $SubmitXML['xmlString'];
		$XMLFail = GetNodeData($ResponseXML, 'ShipmentConfirmResponse:Response:Error:ErrorCode'); // Check for errors returned from UPS
		$XMLWarn = GetNodeData($ResponseXML, 'ShipmentConfirmResponse:Response:Error:ErrorSeverity'); // Check for warnings returned from UPS (process continues)
		if ($XMLFail && $XMLWarn == 'Warning') { // soft error, report it and continue
			\core\classes\messageStack::add('UPS Label Request Warning # ' . $XMLFail . ' - ' . GetNodeData($ResponseXML, 'ShipmentConfirmResponse:Response:Error:ErrorDescription'),'caution');
		} elseif ($XMLFail && $XMLWarn <> 'Warning') { // hard error - return with bad news
			throw new \core\classes\userException("UPS Label Request Error # $XMLFail - " . GetNodeData($ResponseXML, 'ShipmentConfirmResponse:Response:Error:ErrorDescription'));
		}
		$digest = GetNodeData($ResponseXML, 'ShipmentConfirmResponse:ShipmentDigest'); // Check for errors returned from UPS

		// Now resend request with digest to get the label
		$strXML = $this->FormatUPSAcceptRequest($digest);
		\core\classes\messageStack::debug_log('Accept Request xmlString = ' . htmlspecialchars($strXML));
		$this->labelFetchRequest = $strXML;
//echo 'Accept Request xmlString = ' . htmlspecialchars($strXML) . '<br />';
		$url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_LABEL_URL_TEST : MODULE_SHIPPING_UPS_LABEL_URL;
		$SubmitXML = GetXMLString($strXML, $url, "POST");
		\core\classes\messageStack::debug_log('Accept Response response string = ' . htmlspecialchars($SubmitXML['xmlString']));
		$this->labelFetchReturned = $SubmitXML['xmlString'];
//echo 'Accept Response response string = ' . htmlspecialchars($SubmitXML['xmlString']) . '<br />';
		// Check for XML request errors
		if ($SubmitXML['result'] == 'error') throw new \core\classes\userException(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message']);
		$ResponseXML = $SubmitXML['xmlString'];
		$XMLFail = GetNodeData($ResponseXML, 'ShipmentAcceptResponse:Response:Error:ErrorCode'); // Check for errors returned from UPS
		$XMLWarn = GetNodeData($ResponseXML, 'ShipmentAcceptResponse:Response:Error:ErrorSeverity'); // Check for warnings returned from UPS (process continues)
		if ($XMLFail && $XMLWarn == 'Warning') { // soft error, report it and continue
			\core\classes\messageStack::add('UPS Label Retrieval Warning # ' . $XMLFail . ' - ' . GetNodeData($ResponseXML, 'ShipmentAcceptResponse:Response:Error:ErrorDescription'),'caution');
		} elseif ($XMLFail && $XMLWarn <> 'Warning') { // hard error - return with bad news
			throw new \core\classes\userException("UPS Label Retrieval Error # $XMLFail - " . GetNodeData($ResponseXML, 'ShipmentAcceptResponse:Response:Error:ErrorDescription'));
		}

		// Fetch the UPS shipment information information
		$ups_results = array(
//			'tracking_number' => GetNodeData($ResponseXML, 'ShipmentAcceptResponse:ShipmentResults:ShipmentIdentificationNumber'),
			'dim_weight'    => GetNodeData($ResponseXML, 'ShipmentAcceptResponse:ShipmentResults:BillingWeight:Weight'),
			'zone'          => 'N/A',
			'billed_weight' => GetNodeData($ResponseXML, 'ShipmentAcceptResponse:ShipmentResults:BillingWeight:Weight'),
			'net_cost'      => GetNodeData($ResponseXML, 'ShipmentAcceptResponse:ShipmentResults:NegotiatedRates:NetSummaryCharges:GrandTotal:MonetaryValue'),
			'book_cost'     => GetNodeData($ResponseXML, 'ShipmentAcceptResponse:ShipmentResults:ShipmentCharges:TotalCharges:MonetaryValue'),
			'delivery_date' => 'Not Provided');

		// Fetch the package information and label
		$Container = 'PackageResults';	// base location in the XML string (repeated)
		$TagsToFind = array(
			'tracking'      => 'TrackingNumber',
			'graphic_image' => 'GraphicImage',
			'html_image'    => 'HTMLImage',
		);
		$results = GetPackageArray($ResponseXML, $Container, $TagsToFind);

		$returnArray = array();
		if (sizeof($results) == 0) throw new \core\classes\userException("Error - No label found in return string.");
		foreach ($results as $label) {
		    $returnArray[] = $ups_results + array('tracking' => $label['tracking']);
			$date = explode('-', $sInfo->ship_date); // date format YYYY-MM-DD
			$file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/' . $this->id . '/' . $date[0] . '/' . $date[1] . '/' . $date[2] . '/';
			validate_path($file_path);
			// check for label to be for thermal printer or plain paper
			if (MODULE_SHIPPING_UPS_PRINTER_TYPE == 'Thermal') {
				// keep the thermal label encoded for now
				$output_label = base64_decode($label['graphic_image']);
				$filename = $label['tracking'] . '.lpt'; // thermal printer
			} else {
				$output_label = base64_decode($label['graphic_image']);
				$filename = $label['tracking'] . '.gif'; // plain paper
			}
			if (!$handle = @fopen($file_path . $filename, 'w')) throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE,  $file_path . $filename));
			if (!@fwrite($handle, $output_label) === false) 	throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, $file_path . $filename));
			$this->labelFilePath = $file_path . $filename;
			if (!@fclose($handle)) throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, $filename));
		}
        \core\classes\messageStack::add('Successfully retrieved the UPS shipping label. Tracking # ' . $ups_results[$key]['tracking'],'success');
		if (DEBUG) $messageStack->write_debug();
		return $returnArray;
	}

	function FormatUPSShipRequest($pkg, $key) {
		$crlf = chr(13) . chr(10);

		$sBody = '<?xml version="1.0"?>';
		$sBody .= $crlf . '<AccessRequest xml:lang="en-US">';
		$sBody .= $crlf . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= $crlf . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= $crlf . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= $crlf . '</AccessRequest>';

		$sBody .= $crlf . '<?xml version="1.0"?>';
		$sBody .= $crlf . '<ShipmentConfirmRequest>';
		$sBody .= $crlf . '<Request>';
		$sBody .= $crlf . '<TransactionReference>';
		$sBody .= $crlf . '<CustomerContext>Shipment Label Request</CustomerContext>';
		$sBody .= $crlf . '<XpciVersion>1.0001</XpciVersion>';
		$sBody .= $crlf . '</TransactionReference>';
		$sBody .= $crlf . '<RequestAction>' . 'ShipConfirm' . '</RequestAction>'; // must be ShipConfirm for tool to work
		$sBody .= $crlf . '<RequestOption>' . 'validate' . '</RequestOption>'; // 'validate' or 'nonvalidate' address
		$sBody .= $crlf . '</Request>';
		$sBody .= $crlf . '<Shipment>';

		$sBody .= $crlf . '<Shipper>';
		$sBody .= $crlf . '<Name>' . COMPANY_NAME . '</Name>';
		$sBody .= $crlf . '<ShipperNumber>' . MODULE_SHIPPING_UPS_SHIPPER_NUMBER . '</ShipperNumber>';
		if (COMPANY_TELEPHONE1) $sBody .= $crlf . '<PhoneNumber>' . COMPANY_TELEPHONE1 . '</PhoneNumber>';
		if (COMPANY_FAX) $sBody .= $crlf . '<FaxNumber>' . COMPANY_FAX . '</FaxNumber>';
		if (COMPANY_EMAIL) $sBody .= $crlf . '<EMailAddress>' . COMPANY_EMAIL . '</EMailAddress>';
		$sBody .= $crlf . '<Address>';
		if (COMPANY_ADDRESS1) $sBody .= $crlf . '<AddressLine1>' . COMPANY_ADDRESS1 . '</AddressLine1>';
		if (COMPANY_ADDRESS2) $sBody .= $crlf . '<AddressLine2>' . COMPANY_ADDRESS2 . '</AddressLine2>';
//		if (COMPANY_ADDRESS3) $sBody .= $crlf . '<AddressLine3>' . COMPANY_ADDRESS3 . '</AddressLine3>'; // Not used in Current System
		if (COMPANY_CITY_TOWN) $sBody .= $crlf . '<City>' . COMPANY_CITY_TOWN . '</City>';
		if (COMPANY_ZONE) $sBody .= $crlf . '<StateProvinceCode>' . COMPANY_ZONE . '</StateProvinceCode>';
		if (COMPANY_POSTAL_CODE) $sBody .= $crlf . '<PostalCode>' . COMPANY_POSTAL_CODE . '</PostalCode>';
		$sBody .= $crlf . '<CountryCode>' . $_SESSION['language']->get_country_iso_2_from_3(COMPANY_COUNTRY) . '</CountryCode>';
		$sBody .= $crlf . '</Address>';
		$sBody .= $crlf . '</Shipper>';

		$sBody .= $crlf . '<ShipTo>';
		$sBody .= $crlf . '<CompanyName>' . $pkg->ship_primary_name . '</CompanyName>';
		if ($pkg->ship_contact) $sBody .= $crlf . '<AttentionName>' . $pkg->ship_contact . '</AttentionName>';
		if ($pkg->ship_telephone1) $sBody .= $crlf . '<PhoneNumber>' . $pkg->ship_telephone1 . '</PhoneNumber>';
		if ($pkg->fax) $sBody .= $crlf . '<FaxNumber>' . $pkg->fax . '</FaxNumber>';
		if ($pkg->ship_email) $sBody .= $crlf . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
		$sBody .= $crlf . '<Address>';
		if ($pkg->ship_address1) $sBody .= $crlf . '<AddressLine1>' . $pkg->ship_address1 . '</AddressLine1>';
		if ($pkg->ship_address2) $sBody .= $crlf . '<AddressLine2>' . $pkg->ship_address2 . '</AddressLine2>';
//		if ($pkg->ship_address3) $sBody .= $crlf . '<AddressLine3>' . $pkg->ship_address3 . '</AddressLine3>'; // Not used
		if ($pkg->ship_city_town) $sBody .= $crlf . '<City>' . $pkg->ship_city_town . '</City>';
		if ($pkg->ship_state_province) $sBody .= $crlf . '<StateProvinceCode>' . strtoupper($pkg->ship_state_province) . '</StateProvinceCode>';
		if ($pkg->ship_postal_code) $sBody .= $crlf . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
		$sBody .= $crlf . '<CountryCode>' . $pkg->ship_country_code . '</CountryCode>';
		if ($pkg->residential_address) $sBody .= $crlf . '<ResidentialAddress />';
		$sBody .= $crlf . '</Address>';
		$sBody .= $crlf . '</ShipTo>';

/* TBD assume ship from is the same as shipper
		$sBody .= $crlf . '<ShipFrom>';
		$sBody .= $crlf . '<CompanyName>' . COMPANY_NAME . '</CompanyName>';
		if (COMPANY_TELEPHONE1) $sBody .= $crlf . '<PhoneNumber>' . COMPANY_TELEPHONE1 . '</PhoneNumber>';
		if (COMPANY_FAX) $sBody .= $crlf . '<FaxNumber>' . COMPANY_FAX . '</FaxNumber>';
		$sBody .= $crlf . '<Address>';
		if (COMPANY_ADDRESS1) $sBody .= $crlf . '<AddressLine1>' . COMPANY_ADDRESS1 . '</AddressLine1>';
		if (COMPANY_ADDRESS2) $sBody .= $crlf . '<AddressLine2>' . COMPANY_ADDRESS2 . '</AddressLine2>';
//		if (COMPANY_ADDRESS3) $sBody .= $crlf . '<AddressLine3>' . TBD . '</AddressLine3>'; // Not used in Current System
		if (COMPANY_CITY_TOWN) $sBody .= $crlf . '<City>' . COMPANY_CITY_TOWN . '</City>';
		if (COMPANY_ZONE) $sBody .= $crlf . '<StateProvinceCode>' . COMPANY_ZONE . '</StateProvinceCode>';
		if (COMPANY_POSTAL_CODE) $sBody .= $crlf . '<PostalCode>' . COMPANY_POSTAL_CODE . '</PostalCode>';
		$sBody .= $crlf . '<CountryCode>' . $_SESSION['language']->get_country_iso_2_from_3(COMPANY_COUNTRY) . '</CountryCode>';
		$sBody .= $crlf . '</Address>';
		$sBody .= $crlf . '</ShipFrom>';
*/

/* TBD sold is only required for international
		$sBody .= $crlf . '<SoldTo>';
		$sBody .= $crlf . '<CompanyName>' . $pkg->ship_primary_name . '</CompanyName>';
		if ($pkg->ship_contact) $sBody .= $crlf . '<AttentionName>' . $pkg->ship_contact . '</AttentionName>';
		if ($pkg->ship_telephone1) $sBody .= $crlf . '<PhoneNumber>' . $pkg->ship_telephone1 . '</PhoneNumber>';
		$sBody .= $crlf . '<Address>';
		if ($pkg->ship_address1) $sBody .= $crlf . '<Address1>' . $pkg->ship_address1 . '</Address1>';
		if ($pkg->ship_address2) $sBody .= $crlf . '<Address2>' . $pkg->ship_address2 . '</Address2>';
//		if ($pkg->ship_to_address3) $sBody .= $crlf . '<Address3>' . $pkg->ship_to_address3 . '</Address3>'; // Not used
		if ($pkg->ship_city_town) $sBody .= $crlf . '<City>' . $pkg->ship_city_town . '</City>';
		if ($pkg->ship_state_province) $sBody .= $crlf . '<StateProvinceCode>' . $pkg->ship_state_province . '</StateProvinceCode>';
		if ($pkg->ship_postal_code) $sBody .= $crlf . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
		$sBody .= $crlf . '<CountryCode>' . $pkg->ship_country_code . '</CountryCode>';
		$sBody .= $crlf . '</Address>';
		$sBody .= $crlf . '</SoldTo>';
*/
		$sBody .= $crlf . '<Service>';
		$temp = array_flip($this->UPSRateCodes);
		$sBody .= $crlf . '<Code>' . $temp[$pkg->ship_method] . '</Code>';
		$sBody .= $crlf . '</Service>';

		$sBody .= $crlf . '<PaymentInformation>';
		switch ($pkg->bill_charges) {
			default:
			case '0': // bill sender
				$sBody .= $crlf . '<Prepaid>';
				$sBody .= $crlf . '<BillShipper>';
				$sBody .= $crlf . '<AccountNumber>' . MODULE_SHIPPING_UPS_SHIPPER_NUMBER . '</AccountNumber>'; // only bill account (no credit card)
				$sBody .= $crlf . '</BillShipper>';
				$sBody .= $crlf . '</Prepaid>';
				break;
			case '1': // bill recepient
				$sBody .= $crlf . '<FreightCollect>';
				$sBody .= $crlf . '<BillReceiver>';
				$sBody .= $crlf . '<AccountNumber>' . $pkg->bill_acct . '</AccountNumber>'; // only bill accounts (no addresses)
				$sBody .= $crlf . '<Address>';
				$sBody .= $crlf . '<PostalCode>' . $pkg->ship_postal_code . '</PostalCode>';
				$sBody .= $crlf . '</Address>';
				$sBody .= $crlf . '</BillReceiver>';
				$sBody .= $crlf . '</FreightCollect>';
				break;
			case '2': // bill third party
				$sBody .= $crlf . '<BillThirdParty>';
				$sBody .= $crlf . '<BillThirdPartyShipper>';
				$sBody .= $crlf . '<AccountNumber>' . $pkg->bill_acct . '</AccountNumber>'; // only bill accounts (no addresses)
				$sBody .= $crlf . '<ThirdParty>';
				$sBody .= $crlf . '<Address>';
				$sBody .= $crlf . '<PostalCode>' . $pkg->third_party_zip . '</PostalCode>';
				$sBody .= $crlf . '<CountryCode>' . $pkg->ship_country_code . '</CountryCode>';
				$sBody .= $crlf . '</Address>';
				$sBody .= $crlf . '</ThirdParty>';
				$sBody .= $crlf . '</BillThirdPartyShipper>';
				$sBody .= $crlf . '</BillThirdParty>';
				break;
			case '3': // COD - NOT allowed for UPS
				throw new \core\classes\userException("Error - COD - NOT allowed for UPS.");;
		}
		$sBody .= $crlf . '</PaymentInformation>';

		$sBody .= $crlf . '<RateInformation>';
		$sBody .= $crlf . '<NegotiatedRatesIndicator />';
		$sBody .= $crlf . '</RateInformation>';

		$sBody .= $crlf . '<ShipmentServiceOptions>';
		if ($pkg->saturday_delivery) $sBody .= $crlf . '<SaturdayDelivery></SaturdayDelivery>';
		if ($pkg->email_sndr_ship || $pkg->email_sndr_excp || $pkg->email_sndr_dlvr || $pkg->email_rcp_ship || $pkg->email_rcp_excp || $pkg->email_rcp_dlvr) {
			if ($pkg->email_sndr_ship || $pkg->email_sndr_excp || $pkg->email_sndr_dlvr) {
				if ($pkg->email_sndr_ship) {
					$sBody .= $crlf . '<ShipmentNotification>';
					$sBody .= $crlf . '<NotificationCode>6</NotificationCode>';
					$sBody .= $crlf . '<EMailMessage>';
					$sBody .= $crlf . '<EMailAddress>' . $pkg->sender_email_address . '</EMailAddress>';
					$sBody .= $crlf . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= $crlf . '<SubjectCode>01</SubjectCode>';
					$sBody .= $crlf . '</EMailMessage>';
					$sBody .= $crlf . '</ShipmentNotification>';
				}
				if ($pkg->email_sndr_excp) {
					$sBody .= $crlf . '<ShipmentNotification>';
					$sBody .= $crlf . '<NotificationCode>7</NotificationCode>';
					$sBody .= $crlf . '<EMailMessage>';
					$sBody .= $crlf . '<EMailAddress>' . $pkg->sender_email_address . '</EMailAddress>';
					$sBody .= $crlf . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= $crlf . '<SubjectCode>01</SubjectCode>';
					$sBody .= $crlf . '</EMailMessage>';
					$sBody .= $crlf . '</ShipmentNotification>';
				}
				if ($pkg->email_sndr_dlvr) {
					$sBody .= $crlf . '<ShipmentNotification>';
					$sBody .= $crlf . '<NotificationCode>8</NotificationCode>';
					$sBody .= $crlf . '<EMailMessage>';
					$sBody .= $crlf . '<EMailAddress>' . $pkg->sender_email_address . '</EMailAddress>';
					$sBody .= $crlf . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= $crlf . '<SubjectCode>01</SubjectCode>';
					$sBody .= $crlf . '</EMailMessage>';
					$sBody .= $crlf . '</ShipmentNotification>';
				}
			}
			if ($pkg->email_rcp_ship || $pkg->email_rcp_excp || $pkg->email_rcp_dlvr) {
				if ($pkg->email_rcp_ship) {
					$sBody .= $crlf . '<ShipmentNotification>';
					$sBody .= $crlf . '<NotificationCode>6</NotificationCode>';
					$sBody .= $crlf . '<EMailMessage>';
					$sBody .= $crlf . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
					$sBody .= $crlf . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= $crlf . '<SubjectCode>01</SubjectCode>';
					$sBody .= $crlf . '</EMailMessage>';
					$sBody .= $crlf . '</ShipmentNotification>';
				}
				if ($pkg->email_rcp_excp) {
					$sBody .= $crlf . '<ShipmentNotification>';
					$sBody .= $crlf . '<NotificationCode>7</NotificationCode>';
					$sBody .= $crlf . '<EMailMessage>';
					$sBody .= $crlf . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
					$sBody .= $crlf . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= $crlf . '<SubjectCode>01</SubjectCode>';
					$sBody .= $crlf . '</EMailMessage>';
					$sBody .= $crlf . '</ShipmentNotification>';
				}
				if ($pkg->email_rcp_dlvr) {
					$sBody .= $crlf . '<ShipmentNotification>';
					$sBody .= $crlf . '<NotificationCode>8</NotificationCode>';
					$sBody .= $crlf . '<EMailMessage>';
					$sBody .= $crlf . '<EMailAddress>' . $pkg->ship_email . '</EMailAddress>';
					$sBody .= $crlf . '<UndeliverableEMailAddress>' . COMPANY_EMAIL . '</UndeliverableEMailAddress>';
					$sBody .= $crlf . '<SubjectCode>01</SubjectCode>';
					$sBody .= $crlf . '</EMailMessage>';
					$sBody .= $crlf . '</ShipmentNotification>';
				}
			}
		}
		$sBody .= $crlf . '</ShipmentServiceOptions>';

		foreach ($pkg->package as $pkgnum) { // Enter each package
			$sBody .= $crlf . '<Package>';
			$sBody .= $crlf . '<PackagingType><Code>' . $pkg->pkg_type . '</Code></PackagingType>';
			$sBody .= $crlf . '<Dimensions>';
			$sBody .= $crlf . '<UnitOfMeasurement><Code>' . $pkg->pkg_dimension_unit . '</Code></UnitOfMeasurement>';
			$sBody .= $crlf . '<Length>' . ceil($pkgnum['length']) . '</Length>';
			$sBody .= $crlf . '<Width>' . ceil($pkgnum['width']) . '</Width>';
			$sBody .= $crlf . '<Height>' . ceil($pkgnum['height']) . '</Height>';
			$sBody .= $crlf . '</Dimensions>';
			$sBody .= $crlf . '<PackageWeight>';
			$sBody .= $crlf . '<UnitOfMeasurement><Code>' . $pkg->pkg_weight_unit . '</Code></UnitOfMeasurement>';
			$sBody .= $crlf . '<Weight>' . $pkgnum['weight'] . '</Weight>';
			$sBody .= $crlf . '</PackageWeight>';

			$sBody .= $crlf . '<ReferenceNumber>';
			$sBody .= $crlf . '<Code>PO</Code>'; // Purchase Order #
			$sBody .= $crlf . '<Value>' . $pkg->so_po_ref_id . '</Value>';
			$sBody .= $crlf . '</ReferenceNumber>';
			$sBody .= $crlf . '<ReferenceNumber>';
			$sBody .= $crlf . '<Code>IK</Code>'; // Invoice #
			$sBody .= $crlf . '<Value>' . $pkg->purchase_invoice_id . '</Value>';
			$sBody .= $crlf . '</ReferenceNumber>';

			if ($pkg->additional_handling) $sBody .= $crlf . '<AdditionalHandling></AdditionalHandling>';

			$temp = '';
			if ($pkg->delivery_confirmation) {
				$temp .= $crlf . '<DeliveryConfirmation>';
				$temp .= $crlf . '<DCISType>' . $pkg->delivery_confirmation_type . '</DCISType>';
				$temp .= $crlf . '</DeliveryConfirmation>';
			}
			if ($pkg->insurance) {
				$temp .= $crlf . '<InsuredValue>';
				$temp .= $crlf . '<CurrencyCode>' . $pkg->insurance_currency . '</CurrencyCode>';
				$temp .= $crlf . '<MonetaryValue>' . $pkgnum['value'] . '</MonetaryValue>';
				$temp .= $crlf . '</InsuredValue>';
			}
			if ($pkg->cod) {
				$temp .= $crlf . '<COD>';
				$temp .= $crlf . '<CODCode>3</CODCode>';
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
			if ($temp) $sBody .= $crlf . '<PackageServiceOptions>' . $crlf. $temp . $crlf . '</PackageServiceOptions>';
			$sBody .= $crlf . '</Package>';
		}
		$sBody .= $crlf . '</Shipment>';

		$sBody .= $crlf . '<LabelSpecification>';
		$sBody .= $crlf . '<LabelPrintMethod><Code>' . ((MODULE_SHIPPING_UPS_PRINTER_TYPE == 'GIF') ? 'GIF' : 'EPL') . '</Code></LabelPrintMethod>'; // valid values are GIF, EPL, SPL
		$sBody .= $crlf . '<HTTPUserAgent>' . 'Mozilla/4.5' . '</HTTPUserAgent>'; // Default Value
		if (MODULE_SHIPPING_UPS_PRINTER_TYPE <> 'GIF') {
			$sBody .= $crlf . '<LabelStockSize>';
			$sBody .= $crlf . '<UnitOfMeasurement>IN</UnitOfMeasurement>';
			$sBody .= $crlf . '<Width>' . MODULE_SHIPPING_UPS_LABEL_SIZE . '</Width>'; // valid values are 6 and 8
			$sBody .= $crlf . '<Height>4</Height>'; // must be 4
			$sBody .= $crlf . '</LabelStockSize>'; // valid values are 4x6 and 4x8
		}
		$sBody .= $crlf . '<LabelImageFormat><Code>' . ((MODULE_SHIPPING_UPS_PRINTER_TYPE == 'GIF') ? 'GIF' : 'EPL2') . '</Code></LabelImageFormat>';
		$sBody .= $crlf . '</LabelSpecification>';

		$sBody .= $crlf . '</ShipmentConfirmRequest>';
		$sBody .= $crlf;
		return $sBody;
	}

	function FormatUPSAcceptRequest($digest) {
		$crlf = chr(13) . chr(10);

		$sBody = '<?xml version="1.0"?>';
		$sBody .= $crlf . '<AccessRequest xml:lang="en-US">';
		$sBody .= $crlf . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= $crlf . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= $crlf . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= $crlf . '</AccessRequest>';

		$sBody .= $crlf . '<?xml version="1.0"?>';
		$sBody .= $crlf . '<ShipmentAcceptRequest>';
		$sBody .= $crlf . '<Request>';
		$sBody .= $crlf . '<TransactionReference>';
		$sBody .= $crlf . '<CustomerContext>Shipment Label Accept</CustomerContext>';
		$sBody .= $crlf . '<XpciVersion>1.0001</XpciVersion>';
		$sBody .= $crlf . '</TransactionReference>';
		$sBody .= $crlf . '<RequestAction>' . 'ShipAccept' . '</RequestAction>'; // must be ShipAccept for tool to work
		$sBody .= $crlf . '</Request>';
		$sBody .= $crlf . '<ShipmentDigest>' . $digest . '</ShipmentDigest>';
		$sBody .= $crlf . '</ShipmentAcceptRequest>';
		return $sBody;
	}

// ***************************************************************************************************************
//								UPS DELETE LABEL REQUEST
// ***************************************************************************************************************
	function deleteLabel($shipment_id = '') {
		global $admin, $messageStack;
		if (!$shipment_id) throw new \core\classes\userException("Cannot delete shipment, shipment ID was not provided!");

		if ($this->tracking_number) {
			$tracking_number = $this->tracking_number;
		} else {
			$shipments = $admin->DataBase->query("select ship_date, tracking_id from " . TABLE_SHIPPING_LOG . " where shipment_id = " . $shipment_id);
			$tracking_number = $shipments->fields['tracking_id'];
		}

		$strXML = $this->FormatUPSDeleteRequest($tracking_number);
		$this->labelDelRequest = $strXML;
//echo 'Delete Request xmlString = ' . htmlspecialchars($strXML) . '<br />';
		$url = (MODULE_SHIPPING_UPS_TEST_MODE == 'Test') ? MODULE_SHIPPING_UPS_VOID_SHIPMENT_TEST : MODULE_SHIPPING_UPS_VOID_SHIPMENT;
		$SubmitXML = GetXMLString($strXML, $url, "POST");
		$this->labelDelResponse = $SubmitXML['xmlString'];
//echo 'Delete Request response string = ' . htmlspecialchars($SubmitXML['xmlString']) . '<br />';
		// Check for XML request errors
		if ($SubmitXML['result'] == 'error') throw new \core\classes\userException(SHIPPING_UPS_CURL_ERROR . $SubmitXML['message']);

		$ResponseXML = $SubmitXML['xmlString'];
		$XMLFail = GetNodeData($ResponseXML, 'VoidShipmentResponse:Response:Error:ErrorCode'); // Check for errors returned from UPS
		$XMLWarn = GetNodeData($ResponseXML, 'VoidShipmentResponse:Response:Error:ErrorSeverity'); // Check for warnings returned from UPS (process continues)
		if ($XMLFail && $XMLWarn == 'Warning') { // soft error, report it and continue
			\core\classes\messageStack::add('UPS Label Delete Warning # ' . $XMLFail . ' - ' . GetNodeData($ResponseXML, 'VoidShipmentResponse:Response:Error:ErrorDescription'),'caution');
		} elseif ($XMLFail && $XMLWarn <> 'Warning') { // hard error - return with bad news
			throw new \core\classes\userException("UPS Label Delete Error # $XMLFai - ". GetNodeData($ResponseXML, 'VoidShipmentResponse:Response:Error:ErrorDescription'));
		}

		// delete the label file
		$date = explode('-', $shipments->fields['ship_date']);
		$file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/' . $this->id . '/' . $date[0] . '/' . $date[1] . '/' . $date[2] . '/';
		if (file_exists($file_path . $shipments->fields['tracking_id'] . '.lpt')) {
			$file_name = $shipments->fields['tracking_id'] . '.lpt';
		} elseif (file_exists($file_path . $shipments->fields['tracking_id'] . '.gif')) {
			$file_name = $shipments->fields['tracking_id'] . '.gif';
		} else {
			$file_name = false; // file does not exist, skip
		}
		if ($file_name) if (!unlink($file_path . $file_name)) \core\classes\messageStack::add('Trouble deleting label file (' . $file_path . $file_name . ')','caution');

		// if we are here the delete was successful, the lack of an error indicates success
		\core\classes\messageStack::add('Successfully deleted the UPS shipping label. Tracking # ' . $tracking_number,'success');
		return true;
	}

	function FormatUPSDeleteRequest($tracking_number) {
		$crlf = chr(13) . chr(10);
		$sBody = '<?xml version="1.0"?>';
		$sBody .= $crlf . '<AccessRequest xml:lang="en-US">';
		$sBody .= $crlf . '<AccessLicenseNumber>' . MODULE_SHIPPING_UPS_ACCESS_KEY . '</AccessLicenseNumber>';
		$sBody .= $crlf . '<UserId>' . MODULE_SHIPPING_UPS_USER_ID . '</UserId>';
		$sBody .= $crlf . '<Password>' . MODULE_SHIPPING_UPS_PASSWORD . '</Password>';
		$sBody .= $crlf . '</AccessRequest>';

		$sBody .= $crlf . '<?xml version="1.0"?>';
		$sBody .= $crlf . '<VoidShipmentRequest>';
		$sBody .= $crlf . '<Request>';
		$sBody .= $crlf . '<TransactionReference>';
		$sBody .= $crlf . '<CustomerContext>Shipment Label Delete</CustomerContext>';
		$sBody .= $crlf . '<XpciVersion>1.0001</XpciVersion>';
		$sBody .= $crlf . '</TransactionReference>';
		$sBody .= $crlf . '<RequestAction>' . 'Void' . '</RequestAction>'; // must be ShipAccept for tool to work
		$sBody .= $crlf . '</Request>';
		$sBody .= $crlf . '<ExpandedVoidShipment>';
		$sBody .= $crlf . '<ShipmentIdentificationNumber>' . $tracking_number . '</ShipmentIdentificationNumber>';
		$sBody .= $crlf . '</ExpandedVoidShipment>';
		$sBody .= $crlf . '</VoidShipmentRequest>';
		return $sBody;
	}

  } // end class
?>