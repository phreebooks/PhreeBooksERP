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
//  Path: /modules/phreemail/pages/popup_email/pre_process.php
//

/**************   Check user security   *****************************/
$security_level = \core\classes\user::validate(SECURITY_PHREEMAIL_MGT);
/**************  include page specific files  *********************/
require(DIR_FS_MODULES . 'phreeform/language/'  . $_SESSION['language'] . '/language.php'); // error messages, qualifiers

/**************   page specific initialization  *************************/
$recpt_email 		= '';
$recpt_name  		= '';
$senderlist 		= array();
$sendermaillist 	= array();
$receiverlist 		= array();
$receivermaillist 	= array();
// check for a contact id to retrieve email information
$cID 		= isset($_GET['cID']) ? $_GET['cID'] : '';
$email_id 	= isset($_GET['mID']) ? $_GET['mID'] : '';
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_email/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
if($_REQUEST['action'] = 'send'){
	$from_name     = ($_POST['sender_name'])     ? $_POST['sender_name']     : $sender_name;
	$from_address  = ($_POST['sender_email'])    ? $_POST['sender_email']    : $sender_email;
	$to_name       = ($_POST['recpt_name'])      ? $_POST['recpt_name']      : $recpt_name;
	$to_address    = ($_POST['recpt_email'])     ? $_POST['recpt_email']     : $recpt_email;
	$cc_name       = ($_POST['cc_name'])         ? $_POST['cc_name']         : $_POST['cc_email'];
	$cc_address    = ($_POST['cc_email'])        ? $_POST['cc_email']        : '';
	$email_subject = ($_POST['message_subject']) ? $_POST['message_subject'] : $message_subject;
	$email_text    = ($_POST['message_body'])    ? $_POST['message_body']    : $message_body;

	$block = array();
	if ($cc_address) {
		$block['EMAIL_CC_NAME']    = $cc_name;
		$block['EMAIL_CC_ADDRESS'] = $cc_address;
	}
	validate_send_mail($to_name, $to_address, $email_subject, $email_text, $from_name, $from_address, $block);
	$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_SEND, TEXT_EMAIL , ''), 'success');
	echo '<script type="text/javascript"> window.opener.location.reload();' . chr(10);
	echo'self.close();</script>' . chr(10);
}
/*****************   prepare to display templates  *************************/
$include_header   = false;
$include_footer   = false;
// fetch the email information
$result = $admin->DataBase->Execute("select display_name, admin_email from " . TABLE_USERS);
while (!$result->EOF){
	if($result->fields['admin_email'] != ''){
		$sendermaillist[] = $result->fields['admin_email'];
		$senderlist[] = $result->fields['display_name'];
	}
	$result->MoveNext();
}
$result 	  = $admin->DataBase->Execute("select display_name, admin_email from " . TABLE_USERS . " where admin_id = " . $_SESSION['admin_id']);
$sender_name  = $result->fields['display_name'];
$sender_email = $result->fields['admin_email'];

if($cID != ''){
	$result 		= $admin->DataBase->Execute("select email, primary_name from " . TABLE_ADDRESS_BOOK . " where ref_id = '$cID' and type like '%m'");
	while (!$result->EOF){
		if($result->fields['email'] != ''){
			$receivermaillist[] = $result->fields['email'];
			$receiverlist[] = $result->fields['primary_name'];
		}
		$result->MoveNext();
	}
	if($result->RecordCount() != 0){
  		$recpt_email 	= $result->fields['email'];
  		$recpt_name   	= $result->fields['primary_name'];
	}
}




$include_template = 'template_main.php'; // include display template (required)
define('PAGE_TITLE', TEXT_PHREEBOOKS_ERP . ' - ' . COMPANY_NAME);

?>