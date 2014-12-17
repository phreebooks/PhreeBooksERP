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
//  Path: /modules/amazon/classes/amazon.php
//

/*
This script imports the amazon order files to PhreeBooks. 
For the script to work properly, the customer ($this->customer_id) must exist in the customer accounts. The address
information is pulled from the PhreeBooks database since the amazon order file does not include the billing
information.

*/

require_once (DIR_FS_WORKING.'defaults.php');
//require_once (DIR_FS_ADMIN  .'soap/language/en_us/language.php');

class amazon {
	// class constructor
	function __construct() {
	}

	function processOrders($upload_name='file_name') {
		global $db, $messageStack;
		$period = gen_calculate_period(date('Y-m-d'));
		if (!defined('JOURNAL_ID')) define('JOURNAL_ID',12);
		// load the amazon contact record info
		$result = $db->Execute("SELECT id FROM ".TABLE_CONTACTS." WHERE short_name='".MODULE_AMAZON_CUSTOMER_ID."'");
		$cID = $result->fields['id'];
		if (!$cID) { 
			$messageStack->add("Contact could not be found in the Customer database. Please make sure the setting in the defaults.php file match your Customers value.", 'error');
			return;
		}
		$result = $db->Execute("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE ref_id=$cID AND type='cm'");
		$commonMain = array(
			'post_date'          => date('Y-m-d'), // substr($data['purchase-date'], 0, 10); // forces orders posted today
			'period'             => $period,
			'journal_id'         => JOURNAL_ID,
			'currencies_code'    => DEFAULT_CURRENCY,
			'terminal_date'      => date('Y-m-d'),
			'store_id'           => 0,
			'admin_id'           => $_SESSION['admin_id'],
			'rep_id'             => 0,
			'gl_acct_id'         => MODULE_AMAZON_DEFAULT_RECEIVABLES_GL_ACCT,
			'bill_acct_id'       => $result->fields['ref_id'],
			'bill_address_id'    => $result->fields['address_id'],
			'bill_primary_name'  => $result->fields['primary_name'],
			'bill_contact'       => $result->fields['contact'],
			'bill_address1'      => $result->fields['address1'],
			'bill_address2'      => $result->fields['address2'],
			'bill_city_town'     => $result->fields['city_town'],
			'bill_state_province'=> $result->fields['state_province'],
			'bill_postal_code'   => $result->fields['postal_code'],
			'bill_country_code'  => $result->fields['country_code'],
			'bill_telephone1'    => $result->fields['telephone1'],
			'bill_email'         => $result->fields['email'],
			'drop_ship'          => '1',
		);
		$bill_acct_id = $result->fields['ref_id'];
		// iterate through the map to set journal post variables, orders may be on more than 1 line
		// ***************************** START TRANSACTION *******************************
		$db->transStart();
		$itemCnt = 1;
		$items   = array();
		$totals  = array();
		$inStock = true;
		$orderCnt= 0;
		$skip    = false;
		$runaway = 0;
		$rows    = file($_FILES[$upload_name]['tmp_name']);
		$row     = array_shift($rows); // heading
		$this->headings = explode("\t", $row);
		$row     = array_shift($rows); // first order
		if (!$row) { $messageStack->add("There were no orders to process!", 'caution'); return; }
		$data= $this->processRow($row);
		while (true) {
			if (!$row) break;
			$main = $commonMain;
			$main['purch_order_id'] = $data['order-id'];
			$main['description']    = "Amazon Order # ".$data['order-id'];
			$main['shipper_code']   = MODULE_AMAZON_DEFAULT_SHIPPING_CARRIER;
			if (strlen($data['recipient-name']) > 32 || strlen($data['ship-address-1']) > 32 || strlen($data['ship-address-2']) > 32) {
				$messageStack->add(sprintf("Order # %s has a name or address that is too long for the PhreeBooks db and has been truncated: %s", $data['order-id'], $data['recipient-name']), 'caution');
			}
			$main['ship_primary_name']  = $data['recipient-name'];
			$main['ship_address1']      = $data['ship-address-1'];
			$main['ship_address2']      = $data['ship-address-2'];
			$main['ship_contact']       = $data['ship-address-3'];
			$main['ship_city_town']     = $data['ship-city'];
			$main['ship_state_province']= $data['ship-state'];
			$main['ship_postal_code']   = $data['ship-postal-code'];
			$main['ship_country_code']  = gen_get_country_iso_3_from_2($data['ship-country']);
			$main['ship_telephone1']    = $data['buyer-phone-number'];
			$main['ship_email']         = $data['buyer-email'];
			// build the item, check stock if auto_journal
			$inv = $db->Execute("SELECT * FROM ".TABLE_INVENTORY." WHERE sku='{$data['sku']}'");
			$messageStack->debug("\n Executing sql = "."SELECT * FROM ".TABLE_INVENTORY." WHERE sku='{$data['sku']}' resulting in:".print_r($inv->fields, true));
			if (!$inv->fields || sizeof($inv->fields) == 0) {
				$messageStack->add(sprintf("SKU: %s not found in the database, this import was skipped!", $data['sku']));
				$skip = true;
			} else {
				if ($inv->fields['qty_stock'] < $data['quantity-purchased']) $inStock = false;
			}
			$items[] = array(
				'item_cnt'      => $itemCnt,
				'gl_type'       => 'sos',
				'sku'           => $data['sku'],
				'qty'           => $data['quantity-purchased'],
				'description'   => $data['product-name'],
				'credit_amount' => $data['item-price'],
				'gl_account'    => $inv->fields['account_sales_income'] ? $inv->fields['account_sales_income'] : MODULE_AMAZON_DEFAULT_SALES_GL_ACCT,
				'taxable'       => 0,
				'full_price'    => $inv->fields['full_price'],
				'post_date'     => substr($data['purchase-date'], 0, 10),
			);
			// preset some totals to keep running balance
			if (!isset($totals['discount']))    $totals['discount']    = 0;
			if (!isset($totals['sales_tax']))   $totals['sales_tax']   = 0;
			if (!isset($totals['total_amount']))$totals['total_amount']= 0;
			if (!isset($totals['freight']))     $totals['freight']     = 0;
			// fill in order info
			$totals['discount']    += $data['item-promotion-discount'] + $data['ship-promotion-discount'];
			$totals['sales_tax']   += $data['item-tax'];
			$totals['total_amount']+= $data['item-price'] + $data['item-tax'] + $data['shipping-price'] + $data['shipping-tax']; // missing from file: $data['gift-wrap-price'] and $data['gift-wrap-tax']
			$totals['freight']     += $data['shipping-price'];
			// check for continuation order
			$row = array_shift($rows);
			if ($runaway++ > 1000) { $messageStack->add("runaway reached, exiting!", 'error'); break; }
			if ($row) { // check for continuation order
				$nextData = $this->processRow($row);
//				$messageStack->debug("\nContinuing order check, Next order = {$nextData['order-id']} and this order = {$main['purch_order_id']}");
				if ($nextData['order-id'] == $main['purch_order_id']) {
					$data = $nextData;
					$itemCnt++;
					continue; // more items for the same order
				}
			}
			// finish main and item to post
			$main['total_amount'] = $totals['total_amount'];
			// @todo add tax, shipping, gift wrap, and notes records (add to item array)
			$items[] = array( // shipping
				'qty'          => 1,
				'gl_type'      => 'frt',
				'description'  => "Shipping Amazon # ".$data['order-id'],
				'credit_amount'=> $totals['freight'],
				'gl_account'   => MODULE_AMAZON_DEFAULT_FREIGHT_GL_ACCT,
				'taxable'      => 0,
				'post_date'    => substr($data['purchase-date'], 0, 10),
			);
			$items[] = array( // Total
				'qty'          => 1,
				'gl_type'      => 'ttl',
				'description'  => "Total Amazon # ".$data['order-id'],
				'debit_amount' => $totals['total_amount'],
				'gl_account'   => MODULE_AMAZON_DEFAULT_RECEIVABLES_GL_ACCT,
				'post_date'    => substr($data['purchase-date'], 0, 10),
			);
			$dup = $db->Execute("SELECT id FROM ".TABLE_JOURNAL_MAIN." WHERE purch_order_id='{$main['purch_order_id']}'");
			if ($dup->fields['id']) {
//				$messageStack->debug("duplicate order id = ".$dup->fields['id']." and main = ".print_r($main, true));
				$messageStack->add(sprintf("Order # %s has already been imported! It will be skipped.", $data['order-id']), 'caution');
				continue;
			}
			$ledger = new journal();
			$ledger->post_date          = substr($data['purchase-date'], 0, 10);
			$ledger->period             = $period;
			$ledger->closed             = '0';
			$ledger->journal_id         = JOURNAL_ID;
			$ledger->bill_acct_id       = $bill_acct_id;
			$ledger->journal_main_array = $main;
			$ledger->journal_rows       = $items;
			if (!$skip) {
				if (!$ledger->validate_purchase_invoice_id()) return false;
				if (!$ledger->Post('insert')) return;
				$orderCnt++;
			}
			// prepare for next order.
			$data   = $nextData;
			$itemCnt= 1;
			$items  = array();
			$totals = array();
			$inStock= true;
			$skip   = false;
		}
		if ($orderCnt) if (!$ledger->update_chart_history_periods($period)) return;
		$db->transCommit();	// finished successfully
		// ***************************** END TRANSACTION *******************************
		$messageStack->add(sprintf("Successfully posted %s Amazon transactions.", $orderCnt), 'success');
		if (DEBUG) $messageStack->write_debug();
		return true;
	}

