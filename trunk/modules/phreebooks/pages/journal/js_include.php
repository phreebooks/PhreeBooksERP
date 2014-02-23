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
//  Path: /modules/phreebooks/pages/journal/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var image_delete_text = '<?php echo TEXT_DELETE; ?>';
var image_delete_msg  = '<?php echo GL_DELETE_GL_ROW; ?>';
var text_acct_ID      = '<?php echo TEXT_GL_ACCOUNT; ?>';
var text_increased    = '<?php echo GL_ACCOUNT_INCREASED; ?>';
var text_decreased    = '<?php echo GL_ACCOUNT_DECREASED; ?>';
var journalID         = '<?php echo JOURNAL_ID; ?>';
var securityLevel     = <?php echo $security_level; ?>;
<?php echo js_calendar_init($cal_gl); ?>

<?php echo $js_gl_array; ?>

function init() {
<?php if ($_REQUEST['action'] == 'edit') echo '  EditJournal(' . $oID . ');' . chr(10); ?>
  document.getElementById("purchase_invoice_id").focus();
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";
  // check for balance of credits and debits
  var bal_total = cleanCurrency(document.getElementById('balance_total').value);
  if (bal_total != 0) {
  	error_message += "<?php echo GL_ERROR_OUT_OF_BALANCE; ?>";
  	error = 1;
  }
  // check for all accounts valid
  for (var i = 1; i <= ((document.getElementById("item_table").rows.length - 1) / 2); i++) {
	if (!updateDesc(i)) {
	  error_message += "<?php echo GL_ERROR_BAD_ACCOUNT; ?>";
	  error = 1;
	  break;
	}
  }
  // With edit of order and recur, ask if roll through future entries or only this entry
  var todo = document.getElementById('action').value;
  if (document.getElementById('id').value != "" && document.getElementById('recur_id').value > 0) {
	switch (todo) {
	  case 'delete':
		message = '<?php echo GL_ERROR_RECUR_DEL_ROLL_REQD; ?>';
		break;
	  default:
	  case 'save':
		message = '<?php echo GL_ERROR_RECUR_ROLL_REQD; ?>';
	}
	if (confirm(message)) {
	  document.getElementById('recur_frequency').value = '1';
	} else {
	  document.getElementById('recur_frequency').value = '0';
	}		    
  }
  // Check for purchase_invoice_id exists with a recurring entry
  if (document.getElementById('purchase_invoice_id').value == "" && document.getElementById('recur_id').value > 0) {
	error_message += "<?php echo GL_ERROR_NO_REFERENCE; ?>";
	error = 1; // exit the script
  }

  if (error == 1) {
	$.messager.alert('error',error_message,'error');
    return false;
  }
  return true;
}

function OpenGLList() {
  window.open("index.php?module=phreebooks&page=popup_journal&list=1&form=journal","gl_open","width=700,height=550,resizable=1,scrollbars=1,top=150,left=200");
}

function OpenRecurList(currObj) {
	window.open("index.php?module=phreebooks&page=popup_recur&jID="+journalID,"recur","width=400px,height=300px,resizable=1,scrollbars=1,top=150,left=200");
}

function verifyCopy() {
  var id = document.getElementById('id').value;
  if (!id) {
	$.messager.alert('error','<?php echo GL_JS_CANNOT_COPY; ?>','error');
	return;
  }
  if (confirm('<?php echo GL_JS_COPY_CONFIRM; ?>')) {
    // don't allow recurring entries for copy
    document.getElementById('recur_id').value        = '0';
    document.getElementById('recur_frequency').value = '0';
    submitToDo('copy');
  }
}

// Insert other page specific functions here.
function EditJournal(rID) {
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreebooks&page=ajax&op=load_record&rID='+rID,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: processEditJournal
  });
}

