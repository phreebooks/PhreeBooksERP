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
//  Path: /includes/common.js
//
/******************************* overwrite General js Functions ****************************************/

function confirm(msg){
	$.messager.confirm('Please Confirm',msg,function(r){
		if (r){
			return true;
		}
	});
	return false;
}
/******************************* General Functions ****************************************/
function addLoadEvent(func) { 
  var oldonload = window.onload; 
  if (typeof window.onload != 'function') { 
    window.onload = func; 
  } else { 
    window.onload = function() { 
	  if (oldonload) { oldonload(); } 
	  func(); 
	};
  } 
} 

function addUnloadEvent(func) { 
  var oldonunload = window.onunload; 
  if (typeof window.onunload != 'function') { 
    window.onunload = func; 
  } else { 
	window.onunload = function() { 
	  if (oldonunload) { oldonunload(); } 
	  func(); 
	};
  } 
} 

// BOS - set up ajax session refresh timer to stay logged in if the browser is active
var sessionClockID = 0; // start a session clock to stay logged in
function refreshSessionClock() {
  if (sessionClockID) {
    window.clearTimeout(sessionClockID);
    $.ajax({
      type: "GET",
	  url: 'index.php?module=phreedom&page=ajax&op=refresh_session'
    });
  }
  sessionClockID = window.setTimeout("refreshSessionClock()", 300000); // set to 5 minutes
}

function clearSessionClock() {
  if (sessionClockID) {
    window.clearTimeout(sessionClockID);
    sessionClockID = 0;
  }
}
// EOS - set up ajax session refresh timer to stay logged in if the browser is active

function submit_wait() {
  document.getElementById("wait_msg").style.display = "block";
  return true; // allow form to submit
}

function clearField(field_name, text_value) {
	document.getElementById(field_name).style.color = '';
	if (document.getElementById(field_name).value == text_value) {
		document.getElementById(field_name).value       = '';
	}
}

function setField(field_name, text_value) {
  if (document.getElementById(field_name).value == '' || document.getElementById(field_name).value == text_value) {
	document.getElementById(field_name).style.color = inactive_text_color;
	document.getElementById(field_name).value       = text_value;
  } else {
	document.getElementById(field_name).style.color = '';
  }
}

function activeField(field, text_value) {
  if (field.value == text_value) {
	  field.style.color = '';
	  field.value       = '';
  }
	}

function inactiveField(field, text_value) {
  if (field.value == '' || field.value == text_value) {
	  field.style.color = inactive_text_color;
	  field.value       = text_value;
  } else {
	  field.style.color = '';
  }
}

function removeElement(parentDiv, childDiv) {
  if (childDiv == parentDiv) {
    return false;
  } else if (document.getElementById(childDiv)) {     
    var child  = document.getElementById(childDiv);
    var parent = document.getElementById(parentDiv);
    parent.removeChild(child);
  } else {
    return false;
  }
}

function insertValue(dVal, value) {
  if (!document.getElementById(dVal)) return;
  if (document.getElementById(dVal) && value) {
	document.getElementById(dVal).value = value;
	document.getElementById(dVal).style.color = '';
  } else {
	document.getElementById(dVal).value = '';
  }
}

function setCheckedValue(radioObj, newValue) {
  if (!radioObj) return;
  var radioLength = radioObj.length;
  if (radioLength == undefined) {
	radioObj.checked = (radioObj.value == newValue.toString());
	return;
  }
  for (var i = 0; i < radioLength; i++) {
	radioObj[i].checked = false;
	if(radioObj[i].value == newValue.toString()) {
	  radioObj[i].checked = true;
	}
  }
}

// Numeric functions
function d2h(d, padding) {
	if (isNaN(d)) d = parseInt(d);
    var hex = Number(d).toString(16);
    padding = typeof (padding) === "undefined" || padding === null ? padding = 2 : padding;
    while (hex.length < padding) hex = "0" + hex;
    return hex;
}

function h2d(h) {
  return parseInt(h, 16);
}

