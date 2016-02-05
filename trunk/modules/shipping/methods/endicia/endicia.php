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
//  Path: /modules/shipping/methods/endicia/endicia.php
//
namespace shipping\methods\endicia;
define('MODULE_SHIPPING_ENDICIA_PARTNER_ID','lpst'); // 'lpst' - PhreeSoft, LLC Partner ID
define('MODULE_SHIPPING_ENDICIA_DIAL_A_ZIP_PW', 'Phreedom_2012_LLC'); // Dial-A-Zip test acct: 400540, password: HOCKEYSTAR
// Revision history
// 2012-01-15 - Initial Release
ini_set("soap.wsdl_cache_enabled", "0");

// User settings, TBD need to move to user variables
define('ENDICIA_MAX_SINGLE_BOX_WEIGHT', 70); // maximum single box weight for small package in pounds
define('RUBBERSTAMP1',''); // User-supplied text to print on the label. max 50 characters
define('RUBBERSTAMP2','');
define('RUBBERSTAMP3','');
// Set the Label Type
// For Default LabelType: 4�6, 4�5, 4�4.5, DocTab, 6�4
// For DestinationConfirm: 7�3, 6�4, Dymo30384, EnvelopeSize10, Mailer7�5
// For CertifiedMail: 4�6, 7�4, 8�3, Booklet, EnvelopeSize10
// For International: Null or any other value, 4x6c
define('ENDICIA_LABEL_SIZE', 'DocTab');
// constants used in rate screen to match carrier descrptions
define('endicia_GND',   MODULE_SHIPPING_ENDICIA_GND);
define('endicia_GDR',   MODULE_SHIPPING_ENDICIA_GDR);
define('endicia_1DEam', MODULE_SHIPPING_ENDICIA_1DM);
define('endicia_1Dam',  MODULE_SHIPPING_ENDICIA_1DA);
define('endicia_1Dpm',  MODULE_SHIPPING_ENDICIA_1DP);
define('endicia_2Dam',  MODULE_SHIPPING_ENDICIA_2DA);
define('endicia_2Dpm',  MODULE_SHIPPING_ENDICIA_2DP);
define('endicia_3Dam',  MODULE_SHIPPING_ENDICIA_3DA);
define('endicia_3Dpm',  MODULE_SHIPPING_ENDICIA_3DS);
//define('endicia_I2DEam',MODULE_SHIPPING_ENDICIA_XDM);
//define('endicia_I2Dam', MODULE_SHIPPING_ENDICIA_XPR);
//define('endicia_I3D',   MODULE_SHIPPING_ENDICIA_XPD);
// Endicia paths
define('MODULE_SHIPPING_ENDICIA_WSDL_URL','https://LabelServer.Endicia.com/LabelService/EwsLabelService.asmx?WSDL'); // Production Server
define('MODULE_SHIPPING_ENDICIA_TEST_WSDL_URL','https://www.envmgr.com/LabelService/EwsLabelService.asmx?WSDL'); // Test Server
define('MODULE_SHIPPING_ENDICIA_ELS_URL', 'https://www.endicia.com/ELS/ELSServices.cfc?wsdl');
define('MODULE_SHIPPING_USPS_TRACKING_URL','https://tools.usps.com/go/TrackConfirmAction_input?tracking=');
define('MODULE_SHIPPING_ENDICIA_DIAL_A_ZIP_URL','http://www.dial-a-zip.com/XML-Dial-A-ZIP/DAZService.asmx/MethodZIPValidate');

class endicia extends \shipping\classes\shipping {
	public $id				= 'endicia'; // needs to match class name
  	public $text			= MODULE_SHIPPING_ENDICIA_TEXT_TITLE;
  	public $description		= MODULE_SHIPPING_ENDICIA_TEXT_DESCRIPTION;
  	public $sort_order		= 9;
  	public $version			= '1.0';
  	public $shipping_cost;
  	public $handling_cost;

	public $buyPostageAmounts = array(
	  '10'  => TEXT_0010_DOLLARS,
	  '25'  => TEXT_0025_DOLLARS,
	  '100' => TEXT_0100_DOLLARS,
	  '250' => TEXT_0250_DOLLARS,
	  '500' => TEXT_0500_DOLLARS,
	  '1000'=> TEXT_1000_DOLLARS,
	);

	  // Endicia Rate code maps
	public $EndiciaRateCodes = array(
	  'Priority'                    => '1DEam',
	  'Express'                     => '1Dam',
	  'First'                       => '1Dpm',
	  'CriticalMail'                => '2Dam',
	  'LibraryMail'                 => '2Dpm',
	  'StandardMail'                => '3Dam',
	  'MediaMail'                   => '3Dpm',
	  'ParcelPost'                  => 'GND',
	  'ParcelSelect'                => 'GDR',
	//'ExpressMailInternational'    => 'I2DEam',
	//'PriorityMailInternational'   => 'I2Dam',
	//'FirstClassMailInternational' => 'I3D',
	);

