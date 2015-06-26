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
//  Path: /modules/inventory/pages/main/template_tab_gen.php
//
?>
<div id="tab_general">

  <div id="inv_image" title="<?php echo $cInfo->sku; ?>">
    <?php if (isset($cInfo->image_with_path) && $cInfo->image_with_path) {
    	echo html_image(DIR_WS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $cInfo->image_with_path, '', 600) . chr(10);
    } else {
    	echo TEXT_NO_IMAGE;
    }
    ?>
    <div>
	  <h2><?php echo TEXT_SKU . ': ' . $cInfo->sku; ?></h2>
	  <p><?php echo '<br />' . $cInfo->description_sales; ?></p>
    </div>
  </div>

  <table class="ui-widget" style="border-style:none;width:100%">
    <tr><td>
    <table class="ui-widget" style="border-style:none;width:100%">
     <tbody class="ui-widget-content">
     <tr>
	  <td align="right"><?php echo TEXT_SKU; ?></td>
	  <td>
		<?php echo html_input_field('sku', $cInfo->sku, 'readonly="readonly" size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '"', false); ?>
		<?php echo TEXT_INACTIVE; ?>
		<?php echo html_checkbox_field('inactive', '1', $cInfo->inactive); ?>
	  </td>
	  <td align="right"><?php if(isset($cInfo->quantity_on_hand)) echo INV_QTY_ON_HAND; ?></td>
	  <td><?php if(isset($cInfo->quantity_on_hand)) echo html_input_field('quantity_on_hand', $currencies->precise($cInfo->quantity_on_hand), 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td rowspan="5" align="center">
		<?php if (isset($cInfo->image_with_path) && $cInfo->image_with_path) { // show image if it is defined
			echo html_image(DIR_WS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $cInfo->image_with_path, $cInfo->image_with_path, '', '100', 'onclick="showImage()"');
		} else echo '&nbsp;'; ?>
	  </td>
	</tr>
	<tr>
	  <td align="right"><?php echo INV_ENTRY_INVENTORY_DESC_SHORT; ?></td>
	  <td>
	  	<?php echo html_input_field('description_short', $cInfo->description_short, 'size="33" maxlength="32"', false); ?>
		<?php if ($cInfo->id) echo '&nbsp;' . html_icon('categories/preferences-system.png', TEXT_WHERE_USED, 'small', 'onclick="ajaxWhereUsed()"') . chr(10); ?>
	  </td>
	  <td align="right"><?php if(isset($cInfo->quantity_on_order)) echo INV_QTY_ON_ORDER; ?></td>
	  <td><?php if(isset($cInfo->quantity_on_order)) echo html_input_field('quantity_on_order', $currencies->precise($cInfo->quantity_on_order), 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->minimum_stock_level)) echo INV_ENTRY_ITEM_MINIMUM_STOCK; ?></td>
	  <td><?php if(isset($cInfo->minimum_stock_level)) echo html_input_field('minimum_stock_level', $currencies->precise($cInfo->minimum_stock_level), 'size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td align="right"><?php if(isset($cInfo->quantity_on_allocation)) echo INV_QTY_ON_ALLOCATION; ?></td>
	  <td><?php if(isset($cInfo->quantity_on_allocation)) echo html_input_field('quantity_on_allocation', $currencies->precise($cInfo->quantity_on_allocation), 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->reorder_quantity)) echo INV_ENTRY_ITEM_REORDER_QUANTITY; ?></td>
	  <td><?php if(isset($cInfo->reorder_quantity)) echo html_input_field('reorder_quantity', $currencies->precise($cInfo->reorder_quantity), 'size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td align="right"><?php if(isset($cInfo->quantity_on_sales_order)) echo INV_QTY_ON_SALES_ORDER; ?></td>
	  <td><?php if(isset($cInfo->quantity_on_sales_order)) echo html_input_field('quantity_on_sales_order', $currencies->precise($cInfo->quantity_on_sales_order), 'disabled="disabled" size="6" maxlength="5" style="text-align:right"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->lead_time)) echo INV_HEADING_LEAD_TIME; ?></td>
	  <td><?php if(isset($cInfo->lead_time)) echo html_input_field('lead_time', $cInfo->lead_time, 'size="6" maxlength="5" style="text-align:right"', false); ?></td>
	  <td align="right"><?php if(isset($cInfo->item_weight)) echo INV_ENTRY_ITEM_WEIGHT; ?></td>
	  <td><?php if(isset($cInfo->item_weight)) echo html_input_field('item_weight', $currencies->precise($cInfo->item_weight), 'size="11" maxlength="9" style="text-align:right"', false); ?></td>
	</tr>
	</tbody>
	</table>
	<?php if(in_array('sell',$cInfo->posible_transactions)){?>
    <table class="ui-widget" style="border-style:none;width:100%">
 	 <thead class="ui-widget-header">
	  <tr><th colspan="5"><?php echo TEXT_CUSTOMER_DETAILS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	<tr>
	  <td valign="top" align="right"><?php if(isset($cInfo->description_sales)) echo INV_ENTRY_INVENTORY_DESC_SALES; ?></td>
	  <td colspan="5"><?php if(isset($cInfo->description_sales)) echo html_textarea_field('description_sales', 75, 2, $cInfo->description_sales, '', $reinsert_value = true); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->full_price)) echo INV_ENTRY_FULL_PRICE; ?></td>
	  <td>
	  	<?php if(isset($cInfo->full_price)) echo html_input_field('full_price', $currencies->precise($cInfo->full_price), 'onchange="update_full_price_incl_tax(true, true, false);" size="15" maxlength="20" style="text-align:right"', false);
			if (isset($cInfo->full_price) && ENABLE_MULTI_CURRENCY) echo ' (' . DEFAULT_CURRENCY . ')';
			if (isset($cInfo->full_price)) echo '   <i>'.$currencies->precise($cInfo->full_price).'</i>';
		    if(isset($cInfo->price_sheet)) echo '&nbsp;' . html_icon('mimetypes/x-office-spreadsheet.png', BOX_SALES_PRICE_SHEETS, 'small', $params = 'onclick="priceMgr(' . $cInfo->id . ', 0, 0, \'c\', \'\')"') . chr(10); ?>
	  </td>
	  <td align="right"><?php if(isset($cInfo->full_price_with_tax)) echo INV_ENTRY_FULL_PRICE_WT; ?> </td>
	  <td><?php if(isset($cInfo->full_price_with_tax)) echo html_input_field('full_price_with_tax', $currencies->precise($cInfo->full_price_with_tax), 'onchange="update_full_price_incl_tax(true, false, true);" size="15" maxlength="20" style="text-align:right" ', false);
	  if (isset($cInfo->full_price_with_tax) && ENABLE_MULTI_CURRENCY) echo ' (' . DEFAULT_CURRENCY . ')';
	  if(isset($cInfo->full_price_with_tax)) echo '   <i>'.$cInfo->full_price_with_tax.'</i>';
	  ?>
	  </td>
	  <td align="right"><?php if(isset($cInfo->item_taxable)) echo INV_ENTRY_ITEM_TAXABLE; ?></td>
	  <td colspan="2"><?php if(isset($cInfo->item_taxable)) echo html_pull_down_menu('item_taxable', $tax_rates, $cInfo->item_taxable,'onchange="update_full_price_incl_tax(true, true, false);"'); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->product_margin)) echo INV_MARGIN; ?></td>
	  <td><?php if(isset($cInfo->product_margin)) echo html_input_field('product_margin', $currencies->precise($cInfo->product_margin), 'onchange="product_margin_change();" size="15" maxlength="5" style="text-align:right" ', false). '%';
	  if (isset($cInfo->product_margin)) echo '   <i>'.$currencies->precise($cInfo->product_margin).'%</i>'; ?>
	  </td>
	  <td align="right"><?php if(isset($cInfo->product_markup)) echo TEXT_RETAIL_MARKUP; ?></td>
	  <td><?php if(isset($cInfo->product_markup)) echo html_input_field('product_markup', $currencies->precise($cInfo->product_markup), 'onchange="product_markup_change();" size="15" maxlength="5" style="text-align:right" ', false). '';
	  if (isset($cInfo->product_markup)) echo '   <i>'.$currencies->precise($cInfo->product_markup).'</i>'; ?>
	  </td>
	  <td align="right"><?php if(isset($cInfo->price_sheet)) echo TEXT_DEFAULT_PRICE_SHEET; ?></td>
	  <td><?php if(isset($cInfo->price_sheet)) echo html_pull_down_menu('price_sheet', get_price_sheet_data('c'), $cInfo->price_sheet); ?></td>
	</tr>
	</tbody>
	</table>
	<?php }
	if ($_SESSION['admin_security'][SECURITY_ID_PURCHASE_INVENTORY] > 0 && in_array('purchase',$cInfo->posible_transactions)) { ?>
	<table class="ui-widget" style="border-collapse:collapse;width:100%;">
	 	<thead class="ui-widget-header">
	   		<tr><th colspan="7"><?php echo TEXT_VENDOR_DETAILS; ?></th></tr>
	   		<tr>
	   			<th width="5%"></th>
	   			<th><?php echo INV_HEADING_PREFERRED_VENDOR; ?></th>
	   			<th><?php echo INV_ENTRY_INVENTORY_DESC_PURCHASE; ?></th>
	   			<th><?php echo INV_ENTRY_INV_ITEM_COST; ?></th>
	   			<th><?php echo TEXT_PACKAGE_QUANTITY;?></th>
	   			<th><?php echo INV_ENTRY_PURCH_TAX;?></th>
	   			<th><?php echo TEXT_DEFAULT_PRICE_SHEET;?></th>
	   		</tr>
	 	</thead>
		<tbody id="vendor_table_tbody">
		<?php
		$odd = false;
		if(is_array($cInfo->purchase_array))foreach($cInfo->purchase_array as $purchaseRow){
			$i = rand();
			echo '<tr class="' . ($odd?'odd':'even') .'" id ="row_id_'.$i.'" >';
			echo '<td  width="5%">'.html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . INV_MSG_DELETE_VENDOR_ROW . '\')) removeVendorRow('.$i.');"').'</td>';
			echo html_hidden_field('row_id_array[]', $purchaseRow['id']);
			if(isset($cInfo->vendor_id) || $error ) {			echo '<td>'.html_pull_down_menu('vendor_id_array[]', gen_get_contact_array_by_type('v'), $purchaseRow['vendor_id']).'</td>';
			}else{ echo '<td></td>';}
			if(isset($cInfo->description_purchase) || $error ) { echo '<td>'.html_textarea_field('description_purchase_array[]', 75, 2, $purchaseRow['description_purchase'], '', $reinsert_value = true).'</td>';
			}else{ echo '<td></td>';}
			if(isset($cInfo->item_cost) || $error ){ 			echo '<td>'.html_input_field('item_cost_array[]', $currencies->precise($purchaseRow['item_cost']), 'onchange="what_to_update();" size="15" maxlength="20" style="text-align:right"', false).'</td>';
			}else{ echo '<td></td>';}
			if(isset($cInfo->item_cost) || $error ) {			echo '<td>'.html_input_field('purch_package_quantity_array[]', $currencies->precise($purchaseRow['purch_package_quantity']), 'size="6" maxlength="5" style="text-align:right"').'</td>';
			}else{ echo '<td></td>';}
			if(isset($cInfo->purch_taxable) || $error ){ 		echo '<td>'.html_pull_down_menu('purch_taxable_array[]', $purch_tax_rates, $purchaseRow['purch_taxable']).'</td>';
			}else{ echo '<td></td>';}
			if(isset($cInfo->price_sheet_v) || $error ) {		echo '<td>'.html_pull_down_menu('price_sheet_v_array[]', get_price_sheet_data('v'), $purchaseRow['price_sheet_v']);
																echo '&nbsp;' . html_icon('mimetypes/x-office-spreadsheet.png', BOX_SALES_PRICE_SHEETS, 'small', $params = 'onclick="priceMgr(' . $cInfo->id . ', 0, 0, \'v\', '.$purchaseRow['id'].')"') .'</td>';
			}else{ echo '<td></td>';}
			echo '</tr>';
			$odd = !$odd;
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td width="5%"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addVendorRow()"'); ?></td>
				<td  colspan="6"></td>
			</tr>
		</tfoot>
	</table>
	<?php } ?>
    <table class="ui-widget" style="border-style:none;width:100%">
 	 <thead class="ui-widget-header">
	  <tr><th colspan="5"><?php echo TEXT_ITEM_DETAILS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	<tr>
	  <td align="right"><?php echo INV_ENTRY_INVENTORY_TYPE; ?></td>
	  <td><?php echo html_hidden_field('inventory_type', $cInfo->inventory_type);
		echo html_input_field('inv_type_desc', $cInfo->title, 'readonly="readonly"', false); ?> </td>
		<td></td>
	  <td colspan="2"><?php if(isset($cInfo->image_with_path)) echo html_checkbox_field('remove_image', '1', $cInfo->remove_image) . ' ' . TEXT_REMOVE . ': ' . $cInfo->image_with_path; ?></td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->upc_code)) echo INV_HEADING_UPC_CODE; ?></td>
	  <td><?php if(isset($cInfo->upc_code)) echo html_input_field('upc_code', $cInfo->upc_code, 'size="16" maxlength="13" style="text-align:right"', false); ?></td>
	  <td align="right"><?php if(isset($cInfo->image_with_path)) echo INV_ENTRY_SELECT_IMAGE; ?></td>
	  <td colspan="2"><?php if(isset($cInfo->image_with_path)) echo html_file_field('inventory_image'); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php  if(!empty($cInfo->posible_cost_methodes)) echo INV_ENTRY_INVENTORY_COST_METHOD; ?></td>
	  <td>
		<?php foreach ($cost_methods as $key=>$value) if (in_array($key, $cInfo->posible_cost_methodes)) $cost_pulldown_array[] = array('id' => $key, 'text' => $value); ?>
		<?php if(!empty($cInfo->posible_cost_methodes)) echo html_pull_down_menu('cost_method', $cost_pulldown_array, $cInfo->cost_method, ($cInfo->last_journal_date == '0000-00-00 00:00:00' || is_null($cInfo->last_journal_date) ? '' : 'disabled="disabled"')); ?>
	    <?php if(isset($cInfo->serialize)) echo ' ' . INV_ENTRY_INVENTORY_SERIALIZE ; ?>
	  </td>
	  <td align="right"><?php if(isset($cInfo->image_with_path)) echo INV_ENTRY_IMAGE_PATH; ?></td>
	  <td colspan="2"><?php if(isset($cInfo->image_with_path)) echo html_hidden_field('image_with_path', $cInfo->image_with_path);
		if(isset($cInfo->image_with_path)) echo html_input_field('inventory_path', substr($cInfo->image_with_path, 0, strrpos($cInfo->image_with_path, '/'))); ?>
	  </td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->account_sales_income)) echo INV_ENTRY_ACCT_SALES; ?></td>
	  <td><?php if(isset($cInfo->account_sales_income)) echo html_pull_down_menu('account_sales_income', $gl_array_list, $cInfo->account_sales_income); ?></td>
	  <td rowspan="5" colspan="2">
	  <!--  *********************** Attachments  ************************************* -->
		   <fieldset>
		   <legend><?php echo TEXT_ATTACHMENTS; ?></legend>
 	   		<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
		    <thead class="ui-widget-header">
		     <tr><th colspan="3"><?php echo TEXT_ATTACHMENTS; ?></th></tr>
		    </thead>
		    <tbody class="ui-widget-content">
		     <tr><td colspan="3"><?php echo TEXT_SELECT_FILE_TO_ATTACH . ' ' . html_file_field('file_name'); ?></td></tr>
		     <tr  class="ui-widget-header">
		      <th><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
		      <th><?php echo TEXT_FILENAME; ?></th>
		      <th><?php echo TEXT_ACTION; ?></th>
		     </tr>
			<?php
				if (sizeof($cInfo->attachments) > 0) {
				  foreach ($cInfo->attachments as $key => $value) {
				    echo '<tr>';
				    echo ' <td>' . html_checkbox_field('rm_attach_'.$key, '1', false) . '</td>' . chr(10);
				    echo ' <td>' . $value . '</td>' . chr(10);
				    echo ' <td>' . html_button_field('dn_attach_'.$key, TEXT_DOWNLOAD, 'onclick="submitSeq(' . $key . ', \'download\', true)"') . '</td>';
				    echo '</tr>' . chr(10);
				  }
				} else {
				  echo '<tr><td colspan="3">' . TEXT_NO_DOCUMENTS . '</td></tr>';
				} ?>
		     </tbody>
		   </table>
		  </fieldset>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->account_inventory_wage)) echo INV_ENTRY_ACCT_INV; ?></td>
	  <td><?php if(isset($cInfo->account_inventory_wage)) echo html_pull_down_menu('account_inventory_wage', $gl_array_list, $cInfo->account_inventory_wage); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php if(isset($cInfo->account_cost_of_sales)) echo INV_ENTRY_ACCT_COS; ?></td>
	  <td><?php if(isset($cInfo->account_cost_of_sales)) echo html_pull_down_menu('account_cost_of_sales', $gl_array_list, $cInfo->account_cost_of_sales); ?></td>
	</tr>
	<tr></tr>
	<tr></tr>
	<tr></tr>
   </tbody>
   </table>

<?php if ( $cInfo->qty_table) { ?>
  </td>
  <td valign="top">
  <?php echo $cInfo->qty_table ?>
<?php } ?>
  </td></tr>
 </tbody>
 </table>
</div>
