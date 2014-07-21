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
//  Path: /modules/phreedom/pages/import_export/template_main.php
//
echo html_form('import_export', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('10');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<fieldset>
<legend><?php echo GL_UTIL_BEG_BAL_LEGEND; ?></legend>
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
    <tr>
	  <td><?php echo GL_UTIL_BEG_BAL_TEXT; ?></td>
	  <td align="right"><?php echo html_button_field('beg_balances', GL_BTN_BEG_BAL, 'onclick="submitToDo(\'beg_balances\')"'); ?></td>
    </tr>
 </tbody>
</table>
</fieldset>

<fieldset>
<legend><?php echo HEADING_MODULE_IMPORT; ?></legend>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
	<tr><th colspan="4"><?php echo TEXT_AVAILABLE_MODULES; ?></th></tr>
	<tr>
	  <th><?php echo TEXT_MODULE;      ?></th>
	  <th><?php echo TEXT_DESCRIPTION; ?></th>
	  <th><?php echo TEXT_VERSION;     ?></th>
	  <th>&nbsp;</th>
	</tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
if (is_array($page_list)) foreach ($page_list as $key => $value) {
  echo '  <tr>';
  echo '    <td nowrap="nowrap">' . $value['title'] . '</td>' . chr(10);
  echo '    <td>' . constant('MODULE_' . strtoupper($key) . '_DESCRIPTION') . '</td>' . chr(10);
  echo '    <td align="center">' . constant('MODULE_' . strtoupper($key) . '_STATUS') . '</td>' . chr(10);
  echo '    <td align="center">' . html_icon('actions/go-next.png', TEXT_NEXT, 'large', 'onclick="submitToDo(\'go_' . $key . '\')"') . '</td>' . chr(10);
  echo '  </tr>' . chr(10);
  echo '<tr><td colspan="4"><hr /></td></tr>' . chr(10);
}
?>
 </tbody>
</table>
</fieldset>

</form>