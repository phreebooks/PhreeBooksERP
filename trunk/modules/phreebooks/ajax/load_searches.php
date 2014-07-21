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
//  Path: /modules/phreebooks/ajax/load_searches.php
//
/**************   Check user security   *****************************/
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$error       = false;
$debug       = NULL;
$xml         = NULL;
$search_text = db_prepare_input($_GET['guess']);
$type        = db_prepare_input($_GET['type']);
$jID         = db_prepare_input($_GET['jID']);

if ($search_text) {
  $search_fields = array('a.primary_name', 'a.contact', 'a.telephone1', 'a.telephone2', 'a.address1', 
	'a.address2', 'a.city_town', 'a.postal_code', 'c.short_name');
  $search = ' and (' . implode(' like \'%' . $search_text . '%\' or ', $search_fields) . ' like \'%' . $search_text . '%\')';
  $result = $db->Execute("select c.id from ".TABLE_CONTACTS." c left join ".TABLE_ADDRESS_BOOK." a on c.id = a.ref_id 
	where a.type = '".$type."m' and c.inactive='0' ".$search." limit 2");
  if ($result->RecordCount() == 1) { // check to make sure there are no open SO/POs
    $cID = $result->fields['id'];
  	if (in_array($jID, array(6,12))) {
  	  $result = $db->Execute("select id from ".TABLE_JOURNAL_MAIN." where closed = '0' and journal_id in (4,10) and bill_acct_id = $cID limit 1");
  	  if ($result->RecordCount() > 0) $error = true;
  	}
  } else { $error = true; }
} else { $error = true; }

if (!$error) {
  $xml .= xmlEntry('cID',   $cID);
  $xml .= xmlEntry('result','success');
} else {
  $xml .=  xmlEntry('result', 'fail');
}
if ($debug) $xml .= xmlEntry('debug', $debug);
echo createXmlHeader() . $xml . createXmlFooter(); die;
?>