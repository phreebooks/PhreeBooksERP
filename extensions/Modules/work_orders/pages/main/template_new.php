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
//  Path: /modules/work_orders/pages/main/template_new.php
//
echo html_form('work_orders', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'id')), 'post', '');
// include hidden fields
echo html_hidden_field('action', '')                . chr(10);
echo html_hidden_field('id', $id)                 . chr(10);
echo html_hidden_field('wo_id', $wo_id)           . chr(10);
echo html_hidden_field('sku_id', $sku_id)         . chr(10);
echo html_hidden_field('store_id', $store_id)     . chr(10);
echo html_hidden_field('close_date', $close_date) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"';
$toolbar->icon_list['open']['show'] = false;
$toolbar->icon_list['delete']['show'] = false;
if (!$hide_save && $security_level > 1) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
  $toolbar->icon_list['save']['show'] = false;
}
$toolbar->icon_list['print']['params'] = 'onclick="submitToDo(\'print\')"';
if ($security_level > 1) $toolbar->add_icon('new', 'onclick="submitToDo(\'new\')"', $order = 2);
$toolbar->add_help('07.04.WO.04');
echo $toolbar->build_toolbar();
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
  <tr>
	<td align="right"><?php echo TEXT_SKU; ?> </td>
    <td><?php echo html_input_field('sku', $sku, ($id ? 'readonly="readonly" ' : '') . 'size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '"') . '&nbsp;';
	  if (!$id) echo html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="InventoryList(1)"'); ?></td>
	<td align="right"><?php echo TEXT_QUANTITY; ?></td>
	<td><?php echo html_input_field('qty', $qty, 'size="7" maxlength="5" onchange="fetchBOMList()"'); ?></td>
    <td align="right"><?php echo TEXT_POST_DATE; ?></td>
	<td><?php echo html_calendar_field($cal_date); ?></td>
	<td id="image_here" rowspan="4">&nbsp;</td>
  </tr>
  <tr>
	<td align="right"><?php echo TEXT_WORK_ORDER_TITLE; ?> </td>
    <td><?php echo html_input_field('wo_title', $wo_title, 'readonly="readonly" size="33"') . '&nbsp;'; ?></td>
	<td align="right"><?php echo TEXT_PRIORITY; ?></td>
	<td><?php echo html_pull_down_menu('priority', $priority_list, $priority); ?></td>
	<td align="right"><?php echo TEXT_CLOSE; ?></td>
	<td><?php echo html_checkbox_field('closed', '1', $closed ? true : false) . ($closed ? (' ' . gen_locale_date($close_date)) : ''); ?></td>
  </tr>
  <tr>
    <td colspan="6"><?php echo TEXT_WORK_ORDER_NOTES_AND_COMMENTS; ?></td>
  </tr>
  <tr>
	<td colspan="6"><?php echo html_textarea_field('notes', 80, 3, $notes, $params = ''); ?></td>
  </tr>
</tbody>
</table>
<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
 <thead class="ui-widget-header">
	<tr>
	  <th><?php echo TEXT_STEP; ?></th>
	  <th><?php echo TEXT_TASK_NAME; ?></th>
	  <th><?php echo TEXT_DESCRIPTION; ?></th>
	</tr>
 </thead>
 <tbody id="table_tasks" class="ui-widget-content">
<?php
  $odd = true;
  if ($_REQUEST['action'] == 'edit') {
    foreach ($step_list as $value) {
	  $mfg_id = ($value['mfg'] == '1') ? get_user_name($value['mfg_id']) : '-';
	  $qa_id  = ($value['qa'] == '1')  ? get_user_name($value['qa_id'])  : '-';
	  echo '  <tr class="' . ($odd?'odd':'even') . '">' . chr(10);
	  echo '    <td align="center">' . $value['step'] . '</td>' . chr(10);
	  echo '    <td>' . $value['task_name']    . '</td>' . chr(10);
	  echo '    <td>' . $value['description']  . '</td>' . chr(10);
	  echo '    <td align="center">' . $mfg_id . '</td>' . chr(10);
	  echo '    <td align="center">' . $qa_id  . '</td>' . chr(10);
	  echo '    <td align="center">' . ($value['complete'] == '1' ? TEXT_YES : TEXT_NO) . '</td>' . chr(10);
	  echo '  </tr>' . chr(10);
	  $odd = !$odd;
	}
  } else {
    echo '<tr><td>&nbsp;</td></tr>';
  }
?>
 </tbody>
</table>
</form>
