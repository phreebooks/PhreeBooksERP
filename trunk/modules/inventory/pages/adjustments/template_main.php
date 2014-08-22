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
//  Path: /modules/inventory/pages/adjustments/template_main.php
//
echo html_form('inv_adj', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
$hidden_fields = NULL;
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('id', $cInfo->id) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['params']   = 'onclick="OpenAdjList()"';
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . INV_ADJ_DELETE_ALERT . '\')) submitToDo(\'delete\')"';
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"', $order = 2);
if ($security_level < 4) $toolbar->icon_list['delete']['show'] = false;
if ($security_level < 2) $toolbar->icon_list['save']['show']   = false;
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
// add the help file index and build the toolbar
$toolbar->add_help('07.04.02.01');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div>
  <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
   <tbody>
<?php if (ENABLE_MULTI_BRANCH) { ?>
	<tr>
	  <td><?php echo GEN_STORE_ID . '&nbsp;' .  html_pull_down_menu('store_id', gen_get_store_ids(), $cInfo->store_id ? $cInfo->store_id : $_SESSION['admin_prefs']['def_store_id']); ?></td>
	</tr>
<?php } else $hidden_fields .= html_hidden_field('store_id', $_SESSION['admin_prefs']['def_store_id']) . chr(10); ?>
	<tr>
	  <td align="right"><?php echo INV_REASON_FOR_ADJUSTMENT; ?></td>
	  <td><?php echo html_input_field('adj_reason', $cInfo->adj_reason, 'size="50"'); ?></td>
	  <td align="right"><?php echo TEXT_POST_DATE . '&nbsp;'; ?></td>
	  <td><?php echo html_calendar_field($cal_adj); ?></td>
	</tr>
	<tr>
	  <td colspan="2"><?php echo INV_ADJUSTMENT_ACCOUNT . '&nbsp;' . html_pull_down_menu('gl_acct', $gl_array_list, $cInfo->gl_acct ? $cInfo->gl_acct : INV_STOCK_DEFAULT_INVENTORY, ''); ?></td>
	  <td align="right"><?php echo TEXT_REFERENCE; ?></td>
	  <td><?php echo html_input_field('purchase_invoice_id', $cInfo->purchase_invoice_id); ?></td>
	</tr>
   </tbody>
  </table>
</div>
<div>
  <table class="ui-widget" style="margin-left:auto;margin-right:auto;">
	<thead class="ui-widget-header">
	<tr>
	  <th><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
	  <th><?php echo TEXT_SKU; ?></th>
	  <th><?php echo INV_HEADING_QTY_IN_STOCK; ?></th>
	  <th><?php echo INV_ADJ_QUANTITY; ?></th>
	  <th><?php echo TEXT_ITEM_COST; ?></th>
	  <th><?php echo TEXT_BALANCE; ?></th>
	  <th><?php echo TEXT_DESCRIPTION; ?></th>
	</tr>
	</thead>
	<tbody id="item_table" class="ui-widget-content">
<?php
if (!$error) {
  $hidden_fields .= '<script type="text/javascript">addInvRow();</script>';
} else {
  $rowCnt = 1;
  while (true) {
	if (!isset($_POST['sku_'.$rowCnt])) break;
	echo '  <tr>'   . chr(10);
	echo '    <td>' . html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . INV_MSG_DELETE_INV_ITEM . '\')) removeInvRow(' . $rowCnt . ');"') . '</td>' . chr(10);
	echo '    <td nowrap="nowrap">' . chr(10);
	echo html_input_field('sku_' . $rowCnt, $_POST['sku_'.$rowCnt], 'size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '" onfocus="clearField(\'sku_'.$rowCnt.'\', \''.TEXT_SEARCH.'\')" onblur="setField(\'sku_'.$rowCnt.'\', \''.TEXT_SEARCH.'\'); onkeydown="checkEnterEvent(event,' . $rowCnt . '); loadSkuDetails(0, '.$rowCnt.',0)"') . '&nbsp;';
	echo html_icon('actions/system-search.png', TEXT_SEARCH, 'small', $params = 'align="top" style="cursor:pointer" onclick="InventoryList('.$rowCnt.')"');
	// for serialized items, show the icon IF the item type is serial
	$invType = $db->Execute("SELECT inventory_type FROM ".TABLE_INVENTORY." WHERE sku='{$_POST['sku_'.$rowCnt]}'");
	$imgSerialView = in_array($invType->fields['inventory_type'], array('sr','sa')) ? "" : "display:none;";
	echo html_icon('actions/tab-new.png', TEXT_SERIAL_NUMBER, 'small', 'id="imgSerial_'.$rowCnt.'" align="top" style="cursor:pointer;'.$imgSerialView.'" onclick="serialList(\'serial_'.$rowCnt.'\')"');
// Hidden fields
	echo html_hidden_field('serial_' . $rowCnt, $_POST['serial_'.$rowCnt]) . chr(10);
	echo html_hidden_field('acct_' . $rowCnt, $_POST['acct_'.$rowCnt]) . chr(10);
	echo html_hidden_field('def_cost_' . $rowCnt, $_POST['def_cost_'.$rowCnt]) . chr(10);
// End hidden fields
	echo '    </td>'. chr(10);
	echo '    <td>' . html_input_field('stock_' . $rowCnt, $_POST['stock_'.$rowCnt], 'readonly="readonly" size="6" maxlength="5" style="text-align:right"') . '</td>' . chr(10);
	echo '    <td>' . html_input_field('qty_' . $rowCnt, $_POST['qty_'.$rowCnt], 'size="6" maxlength="5" style="text-align:right" onchange="updateBalance()"') . '</td>' . chr(10);
	echo '    <td>' . html_input_field('price_' . $rowCnt, $currencies->format($_POST['price_'.$rowCnt]), 'size="10" maxlength="9" style="text-align:right"') . '</td>' . chr(10);
	echo '    <td>' . html_input_field('balance_' . $rowCnt, $_POST['balance_'.$rowCnt], 'readonly="readonly" size="6" maxlength="5" style="text-align:right"') . '</td>' . chr(10);
	echo '    <td>' . html_input_field('desc_' . $rowCnt, $_POST['desc_'.$rowCnt], 'size="90"') . '</td>' . chr(10);
	echo '  </tr>'  . chr(10);
	$rowCnt++;
  }
} ?>
	</tbody>
	<tfoot>
	  <tr>
	    <td><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addInvRow()"'); ?></td>
	  </tr>
	</tfoot>
  </table>
  <p><?php echo JS_COGS_AUTO_CALC; ?></p>
</div>
<?php echo $hidden_fields; // display the hidden fields that are not used in this rendition of the form ?>
</form>
