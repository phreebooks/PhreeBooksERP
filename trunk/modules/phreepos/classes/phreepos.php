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
//  Path: /modules/phreepos/classes/phreepos.php
//
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');

class phreepos extends journal {
	public $save_payment        = false;
    public $closed = '0';
    public $journal_id          = JOURNAL_ID;
    public $gl_type             = GL_TYPE;
    public $currencies_code     = DEFAULT_CURRENCY;
    public $currencies_value    = '1.0';
    public $journal_rows        = array();	// initialize ledger row(s) array

    function __construct($id = '') {
	    global $db;
		switch ($this->journal_id) {
		  case 19:
			$this->gl_acct_id          = $_SESSION['admin_prefs']['def_cash_acct'] ? $_SESSION['admin_prefs']['def_cash_acct'] : AR_SALES_RECEIPTS_ACCOUNT;
			$this->gl_disc_acct_id     = AR_DISCOUNT_SALES_ACCOUNT;
			$this->purchase_invoice_id = 'DP' . date('Ymd');
			break;
		  case 21:
			$this->gl_acct_id          = $_SESSION['admin_prefs']['def_cash_acct'] ? $_SESSION['admin_prefs']['def_cash_acct'] : AP_PURCHASE_INVOICE_ACCOUNT;
			$this->gl_disc_acct_id     = AP_DISCOUNT_PURCHASE_ACCOUNT;
			$result = $db->Execute("select next_check_num from " . TABLE_CURRENT_STATUS);
			$this->purchase_invoice_id = $result->fields['next_check_num'];
			break;
		  default: $this->fail_message = 'bad journal ID in phreepos/classes/phreepos.php!';
		}
		$this->bill_primary_name   = GEN_PRIMARY_NAME;
		$this->bill_contact        = GEN_CONTACT;
		$this->bill_address1       = GEN_ADDRESS1;
		$this->bill_address2       = GEN_ADDRESS2;
		$this->bill_city_town      = GEN_CITY_TOWN;
		$this->bill_state_province = GEN_STATE_PROVINCE;
		$this->bill_postal_code    = GEN_POSTAL_CODE;
		$this->bill_country_code   = COMPANY_COUNTRY;
		parent::__construct($id = '');    
	  }

