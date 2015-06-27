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
//  Path: /modules/inventory/pages/main/template_main.php
//
echo html_form('inventory', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list', 'inventory_type'))) . chr(10);
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 1) $toolbar->add_icon('new', 'onclick="submitToDo(\'new\')"', $order = 10);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.04.01');
echo $toolbar->build_toolbar($add_search = true); 
?>
<h1><?php echo BOX_INV_MAINTAIN; ?></h1>
<table id="filter_table" class="ui-widget" style="border-collapse:collapse;">
  <thead class="ui-widget-header">
	<tr>
	  <th></th>
	  <th><?php echo FILTER_TABEL_HEAD_FIELD; ?></th>
	  <th><?php echo FILTER_TABEL_HEAD_COPAIRISON; ?></th>
	  <th> <?php echo FILTER_TABEL_HEAD_VALUE;?></th>
	</tr>
  </thead>
  <tbody id="filter_table_body" class="ui-widget-content">
	  <?php
	  if($_SESSION['filter_field']){
	  	foreach ($_SESSION['filter_field'] as $key => $value) {
			echo '<script type="text/javascript"> TableStartValues("' . $_SESSION['filter_field'][$key] . '","' . $_SESSION['filter_criteria'][$key] . '","' . $_SESSION['filter_value'][$key] . '");</script>'.chr(10);
	  	}
	  }else {
	  	echo'<script type="text/javascript"> TableStartValues("a.sku","0","");</script>'.chr(10);
	  }
	  ?>	
 </tbody>
 <tfoot>
 	<tr>
 		<td><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addFilterRow()"'); ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
 		<td style="text-align:right">
		<?php echo html_icon('actions/system-search.png', TEXT_SEARCH, 'medium', 'onclick="submitToDo(\'filter\')"') ?>
		<?php if($_SESSION['filter_field']) echo html_icon('actions/view-refresh.png', TEXT_RESET, 'small', 'onclick="location.href = \'index.php?' . gen_get_all_get_params(array('search_text', 'search_period', 'search_date', 'list', 'action')) . '&action=reset\';" style="cursor:pointer;"');?>
		</td>
 	</tr>
 </tfoot>
</table>
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
	  // only show quantity on hand if it is an inventory trackable item
	  $not_show_types = array('ns','lb','sv','sf','ci','ai','ds');
	  if (in_array($query_result->fields['inventory_type'], $not_show_types)) {
		$qty_in_stock = '';
	  } else {
		$qty_in_stock = $query_result->fields['quantity_on_hand'];
	  }
	  $bkgnd = ($query_result->fields['inactive']) ? ' style="background-color:pink"' : '';
?>
   <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td <?php echo $bkgnd; ?> onclick="submitSeq(<?php echo $query_result->fields['id'].', \'edit\''; ?>)"><?php echo $query_result->fields['sku']; ?></td>
	<td align="center"        onclick="submitSeq(<?php echo $query_result->fields['id'].', \'edit\''; ?>)"><?php echo ($query_result->fields['inactive'] == '1' ? TEXT_YES : ''); ?></td>
	<td                       onclick="submitSeq(<?php echo $query_result->fields['id'].', \'edit\''; ?>)"><?php echo $query_result->fields['description_short']; ?></td>
	<td align="center"        onclick="submitSeq(<?php echo $query_result->fields['id'].', \'edit\''; ?>)"><?php echo $qty_in_stock; ?></td>
	<td align="center"        onclick="submitSeq(<?php echo $query_result->fields['id'].', \'edit\''; ?>)"><?php echo $query_result->fields['quantity_on_sales_order']; ?></td>
	<td align="center"        onclick="submitSeq(<?php echo $query_result->fields['id'].', \'edit\''; ?>)"><?php echo $query_result->fields['quantity_on_allocation']; ?></td>
	<td align="center"        onclick="submitSeq(<?php echo $query_result->fields['id'].', \'edit\''; ?>)"><?php echo $query_result->fields['quantity_on_order']; ?></td>
	<td align="right">
<?php // build the action toolbar
	  if (function_exists('add_extra_action_bar_buttons')) echo add_extra_action_bar_buttons($query_result->fields);
	  if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 
	  'onclick="window.open(\'' . html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=main&amp;cID=' . $query_result->fields['id'] . '&amp;action=edit&amp;inventory_type='.$query_result->fields['inventory_type'], 'SSL')."','_blank')\""). chr(10); 
	  
	  if ($security_level > 3 && $query_result->fields['inventory_type'] <> 'mi' && $query_result->fields['inventory_type'] <> 'ia') echo html_icon('apps/accessories-text-editor.png', TEXT_RENAME, 'small', "onclick='renameItem({$query_result->fields['id']},\"{$query_result->fields['inventory_type']}\")'") . chr(10);
	  if ($security_level > 3 && $query_result->fields['inventory_type'] <> 'mi' && $query_result->fields['inventory_type'] <> 'ia' && ($query_result->fields['last_journal_date'] != '0000-00-00 00:00:00' || $query_result->fields['last_journal_date'] != '')) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', "onclick='if (confirm(\"" . INV_MSG_DELETE_INV_ITEM . "\")) deleteItem({$query_result->fields['id']},\"{$query_result->fields['inventory_type']}\")'") . chr(10);
	  if ($security_level > 1 && $query_result->fields['inventory_type'] <> 'mi' && $query_result->fields['inventory_type'] <> 'ia') echo html_icon('actions/edit-copy.png', TEXT_COPY, 'small', "onclick='copyItem({$query_result->fields['id']},\"{$query_result->fields['inventory_type']}\")'") . chr(10);
	  if ($security_level > 2) echo html_icon('mimetypes/x-office-spreadsheet.png', BOX_SALES_PRICE_SHEETS, 'small', "onclick='priceMgr({$query_result->fields['id']}, \"\",{$query_result->fields['full_price']}, \"c\")'") . chr(10);
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
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?></div>
</form>