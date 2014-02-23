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
//  Path: /modules/work_orders/pages/builder/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass some php variables
var image_delete_text = '<?php echo TEXT_DELETE; ?>';
var image_delete_msg  = '<?php echo WORK_ORDER_MSG_DELETE_WO; ?>';
var text_sku          = '<?php echo TEXT_SKU; ?>';
var text_search       = '<?php echo TEXT_SEARCH; ?>';

// required function called with every page load
function init() {
  <?php if ($_REQUEST['action'] <> 'new' && $_REQUEST['action'] <> 'edit') { // set focus for main window
	echo "  document.getElementById('search_text').focus();";
	echo "  document.getElementById('search_text').select();";
  } ?>
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

function itemList(rowCnt) {
  var storeID = '0';
  var rowCnt = '1';
  window.open("index.php?module=inventory&page=popup_inv&rowID="+rowCnt+"&storeID="+storeID+"&search_text="+document.getElementById('sku').value,"inventory","width=700px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
}

function loadSkuDetails(iID) {
  var bID = 0;
  var cID = 0;
  var qty = 1;
  var jID = 10;
  var rID = 1;
  var sku = '';
  sku = document.getElementById('sku').value;
//  if (!sku) sku = text_search;
  $.ajax({
    type: "GET",
    url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&bID='+bID+'&cID='+cID+'&qty='+qty+'&iID='+iID+'&sku='+sku+'&rID='+rID+'&jID='+jID,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: processSkuDetails
  });
}

function processSkuDetails(sXml) { // call back function
  var xml = parseXml(sXml);
  if (!xml) return;
  document.getElementById('sku_id').value      = $(xml).find("id").text();
  document.getElementById('sku').value         = $(xml).find("sku").text();
  document.getElementById('sku').style.color   = '';
  document.getElementById('description').value = $(xml).find("description_short").text();
}

function deleteItem(id) {
  location.href = 'index.php?module=work_orders&page=builder&action=delete&id='+id;
}

function copyItem(id) {
	$.messager.prompt('<?php echo TEXT_COPY;?>', '<?php echo TEXT_COPY_TO; ?>', function(title){
		if (title){
			return location.href = 'index.php?module=work_orders&page=builder&action=copy&cID='+id+'&title='+title;
		}
		return false;  
	});
}

function taskList(id) {
  window.open("index.php?module=work_orders&page=popup_tasks&rowID="+id,"popup_tasks","width=900px,height=500px,resizable=1,scrollbars=1,top=50,left=50");
}

function addTaskRow() {
  var cell = Array();
  var newRow = document.getElementById('wo_table').insertRow(-1);
  var newCell;
  rowCnt = newRow.rowIndex;

  cell[0]  = '<td align="center"><input type="text" name="step_'+rowCnt+'" id="step_'+rowCnt+'"'+' value="' + rowCnt + '" readonly="readonly" size="7" maxlength="6" style="text-align:right"></td>';
  cell[1]  = '<td nowrap="nowrap" align="center"><input type="text" name="task_'+rowCnt+'" id="task_'+rowCnt+'" size="12" maxlength="15" onfocus="clearField(\'task_'+rowCnt+'\', \''+text_search+'\')" onblur="setField(\'task_'+rowCnt+'\', \''+text_search+'\')">&nbsp;';
  cell[1] += buildIcon(icon_path+'16x16/actions/system-search.png', text_search, 'id="sku_open_'+rowCnt+'" align="top" style="cursor:pointer" onclick="taskList('+rowCnt+')"') + '</td>';
// Hidden fields
  cell[1] += '<input type="hidden" name="task_id_'+rowCnt+'" id="task_id_'+rowCnt+'" value="">';
// End hidden fields
  cell[1] += '</td>';
  cell[2]  = '<td><input type="text" name="desc_'+rowCnt+'" id="desc_'+rowCnt+'" readonly="readonly" size="64" maxlength="64"></td>';
  cell[3]  = '<td align="center">';
  cell[3] += buildIcon(icon_path+'16x16/actions/go-up.png', '<?php echo TEXT_MOVE_UP; ?>',     'onclick="moveUpTaskRow('+rowCnt+');"');
  cell[3] += buildIcon(icon_path+'16x16/actions/go-down.png', '<?php echo TEXT_MOVE_DOWN; ?>', 'onclick="moveDownTaskRow('+rowCnt+');"');
  cell[3] += buildIcon(icon_path+'16x16/actions/edit-undo.png', '<?php echo TEXT_INSERT; ?>',  'onclick="insertTaskRow('+rowCnt+');"');
  cell[3] += buildIcon(icon_path+'16x16/emblems/emblem-unreadable.png', image_delete_text,     'onclick="if (confirm(\''+image_delete_msg+'\')) removeTaskRow('+rowCnt+');"');
  cell[3] += '</td>';

  for (var i=0; i<cell.length; i++) {
    newCell = newRow.insertCell(-1);
	newCell.innerHTML = cell[i];
    newCell.setAttribute('align', 'center'); 
  }
  setField('task_'+rowCnt, text_search);
  return rowCnt;
}

function moveUpTaskRow(rowCnt) {
  if (rowCnt < 2) return; 
  var id   = document.getElementById('task_id_'+rowCnt).value;
  var task = document.getElementById('task_'+rowCnt).value;
  var desc = document.getElementById('desc_'+rowCnt).value;

  document.getElementById('task_id_'+rowCnt).value = document.getElementById('task_id_'+(rowCnt-1)).value;
  document.getElementById('task_'+rowCnt).value    = document.getElementById('task_'+(rowCnt-1)).value;
  document.getElementById('desc_'+rowCnt).value    = document.getElementById('desc_'+(rowCnt-1)).value;

  document.getElementById('task_id_'+(rowCnt-1)).value = id;
  document.getElementById('task_'+(rowCnt-1)).value    = task;
  document.getElementById('desc_'+(rowCnt-1)).value    = desc;
  setField('task_'+rowCnt, text_search);
  setField('task_'+(rowCnt-1), text_search);
}

function moveDownTaskRow(rowCnt) {
  if (rowCnt > (document.getElementById('wo_table').rows.length-2)) return; 
  var id   = document.getElementById('task_id_'+rowCnt).value;
  var task = document.getElementById('task_'+rowCnt).value;
  var desc = document.getElementById('desc_'+rowCnt).value;

  document.getElementById('task_id_'+rowCnt).value = document.getElementById('task_id_'+(rowCnt+1)).value;
  document.getElementById('task_'+rowCnt).value    = document.getElementById('task_'+(rowCnt+1)).value;
  document.getElementById('desc_'+rowCnt).value    = document.getElementById('desc_'+(rowCnt+1)).value;

  document.getElementById('task_id_'+(rowCnt+1)).value = id;
  document.getElementById('task_'+(rowCnt+1)).value    = task;
  document.getElementById('desc_'+(rowCnt+1)).value    = desc;
  setField('task_'+rowCnt, text_search);
  setField('task_'+(rowCnt+1), text_search);
}

function insertTaskRow(rowCnt) {
  addTaskRow();
  for (var i=(document.getElementById('wo_table').rows.length-2); i>rowCnt-1; i--) {
	document.getElementById('task_id_'+(i+1)).value    = document.getElementById('task_id_'+i).value;
    document.getElementById('task_'+(i+1)).value       = document.getElementById('task_'+i).value;
	document.getElementById('desc_'+(i+1)).value       = document.getElementById('desc_'+i).value;
	setField('task_'+(i+1), text_search);
  }
  document.getElementById('task_id_'+rowCnt).value    = '';
  document.getElementById('task_'+rowCnt).value       = '';
  document.getElementById('desc_'+rowCnt).value       = '';
  setField('task_'+rowCnt, text_search);
}

function removeTaskRow(delRowCnt) {
  // remove row from display by reindexing and then deleting last row
  for (var i=delRowCnt; i<(document.getElementById('wo_table').rows.length-1); i++) {
	document.getElementById('task_id_'+i).value    = document.getElementById('task_id_'+(i+1)).value;
	document.getElementById('task_'+i).value       = document.getElementById('task_'+(i+1)).value;
	document.getElementById('desc_'+i).value       = document.getElementById('desc_'+(i+1)).value;
	setField('task_'+i, text_search);
  }
  document.getElementById('wo_table').deleteRow(-1);
} 

// -->
</script>