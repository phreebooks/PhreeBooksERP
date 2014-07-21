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
//  Path: /modules/phreedom/ajax/tab_details.php
//
/**************   Check user security   *****************************/
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
gen_pull_language('phreedom', 'admin');
gen_pull_language($_GET['mod'], 'admin');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
/**************   page specific initialization  *************************/
$page    = $_GET['mod'];
$subject = $_GET['subject'];
$rID     = $_GET['rID'];
$xml     = NULL;

if (!$page || !subject) die('no subject or module');
if (!$_REQUEST['list']) 	$_REQUEST['list'] = 1;
if (!$_REQUEST['action']) 	$_REQUEST['action'] = 'go_first';

require_once(DIR_FS_MODULES . $page . '/classes/' . $subject . '.php');
$my_class = new $subject();
$my_class->message = false;

switch ($_REQUEST['action']) {
  case 'delete':      if ($rID) $my_class->btn_delete($rID); break;
  case 'update':      $my_class->btn_update($rID); break;
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'go_page':                            break;
}

// put the output together
$xml .= "\t" . xmlEntry("subject",      $subject);
$xml .= "\t" . xmlEntry("htmlContents", "<div>" . $my_class->build_main_html() . "</div>");
if ($my_class->message) $xml .= "\t" . xmlEntry("message", $my_class->message);
echo createXmlHeader() . $xml . createXmlFooter();
die;
?>