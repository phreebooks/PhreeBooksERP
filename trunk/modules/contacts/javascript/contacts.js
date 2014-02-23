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
//  Path: /modules/contacts/javascript/contacts.js
//

function contactChart(func, d0) {
	chartProps.modID = "contacts";
	chartProps.divID = "contact_chart";
	chartProps.func  = func;
	chartProps.d0    = d0;
	document.getElementById(chartProps.divID).innerHTML = '&nbsp;';
	phreedomChart();
}

function getAddress(id, type) {
  $.ajax({
	type: "GET",
	url: 'index.php?module=contacts&page=ajax&op=contacts&action=get_address&type='+type+'&aID='+id,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
	},
	success: fillAddress
  });
}

function fillAddress(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  var message = $(xml).find("message").text();
  if (message) { $.messager.alert("Info",message,"info"); }
  else {
	var type = $(xml).find("type").first().text();
	$(xml).find("Address").each(function() {
  	  $(this).children().each(function() {
  		var tagName = this.tagName;
  		if (document.getElementById('address_'+type+'_'+tagName)) insertValue('address_'+type+'_'+tagName, $(this).text());
  	  });
    });
	if (type == 'im') {
		insertValue('i_id',            $(xml).find("contact_id").text());
		insertValue('i_short_name',    $(xml).find("short_name").text());
		insertValue('i_contact_first', $(xml).find("contact_first").text());
		insertValue('i_contact_middle',$(xml).find("contact_middle").text());
		insertValue('i_contact_last',  $(xml).find("contact_last").text());
		insertValue('i_account_number',$(xml).find("account_number").text());
		insertValue('i_gov_id_number', $(xml).find("gov_id_number").text());
	}
  }
}

function copyContactAddress(type) {
  insertValue('address_im_primary_name',   document.getElementById('address_'+type+'m_primary_name').value);
  insertValue('address_im_contact',        document.getElementById('address_'+type+'m_contact').value);
  insertValue('address_im_telephone1',     document.getElementById('address_'+type+'m_telephone1').value);
  insertValue('address_im_telephone2',     document.getElementById('address_'+type+'m_telephone2').value);
  insertValue('address_im_telephone3',     document.getElementById('address_'+type+'m_telephone3').value);
  insertValue('address_im_telephone4',     document.getElementById('address_'+type+'m_telephone4').value);
  insertValue('address_im_email',          document.getElementById('address_'+type+'m_email').value);
  insertValue('address_im_website',        document.getElementById('address_'+type+'m_website').value);
  insertValue('address_im_address1',       document.getElementById('address_'+type+'m_address1').value);
  insertValue('address_im_address2',       document.getElementById('address_'+type+'m_address2').value);
  insertValue('address_im_city_town',      document.getElementById('address_'+type+'m_city_town').value);
  insertValue('address_im_state_province', document.getElementById('address_'+type+'m_state_province').value);
  insertValue('address_im_postal_code',    document.getElementById('address_'+type+'m_postal_code').value);
  insertValue('address_im_country_code',   document.getElementById('address_'+type+'m_country_code').value);
}

function clearAddress(type) {
	$("#"+type+"_address_form input").val(function( i, val ) { return ''; });
	document.getElementById('address_'+type+'_address_id').value   = 0;
	document.getElementById('address_'+type+'_country_code').value = default_country;
	if (type == 'im') {
		if(document.getElementById('i_id'))             document.getElementById('i_id').value             = '';
		if(document.getElementById('i_short_name'))     document.getElementById('i_short_name').value     = '';
		if(document.getElementById('i_contact_middle')) document.getElementById('i_contact_middle').value = '';
		if(document.getElementById('i_contact_first'))  document.getElementById('i_contact_first').value  = '';
		if(document.getElementById('i_contact_last'))   document.getElementById('i_contact_last').value   = '';
		if(document.getElementById('i_account_number')) document.getElementById('i_account_number').value = '';
		if(document.getElementById('i_gov_id_number'))  document.getElementById('i_gov_id_number').value  = '';
	}
}

function deleteAddress(id) {
  $.ajax({
	type: "GET",
	url: 'index.php?module=contacts&page=ajax&op=contacts&action=rm_address&aID='+id,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
	},
	success: deleteAddressResp
  });
}

function deleteAddressResp(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  var rowID = $(xml).find("address_id").text();
  if (rowID) $('#tr_add_'+rowID).remove();
  var message = $(xml).find("message").text();
  if (message) { $.messager.alert("Info",message,"info"); }
  // TBD need to remove the row here
}

function TermsList() {
  var terms = document.getElementById('terms').value;
  window.open("index.php?module=contacts&page=popup_terms&type="+account_type+"&val="+terms,"terms","width=500px,height=300px,resizable=1,scrollbars=1,top=150,left=200");
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

function deleteCRM(id) {
  $.ajax({
	type: "GET",
	url: 'index.php?module=contacts&page=ajax&op=contacts&action=rm_crm&nID='+id,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
	},
	success: deleteCRMResp
  });
}

function deleteCRMResp(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  var rowID = $(xml).find("crm_id").text();
  if (rowID) {
	  $('#tr_crm_a_'+rowID).remove();
	  $('#tr_crm_b_'+rowID).remove();
  }
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

