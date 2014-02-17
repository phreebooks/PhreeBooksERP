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
//  Path: /modules/import_bank/classes/import_bank.php
//
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
gen_pull_language('phreebooks');
require(DIR_FS_MODULES  . 'phreebooks/functions/phreebooks.php');

class impbanking extends journal {
	protected $_questionposts = QUESTION_POSTS;
	protected $_accounttype;
	protected $_creditamount;
	protected $_debitamount;
	protected $_totalamount;
	protected $_description;
	protected $_firstjid;
	public    $_succes = false;
	public    $bank_account_fields = array();
	public    $iban_fields = array();
	public    $known_trans = array();
	public    $open_inv    = array();
	
	 public function __construct(){
	 	global $db, $messageStack;
	 	$messageStack->debug("\n\n*************** Start Import Payment Class *******************");
	 	$temp = $db->Execute("describe " . TABLE_CONTACTS);
	 	while (!$temp->EOF) {
	 		if(strripos($temp->fields['Field'],'bank_account')!==false) $this->bank_account_fields[] = $temp->fields['Field'];
	 		if(strripos($temp->fields['Field'],'iban')!==false)         $this->iban_fields[] = $temp->fields['Field'];
			$temp->MoveNext();
		}
		$this->get_known_trans();
		$this->get_all_open_invoices();
	 }
	 
	 public function start_import($ouwer_bank_account_number, $post_date, $other_bank_account_number, $credit_amount, $debit_amount, $description, $bank_gl_acct, $other_bank_account_iban){
	 	global $db, $messageStack,$currencies;
	 	$this->reset();
	 	$messageStack->debug("\n\n*************** Start Processing Import Payment *******************");
	 	if ($ouwer_bank_account_number <> '') {
			$ouwer_bank = ltrim($ouwer_bank_account_number,0);
		 	If($ouwer_bank == ''){
				$messageStack->add(TEXT_BIMP_ERMSG1 , 'error');
				return false;
				exit;
			}
		 	$sql ="select id, description from " . TABLE_CHART_OF_ACCOUNTS. " where description like '%".$ouwer_bank."%'";
			$result = $db->Execute($sql);
			If($result->RecordCount()== 0){
				$messageStack->add(TEXT_BIMP_ERMSG5 .' '. $ouwer_bank, 'error');
				return false;
				exit;
			}
			if (!$result->RecordCount()> 1){
				$messageStack->add(TEXT_BIMP_ERMSG2 .' '. $ouwer_bank, 'error');
				return false;
				exit;
			}
			$this->gl_acct_id 			= $result->fields['id'];
		}else{
			If($bank_gl_acct == ''){
				$messageStack->add(TEXT_BIMP_ERMSG1 , 'error');
				return false;
				exit;
			}
			$this->gl_acct_id 			= $bank_gl_acct;
		}
		$this->_description			= $description;
		$this->_creditamount		= $currencies->clean_value($credit_amount);
		$this->_debitamount			= $currencies->clean_value($debit_amount);
		$this->total_amount			= $this->_debitamount + $this->_creditamount ;
		$this->post_date           	= gen_db_date($post_date);
		$this->period              	= gen_calculate_period($this->post_date, true);
		$this->admin_id            	= $_SESSION['admin_id'];
		If ($this->find_contact( $other_bank_account_number, $other_bank_account_iban )){	
			$this->find_right_invoice();
		}else{
			if($this->proces_know_mutation($other_bank_account_number, $other_bank_account_iban) == false){
				$this->proces_mutation();
			} 
		}
	$messageStack->debug("\n\n*************** End Processing Import Payment *******************");
	}
	
