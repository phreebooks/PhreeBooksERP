<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /admin/soap/classes/sync.php
//

require_once('classes/parser.php');

class xml_sync extends parser {
  	function __construct() {
  }

  function processXML($rawXML) {
//	$rawXML = str_replace('&', '&amp;', $rawXML); // this character causes parser to break
//echo '<pre>' . $rawXML . '</pre><br>';
//	if (!$this->parse($rawXML)) {
	if (!$objXML = $this->xml_to_object($rawXML)) {
//echo '<pre>' . $rawXML . '</pre><br>';
//echo 'parsed string at shopping cart = '; print_r($this->arrOutput); echo '<br>';
	  return false;  // parse the submitted string, check for errors
	}
	// try to determine the language used, default to en_us
	$this->language = $objXML->Request->Language;
	if (file_exists('language/' . $this->product['language'] . '/language.php')) {
	  require ('language/' . $this->product['language'] . '/language.php');
	} else {
	  require ('language/en_us/language.php');
	}
	if (!$this->validateUser($objXML)) return false;
	if (!$product = $this->formatArray($objXML)) return false;
	if (!$this->syncProducts($product)) return false;
	return true;
  }

  function formatArray($objXML) { // specific to XML spec for a product sync
	// Here we map the received xml array to the pre-defined generic structure (application specific format later)
	$this->reference = $objXML->Request->Reference;
	$products = array('action' => $objXML->Request->Action);
	if (isset($objXML->Request->Product->SKU) && !is_array($objXML->Request->Product->SKU)) $objXML->Request->Product->SKU = array($objXML->Request->Product->SKU);
	if (is_array($objXML->Request->Product->SKU)) foreach ($objXML->Request->Product->SKU as $item) {
	  $products['product'][] = $item;
	}
	return $products;
  }

	/**
 	 * The remaining functions are specific to ZenCart. they need to be modified for the specific application.
 	 * It also needs to check for errors, i.e. missing information, bad data, etc.
 	 */ 
  function syncProducts($products) {
	global $db, $messageStack;
	// error check input
	if (sizeof($products['product']) == 0) return $this->responseXML('20', SOAP_NO_SKUS_UPLOADED, 'error');
	if ($products['action'] <> 'Validate') return $this->responseXML('16', SOAP_BAD_ACTION,       'error');
	
	$result = $db->Execute("select phreebooks_sku from " . TABLE_PRODUCTS);
	$missing_skus = array();
	while(!$result->EOF) {
	  if (!in_array($result->fields['phreebooks_sku'], $products['product'])) $missing_skus[] = $result->fields['phreebooks_sku'];
	  $result->MoveNext();
	}
	// make sure everything went as planned
	if (sizeof($missing_skus) > 0) {
	  $text = SOAP_SKUS_MISSING . implode(', ', $missing_skus);
	  return $this->responseXML('0', $text, 'caution');
	}
	$this->responseXML('0', SOAP_PRODUCTS_IN_SYNC, 'success');
	return true;
  }
}
?>