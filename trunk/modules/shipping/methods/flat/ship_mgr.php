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
//  Path: /modules/shipping/methods/flat/ship_mgr.php
//
?>
<h1><?php echo constant('MODULE_SHIPPING_' . strtoupper($method_id) . '_TEXT_TITLE'); ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr>
    <th colspan="8"><?php echo SHIPPING_FLAT_SHIPMENTS_ON . gen_locale_date($date); ?></th>
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
 </thead>
 <tbody class="ui-widget-content">
  <?php 
	$result = $db->Execute("select id, shipment_id, ref_id, method, deliver_date, tracking_id, cost 
		from " . TABLE_SHIPPING_LOG . " 
		where carrier = 'flat' and ship_date = '" . $date . "'");
	if ($result->RecordCount() > 0) {
		while(!$result->EOF) {
			echo '  <tr>' . chr(10);
			echo '    <td align="right">' . $result->fields['shipment_id'] . '</td>' . chr(10);
			echo '    <td align="right">' . $result->fields['ref_id'] . '</td>' . chr(10);
			echo '    <td align="center">' . constant('flat_' . $result->fields['method']) . '</td>' . chr(10);
			echo '    <td align="right">' . gen_locale_date($result->fields['deliver_date']) . '</td>' . chr(10);
			echo '    <td align="right">' . gen_locale_date($result->fields['actual_date']) . '</td>' . chr(10);
			echo '    <td align="right">' . $result->fields['tracking_id'] . '</td>' . chr(10);
			echo '    <td align="right">' . $currencies->format_full($result->fields['cost']) . '</td>' . chr(10);
			echo '    <td align="right">';
	  		echo html_icon('phreebooks/stock_id.png', TEXT_VIEW_SHIP_LOG, 'small', 'onclick="loadPopUp(\'flat\', \'edit\', ' . $result->fields['id'] . ')"') . chr(10);
//	  		echo html_icon('actions/document-print.png', TEXT_PRINT, 'small', 'onclick="window.open(\'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('method', 'labels', 'date')) . 'method=flat&date=' . $date . '&labels=' . $result->fields['tracking_id'], 'SSL') . '\',\'label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10);
	  		echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SHIPPING_DELETE_CONFIRM . '\')) window.open(\'index.php?module=shipping&page=popup_label_mgr&method=flat&sID=' . $result->fields['shipment_id'] . '&action=delete\',\'popup_label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10);
			echo '    </td>';
			echo '  </tr>' . chr(10);
			$result->MoveNext();
		}
	} else {
		echo '  <tr><td align="center" colspan="8">' . SHIPPING_NO_SHIPMENTS . '</td></tr>';
	}
	?>
 </tbody>
</table>