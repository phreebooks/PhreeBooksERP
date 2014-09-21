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
//  Path: /modules/phreebooks/ajax/load_record.php
//

/**************   Check user security   *****************************/
$xml = NULL;
$security_level = \core\classes\user::validate();
/**************   include page specific files   *********************/
require_once(DIR_FS_ADMIN . 'modules/phreebooks/defaults.php');
/**************   page specific initialization  *********************/
$rID   = db_prepare_input($_GET['rID']);
$main  = $admin->DataBase->query("select * from ".TABLE_JOURNAL_MAIN." where id = '$rID'");
if ($main->RecordCount() <> 1) throw new \core\classes\userException('Bad record submitted. No results found!');
$items = $admin->DataBase->query("select * from ".TABLE_JOURNAL_ITEM." where ref_id = '$rID'");
// build the journal record data
$main->fields['attach_exist'] = file_exists(PHREEBOOKS_DIR_MY_ORDERS.'order_'.$rID.'.zip') ? '1' : '0';
foreach ($main->fields as $key => $value) $xml .= "\t" . xmlEntry($key, $value);
while (!$items->EOF) {
  $xml .= "\t<items>\n";
  foreach ($items->fields as $key => $value) $xml .= "\t\t" . xmlEntry($key, $value);
  $xml .= "\t</items>\n";
  $items->MoveNext();
}

echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>