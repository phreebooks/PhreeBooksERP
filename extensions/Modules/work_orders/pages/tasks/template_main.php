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
//  Path: /modules/work_orders/pages/tasks/template_main.php
//
echo html_form('work_order', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
echo html_hidden_field('id', $id)    . chr(10);
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
if ($security_level > 1) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
  $toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 1) $toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"', $order = 10);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.04.WO.02');
echo $toolbar->build_toolbar($add_search = true);
?>
<h1><?php echo TEXT_WORK_ORDER_TASKS; ?></h1>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
  <tr>
	<td align="right"><?php echo TEXT_TASK_NAME; ?></td>
	<td><?php echo html_input_field('task_name', $task_name); ?></td>
	<td align="right"><?php echo TEXT_DEPARTMENT; ?></td>
	<td><?php echo html_pull_down_menu('dept_id', $departments, $dept_id); ?></td>
	<td align="right"><?php echo TEXT_DOCUMENT_LIST; ?></td>
	<td><?php echo html_input_field('ref_doc', $ref_doc); ?></td>
	<td align="right"><?php echo TEXT_MFG_INIT; ?></td>
	<td><?php echo html_pull_down_menu('mfg', $yes_no_array, $mfg); ?></td>
  </tr>
  <tr>
	<td rowspan="3" align="right"><?php echo TEXT_DESCRIPTION; ?></td>
	<td colspan="3" rowspan="3"><?php echo html_textarea_field('description', 70, 3, $description); ?></td>
	<td align="right"><?php echo TEXT_DRAWINGS; ?></td>
	<td><?php echo html_input_field('ref_spec', $ref_spec); ?></td>
	<td align="right"><?php echo TEXT_QA_INIT; ?></td>
	<td><?php echo html_pull_down_menu('qa', $yes_no_array, $qa); ?></td>
  </tr>
  <tr>
	<td align="right"><?php echo TEXT_TASK_TIME; ?></td>
	<td><?php
	  echo html_input_field('job_time', $job_time, 'size="10" maxlength="10"');
	  echo html_pull_down_menu('job_unit', gen_build_pull_down($job_units), $job_unit ? $job_unit : '1');
	  ?>
	</td>
	<td align="right"><?php echo TEXT_DATA_ENTRY; ?></td>
	<td><?php echo html_pull_down_menu('data_entry', $yes_no_array, $data_entry); ?></td>
  </tr>
  <tr>
	<td colspan="2"><?php echo '&nbsp;'; ?></td>
	<td align="right"><?php echo TEXT_ERP_ENTRY; ?></td>
	<td><?php echo html_pull_down_menu('erp_entry', $yes_no_array, $erp_entry); ?></td>
  </tr>
  <tr><td colspan="8">&nbsp;</td></tr>
 </tbody>
</table>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_WORK_ORDER_TASKS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php  echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
  $odd = true;
  while (!$query_result->EOF) { ?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td onclick="editTask(<?php echo $query_result->fields['id']; ?>)"><?php echo htmlspecialchars($query_result->fields['task_name']); ?></td>
	<td onclick="editTask(<?php echo $query_result->fields['id']; ?>)"><?php echo htmlspecialchars($query_result->fields['description']); ?></td>
	<td onclick="editTask(<?php echo $query_result->fields['id']; ?>)"><?php echo $query_result->fields['ref_doc']; ?></td>
	<td onclick="editTask(<?php echo $query_result->fields['id']; ?>)"><?php echo $query_result->fields['ref_spec']; ?></td>
	<td nowrap="nowrap" onclick="editTask(<?php echo $query_result->fields['id']; ?>)"><?php echo $departments[$query_result->fields['dept_id']]['text']; ?></td>
	<td nowrap="nowrap" align="right">
<?php // build the action toolbar
	  // first pull in any extra buttons, this is dynamic since each row can have different buttons
	  if (function_exists('add_extra_action_bar_buttons')) echo add_extra_action_bar_buttons($query_result->fields);
	  if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="editTask(' . $query_result->fields['id'] . ')"') . chr(10);
	  if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . WORK_ORDER_MSG_DELETE_WO . '\')) deleteItem(' . $query_result->fields['id'] . ')"') . chr(10);
?>
	</td>
  </tr>
<?php
      $query_result->MoveNext();
      $odd = !$odd;
    }
?>
 </tbody>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_WORK_ORDER_TASKS); ?></div>
</form>