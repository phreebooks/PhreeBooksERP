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
//  Path: /modules/shipping/ajax/shipping.php
//
/**************   Check user security   *****************************/
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'shipping/defaults.php');
require_once(DIR_FS_MODULES . 'shipping/functions/shipping.php');
/**************   page specific initialization  *************************/
$mod_dir = DIR_FS_MODULES . 'shipping/methods/';
$xml     = NULL;
$method  = $_GET['method'];
$message = '';

switch ($_REQUEST['action']) {
  case 'form':
	$template= $_GET['template'];
	if (file_exists($mod_dir . $method.'/'.$template.'.php')) {
	  require_once($mod_dir . $method.'/'.$template.'.php');
	  foreach ($output as $key => $value) $xml .= xmlEntry($key, $value);
	} else {
	  $xml .= xmlEntry('message', 'Error locating file: '.$mod_dir.$method.'/'.$template.'.php to load template!');
	}
	break;
  case 'tracking':
  	$tID = $_GET['tID'];
  	if (!$tID) {
  	  $message = 'No tracking ID passed!';
  	} else {
  	  load_specific_method('shipping', $method);
  	  $shipment = new $method;
  	  $message = $shipment->trackPackages('', $tID);
  	}
  	break;
  case 'validate':
	$address = new objectInfo();
	$address->ship_primary_name   = db_prepare_input($_GET['primary_name']);
	$address->ship_contact        = db_prepare_input($_GET['contact']);
	$address->ship_address1       = db_prepare_input($_GET['address1']);
	$address->ship_address2       = db_prepare_input($_GET['address2']);
	$address->ship_city_town      = db_prepare_input($_GET['city_town']);
	$address->ship_state_province = db_prepare_input($_GET['state_province']);
	$address->ship_postal_code    = db_prepare_input($_GET['postal_code']);
	$address->ship_country_code   = db_prepare_input($_GET['country_code']);
	load_specific_method('shipping', $method);
	$shipment = new $method;
	$result = $shipment->validateAddress($address);
	if ($result['result'] == 'success') $xml .= $result['xmlString'];
	$debug   = $result['debug'];
	$message = $result['message'];
	break;
}

//$debug = 'method = '.$method.' and action = '.$_REQUEST['action'].' and tID = '.$tID;
if ($message) $xml .= xmlEntry('message', $message);
if ($debug)   $xml .= xmlEntry('debug',   $debug);
echo createXmlHeader() . $xml . createXmlFooter();
die;
?>