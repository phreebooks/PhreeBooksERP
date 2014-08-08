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
//  Path: /modules/payment/methods/authorizenet/authorizenet.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
define('MODULE_PAYMENT_AUTHORIZENET_VERSION','3.3');
require_once(DIR_FS_MODULES . 'payment/classes/payment.php');
// authorize.net AIM payment method class
// Portions copyright Copyright 2003-2010 Zen Cart Development Team

class authorizenet extends payment {
  public $code        = 'authorizenet'; // needs to match class name
  public $title       = MODULE_PAYMENT_AUTHORIZENET_TEXT_TITLE;
  public $description = MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION;
  public $sort_order  = 5;
  public $enabled;   // $enabled determines whether this module shows or not... in catalog.
  var $delimiter = '|';//$delimiter determines what separates each field of returned data from authorizenet
  // $encapChar denotes what character is used to encapsulate the response fields
  var $encapChar = '*';
  var $authorize = '';
  var $commErrNo = 0;
  var $commError = '';
  // debug content var
  var $reportable_submit_data = array();

  public function __construct(){
  	parent::__construct();
	$this->key[] = array('key' => 'MODULE_PAYMENT_AUTHORIZENET_LOGIN',              'default' => ''										, 'text' => MODULE_PAYMENT_AUTHORIZENET_LOGIN_DESC);
    $this->key[] = array('key' => 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY',             'default' => 'Test'									, 'text' => MODULE_PAYMENT_AUTHORIZENET_TXNKEY_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_AUTHORIZENET_MD5HASH',            'default' => '*Set A Hash Value at AuthNet Admin*'	, 'text' => MODULE_PAYMENT_AUTHORIZENET_MD5HASH_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE',           'default' => 'Test'									, 'text' => MODULE_PAYMENT_AUTHORIZENET_TESTMODE_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE', 'default' => 'Capture'								, 'text' => MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_AUTHORIZENET_USE_CVV',            'default' => '1'									, 'text' => MODULE_PAYMENT_AUTHORIZENET_USE_CVV_DESC);
  }

  function configure($key) {
    switch ($key) {
	  case 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE':
	    $temp = array(
		  array('id' => 'Test',       'text' => TEXT_TEST),
		  array('id' => 'Production', 'text' => TEXT_PRODUCTION),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE':
	    $temp = array(
		  array('id' => 'Authorize', 'text' => TEXT_AUTHORIZE),
		  array('id' => 'Capture',   'text' => TEXT_CAPTURE),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_AUTHORIZENET_USE_CVV':
	    $temp = array(
		  array('id' => '0', 'text' => TEXT_NO),
		  array('id' => '1', 'text' => TEXT_YES),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  default:
	    return parent::configure($key);
    }
  }

  function javascript_validation() {
    $js = '  if (payment_method == "' . $this->code . '") {' . "\n" .
    '    var cc_owner = document.getElementById("authorizenet_field_0").value;' . "\n" .
    '    var cc_number = document.getElementById("authorizenet_field_1").value;' . "\n";
    if (MODULE_PAYMENT_AUTHORIZENET_USE_CVV == '1')  {
      $js .= '    var cc_cvv = document.getElementById("authorizenet_field_4").value;' . "\n";
    }
    $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . sprintf(MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER, CC_OWNER_MIN_LENGTH) . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . sprintf(MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER, CC_NUMBER_MIN_LENGTH) . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n";
    if (MODULE_PAYMENT_AUTHORIZENET_USE_CVV == '1')  {
      $js .= '    if (cc_cvv == "" || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
      '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_CVV . '";' . "\n" .
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
     $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
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
			    'field' => html_input_field('authorizenet_field_0', $this->field_0)),
	    array(  'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
		     	'field' => html_input_field('authorizenet_field_1', $this->field_1)),
	    array(	'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
			    'field' => html_pull_down_menu('authorizenet_field_2', $expires_month, $this->field_2) . '&nbsp;' . html_pull_down_menu('authorizenet_field_3', $expires_year, $this->field_3)),
		array ( 'title' => MODULE_PAYMENT_CC_TEXT_CVV,
				'field' => html_input_field('authorizenet_field_4', $this->field_4, 'size="4" maxlength="4"' . ' id="' . $this->code . '-cc-cvv"' ) . ' ' . '<a href="javascript:popupWindow(\'' . html_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . TEXT_MORE_INFO . '</a>',)
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
    global $response, $db, $order, $messageStack;

	// if the card number has the blanked out middle number fields, it has been processed, the message that 
	// the charges were not processed were set in pre_confirmation_check, just return to continue without processing.
	if (strpos($this->field_1, '*') !== false) return false;

    $order->info['cc_expires'] = $this->field_2 . $this->field_3;
    $order->info['cc_owner']   = $this->field_0;
	$this->cc_card_owner       = $this->field_0;
    $order->info['cc_cvv']     = $this->field_4;

//    $order->info['cc_type']    = $_POST['cc_type'];
    $sessID = session_id();

    // DATA PREPARATION SECTION
    unset($submit_data);  // Cleans out any previous data stored in the variable

    // Create a string that contains a listing of products ordered for the description field
    $description = $order->description;

    // Create a variable that holds the order time
    $order_time = date("F j, Y, g:i a");
/*
    // Calculate the next expected order id (adapted from code written by Eric Stamper - 01/30/2004 Released under GPL)
    $last_order_id = $db->Execute("select * from " . TABLE_ORDERS . " order by orders_id desc limit 1");
    $new_order_id = $last_order_id->fields['orders_id'];
    $new_order_id = ($new_order_id + 1);

    // add randomized suffix to order id to produce uniqueness ... since it's unwise to submit the same order-number twice to authorize.net
    $new_order_id = (string)$new_order_id . '-' . create_random_value(6);
*/
    // Populate an array that contains all of the data to be sent to Authorize.net
    $submit_data = array(
	  'x_login'              => trim(MODULE_PAYMENT_AUTHORIZENET_LOGIN),
	  'x_tran_key'           => trim(MODULE_PAYMENT_AUTHORIZENET_TXNKEY),
	  'x_relay_response'     => 'FALSE', // AIM uses direct response, not relay response
	  'x_delim_data'         => 'TRUE',
	  'x_delim_char'         => $this->delimiter,  // The default delimiter is a comma
	  'x_encap_char'         => $this->encapChar,  // The divider to encapsulate response fields
	  'x_version'            => '3.1',  // 3.1 is required to use CVV codes
	  'x_type'               => MODULE_PAYMENT_AUTHORIZENET_AUTHORIZATION_TYPE == 'Authorize' ? 'AUTH_ONLY': 'AUTH_CAPTURE',
	  'x_method'             => 'CC',
	  'x_amount'             => $order->total_amount,
	  'x_currency_code'      => DEFAULT_CURRENCY,
	  'x_card_num'           => preg_replace('/ /', '', $this->field_1),
	  'x_exp_date'           => $order->info['cc_expires'],
	  'x_card_code'          => $order->info['cc_cvv'],
	  'x_email_customer'     => MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER == '1' ? 'TRUE': 'FALSE',
	  'x_email_merchant'     => MODULE_PAYMENT_AUTHORIZENET_EMAIL_MERCHANT == '1' ? 'TRUE': 'FALSE',
	  'x_cust_id'            => $order->bill_short_name,
	  'x_invoice_num'        => (MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Test' ? 'TEST-' : '') . $order->purchase_invoice_id,
	  'x_first_name'         => $order->bill_first_name,
	  'x_last_name'          => $order->bill_last_name,
	  'x_company'            => str_replace('&', '-', $order->bill_primary_name),
	  'x_address'            => str_replace('&', '-', substr($order->bill_address1, 0, 20)),
	  'x_city'               => $order->bill_city_town,
	  'x_state'              => $order->bill_state_province,
	  'x_zip'                => preg_replace("/[^A-Za-z0-9]/", "", $order->bill_postal_code),
	  'x_country'            => $order->bill_country_code,
	  'x_phone'              => $order->bill_telephone1,
	  'x_email'              => $order->bill_email,
	  'x_ship_to_first_name' => $order->ship_first_name,
	  'x_ship_to_last_name'  => $order->ship_last_name,
	  'x_ship_to_address'    => $order->ship_address1,
	  'x_ship_to_city'       => $order->ship_city_town,
	  'x_ship_to_state'      => $order->ship_state_province,
	  'x_ship_to_zip'        => preg_replace("/[^A-Za-z0-9]/", "", $order->ship_postal_code),
	  'x_ship_to_country'    => $order->ship_country_code,
	  'x_description'        => $description,
	  'x_recurring_billing'  => 'NO',
	  'x_customer_ip'        => get_ip_address(),
	  'x_po_num'             => date('M-d-Y h:i:s'), //$order->info['po_number'],
	  'x_freight'            => $order->freight,
	  'x_tax_exempt'         => 'FALSE', /* 'TRUE' or 'FALSE' */
	  'x_tax'                => ($order->sales_tax) ? $order->sales_tax : 0,
	  'x_duty'               => '0',
	  'x_allow_partial_Auth' => 'FALSE', // unable to accept partial authorizations at this time
	  'Date'                 => $order_time,
	  'IP'                   => get_ip_address(),
	  'Session'              => $sessID,
	);
//echo 'submit_data = '; print_r($submit_data); echo '<br>';
    unset($response);
    $response             = $this->_sendRequest($submit_data);
    $response_code        = $response[0];
    $response_text        = $response[3];
    $this->auth_code      = $response[4];
    $this->transaction_id = $response[6];
    $this->avs_response   = $response[5];
    $this->ccv_response   = $response[38];
    $response_to_customer = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');

    $response['Expected-MD5-Hash'] = $this->calc_md5_response($response[6], $response[9]);
    $response['HashMatchStatus']   = ($response[37] == $response['Expected-MD5-Hash']) ? 'PASS' : 'FAIL';

    // If the MD5 hash doesn't match, then this transaction's authenticity cannot be verified.
    // Thus, order will be placed in Pending status
    if ($response['HashMatchStatus'] != 'PASS' && defined('MODULE_PAYMENT_AUTHORIZENET_MD5HASH') && MODULE_PAYMENT_AUTHORIZENET_MD5HASH != '') {
      $messageStack->add(MODULE_PAYMENT_AUTHORIZENET_TEXT_AUTHENTICITY_WARNING, 'caution');
    }

    // If the response code is not 1 (approved) then redirect back to the payment page with the appropriate error message
    if ($response_code != '1') {
      $messageStack->add($response_to_customer . ' - ' . MODULE_PAYMENT_CC_TEXT_DECLINED_MESSAGE, 'error');
      return true;
    }
    if ($response[88] != '') {
      $_SESSION['payment_method_messages'] = $response[88];
    }
  }

  function _sendRequest($submit_data) {
    global $request_type;
    // Populate an array that contains all of the data to be sent to Authorize.net
    $submit_data = array_merge(array(
	  'x_login'          => trim(MODULE_PAYMENT_AUTHORIZENET_LOGIN),
	  'x_tran_key'       => trim(MODULE_PAYMENT_AUTHORIZENET_TXNKEY),
	  'x_relay_response' => 'FALSE',
	  'x_delim_data'     => 'TRUE',
	  'x_delim_char'     => $this->delimiter,  // The default delimiter is a comma
	  'x_encap_char'     => $this->encapChar,  // The divider to encapsulate response fields
	  'x_version'        => '3.1',  // 3.1 is required to use CVV codes
	  ), $submit_data);

    if(MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Test') {
      $submit_data['x_test_request'] = 'TRUE';
    }

    // set URL
    $url     = 'https://secure.authorize.net/gateway/transact.dll';
    $devurl  = 'https://test.authorize.net/gateway/transact.dll';
    $dumpurl = 'https://developer.authorize.net/param_dump.asp';
    $certurl = 'https://certification.authorize.net/gateway/transact.dll';
    if (defined('AUTHORIZENET_DEVELOPER_MODE')) {
      if (AUTHORIZENET_DEVELOPER_MODE == 'on')      $url = $devurl;
      if (AUTHORIZENET_DEVELOPER_MODE == 'echo')    $url = $dumpurl;
      if (AUTHORIZENET_DEVELOPER_MODE == 'certify') $url = $certurl;
    }

    // concatenate the submission data into $data variable after sanitizing to protect delimiters
    $data = '';
    while(list($key, $value) = each($submit_data)) {
      if ($key != 'x_delim_char' && $key != 'x_encap_char') {
        $value = str_replace(array($this->delimiter, $this->encapChar,'"',"'",'&amp;','&', '='), '', $value);
      }
      $data .= $key . '=' . urlencode($value) . '&';
    }
    // Remove the last "&" from the string
    $data = substr($data, 0, -1);

    // prepare a copy of submitted data for error-reporting purposes
    $this->reportable_submit_data = $submit_data;
    $this->reportable_submit_data['x_login'] = '*******';
    $this->reportable_submit_data['x_tran_key'] = '*******';
    if (isset($this->reportable_submit_data['x_card_num'])) $this->reportable_submit_data['x_card_num'] = str_repeat('X', strlen($this->reportable_submit_data['x_card_num'] - 4)) . substr($this->reportable_submit_data['x_card_num'], -4);
    if (isset($this->reportable_submit_data['x_card_code'])) $this->reportable_submit_data['x_card_code'] = '****';
    $this->reportable_submit_data['url'] = $url;

    // Post order info data to Authorize.net via CURL - Requires that PHP has CURL support installed

    // Send CURL communication
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, ($request_type == 'SSL' ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ));
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); /* compatibility for SSL communications on some Windows servers (IIS 5.0+) */
    if (CURL_PROXY_REQUIRED == '1') {
      $this->proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
      curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $this->proxy_tunnel_flag);
      curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
      curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
    }

    $this->authorize = curl_exec($ch);
    $this->commError = curl_error($ch);
    $this->commErrNo = curl_errno($ch);

    $this->commInfo = @curl_getinfo($ch);
    curl_close ($ch);

    // if in 'echo' mode, dump the returned data to the browser and stop execution
    if ((defined('AUTHORIZENET_DEVELOPER_MODE') && AUTHORIZENET_DEVELOPER_MODE == 'echo')) {
      echo $this->authorize . ($this->commErrNo != 0 ? '<br />' . $this->commErrNo . ' ' . $this->commError : '') . '<br />';
      die('Press the BACK button in your browser to return to the previous page.');
    }

    // parse the data received back from the gateway, taking into account the delimiters and encapsulation characters
    $stringToParse = $this->authorize;
    if (substr($stringToParse,0,1) == $this->encapChar) $stringToParse = substr($stringToParse,1);
    $stringToParse = preg_replace('/.{*}' . $this->encapChar . '$/', '', $stringToParse);
    $response = explode($this->encapChar . $this->delimiter . $this->encapChar, $stringToParse);

    return $response;
  }
  /**
   * Calculate validity of response
   */
  function calc_md5_response($trans_id = '', $amount = '') {
    if ($amount == '' || $amount == '0') $amount = '0.00';
    $validating = md5(MODULE_PAYMENT_AUTHORIZENET_MD5HASH . MODULE_PAYMENT_AUTHORIZENET_LOGIN . $trans_id . $amount);
    return strtoupper($validating);
  }

/*
  function _doRefund($oID, $amount = 0) {
    global $db, $messageStack;
    $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $proceedToRefund = true;
    $refundNote = strip_tags(gen_db_input($_POST['refnote']));
    if (isset($_POST['refconfirm']) && $_POST['refconfirm'] != 'on') {
      $messageStack->add(MODULE_PAYMENT_AUTHORIZENET_TEXT_REFUND_CONFIRM_ERROR, 'error');
      $proceedToRefund = false;
    }
    if (isset($_POST['buttonrefund']) && $_POST['buttonrefund'] == MODULE_PAYMENT_AUTHORIZENET_ENTRY_REFUND_BUTTON_TEXT) {
      $refundAmt = (float)$_POST['refamt'];
      $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_REFUNDED_ORDER_STATUS_ID;
      if ($refundAmt == 0) {
        $messageStack->add(MODULE_PAYMENT_AUTHORIZENET_TEXT_INVALID_REFUND_AMOUNT, 'error');
        $proceedToRefund = false;
      }
    }
    if (isset($_POST['cc_number']) && trim($_POST['cc_number']) == '') {
      $messageStack->add(MODULE_PAYMENT_AUTHORIZENET_TEXT_CC_NUM_REQUIRED_ERROR, 'error');
    }
    if (isset($_POST['trans_id']) && trim($_POST['trans_id']) == '') {
      $messageStack->add(MODULE_PAYMENT_AUTHORIZENET_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToRefund = false;
    }

    // Submit refund request to gateway
    if ($proceedToRefund) {
      $submit_data = array('x_type' => 'CREDIT',
                           'x_card_num' => trim($_POST['cc_number']),
                           'x_amount' => number_format($refundAmt, 2),
                           'x_trans_id' => trim($_POST['trans_id'])
                           );
      unset($response);
      $response = $this->_sendRequest($submit_data);
      $response_code = $response[0];
      $response_text = $response[3];
      $response_alert = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $this->reportable_submit_data['Note'] = $refundNote;

      if ($response_code != '1') {
        $messageStack->add($response_alert, 'error');
      } else {
        // Success, so save the results
        $sql_data_array = array('orders_id' => $oID,
                                'orders_status_id' => (int)$new_order_status,
                                'date_added' => 'now()',
                                'comments' => 'REFUND INITIATED. Trans ID:' . $response[6] . ' ' . $response[4]. "\n" . ' Gross Refund Amt: ' . $response[9] . "\n" . $refundNote,
                                'customer_notified' => 0
                             );
        db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS  . "
                      set orders_status = '" . (int)$new_order_status . "'
                      where orders_id = '" . (int)$oID . "'");
        $messageStack->add(sprintf(MODULE_PAYMENT_AUTHORIZENET_TEXT_REFUND_INITIATED, $response[9], $response[6]), 'success');
        return true;
      }
    }
    return false;
  }

  function _doVoid($oID, $note = '') {
    global $db, $messageStack;

    $new_order_status = (int)MODULE_PAYMENT_AUTHORIZENET_REFUNDED_ORDER_STATUS_ID;
    if ($new_order_status == 0) $new_order_status = 1;
    $voidNote = strip_tags(zen_db_input($_POST['voidnote'] . $note));
    $voidAuthID = trim(strip_tags(zen_db_input($_POST['voidauthid'])));
    $proceedToVoid = true;
    if (isset($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_AUTHORIZENET_ENTRY_VOID_BUTTON_TEXT) {
      if (isset($_POST['voidconfirm']) && $_POST['voidconfirm'] != 'on') {
        $messageStack->add(MODULE_PAYMENT_AUTHORIZENET_TEXT_VOID_CONFIRM_ERROR, 'error');
        $proceedToVoid = false;
      }
    }
    if ($voidAuthID == '') {
      $messageStack->add(MODULE_PAYMENT_AUTHORIZENET_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
      $proceedToVoid = false;
    }
    // Populate an array that contains all of the data to be sent to gateway
    $submit_data = array('x_type' => 'VOID',
                         'x_trans_id' => trim($voidAuthID) );
    // Submit void request to Gateway
    if ($proceedToVoid) {
      $response = $this->_sendRequest($submit_data);
      $response_code = $response[0];
      $response_text = $response[3];
      $response_alert = $response_text . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
      $this->reportable_submit_data['Note'] = $voidNote;

      if ($response_code != '1' || ($response[0]==1 && $response[2] == 310) ) {
        $messageStack->add($response_alert, 'error');
      } else {
        // Success, so save the results
        $sql_data_array = array('orders_id' => (int)$oID,
                                'orders_status_id' => (int)$new_order_status,
                                'date_added' => 'now()',
                                'comments' => 'VOIDED. Trans ID: ' . $response[6] . ' ' . $response[4] . "\n" . $voidNote,
                                'customer_notified' => 0
                             );
        db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $db->Execute("update " . TABLE_ORDERS  . "
                      set orders_status = '" . (int)$new_order_status . "'
                      where orders_id = '" . (int)$oID . "'");
        $messageStack->add(sprintf(MODULE_PAYMENT_AUTHORIZENET_TEXT_VOID_INITIATED, $response[6], $response[4]), 'success');
        return true;
      }
    }
    return false;
  }
*/

}
?>