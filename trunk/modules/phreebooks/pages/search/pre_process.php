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
//  Path: /modules/phreebooks/pages/search/pre_process.php
//
$security_level = validate_user(SECURITY_ID_SEARCH);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');
/**************   page specific initialization  *************************/
history_filter('pb_search');
$sort_by = array(
  'date' => TEXT_DATE,
  'id'   => TEXT_JOURNAL_TYPE,
  'amt'  => TEXT_AMOUNT,
);
$choices = array(
  'all'  => TEXT_ALL,
  'rng'  => TEXT_RANGE,
  'eq'   => TEXT_EQUAL,
  'neq'  => TEXT_NOT_EQUAL,
  'like' => TEXT_CONTAINS,
);
$journal_choices = array('0' => TEXT_ALL);
for ($i = 1; $i < 30; $i++) {
  $j_constant = str_pad($i, 2, '0', STR_PAD_LEFT);
  if (defined('GEN_ADM_TOOLS_J' . $j_constant)) {
	$journal_choices[$i] = sprintf(TEXT_JID_ENTRY, constant('GEN_ADM_TOOLS_J' . $j_constant));
  }
}
$_SESSION['search_date_id']      = $_REQUEST['search_date_id']     ? $_REQUEST['search_date_id']     : (isset($_SESSION['search_date_id'])     ? $_SESSION['search_date_id']     : 'l'); // default to current period
$_SESSION['search_date_from']    = $_REQUEST['search_date_from']   ? $_REQUEST['search_date_from']   : (isset($_SESSION['search_date_from'])   ? $_SESSION['search_date_from']   : gen_locale_date(CURRENT_ACCOUNTING_PERIOD_START));
$_SESSION['search_date_to']      = $_REQUEST['search_date_to']     ? $_REQUEST['search_date_to']     : (isset($_SESSION['search_date_to'])     ? $_SESSION['search_date_to']     : gen_locale_date(CURRENT_ACCOUNTING_PERIOD_END));
$_SESSION['search_journal_id']   = isset($_REQUEST['search_journal_id'])  ? $_REQUEST['search_journal_id']  : (isset($_SESSION['search_journal_id'])  ? $_SESSION['search_journal_id']  : '0');
$_SESSION['search_ref_id']       = $_REQUEST['search_ref_id']      ? $_REQUEST['search_ref_id']      : (isset($_SESSION['search_ref_id'])      ? $_SESSION['search_ref_id']      : 'all');
$_SESSION['search_ref_from']     = $_REQUEST['search_ref_from']    ? $_REQUEST['search_ref_from']    : (isset($_SESSION['search_ref_from'])    ? $_SESSION['search_ref_from']    : '');
$_SESSION['search_ref_to']       = $_REQUEST['search_ref_to']      ? $_REQUEST['search_ref_to']      : (isset($_SESSION['search_ref_to'])      ? $_SESSION['search_ref_to']      : '');
$_SESSION['search_account_id']   = $_REQUEST['search_account_id']  ? $_REQUEST['search_account_id']  : (isset($_SESSION['search_account_id'])  ? $_SESSION['search_account_id']  :  'all');
$_SESSION['search_account_from'] = $_REQUEST['search_account_from']? $_REQUEST['search_account_from']: (isset($_SESSION['search_account_from'])? $_SESSION['search_account_from']: '');
$_SESSION['search_account_to']   = $_REQUEST['search_account_to']  ? $_REQUEST['search_account_to']  : (isset($_SESSION['search_account_to'])  ? $_SESSION['search_account_to']  : '');
$_SESSION['search_sku_id']       = $_REQUEST['search_sku_id']      ? $_REQUEST['search_sku_id']      : (isset($_SESSION['search_sku_id'])      ? $_SESSION['search_sku_id']      : 'all');
$_SESSION['search_sku_from']     = $_REQUEST['search_sku_from']    ? $_REQUEST['search_sku_from']    : (isset($_SESSION['search_sku_from'])    ? $_SESSION['search_sku_from']    : '');
$_SESSION['search_sku_to']       = $_REQUEST['search_sku_to']      ? $_REQUEST['search_sku_to']      : (isset($_SESSION['search_sku_to'])      ? $_SESSION['search_sku_to']      : '');
$_SESSION['search_amount_id']    = $_REQUEST['search_amount_id']   ? $_REQUEST['search_amount_id']   : (isset($_SESSION['search_amount_id'])   ? $_SESSION['search_amount_id']   : 'all');
$_SESSION['search_amount_from']  = $_REQUEST['search_amount_from'] ? $currencies->clean_value($_REQUEST['search_amount_from']) : (isset($_SESSION['search_amount_from']) ? $_SESSION['search_amount_from']   : '');
$_SESSION['search_amount_to']    = $_REQUEST['search_amount_to']   ? $currencies->clean_value($_REQUEST['search_amount_to'])   : (isset($_SESSION['search_amount_to'])   ? $_SESSION['search_amount_to']   : '');
$_SESSION['search_gl_acct_id']   = $_REQUEST['search_gl_acct_id']  ? $_REQUEST['search_gl_acct_id']  : (isset($_SESSION['search_gl_acct_id'])  ? $_SESSION['search_gl_acct_id']  : 'all');
$_SESSION['search_gl_acct_from'] = $_REQUEST['search_gl_acct_from']? $_REQUEST['search_gl_acct_from']: (isset($_SESSION['search_gl_acct_from'])? $_SESSION['search_gl_acct_from']: '');
$_SESSION['search_gl_acct_to']   = $_REQUEST['search_gl_acct_to']  ? $_REQUEST['search_gl_acct_to']  : (isset($_SESSION['search_gl_acct_to'])  ? $_SESSION['search_gl_acct_to']  : '');
$_SESSION['search_main_id']      = $_REQUEST['search_main_id']     ? $_REQUEST['search_main_id']     : (isset($_SESSION['search_main_id'])     ? $_SESSION['search_main_id']     : 'all');
$_SESSION['search_main_from']    = $_REQUEST['search_main_from']   ? $_REQUEST['search_main_from']   : (isset($_SESSION['search_main_from'])   ? $_SESSION['search_main_from']   : '');
$_SESSION['search_main_to']      = $_REQUEST['search_main_to']     ? $_REQUEST['search_main_to']     : (isset($_SESSION['search_main_to'])     ? $_SESSION['search_main_to']     : '');

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/search/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'reset':
	$_SESSION['search_date_id']      = 'l';
	$_SESSION['search_date_from']    = gen_locale_date(CURRENT_ACCOUNTING_PERIOD_START);
	$_SESSION['search_date_to']      = gen_locale_date(CURRENT_ACCOUNTING_PERIOD_END);
	$_SESSION['search_journal_id']   = '0';
	$_SESSION['search_ref_id']       = 'all';
	$_SESSION['search_ref_from']     = '';
	$_SESSION['search_ref_to']       = '';
	$_SESSION['search_account_id']   = 'all';
	$_SESSION['search_account_from'] = '';
	$_SESSION['search_account_to']   = '';
	$_SESSION['search_sku_id']       = 'all';
	$_SESSION['search_sku_from']     = '';
	$_SESSION['search_sku_to']       = '';
	$_SESSION['search_amount_id']    = 'all';
	$_SESSION['search_amount_from']  = '';
	$_SESSION['search_amount_to']    = '';
	$_SESSION['search_gl_acct_id']   = 'all';
	$_SESSION['search_gl_acct_from'] = '';
	$_SESSION['search_gl_acct_to']   = '';
	$_SESSION['search_main_id']      = 'all';
	$_SESSION['search_main_from']    = '';
	$_SESSION['search_main_to']      = '';
  	$_REQUEST['list']                = 1; 
	unset($_REQUEST['list_order']);
	break;
  case 'search':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
