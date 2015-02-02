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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/audit/pages/main/template_main.php
//

// start the form
echo html_form('audit', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('subject', $subject) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=import_export', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('export','onclick="submitToDo(\'export_audit\')"');
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
  foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}
echo $toolbar->build(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>

<fieldset>
<legend><?php echo TEXT_EXPORT; ?></legend>
<label><?php echo TEXT_START_DATE . ' ' . html_calendar_field($cal_from);?></label><br>
<label><?php echo TEXT_END_DATE   . ' ' . html_calendar_field($cal_to)  ;?></label><br>
<label><?php echo TEXT_ALL_OR_ACTIVE   . ' ' . html_pull_down_menu('select', $sel_options,1)  ;?></label><br>
</fieldset>

</form>