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
//  Path: /modules/contacts/pages/admin/template_tab_tabs.php
//
$tabs_toolbar = new \core\classes\toolbar('tabs');
$tabs_toolbar->icon_list['cancel']['show'] = false;
$tabs_toolbar->icon_list['open']['show']   = false;
$tabs_toolbar->icon_list['save']['show']   = false;
$tabs_toolbar->icon_list['delete']['show'] = false;
$tabs_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $tabs_toolbar->add_icon('new', 'onclick="loadPopUp(\'tabs_new\', 0)"', $order = 10);
if ($tabs->extra_buttons) $tabs->customize_buttons($tabs_toolbar);

?>
<div title="<?php echo TEXT_EXTRA_TABS;?>" id="tab_tabs">
  <?php echo $tabs_toolbar->build_toolbar(); ?>
  <h1><?php echo $tabs->title; ?></h1>
  <div id="tabs_content"><?php echo $tabs->build_main_html(); ?></div>
</div>
