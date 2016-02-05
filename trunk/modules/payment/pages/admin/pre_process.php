<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/payment/pages/admin/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
// see if installing or removing a method
if (substr($_REQUEST['action'], 0, 8) == 'install_') {
  $method = substr($_REQUEST['action'], 8);
  $_REQUEST['action'] = 'install';
} elseif (substr($_REQUEST['action'], 0, 7) == 'remove_') {
  $method = substr($_REQUEST['action'], 7);
  $_REQUEST['action'] = 'remove';
}
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'install':
	\core\classes\user::validate_security($security_level, 4);
	$admin->classes['payment']->methods[$method]->install();
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'remove';
	\core\classes\user::validate_security($security_level, 4);
	$admin->classes['payment']->methods[$method]->delete();
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'save':
	\core\classes\user::validate_security($security_level, 3);
  	// foreach method if enabled, save info
	foreach ($admin->classes['payment']->methods as $method) {
	  	if ($method->installed) $method->update();
	}
	// save general tab
	foreach ($admin->classes['payment']->keys as $key => $default) {
	  	$field = strtolower($key);
      	if (isset($_POST[$field])) $admin->DataBase->write_configure($key, $_POST[$field]);
    }
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
    break;
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
define('PAGE_TITLE', TEXT_PAYMENT_MODULE);

?>