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
//  Path: /modules/phreeform/ajax/box_load.php
//

/**************   Check user security   *****************************/
$xml = NULL;
$security_level = validate_ajax_user(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');
require_once(DIR_FS_MODULES . 'phreeform/pages/popup_build/box_html.php');

/**************   page specific initialization  *************************/
$rowID = $_GET['rowID'];
$rID   = $_GET['rID'];
$type  = $_GET['type'] ? $_GET['type'] : '';
if (!isset($_GET['rowID'])) {
  echo createXmlHeader() . xmlEntry('error', 'Row ID was not passed!') . createXmlFooter();
  die;
}

if ($rID) $report = get_report_details($rID);
$kFonts       = gen_build_pull_down($Fonts);
$kFontSizes   = gen_build_pull_down($FontSizes);
$kLineSizes   = gen_build_pull_down($LineSizes);
$kFontColors  = gen_build_pull_down($FontColors);
$kFontAlign   = gen_build_pull_down($FontAlign);
$cFields      = CreateCompanyArray();
$fFields      = crit_build_pull_down($CritChoices);
$kFields      = CreateSpecialDropDown($report);
$kTblFields   = CreateFieldTblDropDown($report);
$kTables      = CreateTableList($report);
$nyChoice     = gen_build_pull_down($NoYesChoice);
$pFields      = gen_build_pull_down($FormProcessing);
$tProcessing  = gen_build_pull_down($TextProcessing);

if (!$type) { // use the first type of the FormEntries array since it will be shown first with a new line
  $temp = array_keys($FormEntries);
  $type = array_shift($temp);
}

$properties       = new objectInfo();
$properties->type = $type;
$output           = box_build($properties, $rowID);

$xml .= xmlEntry("rowID",  $rowID);
$xml .= xmlEntry("html", $output);
//$xml .= xmlEntry("debug", 'sizeof kFields= ' . sizeof($kFields) . ' and rowID = ' . $rowID);
$xml .= xmlEntry("message", 'Success type = ' . $type . ' and html length = ' . strlen($output));

echo createXmlHeader() . $xml . createXmlFooter();
die;
?>