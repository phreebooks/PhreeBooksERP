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
//  Path: /modules/phreebooks/pages/status/template_main.php
//
echo html_form('status', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action',   '') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;

$toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=' . JOURNAL_ID, 'SSL') . '\'"', 2);

if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
switch (JOURNAL_ID) {
  case  2: 
	$toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=journal', 'SSL') . '\'"', 2);
  	$toolbar->add_help('');
  	break;
  case  3: $toolbar->add_help('07.02.03.04'); break;
  case  4: $toolbar->add_help('07.02.03.04'); break;
  case  6: $toolbar->add_help('07.02.03.04'); break;
  case  7: $toolbar->add_help('07.02.03.04'); break;
  case  9: $toolbar->add_help('07.03.03.04'); break;
  case 10: $toolbar->add_help('07.03.03.04'); break;
  case 12: $toolbar->add_help('07.03.03.04'); break;
  case 13: $toolbar->add_help('07.03.03.04'); break;
  case 18: 
  	$toolbar->add_icon('new', 'onclick="location.href=\''.html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;type=c&amp;jID='.JOURNAL_ID, 'SSL') . '\'"', 2);
  	$toolbar->add_help('');
  	break;
  case 20: 
  	$toolbar->add_icon('new', 'onclick="location.href=\''.html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;type=v&amp;jID='.JOURNAL_ID, 'SSL') . '\'"', 2);
  	$toolbar->add_help('');
  	break;
}
$toolbar->search_period = $_REQUEST['search_period'];
echo $toolbar->build_toolbar($add_search = true, $add_periods = true); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<?php if (ENABLE_MULTI_BRANCH) {?>
<div id="filter_bar">
<table class="ui-widget" style="border-style:none;">
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo GEN_STORE_ID . '&nbsp;' . html_pull_down_menu('store_id', gen_get_store_ids(), isset($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $_SESSION['admin_prefs']['def_store_id'], 'onchange="submitToDo(\'filter\')"'); ?></td>
  </tr>
 </tbody>
</table>
</div>
<?php } //end multi branch?>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE')); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header; ?></tr>
 </thead>
 <tbody>
<?php
  $odd = true;
  while (!$query_result->EOF) {
	$oID            = $query_result->fields['id'];
	$post_date      = gen_locale_date($query_result->fields['post_date']);
	$reference_id   = htmlspecialchars($query_result->fields['purchase_invoice_id']);
	$primary_name   = htmlspecialchars($query_result->fields['bill_primary_name']);
	$purch_order_id = htmlspecialchars($query_result->fields['purch_order_id']);
	$closed         = $query_result->fields['closed'] ? TEXT_YES : '';
	$total_amount   = $currencies->format_full($query_result->fields['total_amount'], true, $query_result->fields['currencies_code'], $query_result->fields['currencies_value']);
	if (ENABLE_MULTI_CURRENCY) $total_amount .= ' (' . $query_result->fields['currencies_code'] . ')';
	$bkgnd          = $query_result->fields['waiting'] ? ' style="background-color:lightblue"' : '';
	$attach_exists  = file_exists(PHREEBOOKS_DIR_MY_ORDERS . 'order_' . $oID . '.zip') ? true : false;
	switch (JOURNAL_ID) {
	  case  2:
	    $link_page = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=journal&amp;oID=' . $oID . '&amp;jID=' . JOURNAL_ID . '&amp;action=edit', 'SSL');
	    break; 
	  case  3: 
	  case  4: 
	  case  6: 
	  case  7: 
	  case  9: 
	  case 10: 
	  case 12: 
	  case 13: 
	    $link_page = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID=' . $oID . '&amp;jID=' . JOURNAL_ID . '&amp;action=edit', 'SSL');
	    break;
	  case 18: 
	    $link_page = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;oID=' . $oID . '&amp;jID=' . JOURNAL_ID . '&amp;type=c&amp;action=edit', 'SSL');
	    break;
	  case 20: 
	    $link_page = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;oID=' . $oID . '&amp;jID=' . JOURNAL_ID . '&amp;type=v&amp;action=edit', 'SSL');
	    break;
	}
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $post_date; ?></td>
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $reference_id; ?></td>
	<td<?php echo $bkgnd; ?> onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $primary_name; ?></td>
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $purch_order_id; ?></td>
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $closed; ?></td>
	<td align="right" onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $total_amount; ?></td>
<?php if (defined('MODULE_SHIPPING_STATUS') && JOURNAL_ID == 12) { 
      $sID            = 0;
      $shipped        = false;
	  $result = $db->Execute("select id, shipment_id, ship_date from " . TABLE_SHIPPING_LOG . " where ref_id like '" . $query_result->fields['purchase_invoice_id'] . "%'");
	  if ($result->RecordCount() > 0) {
	    $sID          = $result->fields['id'];
	    $shipped      = $result->fields['shipment_id'];
	    $date_shipped = substr($result->fields['ship_date'], 0, 10);
	  }
	  $temp           = explode(':', $query_result->fields['shipper_code']);
	  $shipper_code   = $temp[0];
	  $ship_meth      = defined('MODULE_SHIPPING_' . strtoupper($temp[0]) . '_TITLE_SHORT') ? constant('MODULE_SHIPPING_' . strtoupper($temp[0]) . '_TITLE_SHORT') : $temp[0];
	  $ship_srv       = defined($temp[0].'_'.$temp[1]) ? constant($temp[0].'_'.$temp[1]) : $temp[1];
?>
	<td align="center" onclick="window.open('<?php echo html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID=' . $oID . '&amp;jID=12&amp;action=edit', 'SSL'); ?>','_blank')"><?php echo $ship_meth ? ($ship_meth . ' ' . $ship_srv) : '&nbsp;'; ?></td>
<?php } // end MODULE_SHIPPING_STATUS ?>
	<td align="right">
<?php // build the action toolbar
	if (function_exists('add_extra_action_bar_buttons')) echo add_extra_action_bar_buttons($query_result->fields);
	switch (JOURNAL_ID) {
	  case  2:
	    break; 
	  case  3: 
	    echo html_icon('actions/system-shutdown.png',   TEXT_TOGGLE, 'small', 'onclick="submitSeq(' . $oID . ', \'toggle\')"') . chr(10);
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	  case  4: 
		if (!$query_result->fields['closed']) echo html_button_field('invoice_' . $oID, TEXT_RECEIVE, 'onclick="window.open(\'' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID=' . $oID . '&amp;jID=6&amp;action=prc_so', 'SSL') . '\',\'_blank\')"') . chr(10);
		echo html_button_field('delivery_' . $oID, ORD_DELIVERY_DATES, 'onclick="deliveryList(' . $oID . ')"') . chr(10);
	    echo html_icon('actions/system-shutdown.png',   TEXT_TOGGLE, 'small', 'onclick="submitSeq(' . $oID . ', \'toggle\')"') . chr(10);
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	  case  6: 
	    break;
	  case  7: 
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	  case  9: 
	    echo html_icon('actions/system-shutdown.png',   TEXT_TOGGLE, 'small', 'onclick="submitSeq(' . $oID . ', \'toggle\')"') . chr(10);
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	  case 10: 
		if (!$query_result->fields['closed']) echo html_button_field('invoice_' . $oID, TEXT_INVOICE, 'onclick="window.open(\'' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;oID=' . $oID . '&amp;jID=12&amp;action=prc_so', 'SSL') . '\',\'_blank\')"') . chr(10);
		echo html_button_field('delivery_' . $oID, ORD_DELIVERY_DATES, 'onclick="deliveryList(' . $oID . ')"') . chr(10);
	    echo html_icon('actions/system-shutdown.png',   TEXT_TOGGLE, 'small', 'onclick="submitSeq(' . $oID . ', \'toggle\')"') . chr(10);
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	  case 12:
	    if (defined('MODULE_SHIPPING_STATUS') && $shipper_code) {
		  if ($sID) {
		    if ($date_shipped == $date_today) echo html_icon('phreebooks/void-truck-icon.png', ORD_VOID_SHIP, 'small', 'onclick="if (confirm(\'Are you sure you want to delete this shipment?\')) voidShipment('.$shipped.', \''.$shipper_code.'\')"').chr(10);
	  	    echo html_icon('phreebooks/stock_id.png', TEXT_VIEW_SHIP_LOG, 'small', 'onclick="loadPopUp(\'' . $shipper_code . '\', \'edit\', ' . $sID . ')"') . chr(10);
		  } elseif (!$shipped) {
		    echo html_icon('phreebooks/truck-icon.png', TEXT_SHIP, 'small', 'onclick="shipList(' . $oID . ', \'' . $shipper_code . '\')"') . chr(10);
		  }
	    }
	    if (!$closed) echo html_icon('apps/accessories-calculator.png', TEXT_PAYMENT, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;type=c&amp;jID=18&amp;oID=' . $oID . '&amp;action=pmt', 'SSL') . '\';"') . chr(10);
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
		break;
	  case 13: 
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	  case 18: 
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	  case 20: 
		echo html_icon('actions/document-print.png',    TEXT_PRINT,  'small', 'onclick="printOrder('. $oID . ')"') . chr(10);
	    break;
	}
	if ($attach_exists) {
	  echo html_icon('status/mail-attachment.png', TEXT_DOWNLOAD_ATTACHMENT,'small', 'onclick="submitSeq(' . $oID . ', \'dn_attach\', true)"') . chr(10);
	}
	echo html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="window.open(\'' . $link_page . '\',\'_blank\')"') . chr(10);
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
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE')); ?></div>
</form>
