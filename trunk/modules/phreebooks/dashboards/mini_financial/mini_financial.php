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
//  Path: /modules/phreebooks/dashboards/mini_financial/mini_financial.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreebooks\dashboards\mini_financial;
class mini_financial extends \core\classes\ctl_panel {
	public $id			 		= 'mini_financial';
	public $description	 		= CP_MINI_FINANCIAL_DESCRIPTION;
	public $security_id  		= SECURITY_ID_JOURNAL_ENTRY;
	public $text		 		= CP_MINI_FINANCIAL_TITLE;
	public $version      		= '4.0';
	public $default_params 		= array();

	function output() {
		global $admin;
		if(count($this->params) != count($this->default_params)) { //upgrading
			$this->params = $this->upgrade($this->params);
		}
		$contents = '';
		$control  = '';
		// Build control box form data
		$control  = '<div class="row">';
		$control .= '  <div style="white-space:nowrap">';
		$control .= CP_MINI_FINANCIAL_NO_OPTIONS . '<br />';
		$control .= html_hidden_field('mini_financial_rId', '');
		$control .= '  </div>';
		$control .= '</div>';
		// Build content box
		$contents = '<table width="100%" border = "0">';
		$period = CURRENT_ACCOUNTING_PERIOD;
		// build assets
		$this->bal_tot_2 = 0;
		$this->bal_tot_3 = 0;
		$this->bal_sheet_data = array();
		$the_list = array(0, 2, 4 ,6);
		$negate_array = array(false, false, false, false);
		$contents .= $this->add_bal_sheet_data($the_list, $negate_array, $period);
		$contents .= '<tr><td>&nbsp;&nbsp;' . htmlspecialchars(RW_FIN_CURRENT_ASSETS) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->bal_tot_2) . '</td></tr>' . chr(10);

