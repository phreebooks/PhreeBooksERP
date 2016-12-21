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
//  Path: /modules/contacts/javascript/contacts.js
// @todo check

function contactChart(func, cID) {
	chartProps.modID = "contacts";
	chartProps.divID = "contact_chart";
	chartProps.func  = func;
	chartProps.d0    = cID;
	document.getElementById(chartProps.divID).innerHTML = '&nbsp;';
	$.ajax({
		type: "GET",
		url: 'index.php?module=phreedom&page=ajax&action=ContactGetChartData&fID='+func+'&cID='+cIDd0,
		dataType: ($.browser.msie) ? "text" : "xml",
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
		},
		success: phreedomChartResp
	});
}

function TermsList() {
  var terms = document.getElementById('terms').value;
  window.open("index.php?module=contacts&action=LoadTermsPopUp&type="+account_type+"&val="+terms,"terms","width=500px,height=300px,resizable=1,scrollbars=1,top=150,left=200");
}

function getPayment(id) {
  $.ajax({
	type: "GET",
	url: 'index.php?module=contacts&page=ajax&op=contacts&action=get_payment&pID='+id,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
	},
	success: fillPayment
  });
}

function fillPayment(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  var message = $(xml).find("message").text();
  if (message) { $.messager.alert("Info",message,"info"); }
  else {
	document.getElementById('payment_id').value        = $(xml).find("payment_id").text();
	document.getElementById('payment_cc_name').value   = $(xml).find("field_0").text();
	document.getElementById('payment_cc_number').value = $(xml).find("field_1").text();
	document.getElementById('payment_exp_month').value = $(xml).find("field_2").text();
	document.getElementById('payment_exp_year').value  = $(xml).find("field_3").text();
	document.getElementById('payment_cc_cvv2').value   = $(xml).find("field_4").text();
  }
}

function clearPayment() {
  document.getElementById('payment_id').value                = 0;
  document.getElementById('payment_cc_name').value           = '';
  document.getElementById('payment_cc_number').value         = '';
  document.getElementById('payment_exp_month').selectedIndex = 0;
  document.getElementById('payment_exp_year').selectedIndex  = 0;
  document.getElementById('payment_cc_cvv2').value           = '';
}

function deletePayment(id) {
  $.ajax({
	type: "GET",
	url: 'index.php?module=contacts&page=ajax&op=contacts&action=rm_payment&pID='+id,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
	},
	success: deletePaymentResp
  });
}

function deletePaymentResp(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  var rowID = $(xml).find("payment_id").text();
  if (rowID) $('#tr_pmt_'+rowID).remove();
  var message = $(xml).find("message").text();
  if (message) { $.messager.alert("Info",message,"info"); }
}

function downloadAttachment(filename) {
  var file_name = attachment_path+filename;
  $.ajax({
	type: "GET",
	url: 'index.php?module=phreedom&page=ajax&op=phreedom&action=download&file='+file_name,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: downloadResponse
  });
}

function downloadResponse(sXML) {
	var xml = parseXml(sXml);
	if (!xml) return; 
}

