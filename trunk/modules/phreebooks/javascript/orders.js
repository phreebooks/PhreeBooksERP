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
//  Path: /modules/phreebooks/javascript/orders.js
//

var bill_add = new Array(0);
var ship_add = new Array(0);
var force_clear = false;
var custCreditLimit = '0';
//var default_sales_tax = -1; // set in js_include.php

function ClearForm() {
  var numRows = 0;
  clearAddress('bill');
  clearAddress('ship');
  document.getElementById('search').value             = text_search;
  document.getElementById('search').style.color       = inactive_text_color;
  document.getElementById('purchase_invoice_id').value= '';
  document.getElementById('id').value                 = '';
  document.getElementById('recur_id').value           = '0';
  document.getElementById('recur_frequency').value    = '0';
  document.getElementById('terms').value              = '0';
  document.getElementById('terms_text').value         = text_terms;
  document.getElementById('item_count').value         = '0';
  document.getElementById('weight').value             = '0';
  document.getElementById('printed').value            = '0';
  document.getElementById('so_po_ref_id').value       = '0';
  document.getElementById('purch_order_id').value     = '';
  document.getElementById('store_id').value           = '0';
  document.getElementById('post_date').value          = defaultPostDate;
  document.getElementById('terminal_date').value      = defaultTerminalDate;
  document.getElementById('gl_acct_id').value         = default_GL_acct;
  document.getElementById('disc_gl_acct_id').value    = default_disc_acct;
  document.getElementById('disc_percent').value       = formatted_zero;
  document.getElementById('discount').value           = formatted_zero;
  document.getElementById('ship_gl_acct_id').value    = default_freight_acct;
  document.getElementById('ship_carrier').value       = '';
  document.getElementById('ship_service').value       = '';
  document.getElementById('freight').value            = formatted_zero;
  document.getElementById('sales_tax').value          = formatted_zero;
  document.getElementById('total').value              = formatted_zero;
  document.getElementById('display_currency').value   = defaultCurrency;
  document.getElementById('currencies_code').value    = defaultCurrency;
  document.getElementById('currencies_value').value   = '1';
  // handle checkboxes
  document.getElementById('waiting').checked          = false;
  document.getElementById('drop_ship').checked        = false;
  document.getElementById('closed').checked           = false;
  document.getElementById('bill_add_update').checked  = false;
  document.getElementById('ship_add_update').checked  = false;
  $("#closed_text").hide();

  document.getElementById('ship_to_search').innerHTML = '&nbsp;'; // turn off ship to id search
  document.getElementById('purchase_invoice_id').readOnly = false;
  // remove all item rows and add a new blank one
  var desc = document.getElementById('desc_1').value;
  var sku  = document.getElementById('sku_1').value;
  if ((sku != '' && sku != text_search) || desc != '') {
	if (force_clear || confirm(warn_form_has_data)) {
      while (document.getElementById('item_table').rows.length > 0) removeInvRow(1);
	  addInvRow();
	} else {
	  if (single_line_list == '1') {
	    numRows = document.getElementById('item_table').rows.length;
	  } else {
		numRows = document.getElementById('item_table').rows.length/2;
	  }
	  for (var i=1; i<=numRows; i++) {
		document.getElementById('id_'+i).value = 0;
		document.getElementById('so_po_item_ref_id_'+i).value = 0;
	  }
	}
  }
}

function clearAddress(type) {
  for (var i=0; i<add_array.length; i++) {
	var add_id = add_array[i];
	document.getElementById(type+'_acct_id').value      = '';
	document.getElementById(type+'_address_id').value   = '';
	document.getElementById(type+'_country_code').value = store_country_code;
	if (type=='bill') {
	  if (add_id != 'country_code') document.getElementById(type+'_'+add_id).style.color = inactive_text_color;
	  document.getElementById(type+'_'+add_id).value = default_array[i];
	}
  	document.getElementById(type+'_to_select').style.visibility = 'hidden';
  	if (document.getElementById(type+'_to_select')) {
      while (document.getElementById(type+'_to_select').options.length) {
	    document.getElementById(type+'_to_select').remove(0);
      }
  	}
	if (type=='ship') {
	  switch (journalID) {
		case '3':
		case '4':
		case '6':
		case '7':
		case '20':
		case '21':
		  document.getElementById(type+'_'+add_id).style.color = '';
		  document.getElementById(type+'_'+add_id).value = company_array[i];
		  break;
		case '9':
		case '10':
		case '12':
		case '13':
		case '18':
		case '19':
			if (add_id != 'country_code') document.getElementById(type+'_'+add_id).style.color = inactive_text_color;
			document.getElementById(type+'_'+add_id).value = default_array[i];
			break;
		default:
	  }
	}
  }
}

function ajaxOrderData(cID, oID, jID, open_order, ship_only) {
  var open_so_po = (open_order) ? '1' : '0';
  var only_ship  = (ship_only)  ? '1' : '0';
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreebooks&page=ajax&op=load_order&cID='+cID+'&oID='+oID+'&jID='+jID+'&so_po='+open_so_po+'&ship_only='+only_ship,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
    },
	success: fillOrderData
  });
}

function fillOrderData(sXml) { // edit response form fill
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("OrderData").length) {
	orderFillAddress(xml, 'bill', false);
	orderFillAddress(xml, 'ship', false);
	fillOrder(xml);
  } else if ($(xml).find("BillContact").length) {
    orderFillAddress(xml, 'bill', true);
	orderFillAddress(xml, 'ship', false);
  } else if ($(xml).find("ShipContact").length) {
    orderFillAddress(xml, 'ship', true);
  }
}

function orderFillAddress(xml, type, fill_address) {
  var newOpt, mainType;
  while (document.getElementById(type+'_to_select').options.length) document.getElementById(type+'_to_select').remove(0);
  var cTag = (type == 'ship' ? 'ShipContact' : 'BillContact');
  $(xml).find(cTag).each(function() {
    var id = $(this).find("id").text();
	if (!id) return;
    mainType = $(this).find("type").first().text() + 'm';
    switch (type) {
	  default:
      case 'bill':
		bill_add          = this;
		default_sales_tax = $(this).find("tax_id").text();
		default_inv_acct  = ($(this).find("gl_type_account").text()) ? $(this).find("gl_type_account").text() : default_inv_acct;
		insertValue('bill_acct_id',    id);
		insertValue('terms',           $(this).find("special_terms").text());
		insertValue('terms_text',      $(this).find("terms_text").text());
		insertValue('search',          $(this).find("short_name").text());
		insertValue('acct_1',          default_inv_acct);
		if($(this).find("dept_rep_id").text() != '')     insertValue('rep_id',          $(this).find("dept_rep_id").text());
		if($(this).find("ship_gl_acct_id").text() != '') insertValue('ship_gl_acct_id', $(this).find("ship_gl_acct_id").text());
		custCreditLimit              = $(this).find("credit_remaining").text();
		var rowCnt = 1;
		while(true) {
		  if (!document.getElementById('tax_'+rowCnt)) break;
		  document.getElementById('tax_'+rowCnt).value = $(this).find("tax_id").text();
		  rowCnt++;
		}
		if (show_status == '1') {
		  window.open("index.php?module=phreebooks&page=popup_status&form=orders&id="+id,"contact_status","width=500px,height=300px,resizable=0,scrollbars=1,top=150,left=200");
		}
		break;
	  case 'ship':
	    ship_add = this;
		insertValue('ship_acct_id', id);
	    insertValue('ship_search',  $(this).find("short_name").text());
	    break;
    }
	//now fill the addresses
    var iIndex = 0;
    $(this).find("Address").each(function() {
      newOpt = document.createElement("option");
	  newOpt.text = $(this).find("primary_name").text() + ', ' + $(this).find("city_town").text() + ', ' + $(this).find("postal_code").text();
	  document.getElementById(type+'_to_select').options.add(newOpt);
	  document.getElementById(type+'_to_select').options[iIndex].value = $(this).find("address_id").text();
      if (fill_address && $(this).find("type").text() == mainType) { // also fill the fields
	    insertValue(type+'_address_id', $(this).find("address_id").text());
	    $(this).children().each (function() {
		  var tagName = this.tagName;
		  if (document.getElementById(type+'_'+tagName)) {
		    document.getElementById(type+'_'+tagName).value = $(this).text();
		    document.getElementById(type+'_'+tagName).style.color = '';
		  }
	    });
	  }
	  iIndex++;
    });
    // add a option for creating a new address
    newOpt = document.createElement("option");
    newOpt.text = text_enter_new;
    document.getElementById(type+'_to_select').options.add(newOpt);	
    document.getElementById(type+'_to_select').options[iIndex].value = '0';
    document.getElementById(type+'_to_select').style.visibility      = 'visible';
    document.getElementById(type+'_to_select').disabled              = false;
  });
}

