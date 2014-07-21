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
//  Path: /modules/payment/methods/nova_xml/nova_xml.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_PAYMENT_NOVA_XML_VERSION','3.3');
require_once(DIR_FS_MODULES . 'payment/classes/payment.php');
// Elevon Payment Module
class nova_xml extends payment {
  public $code        = 'nova_xml'; // needs to match class name
  public $title 	  = MODULE_PAYMENT_NOVA_XML_TEXT_TITLE;
  public $description = MODULE_PAYMENT_NOVA_XML_TEXT_DESCRIPTION;
  public $sort_order  = 2;
    
  public function __construct(){
  	parent::__construct();
    global $order;
	$this->enable_encryption = 1; // set to field position of credit card to create hint, false to turn off encryption
	// Card numbers are not saved, instead keep the first and last four digits and fill middle with *'s
	$this->def_deposit_id = (substr(trim($this->field_1), 0, 2) == '37' ? 'AX' : 'CC') . date('Ymd');

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
	
    $this->key[] = array('key'=>'MODULE_PAYMENT_NOVA_XML_MERCHANT_ID',       'default'=>'', 				'text'=>MODULE_PAYMENT_NOVA_XML_MERCHANT_ID_DESC);
    $this->key[] = array('key'=>'MODULE_PAYMENT_NOVA_XML_USER_ID',           'default'=>'', 				'text'=>MODULE_PAYMENT_NOVA_XML_USER_ID_DESC);
    $this->key[] = array('key'=>'MODULE_PAYMENT_NOVA_XML_PIN',               'default'=>'', 				'text'=>MODULE_PAYMENT_NOVA_XML_PIN_DESC);
    $this->key[] = array('key'=>'MODULE_PAYMENT_NOVA_XML_TESTMODE',          'default'=>'Production',		'text'=>MODULE_PAYMENT_NOVA_XML_TESTMODE_DESC);
    $this->key[] = array('key'=>'MODULE_PAYMENT_NOVA_XML_AUTHORIZATION_TYPE','default'=>'Authorize/Capture','text'=>MODULE_PAYMENT_NOVA_XML_AUTHORIZATION_TYPE_DESC);
  }

