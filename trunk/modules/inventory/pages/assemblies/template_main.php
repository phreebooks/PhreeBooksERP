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
//  Path: /modules/inventory/pages/assemblies/template_main.php
//
echo html_form('inv_assy', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
$hidden_fields = NULL;
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('id', $cInfo->id) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['params']   = 'onclick="OpenAssyList()"';
$toolbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . INV_MSG_DELETE_INV_ITEM . '\')) submitToDo(\'delete\')"';
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"', $order = 2);
if ($security_level < 4) $toolbar->icon_list['delete']['show'] = false;
if ($security_level < 2) $toolbar->icon_list['save']['show']   = false;
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
// add the help file index and build the toolbar
$toolbar->add_help('07.04.03.01');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
  <tr>
    <td valign="top"><table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
<?php if (ENABLE_MULTI_BRANCH) { ?>
     <tr>
	    <td><?php echo GEN_STORE_ID . '&nbsp;'; ?></td>
        <td align="right"><?php echo html_pull_down_menu('store_id', gen_get_store_ids(), $cInfo->store_id ? $cInfo->store_id : $_SESSION['admin_prefs']['def_store_id']); ?></td>
      </tr>
<?php } else $hidden_fields .= html_hidden_field('store_id', $_SESSION['admin_prefs']['def_store_id']) . chr(10); ?>
	  <tr>
		<td><?php echo TEXT_SKU; ?></td>
		<td align="right"><?php echo html_input_field('sku_1', $cInfo->sku_1, 'size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '" onfocus="clearField(\'sku_1\', \''.TEXT_SEARCH.'\')" onblur="setField(\'sku_1\', \''.TEXT_SEARCH.'\'); loadSkuDetails(0, 1)"');
			echo '&nbsp;' . html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="InventoryList(1)"') . chr(10); ?>
		</td>
	  </tr>
	  <tr>
		  <td colspan="2" align="right"><?php echo html_input_field('desc_1', $cInfo->desc_1, 'size="50" maxlength="50"'); ?></td>
	  </tr>
	  <tr>
		  <td><?php echo TEXT_REFERENCE; ?></td>
		  <td align="right"><?php echo html_input_field('purchase_invoice_id', $cInfo->purchase_invoice_id, 'size="21" maxlength="20"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo TEXT_POST_DATE; ?></td>
		<td align="right"><?php echo html_calendar_field($cal_assy); ?></td>
	  </tr>
	  <tr>
		<td><?php echo INV_QTY_ON_HAND; ?></td>
		<td align="right"><?php echo html_input_field('stock_1', $cInfo->stock_1, 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo INV_HEADING_QTY_TO_ASSY; ?></td>
		<td align="right"><?php echo html_input_field('qty_1', $cInfo->qty_1, 'style="text-align:right" size="13" maxlength="10" onchange="updateBalance()"'); ?></td>
	  </tr>
  	  <tr id="serial_row" style="display:none">
		<td><?php echo INV_HEADING_SERIAL_NUMBER; ?></td>
		<td align="right"><?php echo html_input_field('serial_1', $cInfo->serial_1, 'style="text-align:right" size="25" maxlength="24"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo TEXT_BALANCE; ?></td>
		<td align="right"><?php echo html_input_field('bal_1', $cInfo->bal_1, 'disabled="disabled" style="text-align:right" size="13"'); ?></td>
	  </tr>
	</table></td>
	<td>&nbsp;</td>
	<td align="center" valign="top"><?php echo INV_ASSY_PARTS_REQUIRED; ?>
	  <table class="ui-widget" style="border-collapse:collapse;width:600px;margin-left:auto;margin-right:auto;">
	    <thead class="ui-widget-header">
		  <tr>
			<th><?php echo TEXT_SKU; ?></th>
			<th><?php echo TEXT_DESCRIPTION; ?></th>
			<th nowrap="nowrap"><?php echo TEXT_NUM_REQUIRED; ?></th>
			<th nowrap="nowrap"><?php echo TEXT_NUM_AVAILABLE; ?></th>
		  </tr>
		</thead>
		<tbody id="item_table" class="ui-widget-content">
		  <?php $i = 1;
		    while(true) { // if the post failed, this will re-write the required items
		  	  if (!isset($_POST['assy_sku_' . $i])) break;
			  echo '<tr>' . chr(10);
			  echo html_hidden_field('qty_reqd_' . $i, $_POST['qty_reqd_' . $i]);
			  echo '  <td>' . html_input_field('assy_sku_'  . $i, $_POST['assy_sku_'  . $i], 'readonly="readonly" size="15"') . '</td>' . chr(10);
			  echo '  <td>' . html_input_field('assy_desc_' . $i, $_POST['assy_desc_' . $i], 'readonly="readonly" size="35"') . '</td>' . chr(10);
			  echo '  <td>' . html_input_field('assy_qty_'  . $i, $_POST['assy_qty_'  . $i], 'readonly="readonly" style="text-align:right" size="10"') . '</td>' . chr(10);
			  echo '  <td>' . html_input_field('stk_'       . $i, $_POST['stk_'       . $i], 'readonly="readonly" style="text-align:right" size="10"') . '</td>' . chr(10);
			  echo '</tr>' . chr(10);
			  $i++;
		    } 
		  ?>
		</tbody>
	    <tfoot>
		  <tr>
			<td>&nbsp;</td>
			<td align="right"><?php echo TEXT_TOTAL; ?></td>
			<td><?php echo html_input_field('total_needed','', 'readonly="readonly" size="10" style="text-align:right"'); ?></td>
			<td><?php echo html_input_field('total_stock', '', 'readonly="readonly" size="10" style="text-align:right"'); ?></td>
		  </tr>
		</tfoot>
	  </table>
	</td>
  </tr>
 </tbody>
</table>
<?php echo $hidden_fields; ?>
</form>
