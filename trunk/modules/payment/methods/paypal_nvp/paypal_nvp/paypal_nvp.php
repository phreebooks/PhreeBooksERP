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
//  Path: /modules/payment/methods/paypal_nvp/paypal_nvp.php
//
// PayPal Payment Pro Module
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_PAYMENT_PAYPAL_NVP_VERSION','3.2');
require_once(DIR_FS_MODULES . 'payment/classes/payment.php');

class paypal_nvp extends payment {
  public $code              = 'paypal_nvp'; // needs to match class name
  public $title 	        = MODULE_PAYMENT_CC_TEXT_CATALOG_TITLE;
  public $description       = MODULE_PAYMENT_PAYPAL_NVP_TEXT_DESCRIPTION;
  public $enable_encryption = 1; // set to field position of credit card to create hint, false to turn off encryption
  public $sort_order        = 3;
  
  public function __construct(){
  	global $order;
  	parent::__construct();
    if ((int)MODULE_PAYMENT_PAYPAL_NVP_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_PAYPAL_NVP_ORDER_STATUS_ID;
    }
	// save the information
	// Card numbers are not saved, instead keep the first and last four digits and fill middle with *'s
	$this->avs_codes = array(
		'A' => 'Address matches - Postal Code does not match.',
		'B' => 'Street address match, Postal code in wrong format. (International issuer)',
		'C' => 'Street address and postal code in wrong formats.',
		'D' => 'Street address and postal code match. (international issuer)',
		'E' => 'AVS Error.',
		'G' => 'Service not supported by non-US issuer.',
		'I' => 'Address information not verified by international issuer.',
		'M' => 'Street address and Postal code match. (international issuer)',
		'N' => 'No match on address (street) or postal code.',
		'O' => 'No response sent.',
		'P' => 'Postal code matches, street address not verified due to incompatible formats.',
		'R' => 'Retry, system unavailable or timed out.',
		'S' => 'Service not supported by issuer.',
		'U' => 'Address information is unavailable.',
		'W' => '9 digit postal code matches, address (street) does not match.',
		'X' => 'Exact AVS match.',
		'Y' => 'Address (street) and 5 digit postal code match.',
		'Z' => '5 digit postal code matches, address (street) does not match.'
	);
	$this->cvv_codes = array(
		'M' => 'CVV2 match',
		'N' => 'CVV2 No match',
		'P' => 'Not Processed',
		'S' => 'Issuer indicates that CVV2 data should be present on the card, but the merchant has indicated that the CVV2 data is not present on the card.',
		'U' => 'Issuer has not certified for CVV2 or issuer has not provided Visa with the CVV2 encryption keys.'
	);
	$this->key[] = array('key'=>'MODULE_PAYMENT_PAYPAL_NVP_USER_ID',           'default'=>'',    'text'=>MODULE_PAYMENT_PAYPAL_NVP_USER_ID_DESC);
	$this->key[] = array('key'=>'MODULE_PAYMENT_PAYPAL_NVP_PW',                'default'=>'',    'text'=>MODULE_PAYMENT_PAYPAL_NVP_PW_DESC);
	$this->key[] = array('key'=>'MODULE_PAYMENT_PAYPAL_NVP_SIG',               'default'=>'',    'text'=>MODULE_PAYMENT_PAYPAL_NVP_SIG_DESC);
	$this->key[] = array('key'=>'MODULE_PAYMENT_PAYPAL_NVP_TESTMODE',          'default'=>'live','text'=>MODULE_PAYMENT_PAYPAL_NVP_TESTMODE_DESC);
    $this->key[] = array('key'=>'MODULE_PAYMENT_PAYPAL_NVP_AUTHORIZATION_TYPE','default'=>'Sale','text'=>MODULE_PAYMENT_PAYPAL_NVP_AUTHORIZATION_TYPE_DESC);
  }

