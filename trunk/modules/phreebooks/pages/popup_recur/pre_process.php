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
//  Path: /modules/phreebooks/pages/popup_recur/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
define('JOURNAL_ID',$_GET['jID']);

switch (JOURNAL_ID) {
	case  2:	// General Journal
	case  4:	// Purchase Order Journal
	case  6:	// Purchase/Receive Journal
	case 10:	// Sales Order Journal
	case 12:	// Sales/Invoice Journal
		break;
	default:
		die('No valid journal id found (filename: modules/phreebooks/pages/popup_recur.php), Journal ID needs to be passed to this script to identify the correct procedure.');
}

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_recur/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	break;
  default:
}

/*****************   prepare to display templates  *************************/

$include_header = false; // include header flag
$include_footer = false; // include footer flag
$include_template = 'template_main.php'; // include display template (required)
define('PAGE_TITLE', ORD_RECUR_WINDOW_TITLE);

?>