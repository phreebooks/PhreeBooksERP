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
//  Path: /modules/amazon/pages/amazon/template_main.php
//
echo html_form('amazon', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \''.html_href_link(FILENAME_DEFAULT, '', 'SSL').'\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 1) $toolbar->add_icon('import', 'onclick="submitToDo(\'import\')"', $order = 14);
if ($security_level > 1) $toolbar->add_icon('export', 'onclick="submitToDo(\'ship_confirm\')"', $order = 15);
$toolbar->icon_list['import']['text']   = 'Import Amazon Orders from .txt File';
$toolbar->icon_list['export']['text']   = 'Shipment Confirmation from Selected';
$toolbar->add_help('07.03.05.04');
if ($search_text) $toolbar->search_text = $search_text;
$toolbar->search_period = $acct_period;
echo $toolbar->build_toolbar($add_search = true, $add_periods = true); 
// Build the page
?>
<h1><?php echo 'Amazon Import Tool'; ?></h1>
<table class="ui-widget" style="border-stylenone;width:100%">
 <tbody class="ui-widget-content">
  <tr>
    <td align="center"><?php echo 'Select order file to import from Amazon: ' . html_file_field('file_name'); ?></td>
    <td align="right"><?php echo 'Maximum results per page ' . html_pull_down_menu('pull_down_max', $display_length, $max_list, 'onchange="changePageResults();"'); ?></td>
  </tr>
  <tr>
    <td align="center">
	  <?php echo 'Select the date to build the Amazon ship confirmation on: ' . html_calendar_field($cal_pps) . ' '; ?>
	  <?php echo html_button_field('confirm', 'Build Confirmation File', 'onclick="submitToDo(\'ship_confirm\', true)"'); ?>
	</td>
	<td>&nbsp;</td>
  </tr>
</table>

<?php if ($so_query_result->RecordCount() > 0) { // only show if there are open sales orders ?>
<!-- Display open Sales orders -->
<h1><?php echo "Amazon Open Sales Orders"; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr>
    <th><?php   echo TEXT_DATE; ?></th>
    <th><?php   echo 'Order Number'; ?></th>
    <th><?php   echo 'Customer'; ?></th>
    <th><?php   echo 'Telephone'; ?></th>
    <th><?php   echo 'Email'; ?></th>
    <th><?php   echo 'Method'; ?></th>
    <th><?php   echo TEXT_ACTION; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
  <?php
    $odd = true;
	while (!$so_query_result->EOF) {
	  $oID = $so_query_result->fields['id'];
	  $purchase_invoice_id = $so_query_result->fields['purchase_invoice_id'];
	  $temp = explode(':', $so_query_result->fields['shipper_code']);
	  $shipper_code = ($temp[1] == 'GND') ? 'Standard' : 'Expedited';
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>">
	<td><?php echo gen_locale_date($so_query_result->fields['post_date']); ?></td>
	<td><?php echo $purchase_invoice_id; ?></td>
	<td><?php echo $so_query_result->fields['ship_primary_name']; ?></td>
	<td><?php echo $so_query_result->fields['ship_telephone1']; ?></td>
	<td><?php echo $so_query_result->fields['ship_email']; ?></td>
	<td><?php echo $shipper_code; ?></td>
	<td align="right">
<?php
	echo html_icon('actions/document-print.png', TEXT_PRINT, 'small', 'onclick="printSalesOrder(' . $oID . ')"') . chr(10);
	echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID=' . $oID . '&amp;jID=10&amp;action=edit', 'SSL') . '\';"') . chr(10);
?>
	</td>
  </tr>
<?php
	  $so_query_result->MoveNext();
	  $odd = !$odd;
	}
?>
 </tbody>
</table>
<?php } // end only show if there are open sales orders ?>

<h1><?php echo "Amazon Invoice Status"; ?></h1>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . ORD_TEXT_12_WINDOW_TITLE); ?></div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php  echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
  $odd = true;
	while (!$query_result->EOF) {
	  $oID = $query_result->fields['id'];
	  $purchase_invoice_id = $query_result->fields['purchase_invoice_id'];
	  $result = $db->Execute("select id, shipment_id, amazon_confirm from " . TABLE_SHIPPING_LOG . " 
	  	where ref_id like '" . $purchase_invoice_id . "%'");
	  if ($result->RecordCount() > 0) {
	    $sID = $result->fields['id'];
	    $shipped = $result->fields['shipment_id'];
	  } else {
	    $sID = 0;
	    $shipped = false;
	  }
	  $temp = explode(':', $query_result->fields['shipper_code']);
	  $shipper_code = $temp[0];
	  $ship_method = $temp[1];
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>">
	<td><?php echo gen_locale_date($query_result->fields['post_date']); ?></td>
	<td><?php echo $purchase_invoice_id; ?></td>
	<td><?php echo $query_result->fields['purch_order_id']; ?></td>
	<td><?php echo $query_result->fields['ship_primary_name']; ?></td>
	<td><?php echo $shipper_code; ?></td>
	<td><?php echo (($shipped) ? $ship_method : ''); ?></td>
	<td><?php echo (($shipped) ? TEXT_YES : ($temp[0] ? '' : ORD_NA)); ?></td>
	<td><?php echo (($result->fields['amazon_confirm']) ? TEXT_YES : ''); ?></td>
	<td align="right">
<?php
		if ($shipper_code <> '' && $shipped) {
  			echo html_icon('phreebooks/stock_id.png', TEXT_VIEW_SHIP_LOG, 'small', 'onclick="loadPopUp(\'' . $shipper_code . '\', \'edit\', ' . $sID . ')"') . chr(10);
		} else {
  			echo html_icon('mimetypes/text-x-generic.png', 'Create Ship Log Entry', 'small', 'onclick="window.open(\'index.php?module=shipping&amp;page=popup_tracking&amp;action=new\',\'popup_tracking\',\'width=550,height=350,resizable=1,scrollbars=1,top=150,left=200\')"') . chr(10);
		}
	  	echo html_icon('actions/document-print.png', TEXT_PRINT, 'small', 'onclick="printOrder(' . $oID . ')"') . chr(10);
	    echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID=' . $oID . '&amp;jID=12&amp;action=edit', 'SSL') . '\';"') . chr(10);
	    echo html_checkbox_field('oID[' . $oID . ']', '1', ($checked['oID'] ? true : false),'', (!$shipped) ? 'disabled' : '') . chr(10);
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
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . ORD_TEXT_12_WINDOW_TITLE); ?></div>
</form>