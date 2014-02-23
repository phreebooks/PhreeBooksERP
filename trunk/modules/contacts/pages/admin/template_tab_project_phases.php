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
//  Path: /modules/contacts/pages/admin/template_tab_project_phases.php
//
$project_phases_toolbar = new \core\classes\toolbar('project_phases');
$project_phases_toolbar->icon_list['cancel']['show'] = false;
$project_phases_toolbar->icon_list['open']['show']   = false;
$project_phases_toolbar->icon_list['save']['show']   = false;
$project_phases_toolbar->icon_list['delete']['show'] = false;
$project_phases_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $project_phases_toolbar->add_icon('new', 'onclick="loadPopUp(\'project_phases_new\', 0)"', $order = 10);
if ($project_phases->extra_buttons) $project_phases->customize_buttons($project_phases_toolbar);

?>
<div title="<?php echo SETUP_TITLE_PROJECTS_PHASES;?>" id="tab_project_phases">
  <?php echo $project_phases_toolbar->build_toolbar(); ?>
  <h1><?php echo $project_phases->title; ?></h1>
  <div id="project_phases_content"><?php echo $project_phases->build_main_html(); ?></div>
</div>
