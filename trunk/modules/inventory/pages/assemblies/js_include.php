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
//  Path: /modules/inventory/pages/assemblies/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var securityLevel = <?php echo $security_level; ?>;
<?php echo js_calendar_init($cal_assy); ?>

function init() {
  document.getElementById('sku_1').focus();
<?php if ($_REQUEST['action'] == 'edit') echo '  EditAssembly(' . $oID . ')'; ?>

}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";
  var sku = document.getElementById('sku_1').value;
  if (sku == '') { // check for sku not blank
  	error_message += '<?php echo JS_NO_SKU_ENTERED; ?>';
	error = 1;
  }
  var qty = document.getElementById('qty_1').value;
  if (qty == '' || qty == '0') { // check for quantity non-zero
  	error_message += '<?php echo JS_ASSY_VALUE_ZERO; ?>';
	error = 1;
  }
  if (error == 1) {
	$.messager.alert("Processing error",error_message,"error");
    return false;
  }
  return true;
}

// Insert other page specific functions here.
function clearForm() {
  document.getElementById('id').value                  = 0;
  document.getElementById('store_id').value            = 0;
  document.getElementById('purchase_invoice_id').value = '';
  document.getElementById('post_date').value           = '<?php echo date(DATE_FORMAT); ?>';
  document.getElementById('sku_1').value               = '';
  document.getElementById('serial_1').value            = '';
  document.getElementById('desc_1').value              = '';
  document.getElementById('stock_1').value             = '0';
  document.getElementById('qty_1').value               = '';
  document.getElementById('bal_1').value               = '';
  // delete the current rows, if any
  while (document.getElementById("item_table").rows.length > 0) document.getElementById("item_table").deleteRow(-1);
}

function InventoryList(rowCnt) {
  var bID = document.getElementById('store_id').value;
  var sku = document.getElementById('sku_1').value;
  window.open("index.php?module=inventory&page=popup_inv&list=1&type=v&f1=ma&rowID="+rowCnt+"&storeID="+bID+"&search_text="+sku,"inventory","width=700,height=550,resizable=1,scrollbars=1,top=150,left=200");
}

function OpenAssyList() {
  window.open("index.php?module=inventory&page=popup_assy&list=1","inv_assy_open","width=700,height=550,resizable=1,scrollbars=1,top=150,left=200");
}

function EditAssembly(rID) {
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreebooks&page=ajax&op=load_record&rID='+rID,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
    success: processEditAssembly
  });
}

function processEditAssembly(sXml) {
  var sku = '';
  var xml = parseXml(sXml);
  if (!xml) return;
  clearForm();
  var id = $(xml).find("id").first().text();
  document.getElementById('id').value                  = id;
  document.getElementById('store_id').value            = $(xml).find("store_id").text();
  document.getElementById('purchase_invoice_id').value = $(xml).find("purchase_invoice_id").text();
  document.getElementById('post_date').value           = formatDate($(xml).find("post_date").first().text());
  // turn off some icons
  if (id && securityLevel < 3) removeElement('tb_main_0', 'tb_icon_save');
  // fill item rows
  $(xml).find("items").each(function() {
	var type = $(this).find("gl_type").text();
	switch (type) {
	  case 'ttl':
	    break;
	  case 'asy':
		sku = $(this).find("sku").text();
		document.getElementById('sku_1').value    = sku;
		document.getElementById('serial_1').value = $(this).find("serialize").text();
		document.getElementById('desc_1').value   = $(this).find("description").text();
		document.getElementById('qty_1').value    = $(this).find("qty").text();
	  default: // do nothing
	}
  });
  loadSkuStock(sku);
}

function loadSkuStock(sku) {
  var bID = document.getElementById('store_id').value;
  $.ajax({
    type: "GET",
	url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&sku='+sku+'&bID='+bID+'&strict=1',
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: processSkuDetails
  });
}

function loadSkuDetails(iID, rowCnt) {
  var bID = document.getElementById('store_id').value;
  var sku = iID==0 ? document.getElementById('sku_'+rowCnt).value : '';
  if (sku == text_search) return;
  $.ajax({
    type: "GET",
	url: 'index.php?module=inventory&page=ajax&op=inv_details&iID='+iID+'&sku='+sku+'&bID='+bID+'&rID='+rowCnt,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: processSkuDetails
  });
}

