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
//  Path: /modules/contacts/pages/popup_terms/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
<?php echo js_calendar_init($cal_terms); ?>

function init() {
	SetDisabled();
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function changeOptions() {
	LoadDefaults();
	SetDisabled();
}

function LoadDefaults() {
	if (document.popup_terms.special_terms[0].checked) {
		document.getElementById('early_percent').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_PERCENT"); ?>';
		document.getElementById('early_days').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_DAYS"); ?>';
		document.getElementById('standard_days').value = '<?php echo constant($terms_type . "_NUM_DAYS_DUE"); ?>';
		document.popup_terms.due_date.value = '';
	} else if (document.popup_terms.special_terms[1].checked) {
		document.getElementById('early_percent').value = '';
		document.getElementById('early_days').value = '';
		document.getElementById('standard_days').value = '';
		document.popup_terms.due_date.value = '';
	} else if (document.popup_terms.special_terms[2].checked) {
		document.getElementById('early_percent').value = '';
		document.getElementById('early_days').value = '';
		document.getElementById('standard_days').value = '';
		document.popup_terms.due_date.value = '';
	} else if (document.popup_terms.special_terms[3].checked) {
		document.getElementById('early_percent').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_PERCENT"); ?>';
		document.getElementById('early_days').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_DAYS"); ?>';
		document.getElementById('standard_days').value = '<?php echo constant($terms_type . "_NUM_DAYS_DUE"); ?>';
		document.popup_terms.due_date.value = '';
	} else if (document.popup_terms.special_terms[4].checked) {
		document.getElementById('early_percent').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_PERCENT"); ?>';
		document.getElementById('early_days').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_DAYS"); ?>';
		document.getElementById('standard_days').value = '';
		document.popup_terms.due_date.value = '<?php echo $default_date; ?>';
	} else if (document.popup_terms.special_terms[5].checked) {
		document.getElementById('early_percent').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_PERCENT"); ?>';
		document.getElementById('early_days').value = '<?php echo constant($terms_type . "_PREPAYMENT_DISCOUNT_DAYS"); ?>';
		document.getElementById('standard_days').value = '';
		document.popup_terms.due_date.value = '<?php echo $month_end; ?>';
	}
}

function SetDisabled() {
	if (document.popup_terms.special_terms[0].checked) {
		document.getElementById('early_percent').disabled = true;
		document.getElementById('early_days').disabled = true;
		document.getElementById('standard_days').disabled = true;
		document.popup_terms.due_date.disabled = true;
	}
	if (document.popup_terms.special_terms[1].checked) {
		document.getElementById('early_percent').disabled = true;
		document.getElementById('early_days').disabled = true;
		document.getElementById('standard_days').disabled = true;
		document.popup_terms.due_date.disabled = true;
	}
	if (document.popup_terms.special_terms[2].checked) {
		document.getElementById('early_percent').disabled = true;
		document.getElementById('early_days').disabled = true;
		document.getElementById('standard_days').disabled = true;
		document.popup_terms.due_date.disabled = true;
	}
	if (document.popup_terms.special_terms[3].checked) {
		document.getElementById('early_percent').disabled = false;
		document.getElementById('early_days').disabled = false;
		document.getElementById('standard_days').disabled = false;
		document.popup_terms.due_date.disabled = true;
	}
	if (document.popup_terms.special_terms[4].checked) {
		document.getElementById('early_percent').disabled = false;
		document.getElementById('early_days').disabled = false;
		document.getElementById('standard_days').disabled = true;
		document.popup_terms.due_date.disabled = false;
	}
	if (document.popup_terms.special_terms[5].checked) {
		document.getElementById('early_percent').disabled = false;
		document.getElementById('early_days').disabled = false;
		document.getElementById('standard_days').disabled = true;
		document.popup_terms.due_date.disabled = true;
	}
}

function setReturnTerms() {

	var early_terms = ':' + document.getElementById('early_percent').value + ':' + document.getElementById('early_days').value
	var return_terms = '';
	if (document.popup_terms.special_terms[0].checked) {
		return_terms = '0';
		window.opener.document.getElementById('terms_text').value = js_terms_to_language('0');
		window.opener.document.getElementById('terms').value = 0;
	}
	if (document.popup_terms.special_terms[1].checked) {
		return_terms = '1';
		window.opener.document.getElementById('terms_text').value = js_terms_to_language('1');
		window.opener.document.getElementById('terms').value = 1;
	}
	if (document.popup_terms.special_terms[2].checked) {
		return_terms = '2';
		window.opener.document.getElementById('terms_text').value = js_terms_to_language('2');
		window.opener.document.getElementById('terms').value = 2;
	}
	if (document.popup_terms.special_terms[3].checked) {
		return_terms = '3' + early_terms + ':' + document.getElementById('standard_days').value + ':' + document.getElementById('credit_limit').value;
		window.opener.document.getElementById('terms_text').value = js_terms_to_language(return_terms);
		window.opener.document.getElementById('terms').value = return_terms;
	}
	if (document.popup_terms.special_terms[4].checked) {
		return_terms = '4' + early_terms + ':' + document.popup_terms.elements['due_date'].value + ':' + document.getElementById('credit_limit').value;
		window.opener.document.getElementById('terms_text').value = js_terms_to_language(return_terms);
		window.opener.document.getElementById('terms').value = return_terms;
	}
	if (document.popup_terms.special_terms[5].checked) {
		return_terms = '5' + early_terms + ':' + document.popup_terms.elements['due_date'].value + ':' + document.getElementById('credit_limit').value;
		window.opener.document.getElementById('terms_text').value = js_terms_to_language(return_terms);
		window.opener.document.getElementById('terms').value = return_terms;
	}
	self.close();
}

function js_terms_to_language(terms_encoded) { // modified from /includes/general/functions/gen_functions.php function: gen_terms_to_language
	var prepayment_discount_percent = '<?php echo constant($terms_type . '_PREPAYMENT_DISCOUNT_PERCENT'); ?>';
	var prepayment_discount_days = '<?php echo constant($terms_type . '_PREPAYMENT_DISCOUNT_DAYS'); ?>';
	var num_days_due = '<?php echo constant($terms_type . '_NUM_DAYS_DUE'); ?>';
	var terms = terms_encoded.split(':');
	var result = '';
	switch (terms[0]) {
		default:
		case '0': // Default terms
			if (prepayment_discount_percent != '0') {
				result =  prepayment_discount_percent + '<?php echo ACT_EARLY_DISCOUNT_SHORT; ?>' + prepayment_discount_days + ', ';
			}
			result += '<?php echo ACT_TERMS_NET; ?>' + num_days_due;
			break;
		case '1': // Cash on Delivery (COD)
			result = '<?php echo ACT_COD_SHORT; ?>';
			break;
		case '2': // Prepaid
			result = '<?php echo ACT_PREPAID; ?>';
			break;
		case '3': // Special terms
			if (terms[1] != 0) {
				result = terms[1] + '<?php echo ACT_EARLY_DISCOUNT_SHORT; ?>' + terms[2] + ', ';
			}
			result += '<?php echo ACT_TERMS_NET; ?>' + terms[3];
			break;
		case '4': // Due on day of next month
			if (terms[1] != 0) {
				result = terms[1] + '<?php echo ACT_EARLY_DISCOUNT_SHORT; ?>' + terms[2] + ', ';
			}
			result += '<?php echo ACT_DUE_ON; ?>' + terms[3];
			break;
		case '5': // Due at end of month
			if (terms[1] != 0) {
				result = terms[1] + '<?php echo ACT_EARLY_DISCOUNT_SHORT; ?>' + terms[2] + ', ';
			}
			result += '<?php echo ACT_END_OF_MONTH; ?>';
	}
	return result;
}

// -->
</script>