// date functions
function cleanDate(sDate) { // converts date from locale to mysql friendly yyyy-mm-dd
  var tmpArray = new Array();
  var keys  = date_format.split(date_delimiter);
  var parts = sDate.split(date_delimiter);
  for (var i=0; i<keys.length; i++) {
	switch (keys[i]) {
	  case 'Y': tmpArray[0] = parts[i]; break;
	  case 'm': tmpArray[1] = parts[i]; break;
	  case 'd': tmpArray[2] = parts[i]; break;
	}
  }
  return tmpArray.join('-');
}

function formatDate(sDate) { // converts date from mysql friendly yyyy-mm-dd to locale specific
  var tmpArray = new Array();
  var keys  = date_format.split(date_delimiter);
  var parts = sDate.split('-');
  for (var i=0; i<keys.length; i++) {
	switch (keys[i]) {
	  case 'Y': tmpArray[i] = parts[0]; break;
	  case 'm': tmpArray[i] = parts[1]; break;
	  case 'd': tmpArray[i] = parts[2]; break;
	}
  }
  return tmpArray.join(date_delimiter);
}

// Currency translation functions
function cleanCurrency(amount) {
  amount = amount.replace(new RegExp("["+thousands_point+"]", "g"), '');
  amount = amount.replace(new RegExp("["+decimal_point+"]", "g"), '.');
  return amount;
}

function formatCurrency(amount) { // convert to expected currency format
  // amount needs to be a string type with thousands separator ',' and decimal point dot '.' 
  var factor  = Math.pow(10, decimal_places);
  var adj     = Math.pow(10, (decimal_places+2)); // to fix rounding (i.e. .1499999999 rounding to 0.14 s/b 0.15)
  var numExpr = parseFloat(amount);
  if (isNaN(numExpr)) return amount;
  numExpr     = Math.round((numExpr * factor) + (1/adj));
  var minus   = (numExpr < 0) ? '-' : ''; 
  numExpr     = Math.abs(numExpr);
  var decimal = (numExpr % factor).toString();
  while (decimal.length < decimal_places) decimal = '0' + decimal;
  var whole   = Math.floor(numExpr / factor).toString();
  for (var i = 0; i < Math.floor((whole.length-(1+i))/3); i++)
    whole = whole.substring(0,whole.length-(4*i+3)) + thousands_point + whole.substring(whole.length-(4*i+3));
  if (decimal_places > 0) {
    return minus + whole + decimal_point + decimal;
  } else {
	return minus + whole;
  }
}

function formatPrecise(amount) { // convert to expected currency format with the additional precision
  // amount needs to be a string type with thousands separator ',' and decimal point dot '.' 
  var factor = Math.pow(10, decimal_precise);
  var numExpr = parseFloat(amount);
  if (isNaN(numExpr)) return amount;
  numExpr = Math.round(numExpr * factor);
  var minus = (numExpr < 0) ? '-' : ''; 
  numExpr = Math.abs(numExpr);
  var decimal = (numExpr % factor).toString();
  while (decimal.length < decimal_precise) decimal = '0' + decimal;
  var whole = Math.floor(numExpr / factor).toString();
  for (var i = 0; i < Math.floor((whole.length-(1+i))/3); i++)
    whole = whole.substring(0,whole.length-(4*i+3)) + thousands_point + whole.substring(whole.length-(4*i+3));
  if (decimal_precise > 0) {
    return minus + whole + decimal_point + decimal;
  } else {
	return minus + whole;
  }
}

function AlertError(MethodName, e)  {
  if (e.description == null) {  $.messager.alert("methode error",MethodName + "Exception: " + e.message, "error"); }
  else {  $.messager.alert("methode error",MethodName + " Exception: " + e.description, "error"); }
}

// Chart functions
chartProps = new Object();
google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(drawChart);
function drawChart() {}

function phreedomChart() {
  var modID = chartProps.modID;
  var func  = chartProps.func;
  var d0    = chartProps.d0;
  $.ajax({
	type: "GET",
	url: 'index.php?module=phreedom&page=ajax&op=phreedom&action=chart&modID='+modID+'&fID='+func+'&d0='+d0,
	dataType: ($.browser.msie) ? "text" : "xml",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
	},
	success: phreedomChartResp
  });
}

