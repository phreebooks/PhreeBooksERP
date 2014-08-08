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
//  Path: /modules/payment/methods/linkpoint_api/linkpoint_api.php
//
// Revision history
// 2009-08-01 - Author: Harry Lu
// 2010-08-17 - Modified by PhreeSoft
// 2011-07-01 - Added version number for revision control
define('MODULE_PAYMENT_LINKPOINT_API_VERSION','3.3');
require_once(DIR_FS_MODULES . 'payment/classes/payment.php');
@define('MODULE_PAYMENT_LINKPOINT_API_CODE_DEBUG', 'off'); // debug for programmer use only

class linkpoint_api extends payment {
  public $code        = 'linkpoint_api'; // needs to match class name
  public $title       = MODULE_PAYMENT_LINKPOINT_API_TEXT_TITLE;
  public $description = MODULE_PAYMENT_LINKPOINT_API_TEXT_DESCRIPTION;
  public $sort_order  = 8;
  public $enabled, $payment_status, $auth_code, $transaction_id;
  public $_logDir = DIR_FS_SQL_CACHE;

  public function __construct(){
  	global $order, $messageStack;
  	parent::__construct();
	if ($this->enabled && !function_exists('curl_init')) $messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_ERROR_CURL_NOT_FOUND, 'error');
	$this->code_debug  = (MODULE_PAYMENT_LINKPOINT_API_CODE_DEBUG == 'debug') ? true : false;
	// set error messages if misconfigured
	if (MODULE_PAYMENT_LINKPOINT_API_STATUS) {
		$pemFileDir = DIR_FS_WORKING . '/payment/modules/linkpoint_api/' . MODULE_PAYMENT_LINKPOINT_API_LOGIN . '.pem';
		if (MODULE_PAYMENT_LINKPOINT_API_LOGIN == 'EnterYourStoreNumber') {
			$this->title .= MODULE_PAYMENT_LINKPOINT_API_TEXT_NOT_CONFIGURED;
		} elseif (MODULE_PAYMENT_LINKPOINT_API_LOGIN != '' && !file_exists($pemFileDir)) {
			$this->title .= MODULE_PAYMENT_LINKPOINT_API_TEXT_PEMFILE_MISSING;
		} elseif (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE != 'LIVE: Production') {
			$this->title .= MODULE_PAYMENT_LINKPOINT_API_TEXT_TEST_MODE;
		}
	}
	$this->key[] = array('key' => 'MODULE_PAYMENT_LINKPOINT_API_LOGIN',                    'default' => 'YourStoreNumber'			, 'text' => MODULE_PAYMENT_LINKPOINT_API_LOGIN_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE','default' => 'LIVE: Production'			, 'text' => MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE',       'default' => 'Immediate Charge/Capture'	, 'text' => MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT',              'default' => '1'							, 'text' => MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_LINKPOINT_API_STORE_DATA',               'default' => '1'							, 'text' => MODULE_PAYMENT_LINKPOINT_API_STORE_DATA_DESC);
	$this->key[] = array('key' => 'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE',         'default' => 'Production'				, 'text' => MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_DESC);
    $this->key[] = array('key' => 'MODULE_PAYMENT_LINKPOINT_API_DEBUG',                    'default' => 'Off'						, 'text' => MODULE_PAYMENT_LINKPOINT_API_DEBUG_DESC);
  }

  function configure($key) {
    switch ($key) {
	  case 'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE':
	    $temp = array(
		  array('id' => 'LIVE: Production',    'text' => TEXT_PRODUCTION),
		  array('id' => 'TESTING: Successful', 'text' => TEXT_TEST_SUCCESS),
		  array('id' => 'TESTING: Decline',    'text' => TEXT_TEST_FAIL),
	    );
        return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE':
	    $temp = array(
		  array('id' => 'Authorize Only',           'text' => TEXT_AUTHORIZE),
		  array('id' => 'Immediate Charge/Capture', 'text' => TEXT_CAPTURE),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT':
	  case 'MODULE_PAYMENT_LINKPOINT_API_STORE_DATA':
	    $temp = array(
		  array('id' => '0', 'text' => TEXT_NO),
		  array('id' => '1', 'text' => TEXT_YES),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE':
	    $temp = array(
		  array('id' => 'Production',     'text' => TEXT_PRODUCTION),
		  array('id' => 'DevelopersTest', 'text' => TEXT_DEV_TEST),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  case 'MODULE_PAYMENT_LINKPOINT_API_DEBUG':
	    $temp = array(
		  array('id' => 'Off',                 'text' => TEXT_OFF),
		  array('id' => 'Failure Alerts Only', 'text' => TEXT_FAIL_ALERTS),
		  array('id' => 'Log File',            'text' => TEXT_LOG_FILE),
		  array('id' => 'Log and Email',       'text' => TEXT_LOG_EMAIL),
	    );
	    return html_pull_down_menu(strtolower($key), $temp, constant($key));
	  default:
	    return parent::configure($key);
    }
  }

  function javascript_validation() {
	$js = 'if (payment_method == "' . $this->code . '") {' . "\n" .
	'    var cc_owner  = document.getElementById("linkpoint_api_field_0").value;' . "\n" .
	'    var cc_number = document.getElementById("linkpoint_api_field_1").value;' . "\n" .
	'    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
	'      error_message = error_message + "' . sprintf(MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER, CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
	'      error = 1;' . "\n" .
	'    }' . "\n" .
	'    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
	'      error_message = error_message + "' . sprintf(MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER, CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
	'      error = 1;' . "\n" .
	'    }' . "\n" .
	
	'    var cc_cvv    = document.getElementById("linkpoint_api_field_4").value;' . "\n" .
	'    if (cc_cvv == "" || cc_cvv.length < "3") {' . "\n".
	'       error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_CVV . '\n";' . "\n" .
	'       error = 1;' . "\n" .
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

    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
    }

    $today = getdate();
    for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
      $expires_year[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }
    $selection = array (
	  'id'     => $this->code,
	  'page'   => $this->title,
	  'fields' => array (
	    array(  'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
			    'field' => html_input_field('linkpoint_api_field_0', $this->field_0)),
	    array(  'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
		     	'field' => html_input_field('linkpoint_api_field_1', $this->field_1)),
	    array(	'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
			    'field' => html_pull_down_menu('linkpoint_api_field_2', $expires_month, $this->field_2) . '&nbsp;' . html_pull_down_menu('linkpoint_api_field_3', $expires_year, $this->field_3)),
		array ( 'title' => MODULE_PAYMENT_CC_TEXT_CVV,
				'field' => html_input_field('linkpoint_api_field_4', $this->field_4, 'size="4" maxlength="4"' . ' id="' . $this->code . '-cc-cvv"' ) . ' ' . '<a href="javascript:popupWindow(\'' . html_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . TEXT_MORE_INFO . '</a>',)
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
		$has_error = true;
		$payment_error_return = 'payment_error=' . $this->code;
		$error_info2 = '&error=' . urlencode($error) . '&linkpoint_api_cc_owner=' . urlencode($this->field_0) . '&linkpoint_api_cc_expires_month=' . $this->field_2 . '&linkpoint_api_cc_expires_year=' . $this->field_3;
		$messageStack->add($error . '<!-- [' . $this->code . '] -->', 'error');
		if (MODULE_PAYMENT_LINKPOINT_API_STORE_DATA) {
			$cc_type         = $this->cc_card_type;
			$cc_number_clean = $this->cc_card_number;
			$cc_expiry_month = $this->cc_expiry_month;
			$cc_expiry_year  = $this->cc_expiry_year;
			$error_returned  = $payment_error_return . $error_info2;

			$cc_number = (strlen($cc_number_clean) > 8) ? substr($cc_number_clean, 0, 4) . str_repeat('X', (strlen($cc_number_clean) - 8)) . substr($cc_number_clean, -4) : substr($cc_number_clean, 0, 3) . '**short**';

			while (strstr($error_returned, '%3A'))
				$error_returned = str_replace('%3A', ' ', $error_returned);
			while (strstr($error_returned, '%2C'))
				$error_returned = str_replace('%2C', ' ', $error_returned);
			while (strstr($error_returned, '+'))
				$error_returned = str_replace('+', ' ', $error_returned);
			$error_returned = str_replace('&', ' &amp;', $error_returned);
			$cust_info = $error_returned;
			$message = addslashes($message);
			$cust_info = addslashes($cust_info);
			$all_response_info = addslashes($all_response_info);


	        //  Store Transaction history in Database
/* original Harry Lu sql converted to PhreeBooks format
		        $sql_data_array= array(array('fieldName'=>'lp_trans_num', 'value'=>'', 'type'=>'string'),
	                               array('fieldName'=>'order_id', 'value'=>0, 'type'=>'integer'),
	                               array('fieldName'=>'approval_code', 'value'=>'N/A', 'type'=>'string'),
	                               array('fieldName'=>'transaction_response_time', 'value'=>'N/A', 'type'=>'string'),
	                               array('fieldName'=>'r_error', 'value'=>'**CC Info Failed Validation during pre-processing**', 'type'=>'string'),
	                               array('fieldName'=>'customer_id', 'value'=>$_POST['bill_acct_id'] , 'type'=>'integer'),
	                               array('fieldName'=>'avs_response', 'value'=>'', 'type'=>'string'),
	                               array('fieldName'=>'transaction_result', 'value'=>'*CUSTOMER ERROR*', 'type'=>'string'),
	                               array('fieldName'=>'message', 'value'=>$message . ' -- ' . $all_response_info, 'type'=>'string'),
	                               array('fieldName'=>'transaction_time', 'value'=>time(), 'type'=>'string'),
	                               array('fieldName'=>'transaction_reference_number', 'value'=>'', 'type'=>'string'),
	                               array('fieldName'=>'fraud_score', 'value'=>0, 'type'=>'integer'),
	                               array('fieldName'=>'cc_number', 'value'=>$cc_number, 'type'=>'string'),
	                               array('fieldName'=>'cust_info', 'value'=>$cust_info, 'type'=>'string'),
	                               array('fieldName'=>'chargetotal', 'value'=>0, 'type'=>'string'),
	                               array('fieldName'=>'cc_expire', 'value'=>$cc_expiry_month . '/' . $cc_expiry_year, 'type'=>'string'),
	                               array('fieldName'=>'ordertype', 'value'=>'N/A', 'type'=>'string'),
	                               array('fieldName'=>'date_added', 'value'=>'now()', 'type'=>'noquotestring'));
	        $db->perform(TABLE_LINKPOINT_API, $sql_data_array);
*/
		        $sql_data_array= array(
					'lp_trans_num'                 => '',
					'order_id'                     => 0,
					'approval_code'                => 'N/A',
					'transaction_response_time'    => 'N/A',
					'r_error'                      => '**CC Info Failed Validation during pre-processing**',
					'customer_id'                  => $_POST['bill_acct_id'] ,
					'avs_response'                 => '',
					'transaction_result'           => '*CUSTOMER ERROR*',
					'message'                      => $message . ' -- ' . $all_response_info,
					'transaction_time'             => time(),
					'transaction_reference_number' => '',
					'fraud_score'                  => 0,
					'cc_number'                    => $cc_number,
					'cust_info'                    => $cust_info,
					'chargetotal'                  => 0,
					'cc_expire'                    => $cc_expiry_month . '/' . $cc_expiry_year,
					'ordertype'                    => 'N/A',
					'date_added'                   => 'now()',
				);
	        	db_perform(TABLE_LINKPOINT_API, $sql_data_array);
			}
			//gen_redirect(html_href_link(get_cur_url(), $payment_error_return, 'SSL', true, false));
			//gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(), 'SSL'));				
		}

		// if no error, continue with validated data:
		return $has_error;
	}

	// Display Credit Card Information on the Checkout Confirmation Page
	function confirmation() {
		$confirmation = array (
			'title' => $this->title . ': ' . $this->cc_card_type,
			'fields' => array (
				array ( 'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
						'field' => $this->field_0 ),
				array (	'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
						'field' => str_repeat('X', (strlen($this->cc_card_number) - 4)) . substr($this->cc_card_number, -4)	),
				array (	'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
						'field' => strftime('%B, %Y', mktime(0, 0, 0, $this->field_2, 1, '20' . $this->field_3)))
			));

		return $confirmation;
	}
	/**
	 * Prepare the hidden fields comprising the parameters for the Submit button on the checkout confirmation page
	 */
	function process_button() {
		// These are hidden fields on the checkout confirmation page
		$process_button_string = html_hidden_field('cc_owner', $this->field_0) .
		html_hidden_field('cc_expires', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
		html_hidden_field('cc_expires_month', $this->cc_expiry_month) .
		html_hidden_field('cc_expires_year', substr($this->cc_expiry_year, -2)) .
		html_hidden_field('cc_type', $this->cc_card_type) .
		html_hidden_field('cc_number', $this->cc_card_number) .
		html_hidden_field('cc_cvv', $this->field_4);
		$process_button_string .= html_hidden_field(session_name(), session_id());

		return $process_button_string;
	}
	/**
	 * Prepare and submit the authorization to the gateway
	 */
	function before_process() {
		
		global $order, $db, $messageStack, $lp_avs, $lp_trans_num;
		$has_error = false;
		$myorder = array ();

		// Build Info to send to Gateway
		$myorder["result"] = "LIVE";
		switch (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE) {
			case "TESTING: Successful" :
				$myorder["result"] = "GOOD";
				break;
			case "TESTING: Decline" :
				$myorder["result"] = "DECLINE";
				break;
			case "TESTING: Duplicate" :
				$myorder["result"] = "DUPLICATE";
				break;
		}

		// "oid" - Order ID number must be unique. If not set, gateway will assign one.
		$new_order_id = date('Ymdmis') . general_rand(100, 'digits'); // Create a UID for the order
		$myorder["oid"] = $new_order_id; //"";    // time(); ????
		$myorder["ip"] = get_ip_address();

		$myorder["ponumber"] = "";
		$myorder["subtotal"] = $order->total_amount;
		//$myorder["tax"] = ""; // $order->info['tax']; //no tax for pb
		//$myorder["shipping"] = ""; // $order->info['shipping_cost']; //no shipping for pb
		$myorder["chargetotal"] = $order->total_amount;

		// CARD INFO
		$myorder["cardnumber"]   = $this->field_1; //$_POST['cc_number']; //harry
		$myorder["cardexpmonth"] = $this->field_2; // $_POST['cc_expires_month']; //harry
		$myorder["cardexpyear"]  = $this->field_3; // $_POST['cc_expires_year']; //harry
		$myorder["cvmindicator"] = "provided";
		$myorder["cvmvalue"]     = $this->field_4; //$_POST['cc_cvv']; //harry


		// BILLING INFO
		$myorder["userid"]     = $_POST['bill_acct_id']; //$order->bill_primary_name; //$_SESSION['customer_id']; //harry
		$myorder["customerid"] = $_POST['bill_acct_id']; //$order->bill_primary_name; //$_SESSION['customer_id']; //harry
		$myorder["name"]       = htmlentities($this->field_0, ENT_QUOTES, 'UTF-8'); 
		$myorder["company"]    = htmlentities($order->bill_primary_name); // htmlentities($order->billing['company'], ENT_QUOTES, 'UTF-8'); //harry
		$myorder["address1"]   = htmlentities($order->bill_address1); //htmlentities($order->billing['street_address'], ENT_QUOTES, 'UTF-8'); //harry
		$myorder["address2"]   = htmlentities($order->bill_address2); //htmlentities($order->billing['suburb'], ENT_QUOTES, 'UTF-8'); //harry
		$myorder["city"]       = $order->bill_city_town; // $order->billing['city']; //harry
		$myorder["state"]      = $order->bill_state_province; //$order->billing['state']; //harry
		$myorder["country"]    = $order->bill_country_code; //$order->billing['country']['iso_code_2']; //harry
		//$myorder["phone"]    = $order->customer['telephone']; //harry
		//$myorder["fax"]      = $order->customer['fax'];
		//$myorder["email"]    = $order->customer['email_address']; //harry
		$myorder["addrnum"]    = htmlentities($order->bill_address1); // $order->billing['street_address']; // Required for AVS. If not provided, transactions will downgrade. //harry
		$myorder["zip"]        = $order->bill_postal_code; // $order->billing['postcode']; // Required for AVS. If not provided, transactions will downgrade. //harry

		// SHIPPING INFO
		/* harry
		$myorder["sname"] = htmlentities($order->delivery['firstname'] . ' ' . $order->delivery['lastname'], ENT_QUOTES, 'UTF-8');
		$myorder["saddress1"] = htmlentities($order->delivery['street_address'], ENT_QUOTES, 'UTF-8');
		$myorder["saddress2"] = htmlentities($order->delivery['suburb'], ENT_QUOTES, 'UTF-8');
		$myorder["scity"] = $order->delivery['city'];
		$myorder["sstate"] = $order->delivery['state'];
		$myorder["szip"] = $order->delivery['postcode'];
		$myorder["scountry"] = $order->delivery['country']['iso_code_2'];
		*/
		
		// MISC
		$myorder["comments"] = "Phreebooks Sales Order";

		// itemized contents
		for ($i = 0, $n = sizeof($order->item_rows); $i < $n; $i++) {
			$myorder["items"][$i]['id'] = $order->item_rows[$i]['id'];
			$myorder["items"][$i]['description'] = $order->item_rows[$i]['id']; //substr(htmlentities($order->products[$i]['name'], ENT_QUOTES, 'UTF-8'), 0, 100); //harry
			$myorder["items"][$i]['quantity'] = $order->item_rows[$i]['pay']; //$order->products[$i]['qty']; //harry
			$myorder["items"][$i]['price'] = number_format($order->item_rows[$i]['amt']); // number_format($order->products[$i]['price'], 2, '.', ''); //harry
			$myorder["items"][$i]['options']['name'] = "invoice_num" ; //array("invoice_num" => $order->item_rows[$i]['inv']);
			$myorder["items"][$i]['options']['value'] = $order->item_rows[$i]['inv'];
			
//			$myorder["items"][$i]['invoice_num'] = $order->item_rows[$i]['inv'];
			/* harry
			if (isset ($order->products[$i]['attributes'])) {
				for ($j = 0, $m = sizeof($order->products[$i]['attributes']); $j < $m; $j++) {
					$myorder["items"][$i]['options' . $j]['name'] = $order->products[$i]['attributes'][$j]['option'];
					$myorder["items"][$i]['options' . $j]['value'] = $order->products[$i]['attributes'][$j]['value'];
				}
			}
			*/
		}

		$myorder["ordertype"] = (MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE == 'Authorize Only' ? 'PREAUTH' : 'SALE');
		$this->payment_status = $myorder["ordertype"];
		// send request to gateway
		$result = $this->_sendRequest($myorder);
		
		// alert to customer if communication failure
		if (trim($result) == '<r_approved>FAILURE</r_approved><r_error>Could not connect.</r_error>' || !is_array($result)) {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_FAILURE_MESSAGE, 'error');
			//gen_redirect(html_href_link(get_cur_url(), '', 'SSL', true, false));
			//gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(), 'SSL'));
			$has_error = true;			
		}

		// PARSE Results
		$all_response_info = '';
		foreach ($result as $key => $value) {
			$all_response_info .= ' ' . $key . '=' . $value;
		}
		if ($this->code_debug) {
			$messageStack->add($all_response_info, 'caution');
		}

		$chargetotal = $myorder["chargetotal"];

		// prepare transaction info
		$cust_info = '';
		$cc_number = substr($myorder["cardnumber"], 0, 4) . str_repeat('X', abs(strlen($myorder["cardnumber"]) - 8)) . substr($myorder["cardnumber"], -4);
		foreach ($myorder as $key => $value) {
			if ($key != 'cardnumber') {
				if ($key == 'cardexpmonth') {
					$cc_month = $value;
				}
				if ($key == 'cardexpyear') {
					$cc_year = $value;
				}
				if (is_array($value)) {
					$value = print_r($value, true);
				}
				if (!in_array($key, array (
						'keyfile',
						'configfile',
						'transactionorigin',
						'terminaltype',
						'host',
						'port'
					))) {
					$cust_info .= ' ' . $key . '=' . $value . ';';
				}
			} else {
				$cust_info .= ' ' . $key . '=' . $cc_number . ';';
			}
		}

		// store first and last 4 digits of CC number ... which is the Visa-standards-compliant approach, same as observed by Linkpoint's services
		$order->info['cc_number'] = $cc_number;
		$order->info['cc_expires'] = $_POST['cc_expires'];
		$order->info['cc_type'] = $_POST['cc_type'];
		$order->info['cc_owner'] = $_POST['cc_owner'];
		$order->info['cc_cvv'] = '***'; // $_POST['cc_cvv'];

		$lp_trans_num = $result['r_ordernum'];
		$transaction_tax = $result['r_tax']; // The calculated tax for the order, when the ordertype is calctax.
		$transaction_shipping = $result['r_shipping']; // The calculated shipping charges for the order, when the ordertype is calcshipping.
		$this->response_codes = $result['r_avs']; // AVS Response for transaction

		// these are used to update the order-status-history upon order completion
		$this->transaction_id = $result['r_tdate'] . ' Order Number/Code: ' . $result['r_ordernum'];
		$this->auth_code = $result['r_code']; // The approval code for this transaction.

		//  Store Transaction history in Database
/* original Harry Lu sql converted to PhreeBooks format
    	$sql_data_array= array(array('fieldName'=>'lp_trans_num', 'value' => $result['r_ordernum'], 'type'=>'string'), // The order number associated with this transaction.
                           array('fieldName'=>'order_id', 'value' => $result['r_ordernum'], 'type'=>'integer'),
                           array('fieldName'=>'approval_code', 'value' => $result['r_code'], 'type'=>'string'), // The approval code for this transaction.
                           array('fieldName'=>'transaction_response_time', 'value' => $result['r_time'], 'type'=>'string'), // The time+date of the transaction server response.
                           array('fieldName'=>'r_error', 'value' => $result['r_error'], 'type'=>'string'),
                           array('fieldName'=>'customer_id', 'value' => $_POST['bill_acct_id'], 'type'=>'integer'),
                           array('fieldName'=>'avs_response', 'value' => $result['r_avs'], 'type'=>'string'), // AVS Response for transaction
                           array('fieldName'=>'transaction_result', 'value' => $result['r_approved'], 'type'=>'string'), // Transaction result: APPROVED, DECLINED, or FRAUD.
                           array('fieldName'=>'message', 'value' => $result['r_message'] . "\n" . $all_response_info, 'type'=>'string'), // Any message returned by the processor; e.g., CALL VOICE CENTER.
                           array('fieldName'=>'transaction_time', 'value' => $result['r_tdate'], 'type'=>'string'), // A server time-date stamp for this transaction.
                           array('fieldName'=>'transaction_reference_number', 'value' => $result['r_ref'], 'type'=>'string'), // Reference number returned by the CC processor.
                           array('fieldName'=>'fraud_score', 'value' => $result['r_score'], 'type'=>'integer'), // LinkShield fraud risk score.
                           array('fieldName'=>'cc_number', 'value' => $cc_number, 'type'=>'string'),
                           array('fieldName'=>'cust_info', 'value' => $cust_info, 'type'=>'string'),
                           array('fieldName'=>'chargetotal', 'value' => $chargetotal, 'type'=>'string'),
                           array('fieldName'=>'cc_expire', 'value' => $cc_month . '/' . $cc_year, 'type'=>'string'),
                           array('fieldName'=>'ordertype', 'value' => $myorder['ordertype'], 'type'=>'string'), // transaction type: PREAUTH or SALE
                           array('fieldName'=>'date_added', 'value' => 'now()', 'type'=>'noquotestring'));
	    if (MODULE_PAYMENT_LINKPOINT_API_STORE_DATA) {
	      $db->perform(TABLE_LINKPOINT_API, $sql_data_array);
	    }
*/
    	$sql_data_array= array(
			'lp_trans_num'                 => $result['r_ordernum'], // The order number associated with this transaction.
			'order_id'                     => $result['r_ordernum'],
			'approval_code'                => $result['r_code'], // The approval code for this transaction.
			'transaction_response_time'    => $result['r_time'], // The time+date of the transaction server response.
			'r_error'                      => $result['r_error'],
			'customer_id'                  => $_POST['bill_acct_id'],
			'avs_response'                 => $result['r_avs'], // AVS Response for transaction
			'transaction_result'           => $result['r_approved'], // Transaction result: APPROVED, DECLINED, or FRAUD.
			'message'                      => $result['r_message'] . "\n" . $all_response_info, // Any message returned by the processor; e.g., CALL VOICE CENTER.
			'transaction_time'             => $result['r_tdate'], // A server time-date stamp for this transaction.
			'transaction_reference_number' => $result['r_ref'], // Reference number returned by the CC processor.
			'fraud_score'                  => $result['r_score'], // LinkShield fraud risk score.
			'cc_number'                    => $cc_number,
			'cust_info'                    => $cust_info,
			'chargetotal'                  => $chargetotal,
			'cc_expire'                    => $cc_month . '/' . $cc_year,
			'ordertype'                    => $myorder['ordertype'], // transaction type: PREAUTH or SALE
			'date_added'                   => 'now()',
		);
	    if (MODULE_PAYMENT_LINKPOINT_API_STORE_DATA) db_perform(TABLE_LINKPOINT_API, $sql_data_array);

		//  Begin check of specific error conditions
		if ($result["r_approved"] != "APPROVED") {
			if (substr($result['r_error'], 0, 10) == 'SGS-020005') {
				//$messageStack->add($result['r_error'], 'error'); // Error (Merchant config file is missing, empty or cannot be read)
				$messageStack->add($result['r_error'], 'error'); 
			}
			if (substr($result['r_error'], 0, 10) == 'SGS-005000') {
				//$messageStack->add( MODULE_PAYMENT_LINKPOINT_API_TEXT_GENERAL_ERROR . '<br />' . $result['r_error'], 'error'); // The server encountered a database error
				$messageStack->add( MODULE_PAYMENT_LINKPOINT_API_TEXT_GENERAL_ERROR . '<br />' . $result['r_error'], 'error'); // The server encountered a database error
			}
			if (substr($result['r_error'], 0, 10) == 'SGS-000001' || strstr($result['r_error'], 'D:Declined') || strstr($result['r_error'], 'R:Referral')) {
				//$messageStack->add( MODULE_PAYMENT_CC_TEXT_DECLINED_MESSAGE . '<br />' . $result['r_error'], 'error');
				$messageStack->add( MODULE_PAYMENT_CC_TEXT_DECLINED_MESSAGE . '<br />' . $result['r_error'], 'error');
			}
			if (substr($result['r_error'], 0, 10) == 'SGS-005005' || strstr($result['r_error'], 'Duplicate transaction')) {
				//$messageStack->add( MODULE_PAYMENT_LINKPOINT_API_TEXT_DUPLICATE_MESSAGE . '<br />' . $result['r_error'], 'error');
				$messageStack->add( MODULE_PAYMENT_LINKPOINT_API_TEXT_DUPLICATE_MESSAGE . '<br />' . $result['r_error'], 'error');
			}
			$has_error = true;
		}
		//  End specific error conditions

		//  Begin Transaction Status does not equal APPROVED
		if ($result["r_approved"] != "APPROVED") {
			// alert to customer:
			$messageStack->add(MODULE_PAYMENT_CC_TEXT_DECLINED_MESSAGE, 'caution');
			//gen_redirect(html_href_link(get_cur_url(), '', 'SSL', true, false)); //harry
			//gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(), 'SSL'));
			$has_error = true;
		}
		//  End Transaction Status does not equal APPROVED

		$avs_meanings = array ();
		$avs_meanings['YY'] = ' - Street Address and Zip Code match.';
		$avs_meanings['YN'] = ' - Street Address matches but Zip Code does NOT match.';
		$avs_meanings['YX'] = ' - Street Address matches, but Zip Code comparison unavailable.';
		$avs_meanings['NY'] = ' - Street Address DOES NOT match, but Zip Code matches.';
		$avs_meanings['XY'] = ' - Street Address check not available, but Zip Code matches.';
		$avs_meanings['NN'] = ' - Street Address DOES NOT MATCH and Zip Code DOES NOT MATCH.';
		$avs_meanings['NX'] = ' - Street Address DOES NOT MATCH and Zip Code comparison unavailable.';
		$avs_meanings['XN'] = ' - Street Address check not available. Zip Code DOES NOT MATCH.';
		$avs_meanings['XX'] = ' - No validation for address or zip code could be performed (not available from issuing bank).';

		// Possible Fraud order. Allow transaction to process, but notify shop for owner to take appropriate action on order
		if (($result["r_approved"] == "APPROVED") && (substr($result['r_code'], 17, 2) != "YY") && MODULE_PAYMENT_LINKPOINT_API_FRAUD_ALERT == 'Yes') {
			$message = 'Potential Fraudulent Order - Bad Address - Action Required' . "\n" .
			'This alert occurs because the "Approval Code" below does not contain the expected YY response.' . "\n" .
			'Thus, you might want to verify the address with the customer prior to shipping, or be sure to use Registered Mail with Signature Required in case they file a chargeback.' . "\n\n" .
			'Customer Name: ' . $order->customer['firstname'] . ' ' . $order->customer['lastname'] . "\n\n" .
			'AVS Result: ' . $result['r_avs'] . $avs_meanings[substr($result['r_avs'], 0, 2)] . "\n\n" .
			'Order Number: ' . $lp_trans_num . "\n" .
			'Transaction Date and Time: ' . $result['r_time'] . "\n" .
			'Approval Code: ' . $result['r_code'] . "\n" .
			'Reference Number: ' . $result['r_ref'] . "\n\n" .
			'Error Message: ' . $result['r_error'] . "\n\n" .
			'Transaction Result: ' . $result['r_approved'] . "\n\n" .
			'Message: ' . $result['r_message'] . "\n\n" .
			'Fraud Score: ' . ($result['r_score'] == '' ? 'Not Enabled' : $result['r_score']) . "\n\n" .
			'AVS CODE MEANINGS: ' . "\n" .
			'YY** = Street Address and Zip Code match.' . "\n" .
			'YN** = Street Address matches but Zip Code does NOT match.' . "\n" .
			'YX** = Street Address matches, but Zip Code comparison unavailable.' . "\n" .
			'NY** = Street Address DOES NOT match, but Zip Code matches.' . "\n" .
			'XY** = Street Address check not available, but Zip Code matches.' . "\n" .
			'NN** = Street Address DOES NOT MATCH and Zip Code DOES NOT MATCH.' . "\n" .
			'NX** = Street Address DOES NOT MATCH and Zip Code comparison unavailable.' . "\n" .
			'XN** = Street Address check not available. Zip Code DOES NOT MATCH.' . "\n" .
			'XX** = Neither validation is available.' . "\n";
			$html_msg['EMAIL_MESSAGE_HTML'] = nl2br($result['r_message']);
		}
		// end fraud alert
		return $has_error;
	}

	function after_order_create($zf_order_id) {
		global $db, $lp_avs, $lp_trans_num;
		$db->execute("update " . TABLE_LINKPOINT_API . " set order_id ='" . $zf_order_id . "' where lp_trans_num = '" . $lp_trans_num . "'");
	}

	function admin_notification($zf_order_id) {
		global $db;
		if (!MODULE_PAYMENT_LINKPOINT_API_STORE_DATA) return '';
		$output = '';
		$sql = "select * from " . TABLE_LINKPOINT_API . " where order_id = '" . $zf_order_id . "' and transaction_result = 'APPROVED' order by date_added";
		$lp_api = $db->Execute($sql);
		if ($lp_api->RecordCount() > 0)
			require (DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/linkpoint_api/linkpoint_api_admin_notification.php');
		return $output;
	}

	function get_error() {
		$error = array (
			'title' => MODULE_PAYMENT_CC_TEXT_ERROR,
			'error' => stripslashes(urldecode($_GET['error']))
		);
		return $error;
	}

	function check() {
		global $db;
		
		if (!isset ($this->_check)) {
			$check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_LINKPOINT_API_STATUS'");
			
			$this->_check = $check_query->RecordCount();
		}		
		return $this->_check;
	}

  function install() {
	global $db;
	if (!db_table_exists(TABLE_LINKPOINT_API)) {
		$sql = "CREATE TABLE " . TABLE_LINKPOINT_API . " (
		  id int(11) unsigned NOT NULL auto_increment,
		  customer_id varchar(11) NOT NULL default '',
		  lp_trans_num varchar(64) NOT NULL default '',
		  order_id int(11) NOT NULL default '0',
		  avs_response varchar(4) NOT NULL default '',
		  r_error varchar(250) NOT NULL default '',
		  approval_code varchar(254) NOT NULL default '',
		  transaction_result varchar(25) NOT NULL default '',
		  message text NOT NULL,
		  transaction_response_time varchar(25) NOT NULL default '',
		  transaction_time varchar(50) NOT NULL default '',
		  transaction_reference_number varchar(64) NOT NULL default '',
		  fraud_score int(11) NOT NULL default '0',
		  cc_number varchar(20) NOT NULL default '',
		  cc_expire varchar(12) NOT NULL default '',
		  ordertype varchar(8) NOT NULL default '',
		  cust_info text,
		  chargetotal decimal(15,4) NOT NULL default '0.0000',
		  date_added datetime NOT NULL default '0001-01-01 00:00:00',
		  PRIMARY KEY  (id),
		  KEY idx_customer_id_zen (customer_id)     )";
		$db->Execute($sql);
	}
  }

  function remove() {
	global $db;
	if (db_table_exists(TABLE_LINKPOINT_API)) { // cleanup database if contains no data
	  $result = $db->Execute("select count(id) as count from " . TABLE_LINKPOINT_API);
	  if ($result->RecordCount() == 0) $db->Execute("DROP TABLE " . TABLE_LINKPOINT_API);
	}
  }

  function _log($msg, $suffix = '') {
	static $key;
	if (!isset ($key) || $key == '')
		$key = time() . '_' . general_rand(4);
	$file = $this->_logDir . '/' . 'Linkpoint_Debug_' . $suffix . $key . '.log';
	if ($fp = @ fopen($file, 'a')) {
		@ fwrite($fp, $msg);
		@ fclose($fp);
	}
  }

	function _sendRequest($myorder) {
		$myorder["host"] = "secure.linkpt.net";
		if (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE == 'DevelopersTest') {
			$myorder["host"] = "staging.linkpt.net";
		}
		
		$myorder["port"] = "1129";
		$myorder["keyfile"] = (DIR_FS_MODULES . 'payment/methods/linkpoint_api/' . MODULE_PAYMENT_LINKPOINT_API_LOGIN . '.pem');
		$myorder["configfile"] = MODULE_PAYMENT_LINKPOINT_API_LOGIN; // This is your store number
		// set to ECI and UNSPECIFIED for ecommerce transactions:
		$myorder["transactionorigin"] = "ECI";
		$myorder["terminaltype"] = "UNSPECIFIED";
		// debug - for testing communication only
		if (MODULE_PAYMENT_LINKPOINT_API_DEBUG != 'Off') {
		}
		if (MODULE_PAYMENT_LINKPOINT_API_CODE_DEBUG == 'debug') {
			$myorder["debugging"] = "true"; // for development only - not intended for production use
			$myorder["debug"] = "true"; // for development only - not intended for production use
			$myorder["webspace"] = "true"; // for development only - not intended for production use
		}

		include ('modules/payment/methods/linkpoint_api/class.linkpoint_api.php');
		$mylphp = new lphp;

		// Send transaction, using cURL
		$result = $mylphp->curl_process($myorder);

		// do debug output
		$errorMessage = date('M-d-Y h:i:s') . "\n=================================\n\n" . ($mylphp->commError != '' ? $mylphp->commError . "\n\n" : '') . 'Response Code: ' . $result["r_approved"] . "\n\n" . 'Sending to Gateway: ' . "\n" . $mylphp->sendData . "\n\n" . 'Result: ' . substr(print_r($result, true), 5) . "\n\n";
		if ($mylphp->commError != '') {
			$errorMessage .= $mylphp->commError . "\n" . 'CURL info: ' . print_r($mylphp->commInfo, true) . "\n";
		}
		if (CURL_PROXY_REQUIRED == '1') {
			$errorMessage .= 'Using CURL Proxy: [' . CURL_PROXY_SERVER_DETAILS . ']  with Proxy Tunnel: ' . ($proxy_tunnel_flag ? 'On' : 'Off') . "\n";
		}
		$failure = (trim($result) == '<r_approved>FAILURE</r_approved><r_error>Could not connect.</r_error>' || !is_array($result) || $result["r_approved"] != "APPROVED") ? true : false;

		// handle logging
		if (strstr(MODULE_PAYMENT_LINKPOINT_API_DEBUG, 'Log')) {
			$this->_log($errorMessage, $myorder["oid"] . ($failure ? '_FAILED' : ''));
		}
		if (strstr(MODULE_PAYMENT_LINKPOINT_API_DEBUG, 'Email') || ($failure && strstr(MODULE_PAYMENT_LINKPOINT_API_DEBUG, 'Alert'))) {
			//mail //TODO: Harry
			//zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'Linkpoint Debug Data' . ($failure ? ' - FAILURE' : ''), $errorMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array (
			//	'EMAIL_MESSAGE_HTML' => nl2br($errorMessage)
			//), 'debug');
		}
		//DEBUG ONLY:$this->_log($errorMessage /*. print_r($myorder, true) . print_r($mylphp->xmlString, true)*/, $myorder["oid"]);
		if ($myorder['debugging'] == 'true')
			exit;
		return $result;
	}


	/**
	 * Used to submit a refund for a given transaction.
	 */
	function _doRefund($oID, $amount = 0) {
		global $db, $messageStack;
		$new_order_status = (int) MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID;
		if ($new_order_status == 0)
			$new_order_status = 1;
		$proceedToRefund = true;
		$refundNote = strip_tags(addslashes($_POST['refnote']));
		if (isset ($_POST['refconfirm']) && $_POST['refconfirm'] != 'on') {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_CONFIRM_ERROR, 'error');
			$proceedToRefund = false;
		}
		if (isset ($_POST['buttonrefund']) && $_POST['buttonrefund'] == MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_BUTTON_TEXT) {
			$refundAmt = (float) $_POST['refamt'];
			$new_order_status = (int) MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID;
			if ($refundAmt == 0) {
				$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_REFUND_AMOUNT, 'error');
				$proceedToRefund = false;
			}
		}
		if (isset ($_POST['cc_number']) && (int) trim($_POST['cc_number']) == 0) {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_CC_NUM_REQUIRED_ERROR, 'error');
		}
		if (isset ($_POST['trans_id']) && (int) trim($_POST['trans_id']) == 0) {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
			$proceedToRefund = false;
		}

		$sql = "select lp_trans_num, transaction_time from " . TABLE_LINKPOINT_API . " where order_id = " . (int) $oID . " and transaction_result = 'APPROVED' order by transaction_time DESC";
		$query = $db->Execute($sql);
		if ($query->RecordCount() < 1) {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND, 'error');
			$proceedToRefund = false;
		}
		/**
		 * Submit refund request to gateway
		 */
		if ($proceedToRefund) {
			unset ($myorder);
			$myorder["ordertype"] = 'CREDIT';
			$myorder["oid"] = $query->fields['lp_trans_num'];
			if ($_POST['trans_id'] != '')
				$myorder["tdate"] = $_POST['trans_id'];
			$myorder["chargetotal"] = number_format($refundAmt, 2, '.', '');
			$myorder["comments"] = htmlentities($refundNote);

			$result = $this->_sendRequest($myorder);
			$response_alert = $result['r_approved'] . ' ' . $result['r_error'] . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
			$this->reportable_submit_data['Note'] = $refundNote;
			$failure = ($result["r_approved"] != "APPROVED");
			if ($failure) {
				$messageStack->add($response_alert, 'error');
			} else {
				// Success, so save the results
				$this->_updateOrderStatus($oID, $new_order_status, 'REFUND INITIATED. Order ID:' . $result['r_ordernum'] . ' - ' . 'Trans ID: ' . $result['r_tdate'] . "\n" . 'Amount: ' . $myorder["chargetotal"] . "\n" . $refundNote);
				$messageStack->add(sprintf(MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_INITIATED, $result['r_tdate'], $result['r_ordernum']), 'success');
				return true;
			}
		}
		return false;
	}

	/**
	 * Used to capture part or all of a given previously-authorized transaction.
	 */
	function _doCapt($oID, $amt = 0, $currency = 'USD') {
		global $db, $messageStack;

		//@TODO: Read current order status and determine best status to set this to
		$new_order_status = (int) MODULE_PAYMENT_LINKPOINT_API_ORDER_STATUS_ID;
		if ($new_order_status == 0)
			$new_order_status = 1;

		$proceedToCapture = true;
		$captureNote = strip_tags(addslashes($_POST['captnote']));
		if (isset ($_POST['captconfirm']) && $_POST['captconfirm'] == 'on') {
		} else {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPTURE_CONFIRM_ERROR, 'error');
			$proceedToCapture = false;
		}

		$lp_trans_num = (isset ($_POST['captauthid']) && $_POST['captauthid'] != '') ? strip_tags(addslashes($_POST['captauthid'])) : '';
		$sql = "select lp_trans_num, chargetotal from " . TABLE_LINKPOINT_API . " where order_id = " . (int) $oID . " and transaction_result = 'APPROVED' order by date_added";
		if ($lp_trans_num != '')
			$sql = "select lp_trans_num, chargetotal from " . TABLE_LINKPOINT_API . " where lp_trans_num = :trans_num: and transaction_result = 'APPROVED' order by date_added";
		$sql = $db->bindVars($sql, ':trans_num:', $lp_trans_num, 'string');
		$query = $db->Execute($sql);
		if ($query->RecordCount() < 1) {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND, 'error');
			$proceedToCapture = false;
		}
		$captureAmt = (isset ($_POST['captamt']) && $_POST['captamt'] != '') ? (float) strip_tags(addslashes($_POST['captamt'])) : $query->fields['chargetotal'];
		if (isset ($_POST['btndocapture']) && $_POST['btndocapture'] == MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_BUTTON_TEXT) {
			if ($captureAmt == 0) {
				$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
				$proceedToCapture = false;
			}
		}
		/**
		 * Submit capture request to Gateway
		 */
		if ($proceedToCapture) {
			unset ($myorder);
			$myorder["ordertype"] = 'POSTAUTH';
			$myorder["oid"] = $query->fields['lp_trans_num'];
			$myorder["chargetotal"] = number_format($captureAmt, 2, '.', '');
			$myorder["comments"] = htmlentities($captureNote);

			$result = $this->_sendRequest($myorder);
			$response_alert = $result['r_approved'] . ' ' . $result['r_error'] . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
			$failure = ($result["r_approved"] != "APPROVED");
			if ($failure) {
				$messageStack->add($response_alert, 'error');
			} else {
				// Success, so save the results
				$this->_updateOrderStatus($oID, $new_order_status, 'FUNDS COLLECTED. Auth Code: ' . substr($result['r_code'], 0, 6) . ' - ' . 'Trans ID: ' . $result['r_tdate'] . "\n" . ' Amount: ' . number_format($captureAmt, 2) . "\n" . $captureNote);
				$messageStack->add(sprintf(MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPT_INITIATED, $captureAmt, $result['r_tdate'], substr($result['r_code'], 0, 6)), 'success');
				return true;
			}
		}
		return false;
	}
	/**
	 * Used to void a given previously-authorized transaction.
	 */
	function _doVoid($oID, $note = '') {
		global $db, $messageStack;

		$new_order_status = (int) MODULE_PAYMENT_LINKPOINT_API_REFUNDED_ORDER_STATUS_ID;
		if ($new_order_status == 0)
			$new_order_status = 1;
		$voidNote = strip_tags(addslashes($_POST['voidnote'] . $note));
		$voidAuthID = trim(strip_tags(addslashes($_POST['voidauthid'])));
		$proceedToVoid = true;
		if (isset ($_POST['ordervoid']) && $_POST['ordervoid'] == MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_BUTTON_TEXT) {
			if (isset ($_POST['voidconfirm']) && $_POST['voidconfirm'] != 'on') {
				$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_CONFIRM_ERROR, 'error');
				$proceedToVoid = false;
			}
		}
		if ($voidAuthID == '') {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
			$proceedToVoid = false;
		}
		$sql = "select lp_trans_num, transaction_time from " . TABLE_LINKPOINT_API . " where order_id = " . (int) $oID . " and transaction_result = 'APPROVED' order by date_added";
		$query = $db->Execute($sql);
		if ($query->RecordCount() < 1) {
			$messageStack->add(MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND, 'error');
			$proceedToVoid = false;
		}
		/**
		 * Submit void request to Gateway
		 */
		if ($proceedToVoid) {
			unset ($myorder);
			$myorder["ordertype"] = 'VOID';
			$myorder["oid"] = $query->fields['lp_trans_num'];
			if ($voidAuthID != '')
				$myorder["tdate"] = $voidAuthID;
			$myorder["comments"] = htmlentities($voidNote);

			$result = $this->_sendRequest($myorder);
			$response_alert = $result['r_approved'] . ' ' . $result['r_error'] . ($this->commError == '' ? '' : ' Communications Error - Please notify webmaster.');
			$failure = ($result["r_approved"] != "APPROVED");
			if ($failure) {
				$messageStack->add($response_alert, 'error');
			} else {
				// Success, so save the results
				//$this->_updateOrderStatus($oID, $new_order_status, 'VOIDED. OrderNo: ' . $result['r_ordernum'] . ' - Trans ID: ' . $result['r_tdate'] . "\n" . $voidNote);
				$messageStack->add(sprintf(MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_INITIATED, $result['r_tdate'], $result['r_ordernum']), 'success');
				return true;
			}
		}
		return false;
	}
	//error_log( ' ' . print_r($this,TRUE) . "\n", 3, DIR_FS_SQL_CACHE . "/debug.log");
}
?>