	public $PackageMap = array( // for rate estimates, assume this set of options
	  '01' => 'FlatRateEnvelope',
	  '02' => 'Parcel',
	  '03' => 'IrregularParcel',
	  '04' => 'SmallFlatRateBox',
	  '21' => 'MediumFlatRateBox',
	  '25' => 'RegionalRateBoxA',
	  '24' => 'RegionalRateBoxB',
	);

	public $mailPieceShape = array(
	//'Card'                      => MPS_01,
	//'Letter'                    => MPS_02,
	  'FlatRateEnvelope'          => MPS_08,
	  'Flat'                      => MPS_03,
	  'Parcel'                    => MPS_04,
	  'LargeParcel'               => MPS_05,
	  'IrregularParcel'           => MPS_06,
	  'OversizedParcel'           => MPS_07,
	  'FlatRateLegalEnvelope'     => MPS_09,
	  'FlatRatePaddedEnvelope'    => MPS_10,
	  'FlatRateGiftCardEnvelope'  => MPS_11,
	  'FlatRateWindowEnvelope'    => MPS_12,
	  'FlatRateCardboardEnvelope' => MPS_13,
	  'SmallFlatRateEnvelope'     => MPS_14,
	  'SmallFlatRateBox'          => MPS_15,
	  'MediumFlatRateBox'         => MPS_16,
	  'LargeFlatRateBox'          => MPS_17,
	  'DVDFlatRateBox'            => MPS_18,
	  'LargeVideoFlatRateBox'     => MPS_19,
	  'RegionalRateBoxA'          => MPS_20,
	  'RegionalRateBoxB'          => MPS_21,
	);

	public $dialAZipCodes = array(
	  '10' => 'Invalid address',
	  '11' => 'Invalid zip code',
	  '12' => 'Invalid state code',
	  '13' => 'Invalid city',
	  '21' => 'Address not found',
	  '22' => 'Multiple matches, too ambiguious',
	  '25' => 'City, State and ZIP Code are valid, but street address is not a match',
	  '31' => 'Exact match',
	  '32' => 'Default match, more information may give a more specific +4',
	);

  	function __construct() {
		$this->keys[] = array('key' => 'MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER',	'default' => '',										'text' => MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_ENDICIA_PASS_PHRASE',   	'default' => '',										'text' => MODULE_SHIPPING_ENDICIA_PASS_PHRASE_DESC);
      	$this->keys[] = array('key' => 'MODULE_SHIPPING_ENDICIA_TEST_MODE',     	'default' => 'Test',									'text' => TEXT_PRODUCTION_OR_TEST_MODE_USED_FOR_TESTING_SHIPPING_LABELS);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_ENDICIA_PRINTER_TYPE',  	'default' => 'ZPLII',									'text' => SHIPPING_PRINTER_TYPE_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_ENDICIA_PRINTER_NAME',  	'default' => 'zebra',									'text' => SHIPPING_PRINTER_NAME_DESC);
	  	$this->keys[] = array('key' => 'MODULE_SHIPPING_ENDICIA_TYPES',         	'default' => '1DEam,1Dam,1Dpm,2Dam,3Dam,3Dpm,GND,GDR',	'text' => TEXT_SELECT_THE_SERVICES_TO_BE_OFFERED_BY_DEFAULT);
	  	parent::__construct();
  	}

  	function configure($key) {
    	switch ($key) {
	  		case 'MODULE_SHIPPING_ENDICIA_TEST_MODE':
			    $temp = array(
				  array('id' => 'Test', 'text' => TEXT_TEST),
				  array('id' => 'Prod', 'text' => TEXT_PRODUCTION),
			    );
			    $html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
			    break;
			case 'MODULE_SHIPPING_ENDICIA_PRINTER_TYPE':
			    $temp = array(
				  array('id' => 'EPL2', 'text' => 'EPL2'),
				  array('id' => 'ZPLII','text' => 'ZPLII'),
		//		  array('id' => 'GIF',  'text' => 'GIF'),
		//		  array('id' => 'JPEG', 'text' => 'JPEG'),
		//		  array('id' => 'PDF',  'text' => 'PDF'),
		//		  array('id' => 'PNG',  'text' => 'PNG'),
			    );
			    $html .= html_pull_down_menu(strtolower($key), $temp, constant($key));
			    break;
			case 'MODULE_SHIPPING_ENDICIA_TYPES':
			    $temp = array(
			      array('id' => '1DEam', 'text' => MODULE_SHIPPING_ENDICIA_1DM),
				  array('id' => '1Dam',  'text' => MODULE_SHIPPING_ENDICIA_1DA),
				  array('id' => '1Dpm',  'text' => MODULE_SHIPPING_ENDICIA_1DP),
				  array('id' => '2Dam',  'text' => MODULE_SHIPPING_ENDICIA_2DA),
				  array('id' => '2Dpm',  'text' => MODULE_SHIPPING_ENDICIA_2DP),
			      array('id' => '3Dam',  'text' => MODULE_SHIPPING_ENDICIA_3DA),
			      array('id' => '3Dpm',  'text' => MODULE_SHIPPING_ENDICIA_3DS),
			      array('id' => 'GND',   'text' => MODULE_SHIPPING_ENDICIA_GND),
				  array('id' => 'GDR',   'text' => MODULE_SHIPPING_ENDICIA_GDR),
		//		  array('id' => 'I2DEam','text' => MODULE_SHIPPING_ENDICIA_XDM),
		//		  array('id' => 'I2Dam', 'text' => MODULE_SHIPPING_ENDICIA_XPR),
		//		  array('id' => 'I3D',   'text' => MODULE_SHIPPING_ENDICIA_XPD),
			    );
			    $choices = array();
			    foreach ($temp as $value) {
				  $choices[] = html_checkbox_field(strtolower($key).'[]', $value['id'], ((strpos(constant($key), $value['id']) === false) ? false : true), '', $parameters = '') . ' ' . $value['text'];
			    }
			    $html = implode('<br />' . chr(10), $choices);
			    break;
	  		default:
	    		$html = parent::configure($key);
    	}
    	return $html;
  	}

