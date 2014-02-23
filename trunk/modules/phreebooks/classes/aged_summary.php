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
//  Path: /modules/phreeform/custom/classes/aged_summary.php @todo
//
namespace phreebooks\classes;
// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure. Targeted towards aged receivables.
class aged_summary {
  function __construct() {
	$this->accounts = array();
	// List the special fields as an array to substitute out for the sql, must match from the selection menu generation
	$this->special_field_array = array('balance_0', 'balance_30', 'balance_60', 'balance_90');
  }

  function load_report_data($report, $Seq, $sql = '', $GrpField = '') {
	global $db;
	// find list of accounts within search filter 
	$today = date('Y-m-d');
	$late_30 = gen_specific_date($today, -AR_AGING_PERIOD_1);
	$late_60 = gen_specific_date($today, -AR_AGING_PERIOD_2);
	$late_90 = gen_specific_date($today, -AR_AGING_PERIOD_3);
	$sql_fields = substr($sql, strpos($sql,'select ') + 7, strpos($sql, ' from ') - 7);
	// prepare the sql by temporarily replacing calculated fields with real fields
	$this->sql_field_array = explode(', ', $sql_fields);
	for ($i = 0; $i < count($this->sql_field_array); $i++) {
	  $this->sql_field_karray['c' . $i] = substr($this->sql_field_array[$i], 0, strpos($this->sql_field_array[$i], ' '));
	}
	$temp_sql = str_replace(' FROM ', ', ' . TABLE_JOURNAL_MAIN . '.id, journal_id, post_date, total_amount, bill_acct_id FROM ', $sql);
	$temp_sql = $this->replace_special_fields($temp_sql);
	$result = $db->Execute($temp_sql);
	if ($result->RecordCount() == 0) return false; // No data so bail now
	while (!$result->EOF) {
	  for ($i = 0; $i < sizeof($this->sql_field_karray); $i++) {
	    $this->accounts[$result->fields['bill_acct_id']]['c' . $i] = $result->fields['c' . $i];
	  }
	  $negate = (in_array($result->fields['journal_id'], array(7, 13))) ? true : false;
	  $balance  = $negate ? -$result->fields['total_amount'] : $result->fields['total_amount'];
	  $balance -= $this->fetch_paid_amounts($result->fields['id']);
	  if       ($result->fields['post_date'] < $late_90) {
		$this->accounts[$result->fields['bill_acct_id']]['balance_90'] += $balance;
	  } elseif ($result->fields['post_date'] < $late_60) {
		$this->accounts[$result->fields['bill_acct_id']]['balance_60'] += $balance;
	  } elseif ($result->fields['post_date'] < $late_30) {
		$this->accounts[$result->fields['bill_acct_id']]['balance_30'] += $balance;
	  } else {
		$this->accounts[$result->fields['bill_acct_id']]['balance_0']  += $balance;
	  }
	  $result->MoveNext();
	}
	// Generate the output data array
	$RowCnt = 0; // Row counter for output data
	$ColCnt = 1;
	$GrpWorking = false;
	foreach ($this->accounts as $myrow) {
		// Check to see if a total row needs to be displayed
		if (isset($GrpField)) { // we're checking for group totals, see if this group is complete
			if (($myrow[$GrpField] <> $GrpWorking) && $GrpWorking !== false) { // it's a new group so print totals
				$OutputArray[$RowCnt][0] = 'g:' . $GrpWorking;
				foreach($Seq as $offset => $TotalCtl) {
					$OutputArray[$RowCnt][$offset+1] = ProcessData($TotalCtl['grptotal'], $TotalCtl['processing']);
					$Seq[$offset]['grptotal'] = ''; // reset the total
				}
				$RowCnt++; // go to next row
			}
			$GrpWorking = $myrow[$GrpField]; // set to new grouping value
		}
		$OutputArray[$RowCnt][0] = 'd'; // let the display class know its a data element
//echo 'orig myrow = '; print_r($myrow); echo '<br /><br />';
		$myrow = $this->replace_data_fields($myrow, $Seq);
//echo 'new myrow = '; print_r($myrow); echo '<br /><br />';
		foreach($Seq as $key => $TableCtl) { // 
			// insert data into output array and set to next column
			$OutputArray[$RowCnt][$ColCnt] = ProcessData($myrow[$TableCtl['fieldname']], $TableCtl['processing']);
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
		$OutputArray[$RowCnt][0] = 'g:' . $GrpWorking;
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
	$output[] = array('id' => 'contacts.short_name',              'text' => RW_AR_CUSTOMER_ID);
	$output[] = array('id' => 'contacts.account_number',          'text' => RW_AR_ACCOUNT_NUMBER);
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
	return preg_replace($preg_array, TABLE_JOURNAL_MAIN . '.bill_acct_id', $sql);
  }

  function replace_data_fields($myrow, $Seq) {
	foreach ($Seq as $key => $TableCtl) { // We need to find the id number to calculate the special fields
	  if (in_array($this->sql_field_karray[$TableCtl['fieldname']], $this->special_field_array)) {
	    $id = $myrow[$TableCtl['fieldname']];
		break;
	  }
	}
    $new_data = $this->calulate_special_fields($id);
	foreach ($myrow as $key => $value) { 
	  for ($i = 0; $i < count($this->special_field_array); $i++) {
	    if ($this->sql_field_karray[$key] == $this->special_field_array[$i]) $myrow[$key] = $new_data[$this->special_field_array[$i]];
	  }
	}
	return $myrow;
  }

  function fetch_paid_amounts($id) {
	global $db;
	if (!$id) return 0;
	$result = $db->Execute("select sum(i.debit_amount) as debits, sum(i.credit_amount) as credits 
	  from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
	  where i.so_po_item_ref_id = " . $id . " and m.journal_id in (18, 20) and i.gl_type in ('pmt', 'chk')");
	return $result->fields['credits'] - $result->fields['debits'];
  }

  function calulate_special_fields($acct_id) {
	$new_data['balance_0']  = $this->accounts[$acct_id]['balance_0'];
	$new_data['balance_30'] = $this->accounts[$acct_id]['balance_30'];
	$new_data['balance_60'] = $this->accounts[$acct_id]['balance_60'];
	$new_data['balance_90'] = $this->accounts[$acct_id]['balance_90'];
	return $new_data;
  }

}
?>