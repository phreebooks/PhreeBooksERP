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
//  Path: /modules/phreebooks/pages/orders/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var setId                = 0; // flag used for AJAX loading of sku for bar code reading of line item
var skuLength            = <?php echo ORD_BAR_CODE_LENGTH; ?>;
var resClockID           = 0;
var max_sku_len          = <?php echo MAX_INVENTORY_SKU_LENGTH; ?>;
var auto_load_sku        = <?php echo INVENTORY_AUTO_FILL; ?>;
var image_ser_num        = '<?php echo TEXT_SERIAL_NUMBER; ?>';
var add_array            = new Array("<?php echo implode('", "', $js_arrays['fields']); ?>");
var company_array        = new Array("<?php echo implode('", "', $js_arrays['company']); ?>");
var default_array        = new Array("<?php echo implode('", "', $js_arrays['text']); ?>");
var journalID            = '<?php echo JOURNAL_ID; ?>';
var securityLevel        = <?php echo $security_level; ?>;
var single_line_list     = '<?php echo SINGLE_LINE_ORDER_SCREEN; ?>';
var account_type         = '<?php echo $account_type; ?>';
var text_search          = '<?php echo TEXT_SEARCH; ?>';
var text_enter_new       = '<?php echo TEXT_ENTER_NEW; ?>';
var text_properties      = '<?php echo TEXT_PROPERTIES; ?>';
var text_terms           = '<?php echo gen_terms_to_language('0', true, ($account_type == "v") ? 'AP' : 'AR'); ?>';
var text_Please_Select   = '<?php echo GEN_HEADING_PLEASE_SELECT; ?>';
var text_gl_acct         = '<?php echo TEXT_GL_ACCOUNT; ?>';
var text_sales_tax       = '<?php echo ORD_TAXABLE; ?>';
var text_price_manager   = '<?php echo TEXT_PRICE_MANAGER; ?>';
var text_acct_ID         = '<?php echo TEXT_GL_ACCOUNT; ?>';
var post_error           = <?php echo $error ? "true" : "false"; ?>;
var default_inv_acct     = '<?php echo DEF_INV_GL_ACCT; ?>';
var default_sales_tax    = '<?php echo ($account_type == "v") ? INVENTORY_DEFAULT_PURCH_TAX : INVENTORY_DEFAULT_TAX; ?>';
var default_GL_acct      = '<?php echo DEF_GL_ACCT; ?>';
var default_disc_acct    = '<?php echo ($account_type == "v") ? AP_DISCOUNT_PURCHASE_ACCOUNT : AR_DISCOUNT_SALES_ACCOUNT; ?>';
var default_freight_acct = '<?php echo ($account_type == "v") ? AP_DEF_FREIGHT_ACCT : AR_DEF_FREIGHT_ACCT; ?>';
var image_delete_text    = '<?php echo TEXT_DELETE; ?>';
var image_delete_msg     = '<?php echo TEXT_ROW_DELETE_ALERT; ?>';
var store_country_code   = '<?php echo COMPANY_COUNTRY; ?>';
var acct_period          = '<?php echo CURRENT_ACCOUNTING_PERIOD; ?>';
var item_col_1_enable    = '<?php echo ($item_col_1_enable) ? '1' : '0'; ?>';
var item_col_2_enable    = '<?php echo ($item_col_2_enable) ? '1' : '0'; ?>';
var ship_search_HTML     = '<?php echo GEN_CUSTOMER_ID . " " . html_input_field("ship_search", $order->ship_short_name) . "&nbsp;" . html_icon("status/folder-open.png", TEXT_SEARCH, "small", 'align="top" style="cursor:pointer" title="' . TEXT_SEARCH . '" onclick="DropShipList(this)"'); ?>';
var delete_icon_HTML     = '<?php echo substr(html_icon("emblems/emblem-unreadable.png", TEXT_DELETE, "small", "onclick=\"if (confirm(\'" . TEXT_ROW_DELETE_ALERT . "\')) removeInvRow("), 0, -2); ?>';
var serial_num_prompt    = '<?php echo ORD_JS_SERIAL_NUM_PROMPT; ?>';
var no_stock_a           = '<?php echo ORD_JS_NO_STOCK_A; ?>';
var no_stock_b           = '<?php echo ORD_JS_NO_STOCK_B; ?>';
var no_stock_c           = '<?php echo ORD_JS_NO_STOCK_C; ?>';
var inactive_a           = '<?php echo ORD_JS_INACTIVE_A; ?>';
var inactive_b           = '<?php echo ORD_JS_INACTIVE_B; ?>';
var show_status          = '<?php echo ($account_type == "v") ? AP_SHOW_CONTACT_STATUS : AR_SHOW_CONTACT_STATUS; ?>';
var warn_form_modified   = '<?php echo ORD_WARN_FORM_MODIFIED; ?>';
var warn_form_has_data   = '<?php echo ORD_WARN_FORM_HAS_DATA; ?>';
var warn_price_sheet     = '<?php echo ORD_PRICE_SHEET_WARN; ?>';
var cannot_convert_quote = '<?php echo ORD_JS_CANNOT_CONVERT_QUOTE; ?>';
var cannot_convert_so    = '<?php echo ORD_JS_CANNOT_CONVERT_SO; ?>';
var sku_not_unique       = '<?php echo ORD_JS_SKU_NOT_UNIQUE; ?>';
var no_contact_id        = '<?php echo ORD_JS_NO_CID; ?>';
var defaultPostDate      = '<?php echo date(DATE_FORMAT, time()); ?>';
var defaultTerminalDate  = '<?php echo $req_date; ?>';
var defaultCurrency      = '<?php echo DEFAULT_CURRENCY; ?>';
var tax_freight          = '<?php echo ($account_type == "c") ? AR_ADD_SALES_TAX_TO_SHIPPING : AP_ADD_SALES_TAX_TO_SHIPPING; ?>';
var tax_before_discount  = '<?php echo ($account_type == "c") ? AR_TAX_BEFORE_DISCOUNT : AP_TAX_BEFORE_DISCOUNT; ?>';
var lowStockNoProducts   = '<?php echo LOW_STOCK_NO_PRODUCTS ?> ';
var lowStockProcessed    = '<?php echo LOW_STOCK_PROCESSED ?> ';
var lowStockNoVendor     = '<?php echo LOW_STOCK_NO_VENDOR?>';
var lowStockExecuted     = '<?php echo LOW_STOCK_ALREADY_EXECUTED?>';
var lowStockExecute      = true;
var custCreditLimit      = <?php echo AR_CREDIT_LIMIT_AMOUNT; ?>;
var applyCreditLimit     = '<?php echo APPLY_CUSTOMER_CREDIT_LIMIT ?>';
var adminNotValidated    = '<?php echo TEXT_ADMIN_NOT_VALIDATED; ?>';
var action				 = '<?php echo $_REQUEST['action']; ?>';
var ItemIsAlreadyOnOrder = '<?php echo ITEM_IS_ALREADY_ON_ORDER; ?>';
var ItemIsOnHand		 = '<?php echo ITEM_IS_ON_HAND; ?>';
var ItemIsInactive		 = '<?php echo ITEM_IS_INACTIVE; ?>';
var ItemIsOutOfStockAndInactive  = '<?php echo ITEM_IS_OUT_OF_STOCK_AND_INACTIVE; ?>';
var SalesOrderForItem	 = '<?php echo THERE_IS_SALES_ORDER_FOR_THIS_ITEM; ?>';
var ItemIsOnOrder		 = '<?php echo ITEM_IS_ON_ORDER; ?>';
var ItemMustBeOrderd	 = '<?php echo ITEM_MUST_BE_ORDERD; ?>';
<?php 
echo js_calendar_init($cal_order);
if ($template_options['terminal_date']) echo js_calendar_init($cal_terminal);
?>
// List the currency codes and exchange rates
<?php if (ENABLE_MULTI_CURRENCY) echo $currencies->build_js_currency_arrays(); ?>
// List the gl accounts for line item pull downs
<?php echo $js_gl_array; ?>
// List the tax rates
<?php echo $js_tax_rates; ?>
// List the active projects
<?php echo $js_proj_list; ?>
// list the freight options
<?php echo $shipping_methods; ?>

