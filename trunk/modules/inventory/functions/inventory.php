<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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

  function inv_calculate_prices($item_cost, $full_price, $encoded_price_levels, $qty = 1) {
    global $admin;
	if (!defined('MAX_NUM_PRICE_LEVELS')) throw new \core\classes\userException('Constant MAX_NUM_PRICE_LEVELS is not defined! returning from inv_calculate_prices');
	$price_levels = explode(';', $encoded_price_levels);
	$prices = array();
	$previous_price = 0;
	$previous_qty   = 0;
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
			case 0:                                      	break; // None
			case 1: $price -= $adj_val;                  	break; // Decrease by Amount
			case 2: $price -= $price * ($adj_val / 100); 	break; // Decrease by Percent
			case 3: $price += $adj_val;                  	break; // Increase by Amount
			case 4: $price += $price * ($adj_val / 100); 	break; // Increase by Percent
			case 5: $price =  $price * (1+($adj_val / 100));break; // Mark up by Percent
			case 6: $price =  $price / ($adj_val / 100); 	break; // Margin by Percent
			case 7:// tiered pricing
				$price =  (($previous_price * $previous_qty) + ($price * ($qty - $previous_qty))/ $qty);
				$previous_price = $price;
				$previous_qty = $qty;
				break;
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
		$price = $admin->currencies->precise($price);
		if ($src) $prices[$i] = array('qty' => $qty, 'price' => $price);
	}
	return $prices;
  }
  
function inv_status_open_orders($journal_id, $gl_type) { // checks order status for order balances, items received/shipped
  global $admin;
  $item_list = array();
  $orders = $admin->DataBase->query("select id from " . TABLE_JOURNAL_MAIN . "
  	where journal_id = $journal_id and closed = '0'");
  while (!$orders->EOF) {
    $total_ordered = array(); // track this SO/PO sku for totals, to keep >= 0
    $id = $orders['id'];
	// retrieve information for requested id
	$sql = " select sku, qty from " . TABLE_JOURNAL_ITEM . " where ref_id = $id and gl_type = '$gl_type'";
	$ordr_items = $admin->DataBase->query($sql);
	while (!$ordr_items->EOF) {
	  $item_list[$ordr_items['sku']] += $ordr_items['qty'];
	  $total_ordered[$ordr_items['sku']] += $ordr_items['qty'];
	  $ordr_items->MoveNext();
	}
	// calculate received/sales levels (SO and PO)
	$sql = "select i.qty, i.sku, i.ref_id
		from " . TABLE_JOURNAL_MAIN . " m left join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
		where m.so_po_ref_id = " . $id;
	$posted_items = $admin->DataBase->query($sql);
	while (!$posted_items->EOF) {
	  foreach ($item_list as $sku => $balance) {
		if ($sku == $posted_items['sku']) {
		  $total_ordered[$sku] -= $posted_items['qty'];
		  $adjustment = $total_ordered[$sku] > 0 ? $posted_items['qty'] : max(0, $total_ordered[$sku] + $posted_items['qty']);
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