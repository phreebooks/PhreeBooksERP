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
//  Path: /modules/assets/pages/main/template_detail.php
//
echo html_form('assets', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'cID', 'asset_id')), 'post', 'enctype="multipart/form-data"');
// include hidden fields
echo html_hidden_field('id', $cInfo->id) . chr(10);
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action','page')) . '&page=main', 'SSL') . '\'"';
$toolbar->icon_list['open']['show'] = false;
$toolbar->icon_list['delete']['show'] = false;
if ($security_level > 2) {
	$toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
	$toolbar->icon_list['save']['show'] = false;
}
$toolbar->icon_list['print']['show'] = false;
$toolbar->add_help('');
echo $toolbar->build_toolbar(); 
$fields->set_fields_to_display($asset_type);
?>
<h1><?php echo MENU_HEADING_ASSETS . ' - ' . TEXT_ASSET_ID . '# ' . $cInfo->asset_id; ?></h1>

  <div class="easyui-dialog" data-options="closed: true," id="inv_image" title="<?php echo TEXT_IMAGE; ?>" style="width:800px;padding:10px">
    <?php if ($cInfo->image_with_path) echo html_image(DIR_WS_FULL_PATH . 'my_files/' . $_SESSION['company'] . '/assets/images/' . $cInfo->image_with_path, '', 600) . chr(10);
			else echo TEXT_NO_IMAGE; ?>
    <div>
	  <h2><?php echo TEXT_ASSET_ID . ': ' . $cInfo->asset_id; ?></h2>
	  <p><?php echo '<br />' . $cInfo->description_long; ?></p>
    </div>
  </div>

<div class="easyui-tabs" id="detailtabs">
<!-- start the tabsets -->
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
  <table>
	<tr>
	  <td><?php echo TEXT_ASSET_ID; ?></td>
	  <td>
		<?php echo html_input_field('asset_id', $cInfo->asset_id, 'readonly="readonly"', false); ?>
		<?php echo TEXT_INACTIVE; ?>
		<?php echo html_checkbox_field('inactive', '1', $cInfo->inactive); ?>
	  </td>
	  <td rowspan="4" align="center">
		<?php if ($cInfo->image_with_path) { // show image if it is defined
			echo html_image(DIR_WS_MY_FILES . $_SESSION['company'] . '/assets/images/' . $cInfo->image_with_path, $cInfo->image_with_path, '', '100', 'onclick="showImage()"');
		} else echo '&nbsp;'; ?>
	  </td>
	  <td><?php echo TEXT_IMAGE . ' (' . TEXT_REMOVE . ' ' . html_checkbox_field('remove_image', '1', $cInfo->remove_image) . ')'; ?></td>
	</tr>
	<tr>
	  <td><?php echo TEXT_DESCRIPTION_SHORT; ?></td>
	  <td><?php echo html_input_field('description_short', $cInfo->description_short, 'size="33" maxlength="32"', false); ?></td>
	  <td><?php echo html_file_field('asset_image'); ?></td>
	</tr>
	<tr>
	  <td><?php echo ASSETS_ENTRY_ASSETS_TYPE; ?></td>
	  <td><?php echo html_hidden_field('asset_type', $cInfo->asset_type);
		echo html_input_field('inv_type_desc', $assets_types[$cInfo->asset_type], 'readonly="readonly"', false); ?> </td>
	  <td><?php echo ASSETS_ENTRY_IMAGE_PATH; ?></td>
	</tr>
	<tr>
	  <td><?php echo ASSETS_ENTRY_FULL_PRICE; ?></td>
	  <td>
	  	<?php echo html_input_field('full_price', $currencies->format($cInfo->full_price), 'size="11" maxlength="10" style="text-align:right"', false) . (ENABLE_MULTI_CURRENCY ? (' (' . DEFAULT_CURRENCY . ')') : ''); 
		?>
	  </td>
	  <td>
		<?php echo html_hidden_field('image_with_path', $cInfo->image_with_path); 
		echo html_input_field('asset_path', substr($cInfo->image_with_path, 0, strrpos($cInfo->image_with_path, '/'))); ?>
	  </td>
	</tr>
	<tr>
	  <td><?php echo ASSETS_ENTRY_ASSETS_SERIALIZE; ?></td>
	  <td><?php echo html_input_field('serial_number', $cInfo->serial_number, 'size="33" maxlength="32"'); ?></td>
	  <td><?php echo ASSETS_PURCHASE_CONDITION; ?></td>
	  <td><?php echo html_pull_down_menu('purch_cond', $purch_cond_array, $cInfo->purch_cond); ?></td>
	</tr>
	<tr>
	  <td valign="top"><?php echo TEXT_DETAIL_DESCRIPTION; ?></td>
	  <td colspan="3"><?php echo html_textarea_field('description_long', 75, 3, $cInfo->description_long, '', $reinsert_value = true); ?></td>
	</tr>
	<tr>
	  <td><?php echo ASSETS_ENTRY_ACCT_SALES; ?></td>
	  <td><?php echo html_pull_down_menu('account_asset', $gl_array_list, $cInfo->account_asset); ?></td>
	  <td><?php echo ASSETS_DATE_ACCOUNT_CREATION; ?></td>
	  <td><?php echo html_calendar_field($cal_date1); ?></td>
	</tr>
	<tr>
	  <td><?php echo ASSETS_ENTRY_ACCT_INV; ?></td>
	  <td><?php echo html_pull_down_menu('account_depreciation', $gl_array_list, $cInfo->account_depreciation); ?></td>
	  <td><?php echo ASSETS_DATE_LAST_UPDATE; ?></td>
	  <td><?php echo html_calendar_field($cal_date2); ?></td>
	</tr>
	<tr>
	  <td><?php echo ASSETS_ENTRY_ACCT_COS; ?></td>
	  <td><?php echo html_pull_down_menu('account_maintenance', $gl_array_list, $cInfo->account_maintenance); ?></td>
	  <td><?php echo ASSETS_DATE_LAST_JOURNAL_DATE; ?></td>
	  <td><?php echo html_calendar_field($cal_date3); ?></td>
	</tr>
  </table>
