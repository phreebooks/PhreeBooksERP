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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/phreeform/ajax/validate_dir.php
//
$fID = $_GET['fID'] ? $_GET['fID'] : 'validateDir';

/**************   Check user security   *****************************/
$xml = NULL;
$security_level = $_SESSION['admin_security'][SECURITY_ID_PHREEFORM];
if ($security_level == 0) { // no permission to enter page, return error
  echo createXmlHeader($fID) . xmlEntry('error', ERROR_NO_PERMISSION) . createXmlFooter();
  die;
}

/**************  include page specific files    *********************/
gen_pull_language($module);

/**************   page specific initialization  *************************/
$id = $_GET['id'];

if (!$id) die;
$xml = '';
// This script checks the fieldname and field reference and validates that it is good.
$result = $db->Execute("select * from " . TABLE_PHREEFORM . " where id = '" . $id . "'");

// if we have a row, id was valid
if ($result->RecordCount() > 0) {
  foreach ($result->fields as $key => $value) $xml .= xmlEntry($key, $value) . chr(10);
} else {
  $message = PHREEFORM_AJAX_INVALID_ID;
}

//put it all together
echo createXmlHeader($fID) . $xml . createXmlFooter();
die;
?>