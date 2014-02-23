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
//  Path: /modules/phreepos/custom/classes/pos_builder.php
//
namespace phreepos\classes;
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');

class pos_builder {
	public $discount 		= 0;
	public $taxes    		= array();
	public $payment_rows	= array();
	public $line_items		= array();
	
  function __construct() {
	$taxes = ord_calculate_tax_drop_down('c');
	foreach ($taxes as $rate) $this->taxes[$rate['id']] = $rate['rate']/100;
  }

  function load_query_results($tableKey = 'id', $tableValue = 0) {
	global $db, $report, $FieldListings;
	if (!$tableValue) return false;
	$sql = "select * from " . TABLE_JOURNAL_MAIN . " where id = " . $tableValue;
	$result = $db->Execute($sql);
	while (list($key, $value) = each($result->fields)) $this->$key = db_prepare_input($value);
	$this->load_item_details($this->id);
	$this->load_payment_details($this->id);
	$this->load_account_details($this->bill_acct_id);
	// convert particular values indexed by id to common name
	if ($this->rep_id) {
	  $sql = "select short_name, contact_first, contact_last from " . TABLE_CONTACTS . " where id = " . $this->rep_id;
	  $result = $db->Execute($sql);
	  $this->rep_id   = $result->fields['short_name'] ;
	  $this->rep_name = $result->fields['contact_first'] . ' ' . $result->fields['contact_last'];
	} else {
	  $this->rep_id   = '';
	  $this->rep_name = '';
	}
	$terms_date = calculate_terms_due_dates($this->post_date, $this->terms);
	$this->payment_due_date = $terms_date['net_date'];
//	$this->tax_authorities  = 'tax_auths';
	$this->balance_due      = $this->total_amount - $this->total_paid;
	$this->rounded_of       = $this->total_amount - ($this->inv_subtotal_w_tax - $this->discount) ;
	// sequence the results per Prefs[Seq]
	$output = array();
	foreach ($report->fieldlist as $OneField) { // check for a data field and build sql field list
	  if ($OneField->type == 'Data') { // then it's data field, include it
		$field = $OneField->boxfield[0]->fieldname;
		$output[] = $this->$field;
	  }
	}
	// return results
//echo 'line items = '; print_r($this->line_items); echo '<br />';
	return $output;
  }

  function load_table_data($fields = '') {
	// fill the return data array
	$output = array();
	if (is_array($this->line_items) && is_array($fields)) {
	  foreach ($this->line_items as $key => $row) {
		if (!isset($row['invoice_qty'])) continue; // skip SO lines that are not on this invoice
		$row_data = array();
		foreach ($fields as $idx => $element) {
		  $row_data['r' . $idx] = $this->line_items[$key][$element->fieldname];
		}
		$output[] = $row_data;
	  }
	}
  	if (is_array($this->payment_rows) && is_array($fields)) {
	  foreach ($this->payment_rows as $key => $row) {
		if (!isset($row['payment_amount'])) continue; // skip SO lines that are not on this invoice
		$row_data = array();
		foreach ($fields as $idx => $element) {
		  $row_data['r' . $idx] = $this->payment_rows[$key][$element->fieldname];
		}
		$output[] = $row_data;
	  }
	}
	return $output;
  }

  function load_total_results($Params) {
	$total = '';
	if (is_array($Params['Seq'])) {
	  foreach ($Params['Seq'] as $field) $total += $this->$field;
	}
	return $total;
  }

  function load_text_block_data($Params) {
	$TextField = '';
	foreach($Params as $Temp) {
	  $fieldname  = $Temp->fieldname;
      $temp = $Temp->formatting ? ProcessData($this->$fieldname, $Temp->formatting) : $this->$fieldname;
      $TextField .= AddSep($temp, $Temp->processing);
	}
	return $TextField;
  }