function processSkuDetails(sXml) { // call back function
  var text = '';
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("result").text()) return; // not enough or too many hits
  document.getElementById('sku_1').value       = $(xml).find("sku").text();
  document.getElementById('sku_1').style.color = '';
  if (document.getElementById('desc_1').value == '') { // do not overwrite if already there
    document.getElementById('desc_1').value    = $(xml).find("description_purchase").text();
  }
  document.getElementById('stock_1').value     = $(xml).find("branch_qty_in_stock").text();
  var type = $(xml).find("inventory_type").text();
  if (type=='sr' || type=='sa') document.getElementById('serial_row').style.display = '';
  // clear list and add the new data
  while (document.getElementById("item_table").rows.length > 0) document.getElementById("item_table").deleteRow(-1);
  var j = 1;
  $(xml).find("bom").each(function() {
	addListRow();
	if ($(this).find("bom_quantity_on_hand").text() == 'NA') {
	  var stock = '-';	
	} else {
	  var stock = parseFloat($(this).find("bom_quantity_on_hand").text());
	  if (document.getElementById('id').value > 0) { // add back in edit amount
	    stock += parseFloat(document.getElementById('qty_1').value) * parseFloat($(this).find("bom_qty").text());
	  }
	}
	document.getElementById('assy_sku_'+j).value  = $(this).find("bom_sku").text();
	document.getElementById('assy_desc_'+j).value = $(this).find("bom_description_short").text();
	document.getElementById('qty_reqd_'+j).value  = $(this).find("bom_qty").text();
	document.getElementById('assy_qty_'+j).value  = $(this).find("bom_qty").text();
	document.getElementById('stk_'+j).value       = stock;
    j++;
  });
  updateBalance();
}

function addListRow() {
	var cell    = new Array();
	var newRow  = document.getElementById("item_table").insertRow(-1);
	var rowCnt  = newRow.rowIndex;
	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = '<input type="text" name="assy_sku_'+rowCnt+'" id="assy_sku_'+rowCnt+'" readonly="readonly" size="15">';
	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = '<input type="text" name="assy_desc_'+rowCnt+'" id="assy_desc_'+rowCnt+'" readonly="readonly" size="35">';
	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = '<input type="hidden" name="qty_reqd_'+rowCnt+'" id="qty_reqd_'+rowCnt+'"><input type="text" name="assy_qty_'+rowCnt+'" id="assy_qty_'+rowCnt+'" readonly="readonly" style="text-align:right" size="10">';
	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = '<input type="text" name="stk_'+rowCnt+'" id="stk_'+rowCnt+'" readonly="readonly" style="text-align:right" size="10">';
}

function checkBalances() {
  var qtyCheck;
  var error = false;
  for (var i=0, j=1; i<document.getElementById('item_table').rows.length; i++, j++) {
	if (isNaN(document.getElementById('stk_'+j).value)) continue;
	qtyCheck = parseFloat(document.getElementById('stk_'+j).value) - parseFloat(document.getElementById('assy_qty_'+j).value);
	if (qtyCheck < 0) {
	  document.getElementById('stk_'+j).style.color = 'red';
	  error = true;
	} else {
	  document.getElementById('stk_'+j).style.color = '';
	}
  }
  if (error) $.messager.alert("Error",'<?php echo JS_NOT_ENOUGH_PARTS; ?>',"error");
}

function updateBalance() {
	var qtyMin, newQtyNeeded, totalNeeded, totalAvailable;
	var stock = parseFloat(document.getElementById('stock_1').value);
	if (isNaN(stock)) stock = 0;
	var build = parseFloat(document.getElementById('qty_1').value);
	if (isNaN(build)) build = 0;
	var totalNeeded    = 0;
	var totalAvailable = 0;
	for (var i=0, j=1; i<document.getElementById('item_table').rows.length; i++, j++) {
		qtyMin          = parseFloat(document.getElementById('qty_reqd_'+j).value);
		newQtyNeeded    = build * qtyMin;
		totalNeeded    += newQtyNeeded;
		totalAvailable += isNaN(document.getElementById('stk_'+j).value) ? 0 : parseFloat(document.getElementById('stk_'+j).value);
		document.getElementById('assy_qty_'+j).value = newQtyNeeded;
	}
	document.getElementById('total_needed').value = totalNeeded;
	document.getElementById('total_stock').value  = totalAvailable;
	var st = new String(stock + build);
	document.getElementById('bal_1').value = st;
	checkBalances();
}

// -->
</script>