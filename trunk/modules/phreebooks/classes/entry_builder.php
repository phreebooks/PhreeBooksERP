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
//  Path: /modules/phreeform/custom/classes/entry_builder.php
//

require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');

class entry_builder {
  function __construct() {
	$this->discount = 0;
	$taxes = ord_calculate_tax_drop_down('c');
	$this->taxes = array();
	foreach ($taxes as $rate) $this->taxes[$rate['id']] = $rate['rate']/100;
	$this->tax_rates = array();
	foreach ($taxes as $rate) $this->tax_rates[$rate['id']] = $rate['rate'].'%';
  }

  function load_query_results($tableKey = 'id', $tableValue = 0) {
	global $db, $report, $FieldListings;
	if (!$tableValue) return false;
	$sql = "select * from " . TABLE_JOURNAL_MAIN . " where id = " . $tableValue;
	$result = $db->Execute($sql);
	while (list($key, $value) = each($result->fields)) $this->$key = db_prepare_input($value);
	$this->load_so_po_details($this->so_po_ref_id);
	$this->load_item_details($this->id);
	$this->load_account_details($this->bill_acct_id);
	$this->load_payment_details($this->id);
	$this->load_shipment_details($this->id);

	// convert particular values indexed by id to common name
	if ($this->rep_id) {
		$sql = "select short_name from " . TABLE_CONTACTS . " where id = " . $this->rep_id;
		$result = $db->Execute($sql);
		$this->rep_id = $result->fields['short_name'];
	} else {
		$this->rep_id = '';
	}
	$terms_date = calculate_terms_due_dates($this->post_date, $this->terms);
	$this->payment_due_date = $terms_date['net_date'];
//		$this->tax_authorities  = 'tax_auths';
	$this->balance_due      = $this->total_amount - $this->total_paid;

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
//		if (!isset($row['order_qty'])) continue; // uncomment to show all SO lines on invoice
		if (!isset($row['invoice_qty'])) continue; // skip SO lines that are not on this invoice
		$row_data = array();
		foreach ($fields as $idx => $element) {
		  $row_data['r' . $idx] = $this->line_items[$key][$element->fieldname];
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

  function load_so_po_details($id) {
	global $db;
	// fetch the sales order and build the item list
	$sql = "select post_date, purchase_invoice_id from " . TABLE_JOURNAL_MAIN . " where id = " . $id;
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
	  $this->so_post_date = $result->fields['post_date'];
	  $this->so_po_ref_id = $result->fields['purchase_invoice_id'];
	} else {
	  $this->so_post_date = '';
	  $this->so_po_ref_id = '';
	}

	$sql = "select * from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id;
	$result = $db->Execute($sql);
	$this->item_records = array();
	while (!$result->EOF) {
	  if ($result->fields['sku']) {
		$this->item_records[] = $result->fields['id'];
		$this->line_items[$result->fields['id']]['order_price']       = $result->fields['credit_amount'];
		$this->line_items[$result->fields['id']]['order_unit_price']  = $result->fields['credit_amount'] / $result->fields['qty'];
		$this->line_items[$result->fields['id']]['order_qty']         = $result->fields['qty'];
		$this->line_items[$result->fields['id']]['order_sku']         = $result->fields['sku'];
		$this->line_items[$result->fields['id']]['order_description'] = $result->fields['description'];
		$this->line_items[$result->fields['id']]['order_serial_num']  = $result->fields['serialize_number'];
		$this->line_items[$result->fields['id']]['qty_on_backorder']  = $result->fields['qty'];
	  }
	  $result->MoveNext();
	}
	// fetch the past invoices and shipments and add to item array
	if (is_array($this->item_records)) {
	  foreach ($this->item_records as $value) {
		$sql = "select sum(qty) as qty_shipped_prior from " . TABLE_JOURNAL_ITEM . " 
			where so_po_item_ref_id = " . $value . " and ref_id <> " . $this->id;
		$result = $db->Execute($sql);
		$this->line_items[$value]['qty_shipped_prior'] = $result->fields['qty_shipped_prior'];
		$this->line_items[$value]['qty_on_backorder']  = max(0, $this->line_items[$value]['qty_on_backorder'] - $result->fields['qty_shipped_prior']);
	  }
	}
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
	  if ($result->fields['gl_type'] == 'sos' || $result->fields['gl_type'] == 'por') {
		$this->line_items[$index]['item_cnt']            = $result->fields['item_cnt'];
		$this->line_items[$index]['invoice_full_price']  = $result->fields['full_price'];
		$this->line_items[$index]['invoice_unit_price']  = $result->fields['qty'] ? ($price / $result->fields['qty']) : 0;
		$this->line_items[$index]['invoice_discount']    = $result->fields['full_price'] == 0 ? 0 : ($result->fields['full_price'] - ($price / $result->fields['qty'])) / $result->fields['full_price'];
		$this->line_items[$index]['invoice_qty']         = $result->fields['qty'];
		$this->line_items[$index]['invoice_sku']         = $result->fields['sku'];
		$this->line_items[$index]['invoice_description'] = $result->fields['description'];
		$this->line_items[$index]['invoice_serial_num']  = $result->fields['serialize_number'];
		$this->line_items[$index]['qty_on_backorder']    = max(0, $this->line_items[$index]['qty_on_backorder'] - $result->fields['qty']);
		$this->line_items[$index]['invoice_line_tax']    = $line_tax * $price;
		$this->line_items[$index]['invoice_line_tax_rate']= $this->tax_rates[$result->fields['taxable']];
		$this->line_items[$index]['invoice_price']       = $price;
		$this->line_items[$index]['invoice_price_w_tax'] = (1 + $line_tax) * $price; // line item price with tax
		$this->invoice_subtotal   += $price;
		$this->inv_subtotal_w_tax += (1 + $line_tax) * $price;
	  }
	  if ($result->fields['gl_type'] == 'tax') {
		$tax_list[] = $result->fields['description'] . ' - ' . $currencies->format_full($price);
	  }
	  if ($result->fields['gl_type'] == 'dsc') $this->discount = $price;
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
		case 's': // shipping
		  $this->account['shipping'][] = $result->fields;
		  break;
	  }
	  $result->MoveNext();
	}
  }

  function load_payment_details($id) {
	global $db;
	$this->total_paid     = 0;
	$this->payment_method = '';
	$sql = "select * from " . TABLE_JOURNAL_ITEM . " where so_po_item_ref_id = " . $id . " and gl_type in ('pmt', 'chk')";
	$result = $db->Execute($sql);
	$this->payment = array();
	while (!$result->EOF) {
	  $this->total_paid += $result->fields['credit_amount'] - $result->fields['debit_amount']; // one will be zero
	  $sql = "select post_date, shipper_code, purchase_invoice_id, purch_order_id 
	    from " . TABLE_JOURNAL_MAIN . " where id = " . $result->fields['ref_id'];
	  $pmt_info = $db->Execute($sql);
	  // keep the last payment reference, type and method
	  $this->payment_date       = $pmt_info->fields['post_date'];
	  $this->payment_method     = $pmt_info->fields['shipper_code'];
	  $this->payment_deposit_id = $pmt_info->fields['purchase_invoice_id'];
	  $this->payment_reference  = $pmt_info->fields['purch_order_id'];
	  // pull the payment detail
	  $sql = "select description from " . TABLE_JOURNAL_ITEM . " 
		where ref_id = " . $result->fields['ref_id'] . " and gl_type = 'ttl'";
	  $pmt_det = $db->Execute($sql);
	  $this->payment_detail = $this->pull_desc($pmt_det->fields['description']);
	  // keep all payments in an array
	  $this->payment[] = array(
		'amount'     => $result->fields['credit_amount'],
		'method'     => $pmt_info->fields['shipper_code'],
		'date'       => $pmt_info->fields['post_date'],
		'reference'  => $pmt_info->fields['purch_order_id'],
		'deposit_id' => $pmt_info->fields['purchase_invoice_id'],
	  );
	  $result->MoveNext();
	}
  }

  function load_shipment_details($id) {
    global $db;
	$this->ship_carrier = '';
	$this->ship_service = '';
	$this->tracking_id  = '';
	$shipping_info      = explode(':', $this->shipper_code);
	$carrier            = $shipping_info[0];
	if ($carrier) {
	  load_specific_method('shipping', $carrier);
	  $this->ship_carrier = constant('MODULE_SHIPPING_' . strtoupper($carrier) . '_TITLE_SHORT');
	  $this->ship_service = defined($carrier . '_' . $shipping_info[1]) ? constant($carrier . '_' . $shipping_info[1]) : '';
	  $result = $db->Execute("SELECT tracking_id FROM ".TABLE_SHIPPING_LOG." 
	    WHERE ref_id='$this->purchase_invoice_id' OR ref_id LIKE '".$this->purchase_invoice_id."-%'");
	  if ($result->RecordCount() > 0) {
	    $tracking = array();
		while(!$result->EOF) {
		  $tracking[] = $result->fields['tracking_id'];
		  $result->MoveNext();
		}
	    $this->tracking_id = $this->ship_carrier.' '.$this->ship_service.' # '.implode(', ', $tracking);
	  }
	}
  }

  function pull_desc($desc) {
	$output = '';
	$parts = explode(':', $desc);
	if (strpos($parts[2], '****') !== false) { // it is a credit card, return the last 4 numbers
	  switch(substr($parts[2], 0, 1)) {
		case '4': $output .= 'Visa *'; break;
		case '5': $output .= 'MC *';   break;
		case '3': $output .= 'AMX *';  break;
	  }
	  return $output . substr($parts[2], -4);
	}
	return $parts[2];
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => '',                    'text' => TEXT_NONE);
	$output[] = array('id' => 'id',                  'text' => RW_EB_RECORD_ID);
	$output[] = array('id' => 'period',              'text' => TEXT_PERIOD);
	$output[] = array('id' => 'journal_id',          'text' => RW_EB_JOURNAL_ID);
	$output[] = array('id' => 'post_date',           'text' => TEXT_POST_DATE);
	$output[] = array('id' => 'so_post_date',        'text' => TEXT_SO_POST_DATE);
	$output[] = array('id' => 'store_id',            'text' => RW_EB_STORE_ID);
	$output[] = array('id' => 'description',         'text' => RW_EB_JOURNAL_DESC);
	$output[] = array('id' => 'closed',              'text' => RW_EB_CLOSED);
	$output[] = array('id' => 'freight',             'text' => RW_EB_FRT_TOTAL);
	$output[] = array('id' => 'ship_carrier',        'text' => RW_EB_FRT_CARRIER);
	$output[] = array('id' => 'ship_service',        'text' => RW_EB_FRT_SERVICE);
	$output[] = array('id' => 'tracking_id',         'text' => RW_EB_FRT_TRACKING);
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
	$output[] = array('id' => 'so_po_ref_id',        'text' => RW_EB_SO_NUM);
	$output[] = array('id' => 'purchase_invoice_id', 'text' => RW_EB_INV_NUM);
	$output[] = array('id' => 'purch_order_id',      'text' => RW_EB_PO_NUM);
	$output[] = array('id' => 'rep_id',              'text' => RW_EB_SALES_REP);
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
	$output[] = array('id' => 'ship_acct_id',        'text' => RW_EB_SHIP_ACCT_ID);
	$output[] = array('id' => 'ship_address_id',     'text' => RW_EB_SHIP_ADD_ID);
	$output[] = array('id' => 'ship_primary_name',   'text' => RW_EB_SHIP_PRIMARY_NAME);
	$output[] = array('id' => 'ship_contact',        'text' => RW_EB_SHIP_CONTACT);
	$output[] = array('id' => 'ship_address1',       'text' => RW_EB_SHIP_ADDRESS1);
	$output[] = array('id' => 'ship_address2',       'text' => RW_EB_SHIP_ADDRESS2);
	$output[] = array('id' => 'ship_city_town',      'text' => RW_EB_SHIP_CITY);
	$output[] = array('id' => 'ship_state_province', 'text' => RW_EB_SHIP_STATE);
	$output[] = array('id' => 'ship_postal_code',    'text' => RW_EB_SHIP_ZIP);
	$output[] = array('id' => 'ship_country_code',   'text' => RW_EB_SHIP_COUNTRY);
	$output[] = array('id' => 'ship_telephone1',     'text' => RW_EB_SHIP_TELE1);
	$output[] = array('id' => 'ship_email',          'text' => RW_EB_SHIP_EMAIL);
	$output[] = array('id' => 'short_name',          'text' => RW_EB_CUSTOMER_ID);
	$output[] = array('id' => 'account_number',      'text' => RW_EB_ACCOUNT_NUMBER);
	$output[] = array('id' => 'gov_id_number',       'text' => RW_EB_GOV_ID_NUMBER);
	$output[] = array('id' => 'terminal_date',       'text' => RW_EB_SHIP_DATE);
	$output[] = array('id' => 'total_paid',          'text' => RW_EB_TOTAL_PAID);
	$output[] = array('id' => 'payment_date',        'text' => RW_EB_PAYMENT_DATE);
	$output[] = array('id' => 'payment_due_date',    'text' => RW_EB_PAYMENT_DUE_DATE);
	$output[] = array('id' => 'payment_method',      'text' => RW_EB_PAYMENT_METHOD);
	$output[] = array('id' => 'payment_reference',   'text' => RW_EB_PAYMENT_REF);
	$output[] = array('id' => 'payment_deposit_id',  'text' => RW_EB_PAYMENT_DEP_ID);
	$output[] = array('id' => 'payment_detail',      'text' => RW_EB_PAYMENT_DETAIL);
	$output[] = array('id' => 'balance_due',         'text' => RW_EB_BALANCE_DUE);
	return $output;
  }

  function build_table_drop_down() {
	// build the drop down choices
	$output = array();
	$output[] = array('id' => '',                    'text' => TEXT_NONE);
	$output[] = array('id' => 'item_cnt', 			 'text' => RW_EB_ITEM_CNT);
	$output[] = array('id' => 'order_description',   'text' => RW_EB_SO_DESC);
	$output[] = array('id' => 'order_qty',           'text' => RW_EB_SO_QTY);
	$output[] = array('id' => 'order_price',         'text' => RW_EB_SO_TOTAL_PRICE);
	$output[] = array('id' => 'order_unit_price',    'text' => RW_EB_SO_UNIT_PRICE);
	$output[] = array('id' => 'order_sku',           'text' => RW_EB_SO_SKU);
	$output[] = array('id' => 'order_serial_num',    'text' => RW_EB_SO_SERIAL_NUM);
	$output[] = array('id' => 'qty_shipped_prior',   'text' => RW_EB_SHIPPED_PRIOR);
	$output[] = array('id' => 'qty_on_backorder',    'text' => RW_EB_BACKORDER_QTY);
	$output[] = array('id' => 'invoice_description', 'text' => RW_EB_INV_DESC);
	$output[] = array('id' => 'invoice_qty',         'text' => RW_EB_INV_QTY);
	$output[] = array('id' => 'invoice_full_price',  'text' => RW_EB_INV_TOTAL_PRICE);
	$output[] = array('id' => 'invoice_unit_price',  'text' => RW_EB_INV_UNIT_PRICE);
	$output[] = array('id' => 'invoice_discount',    'text' => RW_EB_INV_DISCOUNT);
	$output[] = array('id' => 'invoice_price',       'text' => RW_EB_INV_PRICE);
	$output[] = array('id' => 'invoice_price_w_tax', 'text' => RW_EB_INV_PRICE_W_TAX);
	$output[] = array('id' => 'invoice_line_tax',    'text' => RW_EB_INV_LINE_TAX);
	$output[] = array('id' => 'invoice_line_tax_rate','text'=> RW_EB_INV_LINE_TAX_RATE);
	$output[] = array('id' => 'invoice_sku',         'text' => RW_EB_INV_SKU);
	$output[] = array('id' => 'invoice_serial_num',  'text' => RW_EB_INV_SERIAL_NUM);
	return $output;
  }

}
?>