  function load_item_details($id) {
	global $db, $currencies;
	// fetch the sales order and build the item list
	$this->invoice_subtotal = 0;
	$tax_list = array();
	$sql = "select * from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id;
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  $index    = ($result->fields['so_po_item_ref_id']) ? $result->fields['so_po_item_ref_id'] : $result->fields['id'];
	  $price    = $result->fields['credit_amount'] + $result->fields['debit_amount'];
	  $line_tax = $this->taxes[$result->fields['taxable']];
	  switch ($result->fields['gl_type']){
	  	case 'sos':
	  	case 'por':
			$this->line_items[$index]['invoice_full_price']  = $result->fields['full_price'];
			$this->line_items[$index]['invoice_unit_price']  = $result->fields['qty'] ? ($price / $result->fields['qty']) : 0;
			if ($result->fields['full_price'] != 0 && $this->line_items[$index]['invoice_unit_price'] < $result->fields['full_price']){ 
				$discount    = round(-(1-($price / $result->fields['qty']) / $result->fields['full_price']) * 100,1) .'%';
			}else{
				$discount    = '' ;
			}
			$this->line_items[$index]['invoice_discount']    = $discount;
			$this->line_items[$index]['invoice_qty']         = $result->fields['qty'];
			$this->line_items[$index]['invoice_sku']         = $result->fields['sku'];
			$this->line_items[$index]['invoice_description'] = $result->fields['description'];
			$this->line_items[$index]['invoice_serial_num']  = $result->fields['serialize_number'];
			$this->line_items[$index]['invoice_line_tax']    = $line_tax * $price;
			$this->line_items[$index]['invoice_price']       = $price;
			$this->line_items[$index]['invoice_price_w_tax'] = (1 + $line_tax) * $price; // line item price with tax
			$this->invoice_subtotal   += $price;
			$this->inv_subtotal_w_tax += (1 + $line_tax) * $price;
			break;
	  	case 'tax':
			$tax_list[]           = $result->fields['description'] . ' - ' . $currencies->format_full($price);
			break;
	  	case 'dsc':
	  		$this->discount       = $price;
	  		break;
	  	case 'ttl':
	    	$this->payment_detail = $this->pull_desc($result->fields['description']);
	    	$this->total_paid     = $result->fields['debit_amount'];
	    	break;
	  }
	  $result->MoveNext();
	}
	$this->tax_text = (sizeof($tax_list) > 0) ? implode("\n", $tax_list) : '';
  }

  function load_account_details($id) {
	global $db;
	$sql = "select * from " . TABLE_CONTACTS . " where id = " . $id;
	$result = $db->Execute($sql);
	$this->short_name     = $result->fields['short_name'];
	$this->account_number = $result->fields['account_number'];
	$this->gov_id_number  = $result->fields['gov_id_number'];
	// pull the billing and shipping addresses
	$sql = "select * from " . TABLE_ADDRESS_BOOK . " where ref_id = " . $id;
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  $type = substr($result->fields['type'], 1, 1);
	  switch ($type) {
		case 'm': // main
		  $this->account['mailing'][] = $result->fields;
		  $this->bill_telephone2 = $result->fields['telephone2'];
		  $this->bill_telephone3 = $result->fields['telephone3'];
		  $this->bill_telephone4 = $result->fields['telephone4'];
		  $this->bill_website    = $result->fields['website'];
		  break;
		case 'b': // billing
		  $this->account['billing'][]  = $result->fields;
		  break;
	  }
	  $result->MoveNext();
	}
  }

  function load_payment_details($id) {
	global $db;
	$sql = "select * from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id . " and gl_type in ('pmt', 'chk')";
	$result = $db->Execute($sql);
	$this->payment_rows = array();
	while (!$result->EOF) {
	  // keep all payments in an array
	  $this->payment_rows[] = array(
		'payment_amount'     => ($result->fields['debit_amount'] != 0)? $result->fields['debit_amount'] : $result->fields['credit_amount'],
		'payment_method'     => $this->pull_desc($result->fields['description']),
	  );
	  $result->MoveNext();
	}
  }

  function pull_desc($desc) {
	$output = '';
	$parts = explode(':', $desc);
	if (strpos($parts[2], '****') !== false) { // it is a credit card, return the last 4 numbers
	  switch(substr($parts[1], 0, 1)) {
		case '4': $output .= 'Visa *'; break;
		case '5': $output .= 'MC *';   break;
		case '3': $output .= 'AMX *';  break;
	  }
	  return $output . substr($parts[2], -4);
	}
	return $parts[1];
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => '',                    'text' => TEXT_NONE);
	$output[] = array('id' => 'id',                  'text' => RW_EB_RECORD_ID);
	$output[] = array('id' => 'period',              'text' => TEXT_PERIOD);
	$output[] = array('id' => 'journal_id',          'text' => RW_EB_JOURNAL_ID);
	$output[] = array('id' => 'post_date',           'text' => TEXT_POST_DATE);
	$output[] = array('id' => 'store_id',            'text' => RW_EB_STORE_ID);
	$output[] = array('id' => 'description',         'text' => RW_EB_JOURNAL_DESC);
	$output[] = array('id' => 'closed',              'text' => RW_EB_CLOSED);
	$output[] = array('id' => 'terms',               'text' => RW_EB_TERMS);
	$output[] = array('id' => 'discount',            'text' => RW_EB_INV_DISCOUNT);
	$output[] = array('id' => 'sales_tax',           'text' => RW_EB_SALES_TAX);
	$output[] = array('id' => 'tax_auths',           'text' => RW_EB_TAX_AUTH);
	$output[] = array('id' => 'tax_text',            'text' => RW_EB_TAX_DETAILS);
	$output[] = array('id' => 'invoice_subtotal',    'text' => RW_EB_INV_SUBTOTAL);
	$output[] = array('id' => 'inv_subtotal_w_tax',  'text' => RW_EB_INV_SUB_W_TAX);
	$output[] = array('id' => 'total_amount',        'text' => RW_EB_INV_TOTAL);
	$output[] = array('id' => 'currencies_code',     'text' => RW_EB_CUR_CODE);
	$output[] = array('id' => 'currencies_value',    'text' => RW_EB_CUR_EXC_RATE);
	$output[] = array('id' => 'purchase_invoice_id', 'text' => RW_EB_INV_NUM);
	$output[] = array('id' => 'rep_id',              'text' => RW_EB_SALES_REP_ID);
	$output[] = array('id' => 'rep_name',            'text' => RW_EB_SALES_REP);
	$output[] = array('id' => 'gl_acct_id',          'text' => RW_EB_AR_ACCT);
	$output[] = array('id' => 'bill_acct_id',        'text' => RW_EB_BILL_ACCT_ID);
	$output[] = array('id' => 'bill_address_id',     'text' => RW_EB_BILL_ADD_ID);
	$output[] = array('id' => 'bill_primary_name',   'text' => RW_EB_BILL_PRIMARY_NAME);
	$output[] = array('id' => 'bill_contact',        'text' => RW_EB_BILL_CONTACT);
	$output[] = array('id' => 'bill_address1',       'text' => RW_EB_BILL_ADDRESS1);
	$output[] = array('id' => 'bill_address2',       'text' => RW_EB_BILL_ADDRESS2);
	$output[] = array('id' => 'bill_city_town',      'text' => RW_EB_BILL_CITY);
	$output[] = array('id' => 'bill_state_province', 'text' => RW_EB_BILL_STATE);
	$output[] = array('id' => 'bill_postal_code',    'text' => RW_EB_BILL_ZIP);
	$output[] = array('id' => 'bill_country_code',   'text' => RW_EB_BILL_COUNTRY);
	$output[] = array('id' => 'bill_telephone1',     'text' => RW_EB_BILL_TELE1);
	$output[] = array('id' => 'bill_telephone2',     'text' => RW_EB_BILL_TELE2);
	$output[] = array('id' => 'bill_telephone3',     'text' => RW_EB_BILL_FAX);
	$output[] = array('id' => 'bill_telephone4',     'text' => RW_EB_BILL_TELE4);
	$output[] = array('id' => 'bill_email',          'text' => RW_EB_BILL_EMAIL);
	$output[] = array('id' => 'bill_website',        'text' => RW_EB_BILL_WEBSITE);
	$output[] = array('id' => 'short_name',          'text' => RW_EB_CUSTOMER_ID);
	$output[] = array('id' => 'account_number',      'text' => RW_EB_ACCOUNT_NUMBER);
	$output[] = array('id' => 'gov_id_number',       'text' => RW_EB_GOV_ID_NUMBER);
	$output[] = array('id' => 'terminal_date',       'text' => RW_EB_SHIP_DATE);
	$output[] = array('id' => 'total_paid',          'text' => RW_EB_TOTAL_PAID);
	$output[] = array('id' => 'balance_due',         'text' => RW_EB_BALANCE_DUE);
	$output[] = array('id' => 'rounded_of',          'text' => RW_EB_ROUNDED_OF);
	return $output;
  }

  function build_table_drop_down() {
	// build the drop down choices
	$output = array();
	$output[] = array('id' => '',                    'text' => TEXT_NONE);
	$output[] = array('id' => 'invoice_description', 'text' => RW_EB_INV_DESC);
	$output[] = array('id' => 'invoice_qty',         'text' => RW_EB_INV_QTY);
	$output[] = array('id' => 'invoice_full_price',  'text' => RW_EB_INV_TOTAL_PRICE);
	$output[] = array('id' => 'invoice_unit_price',  'text' => RW_EB_INV_UNIT_PRICE);
	$output[] = array('id' => 'invoice_discount',    'text' => RW_EB_INV_DISCOUNT);
	$output[] = array('id' => 'invoice_price',       'text' => RW_EB_INV_PRICE);
	$output[] = array('id' => 'invoice_price_w_tax', 'text' => RW_EB_INV_PRICE_W_TAX);
	$output[] = array('id' => 'invoice_line_tax',    'text' => RW_EB_INV_LINE_TAX);
	$output[] = array('id' => 'invoice_sku',         'text' => RW_EB_INV_SKU);
	$output[] = array('id' => 'invoice_serial_num',  'text' => RW_EB_INV_SERIAL_NUM);
	//payment methodes.
	$output[] = array('id' => 'payment_amount',      'text' => RW_EB_PAYMENT_AMOUNT);
	$output[] = array('id' => 'payment_method',      'text' => RW_EB_PAYMENT_METHOD);
	return $output;
  }

}
?>