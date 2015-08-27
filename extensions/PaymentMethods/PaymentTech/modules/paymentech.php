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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/services/payment/modules/paymenttech.php
//
/**
 * paymentech payment method class
 * @copyright Portions Copyright 2007 s_mack
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 */
/**
 * Paymentech Payment Module
 * You must have SSL active on your server to be compliant with merchant TOS
 * At time of release this has only been tested on a Canadian merchant store, using
 * Paymentech service via VersaPay in Vancouver, BC. However, it may work with
 * any services provider that uses the Chase bank Orbital/Paymentech solution
 *
 */
class paymentech {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code;
  /**
   * $title is the displayed name for this payment method
   *
   * @var string
   */
  var $title;
  /**
   * $description is a soft name for this payment method
   *
   * @var string
   */
  var $description;
  /**
   * $enabled determines whether this module shows or not... in catalog.
   *
   * @var boolean
   */
  var $enabled;
  /**
   * $response tracks response information returned from the AIM gateway
   *
   * @var string/array
   */
  function paymentech() {
    global $order;
    $this->code = 'paymentech';
    // Payment module title in Admin
    $this->title = MODULE_PAYMENT_PAYMENTECH_TEXT_TITLE;
    if (MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_CAD == '' && MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_USD == '') {
      $this->title .=  '<span class="alert"> (Not Configured)</span>';
    } elseif (MODULE_PAYMENT_PAYMENTECH_TESTMODE == 'Test') {
      $this->title .= '<span class="alert"> (in Testing mode)</span>';
    }
    $this->description = MODULE_PAYMENT_PAYMENTECH_TEXT_DESCRIPTION; // Descriptive Info about module in Admin
    $this->enabled = ((MODULE_PAYMENT_PAYMENTECH_STATUS == 'True') ? true : false); // Whether the module is installed or not
    $this->sort_order = MODULE_PAYMENT_PAYMENTECH_SORT_ORDER; // Sort Order of this payment option on the customer payment page

    if ((int)MODULE_PAYMENT_PAYMENTECH_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_PAYMENTECH_ORDER_STATUS_ID;
    }

	// save the information
	// Card numbers are not saved, instead keep the first and last four digits and fill middle with *'s
	$card_number = trim($_POST['paymentech_field_1']);
	$card_number = substr($card_number, 0, 4) . '********' . substr($card_number, -4);
	$this->payment_fields = implode(':',array($_POST['paymentech_field_0'], $card_number, $_POST['paymentech_field_2'], $_POST['paymentech_field_3'], $_POST['paymentech_field_4']));

	$this->AVSRespCode = array(
		'1 ' => "No address supplied",
		'2 ' => "Bill-to address did not pass Auth Host edit checks",
		'3 ' => "AVS not performed",
		'4 ' => "Issuer does not participate in AVS",
		'R ' => "Issuer does not participate in AVS",
		'5 ' => "Edit-error - AVS data is invalid",
		'6 ' => "System unavailable or time-out",
		'7 ' => "Address information unavailable",
		'8 ' => "Transaction Ineligible for AVS",
		'9 ' => "Zip Match / Zip4 Match / Locale match",
		'A ' => "Zip Match / Zip 4 Match / Locale no match",
		'B ' => "Zip Match / Zip 4 no Match / Locale match",
		'C ' => "Zip Match / Zip 4 no Match / Locale no match",
		'D ' => "Zip No Match / Zip 4 Match / Locale match",
		'E ' => "Zip No Match / Zip 4 Match / Locale no match",
		'F ' => "Zip No Match / Zip 4 No Match / Locale match",
		'G ' => "No match at all",
		'H ' => "Zip Match / Locale match",
		'J ' => "Issuer does not participate in Global AVS",
		'JA' => "International street address and postal match",
		'JB' => "International street address match. Postal code not verified.",
		'JC' => "International street address and postal code not verified.",
		'JD' => "International postal code match. Street address not verified.",
		'M1' => "Cardholder name matches",
		'M2' => "Cardholder name, billing address, and postal code matches",
		'M3' => "Cardholder name and billing code matches",
		'M4' => "Cardholder name and billing address match",
		'M5' => "Cardholder name incorrect, billing address and postal code match",
		'M6' => "Cardholder name incorrect, billing address matches",
		'M7' => "Cardholder name incorrect, billing address matches",
		'M8' => "Cardholder name, billing address and postal code are all incorrect",
		'N3' => "Address matches, ZIP not verified",
		'N4' => "Address and ZIP code match (International only)",
		'N5' => "Address not verified (International only)",
		'N6' => "Address and ZIP code match (International only)",
		'N7' => "ZIP matches, address not verified",
		'N8' => "Address and ZIP code match (International only)",
		'UK' => "Unknown",
		'X ' => "Zip Match / Zip 4 Match / Address Match",
		'Y ' => "Not Performed",
		'Z ' => "Zip Match / Locale no match");

	$this->CVV2RespCode = array(
		'M' => "CVV Match",
		'N' => "CVV No match",
		'P' => "Not processed",
		'S' => "Should have been present",
		'U' => "Unsupported by issuer",
		'I' => "Invalid",
		'Y' => "Invalid");

  }
  /**
   * JS validation which does error-checking of data-entry if this module is selected for use
   * (Number, Owner, and CVV Lengths)
   *
   * @return string
   */
  function javascript_validation() {
    $js = '  if (payment_method == "' . $this->code . '") {' . "\n" .
    '    var cc_owner = document.getElementById("paymentech_field_0").value;' . "\n" .
    '    var cc_number = document.getElementById("paymentech_field_1").value;' . "\n";
    if (MODULE_PAYMENT_PAYMENTECH_USE_CVV == 'True')  {
      $js .= '    var cc_cvv = document.getElementById("paymentech_field_4").value;' . "\n";
    }
    $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_PAYMENTECH_TEXT_JS_CC_OWNER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_PAYMENTECH_TEXT_JS_CC_NUMBER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n";
    if (MODULE_PAYMENT_PAYMENTECH_USE_CVV == 'True')  {
      $js .= '    if (cc_cvv == "" || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
      '      error_message = error_message + "' . MODULE_PAYMENT_PAYMENTECH_TEXT_JS_CC_CVV . '";' . "\n" .
      '      error = 1;' . "\n" .
      '    }' . "\n" ;
    }
    $js .= '  }' . "\n";

    return $js;
  }
  /**
   * Display Credit Card Information Submission Fields on the Checkout Payment Page
   *
   * @return array
   */
  function selection() {
    global $order;

    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
    }

