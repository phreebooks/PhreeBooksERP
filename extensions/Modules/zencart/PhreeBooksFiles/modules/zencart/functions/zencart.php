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
//  Path: /modules/zencart/functions/zencart.php
//

function doCURLRequest($method = 'GET', $url, $vars) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 30 seconds
	if (strtoupper($method) == 'POST') {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	}
	$data  = curl_exec($ch);
	$error = curl_error($ch);
	$error_nr = curl_errno($ch);
  	curl_close($ch);
  	if ($error_nr != 0) throw new Exception("cURL error nr: $error_nr cURL error: $error"); //@todo
	return $data;
}

function pull_down_price_sheet_list() {
  global $admin;
  $output = array(array('id' => '0', 'text' => TEXT_NONE));
  $sql = "select distinct sheet_name from " . TABLE_PRICE_SHEETS . "
	where '" . date('Y-m-d',time()) . "' >= effective_date and inactive = '0'";
  $result = $admin->DataBase->Execute($sql);
  while(!$result->EOF) {
    $output[] = array('id' => $result->fields['sheet_name'], 'text' => $result->fields['sheet_name']);
    $result->MoveNext();
  }
  return $output;
}

?>