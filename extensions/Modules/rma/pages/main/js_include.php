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
//  Path: /modules/rma/pages/main/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass some php variables
var sender            = 'rcv_';
var text_search       = '<?php echo TEXT_SEARCH; ?>';
var image_delete_text = '<?php echo TEXT_DELETE; ?>';
var image_delete_msg  = '<?php echo RMA_ROW_DELETE_ALERT; ?>';
var delete_icon_HTML  = '<?php echo substr(html_icon("emblems/emblem-unreadable.png", TEXT_DELETE, "small", "onclick=\"if (confirm(\'" . RMA_ROW_DELETE_ALERT . "\')) removeInvRow("), 0, -2); ?>';
<?php echo js_calendar_init($cal_create); ?>
<?php echo js_calendar_init($cal_rcv); ?>
<?php echo js_calendar_init($cal_close); ?>
<?php echo js_calendar_init($cal_invoice); ?>

<?php 
  echo $js_disp_code . chr(10);
  echo $js_disp_value . chr(10);
?>

// required function called with every page load
function init() {
  <?php if ($_REQUEST['action'] <> 'new' && $_REQUEST['action'] <> 'edit') { // set focus for main window
	echo "  $('#search_text').focus();";
	echo "  $('#search_text').select();";
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

function ItemList(rqster, rowCnt) {
	sender      = rqster;
	var storeID = '0';
	var sku     = document.getElementById(sender+'sku_'+rowCnt).value;
	window.open("index.php?module=inventory&page=popup_inv&rowID="+rowCnt+"&storeID="+storeID+"&search_text="+sku,"inventory","width=700px,height=550px,resizable=1,scrollbars=1,top=150,left=200");
}

function loadSkuDetails(iID, rID) {
  var cID = 0;
  var jID = 10;
  var sku = '';
  var strict = '';
  if (!rID) return;
  if ( iID == 0 && document.getElementById(sender+'notes_'+rID).value == '' && document.getElementById(sender+'sku_'+rID).value != ''){
	  sku = document.getElementById(sender+'dis_sku_'+rID).value;
	  strict = '&strict=1';
  }
  if (iID == 0 && (sku == '' || sku == text_search)){
	  if ( document.getElementById(sender+'sku_'+rID).value != ''){
		    sku = document.getElementById(sender+'sku_'+rID).value;
		    strict = '&strict=1';
	  }
  }
  $.ajax({
    type: "GET",
    url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&cID='+cID+'&iID='+iID+'&sku='+sku+'&rID='+rID+'&jID='+jID + strict,
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
  var rID = $(xml).find("rID").text();
  if (!rID) return;  
  //switch between receiving and disposition
  if (sender == 'dis_'){
	document.getElementById(sender+'qty_'   +rID).value			 = $(xml).find("qty").text();
	document.getElementById(sender+'sku_'   +rID).value       = $(xml).find("sku").text();
  	document.getElementById(sender+'sku_'   +rID).style.color = '';
  	document.getElementById(sender+'notes_' +rID).value       = $(xml).find("description_short").text();
  } else { // sender == 'rcv_'
	document.getElementById(sender+'qty_'   +rID).value		  = $(xml).find("qty").text();
	document.getElementById(sender+'sku_'   +rID).value       = $(xml).find("sku").text();
	document.getElementById(sender+'sku_'   +rID).style.color = '';
	document.getElementById(sender+'desc_'  +rID).value       = $(xml).find("description_short").text();
		  
  }
}

function deleteItem(id) { // deletes a RMA
  location.href = 'index.php?module=rma&page=main&action=delete&cID='+id;
}

function addItemRow() {
  var cell = Array();
  var newRow = document.getElementById('item_table').insertRow(-1);
  var newCell;
  rowCnt = newRow.rowIndex;

  cell[0] = buildIcon(icon_path+'16x16/emblems/emblem-unreadable.png', image_delete_text);
  cell[1] = '<input type="text" id="dis_qty_'+rowCnt+'" name="dis_qty[]" size="7" style="text-align:right">';
  cell[2] = '<input type="text" id="dis_sku_'+rowCnt+'" name="dis_sku[]" title="'+text_search+'" size="24" onchange="loadSkuDetails(0,'+rowCnt+') ">&nbsp;';
  cell[2] += buildIcon(icon_path+'16x16/status/folder-open.png', text_search, 'id="sku_open_'+rowCnt+'" align="top" style="cursor:pointer" onclick="ItemList(\'dis_\','+rowCnt+')"');
  cell[3] = '<input type="text" id="dis_notes_'+rowCnt+'" name="dis_notes[]" size="48">';
  cell[4] = '<select id="dis_action_'+rowCnt+'" name="dis_action[]"><\/select>';

  for (var i=0; i<cell.length; i++) {
    newCell = newRow.insertCell(-1);
	newCell.innerHTML = cell[i];
  }
  newRow.getElementsByTagName('img')[0].onclick = function() {
	  if (confirm(image_delete_msg)) $(this).parent().parent().remove();
  };
  // fill in the select list
  for (var i=0; i<js_disp_code.length; i++) {
	newOpt = document.createElement("option");
	newOpt.text = js_disp_value[i];
	newRow.getElementsByTagName('select')[0].options.add(newOpt);
	newRow.getElementsByTagName('select')[0].options[i].value = js_disp_code[i];
  }
  // change sku searh field to incative text color
  newRow.getElementsByTagName('input')[1].style.color = inactive_text_color;
  return rowCnt;
}

function addRcvRow() {
  var cell = Array();
  var newRow = document.getElementById('rcv_table').insertRow(-1);
  var newCell;
  rowCnt = newRow.rowIndex;

  cell[0] = buildIcon(icon_path+'16x16/emblems/emblem-unreadable.png', image_delete_text);
  cell[1] = '<input type="text" id="rcv_qty_'+rowCnt+'"   name="rcv_qty[]" size="7" maxlength="6" style="text-align:right">';
  cell[2] = '<input type="text" id="rcv_sku_'+rowCnt+'"   name="rcv_sku[]" title="'+text_search+'" size="12" onchange="loadSkuDetails(0,'+rowCnt+') ">&nbsp;';
  cell[2] += buildIcon(icon_path+'16x16/status/folder-open.png', text_search, 'align="top" style="cursor:pointer" onclick="ItemList(\'rcv_\','+rowCnt+')"') ;
  cell[3] = '<input type="text" id="rcv_desc_'+rowCnt+'"  name="rcv_desc[]"  size="32">';
  cell[4] = '<input type="text" id="rcv_mfg_'+rowCnt+'"   name="rcv_mfg[]"   size="32">';
  cell[5] = '<input type="text" id="rcv_wrnty_'+rowCnt+'" name="rcv_wrnty[]" size="32">';

  for (var i=0; i<cell.length; i++) {
    newCell = newRow.insertCell(-1);
	newCell.innerHTML = cell[i];
  }
  newRow.getElementsByTagName('img')[0].onclick = function() {
	  if (confirm(image_delete_msg)) $(this).parent().parent().remove();
  };
  newRow.getElementsByTagName('input')[1].style.color = inactive_text_color;
  return rowCnt;
}

// -->
</script>