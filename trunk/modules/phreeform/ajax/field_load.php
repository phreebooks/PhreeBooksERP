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
//  Path: /modules/phreeform/ajax/field_load.php
//
/**************   Check user security   *****************************/
$xml = NULL;
$security_level = \core\classes\user::validate(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');

/**************   page specific initialization  *************************/
$report         = new \core\classes\objectInfo();
$report->tables = array();
$i              = 1;
$runaway        = 0; // just in case
while(true) {
  if (isset($_GET['t' . $i])) $report->tables[] = new \core\classes\objectInfo(array('tablename' => $_GET['t' . $i]));
    else break;
  $i++;
  if ($runaway++ > 100) throw new \Exception("caught by runaway counter");
}
if (sizeof($report->tables) < 1) throw new \Exception(PHREEFORM_NO_TABLES_PASSED);
$report->special_class = $_GET['sp'] ? $_GET['sp'] : false;

$kFields = CreateSpecialDropDown($report);
if (sizeof($kFields) > 0) foreach ($kFields as $value) {
  $xml .= "<option>\n";
  $xml .= "\t" . xmlEntry('id',   $value['id']);
  $xml .= "\t" . xmlEntry('text', $value['text']);
  $xml .= "</option>\n";
}
$xml .= xmlEntry("message", 'Success select length = ' . sizeof($kFields));

echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>