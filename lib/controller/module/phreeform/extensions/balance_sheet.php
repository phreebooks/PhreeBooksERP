<?php
/*
 * Bizuno PhreeForm - special class Balance Sheet
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-01-02
 * @filesource /controller/module/phreeform/extensions/balance_sheet.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/functions.php", 'processPhreeBooks', 'function');
bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/extensions/income_statement.php", 'income_statement');

// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure.
class balance_sheet
{
    /**
     * Loads the data from the report and sets the rows, formatted to the balance sheet
     * @param object $report - Report structure
     * @return class variable bal_sheet_data is updated
     */
    function load_report_data($report)
    {
        $period = $report->period;
        $bal   = dbGetValue(BIZUNO_DB_PREFIX."journal_history", "sum(beginning_balance + debit_amount - credit_amount) AS balance", "period=$period", false);
        msgDebug("\nledger total balance = $bal");
        // build assets
        $this->bal_tot_2 = 0;
        $this->bal_tot_3 = 0;
        $this->bal_sheet_data = [];
        $this->bal_sheet_data[] = ['d', lang('current_assets'), '', '', ''];
        $this->add_bal_sheet_data([0, 2, 4 ,6], false, $period);
        $this->bal_sheet_data[] = ['d', lang('current_assets').' - '.lang('total'), '', '', viewFormat($this->bal_tot_2, 'currency')];
        $this->bal_sheet_data[] = ['d', '', '', '', '']; // blank line

        $this->bal_sheet_data[] = ['d', lang('property_equipment'), '', '', ''];
        $this->bal_tot_2 = 0;
        $this->add_bal_sheet_data([8, 10, 12], false, $period);
        $this->bal_sheet_data[] = ['d', lang('property_equipment').' - '.lang('total'), '', '', viewFormat($this->bal_tot_2, 'currency')];
        $this->bal_sheet_data[] = ['d', lang('assets').' - '.lang('total'), '', '', viewFormat($this->bal_tot_3, 'currency')];
        $this->bal_sheet_data[] = ['d', '', '', '', '']; // blank line

        // build liabilities
        $this->bal_sheet_data[] = ['d', lang('current_liabilities'), '', '', ''];
        $this->bal_tot_2 = 0;
        $this->bal_tot_3 = 0;
        $this->add_bal_sheet_data([20, 22], true, $period);
        $this->bal_sheet_data[] = ['d', lang('current_liabilities').' - '.lang('total'), '', '', viewFormat($this->bal_tot_2, 'currency')];
        $this->bal_sheet_data[] = ['d', '', '', '', '']; // blank line

        $this->bal_sheet_data[] = ['d', lang('gl_acct_type_24'), '', '', ''];
        $this->bal_tot_2 = 0;
        $this->add_bal_sheet_data([24], true, $period);
        $this->bal_sheet_data[] = ['d', lang('gl_acct_type_24').' - '.lang('total'), '', '', viewFormat($this->bal_tot_2, 'currency')];
        $this->bal_sheet_data[] = ['d', lang('liabilities').' - '.lang('total'), '', '', viewFormat($this->bal_tot_3, 'currency')];
        $this->bal_sheet_data[] = ['d', '', '', '', '']; // blank line

        // build capital
        $this->bal_sheet_data[] = ['d', lang('capital'), '', '', ''];
        $this->bal_tot_2 = 0;
        $this->add_bal_sheet_data([40, 42, 44], true, $period);
        $net_income = new income_statement($report);
        $net_income->load_report_data($report); // retrieve and add net income value
        $this->bal_tot_2 += $net_income->current_ytd;
        $this->bal_tot_3 += $net_income->current_ytd;
        $this->bal_sheet_data[] = ['d', lang('net_income'), viewFormat($net_income->current_ytd, 'currency'), '', ''];
        $this->bal_sheet_data[] = ['d', lang('capital').' - '.lang('total'), '', '', viewFormat($this->bal_tot_2, 'currency')];
        $this->bal_sheet_data[] = ['d', lang('liabilities_capital').' - '.lang('total'), '', '', viewFormat($this->bal_tot_3, 'currency')];
        return $this->bal_sheet_data;
    }

    /**
     * Adds the data to the balance sheet after pre-processing
     * @param array $gl_types - list of GL accounts to loop through
     * @param boolean $liability - true for is liability, false if not
     * @param integer $period - Current fiscal period
     */
    function add_bal_sheet_data($gl_types, $liability, $period)
    {
        $curZero = viewFormat(0, 'currency');
        foreach ($gl_types as $account_type) {
            $sql = "SELECT gl_account, beginning_balance, debit_amount, credit_amount, beginning_balance + debit_amount - credit_amount AS balance
                FROM ".BIZUNO_DB_PREFIX."journal_history WHERE period=$period AND gl_type=$account_type";
            $stmt   = dbGetResult($sql);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $total_1= 0;
            foreach ($result as $row) {
                $glAcct = getModuleCache('phreebooks', 'chart', 'accounts', $row['gl_account']);
                msgDebug("\ngl account = {$glAcct['title']} and period = $period, beg bal = {$row['beginning_balance']} debit = {$row['debit_amount']}, credit = {$row['credit_amount']}, balance = {$row['balance']}");
                if ($liability) {
                    $total_1 -= $row['balance'];
                    $temp = viewFormat(-$row['balance'], 'currency');
                } else {
                    $total_1 += $row['balance'];
                    $temp = viewFormat($row['balance'], 'currency');
                }
                if (!empty($glAcct['inactive']) && $temp == $curZero) { continue; } // skip inactive accounts
                msgDebug("\nAdding account row");
                $this->bal_sheet_data[] = ['d', getModuleCache('phreebooks', 'chart', 'accounts', $row['gl_account'])['title'], $temp, '', ''];
            }
            $this->bal_tot_2 += $total_1;
            $this->bal_sheet_data[] = ['d', lang('total') . ' ' . lang('gl_acct_type_'.$account_type), '', viewFormat($total_1, 'currency'), ''];
        }
        $this->bal_tot_3 += $this->bal_tot_2;
    }
}
