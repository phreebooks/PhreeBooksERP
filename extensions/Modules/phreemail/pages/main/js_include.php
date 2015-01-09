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
//  Path: /modules/phreemail/pages/main/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass some php variables
<?php if ($_REQUEST['action'] == 'edit') {
echo js_calendar_init($cal_date1);
echo js_calendar_init($cal_date2);
echo js_calendar_init($cal_date3);
} ?>

// required function called with every page load
function init() {

}

function check_form() {

	return true;
}

// -->
</script>