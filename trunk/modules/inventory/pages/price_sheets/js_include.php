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
//  Path: /modules/inventory/pages/price_sheets/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var num_price_levels = <?php echo MAX_NUM_PRICE_LEVELS; ?>;
<?php echo js_calendar_init($cal_ps); ?>

function init() {
  <?php 
  if ($_REQUEST['action'] <> 'new' && $_REQUEST['action'] <> 'edit') {
    echo '  document.getElementById(\'search_text\').focus();';
    echo '  document.getElementById(\'search_text\').select();';
  } ?>
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

<?php if ($_REQUEST['action'] == 'new') { ?>
  var sheetName = document.getElementById('sheet_name').value;
  if (!sheetName) {
  	error = 1;
  	error_message += '<?php echo JS_ERROR_NO_SHEET_NAME; ?>';
  }
<?php } ?>

  if (error == 1) {
	alert(error_message);
	return false;
  }
  return true;
}

// Insert other page specific functions here.
function priceMgr(rowID, itemID) {
  var rowCost = cleanCurrency(document.getElementById('cost_'+rowID).value);
  var rowPrice = cleanCurrency(document.getElementById('sell_'+rowID).value);
  window.open('index.php?module=inventory&page=popup_prices&iID='+itemID+'&cost='+rowCost+'&price='+rowPrice,"price_mgr","width=800,height=400,resizable=1,scrollbars=1,top=150,left=200");
}

function deleteItem(id) {
  location.href = 'index.php?module=inventory&page=price_sheets&action=delete&psID='+id;
}

function initEditForm() {
  updatePrice();
}

function updatePrice() {
  for (var rowID=1; rowID<=num_price_levels; rowID++) {
	var src = document.getElementById('src_'+rowID).selectedIndex;
	if (rowID == 1) src++; // for the first element, the not used is missing
	switch (src) {
	  case 0: // Not Used
		document.getElementById('adj_'+rowID).value = 0;
		document.getElementById('rnd_'+rowID).value = 0;
		document.getElementById('price_'+rowID).value = 0;
		document.getElementById('adj_'+rowID).disabled = true;
		document.getElementById('rnd_'+rowID).disabled = true;
		document.getElementById('adj_val_'+rowID).disabled = true;
		document.getElementById('rnd_val_'+rowID).disabled = true;
		break;
	  case 1: // Direct Entry
		document.getElementById('adj_'+rowID).value = 0;
		document.getElementById('rnd_'+rowID).value = 0;
		document.getElementById('adj_'+rowID).disabled = true;
		document.getElementById('rnd_'+rowID).disabled = true;
		document.getElementById('adj_val_'+rowID).disabled = true;
		document.getElementById('rnd_val_'+rowID).disabled = true;
		document.getElementById('price_'+rowID).disabled = false;
		break;
	  case 2: // Last Cost
	  case 3: // Retail Price
	  case 4: // Price Level 1
	  default:
		document.getElementById('adj_'+rowID).disabled = false;
		document.getElementById('rnd_'+rowID).disabled = false;
		document.getElementById('adj_val_'+rowID).disabled = false;
		document.getElementById('rnd_val_'+rowID).disabled = false;
		document.getElementById('price_'+rowID).disabled = true;
	}

	document.getElementById('adj_val_'+rowID).disabled = false;
	switch (document.getElementById('adj_'+rowID).selectedIndex) {
	  case 0: // None
		document.getElementById('adj_val_'+rowID).disabled = true;
		break;
	  case 1: // Decrease by Amount
	  case 2: // Decrease by Percent
	  case 3: // Increase by Amount
	  case 4: // Increase by Percent
	}

	switch (document.getElementById('rnd_'+rowID).selectedIndex) {
	  case 0: // None
	  case 1: // Next Integer (whole dollar)
		document.getElementById('rnd_val_'+rowID).disabled = true;
		break;
	  case 2: // Constant Cents
	  case 3: // Next Increment
		document.getElementById('rnd_val_'+rowID).disabled = false;
	}
  }
}

// -->
</script>