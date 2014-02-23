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
//  Path: /modules/phreebooks/pages/bulk_bills/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var journalID = '<?php echo JOURNAL_ID; ?>';
<?php echo js_calendar_init($cal_bills0); ?>
<?php echo js_calendar_init($cal_bills1); ?>
<?php echo js_calendar_init($cal_bills2); ?>

function init() {
  checkShipAll();

<?php if ($post_success && $_REQUEST['action'] == 'print') { ?>
  var printWin = window.open("index.php?module=phreeform&page=popup_gen&gID=<?php echo POPUP_FORM_TYPE; ?>&date=a&xfld=journal_main.purchase_invoice_id&xcr=RANGE&xmin=<?php echo $print_crit['min']; ?>&xmax=<?php echo $print_crit['max']; ?>","reportFilter","width=700px,height=550px,resizable=1,scrollbars=1,top=150px,left=200px");
  printWin.focus();
<?php } ?>
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
function checkShipAll() {
  var amt_due;
  for (var i=1; i<document.getElementById("item_table").rows.length+1; i++) {
  	amt_due = parseFloat(document.getElementById('amt_'+i).value);
	if (document.getElementById('pay_'+i).disabled == false && amt_due > 0) {
	  document.getElementById('pay_'+i).checked = true;
	  bbUpdatePayValues(i);
	}
  }
}

function updateDiscTotal(rowCnt) {
	var discount_amount = cleanCurrency(document.getElementById('dscnt_'+rowCnt).value);
	document.getElementById('dscnt_'+rowCnt).value = formatCurrency(discount_amount);
	var pay_total = parseFloat(document.getElementById('amt_'+rowCnt).value) - discount_amount;
	var total_l = new String(pay_total);
	document.getElementById('total_'+rowCnt).value = formatCurrency(total_l);
	document.getElementById('pay_'+rowCnt).checked = true;
	updateTotalPrices();
}

function updateLineTotal(rowCnt) {
	var total_line = cleanCurrency(document.getElementById('total_'+rowCnt).value);
	document.getElementById('total_'+rowCnt).value = formatCurrency(total_line);
	document.getElementById('dscnt_'+rowCnt).value = formatCurrency('0');
	document.getElementById('pay_'+rowCnt).checked = true;
	updateTotalPrices();
}

function bbUpdatePayValues(rowCnt) {
	var postDate = cleanDate(document.getElementById('post_date').value);
	var discDate = document.getElementById('discdate_'+rowCnt).value;
	if (document.getElementById('pay_'+rowCnt).checked) {
		var amount = parseFloat(document.getElementById('amt_'+rowCnt).value);
		var discount = parseFloat(document.getElementById('origdisc_'+rowCnt).value);
		if (postDate > discDate) {
			discount = 0;
			document.getElementById('dscnt_'+rowCnt).value = formatCurrency('0');
		} else {
			document.getElementById('dscnt_'+rowCnt).value = formatCurrency(new String(discount));
		}
		var new_total = new String(amount - discount);
		document.getElementById('total_'+rowCnt).value = formatCurrency(new_total);
	} else {
		document.getElementById('dscnt_'+rowCnt).value = (postDate > discDate) ? formatCurrency('0') : document.getElementById('origdisc_'+rowCnt).value;
		document.getElementById('total_'+rowCnt).value = '';
	}
	updateTotalPrices();
}

// -->
</script>
<script type="text/javascript" src="modules/phreebooks/javascript/banking.js"></script>
