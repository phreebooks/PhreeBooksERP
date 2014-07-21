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
//  Path: /modules/phreedom/pages/import_export/pre_process.php
//
$security_level = validate_user(SECURITY_ID_IMPORT_EXPORT);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreebooks');
gen_pull_language('phreebooks', 'admin');
gen_pull_language('contacts');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
require_once(DIR_FS_WORKING . 'classes/beg_balances_imp.php');
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
/**************   page specific initialization  *************************/
$error   = false; 
$subject = $_POST['subject'];
if (substr($_REQUEST['action'], 0, 3) == 'go_') {
  $subject = substr($_REQUEST['action'], 3);
  $_REQUEST['action']  = 'module';
} elseif (substr($_REQUEST['action'], 0, 11) == 'sample_xml_') {
  $db_table = substr($_REQUEST['action'], 11);
  $_REQUEST['action']   = 'sample_xml';
} elseif (substr($_REQUEST['action'], 0, 11) == 'sample_csv_') {
  $db_table = substr($_REQUEST['action'], 11);
  $_REQUEST['action']   = 'sample_csv';
} elseif (substr($_REQUEST['action'], 0, 13) == 'import_table_') {
  $db_table = substr($_REQUEST['action'], 13);
  $_REQUEST['action']   = 'import_table';
} elseif (substr($_REQUEST['action'], 0, 13) == 'export_table_') {
  $db_table = substr($_REQUEST['action'], 13);
  $_REQUEST['action']   = 'export_table';
}
$coa_types = load_coa_types();
$glEntry   = new journal();
$glEntry->journal_id = JOURNAL_ID;
// retrieve the original beginning_balances
$sql = "select c.id, beginning_balance, c.description, c.account_type
	from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
	where h.period = 1 order by c.id";
$result = $db->Execute($sql);
$glEntry->beg_bal = array();
while (!$result->EOF) {
  $glEntry->beg_bal[$result->fields['id']] = array(
	'desc'      => $result->fields['description'], 
	'type'      => $result->fields['account_type'],
	'type_desc' => $coa_types[$result->fields['account_type']]['text'],
	'beg_bal'   => $result->fields['beginning_balance'],
  );
  $glEntry->affected_accounts[$result->fields['id']] = true; // build list of affected accounts to update chart history
  $result->MoveNext();
}

