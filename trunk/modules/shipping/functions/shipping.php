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
//  Path: /modules/shipping/functions/shipping.php
//

function build_js_methods($methods) {
  global $shipping_defaults;
  $choices         = array_keys($shipping_defaults['service_levels']);
  $service_levels  = 'var freightLevels   = new Array();' . chr(10);
  $carriers        = 'var freightCarriers = new Array();' . chr(10);
  $carrier_details = 'var freightDetails  = new Array();' . chr(10);
  for ($i = 0; $i < sizeof($choices); $i++) $service_levels .= "freightLevels[".$i."]='".$choices[$i]."'; " . chr(10);
  $i = 0;
  if (sizeof($methods) > 0) foreach ($methods as $method) {
    $carriers          .= "freightCarriers[".$i."]='" . $method['id'] . "';" . chr(10);
    $carrier_details   .= 'freightDetails['.$i.'] = new Array();' . chr(10);
    for ($j = 0; $j < sizeof($choices); $j++) {
	  $carrier_details .= "freightDetails[".$i."][".$j."]='" . (defined($method['id'] . '_' . $choices[$j]) ? constant($method['id'] . '_' . $choices[$j]) : "") . "'; " . chr(10);
    }
    $i++;
  }
  return $service_levels . $carriers . $carrier_details;
}

function GetXMLString($y, $SubmitURL, $GetPost) {
  global $cURLpath;
  $output = array();
  $ch = curl_init(); /// initialize a cURL session 
  curl_setopt($ch, CURLOPT_URL, $SubmitURL); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10); // times out after 10 seconds 
  curl_setopt($ch, CURLOPT_HEADER, 0); 
  if ($GetPost=="POST") { curl_setopt($ch, CURLOPT_POST, 1); }	
  curl_setopt($ch, CURLOPT_POSTFIELDS, "$y"); 
  $xyz = curl_exec($ch); 
  // Check for errors
  $curlerrornum = curl_errno($ch);
  $curlerror    = curl_error($ch);
  if ($curlerrornum) { 
	$output['result'] = 'error';
	$output['message'] = 'XML Read Error (cURL) #'.$curlerrornum.'. Description='.$curlerror.'.<br />';
  } else {
	$output['result'] = 'success';
	$output['xmlString'] = $xyz;
  }
  curl_close ($ch);
  return $output;
}

?>