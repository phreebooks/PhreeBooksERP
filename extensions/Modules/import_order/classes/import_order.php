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
//  Path: /modules/import_order/classes/import_order.php
//

class import_order {
	public $records = array();

  	function __construct() {
  		$this->records = array();
  	}

  	/**
  	 * this function will import orders from a csv file to the general journal.
  	 * @param string $lines_array
  	 * @return void|boolean
  	 */
  	function processCSV($filename, $function='Sales') {
  		global $admin, $messageStack;
  		$rows = $this->csv_to_array($_FILES[$filename]['tmp_name'], $delimiter=',');
  		$messageStack->debug("\nfinished parsing, extracted number of rows = ".sizeof($rows));
  	  	switch ($function) {
  			default:
  			case 'Sales':
  				define('JOURNAL_ID',12);
  				define('GL_TYPE','sos');
  				break;
  			case 'SalesOrder':
  				define('JOURNAL_ID',10);
  				define('GL_TYPE','soo');
  		}
  		$tax_rates = ord_calculate_tax_drop_down('c');
  		$count = 0;
  		foreach ($rows as $csv_data) {
	  		// map csv to xml soap format
  			$soap_order = new xml_orders();
  			$soap_order->order = array();
  			$soap_order->order['reference']              = $csv_data['Reference'];
  			$soap_order->order['store_id']               = $csv_data['StoreID'];
  			$soap_order->order['sales_gl_account']       = $csv_data['SalesGLAccount'];
  			$soap_order->order['receivables_gl_acct']    = $csv_data['ReceivablesGLAccount'];
  			$soap_order->order['order_id']               = $csv_data['OrderID'];
  			$soap_order->order['purch_order_id']         = $csv_data['PurchaseOrderID'];
  			$soap_order->order['post_date']              = $csv_data['OrderDate'];
  			$soap_order->order['order_total']            = $csv_data['OrderTotal'];
  			$soap_order->order['tax_total']              = $csv_data['TaxTotal'];
  			$soap_order->order['freight_total']          = $csv_data['ShippingTotal'];
  			$soap_order->order['freight_carrier']        = $csv_data['ShippingCarrier'];
  			$soap_order->order['freight_method']         = $csv_data['ShippingMethod'];
  			$soap_order->order['rep_id']                 = $csv_data['SalesRepID'];
  			// <Payment>
  			$soap_order->order['payment']['holder_name'] = $csv_data['Payment->CardHolderName'];
  			$soap_order->order['payment']['method']      = $csv_data['Payment->Method'];
  			$soap_order->order['payment']['type']        = $csv_data['Payment->CardType'];
  			$soap_order->order['payment']['card_number'] = $csv_data['Payment->CardNumber'];
  			$soap_order->order['payment']['exp_date']    = $csv_data['Payment->ExpirationDate'];
  			$soap_order->order['payment']['cvv2']        = $csv_data['Payment->CVV2Number'];
  			// <Customer> and <Billing> and <Shipping>
  			$types = array ('customer', 'billing', 'shipping');
  			foreach ($types as $value) {
  				$entry = ucfirst($value);
  				$soap_order->order[$value]['primary_name']   = $csv_data['$entry->CompanyName'];
  				$soap_order->order[$value]['contact']        = $csv_data['$entry->Contact'];
  				$soap_order->order[$value]['address1']       = $csv_data['$entry->Address1'];
  				$soap_order->order[$value]['address2']       = $csv_data['$entry->Address2'];
  				$soap_order->order[$value]['city_town']      = $csv_data['$entry->CityTown'];
  				$soap_order->order[$value]['state_province'] = $csv_data['$entry->StateProvince'];
  				$soap_order->order[$value]['postal_code']    = $csv_data['$entry->PostalCode'];
  				$soap_order->order[$value]['country_code']   = $csv_data['$entry->CountryCode'];
  				$soap_order->order[$value]['telephone']      = $csv_data['$entry->Telephone'];
  				$soap_order->order[$value]['email']          = $csv_data['$entry->Email'];
  				if ($value == 'customer') { // additional information for the customer record
  					$soap_order->order[$value]['customer_id']  = $csv_data['$entry->CustomerID'];
  				}
  			}
  			// if billing or shipping is blank, use customer address
  			if ($soap_order->order['billing']['primary_name'] == '' && $soap_order->order['billing']['contact'] == '') {
  				$soap_order->order['billing'] = $soap_order->order['customer'];
  			}
  			if ($soap_order->order['shipping']['primary_name'] == '' && $soap_order->order['shipping']['contact'] == '') {
  				$soap_order->order['shipping'] = $soap_order->order['customer'];
  			}
  			// <LineItems>
  			$soap_order->order['items'] = array();
  			if (!is_array($csv_data['Item'])) $csv_data['Item'] = array($csv_data['Item']);
  			foreach ($csv_data['Item'] as $entry) {
  				$item = array();
  				$sku  = $entry->ItemID;
  				// try to match sku and get the sales gl account
  				$result = $admin->DataBase->query("SELECT account_sales_income FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
  				if ($result->RecordCount() > 0) {
  					$item['sku']     = $sku;
  					$item['gl_acct'] = $result->fields['account_sales_income'];
  				} else {
  					$result = $admin->DataBase->query("SELECT sku, account_sales_income FROM ".TABLE_INVENTORY." WHERE description_short='$sku'");
  					$item['sku']     = $result->fields['sku'];
  					$item['gl_acct'] = $result->fields['account_sales_income'];
  				}
  				$item['description'] = $entry->Description;
  				$item['quantity']    = $entry->Quantity;
  				$item['unit_price']  = $entry->UnitPrice;
  				$item['tax_percent'] = $entry->SalesTaxPercent;
  				$item['taxable']     = $this->guess_tax_id($tax_rates, $item['tax_percent']);
  				$item['total_price'] = $entry->TotalPrice;
  				$soap_order->order['items'][] = $item;
  			}
//			if (function_exists('xtra_order_data')) xtra_order_data($soap_order->order, $csv_data);
  			// Now post like soap transaction
  			$soap_order->buildJournalEntry();
		}
		if (DEBUG) $messageStack->write_debug();
		$messageStack->add("Total lines processed: ".sizeof($rows), "success");
  	}

  	function csv_to_array($filename='', $delimiter=',') {
  		if(!file_exists($filename) || !is_readable($filename)) return FALSE;
  		$header = NULL;
  		$data = array();
  		if (($handle = fopen($filename, 'r')) !== FALSE) {
  			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
  				if (!$header) $header = $row;
  				else $data[] = array_combine($header, $row);
  			}
  			fclose($handle);
  		}
  		return $data;
  	}

}

?>