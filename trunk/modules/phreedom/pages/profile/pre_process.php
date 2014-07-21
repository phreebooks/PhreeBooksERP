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
//  Path: /modules/phreedom/pages/profile/pre_process.php
//
$security_level = validate_user(SECURITY_ID_MY_PROFILE);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
/**************   page specific initialization  *************************/
$error  = false;
$result = $db->Execute("select admin_prefs from " . TABLE_USERS . " where admin_id = " . $_SESSION['admin_id']);
$prefs  = unserialize($result->fields['admin_prefs']);
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/profile/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	validate_security($security_level, 4);
	$prefs['theme']  = db_prepare_input($_POST['theme']);
	$prefs['menu']   = db_prepare_input($_POST['menu']);
	$prefs['colors'] = db_prepare_input($_POST['colors']);
	if (!$prefs['colors']) {
		$error = $messageStack->add(GEN_ERROR_NO_THEME_COLORS,'error');
		break;
	}
	db_perform(TABLE_USERS, array('admin_prefs'=>serialize($prefs)), 'update', 'admin_id = '.$_SESSION['admin_id']);
	$_SESSION['admin_prefs']['theme']  = $prefs['theme'];
	$_SESSION['admin_prefs']['menu']   = $prefs['menu'];
	$_SESSION['admin_prefs']['colors'] = $prefs['colors'];
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(), 'SSL'));
	break;
  default:
}
/*****************   prepare to display templates  *************************/
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_HEADING_PROFILE);
?>