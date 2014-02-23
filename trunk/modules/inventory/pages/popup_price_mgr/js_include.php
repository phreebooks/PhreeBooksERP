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
//  Path: /modules/inventory/pages/popup_price_mgr/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var full_price       = <?php echo $full_price; ?>;
var item_cost        = <?php echo $item_cost; ?>;
var num_price_sheets = <?php echo $price_sheets->RecordCount(); ?>;
var num_price_levels = <?php echo MAX_NUM_PRICE_LEVELS; ?>;

function init() {
  <?php if ($_REQUEST['action'] == 'save') echo 'self.close();' ?>
  for (var sheetID=1; sheetID<=num_price_sheets; sheetID++) {
	updatePrice(sheetID);
  }
}

function check_form() {
  return true;
}
// Insert javscript file references here.

// Insert other page specific functions here.
function updatePrice(sheetID) {
  for (var rowID=1; rowID<=num_price_levels; rowID++) {
	var refID      = sheetID + '_' + rowID;
	var price      = parseFloat(cleanCurrency(document.getElementById('price_'+refID).value));
	var adjustment = parseFloat(cleanCurrency(document.getElementById('adj_val_'+refID).value));
	var remainder  = parseFloat(cleanCurrency(document.getElementById('rnd_val_'+refID).value));
	document.getElementById('adj_'+refID).disabled     = false;
	document.getElementById('rnd_'+refID).disabled     = false;
	document.getElementById('adj_val_'+refID).disabled = false;
	document.getElementById('rnd_val_'+refID).disabled = false;
	document.getElementById('price_'+refID).disabled   = true;
	var src = document.getElementById('src_'+refID).selectedIndex;
	if (rowID == 1) src++; // for the first element, the Not Used selection is missing
	switch (src) {
	  case 0: // Not Used
		document.getElementById('adj_'+refID).value        = formatted_zero;
		document.getElementById('rnd_'+refID).value        = formatted_zero;
		document.getElementById('price_'+refID).value      = formatted_zero;
		document.getElementById('adj_'+refID).disabled     = true;
		document.getElementById('rnd_'+refID).disabled     = true;
		document.getElementById('adj_val_'+refID).disabled = true;
		document.getElementById('rnd_val_'+refID).disabled = true;
		price = 0;
		break;
	  case 1: // Direct Entry
		document.getElementById('adj_'+refID).value        = formatted_zero;
		document.getElementById('rnd_'+refID).value        = formatted_zero;
		document.getElementById('adj_'+refID).value        = 0;
		document.getElementById('adj_'+refID).disabled     = true;
		document.getElementById('rnd_'+refID).disabled     = true;
		document.getElementById('adj_val_'+refID).disabled = true;
		document.getElementById('rnd_val_'+refID).disabled = true;
		document.getElementById('price_'+refID).disabled   = false;
		break;
	  case 2: // Last Cost
		price = item_cost;
		break;
	  case 3: // Retail Price
		price = full_price;
		break;
	  case 4: // Price Level 1
		price = parseFloat(cleanCurrency(document.getElementById('price_'+sheetID+'_1').value));
	}
	document.getElementById('adj_val_'+refID).disabled = false;
	switch (document.getElementById('adj_'+refID).selectedIndex) {
	  case 0: // None
		document.getElementById('adj_val_'+refID).disabled = true;
		break;
	  case 1: // Decrease by Amount
		price -= adjustment;
		break;
	  case 2: // Decrease by Percent
		price -= price * (adjustment / 100);
		break;
	  case 3: // Increase by Amount
		price += adjustment;
		break;
	  case 4: // Increase by Percent
		price += price * (adjustment / 100);
	}
	switch (document.getElementById('rnd_'+refID).selectedIndex) {
	  case 0: // None
		document.getElementById('rnd_val_'+refID).disabled = true;
		break;
	  case 1: // Next Integer (whole dollar)
		document.getElementById('rnd_val_'+refID).disabled = true;
		price = Math.ceil(price);
		break;
	  case 2: // Constant remainder (cents)
		if (remainder < 0) remainder = 0; // don't allow less than zero adjustments
		// conver to fraction if greater than 1 (user left out decimal point)
		if (remainder >= 1) remainder = parseFloat('0.' + remainder);
		document.getElementById('rnd_val_'+refID).disabled = false;
		price = Math.floor(price) + remainder;
		break;
	  case 3: // Next Increment (round to next value)
		document.getElementById('rnd_val_'+refID).disabled = false;
		if (remainder <= 0) { // don't allow less than zero adjustments, assume zero
		  price = Math.ceil(price); 
		} else {
		  price = Math.ceil(price / remainder);
		  price = price * remainder;
		}
	}
	var tot = new String(price);
	document.getElementById('price_'+refID).value = formatPrecise(tot);
	document.getElementById('margin_'+refID).value = (item_cost != 0) ? formatPrecise(tot/item_cost) : '';
  }
}

// -->
</script>