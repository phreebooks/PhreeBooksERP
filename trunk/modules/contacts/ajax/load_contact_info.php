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
//  Path: /modules/contacts/ajax/load_contact_info.php
//

/**************   Check user security   *****************************/
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$guess = db_prepare_input($_GET['guess']);
$xml   = NULL;
if (!$guess) die;

$search_fields = array('a.primary_name', 'a.contact', 'a.telephone1', 'a.telephone2', 'a.telephone4', 
  'a.city_town', 'a.postal_code', 'c.id', 'c.short_name');
$search = ' and (' . implode(' like \'%' . $guess . '%\' or ', $search_fields) . ' like \'%' . $guess . '%\')';

$field_list = array('c.id', 'c.short_name', 'a.primary_name');

$query_raw = "select " . implode(', ', $field_list)  . " 
	from " . TABLE_CONTACTS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.id = a.ref_id 
	where a.type in ('cm', 'vm')" . $search;

$result = $db->Execute($query_raw);

$xml .= xmlEntry("guess", $guess);
while (!$result->EOF) {
  $xml .= "\t<guesses>\n";
  $xml .= "\t" . xmlEntry("id",    $result->fields['id']);
  $xml .= "\t" . xmlEntry("guess", $result->fields['short_name'] . ' - ' . $result->fields['primary_name']);
  $xml .= "\t</guesses>\n";
  $result->MoveNext();
}

echo createXmlHeader() . $xml . createXmlFooter();
die;
?>