<?php // *********************** Attachments  ************************************* ?>
  <div>
   <fieldset>
   <legend><?php echo TEXT_ATTACHMENTS; ?></legend>
   <table class="ui-widget" style="width:100%">
    <thead class="ui-widget-header">
     <tr><th colspan="3"><?php echo TEXT_ATTACHMENTS; ?></th></tr>
    </thead>
    <tbody class="ui-widget-content">
     <tr><td colspan="3"><?php echo TEXT_SELECT_FILE_TO_ATTACH . ' ' . html_file_field('file_name'); ?></td></tr>
     <tr  class="ui-widget-header">
      <th><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
      <th><?php echo TEXT_FILENAME; ?></th>
      <th><?php echo TEXT_ACTION; ?></th>
     </tr>
<?php 
if (sizeof($attachments) > 0) { 
  foreach ($attachments as $key => $value) {
    echo '<tr>';
    echo ' <td>' . html_checkbox_field('rm_attach_'.$key, '1', false) . '</td>' . chr(10);
    echo ' <td>' . $value . '</td>' . chr(10);
    echo ' <td>' . html_button_field('dn_attach_'.$key, TEXT_DOWNLOAD, 'onclick="submitSeq(' . $key . ', \'download\', true)"') . '</td>';
    echo '</tr>' . chr(10);
  }
} else {
  echo '<tr><td colspan="3">' . TEXT_NO_DOCUMENTS . '</td></tr>'; 
} ?>
    </tbody>
   </table>
   </fieldset>
  </div>
</div>

<?php
//********************************* List Custom Fields Here ***********************************
echo $fields->extra_tab_html;
// *********************** End Custom Fields  *************************************

// pull in additional custom tabs
if (isset($extra_assets_tabs) && is_array($extra_assets_tabs)) {
  foreach ($extra_assets_tabs as $tabs) {
    $file_path = DIR_FS_WORKING . 'custom/pages/main/' . $tabs['tab_filename'] . '.php';
    if (file_exists($file_path)) { require($file_path); }
  }
}
?>
</div>
</form>