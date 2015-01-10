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
//  Path: /modules/translator/pages/main/template_export.php
//
echo html_form('translator', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
echo html_hidden_field('action',   '') . chr(10);
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build_toolbar();
array_shift($sel_language); // remove 'all' option
?>
<h1><?php echo TEXT_EXPORT_TRANSLATION; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="2"><?php echo TEXT_EXPORT_TRANSLATION; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr><td colspan="2"><?php echo TRANSLATOR_EXPORT_DESC; ?></td></tr>
  <tr>
	<td><?php echo TRANSLATOR_ISO_EXPORT . '&nbsp;'; ?></td>
	<td><?php echo html_pull_down_menu('lang', $sel_language, DEFAULT_LANGUAGE); ?></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo '&nbsp;'; ?></td>
  </tr>
  <tr>
	<td colspan="2" align="right"><?php echo html_button_field('export', TEXT_EXPORT, 'onclick="submitToDo(\'export_all_go\', true)"'); ?></td>
  </tr>
 </tbody>
</table>
</form>