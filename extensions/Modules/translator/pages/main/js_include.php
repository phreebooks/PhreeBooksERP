<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/translator/pages/main/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass some php variables

// required function called with every page load
function init() {
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";
  if (error == 1) {
	alert(error_message);
	return false;
  } else {
	return true;
  }
}

function checkAllBoxes() {
  var chkbox = new Array();
  chkbox = document.getElementsByTagName('input');
  for (var i=0; i<chkbox.length; i++) {
    if (chkbox[i].type == 'checkbox') chkbox[i].checked = true;
  }
}

// -->
</script>
