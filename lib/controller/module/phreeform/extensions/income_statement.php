<?php
/*
 * Bizuno PhreeForm - special class Income Statement
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
 * @version    3.x Last Update: 2019-03-01
 * @filesource /controller/module/phreeform/extensions/income_statement.php
 */

namespace bizuno;

// This file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure.
class income_statement
{
    function __construct($report)
    {
        // find the period range within the fiscal year from the first period to current requested period
        $this->period       = $report->period;
        $this->year         = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", 'fiscal_year', "period=$this->period");
        $this->period_first = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", 'period', "fiscal_year=$this->year ORDER BY period");
        $this->chart        = getModuleCache('phreebooks', 'chart', 'accounts');
        // check for prior year data present
        $ly_period_first    = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", 'period', "fiscal_year=".($this->year-1)." ORDER BY period");
        if (!$ly_period_first) { // no data for prior fiscal year
            $this->ly_year         = 0;
            $this->ly_period_first = 0;
            $this->ly_period       = 0;
        } else {
            $this->ly_year         = $this->year - 1;
            $this->ly_period_first = $ly_period_first;
            $this->ly_period       = $ly_period_first + ($report->period - $this->period_first);
        }
        $this->output = [];
    }

    /**
     * Gets the report data and formats into array for processing
     * @global object $report - Report structure
     * @param object $report - Report structure
     * @return array - formatted data
     */
    function load_report_data($report)
    {
        global $report;
        $this->net = [];
        $this->current_ytd = 0; // for balance sheet
        // Revenues
        $this->add_income_stmt_data(30, lang('gl_acct_type_30'), true); // Income account_type
        $this->net = $this->totals;
        // Less COGS
        $this->addBlank();
        $this->add_income_stmt_data(32, lang('gl_acct_type_32')); // Cost of Sales account_type
        // Gross profit
        $rowData = ['d'];
        $sqlIdx  = 0;
        foreach ($report->fieldlist as $field) {
            if ( empty($field->visible)){ continue; }
            if (!empty($field->total))  {
                $this->net[$sqlIdx] -= $this->totals[$sqlIdx];
                $rowData[] = isset($field->formatting) ? viewFormat($this->net[$sqlIdx], $field->formatting) : $this->net[$sqlIdx];
            } elseif (isset($field->formatting) && $field->formatting=='glTitle') {
                $rowData[] = lang('gross_profit');
            } else {
                $rowData[] = ' ';
            }
            $sqlIdx++;
        }
        $this->addBlank();
        $this->output[] = $rowData;
        // Less expenses
        $this->addBlank();
        $this->add_income_stmt_data(34, lang('gl_acct_type_34')); // Expenses account_type
        // Net income
        $rowData = ['d'];
        $sqlIdx  = 0;
        foreach ($report->fieldlist as $field) {
            if ( empty($field->visible)) { continue; }
            if (!empty($field->total)) {
                $this->net[$sqlIdx] -= $this->totals[$sqlIdx];
                $rowData[] = isset($field->formatting) ? viewFormat($this->net[$sqlIdx], $field->formatting) : $this->net[$sqlIdx];
            } elseif (isset($field->formatting) && $field->formatting=='glTitle') {
                $rowData[] = lang('net_income');
            } else {
                $rowData[] = ' ';
            }
            $sqlIdx++;
        }
        $this->addBlank();
        $this->output[] = $rowData;
        return $this->output;
    }