  function configure($key) {
    switch ($key) {
	  case 'MODULE_PAYMENT_PAYPAL_NVP_TESTMODE':
	    $temp = array(
		  array('id' => 'sandbox', 'text' => TEXT_TEST),
		  array('id' => 'live',    'text' => TEXT_PRODUCTION),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_PAYPAL_NVP_AUTHORIZATION_TYPE':
	    $temp = array(
		  array('id' => 'Authorization','text' => TEXT_AUTHORIZE),
		  array('id' => 'Sale',         'text' => TEXT_CAPTURE),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  default:
	    return parent::configure($key);
    }
  }

  function javascript_validation() {
    $js = 
	'  if (payment_method == "' . $this->code . '") {' . "\n" .
    '    var cc_owner  = document.getElementById("paypal_nvp_field_0").value +" "+document.getElementById("paypal_nvp_field_5").value;' . "\n" .
    '    var cc_number = document.getElementById("paypal_nvp_field_1").value;' . "\n" . 
    '    var cc_cvv    = document.getElementById("paypal_nvp_field_4").value;' . "\n" . 
    '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . sprintf(MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER, CC_OWNER_MIN_LENGTH) . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . sprintf(MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER, CC_NUMBER_MIN_LENGTH) . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" . 
    '    if (cc_cvv == "" || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
    '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_CVV . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" . 
    '  }' . "\n";
    return $js;
  }

  function selection() {
    global $order;
    for ($i = 1; $i < 13; $i++) {
      $j = ($i < 10) ? '0' . $i : $i;
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => $j . '-' . strftime('%B',mktime(0,0,0,$i,1,2000)));
    }
    $today = getdate();
    for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
      $expires_year[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }
	$selection = array(
	   'id'     => $this->code,
	   'page'   => $this->title,
	   'fields' => array(
			array(	'title' => MODULE_PAYMENT_PAYPAL_NVP_TEXT_CREDIT_CARD_OWNER,
					'field' => html_input_field('paypal_nvp_field_0', $order->paypal_nvp_field_0, 'size="12" maxlength="25"') . '&nbsp;' . html_input_field('paypal_nvp_field_5', $order->paypal_nvp_field_5, 'size="12" maxlength="25"')),
			array( 	'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
					'field' => html_input_field('paypal_nvp_field_1', $order->paypal_nvp_field_1)),
			array( 	'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
					'field' => html_pull_down_menu('paypal_nvp_field_2', $expires_month, $order->paypal_nvp_field_2) . '&nbsp;' . html_pull_down_menu('paypal_nvp_field_3', $expires_year, $order->paypal_nvp_field_3)),
			array(	'title' => MODULE_PAYMENT_CC_TEXT_CVV,
					'field' => html_input_field('paypal_nvp_field_4', $order->paypal_nvp_field_4, 'size="4" maxlength="4"')),
		));
    return $selection;
  }

  function pre_confirmation_check() {
    global $_POST, $messageStack;

	// if the card number has the blanked out middle number fields, it has been processed, show message that 
	// the charges were not processed through the merchant gateway and continue posting payment.
	if (strpos($this->field_1,'*') !== false) {
    	$messageStack->add(MODULE_PAYMENT_CC_NO_DUPS, 'caution');
		return false;
	}
    $result = $this->validate();
    $error  = '';
    switch ($result) {
      case -1:
      $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($this->cc_card_number, 0, 4));
      break;
      case -2:
      case -3:
      case -4:
      $error = TEXT_CCVAL_ERROR_INVALID_DATE;
      break;
      case false:
      $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
      break;
    }

    if ( ($result == false) || ($result < 1) ) {
      $messageStack->add($error . '<!-- ['.$this->code.'] -->', 'error');
      return true;
    }
	return false;
  }

  function before_process() {
    global $order, $db, $messageStack;

	// if the card number has the blanked out middle number fields, it has been processed, the message that 
	// the charges were not processed were set in pre_confirmation_check, just return to continue without processing.
	if (strpos($this->field_1, '*') !== false) return false;

	$order->info['cc_expires'] = $this->field_2 . $this->field_3;
    $order->info['cc_owner']   = $this->field_0 . ' ' . $this->field_5;
	$this->cc_card_owner       = $this->field_0 . ' ' . $this->field_5;
    $order->info['cc_cvv']     = $this->field_4;
    
	switch (substr($this->field_1, 0, 1)) {
	  case '3': $card_type = 'Amex';       break;
	  case '4': $card_type = 'Visa';       break;
	  case '5': $card_type = 'MasterCard'; break;
	  case '6': $card_type = 'Discover';   break;
	}
	// Set request-specific fields.
    $submit_data = array(
		'PAYMENTACTION'  => MODULE_PAYMENT_PAYPAL_NVP_AUTHORIZATION_TYPE,
		'AMT'            => $order->total_amount,
		'SHIPPINGAMT'    => $order->freight,
		'TAXAMT'         => $order->sales_tax ? $order->sales_tax : 0,
		'DESC'           => $order->description,
		'INVNUM'         => $order->purchase_invoice_id,
		'CREDITCARDTYPE' => $card_type,
		'ACCT'           => preg_replace('/ /', '', $this->field_1),
		'EXPDATE'        => $this->field_2 . $this->field_3,
		'CVV2'           => $this->field_4 ? $this->field_4 : '',
		'PAYERID'        => $order->bill_short_name,
		'FIRSTNAME'      => $this->field_0,
		'LASTNAME'       => $this->field_5,
		'STREET'         => str_replace('&', '-', substr($order->bill_address1, 0, 20)),
		'STREET2'        => str_replace('&', '-', substr($order->bill_address2, 0, 20)),
		'CITY'           => $order->bill_city_town,
		'STATE'          => $order->bill_state_province,
		'ZIP'            => preg_replace("/[^A-Za-z0-9]/", "", $order->bill_postal_code),
		'COUNTRYCODE'    => gen_get_country_iso_2_from_3($order->bill_country_code),
		'EMAIL'          => $order->bill_email,
		'PHONENUM'       => $order->bill_telephone,
		'CURRENCYCODE'   => DEFAULT_CURRENCY,
		'SHIPTONAME'     => $order->ship_primary_name,
		'SHIPTOSTREET'   => $order->ship_address1,
		'SHIPTOSTREET2'  => $order->ship_address2,
		'SHIPTOCITY'     => $order->ship_city_town,
		'SHIPTOSTATE'    => $order->ship_state_province,
		'SHIPTOZIP'      => preg_replace("/[^A-Za-z0-9]/", "", $order->ship_postal_code),
		'SHIPTOCOUNTRY'  => $order->ship_country_code,
		'SHIPTOPHONENUM' => $order->ship_telephone,
	);

    // concatenate the submission data and put into $data variable
	$data = ''; // initiate XML string
    while(list($key, $value) = each($submit_data)) {
    	if ($value <> '') $data .= '&' . $key . '=' . urlencode($value);
    }

// FOR TEST PURPOSES
$messageStack->add('Test transaction complete!', 'success');
return false;
// END FOR TEST

	// Execute the API operation; see the PPHttpPost function above.
	if (!$httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $data)) return true; // failed cURL

