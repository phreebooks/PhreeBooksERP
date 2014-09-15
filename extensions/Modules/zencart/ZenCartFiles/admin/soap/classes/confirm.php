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
//  Path: /admin/soap/classes/confirm.php
//

require_once('classes/parser.php');

class xml_confirm extends parser {
  function __construct() {
  }

  function processXML($rawXML) {
  	try{
//	$rawXML = str_replace('&', '&amp;', $rawXML); // this character causes parser to break
//echo '<pre>' . $rawXML . '</pre><br>';
//	if (!$this->parse($rawXML)) {
		$objXML = $this->xml_to_object($rawXML);
//echo '<pre>' . $rawXML . '</pre><br>';
//echo 'parsed string at shopping cart = '; print_r($objXML); echo '<br>';
	// try to determine the language used, default to en_us
	$this->language = $objXML->Request->Language;
	if (file_exists('language/' . $this->language . '/language.php')) {
	  require ('language/' . $this->language . '/language.php');
	} else {
	  require ('language/en_us/language.php');
	}
		$this->validateUser($objXML);
		$orders = $this->formatArray($objXML);
		$this->orderConfirm($orders);
	return true;
  	}catch(Exception $e){
  		$this->responseXML($e->getCode(), $e->getMessage(), 'error');
  	}
  }

  function formatArray($objXML) { // specific to XML spec for a order confirm
	// Here we map the received xml array to the pre-defined generic structure (application specific format later)
	$this->reference = $objXML->Request->Reference;
	$orders = array('action' => $objXML->Request->Action);
	if (is_object($objXML->Request->Order)) $objXML->Request->Order = array($objXML->Request->Order);
	if (is_array($objXML->Request->Order)) foreach ($objXML->Request->Order as $order) {
	  $orders['order'][] = array(
	    'id'     => $order->ID,
		'status' => $order->Status,
	    'msg'    => $order->Message,
	  );
	}
	return $orders;
  }

// The remaining functions are specific to ZenCart. they need to be modified for the specific application.
// It also needs to check for errors, i.e. missing information, bad data, etc.
  function orderConfirm($orders) {
	global $admin;
	// error check input
	if (sizeof($orders['order']) == 0)  throw new Exception(SOAP_NO_ORDERS_TO_CONFIRM, 20);
	if ($orders['action'] <> 'Confirm') throw new Exception(SOAP_BAD_ACTION, 16);

    $order_cnt = 0;
	$order_list = array();
	$order_prefix = defined('MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PREFIX') ? MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PREFIX : false;
    foreach ($orders['order'] as $value) {
      $id = $order_prefix ? str_replace($order_prefix, '', $value['id'], $count = 1) : $value['id'];
	  $result = $db->Execute("select orders_status from " . TABLE_ORDERS . " where orders_id = '$id'");
	  if ($result->RecordCount() == 0 || $result->fields['orders_status'] == $value['status']) continue; // skip this order, not a zencart order
	  // insert a new status in the order status table
	  $db->Execute("insert into " . TABLE_ORDERS_STATUS_HISTORY . " set
		orders_id = '$id',
		orders_status_id = " . zen_db_input($value['status']) . ",
		date_added = now(),
		customer_notified = '0',
	    comments = '" . zen_db_input($value['msg']) . "'");
      // update the status in the orders table
	  $db->Execute("update " . TABLE_ORDERS . " set
	    orders_status = " . zen_db_input($value['status']) . ",
		last_modified = now()
		where orders_id = '$id'");
	  $order_cnt++;
	  $order_list[] = $value['id'];
	}
	$orders = (sizeof($order_list) > 0) ? (' (' . implode(', ', $order_list) . ')') : '';
	$this->responseXML('0', sprintf(SOAP_CONFIRM_SUCCESS, $order_cnt . $orders), 'success');
	return true;
  }
}
?>