  function post_ordr($action) {
	global $db, $messageStack;
	$debit_total  = 0;
	$credit_total = 0;
	switch ($this->journal_id) { // THE SEQUENCE IS IMPORTANT!
	  case 21: // Inventory Direct Purchase Journal (POP)
		$debit_total  += $this->add_item_journal_rows('debit');	// read in line items and add to journal row array
		$debit_total  += $this->add_tax_journal_rows('debit');	// fetch tax rates for tax calculation
		$debit_total  += $this->add_rounding_journal_rows('debit');	// fetch rounding of
		$credit_total += $this->add_discount_journal_row('credit'); // put discount into journal row array
		$this->adjust_total($debit_total - $credit_total);
		$credit_total += $this->add_total_journal_row('credit');	// put total value into ledger row array
		break;
	  case 19: // POS Journal
		$credit_total += $this->add_item_journal_rows('credit'); // read in line items and add to journal row array
		$credit_total += $this->add_tax_journal_rows('credit');	// fetch tax rates for tax calculation
        $credit_total += $this->add_rounding_journal_rows('credit');	// fetch rounding of 
		$debit_total  += $this->add_discount_journal_row('debit'); // put discount into journal row array
		$this->adjust_total($credit_total - $debit_total);
		$debit_total  += $this->add_total_journal_row('debit');	// put total value into ledger row array
		break;
	  default: return $this->error = 'bad journal_id in pre-POST processing'; // this should never happen, JOURNAL_ID is tested at script entry!
	}
	$this->journal_main_array = $this->build_journal_main_array(); // build ledger main record

	// ***************************** START TRANSACTION *******************************
	$messageStack->debug("\n  started order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id);
	$db->transStart();
	// *************  Pre-POST processing *************
	// add/update address book
	if ($this->bill_add_update) { // billing address
	  $this->bill_acct_id = $this->add_account($this->account_type . 'b', $this->bill_acct_id, $this->bill_address_id);
	  if (!$this->bill_acct_id) return false;
	}
	// ************* POST journal entry *************
	if (!$this->validate_purchase_invoice_id())      return false;
	if (!$this->Post($this->id ? 'edit' : 'insert')) return false;
	// ************* post-POST processing *************
	switch ($this->journal_id) {
	  case 19: // POS
		if (!$this->increment_purchase_invoice_id()) return false;
		// cycle through the payments
		foreach ($this->pmt_rows as $pay_method) {
		  $pay_meth  = $pay_method['meth'];
		  $processor = new $pay_meth;
		  if (ENABLE_ENCRYPTION && $this->save_payment && $processor->enable_encryption !== false) {
		    if (!$this->encrypt_payment($pay_method, $processor->enable_encryption)) return false;
		  }
		  if ($processor->before_process()) return false;
		}
		break;
	  case 21: // POP
	    $prior = $this->purchase_invoice_id;
		if (!$this->increment_purchase_invoice_id($force = true)) return false;
		break;
	  default:
	}
	$messageStack->debug("\n  committed order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id . "\n\n");
	$db->transCommit();
	// ***************************** END TRANSACTION *******************************
	$messageStack->add_session('Successfully posted ' . MENU_HEADING_PHREEPOS . ' Ref # ' . $this->purchase_invoice_id, 'success');
	return true;
  }

  function add_total_journal_row($dc) {
	global $payment_modules;
    if ($dc == 'debit' || $dc == 'credit') {
	  $total = 0;
	  for ($i = 0; $i < count($this->pmt_rows); $i++) {
		if ($this->pmt_rows[$i]['pmt']) { // make sure the payment line is set and not zero
		  $desc = MENU_HEADING_PHREEPOS . '-' . TEXT_TOTAL;
		  $method     = $this->pmt_rows[$i]['meth'];
		  if ($method) {
		    $$method    = new $method;
		    $deposit_id = $$method->def_deposit_id ? $$method->def_deposit_id : ('DP' . date('Ymd'));
			$desc = JOURNAL_ID . ':' . $method . ':' . $$method->payment_fields;
		  }
		  $total     += $this->pmt_rows[$i]['pmt'];
		  if ($total > $this->total_amount) { // change was returned, adjust amount received for post
			$this->pmt_rows[$i]['pmt'] = $this->pmt_rows[$i]['pmt'] - ($total - $this->total_amount);
		    $total = $this->total_amount;
		  }
		  $desc = ($this->pmt_rows[$i]['desc']) ? $this->pmt_rows[$i]['desc'] : $desc;
		  $this->journal_rows[] = array(
			'gl_type'          => 'ttl',
			$dc . '_amount'    => $this->pmt_rows[$i]['pmt'],
			'description'      => $desc,
			'gl_account'       => $this->gl_acct_id,
			'serialize_number' => $deposit_id,
			'post_date'        => $this->post_date,
		  );
		}
	  }
	  return $total;
    } else {
	  $this->error = 'bad parameter passed to add_total_journal_row in class phreepos';
    }
  }

  function add_discount_journal_row($debit_credit) { // put discount into journal row array
	if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  if ($this->discount <> 0) {
		$this->journal_rows[] = array(
		  'qty'                     => '1',
		  'gl_type'                 => 'dsc',		// code for discount charges
		  $debit_credit . '_amount' => $this->discount,
		  'description'             => MENU_HEADING_PHREEPOS . '-' . TEXT_DISCOUNT,
		  'gl_account'              => $this->disc_gl_acct_id,
		  'taxable'                 => '0',
		  'post_date'               => $this->post_date,
		);
	  }
	  return $this->discount;
	} else {
	  $this->error = 'bad parameter passed to add_discount_journal_row in class phreepos.';
	}
  }

  function add_item_journal_rows($debit_credit) {	// read in line items and add to journal row array
	if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  $total = 0;
	  for ($i = 0; $i < count($this->item_rows); $i++) {
		if ($this->item_rows[$i]['pstd']) { // make sure the quantity line is set and not zero
		  $this->journal_rows[] = array(
			'id'                      => $this->item_rows[$i]['id'],	// retain the db id (used for updates)
			'so_po_item_ref_id'       => 0,	// item reference id for so/po line items
			'gl_type'                 => $this->gl_type,
			'sku'                     => $this->item_rows[$i]['sku'],
			'qty'                     => $this->item_rows[$i]['pstd'],
			'description'             => $this->item_rows[$i]['desc'],
			$debit_credit . '_amount' => $this->item_rows[$i]['total'],
			'full_price'              => $this->item_rows[$i]['full'],
			'gl_account'              => $this->item_rows[$i]['acct'],
			'taxable'                 => $this->item_rows[$i]['tax'],
			'serialize_number'        => $this->item_rows[$i]['serial'],
			'project_id'              => $this->item_rows[$i]['proj'],
			'post_date'               => $this->post_date,
			'date_1'                  => '',
		  );
		  $total += $this->item_rows[$i]['total'];
		}
	  }
	  return $total;
	} else {
	  $this->error = 'Bad parameter passed to add_item_journal_rows in class phreepos!';
	}
  }

