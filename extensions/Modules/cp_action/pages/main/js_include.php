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
//  Path: /modules/cp_action/pages/main/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass some php variables
<?php 
echo js_calendar_init($cal_date0);
echo js_calendar_init($cal_date1);
echo js_calendar_init($cal_date2);
echo js_calendar_init($cal_date3);
echo js_calendar_init($cal_date4);
echo js_calendar_init($cal_date5);
echo js_calendar_init($cal_date6);
echo js_calendar_init($cal_date7);
echo js_calendar_init($cal_date8);
echo js_calendar_init($cal_date9);
?>

// required function called with every page load
function init() {
  <?php if ($_REQUEST['action'] <> 'new' && $_REQUEST['action'] <> 'edit') { // set focus for main window
	echo "  document.getElementById('search_text').focus();" . chr(10);
	echo "  document.getElementById('search_text').select();" . chr(10);
  } ?>

}

function check_form() {
  return true;
}

function deleteItem(id) {
  location.href = 'index.php?module=cp_action&page=main&action=delete&cID='+id;
}

// -->
</script>