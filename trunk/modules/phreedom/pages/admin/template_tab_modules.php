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
//  Path: /modules/phreedom/pages/admin/template_tab_modules.php
//
?>
<div title="<?php echo MENU_HEADING_MODULES;?>" id="tab_modules">
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="5"><?php echo TEXT_AVAILABLE_MODULES; ?></th></tr>
  <tr>
    <th align="center"><?php echo TEXT_MODULE; ?></th>
    <th align="center"><?php echo TEXT_DESCRIPTION; ?></th>
    <th align="center"><?php echo TEXT_VERSION; ?></th>
    <th align="center" colspan="2"><?php echo TEXT_ACTION; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
if (is_array($admin_classes)) foreach ($admin_classes as $key => $class) {
	if ($key == 'phreedom') continue;
  echo '  <tr>';
  echo '    <td' . ($class->installed ? ' class="ui-state-active"' : '') . '>' . $class->text . '</td>';
  echo '    <td>' . $class->description . '</td>';
  if (!$class->installed && $security_level > 1) {
    echo '    <td>&nbsp;</td>';
	echo '    <td align="center">' . html_button_field('btn_' . $class->id, TEXT_INSTALL, 'onclick="submitToDo(\'install_' . $class->id . '\')"') . '</td>' . chr(10);
  } else {
    echo '    <td align="center">' . $class->status . '</td>';
	echo '    <td align="center" nowrap="nowrap">' . chr(10);
    if (!$class->core && $security_level > 3) echo html_button_field('btn_' . $class->id, TEXT_REMOVE, 'onclick="if (confirm(\'' . TEXT_REMOVE_MESSAGE . '\')) submitToDo(\'remove_' . $class->id . '\')"') . chr(10);
    echo '</td>' . chr(10);
    echo '    <td align="center" nowrap="nowrap">' . chr(10);
    // check to see if the module has special admin settings
	if (file_exists(DIR_FS_MODULES . $class->id . '/pages/admin/pre_process.php')) {
	  echo html_icon('categories/preferences-system.png', TEXT_PROPERTIES, 'medium', 'onclick="location.href=\'' . html_href_link(FILENAME_DEFAULT, 'module=' . $class->id . '&amp;page=admin', 'SSL') . '\'"') . chr(10);
	}
    echo '</td>' . chr(10);
  }
  echo '  </tr>' . chr(10);
  echo '<tr><td colspan="5"><hr /></td></tr>';
}
?>
 </tbody>
</table>
</div>
