<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /admin/includes/classes/phreebooks.php
//

// Hook for including customization of product attributes
if (file_exists(DIR_FS_ADMIN . 'soap/extra_actions/extra_order_actions.php')) { include (DIR_FS_ADMIN . 'soap/extra_actions/extra_order_actions.php'); }
// EOF - Hook for customization

// pull in the general phreebooks parser and general functions
require_once(DIR_FS_ADMIN . 'soap/classes/parser.php');

class phreebooks extends parser {

  function submitXML($action, $data = '') {
	global $db, $messageStack;
	switch ($action) {
	  case 'download':
		$strXML = $this->buildOrderDownloadXML($data);
		$strXML = utf8_encode($strXML);
//echo 'Zencart order array = '; print_r($data); echo '<br>';
//echo 'Submit XML string = <pre>' . htmlspecialchars($strXML) . '</pre><br>';
		$this->response = $this->doCURLRequest('POST', MODULE_PHREEBOOKS_ORDER_DOWNLOAD_URL . '?db=' . MODULE_PHREEBOOKS_ORDER_DOWNLOAD_DB, $strXML);
//echo 'XML response (at the ZenCart Side from PhreeBooks) => <pre>' . htmlspecialchars($this->response) . '</pre><br>' . chr(10);
		if (!$this->response) return false;
		if (!$objXML = $this->xml_to_object($this->response)) return false;  // parse the response string, check for errors
//echo 'Parsed response string = '; print_r($objXML); echo '<br>';
		$this->result = $objXML->Response->Result;
		$this->code   = $objXML->Response->Code;
		$this->text   = $objXML->Response->Text;
		$this->close  = $objXML->Response->SuccessfulOrders;
		$this->failed = $objXML->Response->FailedOrders;
		if ($this->close) {
		  $db->Execute("update " . TABLE_ORDERS . " set phreebooks = 1, last_modified = now() where orders_id in (" . $this->close . ")");
		  if (defined('MODULE_PHREEBOOKS_ORDER_DOWNLOAD_ORDER_STATUS') && MODULE_PHREEBOOKS_ORDER_DOWNLOAD_ORDER_STATUS) {
			// insert a new status in the order status table
			$pb_orders = explode(',', $this->close);
			if (is_array($pb_orders)) foreach ($pb_orders as $value) {
			  $db->Execute("insert into " . TABLE_ORDERS_STATUS_HISTORY . " set
			    orders_id = '" . trim($value) . "',
			    orders_status_id = " . MODULE_PHREEBOOKS_ORDER_DOWNLOAD_ORDER_STATUS . ",
			    date_added = now(),
			    customer_notified = '0',
			    comments = '" . 'Order is in process.' . "'");
			}
			// update the status in the orders table
			$db->Execute("update " . TABLE_ORDERS . " set
			  orders_status = " . MODULE_PHREEBOOKS_ORDER_DOWNLOAD_ORDER_STATUS . ",
			  last_modified = now()
			  where orders_id in (" . $this->close . ")");
		  }
		  $messageStack->add(sprintf('Orders successfully downloaded to PhreeBooks: %s', $this->close), 'success');
		}
		if ($this->failed) {
		  $messageStack->add(sprintf('Orders the failed to download to PhreeBooks: %s' . "<br />" . $this->text, $this->failed), 'error');
		}
		break;
	  default:
		$messageStack->add('Invalid action requested in PhreeBooks interface class. Aborting!', 'error');
	}
  }

  function buildOrderDownloadXML($orders) { // builds download XML string for orders
	// clean up some fields
	$strXML  = '<?xml version="1.0" encoding="UTF-8" ?>' . chr(10);
	$strXML .= '<Request>' . chr(10);
	$strXML .= $this->xmlEntry('UserID',   MODULE_PHREEBOOKS_ORDER_DOWNLOAD_USER);
	$strXML .= $this->xmlEntry('Password', MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PW);
	$strXML .= $this->xmlEntry('Version',  '1.00');
	$strXML .= $this->xmlEntry('Function', 'SalesOrder');
	$strXML .= $this->xmlEntry('Action',   'New');

	foreach ($orders as $oID) {
      $data      = new order($oID); // open the order and read all information
	  $data->id  = $oID;
	  $temp       = explode(' ', $data->info['date_purchased']);
	  $order_date = $temp[0]; // remove the time from the order date stamp

	  $strXML .= '<Order>' . chr(10);
	  $strXML .= $this->xmlEntry('Reference',      $oID);
	  $strXML .= $this->xmlEntry('StoreID',        MODULE_PHREEBOOKS_ORDER_DOWNLOAD_STORE_ID);
	  $strXML .= $this->xmlEntry('SalesRepID',     MODULE_PHREEBOOKS_ORDER_DOWNLOAD_SALES_REP_ID);
	  $strXML .= $this->xmlEntry('SalesGLAccount', MODULE_PHREEBOOKS_ORDER_DOWNLOAD_SALES_GL_ACCOUNT);
	  $strXML .= $this->xmlEntry('ReceivablesGLAccount', MODULE_PHREEBOOKS_ORDER_DOWNLOAD_AR_GL_ACCOUNT);
	  $strXML .= $this->xmlEntry('OrderID',        MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PREFIX . $data->id);
//	  $strXML .= $this->xmlEntry('PurchaseOrderID', 'TBD');
	  $strXML .= $this->xmlEntry('OrderDate',      $order_date);
	  $strXML .= $this->xmlEntry('OrderTotal',     $data->info['total']);
	  $strXML .= $this->xmlEntry('TaxTotal',       $data->info['tax']);
	  $freight_totals = $this->getClassInfo('ot_shipping', $data->totals);
	  $strXML .= $this->xmlEntry('ShippingTotal',  $this->clean_value($freight_totals['text'], $data->info['currency']));
	  $strXML .= $this->xmlEntry('ShippingCarrier',$freight_totals['title']);
	  $strXML .= $this->xmlEntry('ShippingMethod', $freight_totals['title']);
//    TBD also need to include discounts, fees and other order total modules information
// 	  $strXML .= $this->xmlEntry('OrderNotes',     $data->info['ip_address']);
	  $strXML .= '<Payment>' . chr(10);
	  $strXML .= $this->xmlEntry('CardHolderName', $data->billing['name']);
	  $strXML .= $this->xmlEntry('Method',         $data->info['payment_method']);
	  $strXML .= $this->xmlEntry('CardType',       $data->info['cc_type']);
	  $strXML .= $this->xmlEntry('CardNumber',     $data->info['cc_number']);
	  $strXML .= $this->xmlEntry('ExpirationDate', $data->info['cc_expires']);
	  $strXML .= $this->xmlEntry('CVV2Number',     $data->info['cc_cvv']);
	  if (function_exists('zc_set_hint')) $strXML .= $this->xmlEntry('CardHint', zc_set_hint($data->id));
	  if (function_exists('zc_set_value')) {
	    $temp = strtr(base64_encode(zc_set_value($data->id)), '+/=', '-_,');
	    $strXML .= $this->xmlEntry('CardEncodeValue', $temp);
	  }
	  $strXML .= '</Payment>' . chr(10);
	  $strXML .= '<Customer>' . chr(10);
	  switch (PHREEBOOKS_DOWNLOAD_USER_ID_METHOD) {
	    case 'Telephone': $customer_id = ereg_replace("[^0-9]", "", $data->customer['telephone']); break;
	    case 'Email':
	    default:          $customer_id = $data->customer['email_address']; break;
	  }
	  $strXML .= $this->xmlEntry('CustomerID',   $customer_id);
	  $strXML .= $this->xmlEntry('CompanyName',  $data->customer['company']);
	  $strXML .= $this->xmlEntry('Contact',      $data->customer['name']);
	  $strXML .= $this->xmlEntry('Telephone',    $data->customer['telephone']);
	  $strXML .= $this->xmlEntry('Email',        $data->customer['email_address']);
	  $strXML .= $this->xmlEntry('Address1',     $data->customer['street_address']);
	  $strXML .= $this->xmlEntry('Address2',     $data->customer['suburb']);
	  $strXML .= $this->xmlEntry('CityTown',     $data->customer['city']);
	  $codes = $this->getCodes($data->customer['country'], $data->customer['state']);
	  $strXML .= $this->xmlEntry('StateProvince',$codes['state']);
	  $strXML .= $this->xmlEntry('PostalCode',   $data->customer['postcode']);
	  $strXML .= $this->xmlEntry('CountryCode',  $codes['country']);
	  $strXML .= '</Customer>' . chr(10);
	  $strXML .= '<Billing>' . chr(10);
	  $strXML .= $this->xmlEntry('CompanyName',  $data->billing['company']);
	  $strXML .= $this->xmlEntry('Contact',      $data->billing['name']);
	  $strXML .= $this->xmlEntry('Address1',     $data->billing['street_address']);
	  $strXML .= $this->xmlEntry('Address2',     $data->billing['suburb']);
	  $strXML .= $this->xmlEntry('CityTown',     $data->billing['city']);
	  $codes = $this->getCodes($data->billing['country'], $data->billing['state']);
	  $strXML .= $this->xmlEntry('StateProvince',$codes['state']);
	  $strXML .= $this->xmlEntry('PostalCode',   $data->billing['postcode']);
	  $strXML .= $this->xmlEntry('CountryCode',  $codes['country']);
	  $strXML .= '</Billing>' . chr(10);
	  $strXML .= '<Shipping>' . chr(10);
	  $strXML .= $this->xmlEntry('CompanyName',  $data->delivery['company']);
	  $strXML .= $this->xmlEntry('Contact',      $data->delivery['name']);
	  $strXML .= $this->xmlEntry('Address1',     $data->delivery['street_address']);
	  $strXML .= $this->xmlEntry('Address2',     $data->delivery['suburb']);
	  $strXML .= $this->xmlEntry('CityTown',     $data->delivery['city']);
	  $codes = $this->getCodes($data->delivery['country'], $data->delivery['state']);
	  $strXML .= $this->xmlEntry('StateProvince',$codes['state']);
	  $strXML .= $this->xmlEntry('PostalCode',   $data->delivery['postcode']);
	  $strXML .= $this->xmlEntry('CountryCode',  $codes['country']);
	  $strXML .= '</Shipping>' . chr(10);
	  foreach($data->products as $item) {
	    $strXML .= '<Item>' . chr(10);
	    $strXML .= $this->xmlEntry('ItemID',      $this->find_sku($item['id'], $item['name']));
	    $strXML .= $this->xmlEntry('Description', $item['name']);
	    $strXML .= $this->xmlEntry('Quantity',    $item['qty']);
	    $strXML .= $this->xmlEntry('UnitPrice',   $item['price']);
//	    $strXML .= $this->xmlEntry('SalesTax',    $item['tax']);
	    $strXML .= $this->xmlEntry('SalesTaxPercent', $item['tax']);
	    $strXML .= $this->xmlEntry('TotalPrice', ($item['qty'] * $item['price']));
	    $strXML .= '</Item>' . chr(10);
	  }
	  $strXML .= '</Order>' . chr(10);
	}
	$strXML .= '</Request>' . chr(10);
	return $strXML;
  }
  // Misc function to format XML string properly
  function getCodes($country, $zone) {
  	global $db;
  	$codes = array();
  	$iso_country = $db->Execute("select countries_id, countries_iso_code_2 from " . TABLE_COUNTRIES . "
	  where countries_name = '" . $country . "'");
  	if ($iso_country->RecordCount() < 1) { // not found, return original choices
  		$codes['country'] = $country;
  		$codes['state']   = $zone;
  		return $codes;
  	}
  	$codes['country'] = $iso_country->fields['countries_iso_code_2'];
  	$state = $db->Execute("select zone_code from " . TABLE_ZONES . "
	  where zone_country_id = '" . $iso_country->fields['countries_id'] . "' and zone_name = '" . $zone . "'");
  	$codes['state'] = ($state->RecordCount() < 1) ? $zone : $state->fields['zone_code'];
  	return $codes;
  }
  
  // finds the details of the order_total modules for a given class name
  function getClassInfo($className, $searchArray) {
	for ($i = 0; $i < count($searchArray); $i++) {
	  if ($searchArray[$i]['class'] == $className) {
		return array('class' => $className, 'title' => $searchArray[$i]['title'], 'text' => $searchArray[$i]['text']);
	  }
	}
	return false; // not found
  }

  function clean_value($number, $currency_type = DEFAULT_CURRENCY) {
	global $admin;
	// converts the number to standard float format (period as decimal, no thousands separator)
	$temp  = str_replace($admin->currencies->currencies[$currency_type]['thousands_point'], '', trim($number));
	$value = str_replace($admin->currencies->currencies[$currency_type]['decimal_point'], '.', $temp);
	$value = ereg_replace("[^-0-9.]", "", $value);
	return $value;
  }

  function find_sku($id, $name) {
	global $db;
	$result = $db->Execute("select phreebooks_sku from " . TABLE_PRODUCTS . " where products_id = '" . $id . "'");
	return ($result->fields['phreebooks_sku'] <> '') ? $result->fields['phreebooks_sku'] : $name;
  }
}
?>