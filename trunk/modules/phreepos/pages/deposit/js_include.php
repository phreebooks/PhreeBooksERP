<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/phreepos/pages/deposit/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var pmt_array          = new Array(); // holds the encrypted payment information
var add_array          = new Array("<?php echo implode('", "', $js_arrays['fields']); ?>");
var default_array      = new Array("<?php echo implode('", "', $js_arrays['text']); ?>");
var journalID          = '<?php echo JOURNAL_ID; ?>';
var account_type       = '<?php echo $type; ?>';
var payments_installed = <?php echo count($payment_modules) ? 'true' : 'false'; ?>;
<?php echo js_calendar_init($cal_bills); ?>

// List the gl accounts for line item pull downs
<?php echo $js_gl_array; ?>

function init() {
	document.getElementById('bill_to_select').style.visibility = 'hidden';
	activateFields();
	// change color of the bill and ship address fields if they are the default values
	var add_id;
	for (var i=0; i<add_array.length; i++) {
		add_id = add_array[i];
		if (document.getElementById('bill_'+add_id).value == default_array[i]) {
			document.getElementById('bill_'+add_id).style.color = inactive_text_color;
		}
	}
	document.getElementById('search').focus();
<?php 
  if ($_REQUEST['action'] == 'pmt') echo '  loadNewPayment();' . chr(10);
  echo '  updateDepositPrice();' . chr(10);
?>

<?php if ($post_success && $_REQUEST['action'] == 'print') { ?>
  ClearForm();
  var printWin = window.open("index.php?module=phreeform&page=popup_gen&gID=<?php echo POPUP_FORM_TYPE; ?>&date=a&xfld=journal_main.id&xcr=EQUAL&xmin=<?php echo $oID; ?>","popup_gen","width=700px,height=550px,resizable=1,scrollbars=1,top=150px,left=200px");
  printWin.focus();
<?php } ?>
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var todo = document.getElementById('action').value;

  if (journalID == '19' && (todo == 'save' || todo == 'print')) { // only check payment if saving
    var index = document.getElementById('shipper_code').selectedIndex;
    var payment_method = document.getElementById('shipper_code').options[index].value;
	<?php
	  foreach ($payment_modules as $pmt_class) { // fetch the javascript validation of payments module
		$value = $pmt_class['id'];
		echo $$value->javascript_validation();
	  }
	?>
  }

  if (error == 1) {
    alert(error_message);
    return false;
  }
  return true;
}

// Insert other page specific functions here.
function activateFields() {
  if (payments_installed) {
    var index = document.getElementById('shipper_code').selectedIndex;
    for (var i=0; i<document.getElementById('shipper_code').options.length; i++) {
  	  document.getElementById('pm_'+i).style.visibility = 'hidden';
    }
    document.getElementById('pm_'+index).style.visibility = '';
  }
}

function ClearForm() {
  var add_id;
  document.getElementById('id').value                     = '';
  document.getElementById('bill_acct_id').value           = '';
  document.getElementById('bill_address_id').value        = '';
  document.getElementById('bill_telephone1').value        = '';
  document.getElementById('search').value                 = text_search;
  document.getElementById('purchase_invoice_id').value    = '<?php echo $next_inv_ref; ?>';
  document.getElementById('post_date').value              = '<?php echo date(DATE_FORMAT); ?>';
  document.getElementById('purch_order_id').value         = '';
  document.getElementById('gl_acct_id').value             = '<?php echo $gl_acct_id; ?>';
  document.getElementById('gl_disc_acct_id').value        = '';
  document.getElementById('total').value                  = '';
  document.getElementById('shipper_code').value           = '';
  // some special initialization
  document.getElementById('search').style.color           = inactive_text_color;
  document.getElementById('purchase_invoice_id').readOnly = false;
  document.getElementById('bill_country_code').value      = '<?php echo STORE_COUNTRY; ?>';
  for (var i=0; i<add_array.length; i++) {
	add_id = add_array[i];
	if (add_id != 'country_code') document.getElementById('bill_'+add_id).style.color = inactive_text_color;
	document.getElementById('bill_'+add_id).value = default_array[i];
  }
  while (document.getElementById('bill_to_select').options.length) {
	document.getElementById('bill_to_select').remove(0);
  }
  while (document.getElementById('payment_id').options.length) {
	document.getElementById('payment_id').remove(0);
  }
  while (document.getElementById("item_table").rows.length > 1) {
	document.getElementById("item_table").deleteRow(-1); 
  }
  document.getElementById('acct_1').value = '<?php echo DEF_DEP_GL_ACCT; ?>';
  loadNewBalance('<?php echo $gl_acct_id; ?>');
}

