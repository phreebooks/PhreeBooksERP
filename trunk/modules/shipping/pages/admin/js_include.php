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
//  Path: /modules/shipping/pages/admin/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
function init() {
  var dlg = $('#shipping_dialog').dialog({ 
	  autoOpen: false,
	  buttons: { "<?php echo TEXT_SUBMIT; ?>": function() { $(this).dialog('close'); document.getElementById('action').form.submit(); } }
  });
  dlg.parent().appendTo($("#admin"));
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function toggleProperties(id) {
  if (document.getElementById(id).style.display == 'none') {
    document.getElementById(id).style.display = '';
  } else {
    document.getElementById(id).style.display = 'none';
  }
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
  if ($(xml).find("width").text()) $("#shipping_dialog").dialog("option", "width", parseInt($(xml).find("width").text()));
  document.getElementById('action').value = $(xml).find("action").text();
  document.getElementById('shipping_dialog').innerHTML = $(xml).find("html").text();
  $('#shipping_dialog').dialog('open');
}

// -->
</script>