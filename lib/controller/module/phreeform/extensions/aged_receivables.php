<?php
/*
 * Bizuno PhreeForm - special class Aged Receivables
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
 * @filesource /controller/module/phreeform/extensions/aged_receivables.php
 */

namespace bizuno;

// This file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure. Targeted towards aged receivables.
class aged_receivables
{
    function __construct()
    {
        // List the special fields as an array to substitute out for the sql, must match from the selection menu generation
        $this->special_field_array = ['balance_0', 'balance_30', 'balance_60', 'balance_90'];
    }

    /**
     * Retrieves report data and places it into an array.
     * @global type $GrpFieldProcessing
     * @param type $report
     * @param type $Seq
     * @param type $sql
     * @param type $GrpField
     * @return boolean|string
     */
    public function load_report_data($report, $Seq, $sql = '', $GrpField = '')
    {
      global $GrpFieldProcessing;
      // prepare the sql by temporarily replacing calculated fields with real fields
      $sql_fields = substr($sql, strpos($sql,'select ') + 7, strpos($sql, ' FROM ') - 7);
      $this->sql_field_array = explode(', ', $sql_fields);
      for ($i = 0; $i < count($this->sql_field_array); $i++) {
        $this->sql_field_karray['c' . $i] = substr($this->sql_field_array[$i], 0, strpos($this->sql_field_array[$i], ' '));
      }
      $sql = $this->replace_special_fields($sql);

      $stmt = dbGetResult($sql);
      $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      if (sizeof($result) == 0) return false; // No data so bail now
      // Generate the output data array
      $RowCnt = 0; // Row counter for output data
      $ColCnt = 1;
      $GrpWorking = false;
      foreach ($result as $row) {
        $myrow = $row;
        // Check to see if a total row needs to be displayed
        if (isset($GrpField)) { // we're checking for group totals, see if this group is complete
          if (!isset($myrow[$GrpField])) $myrow[$GrpField] = '';
          if (($myrow[$GrpField] <> $GrpWorking) && $GrpWorking !== false) { // it's a new group so print totals
            $OutputArray[$RowCnt][0] = 'g:' . viewProcess($GrpWorking, $GrpFieldProcessing);
            foreach($Seq as $offset => $TotalCtl) {
              $OutputArray[$RowCnt][$offset+1] = ($TotalCtl['total'] == '1') ? viewProcess($TotalCtl['grptotal'], $TotalCtl['processing']) : ' ';
              $Seq[$offset]['grptotal'] = ''; // reset the total
            }
            $RowCnt++; // go to next row
          }
          $GrpWorking = $myrow[$GrpField]; // set to new grouping value
        }
        $OutputArray[$RowCnt][0] = 'd'; // let the display class know its a data element
  //echo 'orig myrow = '; print_r($myrow); echo '<br /><br />';
        $myrow = $this->replace_data_fields($myrow, $report);
  //echo 'new myrow = '; print_r($myrow); echo '<br /><br />';
        foreach($Seq as $key => $TableCtl) { // 
          if (!isset($report->totalonly) || $report->totalonly != '1') { // insert data into output array and set to next column
            $OutputArray[$RowCnt][$ColCnt] = viewProcess($myrow[$TableCtl['fieldname']], $TableCtl['processing']);
            $OutputArray[$RowCnt][$ColCnt] = viewFormat($OutputArray[$RowCnt][$ColCnt], $TableCtl['formatting']);
          }
          $ColCnt++;
          if ($TableCtl['total']) { // add to the running total if need be
            $Seq[$key]['grptotal'] += $myrow[$TableCtl['fieldname']];
            $Seq[$key]['rpttotal'] += $myrow[$TableCtl['fieldname']];
          }
        }
        $RowCnt++;
        $ColCnt = 1;
      }
      if ($GrpWorking !== false) { // if we collected group data show the final group total
          $OutputArray[$RowCnt][0] = 'g:' . viewProcess($GrpWorking, $GrpFieldProcessing);
          foreach ($Seq as $TotalCtl) {
              $OutputArray[$RowCnt][$ColCnt] = ($TotalCtl['total'] == '1') ? viewFormat($TotalCtl['grptotal'], $TotalCtl['formatting']) : ' ';
              $ColCnt++;
          }
          $RowCnt++;
          $ColCnt = 1;
      }
      // see if we have a total to send
      $ShowTotals = false;
      foreach ($Seq as $TotalCtl) { if ($TotalCtl['total']=='1') { $ShowTotals = true; } }
      if ($ShowTotals) {
          $OutputArray[$RowCnt][0] = 'r:' . $report->title;
          foreach ($Seq as $TotalCtl) {
              if ($TotalCtl['total']) { $OutputArray[$RowCnt][$ColCnt] = viewFormat($TotalCtl['rpttotal'], $TotalCtl['formatting']); }
                  else { $OutputArray[$RowCnt][$ColCnt] = ' '; }
              $ColCnt++;
          }
      }
  // echo 'output array = '; print_r($OutputArray); echo '<br />'; exit();
      return $OutputArray;
    }

