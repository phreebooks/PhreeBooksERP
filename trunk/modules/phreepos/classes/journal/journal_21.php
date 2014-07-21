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
//  Path: /modules/phreepos/classes/journal/journal_21.php
//
// Inventory Direct Purchase Journal (POP)
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
class journal_21 extends journal {
	public $id					= '';
	public $save_payment        = false;
    public $closed 				= '0';
    public $journal_id          = 21;
    public $gl_type             = GL_TYPE;
    public $currencies_code     = DEFAULT_CURRENCY;
    public $currencies_value    = '1.0';
    public $gl_disc_acct_id     = AR_DISCOUNT_SALES_ACCOUNT;
    public $bill_acct_id		= '';
    public $bill_address_id		= '';
    public $bill_add_update		= false;
    public $bill_primary_name   = GEN_PRIMARY_NAME;
    public $bill_contact        = GEN_CONTACT;
    public $bill_address1       = GEN_ADDRESS1;
    public $bill_address2       = GEN_ADDRESS2;
    public $bill_city_town      = GEN_CITY_TOWN;
    public $bill_state_province = GEN_STATE_PROVINCE;
    public $bill_postal_code    = GEN_POSTAL_CODE;
    public $bill_country_code   = COMPANY_COUNTRY;
    public $bill_telephone1		= '';
    public $bill_email			= '';
    public $journal_rows        = array();	// initialize ledger row(s) array
	public $opendrawer			= false;
	public $printed				= false;
	public $post_date			= '';
	public $store_id			= 0;
	public $till_id				= 0;
	public $rep_id				= 0;
	public $subtotal			= 0;
	public $disc_percent		= 0;
	public $discount			= 0;
	public $sales_tax			= 0;
	public $rounded_of			= 0;
	public $total_amount		= 0;
	public $pmt_recvd			= 0;
	public $bal_due				= 0;
	public $shipper_code		= '';
	public $so_po_ref_id		= '';
    
    public function __construct($id = '') {
    	global $db;
        $result = $db->Execute("select next_check_num from " . TABLE_CURRENT_STATUS);
        $this->purchase_invoice_id = $result->fields['next_check_num'];     
        $this->gl_acct_id          = $_SESSION['admin_prefs']['def_cash_acct'] ? $_SESSION['admin_prefs']['def_cash_acct'] : AP_PURCHASE_INVOICE_ACCOUNT;
		parent::__construct($id);    
	}

