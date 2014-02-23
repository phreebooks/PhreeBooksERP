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
//  Path: /modules/phreedom/pages/admin/template_main.php
//
echo html_form('admin', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action',   '') . chr(10);
echo html_hidden_field('subject','') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
if ($security_level > 1) $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
else                     $toolbar->icon_list['save']['show']   = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build_toolbar();
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div class="easyui-tabs" id="admintabs">
<?php
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_modules.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_company.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_config.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_email.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_currency.php');
  if (file_exists(DIR_FS_MODULES . $module . '/custom/pages/admin/template_tab_custom.php')) {
    require (DIR_FS_MODULES . $module . '/custom/pages/admin/template_tab_custom.php');
  }
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_manager.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_tools.php');
  require (DIR_FS_MODULES . $module . '/pages/admin/template_tab_stats.php');
?>
</div>
</form>
