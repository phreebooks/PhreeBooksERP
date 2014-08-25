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
//  Path: /modules/amazon/pages/amazon_payment/pre_process.php
//
$security_level = validate_user(SECURITY_ID_AMAZON_PAYMENT_INTERFACE);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$upload_name = 'file_name';
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'import':
	validate_security($security_level, 3);
  	// first verify the file was uploaded ok
	if (!validate_upload($upload_name, 'text', 'csv')) {
	  $messageStack->add('There was an error uploading the file.','error');
	  break;
	} else {	
	  $lines_array = file($_FILES[$upload_name]['tmp_name']);
	  // parse the import file
	  $heading_line = array_shift($lines_array);
	  $headings = explode("\t", $heading_line);
	  // put the data into a 2x2 associative array and process
	  $temp     = array();
	  while ($payment = array_shift($lines_array)) {
	    $iArray = (explode("\t",trim($payment)));
	    $pmt_array = array();
	    foreach ($headings as $key => $value) $pmt_array[$value] = $iArray[$key]; // make it associative
	    $type = $pmt_array['transaction-type'];
	    $date = substr($pmt_array['posted-date'], 0, 10);
	    $oID  = $pmt_array['order-id'];
	    $sku  = $pmt_array['sku'];
	    $qty  = $pmt_array['quantity-purchased'];
	    $amt  = $pmt_array['item-price-credit'];
	    $frt  = $pmt_array['shipping-price-credit'];
	    $fee  = $pmt_array['order-related-fees'];
	    if($pmt_array['settlement-id'])        $settlement_id= $pmt_array['settlement-id'];
	    if($pmt_array['settlement-start-date'])$start_date   = substr($pmt_array['settlement-start-date'],0, 10);
	    if($pmt_array['settlement-end-date'])  $end_date     = substr($pmt_array['settlement-end-date'],  0, 10);
	    if($pmt_array['deposit-date'])         $deposit_date = substr($pmt_array['deposit-date'],         0, 10);
	    // monthly fee
	    $mFee = $pmt_array['other-fees'];
// TBD
	    if (!$oID) continue;
	     
	    $temp[$date][$oID][$sku]['Type'] = $type;
	    if ($qty) $temp[$date][$oID][$sku]['Qty']      = $qty;
	    if ($amt) $temp[$date][$oID][$sku]['Price']    = $amt;
	    if ($frt) $temp[$date][$oID][$sku]['Shipping'] = $frt;
	    if ($fee) $temp[$date][$oID][$sku]['Fee']      = $fee;
	    if ($mFee)$temp[$date][$oID][$sku]['mFee']     = $mFee;
	     
	  }
	  // reformat the output
	  $output  = "Settlement ID: $settlement_id from $start_date to $end_date deposited on $deposit_date\n";
	  $output .= "Type,Order Date,Order ID,SKU,Qty,Price,Shipping,Subtotal,Fees,Other Fees,Total\n";
	  $temp[$date][$oID][$sku];
	  $deposit_total = 0;
	  foreach ($temp as $date => $dates) {
	  	foreach ($dates as $oID => $SKUs) {
	  	  foreach ($SKUs as $sku => $values) {
	  	  	$total = $values['Price'] + $values['Shipping'] + $values['Fee'] + $values['mFee'];
	  	  	$output .= implode(',', array(
	  	  		$values['Type'],
	  	  		$date,
	  	  		$oID,
	  	  		$sku,
	  	  		$values['Qty'],
	  	  		$values['Price'],
	  	  		$values['Shipping'],
	  	  		$values['Price'] + $values['Shipping'],
	  	  		$values['Fee'],
	  	  		$values['mFee'],
	  	  		$total,
	  	  	))."\n";
	  	  	$deposit_total += $total;
	  	  }
	  	} 
	  }
	  $output .= 'Total,,,,,,,,,,'.$deposit_total."\n";
	  // write the file to the temp directory
	  $filename = DIR_FS_MY_FILES . $_SESSION['company']."/temp/$settlement_id.txt";
	  if (!$handle = fopen($filename, 'w')) {
	    $messageStack->add("Cannot open file ($filename) for writing check your permissions.", 'error');
	  }
	  fwrite($handle, $output);
	  fclose($handle);

	  gen_add_audit_log('Format Amazon Payment File.', 'ID: '.$settlement_id);
	  header("Content-type: application/csv");
	  header("Content-disposition: attachment; filename=$settlement_id.csv; size=".strlen($output));
	  header('Pragma: cache');
	  header('Cache-Control: public, must-revalidate, max-age=0');
	  header('Connection: close');
	  header('Expires: ' . date('r', time()+60*60));
	  header('Last-Modified: ' . date('r'));
	  print $output;
	  exit();  
	}
	break;
  default:
}

/*****************   prepare to display templates  *************************/

$include_header   = true;
$include_footer   = true;
$include_tabs     = false;
$include_calendar = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_AMAZON_PAYMENT_MODULE);

?>