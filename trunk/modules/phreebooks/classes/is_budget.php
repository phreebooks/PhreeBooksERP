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
//  Path: /modules/phreebooks/classes/is_budget.php
//
namespace phreebooks\classes;
gen_pull_language('phreebooks', 'admin'); // need coa types defines

require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
// this file contains special function calls to generate the data array needed to build reports not possible
// with the current phreeform structure.
class is_budget {
  function __construct() {
	global $currencies;
	$this->zero = $currencies->format(0);
	$this->coa_types = load_coa_types();
	$this->inc_stmt_data = array();
  }

  function load_report_data($report, $Seq) {
	global $db;
	$this->period = $report->period;
	// find the period range within the fiscal year from the first period to current requested period
	$result = $db->Execute("select fiscal_year from " . TABLE_ACCOUNTING_PERIODS . " where period = " . $this->period);
	$fiscal_year = $result->fields['fiscal_year'];
	$result = $db->Execute("select period from " . TABLE_ACCOUNTING_PERIODS . " where fiscal_year = " . $fiscal_year . " order by period limit 1");
	$this->first_period = $result->fields['period'];
	// check for prior year data present
	$result = $db->Execute("select period from " . TABLE_ACCOUNTING_PERIODS . " where fiscal_year = " . ($fiscal_year - 1) . " order by period limit 1");
	if ($result->RecordCount() == 0) { // no data for prior fiscal year
	  $this->ly_first_period = 0;
	  $this->ly_period       = 0;
	} else {
	  $this->ly_first_period = $result->fields['period'];
	  $this->ly_period       = $this->period - $this->first_period + $this->ly_first_period;
	}

	$running_total = array();
	// Revenues
	$this->add_heading_line(RW_FIN_REVENUES); 
	$total     = $this->add_income_stmt_data(30, $negate = true); // Income account_type
	foreach ($total as $key => $value) $grand_total[$key] = $value;
	// Less COGS
	$this->add_heading_line();
	$this->add_heading_line(RW_FIN_COST_OF_SALES); 
	$total  = $this->add_income_stmt_data(32, $negate = false); // Cost of Sales account_type
	foreach ($total as $key => $value) $grand_total[$key] -= $value;
	$line = array(0 => 'd');
	foreach ($report->fieldlist as $value) {
	  $line[] = ProcessData($temp[$value->fieldname], $value->processing);
	}
	$this->inc_stmt_data[] = $line;
	// Gross Profit
	$grand_total['description'] = RW_FIN_GROSS_PROFIT;
	$line = array(0 => 'd');
	foreach ($report->fieldlist as $value) {
	  $line[] = ProcessData($grand_total[$value->fieldname], $value->processing);
	}
	$this->inc_stmt_data[] = $line;
	// Less Expenses
	$this->add_heading_line();
	$this->add_heading_line(RW_FIN_EXPENSES); 
	$total  = $this->add_income_stmt_data(34, $negate = false); // Expenses account_type
	foreach ($total as $key => $value) $grand_total[$key] -= $value;
	// Net Income
	$this->add_heading_line();
	$grand_total['description'] = RW_FIN_NET_INCOME;
	$line = array(0 => 'd');
	foreach ($report->fieldlist as $value) {
	  $line[] = ProcessData($grand_total[$value->fieldname], $value->processing);
	}
	$this->inc_stmt_data[] = $line;
	return $this->inc_stmt_data;
  }

