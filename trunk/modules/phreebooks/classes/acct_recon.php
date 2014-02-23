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
//  Path: /modules/phreebooks/classes/acct_recon.php
// 
namespace phreebooks\classes;
// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure.
class acct_recon {
	function __construct() {
	}

	function load_report_data($report) {
		global $db;
		$bank_list         = array();
		$dep_in_transit    = 0;
		$chk_in_transit    = 0;
		$period            = $report->period;
		$fiscal_dates      = gen_calculate_fiscal_dates($period);
	    $gl_account        = $report->filterlist[0]->min_val; // assumes that the gl account is the first criteria
	    if (!$gl_account) return false; // No gl account so bail now

	    //Load open Journal Items
		$sql = "SELECT m.id, m.post_date, i.debit_amount, i.credit_amount, m.purchase_invoice_id, i.description " .
			"FROM " . TABLE_JOURNAL_MAIN . " m " . "INNER JOIN " . TABLE_JOURNAL_ITEM . " i " .	"ON m.id = i.ref_id " .
			"WHERE i.gl_account = '" . $gl_account . "' " .
				"AND i.reconciled = 0 " .
				"AND m.post_date <= '" . $fiscal_dates['end_date'] . "' " .
			"ORDER BY post_date";
			
		$result = $db->Execute($sql);
		while (!$result->EOF) {
		  $new_total      = $result->fields['debit_amount'] - $result->fields['credit_amount'];
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
		  $bank_list[$result->fields['id']] = array(
			'post_date'  => $result->fields['post_date'],
			'reference'  => $result->fields['purchase_invoice_id'],
			'description'=> $result->fields['description'],
			'dep_amount' => $dep_amount,
			'pmt_amount' => $pmt_amount,
		  );
		  $result->MoveNext();
		}
		
		// load the gl account end of period balance
		$sql = "select beginning_balance, debit_amount, credit_amount from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
			where account_id = '" . $gl_account . "' and period = " . $period;
		$result        = $db->Execute($sql);
		$gl_init_bal   = $result->fields['beginning_balance'];
		$cash_receipts = $result->fields['debit_amount'];
		$cash_payments = $result->fields['credit_amount'];
		$end_gl_bal    = $gl_init_bal + $cash_receipts - $cash_payments;
		// Check this next line - end_gl_bal_1 or just end_gl_bal?
		$unrecon_diff  = $end_gl_bal - $dep_in_transit + $chk_in_transit;

		$this->bal_sheet_data = array();
		$this->bal_sheet_data[] = array('d', RW_RECON_BB, '', '', '', ProcessData($gl_init_bal, 'null_pcur'));
		$this->bal_sheet_data[] = array('d', '', '', '', '', ''); 
		$this->bal_sheet_data[] = array('d', RW_RECON_CR,        '', '', '', ProcessData($cash_receipts, 'null_pcur')); 
		$this->bal_sheet_data[] = array('d', '', '', '', '', ''); 
		$this->bal_sheet_data[] = array('d', RW_RECON_CD,   '', '', '', ProcessData(-$cash_payments, 'null_pcur')); 
		$this->bal_sheet_data[] = array('d', '', '', '', '', ''); 
		$this->bal_sheet_data[] = array('d', RW_RECON_EB,    '', '', '', ProcessData($end_gl_bal, 'null_pcur')); 
		$this->bal_sheet_data[] = array('d', '', '', '', '', ''); 
		$this->bal_sheet_data[] = array('d', RW_RECON_ADD_BACK, '', '', '', ''); 
		foreach ($bank_list as $value) {
		  if ($value['dep_amount']) {
			$this->bal_sheet_data[] = array('d', '', ProcessData($value['post_date'], 'date'), $value['reference'], ProcessData($value['dep_amount'], 'null_pcur'), ''); 
		  }
		}
		$this->bal_sheet_data[] = array('d', RW_RECON_DIT, '', '', '', ProcessData($dep_in_transit, 'null_pcur')); 
		$this->bal_sheet_data[] = array('d', '', '', '', '', ''); 
		$this->bal_sheet_data[] = array('d', RW_RECON_LOP, '', '', '', ''); 
		foreach ($bank_list as $value) {
		  if ($value['pmt_amount']) {
			$this->bal_sheet_data[] = array('d', '', ProcessData($value['post_date'], 'date'), $value['reference'], ProcessData(-$value['pmt_amount'], 'null_pcur'), ''); 
		  }
		}
		$this->bal_sheet_data[] = array('d', RW_RECON_TOP, '', '', '', ProcessData(-$chk_in_transit, 'null_pcur')); 
		$this->bal_sheet_data[] = array('d', '', '', '', '', ''); 
		$this->bal_sheet_data[] = array('d', RW_RECON_DIFF, '', '', '', ProcessData($unrecon_diff, 'null_pcur')); 
		$this->bal_sheet_data[] = array('d', RW_RECON_EB, '', '', '', ProcessData($end_gl_bal, 'null_pcur')); 
		
		//Load closed Journal Items
		$this->bal_sheet_data[] = array('d', '', '', '', '', '');
		$this->bal_sheet_data[] = array('d', RW_RECON_CLEARED, '', '', '', '');

		$sql = "SELECT m.id, m.post_date, i.debit_amount, i.credit_amount, m.purchase_invoice_id, i.description " .
		"FROM " . TABLE_JOURNAL_MAIN . " m " . "INNER JOIN " . TABLE_JOURNAL_ITEM . " i " . "ON m.id = i.ref_id " .
		"WHERE i.gl_account = '" . $gl_account . "' " .
		"AND i.reconciled = $period " .
		"AND m.post_date <= '" . $fiscal_dates['end_date'] . "' " .
		"ORDER BY post_date";
			
		$result = $db->Execute($sql);
		unset($new_total, $bank_list);
		while (!$result->EOF) {
		  $new_total      = $result->fields['debit_amount'] - $result->fields['credit_amount'];
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
		  $bank_list[$result->fields['id']] = array(
			'post_date'  => $result->fields['post_date'],
			'reference'  => $result->fields['purchase_invoice_id'],
			'description'=> $result->fields['description'],
			'dep_amount' => $dep_amount,
			'pmt_amount' => $pmt_amount,
		  );
		  $result->MoveNext();
		}
		$this->bal_sheet_data[] = array('d', RW_RECON_DCLEARED, '', '', '', '' );
		if (is_array($bank_list)) foreach ($bank_list as $value) {
		  if ($value['dep_amount']) {
			$this->bal_sheet_data[] = array('d', '', ProcessData($value['post_date'], 'date'), $value['reference'], ProcessData($value['dep_amount'], 'null_pcur'), ''); 
		  }
		}
		$this->bal_sheet_data[] = array('d', RW_RECON_TDC, '', '', '', ProcessData( $dep_cleared, 'null_pcur') );
		$this->bal_sheet_data[] = array('d', '', '', '', '', '');
		$this->bal_sheet_data[] = array('d', RW_RECON_PCLEARED, '', '', '', '' );
		if (is_array($bank_list)) foreach ($bank_list as $value) {
		  if ($value['pmt_amount']) {
			$this->bal_sheet_data[] = array('d', '', ProcessData($value['post_date'], 'date'), $value['reference'], ProcessData(-$value['pmt_amount'], 'null_pcur'), ''); 
		  }
		}
		$this->bal_sheet_data[] = array('d', RW_RECON_TPC, '', '', '', ProcessData( $chk_cleared, 'null_pcur') );
		$this->bal_sheet_data[] = array('d', '', '', '', '', '' );
		$this->bal_sheet_data[] = array('d', RW_RECON_NCLEARED, '', '', '', ProcessData( $dep_cleared - $chk_cleared, 'null_pcur') );
		
		return $this->bal_sheet_data;
	}

  function build_table_drop_down() {
	$output = array();
	return $output;
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => 'id', 'text' => RW_RECON_TBD);
	return $output;
  }

}
?>