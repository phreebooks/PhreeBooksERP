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
//  Path: /modules/phreebooks/classes/aged_receivables.php
//
namespace phreebooks\classes;
// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure. Targeted towards aged receivables.
class aged_receivables {
  function __construct() {
	// List the special fields as an array to substitute out for the sql, must match from the selection menu generation
	$this->special_field_array = array('balance_due', 'balance_0', 'balance_30', 'balance_60', 'balance_90');
  }

  function load_report_data($report, $Seq, $sql = '', $GrpField = '') {
	global $db;
	// prepare the sql by temporarily replacing calculated fields with real fields
	$sql_fields = substr($sql, strpos($sql,'select ') + 7, strpos($sql, ' from ') - 7);
	$this->sql_field_array = explode(', ', $sql_fields);
	for ($i = 0; $i < count($this->sql_field_array); $i++) {
	  $this->sql_field_karray['c' . $i] = substr($this->sql_field_array[$i], 0, strpos($this->sql_field_array[$i], ' '));
	}
	$sql = $this->replace_special_fields($sql);
	
	$result = $db->Execute($sql);
	if ($result->RecordCount() == 0) return false; // No data so bail now
	// Generate the output data array
	$RowCnt = 0; // Row counter for output data
	$ColCnt = 1;
	$GrpWorking = false;
	while (!$result->EOF) {
	  $myrow = $result->fields;
	  // Check to see if a total row needs to be displayed
	  if (isset($GrpField)) { // we're checking for group totals, see if this group is complete
		if (($myrow[$GrpField] <> $GrpWorking) && $GrpWorking !== false) { // it's a new group so print totals
		  $OutputArray[$RowCnt][0] = 'g:' . ProcessData($GrpWorking, $GrpFieldProcessing);
		  foreach($Seq as $offset => $TotalCtl) {
			$OutputArray[$RowCnt][$offset+1] = ($TotalCtl['total'] == '1') ? ProcessData($TotalCtl['grptotal'], $TotalCtl['processing']) : ' ';
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
	    if ($report->totalonly <> '1') { // insert data into output array and set to next column
		  $OutputArray[$RowCnt][$ColCnt] = ProcessData($myrow[$TableCtl['fieldname']], $TableCtl['processing']);
	    }
	    $ColCnt++;
	    if ($TableCtl['total']) { // add to the running total if need be
		  $Seq[$key]['grptotal'] += $myrow[$TableCtl['fieldname']];
		  $Seq[$key]['rpttotal'] += $myrow[$TableCtl['fieldname']];
	    }
	  }
	  $RowCnt++;
	  $ColCnt = 1;
	  $result->MoveNext();
	}
	if ($GrpWorking !== false) { // if we collected group data show the final group total
		$OutputArray[$RowCnt][0] = 'g:' . ProcessData($GrpWorking, $GrpFieldProcessing);
		foreach ($Seq as $TotalCtl) {
			$OutputArray[$RowCnt][$ColCnt] = ($TotalCtl['total'] == '1') ? ProcessData($TotalCtl['grptotal'], $TotalCtl['processing']) : ' ';
			$ColCnt++;
		}
		$RowCnt++;
		$ColCnt = 1;
	}
	// see if we have a total to send
	$ShowTotals = false;
	foreach ($Seq as $TotalCtl) if ($TotalCtl['total']=='1') $ShowTotals = true; 
	if ($ShowTotals) {
		$OutputArray[$RowCnt][0] = 'r:' . $report->title;
		foreach ($Seq as $TotalCtl) {
			if ($TotalCtl['total']) $OutputArray[$RowCnt][$ColCnt] = ProcessData($TotalCtl['rpttotal'], $TotalCtl['processing']);
				else $OutputArray[$RowCnt][$ColCnt] = ' ';
			$ColCnt++;
		}
	}
// echo 'output array = '; print_r($OutputArray); echo '<br />'; exit();
	return $OutputArray;
  }

  function build_table_drop_down() {
	$output = array();
	return $output;
  }
  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => 'journal_main.id',                  'text' => RW_AR_RECORD_ID);
	$output[] = array('id' => 'journal_main.period',              'text' => TEXT_PERIOD);
	$output[] = array('id' => 'journal_main.journal_id',          'text' => RW_AR_JOURNAL_ID);
	$output[] = array('id' => 'journal_main.post_date',           'text' => TEXT_POST_DATE);
	$output[] = array('id' => 'journal_main.store_id',            'text' => RW_AR_STORE_ID);
	$output[] = array('id' => 'journal_main.description',         'text' => RW_AR_JOURNAL_DESC);
	$output[] = array('id' => 'journal_main.closed',              'text' => RW_AR_CLOSED);
	$output[] = array('id' => 'journal_main.freight',             'text' => RW_AR_FRT_TOTAL);
	$output[] = array('id' => 'journal_main.ship_carrier',        'text' => RW_AR_FRT_CARRIER);
	$output[] = array('id' => 'journal_main.ship_service',        'text' => RW_AR_FRT_SERVICE);
	$output[] = array('id' => 'journal_main.terms',               'text' => RW_AR_TERMS);
	$output[] = array('id' => 'journal_main.sales_tax',           'text' => RW_AR_SALES_TAX);
	$output[] = array('id' => 'journal_main.tax_auths',           'text' => RW_AR_TAX_AUTH);
	$output[] = array('id' => 'journal_main.total_amount',        'text' => RW_AR_INV_TOTAL);
	$output[] = array('id' => 'journal_main.currencies_code',     'text' => RW_AR_CUR_CODE);
	$output[] = array('id' => 'journal_main.currencies_value',    'text' => RW_AR_CUR_EXC_RATE);
	$output[] = array('id' => 'journal_main.so_po_ref_id',        'text' => RW_AR_SO_NUM);
	$output[] = array('id' => 'journal_main.purchase_invoice_id', 'text' => RW_AR_INV_NUM);
	$output[] = array('id' => 'journal_main.purch_order_id',      'text' => RW_AR_PO_NUM);
	$output[] = array('id' => 'journal_main.rep_id',              'text' => RW_AR_SALES_REP);
	$output[] = array('id' => 'journal_main.gl_acct_id',          'text' => RW_AR_AR_ACCT);
	$output[] = array('id' => 'journal_main.bill_acct_id',        'text' => RW_AR_BILL_ACCT_ID);
	$output[] = array('id' => 'journal_main.bill_address_id',     'text' => RW_AR_BILL_ADD_ID);
	$output[] = array('id' => 'journal_main.bill_primary_name',   'text' => RW_AR_BILL_PRIMARY_NAME);
	$output[] = array('id' => 'journal_main.bill_contact',        'text' => RW_AR_BILL_CONTACT);
	$output[] = array('id' => 'journal_main.bill_address1',       'text' => RW_AR_BILL_ADDRESS1);
	$output[] = array('id' => 'journal_main.bill_address2',       'text' => RW_AR_BILL_ADDRESS2);
	$output[] = array('id' => 'journal_main.bill_city_town',      'text' => RW_AR_BILL_CITY);
	$output[] = array('id' => 'journal_main.bill_state_province', 'text' => RW_AR_BILL_STATE);
	$output[] = array('id' => 'journal_main.bill_postal_code',    'text' => RW_AR_BILL_ZIP);
	$output[] = array('id' => 'journal_main.bill_country_code',   'text' => RW_AR_BILL_COUNTRY);
	$output[] = array('id' => 'journal_main.bill_telephone1',     'text' => RW_AR_BILL_TELE1);
//	$output[] = array('id' => 'contacts.bill_telephone2',         'text' => RW_AR_BILL_TELE2);
//	$output[] = array('id' => 'contacts.bill_fax',                'text' => RW_AR_BILL_FAX);
	$output[] = array('id' => 'journal_main.bill_email',          'text' => RW_AR_BILL_EMAIL);
//	$output[] = array('id' => 'contacts.bill_website',            'text' => RW_AR_BILL_WEBSITE);
	$output[] = array('id' => 'journal_main.ship_acct_id',        'text' => RW_AR_SHIP_ACCT_ID);
	$output[] = array('id' => 'journal_main.ship_address_id',     'text' => RW_AR_SHIP_ADD_ID);
	$output[] = array('id' => 'journal_main.ship_primary_name',   'text' => RW_AR_SHIP_PRIMARY_NAME);
	$output[] = array('id' => 'journal_main.ship_contact',        'text' => RW_AR_SHIP_CONTACT);
	$output[] = array('id' => 'journal_main.ship_address1',       'text' => RW_AR_SHIP_ADDRESS1);
	$output[] = array('id' => 'journal_main.ship_address2',       'text' => RW_AR_SHIP_ADDRESS2);
	$output[] = array('id' => 'journal_main.ship_city_town',      'text' => RW_AR_SHIP_CITY);
	$output[] = array('id' => 'journal_main.ship_state_province', 'text' => RW_AR_SHIP_STATE);
	$output[] = array('id' => 'journal_main.ship_postal_code',    'text' => RW_AR_SHIP_ZIP);
	$output[] = array('id' => 'journal_main.ship_country_code',   'text' => RW_AR_SHIP_COUNTRY);
	$output[] = array('id' => 'journal_main.ship_telephone1',     'text' => RW_AR_SHIP_TELE1);
//	$output[] = array('id' => 'contacts.ship_telephone2',         'text' => RW_AR_SHIP_TELE2);
//	$output[] = array('id' => 'contacts.ship_fax',                'text' => RW_AR_SHIP_FAX);
	$output[] = array('id' => 'journal_main.ship_email',          'text' => RW_AR_SHIP_EMAIL);
//	$output[] = array('id' => 'contacts.ship_website',            'text' => RW_AR_SHIP_WEBSITE);
	$output[] = array('id' => 'contacts.short_name',              'text' => RW_AR_CUSTOMER_ID);
	$output[] = array('id' => 'contacts.account_number',          'text' => RW_AR_ACCOUNT_NUMBER);
	$output[] = array('id' => 'journal_main.terminal_date',       'text' => RW_AR_SHIP_DATE);
	$output[] = array('id' => 'balance_due',         			  'text' => RW_AR_BALANCE_DUE);
	$output[] = array('id' => 'balance_0',                        'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_1);
	$output[] = array('id' => 'balance_30',                       'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_2);
	$output[] = array('id' => 'balance_60',                       'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_3);
	$output[] = array('id' => 'balance_90',                       'text' => TEXT_AGE . ' ' . AR_AGING_HEADING_4);
	return $output;
  }

  function replace_special_fields($sql) {
 	$preg_array = array();
  	for ($i = 0; $i < count ($this->special_field_array); $i++ ) {
	  $preg_array[] = '/' . $this->special_field_array[$i] . '/';
	}
	return preg_replace($preg_array, TABLE_JOURNAL_MAIN . '.id', $sql);
  }

  function replace_data_fields($myrow, $report) {
	foreach ($this->sql_field_karray as $key => $value) { // We need to find the id number to calculate the special fields
	  if (in_array($value, $this->special_field_array)) {
	    $id = $myrow[$key];
		break;
	  }
	}
    $new_data = $this->calulate_special_fields($id);
	foreach ($myrow as $key => $value) { 
	  for ($i = 0; $i < count($this->special_field_array); $i++) {
	    if ($this->sql_field_karray[$key] == $this->special_field_array[$i]) 
		  $myrow[$key] = $new_data[$this->special_field_array[$i]];
	  }
	}
	return $myrow;
  }

  function calulate_special_fields($id) {
	global $db;
	$today = date('Y-m-d');
	$new_data = array();
	$result = $db->Execute("select debit_amount, credit_amount from " . TABLE_JOURNAL_ITEM . " where gl_type = 'ttl' and ref_id = " . $id);
	$result2 = $db->Execute("select journal_id, post_date from " . TABLE_JOURNAL_MAIN . " where id = " . $id);
	$post_date = $result2->fields['post_date'];
	$negate = false;
	if (in_array($result2->fields['journal_id'], array(6,7))) {
	  $late_30 = gen_specific_date($today, -AP_AGING_DATE_1);
	  $late_60 = gen_specific_date($today, -AP_AGING_DATE_2);
	  $late_90 = gen_specific_date($today, -AP_AGING_DATE_3);
	  if($result2->fields['journal_id'] != 7) $negate = true;
	} else {
	  $late_30 = gen_specific_date($today, -AR_AGING_PERIOD_1);
	  $late_60 = gen_specific_date($today, -AR_AGING_PERIOD_2);
	  $late_90 = gen_specific_date($today, -AR_AGING_PERIOD_3);
	  if($result2->fields['journal_id'] != 12) $negate = true;
	}
	$result3 = $db->Execute("select sum(debit_amount) as debits, sum(credit_amount) as credits 
	  from " . TABLE_JOURNAL_ITEM . " where so_po_item_ref_id = '" . $id . "' and gl_type in ('pmt', 'chk')");
	if($negate){
		$total_billed = $result->fields['credit_amount'] - $result->fields['debit_amount'];
		$total_paid = $result3->fields['debits'] - $result3->fields['credits'];	
	}else{
		$total_billed = $result->fields['debit_amount'] - $result->fields['credit_amount'];
		$total_paid = $result3->fields['credits'] - $result3->fields['debits'];
	}
	$balance = $total_billed - $total_paid;
	$new_data['balance_due'] = $balance;
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
?>