    $this->transaction_id = $httpParsedResponseAr['TRANSACTIONID'];
	if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
		$messageStack->add(sprintf(MODULE_PAYMENT_PAYPAL_NVP_SUCCESSE_CODE, $httpParsedResponseAr['ACK'], $this->transaction_id, $this->cvv_codes[$httpParsedResponseAr['CVV2MATCH']]), 'success');
		$messageStack->add('Address verification results: ' . $this->avs_codes[$httpParsedResponseAr['AVSCODE']], 'success');
//echo 'Success response:'; print_r($httpParsedResponseAr); echo '<br>';
		return false;
	}
    $messageStack->add(MODULE_PAYMENT_PAYPAL_NVP_DECLINE_CODE . $httpParsedResponseAr['L_ERRORCODE0'] . ': ' . urldecode($httpParsedResponseAr['L_LONGMESSAGE0']) . ' - ' . MODULE_PAYMENT_CC_TEXT_DECLINED_MESSAGE, 'error');
//echo 'Failed response:'; print_r($httpParsedResponseAr); echo '<br>';
	return true;
  }

  function PPHttpPost($methodName_, $nvpStr_) {
	// Set up your API credentials, PayPal end point, and API version.
    $submit_data = array(
		'METHOD'    => $methodName_,
		'VERSION'   => '52.0',
		'PWD'       => MODULE_PAYMENT_PAYPAL_NVP_PW,
		'USER'      => MODULE_PAYMENT_PAYPAL_NVP_USER_ID,
		'SIGNATURE' => MODULE_PAYMENT_PAYPAL_NVP_SIG,
		'IPADDRESS' => get_ip_address(),
	);

	$data = ''; // initiate XML string
    while(list($key, $value) = each($submit_data)) {
    	if ($value <> '') $data .= '&' . $key . '=' . urlencode($value);
    }
	$data .= substr($data, 1) . $nvpStr_; // build the submit string

	if("sandbox" === MODULE_PAYMENT_PAYPAL_NVP_TESTMODE || "beta-sandbox" === MODULE_PAYMENT_PAYPAL_NVP_TESTMODE) {
		$API_Endpoint = MODULE_PAYMENT_PAYPAL_NVP_SANDBOX_SIG_URL;
	} else {
		$API_Endpoint = MODULE_PAYMENT_PAYPAL_NVP_LIVE_SIG_URL;
	}
	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	// Turn off the server and peer verification (TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//echo 'string = ' . $data . '<br>';
	$httpResponse = curl_exec($ch);

	if(!$httpResponse) {
		$messageStack->add('XML Read Error (cURL) #' . curl_errno($ch) . '. Description = ' . curl_error($ch),'error');
		return false;
//		exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
	}
	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);
	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if(sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	}
	if (0 == sizeof($httpParsedResponseAr) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		$messageStack->add('PayPal Response Error.','error');
		return false;
//		exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	}
	return $httpParsedResponseAr;
  }
}
?>