	private function find_contact($other_bank_account_number, $other_bank_account_iban){
		global $db, $messageStack;
		$criteria = false;
		$messageStack->debug("\n trying to find a contact ");
		if($other_bank_account_number =='' && $other_bank_account_iban ==''){
			$messageStack->debug("\n there is no other bank account number or iban. can not find contact. ");
			return false;
		}
		if($other_bank_account_iban != '' && count($this->iban_fields) > 0 ){
			$criteria = '(' . implode(' = "' . $other_bank_account_iban . '" or ', $this->iban_fields) . ' = "' . $other_bank_account_iban . '")';
		}   
		if($other_bank_account_number != '' && $criteria == false){
			if(count($this->bank_account_fields) > 0){
				$criteria = '(' . implode(' = "' . ltrim($other_bank_account_number,0) . '" or ', $this->bank_account_fields) . ' = "' . ltrim($other_bank_account_number,0) . '")';
			}else{
				if($other_bank_account_iban != '' )  $messageStack->debug("\n iban field is supplied but cant find it in the contact fields. If you want it add it in the contacts module administration ");
				if($other_bank_account_number != '') $messageStack->debug("\n the other bank account field is supplied but cant find it in the contact fields. If you want it add it in the contacts module administration");
				return false;
			}
		} 
		
		$sql ="SELECT * FROM ". TABLE_CONTACTS ." WHERE (`type` ='v' or `type`='c' ) and ".$criteria;
		$result1 = $db->Execute($sql);
		$contact = false;
		if(!$result1->RecordCount()== 0){
			$messageStack->debug("\n found a costumer or vender with the bankaccountnumber ". ltrim($other_bank_account_number,0));
			if (!$result1->RecordCount()> 1){
				//TEXT_IMP_ERMSG17 = two or more accounts with the same account
				$messageStack->add(TEXT_BIMP_ERMSG4 . $other_bank_account_number, 'error');
				return false;
			}
			$contact['id']   = $result1->fields['id'];
			$contact['type'] = $result1->fields['type'];
		}else{
			$contact = $this->unknown_contact($other_bank_account_number, $other_bank_account_iban);
		}
		if($contact == false){
			$messageStack->debug("\n was unable to find a 1 matching contact. ");		
			return false;
		}
		$result2 = $db->Execute("SELECT * FROM ". TABLE_ADDRESS_BOOK ." WHERE (`type` ='vm' or `type`='vb' or `type` ='cm' or `type` ='cb' ) and `ref_id` = '" . $contact['id']."'");
		$this->bill_short_name     	= $contact['id'];
		$this->bill_acct_id        	= $contact['id'];
		$this->bill_addres_id		= $contact['id'];
		$this->_accounttype 		= $contact['type'];
		$this->bill_address_id     	= $result2->fields['address_id'];
		$this->bill_primary_name	= $result2->fields['primary_name'];
		$this->bill_contact        	= $result2->fields['contact'];
		$this->bill_address1       	= $result2->fields['address1'];
		$this->bill_address2       	= $result2->fields['address2'];
		$this->bill_city_town      	= $result2->fields['city_town'];
		$this->bill_state_province 	= $result2->fields['state_province'];
		$this->bill_postal_code    	= $result2->fields['postal_code'];
		$this->bill_country_code 	= $result2->fields['country_code']; 
		$this->id					= '';
		return true;
	}
	
