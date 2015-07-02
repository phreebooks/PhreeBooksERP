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
//  Path: /modules/inventory/pages/popup_prices/template_main.php
//
echo html_form('prices', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
if (!ENABLE_MULTI_CURRENCY) echo html_hidden_field('display_currency', DEFAULT_CURRENCY);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.04.06');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE . ' - ' . $sku; ?></h1>
<div style="float:right"><?php echo (ENABLE_MULTI_CURRENCY) ? (TEXT_CURRENCY . ' ' . html_input_field('display_currency', DEFAULT_CURRENCY, 'readonly="readonly" size="4"')) : ''; ?></div>
<table class="ui-widget" style="border-style:none;width:300px">
 <tbody class="ui-widget-content">
  <tr>
    <td colspan="2"><?php echo $inventory_details->fields['description_short']; ?></td>
  </tr>
<?php if ($type == 'v') { ?>
  <tr onclick="setReturnPrice('<?php echo $rowId; ?>', 'cost');">
    <td style="cursor:pointer"><?php echo INV_ENTRY_INV_ITEM_COST . ': '; ?></td>
    <td style="cursor:pointer"><?php echo html_input_field('cost', $currencies->precise($inventory_details->fields['item_cost']), 'style="cursor:pointer" readonly="readonly" size="10" style="text-align:right"'); ?></td>
  </tr>
<?php } else { ?> 
  <tr onclick="setReturnPrice('<?php echo $rowId; ?>', 'full');">
    <td style="cursor:pointer"><?php echo INV_ENTRY_FULL_PRICE . ': '; ?></td>
    <td style="cursor:pointer"><?php echo html_input_field('full', $currencies->precise($inventory_details->fields['full_price']), 'style="cursor:pointer" readonly="readonly" size="10" style="text-align:right"'); ?></td>
  </tr>
<?php } ?> 
 </tbody>
</table>
<?php
if (is_object($price_sheets)) {
  if ($price_sheets->RecordCount() > 0) {
    $cnt = 0;
	while (!$price_sheets->EOF) { 
	  echo '<table class="ui-widget" style="border-collapse:collapse;width:300px">';
 	  echo '<thead class="ui-widget-header">';
	  echo '<tr><th colspan="2">' . PRICE_SHEETS_LOG . $price_sheets->fields['sheet_name'] . '</th></tr>';
 	  echo '<tr><th>' . TEXT_QUANTITY . '</th><th align="center">' . TEXT_PRICE . '</th></tr>';
	  echo ' </thead><tbody class="ui-widget-content">' . chr(10);
 	  // remove the first and last element from the price level source array (not used and Level 1 price source)
	  $first_source_list = $price_mgr_sources;
	  array_shift($first_source_list);
	  array_pop($first_source_list);
	  // extract the pricing information
	  $levels = isset($special_prices[$price_sheets->fields['id']]) ? $special_prices[$price_sheets->fields['id']] : $price_sheets->fields['default_levels'];
	  $prices = inv_calculate_prices($inventory_details->fields['item_cost'], $inventory_details->fields['full_price'], $levels);
	  foreach ($prices as $price_level) {
		echo '<tr onclick="setReturnPrice(\'' . $rowId . '\', \'price_' . $cnt . '\')">';
		echo '<td style="cursor:pointer" align="center">' . $price_level['qty'] . '</td>';
		echo '<td style="cursor:pointer" align="right">' . html_input_field('price_' . $cnt, $price_level['price'], 'style="cursor:pointer" readonly="readonly" size="10" style="text-align:right"') . '</td></tr>';
		$cnt++;
	  }
      $price_sheets->MoveNext();
	  echo '</tbody></table>';
	}
	echo html_hidden_field('num_prices', $cnt);
  } 
} else {
  echo '<div align="center"><p>' . INV_NO_PRICE_SHEETS . '</p></div>';
} // end if ($price_sheets->RecordCount() > 0) 
?>
</form>