$cal_from = array(
  'name'      => 'dateFrom',
  'form'      => 'site_search',
  'fieldname' => 'search_date_from',
  'imagename' => 'btn_date_1',
  'default'   => $_SESSION['search_date_from'],
  'params'    => array('align' => 'left'),
);
$cal_to = array(
  'name'      => 'dateTo',
  'form'      => 'site_search',
  'fieldname' => 'search_date_to',
  'imagename' => 'btn_date_2',
  'default'   => $_SESSION['search_date_to'],
  'params'    => array('align' => 'left'),
);

// load gl accounts
$gl_array_list = gen_coa_pull_down(SHOW_FULL_GL_NAMES, true, false, true);

// build the list header
$heading_array = array(
  'm.id'                  => TEXT_JOURNAL_RECORD_ID,
  'm.description'         => TEXT_DESCRIPTION,
  'm.bill_primary_name'   => GEN_PRIMARY_NAME,
  'm.post_date'           => TEXT_POST_DATE,
  'm.purchase_invoice_id' => TEXT_REFERENCE,
  'm.total_amount'        => TEXT_TOTAL,
);
$result      = html_heading_bar($heading_array);
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];

// build the list for the page selected
$criteria = array();
if ($_SESSION['search_journal_id']) $criteria[] = 'm.journal_id = ' . $_SESSION['search_journal_id'];

