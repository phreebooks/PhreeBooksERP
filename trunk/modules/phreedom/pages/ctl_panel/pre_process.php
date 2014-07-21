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
//  Path: /modules/phreedom/pages/ctl_panel/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/

/**************   page specific initialization  *************************/
$menu_id = $_GET['mID'];
// retrieve all modules from directory, and available dashboards
if (!isset($dirs)) $dirs = scandir(DIR_FS_MODULES);
$dashboards = array();
foreach ($dirs as $dir) {
  if (defined('MODULE_' . strtoupper($dir) . '_STATUS') && file_exists(DIR_FS_MODULES . $dir . '/dashboards/')) {
    $choices = scandir(DIR_FS_MODULES . "$dir/dashboards/");
	foreach ($choices as $dashboard) {
	  if ($dashboard == '.' || $dashboard == '..' || !file_exists(DIR_FS_MODULES."$dir/dashboards/$dashboard/$dashboard.php")) continue;
	  $dashboards[] = array('module_id' => $dir, 'dashboard_id' => $dashboard);
	}
  }
}

// retireve current user profile for this page
$my_profile = array();
$result = $db->Execute("select dashboard_id from " . TABLE_USERS_PROFILES . " 
  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "'");
while (!$result->EOF) {
  $my_profile[] = $result->fields['dashboard_id'];
  $result->MoveNext();
}

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/ctl_panel/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  	foreach ($dashboards as $dashboard) {
	  // build add and delete list
	  // if post is set and not in my_profile -> add
	  if (isset($_POST[$dashboard['dashboard_id']]) && !in_array($dashboard['dashboard_id'], $my_profile)) {
	  	include_once (DIR_FS_MODULES . $dashboard['module_id'] . '/dashboards/' . $dashboard['dashboard_id'] . '/' . $dashboard['dashboard_id'] . '.php');
	    $dbItem = new $dashboard['dashboard_id'];
	    $dbItem->menu_id      = $menu_id;
	    $dbItem->module_id    = $dashboard['module_id'];
		$dbItem->Install();
	  }
	  // if post is not set and in my_profile -> delete
	  if (!isset($_POST[$dashboard['dashboard_id']]) && in_array($dashboard['dashboard_id'], $my_profile)) { // delete it
	  	include_once  (DIR_FS_MODULES . $dashboard['module_id'] . '/dashboards/' . $dashboard['dashboard_id'] . '/' . $dashboard['dashboard_id'] . '.php');
	    $dbItem = new $dashboard['dashboard_id'];
	    $dbItem->menu_id      = $menu_id;
	    $dbItem->module_id    = $dashboard['module_id'];
	    $dbItem->Remove();
	  }
	}
	gen_redirect(html_href_link(FILENAME_DEFAULT, '&module=phreedom&page=main&mID=' . $menu_id, 'SSL'));
	break;
  default:
}

/*****************   prepare to display templates  *************************/

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', CP_ADD_REMOVE_BOXES);

?>