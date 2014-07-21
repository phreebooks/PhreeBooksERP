<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008-2014 PhreeSoft                               |
// | http://www.PhreeSoft.com                                        |
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
//  Path: /modules/shipping/methods/ups/ship_mgr.php
//
?>
<div align="center" class="pageHeading"><?php echo constant('MODULE_SHIPPING_' . strtoupper($method_id) . '_TEXT_TITLE'); ?></div>
<table width="95%">
  <tr>
    <td>
	 <table>
	  <tr>
		<td><?php echo SRV_SHIP_UPS . ' => ' . html_button_field('ship_ups', SRV_SHIP_UPS, 'onclick="window.open(\'index.php?module=shipping&amp;page=popup_label_mgr&amp;method=ups\',\'popup_label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SHIPPING_BUTTON_CREATE_LOG_ENTRY . ' => ' . html_button_field('ship_log', SHIPPING_BUTTON_CREATE_LOG_ENTRY, 'onclick="window.open(\'index.php?module=shipping&amp;page=popup_tracking&amp;method=ups&amp;action=new\',\'popup_tracking\',\'width=550,height=350,resizable=1,scrollbars=1,top=150,left=200\')"') . chr(10); ?></td>
	  </tr>
	 </table>
	</td>
	<td>
	 <table>
	  <tr>
		<th colspan="2"><?php echo SHIPPING_UPS_VIEW_REPORTS . gen_locale_date($date); ?></th>
	  </tr>
	  <tr>
		<td><?php echo SHIPPING_UPS_CLOSE_REPORTS; ?></td>
		<td align="right"><?php echo html_submit_field('close_ups', TEXT_VIEW, ''); ?></td>
	  </tr>
	 </table>
	</td>
  </tr>
</table>
<table width="95%" border="1" cellspacing="1" cellpadding="1">
  <tr>
    <th colspan="8"><?php echo SHIPPING_UPS_SHIPMENTS_ON . gen_locale_date($date); ?></th>
  </tr>
  <tr>
	<th><?php echo SHIPPING_TEXT_SHIPMENT_ID; ?></th>
	<th><?php echo SHIPPING_TEXT_REFERENCE_ID; ?></th>
	<th><?php echo SHIPPING_TEXT_SERVICE; ?></th>
	<th><?php echo SHIPPING_TEXT_EXPECTED_DATE; ?></th>
	<th><?php echo SHIPPING_TEXT_ACTUAL_DATE; ?></th>
	<th><?php echo SHIPPING_TEXT_TRACKING_NUM; ?></th>
	<th><?php echo SHIPPING_TEXT_COST; ?></th>
	<th><?php echo TEXT_ACTION; ?></th>
  </tr>
	<?php 
	$result = $db->Execute("select id, shipment_id, ref_id, method, deliver_date, actual_date, tracking_id, cost 
		from " . TABLE_SHIPPING_LOG . " where carrier = 'ups' and ship_date like '" . $date . "%'");
	if ($result->RecordCount() > 0) {
		while(!$result->EOF) {
			echo '  <tr>' . chr(10);
			echo '    <td class="dataTableContent" align="right">' . $result->fields['shipment_id'] . '</td>' . chr(10);
			echo '    <td class="dataTableContent" align="right">' . $result->fields['ref_id'] . '</td>' . chr(10);
			echo '    <td class="dataTableContent" align="center">' . constant('ups_' . $result->fields['method']) . '</td>' . chr(10);
			echo '    <td class="dataTableContent" align="right">' . ($result->fields['deliver_date']<>'0000-00-00' ? gen_locale_date($result->fields['deliver_date']) : '&nbsp;') . '</td>' . chr(10);
			echo '    <td class="dataTableContent" align="right">' . ($result->fields['actual_date']<>'0000-00-00' ? gen_locale_date($result->fields['actual_date']) : '&nbsp;') . '</td>' . chr(10);
			echo '    <td class="dataTableContent" align="right"><a target="_blank" href="' . UPS_TRACKING_URL . $result->fields['tracking_id'] . '">' . $result->fields['tracking_id'] . '</a></td>' . chr(10);
			echo '    <td class="dataTableContent" align="right">' . $currencies->format_full($result->fields['cost']) . '</td>' . chr(10);
			echo '    <td class="dataTableContent" align="right">';
	  		echo html_icon('phreebooks/stock_id.png', TEXT_VIEW_SHIP_LOG, 'small', 'onclick="loadPopUp(\'ups\', \'edit\', ' . $result->fields['id'] . ')"') . chr(10);
	  		echo html_icon('actions/document-print.png', TEXT_PRINT, 'small', 'onclick="window.open(\'index.php?module=shipping&amp;page=popup_label_viewer&amp;method=ups&amp;date=' . $date . '&amp;labels=' . $result->fields['tracking_id'] . '\',\'label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10);
	  		echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SHIPPING_DELETE_CONFIRM . '\')) window.open(\'index.php?module=shipping&amp;page=popup_label_mgr&amp;method=ups&amp;sID=' . $result->fields['shipment_id'] . '&amp;action=delete\',\'popup_label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10);
			echo '    </td>';
			echo '  </tr>' . chr(10);
			$result->MoveNext();
		}
	} else {
		echo '  <tr><td align="center" colspan="8">' . SHIPPING_NO_SHIPMENTS . '</td></tr>';
	}
	?>
</table>