<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/phreebooks/pages/admin_tools/pre_process.php
//
$security_level = validate_user(SECURITY_ID_GEN_ADMIN_TOOLS);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
require(DIR_FS_WORKING . 'functions/phreebooks.php');
require(DIR_FS_WORKING . 'classes/gen_ledger.php');
/**************   page specific initialization  *************************/
define('JOURNAL_ID',2);	// General Journal
if (!defined('CURRENT_ACCOUNTING_PERIOD')) gen_auto_update_period(false);
$error      = false;
$start_date = ($_POST['start_date'])  ? gen_db_date($_POST['start_date']) : CURRENT_ACCOUNTING_PERIOD_START;
$end_date   = ($_POST['end_date'])    ? gen_db_date($_POST['end_date'])   : CURRENT_ACCOUNTING_PERIOD_END;
// see what fiscal year we are looking at (assume this FY is entered for the first time)
if ($_POST['fy']) {
  $fy = $_POST['fy'];
} else {
  $result = $db->Execute("select fiscal_year from " . TABLE_ACCOUNTING_PERIODS . " where period = " . CURRENT_ACCOUNTING_PERIOD);
  $fy = $result->fields['fiscal_year'];
}
// find the highest posted period to disallow accounting period changes
$result     = $db->Execute("select max(period) as period from " . TABLE_JOURNAL_MAIN);
$max_period = ($result->fields['period'] > 0) ? $result->fields['period'] : 0;
// find the highest fiscal year and period in the system
$result     = $db->Execute("select max(fiscal_year) as fiscal_year, max(period) as period from " . TABLE_ACCOUNTING_PERIODS);
$highest_fy = ($result->fields['fiscal_year'] > 0) ? ($result->fields['fiscal_year']) : '';
$highest_period = ($result->fields['period'] > 0) ? ($result->fields['period']) : '';
$period     = CURRENT_ACCOUNTING_PERIOD;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/admin_tools/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'update':
	validate_security($security_level, 3);
  	// propagate into remaining fiscal years if the last date was changed.
	$fy_array = array();
	$x = 0;
	while(isset($_POST['start_' . $x])) {
		$update_period = db_prepare_input($_POST['per_' . $x]);
		$fy_array = array(
			'start_date' => gen_db_date(db_prepare_input($_POST['start_'.$x])),
			'end_date'   => gen_db_date(db_prepare_input($_POST['end_'  .$x])));
		db_perform(TABLE_ACCOUNTING_PERIODS, $fy_array, 'update', 'period = ' . (int)$update_period);
		$x++;
	}
	// see if there is a disconnect between fiscal years
	$next_period = $update_period + 1;
	$next_start_date = date('Y-m-d', strtotime($fy_array['end_date']) + (60 * 60 * 24));
	$result = $db->Execute("select start_date from " . TABLE_ACCOUNTING_PERIODS . " where period = " . $next_period);
	if ($result->RecordCount() > 0) { // next FY exists, check it
		if ($next_start_date <> $result->fields['start_date']) {
			$fy_array = array('start_date' =>$next_start_date);
			db_perform(TABLE_ACCOUNTING_PERIODS, $fy_array, 'update', 'period = ' . (int)$next_period);
			$messageStack->add(GL_ERROR_FISCAL_YEAR_SEQ, 'caution');
			$fy++;
		}
	}
	gen_add_audit_log(GL_LOG_FY_UPDATE . TEXT_UPDATE);
	break;
  case 'new':
	validate_security($security_level, 2);
  	$result = $db->Execute("select * from " . TABLE_ACCOUNTING_PERIODS . " where period = " . $highest_period);
	$next_fy         = $result->fields['fiscal_year'] + 1;
	$next_period     = $result->fields['period'] + 1;
	$next_start_date = date('Y-m-d', strtotime($result->fields['end_date']) + (60 * 60 * 24));
	$highest_period  = validate_fiscal_year($next_fy, $next_period, $next_start_date);
	build_and_check_account_history_records();
	// *************** roll account balances into next fiscal year *************************
    $glEntry = new journal();
	$result = $db->Execute("select id from " . TABLE_CHART_OF_ACCOUNTS);
	while (!$result->EOF) {
		$glEntry->affected_accounts[$result->fields['id']] = 1;
		$result->MoveNext();
	}
	$glEntry->update_chart_history_periods(CURRENT_ACCOUNTING_PERIOD); // from current period through new fiscal year
	$fy = $next_fy;	// set the pointer to open the fiscal year added
	gen_add_audit_log(GL_LOG_FY_UPDATE . TEXT_ADD);
	break;
  case "change":
	// retrieve the desired period and update the system default values.
	validate_security($security_level, 3);
  	$period = (int)db_prepare_input($_POST['period']);
	if ($period <= 0 || $period > $highest_period) {
		$messageStack->add(GL_ERROR_BAD_ACCT_PERIOD, 'error');
		break;
	}
	$result = $db->Execute("select start_date, end_date from " . TABLE_ACCOUNTING_PERIODS . " where period = " . $period);
	$db->Execute("update " . TABLE_CONFIGURATION . " set configuration_value = " . $period . " 
		where configuration_key = 'CURRENT_ACCOUNTING_PERIOD'");
	$db->Execute("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $result->fields['start_date'] . "' 
		where configuration_key = 'CURRENT_ACCOUNTING_PERIOD_START'");
	$db->Execute("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $result->fields['end_date'] . "' 
		where configuration_key = 'CURRENT_ACCOUNTING_PERIOD_END'");
	gen_add_audit_log(GEN_LOG_PERIOD_CHANGE);
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'beg_balances': // Enter beginning balances
	gen_redirect(html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=beg_bal', 'SSL'));
	break;
  case 'purge_db':
	validate_security($security_level, 4);
  	if ($_POST['purge_confirm'] == 'purge') {
		$db->Execute("TRUNCATE TABLE " . TABLE_JOURNAL_MAIN);
		$db->Execute("TRUNCATE TABLE " . TABLE_JOURNAL_ITEM);
		$db->Execute("TRUNCATE TABLE " . TABLE_ACCOUNTS_HISTORY);
		$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY_HISTORY);
		$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY_COGS_OWED);
		$db->Execute("TRUNCATE TABLE " . TABLE_INVENTORY_COGS_USAGE);
		$db->Execute("TRUNCATE TABLE " . TABLE_RECONCILIATION);
		if (defined('MODULE_SHIPPING_STATUS')) $db->Execute("TRUNCATE TABLE " . TABLE_SHIPPING_LOG);
		$db->Execute("update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " set beginning_balance = 0, debit_amount = 0, credit_amount = 0");
		$db->Execute("update " . TABLE_INVENTORY . " set quantity_on_hand = 0, quantity_on_order = 0, quantity_on_sales_order = 0");
		$messageStack->add(GL_UTIL_PURGE_CONFIRM, 'success');
	} else {
		$messageStack->add(GL_UTIL_PURGE_FAIL, 'caution');
	}
	gen_add_audit_log(GL_LOG_PURGE_DB);
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	break;
  case 'repost':
	validate_security($security_level, 4);
  		// determine which journals were selected to re-post
	$valid_journals = array(2,3,4,6,7,8,9,10,12,13,14,16,18,19,20,21,22);
	$journals = array();
	foreach ($valid_journals as $journal_id) if (isset($_POST['jID_' . $journal_id])) $journals[] = $journal_id;
	$repost_cnt = repost_journals($journals, $start_date, $end_date);
	if ($repost_cnt === false) {
	  $messageStack->add(GEN_ADM_TOOLS_RE_POST_FAILED,'caution');
	} else {
	  $messageStack->add(sprintf(GEN_ADM_TOOLS_RE_POST_SUCCESS, $repost_cnt),'success');
	  gen_add_audit_log(GEN_ADM_TOOLS_AUDIT_LOG_RE_POST, implode(',', $journals));
	}
	if (DEBUG) $messageStack->write_debug();
	break;

	case 'inv_owed_fix' :
		validate_security($security_level, 3);
		$error = false;
		$result = $db->Execute("SELECT journal_main_id FROM ".TABLE_INVENTORY_COGS_OWED);
		$cnt = 0;
		$db->transStart();
		while (!$result->EOF) {
		    $gl_entry = new journal($result->fields['journal_main_id']);
		    $gl_entry->remove_cogs_rows(); // they will be regenerated during the re-post
		    if (!$gl_entry->Post('edit', true)) {
			  $db->transRollback();
			  $messageStack->add('<br /><br />Failed Re-posting the inventory owed. The record that failed was # '.$gl_entry->id,'error');
			  $error = true;
			  break;
		    }
			$cnt++;
		    $result->MoveNext();
		}
	    $db->transCommit();
		if ($error) {
			$messageStack->add(GEN_ADM_TOOLS_RE_POST_FAILED,'caution');
		} else {
			$messageStack->add(sprintf(GEN_ADM_TOOLS_RE_POST_SUCCESS, $cnt), 'success');
			gen_add_audit_log(GEN_ADM_TOOLS_AUDIT_LOG_RE_POST, "inventory owed");
		}
		if (DEBUG) $messageStack->write_debug();
		break;

  case 'coa_hist_test':
  case 'coa_hist_fix':
	validate_security($security_level, 4);
  	$tolerance    = 1 / pow(10, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']); // i.e. 1 cent in USD
	// pull fiscal years
	$fiscal_years = array();
	$result = $db->Execute("select distinct fiscal_year, max(period) as last_period
	  from " . TABLE_ACCOUNTING_PERIODS . " group by fiscal_year order by fiscal_year ASC");
	$max_periods = array();
	while (!$result->EOF) {
	  $max_periods[] = $result->fields['last_period'];
	  $max_period    = $result->fields['last_period'];
	  $result->MoveNext();
	}

	// select list of accounts that need to be closed, adjusted
	$sql = "select id, account_type from " . TABLE_CHART_OF_ACCOUNTS . " where account_type in (30, 32, 34, 42, 44)";
	$result = $db->Execute($sql);
	$acct_list = array();
	while(!$result->EOF) {
	  $acct_list[] = $result->fields['id'];
	  if ($result->fields['account_type'] == 44) $retained_earnings_acct = $result->fields['id'];
	  $result->MoveNext();
	}
	$history = array();
	$bad_accounts = array();
	$result = $db->Execute("select period, account_id, beginning_balance, debit_amount, credit_amount 
	    from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " order by account_id, period");
	while(!$result->EOF) {
	  $history[$result->fields['account_id']][$result->fields['period']] = array(
	    'beg_bal' => $result->fields['beginning_balance'],
	    'debit'   => $result->fields['debit_amount'],
	    'credit'  => $result->fields['credit_amount'],
	  );
	  $result->MoveNext();	
	}
	// check beginning balances
	$first_error_period = 9999;
	foreach ($history as $acct => $activity) {
	  foreach ($activity as $period => $data) {
	    if ($period == $max_period || $acct == $retained_earnings_acct) continue; // skip the last period, retained earnings account
		// read and check with journal
		$posted = $db->Execute("select sum(debit_amount) as debit, sum(credit_amount) as credit 
		  from " . TABLE_JOURNAL_MAIN . " m join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
		  where period = " . $period . " and gl_account = '" . $acct . "' 
		  and journal_id in (2, 6, 7, 12, 13, 14, 16, 18, 19, 20, 21)");
		$posted->fields['debit']  = $posted->fields['debit']  ? $posted->fields['debit']  : 0;
		$posted->fields['credit'] = $posted->fields['credit'] ? $posted->fields['credit'] : 0;
		$diff_debit   = $currencies->format($history[$acct][$period]['debit']  - $posted->fields['debit']);
		$diff_credit  = $currencies->format($history[$acct][$period]['credit'] - $posted->fields['credit']);
		$posted_bal   = $currencies->format($history[$acct][$period]['beg_bal'] + $history[$acct][$period]['debit'] - $history[$acct][$period]['credit']);
		$next_beg_bal = $history[$acct][$period]['beg_bal'] + $posted->fields['debit'] - $posted->fields['credit'];
		if (in_array($acct, $acct_list) && in_array($period, $max_periods)) $next_beg_bal = 0;
		if (abs($diff_debit) > $tolerance || abs($diff_credit) > $tolerance) {
		  if ($_REQUEST['action'] == 'coa_hist_test') {
		    $messageStack->add(sprintf(GEN_ADM_TOOLS_REPAIR_ERROR_MSG, $period, 'gl '.$acct, $posted_bal, $currencies->format($next_beg_bal)), 'caution');
		  }
		  $bad_accounts[$acct][$period]['debit_amount']  = $posted->fields['debit'];
		  $bad_accounts[$acct][$period]['credit_amount'] = $posted->fields['credit'];
		  $history[$acct][$period]['debit']     = $posted->fields['debit'];
		  $history[$acct][$period]['credit']    = $posted->fields['credit'];
		  $first_error_period = min($first_error_period, $period);
		}
		if ($currencies->format(abs($next_beg_bal - $history[$acct][$period+1]['beg_bal'])) > $tolerance) {
		  if ($_REQUEST['action'] == 'coa_hist_test' && $period <= CURRENT_ACCOUNTING_PERIOD) {
		    $messageStack->add(sprintf(GEN_ADM_TOOLS_REPAIR_BALANCE_ERROR_MSG, $period, $acct, $currencies->format($history[$acct][$period+1]['beg_bal']), $currencies->format($next_beg_bal)), 'caution');
		  }
		  $bad_accounts[$acct][$period+1]['beginning_balance'] = $next_beg_bal;
		  $history[$acct][$period+1]['beg_bal'] = $next_beg_bal;
		  $first_error_period = min($first_error_period, $period);
		}
/*		$totals[$period]['beg_bal'] += $history[$acct][$period]['beg_bal'];
		$totals[$period]['debit']   += $history[$acct][$period]['debit'];
		$totals[$period]['credit']  += $history[$acct][$period]['credit'];
		// read and check history db values*/
	  }
	}
	if ($_REQUEST['action'] == 'coa_hist_fix' && sizeof($bad_accounts) > 0) {
		// *************** START TRANSACTION *************************
		$db->transStart();
	    $glEntry = new journal();
		foreach ($bad_accounts as $gl_acct => $acct_array) {
		  $glEntry->affected_accounts[$gl_acct] = 1;
		  foreach ($acct_array as $period => $sql_data_array) {
			db_perform(TABLE_CHART_OF_ACCOUNTS_HISTORY, $sql_data_array, 'update', "account_id='".$gl_acct."' and period=".$period);
		  }
		}
		$min_period = max($first_error_period, 2); // avoid a crash if min_period is the first period
		if ($glEntry->update_chart_history_periods($min_period - 1)) { // from prior period than the error account
			$db->transCommit();
			$messageStack->add(GEN_ADM_TOOLS_REPAIR_COMPLETE,'success');
			gen_add_audit_log(GEN_ADM_TOOLS_REPAIR_LOG_ENTRY);
		}
	}
	if (sizeof($bad_accounts) == 0) {
	  $messageStack->add(GEN_ADM_TOOLS_REPAIR_SUCCESS,'success');
	} else {
	  $messageStack->add(GEN_ADM_TOOLS_REPAIR_ERROR,'error');
	}
	if (DEBUG) $messageStack->write_debug();
    break;

  default:
}

/*****************   prepare to display templates  *************************/
$fy_array = array();
$cal_end   = array();
$i = 0;
$result = $db->Execute("select period, start_date, end_date from ".TABLE_ACCOUNTING_PERIODS." where fiscal_year = $fy");
while(!$result->EOF) {
  $fy_array[$result->fields['period']] = array('start' => $result->fields['start_date'], 'end' => $result->fields['end_date']);
  $cal_start[$i] = array(
    'name'      => 'startDate',
    'form'      => 'admin_tools',
    'fieldname' => 'start_'.$i,
    'imagename' => 'btn_date_2',
    'default'   => gen_locale_date($result->fields['start_date']),
    'params'    => array('align' => 'left'),
  );
  $cal_end[$i] = array(
    'name'      => 'endDate',
    'form'      => 'admin_tools',
    'fieldname' => 'end_'.$i,
    'imagename' => 'btn_date_2',
    'default'   => gen_locale_date($result->fields['end_date']),
    'params'    => array('align' => 'left'),
  );
  $i++;
  $result->MoveNext();
}
// set up calendars for re-post
$cal_repost_start = array(
    'name'      => 'repostStartDate',
    'form'      => 'admin_tools',
    'fieldname' => 'start_date',
    'imagename' => 'btn_date_2',
    'default'   => gen_locale_date($start_date),
    'params'    => array('align' => 'left'),
);
$cal_repost_end = array(
    'name'      => 'repostEndDate',
    'form'      => 'admin_tools',
    'fieldname' => 'end_date',
    'imagename' => 'btn_date_2',
    'default'   => gen_locale_date($end_date),
    'params'    => array('align' => 'left'),
);

$result = $db->Execute("SELECT journal_main_id FROM ".TABLE_INVENTORY_COGS_OWED);
$cogs_owed = $result->RecordCount();

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_HEADING_ADMIN_TOOLS);

?>