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
//  Path: /modules/phreebooks/pages/admin/pre_process.php
//
$security_level = validate_user(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreedom', 'admin');
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'classes/install.php');
require_once(DIR_FS_WORKING . 'classes/chart_of_accounts.php');
require_once(DIR_FS_WORKING . 'classes/tax_auths.php');
require_once(DIR_FS_WORKING . 'classes/tax_auths_vend.php');
require_once(DIR_FS_WORKING . 'classes/tax_rates.php');
require_once(DIR_FS_WORKING . 'classes/tax_rates_vend.php');

/**************   page specific initialization  *************************/
$error  = false; 
$install           = new phreebooks_admin();
$chart_of_accounts = new chart_of_accounts();
$tax_auths         = new tax_auths();
$tax_auths_vend    = new tax_auths_vend();
$tax_rates         = new tax_rates();
$tax_rates_vend    = new tax_rates_vend();

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'import':
	validate_security($security_level, 3);
  	$delete_chart = ($_POST['delete_chart']) ? true : false;
	$std_chart    = db_prepare_input($_POST['std_chart']);
	// first verify the file was uploaded ok
	if (!$std_chart) if (!validate_upload('file_name', 'text', 'txt')) break;
	if ($delete_chart) {
	  $result = $db->Execute("select id from " . TABLE_JOURNAL_MAIN . " limit 1");
	  if ($result->RecordCount() > 0) {
	    $messageStack->add(GL_JOURNAL_NOT_EMTPY,'error');
	    break;
	  }
	  $db->Execute("TRUNCATE TABLE " . TABLE_CHART_OF_ACCOUNTS);
	  $db->Execute("TRUNCATE TABLE " . TABLE_CHART_OF_ACCOUNTS_HISTORY);
	}
	$filename = $std_chart ? (DIR_FS_WORKING.'language/'.$_SESSION['language'].'/charts/'.$std_chart) : $_FILES['file_name']['tmp_name'];
	$accounts = xml_to_object(file_get_contents($filename));
	if (is_object($accounts->ChartofAccounts)) $accounts = $accounts->ChartofAccounts; // just pull the first one
	if (is_object($accounts->account)) $accounts->account = array($accounts->account); // in case of only one chart entry
	if (is_array($accounts->account)) foreach ($accounts->account as $account) {
	  $result = $db->Execute("select id from " . TABLE_CHART_OF_ACCOUNTS . " where id = '" . $account->id . "'");
	  if ($result->RecordCount() > 0) {
	    $messageStack->add(sprintf(GL_ACCOUNT_DUPLICATE, $account->id),'error');
		continue;
	  }
	  $sql_data_array = array(
	    'id'              => $account->id,
		'description'     => $account->description,
		'heading_only'    => $account->heading,
		'primary_acct_id' => $account->primary,
		'account_type'    => $account->type,
	  );
	  db_perform(TABLE_CHART_OF_ACCOUNTS, $sql_data_array, 'insert');
	}
	build_and_check_account_history_records();
	break;
  case 'save':
	validate_security($security_level, 3);
  	// some special values for checkboxes
	$_POST['ar_use_credit_limit'] = isset($_POST['ar_use_credit_limit']) ? '1' : '0';
	$_POST['ap_use_credit_limit'] = isset($_POST['ap_use_credit_limit']) ? '1' : '0';
	// save general tab
	foreach ($install->keys as $key => $default) {
	  $field = strtolower($key);
      if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
    }
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	$messageStack->add(PHREEBOOKS_CONFIG_SAVED,'success');
    break;
  case 'delete':
	validate_security($security_level, 4);
    $subject = $_POST['subject'];
    $id      = $_POST['rowSeq'];
	if (!$subject || !$id) break;
    $$subject->btn_delete($id);
    switch($subject) {
      case 'chart_of_accounts': $default_tab_id = 'chart_of_accounts'; break;
      case 'tax_auths':         $default_tab_id = 'tax_auths';         break;
      case 'tax_auths_vend':    $default_tab_id = 'tax_auths_vend';    break;
      case 'tax_rates':         $default_tab_id = 'tax_rates';         break;
      case 'tax_rates_vend':    $default_tab_id = 'tax_rates_vend';    break;
    }
	break;
  default:
}

/*****************   prepare to display templates  *************************/
// build some general pull down arrays
$sel_yes_no = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_YES),
);

$sel_gl_desc = array(
 array('id' => '0', 'text' => TEXT_NUMBER),
 array('id' => '1', 'text' => TEXT_DESCRIPTION),
 array('id' => '2', 'text' => TEXT_BOTH),
);

$sel_order_lines = array(
  array('id' => '0', 'text' => TEXT_DOUBLE_MODE),
  array('id' => '1', 'text' => TEXT_SINGLE_MODE),
);

$sel_inv_due = array( // invoice date versus due date for aging
 array('id' => '0', 'text' => BNK_INVOICE_DATE),
 array('id' => '1', 'text' => BNK_DUE_DATE),
);

// load available charts based on language
if (is_dir($dir = DIR_FS_WORKING.'language/'.$_SESSION['language'].'/charts')) { $charts = scandir($dir); }
  else { $charts = scandir(DIR_FS_WORKING . 'language/en_us/charts'); }
$sel_chart = array(array('id' => '0', 'text' => TEXT_SELECT));
foreach ($charts as $chart) {
  if (strpos($chart, 'xml')) {
	$temp = xml_to_object(file_get_contents(DIR_FS_WORKING . 'language/' . $_SESSION['language'] . '/charts/' . $chart));
	if ($temp->ChartofAccounts) $temp = $temp->ChartofAccounts;
    $sel_chart[] = array('id' => $chart, 'text' => $temp->description);
  }
}

// some pre-defined gl accounts
$cash_chart = gen_coa_pull_down(2, false, true, false, $restrict_types = array(0));    // cash types only
$ar_chart   = gen_coa_pull_down(2, false, true, false, $restrict_types = array(2));    // ar types only
$ap_chart   = gen_coa_pull_down(2, false, true, false, $restrict_types = array(20));   // ap types only
$ocl_chart  = gen_coa_pull_down(2, false, true, false, $restrict_types = array(22));   // other current liability types only
$inc_chart  = gen_coa_pull_down(2, false, true, false, $restrict_types = array(30));   // income types only
$inv_chart  = gen_coa_pull_down(2, false, true, false, $restrict_types = array(4,34)); // inv, expenses types only

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_PHREEBOOKS_MODULE_ADM);

?>