	private function find_right_invoice(){
		global $db, $messageStack, $currencies;
		$messageStack->debug("\n trying to find the right invoice");
		$found_invoices = array();
		$invoice_number = array();
		$invoice_id 	= array();
		$epsilon 		= 0.00001;
		if(isset($this->open_inv[$this->bill_acct_id])) foreach ($this->open_inv[$this->bill_acct_id] as $key => $invoice) {
			//when we find a invoice we book a payment to it
			if( strripos($this->_description, $invoice['purchase_invoice_id']) !== false){ // if invoice is part of the description use it
				$messageStack->debug("\n Found matching invoice number in description purchase_invoice_id ".$invoice['purchase_invoice_id'].' id '.$invoice['id']."\n variables ".arr2string($invoice));
				$invoice['found_by_number'] = true;
				$found_invoices[] = $invoice;
			}else if ( abs($invoice['total_amount'] - $invoice['amount_paid'] - $this->total_amount) < $epsilon)	{
				$messageStack->debug("\n Found by value matching invoice purchase_invoice_id ".$invoice['purchase_invoice_id'].' id '.$invoice['id']."\n variables ".arr2string($invoice));
				$invoice['found_by_number'] = false;
				$found_invoices[] = $invoice;
			}
		}
		
		if ($this->_accounttype =='c'){
			$this->_firstjid 			= 18; 
			$gl_acct_id         		= AR_DEFAULT_GL_ACCT ;
			$gl_disc_acct_id			= AR_DISCOUNT_SALES_ACCOUNT;
			$this->purchase_invoice_id 	= 'DP' . $this->post_date; 
			$this->description			= sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_18_C_WINDOW_TITLE'));
			define('GL_TYPE','pmt');
		}else{
			$this->_firstjid 			= 20;
			$gl_acct_id         		= AP_DEFAULT_PURCHASE_ACCOUNT ;
			$gl_disc_acct_id			= AP_DISCOUNT_PURCHASE_ACCOUNT;
			$result 					= $db->Execute("select next_check_num from " . TABLE_CURRENT_STATUS);
			$this->description			= sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_20_V_WINDOW_TITLE'));
			define('GL_TYPE','chk');
		}
		$this->journal_id = $this->_firstjid;
		$good = false;
		$step = 1;
		$difference_perc = 100;
		while (!$good && $step <= 9 && sizeof($found_invoices) > 0){
			$amount_used = 0;
			if (!$good) $this->journal_rows = null;
			for($i=0; $i < sizeof($found_invoices); $i++){
				$found_invoices[$i]['amount']   = $found_invoices[$i]['total_amount'] - $found_invoices[$i]['amount_paid'];
				$found_invoices[$i]['payed_if_full'] = true;
				switch ($step){
					case 6:
					case 1: // full amount is payed
						$found_invoices[$i]['discount'] = false; 
						$messageStack->debug("\n step ".$step." trying if we get totals balanced when the full amount is payed. ");
						break;
					case 7:
					case 2: // make use of discount full
						$messageStack->debug("\n step ".$step." trying if we get totals balanced when the full discount is used. ");
						if($found_invoices[$i]['early_date'] >= $this->post_date){// if post_date is smaller than early_date allow discount 
							$found_invoices[$i]['discount'] = round($found_invoices[$i]['total_amount'] * $found_invoices[$i]['percent'],  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
							$messageStack->debug("\n discount could be used for invoice ".$found_invoices[$i]['purchase_invoice_id']." Payed = ". $found_invoices[$i]['amount']." Discount = ". $found_invoices[$i]['discount']);
						}
						break;
					case 8:
					case 3: // make use of less discount than is allowed.
						$messageStack->debug("\n step ".$step." trying if we get totals balanced when less than the full discount is used. ");
						if( $found_invoices[$i]['discount'] && $difference > 0 ){
							$messageStack->debug("\n for invoice ".$found_invoices[$i]['purchase_invoice_id']." discount = ".$found_invoices[$i]['discount']." difference = ". $difference);
							if($found_invoices[$i]['discount'] >= $difference){
								$found_invoices[$i]['discount'] -= $difference;
								$difference = 0;
								$messageStack->debug("\n for invoice ".$found_invoices[$i]['purchase_invoice_id']." discount reduced by the difference.");
							}else{
								$difference -= $found_invoices[$i]['discount'];
								$found_invoices[$i]['discount'] = false;
								$messageStack->debug("\n for invoice ".$found_invoices[$i]['purchase_invoice_id']." discount is set to null and difference is decreased by the discount.");
							}
							$found_invoices[$i]['discount'] = round($found_invoices[$i]['discount'],  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']); 
						}
						break;
					case 5:// unset items that where not found by invoice_number
						$messageStack->debug("\n step ".$step." removing items that were not found by invoice number. ");
						if(!$found_invoices[$i]['found_by_number']) {
							$messageStack->debug("\n removing invoice number. ".$found_invoices[$i]['purchase_invoice_id']);
							$found_invoices[$i]['total_amount'] = 0;
							$found_invoices[$i]['amount_paid']  = 0;
							$found_invoices[$i]['amount']		= 0;
							$found_invoices[$i]['early_date']   = 0;
							$found_invoices[$i]['percent']		= 0;	
							$found_invoices[$i]['discount']     = false;
						}
						$messageStack->debug("\n now only use invoices " .arr2string($invoice));
						break;
					case 9;//do a partial payment
						if($difference_perc > 0){
					    	$found_invoices[$i]['amount'] = round($found_invoices[$i]['amount'] / $difference_perc,  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
					    	$messageStack->debug("\n step ".$step." doing a partial payment. ");
							$found_invoices[$i]['payed_if_full']  = false;
					    	$found_invoices[$i]['discount']       = false;
						}else{
							$found_invoices[$i]['amount'] 		  = 0;
							$found_invoices[$i]['payed_if_full']  = false;
					    	$found_invoices[$i]['discount']       = false;
						}
					    break;
					case 4;//try the first invoice		
						$messageStack->debug("\n step ".$step." trying the first invoice. ");
						if($i > 0){
							$found_invoices[$i]['discount'] 	 = false; 
							$found_invoices[$i]['amount']   	 = 0;
							$found_invoices[$i]['payed_if_full'] = false;
						}
					    break;
				}
				if($found_invoices[$i]['amount'] <> 0){
					$this->journal_rows[] = array(
						'so_po_item_ref_id'		=> $found_invoices[$i]['id'], 	
						'gl_type'               => GL_TYPE,
						'gl_account'            => $gl_acct_id,
						'serialize_number'      => $found_invoices[$i]['purchase_invoice_id'],
						'post_date'             => $this->post_date,
						'debit_amount'     		=> ($this->_firstjid == 20 ? $found_invoices[$i]['amount'] : 0),	
						'credit_amount'			=> ($this->_firstjid == 20 ? 0       : $found_invoices[$i]['amount'] ),
				  		'description'    		=> $this->_description,	
					);
				}
				if($found_invoices[$i]['discount']){
					$this->journal_rows[] = array(
						'so_po_item_ref_id'		=> $found_invoices[$i]['id'], 	
						'gl_type'               => 'dsc',
						'gl_account'            => $gl_disc_acct_id,
						'serialize_number'      => $found_invoices[$i]['purchase_invoice_id'],
						'post_date'             => $this->post_date,
						'debit_amount'     		=> ($this->_firstjid == 20 ? 0         : $found_invoices[$i]['discount']),	
						'credit_amount'			=> ($this->_firstjid == 20 ? $found_invoices[$i]['discount'] : 0 ),
			  			'description'    		=> $this->_description,	
					);
				}
				$amount_used += ($found_invoices[$i]['amount'] - $found_invoices[$i]['discount']);
			}
			if( $step == 5 ) $difference_perc = $amount_used / $this->total_amount;
			$difference = $this->total_amount - $amount_used;
			$good = abs($this->total_amount - $amount_used) < $epsilon;
			$messageStack->debug("\n contiune = ".$good." total_amount = ".$this->total_amount .' amount_used = '.$amount_used .' difference percent = '.$difference_perc);
			$step++;
		}
		if ($good == false){
			if(sizeof($found_invoices) != 0 ){
				$messageStack->debug("\n Found invoices but total amounts didn't align. ");
				$this->journal_rows = null;
				$amount_used = 0;
			}
			$messageStack->debug("\n posting payment as a deposit ");
			$this->journal_rows[] = array(
				'so_po_item_ref_id'		=> '', 	
				'gl_type'               => GL_TYPE,
				'gl_account'            => $gl_acct_id,
				'serialize_number'      => '',
				'post_date'             => $this->post_date,
				'debit_amount'     		=> $this->_debitamount,	
				'credit_amount'			=> $this->_creditamount,
		  		'description'    		=> $this->_description,
			);
			$this->waiting = 1;
			
		}else{ 
			for($i=0; $i < sizeof($found_invoices); $i++){
				$this->open_inv[$this->bill_acct_id][$found_invoices[$i]['purchase_invoice_id']]['amount_paid'] += $found_invoices[$i]['amount'];
				if($found_invoices[$i]['payed_if_full']){ 
					$messageStack->debug("\n unsetting invoice ".arr2string($this->open_inv[$this->bill_acct_id][$found_invoices[$i]['purchase_invoice_id']]));
					unset($this->open_inv[$this->bill_acct_id][$found_invoices[$i]['purchase_invoice_id']]);
				}
			}
			$messageStack->debug("\n posting payment to invoices ".arr2string($found_invoices));
		}
		$this->journal_rows[] = array(	
			'gl_type'               => 'ttl',
			'gl_account'            => $this->gl_acct_id,
			'post_date'             => $this->post_date,
			'debit_amount'     		=> $this->_creditamount,	
			'credit_amount'			=> $this->_debitamount,
		  	'description'    		=> $this->_description,	
		);
		$this->journal_main_array = $this->build_journal_main_array();
		$this->validate_purchase_invoice_id();
		$this->Post('insert', true);
		$this->increment_purchase_invoice_id();	
		if ($good == false){ // make credit inv
			$messageStack->debug("\n Making credit invoice. ");
			$this->journal_main_array   = null;
			$this->journal_id          	= ($this->_firstjid == 20) ? 7 : 13;  
			$this->gl_acct_id          	= $gl_acct_id;
			$this->id				 	= null;
			$this->description			= GENERAL_JOURNAL_7_DESC;
			$this->purchase_invoice_id  = '';
			$this->journal_rows 		= null;
			$this->journal_rows[] = array(
				'gl_type'               => 'por',
				'gl_account'            => $gl_acct_id,
				'post_date'             => $this->post_date,
				'debit_amount'     		=> $this->_debitamount,	
				'credit_amount'			=> $this->_creditamount,	
			);
			$this->journal_rows[] = array(	
				'id'					=> '',
				'gl_type'               => 'ttl',
				'gl_account'            => $this->gl_acct_id,
				'post_date'             => $this->post_date,
				'debit_amount'     		=> $this->_creditamount,	
				'credit_amount'			=> $this->_debitamount,
			  	'description'    		=> $this->_description,	
			);
			$this->journal_main_array = $this->build_journal_main_array();
			$this->validate_purchase_invoice_id();
			$this->Post('insert',true);
			$this->increment_purchase_invoice_id();
		}	
		$this->_succes = true;
		return true;
	}
	
	private function proces_mutation(){
		global $db, $messageStack;
		$messageStack->debug("\n this wil be posted as a journal ");
		$sql ="select gl_account from " . TABLE_JOURNAL_ITEM. " where description = '".$this->_description."' and not gl_account='".$this->gl_acct_id."' and not gl_account='".$this->_questionposts."'";
		$result = $db->Execute($sql);
		$gl_account = $this->_questionposts;
		If(!$result->RecordCount() == 0){
			$result->EOF;
			if(!$result->fields['gl_acount'] == ''){
				$gl_account = $result->fields['gl_acount'];
			}
		}
		$this->id					= '';
		$this->journal_id 			= 2;
		$this->description			= '';
		$this->journal_rows[] = array(
			'gl_account'            => $gl_account,
			'post_date'             => $this->post_date,
			'debit_amount'     		=> $this->_debitamount,	
			'credit_amount'			=> $this->_creditamount,
		  	'description'    		=> $this->_description,	
		);
		$this->journal_rows[] = array(	
			'gl_account'            => $this->gl_acct_id,
			'post_date'             => $this->post_date,
			'debit_amount'     		=> $this->_creditamount,	
			'credit_amount'			=> $this->_debitamount,
		  	'description'    		=> $this->_description,	
		);
		$this->journal_main_array = $this->build_journal_main_array();
		if(!$this->Post('insert', true)){
			$messageStack->debug("\n unable to post the journal e error was returned by the class journal");
			return false;
			exit;	
		}
		$this->_succes = true;
	}
	
	private function proces_know_mutation($other_bank_account_number, $other_bank_account_iban){
		global $db, $messageStack;
		$messageStack->debug("\n we start looking for a match with a know bank account in proces_know_mutation.\n where bank_account = '".$other_bank_account_number."' or bank_iban = '".$other_bank_account_iban );
		if (isset($this->known_trans[$other_bank_account_iban])){
			$temp = $other_bank_account_iban;
		}elseif (isset($this->known_trans[$other_bank_account_number])){
			$temp = $other_bank_account_number;
		}else{
			$messageStack->debug("\n couldn't find a match with a know bank account.");
			return false;
		}
		if($this->known_trans[$temp]['gl_acct_id'] == ''){
			$messageStack->debug("\n the gl_acct_id is empty.");
			return false;
		}
		$this->id					= '';
		$this->journal_id 			= 2;
		$this->description			= $this->known_trans[$temp]['description'];
		$this->journal_rows[] = array(
			'gl_account'            => $this->known_trans[$temp]['gl_acct_id'],
			'post_date'             => $this->post_date,
			'debit_amount'     		=> $this->_debitamount,	
			'credit_amount'			=> $this->_creditamount,
		  	'description'    		=> $this->known_trans[$temp]['description'],	
		);
		$this->journal_rows[] = array(	
			'gl_account'            => $this->gl_acct_id,
			'post_date'             => $this->post_date,
			'debit_amount'     		=> $this->_creditamount,	
			'credit_amount'			=> $this->_debitamount,
		  	'description'    		=> $this->_description,	
		);
		$this->journal_main_array = $this->build_journal_main_array();
		if(!$this->Post('insert',true)){
			$this->journal_rows = null;
			return false;
		}
		$this->_succes = true;
		return true;
	}
	
	private function unknown_contact($other_bank_account_number, $other_bank_account_iban){
		global $messageStack;
		if (isset($this->known_trans[$other_bank_account_iban]))   return false;
		if (isset($this->known_trans[$other_bank_account_number])) return false;
		//looking if it is a new contact
		$messageStack->debug("\n start looking for unknown match");
		foreach ($this->open_inv as $contact_id => $contact) {
			//when we find a invoice we book a payment to it
			foreach ($contact as $invoice_id => $invoice) {
				if( strripos($this->_description, $invoice['purchase_invoice_id']) !== false){ // if invoice is part of the description use it
					$messageStack->debug("\n Found contact by invoice_nr in description. found contact ".$contact_id);
					if($other_bank_account_number) $messageStack->add(sprintf(TEXT_NEW_BANK, $other_bank_account_number, $invoice['short_name']), 'caution'); 
					if($other_bank_account_iban)   $messageStack->add(sprintf(TEXT_NEW_IBAN, $other_bank_account_iban,   $invoice['short_name']), 'caution');
					return array('id' => $contact_id,'type' => $invoice['type']);
				}				
			}
		}
		return false;
	}

	private function get_known_trans(){
		global $db, $messageStack;
		$result = $db->Execute("select * from " . TABLE_IMPORT_BANK);
	 	while(!$result->EOF){
    		$this->known_trans[$result->fields['bank_account']] = array(
    			'gl_acct_id'  => $result->fields['gl_acct_id'], 
    			'description' => $result->fields['description']
    		);
    		$result->MoveNext();
    	}
	}
	
	private function get_all_open_invoices(){
		// to build this data array, all current open invoices need to be gathered and then the paid part needs
		// to be applied along with discounts taken by row.
		global $db, $currencies;
		$sql = "select m.id as id, m.journal_id as journal_id, m.post_date as post_date, m.terms as terms, m.purch_order_id as purch_order_id,
		 m.purchase_invoice_id as purchase_invoice_id, m.total_amount as total_amount, m.gl_acct_id as gl_acct_id, m.bill_acct_id as bill_acct_id, c.type as type, c.short_name as short_name  
		  from " . TABLE_JOURNAL_MAIN . " m join ".TABLE_CONTACTS." c on m.bill_acct_id = c.id
		  where journal_id in (6, 7, 12, 13) and closed = '0'";
		$sql .= " order by m.post_date";
		$result = $db->Execute($sql);
		$open_invoices = array();
		while (!$result->EOF) {
			if ($result->fields['journal_id'] == 7 || $result->fields['journal_id'] == 13) {
				 $result->fields['total_amount'] = -$result->fields['total_amount'];
			}
			$result->fields['total_amount'] -= fetch_partially_paid($result->fields['id']);
			$result->fields['description']   = $result->fields['purch_order_id'];
			$result->fields['discount']      = '';
			$result->fields['amount_paid']   = '';
			$open_invoices[$result->fields['id']] = $result->fields;
			$result->MoveNext();
		}
		ksort($open_invoices);
		$balance   = 0;
		$index     = 0;
		
		foreach ($open_invoices as $key => $line_item) {
			// fetch some information about the invoice
		  	$sql = "select id, post_date, terms, purchase_invoice_id, purch_order_id, gl_acct_id, waiting  
				from " . TABLE_JOURNAL_MAIN . " where id = " . $key;
		  	$result = $db->Execute($sql);
		  	$due_dates = calculate_terms_due_dates($result->fields['post_date'], $result->fields['terms'], ($type == 'v' ? 'AP' : 'AR'));
		  	$negate = (($line_item['journal_id'] == 20 && $line_item['type'] == 'c') || ($line_item['journal_id'] == 18 && $line_item['type'] == 'v')) ? true : false;
		  	if ($negate) {
		    	$line_item['total_amount'] = -$line_item['total_amount'];
		    	$line_item['discount']     = -$line_item['discount'];
		    	$line_item['amount_paid']  = -$line_item['amount_paid'];
		  	}
		  	$balance += $line_item['total_amount'];
		  	$this->open_inv[$line_item['bill_acct_id']][$result->fields['id']] = array(
				'id'                  => $result->fields['id'],
				'waiting'             => $result->fields['waiting'],
				'purchase_invoice_id' => $result->fields['purchase_invoice_id'],
				'purch_order_id'      => $result->fields['purch_order_id'],
				'percent'             => $due_dates['discount'],
				'post_date'           => $result->fields['post_date'],
				'early_date'          => $due_dates['early_date'],
				'net_date'            => $due_dates['net_date'],
				'total_amount'        => round($line_item['total_amount'],  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']),
				'gl_acct_id'          => $result->fields['gl_acct_id'],
				'description'         => $line_item['description'],
		  		'type'		          => $line_item['type'],
		  		'short_name'		  => $line_item['short_name'],
		  		'discount'            => round($line_item['discount'],  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']), 
				'amount_paid'         => round($line_item['amount_paid'],  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']), 
		  	);
		  	$index++;
		}
		
	}
	
	private function reset(){
		$this->_succes 				= false;
	 	$this->journal_rows 		= null;
	 	$this->journal_main_array 	= null;
	 	$this->description 			= null;
	 	$this->purchase_invoice_id 	= '';
		$this->id					= null;
		$this->period				= null;
		$this->journal_id			= null;
		$this->post_date			= null;
		$this->store_id				= null;
		$this->closed				= null;
		$this->closed_date			= null;
		$this->freight				= null;
		$this->discount				= null;
		$this->shipper_code			= null;
		$this->terms				= null;
		$this->sales_tax			= null;
		$this->total_amount			= null;
		$this->currencies_code		= null;
		$this->currencies_value		= null;
		$this->so_po_ref_id			= null;
		$this->purch_order_id		= null;
		$this->admin_id				= null;
		$this->rep_id				= null;
		$this->waiting				= null;
		$this->gl_acct_id			= null;
		$this->bill_acct_id			= null;
		$this->bill_address_id		= null;
		$this->bill_primary_name	= null;
		$this->bill_contact			= null;
		$this->bill_address1		= null;
		$this->bill_address2		= null;
		$this->bill_city_town		= null;
		$this->bill_state_province	= null;
		$this->bill_postal_code		= null;
		$this->bill_country_code	= null;
		$this->bill_telephone1		= null;
		$this->bill_email			= null;
		$this->recur_id				= null;
	}
	
	public function __destruct(){
		global $messageStack;
		$messageStack->debug("\n\n*************** end Import Payment Class*******************");
		//print_r($this);
	}
}