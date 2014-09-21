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
//  Path: /modules/contacts/config_phreeform.php
//

$FormProcessing['terms']  = TEXT_TERMS_TO_LANGUAGE;
$FormProcessing['branch'] = TEXT_CONTACT_ID;
// Extra form processing operations
function pf_process_contacts($strData, $Process) {
  switch ($Process) {
	case "terms":     return gen_terms_to_language($strData);
	case "branch":    return contacts_get_short_name($strData);
	default: // Do nothing
  }
  return $strData; // No Process recognized, return original value
}

function contacts_get_short_name($id) {
  global $admin;
  if (!$id) return COMPANY_ID;
  $result = $admin->DataBase->query("select short_name from " . TABLE_CONTACTS . " where id = " . (int)$id);
  return $result->RecordCount()==0 ? COMPANY_ID : $result->fields['short_name'];
}

?>