	function update() {
	    foreach ($this->keys as $key) {
			$field = strtolower($key['key']);
			switch ($key['key']) {
		    	case 'MODULE_SHIPPING_ENDICIA_TYPES': // read the checkboxes
			  		$admin->DataBase->write_configure($key['key'], implode(',', $_POST[$field]));
			  		break;
				default:  // just write the value
			  		if (isset($_POST[$field])) $admin->DataBase->write_configure($key['key'], $_POST[$field]);
			}
		}
	}

// ***************************************************************************************************************
//								Endicia Address Validation Request
// ***************************************************************************************************************
  function validateAddress($address) { // this can be called with ajax or directly
	global $messageStack;
	$output = array();
	$xml  = '?input=<VERIFYADDRESS>';
	$xml .= '<COMMAND>ZIP1</COMMAND>';
	$xml .= '<SERIALNO>830413</SERIALNO>'; // Phreesoft, LLC common Dial-A-Zip validation login info for use only with this module
	$xml .= '<USER>830413</USER>';
	$xml .= '<PASSWORD>Phreedom_2012_LLC</PASSWORD>';
	$xml .= '<ADDRESS0>' . urlencode(remove_special_chars($address->ship_primary_name)) . '</ADDRESS0>';
	$xml .= '<ADDRESS1>' . urlencode(remove_special_chars($address->ship_contact)) . '</ADDRESS1>';
	$xml .= '<ADDRESS2>' . urlencode(remove_special_chars($address->ship_address1).' '.remove_special_chars($address->ship_address2)) . '</ADDRESS2>';
	$xml .= '<ADDRESS3>' . urlencode(strtoupper($address->ship_city_town).', '.strtoupper($address->ship_state_province).' '.strip_alphanumeric($address->ship_postal_code)) . '</ADDRESS3>';
	$xml .= '</VERIFYADDRESS>';
	if (($result = @file_get_contents(MODULE_SHIPPING_ENDICIA_DIAL_A_ZIP_URL . $xml)) === false)  throw new \core\classes\userException("can not open ". MODULE_SHIPPING_ENDICIA_DIAL_A_ZIP_URL . $xml);
	$result = substr($result, strpos($result, '>')+1);
	$result = str_replace('<Dial-A-ZIP_Response>',  '', trim($result));
	$result = str_replace('</Dial-A-ZIP_Response>', '', trim($result));
	$parts  = xml_to_object($result);
	if ($parts->ReturnCode == '31') {
	  $address->ship_contact        = '';
	  $address->ship_address1       = $parts->AddrLine1;
	  $address->ship_address2       = $parts->AddrLine2;
	  $address->ship_city_town      = $parts->City;
	  $address->ship_state_province = $parts->State;
	  $address->ship_postal_code    = $parts->ZIP5 . '-' . $parts->Plus4;
	  $response = array(
	    'result'    => 'success',
	    'xmlString' => '<address>'.object_to_xml($address).'</address>',
	    'message'   => 'The address will be corrected per results from Dial-A-Zip.',
//	    'debug'     => 'result = '.str_replace('<', '[', $result),
	  );
	} else {
	  $response = array(
		'result'  => 'error',
		'message' => sprintf(SHIPPING_ENDICIA_ADD_VAL_ERROR, $parts->ReturnCode, $this->dialAZipCodes[$parts->ReturnCode]),
//		'debug'   => 'result = '.str_replace('<', '[', $result),
	  );
	}
	return $response; // xml string response
  }

// ***************************************************************************************************************
//								Endicia Rate and Service Request
// ***************************************************************************************************************
  function quote($pkg) { // assumes only one package at a time
  	global $messageStack;
	if ($pkg->pkg_weight == 0) throw new \core\classes\userException(TEXT_SHIPMENT_WEIGHT_CANNOT_BE_ZERO);
	if ($pkg->ship_to_postal_code == '') throw new \core\classes\userException(SHIPPING_ENDICIA_ERROR_POSTAL_CODE);
	if ($pkg->pkg_weight > ENDICIA_MAX_SINGLE_BOX_WEIGHT) throw new \core\classes\userException(SHIPPING_ENDICIA_ERROR_TOO_HEAVY);
	$request = array(
	  'PostageRatesRequest' => array(
	    'RequesterID'  => MODULE_SHIPPING_ENDICIA_PARTNER_ID,
	    'CertifiedIntermediary' => array(
	      'AccountID'  => MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER,
	      'PassPhrase' => MODULE_SHIPPING_ENDICIA_PASS_PHRASE,
	    ),
	    'MailClass'      => ($pkg->ship_to_country_iso2<>'US') ? 'International' : 'Domestic',
	    'WeightOz'       => ceil($pkg->pkg_weight * 16),
	    'MailpieceShape' => $this->PackageMap[$pkg->pkg_type],
//	    'MailpieceDimensions' => array(
//		  '@attributes' => array(
//		    'Length' => $pkg->pkg_length,
//			'Width'  => $pkg->pkg_width,
//			'Height' => $pkg->pkg_height,
//		  ),
//		),
		'RegisteredMailValue' => number_format($pkg->total_amount,2),
		'Value' => number_format($pkg->total_amount,2),
	    'Services' => array(
	      '@attributes' => array(
	        'DeliveryConfirmation'    => 'OFF',
	        'SignatureConfirmation'   => 'OFF',
	        'COD'                     => $pkg->cod ? 'ON' : 'OFF',
	        'DeliveryConfirmation'    => 'ON', // set default to on
			'CertifiedMail'           => 'OFF',
			'ElectronicReturnReceipt' => 'OFF',
			'InsuredMail'             => 'OFF',
			'RestrictedDelivery'      => 'OFF',
			'ReturnReceipt'           => 'OFF',
			'AdultSignature'          => 'OFF',
			'AdultSignatureRestrictedDelivery' => 'OFF',
		  ),
	    ),
	    'FromPostalCode' => $pkg->ship_postal_code,
	    'ToPostalCode'   => $pkg->ship_to_postal_code,
	    'ToCountryCode'  => $pkg->ship_to_country_iso2,
		'Machinable'     => 'TRUE',
		'CODAmount'      => $pkg->cod ? $pkg->total_amount : '0',
		'InsuredValue'   => $pkg->insurance ? $pkg->total_amount : '0',

	  ),
	);

//echo 'Endicia XML Submit String:<br />'; print_r($request); echo '<br />'; return false;
	$arrRates = array();
	$url = (MODULE_SHIPPING_ENDICIA_TEST_MODE=='Prod') ? MODULE_SHIPPING_ENDICIA_WSDL_URL : MODULE_SHIPPING_ENDICIA_TEST_WSDL_URL;
	$client = new \SoapClient($url, array('trace'=>1));
	try {
	  $response = $client->CalculatePostageRates($request);
//echo 'Request <pre>'  . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  $user_choices = explode(',', str_replace(' ', '', MODULE_SHIPPING_ENDICIA_TYPES));
	  if ($response->PostageRatesResponse->Status == 0) {
		if (is_object($response->PostageRatesResponse->PostagePrice)) $response->PostageRatesResponse->PostagePrice = array($response->PostageRatesResponse->PostagePrice);
		if (is_array($response->PostageRatesResponse->PostagePrice)) foreach ($response->PostageRatesResponse->PostagePrice as $rateReply) {
		  $service = $this->EndiciaRateCodes[$rateReply->MailClass];
		  $total   = $rateReply->TotalAmount;
		  if (in_array($service, $user_choices)) {
		    $arrRates[$this->id][$service]['cost'] = $rateReply->TotalAmount;
		    $arrRates[$this->id][$service]['book'] = $rateReply->TotalAmount;
		    $arrRates[$this->id][$service]['note'] = '';
		  	if (function_exists('endicia_shipping_rate_calc')) {
			  $arrRates[$this->id][$service]['quote'] = endicia_shipping_rate_calc($arrRates[$this->id][$service]['book'], $arrRates[$this->id][$service]['cost'], $service);
			} else {
			  $arrRates[$this->id][$service]['quote']= $rateReply->TotalAmount;
			}
		  }
		}
	  } else {
	  	throw new \core\classes\userException(TEXT_ERROR.' ('.$response->PostageRatesResponse->Status.') '.$response->PostageRatesResponse->ErrorMessage);
	  }
	} catch (SoapFault $e) {
//echo 'Fault Request <pre>'  . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Fault Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  throw $e;
	}
// echo 'arrRates array = '; print_r($arrRates); echo '<br /><br />';
	return array('result' => 'success', 'rates' => $arrRates);
  }

// ***************************************************************************************************************
//								Endicia Buy Postage Request
// ***************************************************************************************************************
  function buyPostage() {
  	global $messageStack, $admin;
  	$amount = db_prepare_input($_POST['endicia_postage']);
  	if (!in_array($amount, array('10', '25', '100', '250', '500', '1000'))) {
  		throw new \core\classes\userException('The postage purchase amount submitted is an invalid amount!');
  	}
	$data  = array(
	  'RecreditRequest' => array(
  	    'RequesterID' => MODULE_SHIPPING_ENDICIA_PARTNER_ID,
  	    'RequestID' => time(),
  	    'CertifiedIntermediary' => array(
  	      'AccountID' => MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER,
  	      'PassPhrase' => MODULE_SHIPPING_ENDICIA_PASS_PHRASE,
  	    ),
  	    'RecreditAmount' => $amount,
  	  ),
	);
	$url = (MODULE_SHIPPING_ENDICIA_TEST_MODE=='Prod') ? MODULE_SHIPPING_ENDICIA_WSDL_URL : MODULE_SHIPPING_ENDICIA_TEST_WSDL_URL;
	$client = new \SoapClient($url, array('trace'=>1));
  	try {
	  $response = $client->BuyPostage($data);
  	  if ($response->RecreditRequestResponse->Status == 0) {
  		\core\classes\messageStack::add(sprintf(SHIPPING_ENDICIA_PURCHASE_SUCCESS_MSG, $admin->currencies->format($response->RecreditRequestResponse->CertifiedIntermediary->PostageBalance), $response->RecreditRequestResponse->CertifiedIntermediary->SerialNumber),'success');
  	  } else {
  		throw new \core\classes\userException(TEXT_ERROR.' ('.$response->RecreditRequestResponse->Status.') '.$response->RecreditRequestResponse->ErrorMessage);
	  }
  	} catch (SoapFault $exception) {
  	  throw new \core\classes\userException("SOAP error ({$exception->faultcode}) {$exception->faultstring}");
  	}
  	return true;
  }

// ***************************************************************************************************************
//								Endicia Change PassPhrase Request
// ***************************************************************************************************************
  function changePassPhrase() {
  	global $messageStack;
  	$old_pp = db_prepare_input($_POST['pass_phrase_current']);
  	$new_pp = db_prepare_input($_POST['pass_phrase_new']);
  	$dup_pp = db_prepare_input($_POST['pass_phrase_confirm']);
  	// error check
  	if ($old_pp <> MODULE_SHIPPING_ENDICIA_PASS_PHRASE) throw new \core\classes\userException(SHIPPING_ENDICIA_PASSPHRASE_OLD_NOT_MATCH);
  	if ($new_pp <> $dup_pp) throw new \core\classes\userException(SHIPPING_ENDICIA_PASSPHRASE_NEW_NOT_MATCH);
  	$data = array(
  	  'ChangePassPhraseRequest' => array(
  	    'RequesterID' => MODULE_SHIPPING_ENDICIA_PARTNER_ID,
  	    'RequestID' => time(),
  	    'CertifiedIntermediary' => array(
  	      'AccountID' => MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER,
  	      'PassPhrase' => MODULE_SHIPPING_ENDICIA_PASS_PHRASE,
  	    ),
  	    'NewPassPhrase' => $new_pp,
  	  ),
  	);
  	$url = (MODULE_SHIPPING_ENDICIA_TEST_MODE=='Prod') ? MODULE_SHIPPING_ENDICIA_WSDL_URL : MODULE_SHIPPING_ENDICIA_TEST_WSDL_URL;
  	$client = new \SoapClient($url, array('trace'=>1));
  	try {
	  $response = $client->ChangePassPhrase($data);
  	  if ($response->ChangePassPhraseRequestResponse->Status == 0) {
  	  	$admin->DataBase->write_configure('MODULE_SHIPPING_ENDICIA_PASS_PHRASE', $new_pp);
  		\core\classes\messageStack::add(SHIPPING_ENDICIA_PASSPHRASE_SUCCESS_MSG, 'success');
  	  } else {
  		throw new \core\classes\userException(TEXT_ERROR.' ('.$response->ChangePassPhraseRequestResponse->Status.') '.$response->ChangePassPhraseRequestResponse->ErrorMessage);
  	  }
  	} catch (SoapFault $exception) {
  	  throw new \core\classes\userException("SOAP error ({$exception->faultcode}) {$exception->faultstring}");
  	}
  }

// ***************************************************************************************************************
//								Endicia Label Request (domestic, single piece only)
// ***************************************************************************************************************
  function retrieveLabel($sInfo) {
	global $messageStack;
	$endicia_results = array();
	if (in_array($sInfo->ship_method, array('I2DEam','I2Dam','I3D'))) { // unsupported ship methods
	  throw new \core\classes\userException('The ship method requested is not supported by this tool presently. Please ship the package via a different tool.');
	}
	$labels = array();
	$xml = $this->FormatEndiciaShipRequest($sInfo);
//echo 'Endicia XML Label Submit String:'; print_r($xml); echo '<br />'; //return false;
//	$client = new SoapClient((MODULE_SHIPPING_ENDICIA_TEST_MODE=='Test')?PATH_TO_TEST_RATE_WSDL:PATH_TO_RATE_WSDL, array('trace'=>1));
	$url = (MODULE_SHIPPING_ENDICIA_TEST_MODE=='Prod') ? MODULE_SHIPPING_ENDICIA_WSDL_URL : MODULE_SHIPPING_ENDICIA_TEST_WSDL_URL;
	$client = new \SoapClient($url, array('trace'=>1));
	try {
	  $response = $client->GetPostageLabel($xml);
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
//echo 'label response array = '; print_r($response); echo '<br />';
	  if ($response->LabelRequestResponse->Status == 0) {
		$net_cost  = $response->LabelRequestResponse->FinalPostage;
		$book_cost = $response->LabelRequestResponse->FinalPostage;
		$del_date  = '';
		$tracking  = $response->LabelRequestResponse->TrackingNumber;
		$zone      = '';
		$label     = $response->LabelRequestResponse->Base64LabelImage;
		$endicia_results[] = array(
		  'ref_id'        => $sInfo->purchase_invoice_id,
		  'tracking'      => $tracking,
		  'book_cost'     => $book_cost,
		  'net_cost'      => $net_cost,
//		  'zone'          => '',
//		  'delivery_date' => '',
//		  'dim_weight'    => '',
//		  'billed_weight' => '',
		);
		if ($label) {
		  $date      = explode('-',$sInfo->ship_date);
		  $file_path = SHIPPING_DEFAULT_LABEL_DIR.$this->id.'/'.$date[0].'/'.$date[1].'/'.$date[2].'/';
		  validate_path($file_path);
		  $this->returned_label = $label;
		  $filename = $tracking.'.lpt'; // assume thermal printer
		  // decode label if necessary
//echo 'label raw = '.$label.'<br>';
//echo 'label decoded = '.base64_decode($label).'<br>';
//		  if (!in_array(MODULE_SHIPPING_ENDICIA_PRINTER_TYPE, array('EPL2','ZPLII'))) $label = base64_decode($label);
		  $label = base64_decode($label);
		  if (!$handle = @fopen($file_path . $filename, 'w')) throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, file_path . $filename));
		  if (!@fwrite($handle, $label)) throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, 	$file_path . $filename));
		  if (!@fclose($handle)) throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, $filename));
		  \core\classes\messageStack::add(sprintf(SHIPPING_ENDICIA_LABEL_STATUS, $tracking, $response->LabelRequestResponse->PostageBalance),'success');
		} else {
		  throw new \core\classes\userException('Error - No label found in return string.');
		}
	  } else {
	  	throw new \core\classes\userException(TEXT_ERROR.' ('.$response->LabelRequestResponse->Status.') '.$response->LabelRequestResponse->ErrorMessage);
	  }
	} catch (SoapFault $exception) {
//echo 'Fault Request <pre>'  . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Fault Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  throw new \core\classes\userException("Soap Error ({$exception->faultcode}) {$exception->faultstring}");
	}
	return $endicia_results;
  }

  function FormatEndiciaShipRequest($pkg) {
	$rateCodes       = array_flip($this->EndiciaRateCodes);
	$fromPostalCodes = explode('-', COMPANY_POSTAL_CODE);
	$toPostalCodes   = explode('-', $pkg->ship_postal_code);
	$data = array(
	  'LabelRequest' => array(
		'RequesterID'         => MODULE_SHIPPING_ENDICIA_PARTNER_ID,
		'AccountID'           => MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER,
		'PassPhrase'          => MODULE_SHIPPING_ENDICIA_PASS_PHRASE,
//		'Description'         => 'Sample Label',
		'PartnerCustomerID'   => MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER,
		'PartnerTransactionID'=> $pkg->purchase_invoice_id ? $pkg->purchase_invoice_id : time(),
  		'MailClass'           => $rateCodes[$pkg->ship_method],
		'MailpieceShape'      => $pkg->pkg_type,
  		'DateAdvance'         => '0',
		'WeightOz'            => ceil($pkg->package['weight'] * 16),
		'CostCenter'          => '0',
		'Value'               => $pkg->total_amount,
		'Stealth'             => 'TRUE',
  		'Services'            => array(
		  'DeliveryConfirmation'    => $pkg->delivery_confirmation ? 'ON' : 'OFF',
//		  'SignatureConfirmation'   => 'OFF',
//		  'CertifiedMail'           => 'OFF',
//		  'RestrictedDelivery'      => 'OFF',
//		  'ReturnReceipt'           => 'OFF',
//		  'ElectronicReturnReceipt' => 'OFF',
//		  'HoldForPickup'           => 'OFF',
//		  'OpenAndDistribute'       => 'OFF',
		  'COD'                     => $pkg->cod   ? 'ON' : 'OFF',
		  'InsuredMail'             => $pkg->ins_1 ? 'ON' : 'OFF',
		  'AdultSignature'          => 'OFF',
//		  'AdultSignatureRestrictedDelivery' => 'OFF',
		),
  		'ResponseOptions'=> array(
		  'PostagePrice' => 'TRUE',
		),
//		'OriginCountry'    => 'United States',
		'ToCompany'        => remove_special_chars($pkg->ship_primary_name),
		'ToName'           => remove_special_chars($pkg->ship_contact),
		'ToAddress1'       => remove_special_chars($pkg->ship_address1),
		'ToAddress2'       => remove_special_chars($pkg->ship_address2),
		'ToCity'           => strtoupper($pkg->ship_city_town),
		'ToState'          => ($pkg->ship_country_code == 'US') ? strtoupper($pkg->ship_state_province) : '',
		'ToPostalCode'     => $toPostalCodes[0],
		'ToZIP4'           => $toPostalCodes[1],
//		'ToCountry'        => $pkg->ship_country_code,
//		'ToCountryCode'    => $pkg->ship_country_code,
		'ToPhone'          => strip_alphanumeric($pkg->ship_telephone1),
		'ToEMail'          => $pkg->ship_email,
  		'FromCompany'      => COMPANY_NAME,
  		'FromName'         => (COMPANY_NAME == '') ? AR_CONTACT_NAME : '',
		'ReturnAddress1'   => COMPANY_ADDRESS1,
		'ReturnAddress2'   => COMPANY_ADDRESS2,
		'FromCity'         => COMPANY_CITY_TOWN,
		'FromState'        => COMPANY_ZONE,
		'FromPostalCode'   => $fromPostalCodes[0],
		'FromZIP4'         => $fromPostalCodes[1],
//		'FromCountry'      => COMPANY_COUNTRY, // blank is shipped from USA
		'FromPhone'        => strip_alphanumeric(COMPANY_TELEPHONE1),
		'FromEMail'        => COMPANY_EMAIL,
		'LabelSize'        => ENDICIA_LABEL_SIZE,
		'LabelType'        => 'Default',
		'ImageFormat'      => MODULE_SHIPPING_ENDICIA_PRINTER_TYPE,
//		'ImageResolution'  => '300', // defaults based on image type selected
  		'ImageRotation'    => 'None', // options are 'None', 'Rotate180'
	  	'CustomsQuantity1' => 0,
	  	'CustomsValue1'    => 0,
	  	'CustomsWeight1'   => 0,
	  	'CustomsQuantity2' => 0,
	  	'CustomsValue2'    => 0,
	  	'CustomsWeight2'   => 0,
	  	'CustomsQuantity3' => 0,
	  	'CustomsValue3'    => 0,
	  	'CustomsWeight3'   => 0,
	  	'CustomsQuantity4' => 0,
	  	'CustomsValue4'    => 0,
	  	'CustomsWeight4'   => 0,
	  	'CustomsQuantity5' => 0,
	  	'CustomsValue5'    => 0,
	  	'CustomsWeight5'   => 0,
  	  ),
	);
  	if ($pkg->package['length'] && $pkg->package['width'] && $pkg->package['height'])
  	  $data['LabelRequest']['MailpieceDimensions'] = array(
  		'Length' => $pkg->package['length'],
  		'Width'  => $pkg->package['width'],
  	  	'Height' => $pkg->package['height'],
  	);
  	if (MODULE_SHIPPING_ENDICIA_TEST_MODE=='Test') $data['LabelRequest']['Test'] = 'YES';
  	if ($pkg->cod) $data['LabelRequest']['CODAmount']       = number_format($pkg->total_amount, 2);
 	if ($pkg->ins_1) $data['LabelRequest']['InsuredValue']  = number_format($pkg->package['value'], 2);
 	if (RUBBERSTAMP1) $data['LabelRequest']['RubberStamp1'] = RUBBERSTAMP1;
 	if (RUBBERSTAMP2) $data['LabelRequest']['RubberStamp2'] = RUBBERSTAMP2;
 	if (RUBBERSTAMP3) $data['LabelRequest']['RubberStamp3'] = RUBBERSTAMP3;
  	return $data;
  }