function phreedomChartResp(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  var error = $(xml).find("error").text();
  if (error) { $.messager.alert("Chart error",error,"error"); }
  else { // activate the chart response
	$('#'+chartProps.divID).dialog("option", "title", $(xml).find("title").text());
	$('#'+chartProps.divID).dialog("option", "width", parseInt($(xml).find("width").text())+40);
    var data = new google.visualization.DataTable();
    data.addColumn('string', $(xml).find("label_text").text());
    data.addColumn('number', $(xml).find("value_text").text());
    var rowCnt = parseInt($(xml).find("rowCnt").text());
    var divID  = document.getElementById(chartProps.divID);
    data.addRows(rowCnt);
    rowCnt = 0;
    $(xml).find("chartData").each(function() {
      data.setCell(rowCnt, 0, $(this).find("string").text());
      data.setCell(rowCnt, 1, parseFloat($(this).find("number").text()));
      rowCnt++;
    });
    var options = {'title':$(xml).find("title").text(), 'width':$(xml).find("width").text(), 'height':$(xml).find("height").text()};
    switch ($(xml).find("type").text()) {
      default:
      case 'pie':    var chart = new google.visualization.PieChart(divID);    break;
      case 'bar':    var chart = new google.visualization.BarChart(divID);    break;
      case 'column': var chart = new google.visualization.ColumnChart(divID); break;
      case 'guage':  var chart = new google.visualization.Guage(divID);       break;
      case 'line':   var chart = new google.visualization.LineChart(divID);   break;
//    case 'map':    var chart = new google.visualization.Map(divID);         break;
    }
    chart.draw(data, options);
	$('#'+chartProps.divID).dialog('open');
  }
}

// ******************** functions used for combo box scripting ***********************************
var fActiveMenu = false;
var oOverMenu   = false;

if (document.images) { //pre-load images
 img_on      = new Image();
 img_on.src  = combo_image_on; 
 img_off     = new Image();
 img_off.src = combo_image_off; 
}

if (pbBrowser != 'IE') document.onmouseup = mouseSelect(0); // turns off the combo box if not hovering over

function mouseSelect(e) {
	if (fActiveMenu) {
		if (oOverMenu == false) {
			oOverMenu = false;
			document.getElementById(fActiveMenu).style.display = "none";
			fActiveMenu = false;
			return false;
		}
		return false;
	}
	return true;
}

function dropDownData(id, text) {
  this.id   = id;
  this.text = text;
}

function buildDropDown(selElement, data, defValue) {
  // build the dropdown
  for (var i=0; i<data.length; i++) {
	newOpt = document.createElement("option");
	newOpt.text = data[i].text;
	document.getElementById(selElement).options.add(newOpt);
	document.getElementById(selElement).options[i].value = data[i].id;
  }
  if (defValue != false) document.getElementById(selElement).value = defValue;
}

function htmlComboBox(name, values, defaultVal, parameters, width, onChange) {
  var field;
  field = '<input type="text" name="' + name + '" id="' + name + '" value="' + defaultVal + '" ' + parameters + '>';
  field += '<image name="imgName' + name + '" id="imgName' + name + '" src="' + icon_path + '16x16/phreebooks/pull_down_inactive.gif" height="16" width="16" align="absmiddle" style="border:none;" onMouseOver="handleOver(\'imgName' + name + '\'); return true;" onMouseOut="handleOut(\'imgName' + name + '\'); return true;" onclick="JavaScript:cbMmenuActivate(\'' + name + '\', \'combodiv' + name + '\', \'combosel' + name + '\', \'imgName' + name + '\')">';
  field += '<div id="combodiv' + name + '" style="position:absolute; display:none; top:0px; left:0px; z-index:5000" onmouseover="javascript:oOverMenu=\'combodiv' + name + '\';" onmouseout="javascript:oOverMenu=false;">';
  field += '<select size="10" id="combosel' + name + '" style="width:' + width + '; border-style:none" onclick="JavaScript:textSet(\'' + name + '\', this.value); ' + onChange + ';" onkeypress="JavaScript:comboKey(\'' + name + '\', this, event);">';
  field += '</select></div>';
  return field;
}

