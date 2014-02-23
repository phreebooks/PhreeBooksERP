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
//  Path: /modules/work_orders/pages/tasks/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass some php variables
var securityLevel = <?php echo $security_level; ?>;

// required function called with every page load
function init() {
  document.getElementById('search_text').focus();
  document.getElementById('search_text').select();
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  if (error == 1) {
	alert(error_message);
	return false;
  } else {
	return true;
  }
}

function deleteItem(id) {
  location.href = 'index.php?module=work_orders&page=tasks&action=delete&cID='+id;
}

function clearTask() {
  document.getElementById('id').value          = '0';
  document.getElementById('task_name').value   = '';
  document.getElementById('description').value = '';
  document.getElementById('ref_doc').value     = '';
  document.getElementById('ref_spec').value    = '';
  document.getElementById('dept_id').value     = '';
  document.getElementById('job_time').value    = '';
  document.getElementById('job_unit').value    = '1';
  document.getElementById('mfg').value         = '0';
  document.getElementById('qa').value          = '0';
  document.getElementById('data_entry').value  = '0';
  document.getElementById('erp_entry').value   = '0';
}

// ajax pair to fetch item info
function editTask(id) { // request funtion
  $.ajax({
    type: "GET",
    url: 'index.php?module=work_orders&page=ajax&op=load_task&id='+id,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fillTask
  });
}

function fillTask(sXml) { // call back function
  var xml = parseXml(sXml);
  if (!xml) return;
  var id = $(xml).find("id").first().text();
  document.getElementById('id').value          = id;
  document.getElementById('task_name').value   = $(xml).find("task_name").text();
  document.getElementById('description').value = $(xml).find("description").text();
  document.getElementById('ref_doc').value     = ($(xml).find("ref_doc").text())  ? $(xml).find("ref_doc").text()  : '';
  document.getElementById('ref_spec').value    = ($(xml).find("ref_spec").text()) ? $(xml).find("ref_spec").text() : '';
  document.getElementById('dept_id').value     = ($(xml).find("dept_id").text())  ? $(xml).find("dept_id").text()  : '';
  document.getElementById('job_time').value    = $(xml).find("job_time").text();
  document.getElementById('job_unit').value    = $(xml).find("job_unit").text();
  document.getElementById('mfg').value         = $(xml).find("mfg").text();
  document.getElementById('qa').value          = $(xml).find("qa").text();
  document.getElementById('data_entry').value  = $(xml).find("data_entry").text();
  document.getElementById('erp_entry').value  = $(xml).find("erp_entry").text();
  // turn off some icons
  if (id && securityLevel < 3) removeElement('tb_main_0', 'tb_icon_save');
}
// end ajax pair

// -->
</script>