    function build_table_drop_down()
    {
        $output = [];
        return $output;
    }

    function build_selection_dropdown() 
    {
      // build user choices for this class with the current and newly established fields
      $output = [];
      $output[] = ['id' => 'journal_main.id',                  'text' => RW_AR_RECORD_ID];
      $output[] = ['id' => 'journal_main.period',              'text' => lang('period')];
      $output[] = ['id' => 'journal_main.journal_id',          'text' => RW_AR_JOURNAL_ID];
      $output[] = ['id' => 'journal_main.post_date',           'text' => TEXT_POST_DATE];
      $output[] = ['id' => 'journal_main.store_id',            'text' => RW_AR_STORE_ID];
      $output[] = ['id' => 'journal_main.description',         'text' => RW_AR_JOURNAL_DESC];
      $output[] = ['id' => 'journal_main.closed',              'text' => RW_AR_CLOSED];
      $output[] = ['id' => 'journal_main.freight',             'text' => RW_AR_FRT_TOTAL];
      $output[] = ['id' => 'journal_main.ship_carrier',        'text' => RW_AR_FRT_CARRIER];
      $output[] = ['id' => 'journal_main.ship_service',        'text' => RW_AR_FRT_SERVICE];
      $output[] = ['id' => 'journal_main.terms',               'text' => RW_AR_TERMS];
      $output[] = ['id' => 'journal_main.sales_tax',           'text' => RW_AR_SALES_TAX];
      $output[] = ['id' => 'journal_main.total_amount',        'text' => RW_AR_INV_TOTAL];
      $output[] = ['id' => 'journal_main.balance_due',         'text' => RW_AR_BALANCE_DUE];
      $output[] = ['id' => 'journal_main.currency',            'text' => RW_AR_CUR_CODE];
      $output[] = ['id' => 'journal_main.currency_rate',       'text' => RW_AR_CUR_EXC_RATE];
      $output[] = ['id' => 'journal_main.so_po_ref_id',        'text' => RW_AR_SO_NUM];
      $output[] = ['id' => 'journal_main.invoice_num',         'text' => RW_AR_INV_NUM];
      $output[] = ['id' => 'journal_main.purch_order_id',      'text' => RW_AR_PO_NUM];
      $output[] = ['id' => 'journal_main.rep_id',              'text' => RW_AR_SALES_REP];
      $output[] = ['id' => 'journal_main.gl_acct_id',          'text' => RW_AR_AR_ACCT];
      $output[] = ['id' => 'journal_main.contact_id_b',        'text' => RW_AR_BILL_ACCT_ID];
      $output[] = ['id' => 'journal_main.address_id_b',     'text' => RW_AR_BILL_ADD_ID];
      $output[] = ['id' => 'journal_main.primary_name_b',   'text' => RW_AR_BILL_PRIMARY_NAME];
      $output[] = ['id' => 'journal_main.contact_b',        'text' => RW_AR_BILL_CONTACT];
      $output[] = ['id' => 'journal_main.address1_b',       'text' => RW_AR_BILL_ADDRESS1];
      $output[] = ['id' => 'journal_main.address2_b',       'text' => RW_AR_BILL_ADDRESS2];
      $output[] = ['id' => 'journal_main.city_b_town',      'text' => RW_AR_BILL_CITY];
      $output[] = ['id' => 'journal_main.state_b_province', 'text' => RW_AR_BILL_STATE];
      $output[] = ['id' => 'journal_main.postal_code_b',    'text' => RW_AR_BILL_ZIP];
      $output[] = ['id' => 'journal_main.country_b_code',   'text' => RW_AR_BILL_COUNTRY];
      $output[] = ['id' => 'journal_main.telephone1_b',     'text' => RW_AR_BILL_TELE1];
  //    $output[] = ['id' => 'contacts.bill_telephone2',         'text' => RW_AR_BILL_TELE2];
  //    $output[] = ['id' => 'contacts.bill_fax',                'text' => RW_AR_BILL_FAX];
      $output[] = ['id' => 'journal_main.email_b',          'text' => RW_AR_BILL_EMAIL];
  //    $output[] = ['id' => 'contacts.bill_website',            'text' => RW_AR_BILL_WEBSITE];
      $output[] = ['id' => 'journal_main.contact_id_s',         'text' => RW_AR_SHIP_ACCT_ID];
      $output[] = ['id' => 'journal_main.address_id_s',     'text' => RW_AR_SHIP_ADD_ID];
      $output[] = ['id' => 'journal_main.primary_name_s',   'text' => RW_AR_SHIP_PRIMARY_NAME];
      $output[] = ['id' => 'journal_main.contact_s',        'text' => RW_AR_SHIP_CONTACT];
      $output[] = ['id' => 'journal_main.address1_s',       'text' => RW_AR_SHIP_ADDRESS1];
      $output[] = ['id' => 'journal_main.address2_s',       'text' => RW_AR_SHIP_ADDRESS2];
      $output[] = ['id' => 'journal_main.city_s_town',      'text' => RW_AR_SHIP_CITY];
      $output[] = ['id' => 'journal_main.state_s_province', 'text' => RW_AR_SHIP_STATE];
      $output[] = ['id' => 'journal_main.postal_code_s',    'text' => RW_AR_SHIP_ZIP];
      $output[] = ['id' => 'journal_main.country_s_code',   'text' => RW_AR_SHIP_COUNTRY];
      $output[] = ['id' => 'journal_main.telephone1_s',     'text' => RW_AR_SHIP_TELE1];
  //    $output[] = ['id' => 'contacts.ship_telephone2',         'text' => RW_AR_SHIP_TELE2];
  //    $output[] = ['id' => 'contacts.ship_fax',                'text' => RW_AR_SHIP_FAX];
      $output[] = ['id' => 'journal_main.email_s',          'text' => RW_AR_SHIP_EMAIL];
  //    $output[] = ['id' => 'contacts.ship_website',            'text' => RW_AR_SHIP_WEBSITE];
      $output[] = ['id' => 'contacts.short_name',              'text' => RW_AR_CUSTOMER_ID];
      $output[] = ['id' => 'contacts.account_number',          'text' => RW_AR_ACCOUNT_NUMBER];
      $output[] = ['id' => 'journal_main.terminal_date',       'text' => RW_AR_SHIP_DATE];
      $output[] = ['id' => 'balance_0',                        'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_1];
      $output[] = ['id' => 'balance_30',                       'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_2];
      $output[] = ['id' => 'balance_60',                       'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_3];
      $output[] = ['id' => 'balance_90',                       'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_4];
      return $output;
    }

    function replace_special_fields($sql) {
        $preg_array = [];
        for ($i = 0; $i < count ($this->special_field_array); $i++ ) {
            $preg_array[] = '/' . $this->special_field_array[$i] . '/';
        }
        return preg_replace($preg_array, BIZUNO_DB_PREFIX."journal_main.id", $sql);
    }

    function replace_data_fields($myrow, $report)
    {
        foreach ($this->sql_field_karray as $key => $value) { // We need to find the id number to calculate the special fields
            if (in_array($value, $this->special_field_array)) {
                $id = $myrow[$key];
                break;
            }
        }
        $new_data = $this->calulate_special_fields($id);
        foreach ($myrow as $key => $value) { 
            for ($i = 0; $i < count($this->special_field_array); $i++) {
                if (!isset($this->sql_field_karray[$key])) { continue; }
                if ($this->sql_field_karray[$key] == $this->special_field_array[$i]) { 
                    $myrow[$key] = $new_data[$this->special_field_array[$i]];
                }
            }
        }
        return $myrow;
    }

    function calulate_special_fields($id)
    {
        $today   = date('Y-m-d');
        $new_data= [];
        $result  = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['debit_amount', 'credit_amount'], "ref_id=$id AND gl_type='ttl'");
        $result2 = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['journal_id', 'post_date'], "id=$id");
        $total_billed = $result['debit_amount'] - $result['credit_amount'];
        $post_date = $result2['post_date'];
        $late_30 = localeCalculateDate($today, -30);
        $late_60 = localeCalculateDate($today, -60);
        $late_90 = localeCalculateDate($today, -90);
        $negate = in_array($result2['journal_id'], [6,7]) ? true : false;
        $result = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['SUM(debit_amount) AS debits', 'SUM(credit_amount) AS credits'], "item_ref_id=$id AND gl_type='pmt'", false);
        $total_paid = $result['credits'] - $result['debits'];
        $balance = $total_billed - $total_paid;
        if($negate) { $balance = -$balance;     }
        $new_data['balance_0']  = 0;
        $new_data['balance_30'] = 0;
        $new_data['balance_60'] = 0;
        $new_data['balance_90'] = 0;
        if       ($post_date < $late_90) {
            $new_data['balance_90'] = $balance;
        } elseif ($post_date < $late_60) {
            $new_data['balance_60'] = $balance;
        } elseif ($post_date < $late_30) {
            $new_data['balance_30'] = $balance;
        } else {
            $new_data['balance_0']  = $balance;
        }
        return $new_data;
    }
}