function cbMmenuActivate(idEdit, idMenu, idSel, idImg) {
  if (fActiveMenu) return mouseSelect(0);
//$.messager.alert("cbMmenuActivate",'idEdit = '+idEdit+' and idMenu = '+idMenu+' and idSel = '+idSel+' and idImg = '+idImg,"info");
  oEdit = document.getElementById(idEdit);
  oMenu = document.getElementById(idMenu);
  oSel  = document.getElementById(idSel);
  oImg  = document.getElementById(idImg);
  nTop  = oEdit.offsetTop + oEdit.offsetHeight;
  nLeft = oEdit.offsetLeft;
  while (oEdit.offsetParent != document.body) {
    oEdit  = oEdit.offsetParent;
	nTop  += oEdit.offsetTop;
	nLeft += oEdit.offsetLeft;
  }
  oMenu.style.display = "";
  oMenu.style.top     = nTop + 'px';
  oMenu.style.left    = (nLeft - oSel.offsetWidth + oImg.offsetLeft + oImg.offsetWidth) + 'px';
  fActiveMenu = idMenu;
  document.getElementById(idSel).value = document.getElementById(idEdit).value;
  document.getElementById(idSel).focus();
  return false;
}

function textSet(idEdit, text) {
  document.getElementById(idEdit).value = text;
  oOverMenu = false;
  mouseSelect(0);
  document.getElementById(idEdit).focus();
}

function comboKey(idEdit, idSel, e) {
  var keyPressed;
  if(window.event) {
	keyPressed = window.event.keyCode; // IE hack
  } else {
	keyPressed = e.which; // standard method
  }
  if (keyPressed == 13 || keyPressed == 32) {
	textSet(idEdit,idSel.value);
  } else if (keyPressed == 27) {
	mouseSelect(0);
	document.getElementById(idEdit).focus();
  }
}

function handleOver(idImg) { 
 if (document.images) document.getElementById(idImg).src=img_on.src;
}

function handleOut(idImg) {
 if (document.images) document.getElementById(idImg).src=img_off.src;
}

// ***************** START function to build html strings ******************************************
function buildIcon(imagePath, alt, params) {
    var image_html = '<img src="' + imagePath + '" alt="' + alt + '" title="' + alt + '" ' + params + ' />';
    return image_html;
}

// ***************** START function to set button pressed ******************************************
function showLoading() {
  $("#please_wait").show();
}

function hideLoading() {
  $("#please_wait").hide();
}

function submitToDo(action, multi_submit) {
  if (!multi_submit) multi_submit = false;  
  document.getElementById('action').value = action;
  if (!form_submitted && check_form() && !multi_submit) {
	showLoading();
	form_submitted = true;
	document.getElementById('action').form.submit();
  } else if (multi_submit) {
	document.getElementById('action').form.submit();						 
  }
}

function submitSeq(rowSeq, action, multi_submit) {
  if (!multi_submit) multi_submit = false;  
  document.getElementById('rowSeq').value = rowSeq;
  submitToDo(action, multi_submit);
}

function submitSortOrder(sField, sOrder) {
  document.getElementById('sf').value = sField;
  document.getElementById('so').value = sOrder;
  document.getElementById('action').form.submit();
}

function searchPage(get_params) {
  var searchText = document.getElementById('search_text').value;
  location.href = 'index.php?'+get_params+'&search_text='+searchText;
}

function periodPage(get_params) {
  var searchPeriod = document.getElementById('search_period').value;
  location.href = 'index.php?'+get_params+'&search_period='+searchPeriod;
}

function jumpToPage(get_params) {
  var index = document.getElementById('list').selectedIndex;
  var pageNum = document.getElementById('list').options[index].value;
  location.href = 'index.php?'+get_params+'&list='+pageNum;
}

function checkEnter(e) {
  var keycode;
  if(window.event) { // IE
    keycode = event.keyCode;
  } else if (e.which) { // Netscape/Firefox/Opera
    keycode = e.which;
  }
  if (keycode == 13) {
	  document.getElementById('action').value = 'search';
	  document.getElementById('search_text').form.submit();
  }
}