		$this->bal_tot_2 = 0;
		$the_list = array(8, 10, 12);
		$negate_array = array(false, false, false);
		$this->add_bal_sheet_data($the_list, $negate_array, $period);
		$contents .= '<tr><td>&nbsp;&nbsp;' . htmlspecialchars(RW_FIN_PROP_EQUIP) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->bal_tot_2) . '</td></tr>' . chr(10);
		$contents .= '<tr><td>' . htmlspecialchars(RW_FIN_ASSETS) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->bal_tot_3) . '</td></tr>' . chr(10);
		$contents .= '<tr><td colspan="2">&nbsp;</td></tr>' . chr(10);

		// build liabilities
		$this->bal_tot_2 = 0;
		$this->bal_tot_3 = 0;
		$the_list = array(20, 22);
		$negate_array = array(true, true);
		$this->add_bal_sheet_data($the_list, $negate_array, $period);
		$contents .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;' . htmlspecialchars(RW_FIN_CUR_LIABILITIES) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->bal_tot_2) . '</td></tr>' . chr(10);

		$this->bal_tot_2 = 0;
		$the_list = array(24);
		$negate_array = array(true);
		$this->add_bal_sheet_data($the_list, $negate_array, $period);
		$contents .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;' . htmlspecialchars(RW_FIN_LT_LIABILITIES) . '</td>' . chr(10);
		$contents .= '<td align="right">&nbsp;&nbsp;' . $this->ProcessData($this->bal_tot_2) . '</td></tr>' . chr(10);
		$contents .= '<tr><td>&nbsp;&nbsp;' . htmlspecialchars(RW_FIN_TOTAL_LIABILITIES) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->bal_tot_3) . '</td></tr>' . chr(10);

		// build capital
		$this->bal_tot_2 = 0;
		$the_list = array(40, 42, 44);
		$negate_array = array(true, true, true);
		$this->add_bal_sheet_data($the_list, $negate_array, $period);

		$contents .= $this->load_report_data($period); // retrieve and add net income value
		$this->bal_tot_2 += $this->ytd_net_income;
		$this->bal_tot_3 += $this->ytd_net_income;
		$contents .= '<tr><td>&nbsp;&nbsp;' . htmlspecialchars(RW_FIN_NET_INCOME) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->ytd_net_income) . '</td></tr>' . chr(10);

		$contents .= '<tr><td>&nbsp;&nbsp;' . htmlspecialchars(RW_FIN_CAPITAL) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->bal_tot_2) . '</td></tr>' . chr(10);

		$contents .= '<tr><td>' . htmlspecialchars(RW_FIN_TOTAL_LIABILITIES_CAPITAL) . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->bal_tot_3) . '</td></tr>' . chr(10);
		$contents .= '</table>' . chr(10);
		return $this->build_div($contents, $control);
	}

	function add_bal_sheet_data($the_list, $negate_array, $period) {
		global $admin;
		$contents = '';
		foreach($the_list as $key => $account_type) {
			$total_1 = 0;
			$sql = $admin->DataBase->prepare("SELECT h.beginning_balance + h.debit_amount - h.credit_amount as balance, c.description
			  FROM " . TABLE_CHART_OF_ACCOUNTS . " c INNER JOIN " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h ON c.id = h.account_id
			  WHERE h.period = $period and c.account_type = " . $account_type);
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
				if ($negate_array[$key]) {
			  		$total_1 -= $result['balance'];
				} else {
			  		$total_1 += $result['balance'];
				}
		  	}
		  	$this->bal_tot_2 += $total_1;
			$contents .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;' . constant('RW_FIN_HEAD_' . $account_type) . '</td>' . chr(10);
			$contents .= '<td align="right">' . $this->ProcessData($total_1) . '</td></tr>' . chr(10);
		}
		$this->bal_tot_3 += $this->bal_tot_2;
		return $contents;
	}

	function ProcessData($strData) {
	    global $currencies;
	  	return $currencies->format_full($strData, true, DEFAULT_CURRENCY, 1, 'fpdf');
	}

	function load_report_data($period) {
		global $admin;
		$contents = '';
		// find the period range within the fiscal year from the first period to current requested period
		$sql = $admin->DataBase->prepare("SELECT fiscal_year FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE period = " . $period);
		$sql->execute();
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		$fiscal_year = $result['fiscal_year'];
		$sql = $admin->DataBase->prepare("SELECT period FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE fiscal_year = $fiscal_year ORDER BY period LIMIT 1");
		$sql->execute();
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		$first_period = $result['period'];
		// build revenues
		$cur_year  = $this->add_income_stmt_data(30, $first_period, $period, $negate = true); // Income account_type
		$ytd_temp  = $this->ProcessData($this->total_3);
		$this->ytd_net_income = $this->total_3;
		$contents .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;' . RW_FIN_TOTAL_INCOME . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->total_3) . '</td></tr>' . chr(10);
		// less COGS
		$cur_year  = $this->add_income_stmt_data(32, $first_period, $period, $negate = false); // Cost of Sales account_type
		$ytd_temp  = $this->ProcessData($this->total_3);
		$this->ytd_net_income -= $this->total_3;
		$contents .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;' . RW_FIN_COST_OF_SALES . '</td>' . chr(10);
		$contents .= '<td align="right">(' . $this->ProcessData($this->total_3) . ')</td></tr>' . chr(10);
		$contents .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;' . RW_FIN_GROSS_PROFIT . '</td>' . chr(10);
		$contents .= '<td align="right">' . $this->ProcessData($this->ytd_net_income) . '</td></tr>' . chr(10);
		// less expenses
		$cur_year  = $this->add_income_stmt_data(34, $first_period, $period, $negate = false); // Expenses account_type
		$this->ytd_net_income -= $this->total_3;
		$contents .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;' . RW_FIN_EXPENSES . '</td>' . chr(10);
		$contents .= '<td align="right">(' . $this->ProcessData($this->total_3) . ')</td></tr>' . chr(10);
		$ytd_temp  = $this->ProcessData($this->ytd_net_income);
		return $contents;
	}

	function add_income_stmt_data($type, $first_period, $period, $negate = false) {
		global $admin;
		$account_array = array();
		$ytd_period = $admin->DataBase->prepare("SELECT c.id, c.description, (sum(h.debit_amount) - sum(h.credit_amount)) as balance
		  FROM " . TABLE_CHART_OF_ACCOUNTS . " c INNER JOIN " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h ON c.id = h.account_id
		  WHERE h.period >= $first_period and h.period <= $period and c.account_type = $type GROUP BY h.account_id order by c.id");
		$ytd_period->execute();
		$sql = $admin->DataBase->prepare("SELECT beginning_balance
		  FROM " . TABLE_CHART_OF_ACCOUNTS . " c INNER JOIN " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h ON c.id = h.account_id
		  WHERE h.period = $first_period and c.account_type = $type GROUP BY h.account_id order by c.id");
		$sql->execute();
		$beg_balance = $sql->fetch(\PDO::FETCH_LAZY);
		$ytd_total_1 = 0;
		while ($year_to_period = $ytd_period->fetch(\PDO::FETCH_LAZY)){
			if ($negate) {
				$ytd_total_1 += -$beg_balance['beginning_balance'] - $year_to_period['balance'];
				$ytd_temp     = $this->ProcessData(-$beg_balance['beginning_balance'] - $year_to_period['balance']);
			} else {
				$ytd_total_1 += $beg_balance['beginning_balance'] + $year_to_period['balance'];
				$ytd_temp     = $this->ProcessData($beg_balance['beginning_balance'] + $year_to_period['balance']);
			}
			$account_array[ $year_to_period['id'] ] = array($year_to_period['description'], '', $ytd_temp);
		}
		$this->total_3 = $ytd_total_1;
		return $account_array;
	}
}
?>