function billsAcctList() {
	window.open("index.php?module=contacts&page=popup_accts&type="+account_type+"&form=bills_deposit&fill=bill&jID="+journalID+"&search_text="+document.getElementById('search').value,"accounts","width=850px,height=550px,resizable=1,scrollbars=1,top=150,left=100");
}

// ******* AJAX balance request function pair *********/
function loadNewBalance() { // request funtion
  var gl_acct   = document.getElementById('gl_acct_id').value;
  var post_date = document.getElementById('post_date').value;
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreebooks&page=ajax&op=acct_balance&gl_acct_id='+gl_acct+'&post_date='+post_date,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
    },
	success: showNewBalance
  });
}

function showNewBalance(sXml) { // call back function
  var xml = parseXml(sXml);
  if (!xml) return;
  var value = $(xml).find("value").text();
  if (isNaN(value) || !value) value = '0';
  var start_balance = parseFloat(value);
  var total_checks  = cleanCurrency(document.getElementById('total').value);
  balance_remain    = new String(start_balance - total_checks);
  sb = new String(start_balance);
  document.getElementById('acct_balance').value = formatCurrency(sb);
  document.getElementById('end_balance').value  = formatCurrency(balance_remain);
}

function ajaxOrderData(cID, oID, jID, open_order, ship_only) {
  $.ajax({
    type: "GET",
    url: 'index.php?module=contacts&page=ajax&op=load_contact&cID='+cID,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
    },
	success: fillOrderData
  });
}

function fillOrderData(sXml) { // edit response form fill 
  var newOpt;
  var type = 'bill';
  var xml = parseXml(sXml);
  if (!xml) return;
  while (document.getElementById(type+'_to_select').options.length) document.getElementById(type+'_to_select').remove(0);
  var id = $(xml).find("id").first().text();
  if (!id) return;
  var mainType = $(xml).find("type").first().text() + 'm';
  bill_add     = xml;
  insertValue('bill_acct_id', id);
  insertValue('search', $(xml).find("short_name").text());
  //now fill the addresses
  var iIndex = 0;
  $(xml).find("BillAddress").each(function() {
    newOpt = document.createElement("option");
	newOpt.text = $(this).find("primary_name").text() + ', ' + $(this).find("city_town").text() + ', ' + $(this).find("postal_code").text();
	document.getElementById(type+'_to_select').options.add(newOpt);
	document.getElementById(type+'_to_select').options[iIndex].value = $(this).find("address_id").text();
    if ($(this).find("type").text() == mainType) { // also fill the fields
	  insertValue(type+'_address_id', $(this).find("address_id").text());
	  $(this).children().each (function() {
		var tagName = this.tagName;
		if (document.getElementById(type+'_'+tagName)) {
		    document.getElementById(type+'_'+tagName).value = $(this).text();
		    document.getElementById(type+'_'+tagName).style.color = '';
		}
	  });
	}
	iIndex++;
    // add a option for creating a new address
    newOpt = document.createElement("option");
    newOpt.text = '<?php echo TEXT_ENTER_NEW; ?>';
    document.getElementById(type+'_to_select').options.add(newOpt);	
    document.getElementById(type+'_to_select').options[iIndex].value = '0';
    document.getElementById(type+'_to_select').style.visibility      = 'visible';
    document.getElementById(type+'_to_select').disabled              = false;
  });
}

