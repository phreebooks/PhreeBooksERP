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
//  Path: /modules/work_orders/pages/builder/template_main.php
//
echo html_form('builder', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
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
$toolbar->add_help('07.04.WO.03');
echo $toolbar->build_toolbar($add_search = true); 
?>
<h1><?php echo BOX_WORK_ORDERS_BUILDER; ?></h1>
<div id="filter_bar">
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo TEXT_FILTERS . '&nbsp;' . TEXT_SHOW_INACTIVE . '&nbsp;' . html_checkbox_field('f0', '1', $f0); ?></td>
	<td><?php echo '&nbsp;' . html_button_field('apply', TEXT_APPLY, 'onclick="form.submit();"'); ?></td>
  </tr>
 </tbody>
</table>
</div>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_WORK_ORDERS); ?>
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
	$hide_action = ($query_result->fields['revision'] < $rev_list[$query_result->fields['wo_title']]) ? true : false;
	$bkgnd = ($query_result->fields['inactive']) ? ' style="background-color:pink"' : '';
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td<?php echo $bkgnd; ?> onclick="submitSeq(<?php echo $query_result->fields['id'] . ', \'edit\''; ?>)"><?php echo $query_result->fields['wo_title']; ?></td>
	<td<?php echo $bkgnd; ?> onclick="submitSeq(<?php echo $query_result->fields['id'] . ', \'edit\''; ?>)"><?php echo $query_result->fields['sku']; ?></td>
	<td onclick="submitSeq(<?php echo $query_result->fields['id'] . ', \'edit\''; ?>)"><?php echo $query_result->fields['description']; ?></td>
	<td onclick="submitSeq(<?php echo $query_result->fields['id'] . ', \'edit\''; ?>)"><?php echo $query_result->fields['revision']; ?></td>
	<td onclick="submitSeq(<?php echo $query_result->fields['id'] . ', \'edit\''; ?>)"><?php echo gen_locale_date($query_result->fields['revision_date']); ?></td>
	<td align="right">
<?php 
// build the action toolbar
	  // first pull in any extra buttons, this is dynamic since each row can have different buttons
	  if (function_exists('add_extra_action_bar_buttons')) echo add_extra_action_bar_buttons($query_result->fields);

	  if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="submitSeq(' . $query_result->fields['id'] . ', \'edit\')"') . chr(10);
	  if (!$hide_action) {
	    if ($security_level > 1) echo html_icon('actions/edit-copy.png', TEXT_COPY, 'small', 'onclick="copyItem(' . $query_result->fields['id'] . ')"') . chr(10);
	    if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . WORK_ORDER_MSG_DELETE_WO . '\')) deleteItem(' . $query_result->fields['id'] . ')"') . chr(10);
	  }
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
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_WORK_ORDERS); ?></div>
</form>