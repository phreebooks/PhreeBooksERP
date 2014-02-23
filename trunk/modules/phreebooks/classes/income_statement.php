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
//  Path: /modules/phreebooks/classes/income_statement.php
//
namespace phreebooks\classes;
gen_pull_language('phreebooks', 'admin'); // need coa types defines

// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure.
class income_statement {
	function __construct() {
		global $currencies;
		$this->zero = $currencies->format(0);
		$this->coa_types = load_coa_types();
		$this->inc_stmt_data = array();
	}

	function load_report_data($report) {
		global $db;
		// see if it's a single year or year-year output
		$this->columns = (isset($Seq[3])) ? 4 : 2;
		$period = $report->period;
		// find the period range within the fiscal year from the first period to current requested period
		$result = $db->Execute("select fiscal_year from " . TABLE_ACCOUNTING_PERIODS . " where period = " . $period);
		$fiscal_year = $result->fields['fiscal_year'];
		$result = $db->Execute("select period from " . TABLE_ACCOUNTING_PERIODS . " where fiscal_year = " . $fiscal_year . " order by period limit 1");
		$first_period = $result->fields['period'];
		// check for prior year data present
		$result = $db->Execute("select period from " . TABLE_ACCOUNTING_PERIODS . " where fiscal_year = " . ($fiscal_year - 1) . " order by period limit 1");
		if ($result->RecordCount() == 0) { // no data for prior fiscal year
			$ly_first_period = 0;
			$ly_fiscal_year  = 0;
			$ly_period       = 0;
		} else {
			$ly_first_period = $result->fields['period'];
			$ly_fiscal_year  = $fiscal_year - 1;
			$ly_period       = $period - $first_period + $ly_first_period;
		}

		// build revenues
		$this->add_heading_line(RW_FIN_REVENUES); 
		$cur_year  = $this->add_income_stmt_data(30, $first_period, $period, $negate = true); // Income account_type
		$cur_temp  = ProcessData($this->total_2, $Seq[1]['processing']);
		$ytd_temp  = ProcessData($this->total_3, $Seq[2]['processing']);
		$this->cur_net_income = $this->total_2;
		$this->ytd_net_income = $this->total_3;
		if ($this->columns == 4) {
		  $last_year = $this->add_income_stmt_data(30, $ly_first_period, $ly_period, $negate = true); // last year
		  $last_temp = ProcessData($this->total_2, $Seq[3]['processing']);
		  $lytd_temp = ProcessData($this->total_3, $Seq[4]['processing']);
		  $this->last_net_income = $this->total_2;
		  $this->lytd_net_income = $this->total_3;
		} else {
		  $last_year = array();
		}
		$this->build_income_data($cur_year, $last_year);
		$this->add_heading_line();
		$this->inc_stmt_data[] = array('d', TEXT_TOTAL . ' ' . $this->coa_types[30]['text'], $cur_temp, $ytd_temp, $last_temp, $lytd_temp);

		// less COGS
		$this->add_heading_line();
		$this->add_heading_line(RW_FIN_COST_OF_SALES); 
		$cur_year  = $this->add_income_stmt_data(32, $first_period, $period, $negate = false); // Cost of Sales account_type
		$cur_temp  = ProcessData($this->total_2, $Seq[1]['processing']);
		$ytd_temp  = ProcessData($this->total_3, $Seq[2]['processing']);
		$this->cur_net_income -= $this->total_2;
		$this->ytd_net_income -= $this->total_3;
		if ($this->columns == 4) {
		  $last_year = $this->add_income_stmt_data(32, $ly_first_period, $ly_period, $negate = false); // last year
		  $last_temp = ProcessData($this->total_2, $Seq[3]['processing']);
		  $lytd_temp = ProcessData($this->total_3, $Seq[4]['processing']);
		  $this->last_net_income -= $this->total_2;
		  $this->lytd_net_income -= $this->total_3;
		} else {
		  $last_year = array();
		}
		$this->build_income_data($cur_year, $last_year);
		$this->add_heading_line();
		$this->inc_stmt_data[] = array('d', TEXT_TOTAL . ' ' . $this->coa_types[32]['text'], $cur_temp, $ytd_temp, $last_temp, $lytd_temp);

		$cur_temp  = ProcessData($this->cur_net_income,  $Seq[1]['processing']);
		$ytd_temp  = ProcessData($this->ytd_net_income,  $Seq[2]['processing']);
		$last_temp = ProcessData($this->last_net_income, $Seq[3]['processing']);
		$lytd_temp = ProcessData($this->lytd_net_income, $Seq[4]['processing']);
		$this->add_heading_line();
		$this->inc_stmt_data[] = array('d', RW_FIN_GROSS_PROFIT, $cur_temp, $ytd_temp, $last_temp, $lytd_temp);

		// less expenses
		$this->add_heading_line();
		$this->add_heading_line(RW_FIN_EXPENSES); 
		$cur_year  = $this->add_income_stmt_data(34, $first_period, $period, $negate = false); // Expenses account_type
		$cur_temp  = ProcessData($this->total_2, $Seq[1]['processing']);
		$ytd_temp  = ProcessData($this->total_3, $Seq[2]['processing']);
		$this->cur_net_income -= $this->total_2;
		$this->ytd_net_income -= $this->total_3;
		if ($this->columns == 4) {
		  $last_year = $this->add_income_stmt_data(34, $ly_first_period, $ly_period, $negate = false); // last year
		  $last_temp = ProcessData($this->total_2, $Seq[3]['processing']);
		  $lytd_temp = ProcessData($this->total_3, $Seq[4]['processing']);
		  $this->last_net_income -= $this->total_2;
		  $this->lytd_net_income -= $this->total_3;
		} else {
		  $last_year = array();
		}
		$this->build_income_data($cur_year, $last_year);
		$this->add_heading_line();
		$this->inc_stmt_data[] = array('d', TEXT_TOTAL . ' ' . $this->coa_types[34]['text'], $cur_temp, $ytd_temp, $last_temp, $lytd_temp);

		$cur_temp  = ProcessData($this->cur_net_income,  $Seq[1]['processing']);
		$ytd_temp  = ProcessData($this->ytd_net_income,  $Seq[2]['processing']);
		$last_temp = ProcessData($this->last_net_income, $Seq[3]['processing']);
		$lytd_temp = ProcessData($this->lytd_net_income, $Seq[4]['processing']);
		$this->add_heading_line();
		$this->inc_stmt_data[] = array('d', RW_FIN_NET_INCOME, $cur_temp, $ytd_temp, $last_temp, $lytd_temp);
//echo 'array = '; print_r($this->inc_stmt_data); echo '<br />';
		return $this->inc_stmt_data;
	}