// ajax wrappers
function parseXml(xml) {   
  if (jQuery.browser.msie) {
    var xmlDoc = new ActiveXObject("Microsoft.XMLDOM"); 
    xmlDoc.loadXML(xml);
    xml = xmlDoc;
  }
  var error = '';
  var msg = '';
  var caution = '';
  if ($(xml).find("debug").text() != '') $.messager.alert("debug info",$(xml).find("debug").text(),"info");
  $(xml).find("messageStack").each(function() {
	  if ($(this).find("messageStack_error").text() != ''){
		  error += $(this).find("messageStack_error").text() + "\n";
	  }else if ($(xml).find("messageStack_msg").text() != ''){
		  msg += $(this).find("messageStack_msg").text() + "\n";
	  }else if ($(xml).find("messageStack_caution").text() != ''){
		  caution += $(this).find("messageStack_caution").text() + "\n";
	  }
  });
  if (error) {
	  $.messager.show({
		  title:'MessageStack Error',
		  msg: error,
		  timeout:0,
		  showType:'fade'
	  });  
	  return false;
  }
  if (msg) {
	  $.messager.show({
		  title:'MessageStack msg',
		  msg: msg,
		  timeout:0,
		  showType:'fade'
	  });
  }
  if (caution) {
	  $.messager.show({
		  title:'MessageStack Caution',
		  msg: caution,
		  showType:'show'
	  });
  }
  if ($(xml).find("error").text()) {
	  $.messager.alert("xml Error", $(xml).find("error").text(),"error");
	  return false;
  }
  return xml;
}

// ajax pair to reload tab on pages
function tabPage(subject, action, rID) {
  if (subject) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=phreedom&page=ajax&op=tab_details&mod='+module+'&subject='+subject+'&action='+action+'&rID='+rID,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
    	  $.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
      },
	  success: processTabPage
    });
  }
}

function processTabPage(sXml) {
  var text = '';
  var xml = parseXml(sXml);
  if (!xml) return;
  subject = $(xml).find("subject").text();
  if (!subject) $.messager.alert('error','no subject returned','error');
  obj = document.getElementById(subject+'_content');
  obj.innerHTML = $(xml).find("htmlContents").text();
  if ($(xml).find("message").text()) $.messager.alert("Message",$(xml).find("message").text(),'info');
}

/************************ Folder Navigation Functions **********************************/
function Toggle(item) {
  obj = document.getElementById(item);
  if (obj == null) {
    visible = false;
  } else {
    visible = (obj.style.display!="none");
  }
  key = document.getElementById("img" + item);
  if (visible) {
    obj.style.display="none";
    key.innerHTML="<img src='"+icon_path+"16x16/places/folder.png' width='16' height='16' hspace='0' vspace='0' border='0' />";
  } else {
    if (obj != null) obj.style.display = "block";
    key.innerHTML = "<img src='"+icon_path+"16x16/actions/document-open.png' width='16' height='16' hspace='0' vspace='0' border='0' />";
  }
  if (document.getElementById('id'))        document.getElementById('id').value          = '';
  if (document.getElementById('doc_title')) document.getElementById('doc_title').value   = '';
  if (document.getElementById('folder_path')) {
    var tempArray = item.split("_");
    strDir = (tempArray[2] == '0') ? '/' : build_path(tempArray[2]);
    document.getElementById('folder_path').value = strDir;
	document.getElementById('parent_id').value   = tempArray[2];
  }
}

function build_path(id) {
  var strTitle;
  strTitle = (id != '0') ? dir_idx[id]+'/' : '/';
  if (lvl_idx[id]) strTitle = build_path(lvl_idx[id]) + strTitle;
  return strTitle;
}

function Expand(tab_type) {
  divs = document.getElementsByTagName("DIV");
  for (var i=0; i<divs.length; i++) {
	div_id = divs[i].id;
	if (div_id.substr(0,3) == tab_type) {
	  divs[i].style.display = "block";
	  key = document.getElementById("img" + div_id);
	  key.innerHTML = "<img src='"+icon_path+"16x16/actions/document-open.png' width='16' height='16' hspace='0' vspace='0' border='0' />";
	}
  }
}

function Collapse(tab_type) {
  divs=document.getElementsByTagName("DIV");
  for (var i=0; i<divs.length; i++) {
	div_id = divs[i].id;
	if (div_id.substr(0,3) == tab_type) {
	  divs[i].style.display="none";
	  key = document.getElementById("img" + div_id);
	  key.innerHTML="<img src='"+icon_path+"16x16/places/folder.png' width='16' height='16' hspace='0' vspace='0' border='0' />";
	}
  }
}
