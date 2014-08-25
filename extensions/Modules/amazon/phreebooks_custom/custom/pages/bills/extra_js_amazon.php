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
//  Path: /modules/phreebooks/custom/pages/bills/extra_js.php
//

// start the extra javascript
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.

// ****** BOF - ajax pair for filling in amazon payments ****************
function amazonFillRequest() {
  var fn = prompt('Enter the Filename to fill receipt window with.', '');
  if (!fn) return;
    $.ajax({
      type: "GET",
	  url: 'index.php?module=pps&page=ajax&op=amazon_receipt&fn=' + fn,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
      },
	  success: amazonFillResponse
    });
}

function amazonFillResponse(sXml) {
  var msg = '';
  var xml = parseXml(sXml);
  if (!xml) return;
  $(xml).find("Order").each(function() {
	var orderID = $(this).find("Number").text();
	found = false;
	for (var r=1; r<document.getElementById('item_table').rows.length; r++) {
	  if (document.getElementById('desc_'+r).value == orderID) {
		document.getElementById('dscnt_'+r).value = parseFloat($(this).find("Discount").text());
		updateRowTotal(r);
		found = true;
		break;
	  }
	}
	if (!found) msg += 'could not find order # '+orderID+'\n';
  });
  alert(msg+'Finished Processing! Any listed orders could not be found.');
}
// ****** EOF - ajax pair for filling in amazon payments ****************

// -->
</script>