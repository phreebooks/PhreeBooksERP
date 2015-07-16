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
//  Path: /modules/phreebooks/classes/journal/journal_20.php
//
// Cash Dist Journal (20)
require_once(DIR_FS_MODULES . 'phreebooks/classes/Banking_test.php');
class journal_20 extends banking {
    public $journal_id          = 20;
    public $gl_type             = 'chk';
    public $popup_form_type		= 'bnk:chk';
    public $gl_disc_acct_id     = AP_DISCOUNT_PURCHASE_ACCOUNT;
  
    public function __construct($id = '') {
    	global $db;
    	$this->gl_acct_id          = $_SESSION['admin_prefs']['def_cash_acct'] ? $_SESSION['admin_prefs']['def_cash_acct'] : AP_PURCHASE_INVOICE_ACCOUNT;
		$result = $db->Execute("select next_check_num from " . TABLE_CURRENT_STATUS);
		$this->purchase_invoice_id = $result->fields['next_check_num'];
		parent::__construct($id = '');  
	}	

/*******************************************************************************************************************/
// START Chart of Accout Functions
/*******************************************************************************************************************/
  	function Post_chart_balances() {
		global $db, $messageStack, $currencies;
		$messageStack->debug("\n  Posting Chart Balances...");
		$accounts = array();
	    if (sizeof($this->journal_rows) > 0) foreach ($this->journal_rows as $value) {
			$credit_amount = ($value['credit_amount']) ? $value['credit_amount'] : '0';
		  	$debit_amount  = ($value['debit_amount'])  ? $value['debit_amount']  : '0';
		  	if  (round($credit_amount, $this->currencies[DEFAULT_CURRENCY]['decimal_places'] + 2) <> 0 
		      || round($debit_amount,  $this->currencies[DEFAULT_CURRENCY]['decimal_places'] + 2) <> 0) {
				$accounts[$value['gl_account']]['credit'] += $credit_amount;
				$accounts[$value['gl_account']]['debit']  += $debit_amount;
		    	$this->affected_accounts[$value['gl_account']] = 1;
		  	}
	    }
		if (sizeof($accounts) > 0) foreach ($accounts as $gl_acct => $values) {
			if ($values['credit'] <> 0 || $values['debit'] <> 0) {
				$sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " set 
				  credit_amount = credit_amount + " . $values['credit'] . ", 
				  debit_amount = debit_amount + " . $values['debit'] . ", 
				  last_update = '" . $this->post_date . "' 
				  where account_id = '" . $gl_acct . "' and period = " . $this->period;
				$messageStack->debug("\n    Post chart balances: credit_amount = " . $values['credit'] . ", debit_amount = " . $values['debit'] . ", acct = " . $gl_acct . ", period = " . $this->period);
				$result = $db->Execute($sql);
				if ($result->AffectedRows() <> 1) return $this->fail_message(GL_ERROR_POSTING_CHART_BALANCES . ($gl_acct ? $gl_acct : TEXT_NOT_SPECIFIED));
			}
		}
		$messageStack->debug("\n  end Posting Chart Balances.");
		return true;
  	}

	function unPost_chart_balances() {
		global $db, $messageStack;
		$messageStack->debug("\n  unPosting Chart Balances...");
		for ($i=0; $i<count($this->journal_rows); $i++) {
			// Update chart of accounts history 
			$sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " set 
			  credit_amount = credit_amount - " . $this->journal_rows[$i]['credit_amount'] . ", 
			  debit_amount = debit_amount - " . $this->journal_rows[$i]['debit_amount'] . " 
			  where account_id = '" . $this->journal_rows[$i]['gl_account'] . "' and period = " . $this->period;
			$messageStack->debug("\n    unPost chart balances: credit_amount = " . $this->journal_rows[$i]['credit_amount'] . ", debit_amount = " . $this->journal_rows[$i]['debit_amount'] . ", acct = " . $this->journal_rows[$i]['gl_account'] . ", period = " . $this->period);
			$coa_update = $db->Execute($sql);
			$this->affected_accounts[$this->journal_rows[$i]['gl_account']] = 1;
		}
		$messageStack->debug("\n  end unPosting Chart Balances.");
		return true;
  	}
	
/*******************************************************************************************************************/
// END Chart of Accout Functions
/*******************************************************************************************************************/	
// START Inventory Functions Posting
/*******************************************************************************************************************/
	function Post_inventory() {
		global $messageStack;
		$messageStack->debug("\n  Posting Inventory ...");
		$messageStack->debug(" end Posting Inventory not requiring any action.");
		return true;
  	}

