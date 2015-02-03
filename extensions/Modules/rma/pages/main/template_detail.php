<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/rma/pages/main/template_detail.php
//
echo html_form('rma', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'cID', 'asset_id')), 'post', 'enctype="multipart/form-data"') . chr(10);
$hidden_fields = NULL;
// include hidden fields
echo html_hidden_field('id', $cInfo->id) . chr(10);
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
echo html_hidden_field('rma_num', $cInfo->rma_num) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params']   = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action', page)) . '&page=main', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']       = false;
$toolbar->icon_list['delete']['show']     = false;
if ($security_level > 1) {
	$toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
	$toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['print']['show']      = false;
$toolbar->add_help('');
echo $toolbar->build();
?>
<h1><?php echo ($_REQUEST['action'] == 'new') ? sprintf(TEXT_NEW_ARGS, TEXT_RMA) : (sprintf(TEXT_EDIT_ARGS, TEXT_RMA) . ' - ' . TEXT_RMA_ID . '# ' . $cInfo->rma_num); ?></h1>

<div class="easyui-tabs" id="detailtabs">
<?php
require (DIR_FS_WORKING . 'pages/main/tab_general.php');
require (DIR_FS_WORKING . 'pages/main/tab_receiving.php');
// pull in additional custom tabs
if (isset($extra_rma_tabs) && is_array($extra_rma_tabs)) {
  foreach ($extra_rma_tabs as $tabs) {
	$file_path = DIR_FS_WORKING . 'custom/pages/main/' . $tabs['tab_filename'] . '.php';
	if (file_exists($file_path)) {
	  require($file_path);
	}
  }
}
require (DIR_FS_WORKING . 'pages/main/tab_disposition.php');
?>
</div>
<?php echo $hidden_fields; ?>
</form>
