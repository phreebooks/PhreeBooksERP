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
//  Path: /modules/contacts/pages/admin/template_tab_departments.php
//
$departments_toolbar = new \core\classes\toolbar('departments');
$departments_toolbar->icon_list['cancel']['show'] = false;
$departments_toolbar->icon_list['open']['show']   = false;
$departments_toolbar->icon_list['save']['show']   = false;
$departments_toolbar->icon_list['delete']['show'] = false;
$departments_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $departments_toolbar->add_icon('new', 'onclick="loadPopUp(\'departments_new\', 0)"', $order = 10);
if ($departments->extra_buttons) $departments->customize_buttons($departments_toolbar);

?>
<div title="<?php echo HR_POPUP_WINDOW_TITLE;?>" id="tab_departments">
  <?php echo $departments_toolbar->build_toolbar(); ?>
  <h1><?php echo $departments->title; ?></h1>
  <div id="departments_content"><?php echo $departments->build_main_html(); ?></div>
</div>
