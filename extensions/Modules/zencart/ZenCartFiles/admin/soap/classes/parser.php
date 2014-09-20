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
//  Path: /admin/soap/classes/parser.php
//

class parser {

  function doCURLRequest($method = 'GET', $url, $vars) {
	global $messageStack;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); // times out after 10 seconds 
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	if (strtoupper($method) == 'POST') {
	  curl_setopt($ch, CURLOPT_POST, true);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	}
	if (CURL_PROXY_REQUIRED == 'True') {
	  curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, true);
	  curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
	  curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
	}
	$data  = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if ($data != '') {
	  return $data;
	} else {
	  $messageStack->add('ZenCart Interface cURL error: ' . $error, 'error');
	  return false; 
	}
  }

  function xmlEntry($key, $data, $ignore = NULL) {
    $str = "\t<" . $key . ">";
    if ($data != NULL) $str .= $ignore ? $data : "<![CDATA[$data]]>";
    $str .= "</" . $key . ">\n";
    return $str;
  }

  function xml_to_object($xml = '') {
    global $messageStack;
    $xml     = trim($xml);
    $output  = NULL;
    $runaway = 0;
    while (strlen($xml) > 0) {
	  if (strpos($xml, '<?xml') === 0) { // header xml, ignore
	    $xml = trim(substr($xml, strpos($xml, '>') + 1));
	  } elseif (strpos($xml, '</') === 0) { // ending tag, should not happen
	    $xml = trim(substr($xml, strpos($xml, '>') + 1));
	  } elseif (substr($xml, 0, 3) == '<![') { // it's data, clean up and return
	    return substr($xml, strpos($xml, '[CDATA[') + 7, strrpos($xml, ']]') - strpos($xml, '[CDATA[') - 7);
	  } elseif (substr($xml, 0, 1) == '<') { // beginning tag, process
	    $tag = substr($xml, 1, strpos($xml, '>') - 1);
	    $taglen = strlen($tag) + 2;
	    $end_tag = '</' . $tag . '>';
	    if (strpos($xml, $end_tag) === false) {
	      $messageStack->add('PhreeBooks XML parse error looking for end tag: ' . $tag . ' but could not find it!','error');
	      return false;
	    }
	    while(true) {
		  $runaway++;
		  if ($runaway > 10000) return $messageStack->add('Runaway counter 1 reached. There is an error in the xml entry!','error');	
	      $data = trim(substr($xml, $taglen, strpos($xml, $end_tag) - $taglen));
		  if (isset($output->$tag)) {
		    if (!is_array($output->$tag)) $output->$tag = array($output->$tag);
		    array_push($output->$tag, $this->xml_to_object($data));
		  } else {
		    $output->$tag = $this->xml_to_object($data);
		  }
		  $xml = trim(substr($xml, strpos($xml, $end_tag) + strlen($end_tag)));
		  $next_tag = substr($xml, 1, strpos($xml, '>') - 1);
		  if ($next_tag <> $tag) break;
	    }
	  } else { // it's probably just plain data, return with it
	    return $xml;
	  }
	  $runaway++;
	  if ($runaway > 10000) $messageStack->add('Runaway counter 2 reached. There is an error in the xml entry!','error');	
    }
    return $output;
  }

  function object_to_xml($params, $multiple = false, $multiple_key = '') {
    $output = NULL;
    if (!is_array($params) && !is_object($params)) return;
    foreach ($params as $key => $value) {
	  $xml_key = $multiple ? $multiple_key : $key;
      if       (is_array($value)) {
	    $output .= object_to_xml($value, true, $key);
      } elseif (is_object($value)) {
	    $output .= "<" . $xml_key . ">\n" . object_to_xml($value) . "</" . $xml_key . ">\n";
	  } else {
	    if ($value <> '') $output .= xmlEntry($xml_key, $value);
	  }
    }
    return $output;
  }

  function validateUser($objXML) {
	global $db;
	$this->username = $objXML->Request->UserName;
	$this->password = $objXML->Request->UserPassword;
	if (!$this->username || !$this->password) return $this->responseXML('10', SOAP_NO_USER_PW, 'error');
// TBD - This portion is specific to the application database name, fields and password validation methods
//	if (!is_object($db)) { echo 'the database is not open ...'; return false; }
	// validate user with db (call validation function)
	$result = $db->Execute("select admin_pass from " . DB_PREFIX . "admin where admin_name = '" . $this->username . "'");
	if ($result->RecordCount() == 0) return $this->responseXML('11', SOAP_USER_NOT_FOUND, 'error');
	if (!zen_validate_password($this->password, $result->fields['admin_pass'])) {
	  return $this->responseXML('12', SOAP_PASSWORD_NOT_FOUND, 'error');
	}
	return true; // if both the username and password are correct
  }

  function responseXML($code, $text, $level) {
	$text = preg_replace('/&nbsp;/', '', $text); // the &nbsp; messes up the response XML
	$strResponse  = '<?xml version="1.0" encoding="UTF-8" ?>' . chr(10);
	$strResponse .= '<Response>' . chr(10);
	$strResponse .= $this->xmlEntry('Version', '1.00');
	$strResponse .= $this->xmlEntry('Reference', $this->reference);
	$strResponse .= $this->xmlEntry('UserName', $this->username);
	switch ($level) {
	  case 'error':
	  case 'caution':
	  case 'success':
		$strResponse .= $this->xmlEntry('Result', $level);
		$strResponse .= $this->xmlEntry('Code', $code);
		$strResponse .= $this->xmlEntry('Text', $text);
		break;
	  default:
		$strResponse .= $this->xmlEntry('Result', 'error');
		$strResponse .= $this->xmlEntry('Code', $code);
		$strResponse .= $this->xmlEntry('Text', SOAP_UNEXPECTED_ERROR);
	}
	$strResponse .= '</Response>';
	echo $strResponse;
	return false;
  }

}
?>