function fillOrder(xml) {
  $(xml).find("OrderData").each(function() {
	$(this).children().each (function() {
	  var tagName = this.tagName;
	  if (document.getElementById(tagName)) {
	    document.getElementById(tagName).value = $(this).first().text();
	    document.getElementById(tagName).style.color = '';
	  }
	});
    // fix some special cases, checkboxes, and active fields
    insertValue('bill_to_select', $(this).find("bill_address_id").text());
    insertValue('ship_to_select', $(this).find("ship_address_id").text());
    document.getElementById('display_currency').value = $(this).find("currencies_code").text();
    document.getElementById('closed').checked         = $(this).find("cb_closed").text()    == '1' ? true : false;
    document.getElementById('waiting').checked        = $(this).find("cb_waiting").text()   == '1' ? true : false;
    document.getElementById('drop_ship').checked      = $(this).find("cb_drop_ship").text() == '1' ? true : false;
	if ($(this).find("cb_waiting").text() == '1') document.getElementById('waiting').value = '1'; // if hidden set value
//
// Uncomment to set Sales Invoice number = Sales Order number when invoicing a Sales Order
//  if (journalID=='12' && $(this).find("purch_order_num").text()) document.getElementById('purchase_invoice_id').value = $(this).find("purch_order_num").text();
//
    if ($(this).find("id").first().text() && journalID != '6' && journalID != '7') document.getElementById('purchase_invoice_id').readOnly = true;
    buildFreightDropdown();
    insertValue('ship_service', $(this).find("ship_service").text());
    if ($(this).find("cb_closed").text() == '1') {
	  switch (journalID) {
	    case  '6':
	    case  '7':
	    case '12':
	    case '13':
	      $("#closed_text").show();
		  removeElement('tb_main_0', 'tb_icon_payment');
		  break;
	    default:
	  }
    }
    // disable the purchase_invoice_id field since it cannot change, except purchase/receive
    if ($(this).find("id").first().text() && journalID != '6' && journalID != '7' && journalID != '21') {
	  document.getElementById('purchase_invoice_id').readOnly = true;
    }
    if ($(this).find("id").first().text() && $(this).find("attach_exist").text() == 1) {
	  document.getElementById('show_attach').style.display = ''; // show attachment button and delete checkbox if it exists
    }
    if ($(this).find("id").first().text() && securityLevel < 3) { // turn off some icons
	  removeElement('tb_main_0', 'tb_icon_print');
	  removeElement('tb_main_0', 'tb_icon_save');
	  removeElement('tb_main_0', 'tb_icon_payment');
	  removeElement('tb_main_0', 'tb_icon_save_as_so');
	  removeElement('tb_main_0', 'tb_icon_ship_all');
    }
	removeElement('tb_main_0', 'tb_icon_recur');
    // fill inventory rows and add a new blank one
    var order_discount = formatted_zero;
    var jIndex = 1;
    $(this).find("Item").each(function() {
	  var gl_type = $(this).find("gl_type").text();
      switch (gl_type) {
	    case 'ttl':
	    case 'tax': // the total and tax will be recalculated when the form is loaded
	      break;
	    case 'dsc':
	      order_discount =                            $(this).find("total").text();
		  if ($(this).find("gl_account").text()) insertValue('disc_gl_acct_id', $(this).find("gl_account").text());
		  break;
	    case 'frt':
		  insertValue('freight',                      $(this).find("total").text());
		  if ($(this).find("gl_account").text()) insertValue('ship_gl_acct_id', $(this).find("gl_account").text());
		  break;
	    case 'soo':
	    case 'sos':
	    case 'poo':
	    case 'por':
	      if (action == 'prc_so' && parseFloat($(this).find("purch_package_quantity").text()) > 0 ){
	    	quantity  = $(this).find("qty").text() * $(this).find("purch_package_quantity").text();
	    	unitPrice = formatCurrency(cleanCurrency($(this).find("unit_price").text()) / $(this).find("purch_package_quantity").text());
	      }else{
	        quantity  = $(this).find("qty").text();
	    	unitPrice = $(this).find("unit_price").text();
	      }
		  insertValue('id_' + jIndex,                $(this).find("id").text());
		  insertValue('item_cnt_' + jIndex,          $(this).find("item_cnt").text());
		  insertValue('so_po_item_ref_id_' + jIndex, $(this).find("so_po_item_ref_id").text());
		  insertValue('qty_' + jIndex,               quantity);
		  insertValue('pstd_' + jIndex,              $(this).find("pstd").text());
		  insertValue('sku_'  + jIndex,              $(this).find("sku").text());
		  insertValue('desc_'  + jIndex,             $(this).find("description").text());
		  insertValue('proj_'  + jIndex,             $(this).find("proj_id").text());
		  insertValue('date_1_'  + jIndex,           $(this).find("date_1").text());
		  insertValue('acct_'  + jIndex,             $(this).find("gl_account").text());
		  insertValue('tax_'  + jIndex,              $(this).find("taxable").text());
		  insertValue('full_'  + jIndex,             $(this).find("full_price").text());
		  insertValue('weight_'  + jIndex,           $(this).find("weight").text());
		  insertValue('serial_'  + jIndex,           $(this).find("serialize").text());
		  insertValue('stock_'  + jIndex,            $(this).find("stock").text());
		  insertValue('inactive_'  + jIndex,         $(this).find("inactive").text());
		  insertValue('lead_' + jIndex,              $(this).find("lead").text());
		  insertValue('price_' + jIndex,             unitPrice);
		  insertValue('total_' + jIndex,             $(this).find("total").text());
		  if(journalID == 4){
			  if(parseFloat($(this).find("purch_package_quantity").text()) > 0 ){
				  insertValue('purch_package_quantity_' + jIndex, $(this).find("purch_package_quantity").text());
			  }else{
				  insertValue('purch_package_quantity_' + jIndex, 1);
			  }
		  }
		  var imgSerial = document.getElementById('imgSerial_'+jIndex);
		  if (imgSerial != null && $(xml).find("inventory_type").text() == 'sr') {
			  document.getElementById('imgSerial_'+jIndex).style.display = '';
		  }
		  document.getElementById('sku_prop_'+jIndex).style.display = '';
	      if ($(this).find("so_po_item_ref_id").text() || ((journalID == 4 || journalID == 10) && $(this).find("pstd").text())) {
	        // don't allow sku to change, hide the sku search icon
	        document.getElementById('sku_' + jIndex).readOnly = true;
	        document.getElementById('sku_open_' + jIndex).style.display = 'none';
	        // don't allow row to be removed, turn off the delete icon
	        rowOffset = (single_line_list == '1') ? jIndex-1 : (jIndex*2)-2;
	        document.getElementById("item_table").rows[rowOffset].cells[0].innerHTML = '&nbsp;';
	      }
	      
	      $('#qty_'+jIndex).change("change", function(){ var rIdx=$(this).closest('tr').prevAll().length+1; updateRowTotal(rIdx, true);});
	      $('#pstd_'+jIndex).change("change",function(){ var rIdx=$(this).closest('tr').prevAll().length+1; updateRowTotal(rIdx, true);});
		  updateRowTotal(jIndex, false);
		  addInvRow();
		  jIndex++;
	    default: // do nothing
	  }
    });
    insertValue('discount', order_discount);
    calculateDiscountPercent();
  });
}

function AccountList(override) {
  var guess = document.getElementById('search').value;
  if (guess != text_search && guess != '' && !override) {
    $.ajax({
	  type: "GET",
	  url: 'index.php?module=phreebooks&page=ajax&op=load_searches&jID='+journalID+'&type='+account_type+'&guess='+guess,
	  dataType: ($.browser.msie) ? "text" : "xml",
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
	    alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
	  },
	  success: AccountListResp
    });
  } else { // force the popup
	AccountListResp();
  }
}

