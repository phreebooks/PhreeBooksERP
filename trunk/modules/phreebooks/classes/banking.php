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
//  Path: /modules/phreebooks/classes/banking.php
//
namespace phreebooks\classes;
class banking extends \core\classes\journal {

	function __construct() {
		global $db;
		$this->journal_id          = JOURNAL_ID;//@todo remove
		$this->save_payment        = false;
		$this->search              = TEXT_SEARCH;
		$this->bill_primary_name   = TEXT_NAME_OR_COMPANY;
		$this->bill_contact        = TEXT_ATTENTION;
		$this->bill_address1       = TEXT_ADDRESS1;
		$this->bill_address2       = TEXT_ADDRESS2;
		$this->bill_city_town      = TEXT_CITY_TOWN;
		$this->bill_state_province = TEXT_STATE_PROVINCE;
		$this->bill_postal_code    = TEXT_POSTAL_CODE;
		$this->bill_country_code   = COMPANY_COUNTRY;
		$this->bill_email          = TEXT_EMAIL;
		switch ($this->journal_id) {
			case 18:
				$this->gl_acct_id          = $_SESSION['admin_prefs']['def_cash_acct'] ? $_SESSION['admin_prefs']['def_cash_acct'] : AR_SALES_RECEIPTS_ACCOUNT;
				$this->gl_disc_acct_id     = AR_DISCOUNT_SALES_ACCOUNT;
				$this->purchase_invoice_id = 'DP' . date('Ymd');
				$this->error_6				= GENERAL_JOURNAL_18_ERROR_6;
				break;
			case 20:
				$this->gl_acct_id          = $_SESSION['admin_prefs']['def_cash_acct'] ? $_SESSION['admin_prefs']['def_cash_acct'] : AP_PURCHASE_INVOICE_ACCOUNT;
				$this->gl_disc_acct_id     = AP_DISCOUNT_PURCHASE_ACCOUNT;
				$result = $db->Execute("select next_check_num from " . TABLE_CURRENT_STATUS);
				$this->purchase_invoice_id = $result->fields['next_check_num'];
				$this->error_6				= GENERAL_JOURNAL_20_ERROR_6;
				break;
			default: throw new \core\classes\userException('bad journal ID in phreebooks/classes/banking.php!');
		}
	}

