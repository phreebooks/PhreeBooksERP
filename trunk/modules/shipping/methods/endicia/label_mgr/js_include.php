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
//  Path: /modules/shipping/methods/endicia/label_mgr/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var method = '<?php echo $shipping_module; ?>';
<?php echo js_calendar_init($cal_ship); ?>

function init() {
  <?php 
    if (!$error && !$auto_print && ($_REQUEST['action'] == 'label' || $_REQUEST['action'] == 'delete')) {
	  echo '  window.opener.location.reload();' . chr(10);
	  echo '  self.close();' . chr(10);
    } 
    if (!$auto_print) echo '  document.getElementById("wt_1").focus();' . chr(10);
  ?>
  validateAddress();
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";
  if (!document.getElementById('wt_1').value) {
  	error_message += '<?php echo SHIPPING_ERROR_WEIGHT_ZERO; ?>' + '\n';
	error = 1;
  }
  if (error == 1) {
    alert(error_message);
    return false;
  }
  return true;
}

// Insert other page specific functions here.
function validateAddress() {
  var address;
  if (!document.getElementById('ship_primary_name')) return;
  if (!document.getElementById('ship_primary_name').value) return; // form is blank, don't validate
  if (document.getElementById('ship_country_code').value != 'USA') return; // US addresses only
  address  = '&primary_name='  +document.getElementById('ship_primary_name').value;
  address += '&contact='       +document.getElementById('ship_contact').value;
  address += '&address1='      +document.getElementById('ship_address1').value;
  address += '&address2='      +document.getElementById('ship_address2').value;
  address += '&city_town='     +document.getElementById('ship_city_town').value;
  address += '&state_province='+document.getElementById('ship_state_province').value;
  address += '&postal_code='   +document.getElementById('ship_postal_code').value;
  address += '&country_code='  +document.getElementById('ship_country_code').value;
  $.ajax({
    type: "GET",
    url: 'index.php?module=shipping&page=ajax&op=shipping&action=validate&method='+method+address,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert ("Ajax Error: "+XMLHttpRequest.responseText+"\nTextStatus: "+textStatus+"\nErrorThrown: "+errorThrown);
    },
	success: fillValidate
  });
}

function fillValidate(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("message").text()) alert ($(xml).find("message").text());
  if ($(xml).find("address").text()) {
    document.getElementById('ship_contact').value        = $(xml).find("ship_contact").text();
    document.getElementById('ship_address1').value       = $(xml).find("ship_address1").text();
    document.getElementById('ship_address2').value       = $(xml).find("ship_address2").text();
    document.getElementById('ship_city_town').value      = $(xml).find("ship_city_town").text();
    document.getElementById('ship_state_province').value = $(xml).find("ship_state_province").text();
    document.getElementById('ship_postal_code').value    = $(xml).find("ship_postal_code").text();
  }
}

function paperPrint() {
  window.location = "<?php echo html_href_link(FILENAME_DEFAULT, 'module=shipping&page=popup_label_viewer&method='.$shipping_module.'&date='.$date.'&labels='.implode(':',$pdf_list), 'SSL'); ?>";
}

// java label printing
var therm_label = '<?php echo $label_data; ?>';
function labelPrint() {
	var tmp = '';
	alert('looking for printer');
	qz.findPrinter('<?php echo MODULE_SHIPPING_FEDEX_V7_PRINTER_NAME; ?>');
	alert('found printer');
	qz.append64(therm_label);
	alert('finished appending');
	qz.print();
	alert('finished printing');
	tmp += qz.getQueueInfo();
	jzebraDonePrinting(qz);
<?php
	if (sizeof($pdf_list) > 0) {
		echo "	paperPrint();\n";
	} else {
		echo "	window.opener.location.reload();\n";
		echo "	window.close();\n";
	} 
?>
}

function jzebraDonePrinting(qz) {
	if (!qz.getException()) {
		if (!qz.getPrinter()) alert('Please detect a printer first!');
	} else {
		alert('Applet returned error: '+qz.getException().getLocalizedMessage());
	}
}

// -->
</script>