	function post_ordr($action) {
		global $db, $messageStack;
		$debit_total  = 0;
		$credit_total = 0;
	    $debit_total  += $this->add_item_journal_rows(); // read in line items and add to journal row array
	    $debit_total  += $this->add_tax_journal_rows();  // fetch tax rates for tax calculation
		$credit_total += $this->add_discount_journal_row(); // put discount into journal row array		
		$debit_total  += $this->add_rounding_journal_rows($credit_total - $debit_total);	// fetch rounding of  
	    //$this->adjust_total($debit_total - $credit_total);
	    $credit_total += $this->add_total_journal_row();    // put total value into ledger row array
		$this->journal_main_array = $this->build_journal_main_array(); // build ledger main record
	
		// ***************************** START TRANSACTION *******************************
		$messageStack->debug("\n  started order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id);
		$db->transStart();
		// *************  Pre-POST processing *************
		// add/update address book
		if ($this->bill_add_update) { // billing address
			$this->bill_acct_id = $this->add_account($this->account_type . 'b', $this->bill_acct_id, $this->bill_address_id);
			if (!$this->bill_acct_id){
				$messageStack->add('no customer was selected', 'error');
				$db->transRollback();
				return false;
			} 
		}
		// ************* POST journal entry *************
		if (!$this->validate_purchase_invoice_id()) {
			$messageStack->add('invoice number is being used in a other post', 'error');
			$db->transRollback();
			return false;
		}
		if (!$this->Post($this->id ? 'edit' : 'insert',true)){
			$messageStack->add('it was not posible to post the sale', 'error');
			$db->transRollback();
			return false;
		}
		// ************* post-POST processing *************
		if (!$this->increment_purchase_invoice_id()){
			$messageStack->add('invoice number can not be incrementedt', 'error');
			$db->transRollback();
			return false;
		}
		$messageStack->debug("\n  committed order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id . "\n\n");
		$db->transCommit();
		// ***************************** END TRANSACTION *******************************
		$messageStack->add('Successfully posted ' . MENU_HEADING_PHREEPOS . ' Ref # ' . $this->purchase_invoice_id, 'success');
		return true;
	}

  function refund_ordr() {
    global $db, $messageStack;
    // *************** START TRANSACTION *************************
    $db->transStart();
    if (!$this->unPost('delete')) return false;
    $db->transCommit();
    // *************** END TRANSACTION *************************
    return true;
  }

  function add_total_journal_row() {
	global $payment_modules;
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
			'credit_amount'    => $this->pmt_rows[$i]['pmt'],
			'description'      => $desc,
			'gl_account'       => $this->gl_acct_id,
			'serialize_number' => $deposit_id,
			'post_date'        => $this->post_date,
		  );
		}
	  }
	  return $total;
  }

  function add_discount_journal_row() { // put discount into journal row array
	  if ($this->discount <> 0) {
		$this->journal_rows[] = array(
		  'qty'                     => '1',
		  'gl_type'                 => 'dsc',		// code for discount charges
		  'credit_amount' 			=> $this->discount,
		  'description'             => MENU_HEADING_PHREEPOS . '-' . TEXT_DISCOUNT,
		  'gl_account'              => $this->disc_gl_acct_id,
		  'taxable'                 => '0',
		  'post_date'               => $this->post_date,
		);
	  }
	  return $this->discount;
  }

  function add_item_journal_rows() {	// read in line items and add to journal row array
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
			'debit_amount' 			  => $this->item_rows[$i]['total'],
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
  }

  function add_tax_journal_rows() {
	global $currencies;
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
				$auth_array[$auth] += ($tax_auths[$auth]['tax_rate'] / 100) * $line_total;
			  }
		    }
		  }
	    }
	  }
	  // calculate each tax total by authority and put into journal row array
	  foreach ($auth_array as $auth => $auth_tax_collected) {
		if ($auth_tax_collected == '' && $tax_auths[$auth]['account_id'] == '') continue;
	  	if( ROUND_TAX_BY_AUTH == true ){
			$amount = number_format($auth_tax_collected, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places'], '.', '');
		}else {
			$amount = $auth_tax_collected;
		} 
	    $this->journal_rows[] = array( // record for specific tax authority
		  'qty'                     => '1',
		  'gl_type'                 => 'tax',		// code for tax entry
		  'debit_amount' 			=> $amount,
		  'description'             => $tax_auths[$auth]['description_short'],
		  'gl_account'              => $tax_auths[$auth]['account_id'],
		  'post_date'               => $this->post_date,
	    );
	    $total += $amount;
	  }
	  return $total;
  }

  // this function adjusts the posted total to the calculated one to take into account fractions of a cent
  function adjust_total($amount) {
	if ($this->total_amount == $amount) $this->total_amount = $amount;
  }
  
  function add_rounding_journal_rows($amount) { // put rounding into journal row array
	global $messageStack, $currencies;
	if((float)(string)$this->total_amount == (float)(string) $amount) return ;
	$this->rounding_amt = round(($this->total_amount - $amount), $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
	$messageStack->debug("\n calculated total = ".$amount." Posted total = ". $this->total_amount." rounding = ".$this->rounding_amt);
	if ($this->rounding_amt <> 0 ) {
		$this->journal_rows[] = array(
			'qty'            => '1',
			'gl_type'        => 'rnd',		// code for discount charges
			'debit_amount'   => ($this->rounding_amt > 0) ? -$this->rounding_amt : '',
			'credit_amount'  => ($this->rounding_amt < 0) ? $this->rounding_amt  : '',
			'description'    => MENU_HEADING_PHREEPOS . '-' . TEXT_ROUNDING_OF,
			'gl_account'     => $this->rounding_gl_acct_id,
			'taxable'        => '0',
			'post_date'      => $this->post_date,
		);
	}
	return $this->rounding_amt;
  }
  
}
?>