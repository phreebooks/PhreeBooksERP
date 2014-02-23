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
//  Path: /modules/shipping/pages/ship_mgr/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
<?php echo js_calendar_init($cal_ship); ?>

function init() {
  var dlg = $('#shipping_dialog').dialog({ 
	autoOpen: false,
	buttons: { "<?php echo TEXT_SUBMIT; ?>": function() { $(this).dialog('close'); document.getElementById('action').form.submit(); } }
  });
  dlg.parent().appendTo($("#ship_mgr"));
  document.getElementById('search_date').onchange = calendarPage;
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function loadPopUp(method, action, id) {
  window.open("index.php?module=shipping&page=popup_tracking&method="+method+"&action="+action+"&sID="+id,"popup_tracking","width=500,height=350,resizable=1,scrollbars=1,top=150,left=200");
}

function submitAction(module_id, action) {
  document.getElementById('module_id').value = module_id;
  submitToDo(action, true);
}

function submitShipSequence(module_id, rowSeq, todo) {
  document.getElementById('module_id').value = module_id;
  submitSeq(rowSeq, todo);
}

function calendarPage() {
  location.href = 'index.php?module=shipping&page=ship_mgr&search_date='+document.ship_mgr.search_date.value;
}

function getDialog(method, template) {
  if (!method || !template) return;
  $.ajax({
    type: "GET",
    url: 'index.php?module=shipping&page=ajax&op=shipping&action=form&method='+method+'&template='+template,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fillDialog
  });
}

function fillDialog(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("message").text()) alert ($(xml).find("message").text());
  if ($(xml).find("width").text()) {
	  $("#shipping_dialog").dialog("option", "width", parseInt($(xml).find("width").text()));
  }
  document.getElementById('action').value = $(xml).find("action").text();
  document.getElementById('module_id').value = $(xml).find("method").text();
  document.getElementById('shipping_dialog').innerHTML = $(xml).find("html").text();
  $('#shipping_dialog').dialog('open');
}

function trackPackage(method, tID) {
  if (!method || !tID) return;
  $.ajax({
    type: "GET",
    url: 'index.php?module=shipping&page=ajax&op=shipping&action=tracking&method='+method+'&tID='+tID,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fillTracking
  });
}

function fillTracking(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("message").text()) alert ($(xml).find("message").text());
}

// -->
</script>