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
//  Path: /modules/phreeform/custom/classes/subaccount_is.php
//

// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure.
class subaccount_is {
  function __construct() {
	$this->coa_info    = load_coa_info($affected_accounts = array(30, 32, 34));
	$this->runaway_cnt = 100;
  }

  function load_report_data($report) {
	global $db, $Seq;
	$period = $report->period;
	$this->max_num_levels = sizeof($report['FieldListings']) - 1; // maximum number of indents
	$balance = array();
	$sql = "select account_id, beginning_balance + debit_amount - credit_amount as balance 
		from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " where period = " . $period . " order by account_id";
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  $balance[$result->fields['account_id']] = -$result->fields['balance'];
	  $result->MoveNext();
	}
	$this->fill_bal_sheet($balance);
	// build the output data
	$this->bal_sheet_data = array();
	$this->build_data_fields($this->coa_info, $level = 1);
	return $this->bal_sheet_data;
  }

  function fill_bal_sheet($balance) {
    global $messageStack;
    $parent = array();
	// build list of parents
    foreach ($this->coa_info as $details) {
	  if ($details['primary_acct_id']) $parent[] = $details['primary_acct_id'];
	}
	// see if we are at the botton of the tree
    foreach ($this->coa_info as $acct_id => $details) {
	  if (!in_array($acct_id, $parent) && $details['primary_acct_id']) {
	    $this->coa_info[$acct_id]['balance'] = $balance[$acct_id];
	    if (isset($this->coa_info[$acct_id]['total'])) {
		  $this->coa_info[$acct_id]['total'] += $balance[$acct_id];
	      $this->coa_info[$details['primary_acct_id']]['total'] += $this->coa_info[$acct_id]['total'];
		} else {
	      $this->coa_info[$details['primary_acct_id']]['total'] += $balance[$acct_id];
		}
		$this->coa_info[$details['primary_acct_id']]['child'][]  = $this->coa_info[$acct_id];
		unset($this->coa_info[$acct_id]);
	  }
	}
	if ($this->runaway_cnt-- < 0) {
	  $messageStack->add('Runaway counter expired, check your subaccounts for a recursive reference.','error');
	  return false;
	}
	if (sizeof($parent) > 0) $this->fill_bal_sheet($balance);
	// if we are here, the tree is built and all that is left are the top levels
    foreach ($this->coa_info as $acct_id => $account) {
	  $this->coa_info[$acct_id]['balance'] = $balance[$acct_id];
	  if (isset($this->coa_info[$acct_id]['total'])) $this->coa_info[$acct_id]['total'] += $balance[$acct_id];
	}
  }

  function build_data_fields($data, $level) {
    foreach ($data as $value) {
	  if (is_array($value['child'])) $this->build_data_fields($value['child'], $level + 1);
	  $tot_level = max(1, $this->max_num_levels - $level + 1);
	  $bal_level = max(1, $this->max_num_levels - $level);
	  $data = array('d', $value['description']);
	  for ($i = 1; $i <= $this->max_num_levels; $i++) {
	    if      ($i == $tot_level) $data[] = ProcessData($value['total'],   'null_dcur');
	    else if ($i == $bal_level) $data[] = ProcessData($value['balance'], 'null_dcur');
		else                       $data[] = '';
	  }
	  $this->bal_sheet_data[] = $data;
	}
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output   = array();
	$output[] = array('id' => '[table1].id', 'text' => RW_RECORD_ID);
	return $output;
  }

}
?>