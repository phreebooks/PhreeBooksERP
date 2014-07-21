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
//  Path: /modules/phreebooks/classes/orders.php
//

class orders extends journal {
 var $closed = '0';
	
  function __construct() {
	$this->journal_id          = JOURNAL_ID;
	$this->gl_type             = GL_TYPE;
	$this->gl_acct_id          = DEF_GL_ACCT;
	$this->currencies_code     = DEFAULT_CURRENCY;
	$this->currencies_value    = '1.0';
	$this->bill_primary_name   = GEN_PRIMARY_NAME;
	$this->bill_contact        = GEN_CONTACT;
	$this->bill_address1       = GEN_ADDRESS1;
	$this->bill_address2       = GEN_ADDRESS2;
	$this->bill_city_town      = GEN_CITY_TOWN;
	$this->bill_state_province = GEN_STATE_PROVINCE;
	$this->bill_postal_code    = GEN_POSTAL_CODE;
	$this->bill_country_code   = COMPANY_COUNTRY;
	// shipping defaults
	$this->ship_short_name     = '';
	$this->ship_add_update     = 0;
	$this->ship_acct_id        = 0;
	$this->ship_address_id     = 0;
	$this->ship_country_code   = COMPANY_COUNTRY;
	$this->shipper_code        = '';
	$this->drop_ship           = 0;
	$this->freight             = 0;
	switch ($this->journal_id) { // default to company data for purchases/PO's
	  case  3:
	  case  4:
	  case  6:
	  case  7:
		$this->disc_gl_acct_id     = AP_DISCOUNT_PURCHASE_ACCOUNT;
		$this->ship_gl_acct_id     = AP_DEF_FREIGHT_ACCT;
		$this->ship_primary_name   = COMPANY_NAME;
		$this->ship_contact        = AP_CONTACT_NAME;
		$this->ship_address1       = COMPANY_ADDRESS1;
		$this->ship_address2       = COMPANY_ADDRESS2;
		$this->ship_city_town      = COMPANY_CITY_TOWN;
		$this->ship_state_province = COMPANY_ZONE;
		$this->ship_postal_code    = COMPANY_POSTAL_CODE;
		$this->ship_telephone1     = COMPANY_TELEPHONE1;
		$this->ship_email          = COMPANY_EMAIL;
		break;
	  case  9:
	  case 10:
	  case 12:
	  case 13:
		$this->disc_gl_acct_id     = AR_DISCOUNT_SALES_ACCOUNT;
		$this->ship_gl_acct_id     = AR_DEF_FREIGHT_ACCT;
		$this->ship_primary_name   = GEN_PRIMARY_NAME;
		$this->ship_contact        = GEN_CONTACT;
		$this->ship_address1       = GEN_ADDRESS1;
		$this->ship_address2       = GEN_ADDRESS2;
		$this->ship_city_town      = GEN_CITY_TOWN;
		$this->ship_state_province = GEN_STATE_PROVINCE;
		$this->ship_postal_code    = GEN_POSTAL_CODE;
		$this->ship_telephone1     = GEN_TELEPHONE1;
		$this->ship_email          = GEN_EMAIL;
		break;
	  default:
	}
  }

