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
//  Path: /modules/inventory/functions/inventory.php
//

  function load_store_stock($sku, $store_id) {
	global $db;
	$sql = "select sum(remaining) as remaining from " . TABLE_INVENTORY_HISTORY . " 
		where store_id = '" . $store_id . "' and sku = '" . $sku . "'";
	$result = $db->Execute($sql);
	$store_bal = $result->fields['remaining'];
	$sql = "select sum(qty) as qty from " . TABLE_INVENTORY_COGS_OWED . " 
		where store_id = '" . $store_id . "' and sku = '" . $sku . "'";
	$result = $db->Execute($sql);
	$qty_owed = $result->fields['qty'];
	return ($store_bal - $qty_owed);
  }

  function inv_calculate_prices($item_cost, $full_price, $encoded_price_levels) {
    global $currencies;
	if (!defined('MAX_NUM_PRICE_LEVELS')) throw new \Exception('Constant MAX_NUM_PRICE_LEVELS is not defined! returning from inv_calculate_prices');
	$price_levels = explode(';', $encoded_price_levels);
	$prices = array();
	for ($i=0, $j=1; $i < MAX_NUM_PRICE_LEVELS; $i++, $j++) {
		$level_info = explode(':', $price_levels[$i]);
		$price      = $level_info[0] ? $level_info[0] : ($i==0 ? $full_price : 0);
		$qty        = $level_info[1] ? $level_info[1] : $j;
		$src        = $level_info[2] ? $level_info[2] : 0;
		$adj        = $level_info[3] ? $level_info[3] : 0;
		$adj_val    = $level_info[4] ? $level_info[4] : 0;
		$rnd        = $level_info[5] ? $level_info[5] : 0;
		$rnd_val    = $level_info[6] ? $level_info[6] : 0;
		if ($j == 1) $src++; // for the first element, the Not Used selection is missing

		switch ($src) {
			case 0: $price = 0;                  break; // Not Used
			case 1: 			                 break; // Direct Entry
			case 2: $price = $item_cost;         break; // Last Cost
			case 3: $price = $full_price;        break; // Retail Price
			case 4: $price = $first_level_price; break; // Price Level 1
		}

		switch ($adj) {
			case 0:                                      break; // None
			case 1: $price -= $adj_val;                  break; // Decrease by Amount
			case 2: $price -= $price * ($adj_val / 100); break; // Decrease by Percent
			case 3: $price += $adj_val;                  break; // Increase by Amount
			case 4: $price += $price * ($adj_val / 100); break; // Increase by Percent
		}

		switch ($rnd) {
			case 0: // None
				break;
			case 1: // Next Integer (whole dollar)
				$price = ceil($price);
				break;
			case 2: // Constant remainder (cents)
				$remainder = $rnd_val;
				if ($remainder < 0) $remainder = 0; // don't allow less than zero adjustments
				// conver to fraction if greater than 1 (user left out decimal point)
				if ($remainder >= 1) $remainder = '.' . $rnd_val;
				$price = floor($price) + $remainder;
				break;
			case 3: // Next Increment (round to next value)
				$remainder = $rnd_val;
				if ($remainder <= 0) { // don't allow less than zero adjustments, assume zero
				  $price = ceil($price);
				} else {
				  $price = ceil($price / $remainder) * $remainder;
				}
		}

		if ($j == 1) $first_level_price = $price; // save level 1 pricing
		$price = $currencies->precise($price);
		if ($src) $prices[$i] = array('qty' => $qty, 'price' => $price);
	}
	return $prices;
  }

 
  function gather_history($sku) {
    global $db;
	$inv_history = array();
	$dates = gen_get_dates();
	$cur_month = $dates['ThisYear'] . '-' . substr('0' . $dates['ThisMonth'], -2) . '-01';
	for($i = 0; $i < 13; $i++) {
	  $index = substr($cur_month, 0, 7);
	  $history['purchases'][$index] = array(
	  	'post_date'    => $cur_month,
	  	'qty'          => 0,
	  	'total_amount' => 0,
	  );
	  $history['sales'][$index] = array(
	  	'post_date'    => $cur_month,
	  	'qty'          => 0,
	  	'usage'        => 0,
	  	'total_amount' => 0,
	  );
	  $cur_month = gen_specific_date($cur_month, 0, -1, 0);
	}
	$last_year = ($dates['ThisYear'] - 1) . '-' . substr('0' . $dates['ThisMonth'], -2) . '-01';

	// load the SO's and PO's and get order, expected del date
	$sql = "select m.id, m.journal_id, m.store_id, m.purchase_invoice_id, i.qty, i.post_date, i.date_1, 
	i.id as item_id 
	  from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
	  where m.journal_id in (4, 10) and i.sku = '" . $sku ."' and m.closed = '0' 
	  order by i.date_1";
	$result = $db->Execute($sql);
	while(!$result->EOF) {
	  switch ($result->fields['journal_id']) {
	    case  4:
		  $gl_type   = 'por';
		  $hist_type = 'open_po';
		  break;
	    case 10:
		  $gl_type   = 'sos';
		  $hist_type = 'open_so';
		  break;
	  }
	  $sql = "select sum(qty) as qty from " . TABLE_JOURNAL_ITEM . " 
		where gl_type = '" . $gl_type . "' and so_po_item_ref_id = " . $result->fields['item_id'];
	  $adj = $db->Execute($sql); // this looks for partial received to make sure this item is still on order
	  if ($result->fields['qty'] > $adj->fields['qty']) {
		$history[$hist_type][] = array(
		  'id'                  => $result->fields['id'],
		  'store_id'            => $result->fields['store_id'],
		  'purchase_invoice_id' => $result->fields['purchase_invoice_id'],
		  'post_date'           => $result->fields['post_date'],
		  'qty'                 => $result->fields['qty'],
		  'date_1'              => $result->fields['date_1'],
		);
	  }
	  $result->MoveNext();
	}

	// load the units received and sold, assembled and adjusted
	$sql = "select m.journal_id, m.post_date, i.qty, i.gl_type, i.credit_amount, i.debit_amount 
	  from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
	  where m.journal_id in (6, 12, 14, 16, 19, 21) and i.sku = '" . $sku ."' and m.post_date >= '" . $last_year . "' 
	  order by m.post_date DESC";
	$result = $db->Execute($sql);
	while(!$result->EOF) {
	  $month = substr($result->fields['post_date'], 0, 7);
	  switch ($result->fields['journal_id']) {
	    case  6:
	    case 21:
	      $history['purchases'][$month]['qty']          += $result->fields['qty'];
	      $history['purchases'][$month]['total_amount'] += $result->fields['debit_amount'];
		  break;
	    case 12:
	    case 19:
	      $history['sales'][$month]['qty']              += $result->fields['qty'];
	      $history['sales'][$month]['usage']            += $result->fields['qty'];
	      $history['sales'][$month]['total_amount']     += $result->fields['credit_amount'];
		  break;
	    case 14:
		  if ($result->fields['gl_type'] == 'asi') { // only if part of an assembly
	        $history['sales'][$month]['usage'] -= $result->fields['qty']; // need to negate quantity since assy.
		  }
		  break;
	    case 16:
	      $history['sales'][$month]['usage'] += $result->fields['qty'];
		  break;
	  }
	  $result->MoveNext();
	}

	// calculate average usage
	$cnt = 0;
	$history['averages'] = array();
	foreach ($history['sales'] as $key => $value) {
	  if ($cnt == 0) { 
	    $cnt++;
		continue; // skip current month since we probably don't have the full months worth
	  }
	  $history['averages']['12month'] += $history['sales'][$key]['usage'];
	  if ($cnt < 7) $history['averages']['6month'] += $history['sales'][$key]['usage'];
	  if ($cnt < 4) $history['averages']['3month'] += $history['sales'][$key]['usage'];
	  if ($cnt < 2) $history['averages']['1month'] += $history['sales'][$key]['usage'];
	  $cnt++;
	}
	$history['averages']['12month'] = round($history['averages']['12month'] / 12, 2);
	$history['averages']['6month']  = round($history['averages']['6month']  /  6, 2);
	$history['averages']['3month']  = round($history['averages']['3month']  /  3, 2);
	return $history;
  }

  function inv_calculate_sales_price($qty, $sku_id, $contact_id = 0, $type = 'c') {
    global $db, $currencies;
	$price_sheet = '';
	$contact_tax = 1;
	if ($contact_id) {
	  $contact = $db->Execute("select type, price_sheet, tax_id from " . TABLE_CONTACTS . " where id = '" . $contact_id . "'");
	  $type        = $contact->fields['type'];
	  $price_sheet = $contact->fields['price_sheet'];
	  $contact_tax = $contact->fields['tax_id'];
	}
	// get the inventory prices
	if($type == 'v'){
		if ($contact_id) $inventory = $db->Execute("select p.item_cost, a.full_price, a.price_sheet, p.price_sheet_v, a.item_taxable, p.purch_taxable from " . TABLE_INVENTORY . " a join " . TABLE_INVENTORY_PURCHASE . " p on a.sku = p.sku  where a.id = '" . $sku_id . "' and p.vendor_id = '" . $contact_id . "'");
		else $inventory = $db->Execute("select MAX(p.item_cost) as item_cost, a.full_price, a.price_sheet, p.price_sheet_v, a.item_taxable, p.purch_taxable from " . TABLE_INVENTORY . " a join " . TABLE_INVENTORY_PURCHASE . " p on a.sku = p.sku  where a.id = '" . $sku_id . "'");
		$inv_price_sheet = $inventory->fields['price_sheet_v'];
	}else{
		$inventory = $db->Execute("select MAX(p.item_cost) as item_cost, a.full_price, a.price_sheet, p.price_sheet_v, a.item_taxable, p.purch_taxable from " . TABLE_INVENTORY . " a join " . TABLE_INVENTORY_PURCHASE . " p on a.sku = p.sku  where a.id = '" . $sku_id . "'");
		$inv_price_sheet = $inventory->fields['price_sheet'];
	}
	// set the default tax rates
	$purch_tax = ($contact_tax == 0 && $type=='v') ? 0 : $inventory->fields['purch_taxable'];
	$sales_tax = ($contact_tax == 0 && $type=='c') ? 0 : $inventory->fields['item_taxable'];
	// determine what price sheet to use, priority: customer, inventory, default
	if ($price_sheet <> '') {
	  $sheet_name = $price_sheet;
	} elseif ($inv_price_sheet <> '') {
	  $sheet_name = $inv_price_sheet;
	} else {
	  $default_sheet = $db->Execute("select sheet_name from " . TABLE_PRICE_SHEETS . " 
		where type = '" . $type . "' and default_sheet = '1'");
	  $sheet_name = ($default_sheet->RecordCount() == 0) ? '' : $default_sheet->fields['sheet_name'];
	}
	// determine the sku price ranges from the price sheet in effect
	$levels = false;
	if ($sheet_name <> '') {
	  $sql = "select id, default_levels from " . TABLE_PRICE_SHEETS . " 
	    where inactive = '0' and type = '$type' and sheet_name = '$sheet_name' and 
	    (expiration_date is null or expiration_date = '0000-00-00' or expiration_date >= '" . date('Y-m-d') . "')";
	  $price_sheets = $db->Execute($sql);
	  // retrieve special pricing for this inventory item
	  $sql = "select price_sheet_id, price_levels from " . TABLE_INVENTORY_SPECIAL_PRICES . " 
		where price_sheet_id = '" . $price_sheets->fields['id'] . "' and inventory_id = $sku_id";
	  $result = $db->Execute($sql);
	  $special_prices = array();
	  while (!$result->EOF) {
	    $special_prices[$result->fields['price_sheet_id']] = $result->fields['price_levels'];
	    $result->MoveNext();
	  }
	  $levels = isset($special_prices[$price_sheets->fields['id']]) ? $special_prices[$price_sheets->fields['id']] : $price_sheets->fields['default_levels'];
	}
	if ($levels) {
	  $prices = inv_calculate_prices($inventory->fields['item_cost'], $inventory->fields['full_price'], $levels);
	  $price = '0.0';
	  if(is_array($prices)) foreach ($prices as $value) if ($qty >= $value['qty']) $price = $currencies->clean_value($value['price']);
	} else {
	  $price = ($type=='v') ? $inventory->fields['item_cost'] : $inventory->fields['full_price'];
	}
	return array('price'=>$price, 'sales_tax'=>$sales_tax, 'purch_tax'=>$purch_tax);
  }

function inv_status_open_orders($journal_id, $gl_type) { // checks order status for order balances, items received/shipped
  global $db;
  $item_list = array();
  $orders = $db->Execute("select id from " . TABLE_JOURNAL_MAIN . " 
  	where journal_id = " . $journal_id . " and closed = '0'");
  while (!$orders->EOF) {
    $total_ordered = array(); // track this SO/PO sku for totals, to keep >= 0
    $id = $orders->fields['id'];
	// retrieve information for requested id
	$sql = " select sku, qty from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $id . " and gl_type = '" . $gl_type . "'";
	$ordr_items = $db->Execute($sql);
	while (!$ordr_items->EOF) {
	  $item_list[$ordr_items->fields['sku']] += $ordr_items->fields['qty'];
	  $total_ordered[$ordr_items->fields['sku']] += $ordr_items->fields['qty'];
	  $ordr_items->MoveNext();
	}
	// calculate received/sales levels (SO and PO)
	$sql = "select i.qty, i.sku, i.ref_id 
		from " . TABLE_JOURNAL_MAIN . " m left join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
		where m.so_po_ref_id = " . $id;
	$posted_items = $db->Execute($sql);
	while (!$posted_items->EOF) {
	  foreach ($item_list as $sku => $balance) {
		if ($sku == $posted_items->fields['sku']) {
		  $total_ordered[$sku] -= $posted_items->fields['qty'];
		  $adjustment = $total_ordered[$sku] > 0 ? $posted_items->fields['qty'] : max(0, $total_ordered[$sku] + $posted_items->fields['qty']);
		  $item_list[$sku] -= $adjustment;
		}
	  }
	  $posted_items->MoveNext();
	}
	$orders->MoveNext();
  } // end for each open order
  return $item_list;
}

/**
 * this function check is a value is a barcode
 * @param unknown $barcode
 * @return boolean
 */

function validate_UPCABarcode($barcode){
	// check to see if barcode is 12 digits long
  	if(!preg_match("/^[0-9]{12}$/",$barcode)) return false;
  	$digits = $barcode;
	// 1. sum each of the odd numbered digits
  	$odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];  
  	// 2. multiply result by three
  	$odd_sum_three = $odd_sum * 3;
  	// 3. add the result to the sum of each of the even numbered digits
  	$even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9];
  	$total_sum = $odd_sum_three + $even_sum;
	// 4. subtract the result from the next highest power of 10
  	$next_ten = (ceil($total_sum/10))*10;
  	$check_digit = $next_ten - $total_sum;
	// if the check digit and the last digit of the barcode are OK return true;
	if($check_digit == $digits[11]) return true;
	return false;
}


/**
 * this function check is a value is a EAN barcode
 * @param unknown $barcode
 * @return boolean
 */

function validate_EAN13Barcode($barcode) {
	// check to see if barcode is 13 digits long
	if(!preg_match("/^[0-9]{13}$/",$barcode)) return false;

	$digits = $barcode;	
	// 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
	$even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];
	// 2. Multiply this result by 3.
	$even_sum_three = $even_sum * 3;
	// 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
	$odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];
	// 4. Sum the results of steps 2 and 3.
	$total_sum = $even_sum_three + $odd_sum;
	// 5. The check character is the smallest number which, when added to the result in step 4, produces a multiple of 10.
	$next_ten = (ceil($total_sum/10))*10;
	$check_digit = $next_ten - $total_sum;
	// if the check digit and the last digit of the barcode are OK return true;
	if($check_digit == $digits[12]) return true;
	return false;
}

?>