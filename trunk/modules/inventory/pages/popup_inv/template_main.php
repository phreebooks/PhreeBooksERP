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
//  Path: /modules/inventory/pages/popup_inv/template_main.php
//
echo html_form('search_form', FILENAME_DEFAULT, gen_get_all_get_params(array('action','list', 'f0', 'f1', 'f2', 'f3', 'f4', 'f5'))) . chr(10);
// include hidden fields
echo html_hidden_field('action',   '') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.04.01');
$toolbar->search_period = $acct_period;
echo $toolbar->build_toolbar($add_search = true); 
// Build the page
?>
<h1><?php echo INV_POPUP_WINDOW_TITLE; ?></h1>
<div id="filter_bar">
<table class="ui-widget" style="border-style:none;">
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo TEXT_FILTERS . '&nbsp;' . TEXT_SHOW_INACTIVE . '&nbsp;' . html_checkbox_field('f0', '1', $f0); ?></td>
	<td><?php echo '&nbsp;' . INV_ENTRY_INVENTORY_TYPE . '&nbsp;' . html_pull_down_menu('f1', $type_select_list, $f1, ''); ?></td>
<?php if ($account_type == 'v' && $contactID) {?>
	<td><?php echo '&nbsp;' . INV_HEADING_PREFERRED_VENDOR . '&nbsp;' . html_checkbox_field('f2', '1', $f2); ?></td>
<?php } ?>
	<td><?php echo '&nbsp;' . html_button_field('apply', TEXT_APPLY, 'onclick="submitToDo();"'); ?></td>
  </tr>
 </tbody>
</table>
</div>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?>
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
	  $display_stock = true;
	  if (strpos(COG_ITEM_TYPES, $query_result->fields['inventory_type']) === false) {
		$display_stock = false;
		$return_stock  = TEXT_NA;
	  } elseif (ENABLE_MULTI_BRANCH) {
	  	$store_stock  = load_store_stock($query_result->fields['sku'], $store_id);
	  }
	  switch ($account_type) {
		default:
		case 'c':
			$price = inv_calculate_sales_price(1, $query_result->fields['id'], 0, 'c');
			break;
		case 'v':
			$price = inv_calculate_sales_price(1, $query_result->fields['id'], 0, 'v');
			break;
	  }
	  $bkgnd = ($query_result->fields['inactive'] == 1) ? ' style="background-color:pink"' : '';
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer" onclick="setReturnItem(<?php echo $query_result->fields['id']; ?>)">
	<td<?php echo $bkgnd; ?>><?php echo $query_result->fields['sku']; ?></td>
	<td><?php echo $query_result->fields['description_short']; ?></td>
	<td align="right"><?php echo $currencies->precise($price['price']); ?></td>
	<td align="center"><?php echo ($display_stock) ? $query_result->fields['quantity_on_hand'] : '&nbsp;'; ?></td>
	<td align="center"><?php echo ($display_stock) ? $query_result->fields['quantity_on_order'] : '&nbsp;'; ?></td>
	<?php if (ENABLE_MULTI_BRANCH) echo '<td align="center">' . ($display_stock ? $store_stock : '&nbsp;') . '</td>' . chr(10); ?>
  </tr>
<?php
	  $query_result->MoveNext();
	  $odd = !$odd;
	}
?>
 </tbody>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?></div>
</form>