function init() {
  document.getElementById('ship_to_select').style.visibility = 'hidden';
  document.getElementById('bill_to_select').style.visibility = 'hidden';
  document.getElementById('ship_to_search').innerHTML = '&nbsp;'; // turn off ship to id search
  $('#override_order').dialog({ autoOpen:false, width:600 });
<?php 
  if ($error && isset($order->shipper_code)) {
    $values = explode(':', $order->shipper_code);
    echo '  document.getElementById("ship_carrier").value = "' . $values[0] . '";' . chr(10);
    echo '  buildFreightDropdown();';
    echo '  document.getElementById("ship_service").value = "' . $values[1] . '";' . chr(10);
  } else {
    echo '  buildFreightDropdown();';
  }
?>
  setField('sku_1',text_search);
  // change color of the bill and ship address fields if they are the default values
  var add_id;
  for (var i=0; i<add_array.length; i++) {
	add_id = add_array[i];
	if (document.getElementById('bill_'+add_id).value == '') {
	  document.getElementById('bill_'+add_id).value = default_array[i];
	}
	if (document.getElementById('bill_'+add_id).value == default_array[i]) {
	  if (add_id != 'country_code') document.getElementById('bill_'+add_id).style.color = inactive_text_color;
	}
	if (document.getElementById('ship_'+add_id).value == default_array[i]) {
	  if (add_id != 'country_code') document.getElementById('ship_'+add_id).style.color = inactive_text_color;
	}
  }

  document.orders.elements['search'].focus();

<?php 
  if (!$error) echo 'DropShipView(document.orders);' . "\n";
  if (!$error && $_REQUEST['action'] == 'print') {
    echo '  force_clear = true;' . "\n";
	echo '  ClearForm();' . "\n";
	echo '  var printWin = window.open("index.php?module=phreeform&page=popup_gen&gID=' . POPUP_FORM_TYPE . '&date=a&xfld=journal_main.id&xcr=EQUAL&xmin=' . $order->id . '","popup_gen","width=700px,height=550px,resizable=1,scrollbars=1,top=150px,left=200px");' . "\n";
    echo '  printWin.focus();' . "\n";
  }
  if (!$error && $_REQUEST['action'] == 'email') {
    echo '  force_clear = true;' . "\n";
	echo '  ClearForm();' . "\n";
	echo '  var printWin = window.open("index.php?module=phreebooks&page=popup_email&oID=' . $order->id . '","forms","width=500px,height=350px,resizable=1,scrollbars=1,top=150px,left=200px");' . "\n";
    echo '  printWin.focus()' . "\n";
  }
  if ($_REQUEST['action'] == 'edit')   echo '  ajaxOrderData(0, ' . $oID . ', ' . JOURNAL_ID . ', false, false);' . "\n";
  if ($_REQUEST['action'] == 'prc_so') echo '  ajaxOrderData(0, ' . $oID . ', ' . JOURNAL_ID . ', true, false);' . "\n";
  if (ORD_ENABLE_LINE_ITEM_BAR_CODE) echo 'refreshOrderClock();'; 
?>
  $("#search").change(function(){
	if(document.getElementById('search').value != ''){ AccountList(false); }
  });
}