  function add_income_stmt_data($type, $negate = false) {
	global $db, $report;
	$account_array = array();
	// current period
	$sql = "select c.id, c.description, h.debit_amount - h.credit_amount as balance, budget   
		from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
		where h.period = " . $this->period . " and c.account_type = " . $type . " 
		order by c.id";
	$cur_period = $db->Execute($sql);
	$sql = "select (sum(h.debit_amount) - sum(h.credit_amount)) as balance, sum(budget) as budget  
		from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
		where h.period >= " . $this->first_period . " and h.period <= " . $this->period . " and c.account_type = " . $type . " 
		group by h.account_id order by c.id";
	$ytd_period = $db->Execute($sql);
	// last year to date
	$sql = "select c.id, c.description, h.debit_amount - h.credit_amount as balance, budget   
		from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
		where h.period = " . $this->ly_period . " and c.account_type = " . $type . " 
		order by c.id";
	$lcur_period = $db->Execute($sql);
	$sql = "select (sum(h.debit_amount) - sum(h.credit_amount)) as balance, sum(budget) as budget  
		from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
		where h.period >= " . $this->ly_first_period . " and h.period <= " . $this->ly_period . " and c.account_type = " . $type . " 
		group by h.account_id order by c.id";
	$lytd_period = $db->Execute($sql);
/*
	// beginning balances (not needed for income statement since these account types start the year at 0)
	$sql = "select beginning_balance 
		from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
		where h.period = " . $this->first_period . " and c.account_type = " . $type . " 
		group by h.account_id order by c.id";
	$beg_balance = $db->Execute($sql);
*/
	$cur_total_1     = 0;
	$ytd_total_1     = 0;
	$ly_cur_total_1  = 0;
	$ly_ytd_total_1  = 0;
	$bgt_total_1     = 0;
	$bgt_ytd_total_1 = 0;
	$temp  = array();
	$total = array('description' => TEXT_TOTAL . ' ' . $this->coa_types[$type]['text']);
	while (!$cur_period->EOF) {
		$factor = $negate ? -1 : 1;
		$temp[$cur_period->fields['id']]['description'] = $cur_period->fields['description'];
		$temp[$cur_period->fields['id']]['current']     = $factor * $cur_period->fields['balance'];
		$temp[$cur_period->fields['id']]['current_ytd'] = $factor * $ytd_period->fields['balance'];
		$temp[$lcur_period->fields['id']]['ly_current'] = $factor * $lcur_period->fields['balance'];
		$temp[$lcur_period->fields['id']]['ly_ytd']     = $factor * $lytd_period->fields['balance'];
		$temp[$cur_period->fields['id']]['budget_cur']  = $factor * $cur_period->fields['budget'];
		$temp[$cur_period->fields['id']]['budget_ytd']  = $factor * $ytd_period->fields['budget'];
		$total['current']     += $factor * $cur_period->fields['balance'];
		$total['current_ytd'] += $factor * $ytd_period->fields['balance'];
		$total['ly_current']  += $factor * $lcur_period->fields['balance'];
		$total['ly_ytd']      += $factor * $lytd_period->fields['balance'];
		$total['budget_cur']  += $factor * $cur_period->fields['budget'];
		$total['budget_ytd']  += $factor * $ytd_period->fields['budget'];
		$cur_period->MoveNext();
		$ytd_period->MoveNext();
		$lcur_period->MoveNext();
		$lytd_period->MoveNext();
//		$beg_balance->MoveNext();
	}
	foreach ($temp as $acct) {
	  $line = array(0 => 'd');
	  foreach ($report->fieldlist as $value) {
	    $line[] = ProcessData($acct[$value->fieldname], $value->processing);
	  }
	  $this->inc_stmt_data[] = $line;
	}
	$this->add_heading_line();
	$line = array(0 => 'd');
	foreach ($report->fieldlist as $value) {
	  $line[] = ProcessData($total[$value->fieldname], $value->processing);
	}
	$this->inc_stmt_data[] = $line;
	return $total;
  }

  function add_heading_line($title = '') {
	global $report;
	$line = array('d', $title);
	for ($i = 0; $i < sizeof($report->fieldlist) - 1; $i++) $line[] = '';
	$this->inc_stmt_data[] = $line;
  }

  function build_table_drop_down() {
	$output = array();
	return $output;
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => 'description', 'text' => IS_BUDGET_ACCOUNT);
	$output[] = array('id' => 'current',     'text' => IS_BUDGET_CUR_MONTH);
	$output[] = array('id' => 'current_ytd', 'text' => IS_BUDGET_YTD);
	$output[] = array('id' => 'ly_current',  'text' => IS_BUDGET_LY_CUR);
	$output[] = array('id' => 'ly_ytd',      'text' => IS_BUDGET_LAST_YTD);
	$output[] = array('id' => 'budget_cur',  'text' => IS_BUDGET_CUR_BUDGET);
	$output[] = array('id' => 'budget_ytd',  'text' => IS_BUDGET_YTD_BUDGET);
	return $output;
  }

}
?>