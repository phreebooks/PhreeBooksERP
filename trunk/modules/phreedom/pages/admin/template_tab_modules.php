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
//  Path: /modules/phreedom/pages/admin/template_tab_modules.php
//
?>
<div id="tab_modules">
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="4"><?php echo TEXT_AVAILABLE_MODULES; ?></th></tr>
  <tr>
    <th><?php echo TEXT_MODULE; ?></th>
    <th><?php echo TEXT_DESCRIPTION; ?></th>
    <th><?php echo TEXT_VERSION; ?></th>
    <th><?php echo TEXT_ACTION; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
if (is_array($page_list)) foreach ($page_list as $key => $value) {
  $installed = defined('MODULE_' . strtoupper($key) . '_STATUS');
  echo '  <tr>';
  echo '    <td' . ($installed ? ' class="ui-state-active"' : '') . '>' . $value . '</td>';
  echo '    <td>' . constant('MODULE_' . strtoupper($key) . '_DESCRIPTION') . '</td>';
  if (!$installed && $security_level > 1) {
    echo '    <td>&nbsp;</td>';
	echo '    <td align="center">' . html_button_field('btn_' . $key, TEXT_INSTALL, 'onclick="submitToDo(\'install_' . $key . '\')"') . '</td>' . chr(10);
  } else {
    echo '    <td align="center">' . constant('MODULE_' . strtoupper($key) . '_STATUS') . '</td>';
	echo '    <td align="center" nowrap="nowrap">' . chr(10);
    if ($security_level > 3) echo html_button_field('btn_' . $key, TEXT_REMOVE, 'onclick="if (confirm(\'' . TEXT_REMOVE_MESSAGE . '\')) submitToDo(\'remove_' . $key . '\')"') . chr(10);
    // check to see if the module has special admin settings
	if (file_exists(DIR_FS_MODULES . $key . '/pages/admin/pre_process.php')) {
	  echo html_icon('categories/preferences-system.png', TEXT_PROPERTIES, 'medium', 'onclick="location.href=\'' . html_href_link(FILENAME_DEFAULT, 'module=' . $key . '&amp;page=admin', 'SSL') . '\'"') . chr(10);
	}
    echo '</td>' . chr(10);
  }
  echo '  </tr>' . chr(10);
  echo '<tr><td colspan="4"><hr /></td></tr>';
}
?>
 </tbody>
</table>
</div>
