<?php
/*
 * Bizuno PhreeForm - special class Account Reconciliation
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
 * @version    3.x Last Update: 2018-09-05
 * @filesource /controller/module/phreeform/extensions/acct_recon.php
 */

namespace bizuno;

// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure.
class acct_recon {
    // @todo need to do something about this, clutter general language or treat like extensions???
    private $lang = [
        'RW_RECON_CR'      => 'Cash Receipts',
        'RW_RECON_CD'      => 'Cash Disbursements',
        'RW_RECON_ADD_BACK'=> 'Add Back Deposits in Transit',
        'RW_RECON_DIT'     => 'Total Deposits in Transit',
        'RW_RECON_LOP'     => 'Less Outstanding Payments',
        'RW_RECON_TOP'     => 'Total Outstanding Payments',
        'RW_RECON_DIFF'    => 'Unreconciled Difference',
        'RW_RECON_CLEARED' => 'Cleared Transactions',
        'RW_RECON_DCLEARED'=> 'Deposits Cleared',
        'RW_RECON_PCLEARED'=> 'Payments Cleared',
        'RW_RECON_TDC'     => 'Total Deposits Cleared',
        'RW_RECON_TPC'     => 'Total Payments Cleared',
        'RW_RECON_NCLEARED'=> 'Net Cleared'];

    function __construct() { }

    function load_report_data($report) {
        $bank_list      = [];
        $dep_in_transit = 0;
        $chk_in_transit = 0;
        $period         = $report->period;
        $temp           = explode(":", $report->datedefault);
        $fiscal_dates   = $temp[2]; // end_date
        $gl_account     = $report->filterlist[0]->min; // assumes that the gl account is the first criteria
        if (!$gl_account) { return msgAdd("No GL Account has been selected!", 'error'); } // No gl account so bail now

        //Load open Journal Items
        $sql = "SELECT m.id, m.post_date, i.debit_amount, i.credit_amount, m.invoice_num, i.description 
            FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id = i.ref_id 
            WHERE i.gl_account = '$gl_account' AND i.reconciled=0 AND m.post_date<='$fiscal_dates' ORDER BY post_date";
        if (!$stmt = dbGetResult($sql)) { return msgAdd("Error in account recon special class!", 'error'); }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $new_total    = $row['debit_amount'] - $row['credit_amount'];
            if ($new_total < 0) {
              $dep_amount = '';
              $pmt_amount = -$new_total;
              $payment    = 1;
            } else {
              $dep_amount = $new_total;
              $pmt_amount = '';
              $payment    = 0;
            }
            $dep_in_transit += $dep_amount;
            $chk_in_transit += $pmt_amount;
            $bank_list[$row['id']] = [
                'post_date'  => $row['post_date'],
                'reference'  => $row['invoice_num'],
                'description'=> $row['description'],
                'dep_amount' => $dep_amount,
                'pmt_amount' => $pmt_amount,
                ];
        }
        // load the gl account end of period balance
        $result        = dbGetValue(BIZUNO_DB_PREFIX."journal_history", ['beginning_balance', 'debit_amount', 'credit_amount'], "gl_account='$gl_account' AND period=$period");
        $gl_init_bal   = $result['beginning_balance'];
        $cash_receipts = $result['debit_amount'];
        $cash_payments = $result['credit_amount'];
        $end_gl_bal    = $gl_init_bal + $cash_receipts - $cash_payments;
        // Check this next line - end_gl_bal_1 or just end_gl_bal?
        $unrecon_diff  = $end_gl_bal - $dep_in_transit + $chk_in_transit;

