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
//  Path: /modules/phreebooks/javascript/banking.js
//
var bill_add = new Array(0);

function ClearForm() {
  var add_id;
  document.getElementById('id').value                     = '';
  document.getElementById('bill_acct_id').value           = '';
  document.getElementById('bill_address_id').value        = '';
  document.getElementById('bill_telephone1').value        = '';
  document.getElementById('search').value                 = text_search;
//  document.getElementById('purchase_invoice_id').value  = ''; // this erases the current receipt/check number
  document.getElementById('post_date').value              = defaultPostDate;
  document.getElementById('purch_order_id').value         = '';
  document.getElementById('gl_acct_id').value             = defaultGlAcct;
  document.getElementById('gl_disc_acct_id').value        = defaultDiscAcct;
  document.getElementById('total').value                  = '';
//  document.getElementById('acct_balance').value         = formatted_zero; // this is calculated in loadNewBalance below
//  document.getElementById('end_balance').value          = formatted_zero;
  document.getElementById('shipper_code').selectedIndex   = 0;
  // some special initialization
  document.getElementById('search').style.color           = inactive_text_color;
  document.getElementById('purchase_invoice_id').readOnly = false;
  document.getElementById('bill_country_code').value      = store_country_code;
  for (var i=0; i<add_array.length; i++) {
	add_id = add_array[i];
	if (add_id != 'country_code') document.getElementById('bill_'+add_id).style.color = inactive_text_color;
	document.getElementById('bill_'+add_id).value = default_array[i];
  }
  while (document.getElementById('bill_to_select').options.length) {
	document.getElementById('bill_to_select').remove(0);
  }
  while (document.getElementById('payment_id').options.length) {
	document.getElementById('payment_id').remove(0);
  }
  while (document.getElementById("item_table").rows.length > 0) {
	document.getElementById("item_table").deleteRow(-1); 
  }
  loadNewBalance(defaultGlAcct);
}

