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
//  Path: /soap/classes/phreebooksAPI.php
//
// hook for custom functions for additional processing
if (file_exists(DIR_FS_ADMIN."soap/custom/phreebooksAPI.php")) { require_once(DIR_FS_ADMIN."soap/custom/phreebooksAPI.php"); }

class xml_orders extends parser {
	var $arrMsg = array();
	var $trap   = false; // when set to true, writes a debug trace file

	function __construct() {
		global $messageStack;
		$messageStack->debug_header();
		$this->trace      = $messageStack->debug_info;
	}

	function msgAdd($message, $level='error') { $this->arrMsg[$level][] = array('text' => $message); }
	function msgDebug($text) { $this->trace .= "\n" . (is_array($text) ? print_r($text, true) : $text); }
	function msgDebugWrite() {
		if (!$this->trap || strlen($this->trace) < 1) return;
		$this->trace .= "\n\nMsg array contains: ".print_r($this->arrMsg, true);
		if ($this->fileWrite($this->trace, DIR_FS_MY_FILES . 'trace.txt')) unset($this->trace);
	}
	
	function processXML() {
		global $messageStack;
$this->trap = true;
//		$this->msgAdd("Ready to process!", 'error');
		if (!$this->validateUser($_POST['UserID'], $_POST['UserPW'])) return;
		switch ($_POST['Action']) {
			case 'journal':
			case 'SalesOrder': $this->processOrder(); break;
			default: $this->msgAdd("Bad action!", 'error');
		}
		// send back messageStack merged with API msg reformatted
		if (isset($_SESSION['messageToStack']) && is_array($_SESSION['messageToStack'])) foreach ($_SESSION['messageToStack'] as $value) {
			// strip the icon and html space
			$text = substr($value['text'], strpos($value['text'], '>')+1);
			$text = str_replace ('&nbsp;', ' ', $text);
			$this->arrMsg[$value['type']][] = array('text'=>$text);
		}
		$this->msgDebug("\n\nbizunoAPI Response = ".print_r($this->arrMsg, true));
		$this->msgDebugWrite();
		echo json_encode($this->arrMsg);
	}

