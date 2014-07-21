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
//  Path: /modules/phreebooks/ajax/load_bill.php
//
$xml = NULL;
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
gen_pull_language('contacts');
require_once(DIR_FS_MODULES . 'phreebooks/defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');

/**************   page specific initialization  *************************/
$error = false;
$debug = NULL;
$cID   = db_prepare_input($_GET['cID']); // contact record ID
$bID   = db_prepare_input($_GET['bID']); // journal record ID
$jID   = db_prepare_input($_GET['jID']); // journal ID

define('JOURNAL_ID', $jID);

if ($bID) {
  $bill = $db->Execute("select * from " . TABLE_JOURNAL_MAIN . " where id = '$bID'");
  if ($bill->fields['bill_acct_id']) $cID = $bill->fields['bill_acct_id']; // replace bID with ID from payment
} else {
  $bill = new objectInfo();
}
// select the customer and build the contact record
$contact = $db->Execute("select * from " . TABLE_CONTACTS . " where id = '$cID'");
$type = $contact->fields['type'];
define('ACCOUNT_TYPE', $type);
$bill_add  = $db->Execute("select * from " . TABLE_ADDRESS_BOOK . " 
  where ref_id = '" . $cID . "' and type in ('" . $type . "m', '" . $type . "b')");

//$debug .= 'Processing main_id = ' . $bID . ' and contact ID = ' . $cID . ' and type = ' . $type . chr(10);
// fetch the line items
$invoices  = fill_paid_invoice_array($bID, $cID, $type);
$item_list = $invoices['invoices'];

// some adjustments based on what we are doing
$bill->fields['payment_fields'] = $invoices['payment_fields'];
$bill->fields['post_date']      = gen_locale_date($bill->fields['post_date'] ? $bill->fields['post_date'] : date('Y-m-d'));

// build the form data
if (sizeof($contact->fields) > 0) {
  $xml .= "<BillContact>\n";
  foreach ($contact->fields as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
  if ($bill_add->fields) while (!$bill_add->EOF) {
    $xml .= "\t<Address>\n";
    foreach ($bill_add->fields as $key => $value) $xml .= "\t\t" . xmlEntry($key, $value);
    $xml .= "\t</Address>\n";
    $bill_add->MoveNext();
  }
  $xml .= "</BillContact>\n";
}
if (sizeof($bill->fields) > 0) { // there was an bill to open
  $xml .= "<BillData>\n";
  foreach ($bill->fields as $key => $value) $xml .= "\t\t" . xmlEntry($key, $value);
  foreach ($item_list as $item) { // there should always be invoices to pull
    $xml .= "\t<Item>\n";
    foreach ($item as $key => $value) $xml .= "\t\t" . xmlEntry($key, $value);
    $xml .= "\t</Item>\n";
  }
  $xml .= "</BillData>\n";
}

if ($debug) $xml .= xmlEntry('debug', $debug);
echo createXmlHeader() . $xml . createXmlFooter();
die;
?>