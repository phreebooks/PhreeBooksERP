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
//  Path: /modules/contacts/pages/admin/template_main.php
//

// start the form
echo html_form('admin', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);

// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('subject','') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=admin', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
if ($security_level > 1) $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
else                     $toolbar->icon_list['save']['show']   = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build_toolbar();
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div id="admintabs">
<ul>
<?php
  echo add_tab_list('tab_add_book',      TEXT_ADDRESS_BOOK);
  echo add_tab_list('tab_departments',   HR_POPUP_WINDOW_TITLE);
  echo add_tab_list('tab_dept_types',    SETUP_TITLE_DEPT_TYPES);
  echo add_tab_list('tab_project_costs', SETUP_TITLE_PROJECTS_COSTS);
  echo add_tab_list('tab_project_phases',SETUP_TITLE_PROJECTS_PHASES);
  echo add_tab_list('tab_tabs',          TEXT_EXTRA_TABS);
  echo add_tab_list('tab_fields',        TEXT_EXTRA_FIELDS);
  if (file_exists(DIR_FS_MODULES . $module . '/custom/pages/admin/template_tab_custom.php')) {
    echo add_tab_list('tab_custom',      TEXT_CUSTOM_TAB); 
  }
  echo add_tab_list('tab_stats',         TEXT_STATISTICS);
?>
</ul>
<?php
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_add_book.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_departments.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_dept_types.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_project_costs.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_project_phases.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_tabs.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_fields.php');
  if (file_exists(DIR_FS_MODULES . $module . '/custom/pages/admin/template_tab_custom.php')) {
    require (DIR_FS_MODULES . $module . '/custom/pages/admin/template_tab_custom.php');
  }
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_stats.php');
?>
</div>
</form>