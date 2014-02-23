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
//  Path: /modules/contacts/pages/admin/template_tab_dept_types.php
//
$dept_types_toolbar = new \core\classes\toolbar('dept_types');
$dept_types_toolbar->icon_list['cancel']['show'] = false;
$dept_types_toolbar->icon_list['open']['show']   = false;
$dept_types_toolbar->icon_list['save']['show']   = false;
$dept_types_toolbar->icon_list['delete']['show'] = false;
$dept_types_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $dept_types_toolbar->add_icon('new', 'onclick="loadPopUp(\'dept_types_new\', 0)"', $order = 10);
if ($dept_types->extra_buttons) $dept_types->customize_buttons($dept_types_toolbar);

?>
<div title="<?php echo SETUP_TITLE_DEPT_TYPES;?>" id="tab_dept_types">
  <?php echo $dept_types_toolbar->build_toolbar(); ?>
  <h1><?php echo $dept_types->title; ?></h1>
  <div id="dept_types_content"><?php echo $dept_types->build_main_html(); ?></div>
</div>