  function post_ordr($action) {
	global $db, $messageStack;
	$this->journal_rows = array();	// initialize ledger row(s) array
	$debit_total  = 0;
	$credit_total = 0;
	switch ($this->journal_id) { // THE SEQUENCE IS IMPORTANT!
	  case  6: // Purchase/Receive Journal
	  case 13: // Customer Credit Memo Journal
		$this->closed = 0; // force the inv receipt/vcm open since it will be closed by the system, if necessary
		// continue like other payable prep
	  case  3: // Purchase Quote Journal
	  case  4: // Purchase Order Journal
	  case 21: // Inventory Direct Purchase Journal (POP)
		$debit_total  += $this->add_item_journal_rows('debit');	// read in line items and add to journal row array
		$debit_total  += $this->add_freight_journal_row('debit');	// put freight into journal row array
		$debit_total  += $this->add_tax_journal_rows('debit');	// fetch tax rates for tax calculation
		$credit_total += $this->add_discount_journal_row('credit'); // put discount into journal row array
		$this->total_amount = $debit_total - $credit_total;
		$credit_total += $this->add_total_journal_row('credit');	// put total value into ledger row array
		break;
	  case  7: // Vendor Credit Memo Journal
	  case 12: // Sales/Invoice Journal (Invoice)
		$this->closed = 0; // force the inv/cm open since it will be closed by the system, if necessary
		// continue like other receivable prep
	  case  9: // Sales Quote Journal
	  case 10: // Sales Order Journal
	  case 19: // POS Journal
		$credit_total += $this->add_item_journal_rows('credit'); // read in line items and add to journal row array
		$credit_total += $this->add_freight_journal_row('credit');	// put freight into journal row array
		$credit_total += $this->add_tax_journal_rows('credit');	// fetch tax rates for tax calculation
		$debit_total  += $this->add_discount_journal_row('debit'); // put discount into journal row array
		$this->total_amount = $credit_total - $debit_total;
		$debit_total  += $this->add_total_journal_row('debit');	// put total value into ledger row array
		break;
	  default: return $this->fail_message('bad journal_id in pre-POST processing'); // this should never happen, JOURNAL_ID is tested at script entry!
	}
	$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record

	// ***************************** START TRANSACTION *******************************
	$messageStack->debug("\n  started order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id);
	$db->transStart();
	// *************  Pre-POST processing *************
	// add/update address book
	if ($this->bill_add_update) { // billing address
	  $this->bill_acct_id = $this->add_account($this->account_type . 'b', $this->bill_acct_id, $this->bill_address_id);
	  if (!$this->bill_acct_id) return false;
	}
	if ($this->ship_add_update) { // shipping address
	  if (!$this->ship_acct_id) $this->ship_acct_id = $this->bill_acct_id; // set to bill if adding contact and id not set.
	  if ($this->bill_address_id == $this->ship_address_id) $this->ship_address_id = ''; // force create new ship address, here from copy button
	  $this->ship_acct_id = $this->add_account($this->account_type . 's', $this->ship_acct_id, $this->ship_address_id);
	  if (!$this->ship_acct_id) return false;
	}
	// set the ship account id to bill account id if null (new accounts with ship update box not checked)
	// basically defaults the shipping same as billing if not specified
	if (!$this->journal_main_array['ship_acct_id'])    $this->journal_main_array['ship_acct_id']    = $this->journal_main_array['bill_acct_id'];
	if (!$this->journal_main_array['ship_address_id']) $this->journal_main_array['ship_address_id'] = $this->journal_main_array['bill_address_id'];

	// ************* POST journal entry *************
	if ($this->recur_id > 0) { // if new record, will contain count, if edit will contain recur_id
	  $first_id                  = $this->id;
	  $first_post_date           = $this->post_date;
	  $first_purchase_invoice_id = $this->purchase_invoice_id;
	  $first_terminal_date       = $this->first_terminal_date;
	  if ($this->id) { // it's an edit, fetch list of affected records to update if roll is enabled
		$affected_ids = $this->get_recur_ids($this->recur_id, $this->id);
		for ($i = 0; $i < count($affected_ids); $i++) {
		  $this->id = $affected_ids[$i]['id'];
		  $this->journal_main_array['id'] = $affected_ids[$i]['id'];
		  $this->remove_cogs_rows();
		  if ($i > 0) { // Remove row id's for future posts, keep if re-posting single entry
			for ($j = 0; $j < count($this->journal_rows); $j++) $this->journal_rows[$j]['id'] = '';
			$this->post_date           = $affected_ids[$i]['post_date'];
			$this->terminal_date       = $affected_ids[$i]['terminal_date'];
		    $this->purchase_invoice_id = $affected_ids[$i]['purchase_invoice_id'];
		  } else { // for first entry, post date may be changed, use $_POST value
			$this->post_date           = $first_post_date;
			$this->terminal_date       = $first_terminal_date;
			$this->purchase_invoice_id = $first_purchase_invoice_id;
		  }
		  $this->period        = gen_calculate_period($this->post_date, true);
		  $this->journal_main_array['post_date']     = $this->post_date;
		  $this->journal_main_array['period']        = $this->period;
		  $this->journal_main_array['terminal_date'] = $this->terminal_date;
		  if (!$this->validate_purchase_invoice_id()) return false;
		  $messageStack->debug("\n\n  re-posting recur id = " . $this->id);
		  if (!$this->Post('edit')) return false;
		  // test for single post versus rolling into future posts, terminate loop if single post
		  if (!$this->recur_frequency) break;
		}
	  } else { // it's an insert
		// fetch the next recur id
		$this->journal_main_array['recur_id'] = time();
		$day_offset   = 0;
		$month_offset = 0;
		$year_offset  = 0;
		$post_date    = $this->post_date;
		for ($i = 0; $i < $this->recur_id; $i++) {
		  if (!$this->validate_purchase_invoice_id()) return false;
		  if (!$this->Post('insert')) return false;
		  $this->id = '';
		  $this->journal_main_array['id'] = $this->id;
		  $this->remove_cogs_rows();
		  for ($j = 0; $j < count($this->journal_rows); $j++) $this->journal_rows[$j]['id'] = '';
		  switch ($this->recur_frequency) {
			default:
			case '1': $day_offset   = ($i+1)*7;  break; // Weekly
			case '2': $day_offset   = ($i+1)*14; break; // Bi-weekly
			case '3': $month_offset = ($i+1)*1;  break; // Monthly
			case '4': $month_offset = ($i+1)*3;  break; // Quarterly
			case '5': $year_offset  = ($i+1)*1;  break; // Yearly
		  }
		  $this->post_date     = gen_specific_date($post_date, $day_offset, $month_offset, $year_offset);
		  if ($this->terminal_date) $this->terminal_date = gen_specific_date($this->terminal_date, $day_offset, $month_offset, $year_offset);
		  $this->period        = gen_calculate_period($this->post_date, true);
		  if (!$this->period && $i < ($this->recur_id - 1)) { // recur falls outside of available periods, ignore last calculation
		    return $this->fail_message(ORD_PAST_LAST_PERIOD);
		  }
		  $this->journal_main_array['post_date']     = $this->post_date;
		  $this->journal_main_array['period']        = $this->period;
		  $this->journal_main_array['terminal_date'] = $this->terminal_date;
		  if (in_array($this->journal_id, array(4, 10, 12, 19)) && $first_purchase_invoice_id == '') {
			$this->increment_purchase_invoice_id(true);
		  }
		  $this->purchase_invoice_id = string_increment($this->journal_main_array['purchase_invoice_id']);					
		}
	  }
	  // restore the first values to continue with post process
	  if (in_array($this->journal_id, array(4, 10, 12, 19)) && $first_purchase_invoice_id == '') { // special case for auto increment 
		$first_purchase_invoice_id = $this->purchase_invoice_id;
	  }
	  $this->id                  = $first_id;
	  $this->post_date           = $first_post_date;
	  $this->purchase_invoice_id = $first_purchase_invoice_id;
	  $this->terminal_date       = $first_terminal_date;
	  $this->journal_main_array['id']                  = $first_id;
	  $this->journal_main_array['post_date']           = $first_post_date;
	  $this->journal_main_array['purchase_invoice_id'] = $first_purchase_invoice_id;
	  $this->journal_main_array['terminal_date']       = $first_terminal_date;
	} else {
	  if (!$this->validate_purchase_invoice_id()) return false;
	  if (!$this->Post($this->id ? 'edit' : 'insert')) return false;
	}
	// ************* post-POST processing *************
	switch ($this->journal_id) {
	  case  3: // Purchase Quote Journal
	  case  4: // Purchase Order Journal
	  case  7: // Vendor Credit Memo Journal
	  case  9: // Sales Quote Journal
	  case 10: // Sales Order Journal
	  case 12: // Sales/Invoice Journal
	  case 13: // Customer Credit Memo Journal
		if ($this->purchase_invoice_id == '') {	// it's a new record, increment the po/so/inv to next number
			if (!$this->increment_purchase_invoice_id()) return false;
		}
		break;
	  case  6: // Purchase Journal
	  default:
		break;
	}
	$messageStack->debug("\n  committed order post purchase_invoice_id = " . $this->purchase_invoice_id . " and id = " . $this->id);
	$db->transCommit();	// finished successfully
//echo 'committed transaction - bailing!'; exit();
	// ***************************** END TRANSACTION *******************************
	$messageStack->add(sprintf(TEXT_SUCCESSFULLY_POSTED, constant('ORD_HEADING_NUMBER_' . $this->journal_id) . ' ' . $this->purchase_invoice_id), 'success');
	return true;
  }

  function delete_ordr() {
	global $db, $messageStack;
	// verify no item rows have been acted upon (received, shipped, paid, etc.)
	switch ($this->journal_id) {
	  case  4: // Purchase Order Journal
	  case 10: // Sales Order Journal
		$result = $db->Execute("select id from " . TABLE_JOURNAL_MAIN . " where so_po_ref_id = " . $this->id);
		if ($result->RecordCount() > 0) return $this->fail_message(constant('GENERAL_JOURNAL_' . $this->journal_id . '_ERROR_6'));
		break;
	  case  6: // Purchase Journal
	  case  7: // Vendor Credit Memo Journal
	  case 12: // Sales/Invoice Journal
	  case 13: // Customer Credit Memo Journal
		// first check for main entries that refer to delete id (credit memos)
		$result = $db->Execute("select id from " . TABLE_JOURNAL_MAIN . " where so_po_ref_id = " . $this->id);
		if ($result->RecordCount() > 0) return $this->fail_message(constant('GENERAL_JOURNAL_' . $this->journal_id . '_ERROR_6'));
		// next check for payments that link to deleted id (payments)
		$result = $db->Execute("select id from " . TABLE_JOURNAL_ITEM . " 
			where gl_type = 'pmt' and so_po_item_ref_id = " . $this->id);
		if ($result->RecordCount() > 0) return $this->fail_message(constant('GENERAL_JOURNAL_' . $this->journal_id . '_ERROR_6'));
		break;
	  case  3: // Purchase Quote Journal
	  case  9: // Sales Quote Journal
	  default:
	}
	// *************** START TRANSACTION *************************
	$recur_id        = $this->recur_id;
	$recur_frequency = $this->recur_frequency;
	$db->transStart();
	if ($recur_id > 0) { // will contain recur_id
	  $affected_ids = $this->get_recur_ids($recur_id, $this->id);
	  for ($i = 0; $i < count($affected_ids); $i++) {
		$this->id = $affected_ids[$i]['id'];
		$this->journal($this->id); // load the posted record based on the id submitted
		if (!$this->unPost('delete')) return false;
		// test for single post versus rolling into future posts, terminate loop if single post
		if (!$recur_frequency) break;
	  }
	} else {
	  if (!$this->unPost('delete')) return false;
	}
	$db->transCommit();
	// *************** END TRANSACTION *************************
	return true;
  }

  function add_total_journal_row($debit_credit) {	// put total value into ledger row array
	if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  $this->journal_rows[] = array( // record for accounts receivable
		'gl_type'                 => 'ttl',
		$debit_credit . '_amount' => $this->total_amount,
		'description'             => constant('ORD_TEXT_' . $this->journal_id . '_WINDOW_TITLE') . '-' . TEXT_TOTAL,
		'gl_account'              => $this->gl_acct_id,
		'post_date'               => $this->post_date,
	  );
	  return $this->total_amount;
	} else {
	  die('bad parameter passed to add_total_journal_row in class orders');
	}
  }

  function add_discount_journal_row($debit_credit) { // put discount into journal row array
    if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  if ($this->discount <> 0) {
	    $this->journal_rows[] = array(
		  'qty'                     => '1',
		  'gl_type'                 => 'dsc',		// code for discount charges
		  $debit_credit . '_amount' => $this->discount,
		  'description'             => constant('ORD_TEXT_' . $this->journal_id . '_WINDOW_TITLE') . '-' . TEXT_DISCOUNT,
		  'gl_account'              => $this->disc_gl_acct_id,
		  'taxable'                 => '0',
		  'post_date'               => $this->post_date,
	    );
	  }
	  return $this->discount;
    } else {
	  die('bad parameter passed to add_discount_journal_row in class orders.');
    }
  }

