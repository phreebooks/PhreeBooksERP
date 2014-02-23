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
//  Path: /modules/phreebooks/pages/popup_bar_code/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var skuLength     = <?php echo ORD_BAR_CODE_LENGTH; ?>;

// Set for standard UCC bar codes
var journal_id       = '<?php echo $jID; ?>';
var resClockID       = 0;

function init() {
  document.getElementById('upc').focus();
  refreshOrderClock();
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function refreshOrderClock() {
  if (resClockID) {
    clearTimeout(resClockID);
    resClockID = 0;
  }
  setReturnItem(false); // do something
  // reset the clock
  resClockID = setTimeout("refreshOrderClock()", 250);
}

// AJAX balance request function pair
function setReturnItem(override) { // request funtion
  var upc = document.getElementById('upc').value;
  if (!override && upc.length < skuLength) return; // not enough characters
  var qty = document.getElementById('qty').value;
  var id = window.opener.document.getElementById('bill_acct_id').value;
  document.getElementById('upc').value = '';
  document.getElementById('qty').value = '1';
  if (!qty) {
	$.messager.alert('error','The quantity cannot be less than or equal to zero!','error');
    return;
  }
  $.ajax({
    type: "GET",
    url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=upcDetails&cID='+id+'&qty='+qty+'&upc='+upc,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: processUpcDetails
  });
  document.getElementById('upc').focus();
}

function processUpcDetails(sXml) { // call back function
  var rowID = 1;
  var i     = 1;
  var xml   = parseXml(sXml);
  if (!xml) return;
  // retrieve the current empty row
  while (true) {
    if (!window.opener.document.getElementById('sku_'+i)) {
      window.opener.addInvRow();
	  break;
	}
    if (window.opener.document.getElementById('sku_'+i).value == '<?php echo TEXT_SEARCH; ?>') break;
	i++;
	if (i>1000) break; // failsafe
  }
  rowID = i;
  // fill in the data
  var exchange_rate = window.opener.document.getElementById('currencies_value').value;
  switch (journal_id) {
	case  '3':
    case  '4':
	case  '9':
	case '10':
      window.opener.document.getElementById('qty_'+rowID).value  = $(xml).find("qty").text();
	  break;
	default:
      window.opener.document.getElementById('pstd_'+rowID).value = $(xml).find("qty").text();
  }
  window.opener.document.getElementById('sku_'+rowID).value       = $(xml).find("sku").text();
  window.opener.document.getElementById('sku_'+rowID).style.color = '';
  window.opener.document.getElementById('desc_'+rowID).value      = $(xml).find("description_sales").text();
  window.opener.document.getElementById('full_'+rowID).value      = formatCurrency($(xml).find("full_price").text()  * exchange_rate);
  window.opener.document.getElementById('price_'+rowID).value     = formatCurrency($(xml).find("sales_price").text() * exchange_rate);
  window.opener.document.getElementById('acct_'+rowID).value      = $(xml).find("account_sales_income").text();
  window.opener.document.getElementById('weight_'+rowID).value    = $(xml).find("item_weight").text();
  window.opener.document.getElementById('stock_'+rowID).value     = $(xml).find("quantity_on_hand").text();
  window.opener.document.getElementById('tax_'+rowID).value       = $(xml).find("item_taxable").text();
  window.opener.document.getElementById('lead_'+rowID).value      = $(xml).find("lead_time").text();
  window.opener.document.getElementById('inactive_'+rowID).value  = $(xml).find("inactive").text();
  window.opener.updateRowTotal(rowID, true);
  window.opener.addInvRow();
}

// -->
</script>