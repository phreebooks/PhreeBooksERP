<?php
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
//  Path: /modules/work_orders/pages/popup_tasks/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
// var jsVariable = '<?php echo CONSTANT; ?>';

function init() {
  document.getElementById('search_text').focus();
  document.getElementById('search_text').select();
}

function check_form() {
  return true;
}
// Insert javscript file references here.


// Insert other page specific functions here.
function taskRecord(id, name, desc) {
  this.id          = id;
  this.task_id     = name;
  this.description = desc;
}

function setReturnItem(index, rowCnt) {
  window.opener.document.getElementById('task_id_'+rowCnt).value    = task_list[index].id;
  window.opener.document.getElementById('task_'+rowCnt).value       = task_list[index].task_id;
  window.opener.document.getElementById('desc_'+rowCnt).value       = task_list[index].description;
  window.opener.document.getElementById('task_'+rowCnt).style.color = '';
  self.close();
}

function assyRecord(sku, description, qty, quantity_on_hand) {
	this.sku = sku;
	this.description = description;
	this.qty = qty;
	this.quantity_on_hand = quantity_on_hand;
}

// -->
</script>