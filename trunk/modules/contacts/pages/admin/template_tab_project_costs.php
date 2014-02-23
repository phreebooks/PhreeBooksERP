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
//  Path: /modules/contacts/pages/admin/template_tab_project_costs.php
//
$project_costs_toolbar = new \core\classes\toolbar('project_costs');
$project_costs_toolbar->icon_list['cancel']['show'] = false;
$project_costs_toolbar->icon_list['open']['show']   = false;
$project_costs_toolbar->icon_list['save']['show']   = false;
$project_costs_toolbar->icon_list['delete']['show'] = false;
$project_costs_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $project_costs_toolbar->add_icon('new', 'onclick="loadPopUp(\'project_costs_new\', 0)"', $order = 10);
if ($project_costs->extra_buttons) $project_costs->customize_buttons($project_costs_toolbar);

?>
<div title="<?php echo SETUP_TITLE_PROJECTS_COSTS;?>" id="tab_project_costs">
  <?php echo $project_costs_toolbar->build_toolbar(); ?>
  <h1><?php echo $project_costs->title; ?></h1>
  <div id="project_costs_content"><?php echo $project_costs->build_main_html(); ?></div>
</div>