function AccountListResp(sXml) {
  var xml = parseXml(sXml);
//  if (!xml) return;
  if ($(xml).find("result").text() == 'success') {
	var cID = $(xml).find("cID").text();
	ajaxOrderData(cID, 0, journalID, false, false);
  } else {
    var fill = '';
    switch (journalID) {
	  case '3':
	  case '4':
	  case '6':
	  case '7':
	  case '20': fill = 'bill'; break;
	  case '9':
	  case '10':
	  case '12':
	  case '13':
	  case '18': fill = 'both'; break;
	  default:
    }
    window.open("index.php?module=contacts&page=popup_accts&type="+account_type+"&fill="+fill+"&jID="+journalID+"&search_text="+document.getElementById('search').value,"accounts","width=850px,height=550px,resizable=1,scrollbars=1,top=150,left=100");
  }
}

function DropShipList(currObj) {
	window.open("index.php?module=contacts&page=popup_accts&type=c&fill=ship&jID="+journalID+"&search_text="+document.getElementById('ship_search').value,"accounts","width=850px,height=550px,resizable=1,scrollbars=1,top=150,left=100");
}

function OpenOrdrList(currObj) {
  window.open("index.php?module=phreebooks&page=popup_orders&jID="+journalID,"search_po","width=700px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
}

function OpenRecurList(currObj) {
  window.open("index.php?module=phreebooks&page=popup_recur&jID="+journalID,"recur","width=400px,height=300px,resizable=1,scrollbars=1,top=150,left=200");
}

function InventoryList(rowCnt) {
  var storeID = document.getElementById('store_id').value;
  var sku     = document.getElementById('sku_'+rowCnt).value;
  var cID     = document.getElementById('bill_acct_id').value;
  if(account_type == 'v' && cID != ''){
	  var strict = 1;
  }else{
	  var strict = 0;
  }
  window.open("index.php?module=inventory&page=popup_inv&f2="+strict+"&type="+account_type+"&rowID="+rowCnt+"&storeID="+storeID+"&cID="+cID+"&search_text="+sku,"inventory","width=700px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
}

function PriceManagerList(elementID) {
  var sku = document.getElementById('sku_'+elementID).value;
  if (!sku || sku == text_search) {
	alert(warn_price_sheet);
	return;
  }
  window.open("index.php?module=inventory&page=popup_prices&rowId="+elementID+"&sku="+sku+"&type="+account_type,"prices","width=550px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
}

function TermsList() {
  var terms = document.getElementById('terms').value;
  window.open("index.php?module=contacts&page=popup_terms&type="+account_type+"&form=orders&val="+terms,"terms","width=500px,height=300px,resizable=1,scrollbars=1,top=150,left=200");
}

function FreightList() {
  window.open("index.php?module=shipping&page=popup_shipping&form=orders","shipping","width=900px,height=650px,resizable=1,scrollbars=1,top=150,left=200");
}

function convertQuote() {
  var id = document.getElementById('id').value;
  if (id != '') {
	window.open("index.php?module=phreebooks&page=popup_convert&oID="+id,"popup_convert","width=500px,height=300px,resizable=1,scrollbars=1,top=150,left=200");
  } else {
    alert(cannot_convert_quote);
  }
}

function convertSO() {
  var id = document.getElementById('id').value;
  if (id != '') {
	window.open("index.php?module=phreebooks&page=popup_convert_po&oID="+id,"popup_convert_po","width=500px,height=300px,resizable=1,scrollbars=1,top=150,left=200");
  } else {
    alert(cannot_convert_so);
  }
}

function serialList(id) {
	switch (journalID) {
		default: // for purchases
			var newChoice = prompt(serial_num_prompt, $('#'+id).val());
			$('#'+id).val(newChoice);
			break;
		case  '7':
		case  '9':
		case '10':
		case '12':
			var curDef  = $("#"+id).val();
			var rowID   = id.replace("serial_", "");
			var sku     = $("#sku_"+rowID).val();
			var storeID = $("#store_id").val();
			window.open("index.php?module=inventory&page=popup_serial&def="+curDef+"&sku="+sku+"&rowID="+rowID+"&storeID="+storeID,"serialize","width=700px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
			break;
	}
}

function openBarCode() {
  window.open("index.php?module=phreebooks&page=popup_bar_code&jID="+journalID,"bar_code","width=300px,height=150px,resizable=1,scrollbars=1,top=110,left=200");
}

function downloadAttachment() {
  document.getElementById('action').value = 'dn_attach';
  document.getElementById('action').form.submit();
}

function DropShipView(currObj) {
	var add_id;
	if (document.getElementById('drop_ship').checked) {
		for (var i=0; i<add_array.length; i++) {
			add_id = add_array[i];
			if (add_id != 'country_code') document.getElementById('ship_'+add_id).style.color = inactive_text_color;
			document.getElementById('ship_'+add_id).value = default_array[i];
		}
		document.getElementById('ship_country_code').value  = store_country_code;
		document.getElementById('ship_add_update').checked  = false;
		document.getElementById('ship_add_update').disabled = false;
		// turn on ship to id search
		document.getElementById('ship_to_search').innerHTML = ship_search_HTML;
	} else {
		while (document.getElementById('ship_to_select').options.length) {
			document.getElementById('ship_to_select').remove(0);
		}
		for (var i=0; i<add_array.length; i++) {
			add_id = add_array[i];
			switch (journalID) {
				case '3':
				case '4':
				case '6':
				case '7':
				case '20': // fill company address
				case '21':
					document.getElementById('ship_'+add_id).style.color = '';
					document.getElementById('ship_'+add_id).value       = company_array[i];
					break;
				case '9':
				case '10':
				case '12':
				case '13':
				case '18': // fill default address text
				case '19':
					if (add_id != 'country_code') document.getElementById('ship_'+add_id).style.color = inactive_text_color;
					document.getElementById('ship_'+add_id).value = default_array[i];
					break;
				default:
			}
		}
		document.getElementById('ship_country_code').value = store_country_code;
		document.getElementById('ship_add_update').checked = false;
		document.getElementById('ship_add_update').disabled = false;
		document.getElementById('ship_to_select').style.visibility = 'hidden';
		document.getElementById('ship_to_search').innerHTML = '&nbsp;'; // turn off ship to id search
	}
}

function fillAddress(type) {
  var index = document.getElementById(type+'_to_select').value;
  var address = '';
  if (type == "bill") address = bill_add;
  if (type == "ship") address = ship_add;
  if (index == '0') { // set to defaults
    document.getElementById(type+'_acct_id').value    = 0;
    document.getElementById(type+'_address_id').value = 0;
    for (var i=0; i<add_array.length; i++) {
	  add_id = add_array[i];
	  if (add_id != 'country_code') document.getElementById(type+'_'+add_id).style.color = inactive_text_color;
	  document.getElementById(type+'_'+add_id).value = default_array[i];
    }
    return;
  }
  $(address).find("Address").each(function() {
    if ($(this).find("address_id").text() == index) {
      document.getElementById(type+'_acct_id').value    = $(this).find("ref_id").text();
      document.getElementById(type+'_address_id').value = (index == 'new') ? '0' : $(this).find("address_id").text();
      var add_id;
      for (var i=0; i<add_array.length; i++) {
	    add_id = add_array[i];
	    if (index != '0' && $(this).find(add_id).text()) {
	      document.getElementById(type+'_'+add_id).style.color = '';
	      document.getElementById(type+'_'+add_id).value = $(this).find(add_id).text();
	    } else {
	      if (add_id != 'country_code') document.getElementById(type+'_'+add_id).style.color = inactive_text_color;
	      document.getElementById(type+'_'+add_id).value = default_array[i];
	    }
      }
	}
  });
}

function copyAddress() {
	document.getElementById('ship_address_id').value = document.getElementById('bill_address_id').value;
	document.getElementById('ship_acct_id').value    = document.getElementById('bill_acct_id').value;
	var add_id;
	for (var i=0; i<add_array.length; i++) {
		add_id = add_array[i];
		if (document.getElementById('bill_'+add_id).value != default_array[i]) {
			document.getElementById('ship_'+add_id).style.color = '';
			document.getElementById('ship_'+add_id).value = document.getElementById('bill_'+add_id).value;
		} else {
			if (add_id != 'country_code') document.getElementById('ship_'+add_id).style.color = inactive_text_color;
			document.getElementById('ship_'+add_id).value = default_array[i];
		}
	}
	document.getElementById('ship_country_code').selectedIndex = document.getElementById('bill_country_code').selectedIndex;
}

function addInvRow() {
  var newCell = '';
  var cell    = '';
  var rowCnt  = 0;
  var newRow  = document.getElementById('item_table').insertRow(-1);
  var newRow2 = '';
  if (single_line_list == '1') {
	var odd = (newRow.rowIndex%2 == 0) ? 'even' : 'odd';
    rowCnt  = newRow.rowIndex;
  } else {
    newRow2 = document.getElementById('item_table').insertRow(-1);
    rowCnt  = (newRow2.rowIndex - 1)/2;
    var odd = ((newRow.rowIndex/2)%2 == 0) ? 'even' : 'odd';
    newRow2.setAttribute("className", odd);
    newRow2.setAttribute("class", odd);
  }
  newRow.setAttribute("className", odd);
  newRow.setAttribute("class", odd);
// NOTE: any change here also need to be made to template form for reload if action fails
    cell    = buildIcon(icon_path+'16x16/emblems/emblem-unreadable.png', image_delete_text, 'onclick="if (confirm(\''+image_delete_msg+'\')) removeInvRow('+rowCnt+');"');
    newCell = newRow.insertCell(-1);
    newCell.innerHTML = cell;
//  if (single_line_list != '1') newCell.rowSpan = 2;
  if (single_line_list == '1') {
    cell    = '<input type="text" name="item_cnt_'+rowCnt+'" id="item_cnt_'+rowCnt+'" value="'+rowCnt+'" size="3" maxlength="3" readonly="readonly" />';
    newCell = newRow.insertCell(-1);
    newCell.innerHTML = cell;
    newCell.align     = 'center';
  }
  cell    = '<input type="text" name="qty_'+rowCnt+'" id="qty_'+rowCnt+'"'+(item_col_1_enable == '1' ? " " : " readonly=\"readonly\"")+' size="7" maxlength="6" style="text-align:right" />';
  newCell = newRow.insertCell(-1);
  newCell.innerHTML = cell;
  newCell.align  = 'center';
  newCell.style.whiteSpace = 'nowrap'; 
  cell    = '<input type="text" name="pstd_'+rowCnt+'" id="pstd_'+rowCnt+'"'+(item_col_2_enable == '1' ? " " : " readonly=\"readonly\"")+' size="7" maxlength="6" style="text-align:right" />';
  switch (journalID) {
    case  '6':
	case  '7':
	case '12':
	case '13':
    case '19':
    case '21':
      cell += '&nbsp;' + buildIcon(icon_path+'16x16/actions/tab-new.png', image_ser_num, 'id="imgSerial_'+rowCnt+'" style="cursor:pointer; display:none;" onclick="serialList(\'serial_'+rowCnt+'\')"');
    default:
  }
  newCell = newRow.insertCell(-1);
  newCell.innerHTML = cell;
  newCell.align  = 'center';
  newCell.style.whiteSpace = 'nowrap'; 
  cell    = '<input type="text" name="sku_'+rowCnt+'" id="sku_'+rowCnt+'" size="'+(max_sku_len+1)+'" maxlength="'+max_sku_len+'" onfocus="clearField(\'sku_'+rowCnt+'\', \''+text_search+'\')" onkeydown="checkEnterEvent(event, '+rowCnt+');" onblur="setField(\'sku_'+rowCnt+'\', \''+text_search+'\'); loadSkuDetails(0, '+rowCnt+')" />&nbsp;';
  cell   += buildIcon(icon_path+'16x16/actions/system-search.png', text_search, 'id="sku_open_'+rowCnt+'" align="top" style="cursor:pointer" onclick="InventoryList('+rowCnt+')"');
  cell   += buildIcon(icon_path+'16x16/actions/document-properties.png', text_properties, 'id="sku_prop_'+rowCnt+'" align="top" style="cursor:pointer; display:none;" onclick="InventoryProp('+rowCnt+')"');
  newCell = newRow.insertCell(-1);
  newCell.innerHTML = cell;
  newCell.align  = 'center';
  newCell.style.whiteSpace = 'nowrap'; 
  // for textarea uncomment below, (No control over input length, truncated to 255 by db) or ...
//  cell = '<textarea name="desc_'+rowCnt+'" id="desc_'+rowCnt+'" cols="'+((single_line_list=='1')?50:110)+'" rows="1" maxlength="255"></textarea>';
  // for standard controlled input, uncomment below
  cell = '<input name="desc_'+rowCnt+'" id="desc_'+rowCnt+'" size="'+((single_line_list=='1')?50:75)+'" maxlength="255" />';
  newCell = newRow.insertCell(-1);
  newCell.innerHTML = cell;
  if (single_line_list != '1') newCell.colSpan = 3;
  // Project field
  if (single_line_list != '1') {
    cell = '<select name="proj_'+rowCnt+'" id="proj_'+rowCnt+'"></select>';
    newCell = newRow.insertCell(-1);
    newCell.innerHTML = cell;
    newCell.colSpan = 2;
    newCell.align   = 'center';
    newCell.style.whiteSpace  = 'nowrap'; 
  }
  // second row ( or continued first row if option selected)
  if (single_line_list != '1') {
	cell    = '<input type="text" name="item_cnt_'+rowCnt+'" id="item_cnt_'+rowCnt+'" value="'+rowCnt+'" size="3" maxlength="3" readonly="readonly" />';
	newCell = newRow2.insertCell(-1);
	newCell.innerHTML = cell;
    cell = '<select name="acct_'+rowCnt+'" id="acct_'+rowCnt+'"></select>';
    newCell = newRow2.insertCell(-1);
  } else {
	cell = htmlComboBox('acct_'+rowCnt, values = '', default_inv_acct, 'size="10"', '220px', '');
    newCell = newRow.insertCell(-1);  }
    newCell.innerHTML = cell;
    newCell.align  = 'center';
    newCell.style.whiteSpace = 'nowrap'; 
    if (single_line_list != '1') newCell.colSpan = 3;
    if (single_line_list != '1') {
    cell  = '<input type="text" name="full_'+rowCnt+'" id="full_'+rowCnt+'" readonly="readonly" size="11" maxlength="10" style="text-align:right" />';
    newCell = newRow2.insertCell(-1);
    newCell.innerHTML = cell;
    newCell.align  = 'center';
    newCell.style.whiteSpace = 'nowrap'; 
    cell  = '<input type="text" name="disc_'+rowCnt+'" id="disc_'+rowCnt+'" readonly="readonly" size="11" maxlength="10" style="text-align:right" />';
    newCell = newRow2.insertCell(-1);
    newCell.innerHTML = cell;
    newCell.align  = 'center';
    newCell.style.whiteSpace = 'nowrap'; 
  }
  cell  = '<input type="text" name="price_'+rowCnt+'" id="price_'+rowCnt+'" size="10" maxlength="15" onchange="updateRowTotal('+rowCnt+', false)" style="text-align:right" />&nbsp;';
  cell += buildIcon(icon_path+'16x16/mimetypes/x-office-spreadsheet.png', text_price_manager, 'align="top" style="cursor:pointer" onclick="PriceManagerList('+rowCnt+')"');
  if (single_line_list != '1') {
    newCell = newRow2.insertCell(-1);
  } else {
    newCell = newRow.insertCell(-1);
  }
  newCell.innerHTML = cell;
  newCell.align  = 'center';
  newCell.style.whiteSpace = 'nowrap'; 
  cell  = '<select name="tax_'+rowCnt+'" id="tax_'+rowCnt+'" onchange="updateRowTotal('+rowCnt+', false)"></select>';
  if (single_line_list != '1') {
    newCell = newRow2.insertCell(-1);
  } else {
    newCell = newRow.insertCell(-1);
  }
  newCell.innerHTML = cell;
  newCell.align  = 'center';
  newCell.style.whiteSpace = 'nowrap'; 
// Hidden fields
  cell  = '<input type="hidden" name="id_'+rowCnt+'" id="id_'+rowCnt+'" value="" />';
  cell += '<input type="hidden" name="so_po_item_ref_id_'+rowCnt+'" id="so_po_item_ref_id_'+rowCnt+'" value="" />';
  cell += '<input type="hidden" name="weight_'+rowCnt+'" id="weight_'+rowCnt+'" value="0" />';
  cell += '<input type="hidden" name="stock_'+rowCnt+'" id="stock_'+rowCnt+'" value="NA" />';
  cell += '<input type="hidden" name="inactive_'+rowCnt+'" id="inactive_'+rowCnt+'" value="0" />';
  cell += '<input type="hidden" name="lead_'+rowCnt+'" id="lead_'+rowCnt+'" value="0" />';
  cell += '<input type="hidden" name="serial_'+rowCnt+'" id="serial_'+rowCnt+'" value="" />';
  cell += '<input type="hidden" name="date_1_'+rowCnt+'" id="date_1_'+rowCnt+'" value="" />';
  cell += '<input type="hidden" name="purch_package_quantity_'+rowCnt+'" id="purch_package_quantity_'+rowCnt+'" value="" />';
  if (single_line_list == '1') {
	cell += '<input type="hidden" name="proj_'+rowCnt+'" id="proj_'+rowCnt+'" value="" />';
    cell += '<input type="hidden" name="full_'+rowCnt+'" id="full_'+rowCnt+'" value="" />';
    cell += '<input type="hidden" name="disc_'+rowCnt+'" id="disc_'+rowCnt+'" value="" />';
  }
// End hidden fields
  cell += '<input type="text" name="total_'+rowCnt+'" id="total_'+rowCnt+'" value="'+formatted_zero+'" size="11" maxlength="20" onchange="updateUnitPrice('+rowCnt+')" style="text-align:right" />';
  if (single_line_list != '1') {
    newCell = newRow2.insertCell(-1);
  } else {
    newCell = newRow.insertCell(-1);
  }
  newCell.innerHTML = cell;
  newCell.align  = 'center';
  newCell.style.whiteSpace = 'nowrap'; 

  // populate the drop downs
  var selElement = (single_line_list == '1') ? ('comboselacct_'+rowCnt) : ('acct_'+rowCnt);
  if (js_gl_array) buildDropDown(selElement, js_gl_array, default_inv_acct);
  if (tax_rates)   buildDropDown('tax_'+rowCnt, tax_rates, default_sales_tax);
  if (proj_list && single_line_list != '1') buildDropDown('proj_'+rowCnt, proj_list, false);

  setField('sku_'+rowCnt, text_search);
  setId = rowCnt; // set the upc auto-reader to the newest line added
  return rowCnt;
}

function removeInvRow(index) {
  var i, offset, newOffset;
  var numRows;
  if (single_line_list == '1') {
	numRows = document.getElementById('item_table').rows.length;
  } else {
	numRows = (document.getElementById('item_table').rows.length)/2;
  }
  // remove row from display by reindexing and then deleting last row
  for (i=index; i<numRows; i++) {
	// move the delete icon from the previous row
	offset = (single_line_list == '1') ? i : i*2;
	newOffset = (single_line_list == '1') ? i-1 : (i*2)-2;
	if (document.getElementById('item_table').rows[offset].cells[0].innerHTML == '&nbsp;') {
	  document.getElementById('item_table').rows[newOffset].cells[0].innerHTML = '&nbsp;';
	} else {
	  document.getElementById('item_table').rows[newOffset].cells[0].innerHTML = delete_icon_HTML + i + ');">';
	}
	document.getElementById('qty_'+i).value               		= document.getElementById('qty_'+(i+1)).value;
	document.getElementById('pstd_'+i).value              		= document.getElementById('pstd_'+(i+1)).value;
	document.getElementById('sku_'+i).value               		= document.getElementById('sku_'+(i+1)).value;
	document.getElementById('sku_'+i).readOnly            		=(document.getElementById('sku_'+(i+1)).readOnly) ? true : false;
	document.getElementById('sku_open_'+i).style.display  		=(document.getElementById('sku_'+(i+1)).readOnly) ? 'none' : '';
	document.getElementById('desc_'+i).value              		= document.getElementById('desc_'+(i+1)).value;
	document.getElementById('proj_'+i).value              		= document.getElementById('proj_'+(i+1)).value;
	document.getElementById('price_'+i).value             		= document.getElementById('price_'+(i+1)).value;
	document.getElementById('acct_'+i).value              		= document.getElementById('acct_'+(i+1)).value;
	document.getElementById('tax_'+i).selectedIndex       		= document.getElementById('tax_'+(i+1)).selectedIndex;
// Hidden fields
	document.getElementById('id_'+i).value                		= document.getElementById('id_'+(i+1)).value;
	document.getElementById('so_po_item_ref_id_'+i).value 		= document.getElementById('so_po_item_ref_id_'+(i+1)).value;
	document.getElementById('weight_'+i).value            		= document.getElementById('weight_'+(i+1)).value;
	document.getElementById('stock_'+i).value             		= document.getElementById('stock_'+(i+1)).value;
	document.getElementById('inactive_'+i).value          		= document.getElementById('inactive_'+(i+1)).value;
	document.getElementById('lead_'+i).value              		= document.getElementById('lead_'+(i+1)).value;
	document.getElementById('serial_'+i).value            		= document.getElementById('serial_'+(i+1)).value;
	document.getElementById('full_'+i).value              		= document.getElementById('full_'+(i+1)).value;
	document.getElementById('disc_'+i).value              		= document.getElementById('disc_'+(i+1)).value;
	if (document.getElementById('purch_package_quantity_'+i))
		document.getElementById('purch_package_quantity_'+i).value=document.getElementById('purch_package_quantity_'+(i+1)).value;
// End hidden fields
	document.getElementById('total_'+i).value             		= document.getElementById('total_'+(i+1)).value;
	document.getElementById('sku_'+i).style.color = (document.getElementById('sku_'+i).value == text_search) ? inactive_text_color : document.getElementById('sku_'+(i+1)).style.color;
  }
  document.getElementById('item_table').deleteRow(-1);
  if (single_line_list != '1') document.getElementById('item_table').deleteRow(-1);
  updateTotalPrices();
} 

function updateRowTotal(rowCnt, useAjax) {
	var qty = 0;
	var unit_price = cleanCurrency(document.getElementById('price_'+rowCnt).value);
	var full_price = cleanCurrency(document.getElementById('full_' +rowCnt).value);
	switch (journalID) {
		case  '3':
		case  '4':
		case  '9':
		case '10': 
		  qty = parseFloat(document.getElementById('qty_'+rowCnt).value);
		  if (isNaN(qty)) qty = 0; // if blank or a non-numeric value is in the qty field, assume zero
		  break;
		case  '6':
		case  '7':
		case '12':
		case '13':
		case '18':
		case '19':
		case '21':
		case '20': 
		  qty = parseFloat(document.getElementById('pstd_'+rowCnt).value);
		  if (isNaN(qty)) qty = 0; // if blank or a non-numeric value is in the pstd field, assume zero
		  break;
		default:
	}
	var total_line = qty * unit_price;
	var total_l = new String(total_line);
	document.getElementById('price_'+rowCnt).value = formatPrecise(unit_price);
	document.getElementById('total_'+rowCnt).value = formatCurrency(total_l);
	// calculate discount
	if (full_price > 0) {
	  var discount = (full_price - unit_price)/full_price;
	  document.getElementById('disc_'+rowCnt).value = new String(Math.round(1000*discount)/10) + ' %';
	}
	updateTotalPrices();
	// call the ajax price sheet update based on customer
	if (useAjax && qty != 0 && sku != '' && sku != text_search) {
	  switch (journalID) {
		case  '9': // only update prices for sales and if no SO was used
		case '10':
		case '12':
		case '19':
		  var sku          = document.getElementById('sku_'+rowCnt).value;
		  var bill_acct_id = document.getElementById('bill_acct_id').value;
		  so_exists        = document.getElementById('so_po_item_ref_id_'+rowCnt).value;
		  if (!so_exists && auto_load_sku) {
		    $.ajax({
			  type: "GET",
			  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuPrice&cID='+bill_acct_id+'&sku='+sku+'&qty='+qty+'&rID='+rowCnt,
			  dataType: ($.browser.msie) ? "text" : "xml",
			  error: function(XMLHttpRequest, textStatus, errorThrown) {
			    alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
			  },
			  success: processSkuPrice
		    });
		  }
		  break;
		default: // no AJAX
	  }
	}
}

// ajax response to price sheet request
function processSkuPrice(sXml) { // call back function
  var xml = parseXml(sXml);
  if (!xml) return;
  var rowCnt = $(xml).find("rID").text();
  if (!rowCnt) return;
  document.getElementById('price_'+rowCnt).value = formatPrecise($(xml).find("sales_price").text());
  updateRowTotal(rowCnt, false);
}

function updateUnitPrice(rowCnt) {
  var qty = 0;
  var total_line = cleanCurrency(document.getElementById('total_'+rowCnt).value);
  document.getElementById('total_'+rowCnt).value = formatCurrency(total_line);
  switch (journalID) {
	case '3':
	case '4':
	case '9':
	case '10':
	  qty = parseFloat(document.getElementById('qty_'+rowCnt).value);
	  if (isNaN(qty)) {
		qty = 1;
		document.getElementById('qty_'+rowCnt).value = qty;
	  }
	  break;
	case '6':
	case '7':
	case '12':
	case '13':
	case '18':
	case '19':
	case '20':
	case '21':
	  qty = parseFloat(document.getElementById('pstd_'+rowCnt).value);
	  if (isNaN(qty)) {
		qty = 1;
		document.getElementById('pstd_'+rowCnt).value = qty;
	  }
	  break;
	default:
  }
  var unit_price = total_line / qty;
  var unit_p = new String(unit_price);
  document.getElementById('price_'+rowCnt).value = formatPrecise(unit_p);
  updateTotalPrices();
}

function updateTotalPrices() {
  var numRows = 0;
  var discount = parseFloat(cleanCurrency(document.getElementById('discount').value));
  if (isNaN(discount)) discount = 0;
  var discountPercent = parseFloat(cleanCurrency(document.getElementById('disc_percent').value));
  if (isNaN(discountPercent)) discountPercent = 0;
  var item_count       = 0;
  var shipment_weight  = 0;
  var subtotal         = 0;
  var taxable_subtotal = 0;
  var freight_def_tax = default_sales_tax;
  var lineTotal        = '';
  if (single_line_list == '1') {
	numRows = document.getElementById('item_table').rows.length;
  } else {
	numRows = document.getElementById('item_table').rows.length/2;
  }
  for (var i=1; i<numRows+1; i++) {
	switch (journalID) {
	  case  '3':
	  case  '4':
	  case  '9':
	  case '10':
   	    item_count      += document.getElementById('qty_'+i).value ? parseFloat(document.getElementById('qty_'+i).value) : 0;
  	    shipment_weight += document.getElementById('qty_'+i).value * document.getElementById('weight_'+i).value;
	    break;
	  case  '6':
	  case  '7':
	  case '12':
	  case '13':
	  case '18':
	  case '19':
	  case '20':
	  case '21':
   	    item_count      += document.getElementById('pstd_'+i).value ? parseFloat(document.getElementById('pstd_'+i).value) : 0;
  	    shipment_weight += document.getElementById('pstd_'+i).value * document.getElementById('weight_'+i).value;
	    break;
	  default:
	}
    lineTotal = parseFloat(cleanCurrency(document.getElementById('total_'+i).value));
  	if (document.getElementById('tax_'+i).value != '0') {
      tax_index = document.getElementById('tax_'+i).selectedIndex;
	  if (tax_index == -1) { // if the rate array index is not defined
		tax_index = 0;
		document.getElementById('tax_'+i).value = tax_index;
	  }
	  if (tax_before_discount == '0') { // tax after discount
        taxable_subtotal += lineTotal * (1-(discountPercent/100)) * (tax_rates[tax_index].rate / 100);
	  } else { 
        taxable_subtotal += lineTotal * (tax_rates[tax_index].rate / 100);
	  }
	  freight_def_tax = tax_index;
	}
	subtotal += lineTotal;
  }

  // recalculate discount
  discount = subtotal * (discountPercent/100);
  var strDiscount = new String(discount);
  document.getElementById('discount').value = formatCurrency(strDiscount);
  // freight
  var strFreight = cleanCurrency(document.getElementById('freight').value);
  var freight = parseFloat(strFreight);
  if (isNaN(freight)) freight = 0;
  strFreight = new String(freight);
  document.getElementById('freight').value = formatCurrency(strFreight);
  if (tax_freight != 0 && freight_def_tax != 0) for (keyVar in tax_rates) {
    if (tax_rates[keyVar].id == tax_freight) taxable_subtotal += parseFloat(freight) * tax_rates[keyVar].rate / 100;
  }

  var nst = new String(taxable_subtotal);
  document.getElementById('sales_tax').value = formatCurrency(nst);
  document.getElementById('item_count').value = item_count;
  document.getElementById('weight').value = shipment_weight;
  var st = new String(subtotal);
  document.getElementById('subtotal').value = formatCurrency(st);
  var new_total = subtotal - discount + freight + taxable_subtotal;
  var tot = new String(new_total);
  document.getElementById('total').value = formatCurrency(tot);
  if (journalID == '12' && applyCreditLimit == '1') {
	if (tot > custCreditLimit && document.getElementById('override_user').value == '') showOverride();
  } else {
    if (document.getElementById('tb_icon_save'))          document.getElementById('tb_icon_save').style.visibility          = "";
    if (document.getElementById('tb_icon_print'))         document.getElementById('tb_icon_print').style.visibility         = "";
    if (document.getElementById('tb_icon_post_previous')) document.getElementById('tb_icon_post_previous').style.visibility = "";
    if (document.getElementById('tb_icon_post_next'))     document.getElementById('tb_icon_post_next').style.visibility     = "";
  }
}

function calculateDiscountPercent() {
  var percent  = parseFloat(cleanCurrency(document.getElementById('disc_percent').value));
  var subTotal = parseFloat(cleanCurrency(document.getElementById('subtotal').value));
  var discount = new String((percent / 100) * subTotal);
  document.getElementById('discount').value = formatCurrency(discount);
  updateTotalPrices();
}

function calculateDiscount() {
  // determine the discount percent
  var discount = parseFloat(cleanCurrency(document.getElementById('discount').value));
  if (isNaN(discount)) discount = 0;
  var subTotal = parseFloat(cleanCurrency(document.getElementById('subtotal').value));
  if (subTotal != 0) {
    var percent = 100000 * (1 - ((subTotal - discount) / subTotal));
    document.getElementById('disc_percent').value = Math.round(percent) / 1000;
  } else {
  	document.getElementById('disc_percent').value = '0.00';
  }
  updateTotalPrices();
}

function showOverride() {
	var total = parseFloat(cleanCurrency($('#total').val()));
	var limit = parseFloat(custCreditLimit); // both strings
	if (total > limit) {
		if (document.getElementById('tb_icon_save'))          document.getElementById('tb_icon_save').style.visibility          = "hidden";
		if (document.getElementById('tb_icon_print'))         document.getElementById('tb_icon_print').style.visibility         = "hidden";
		if (document.getElementById('tb_icon_post_previous')) document.getElementById('tb_icon_post_previous').style.visibility = "hidden";
		if (document.getElementById('tb_icon_post_next'))     document.getElementById('tb_icon_post_next').style.visibility     = "hidden";
		$('#override_order').dialog('open');
	}
}

function checkOverride () {
  var user = document.getElementById('override_user').value;
  var pass = document.getElementById('override_pass').value;
  $.ajax({
	type: "GET",
	url: 'index.php?module=phreedom&page=ajax&op=validate&u='+user+'&p='+pass+'&level=4',
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
	  alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
	},
	success: clearOverride
  });
}

function clearOverride(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  var result = $(xml).find("result").text();
  if (result == 'validated') {
	$('#override_order').dialog('close');
    if (document.getElementById('tb_icon_save'))          document.getElementById('tb_icon_save').style.visibility          = "";
    if (document.getElementById('tb_icon_print'))         document.getElementById('tb_icon_print').style.visibility         = "";
    if (document.getElementById('tb_icon_post_previous')) document.getElementById('tb_icon_post_previous').style.visibility = "";
    if (document.getElementById('tb_icon_post_next'))     document.getElementById('tb_icon_post_next').style.visibility     = "";
  } else {
	alert(adminNotValidated);
  }
}

function checkShipAll() {
  var numRows = 0;
  var item_count;
  if (single_line_list == '1') {
	numRows = document.getElementById('item_table').rows.length;
  } else {
	numRows = document.getElementById('item_table').rows.length/2;
  }
  for (var i=1; i<numRows; i++) {
   	item_count = parseFloat(document.getElementById('qty_'+i).value);
  	if (item_count != 0 && !isNaN(item_count)) {
	  document.getElementById('pstd_'+i).value = item_count;
	}
	updateRowTotal(i, false);
  }
}

function updateDesc(rowID) {
 // this function not used - it sets the chart of accounts description if required by the form
}

function buildFreightDropdown() {
  // fetch the selection
  if (!freightCarriers) return;
  var selectedCarrier = document.getElementById('ship_carrier').value;
  for (var i=0; i<freightCarriers.length; i++) {
	if (freightCarriers[i] == selectedCarrier) break;
  }
  var selectedMethod = document.getElementById('ship_service').value;
  for (var j=0; j<freightLevels.length; j++) {
	if (freightLevels[j] == selectedMethod) break;
  }
  // erase the drop-down
  while (document.getElementById('ship_service').options.length) document.getElementById('ship_service').remove(0);
  // build the new one, first check to see if None was selected
  if (i == freightCarriers.length) return; // None was selected, leave drop-down empty
  var m = 0; // allows skip if method is not available
  for (var k=0; k<freightLevels.length; k++) {
	if (freightDetails[i][k] != '') {
	  var newOpt = document.createElement("option");
	  newOpt.text = freightDetails[i][k];
	  document.getElementById('ship_service').options.add(newOpt);
	  document.getElementById('ship_service').options[m].value = freightLevels[k];
	  m++;
	}
  }
  // set the default choice 
  document.getElementById('ship_service').value = selectedMethod;
}

function recalculateCurrencies() {
  var workingTotal = 0;
  var workingUnitValue = 0;
  var itemTotal = 0;
  var numRows = 0;
  var newTotal = 0;
  var newValue = 0;
  var currentCurrency = document.getElementById('currencies_code').value;
  var currentValue = parseFloat(document.getElementById('currencies_value').value);
  var desiredCurrency = document.getElementById('display_currency').value;
  for (var i=0; i<js_currency_codes.length; i++) {
	if (js_currency_codes[i] == desiredCurrency) newValue = js_currency_values[i];
  }
  // update the line item table
  if (single_line_list == '1') {
	numRows = document.getElementById('item_table').rows.length;
  } else {
	numRows = document.getElementById('item_table').rows.length/2;
  }
  for (var i=1; i<(numRows+1); i++) {
	itemTotal = parseFloat(cleanCurrency(document.getElementById('total_'+i).value, currentCurrency));
	if (isNaN(itemTotal)) continue;
	workingTotal = itemTotal / currentValue;
    newTotal = workingTotal * newValue;
	switch (journalID) {
	  case '3':
	  case '4':
	  case '9':
	  case '10':
		workingUnitValue = newTotal / document.getElementById('qty_'+i).value;
		break;
	  case '6':
	  case '7':
	  case '12':
	  case '13':
	  case '18':
	  case '19':
	  case '20':
	  case '21':
		workingUnitValue = newTotal / document.getElementById('pstd_'+i).value;
		break;
	  default:
	}
	if (isNaN(workingUnitValue)) continue;
	document.getElementById('total_'+i).value = formatCurrency(new String(newTotal), desiredCurrency);
	document.getElementById('price_'+i).value = formatPrecise(new String(workingUnitValue), desiredCurrency);
  }
  // convert shipping
  var newFreight = parseFloat(document.getElementById('freight').value);
  newFreight = (newFreight / currentValue) * newValue;
  document.getElementById('freight').value = formatCurrency(new String(newFreight), desiredCurrency);

  updateTotalPrices();
  // prepare the page settings for post
  document.getElementById('currencies_code').value = desiredCurrency;
  document.getElementById('currencies_value').value = new String(newValue);
}

// AJAX auto load SKU pair
function loadSkuDetails(iID, rowCnt) {
	if (!rowCnt) return;
	var qty = 0;
	var sku = '';
	// if a sales order or purchase order exists, keep existing information.
	so_exists = document.getElementById('so_po_item_ref_id_'+rowCnt).value;
	if (so_exists != '') return;
	// check to see if there is a sku present
	if (!iID) {
		sku = document.getElementById('sku_'+rowCnt).value; // read the search field as the real value	  
	}
	if (sku == text_search) return;
	// add new row
	if (single_line_list == '1') {
		tempRowCnt = document.getElementById('item_table').rows.length;
		if ((iID && rowCnt == tempRowCnt )||(document.getElementById('sku_'+tempRowCnt).value != text_search  && document.getElementById('sku_'+tempRowCnt).value != '')){
			var value = addInvRow();
			document.getElementById('sku_'+value).focus();
		}
	} else {
		tempRowCnt = parseInt((document.getElementById('item_table').rows.length/2));
		if ((iID && rowCnt == tempRowCnt )||(document.getElementById('sku_'+tempRowCnt).value != text_search  && document.getElementById('sku_'+tempRowCnt).value != '')){
			var value = addInvRow();
			document.getElementById('sku_'+value).focus();
		}
	}
	var cID = document.getElementById('bill_acct_id').value;
	var bID = document.getElementById('store_id').value;
	switch (journalID) {
		case  '3':
		case  '4':
		case  '9':
		case '10': qty = document.getElementById('qty_'+rowCnt).value; break;
		case  '6':
		case  '7':
		case '12':
		case '13':
		case '18':
		case '19':
		case '20':
		case '21': qty = document.getElementById('pstd_'+rowCnt).value; break;
		default:
	}
	$.ajax({
		type: "GET",
		url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&bID='+bID+'&cID='+cID+'&qty='+qty+'&iID='+iID+'&sku='+sku+'&rID='+rowCnt+'&jID='+journalID,
		dataType: ($.browser.msie) ? "text" : "xml",
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert ("Ajax ErrorThrown: " + errorThrown + "\nTextStatus: " + textStatus + "\nError: " + XMLHttpRequest.responseText);
    	},
    	success: fillInventory
	});
}

function fillInventory(sXml) {
  var qty_pstd = 0;
  var qty = 0;
  var text = '';
  var exchange_rate = document.getElementById('currencies_value').value;
  var xml = parseXml(sXml);
  if (!xml) return;
  var rowCnt = $(xml).find("rID").text();
  var sku    = $(xml).find("sku").first().text(); // only the first find, avoids bom add-ons
  if (!sku) return;
  document.getElementById('sku_'     +rowCnt).value       = sku;
  document.getElementById('sku_'     +rowCnt).style.color = '';
  document.getElementById('sku_'     +rowCnt).style.backgroundColor = '';
  document.getElementById('sku_'     +rowCnt).removeAttribute("title");
  var imgSerial = document.getElementById('imgSerial_'+rowCnt);
  if (imgSerial != null && ($(xml).find("inventory_type").text() == 'sr' || $(xml).find("inventory_type").text() == 'sa')){
    document.getElementById('imgSerial_'+rowCnt).style.display = '';
  }
  document.getElementById('sku_prop_'+rowCnt).style.display = '';
  document.getElementById('weight_'  +rowCnt).value       = $(xml).find("item_weight").text();
  document.getElementById('stock_'   +rowCnt).value       = $(xml).find("branch_qty_in_stock").text(); // stock at this branch
//document.getElementById('stock_'   +rowCnt).value       = $(xml).find("quantity_on_hand").text(); // to insert total stock available
  document.getElementById('lead_'    +rowCnt).value       = $(xml).find("lead_time").text();
  document.getElementById('inactive_'+rowCnt).value       = $(xml).find("inactive").text();
  switch (journalID) {
	case  '3':
	case  '4':
	  document.getElementById('purch_package_quantity_'+rowCnt).value= $(xml).find("purch_package_quantity").text();
	  qty_pstd = 'qty_';
	  document.getElementById('qty_'   +rowCnt).value     = formatPrecise($(xml).find("qty").first().text());
	  document.getElementById('acct_'  +rowCnt).value     = $(xml).find("account_inventory_wage").text();
	  document.getElementById('price_' +rowCnt).value     = formatPrecise($(xml).find("sales_price").text() * exchange_rate);
	  document.getElementById('full_'  +rowCnt).value     = formatCurrency($(xml).find("item_cost").text() * exchange_rate);
	  if(default_sales_tax == -1) document.getElementById('tax_'+rowCnt).value = $(xml).find("purch_taxable").text();
	  if ($(xml).find("description_purchase").text()) {
	    document.getElementById('desc_'  +rowCnt).value   = $(xml).find("description_purchase").text();
	  } else {
	    document.getElementById('desc_'  +rowCnt).value   = $(xml).find("description_short").text();
	  }
	  if(journalID == 4){
		  if ($(xml).find("quantity_on_order").text() >= 0) {
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'Yellow';
			  document.getElementById('sku_'+rowCnt).title = ItemIsAlreadyOnOrder;
		  }else if($(xml).find("branch_qty_in_stock").text() <= 0){
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'Green';
			  document.getElementById('sku_'+rowCnt).title = ItemIsOnHand;
		  }else if($(xml).find("inactive").text() == 1) {
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'pink';
			  document.getElementById('sku_'+rowCnt).title = ItemIsInactive;
		  }
	  }
	  break;
	case  '6':
	case  '7':
    case '21':
	  qty_pstd = 'pstd_';
	  document.getElementById('pstd_'  +rowCnt).value     = formatPrecise($(xml).find("qty").first().text());
	  document.getElementById('acct_'  +rowCnt).value     = $(xml).find("account_inventory_wage").text();
	  document.getElementById('price_' +rowCnt).value     = formatPrecise($(xml).find("sales_price").text() * exchange_rate);
	  document.getElementById('full_'    +rowCnt).value   = formatCurrency($(xml).find("item_cost").text() * exchange_rate);
	  if(default_sales_tax == -1) document.getElementById('tax_'   +rowCnt).value     = $(xml).find("purch_taxable").text();
	  if ($(xml).find("description_purchase").text()) {
	    document.getElementById('desc_'  +rowCnt).value   = $(xml).find("description_purchase").text();
	  } else {
	    document.getElementById('desc_'  +rowCnt).value   = $(xml).find("description_short").text();
	  }
	  if(journalID == 6){
		  if($(xml).find("quantity_on_sales_order").text() >= 0){
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'Aquamarine';
			  document.getElementById('sku_'+rowCnt).title = SalesOrderForItem;
		  }else if($(xml).find("inactive").text() == 1) {
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'pink';
			  document.getElementById('sku_'+rowCnt).title = ItemIsInactive;
		  }
	  }
	  break;
	case  '9':
	case '10':
	  qty_pstd = 'qty_';
	  document.getElementById('qty_'  +rowCnt).value = formatPrecise($(xml).find("qty").first().text());
	  document.getElementById('acct_' +rowCnt).value = $(xml).find("account_sales_income").text();
	  document.getElementById('price_'+rowCnt).value = formatPrecise($(xml).find("sales_price").text() * exchange_rate);
	  document.getElementById('full_' +rowCnt).value = formatCurrency($(xml).find("full_price").text() * exchange_rate);
	  if (default_sales_tax == -1) document.getElementById('tax_'+rowCnt).value = $(xml).find("item_taxable").text();
	  if ($(xml).find("description_sales").text()) {
	    document.getElementById('desc_'  +rowCnt).value   = $(xml).find("description_sales").text();
	  } else {
	    document.getElementById('desc_'  +rowCnt).value   = $(xml).find("description_short").text();
	  }
	  if (journalID == 10) {
		  if($(xml).find("inactive").text() == 1 && $(xml).find("branch_qty_in_stock").text() == 0) {
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'pink';
			  document.getElementById('sku_'+rowCnt).title = ItemIsOutOfStockAndInactive;
		  }else if($(xml).find("branch_qty_in_stock").text() == 0 && $(xml).find("quantity_on_order").text() >= 0) {
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'Yellow';
			  document.getElementById('sku_'+rowCnt).title = ItemIsOnOrder;
		  }else if($(xml).find("branch_qty_in_stock").text() == 0) {
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'FF6633';
			  document.getElementById('sku_'+rowCnt).title = ItemMustBeOrderd;
		  }
	  } else {
		  if($(xml).find("inactive").text() == 1 && $(xml).find("branch_qty_in_stock").text() == 0) {
			  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'pink';
			  document.getElementById('sku_'+rowCnt).title = ItemIsOutOfStockAndInactive;
		  }
	  }
	  break;
	case '12':
	case '13':
	case '19':
	  qty_pstd = 'pstd_';
	  document.getElementById('pstd_' +rowCnt).value = formatPrecise($(xml).find("qty").first().text());
	  document.getElementById('acct_' +rowCnt).value = $(xml).find("account_sales_income").text();
	  document.getElementById('price_'+rowCnt).value = formatPrecise($(xml).find("sales_price").text() * exchange_rate);
	  document.getElementById('full_' +rowCnt).value = formatCurrency($(xml).find("full_price").text() * exchange_rate);
	  if (default_sales_tax == -1) document.getElementById('tax_'+rowCnt).value = $(xml).find("item_taxable").text();
	  if ($(xml).find("description_sales").text()) {
	    document.getElementById('desc_'  +rowCnt).value = $(xml).find("description_sales").text();
	  } else {
	    document.getElementById('desc_'  +rowCnt).value = $(xml).find("description_short").text();
	  }
	  if(journalID == 12 && $(xml).find("inactive").text() == 1 && $(xml).find("branch_qty_in_stock").text() == 0) {
		  document.getElementById('sku_'+rowCnt).style.backgroundColor = 'pink';
		  document.getElementById('sku_'+rowCnt).title = ItemIsOutOfStockAndInactive;
	  }
	  break;
	default:
  }
  updateRowTotal(rowCnt, false);
  $(xml).find("stock_note").each(function() {
	text += $(this).find("text_line").text() + "\n";
  });
  if (text) alert(text);
  document.getElementById('qty_'  +rowCnt).addEventListener("change", function(){ updateRowTotal(rowCnt, true);});
  document.getElementById('pstd_' +rowCnt).addEventListener("change", function(){ updateRowTotal(rowCnt, true);});
}

function InventoryProp(elementID) {
  var sku = document.getElementById('sku_'+elementID).value;
  if (sku != text_search && sku != '') {
	  window.open("index.php?module=inventory&page=main&action=properties&sku="+sku+'&rowID='+elementID,"inventory","width=800px,height=600px,resizable=1,scrollbars=1,top=50,left=50");
  }
}

function ContactProp() {
  var type = '';
  var bill_acct_id = document.getElementById('bill_acct_id').value;
  switch (journalID) {
	case  '3':
	case  '4':
	case  '6':
	case  '7':
	case '20':
	case '21':
		type = 'v';
		break;
	case  '9':
	case '10':
	case '12':
	case '13':
	case '18':
	case '19':
		type = 'c';
		break;
	default:
  }
  if (bill_acct_id == 0 || bill_acct_id == '') {
	alert(no_contact_id);
  } else {
    window.open("index.php?module=contacts&page=main&type="+type+"&action=properties&cID="+bill_acct_id,"contacts","width=800px,height=700px,resizable=1,scrollbars=1,top=50,left=50");
  }
}

function PreProcessLowStock() {
  var rowCnt;
  if (!lowStockExecute) {
	alert(lowStockExecuted);
	return;
  }
  var acct   = document.getElementById('bill_acct_id').value;
  if (!acct){
    alert(lowStockNoVendor);
    return;
  }
  var store  = document.getElementById('store_id').value;
  if (single_line_list == '1') {
	rowCnt = document.getElementById('item_table').rows.length;
  } else {
	rowCnt = document.getElementById('item_table').rows.length/2;
  }
  if (rowCnt<=1)    rowCnt = 1;
  if (isNaN(store)) store  = 0;
 $.ajax({
	type: "GET",
	url: 'index.php?module=phreebooks&page=ajax&op=low_stock&cID='+acct+'&sID='+store+'&rID='+rowCnt,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
	  alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
	},
	success: PostProcessLowStock
  });
}

function PostProcessLowStock(sXml) {
	var xml = parseXml(sXml);
	var exchange_rate = document.getElementById('currencies_value').value;
	if (!xml) return;
	var i = 0;
	$(xml).find("LowStock").each(function() {
		addInvRow();
		var rowCnt = $(this).find("rID").text();
		document.getElementById('sku_'     +rowCnt).value       = $(this).find("sku").text();
		document.getElementById('sku_'     +rowCnt).style.color = '';
		document.getElementById('full_'    +rowCnt).value       = formatCurrency($(xml).find("full_price").text() * exchange_rate);
		document.getElementById('weight_'  +rowCnt).value       = $(this).find("item_weight").text();
		document.getElementById('stock_'   +rowCnt).value       = $(this).find("quantity").text(); 
		document.getElementById('lead_'    +rowCnt).value       = $(this).find("lead_time").text();
		document.getElementById('inactive_'+rowCnt).value       = $(this).find("inactive").text();
		document.getElementById('qty_'     +rowCnt).value     	= $(this).find("reorder_quantity").text();
		document.getElementById('acct_'    +rowCnt).value     	= $(this).find("account_inventory_wage").text();
		document.getElementById('price_'   +rowCnt).value     	= formatPrecise($(this).find("item_cost").text() * exchange_rate);
		document.getElementById('tax_'     +rowCnt).value		= $(this).find("purch_taxable").text();
		document.getElementById('sku_prop_'+rowCnt).style.display = '';
		document.getElementById('purch_package_quantity_'+rowCnt).value	 = $(this).find("purch_package_quantity").text();
		if ($(this).find("description_purchase").text()) {
			document.getElementById('desc_'+rowCnt).value   	= $(this).find("description_purchase").text();
		} else {
			document.getElementById('desc_'+rowCnt).value   	= $(this).find("description_short").text();
		}
		i++;
		updateRowTotal(rowCnt, false);
	});
	if (i==0) {
		alert(lowStockNoProducts);
	} else {
		alert(lowStockProcessed+i);
	}
	lowStockExecute = false;
}
