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
//  Path: /modules/contacts/pages/main/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var attachment_path = '<?php echo urlencode(CONTACTS_DIR_ATTACHMENTS); ?>';
var default_country = '<?php echo COMPANY_COUNTRY; ?>';
var account_type    = '<?php echo $type; ?>';

function init() {
<?php if ($include_template == 'template_main.php') {
 	echo '  document.getElementById("search_text").focus();'  . chr(10); 
  	echo '  document.getElementById("search_text").select();' . chr(10); 
  }
?>
  if (window.extra_init) { extra_init() } // hook for additional initializations
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";
  <?php if ($cInfo->auto_type == false && ($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'update' || $_REQUEST['action'] == 'new')) { ?> // if showing the edit/update detail form
  var acctId = document.getElementById('short_name').value;
  if (acctId == '') {
      error_message += "<?php echo ACT_JS_SHORT_NAME; ?>";
	  error = 1;
  }
  <?php } ?>
  if (error == 1) {
	$.messager.alert("Processing error",error_message,"error");
    return false;
  } else {
    return true;
  }
}

// Insert other page specific functions here.
function loadContacts() {
//  var guess = document.getElementById('dept_rep_id').value;
  var guess = document.getElementById('dept_rep_id').value;
//  document.getElementById('dept_rep_id').options[0].text = guess;
  if (guess.length < 3) return;
  $.ajax({
    type: "GET",
    url: 'index.php?module=contacts&page=ajax&op=load_contact_info&guess='+guess,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fillContacts
  });
}

// ajax response handler call back function
function fillContacts(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  while (document.getElementById('comboseldept_rep_id').options.length) document.getElementById('comboseldept_rep_id').remove(0);
  var iIndex = 0;
  $(xml).find("guesses").each(function() {
	newOpt = document.createElement("option");
	newOpt.text = $(this).find("guess").text() ? $(this).find("guess").text() : '<?php echo TEXT_FIND; ?>';
	document.getElementById('comboseldept_rep_id').options.add(newOpt);
	document.getElementById('comboseldept_rep_id').options[iIndex].value = $(this).find("id").text();
	if (!fActiveMenu) cbMmenuActivate('dept_rep_id', 'combodivdept_rep_id', 'comboseldept_rep_id', 'imgNamedept_rep_id');
	document.getElementById('dept_rep_id').focus();
	iIndex++;
  });
}

// -->
</script>
<script type="text/javascript" src="modules/contacts/javascript/contacts.js"></script>