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
//  Path: /modules/phreedom/pages/main/pre_process.php
//
/**************   Check user security   *****************************/
// Not here because may not be logged in
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
/**************   page specific initialization  *************************/
$menu_id      = isset($_GET['mID']) ? $_GET['mID'] : 'index'; // default to index unless heading is passed
if (isset($_GET['req']) && $_GET['req'] == 'pw_lost_sub') $_REQUEST['action'] = 'pw_lost_sub';
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {

  	case 'save':
		$dashboard_id = db_prepare_input($_POST['dashboard_id']);
		// since we don't know where the module is, go find it.
		foreach ($admin->classes as $module_class) {
	  		foreach ($module_class->dashboards as $dashboard){
		  		if ($dashboard->id == $dashboard_id) {
		  			load_method_language(DIR_FS_MODULES . "{$module_class->id}/dashboards/{$dashboard->id}");
		  			$dashboard->menu_id	= $menu_id;
					$dashboard->params	= array();
					$dashboard->update();
		  		}
  			}
		}
		break;
  	case 'delete':
		$dashboard_id = db_prepare_input($_POST['dashboard_id']);
		//since we don't know where the module is, go find it.
		foreach ($admin->classes as $module_class) {
	  		foreach ($module_class->dashboards as $dashboard){
		  		if ($dashboard->id == $dashboard_id) {
		  			load_method_language(DIR_FS_MODULES . "{$module_class->id}/dashboards/{$dashboard->id}");
		  			$dashboard->menu_id	= $menu_id;
					$dashboard->remove();
		  		}
  			}
		}
		break;
  	case 'move_up':
  	case 'move_down':
		$dashboard_id = db_prepare_input($_POST['dashboard_id']);
		$sql = "select column_id, row_id from " . TABLE_USERS_PROFILES . "
		  where user_id={$_SESSION['admin_id']} and menu_id='$menu_id' and dashboard_id='$dashboard_id'";
		$result         = $admin->DataBase->Execute($sql);
		$current_row    = $result->fields['row_id'];
		$current_column = $result->fields['column_id'];
		$new_row        = ($_REQUEST['action'] == 'move_up') ? ($current_row - 1) : ($current_row + 1);
		$sql = "select max(row_id) as max_row from " . TABLE_USERS_PROFILES . "
		  where user_id={$_SESSION['admin_id']} and menu_id='$menu_id' and column_id='$current_column'";
		$result         = $admin->DataBase->Execute($sql);
		$max_row        = $result->fields['max_row'];
		if (($new_row >= 1 && $_REQUEST['action'] == 'move_up') || ($new_row <= $max_row && $_REQUEST['action'] == 'move_down')) {
		  	$sql = "update " . TABLE_USERS_PROFILES . " set row_id=0            where user_id={$_SESSION['admin_id']} and menu_id='$menu_id' and column_id=$current_column and row_id='$current_row'";
		  	$admin->DataBase->Execute($sql);
		  	$sql = "update " . TABLE_USERS_PROFILES . " set row_id=$current_row where user_id={$_SESSION['admin_id']} and menu_id='$menu_id' and column_id=$current_column and row_id='$new_row'";
		  	$admin->DataBase->Execute($sql);
		  	$sql = "update " . TABLE_USERS_PROFILES . " set row_id=$new_row     where user_id={$_SESSION['admin_id']} and menu_id='$menu_id' and column_id=$current_column and row_id=0";
		  	$admin->DataBase->Execute($sql);
		}
		break;
  	case 'move_left':
  	case 'move_right':
		$dashboard_id = db_prepare_input($_POST['dashboard_id']);
		$sql = "select column_id, row_id from " . TABLE_USERS_PROFILES . "
		  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "' and dashboard_id = '" . $dashboard_id . "'";
		$result         = $admin->DataBase->Execute($sql);
		$current_row    = $result->fields['row_id'];
		$current_column = $result->fields['column_id'];
		$new_col = ($_REQUEST['action'] == 'move_left') ? ($current_column - 1) : ($current_column + 1);
		if (($new_col >= 1 && $_REQUEST['action'] == 'move_left') || ($new_col <= MAX_CP_COLUMNS && $_REQUEST['action'] == 'move_right')) {
	  		$sql = "select max(row_id) as max_row from " . TABLE_USERS_PROFILES . "
			  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "' and column_id = '" . $new_col . "'";
	  		$result = $admin->DataBase->Execute($sql);
	  		$new_max_row = $result->fields['max_row'] + 1;
	  		$sql = "update  " . TABLE_USERS_PROFILES . " set column_id = " . $new_col . ", row_id = " . $new_max_row . "
			  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "' and dashboard_id = '" . $dashboard_id . "'";
	  		$admin->DataBase->Execute($sql);
	  		$sql = "update  " . TABLE_USERS_PROFILES . " set row_id = row_id - 1
			  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $menu_id . "'
			  and column_id = " . $current_column . " and row_id >= '" . $current_row . "'";
	  		$admin->DataBase->Execute($sql);
		}
		break;
  	case 'php_info':
  		die(phpinfo());
	default:
}


?>