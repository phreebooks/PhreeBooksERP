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
//  Path: /soap/classes/parser.php
//

class parser {
   
  function validateUser($username = '', $password = '') {
	global $db;
	if (!$username || !$password) {
	  return $this->responseXML('10', SOAP_NO_USER_PW, 'error');
	}
	// This portion is specific to the application database name, fields and password validation methods
	// validate user with db (call validation function)
	$result = $db->Execute("select admin_pass from " . TABLE_USERS . " where admin_name = '" . $username . "'");
	if ($result->RecordCount() == 0) {
	  return $this->responseXML('11', SOAP_USER_NOT_FOUND, 'error');
	}
	try{
		\core\classes\encryption::validate_password($password, $result->fields['admin_pass']);
	}catch(Exception $e){
		return $this->responseXML('12', $e->getMessage(), 'error');
	}
	return true; // if both the username and password are correct
  }

  function responseXML($code, $text, $level, $extra_xml = false) {
	$strResponse  = '';
	$strResponse .= '<?xml version="1.0" encoding="UTF-8" ?>' . chr(10);
	$strResponse .= '<Response>' . chr(10);
	$strResponse .= xmlEntry('Version',   '1.00');
	$strResponse .= xmlEntry('Reference', $this->reference);
	$strResponse .= xmlEntry('Code',      $code);
	switch ($level) {
	  case 'success':
		$strResponse .= xmlEntry('Result', 'success');
		$strResponse .= xmlEntry('Text',   $text);
		break;
	  case 'error':
		$strResponse .= xmlEntry('Result', 'error');
		$strResponse .= xmlEntry('Text',   $text);
		break;
	  default:
		$strResponse .= xmlEntry('Result', 'error');
		$strResponse .= xmlEntry('Text',   SOAP_UNEXPECTED_ERROR);
	}
	if ($extra_xml) $strResponse .= $extra_xml;
	$strResponse .= '</Response>';
	echo $strResponse;
	ob_end_flush();
	session_write_close();
	die;
  }

  function get_account_id($short_name, $type = '') {
	global $db;
	$result = $db->Execute("select id from " . TABLE_CONTACTS . " 
		where short_name = '" . $short_name . "' and type = '" . $type . "'");
	return ($result->RecordCount() == 0) ? 0 : $result->fields['id'];
  }

  function get_user_id($admin_name) {
	global $db;
	$result = $db->Execute("select admin_id from " . TABLE_USERS . " where admin_name = '" . $admin_name . "'");
	return ($result->RecordCount() == 0) ? false : $result->fields['admin_id'];
  }

  function float($str) {
	if(strstr($str, ",")) {
	  $str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs
	  $str = str_replace(",", ".", $str); // replace ',' with '.'
	}
	if (preg_match("#([0-9\.]+)#", $str, $match)) { // search for number that may contain '.'
	  return floatval($match[0]);
	} else {
	  return floatval($str); // take some last chances with floatval
	}
  }

}
?>