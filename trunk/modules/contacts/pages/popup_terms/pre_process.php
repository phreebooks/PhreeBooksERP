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
//  Path: /modules/contacts/pages/popup_terms/pre_process.php
//

$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$account_type = (isset($_GET['type']) ? $_GET['type'] : 'c');	// current types are c (customer) and v (vendor)
switch ($account_type) {
  default:
  case 'c': 
	$terms_type       = 'AR';
	$credit_limit     = AR_CREDIT_LIMIT_AMOUNT;
	$discount_percent = AR_PREPAYMENT_DISCOUNT_PERCENT;
	$discount_days    = AR_PREPAYMENT_DISCOUNT_DAYS;
	$num_days_due     = AR_NUM_DAYS_DUE;
	break;
  case 'v': 
	$terms_type       = 'AP';
	$credit_limit     = AP_CREDIT_LIMIT_AMOUNT;
	$discount_percent = AP_PREPAYMENT_DISCOUNT_PERCENT;
	$discount_days    = AP_PREPAYMENT_DISCOUNT_DAYS;
	$num_days_due     = AP_NUM_DAYS_DUE;
}

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_terms/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  default:
}

/*****************   prepare to display templates  *************************/
$cal_terms = array(
  'name'      => 'dateReference',
  'form'      => 'popup_terms',
  'fieldname' => 'due_date',
  'imagename' => 'btn_terms',
  'default'   => '',
  'params'    => array('align' => 'left'),
);

$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', ACT_POPUP_TERMS_WINDOW_TITLE);

?>