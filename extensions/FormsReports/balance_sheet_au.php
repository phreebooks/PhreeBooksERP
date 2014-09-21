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
//  Path: /modules/phreebooks/classes/balance_sheet_au.php
//

require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/income_statement_au.php');

// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure.
class balance_sheet_au {
	function __construct() {
		$this->coa_types = load_coa_types();
	}

	function load_report_data($report) {
		$period = $report->period;
		// build assets
		$this->bal_tot_2 = 0;
		$this->bal_tot_3 = 0;
		$this->bal_sheet_data = array();
		$this->bal_sheet_data[] = array('d', RW_FIN_CURRENT_ASSETS, '');
		$the_list = array(0, 2, 4 ,6);
		$this->add_bal_sheet_data($the_list, false, $period);
		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_CURRENT_ASSETS, ProcessData($this->bal_tot_2, $report->fieldlist[1]->processing));
//		$this->bal_sheet_data[] = array('d', '', ''); // blank line

		$this->bal_sheet_data[] = array('d', RW_FIN_PROP_EQUIP, '');
		$this->bal_tot_2 = 0;
		$the_list = array(8, 10, 12);
		$this->add_bal_sheet_data($the_list, false, $period);
		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_PROP_EQUIP, ProcessData($this->bal_tot_2, $report->fieldlist[1]->processing));
		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_ASSETS, ProcessData($this->bal_tot_3, $report->fieldlist[1]->processing));
		$this->bal_sheet_data[] = array('d', '', ''); // blank line
		$total_assets = $this->bal_tot_3;

		// build liabilities
		$this->bal_sheet_data[] = array('d', RW_FIN_CUR_LIABILITIES, '');
		$this->bal_tot_2 = 0;
		$this->bal_tot_3 = 0;
		$the_list = array(20, 22);
		$this->add_bal_sheet_data($the_list, true, $period);
		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_CUR_LIABILITIES, ProcessData($this->bal_tot_2, $report->fieldlist[1]->processing));
//		$this->bal_sheet_data[] = array('d', '', ''); // blank line

		$this->bal_sheet_data[] = array('d', RW_FIN_LONG_TERM_LIABILITIES, '');
		$this->bal_tot_2 = 0;
		$the_list = array(24);
		$this->add_bal_sheet_data($the_list, true, $period);
		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_LT_LIABILITIES, ProcessData($this->bal_tot_2, $report->fieldlist[1]->processing));
		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_LIABILITIES, ProcessData($this->bal_tot_3, $report->fieldlist[1]->processing));
		$this->bal_sheet_data[] = array('d', '', ''); // blank line

		// Net Assets (Added for AU)
		$this->bal_sheet_data[] = array('d', 'Net Assets', ProcessData($total_assets-$this->bal_tot_3, $report->fieldlist[1]->processing));
		$this->bal_sheet_data[] = array('d', '', ''); // blank line
		$this->bal_tot_3 = 0;

		// build equity
		$this->bal_sheet_data[] = array('d', RW_FIN_CAPITAL, '');
		$this->bal_tot_2 = 0;
		$the_list = array(40, 42, 44);
		$this->add_bal_sheet_data($the_list, true, $period);
		$net_income = new income_statement_au();
		$net_income->load_report_data($report, $period); // retrieve and add net income value
		$this->bal_tot_2 += $net_income->ytd_net_income;
//		$this->bal_tot_3 += $net_income->ytd_net_income;
		$this->bal_sheet_data[] = array('d', RW_FIN_NET_INCOME, ProcessData($net_income->ytd_net_income, $report->fieldlist[1]->processing));
		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_CAPITAL, ProcessData($this->bal_tot_2, $report->fieldlist[1]->processing));
//		$this->bal_sheet_data[] = array('d', RW_FIN_TOTAL_LIABILITIES_CAPITAL, ProcessData($this->bal_tot_3, $report->fieldlist[1]->processing));
		return $this->bal_sheet_data;
	}

	function add_bal_sheet_data($the_list, $negate, $period) {
		global $admin, $Seq;
		foreach($the_list as $key => $account_type) {
			$sql = "select h.beginning_balance + h.debit_amount - h.credit_amount as balance, c.description
				from " . TABLE_CHART_OF_ACCOUNTS . " c inner join " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " h on c.id = h.account_id
				where h.period = " . $period . " and c.account_type = " . $account_type;
			$result = $admin->DataBase->query($sql);
			$total_1 = 0;
			while (!$result->EOF) {
				if ($negate) {
					$total_1 -= $result->fields['balance'];
					$temp = ProcessData(-$result->fields['balance'], $Seq[1]['processing']);
				} else {
					$total_1 += $result->fields['balance'];
					$temp = ProcessData($result->fields['balance'], $Seq[1]['processing']);
				}
//				$this->bal_sheet_data[] = array('d', $result->fields['description'], $temp, '', '');
				$result->MoveNext();
			}
			$this->bal_tot_2 += $total_1;
			$total_1 = ProcessData($total_1, $Seq[1]['processing']);
			$this->bal_sheet_data[] = array('d', $this->coa_types[$account_type]['text'], $total_1);
//			$this->bal_sheet_data[] = array('d', TEXT_TOTAL . ' ' . $this->coa_types[$account_type]['text'], $total_1);
		}
		$this->bal_tot_3 += $this->bal_tot_2;
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