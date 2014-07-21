<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/inventory/pages/price_sheets/template_main.php
//
echo html_form('pricesheet', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 1) $toolbar->add_icon('new', 'onclick="submitToDo(\'new\')"', $order = 10);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
// add the help file index and build the toolbar
$toolbar->add_help('07.04.06');
echo $toolbar->build_toolbar($add_search = true); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_PRICE_SHEETS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%;">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
 <?php
 $odd = true;
 while (!$query_result->EOF) {
	$result = $db->Execute("select id from " . TABLE_INVENTORY_SPECIAL_PRICES . " 
		where price_sheet_id = " . $query_result->fields['id']);
	$special_price = ($result->RecordCount() > 0) ? TEXT_YES : '';
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo $query_result->fields['sheet_name']; ?></td>
	<td style="text-align:center" onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo ($query_result->fields['inactive'] == '1' ? TEXT_YES : ''); ?></td>
	<td style="text-align:center" onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo $query_result->fields['revision']; ?></td>
	<td style="text-align:center" onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo ($query_result->fields['default_sheet'] == '1' ? TEXT_YES : ''); ?></td>
	<td style="text-align:center" onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo gen_locale_date($query_result->fields['effective_date']); ?></td>
	<td style="text-align:center" onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo gen_locale_date($query_result->fields['expiration_date']); ?></td>
	<td style="text-align:center" onclick="submitSeq(<?php echo $query_result->fields['id']; ?>, 'edit')"><?php echo $special_price; ?></td>
	<td style="text-align:right" >
<?php 
	if (function_exists('add_extra_action_bar_buttons')) echo add_extra_action_bar_buttons($query_result->fields);
	if ($query_result->fields['revision'] == $rev_levels[$query_result->fields['sheet_name']]) {
		if ($security_level > 1) echo html_button_field('revise_' . $query_result->fields['id'], TEXT_REVISE, 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list', 'psID')) . '&amp;list=' . $_REQUEST['list'] . '&amp;action=revise&amp;psID=' . $query_result->fields['id'], 'SSL') . '\'"');
	}
	if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="submitSeq(' . $query_result->fields['id'] . ', \'edit\')"') . chr(10);
	if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . PRICE_SHEET_MSG_DELETE . '\')) deleteItem(' . $query_result->fields['id'] . ')"') . chr(10);
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
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_PRICE_SHEETS); ?></div>
<?php echo html_button_field('prices', TEXT_BULK_EDIT, 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=bulk_prices', 'SSL') . '\'"'); ?>
</form>
