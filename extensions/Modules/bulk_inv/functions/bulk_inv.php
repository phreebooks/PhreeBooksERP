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
//  Path: /modules/bulk_inv/functions/bulk_inv.php
// 
/*
  function doCURLRequest($method = 'GET', $url, $vars) {
	global $messageStack;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 30 seconds
	if (strtoupper($method) == 'POST') {
	  curl_setopt($ch, CURLOPT_POST, true);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	}
	if (CURL_PROXY_REQUIRED == '1') {
	  curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, true);
	  curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
	  curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
	}
	$data = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if ($data != '') {
	  return $data;
	} else {
	  throw new \core\classes\userException('cURL error: ' . $error);
	}
  }

function pull_down_price_sheet_list() {
  global $db;
  $output = array(array('id' => '0', 'text' => TEXT_NONE));
  $sql = "select distinct sheet_name from " . TABLE_PRICE_SHEETS . "
	where '" . date('Y-m-d',time()) . "' >= effective_date and inactive = '0'";
  $result = $db->Execute($sql);
  while(!$result->EOF) {
    $output[] = array('id' => $result->fields['sheet_name'], 'text' => $result->fields['sheet_name']);
    $result->MoveNext();
  }
  return $output;
}

function pps_build_pull_down($keyed_array) {
  $values = array();
  $values[] = array('id' => '', 'text' => TEXT_NONE);
  if (is_array($keyed_array)) {
    foreach($keyed_array as $key => $value) {
	  $values[] = array('id' => $key, 'text' => $value);
    }
  }
  return $values;
}
*/
?>