  function add_tax_journal_rows($debit_credit) {
	global $currencies;
    if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  $total        = 0;
	  $auth_array   = array();
	  $tax_rates    = ord_calculate_tax_drop_down('b');
	  $tax_auths    = gen_build_tax_auth_array();
	  $tax_discount = $this->account_type == 'v' ? AP_TAX_BEFORE_DISCOUNT : AR_TAX_BEFORE_DISCOUNT;
	  // calculate each tax value by authority per line item
	  foreach ($this->journal_rows as $idx => $line_item) {
	    if ($line_item['taxable'] > 0 && ($line_item['gl_type'] == $this->gl_type || $line_item['gl_type'] == 'frt')) {
		  foreach ($tax_rates as $rate) {
		    if ($rate['id'] == $line_item['taxable']) {
			  $auths = explode(':', $rate['auths']);
			  foreach ($auths as $auth) {
			    $line_total = $line_item['debit_amount'] + $line_item['credit_amount']; // one will always be zero
			    if (ENABLE_ORDER_DISCOUNT && $tax_discount == '0') {
				  $line_total = $line_total * (1 - $this->disc_percent);
			    }
			    if (ROUND_TAX_BY_AUTH) {
				  $auth_array[$auth] += round((($tax_auths[$auth]['tax_rate'] / 100) * $line_total), $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
			    } else {
				  $auth_array[$auth] += ($tax_auths[$auth]['tax_rate'] / 100) * $line_total;
			    }
			  }
		    }
		  }
	    }
	  }
	  // calculate each tax total by authority and put into journal row array
	  foreach ($auth_array as $auth => $auth_tax_collected) {
	    $this->journal_rows[] = array( // record for specific tax authority
		  'qty'                     => '1',
		  'gl_type'                 => 'tax',		// code for tax entry
		  $debit_credit . '_amount' => $auth_tax_collected,
		  'description'             => $tax_auths[$auth]['description_short'],
		  'gl_account'              => $tax_auths[$auth]['account_id'],
		  'post_date'               => $this->post_date,
	    );
	    $total += $auth_tax_collected;
	  }
	  return $total;
    } else {
	  $this->error = 'bad parameter passed to add_tax_journal_rows in class phreepos.';
    }
  }

  // this function adjusts the posted total to the calculated one to take into account fractions of a cent
  function adjust_total($amount) {
	global $currencies;
	$posted_total     = $currencies->format($this->total_amount);
	$calculated_total = $currencies->format($amount);
	if ($posted_total == $calculated_total) $this->total_amount = $amount;
  }
  
  function add_rounding_journal_rows($debit_credit) { // put rounding into journal row array
	if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  if ($this->rounding_amt <> 0) {
		$this->journal_rows[] = array(
		  'qty'                     => '1',
		  'gl_type'                 => 'rnd',		// code for discount charges
		  $debit_credit . '_amount' => $this->rounding_amt,
		  'description'             => MENU_HEADING_PHREEPOS . '-' . TEXT_ROUNDING_OF,
		  'gl_account'              => $this->rounding_gl_acct_id,
		  'taxable'                 => '0',
		  'post_date'               => $this->post_date,
		);
	  }
	  return $this->rounding_amt;
	} else {
	  $this->error = 'bad parameter passed to add_rounding_journal_rows in class phreepos.';
	}
  }
  
  function encrypt_payment($method, $card_key_pos = false) {
	  $encrypt = new encryption();
	  $cc_info = array();
	  $cc_info['name']    = isset($_POST[$method.'_field_0']) ? db_prepare_input($_POST[$method.'_field_0']) : '';
	  $cc_info['number']  = isset($_POST[$method.'_field_1']) ? db_prepare_input($_POST[$method.'_field_1']) : '';
	  $cc_info['exp_mon'] = isset($_POST[$method.'_field_2']) ? db_prepare_input($_POST[$method.'_field_2']) : '';
	  $cc_info['exp_year']= isset($_POST[$method.'_field_3']) ? db_prepare_input($_POST[$method.'_field_3']) : '';
	  $cc_info['cvv2']    = isset($_POST[$method.'_field_4']) ? db_prepare_input($_POST[$method.'_field_4']) : '';
	  $cc_info['alt1']    = isset($_POST[$method.'_field_5']) ? db_prepare_input($_POST[$method.'_field_5']) : '';
	  $cc_info['alt2']    = isset($_POST[$method.'_field_6']) ? db_prepare_input($_POST[$method.'_field_6']) : '';
	  if (!$enc_value = $encrypt->encrypt_cc($cc_info)) return false;
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
  
}
?>