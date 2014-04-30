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
//  Path: /modules/phreedom/pages/encryption/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_ENCRYPTION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
/**************   page specific initialization  *************************/
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/encryption/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
    $enc_key = db_prepare_input($_POST['enc_key']);
    $enc_key_confirm = db_prepare_input($_POST['enc_key_confirm']);
	if ($enc_key <> $enc_key_confirm) throw new \core\classes\userException(TEXT_ERROR_ENCRYPTION_KEY_MATCH);
	\core\classes\encryption::validate_password($enc_key, ENCRYPTION_VALUE);
	$_SESSION['admin_encrypt'] = $enc_key;
    $messageStack->add(GEN_ENCRYPTION_KEY_SET,'success');
	break;
  case 'encrypt_key':
	\core\classes\user::validate_security($security_level, 4);
	$old_key =         db_prepare_input($_POST['old_encrypt_key']);
	$new_key =         db_prepare_input($_POST['new_encrypt_key']);
	$new_key_confirm = db_prepare_input($_POST['new_encrypt_confirm']);
	if (defined('ENCRYPTION_VALUE')){
		try{
			\core\classes\encryption::validate_password($old_key, ENCRYPTION_VALUE);
		}catch(Exception $e){
			throw new \core\classes\userException(ERROR_OLD_ENCRYPT_NOT_CORRECT,$e->getCode(),$e);
		}
	}
	if (strlen($new_key) < ENTRY_PASSWORD_MIN_LENGTH) throw new \core\classes\userException(sprintf(ENTRY_PASSWORD_NEW_ERROR, ENTRY_PASSWORD_MIN_LENGTH));
	if ($new_key != $new_key_confirm) throw new \core\classes\userException(ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
	write_configure('ENCRYPTION_VALUE', \core\classes\encryption::password($new_key));
    $messageStack->add(GEN_ENCRYPTION_KEY_CHANGED,'success');
    break;
  default:
}
/*****************   prepare to display templates  *************************/
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_DATA_ENCRYPTION);

?>