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
//  Path: /modules/shipping/methods/fedex_v7/label_mgr/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var formName          = "<?php echo $_GET['form']; ?>"; 
var image_delete_text = '<?php echo TEXT_DELETE; ?>';
var image_delete_msg  = '<?php echo SHIPPING_DELETE_CONFIRM; ?>';
<?php echo js_calendar_init($cal_ship); ?>
<?php echo js_calendar_init($cal_exp); ?>

function init() {
  <?php 
    if (!$error && !$auto_print && ($_REQUEST['action'] == 'label' || $_REQUEST['action'] == 'delete')) {
	  echo '  window.opener.location.reload();' . chr(10);
	  echo '  self.close();' . chr(10);
    } 
    if (!$auto_print) echo '  document.getElementById("wt_1").focus();' . chr(10);
  ?>
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";
  if (!document.getElementById('total_weight').value) {
  	error_message += '<?php echo SHIPPING_ERROR_WEIGHT_ZERO; ?>' + '\n';
	error = 1;
  }
  if (error == 1) {
	$.messager.alert('error',error_message,'error');
    return false;
  }
  return true;
}

// Insert other page specific functions here.
function paperPrint() {
  window.location = "<?php echo html_href_link(FILENAME_DEFAULT, 'module=shipping&page=popup_label_viewer&method=' . $shipping_module . '&date=' . $date . '&labels=' . implode(':',$pdf_list), 'SSL'); ?>";
}

function addRow() {
	var cell = new Array(5);
	var newRow = document.getElementById("item_table").insertRow(-1);
	var rowCnt = newRow.rowIndex;
	// NOTE: any change here also need to be made below for reload if action fails
	cell[0] = '<td align="center">';
	cell[0] += buildIcon(icon_path+'16x16/emblems/emblem-unreadable.png', image_delete_text, 'style="cursor:pointer" onclick="if (confirm(\''+image_delete_msg+'\')) removeRow('+rowCnt+');"') + '</td>';
	cell[1] = '<td align="center"><input type="text" name="qty_'+rowCnt+'" id="qty_'+rowCnt+'" value="1" size="6" maxlength="5" style="text-align:right"></td>';
	cell[2] = '<td align="center"><input type="text" name="wt_' +rowCnt+'" id="wt_' +rowCnt+'" value="" size="5" maxlength="4" style="text-align:right" onchange="updateWeight('+rowCnt+')"></td>';
	cell[3] = '<td align="center"><input type="text" name="len_'+rowCnt+'" id="len_'+rowCnt+'" value="8" size="5" maxlength="4" style="text-align:right"></td>';
	cell[4] = '<td align="center"><input type="text" name="wid_'+rowCnt+'" id="wid_'+rowCnt+'" value="6" size="5" maxlength="4" style="text-align:right"></td>';
	cell[5] = '<td align="center"><input type="text" name="hgt_'+rowCnt+'" id="hgt_'+rowCnt+'" value="4" size="5" maxlength="4" style="text-align:right"></td>';
	cell[6] = '<td align="center"><input type="text" name="ins_'+rowCnt+'" id="ins_'+rowCnt+'" value="0.00" size="8" maxlength="7" style="text-align:right" onchange="updateWeight('+rowCnt+')"></td>';
	var newCell;
	for (var i=0; i<cell.length; i++) {
		newCell = newRow.insertCell(-1);
		newCell.innerHTML = cell[i];
	}
	return rowCnt;
}

function removeRow(delRowCnt) {
  // remove row from display by reindexing and then deleting last row
  for (var i=delRowCnt; i<(document.getElementById('item_table').rows.length-1); i++) {
  	// delete icon (don't delete to keep row reference)
	// remaining cell values
	document.getElementById('qty_'+i).value = document.getElementById('qty_'+(i+1)).value;
	document.getElementById('wt_'+i).value  = document.getElementById('wt_'+(i+1)).value;
	document.getElementById('len_'+i).value = document.getElementById('len_'+(i+1)).value;
	document.getElementById('wid_'+i).value = document.getElementById('wid_'+(i+1)).value;
	document.getElementById('hgt_'+i).value = document.getElementById('hgt_'+(i+1)).value;
	document.getElementById('ins_'+i).value = document.getElementById('ins_'+(i+1)).value;
  }
  document.getElementById('item_table').deleteRow(-1);
  updateWeight();
}

function updateWeight() {
  var temp;
  var weightTotal = 0;
  var valueTotal = 0;
  for (var i=1; i<(document.getElementById('item_table').rows.length+1); i++) {
	temp = parseFloat(cleanCurrency(document.getElementById('wt_'+i).value));
	tempQty = parseFloat(cleanCurrency(document.getElementById('qty_'+i).value));
  	if (!isNaN(temp)) weightTotal += (tempQty * temp);
	temp = parseFloat(cleanCurrency(document.getElementById('ins_'+i).value));
  	if (!isNaN(temp)) valueTotal += (tempQty * temp);
  }
//  var tot = new String(weightTotal);
  document.getElementById('total_weight').value = weightTotal;
  var vt = new String(valueTotal);
  document.getElementById('total_value').value = formatCurrency(vt);
}

// java label printing
function labelPrint() {
  var applet = document.qz;
  if (applet != null) {
	applet.append("<?php echo $label_data; ?>");
	applet.print();
  }
  monitorPrinting();
}

function monitorPrinting() {
  var applet = document.qz;
  if (applet != null) {
    if (!applet.isDonePrinting()) {
      window.setTimeout('monitorPrinting()', 1000);
    } else {
      var e = applet.getException();
      if (e != null) {
    	$.messager.alert("Exception occured ", e.getLocalizedMessage(),'error');
	  } else {
		<?php echo (sizeof($pdf_list) > 0) ? " paperPrint();\n" : " window.opener.location.reload();\n self.close();\n"; ?>
	  }
    }
  } else {
	alert("Error: Java label printing applet not loaded!");
  }
}

// -->
</script>