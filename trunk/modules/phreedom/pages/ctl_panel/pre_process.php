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
//  Path: /modules/phreedom/pages/ctl_panel/pre_process.php
//
$security_level = \core\classes\user::validate(0, true);
/**************  include page specific files    *********************/

/**************   page specific initialization  *************************/
$menu_id = $_GET['mID'];
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
  		foreach ($admin_classes as $module_class) {
  			if ($module_class->installed){
				// build add and delete list
		  		// if post is set and not in my_profile -> add
		  		foreach ($module_class->dashboards as $dashboard){
			  		if (isset($_POST[$dashboard->id]) && !in_array($dashboard->id, $my_profile)) {
						$dashboard->install();
			  		}else{
			  			// 	if post is not set and in my_profile -> delete
			  			$dashboard->delete();
			  		}
		  		}
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