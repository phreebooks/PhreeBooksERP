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
//  Path: /modules/assets/pages/admin/js_include.php
//
?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
function init() {
  $(function() { // initialize tables
    $('#tab_table').dataTable();
    $('#field_table').dataTable();
  });
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function loadPopUp(action, id) {
  switch(action) {
    case 'fields_new':    action = 'new';    subject = 'fields'; break;
    case 'tabs_new':      action = 'new';    subject = 'tabs';   break;
    case 'fields_edit':   action = 'edit';   subject = 'fields'; break;
    case 'tabs_edit':     action = 'edit';   subject = 'tabs';   break;
    case 'fields_delete': action = 'delete'; subject = 'fields'; break;
    case 'tabs_delete':   action = 'delete'; subject = 'tabs';   break;
  }
  window.open("index.php?module=phreedom&page=popup_setup&topic="+module+"&subject="+subject+"&action="+action+"&sID="+id,"popup_setup","width=500,height=550,resizable=1,scrollbars=1,top=150,left=200");
}

function subjectDelete(subject, id) {
  document.getElementById('subject').value = subject;
  submitSeq(id, 'delete');
}

// -->
</script>