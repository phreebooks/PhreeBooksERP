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
echo $messageStack->output();
$column = 1;
?>
<div><a href="<?php echo html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=ctl_panel&amp;mID=' . $menu_id, 'SSL'); ?>"><?php echo CP_CHANGE_PROFILE; ?></a></div>
<table style="width:100%;margin-left:auto;margin-right:auto;">
  <tr>
  </tr>
  <tr>
    <td width="33%" valign="top">
      <div id="col_<?php echo $column; ?>" style="position:relative;">
<?php
while(!$cp_boxes->EOF) {
  if ($cp_boxes->fields['column_id'] <> $column) {
	while ($cp_boxes->fields['column_id'] <> $column) {
	  $column++;
	  echo '      </div>' . chr(10);
	  echo '    </td>' . chr(10);
	  echo '    <td width="33%" valign="top">' . chr(10);
	  echo '      <div id="col_' . $column . '" style="position:relative;">' . chr(10);
	}
  }
  $dashboard 	= $cp_boxes->fields['dashboard_id'];
  $module_id    = $cp_boxes->fields['module_id'];
  $column_id    = $cp_boxes->fields['column_id'];
  $row_id       = $cp_boxes->fields['row_id'];
  $params       = unserialize($cp_boxes->fields['params']);
  if (file_exists(DIR_FS_MODULES . "$module_id/dashboards/$dashboard/$dashboard.php")) {
    load_method_language(DIR_FS_MODULES . "$module_id/dashboards/$dashboard");
    require_once        (DIR_FS_MODULES . "$module_id/dashboards/$dashboard/$dashboard.php");
    $new_box               = new $dashboard;
    $new_box->menu_id      = $menu_id;
    $new_box->module_id    = $module_id;
    $new_box->column_id    = $cp_boxes->fields['column_id'];
    $new_box->row_id       = $cp_boxes->fields['row_id'];
    echo $new_box->Output($params);
  }
  $cp_boxes->MoveNext();
}

while (MAX_CP_COLUMNS <> $column) { // fill remaining columns with blank space
  $column++;
  echo '      </div>' . chr(10);
  echo '    </td>' . chr(10);
  echo '    <td width="33%" valign="top">' . chr(10);
  echo '      <div id="col_' . $column . '" style="position:relative;">' . chr(10);
}
?>
      </div>
    </td>
  </tr>
</table>
