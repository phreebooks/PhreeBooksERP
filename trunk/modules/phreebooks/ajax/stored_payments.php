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
//  Path: /modules/phreebooks/ajax/stored_payments.php
//
/**************   Check user security   *****************************/
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$contact_id = db_prepare_input($_GET['contact_id']);
$xml = NULL;

$enc_data = new encryption();
$sql = "select id, hint, enc_value from ".TABLE_DATA_SECURITY." where module='contacts' and ref_1 = $contact_id";
$result = $db->Execute($sql);
while (!$result->EOF) {
	$data = $enc_data->decrypt($_SESSION['admin_encrypt'], $result->fields['enc_value']);
	$fields = explode(':', $data);
	if (strlen($fields[3]) == 2) $fields[3] = '20'.$fields[3]; // make sure year is 4 digits
	$xml .= "<Payment>\n";
	$xml .= "\t" . xmlEntry("id",   $result->fields['id']);
	$xml .= "\t" . xmlEntry("name", $fields[0]); // will be the name field for credit cards
	$xml .= "\t" . xmlEntry("hint", $result->fields['hint']);
	for ($i = 0; $i < sizeof($fields); $i++) $xml .= "\t" . xmlEntry("field_" . $i, $fields[$i]);
	$xml .= "</Payment>\n";
	$result->MoveNext();
}
// error check
if (!$_SESSION['admin_encrypt'] && $result->RecordCount() > 0) { // no permission to enter page, return error
  echo createXmlHeader() . xmlEntry('error', BNK_ERROR_NO_ENCRYPT_KEY) . createXmlFooter();
  die;
}

echo createXmlHeader() . $xml . createXmlFooter();
die;
?>