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
//  Path: /modules/phreedom/pages/admin/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
<?php echo js_calendar_init($cal_clean); ?>

function init() {
	$(function() {
		$('#admintabs').tabs();
		$('#currency_table').dataTable( dataTables_i18n );
	});
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function loadPopUp(action, id) {
  switch(action) {
    case 'countries_new':    action = 'new';    subject = 'countries'; break;
    case 'currency_new':     action = 'new';    subject = 'currency';  break;
    case 'zones_new':        action = 'new';    subject = 'zones';     break;
    case 'countries_edit':   action = 'edit';   subject = 'countries'; break;
    case 'currency_edit':    action = 'edit';   subject = 'currency';  break;
    case 'zones_edit':       action = 'edit';   subject = 'zones';     break;
//    case 'countries_delete': action = 'delete'; subject = 'countries'; break;
//    case 'currency_delete':  action = 'delete'; subject = 'currency';  break;
//    case 'zones_delete':     action = 'delete'; subject = 'zones';     break;
  }
  window.open("index.php?module=phreedom&page=popup_setup&topic="+module+"&subject="+subject+"&action="+action+"&sID="+id,"popup_setup","width=500,height=550,resizable=1,scrollbars=1,top=150,left=200");
}

function subjectDelete(subject, id) {
  document.getElementById('subject').value = subject;
  submitSeq(id, 'delete');
}

// -->
</script>