  function configure($key) {
    switch ($key) {
	  case 'MODULE_PAYMENT_NOVA_XML_TESTMODE':
	    $temp = array(
		  array('id' => 'Test',       'text' => TEXT_TEST),
		  array('id' => 'Production', 'text' => TEXT_PRODUCTION),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_NOVA_XML_AUTHORIZATION_TYPE':
	    $temp = array(
		  array('id' => 'Authorize',         'text' => TEXT_AUTHORIZE),
		  array('id' => 'Authorize/Capture', 'text' => TEXT_CAPTURE),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  default:
	    return parent::configure($key);
    }
  }

  function javascript_validation() {
    $js = 
	'  if (payment_method == "' . $this->code . '") {' . "\n" .
    '    var cc_owner  = document.getElementById("nova_xml_field_0").value;' . "\n" .
    '    var cc_number = document.getElementById("nova_xml_field_1").value;' . "\n" .
    '    var cc_cvv    = document.getElementById("nova_xml_field_4").value;' . "\n" . 
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
  /**
   * Display Credit Card Information Submission Fields on the Checkout Payment Page
   *
   * @return array
   */
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
	    array(  'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
			    'field' => html_input_field('nova_xml_field_0', $this->field_0)),
	    array(  'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
		     	'field' => html_input_field('nova_xml_field_1', $this->field_1)),
	    array(	'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
			    'field' => html_pull_down_menu('nova_xml_field_2', $expires_month, $this->field_2) . '&nbsp;' . html_pull_down_menu('nova_xml_field_3', $expires_year, $this->field_3)),
		array ( 'title' => MODULE_PAYMENT_CC_TEXT_CVV,
				'field' => html_input_field('nova_xml_field_4', $this->field_4, 'size="4" maxlength="4"' . ' id="' . $this->code . '-cc-cvv"' ) . ' ' . '<a href="javascript:popupWindow(\'' . html_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . TEXT_MORE_INFO . '</a>',)
	  ));
    return $selection;
  }
  /**
   * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   *
   */
  function pre_confirmation_check() {
    global $messageStack;

	// if the card number has the blanked out middle number fields, it has been processed, show message that 
	// the charges were not processed through the merchant gateway and continue posting payment.
	if (strpos($this->field_1, '*') !== false) {
    	$messageStack->add(MODULE_PAYMENT_CC_NO_DUPS, 'caution');
		return false;
	}

    $result = $this->validate();
    $error = '';
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

    if (($result == false) || ($result < 1)) {
      $messageStack->add($error . '<!-- ['.$this->code.'] -->', 'error');
      return true;
    }

    $this->cc_cvv2         = $this->field_4;
	return false;
  }
  /**
   * Store the CC info to the order and process any results that come back from the payment gateway
   *
   */
  function before_process() {
    global $order, $db, $messageStack;
	// if the card number has the blanked out middle number fields, it has been processed, the message that 
	// the charges were not processed were set in pre_confirmation_check, just return to continue without processing.
	if (strpos($this->field_1, '*') !== false) return false;

    $order->info['cc_expires'] = $this->field_2 . substr($this->field_3, -2);
    $order->info['cc_owner']   = $this->field_0;
	$this->cc_card_owner       = $this->field_0;
    $order->info['cc_cvv']     = $this->field_4;
    // Create a string that contains a listing of products ordered for the description field
    $description = $order->description;
    // Populate an array that contains all of the data to be sent to Nova (their xml string is one level)
    $submit_data = array(
		'ssl_transaction_type'   => ((MODULE_PAYMENT_NOVA_XML_AUTHORIZATION_TYPE == 'Authorize') ? 'CCAUTHONLY' : 'CCSALE'),
		'ssl_merchant_id'        => MODULE_PAYMENT_NOVA_XML_MERCHANT_ID, 
		'ssl_pin'                => MODULE_PAYMENT_NOVA_XML_PIN, 
		'ssl_user_id'            => MODULE_PAYMENT_NOVA_XML_USER_ID, 
		'ssl_amount'             => $order->total_amount,
		'ssl_salestax'           => ($order->sales_tax) ? $order->sales_tax : 0,
		'ssl_card_number'        => preg_replace('/ /', '', $this->field_1),
		'ssl_exp_date'           => $order->info['cc_expires'],
		'ssl_cvv2cvc2_indicator' => $this->field_4 ? '1' : '9', // if cvv2 exists, present else not present
		'ssl_cvv2cvc2'           => $this->field_4 ? $this->field_4 : '',
		'ssl_description'        => $description,
		'ssl_invoice_number'     => (MODULE_PAYMENT_NOVA_XML_TESTMODE == 'Test' ? 'TEST-' : '') . $order->purchase_invoice_id,
        'ssl_customer_code'      => str_replace('&', '-', $order->bill_short_name),
		'ssl_company'            => str_replace('&', '-', $order->bill_primary_name),
		'ssl_first_name'         => $order->bill_first_name, // passed with credit card form
		'ssl_last_name'          => $order->bill_last_name, // passed with credit card form
		'ssl_avs_address'        => str_replace('&', '-', substr($order->bill_address1, 0, 20)), // maximum of 20 characters per spec
		'ssl_address2'           => str_replace('&', '-', substr($order->bill_address2, 0, 20)),
		'ssl_city'               => $order->bill_city_town,
		'ssl_state'              => $order->bill_state_province,
		'ssl_avs_zip'            => preg_replace("/[^A-Za-z0-9]/", "", $order->bill_postal_code),
		'ssl_country'            => $order->bill_country_code,
		'ssl_phone'              => $order->bill_telephone1,
		'ssl_email'              => $order->bill_email ? $order->bill_email : COMPANY_EMAIL,
		'ssl_ship_to_company'    => $order->ship_primary_name,
		'ssl_ship_to_first_name' => $order->ship_first_name,
		'ssl_ship_to_last_name'  => $order->ship_last_name,
		'ssl_ship_to_address1'   => $order->ship_address1,
		'ssl_ship_to_address2'   => $order->ship_address2,
		'ssl_ship_to_city'       => $order->ship_city_town,
		'ssl_ship_to_state'      => $order->ship_state_province,
		'ssl_ship_to_zip'        => preg_replace("/[^A-Za-z0-9]/", "", $order->ship_postal_code),
		'ssl_ship_to_country'    => $order->ship_country_code,
		'ssl_ship_to_phone'      => $order->ship_telephone,
/* The following are not used at this time
		'ssl_email_header' => 'Not Used',
		'ssl_email_apprvl_header_html => 'Not Used',
		'ssl_email_decl_header_html => 'Not Used',
		'ssl_email_footer => 'Not Used',
		'ssl_email_apprvl_footer_html => 'Not Used',
		'ssl_email_decl_footer_html => 'Not Used',
		'ssl_do_customer_email => 'FALSE', // set up in admin on Virtual Merchant website
		'ssl_do_merchant_email => 'FALSE', // set up in admin on Virtual Merchant website
		'ssl_merchant_email' => COMPANY_EMAIL,
		'ssl_header_color' => '',
		'ssl_text_color' => '',
		'ssl_background_color' => '',
		'ssl_table_color' => '',
		'ssl_link_color' => '',
*/
		'ssl_show_form'          => 'FALSE',
/* The following are ignored when ssl_show_form = FALSE
		'ssl_header_html' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_footer_html' => 'Not Used', // set up in admin on Virtual Merchant website
*/
		'ssl_result_format'      => 'ASCII'
/* The following are ignored when ssl_result_format = ASCII
		'ssl_receipt_header_html' => 'Not Used', 		// set up in admin on Virtual Merchant website
		'ssl_receipt_apprvl_header_html' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_decl_header_html' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_footer_html' => 'Not Used', 		// set up in admin on Virtual Merchant website
		'ssl_receipt_apprvl_footer_html' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_decl_footer_html' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_link_method' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_apprvl_method' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_decl_method' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_link_url' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_apprvl_post_url' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_decl_post_url' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_apprvl_get_url' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_decl_get_url' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_error_url' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_link_text' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_apprvl_text' => 'Not Used', // set up in admin on Virtual Merchant website
		'ssl_receipt_decl_text' => 'Not Used', // set up in admin on Virtual Merchant website
*/
    );

    if (MODULE_PAYMENT_NOVA_XML_TESTMODE == 'Test') {
      $submit_data['ssl_test_mode'] = 'TRUE';
    }

    // concatenate the submission data and put into $data variable
	$data = '?xmldata=<txn>'; // initiate XML string
    while(list($key, $value) = each($submit_data)) {
      if ($value <> '') $data .= '<' . urlencode($key) . '>' . urlencode($value) . '</' . urlencode($key) . '>';
    }
	$data .= '</txn>'; // terminate XML string

    // SEND DATA BY CURL SECTION
    // Post order info data to Nova, make sure you have cURL support installed
//echo 'sending data = '.$data.'<br>';
	$url     = 'https://www.myvirtualmerchant.com/VirtualMerchant/processxml.do' . $data;
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20); // times out after 20 seconds 
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	$authorize    = curl_exec($ch); 
	$curlerrornum = curl_errno($ch);
	$curlerror    = curl_error($ch);
	curl_close ($ch);
	if ($curlerrornum) { 
		$messageStack->add('XML Read Error (cURL) #' . $curlerrornum . '. Description = ' . $curlerror,'error');
		return true;
	}
	// since the response is only one level deep, we can do a simple parse
	$authorize = trim($authorize);
	$authorize = substr($authorize, strpos($authorize, '<txn>') + 5); // remove up to and including the container tag
	$authorize = substr($authorize, 0, strpos($authorize, '</txn>')); // remove from end container tag on
	$results   = array();
	$runaway_loop_counter = 0;
	while ($authorize) {
		$key       = substr($authorize, strpos($authorize, '<') + 1, strpos($authorize, '>') - 1);
		$authorize = substr($authorize, strpos($authorize, '>') + 1); // remove start tag
		$value     = substr($authorize, 0, strpos($authorize, '<')); // until start of end tag
		$authorize = substr($authorize, strpos($authorize, '>') + 1); // remove end tag
		$results[$key] = $value;
		if ($runaway_loop_counter++ > 1000) break;
	}
//echo 'receive data = '; print_r($results); echo '<br>';
	
    $this->transaction_id = $results['ssl_txn_id'];
    $this->auth_code = $results['ssl_approval_code'];

    // If the response code is not 0 (approved) then redirect back to the payment page with the appropriate error message
    if ($results['errorCode']) {
      $messageStack->add('Decline Code #' . $results['errorCode'] . ': ' . $results['errorMessage'] . ' - ' . MODULE_PAYMENT_CC_TEXT_DECLINED_MESSAGE, 'error');
	  return true;
    } elseif ($results['ssl_result'] == '0') {
		$messageStack->add($results['ssl_result_message'] . ' - Approval code: ' . $this->auth_code . ' --> CVV2 results: ' . $this->cvv_codes[$results['ssl_cvv2_response']], 'success');
		$messageStack->add('Address verification results: ' . $this->avs_codes[$results['ssl_avs_response']], 'success');
		return false;
	}
	$messageStack->add($results['ssl_result_message'] . ' - ' . MODULE_PAYMENT_CC_TEXT_DECLINED_MESSAGE, 'error');
	return true;
  }
}
?>