// ***************************************************************************************************************
//								Endicia Label Refund Request
// ***************************************************************************************************************
  function deleteLabel($tracking_number = '') { // only one at a time allowed
  	global $messageStack;
	if (!$tracking_number) throw new \core\classes\userException(SHIPPING_DELETE_ERROR);
  	$xml  = "<RefundRequest>\n";
  	$xml .= xmlEntry('AccountID', MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER);
  	$xml .= xmlEntry('PassPhrase', MODULE_SHIPPING_ENDICIA_PASS_PHRASE);
  	$xml .= xmlEntry('Test', (MODULE_SHIPPING_ENDICIA_TEST_MODE=='Test')?'Y':'N');
  	$xml .= "  <RefundList>\n";
  	$xml .= xmlEntry('PICNumber', $tracking_number);
  	$xml .= "  </RefundList>\n";
  	$xml .= "</RefundRequest>\n";
  	$client = new \SoapClient(MODULE_SHIPPING_ENDICIA_ELS_URL, array('trace'=>1));
  	try {
	  $response = $client->RefundRequest($xml);
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  if ($response->RefundResponse->RefundList->PICNumber->IsAppoved == 'YES') {
	  	\core\classes\messageStack::add(sprintf(SHIPPING_ENDICIA_REFUND_MSG, $response->RefundResponse->RefundList->PICNumber, $response->RefundResponse->RefundList->PICNumber->IsApproved, $response->RefundResponse->RefundList->PICNumber->ErrorMsg), 'success');
	  } else {
	  	throw new \core\classes\userException(TEXT_ERROR.' '.$response->RefundResponse->RefundList->PICNumber->ErrorMsg);
  	  }
  	} catch (SoapFault $exception) {
//echo 'Fault Request <pre>'  . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Fault Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  throw new \core\classes\userException("SOAP error ({$exception->faultcode}) {$exception->faultstring}");
  	}
	return true;
  }