  function processOrder() {
	global $db, $messageStack;
	define('JOURNAL_ID',10); // make them all sales orders for now
	define('GL_TYPE',  'soo');
	$this->msgDebug("\njournal_id = ".JOURNAL_ID." and function = ".$this->function);
	$tax_rates = ord_calculate_tax_drop_down('c');
	$order = json_decode($_POST['Order'], true);
	// Here we map the received xml array to the pre-defined generic structure (application specific format later)
	if ($order['ReceivablesGLAccount'] <> '') { // see if requestor specifies a AR account else use default
	    define('DEF_GL_ACCT', $order['ReceivablesGLAccount']);
	} else {
	    define('DEF_GL_ACCT', AR_DEFAULT_GL_ACCT);
	}
	  $this->order = array(
		'order_id'           => $order['General']['OrderID'],
		'purch_order_id'     => $order['General']['PurchaseOrderID'],
		'post_date'          => $order['General']['OrderDate'],
		'order_total'        => $order['General']['OrderTotal'],
		'tax_total'          => $order['General']['TaxTotal'],
		'freight_total'      => $order['General']['ShippingTotal'],
		'freight_carrier'    => $order['General']['ShippingCarrier'],
		'freight_method'     => $order['General']['ShippingMethod'],
		'rep_id'             => $order['General']['SalesRepID'],
//		'discount_total'     => $order['General']['DiscountTotal'],
		// <Payment>
		'payment' => array(
			'holder_name' => $order['Payment']['CardHolderName'],
			'method'      => $order['Payment']['Method'],
			'type'        => $order['Payment']['CardType'],
			'card_number' => $order['Payment']['CardNumber'],
			'exp_date'    => $order['Payment']['ExpirationDate'],
			'cvv2'        => $order['Payment']['CVV2Number'],
		),
	  );
	  // Billing and Shipping
	  $types = array('billing'=>'Billing', 'shipping'=>'Shipping');
	  foreach ($types as $key => $entry) {
	    $this->order[$key] = array(
			'primary_name'  => $order[$entry]['CompanyName'],
			'contact'       => $order[$entry]['Contact'],
			'address1'      => $order[$entry]['Address1'],
			'address2'      => $order[$entry]['Address2'],
			'city_town'     => $order[$entry]['City'],
			'state_province'=> $order[$entry]['State'],
			'postal_code'   => $order[$entry]['PostalCode'],
			'country_code'  => $order[$entry]['Country'],
			'telephone'     => $order[$entry]['Telephone'],
			'email'         => $order[$entry]['Email'],
	    );
	    if ($key == 'billing') { // additional information for the customer record
		  $this->order[$key]['customer_id']  = $order[$entry]['CustomerID'];
	    }
	  }
	  // if billing or shipping is blank, use customer address
	  if ($this->order['billing']['primary_name'] == '' && $this->order['billing']['contact'] == '') {
	    $this->order['billing'] = $this->order['customer'];
	  }
	  if ($this->order['shipping']['primary_name'] == '' && $this->order['shipping']['contact'] == '') {
	    $this->order['shipping'] = $this->order['customer'];
	  }
	  // <LineItems>
	  $this->order['items'] = array();
	  foreach ($order['Item'] as $entry) {
		$item = array();
		$sku                 = $entry['ItemID'];
		// try to match sku and get the sales gl account
		$result = $db->Execute("SELECT account_sales_income FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
		if ($result->RecordCount() > 0) {
		  $item['sku']       = $sku;
		  $item['gl_acct']   = $result->fields['account_sales_income'];
		} else {
		  $result = $db->Execute("SELECT sku, account_sales_income FROM ".TABLE_INVENTORY." WHERE description_short='$sku'");
		  $item['sku']       = $result->fields['sku'];
		  $item['gl_acct']   = $result->fields['account_sales_income'];
		}
		$item['description'] = $entry['Description'];
		$item['quantity']    = $entry['Quantity'];
		$item['unit_price']  = $entry['UnitPrice'];
		$item['tax_percent'] = $entry['SalesTaxPercent'];
//		$item['sales_tax']   = $entry['SalesTax']; // sales tax will be calculated
		$item['taxable']     = $this->guess_tax_id($tax_rates, $item['tax_percent']);
		$item['total_price'] = $entry['TotalPrice'];
		$this->order['items'][] = $item;
	  }
	  if (function_exists('xtra_order_data')) xtra_order_data($this->order, $order);
	  $this->buildJournalEntry();
  }

// The remaining functions are specific to PhreeBooks. They need to be modified for the specific application.
// It also needs to check for errors, i.e. missing information, bad data, etc. 
  function buildJournalEntry() {
	global $db, $messageStack, $currencies;
	// set some preliminary information
	$account_type = 'c';
	$psOrd = new orders();
	// make the received string look like a form submission then post as usual
	$psOrd->account_type        = $account_type;
	$psOrd->id                  = ''; // should be null unless opening an existing purchase/receive
	$psOrd->journal_id          = JOURNAL_ID;
	$psOrd->post_date           = $this->order['post_date']; // date format should already be YYYY-MM-DD
	$psOrd->terminal_date       = $this->order['post_date']; // make same as order date for now
	$psOrd->period              = gen_calculate_period($psOrd->post_date);
	$psOrd->store_id            = $this->get_account_id($this->order['store_id'], 'b');
	$psOrd->admin_id            = $this->get_user_id($this->username);
	$psOrd->description         = SOAP_XML_SUBMITTED_SO;
	$psOrd->gl_acct_id          = DEF_GL_ACCT;
	$psOrd->freight             = $currencies->clean_value(db_prepare_input($this->order['freight_total']), DEFAULT_CURRENCY);
	$psOrd->discount            = $currencies->clean_value(db_prepare_input($this->order['discount_total']), DEFAULT_CURRENCY);
	$psOrd->sales_tax           = db_prepare_input($this->order['tax_total']);
	$psOrd->total_amount        = db_prepare_input($this->order['order_total']);
	// The order ID should be set by the submitter
	$psOrd->purchase_invoice_id = db_prepare_input($this->order['order_id']);
	$psOrd->purch_order_id      = db_prepare_input($this->order['purch_order_id']);
	$psOrd->shipper_code        = db_prepare_input($this->order['freight_carrier']);
	/* Values below are not used at this time
	$psOrd->sales_tax_auths
	$psOrd->drop_ship = 0;
	$psOrd->waiting = 0;
	$psOrd->closed = 0;
	$psOrd->subtotal
	*/
	$psOrd->bill_add_update = 1; // force an address book update
	// see if the customer record exists
	$psOrd->short_name          = db_prepare_input($this->order['billing']['customer_id']);
  	if (!$psOrd->short_name && AUTO_INC_CUST_ID) {
	  $result = $db->Execute("select next_cust_id_num from ".TABLE_CURRENT_STATUS);
	  $short_name = $result->fields['next_cust_id_num'];
	  $next_id = $short_name++;;
	  $db->Execute("update ".TABLE_CURRENT_STATUS." set next_cust_id_num = '$next_id'");
  	}
	$psOrd->ship_short_name     = $psOrd->short_name;
	if (!$result = $this->checkForCustomerExists($psOrd)) return;
	$psOrd->ship_add_update     = $result['ship_add_update'];
	$psOrd->bill_acct_id        = $result['bill_acct_id'];
	$psOrd->bill_address_id     = $result['bill_address_id'];
	$psOrd->ship_acct_id        = $result['ship_acct_id'];
	$psOrd->ship_address_id     = $result['ship_address_id'];
	if ($result['terms']) $psOrd->terms = $result['terms'];
	// Phreebooks requires a primary name or the order is not valid, use company name if exists, else contact name
	if ($this->order['billing']['primary_name'] == '') {
	  $psOrd->bill_primary_name = $this->order['billing']['contact'];
	  $psOrd->bill_contact      = '';
	} else {
	  $psOrd->bill_primary_name = $this->order['billing']['primary_name'];
	  $psOrd->bill_contact      = $this->order['billing']['contact'];
	}
	$psOrd->bill_address1       = $this->order['billing']['address1'];
	$psOrd->bill_address2       = $this->order['billing']['address2'];
	$psOrd->bill_city_town      = $this->order['billing']['city_town'];
	$psOrd->bill_state_province = $this->order['billing']['state_province'];
	$psOrd->bill_postal_code    = $this->order['billing']['postal_code'];
	$psOrd->bill_country_code   = gen_get_country_iso_3_from_2($this->order['billing']['country_code']);
	$psOrd->bill_telephone1     = $this->order['billing']['telephone'];
	$psOrd->bill_email          = $this->order['billing']['email'];
	if ($this->order['shipping']['primary_name'] == '') {
	  $psOrd->ship_primary_name = $this->order['shipping']['contact'];
	  $psOrd->ship_contact      = '';
	} else {
	  $psOrd->ship_primary_name = $this->order['shipping']['primary_name'];
	  $psOrd->ship_contact      = $this->order['shipping']['contact'];
	}
	$psOrd->ship_address1       = $this->order['shipping']['address1'];
	$psOrd->ship_address2       = $this->order['shipping']['address2'];
	$psOrd->ship_city_town      = $this->order['shipping']['city_town'];
	$psOrd->ship_state_province = $this->order['shipping']['state_province'];
	$psOrd->ship_postal_code    = $this->order['shipping']['postal_code'];
	$psOrd->ship_country_code   = gen_get_country_iso_3_from_2($this->order['shipping']['country_code']);
	$psOrd->ship_telephone1     = $this->order['shipping']['telephone'];
	$psOrd->ship_email          = $this->order['shipping']['email'];
	// check for truncation of addresses
	if (strlen($psOrd->bill_primary_name) > 32 || strlen($psOrd->bill_address1) > 32 || strlen($psOrd->ship_primary_name) > 32 || strlen($psOrd->ship_address1) > 32) {
	  $this->msgAdd('Either the Primary Name or Address has been truncated to fit in the Phreedom database field sizes. Please check source information.', 'caution');
	}
	// load the item rows
	switch (JOURNAL_ID) {
	  case 12: $index = 'pstd'; break;
	  case 10: 
	  default: $index = 'qty';  break;
	}
	for ($i = 0; $i < count($this->order['items']); $i++) {
	  $psOrd->item_rows[] = array(
		'gl_type' => GL_TYPE,
		$index    => db_prepare_input($this->order['items'][$i]['quantity']),
		'sku'     => db_prepare_input($this->order['items'][$i]['sku']),
		'desc'    => db_prepare_input($this->order['items'][$i]['description']),
		'price'   => db_prepare_input($this->order['items'][$i]['unit_price']),
		'acct'    => db_prepare_input($this->order['items'][$i]['gl_acct']),
		'tax'     => db_prepare_input($this->order['items'][$i]['taxable']),
		'total'   => db_prepare_input($this->order['items'][$i]['total_price']),
	  );
	}
	// error check input
	$missing_fields = array();
	if (!$psOrd->short_name && !AUTO_INC_CUST_ID)                             $missing_fields[] = ACT_SHORT_NAME;
	if (!$psOrd->post_date)                                                   $missing_fields[] = TEXT_POST_DATE;
	if (!$psOrd->period)                                                      $missing_fields[] = TEXT_PERIOD;
	if (!$psOrd->bill_primary_name)                                           $missing_fields[] = GEN_PRIMARY_NAME;
	if (!$psOrd->bill_country_code)                                           $missing_fields[] = GEN_COUNTRY_CODE;
	if (ADDRESS_BOOK_CONTACT_REQUIRED        && !$psOrd->bill_contact)        $missing_fields[] = GEN_CONTACT;
	if (ADDRESS_BOOK_ADDRESS1_REQUIRED       && !$psOrd->bill_address1)       $missing_fields[] = GEN_ADDRESS1;
	if (ADDRESS_BOOK_ADDRESS2_REQUIRED       && !$psOrd->bill_address2)       $missing_fields[] = GEN_ADDRESS2;
	if (ADDRESS_BOOK_CITY_TOWN_REQUIRED      && !$psOrd->bill_city_town)      $missing_fields[] = GEN_CITY_TOWN;
	if (ADDRESS_BOOK_STATE_PROVINCE_REQUIRED && !$psOrd->bill_state_province) $missing_fields[] = GEN_STATE_PROVINCE;
	if (ADDRESS_BOOK_POSTAL_CODE_REQUIRED    && !$psOrd->bill_postal_code)    $missing_fields[] = GEN_POSTAL_CODE;
	if (defined('MODULE_SHIPPING_STATUS')) {
//	  if (!$psOrd->ship_primary_name)                                         $missing_fields[] = GEN_PRIMARY_NAME;
//	  if (!$psOrd->ship_country_code)                                         $missing_fields[] = GEN_COUNTRY_CODE;
	  if (ADDRESS_BOOK_SHIP_CONTACT_REQ      && !$psOrd->ship_contact)        $missing_fields[] = GEN_CONTACT;
	  if (ADDRESS_BOOK_SHIP_ADD1_REQ         && !$psOrd->ship_address1)       $missing_fields[] = GEN_ADDRESS1;
	  if (ADDRESS_BOOK_SHIP_ADD2_REQ         && !$psOrd->ship_address2)       $missing_fields[] = GEN_ADDRESS2;
	  if (ADDRESS_BOOK_SHIP_CITY_REQ         && !$psOrd->ship_city_town)      $missing_fields[] = GEN_CITY_TOWN;
	  if (ADDRESS_BOOK_SHIP_STATE_REQ        && !$psOrd->ship_state_province) $missing_fields[] = GEN_STATE_PROVINCE;
	  if (ADDRESS_BOOK_SHIP_POSTAL_CODE_REQ  && !$psOrd->ship_postal_code)    $missing_fields[] = GEN_POSTAL_CODE;
	}
	if (sizeof($missing_fields) > 0) {
	  $this->msgAdd(sprintf(SOAP_MISSING_FIELDS, $this->order['reference'], implode(', ', $missing_fields)), 'error');
	  return;
	}

	if (function_exists('xtra_order_before_post')) xtra_order_before_post($psOrd, $this->order);
	
	// post the sales order
	$this->msgDebug('ready to post =>'.print_r($psOrd, true));
	$post_success = $psOrd->post_ordr('insert');
	if (!$post_success) { // extract the error message from the messageStack and return with error
	  $db->transRollback();
	  return;
	}

	if (function_exists('xtra_order_after_post')) xtra_order_after_post($psOrd, $this->order);

	gen_add_audit_log(constant('AUDIT_LOG_SOAP_'.JOURNAL_ID.'_ADDED'), $psOrd->purchase_invoice_id, $psOrd->total_amount);
  }

  function checkForCustomerExists($psOrd) {
	global $db;
	$output = array();
	$result = $db->Execute("select id, special_terms from ".TABLE_CONTACTS." 
		where type = 'c' and short_name = '" . $psOrd->short_name . "'");
	if ($result->RecordCount() == 0) { // create new record
	  $output['bill_acct_id']    = '';
	  $output['ship_acct_id']    = '';
	  $output['bill_address_id'] = '';
	} else {
	  $output['bill_acct_id'] = $result->fields['id'];
	  $output['ship_acct_id'] = $output['bill_acct_id']; // no drop ships allowed
	  $output['terms']        = $result->fields['special_terms'];
	  // find main address to update as billing address
	  $result = $db->Execute("select address_id from ".TABLE_ADDRESS_BOOK." 
		where type = 'cm' and ref_id = " . $output['bill_acct_id']);
	  if ($result->RecordCount() == 0) {
	  	$this->msgAdd(SOAP_ACCOUNT_PROBLEM." ".$this->order['reference']);
	    return false;
	  }
	  $output['bill_address_id'] = $result->fields['address_id'];
	}
	// check to see if billing and shipping are different, if so set ship update flag
	// for now look at the primary name or address1 to be different, can be expanded to differentiate further if necessary
	if (($psOrd->bill_primary_name <> $psOrd->ship_primary_name) || ($psOrd->bill_address1 <> $psOrd->ship_address1)) {
	  $result = $db->Execute("select address_id from " . TABLE_ADDRESS_BOOK . " 
		where primary_name = '" . $psOrd->ship_primary_name . "' and 
			address1 = '" . $psOrd->ship_address1 . "' and 
			type = 'cs' and ref_id = " . $output['bill_acct_id']);
	  $output['ship_add_update'] = 1;
	  $output['ship_address_id'] =  ($result->RecordCount() == 0) ? '' : $result->fields['address_id'];
	} else {
	  $output['ship_add_update'] = 0;
	  $output['ship_address_id'] = $output['bill_address_id'];
	}
	return $output;
  }

  function guess_tax_id($rate_array, $rate) {
	foreach ($rate_array as $value) if ($value['rate'] == $rate) return $value['id'];
	return 0; // no tax since no rate match
  }

  private function fileWrite($data, $filename, $verbose=true) {
  	if (strlen($data) < 1) return false;
  	$path = substr($filename, 0, strrpos($filename, '/') + 1); // pull the path from the full path and file
  	if (!is_dir($path)) mkdir($path,0755,true);
  	if (!$handle = @fopen($filename, 'w')) {
  		if ($verbose) $this->msgAdd(sprintf($this->lang('err_io_file_open'), $filename), "error");
  		return false;
  	}
  	if (@fwrite($handle, $data) === false) {
  		if ($verbose) $this->msgAdd(sprintf($this->lang('err_io_file_write'), $filename), "error");
  		return false;
  	}
  	@fclose($handle);
  	return true;
  }
  
}
?>