function check_form() {
  var error = 0;
  var i, stock, qty, inactive, message;
  var error_message = "<?php echo JS_ERROR; ?>";
  var todo = document.getElementById('action').value;
  if (single_line_list=='1') {
	var numRows = document.getElementById('item_table').rows.length;
  } else {
	var numRows = document.getElementById('item_table').rows.length/2;
  }

  // With edit of order and recur, ask if roll through future entries or only this entry
  if (document.getElementById('id').value != "" && document.getElementById('recur_id').value > 0) {
	switch (todo) {
	  case 'delete':
		message = '<?php echo ORD_JS_RECUR_DEL_ROLL_REQD; ?>';
		break;
	  default:
	  case 'save':
		message = '<?php echo ORD_JS_RECUR_ROLL_REQD; ?>';
	}
	if (confirm(message)) {
	  document.getElementById('recur_frequency').value = '1';
	} else {
	  document.getElementById('recur_frequency').value = '0';
	}		    
  }

  switch (journalID) {
	case  '6':
	case  '7':
	  // Check for purchase_invoice_id exists with a recurring entry
	  if (document.getElementById('purchase_invoice_id').value == "" && document.getElementById('recur_id').value > 0) {
		error_message += "<?php echo ORD_JS_RECUR_NO_INVOICE; ?>";
		error = 1; // exit the script
		break;
	  }
	  // validate that for purchases, either the waiting box needs to be checked or an invoice number needs to be entered
	  if (document.orders.purchase_invoice_id.value == "" && !document.orders.waiting.checked) {
		error_message += "<?php echo ORD_JS_WAITING_FOR_PAYMENT; ?>";
		error = 1; // exit the script
	  }
	  break;
	case  '9':
	case '10':
	case '12':
	  //validate item status (inactive, out of stock [SO] etc.)
	  for (var i=1; i<numRows; i++) {
		if (document.getElementById('inactive_'+i).value=='1') {
		  if (!confirm(inactive_a + document.getElementById('sku_'+i).value + inactive_b)) return false;
		}
		if (document.getElementById('stock_'+i).value=='NA') continue; // skip if we don't care about inventory
		qty = (journalID == '12') ? parseFloat(document.getElementById('pstd_'+i).value) : parseFloat(document.getElementById('qty_'+i).value);
		stock = parseFloat(document.getElementById('stock_'+i).value);
		if (qty > stock) {
		  if (!confirm(no_stock_a + document.getElementById('sku_'+i).value + no_stock_b + stock + no_stock_c)) return false;
		}
	  }
	  break;
	case  '3':
	case  '4':
	case '13':
	case '18':
	case '20':
	default:
  }

  if (error == 1) {
    alert(error_message);
    return false;
  }
  return true;
}

