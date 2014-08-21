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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/audit/pages/admin/pre_process.php
//

/**************   Check user security   *****************************/
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module);
gen_pull_language($module, 'admin');
/**************   page specific initialization  *************************/
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  	try{
		\core\classes\user::validate_security($security_level, 3);
		// save general tab
		foreach ($admin->classes['audit']->keys as $key => $default) {
		  $field = strtolower($key);
	      if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
	    }
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		$messageStack->add(ZENCART_CONFIG_SAVED, 'success');
	    break;
  	}catch(\Exception $e) {
	  	$messageStack->add($e->getMessage(), $e->getCode());
	}
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

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_AUDIT_CONFIGURATION);

?>