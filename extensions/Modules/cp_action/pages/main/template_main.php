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
//  Path: /modules/cp_action/pages/main/template_main.php
//
echo html_form('capa', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 1) $toolbar->add_icon('new', 'onclick="submitToDo(\'new\')"', $order = 10);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('');
echo $toolbar->build($add_search = true);
?>
<h1><?php echo TEXT_CORRECTIVE_OR_PREVENTATIVE_ACTION; ?></h1>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_CAPAS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php  echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
  $odd = true;
  while (!$query_result->EOF) {
		$desc = (strlen($query_result->fields['notes_issue']) < 49) ? $query_result->fields['notes_issue'] : substr($query_result->fields['notes_issue'], 0, 48) . ' ...';
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo $query_result->fields['capa_num']; ?></td>
	<td onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo gen_locale_date($query_result->fields['creation_date']); ?></td>
	<td onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo $desc; ?></td>
	<td onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo $status_codes[$query_result->fields['capa_status']]; ?></td>
	<td onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo $query_result->fields['closed_date'] == '0000-00-00' ? '&nbsp;' : gen_locale_date($query_result->fields['closed_date']); ?></td>
	<td align="right">
<?php
	  // first pull in any extra buttons, this is dynamic since each row can have different buttons
	  if (function_exists('add_extra_action_bar_buttons')) echo add_extra_action_bar_buttons($query_result->fields);
	  if ($security_level > 2) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="submitSeq(' . $query_result->fields['id'] . ', \'edit\')"') . chr(10);
	  if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . CAPA_MSG_DELETE_CAPA . '\')) deleteItem(' . $query_result->fields['id'] . ')"') . chr(10);
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
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_CAPAS); ?></div>
</form>
