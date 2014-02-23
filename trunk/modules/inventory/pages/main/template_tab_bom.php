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
//  Path: /modules/inventory/pages/main/template_tab_bom.php
//
// start the bill of materials tab html
?>
<div title="<?php echo INV_BOM;?>" id="tab_bom">
 <div style="width:850px;margin-left:auto;margin-right:auto">
  <div>
   <table class="ui-widget" style="border-collapse:collapse;width:100%">
    <thead class="ui-widget-header">
	 <tr>
	  <th></th>
	  <th><?php echo TEXT_SKU; ?></th>
	  <th><?php echo INV_ENTRY_INVENTORY_DESC_SHORT; ?></th>
	  <th><?php echo TEXT_QUANTITY; ?></th>
	  <th><?php echo INV_ENTRY_INV_ITEM_COST; ?></th>
	  <th><?php echo INV_ENTRY_FULL_PRICE; ?></th>
	 </tr>
    </thead>
    <tbody id="bom_table_body" class="ui-widget-content">
<?php
	if (count($cInfo->bom)) {
		for ($j = 0, $i = 1; $j < count($cInfo->bom); $j++, $i++) {
			$readonly = '';
			echo '    <tr>';
			echo '      <td>';
			if ($cInfo->allow_edit_bom) {
				echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();bomTotalValues();"');
			} else {
				echo '&nbsp;';
				$readonly = 'readonly="readonly" ';
			}
			echo '      </td>' . chr(10);
			echo '      <td nowrap="nowrap">';
			// Hidden fields
			echo '      	<input type="hidden" name="id_' . $i . '" id="id_' . $i . '" value="' . $cInfo->bom[$j]['id'] . '" />' . chr(10);
			// End hidden fields
			echo '			<input type="text" name="assy_sku[]" id="sku_' . $i . '" value="' . $cInfo->bom[$j]['sku'] . '" ' . $readonly . 'size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '" onchange="bom_guess(' . $i . ');"  />&nbsp;' . chr(10);
			if ($cInfo->allow_edit_bom) echo html_icon('actions/system-search.png', TEXT_SKU, 'small', $params = 'align="top" style="cursor:pointer" onclick="InventoryList(' . $i . ')"') . chr(10);
			echo '      </td>' . chr(10);
			echo '      <td><input type="text" name="assy_desc[]" 		 id="desc_' . $i . '" 		 value="' . $cInfo->bom[$j]['description'] . '" ' . $readonly . 'size="64" maxlength="64" /></td>' . chr(10);
			echo '      <td><input type="text" name="assy_qty[]" 		 id="qty_' . $i . '" 		 value="' . $currencies->precise($cInfo->bom[$j]['qty']) . '" 		' . $readonly . 'size="6" maxlength="5" /></td>' . chr(10);
			echo '      <td><input type="text" name="assy_item_cost[]" 	 id="item_cost_' . $i . '" 	 value="' . $currencies->precise($cInfo->bom[$j]['item_cost']) . '" 	' . $readonly . 'size="6" maxlength="5" /></td>' . chr(10);
			echo '      <td><input type="text" name="assy_sales_price[]" id="sales_price_' . $i . '" value="' . $currencies->precise($cInfo->bom[$j]['full_price']) . '" 	' . $readonly . 'size="6" maxlength="5" /></td>' . chr(10);
			echo '    </tr>';
		}
	} else {
		echo '<script language="JavaScript">addBOMRow();</script>';
	}
?>
     </tbody>
     <tfoot>
		  <tr>
			<td><?php if ($cInfo->allow_edit_bom) { // show add button if no posting have been made
						echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addBOMRow()"');
					} else { echo '&nbsp;'; } ?>
			</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="right"><?php echo TEXT_TOTAL; ?></td>
			<td><?php echo html_input_field('total_item_cost','', 'readonly="readonly" size="10" style="text-align:right"'); ?></td>
			<td><?php echo html_input_field('total_sales_price', '', 'readonly="readonly" size="10" style="text-align:right"'); ?></td>
		  </tr>
	</tfoot>
   </table>
  </div>
 </div>
</div>
<?php echo '<script language="JavaScript">bomTotalValues();</script>';?>