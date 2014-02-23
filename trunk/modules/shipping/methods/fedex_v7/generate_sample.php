<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
//  Path: /modules/shipping/methods/fedex_v7/generate_sample.php
//
/**************   Check user security   *****************************/
// none
/**************  include page specific files    *********************/
load_method_language(DIR_FS_WORKING . 'methods/', 'fedex_v7');
require(DIR_FS_WORKING . 'defaults.php');
require(DIR_FS_WORKING . 'functions/shipping.php');
require(DIR_FS_WORKING . 'methods/fedex_v7/sample_data.php');
/**************   page specific initialization  *************************/
$error               = false;
$backup              = new \phreedom\classes\backup();
$backup->source_dir  = DIR_FS_MY_FILES . $_SESSION['company'] . '/temp/fedex_qual/';
$backup->dest_dir    = DIR_FS_MY_FILES . 'backups/';
$backup->dest_file   = 'fedex_qual.zip';
/***************   Act on the action request   *************************/
// retrieve the sample ship to addresses and query FEDEX_V7
$count = 1;
foreach ($shipto as $pkg) {
  $sInfo = new \shipping\classes\shipment();	// load defaults
  while (list($key, $value) = each($pkg)) $sInfo->$key = db_prepare_input($value);
  $sInfo->ship_date = date('Y-m-d', strtotime($sInfo->ship_date));
  // load package information
  $sInfo->package = array();
  foreach ($pkg['package'] as $item) {
	$sInfo->package[] = array(
	  'weight' => $item['weight'],
	  'length' => $item['length'],
	  'width'  => $item['width'],
	  'height' => $item['height'],
	  'value'  => $item['value'],
	);
  }
  if (count($sInfo->package) > 0) {
	$shipment = new \shipping\methods\fedex_v7\fedex_v7();
	if (!$result = $shipment->retrieveLabel($sInfo)) $error = true;
  }
  // fetch label
  $ext = (MODULE_SHIPPING_FEDEX_V7_PRINTER_TYPE == 'Thermal') ? '.lpt' : '.pdf';
  write_file($backup->source_dir . 'Label_' . $count . $ext, $shipment->returned_label);
  $count++;
}
// convert session messages to current stack
if ($error || $_SESSION['messageToStack']) {
  for ($i = 0; $i < sizeof($_SESSION['messageToStack']); $i++) {
	$messageStack->add($_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
  }
  unset($_SESSION['messageToStack']);
}
$backup->make_zip('dir');
// Download file
$backup->download($backup->dest_dir, $backup->dest_file, false); // will not return from here if successful
/*****************   prepare to display templates  *************************/
// N/A
?>