function checkEnterEvent(event, row){
	if(event.keyCode == 13){//enter event
		var value = addInvRow();
		document.getElementById('sku_'+value).focus();
	}
}

// Insert other page specific functions here.
function salesTaxes(id, text, rate) {
  this.id   = id;
  this.text = text;
  this.rate = rate;
}

<?php if (ORD_ENABLE_LINE_ITEM_BAR_CODE) { ?>
function refreshOrderClock() {
  if (resClockID) {
    clearTimeout(resClockID);
    resClockID = 0;
  }
  if (setId) { // call the ajax to load the inventory info
    var upc = document.getElementById('sku_'+setId).value;
    if (upc != text_search && upc.length == skuLength) {
      var acct = document.getElementById('bill_acct_id').value;
      switch (journalID) {
	    case  '3':
	    case  '4':
	    case  '9':
	    case '10':
		  var qty = document.getElementById('qty_'+setId).value;
		  break;
	    case  '6':
	    case  '7':
	    case '12':
	    case '13':
	    case '18':
	    case '20':
		  var qty = document.getElementById('pstd_'+setId).value;
		  break;
	    default:
      }
	  $.ajax({
		type: "GET",
		url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&cID='+acct+'&qty='+qty+'&upc='+upc+'&rID='+setId+'&jID='+journalID,
		dataType: ($.browser.msie) ? "text" : "xml",
		error: function(XMLHttpRequest, textStatus, errorThrown) {
		  alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
		},
		success: fillInventory
	  });
      setId = 0;
	}
  }
  resClockID = setTimeout("refreshOrderClock()", 250);
}
<?php } ?>

// -->
</script>
<script type="text/javascript" src="modules/phreebooks/javascript/orders.js"></script>
