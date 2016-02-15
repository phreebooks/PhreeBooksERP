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
//  Path: /modules/amazon/ajax/amazon_receipt.php
//

/**************   Check user security   *****************************/
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$error = false;
$filename = DIR_FS_MY_FILES . $_SESSION['user']->company . '/temp/' . $_GET['fn'] . '.txt';
$xml = NULL;

// Check for the file to exist
if (!file_exists($filename)) {
  $error = true;
  $xml .= xmlEntry("error", 'No payment file named: ' . $_GET['fn'] . ' could be found!');
} else { // read the file and sort by order_id
  $lines = file($filename);
  array_shift($lines);	// discard summary line
  $title_line = trim(array_shift($lines)); // pull header and remove extra white space characters
  $titles = explode(",", $title_line); // assume titles don't contain double quotes
  $orders = array();
  foreach($lines as $line_num => $line) {    
	$parsed_array = explode(",", trim($line));
	if ($parsed_array[0] <> 'Order') continue; // it's not an order line, skip it
	$order_id = $parsed_array[2]; // pull the order id to start adding them up
	$orders[$order_id]['date']   =  $parsed_array[1];
	$orders[$order_id]['fee']   += -$parsed_array[8];
	$orders[$order_id]['total'] +=  $parsed_array[10];
  }
}

// delete the temp file
// unlink($filename);

// fill the return array
if (!$error && sizeof($orders) > 0) foreach ($orders as $number => $details) {
  $xml .= "<Order>\n";
  $xml .= "\t" . xmlEntry("Number",   $number);
  $xml .= "\t" . xmlEntry("Date",     $details['date']);
  $xml .= "\t" . xmlEntry("Discount", $details['fee']);
  $xml .= "\t" . xmlEntry("Total",    $details['total']);
  $xml .= "</Order>\n";
}

echo createXmlHeader() . $xml . createXmlFooter();
die;
?>