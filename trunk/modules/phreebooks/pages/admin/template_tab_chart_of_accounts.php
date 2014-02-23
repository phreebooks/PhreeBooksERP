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
//  Path: /modules/phreebooks/pages/admin/template_tab_chart_of_accounts.php
//
$chart_of_accounts_toolbar = new \core\classes\toolbar;
$chart_of_accounts_toolbar->icon_list['cancel']['show'] = false;
$chart_of_accounts_toolbar->icon_list['open']['show']   = false;
$chart_of_accounts_toolbar->icon_list['save']['show']   = false;
$chart_of_accounts_toolbar->icon_list['delete']['show'] = false;
$chart_of_accounts_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $chart_of_accounts_toolbar->add_icon('new', 'onclick="loadPopUp(\'chart_of_accounts_new\', 0)"', $order = 10);
?>
<div title="<?php echo GL_POPUP_WINDOW_TITLE;?>" id="tab_chart_of_accounts">
  <?php echo $chart_of_accounts_toolbar->build_toolbar(); ?>
  <h1><?php echo $chart_of_accounts->title; ?></h1>
  <div align="center">
    <?php echo GL_SELECT_STD_CHART . html_pull_down_menu('std_chart', $sel_chart); ?>
    <?php echo GL_CHART_IMPORT_DESC . html_file_field('file_name') . '<br />'; ?>
    <?php echo html_checkbox_field('delete_chart', '1', false) . GL_CHART_REPLACE; ?>
    <?php echo html_button_field('import', TEXT_IMPORT, 'onclick="submitToDo(\'import\')"'); ?>
    <?php echo '<br />' . GL_CHART_DELETE_WARNING; ?>
  </div>
  <div id="chart_of_accounts_content"><?php echo $chart_of_accounts->build_main_html(); ?></div>
</div>
