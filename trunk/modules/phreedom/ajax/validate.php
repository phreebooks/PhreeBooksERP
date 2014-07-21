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
//  Path: /modules/phreedom/ajax/validate.php
//

/**************   Check user security   *****************************/
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$xml   = NULL;
$user  = $_GET['u'];
$pass  = $_GET['p'];
$level = $_GET['level'];

$result = $db->Execute("select inactive, admin_pass from " . TABLE_USERS . " where admin_name = '" . $user . "'");
if ($result->RecordCount() <> 1 || $result->fields['inactive']) {
  $xml = xmlEntry('result', 'failed');
} elseif (!pw_validate_password($pass, $result->fields['admin_pass'])) {
  $xml = xmlEntry('result', 'failed');
} else {
  $xml = xmlEntry('result', 'validated');
}

echo createXmlHeader() . $xml . createXmlFooter();
die;
?>