function AccountList() {
  var guess = document.getElementById('search').value;
  if (guess != text_search && guess != '') {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=phreebooks&page=ajax&op=load_searches&jID='+journalID+'&type='+account_type+'&guess='+guess,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
    	  $.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
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
    ajaxBillData(cID, 0, journalID);
  } else { // search result <> 1
	var search_text = document.getElementById('search').value;
    window.open('index.php?module=phreebooks&page=popup_bills_accts&list=1&jID='+journalID+"&type="+account_type+"&search_text="+search_text,"invoices","width=700px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
  }
}

function OpenOrdrList(currObj) {
  window.open('index.php?module=phreebooks&page=popup_bills&list=1&jID='+journalID+"&type="+account_type,"invoices","width=700px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
}

function popupWindowCvv() {
  window.open('index.php?module=phreebooks&page=popup_cvv',"popup_payment_cvv","width=550,height=550,resizable=1,scrollbars=1,top=150,left=200");
}

function ajaxBillData(cID, bID, jID) {
  $.ajax({
    type: "GET",
	url: 'index.php?module=phreebooks&page=ajax&op=load_bill&cID='+cID+'&bID='+bID+'&jID='+jID,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fillBillData
  });
}

function fillBillData(sXml) { // edit response form fill 
  var xml = parseXml(sXml);
  if (!xml) return;
  ClearForm();
  billFillAddress(xml);
  if ($(xml).find("BillData").length) fillBill(xml);
  loadNewPayment();
}

function billFillAddress(xml) {
  var newOpt;
  while (document.getElementById('bill_to_select').options.length) document.getElementById('bill_to_select').remove(0);
  $(xml).find("BillContact").each(function() {
    var id = $(this).find("id").text();
	if (!id) return;
    mainType = $(this).find("type").first().text() + 'm';
    insertValue('bill_acct_id', id);
    insertValue('search',       $(this).find("short_name").text());
	bill_add = this;
	//now fill the addresses
    var iIndex = 0;
    $(this).find("Address").each(function() {
      newOpt = document.createElement("option");
	  newOpt.text = $(this).find("primary_name").text() + ', ' + $(this).find("city_town").text() + ', ' + $(this).find("postal_code").text();
	  document.getElementById('bill_to_select').options.add(newOpt);
	  document.getElementById('bill_to_select').options[iIndex].value = $(this).find("address_id").text();
      if ($(this).find("type").text() == mainType) { // also fill the fields
	    insertValue('bill_address_id', $(this).find("address_id").text());
	    $(this).children().each (function() {
		  var tagName = this.tagName;
		  if (document.getElementById('bill_'+tagName)) {
		    document.getElementById('bill_'+tagName).value = $(this).text();
		    document.getElementById('bill_'+tagName).style.color = '';
		  }
	    });
	  }
	  iIndex++;
    });
    // add a option for creating a new address
    newOpt = document.createElement("option");
    newOpt.text = text_enter_new;
    document.getElementById('bill_to_select').options.add(newOpt);	
    document.getElementById('bill_to_select').options[iIndex].value = '0';
    document.getElementById('bill_to_select').style.visibility      = 'visible';
    document.getElementById('bill_to_select').disabled              = false;
  });
}

function fillBill(xml) {
  $(xml).find("BillData").each(function() {
	$(this).children().each (function() {
	  var tagName = this.tagName;
	  if (document.getElementById(tagName)) {
	    document.getElementById(tagName).value = $(this).first().text();
	    document.getElementById(tagName).style.color = '';
	  }
	});
    // fix some special cases, checkboxes, and active fields
	if ($(this).find("shipper_code").text() && journalID == '18') { // holds payment method for receipts
	  var shipper_code = $(this).find("shipper_code").text();
	  document.getElementById('shipper_code').value = shipper_code;
	  activateFields();
	  if ($(this).find("payment_fields").text()) {
		var temp = $(this).find("payment_fields").text();
		var fieldArray = temp.split(':');
	    for (i=0, j=1; i<fieldArray.length; i++, j++) {
		  if (document.getElementById(shipper_code+'_field_'+i))
		    document.getElementById(shipper_code+'_field_'+i).value = fieldArray[j];
	    }
	  }
    }
    if ((document.getElementById('id').value && securityLevel < 3) ||
	   (!document.getElementById('id').value && securityLevel < 2)) { // turn off some icons
	  removeElement('tb_main_0', 'tb_icon_print');
	  removeElement('tb_main_0', 'tb_icon_save');
    }
  });
  // fill invoice rows
  var jIndex = 1;
  $(xml).find("Item").each(function() {
	var rowCnt = addInvRow();
	insertValue('id_'    + jIndex, $(this).find("id").text());
	insertValue('inv_'   + jIndex, $(this).find("purchase_invoice_id").text());
	insertValue('prcnt_' + jIndex, $(this).find("percent").text());
	insertValue('early_' + jIndex, $(this).find("early_date").text());
	insertValue('due_'   + jIndex, $(this).find("net_date").text());
	insertValue('amt_'   + jIndex, $(this).find("total_amount").text());
	insertValue('acct_'  + jIndex, $(this).find("gl_acct_id").text());
	insertValue('desc_'  + jIndex, $(this).find("description").text());
	insertValue('dscnt_' + jIndex, $(this).find("discount").text());
	insertValue('total_' + jIndex, $(this).find("amount_paid").text());
	if ($(this).find("waiting").text() == '1') { // waiting for invoice (no invoice number)
		document.getElementById('desc_' + jIndex).readOnly  = true;
		document.getElementById('dscnt_' + jIndex).readOnly = true;
		document.getElementById('total_' + jIndex).readOnly = true;
		document.getElementById('item_table').rows[rowCnt-1].className = 'ui-state-error';
		document.getElementById('item_table').rows[rowCnt-1].cells[6].innerHTML = '&nbsp;'; // remove checkbox
	} else if ($(this).find("amount_paid").text()) {
		document.getElementById('pay_' + jIndex).checked    = true;
	}
	jIndex++;
  });
  updateTotalPrices();
}

function fillAddress(type) {
  var index   = document.getElementById(type+'_to_select').value;
  var address = bill_add;
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

// ******* AJAX balance request function pair *********/
function loadNewBalance() { // request funtion
  var gl_acct   = document.getElementById('gl_acct_id').value;
  var post_date = document.getElementById('post_date').value;
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreebooks&page=ajax&op=acct_balance&gl_acct_id='+gl_acct+'&post_date='+post_date,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: showNewBalance
  });
}

function showNewBalance(sXml) { // call back function
  var xml = parseXml(sXml);
  if (!xml) return;
  var value = $(xml).find("value").text();
  if (isNaN(value) || !value) value = '0';
  var start_balance = parseFloat(value);
  var total_checks  = cleanCurrency(document.getElementById('total').value);
  balance_remain    = new String(start_balance - total_checks);
  sb = new String(start_balance);
  document.getElementById('acct_balance').value = formatCurrency(sb);
  document.getElementById('end_balance').value  = formatCurrency(balance_remain);
}
// ******* END - AJAX balance request function pair *********/

function addInvRow() {
   var cell = Array(7);
   var newRow = document.getElementById("item_table").insertRow(-1);
   var newCell;
   rowCnt = newRow.rowIndex;

   // NOTE: any change here also need to be made below for reload if action fails
   cell[0] = '<td align="center"><input type="text" name="inv_'+rowCnt+'" id="inv_'+rowCnt+'" readonly="readonly" size="15">';
// Hidden fields
   cell[0] += '<input type="hidden" name="id_'+rowCnt+'"    id="id_'+rowCnt+'"    value="">';
   cell[0] += '<input type="hidden" name="prcnt_'+rowCnt+'" id="prcnt_'+rowCnt+'" value="">';
   cell[0] += '<input type="hidden" name="early_'+rowCnt+'" id="early_'+rowCnt+'" value="">';
   cell[0] += '<input type="hidden" name="acct_'+rowCnt+'"  id="acct_'+rowCnt+'"  value="">';
// End hidden fields
   cell[0] += '</td>';
   cell[1] = '<td align="center"><input type="text" name="due_'+rowCnt+'" id="due_'+rowCnt+'" readonly="readonly" size="15"></td>';
   cell[2] = '<td align="center"><input type="text" name="amt_'+rowCnt+'" id="amt_'+rowCnt+'" readonly="readonly" size="12" style="text-align:right"></td>';
   cell[3] = '<td align="center"><input type="text" name="desc_'+rowCnt+'" id="desc_'+rowCnt+'" size="64" maxlength="64"></td>';
   cell[4] = '<td align="center"><input type="text" name="dscnt_'+rowCnt+'" id="dscnt_'+rowCnt+'" size="15" maxlength="20" onchange="updateRowTotal('+rowCnt+')" style="text-align:right"></td>';
   cell[5] = '<td align="center"><input type="text" name="total_'+rowCnt+'" id="total_'+rowCnt+'" value="'+formatted_zero+'" size="15" maxlength="20" onchange="updateUnitPrice('+rowCnt+')" style="text-align:right"></td>';
   cell[6] = '<td align="center"><input type="checkbox" name="pay_'+rowCnt+'" id="pay_'+rowCnt+'" value="1" onclick="updatePayValues('+rowCnt+')"></td>';

   for (var i=0; i<cell.length; i++) {
		newCell = newRow.insertCell(-1);
		newCell.innerHTML = cell[i];
	}
	return rowCnt;
}

function addBulkRow() {
   var cell = Array(7);
   var newRow = document.getElementById("item_table").insertRow(-1);
   var newCell;
   rowCnt = newRow.rowIndex;

   // NOTE: any change here also need to be made below for reload if action fails
   cell[0] = '<td align="center"><input type="text" name="due_'+rowCnt+'" id="due_'+rowCnt+'" readonly="readonly" size="15"></td>';
// Hidden fields
   cell[0] += '<input type="hidden" name="id_'+rowCnt+'" id="id_'+rowCnt+'" value="">';
   cell[0] += '<input type="hidden" name="prcnt_'+rowCnt+'" id="prcnt_'+rowCnt+'" value="">';
   cell[0] += '<input type="hidden" name="early_'+rowCnt+'" id="early_'+rowCnt+'" value="">';
   cell[0] += '<input type="hidden" name="acct_'+rowCnt+'" id="acct_'+rowCnt+'" value="">';
// End hidden fields
   cell[0] += '</td>';
   cell[1] = '<td align="center"><input type="text" name="disc_'+rowCnt+'" id="disc_'+rowCnt+'" readonly="readonly" size="15"></td>';
   cell[2] = '<td align="center"><input type="text" name="desc_'+rowCnt+'" id="desc_'+rowCnt+'" size="40"></td>';
   cell[3] = '<td align="center"><input type="text" name="inv_'+rowCnt+'" id="inv_'+rowCnt+'" readonly="readonly" size="15">';
   cell[4] = '<td align="center"><input type="text" name="amt_'+rowCnt+'" id="amt_'+rowCnt+'" readonly="readonly" size="12" style="text-align:right"></td>';
   cell[5] = '<td align="center"><input type="text" name="dscnt_'+rowCnt+'" id="dscnt_'+rowCnt+'" size="11" maxlength="10" onchange="updateRowTotal('+rowCnt+')" style="text-align:right"></td>';
   cell[6] = '<td align="center"><input type="text" name="total_'+rowCnt+'" id="total_'+rowCnt+'" value="'+formatted_zero+'" size="11" maxlength="20" onchange="updateUnitPrice('+rowCnt+')" style="text-align:right"></td>';
   cell[7] = '<td align="center"><input type="checkbox" name="pay_'+rowCnt+'" id="pay_'+rowCnt+'" value="1" onclick="updatePayValues('+rowCnt+')"></td>';

   for (var i=0; i<cell.length; i++) {
		newCell = newRow.insertCell(-1);
		newCell.innerHTML = cell[i];
	}
	return rowCnt;
}

function updateRowTotal(rowCnt) {
	var discount_amount = cleanCurrency(document.getElementById('dscnt_'+rowCnt).value);
	document.getElementById('dscnt_'+rowCnt).value = formatCurrency(discount_amount);
	var pay_total = parseFloat(cleanCurrency(document.getElementById('amt_'+rowCnt).value)) - discount_amount;
	var total_l = new String(pay_total);
	document.getElementById('total_'+rowCnt).value = formatCurrency(total_l);
	document.getElementById('pay_'+rowCnt).checked = true;
	updateTotalPrices();
}

function updateUnitPrice(rowCnt) {
	var total_line = cleanCurrency(document.getElementById('total_'+rowCnt).value);
	document.getElementById('total_'+rowCnt).value = formatCurrency(total_line);
//	document.getElementById('dscnt_'+rowCnt).value = '';
	document.getElementById('pay_'+rowCnt).checked = true;
	updateTotalPrices();
}

function updatePayValues(rowCnt) {
	if (document.getElementById('pay_'+rowCnt).checked) {
		var postDate = new Date(document.getElementById('post_date').value);
		var earlyDate = new Date(document.getElementById('early_'+rowCnt).value);
		var amount = cleanCurrency(document.getElementById('amt_'+rowCnt).value);
		var discountPercent = parseFloat(document.getElementById('prcnt_'+rowCnt).value);
		if (isNaN(discountPercent)) discountPercent = 0;
		var discountAmount = new String(discountPercent * amount);
		if (postDate > earlyDate) { // no discount if post date after early date
			discountPercent = 0;
			discountAmount = '0';
		}
		document.getElementById('dscnt_'+rowCnt).value = formatCurrency(discountAmount);
		var new_total = new String(amount - parseFloat(document.getElementById('dscnt_'+rowCnt).value));
		document.getElementById('total_'+rowCnt).value = formatCurrency(new_total);
	} else {
		document.getElementById('dscnt_'+rowCnt).value = '';
		document.getElementById('total_'+rowCnt).value = formatCurrency('0');
	}
	updateTotalPrices();
}

function updateTotalPrices() {
  var temp = '';
  var total = 0;
  for (var i=1; i<=document.getElementById("item_table").rows.length; i++) {
	if (document.getElementById('total_'+i).value) {
	  temp = cleanCurrency(document.getElementById('total_'+i).value);
	  total += parseFloat(temp);
	}
  }
  var tot = new String(total);
  document.getElementById('total').value = formatCurrency(tot);
  if (journalID == 20) {
    var start_balance = cleanCurrency(document.getElementById('acct_balance').value);
    temp = new String(start_balance - tot);
    document.getElementById('end_balance').value = formatCurrency(temp);
  }
}

function activateFields() {
  if (payments_installed) {
    var index = document.getElementById('shipper_code').selectedIndex;
    for (var i=0; i<document.getElementById('shipper_code').options.length; i++) {
  	  document.getElementById('pm_'+i).style.visibility = 'hidden';
    }
    document.getElementById('pm_'+index).style.visibility = '';
  }
}
