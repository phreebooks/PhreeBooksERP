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
//  Path: /modules/amazon/pages/amazon/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
<?php echo js_calendar_init($cal_pps); ?>

function init() {
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function loadPopUp(subject, action, id) {
  window.open("index.php?module=shipping&page=popup_tracking&subject="+subject+"&action="+action+"&sID="+id,"popup_tracking","width=500,height=350,resizable=1,scrollbars=1,top=150,left=200");
}

function changePageResults() {
  var index = document.getElementById('pull_down_max').selectedIndex;
  var maxList = document.getElementById('pull_down_max').options[index].value;
  location.href = 'index.php?module=pps&page=amazon&pull_down_max='+maxList;
}

function printSalesOrder(id) {
  var printWin = window.open("index.php?module=phreeform&page=popup_form&gn=<?php echo SO_POPUP_FORM_TYPE; ?>&mID="+id+"&cr0=<?php echo TABLE_JOURNAL_MAIN; ?>.id:"+id,"forms","width=700px,height=550px,resizable=1,scrollbars=1,top=150px,left=200px");
  printWin.focus();
}

function printOrder(id) {
  var printWin = window.open("index.php?module=phreeform&page=popup_form&gn=<?php echo POPUP_FORM_TYPE; ?>&mID="+id+"&cr0=<?php echo TABLE_JOURNAL_MAIN; ?>.id:"+id,"forms","width=700px,height=550px,resizable=1,scrollbars=1,top=150px,left=200px");
  printWin.focus();
}

// -->
</script>