function processEditJournal(sXml) {
  var DebitOrCredit;
  var xml = parseXml(sXml);
  if (!xml) return;
  document.getElementById('auto_complete').checked = false;
  var id = $(xml).find("id").first().text();
  document.getElementById('id').value        = id;
  if ($(xml).find("purchase_invoice_id").text()) document.getElementById('purchase_invoice_id').value = $(xml).find("purchase_invoice_id").text();
  document.getElementById('post_date').value = formatDate($(xml).find("post_date").first().text());
  document.getElementById('recur_id').value  = $(xml).find("recur_id").text();
  document.getElementById('store_id').value  = $(xml).find("store_id").text();
  if ($(xml).find("attach_exist").text() == 1) {
//	alert ('Showing attach val = '+$(xml).find("attach_exist").text());
	document.getElementById('show_attach').style.display = ''; // show attachment button and delete checkbox if it exists
  } else {
//	alert ('Not showing attach val = '+$(xml).find("attach_exist").text());
  }
  
  // delete the rows
  while (document.getElementById("item_table").rows.length > 0) document.getElementById("item_table").deleteRow(-1);
  // turn off some icons
  if (id && securityLevel < 3) removeElement('tb_main_0', 'tb_icon_save');
  removeElement('tb_main_0', 'tb_icon_recur');// turn off recur if transaction already saved
  // fill item rows
  var jIndex = 1;
  $(xml).find("items").each(function() {
	var rowCnt = addGLRow();
	document.getElementById('id_'+jIndex).value     = $(this).find("id").text();
	document.getElementById('acct_'+jIndex).value   = $(this).find("gl_account").text();
	if ($(this).find("description").text()) document.getElementById('desc_'+jIndex).value = $(this).find("description").text();
	document.getElementById('debit_'+jIndex).value  = formatCurrency($(this).find("debit_amount").text());
	document.getElementById('credit_'+jIndex).value = formatCurrency($(this).find("credit_amount").text());
	DebitOrCredit = ($(this).find("debit_amount").text() != 0) ? 'd' : 'c';
	formatRow(jIndex, DebitOrCredit);
	jIndex++;
  });
  updateBalance();
  if ($(xml).find("closed").text() == '1') $.messager.alert('error','<?php echo WARNING_ENTRY_RECONCILED; ?>','warning');
}

function downloadAttachment() {
  document.getElementById('action').value = 'dn_attach';
  document.getElementById('action').form.submit();
}

function glProperties(id, description, asset) {
  this.id          = id;
  this.description = description;
  this.asset       = asset;
}

function addGLRow(debit, credit, description) {
	if (!debit)       debit  = '';
	if (!credit)      credit = '';
	if (!description) description = '';
	var cell = new Array();
	var newRow = document.getElementById("item_table").insertRow(-1);
	var rowCnt = (newRow.rowIndex+1) / 2;
	// NOTE: any change here also need to be made below for reload if action fails
	cell[0]  = '<td>';
	cell[0] += buildIcon(icon_path+'16x16/emblems/emblem-unreadable.png', image_delete_text, 'style="cursor:pointer" onclick="if (confirm(\''+image_delete_msg+'\')) removeGLRow('+rowCnt+');"') + '<\/td>';
	cell[1]  = '<td>';
	cell[1] += '<select name="acct_'+rowCnt+'" id="acct_'+rowCnt+'" onchange="updateDesc('+rowCnt+')"><\/select>&nbsp;';
	// Hidden fields
	cell[1] += '<input type="hidden" name="id_'+rowCnt+'" id="id_'+rowCnt+'" value="" />';
	// End hidden fields
	cell[2] = '<td><input type="text" name="desc_'+rowCnt+'" id="desc_'+rowCnt+'" size="64" maxlength="64" value="'+description+'"><\/td>';
	cell[3] = '<td><input type="text" name="debit_'+rowCnt+'" id="debit_'+rowCnt+'" style="text-align:right" size="13" maxlength="12"  value="'+debit+'" onchange="formatRow('+rowCnt+', \'d\')"><\/td>';
	cell[4] = '<td><input type="text" name="credit_'+rowCnt+'" id="credit_'+rowCnt+'" style="text-align:right" size="13" maxlength="12" value="'+credit+'"onchange="formatRow('+rowCnt+', \'c\')"><\/td>';

	var newCell, cellCnt, newDiv, divIdName, newDiv, newOpt;
	for (var i=0; i<cell.length; i++) {
		newCell = newRow.insertCell(-1);
		newCell.innerHTML = cell[i];
	}
	// build the account dropdown
    for (i=0; i<js_gl_array.length; i++) {
	  newOpt = document.createElement("option");
      newOpt.text = js_gl_array[i].description;
	  document.getElementById('acct_'+rowCnt).options.add(newOpt);
	  document.getElementById('acct_'+rowCnt).options[i].value = js_gl_array[i].id;
    }
	// insert information row
	newRow = document.getElementById("item_table").insertRow(-1);
	newRow.className += ' ui-state-highlight';
	newCell = newRow.insertCell(-1);
	newCell.colSpan = 3;
	newCell.innerHTML = '<td colspan="3">&nbsp;<\/td>';
	newCell = newRow.insertCell(-1);
	newCell.colSpan = 2;
	newCell.innerHTML = '<td colspan="2" id="msg_'+rowCnt+'">&nbsp;<\/td>';
	document.getElementById("acct_" + rowCnt).focus();
	return rowCnt;
}