  function add_freight_journal_row($debit_credit) {	// put freight into journal row array
    if ($debit_credit == 'debit' || $debit_credit == 'credit') {
      // if no line items are charged tax, do not charge tax on shipping. ADDED 2014-04-28 by Dave
      $tax_freight = false;
      foreach ($this->journal_rows as $line_item) {
    	if ($line_item['taxable'] > 0 && $line_item['gl_type'] == $this->gl_type) $tax_freight = true;
      }

      switch ($this->journal_id) {
	    case  3: 
	    case  4:
	    case  6:
	    case  7: $freight_tax_id = $tax_freight ? AP_ADD_SALES_TAX_TO_SHIPPING : 0; break;
	    case  9:
	    case 10: 
	    case 12:
	    case 13: $freight_tax_id = $tax_freight ? AR_ADD_SALES_TAX_TO_SHIPPING : 0; break;
	  }	   
	  if ($this->freight) { // calculate freight charges
		$this->journal_rows[] = array(
		  'qty'                     => '1',
		  'gl_type'                 => 'frt',		// code for shipping/freight charges
		  $debit_credit . '_amount' => $this->freight,
		  'description'             => constant('ORD_TEXT_' . $this->journal_id . '_WINDOW_TITLE') . '-' . TEXT_SHIPPING,
		  'gl_account'              => $this->ship_gl_acct_id,
		  'taxable'                 => $freight_tax_id,
		  'post_date'               => $this->post_date,
		);
	  }
	  return $this->freight;
    } else {
	  die('bad parameter passed to add_freight_journal_row in class orders');
    }
  }