	private function processRow($row, $delimiter="\t") {
		$data = explode($delimiter, $row);
		$output = array();
		foreach ($this->headings as $key => $value) $output[$value] = isset($data[$key]) ? $data[$key] : '';
		return $output;
	}

/*
	function processCSV($lines_array = '') {
		global $currencies;
		if (!$this->cyberParse($lines_array)) return false;  // parse the submitted string, check for errors
//echo 'parsed string = '; print_r($this->records); echo '<br />';
		$rowID = 0;
		$currentOrder = $this->records[$rowID];
		while ($rowID < count($this->records)) {
			$orderNum = $currentOrder['order-id'];
			$currentOrder['items'][] = array(
				'qty'   => $currencies->clean_value($this->records[$rowID]['quantity-purchased']),
				'desc'  => $this->records[$rowID]['product-name'],
				'total' => $currencies->clean_value($this->records[$rowID]['item-price']),
				'sku'   => $this->records[$rowID]['sku'],
				'tax'   => $this->records[$rowID]['item-tax']);
			$this->order_total    += $this->records[$rowID]['item-price'] + $this->records[$rowID]['shipping-price'];
			$this->shipping_total += $this->records[$rowID]['shipping-price'];
			$this->tax_total      += $this->records[$rowID]['item-tax'];
			$rowID++;
			$nextOrderNum = $this->records[$rowID]['order-id'];
			if ($nextOrderNum <> $orderNum) { // end of this order
				if (!$this->formatArray($currentOrder)) return false;
				if (!$this->submitJournalEntry()) return false;
//echo 'Order # ' . $currentOrder['order-id'] . ' was built and posted.<br />';
				$currentOrder = $this->records[$rowID];
				$this->order_total    = 0; // reset totals for next order
				$this->shipping_total = 0;
				$this->tax_total      = 0;
			} // else more items for the same order, loop and add to item list
		}
	    gen_add_audit_log('Imported Amazon Orders', 'OrderCnt: ' . $rowID);
		return true;
	}

	function cyberParse($lines) {
		if(!$lines) return false;
		
		$title_line = trim(array_shift($lines));	// pull header and remove extra white space characters
		$titles = explode("\t", $title_line);
		
		$records = array();
		foreach($lines as $line_num => $line) {
			$parsed_array = explode("\t", $line);
			$fields = array();
			for ($field_num = 0; $field_num < count($titles); $field_num++) {
				$fields[$titles[$field_num]] = $parsed_array[$field_num];
			}
			$records[] = $fields;
		}
		$this->records = $records;
		return true;
	}

	function formatArray($order) {
		global $db;
		$this->order = array();
		// Here we map the received xml array to the pre-defined generic structure (application specific format later)
		// <OrderRequest>
		$this->order['action']              = 'new';
		$this->order['reference_name']      = "Amazon Import ID: {$order['order-id']}";
		// <Originator>
		$this->order['store_id']            = 'amazon';
		$this->order['sales_gl_account']    = MODULE_AMAZON_DEFAULT_SALES_GL_ACCT;
		$this->order['receivables_gl_acct'] = MODULE_AMAZON_DEFAULT_RECEIVABLES_GL_ACCT;
		// <OrderSummary>
		$this->order['order_id']            = $order['order-id'];
		$this->order['post_date']           = substr($order['purchase-date'], 0, strpos($order['purchase-date'], 'T'));
		$this->order['order_total']         = $this->order_total;
		$this->order['tax_total']           = $this->tax_total;
		$this->order['freight_total']       = $this->shipping_total;
		$this->order['freight_carrier']     = MODULE_AMAZON_DEFAULT_SHIPPING_CARRIER;
		switch ($order['ship-service-level']) {
			default:
			case 'Standard':  $this->order['freight_method'] = 'GND';  break;
			case 'Expedited': $this->order['freight_method'] = '2Dpm'; break;
		}
		// <Customer> and <Billing> and <Shipping>
		$this->order['customer']['customer_id']   = $this->customer_id; // should already be in the database
		$this->order['billing']['primary_name']   = $this->billing_primary_name;
		$this->order['billing']['contact']        = $this->billing_contact;
		$this->order['billing']['address1']       = $this->billing_address1;
		$this->order['billing']['address2']       = $this->billing_address2;
		$this->order['billing']['city_town']      = $this->billing_city_town;
		$this->order['billing']['state_province'] = $this->billing_state_province;
		$this->order['billing']['postal_code']    = $this->billing_postal_code;
		$this->order['billing']['country_code']   = $this->billing_country_code;

		$this->order['shipping']['primary_name']  = $order['recipient-name'];
		if ($order['ship-address-2']) { // then only one address line exists
			$this->order['shipping']['contact']   = $order['ship-address-1'];
			$this->order['shipping']['address1']  = $order['ship-address-2'];
			$this->order['shipping']['address2']  = $order['ship-address-3'];
		} else {
			$this->order['shipping']['contact']   = '';
			$this->order['shipping']['address1']  = $order['ship-address-1'];
			$this->order['shipping']['address2']  = $order['ship-address-3']; // should be null anyway
		}
		$this->order['shipping']['city_town']     = $order['ship-city'];
		$this->order['shipping']['state_province']= strtoupper($order['ship-state']);
		$this->order['shipping']['postal_code']   = $order['ship-postal-code'];
		$this->order['shipping']['country_code']  = $order['ship-country']; // in ISO 2 format already
		$this->order['shipping']['telephone1']    = $order['buyer-phone-number'];
		$this->order['shipping']['email']         = $order['buyer-email'];

		// <LineItems>
		$this->order['items'] = array();
		$total_weight = 0;
		foreach ($order['items'] as $item) {
			$total_weight += $this->fetchSkuWeight($item['sku']) * $item['qty'];
			$this->order['items'][] = array(
				'sku'         => $item['sku'],
				'description' => $item['desc'],
				'quantity'    => $item['qty'],
				'unit_price'  => $item['total'] / $item['qty'],
				'sales_tax'   => $item['tax'],
				'total_price' => $item['total']);
		}
		return true;
	}

// The remaining functions are specific to PhreeBooks. they need to be modified for the specific application.
// It also needs to check for errors, i.e. missing information, bad data, etc. 
	function submitJournalEntry() {
		global $messageStack;
		// set some preliminary information
		define('JOURNAL_ID',10);
		define('GL_TYPE','soo');
		if ($this->order['sales_gl_account'] <>'') { // see if requestor specifies a sales account else use default
			define('DEF_INV_GL_ACCT',$this->order['sales_gl_account']);
		} else {
			define('DEF_INV_GL_ACCT',AR_DEF_GL_SALES_ACCT);
		}
		if ($this->order['receivables_gl_acct'] <> '') { // see if requestor specifies a AR account else use default
			define('DEF_GL_ACCT',$this->order['receivables_gl_acct']);
		} else {
			define('DEF_GL_ACCT',AR_DEFAULT_GL_ACCT);
		}
		$account_type = 'c';
		$psOrd = new orders();

		// make the received string look like a form submission then post as usual
		$psOrd->account_type    = $account_type;
		$psOrd->id = ''; // should be null unless opening an existing purchase/receive
		$psOrd->journal_id      = JOURNAL_ID;
		// post date is the date of import, to use the amazon date set equal to $this->order['post_date'] (date format should already be YYYY-MM-DD)
		$psOrd->post_date       = date('Y-m-d', time());
		$psOrd->period          = gen_calculate_period($psOrd->post_date);
		$psOrd->store_id        = $this->get_account_id($this->order['store_id'], 'b');
		$psOrd->admin_id        = $_SESSION['admin_id'];
		$psOrd->rep_id          = $this->default_sales_rep;
		$psOrd->description     = SOAP_XML_SUBMITTED_SO;
		$psOrd->gl_acct_id      = DEF_GL_ACCT;

		$psOrd->freight         = $this->float(db_prepare_input($this->order['freight_total']));
		$psOrd->sales_tax       = db_prepare_input($this->order['tax_total']);
		$psOrd->total_amount    = db_prepare_input($this->order['order_total']);
		// The order ID should be set by the submitter
		$psOrd->purchase_invoice_id = db_prepare_input($this->order['order_id']);
		$psOrd->purch_order_id  = $psOrd->purchase_invoice_id; // make this the po number to transfer to invoice
		$psOrd->shipper_code    = db_prepare_input($this->order['freight_carrier'] . ':' . $this->order['freight_method']);
		$psOrd->drop_ship       = 1;
		$psOrd->waiting         = 0;
		$psOrd->closed          = 0;
		$psOrd->terminal_date   = date('Y-m-d',time());
		$psOrd->bill_add_update = 1; // force an address book update
		// see if the customer record exists
		$psOrd->short_name      = db_prepare_input($this->order['customer']['customer_id']);
		$psOrd->ship_short_name = $psOrd->short_name;
		$result = $this->checkForCustomerExists($psOrd);
		if (!$result) return false;
		$psOrd->bill_add_update = false; // do not update address fields
		$psOrd->ship_add_update = false;
		$psOrd->bill_acct_id    = $result['bill_acct_id'];
		$psOrd->bill_address_id = $result['bill_address_id'];
		$psOrd->ship_acct_id    = $result['ship_acct_id'];
		$psOrd->ship_address_id = $result['ship_address_id'];

		// Phreebooks requires a primary name or the order is not valid, use company name if exists, else contact name
		$psOrd->bill_primary_name   = ($this->order['billing']['primary_name'] <> '') ? $this->order['billing']['primary_name'] : $this->order['billing']['contact'];
		$psOrd->bill_contact        = $this->order['billing']['contact'];
		$psOrd->bill_address1       = $this->order['billing']['address1'];
		$psOrd->bill_address2       = $this->order['billing']['address2'];
		$psOrd->bill_city_town      = $this->order['billing']['city_town'];
		$psOrd->bill_state_province = $this->order['billing']['state_province'];
		$psOrd->bill_postal_code    = $this->order['billing']['postal_code'];
		$psOrd->bill_country_code   = gen_get_country_iso_3_from_2($this->order['billing']['country_code']);

		$psOrd->ship_primary_name   = ($this->order['shipping']['primary_name'] <> '') ? $this->order['shipping']['primary_name'] : $this->order['shipping']['contact'];
		$psOrd->ship_contact        = $this->order['shipping']['contact'];
		$psOrd->ship_address1       = $this->order['shipping']['address1'];
		$psOrd->ship_address2       = $this->order['shipping']['address2'];
		$psOrd->ship_city_town      = $this->order['shipping']['city_town'];
		$psOrd->ship_state_province = $this->order['shipping']['state_province'];
		$psOrd->ship_postal_code    = $this->order['shipping']['postal_code'];
		$psOrd->ship_country_code   = gen_get_country_iso_3_from_2($this->order['shipping']['country_code']);
		$psOrd->ship_telephone1     = $this->order['shipping']['telephone1'];
		$psOrd->ship_email          = $this->order['shipping']['email'];

		// check for truncation of addresses
		if (strlen($psOrd->ship_primary_name) > 32 || strlen($psOrd->ship_address1) > 32) {
			$messageStack->add('Either the Primary Name or Address has been truncated to fit in the PhreeBooks database field sizes. Customer:' . $psOrd->ship_primary_name, 'caution');
		}

		// load the item rows
		for ($i = 0; $i < count($this->order['items']); $i++) {
			$psOrd->item_rows[] = array(
				'gl_type' => GL_TYPE,
				'qty'     => db_prepare_input($this->order['items'][$i]['quantity']),
				'sku'     => db_prepare_input($this->order['items'][$i]['sku']),
				'desc'    => db_prepare_input($this->order['items'][$i]['description']),
				'price'   => db_prepare_input($this->order['items'][$i]['unit_price']),
				'acct'    => DEF_INV_GL_ACCT,
				'tax'     => 0,	// For now, make everything non-taxable
				'total'   => db_prepare_input($this->order['items'][$i]['total_price']));
		}
		
		// error check input
		if (!$psOrd->short_name) return $messageStack->add(SOAP_NO_CUSTOMER_ID, 'error');
		if (!$psOrd->post_date)  return $messageStack->add(SOAP_NO_POST_DATE, 'error');
		if (!$psOrd->period)     return $messageStack->add(SOAP_BAD_POST_DATE, 'error');

		if (!$psOrd->ship_primary_name)                                           return $messageStack->add(SOAP_NO_SHIPPING_PRIMARY_NAME, 'error');
		if (ADDRESS_BOOK_CONTACT_REQUIRED        && !$psOrd->ship_contact)        return $messageStack->add(SOAP_NO_SHIPPING_CONTACT, 'error');
		if (ADDRESS_BOOK_ADDRESS1_REQUIRED       && !$psOrd->ship_address1)       return $messageStack->add(SOAP_NO_SHIPPING_ADDRESS1, 'error');
		if (ADDRESS_BOOK_ADDRESS2_REQUIRED       && !$psOrd->ship_address2)       return $messageStack->add(SOAP_NO_SHIPPING_ADDRESS2, 'error');
		if (ADDRESS_BOOK_CITY_TOWN_REQUIRED      && !$psOrd->ship_city_town)      return $messageStack->add(SOAP_NO_SHIPPING_CITY_TOWN, 'error');
		if (ADDRESS_BOOK_STATE_PROVINCE_REQUIRED && !$psOrd->ship_state_province) return $messageStack->add(SOAP_NO_SHIPPING_STATE_PROVINCE, 'error');
		if (ADDRESS_BOOK_POSTAL_CODE_REQUIRED    && !$psOrd->ship_postal_code)    return $messageStack->add(SOAP_NO_SHIPPING_POSTAL_CODE, 'error');
		if (!$psOrd->ship_country_code)                                           return $messageStack->add(SOAP_NO_SHIPPING_COUNTRY_CODE, 'error');

		// post the sales order
//echo 'ready to post =><br />'; echo  'psOrd object = '; print_r($psOrd); echo '<br />';
		$post_success = $psOrd->post_ordr($action);
		if (!$post_success) {
			$messageStack->add('Skipped posting order # ' . $psOrd->purchase_invoice_id, 'error');
		}
		gen_add_audit_log('SOAP Sales Orders - Add', $psOrd->purchase_invoice_id, $psOrd->total_amount);
		return true;
	}

	function checkForCustomerExists($psOrd) {
		global $db, $messageStack;
		$output = array();
		$result = $db->Execute("SELECT id FROM ".TABLE_CONTACTS." WHERE type='c' AND short_name='$psOrd->short_name'");
		if ($result->RecordCount() == 0) { // record not fond, error since it needs to be there for this import to work.
			return $messageStack->add(SOAP_ACCOUNT_PROBLEM, 'error');
		} else {
			$output['ship_add_update'] = 0;
			$output['bill_acct_id']    = $result->fields['id'];
			$output['ship_acct_id']    = '0';
			// find main address to update as billing address
			$result = $db->Execute("SELECT address_id FROM ".TABLE_ADDRESS_BOOK." WHERE type='cm' AND ref_id={$output['bill_acct_id']}");
			if ($result->RecordCount() == 0) return $messageStack->add(SOAP_ACCOUNT_PROBLEM, 'error');
			$output['bill_address_id'] = $result->fields['address_id'];
			$output['ship_address_id'] = '0'; // don't add ship to address
		}
		return $output;
	}
	
	function fetchSkuWeight($sku) {
		global $db;
		$result = $db->Execute("SELECT item_weight FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
		return $result->RecordCount() ? $result->fields['item_weight'] : 0;
	}
*/
	function dumpAmazon() {
	    global $db, $messageStack;
	    $separator = "\t";
	    $file_name = "amazon_upload.txt";
	    //		$separator = ",";
	    //		$file_name = "amazon_upload.csv";
	
	    $result = $db->Execute("SELECT * FROM ".TABLE_INVENTORY." WHERE amazon='1' AND inactive='0'");
	    $line = array();
	    //		$output = "TemplateType=ConsumerElectronics,Version=1.01,This row for Amazon.com use only.  Do not modify or delete.,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,Rebates,,,,,,,,PC,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,PDA,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,Misc Consumer Electronics";
	    $output = "TemplateType=ConsumerElectronics,Version=1.01,This row for Amazon.com use only.  Do not modify or delete.";
	    $line[] = str_replace(",",$separator,$output);
	    //		$output = "sku,standard-product-id,product-id-type,title,manufacturer,brand,mfr-part-number,merchant-catalog-number,bullet-point1,bullet-point2,bullet-point3,bullet-point4,bullet-point5,description,product_type,legal-disclaimer,prop-65,item-type,used-for1,used-for2,used-for3,used-for4,used-for5,other-item-attributes1,other-item-attributes2,other-item-attributes3,other-item-attributes4,other-item-attributes5,subject-content1,subject-content2,subject-content3,subject-content4,subject-content5,search-terms1,search-terms2,search-terms3,search-terms4,search-terms5,platinum-keywords1,platinum-keywords2,platinum-keywords3,platinum-keywords4,platinum-keywords5,main-image-url,other-image-url1,other-image-url2,other-image-url3,other-image-url4,other-image-url5,other-image-url6,other-image-url7,other-image-url8,item-weight-unit-of-measure,item-weight,item-length-unit-of-measure,item-length,item-height,item-width,package-weight-unit-of-measure,package-weight,package-length-unit-of-measure,package-length,package-height,package-width,product-tax-code,launch-date,map,msrp,item-price,sale-price,sale-from-date,sale-through-date,quantity,leadtime-to-ship,is-gift-message-available,is-giftwrap-available,update-delete,target-audience-keywords1,target-audience-keywords2,target-audience-keywords3,item-condition,PC_AdditionalDrives1,PC_AdditionalDrives2,PC_AdditionalDrives3,PC_AdditionalDrives4,PC_AdditionalDrives5,PC_AdditionalDrives6,PC_AdditionalDrives7,PC_AdditionalDrives8,PC_AdditionalDrives9,PC_AdditionalDrives10,PC_HardDriveSize1,PC_HardDriveSize2,PC_HardDriveSize3,PC_HardDriveSize4,PC_HardDriveSize5,PC_HardDriveSize6,PC_HardDriveSize7,PC_HardDriveSize8,PC_HardDriveSizeUnitOfMeasure,PC_HardDriveInterface1,PC_HardDriveInterface2,PC_HardDriveInterface3,PC_HardDriveInterface4,PC_SoftwareIncluded,PC_ComputerMemoryType1,PC_ComputerMemoryType2,PC_ComputerMemoryType3,PC_ComputerMemoryType4,PC_ComputerMemoryType5,PC_ComputerMemoryType6,PC_ComputerMemoryType7,PC_ComputerMemoryType8,PC_ComputerMemoryType9,PC_ComputerMemoryType10,PC_RAMSize,PC_RAMSizeUnitOfMeasure,PC_MemorySlotsAvailable,PC_ProcessorBrand,PC_ProcessorSpeed,PC_ProcessorSpeedUnitOfMeasure,PC_ProcessorType,PC_ProcessorCount,PC_ScreenResolution,PC_ScreenSize,PC_ScreenSizeUnitOfMeasure,PC_OperatingSystem1,PC_OperatingSystem2,PC_HardwarePlatform,PC_U-RackSize,PC_WirelessType1,PC_WirelessType2,PC_WirelessType3,PC_GraphicsDescription1,PC_GraphicsRAMSize1,PC_GraphicsCardInterface1,PC_GraphicsDescription2,PC_GraphicsRAMSize2,PC_GraphicsCardInterface2,PC_GraphicsRAMSizeUnitOfMeasure,PC_AdditionalFeatures,rebate-start-date1,rebate-end-date1,rebate-message1,rebate-name1,rebate-start-date2,rebate-end-date2,rebate-message2,rebate-name2,PDA_HardDriveSize,PDA_HardDriveSizeUnitOfMeasure,PDA_SoftwareIncluded,PDA_ComputerMemoryType1,PDA_ComputerMemoryType2,PDA_ComputerMemoryType3,PDA_ComputerMemoryType4,PDA_ComputerMemoryType5,PDA_ComputerMemoryType6,PDA_ComputerMemoryType7,PDA_ComputerMemoryType8,PDA_ComputerMemoryType9,PDA_ComputerMemoryType10,PDA_MemorySlotsAvailable,PDA_RAMSize,PDA_RAMSizeUnitOfMeasure,PDA_ProcessorSpeed,PDA_ProcessorSpeedUnitOfMeasure,PDA_ProcessorType,PDA_ROMSize,PDA_ROMSizeUnitOfMeasure,PDA_ScreenResolution,PDA_ColorScreen,PDA_ScreenSize,PDA_ScreenSizeUnitOfMeasure,PDA_OperatingSystem1,PDA_OperatingSystem2,PDA_OperatingSystem3,PDA_OperatingSystem4,PDA_OperatingSystem5,PDA_WirelessType1,PDA_WirelessType2,PDA_WirelessType3,PDA_AdditionalFeatures,PDA_PDABaseModel1,PDA_PDABaseModel2,PDA_PDABaseModel3,PDA_PDABaseModel4,PDA_PDABaseModel5,CE_Color,CE_SpeakerDiameter,CE_SpeakerDiameterUnitOfMeasure,CE_ColorMap,CE_Voltage,CE_Wattage,CE_PowerSource,CE_AdditionalFeatures,CE_VehicleSpeakerSize,CE_TelephoneType1,CE_TelephoneType2,CE_TelephoneType3,CE_TelephoneType4,CE_PDABaseModel1,CE_PDABaseModel2,CE_PDABaseModel3,CE_PDABaseModel4,CE_PDABaseModel5,CE_DigitalMediaFormat,CE_HomeAutomationCommunicationDevice,CE_DigitalAudioCapacity,CE_HolderCapacity,CE_MemorySlotsAvailable,CE_RAMSize,CE_RAMSizeUnitOfMeasure,CE_ScreenResolution,CE_ColorScreen,CE_ScreenSize,CE_ScreenSizeUnitOfMeasure,CE_WirelessType1,CE_WirelessType2,CE_WirelessType3,CE_HardDriveSize1,CE_HardDriveSize2,CE_HardDriveSize3,CE_HardDriveSize4,CE_HardDriveSize5,CE_HardDriveSize6,CE_HardDriveSize7,CE_HardDriveSize8,CE_HardDriveSizeUnitOfMeasure,CE_HardDriveInterface1,CE_HardDriveInterface2,CE_HardDriveInterface3,CE_HardDriveInterface4,CE_OperatingSystem1,CE_OperatingSystem2,CE_OperatingSystem3,CE_OperatingSystem4,CE_OperatingSystem5,CE_HardwarePlatform,CE_ComputerMemoryType1,CE_ComputerMemoryType2,CE_ComputerMemoryType3,CE_ComputerMemoryType4,CE_ComputerMemoryType5,CE_ComputerMemoryType6,CE_ComputerMemoryType7,CE_ComputerMemoryType8,CE_ComputerMemoryType9,CE_ComputerMemoryType10,CE_ItemPackageQuantity,id";
	    //		$line[] = $output;
	    //		$line[] = str_replace(",","\t",$output);
	    $first_run = true;
	    while (!$result->EOF) {
	        // fetch the price levels
	        if (ZENCART_USE_PRICE_SHEETS) {
	            $sql = "SELECT id, default_levels FROM ".TABLE_PRICE_SHEETS."
					WHERE '".date('Y-m-d',time())."'>=effective_date AND sheet_name = '".MODULE_AMAZON_DEFAULT_PRICE_SHEET."' AND inactive='0'";
	            $default_levels = $db->Execute($sql);
	            if ($default_levels->RecordCount() == 0) {
	                $messageStack->add('Couldn\'t find a default price level for price sheet: '.MODULE_AMAZON_DEFAULT_PRICE_SHEET,'error');
	                return false;
	            }
	            $sql = "SELECT price_levels FROM ".TABLE_INVENTORY_SPECIAL_PRICES."
					WHERE inventory_id={$result->fields['id']} AND price_sheet_id={$default_levels->fields['id']}";
	            $special_levels = $db->Execute($sql);
	            if ($special_levels->RecordCount() > 0) {
	                $price_levels = $special_levels->fields['price_levels'];
	            } else {
	                $price_levels = $default_levels->fields['default_levels'];
	            }
	        }
	        //build the uplink array sequenced properly
	        $output = array();
	        $output['sku'] = $result->fields['sku'];
	        // test for duplicate upc's
	        if (!$result->fields['upc_code'] && !$result->fields['amazon_asin']) { // find the dup
	            $messageStack->add('Missing UPC code and Amazon ASIN ID for SKU: '.$result->fields['sku'], 'error');
	        }
	        $output['standard-product-id']     = $result->fields['amazon_asin'] ? $result->fields['amazon_asin'] : $result->fields['upc_code'];
	        $output['product-id-type']         = $result->fields['amazon_asin'] ? 'ASIN' : 'UPC';
	        $output['title']                   = $result->fields['description_sales'];
	        $output['manufacturer']            = $result->fields['manufacturer'];
	        $output['brand']                   = $result->fields['brand'];
	        $output['mfr-part-number']         = $result->fields['oem_sku'];
	        $output['merchant-catalog-number'] = $result->fields['merchant_catalog_number'];
	        $output['bullet-point1']           = $result->fields['bullet_point_1'];
	        $output['bullet-point2']           = $result->fields['bullet_point_2'];
	        $output['bullet-point3']           = $result->fields['bullet_point_3'];
	        $output['bullet-point4']           = $result->fields['bullet_point_4'];
	        $output['bullet-point5']           = $result->fields['bullet_point_5'];
	        $output['description']             = $result->fields['description'];
	        $output['product_type']            = 'ConsumerElectronics'; // $result->fields['product_type'];
	        $output['legal-disclaimer']        = $result->fields['legal_disclaimer'];
	        $output['prop-65']                 = $result->fields['prop_65'] ? 'true' : 'false';
	        $output['item-type']               = $result->fields['item_type']; // item_type_keyword ???
//			$output['used-for1'] = ''; // $result->fields['used-for1'];
//			$output['used-for2'] = ''; // $result->fields['used-for2'];
//			$output['used-for3'] = ''; // $result->fields['used-for3'];
//			$output['used-for4'] = ''; // $result->fields['used-for4'];
//			$output['used-for5'] = ''; // $result->fields['used-for5'];
	        $output['other-item-attributes1']  = $result->fields['other_item_attributes_1'];
	        $output['other-item-attributes2']  = $result->fields['other_item_attributes_2'];
	        $output['other-item-attributes3']  = $result->fields['other_item_attributes_3'];
	        $output['other-item-attributes4']  = $result->fields['other_item_attributes_4'];
	        $output['other-item-attributes5']  = $result->fields['other_item_attributes_5'];
//			$output['subject-content1'] = ''; // $result->fields['subject-content1'];
//			$output['subject-content2'] = ''; // $result->fields['subject-content2'];
//			$output['subject-content3'] = ''; // $result->fields['subject-content3'];
//			$output['subject-content4'] = ''; // $result->fields['subject-content4'];
//			$output['subject-content5'] = ''; // $result->fields['subject-content5'];
	        $output['search-terms1']           = $result->fields['search_terms_1'];
	        $output['search-terms2']           = $result->fields['search_terms_2'];
	        $output['search-terms3']           = $result->fields['search_terms_3'];
	        $output['search-terms4']           = $result->fields['search_terms_4'];
	        $output['search-terms5']           = $result->fields['search_terms_5'];
//			$output['platinum-keywords1'] = $result->fields['platinum-keywords1'];
//			$output['platinum-keywords2'] = $result->fields['platinum-keywords2'];
//			$output['platinum-keywords3'] = $result->fields['platinum-keywords3'];
//			$output['platinum-keywords4'] = $result->fields['platinum-keywords4'];
//			$output['platinum-keywords5'] = $result->fields['platinum-keywords5'];
// Amazon only supports one level, so we'll use the first path dir and filename only
	        $imageType = substr($result->fields['image_with_path'], -3);
	        if (in_array($imageType, array('jpg', 'JPG', 'peg','PEG', 'gif', 'GIF'))) {
	            $output['main-image-url'] = PPS_FULL_URL . $result->fields['image_with_path'];
	        } else {
	            $output['main-image-url'] = '';
	            $messageStack->add_session('Image at path: '.$result->fields['image_with_path'].' for sku ' . $output['sku'] . ' must be of type jpg or gif for amazon!', 'error');
	        }
//			$output['other-image-url1'] = $result->fields['other-image-url1'];
//			$output['other-image-url2'] = $result->fields['other-image-url2'];
//			$output['other-image-url3'] = $result->fields['other-image-url3'];
//			$output['other-image-url4'] = $result->fields['other-image-url4'];
//			$output['other-image-url5'] = $result->fields['other-image-url5'];
//			$output['other-image-url6'] = $result->fields['other-image-url6'];
//			$output['other-image-url7'] = $result->fields['other-image-url7'];
//			$output['other-image-url8'] = $result->fields['other-image-url8'];
	        $output['item-weight-unit-of-measure'] = 'LB'; // $result->fields['item_weight_unit_of_measure'];
	        $output['item-weight']      = $result->fields['item_weight'];
	        $output['item-length-unit-of-measure'] = 'IN'; // $result->fields['item_length_unit_of_measure'];
	        $output['item-length']      = $result->fields['dim_length'];
	        $output['item-height']      = $result->fields['dim_height'];
	        $output['item-width']       = $result->fields['dim_width'];
	        $output['package-weight-unit-of-measure'] = 'LB'; // $result->fields['package_weight_unit_of_measure'];
	        if ($result->fields['item_weight'] == 0) {
	            $messageStack->add_session('Item ' . $result->fields['sku'] . ' has no weight. Please edit the inventory record and add a weight.', 'error');
	        }
	        $output['package-weight']   = $result->fields['package_weight']>0 ? $result->fields['package_weight'] : ceil($result->fields['item_weight']);
	        $output['package-length-unit-of-measure'] = 'IN'; // $result->fields['package_length_unit_of_measure'];
	        $output['package-length']   = $result->fields['package_length']>0 ? $result->fields['package_length'] : ceil($result->fields['dim_length']);
	        $output['package-height']   = $result->fields['package_height']>0 ? $result->fields['package_height'] : ceil($result->fields['dim_height']);
	        $output['package-width']    = $result->fields['package_width']>0  ? $result->fields['package_width']  : ceil($result->fields['dim_width']);
//			$output['product-tax-code'] = $result->fields['product_tax_code']; // *****************************
	        $output['launch-date']      = substr($result->fields['creation_date'], 0, 10);
//			$output['release-date']     = $result->fields['creation_date'];
	        $output['msrp']             = $result->fields['full_price'];
	        $prices = inv_calculate_prices($result->fields['item_cost'], $result->fields['full_price'], $price_levels);
	        $output['currency']         = 'USD';
//			if ($result->fields['map_price'] > 0) $output['map-price'] = $result->fields['map_price'];
	        $output['item-price']       = $prices[0]['price'];
//			$output['sale-price']       = ''; // $result->fields['sale-price'];
//			$output['sale-from-date']   = ''; // $result->fields['sale-from-date'];
//			$output['sale-through-date']= ''; // $result->fields['sale-through-date'];
// for assemblies, we need to determine if we have enough product to assemble a unit and adjust qty accordingly
	        if ($result->fields['inventory_type'] == 'as') {
	            $sql = "SELECT a.qty, i.quantity_on_hand
				  FROM ".TABLE_INVENTORY_ASSY_LIST." a JOIN " . TABLE_INVENTORY . " i on a.sku = i.sku
				  where a.ref_id = " . $result->fields['id'];
	            $bom_list = $db->Execute($sql);
	            $min_qty = 9999;
	            while (!$bom_list->EOF) {
	                $qty = $bom_list->fields['quantity_on_hand'] / $bom_list->fields['qty'];
	                $min_qty = min($min_qty, floor($qty));
	                $bom_list->MoveNext();
	            }
	            $result->fields['quantity_on_hand'] = $min_qty;
	        }
	        $available = $result->fields['quantity_on_hand'] - $result->fields['quantity_on_sales_order'] - $result->fields['quantity_on_allocation'];
	        $output['quantity'] = max(0, $available); // no negative numbers
	        $output['leadtime-to-ship'] = $result->fields['leadtime_to_ship'];
//			$output['is-discontinued-by-manufacturer'] = '';
//			$output['update-delete'] = $result->fields['update_delete'];
	        $output['target-audience-keywords1'] = 'people';
	        $output['target-audience-keywords2'] = 'professional-audience';
//			$output['target-audience-keywords3'] = '';
	        $output['item-condition'] = 'New'; // $result->fields['item_condition'];
//			$output['condition_note'] = $result->fields['condition_note'];
/*
	         $output[] = ''; // $result->fields['rebate-start-date1'];
	         $output[] = ''; // $result->fields['rebate-end-date1'];
	         $output[] = ''; // $result->fields['rebate-message1'];
	         $output[] = ''; // $result->fields['rebate-name1'];
	         $output[] = ''; // $result->fields['rebate-start-date2'];
	         $output[] = ''; // $result->fields['rebate-end-date2'];
	         $output[] = ''; // $result->fields['rebate-message2'];
	         $output[] = ''; // $result->fields['rebate-name2'];
*/
	        $output['CE_Voltage'] = substr($result->fields['cat_attrib_01'], 0, strpos($result->fields['cat_attrib_01'], ' '));
/*
	         $output[] = ''; // $result->fields['CE_Color'];
	         $output[] = ''; // $result->fields['CE_ColorMap'];
	         $output[] = ''; // $result->fields['CE_Wattage'];
	         $output[] = ''; // $result->fields['CE_PowerSource'];
	         $output[] = ''; // $result->fields['CE_AdditionalFeatures'];
	         $output[] = ''; // $result->fields['CE_ItemPackageQuantity'];
*/

	        // implode it and download to user
	        if ($first_run) { // save the keys for the first pass
	            $headings = array_keys($output);
	            $line[] = implode($separator, $headings);
	            $first_run = false;
	        }
	        // if it's comma separated delimit
	        if ($separator == ",") foreach ($output as $key=>$value) if (strpos($value,',') !== false) $output[$key] = '"' . $value . '"';
	        $line[] = implode($separator, $output);
	        $result->MoveNext();
	    }
	
	    // BOF - Special section for upload of free shipping override
	    if (false) {
	        $result = $db->Execute("SELECT sku, amazon_freeship FROM ".TABLE_INVENTORY." WHERE amazon='1' AND inactive='0'");
	        $line = array();
	        $output = "TemplateType=Overrides,Version=1.01,This row for Amazon.com use only.  Do not modify or delete.";
	        $line[] = str_replace(",",$separator,$output);
	        $first_run = true;
	        while (!$result->EOF) {
	            if ($result->fields['amazon_freeship']) {
	                $output = array();
	                $output['sku'] = $result->fields['sku'];
	                $output['Locale1'] = 'ContinentalUS';
	                $output['FulfillmentServiceLevel1'] = 'Standard';
	                $output['ShippingAmt1'] = '0.00';
	                $output['Type1'] = 'Exclusive';
//			        $output['DoNotShip1'] = '';  // possible values are true and false
	                // more fields allowed Locale2 through DoNotShip5
	                // There is no way to delete this directly on Amazon, send feed with all SKUs set to
	                // delete to remove the Free Shipping Override flag
	                $output['UpdateDelete'] = 'Update';
	                // implode it and download to user
	                if ($first_run) { // save the keys for the first pass
	                    $headings = array_keys($output);
	                    $line[] = implode($separator, $headings);
	                    $first_run = false;
	                }
	                // if it's comma separated delimit
	                if ($separator == ",") foreach ($output as $key=>$value) if (strpos($value,',') !== false) $output[$key] = '"' . $value . '"';
	                $line[] = implode($separator, $output);
	            }
	            $result->MoveNext();
	        }
	    }
	    // EOF - Special section for upload of free shipping overrride
	
	    $contents = implode("\n", $line);
	    $contents .= "\n";
	
	    if ($messageStack->size == 0) {
	        header('Content-type: text/plain');
	        header('Content-Length: ' . strlen($contents));
	        header('Content-Disposition: attachment; filename=' . $file_name);
	        header('Expires: 0');
	        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	        header('Pragma: public');
	        echo $contents;
	        exit();
	    }
	}

}
?>