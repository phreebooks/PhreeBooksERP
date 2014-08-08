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
//  Path: /modules/contacts/ajax/load_contact.php
//
/**************   Check user security   *****************************/
$xml = NULL;
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
gen_pull_language('contacts');
/**************   page specific initialization  *************************/
$cID   = db_prepare_input($_GET['cID']);
$error = false;
  // select the customer and build the contact record
$contact    = $db->Execute("select * from " . TABLE_CONTACTS . " where id = '" . $cID . "'");
$type       = $contact->fields['type'];
$terms_type = ($type == 'c') ? 'AR' : 'AP';
$contact->fields['terms_text'] = gen_terms_to_language($contact->fields['special_terms'], true, $terms_type);
$contact->fields['ship_gl_acct_id'] = ($type == 'v') ? AP_DEF_FREIGHT_ACCT : AR_DEF_FREIGHT_ACCT;
$bill_add   = $db->Execute("select * from " . TABLE_ADDRESS_BOOK . " 
  where ref_id = '" . $cID . "' and type in ('" . $type . "m', '" . $type . "b')");
//fix some special fields
if (!$contact->fields['dept_rep_id']) unset($contact->fields['dept_rep_id']); // clear the rep field if not set to a contact
$ship_add = $db->Execute("select * from " . TABLE_ADDRESS_BOOK . " 
  where ref_id = '" . $cID . "' and type in ('" . $type . "m', '" . $type . "s')");

// build the form data
if ($contact->fields) {
  $xml .= "\t<Contact>\n";
  foreach ($contact->fields as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
  $xml .= "\t</Contact>\n";
}
if ($bill_add->fields) while (!$bill_add->EOF) {
  $xml .= "\t<BillAddress>\n";
  foreach ($bill_add->fields as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
  $xml .= "\t</BillAddress>\n";
  $bill_add->MoveNext();
}
if (defined('MODULE_SHIPPING_STATUS') && $ship_add->fields) while (!$ship_add->EOF) {
  $xml .= "\t<ShipAddress>\n";
  foreach ($ship_add->fields as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
  $xml .= "\t</ShipAddress>\n";
  $ship_add->MoveNext();
}
echo createXmlHeader() . $xml . createXmlFooter();
die;
?>