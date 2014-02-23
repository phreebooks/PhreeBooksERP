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
//  Path: /modules/phreepos/pages/admin/template_other_trans.php
//
$trans_toolbar = new \core\classes\toolbar();
$trans_toolbar->icon_list['cancel']['show'] = false;
$trans_toolbar->icon_list['open']['show']   = false;
$trans_toolbar->icon_list['delete']['show'] = false;
$trans_toolbar->icon_list['save']['show']   = false;
$trans_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $trans_toolbar->add_icon('new', 'onclick="loadPopUp(\'other_transactions_new\', 0)"', $order = 10);
?>
<div title="<?php echo TEXT_OTHER_TRANS;?>" id="tab_other_trans">
  <?php echo $trans_toolbar->build_toolbar(); ?>
  <h1><?php echo $trans->title; ?></h1>
  <div id="other_trans_content"><?php echo $trans->build_main_html(); ?></div>
</div>
