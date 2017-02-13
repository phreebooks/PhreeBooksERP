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
//  Path: /modules/shipping/pages/popup_shipping/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var action   = '<?php echo strip_alphanumeric($_REQUEST['action']); ?>'; 
<?php echo js_calendar_init($cal_ship); ?>

function init() {
	switch (action) {
	  case 'rates':
	    break;
	  default:
		if (window.opener.document.getElementById('ship_city_town').value != '<?php echo GEN_CITY_TOWN ?>') {
			document.step1.ship_to_city.value = window.opener.document.getElementById('ship_city_town').value;
		}
		if (window.opener.document.getElementById('ship_state_province').value != '<?php echo GEN_STATE_PROVINCE ?>') {
			document.step1.ship_to_state.value = window.opener.document.getElementById('ship_state_province').value;
		}
		if (window.opener.document.getElementById('ship_postal_code').value != '<?php echo GEN_POSTAL_CODE ?>') {
			document.step1.ship_to_postal_code.value = window.opener.document.getElementById('ship_postal_code').value;
		}
		var ship_to_country = window.opener.document.getElementById('ship_country_code').value;
		if (!ship_to_country) ship_to_country = '<?php echo COMPANY_COUNTRY; ?>'
		document.getElementById('ship_to_country_code').value = ship_to_country;
		document.getElementById('pkg_item_count').value = window.opener.document.getElementById('item_count').value;
		document.getElementById('pkg_weight').value     = window.opener.document.getElementById('weight').value;
		document.getElementById('pkg_total').value      = window.opener.document.getElementById('total').value;
		if (document.getElementById('cod_amount')) {
			document.getElementById('cod_amount').value = window.opener.document.getElementById('total').value;
		}
	}
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function setReturnRate(rate, method, service) {
	window.opener.document.getElementById('freight').value      = rate;
	window.opener.document.getElementById('ship_carrier').value = method;
	window.opener.buildFreightDropdown();
	window.opener.document.getElementById('ship_service').value = service;
	window.opener.updateTotalPrices();
	self.close();
}

// -->
</script>