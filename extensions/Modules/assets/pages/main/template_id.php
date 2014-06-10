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
//  Path: /modules/assets/pages/cat_inv/template_id.php
//

echo html_form('asset', FILENAME_DEFAULT, gen_get_all_get_params(array('action')));
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action','page')) . '&amp;page=main', 'SSL') . '\'"';
$toolbar->icon_list['open']['show'] = false;
$toolbar->icon_list['delete']['show'] = false;
$toolbar->icon_list['save']['show'] = false;
$toolbar->icon_list['print']['show'] = false;
$toolbar->add_icon('continue', 'onclick="submitToDo(\'create\')"', $order = 10);
$toolbar->add_help('');
echo $toolbar->build_toolbar();
?>
  <h1><?php echo sprintf(TEXT_NEW_ARGS, TEXT_ASSET); ?></h1>
  <table>
    <tr>
	  <th nowrap="nowrap" colspan="2"><?php echo sprintf(TEXT_NEW_ARGS, TEXT_ASSET); ?></th>
    </tr>
    <tr>
	  <td align="right"><?php echo TEXT_ASSET_ID; ?></td>
	  <td><?php echo html_input_field('asset_id', $asset_id, 'size="17" maxlength="16"'); ?></td>
    </tr>
    <tr>
	  <td align="right"><?php echo TEXT_ENTER_THE_ASSET_TYPE; ?></td>
	  <td><?php echo html_pull_down_menu('asset_type', gen_build_pull_down($assets_types), isset($asset_type) ? $asset_type : 'vh'); ?></td>
    </tr>
    <tr>
	  <td nowrap="nowrap" colspan="2"><?php echo '&nbsp;'; ?></td>
    </tr>
  </table>
</form>