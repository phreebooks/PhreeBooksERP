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
//  Path: /modules/assets/pages/admin/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreedom', 'admin');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
/**************   page specific initialization  *************************/
$tabs   = new \assets\classes\tabs();
$fields = new \assets\classes\fields();
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'insert':      $subject_module->btn_insert(); break;
  case 'save':        $subject_module->btn_save();   break;
  case 'delete':
  	\core\classes\user::validate_security($security_level, 4); // security check
    $subject = $_POST['subject'];
    $id      = $_POST['rowSeq'];
	if (!$subject || !$id) break;
    $$subject->btn_delete($id);
	break;
  case 'update':      $subject_module->btn_update(); break;
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
// build some general pull down arrays
$sel_yes_no = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_YES),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_ASSETS_ADMIN);

?>