<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/contacts/ajax/load_contact.php
//
/**************   Check user security   *****************************/
$xml = NULL;
$security_level = \core\classes\user::validate();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$cID   = db_prepare_input($_GET['cID']);
  // select the customer and build the contact record
$contact    = $admin->DataBase->query("select * from " . TABLE_CONTACTS . " where id = '$cID'");
$type       = $contact['type'];
$terms_type = ($type == 'c') ? 'AR' : 'AP';
$contact['terms_text'] = gen_terms_to_language($contact['special_terms'], true, $terms_type);
$contact['ship_gl_acct_id'] = ($type == 'v') ? AP_DEF_FREIGHT_ACCT : AR_DEF_FREIGHT_ACCT;
$sql   = $admin->DataBase->prepare("select * from " . TABLE_ADDRESS_BOOK . " where ref_id = '$cID' and type in ('{$type}m', '{$type}b')");
$sql->execute();

//fix some special fields
if (!$contact['dept_rep_id']) unset($contact['dept_rep_id']); // clear the rep field if not set to a contact
$ship_add = $admin->DataBase->prepare("select * from " . TABLE_ADDRESS_BOOK . " where ref_id = '$cID' and type in ('{$type}m', '{$type}s')");
$ship_add->execute();
// build the form data
if ($contact) {
  $xml .= "\t<Contact>\n";
  foreach ($contact as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
  $xml .= "\t</Contact>\n";
}
while ($result = $sql->fetch(\PDO::FETCH_ASSOC)){
  $xml .= "\t<BillAddress>\n";
  foreach ($result as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
  $xml .= "\t</BillAddress>\n";
}
if (defined('MODULE_SHIPPING_STATUS'))  while ($ship_add = $sql->fetch(\PDO::FETCH_ASSOC)){
  $xml .= "\t<ShipAddress>\n";
  foreach ($result as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
  $xml .= "\t</ShipAddress>\n";
}
echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>