  function add_item_journal_rows($debit_credit) {	// read in line items and add to journal row array
	if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  $total = 0;
	  for ($i=0; $i<count($this->item_rows); $i++) {
		switch ($this->journal_id) { // determine to pick from the qty or pstd value 
		  case  3: 
		  case  4:
			$qty_pstd = 'qty';
			$terminal_date = gen_specific_date($this->post_date, $this->item_rows[$i]['lead_time']);
			break;
		  case  9:
		  case 10: 
			$qty_pstd = 'qty';
			$terminal_date = $this->terminal_date;
			break;
		  case  6:
		  case  7:
			$qty_pstd = 'pstd';
			$terminal_date = $this->post_date;
			break;
		  case 12:
		  case 13:
			$qty_pstd = 'pstd';
			$terminal_date = $this->terminal_date;
			break;
		  default:
		}
		if ($this->item_rows[$i][$qty_pstd]) { // make sure the quantity line is set and not zero
		  $this->journal_rows[] = array(
			'id'                      => $this->item_rows[$i]['id'],	// retain the db id (used for updates)
			'item_cnt'                => $this->item_rows[$i]['item_cnt'],
		    'so_po_item_ref_id'       => $this->item_rows[$i]['so_po_item_ref_id'],	// item reference id for so/po line items
			'gl_type'                 => $this->gl_type,
			'sku'                     => $this->item_rows[$i]['sku'],
			'qty'                     => $this->item_rows[$i][$qty_pstd],
			'description'             => $this->item_rows[$i]['desc'],
			$debit_credit . '_amount' => $this->item_rows[$i]['total'],
			'full_price'              => $this->item_rows[$i]['full'],
			'gl_account'              => $this->item_rows[$i]['acct'],
			'taxable'                 => $this->item_rows[$i]['tax'],
			'serialize_number'        => $this->item_rows[$i]['serial'],
			'project_id'              => $this->item_rows[$i]['proj'],
			'purch_package_quantity'  => $this->item_rows[$i]['purch_package_quantity'],
			'post_date'               => $this->post_date,
			'date_1'                  => $this->item_rows[$i]['date_1'] ? $this->item_rows[$i]['date_1'] : $terminal_date,
		  );
		  $total += $this->item_rows[$i]['total'];
		}
	  }
	  return $total;
	} else {
	  die('Bad parameter passed to add_item_journal_rows in class orders!');
	}
  }

  function add_tax_journal_rows($debit_credit) {
    global $currencies;
	if ($debit_credit == 'debit' || $debit_credit == 'credit') {
	  $total          = 0;
	  $auth_array     = array();
	  $tax_rates      = ord_calculate_tax_drop_down('b');
	  $tax_auths      = gen_build_tax_auth_array();
	  $tax_discount   = $this->account_type == 'v' ? AP_TAX_BEFORE_DISCOUNT : AR_TAX_BEFORE_DISCOUNT;
	  // calculate each tax value by authority per line item
	  foreach ($this->journal_rows as $idx => $line_item) {
		if ($line_item['taxable'] > 0 && ($line_item['gl_type'] == $this->gl_type || $line_item['gl_type'] == 'frt')) {
		  foreach ($tax_rates as $rate) {
			if ($rate['id'] == $line_item['taxable']) {
			  $auths = explode(':', $rate['auths']);
		      foreach ($auths as $auth) {
				$line_total = $line_item['debit_amount'] + $line_item['credit_amount']; // one will always be zero
			    if (ENABLE_ORDER_DISCOUNT && $tax_discount == '0' && $line_item['gl_type'] <> 'frt') {
				  $line_total = $line_total * (1 - $this->disc_percent);
				}
//				this is wrong this is rounding per orderline not per tax auth. moved this to the next foreach.		
//				if (ROUND_TAX_BY_AUTH) {
//				  $auth_array[$auth] += number_format(($tax_auths[$auth]['tax_rate'] / 100) * $line_total, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places'], '.', '');
//				} else {
				  $auth_array[$auth] += ($tax_auths[$auth]['tax_rate'] / 100) * $line_total;
//				}
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
		  $debit_credit . '_amount' => $amount,
		  'description'             => $tax_auths[$auth]['description_short'],
		  'gl_account'              => $tax_auths[$auth]['account_id'],
		  'post_date'               => $this->post_date,
		);
		$total += $amount;
	  }
	  $this->sales_tax = $total;
	  return $total;
	} else {
	  die('bad parameter passed to add_tax_journal_rows in class orders');
	}
  }

}
?>