// ******* AJAX payment stored payment values request function pair *********/
function loadNewPayment() { // request funtion
  var contact_id = document.getElementById('bill_acct_id').value;
  if (!contact_id) return;
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreebooks&page=ajax&op=stored_payments&contact_id='+contact_id,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
    },
	success: showNewPayment
  });
}

function showNewPayment(sXml) { // call back function
  var show = false;
  var xml  = parseXml(sXml);
  if (!xml) return;
  while (document.getElementById('payment_id').options.length) document.getElementById('payment_id').remove(0);
  if ($(xml).find("payments").length) { // build the dropdown
    newOpt = document.createElement("option");
    newOpt.text = '<?php echo TEXT_ENTER_NEW; ?>';
    document.getElementById('payment_id').options.add(newOpt);	
    document.getElementById('payment_id').options[0].value = '';
    pmt_array[0] = new Object();
    pmt_array[0].field_0 = '';
    pmt_array[0].field_1 = '';
    pmt_array[0].field_2 = '';
    pmt_array[0].field_3 = '';
    pmt_array[0].field_4 = '';
    var j = 1;
	$(xml).find("payments").each(function() {
	  show = true;
	  newOpt = document.createElement("option");
	  newOpt.text = $(this).find("name").text();
	  if ($(this).find("hint").text()) newOpt.text += ', ' + $(this).find("hint").text();
	  document.getElementById('payment_id').options.add(newOpt);
	  document.getElementById('payment_id').options[j].value = $(this).find("id").text();
	  pmt_array[j] = new Object();
	  if ($(this).find("field_0").text()) pmt_array[j].field_0 = $(this).find("field_0").text();
	  if ($(this).find("field_1").text()) pmt_array[j].field_1 = $(this).find("field_1").text();
	  if ($(this).find("field_2").text()) pmt_array[j].field_2 = $(this).find("field_2").text();
	  if ($(this).find("field_3").text()) pmt_array[j].field_3 = $(this).find("field_3").text();
	  if ($(this).find("field_4").text()) pmt_array[j].field_4 = $(this).find("field_4").text();
	  j++;
    });
  }
  document.getElementById('payment_id').style.visibility = show ? '' : 'hidden';
}
// ******* END - AJAX payment stored payment values request function pair *********/

function fillPayment() {
  var index = document.getElementById('shipper_code').selectedIndex;
  var pmtMethod = document.getElementById('shipper_code').options[index].value;
  var pmtIndex = document.getElementById('payment_id').selectedIndex;
  if (document.getElementById(pmtMethod+'_field_0')) 
    document.getElementById(pmtMethod+'_field_0').value = pmt_array[pmtIndex].field_0;
  if (document.getElementById(pmtMethod+'_field_1')) 
    document.getElementById(pmtMethod+'_field_1').value = pmt_array[pmtIndex].field_1;
  if (document.getElementById(pmtMethod+'_field_2')) 
    document.getElementById(pmtMethod+'_field_2').value = pmt_array[pmtIndex].field_2;
  if (document.getElementById(pmtMethod+'_field_3')) 
    document.getElementById(pmtMethod+'_field_3').value = pmt_array[pmtIndex].field_3;
  if (document.getElementById(pmtMethod+'_field_4')) 
    document.getElementById(pmtMethod+'_field_4').value = pmt_array[pmtIndex].field_4;
}

function updateDepositPrice() {
  var temp  = cleanCurrency(document.getElementById('total_1').value);
  var total = parseFloat(temp);
  var tot = new String(total);
  document.getElementById('total').value   = formatCurrency(tot);
  document.getElementById('total_1').value = formatCurrency(tot);
  temp  = cleanCurrency(document.getElementById('acct_balance').value);
  var bank_bal = parseFloat(temp);
  tot = new String(bank_bal - total);
  document.getElementById('end_balance').value = formatCurrency(tot);
}

function buildFreightDropdown() {}

// -->
</script>
