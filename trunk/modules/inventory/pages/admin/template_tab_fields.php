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
//  Path: /modules/inventory/pages/admin/template_tab_fields.php
//
$field_toolbar = new \core\classes\toolbar();
$field_toolbar->icon_list['cancel']['show'] = false;
$field_toolbar->icon_list['open']['show']   = false;
$field_toolbar->icon_list['delete']['show'] = false;
$field_toolbar->icon_list['save']['show']   = false;
$field_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $field_toolbar->add_icon('new', 'onclick="loadPopUp(\'fields_new\', 0)"', $order = 10);
?>
<div title="<?php echo TEXT_FIELDS;?>" id="tab_fields">
  <?php echo $field_toolbar->build_toolbar(); ?>
  <h1><?php echo $fields->title; ?></h1>
  <div id="fields_content"><?php echo $fields->build_main_html(); ?></div>
</div>
