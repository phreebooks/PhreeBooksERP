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
//  Path: /modules/magento/pages/main/template_main.php
//

echo html_form('magento', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
echo html_hidden_field('action', '') . chr(10);

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;

// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;

//$toolbar->add_help('');
echo $toolbar->build();
?>
<h1><?php echo BOX_MAGENTO_MODULE; ?></h1>
  <table width="600" border="0">
	<tr><td colspan="2" align="right"><img src="<?php echo DIR_WS_ADMIN . 'modules/magento/images/magento_logo.png'; ?>" alt="Magento Logo" /></td></tr>
    <tr><th colspan="2"><?php echo MAGENTO_BULK_UPLOAD_TITLE; ?></th></tr>
    <tr><td colspan="2"><?php echo MAGENTO_BULK_UPLOAD_INFO; ?></td></tr>
    <tr>
      <td align="right"><?php echo MAGENTO_INCLUDE_IMAGES; ?></td>
      <td><?php echo html_checkbox_field('include_images', '1', false); ?></td>
    </tr>
    <tr>
      <td align="right"><?php echo MAGENTO_BULK_UPLOAD_TEXT; ?></td>
      <td><?php echo html_button_field('bulkupload', MAGENTO_BULK_UPLOAD_BTN, 'onclick="submitToDo(\'bulkupload\')"'); ?></td>
	</tr>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr><th colspan="2"><?php echo MAGENTO_PRODUCT_SYNC_TITLE; ?></th></tr>
    <tr><td colspan="2"><?php echo MAGENTO_PRODUCT_SYNC_INFO; ?></td></tr>
    <tr>
      <td align="right"><?php echo MAGENTO_PRODUCT_SYNC_TEXT; ?></td>
      <td><?php echo html_button_field('sync', MAGENTO_PRODUCT_SYNC_BTN, 'onclick="submitToDo(\'sync\')"'); ?></td>
	</tr>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr><th colspan="2"><?php echo MAGENTO_SHIP_CONFIRM_TITLE; ?></th></tr>
    <tr><td colspan="2"><?php echo MAGENTO_SHIP_CONFIRM_INFO; ?></td></tr>
    <tr>
      <td align="right"><?php echo MAGENTO_TEXT_CONFIRM_ON; ?></td>
      <td><?php echo html_calendar_field($cal_zc); ?></td>
	</tr>
    <tr>
      <td align="right"><?php echo MAGENTO_SHIP_CONFIRM_TEXT; ?></td>
      <td><?php echo html_button_field('confirm', MAGENTO_SHIP_CONFIRM_BTN, 'onclick="submitToDo(\'confirm\')"'); ?></td>
	</tr>
  </table>
</form>
