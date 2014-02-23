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
//  Path: /modules/phreebooks/pages/reconciliation/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var textShow = '<?php echo TEXT_DETAILS; ?>';
var textHide = '<?php echo TEXT_HIDE; ?>';
var totalCnt = <?php echo sizeof($bank_list); ?>;

function init() {
	updateBalance();
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  if (error == 1) {
	$.messager.alert('error',error_message,'error');
    return false;
  }
  return true;
}

// Insert other page specific functions here.
function showDetails(ref) {
  if(document.all) { // IE browsers
    if (document.getElementById('disp_'+ref).innerText == textHide) {
      document.getElementById('detail_'+ref).style.display = 'none';
	  document.getElementById('disp_'+ref).innerText = textShow;
	} else {
      document.getElementById('detail_'+ref).style.display = '';
	  document.getElementById('disp_'+ref).innerText = textHide;
	}
  } else {
    if (document.getElementById('disp_'+ref).textContent == textHide) {
      document.getElementById('detail_'+ref).style.display = 'none';
	  document.getElementById('disp_'+ref).textContent = textShow;
	} else {
      document.getElementById('detail_'+ref).style.display = '';
	  document.getElementById('disp_'+ref).textContent = textHide;
	}
  }
}

function updateSummary(ref) {
  var cnt = 0;
  var rowRef = 'disp_'+ref+'_';
  var checked = document.getElementById('sum_'+ref).checked;
  document.getElementById('disp_'+ref).style.backgroundColor = '';
  while(true) {
	if (!document.getElementById(rowRef+cnt)) break;
	document.getElementById('chk_'+ref).checked = (checked) ? true : false;
	cnt++;
	ref++;
  }
  updateBalance();
}

function updateDetail(ref) {
  var numDetail  = 0;
  var numChecked = 0;
  var rowRef     = 'disp_'+ref+'_';
  var cnt        = 0;
  var origRef    = ref;
  while (true) {
	if (!document.getElementById(rowRef+cnt)) break;
	if (document.getElementById('chk_'+ref).checked) numChecked++;
	numDetail++;
	cnt++;
	ref++;
  }
  if (numChecked == 0) { // none checked
  	document.getElementById('disp_'+origRef).style.backgroundColor = '';
    document.getElementById('sum_'+origRef).checked = false;
  } else if (numChecked == numDetail) { // all checked
  	document.getElementById('disp_'+origRef).style.backgroundColor = '';
    document.getElementById('sum_'+origRef).checked = true;
  } else { // partial checked
  	document.getElementById('disp_'+origRef).style.backgroundColor = 'yellow';
    document.getElementById('sum_'+origRef).checked = true;
  }
  updateBalance();
}

function updateBalance() {
  var value;
  var start_balance = parseFloat(cleanCurrency(document.getElementById('start_balance').value));
  var open_checks   = 0;
  var open_deposits = 0;
  var gl_balance = parseFloat(cleanCurrency(document.getElementById('gl_balance').value));
  for (var i=0; i<totalCnt; i++) {
    if (!document.getElementById('chk_'+i).checked) {
	  value = parseFloat(document.getElementById('pmt_'+i).value);
	  if (value < 0) {
	    if (!isNaN(value)) open_checks -= value;
	  } else {
	    if (!isNaN(value)) open_deposits += value;
	  }
	}
  }
  var sb = new String(start_balance);
  document.getElementById('start_balance').value = formatCurrency(sb);
  var dt = new String(open_checks);
  document.getElementById('open_checks').value = formatCurrency(dt);
  var ct = new String(open_deposits);
  document.getElementById('open_deposits').value = formatCurrency(ct);

  var balance = start_balance - open_checks + open_deposits - gl_balance;
  var tot = new String(balance);
  document.getElementById('balance').value = formatCurrency(tot);
  var numExpr = Math.round(eval(balance) * Math.pow(10, decimal_places));
  if (numExpr == 0) {
  	document.getElementById('balance').style.color = '';
  } else {
  	document.getElementById('balance').style.color = 'red';
  }
}

// -->
</script>