$page_list = array();
$dir = scandir(DIR_FS_MODULES);
foreach ($dir as $file) {
  if (is_dir(DIR_FS_MODULES . $file) && $file <> '.' && $file <> '..') {
	if (file_exists(DIR_FS_MODULES . $file . '/' . $file . '.xml')) {
	  gen_pull_language($file, 'admin');
	  $page_list[$file] = array(
	    'title'     => constant('MODULE_' . strtoupper($file) . '_TITLE'),
		'structure' => load_module_xml($file),
	  );
	}
  }
}

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'phreedom/custom/pages/import_export/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'sample_xml':
  case 'sample_csv':
    $type = $_REQUEST['action']=='sample_csv' ? 'csv' : 'xml';
    switch ($type) {
	  case 'xml':
	  	$output = build_sample_xml($page_list[$subject]['structure'], $db_table);
		header("Content-type: plain/txt");
		break;
	  case 'csv':
	  	$output = build_sample_csv($page_list[$subject]['structure'], $db_table);
		header("Content-type: application/csv");
		break;
	}
	header("Content-disposition: attachment; filename=sample_$db_table.$type; size=" . strlen($output));
	header('Pragma: cache');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header('Connection: close');
	header('Expires: ' . date('r', time()+3600));
	header('Last-Modified: ' . date('r'));
	print $output;
	exit();  
  case 'import_table':
	$format = $_POST['import_format_' . $db_table];
	switch ($format) {
	  case 'xml':
		if (!validate_upload('file_name_' . $db_table, 'text', 'xml')) break;
    	$result = table_import_xml($page_list[$subject]['structure'], $db_table, 'file_name_' . $db_table);
	    break;
	  case 'csv':
		if (!validate_upload('file_name_' . $db_table, 'text', 'csv')) break;
    	$result = table_import_csv($page_list[$subject]['structure'], $db_table, 'file_name_' . $db_table);
	    break;
	}
	$_REQUEST['action'] = 'module'; // retun to module page
	break;
  case 'export_table':
	$format = $_POST['export_format_' . $db_table];
	switch ($format) {
	  case 'xml': $output = table_export_xml($page_list[$subject]['structure'], $db_table); break;
	  case 'csv': $output = table_export_csv($page_list[$subject]['structure'], $db_table); break;
	}
	if ($output) {
	  header("Content-disposition: attachment; filename=$db_table.$format; size=" . strlen($output));
	  header('Pragma: cache');
	  header('Cache-Control: public, must-revalidate, max-age=0');
	  header('Connection: close');
	  header('Expires: ' . date('r', time()+3600));
	  header('Last-Modified: ' . date('r'));
	  print $output;
	  exit();  
	} else{
	  $messageStack->add('There are no records in this database table.','caution');
	  $_REQUEST['action'] = 'module'; // retun to module page
	}
	break;
  case 'save_bb':
	validate_security($security_level, 4);
  	define('JOURNAL_ID',2);	// General Journal
	$total_amount = 0;
	$coa_values = $_POST['coa_value'];
	$index = 0;
	foreach ($glEntry->beg_bal as $coa_id => $values) {
	  if ($coa_types[$values['type']]['asset']) { // it is a debit
		$entry = $currencies->clean_value($coa_values[$index]);
	  } else { // it is a credit
		$entry = -$currencies->clean_value($coa_values[$index]);
	  }
	  $glEntry->beg_bal[$coa_id]['beg_bal'] = $entry;
	  $total_amount += $entry;
	  $index++;
	}
	// check to see if journal is still in balance
	$total_amount = $currencies->format($total_amount);
	if ($total_amount <> 0) {
	  $messageStack->add(GL_ERROR_NO_BALANCE, 'error');
	  break;
	}
	// *************** START TRANSACTION *************************
	$db->transStart();
	foreach ($glEntry->beg_bal as $account => $values) {
	  $sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
		set beginning_balance = " . $values['beg_bal'] . " 
		where period = 1 and account_id = '" . $account . "'";
	  $result = $db->Execute($sql);
	}
	if (!$glEntry->update_chart_history_periods($period = 1)) { // roll the beginning balances into chart history table
	  $glEntry->fail_message(GL_ERROR_UPDATE_COA_HISTORY);
	} else {
	  $db->transCommit();	// post the chart of account values
	  gen_add_audit_log('Enter Beginning Balances');
	  if (DEBUG) $messageStack->write_debug();
	  gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	  // *************** END TRANSACTION *************************
	}
	if (DEBUG) $messageStack->write_debug();
	$messageStack->add(GL_ERROR_NO_POST, 'error');
	break;

  case 'import_inv':
  case 'import_po':
  case 'import_ap':
  case 'import_so':
  case 'import_ar':
	validate_security($security_level, 4);
    switch ($_REQUEST['action']) {
	  case 'import_inv':
		$upload_name = 'file_name_inv';
		define('JOURNAL_ID',0);
		break;
	  case 'import_po':
		$upload_name = 'file_name_po';
		define('JOURNAL_ID',4);
		define('DEF_INV_GL_ACCT',AP_DEFAULT_INVENTORY_ACCOUNT);
		define('BB_ACCOUNT_TYPE','v');
		define('BB_GL_TYPE','poo');
		break;
	  case 'import_ap':
		$upload_name = 'file_name_ap';
		define('JOURNAL_ID',6);
		define('DEF_INV_GL_ACCT',AP_DEFAULT_INVENTORY_ACCOUNT);
		define('BB_ACCOUNT_TYPE','v');
		define('BB_GL_TYPE','por');
		break;
	  case 'import_so':
		$upload_name = 'file_name_so';
		define('JOURNAL_ID',10);
		define('DEF_INV_GL_ACCT',AR_DEFAULT_INVENTORY_ACCOUNT);
		define('BB_ACCOUNT_TYPE','c');
		define('BB_GL_TYPE','soo');
		break;
	  case 'import_ar':
		$upload_name = 'file_name_ar';
		define('JOURNAL_ID',12);
		define('DEF_INV_GL_ACCT',AR_DEFAULT_INVENTORY_ACCOUNT);
		define('BB_ACCOUNT_TYPE','c');
		define('BB_GL_TYPE','sos');
		break;
	}
	// preload the chart of accounts
	$result = $db->Execute("select id from " . TABLE_CHART_OF_ACCOUNTS);
	$coa = array();
	while (!$result->EOF) {
	  $coa[] = $result->fields['id'];
	  $result->MoveNext();
	}
	$result     = $db->Execute("select start_date from " . TABLE_ACCOUNTING_PERIODS . " where period = 1");
	$first_date = $result->fields['start_date'];
	// first verify the file was uploaded ok
	if (!validate_upload($upload_name, 'text', 'csv')) {
	  $error  = true;
	  break;
	}
	$so_po = new beg_bal_import();
	switch ($_REQUEST['action']) {
	  case 'import_inv': if (!$so_po->processInventory($upload_name)) $error = true; break;
	  case 'import_po':
	  case 'import_ap':
	  case 'import_so':
	  case 'import_ar':  if (!$so_po->processCSV($upload_name))       $error = true; break;
	}
	if ($error) {
	  $messageStack->add(GL_ERROR_NO_POST, 'error');
	} else {
	  $messageStack->add(TEXT_SUCCESS . '-' . constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE') . '-' . TEXT_IMPORT . ': ' . sprintf(SUCCESS_IMPORT_COUNT, $so_po->line_count),'success');
	  gen_add_audit_log(constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE') . '-' . TEXT_IMPORT, $so_po->line_count);
	}
  default:
}

/*****************   prepare to display templates  *************************/
$include_header   = true;
$include_footer   = true;

switch ($_REQUEST['action']) {
  case 'beg_balances':
  case 'import_inv':
  case 'import_po':
  case 'import_ap':
  case 'import_so':
  case 'import_ar':
    $include_template = 'template_beg_bal.php';
    define('PAGE_TITLE', GL_HEADING_BEGINNING_BALANCES);
    break;
  case 'module':
    // find the available tables based on $subject
    $include_template = 'template_modules.php';
    define('PAGE_TITLE', HEADING_MODULE_IMPORT_EXPORT);
	break;
  default:
    $include_template = 'template_main.php';
    define('PAGE_TITLE', IE_HEADING_TITLE);
}
?>