  	function unPost_inventory() {
		global $messageStack;
		$messageStack->debug("\n  unPosting Inventory ...");
		$messageStack->debug(" end unPosting Inventory with no action.");
		return true; 
  	}

/*******************************************************************************************************************/
// END Inventory Functions Posting
/*******************************************************************************************************************/
  	
	function post_ordr($action) {
		global $db, $currencies, $messageStack, $processor;
		$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
		$this->journal_rows = array();	// initialize ledger row(s) array
		$result        = $this->add_item_journal_rows('debit');	// read in line items and add to journal row array
		$debit_total   = $result['total'];
		$credit_total  = $this->add_discount_journal_row('credit');
		$credit_total += $this->add_total_journal_row('credit', $result['total'] - $result['discount']);
		// ***************************** START TRANSACTION *******************************
		$db->transStart();
		// *************  Pre-POST processing *************
		if (!$this->validate_purchase_invoice_id()) return false;

		// ************* POST journal entry *************
		if ($this->id) {	// it's an edit, first unPost record, then rewrite
			if (!$this->Post($new_post = 'edit')) return false;
		    $messageStack->add(BNK_REPOST_PAYMENT,'caution');
		} else {
			if (!$this->Post($new_post = 'insert')) return false;
		}

		// ************* post-POST processing *************
		if ($new_post == 'insert') { // only increment if posting a new payment
			if (!$this->increment_purchase_invoice_id($force = true)) return false;
		}
		$db->transCommit();	// finished successfully
		// ***************************** END TRANSACTION *******************************
		$this->session_message(sprintf(TEXT_POST_SUCCESSFUL, constant('ORD_HEADING_NUMBER_' . $this->journal_id), $this->purchase_invoice_id), 'success');
		return true;
	}

	function bulk_pay() {
		global $db, $currencies, $messageStack;
		$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
		$this->journal_rows       = array();	// initialize ledger row(s) array

		$result        = $this->add_item_journal_rows('debit');	// read in line items and add to journal row array
		$debit_total   = $result['total'];
		$credit_total  = $this->add_discount_journal_row('credit');
		$credit_total += $this->add_total_journal_row('credit', $result['total'] - $result['discount']);

		// *************  Pre-POST processing *************
		if (!$this->validate_purchase_invoice_id()) return false;
		// ************* POST journal entry *************
		if (!$this->Post('insert')) return false; // all bulk pay are new posts, cannot edit
		// ************* post-POST processing *************
		for ($i = 0; $i < count($this->item_rows); $i++) {
			$total_paid = $this->item_rows[$i]['total'] + $this->item_rows[$i]['dscnt'];
			if ($total_paid == $this->item_rows[$i]['amt']) {
				 $this->close_so_po($this->item_rows[$i]['id'], true);
			}
		}
		if (!$this->increment_purchase_invoice_id(true)) return false;
		return true;
	}

	function add_total_journal_row($debit_credit, $amount) {	// put total value into ledger row array
		global $processor;
		if ($debit_credit == 'debit' || $debit_credit == 'credit') {
			$this->journal_rows[] = array( // record for accounts receivable
				'gl_type'                 => 'ttl',
				$debit_credit . '_amount' => $amount,
				'description'             => GEN_ADM_TOOLS_J20 . '-' . TEXT_TOTAL,
				'gl_account'              => $this->gl_acct_id,
			);
			return $amount;
		} else {
			die('bad parameter passed to add_total_journal_row in class orders');
		}
	}
}
?>