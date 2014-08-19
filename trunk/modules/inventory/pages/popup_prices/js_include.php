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
//  Path: /modules/inventory/pages/popup_prices/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.

function init() {
  var currenciesCode = window.opener.document.getElementById('currencies_code').value;
  var currenciesValue = parseFloat(window.opener.document.getElementById('currencies_value').value);
  document.getElementById('display_currency').value = currenciesCode;
  var temp = cleanCurrency(document.getElementById('cost').value) * currenciesValue;
  document.getElementById('cost').value = formatPrecise(new String(temp), currenciesCode);
  temp = cleanCurrency(document.getElementById('full').value) * currenciesValue;
  document.getElementById('full').value = formatPrecise(new String(temp), currenciesCode);
  for (var i=0; i<parseInt(document.getElementById('num_prices').value); i++) {
  	temp = cleanCurrency(document.getElementById('price_'+i).value) * currenciesValue;
    document.getElementById('price_'+i).value = formatPrecise(new String(temp), currenciesCode);
  }
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function setReturnPrice(elementID, rowID) {
  amount = document.getElementById(rowID).value;
  window.opener.document.getElementById('price_'+elementID).value = amount;
  window.opener.updateRowTotal(elementID, false);
  self.close();
}

// -->
</script>