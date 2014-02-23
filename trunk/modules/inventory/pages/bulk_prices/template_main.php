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
//  Path: /modules/inventory/pages/bulk_prices/template_main.php
//
echo html_form('bulk_prices', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level < 3) $toolbar->icon_list['save']['show'] = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.04.06');
echo $toolbar->build_toolbar($add_search = true); 
// Build the page
?>
<h1><?php echo INV_BULK_SKU_ENTRY_TITLE; ?></h1>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
  <?php
	$j = 1;
	$odd = true;
    while (!$query_result->EOF) {
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>">
	<td><?php echo html_hidden_field('id_' . $j, $query_result->fields['id']) . $query_result->fields['sku']; ?></td>
	<td><?php echo $query_result->fields['inactive'] == '1' ? TEXT_YES : ''; ?></td>
	<td><?php echo $query_result->fields['description_short']; ?></td>
	<td><?php echo html_input_field('lead_'  . $j, $query_result->fields['lead_time'], 'size="11" style="text-align:right"'); ?></td>
	<td><?php echo html_input_field('min_'   . $j, $currencies->precise($query_result->fields['minimum_stock_level']), 'size="11" style="text-align:right"'); ?></td>
	<td><?php echo html_input_field('reOrd_' . $j, $currencies->precise($query_result->fields['reorder_quantity']), 'size="11" style="text-align:right"'); ?></td>
	<td><?php echo html_input_field('cost_'  . $j, $currencies->precise($query_result->fields['item_cost']), 'size="11" style="text-align:right"'); ?></td>
	<td><?php echo html_input_field('sell_'  . $j, $currencies->precise($query_result->fields['full_price']), 'size="11" style="text-align:right"'); ?></td>
	<td><?php if ($security_level > 1) echo html_icon('mimetypes/x-office-spreadsheet.png', BOX_PRICE_SHEET_MANAGER, 'small', $params = 'onclick="priceMgr(' . $j . ', ' . $query_result->fields['id'] . ')"'); ?></td>
  </tr>
<?php
	  $j++;
	  $query_result->MoveNext();
	  $odd = !$odd;
    }
?>
</tbody>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?></div>
</form>
