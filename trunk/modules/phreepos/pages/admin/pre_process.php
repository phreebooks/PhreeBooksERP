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
//  Path: /modules/phreepos/pages/admin/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreedom', 'admin');
/**************   page specific initialization  *************************/
$tills   = new \phreepos\classes\tills();
$trans	 = new \phreepos\classes\other_transactions();
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  	\core\classes\user::validate_security($security_level, 3); // security check
	if(AR_TAX_BEFORE_DISCOUNT == false && PHREEPOS_DISCOUNT_OF == true && $_POST['phreepos_discount_of'] == 1 ){ // tax after discount
		throw new \core\classes\userException("your setting tax before discount and discount over total don't work together, <br/>This has circulair logic. one can't preceed the other");
	}else{
		// save general tab
		foreach ($admin_classes['phreepos']->keys as $key => $default) {
		  $field = strtolower($key);
	      if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
	    }
		$messageStack->add(TEXT_CONFIG_SAVED, 'success');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	    break;
	}
  case 'delete':
	\core\classes\user::validate_security($security_level, 4); // security check
    $subject = $_POST['subject'];
    $id      = $_POST['rowSeq'];
	if (!$subject || !$id) break;
    $$subject->btn_delete($id);
	break;
  default:
}
/*****************   prepare to display templates  *************************/
// build some general pull down arrays
$sel_yes_no = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_YES),
);

$sel_rounding = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_INTEGER),
 array('id' => '2', 'text' => TEXT_10_CENTS),
 array('id' => '3', 'text' => TEXT_NEUTRAL),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_PHREEPOS_ADMIN);

?>