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
//  Path: /modules/phreedom/pages/main/template_main.php
//
// display alerts/error messages, if any since the toolbar is not shown
if(is_object($messageStack)) echo $messageStack->output();
$current_column = 1;
$row_started = true;
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('dashboard_id', '') . chr(10);
?>
<div><a href="<?php echo html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=ctl_panel&amp;mID=' . $basis->cInfo->menu_id, 'SSL'); ?>"><?php echo TEXT_ADD_DASHBOARD_ITEMS_TO_THIS_PAGE; ?></a></div>
<table style="width:100%;margin-left:auto;margin-right:auto;">
  <tr>
  </tr>
  <tr>
    <td width="33%" valign="top">
      <div id="col_<?php echo $current_column; ?>" style="position:relative;">
<?php
foreach ($basis->cInfo->cp_boxes as $key => $box) {
	$box->row_started = false;
	if($box->column_id <> $current_column) {
		$box->row_started = true;
		while ($box->column_id <> $current_column) {
			$current_column++;
			echo '      </div>' . chr(10);
			echo '    </td>' . chr(10);
			echo '    <td width="33%" valign="top">' . chr(10);
			echo "      <div id='col_{$current_column}' style='position:relative;'>" . chr(10);
		}
	}
 	echo $box->output();
}

while (MAX_CP_COLUMNS <> $current_column) { // fill remaining columns with blank space
  	$current_column++;
  	echo '      </div>' . chr(10);
  	echo '    </td>' . chr(10);
  	echo '    <td width="33%" valign="top">' . chr(10);
  	echo "      <div id='col_{$current_column}' style='position:relative;'>" . chr(10);
}
?>
      </div>
    </td>
  </tr>
</table>
