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
//  Path: /modules/shipping/pages/popup_tracking/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
<?php echo js_calendar_init($cal_ship); ?>
<?php echo js_calendar_init($cal_del); ?>

// list the freight options
<?php echo $js_methods; ?>

function init() {
  <?php if ($close_popup) {
	echo '  window.opener.location.reload();' . chr(10);
	echo '  self.close();' . chr(10);
  } ?>
  buildFreightDropdown();
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function buildFreightDropdown() {
  // fetch the selection
  var selectedCarrier = document.getElementById('carrier').value;
  for (var i=0; i<freightCarriers.length; i++) {
	if (freightCarriers[i] == selectedCarrier) break;
  }
  var selectedMethod = document.getElementById('method').value;
  for (var j=0; j<freightLevels.length; j++) {
	if (freightLevels[j] == selectedMethod) break;
  }
  // erase the drop-down
  while (document.getElementById('method').options.length) document.getElementById('method').remove(0);
  // build the new one, first check to see if None was selected
  if (i == freightCarriers.length) return; // None was selected, leave drop-down empty
  var m = 0; // allows skip if method is not available
  for (var k=0; k<freightLevels.length; k++) {
	if (freightDetails[i][k] != '') {
	  var newOpt = document.createElement("option");
	  newOpt.text = freightDetails[i][k];
	  document.getElementById('method').options.add(newOpt);
	  document.getElementById('method').options[m].value = freightLevels[k];
	  m++;
	}
  }
  // set the default choice 
  document.getElementById('method').value = selectedMethod;
}


// -->
</script>