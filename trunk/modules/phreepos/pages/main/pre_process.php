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
//  Path: /modules/phreepos/pages/main/pre_process.php
//
$security_level = validate_user(SECURITY_ID_PHREEPOS);
define('JOURNAL_ID',19);
/**************  include page specific files    *********************/
gen_pull_language('contacts');
gen_pull_language('phreebooks');
gen_pull_language('inventory');
gen_pull_language('phreeform');
require_once(DIR_FS_MODULES . 'payment/defaults.php');
require_once(DIR_FS_MODULES . 'inventory/defaults.php');
require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
require_once(DIR_FS_WORKING . 'classes/tills.php');
require_once(DIR_FS_WORKING . 'classes/other_transactions.php');
if (file_exists(DIR_FS_MODULES . 'phreepos/custom/classes/journal/journal_'.JOURNAL_ID.'.php')) { 
	require_once(DIR_FS_MODULES . 'phreepos/custom/classes/journal/journal_'.JOURNAL_ID.'.php') ; 
}else{
    require_once(DIR_FS_MODULES . 'phreepos/classes/journal/journal_'.JOURNAL_ID.'.php'); // is needed here for the defining of the class and retriving the security_token
}
/**************   page specific initialization  *************************/
define('ORD_ACCT_ID',		GEN_CUSTOMER_ID);
define('GL_TYPE',			'sos');
define('DEF_INV_GL_ACCT',	AR_DEF_GL_SALES_ACCT);
define('DEF_GL_ACCT',		AR_DEFAULT_GL_ACCT);
define('DEF_GL_ACCT_TITLE',	ORD_AR_ACCOUNT);
define('POPUP_FORM_TYPE',	'pos:rcpt');
$account_type = 'c';
$order        = new journal_19();
$tills        = new tills();
$trans	 	  = new other_transactions();
$payment_modules = load_all_methods('payment');
$extra_ThirdToolbar_buttons = null;
$extra_toolbar_buttons		= null;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/

/*****************   prepare to display templates  *************************/
// generate address arrays for javascript
$js_arrays = gen_build_company_arrays();
// load the tax rates
$tax_rates = ord_calculate_tax_drop_down($account_type);
// generate a rate array parallel to the drop down for the javascript total calculator
$js_tax_rates = 'var tax_rates = new Array();' . chr(10);
for ($i = 0; $i < count($tax_rates); $i++) {
  $js_tax_rates .= 'tax_rates[' . $i . '] = new salesTaxes("' . $tax_rates[$i]['id'] . '", "' . $tax_rates[$i]['text'] . '", "' . $tax_rates[$i]['rate'] . '");' . chr(10);
}

$ot_tax_rates = ord_calculate_tax_drop_down('v');
$js_ot_tax_rates = 'var ot_tax_rates = new Array();' . chr(10);
for ($i = 0; $i < count($ot_tax_rates); $i++) {
  $js_ot_tax_rates .= 'ot_tax_rates[' . $ot_tax_rates[$i]['id'] . '] = new purTaxes("' . $ot_tax_rates[$i]['id'] . '", "' . $ot_tax_rates[$i]['text'] . '", "' . $ot_tax_rates[$i]['rate'] . '");' . chr(10);
}
//payment modules
// generate payment choice arrays for receipt of payments
$js_pmt_types = 'var pmt_types = new Array();' . chr(10);
foreach ($payment_modules as $key => $pmts) {
  $pmt_method = $pmts['id'];
  $$pmt_method = new $pmt_method;
  if($$pmt_method->show_in_pos == false || $$pmt_method->pos_gl_acct == '') {
  	unset($payment_modules[$key]);
  }else{
  	$js_pmt_types .= 'pmt_types[\'' . $pmts['id'] . '\'] = "' . $pmts['text'] . '";' . chr(10);
  }
}
//check if setting are right for usage of phreepos 
if(count($payment_modules) < 1 ){
	$messageStack->add(ERROR_NO_PAYMENT_METHODES, 'error');
	gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
}
if(AR_TAX_BEFORE_DISCOUNT == false && PHREEPOS_DISCOUNT_OF == true ){ // tax after discount
	$messageStack->add('your setting tax before discount and discount over total don\'t work together, <br/>This has circulair logic one can\'t preceed the other', 'error');
	gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
}
$js_currency  = 'var currency  = new Array();' . chr(10);
foreach ($currencies->currencies as $key => $currency) {
	$js_currency .= 'currency["' . $key . '"] = new currencyType("' . $key . '", "'. $currency['title'] . '", "'. $currency['value'] . '", "'. $currency['decimal_point'] . '", "' . $currency['thousands_point'] . '", "' . $currency['decimal_places'] . '", "' . $currency['decimal_precise'] . '");' . chr(10);
}
// see if current user points to a employee for sales rep default
$result = $db->Execute("select account_id from " . TABLE_USERS . " where admin_id = " . $_SESSION['admin_id']);
$default_sales_rep = $result->fields['account_id'] ? $result->fields['account_id'] : '0';
// build the display options
$template_options = array();
$req_date = date(DATE_FORMAT);

$include_header   = false;
$include_footer   = false;

switch ($_REQUEST['action']) {
  case 'pos_return': 
    $include_template = 'template_return.php';
	define('PAGE_TITLE', BOX_PHREEPOS_RETURN);
    break;
  default: 
    $include_template = 'template_main.php';
	define('PAGE_TITLE', BOX_PHREEPOS);
	break;
}


define('PAYMENT_TITLE', PHREEPOS_PAYMENT_TITLE);
 
?>