$ref_fields = array('m.purchase_invoice_id', 'm.purch_order_id', 'i.serialize_number');
$result = build_search_sql($ref_fields, $_SESSION['search_ref_id'], $_SESSION['search_ref_from'], $_SESSION['search_ref_to']);
if ($result) $criteria[] = $result;

$account_fields = array('m.bill_primary_name', 'm.ship_primary_name');
$result = build_search_sql($account_fields, $_SESSION['search_account_id'], $_SESSION['search_account_from'], $_SESSION['search_account_to']);
if ($result) $criteria[] = $result;

$sku_fields = array('i.sku', 'i.description');
$result = build_search_sql($sku_fields, $_SESSION['search_sku_id'], $_SESSION['search_sku_from'], $_SESSION['search_sku_to']);
if ($result) $criteria[] = $result;

$amt_fields = array('m.total_amount', 'i.debit_amount', 'i.credit_amount');
$result = build_search_sql($amt_fields, $_SESSION['search_amount_id'], $_SESSION['search_amount_from'], $_SESSION['search_amount_to']);
if ($result) $criteria[] = $result;

$gl_acct_fields = array('m.gl_acct_id', 'i.gl_account');
$result = build_search_sql($gl_acct_fields, $_SESSION['search_gl_acct_id'], $_SESSION['search_gl_acct_from'], $_SESSION['search_gl_acct_to']);
if ($result) $criteria[] = $result;

$main_fields = array('m.id');
$result = build_search_sql($main_fields, $_SESSION['search_main_id'], $_SESSION['search_main_from'], $_SESSION['search_main_to']);
if ($result) $criteria[] = $result;

$date_prefs = array(
	'fieldname' => 'm.post_date',
	'params' => $_SESSION['search_date_id'] . ':' . ($_SESSION['search_date_from']) . ':' . ($_SESSION['search_date_to']));
$temp = gen_build_sql_date($date_prefs['params'], $date_prefs['fieldname']);
if ($temp['sql']) $criteria[] = '(' . $temp['sql'] . ')';

$crit = ($criteria) ? (" where " . implode(' and ', $criteria)) : '';
$query_raw = "select SQL_CALC_FOUND_ROWS distinct m.id, m.journal_id, m.post_date, m.description, m.total_amount, 
	m.purchase_invoice_id, m.bill_primary_name, m.bill_acct_id   
	from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i 
	on m.id = i.ref_id " . $crit . " order by $disp_order";
//echo 'sql = '; print_r($query_raw); echo '<br>';
$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
$query_split  = new splitPageResults($_REQUEST['list'], '');
if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
	$_REQUEST['list'] = $query_split->current_page_number;
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
	$query_split  = new splitPageResults($_REQUEST['list'], '');
}
history_save('pb_search');

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php'; // include display template (required)
define('PAGE_TITLE', HEADING_TITLE_SEARCH_INFORMATION);
?>