    $today = getdate();
    for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
      $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }
    $selection = array('id' => $this->code,
	 'module' => MODULE_PAYMENT_PAYMENTECH_TEXT_CATALOG_TITLE,
	 'fields' => array(	array('title' => TEXT_CREDIT_CARD_OWNER. ': ',
							  'field' => html_input_field('paymentech_field_0', $order->paymentech_field_0)),
						array('title' => TEXT_CREDIT_CARD_NUMBER. ': ',
							  'field' => html_input_field('paymentech_field_1', $order->paymentech_field_1)),
						array('title' => TEXT_CREDIT_CARD_EXPIRATION_DATE. ': ',
							  'field' => html_pull_down_menu('paymentech_field_2', $expires_month, $order->paymentech_field_2) . '&nbsp;' . html_pull_down_menu('paymentech_field_3', $expires_year, $order->paymentech_field_3)),
		));
    if (MODULE_PAYMENT_PAYMENTECH_USE_CVV == 'True') {
      $selection['fields'][] = array('title' => MODULE_PAYMENT_PAYMENTECH_TEXT_CVV,
								 'field' => html_input_field('paymentech_field_4', $order->paymentech_field_4, 'size="4", maxlength="4"'));
    }
    return $selection;
  }
  /**
   * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   *
   */
  function pre_confirmation_check() {
    global $_POST, $messageStack;

	// if the card number has the blanked out middle number fields, it has been processed, show message that
	// the charges were not processed through the merchant gateway and continue posting payment.
	if (strpos($_POST['paymentech_field_1'],'*') !== false) throw new \core\classes\userException(MODULE_PAYMENT_PAYMENTECH_NO_DUPS);

    include(DIR_FS_MODULES . 'general/classes/cc_validation.php');

    $cc_validation = new cc_validation();
    $result = $cc_validation->validate($_POST['paymentech_field_1'], $_POST['paymentech_field_2'], $_POST['paymentech_field_3'], $_POST['paymentech_field_4']);
    $error = '';
    switch ($result) {
      	case -1:
      		$error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
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

    if ( ($result == false) || ($result < 1) ) throw new \core\classes\userException($error . '<!-- ['.$this->code.'] -->');

    $this->cc_card_type = $cc_validation->cc_type;
    $this->cc_card_number = $cc_validation->cc_number;
    $this->cc_cvv2 = $_POST['paymentech_field_4'];
    $this->cc_expiry_month = $cc_validation->cc_expiry_month;
    $this->cc_expiry_year = $cc_validation->cc_expiry_year;
	return false;
  }
  /**
   * Store the CC info to the order and process any results that come back from the payment gateway
   * @throws \core\classes\userException
   */
  function before_process() {
    global $order, $admin, $messageStack;

	// if the card number has the blanked out middle number fields, it has been processed, the message that
	// the charges were not processed were set in pre_confirmation_check, just return to continue without processing.
	if (strpos($_POST['paymentech_field_1'], '*') !== false)  throw new \core\classes\userException(MODULE_PAYMENT_PAYMENTECH_NO_DUPS);

    $order->info['cc_expires'] = $_POST['paymentech_field_2'] . $_POST['paymentech_field_3'];
    $order->info['cc_owner'] = $_POST['paymentech_field_0'];
	$this->cc_card_owner = $_POST['paymentech_field_0'];
    $order->info['cc_cvv'] = $_POST['paymentech_field_4'];

    // Create a string that contains a listing of products ordered for the description field
    $description = $order->description;

	// Generate the XML file to be sent to Paymentech
	if (MODULE_PAYMENT_PAYMENTECH_TESTMODE == 'Test') {
		$MerchantID = MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_TEST;
	} else {
		switch (DEFAULT_CURRENCY) {
			case 'USD': $MerchantID = MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_USD; break;
			case 'CAD': $MerchantID = MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_CAD; break;
		}
	}

	$post_string = "
		<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<Request>
			<NewOrder>
				<IndustryType>EC</IndustryType>
				<MessageType>" . (MODULE_PAYMENT_PAYMENTECH_AUTHORIZATION_TYPE == 'Authorize' ? 'A' : 'AC') . "</MessageType>
				<BIN>" . MODULE_PAYMENT_PAYMENTECH_BIN . "</BIN>
				<MerchantID>" . $MerchantID . "</MerchantID>
				<TerminalID>" . MODULE_PAYMENT_PAYMENTECH_TERMINAL_ID . "</TerminalID>
				<AccountNum>" . $_POST['paymentech_field_1'] . "</AccountNum>
				<Exp>" . $order->info['cc_expires'] . "</Exp>
				<CurrencyCode>" . (DEFAULT_CURRENCY == 'USD' ? '840' : '124') . "</CurrencyCode>
				<CurrencyExponent>2</CurrencyExponent>
				<CardSecValInd>" . ($this->cc_cvv2 ? 1 : 9 ) . "</CardSecValInd>
				<CardSecVal>" . ($this->cc_cvv2 ? $this->cc_cvv2 : '') . "</CardSecVal>
				<AVSzip>" . preg_replace("/[^A-Za-z0-9]/", "", $order->bill_postal_code) . "</AVSzip>
				<AVSaddress1>" . substr($order->bill_address1, 0, 20) . "</AVSaddress1>"
				. ( $order->bill_address2 ? '
				<AVSaddress2>' . $order->bill_address2 . '</AVSaddress2>' : '' ) . "
				<AVScity>" . $order->bill_city_town . "</AVScity>
				<AVSstate>" . $order->bill_state_province . "</AVSstate>
				<AVSphoneNum>" . $order->bill_telephone . "</AVSphoneNum>
				<AVSname>" . $this->cc_card_owner . "</AVSname>
				<AVScountryCode>" . gen_get_country_iso_2_from_3($order->bill_country_code) . "</AVScountryCode>
				<AVSDestzip>" . preg_replace("/[^A-Za-z0-9]/", "", $order->ship_postal_code) . "</AVSDestzip>
				<AVSDestaddress1>" . $order->ship_address1 . "</AVSDestaddress1>"
				 . ( $order->ship_address2 ? '
				<AVSDestaddress2>' . $order->ship_address2 . '</AVSDestaddress2>' : '' ) . "
				<AVSDestcity>" . $order->ship_city_town . "</AVSDestcity>
				<AVSDeststate>" . $order->ship_state_province . "</AVSDeststate>
				<AVSDestphoneNum>" . $order->ship_telephone . "</AVSDestphoneNum>
				<AVSDestname>" . $order->ship_primary_name . "</AVSDestname>
				<AVSDestcountryCode>" . gen_get_country_iso_2_from_3($order->ship_country_code) . "</AVSDestcountryCode>
				<OrderID>" . $order->purchase_invoice_id . "</OrderID>
				<Amount>" . ($order->total_amount * pow(10, $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places'])) . "</Amount>
			</NewOrder>
		</Request>
	";

	$header  = "POST /AUTHORIZE HTTP/1.0\r\n";                //HTTP/1.1 should work fine also
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-type: application/PTI41\r\n";
	$header .= "Content-length: " . strlen($post_string) . "\r\n";
	$header .= "Content-transfer-encoding: text\r\n";
	$header .= "Request-number: 1\r\n";
	$header .= "Document-type: Request\r\n";
	$header .= "Merchant-id: " . $MerchantID . "\r\n\r\n";
//	$header .= "Connection: close \r\n\r\n";                        //Must have two CR/LF's here
	$header .= $post_string;

    // SEND DATA BY CURL SECTION
    // Post order info data to Paymentech gateway, make sure you have cURL support installed

    if (MODULE_PAYMENT_PAYMENTECH_TESTMODE == 'Test') {
		$url = MODULE_PAYMENT_PAYMENTECH_TEST_URL_PRIMARY;
	} else {
		$url = MODULE_PAYMENT_PAYMENTECH_PRODUCTION_URL_PRIMARY;
	}

//echo 'transmit xml = '; echo htmlspecialchars($header); echo '<br><br>';

	$GetPost = 'POST';
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_HEADER, false);                  // You are providing a header manually so turn off auto header generation
    curl_setopt($ch, CURLOPT_VERBOSE, false);


//*
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);          // The following two options are necessary to properly set up SSL
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
//*/

/*
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if ($GetPost == 'POST') {
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $header);
    }
*/

    if (CURL_PROXY_REQUIRED == 'True') {
      curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, true);
      curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
      curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
    }

	$authorize = curl_exec($ch);
	// Check for curl errors
	$curlerrornum = curl_errno($ch);
	$curlerror = curl_error($ch);
	curl_close ($ch);
	if ($curlerrornum) throw new \core\classes\userException("XML Read Error (cURL) #'$curlerrornum' Description = $curlerror");
//echo 'response xml = ' . htmlspecialchars($authorize) . '<br><br>';

	// since the response is only one level deep, we can do a simple parse
	$authorize = trim($authorize);
	$authorize = substr($authorize, strpos($authorize, '<NewOrderResp>') + 14); // remove up to and including the container tag
	$authorize = substr($authorize, 0, strpos($authorize, '</NewOrderResp>')); // remove from end container tag on
	$results = array();
	$runaway_loop_counter = 0;
	while ($authorize) {
		$key = substr($authorize, strpos($authorize, '<') + 1, strpos($authorize, '>') - 1);
		$authorize = substr($authorize, strpos($authorize, '>') + 1); // remove start tag
		$value = substr($authorize, 0, strpos($authorize, '<')); // until start of end tag
		$authorize = substr($authorize, strpos($authorize, '>') + 1); // remove end tag
		$results[$key] = $value;
		if ($runaway_loop_counter++ > 1000) break;
	}

//echo 'RespCode = ' . $results['RespCode'] . '<br>';
//echo 'AVSRespCode = ' . $results['AVSRespCode'] . '<br>';
//echo 'CVV2RespCode = ' . $results['CVV2RespCode'] . '<br>';
//echo 'TxRefNum = ' . $results['TxRefNum'] . '<br><br>';

	if ($results['ProcStatus'] == 0) { //initial gateway test passed
		if ($results['ApprovalStatus'] == 1) { //Gateway returned approved
			$this->auth_code = $results['AuthCode'];
			$this->transaction_id = $results['TxRefNum'];
		    $messageStack->add($results['StatusMsg'] . ' - Approval code: ' . $this->auth_code . ' --> CVV2 results: ' . $this->CVV2RespCode[$results['CVV2RespCode']], 'success');
		    $messageStack->add('Address verification results: ' . $this->AVSRespCode[$results['AVSRespCode']], 'success');
/* DELETE ME */ return true; // force a fail to not post
		    return false;
		} else {
			throw new \core\classes\userException(sprintf(MODULE_PAYMENT_PAYMENTECH_TEXT_DECLINED_MESSAGE, $results['StatusMsg']));
		}
	}
	//gateway test failed
	throw new \core\classes\userException(MODULE_PAYMENT_PAYMENTECH_TEXT_GATEWAY_ERROR);
  }
  /**
   * Post-process activities.
   *
   * @return boolean
   */
  function after_process() {
    return false;
  }
  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $admin;
    if (!isset($this->_check)) {
      $check_query = $admin->DataBase->query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYMENTECH_STATUS'");
      $this->_check = $check_query->fetch(\PDO::FETCH_NUM);
    }
    return $this->_check;
  }
  /**
   * Install the payment module and its configuration settings
   *
   */
  function install() {
    global $admin;
    $admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Paymentech Module', 'MODULE_PAYMENT_PAYMENTECH_STATUS', 'True', 'Do you want to accept Paymentech payments?', '6', '0', 'cfg_select_option(array(\'True\', \'False\'), ', now())");
	$admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_PAYMENTECH_TESTMODE', 'Test', 'Transaction mode used for processing orders.', '6', '0', 'cfg_select_option(array(\'Test\', \'Production\'), ', now())");
	$admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID (Test Account)', 'MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_TEST', '', 'Your Paymentech assigned Merchant ID (6 or 12 digit). Leave blank if you do not have a test account. This is only used when in testing mode.', '6', '0', now())");
	$admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID (USD Account)', 'MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_USD', '', 'Your Paymentech assigned Merchant ID (6 or 12 digit). Leave blank if you do not have USD account.', '6', '0', now())");
    $admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID (CAD Account)', 'MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_CAD', '', 'Your Paymentech assigned Merchant ID (6 or 12 digit). Leave blank if you do not have a CAD account.', '6', '0', now())");
    $admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Bin Number', 'MODULE_PAYMENT_PAYMENTECH_BIN', '000002', 'Bin Number assigned by Paymentech.', '6', '0', now())");
    $admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Terminal ID', 'MODULE_PAYMENT_PAYMENTECH_TERMINAL_ID', '001', 'Most are 001. Only change if instructed by Paymentech', '6', '0', now())");
    $admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Authorization Type', 'MODULE_PAYMENT_PAYMENTECH_AUTHORIZATION_TYPE', 'Authorize/Capture', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?', '6', '0', 'cfg_select_option(array(\'Authorize\', \'Authorize/Capture\'), ', now())");
    $admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Request CVV Number', 'MODULE_PAYMENT_PAYMENTECH_USE_CVV', 'True', 'Do you want to ask the customer for the card\'s CVV number', '6', '0', 'cfg_select_option(array(\'True\', \'False\'), ', now())");
    $admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYMENTECH_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
	$admin->DataBase->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Currencies Accepted', 'MODULE_PAYMENT_PAYMENTECH_CURRENCIES', 'USD only', 'Accept Canadian dollars, US dollars, or both? If you have a Merchant ID set up for both then the currency will matched appropriately.', '6', '0', 'cfg_select_option(array(\'CAD only\', \'USD only\', \'CAD and USD\'), ', now())");
  }
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $admin;
    $admin->DataBase->exec("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array(
	  'MODULE_PAYMENT_PAYMENTECH_STATUS',
	  'MODULE_PAYMENT_PAYMENTECH_TESTMODE',
	  'MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_TEST',
	  'MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_USD',
	  'MODULE_PAYMENT_PAYMENTECH_MERCHANT_ID_CAD',
	  'MODULE_PAYMENT_PAYMENTECH_BIN',
	  'MODULE_PAYMENT_PAYMENTECH_TERMINAL_ID',
	  'MODULE_PAYMENT_PAYMENTECH_AUTHORIZATION_TYPE',
	  'MODULE_PAYMENT_PAYMENTECH_USE_CVV',
	  'MODULE_PAYMENT_PAYMENTECH_SORT_ORDER',
	  'MODULE_PAYMENT_PAYMENTECH_CURRENCIES');
  }

}

?>