    /**
     * adds a row of data to the output file
     * @global object $report
     * @param sting $type - GL type/name for groupings
     * @param string $title - test to add to total line
     * @param boolean $negate - for values that are on the other side of the balance sheet
     * @return data added to output file
     */
    function add_income_stmt_data($type, $title, $negate=false)
    {
        global $report;
        $this->totals   = [];
        $this->addBlank($title);
        $data = [];
            $sql = "SELECT gl_account, debit_amount-credit_amount AS balance, budget
            FROM ".BIZUNO_DB_PREFIX."journal_history WHERE period=$this->period AND gl_type=$type ORDER BY gl_account";
        if (!$stmt = dbGetResult($sql)) { return; }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); // current period
        foreach ($result as $row) {
            $data[$row['gl_account']] = [
                'amount' => $negate ? -$row['balance'] : $row['balance'],
                'budget' => $row['budget'],
                ];
        }
        $sql = "SELECT gl_account, (SUM(debit_amount)-SUM(credit_amount)) AS balance, SUM(budget) AS budget
            FROM ".BIZUNO_DB_PREFIX."journal_history WHERE period>=$this->period_first AND period<=$this->period AND gl_type=$type GROUP BY gl_account ORDER BY gl_account";
        if (!$stmt = dbGetResult($sql)) { return; }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $row) { // year to date
            $data[$row['gl_account']]['amount_ytd'] = $negate ? -$row['balance'] : $row['balance'];
            $data[$row['gl_account']]['budget_ytd'] = $row['budget'];
            $this->current_ytd += -$row['balance'];
        }
        $sql  = "SELECT gl_account, debit_amount-credit_amount AS balance, budget
            FROM ".BIZUNO_DB_PREFIX."journal_history WHERE period=$this->ly_period AND gl_type=$type ORDER BY gl_account";
        if (!$stmt = dbGetResult($sql)) { return; }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); // last year current period
        foreach ($result as $row) {
            $data[$row['gl_account']]['ly_amount'] = $negate ? -$row['balance'] : $row['balance'];
            $data[$row['gl_account']]['ly_budget'] = $row['budget'];
        }
        $sql = "SELECT gl_account, (SUM(debit_amount)-SUM(credit_amount)) AS balance, SUM(budget) AS budget
            FROM ".BIZUNO_DB_PREFIX."journal_history WHERE period>=$this->ly_period_first AND period<=$this->ly_period AND gl_type=$type GROUP BY gl_account ORDER BY gl_account";
        if (!$stmt = dbGetResult($sql)) { return; }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $row) { // last year to date
            $data[$row['gl_account']]['ly_amount_ytd'] = $negate ? -$row['balance'] : $row['balance'];
            $data[$row['gl_account']]['ly_budget_ytd'] = $row['budget'];
        }
        if (!empty($report->totalonly)) { foreach ($data as $gl_acct => $row) { // group by parent
            $parent = !empty($this->chart[$gl_acct]['parent']) ? $this->chart[$gl_acct]['parent'] : false;
            if (!empty($parent)) {
                $data[$parent]['amount']       += $row['amount'];
                $data[$parent]['budget']       += $row['budget'];
                $data[$parent]['amount_ytd']   += $row['amount_ytd'];
                $data[$parent]['budget_ytd']   += $row['budget_ytd'];
                $data[$parent]['ly_amount']    += $row['ly_amount'];
                $data[$parent]['ly_budget']    += $row['ly_budget'];
                $data[$parent]['ly_amount_ytd']+= $row['ly_amount_ytd'];
                $data[$parent]['ly_budget_ytd']+= $row['ly_budget_ytd'];
                unset($data[$gl_acct]);
            }
        } }
        foreach ($data as $gl_acct => $row) { // rows
            $report->currentValues = $row; // set the stored processing values to save sql's
            $rowData = ['d'];
            $allZero = true;
            $sqlIdx  = 0;
            foreach ($report->fieldlist as $field) {
                    if (isset($field->visible) && $field->visible) {
                    $value = isset($field->processing) ? viewProcess($gl_acct, $field->processing) : $gl_acct;
                    $rowData[] = isset($field->formatting) ? viewFormat($value, $field->formatting) : $value;
                    if (isset($field->total) && $field->total) {
                        if (!isset($this->totals[$sqlIdx])) { $this->totals[$sqlIdx] = 0; }
                        $this->totals[$sqlIdx] += $value;
                        if (round($value, 3) != 0) { $allZero = false; }
                    }
                    $sqlIdx++;
                }
            }
            if (!$allZero) { $this->output[] = $rowData; }
        }
        // show totals
        $rowData = ['d'];
        $sqlIdx  = 0;
        foreach ($report->fieldlist as $field) {
            if (isset($field->visible) && $field->visible) {
                if (isset($field->total) && $field->total) {
                    $rowData[] = viewFormat($this->totals[$sqlIdx], $field->formatting);
                } elseif (isset($field->formatting) && $field->formatting=='glTitle') {
                    $rowData[] = $title.' - '.lang('total');
                } else {
                    $rowData[] = ' ';
                }
                $sqlIdx++;
            }
        }
        $this->output[] = ['d']; // blank line
        $this->output[] = $rowData;
    }

    /**
     * Adds a blank line of data to the report for readability
     * @global object $report - Report structure
     * @param string $title - if not null, will add the title to the empty row
     */
    private function addBlank($title='')
    {
        global $report;
        $rowData = ['d'];
        foreach ($report->fieldlist as $field) {
            if (isset($field->visible) && $field->visible) { $rowData[] = sizeof($rowData)==1 ? $title : ''; }
        }
        $this->output[] = $rowData;
    }
}