	function add_income_stmt_data($type, $first_period, $period, $negate = false) {
		global $db, $Seq;
		$account_array = array();
		$sql = "select c.id, c.description, h.debit_amount - h.credit_amount as balance, budget   
			from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
			where h.period = " . $period . " and c.account_type = " . $type . " 
			order by c.id";
		$cur_period = $db->Execute($sql);
		$sql = "select (sum(h.debit_amount) - sum(h.credit_amount)) as balance, sum(budget) as budget  
			from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
			where h.period >= " . $first_period . " and h.period <= " . $period . " and c.account_type = " . $type . " 
			group by h.account_id order by c.id";
		$ytd_period = $db->Execute($sql);
		$sql = "select beginning_balance 
			from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
			where h.period = " . $first_period . " and c.account_type = " . $type . " 
			group by h.account_id order by c.id";
		$beg_balance = $db->Execute($sql);
		$cur_total_1 = 0;
		$ytd_total_1 = 0;
		$bgt_total_1 = 0;
		while (!$cur_period->EOF) {
			if ($negate) {
				$cur_total_1 += -$cur_period->fields['balance'];
				$ytd_total_1 += -$beg_balance->fields['beginning_balance'] - $ytd_period->fields['balance'];
				$bgt_total_1 += -$ytd_period->fields['budget'];
				$cur_temp     = ProcessData(-$cur_period->fields['balance'], $Seq[1]['processing']);
				$ytd_temp     = ProcessData(-$beg_balance->fields['beginning_balance'] - $ytd_period->fields['balance'], $Seq[2]['processing']);
				$bgt_temp     = ProcessData(-$ytd_period->fields['budget'], $Seq[2]['processing']);
			} else {
				$cur_total_1 += $cur_period->fields['balance'];
				$ytd_total_1 += $beg_balance->fields['beginning_balance'] + $ytd_period->fields['balance'];
				$bgt_total_1 += $ytd_period->fields['budget'];
				$cur_temp     = ProcessData($cur_period->fields['balance'], $Seq[1]['processing']);
				$ytd_temp     = ProcessData($beg_balance->fields['beginning_balance'] + $ytd_period->fields['balance'], $Seq[2]['processing']);
				$bgt_temp     = ProcessData($ytd_period->fields['budget'], $Seq[2]['processing']);
			}
			$account_array[$cur_period->fields['id']] = array($cur_period->fields['description'], $cur_temp, $ytd_temp);
			$cur_period->MoveNext();
			$ytd_period->MoveNext();
			$beg_balance->MoveNext();
		}
		$this->total_2 = $cur_total_1;
		$this->total_3 = $ytd_total_1;
		return $account_array;
	}

  function add_heading_line($title = '') {
	if ($this->columns == 4) {
	  $this->inc_stmt_data[] = array('d', $title, '', '', '', '');
	} else {
	  $this->inc_stmt_data[] = array('d', $title, '', '');
	}
  }

  function build_income_data($cur_year, $last_year) {
	$temp = array();
	if (sizeof($cur_year) > 0) foreach ($cur_year as $acct => $data) {
		$temp[$acct] = array('desc' => $data[0], 'curr' => $data[1], 'ytd' => $data[2], 'last' => $this->zero, 'lytd' => $this->zero);
	}
	if (sizeof($last_year) > 0) foreach ($last_year as $acct => $data) {
		$temp[$acct]['last'] = $data[1];
		$temp[$acct]['lytd'] = $data[2];
	}
	if (sizeof($temp) > 0) foreach ($temp as $data) {
	  if ($this->columns == 4) {
		$this->inc_stmt_data[] = array('d', $data['desc'], $data['curr'], $data['ytd'], $data['last'], $data['lytd']);		
	  } else {
		$this->inc_stmt_data[] = array('d', $data['desc'], $data['curr'], $data['ytd']);		
	  }
	}
  }

  function build_selection_dropdown() {
	$output = array();
	return $output;
  }

  function build_table_drop_down() {
	$output = array();
	return $output;
  }
 
}
?>