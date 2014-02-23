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
//  Path: /modules/phreepos/pages/closing/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var textShow = '<?php echo TEXT_DETAILS; ?>';
var textHide = '<?php echo TEXT_HIDE; ?>';
var totalCnt = <?php echo sizeof($bank_list); ?>;
var currencyCnt = <?php echo sizeof($currencies->currencies); ?>;
<?php echo js_calendar_init($cal_gl); ?>

function init() {
	<?php if (!empty($combined_list) ) echo 'updateBalance();';?>
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

function show(row){
	if (document.getElementById('curr_'+row).style.display == 'none') {
    	document.getElementById('curr_'+row).style.display = '';
	} else {
		document.getElementById('curr_'+row).style.display = 'none';
	}
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
  var first_cnt = 0;
  var total     = 0;
  var rowRef = 'disp_'+ref+'_';
  //var secRef = ref;
  //var startRef = ref;
  var start_balance = parseFloat(cleanCurrency(document.getElementById('samt_'+ref).value));
  document.getElementById('disp_'+ref).style.backgroundColor = '';
  while(true) {
	if (!document.getElementById(rowRef + first_cnt)) break;
	first_cnt++;
	//ref++;
  }
  var amount = new String(start_balance / first_cnt);
  var intAmount = start_balance / first_cnt;
  var balance = new String(start_balance);
  document.getElementById('samt_'+ref).value = formatCurrency(balance);
  var sec_cnt   = first_cnt;
  while(true) {
	//if (!document.getElementById(rowRef + sec_cnt)) break;
	if( first_cnt == 1){
		amount = new String( start_balance - total);
		document.getElementById('amt_'+ref).value = formatCurrency(amount);
		break;
	}
	document.getElementById('amt_'+ref).value = formatCurrency(amount);
	total = total + parseFloat(cleanCurrency(document.getElementById('amt_'+ref).value));
	first_cnt--;
	ref++;
  }
  updateBalance();
}

function updateDetail(ref) {
  var balance    = 0;
  var rowRef     = 'disp_'+ref+'_';
  var cnt        = 0;
  var origRef    = ref;
  while(true) {
	if (!document.getElementById(rowRef+cnt)) break;
	balance = balance + parseFloat(cleanCurrency(document.getElementById('amt_'+ref).value));
	var temp = new String(parseFloat(cleanCurrency(document.getElementById('amt_'+ref).value)));
	document.getElementById('amt_'+ref).value = formatCurrency(temp);
	cnt++;
	ref++;
  }
  var new_balance = new String(balance);
  document.getElementById('samt_'+origRef).value = formatCurrency(new_balance);
  updateBalance();
}

function updateBalance() {
	  var value;
	  var new_balance = 0;	  
	  var open_checks   = 0;
	  var open_deposits = 0;
	  for (var i=0; i<currencyCnt; i++) {
		if (!isNaN(parseFloat(document.getElementById('new_balance_'+i).value))){
		  	new_balance += parseFloat(cleanCurrency(document.getElementById('new_balance_'+i).value)) * parseFloat(document.getElementById('currencies_value_'+i).value);
	  	}
		var nb = new String(parseFloat(cleanCurrency(document.getElementById('new_balance_'+i).value)));
		document.getElementById('new_balance_'+i).value = formatCurrency(nb);
	  }
	  var till_balance = parseFloat(cleanCurrency(document.getElementById('till_balance').value));
	  for (var i=0; i<totalCnt; i++) {
		  if (!isNaN(parseFloat(document.getElementById('pmt_'+i).value))) open_checks   += parseFloat(cleanCurrency(document.getElementById('pmt_'+i).value));
		  if (!isNaN(parseFloat(document.getElementById('amt_'+i).value))) open_deposits += parseFloat(cleanCurrency(document.getElementById('amt_'+i).value));
		  var temp = new String(parseFloat(cleanCurrency(document.getElementById('amt_'+i).value)));
		  document.getElementById('amt_'+i).value = formatCurrency(temp);
	  }
	  var sb = new String(new_balance);
	  document.getElementById('new_balance').value = formatCurrency(sb);
	  var dt = new String(open_checks);
	  document.getElementById('open_checks').value = formatCurrency(dt);
	  var ct = new String(open_deposits);
	  document.getElementById('open_deposits').value = formatCurrency(ct);

	  var balance = (open_deposits - open_checks) + (new_balance - till_balance) ;
	  var tot = new String(balance);
	  document.getElementById('balance').value = formatCurrency(tot);
	  var numExpr = Math.round(eval(balance) * Math.pow(10, decimal_places));
	  if (numExpr == 0) {
	  	document.getElementById('balance').style.color = '';
	  } else {
	  	document.getElementById('balance').style.color = 'red';
	  }
	}

function updateCurr(currency,row){
	var new_balance = 0;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_001').value))) new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_001').value)) * 0.01;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_002').value))) new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_002').value)) * 0.02;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_005').value))) new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_005').value)) * 0.05;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_01').value)))  new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_01').value))  * 0.1;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_02').value)))  new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_02').value))  * 0.2;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_05').value)))  new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_05').value))  * 0.5;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_1').value)))   new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_1').value))   * 1;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_2').value)))   new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_2').value))   * 2;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_5').value)))   new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_5').value))   * 5;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_10').value)))  new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_10').value))  * 10;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_20').value)))  new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_20').value))  * 20;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_50').value)))  new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_50').value))  * 50;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_100').value))) new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_100').value)) * 100;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_200').value))) new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_200').value)) * 200;
	if (!isNaN(parseFloat(document.getElementById('t_'+currency+'_500').value))) new_balance = new_balance + parseFloat(cleanCurrency(document.getElementById('t_'+currency+'_500').value)) * 500;
	var nb = new String(new_balance);
	document.getElementById('new_balance_'+row).value = formatCurrency(nb);
	updateBalance();
}
// -->
</script>