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
//  Path: /modules/phreedom/pages/popup_setup/pre_process.php
//
/**************  include page specific files    *********************/
$topic   = $_GET['topic'];
$subject = $_GET['subject'];
if (!$subject || !$topic) die('The popup_setup script require a topic name and a subject name!');
gen_pull_language($topic, 'admin');
gen_pull_language('phreedom','admin');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
require_once(DIR_FS_MODULES . $topic . '/classes/' . $subject . '.php');
/**************   page specific initialization  *************************/
$close_popup    = false;
$sID            = $_GET['sID'];
$subject_module = new $subject();
/**************   Check user security   *****************************/
$security_level = $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION];
if ($security_level == 0) { // not supposed to be here
  $messageStack->add(ERROR_NO_PERMISSION, 'error');
  $close_popup = true;
}
/***************   hook for custom actions  ***************************/
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
    if ($subject_module->btn_save($sID)) $close_popup = true;
	break;
  default:
}
/*****************   prepare to display templates  *************************/
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', $subject_module->title);

?>