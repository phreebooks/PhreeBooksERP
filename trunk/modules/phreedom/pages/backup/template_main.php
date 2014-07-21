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
//  Path: /modules/phreedom/pages/backup/template_main.php
//
echo html_form('popup_backup', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->icon_list['restore'] = array(
  'show'   => true, 
  'icon'   => 'devices/drive-optical.png',
  'params' => 'onclick="if (confirm(\'' . GEN_BACKUP_WARNING . '\')) submitToDo(\'restore\')"', 
  'text'   => BOX_HEADING_RESTORE, 
  'order'  => 10,
);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
// add the help file index and build the toolbar
$toolbar->add_help('01');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo BOX_HEADING_BACKUP; ?></h1>
<fieldset>
<legend><?php echo GEN_ADM_TOOLS_CLEAN_LOG; ?></legend>
<p><?php echo GEN_ADM_TOOLS_CLEAN_LOG_DESC; ?></p>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
    <tr>
	  <th><?php echo GEN_ADM_TOOLS_CLEAN_LOG_BACKUP; ?></th>
	  <th><?php echo GEN_ADM_TOOLS_CLEAN_LOG_CLEAN; ?></th>
	</tr>
 </thead>
 <tbody class="ui-widget-content">
	<tr>
	  <td align="center"><?php echo html_button_field('backup_log', GEN_ADM_TOOLS_BTN_BACKUP, 'onclick="submitToDo(\'backup_log\', true)"'); ?></td>
	  <td align="center"><?php echo html_button_field('clean_log',  GEN_ADM_TOOLS_BTN_CLEAN,  'onclick="if (confirm(\'' . GEN_ADM_TOOLS_BTN_CLEAN_CONFIRM . '\')) submitToDo(\'clean_log\')"'); ?></td>
	</tr>
 </tbody>
</table>
</fieldset>

<fieldset>
<legend><?php echo BOX_HEADING_BACKUP; ?></legend>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
  <tr><th colspan="3"><?php echo GEN_BACKUP_COMP_TYPE; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr><td colspan="3"><?php echo GEN_BACKUP_GEN_INFO; ?></td></tr> 
  <tr>
	<td align="center"><?php echo html_radio_field('conv_type', 'zip',  true,  '', '') . GEN_COMP_ZIP; ?></td>
	<td align="center"><?php echo html_radio_field('conv_type', 'bz2',  false, '', '') . GEN_COMP_BZ2; ?></td>
	<td align="center" nowrap="nowrap"><?php echo html_radio_field('conv_type', 'none', false, '', '') . GEN_COMP_NONE; ?></td>
  </tr>
  <tr class="ui-widget-header"><th colspan="3"><?php echo TEXT_OPTIONS; ?></th></tr>
  <tr>
	<td colspan="3"><?php echo html_radio_field('dl_type', 'file', true, '', '') . GEN_BACKUP_DB_ONLY; ?></td>
  </tr>
  <tr>
	<td colspan="3"><?php echo html_radio_field('dl_type', 'dir', false, '', '') . GEN_BACKUP_FULL; ?></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo html_checkbox_field('save_local', '1', false, '', '') . GEN_BACKUP_SAVE_LOCAL; ?></td>
	<td align="right"><?php echo html_button_field('backup_db', GEN_BACKUP_ICON_TITLE, 'onclick="submitToDo(\'save\', true)"'); ?></td>
  </tr>
 </tbody>
</table>
</fieldset>
</form>