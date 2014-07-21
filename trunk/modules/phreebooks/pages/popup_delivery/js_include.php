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
//  Path: /modules/phreebooks/pages/popup_delivery/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.

function init() {
<?php if ($_REQUEST['action'] == 'save') echo '  self.close();' ?>
}

function check_form() {
  return true;
}

// Insert other page specific functions here.

// -->
</script>
<?php 
$cal_gen = array();
echo '<script language="JavaScript">' . chr(10);
for ($i=0, $j=1; $i<$num_items; $i++, $j++) {
	$cal_gen[$j] = array(
	  'name'      => 'date_' . $j,
	  'form'      => 'popup_delivery',
	  'fieldname' => 'eta_date_' . $j,
	  'imagename' => 'btn_date_' . $j,
	  'default'   => '',
	  'params'    => array('align' => 'left'),
	);
	echo js_calendar_init($cal_gen[$j]);
}
echo '</script>' . chr(10);
?>