        $this->bal_sheet_data = [];
        $this->bal_sheet_data[] = ['d', lang('beginning_balance'), '', '', '', viewFormat($gl_init_bal, 'currency')];
        $this->bal_sheet_data[] = ['d', '', '', '', '', '']; 
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_CR'), '', '', '', viewFormat($cash_receipts, 'currency')]; 
        $this->bal_sheet_data[] = ['d', '', '', '', '', '']; 
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_CD'), '', '', '', viewFormat(-$cash_payments, 'currency')]; 
        $this->bal_sheet_data[] = ['d', '', '', '', '', '']; 
        $this->bal_sheet_data[] = ['d', lang('ending_balance'), '', '', '', viewFormat($end_gl_bal, 'currency')]; 
        $this->bal_sheet_data[] = ['d', '', '', '', '', '']; 
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_ADD_BACK'), '', '', '', '']; 
        foreach ($bank_list as $value) {
          if ($value['dep_amount']) { $this->bal_sheet_data[] = ['d', '', viewFormat($value['post_date'], 'date'), $value['reference'], viewFormat($value['dep_amount'], 'currency'), '']; }
        }
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_DIT'), '', '', '', viewFormat($dep_in_transit, 'currency')]; 
        $this->bal_sheet_data[] = ['d', '', '', '', '', '']; 
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_LOP'), '', '', '', '']; 
        foreach ($bank_list as $value) {
          if ($value['pmt_amount']) {
            $this->bal_sheet_data[] = ['d', '', viewFormat($value['post_date'], 'date'), $value['reference'], viewFormat(-$value['pmt_amount'], 'currency'), '']; 
          }
        }
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_TOP'),  '', '', '', viewFormat(-$chk_in_transit, 'currency')]; 
        $this->bal_sheet_data[] = ['d', '', '', '', '', '']; 
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_DIFF'), '', '', '', viewFormat($unrecon_diff, 'currency')]; 
        $this->bal_sheet_data[] = ['d', lang('ending_balance'),   '', '', '', viewFormat($end_gl_bal, 'currency')]; 
        
        //Load closed Journal Items
        $this->bal_sheet_data[] = ['d', '', '', '', '', ''];
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_CLEARED'), '', '', '', ''];

        $sql = "SELECT m.id, m.post_date, i.debit_amount, i.credit_amount, m.invoice_num, i.description 
        FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id = i.ref_id 
        WHERE i.gl_account = '$gl_account' AND i.reconciled=$period AND m.post_date<='$fiscal_dates' ORDER BY post_date";
        if (!$stmt = dbGetResult($sql)) { return msgAdd("Error in account recon special class part 2!"); }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $new_total   = 0;
        $dep_cleared = 0;
        $chk_cleared = 0;
        $bank_list   = [];
        foreach ($result as $row) {
          $new_total    = $row['debit_amount'] - $row['credit_amount'];
          if ($new_total < 0) {
            $dep_amount = '';
            $pmt_amount = -$new_total;
            $payment    = 1;
          } else {
            $dep_amount = $new_total;
            $pmt_amount = '';
            $payment    = 0;
          }
          $dep_cleared += $dep_amount;
          $chk_cleared += $pmt_amount;
          $bank_list[$row['id']] = [
              'post_date'  => $row['post_date'],
            'reference'  => $row['invoice_num'],
            'description'=> $row['description'],
            'dep_amount' => $dep_amount,
            'pmt_amount' => $pmt_amount,
            ];
        }
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_DCLEARED'), '', '', '', ''];
        if (is_array($bank_list)) foreach ($bank_list as $value) {
          if ($value['dep_amount']) {
            $this->bal_sheet_data[] = ['d', '', viewFormat($value['post_date'], 'date'), $value['reference'], viewFormat($value['dep_amount'], 'currency'), '']; 
          }
        }
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_TDC'), '', '', '', viewFormat( $dep_cleared, 'currency')];
        $this->bal_sheet_data[] = ['d', '', '', '', '', ''];
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_PCLEARED'), '', '', '', ''];
        if (is_array($bank_list)) foreach ($bank_list as $value) {
          if ($value['pmt_amount']) {
            $this->bal_sheet_data[] = ['d', '', viewFormat($value['post_date'], 'date'), $value['reference'], viewFormat(-$value['pmt_amount'], 'currency'), '']; 
          }
        }
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_TPC'), '', '', '', viewFormat( $chk_cleared, 'currency')];
        $this->bal_sheet_data[] = ['d', '', '', '', '', ''];
        $this->bal_sheet_data[] = ['d', lang('RW_RECON_NCLEARED'), '', '', '', viewFormat( $dep_cleared - $chk_cleared, 'currency')];
        
        return $this->bal_sheet_data;
    }
}
