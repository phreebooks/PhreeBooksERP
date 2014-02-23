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
//  Path: /modules/work_orders/pages/builder/template_detail.php
//
echo html_form('work_orders', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'id')), 'post', '');
// include hidden fields
echo html_hidden_field('action',   '')      . chr(10);
echo html_hidden_field('id',     $id)     . chr(10);
echo html_hidden_field('sku_id', $sku_id) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
if (!$hide_save && ((!$id && $security_level > 1) || ($id && $security_level > 2))) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
  $toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_help('07.04.WO.03');
echo $toolbar->build_toolbar(); 
?>
<h1><?php echo BOX_WORK_ORDERS_BUILDER . ' - ' . $wo_title; ?></h1>
<div class="easyui-tabs" id="buildertabs">
<?php
  require (DIR_FS_WORKING . 'pages/builder/template_tab_gen.php');
  require (DIR_FS_WORKING . 'pages/builder/template_tab_hist.php');
?>
</div>
</form>
