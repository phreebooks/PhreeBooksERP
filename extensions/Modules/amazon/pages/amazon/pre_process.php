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
//  Path: /modules/amazon/pages/amazon/pre_process.php
//
define('AMAZON_SHIP_CONFIRM_FILE_NAME','confirm_'.date('Y-m-d').'.txt');
$security_level = validate_user(SECURITY_ID_AMAZON_INTERFACE);
/**************  include page specific files    *********************/
gen_pull_language('phreebooks');
//require(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
//require(DIR_FS_MODULES . 'phreebooks/classes/orders.php');
//require(DIR_FS_ADMIN   . 'soap/classes/parser.php');
require(DIR_FS_WORKING . 'classes/amazon.php');
/**************   page specific initialization  *************************/
$upload_name = 'file_name'; // Template field name for the uploaded file
define('JOURNAL_ID', 12); // used for importing orders, 12 is hard coded for amazon invoice manager
define('SO_POPUP_FORM_TYPE','cust:so');
define('POPUP_FORM_TYPE','cust:inv');
$error = false;
// fill search and accounting period criteria
$acct_period = ($_GET['search_period']) ? $_GET['search_period'] : CURRENT_ACCOUNTING_PERIOD;
$search_text = $_POST['search_text']    ? $_POST['search_text']  : $_GET['search_text'];
if (isset($_POST['search_text'])) $_GET['search_text'] = $_POST['search_text']; // save the value for get redirects 
if ($search_text == TEXT_SEARCH)    $search_text = '';
$max_list    = ($_GET['pull_down_max']) ? $_GET['pull_down_max'] : MAX_DISPLAY_SEARCH_RESULTS;
$ship_date   = $_POST['ship_date']      ? gen_db_date($_POST['ship_date']) : date('Y-m-d');
$action      = $_POST['action'];
// load the sort fields
$_GET['sf'] = $_POST['sort_field'] ? $_POST['sort_field'] : $_GET['sf'];
$_GET['so'] = $_POST['sort_order'] ? $_POST['sort_order'] : $_GET['so'];
if(!isset($_REQUEST['list'])) $_REQUEST['list'] = 1;
/***************   Act on the action request   *************************/
switch ($action) {
  case 'import':
	validate_security($security_level, 3);
  	// first verify the file was uploaded ok
	if (!validate_upload($upload_name, 'text', 'txt')) {
		$messageStack->add('There was an error uploading the file.','error');
		break;
	} else {
		$salesOrder = new amazon();
		if ($salesOrder->processOrders($upload_name)) {
			gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		}
	}
	break;

  case 'ship_confirm':
    $str = "order-id	order-item-id	quantity	ship-date	carrier-code	carrier-name	tracking-number	ship-method\n";
	// fetch every shipment for the given post_date
	$result = $db->Execute("SELECT ref_id, carrier, method, ship_date, tracking_id 
	  FROM ".TABLE_SHIPPING_LOG." WHERE ship_date LIKE '$ship_date%'");
	if ($result->RecordCount() == 0) {
	  $messageStack->add('No valid Amazon orders have been shipped on the date selected!', 'caution');
	  break;
	}

	// for each shipment, fetch the invoice details needed
	$no_dups = array();
	while (!$result->EOF) {
	  if ( strpos($result->fields['ref_id'], '-') !== false) {
	    $purchase_invoice_id = substr($result->fields['ref_id'], 0, strpos($result->fields['ref_id'], '-'));
	  } else {
	    $purchase_invoice_id = $result->fields['ref_id'];
	  }
	  if (in_array($purchase_invoice_id, $no_dups)) { // it's a duplicate, skip
		$result->MoveNext();
	    continue;
	  }
	  $no_dups[] = $purchase_invoice_id;
	  $details = $db->Execute("SELECT purch_order_id, bill_primary_name FROM ".TABLE_JOURNAL_MAIN." 
	    WHERE journal_id=12 AND purchase_invoice_id='$purchase_invoice_id'");
	  if (strpos($details->fields['bill_primary_name'], 'Amazon') !== false) {
		switch ($result->fields['carrier']) {
		  case 'fedex': 
		  case 'fedex_v7': 
			$carrier      = 'FedEx';
			$carrier_name = '';
			$method       = ($result->fields['method'] == 'GND') ? 'Standard' : 'Expedited';
			break;
		  case 'ups':
			$carrier      = 'UPS';
			$carrier_name = '';
			$method       = ($result->fields['method'] == 'GND') ? 'Standard' : 'Expedited';
			break;
		  case 'usps':
			$carrier      = 'USPS';
			$carrier_name = '';
			$method       = 'Standard';
			break;
		  default:
			$carrier      = 'Other';
			$carrier_name = 'Other Carrier';
			$method       = ($result->fields['method'] == 'GND') ? 'Standard' : 'Expedited';
		}
		$output  = $details->fields['purch_order_id'] . "\t";
		$output .= '' . "\t";
		$output .= '' . "\t"; // was $output .= $result->fields['qty'] . "\t";
		$output .= substr($result->fields['ship_date'], 0, 10) . "\t";
		$output .= $carrier . "\t";
		$output .= $carrier_name . "\t";
		$output .= $result->fields['tracking_id'] . "\t";
		$output .= $method . "\n";
		$str .= $output;
		$db->Execute("UPDATE ".TABLE_SHIPPING_LOG." SET amazon_confirm='1' WHERE ref_id LIKE '$purchase_invoice_id%'");
	  }
	  $result->MoveNext();
	}
	gen_add_audit_log('Generated Amazon Confirmation File.', 'OrderCnt: '.$result->RecordCount());
	header("Content-type: plain/txt");
	header("Content-disposition: attachment; filename=".AMAZON_SHIP_CONFIRM_FILE_NAME."; size=".strlen($str));
	header('Pragma: cache');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header('Connection: close');
	header('Expires: ' . date('r', time()+60*60));
	header('Last-Modified: ' . date('r', time()));
	print $str;
	die;
  case 'go_first':    $_REQUEST['list'] = 1;     break;
  case 'go_previous': $_REQUEST['list']--;       break;
  case 'go_next':     $_REQUEST['list']++;       break;
  case 'go_last':     $_REQUEST['list'] = 99999; break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
$display_length = array(
  array('id' => MAX_DISPLAY_SEARCH_RESULTS, 'text' => MAX_DISPLAY_SEARCH_RESULTS),
  array('id' => '30',  'text' => '30'),
  array('id' => '40',  'text' => '40'),
  array('id' => '50',  'text' => '50'),
  array('id' => '100', 'text' => '100'),
  array('id' => '150', 'text' => '150'),
);

// build the list of open Sales Orders
$static_array = array( // since all these are for info only cannot sort
  'post_date'           => TEXT_DATE,
  'purchase_invoice_id' => 'Amazon Order ID',
  'ship_primary_name'   => 'Customer Name',
  'ship_telephone1'     => 'Telephone',
  'ship_email'          => 'Email',
  'shipper_code'        => TEXT_CARRIER,
  'filler'              => TEXT_ACTION,
);

$cal_pps = array(
  'name'      => 'shipDate',
  'form'      => 'amazon',
  'fieldname' => 'ship_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($ship_date),
  'params'    => array('align' => 'left'),
);

$result = html_heading_bar(array(), 'Date', 'desc', $static_array);
$so_list_header = $result['html_code'];

// build the list for the page selected
$field_list = array('id', 'post_date', 'shipper_code', 'purchase_invoice_id', 'ship_primary_name', 'ship_telephone1', 'ship_email');
		
$query_raw = "SELECT ".implode(', ', $field_list)." FROM ".TABLE_JOURNAL_MAIN." 
		WHERE journal_id=10 AND closed='0' AND bill_primary_name LIKE '%amazon%' ORDER BY post_date DESC";

$so_query_split = new splitPageResults($list=1, $max_list, $query_raw, $query_numrows);
$so_query_result = $db->Execute($query_raw);

// build the list header - Invoices
if (!isset($_GET['list_order'])) $_GET['list_order'] = 'post_date-desc'; // default to descending by invoice number
$heading_array = array(
	'post_date'           => TEXT_DATE,
	'purchase_invoice_id' => TEXT_INVOICE,
	'purch_order_id'      => 'Amazon Order ID',
	'ship_primary_name'   => 'Customer Name',
	'shipper_code'        => TEXT_CARRIER,
);
$result = html_heading_bar($heading_array, $_GET['sf'], $_GET['so'], array('Method', TEXT_SHIPPED, 'Confirmed', TEXT_ACTION));
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];

// build the list for the page selected
$period_filter = ($acct_period == 'all') ? '' : (' and period = ' . $acct_period);
if (isset($search_text) && $search_text <> '') {
  $search_fields = array('bill_primary_name', 'ship_primary_name', 'purchase_invoice_id', 'purch_order_id');
  // hook for inserting new search fields to the query criteria.
  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  $search = ' and (' . implode(' like \'%' . $search_text . '%\' or ', $search_fields) . ' like \'%' . $search_text . '%\')';
} else {
  $search = '';
}

$field_list = array('id', 'post_date', 'shipper_code', 'purchase_invoice_id', 'purch_order_id', 'ship_primary_name');
		
$query_raw = "SELECT SQL_CALC_FOUND_ROWS ".implode(', ', $field_list)." from ".TABLE_JOURNAL_MAIN." 
		WHERE bill_primary_name LIKE '%amazon%' AND journal_id=12 $period_filter $search ORDER BY $disp_order, purchase_invoice_id DESC";

$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
$query_split  = new splitPageResults($_REQUEST['list'], '');

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_AMAZON_MODULE);

?>