// ***************************************************************************************************************
//								Endicia Tracking Request
// ***************************************************************************************************************
  function trackPackages($track_date = '0000-00-00', $log_id = false) { // single tracking # or date range
	global $admin, $messageStack;
//	$result = array();
	if ($log_id) {
	  $shipments  = $admin->DataBase->query("select id, ref_id, deliver_date, actual_date, tracking_id, notes
		from ".TABLE_SHIPPING_LOG." where carrier = '$this->id' and id = '$log_id'");
	} else {
	  $start_date = $track_date;
	  $date = new \core\classes\DateTime($date_from);
	  $end_date   = $date->modify("+1 day")->format("Y-m-d");
	  $shipments  = $admin->DataBase->query("select id, ref_id, deliver_date, actual_date, tracking_id, notes
		from ".TABLE_SHIPPING_LOG." where carrier = '$this->id' and ship_date >= '$start_date' and ship_date < '$end_date'");
	}
	if ($shipments->fetch(\PDO::FETCH_NUM) == 0) return 'No records were found!';
	$xml  = "<StatusRequest>\n";
	$xml .= xmlEntry('AccountID',  MODULE_SHIPPING_ENDICIA_ACCOUNT_NUMBER);
	$xml .= xmlEntry('PassPhrase', MODULE_SHIPPING_ENDICIA_PASS_PHRASE);
	$xml .= xmlEntry('Test',       MODULE_SHIPPING_ENDICIA_TEST_MODE=='Test'?'Y':'N');
	$xml .= xmlEntry('FullStatus', 'N');
	while (!$shipments->EOF) {
	  $xml .= "  <StatusList>\n";
	  $xml .= xmlEntry('PICNumber', $shipments->fields['tracking_id']);
	  $xml .= "  </StatusList>\n";
	  $shipments->MoveNext();
	}
	$xml .= "</StatusRequest>\n";
  	$client = new \SoapClient(MODULE_SHIPPING_ENDICIA_ELS_URL, array('trace'=>1));
	try {
	  $response = $client->StatusRequest($xml);
//echo 'Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  if ($response->StatusResponse->Status == 0) {
	  	if (!is_array($response->StatusResponse->StatusList)) $response->StatusResponse->StatusList = array($response->StatusResponse->StatusList);
	  	foreach ($response->StatusResponse->StatusList as $status) {
	  	  $description = $status->PICNumber->Status;
	  	  $status_code = $status->PICNumber->StatusCode;
	  	  $message     = sprintf(SHIPPING_ENDICIA_TRACK_STATUS, $shipments->fields['ref_id'], $shipments->fields['tracking_id'], $description);
	  	  \core\classes\messageStack::add($message, $status_code=='D'?'success':'caution');
		}
	  } else {
	  	throw new \core\classes\userException(TEXT_ERROR.' ('.$response->StatusResponse->Status.') '.$response->StatusResponse->ErrorMsg);
	  }
	} catch (SoapFault $exception) {
//echo 'Error Request <pre>' . htmlspecialchars($client->__getLastRequest()) . '</pre>';
//echo 'Error Response <pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
	  throw new \core\classes\userException( "SOAP Error ({$exception->faultcode}) {$exception->faultstring}");
	}
	return $message;
  }

}
?>