function removeGLRow(delRowCnt) {
  var glIndex = (delRowCnt * 2) - 2;
  // remove row from display by reindexing and then deleting last row
  for (var i = delRowCnt; i < ((document.getElementById("item_table").rows.length - 1) / 2); i++) {
	// remaining cell values
	document.getElementById('acct_'+i).value   = document.getElementById('acct_'+(i+1)).value;
	document.getElementById('desc_'+i).value   = document.getElementById('desc_'+(i+1)).value;
	document.getElementById('debit_'+i).value  = document.getElementById('debit_'+(i+1)).value;
	document.getElementById('credit_'+i).value = document.getElementById('credit_'+(i+1)).value;
// Hidden fields
	document.getElementById('id_'+i).value = document.getElementById('id_'+(i+1)).value;
// End hidden fields
	// move information fields
	document.getElementById("item_table").rows[glIndex+1].cells[0].innerHTML = document.getElementById("item_table").rows[glIndex+3].cells[0].innerHTML;
	document.getElementById("item_table").rows[glIndex+1].cells[1].innerHTML = document.getElementById("item_table").rows[glIndex+3].cells[1].innerHTML;
	glIndex = glIndex + 2; // increment the row counter (two rows per entry)
  }
  document.getElementById("item_table").deleteRow(-1);
  document.getElementById("item_table").deleteRow(-1);
  updateBalance(true);
}

function showAction(rowID, DebitOrCredit) {
  var acct = document.getElementById('acct_'+rowID).value;
  var textValue = ' ';

  for (var i = 0; i < js_gl_array.length; i++) {
	if (js_gl_array[i].id == acct) {
	  if ((js_gl_array[i].asset == '1' && DebitOrCredit == 'd') || (js_gl_array[i].asset == '0' && DebitOrCredit == 'c')) {
	    textValue = text_increased;
	  } else {
	    textValue = text_decreased;
	  }
	  break;
	}
  }

  if (document.getElementById('debit_'+rowID).value == '' && document.getElementById('credit_'+rowID).value == '') {
    textValue = ' ';
  }
  if(document.all) { // IE browsers
    document.getElementById("item_table").rows[(rowID*2)-1].cells[1].innerText = textValue;  
  } else { //firefox
    document.getElementById("item_table").rows[(rowID*2)-1].cells[1].textContent = textValue;  
  }
}

function formatRow(rowID, DebitOrCredit) {
  var temp;
  showAction(rowID, DebitOrCredit);
  if (DebitOrCredit == 'd') {
  	if (document.getElementById('debit_'+rowID).value != '') {
		temp = cleanCurrency(document.getElementById('debit_'+rowID).value);
		document.getElementById('debit_'+rowID).value = formatCurrency(temp);
		document.getElementById('credit_'+rowID).value = '';
	}
  } else {
  	if (document.getElementById('credit_'+rowID).value != '') {
		temp = cleanCurrency(document.getElementById('credit_'+rowID).value);
		document.getElementById('credit_'+rowID).value = formatCurrency(temp);
		document.getElementById('debit_'+rowID).value = '';
	}
  }
  updateBalance();
}

function updateBalance() {
  var debit_total = 0;
  var credit_total = 0;
  var balance_total = 0;
  var description = '';
  for (var i = 1; i <= ((document.getElementById('item_table').rows.length) / 2); i++) {
	temp = parseFloat(cleanCurrency(document.getElementById('debit_'+i).value));
  	if (!isNaN(temp)) debit_total += temp;
	temp = parseFloat(cleanCurrency(document.getElementById('credit_'+i).value));
  	if (!isNaN(temp)) credit_total += temp;
  	description = document.getElementById('desc_'+i).value;
  }
  var debit  = 0;
  var credit = 0;
  if (debit_total != credit_total && document.getElementById('auto_complete').checked == true) { // auto fill only for new entries
	if (debit_total > credit_total){
		credit = debit_total  - credit_total;
		credit_total += credit;
	}
	if (debit_total < credit_total){
		debit  = credit_total - debit_total;
		debit_total += debit;
	}
	addGLRow(debit?formatCurrency(debit):'', credit?formatCurrency(credit):'', description);
  }
  balance_total = debit_total - credit_total;
  var dt = new String(debit_total);
  document.getElementById('debit_total').value = formatCurrency(dt);
  var ct = new String(credit_total);
  document.getElementById('credit_total').value = formatCurrency(ct);
  var tot = new String(balance_total);
  document.getElementById('balance_total').value = formatCurrency(tot);
  if (document.getElementById('balance_total').value == formatted_zero) {
  	document.getElementById('balance_total').style.color = '';
  } else {
  	document.getElementById('balance_total').style.color = 'red';
  }
}

function updateDesc(rowID) {
  var acct = document.getElementById('acct_'+rowID).value;
  var DebitOrCredit = '';
  if (document.getElementById('debit_'+rowID).value != '') {
  	DebitOrCredit = 'd';
  } else if (document.getElementById('credit_'+rowID).value != '') {
  	DebitOrCredit = 'c';
  }
  showAction(rowID, DebitOrCredit);
  return true;
}

// -->
</script>