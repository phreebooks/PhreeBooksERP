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
//  Path: /modules/import_bank/pages/admin/template_tab_kt.php
//
$kt_toolbar = new \core\classes\toolbar();
$kt_toolbar->icon_list['cancel']['show'] = false;
$kt_toolbar->icon_list['open']['show']   = false;
$kt_toolbar->icon_list['delete']['show'] = false;
$kt_toolbar->icon_list['save']['show']   = false;
$kt_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $kt_toolbar->add_icon('new', 'onclick="loadPopUp(\'known_transactions_new\', 0)"', $order = 10);
?>
<div title="<?php echo TEXT_KNOWN_TRANSACTION;?>" id="tab_kt">
  <?php echo $kt_toolbar->build_toolbar(); ?>
  <h1><?php echo $kt->title; ?></h1>
  <div id="kt_content"><?php echo $kt->build_main_html(); ?></div>
</div>
