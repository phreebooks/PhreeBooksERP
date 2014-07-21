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
//  Path: /modules/phreeform/ajax/load_email_msg.php
//

/**************   Check user security   *****************************/
$security_level = validate_ajax_user(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');

/**************   page specific initialization  *************************/
$rID = $_GET['rID'];
if (!$rID) die;

$result  = $db->Execute("select doc_title from " . TABLE_PHREEFORM . " where id = '" . $rID . "'");
$subject = $result->fields['doc_title'] . ' ' . TEXT_FROM . ' ' . COMPANY_NAME;
$report  = get_report_details($rID);

if (!$report->emailmessage) {
  $text = sprintf(PHREEFORM_EMAIL_BODY, $result->fields['doc_title'], COMPANY_NAME);
} else {
  $text = TextReplace($report->emailmessage);
}

$xml  = '';
$xml .= "\t" . xmlEntry("subject", $subject);
$xml .= "\t" . xmlEntry("text",    $text);

// error check

echo createXmlHeader() . $xml . createXmlFooter();
die;
?>