	function post_ordr($action) {
		global $db, $currencies, $messageStack, $admin;
		$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
		$this->journal_rows = array();	// initialize ledger row(s) array

		switch ($this->journal_id) {
			case 18: // Cash Receipts Journal
				$method = (isset($this->shipper_code)) ? $this->shipper_code : 'freecharger';
				if (!$admin->classes['payment']->methods[$method]->installed) throw new \core\clases\userException("payment methode $method is not installed");
				$result        = $this->add_item_journal_rows('credit');	// read in line items and add to journal row array
				$credit_total  = $result['total'];
				$debit_total   = $this->add_discount_journal_row('debit');
				$debit_total  += $this->add_total_journal_row('debit', $result['total'] - $result['discount']);
				break;
			case 20: // Cash Disbursements Journal
				$result        = $this->add_item_journal_rows('debit');	// read in line items and add to journal row array
				$debit_total   = $result['total'];
				$credit_total  = $this->add_discount_journal_row('credit');
				$credit_total += $this->add_total_journal_row('credit', $result['total'] - $result['discount']);
				break;
			default: throw new \core\classes\userException("bad journal_id in banking pre-POST processing id {$this->journal_id}"); 	// this should never happen, JOURNAL_ID is tested at script entry!
		}

		// ***************************** START TRANSACTION *******************************
		$db->transStart();
		// *************  Pre-POST processing *************
		$this->validate_purchase_invoice_id();

		// ************* POST journal entry *************
		if ($this->id) {	// it's an edit, first unPost record, then rewrite
			$this->Post($new_post = 'edit');
		    $messageStack->add(BNK_REPOST_PAYMENT,'caution');
		} else {
			$this->Post($new_post = 'insert');
		}

		// ************* post-POST processing *************
		switch ($this->journal_id) {
			case 18:
				if ($this->purchase_invoice_id == '') {	// it's a new record, increment the po/so/inv to next number
					$this->increment_purchase_invoice_id();
				}
				// Lastly, we process the payment (for receipts). NEEDS TO BE AT THE END BEFORE THE COMMIT!!!
				// Because, if an error here we need to back out the entire post (which we can), but if
				// the credit card has been processed and the post fails, there is no way to back out the credit card charge.
//				$admin->classes['payment']->methods[$method]->pre_confirmation_check();
				// Update the save payment/encryption data if requested
				if (ENABLE_ENCRYPTION && $this->save_payment && $admin->classes['payment']->methods[$method]->enable_encryption !== false) {
					$this->encrypt_payment($method, $admin->classes['payment']->methods[$method]->enable_encryption);
				}
				$admin->classes['payment']->methods[$method]->before_process();
				break;
			case 20:
				if ($new_post == 'insert') { // only increment if posting a new payment
					$this->increment_purchase_invoice_id($force = true);
				}
				break;
			default:
		}

		$db->transCommit();	// finished successfully
		// ***************************** END TRANSACTION *******************************
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_POSTED, $journal_types_list[$this->journal_id]['id_field_name'], $this->purchase_invoice_id), 'success');
		return true;
	}

	function bulk_pay() {
		$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
		$this->journal_rows       = array();	// initialize ledger row(s) array

		$result        = $this->add_item_journal_rows('debit');	// read in line items and add to journal row array
		$debit_total   = $result['total'];
		$credit_total  = $this->add_discount_journal_row('credit');
		$credit_total += $this->add_total_journal_row('credit', $result['total'] - $result['discount']);

		// *************  Pre-POST processing *************
		$this->validate_purchase_invoice_id();
		// ************* POST journal entry *************
		$this->Post('insert'); // all bulk pay are new posts, cannot edit
		// ************* post-POST processing *************
		for ($i = 0; $i < count($this->item_rows); $i++) {
			$total_paid = $this->item_rows[$i]['total'] + $this->item_rows[$i]['dscnt'];
			if ($total_paid == $this->item_rows[$i]['amt']) {
				 $this->close_so_po($this->item_rows[$i]['id'], true);
			}
		}
		$force = ($this->journal_id == 18) ? false : true; // don't force increment if it's a bulk receipt
		$this->increment_purchase_invoice_id($force);
		return true;
	}

	function delete_payment() {
		global $db;
		// verify no item rows have been acted upon (accounts reconciliation)
		$result = $db->Execute("select closed from " . TABLE_JOURNAL_MAIN . " where id = " . $this->id);
		if ($result->fields['closed'] == '1') throw new \core\classes\userException($this->error_6);
		// *************** START TRANSACTION *************************
		$db->transStart();
		$this->unPost('delete');
		$db->transCommit();
		// *************** END TRANSACTION *************************
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_DELETED, $journal_types_list[ $this->journal_id]['id_field_name'], $this->purchase_invoice_id), 'success');
		return true;
	}

	function add_total_journal_row($debit_credit, $amount) {	// put total value into ledger row array
		global $admin, $journal_types_list;
		if ($debit_credit == 'debit' || $debit_credit == 'credit') {
			switch ($this->journal_id) {
				case '18':
					$desc = $journal_types_list[18]['text'] . '-' . TEXT_TOTAL . ':' . $admin->classes['payment']->methods[$method]->payment_fields;
					break;
				case '20':
				default:
					$desc = $journal_types_list[20]['text'] . '-' . TEXT_TOTAL;
			}
			$this->journal_rows[] = array( // record for accounts receivable
				'gl_type'              => 'ttl',
				$debit_credit.'_amount'=> $amount,
				'description'          => $desc,
				'gl_account'           => $this->gl_acct_id,
				'post_date'            => $this->post_date,
			);
			return $amount;
		} else {
			throw new \core\classes\userException('bad parameter passed to add_total_journal_row in class orders');
		}
	}

	function add_discount_journal_row($debit_credit) {	// put total value into ledger row array
		if ($debit_credit == 'debit' || $debit_credit == 'credit') {
			$discount = 0;
			for ($i=0; $i<count($this->item_rows); $i++) {
				if ($this->item_rows[$i]['dscnt'] <> 0) {
					$this->journal_rows[] = array(
						'so_po_item_ref_id'       => $this->item_rows[$i]['id'],
						'gl_type'                 => 'dsc',
						'description'             => TEXT_DISCOUNT,
						'gl_account'              => $this->gl_disc_acct_id,
						'serialize_number'        => $this->item_rows[$i]['inv'],
						$debit_credit . '_amount' => $this->item_rows[$i]['dscnt']);
					$discount += $this->item_rows[$i]['dscnt'];
				}
			}
			return $discount;
		} else {
			throw new \core\classes\userException('bad parameter passed to add_discount_journal_row in class banking');
		}
	}

	function add_item_journal_rows($debit_credit) {	// read in line items and add to journal row array
		if ($debit_credit == 'debit' || $debit_credit == 'credit') {
			$result = array('discount' => 0, 'total' => 0);
			for ($i=0; $i<count($this->item_rows); $i++) {
				$total_paid = $this->item_rows[$i]['dscnt'] + $this->item_rows[$i]['total'];
				$this->journal_rows[] = array(
					'so_po_item_ref_id'       => $this->item_rows[$i]['id'], // link purch/rec id here for multi-id payments
					'gl_type'                 => $this->item_rows[$i]['gl_type'],
					'description'             => $this->item_rows[$i]['desc'],
					$debit_credit . '_amount' => $total_paid,
					'gl_account'              => $this->item_rows[$i]['acct'],
					'serialize_number'        => $this->item_rows[$i]['inv'],
					'post_date'               => $this->post_date,
				);
				$result['total'] += $total_paid;
				$result['discount'] += $this->item_rows[$i]['dscnt'];
			}
			return $result;
		} else {
			throw new \core\classes\userException('bad parameter passed to add_item_journal_rows in class banking');
		}
	}

	function encrypt_payment($method, $card_key_pos = false) {
	  $cc_info = array();
	  $cc_info['name']    = isset($_POST[$method.'_field_0']) ? db_prepare_input($_POST[$method.'_field_0']) : '';
	  $cc_info['number']  = isset($_POST[$method.'_field_1']) ? db_prepare_input($_POST[$method.'_field_1']) : '';
	  $cc_info['exp_mon'] = isset($_POST[$method.'_field_2']) ? db_prepare_input($_POST[$method.'_field_2']) : '';
	  $cc_info['exp_year']= isset($_POST[$method.'_field_3']) ? db_prepare_input($_POST[$method.'_field_3']) : '';
	  $cc_info['cvv2']    = isset($_POST[$method.'_field_4']) ? db_prepare_input($_POST[$method.'_field_4']) : '';
	  $cc_info['alt1']    = isset($_POST[$method.'_field_5']) ? db_prepare_input($_POST[$method.'_field_5']) : '';
	  $cc_info['alt2']    = isset($_POST[$method.'_field_6']) ? db_prepare_input($_POST[$method.'_field_6']) : '';
	  $enc_value = \core\classes\encryption::encrypt_cc($cc_info);
	  $payment_array = array(
		'hint'      => $enc_value['hint'],
		'module'    => 'contacts',
		'enc_value' => $enc_value['encoded'],
		'ref_1'     => $this->bill_acct_id,
		'ref_2'     => $this->bill_address_id,
		'exp_date'  => $enc_value['exp_date'],
	  );
	  db_perform(TABLE_DATA_SECURITY, $payment_array, $this->payment_id ? 'update' : 'insert', 'id = '.$this->payment_id);
	  return true;
	}

} // end class banking
?>