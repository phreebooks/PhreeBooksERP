<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |

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
//  Path: /modules/shipping/methods/yrc/ship_mgr.php
//@todo update to release 4.0
?>
<h1><?php echo constant('MODULE_SHIPPING_' . strtoupper($method_id) . '_TEXT_TITLE'); ?></h1>
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
  <tr>
	<td colspan="8"><?php echo TEXT_CREATE_A_SHIPMENT_ENTRY . ' => ' . html_button_field('close', TEXT_CREATE_A_SHIPMENT_ENTRY, 'onclick="window.open(\'index.php?module=shipping&amp;page=popup_tracking&amp;method=yrc&amp;action=new\',\'popup_tracking\',\'width=550,height=350,resizable=1,scrollbars=1,top=150,left=200\')"') . chr(10); ?></td>
  </tr>
 </tbody>
</table>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr>
    <th colspan="8"><?php echo SHIPPING_YRC_SHIPMENTS_ON . ' ' . \core\classes\DateTime::createFromFormat(DATE_FORMAT, $date); ?></th>
  </tr>
  <tr>
	<th><?php echo TEXT_SHIPMENT_ID; ?></th>
	<th><?php echo TEXT_REFERENCE_ID; ?></th>
	<th><?php echo TEXT_SERVICE; ?></th>
	<th><?php echo TEXT_EXPECTED_DELIVERY_DATE; ?></th>
	<th><?php echo TEXT_ACTUAL_DELIVERY_DATE; ?></th>
	<th><?php echo TEXT_TRACKING_NUMBER; ?></th>
	<th><?php echo TEXT_COST; ?></th>
	<th><?php echo TEXT_ACTION; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
  <?php
	$result = $admin->DataBase->query("select id, shipment_id, ref_id, method, deliver_date, tracking_id, cost
		from " . TABLE_SHIPPING_LOG . " where carrier = 'yrc' and ship_date = '" . $date . "'");
	if ($result->fetch(\PDO::FETCH_NUM) > 0) {
		while(!$result->EOF) {
			echo '  <tr>' . chr(10);
			echo '    <td align="right">' . $result->fields['shipment_id'] . '</td>' . chr(10);
			echo '    <td align="right">' . $result->fields['ref_id'] . '</td>' . chr(10);
			echo '    <td align="center">' . constant('yrc_' . $result->fields['method']) . '</td>' . chr(10);
			echo '    <td align="right">' . \core\classes\DateTime::createFromFormat(DATE_FORMAT, $result->fields['deliver_date']) . '</td>' . chr(10);
			echo '    <td align="right">' . ($result->fields['actual_date'] ? \core\classes\DateTime::createFromFormat(DATE_FORMAT, $result->fields['actual_date']) : '&nbsp;') . '</td>' . chr(10);
			echo '    <td align="right"><a target="_blank" href="' . MODULE_SHIPPING_YRC_TRACKING_URL . '">' . $result->fields['tracking_id'] . '</a></td>' . chr(10);
			echo '    <td align="right">' . $admin->currencies->format_full($result->fields['cost']) . '</td>' . chr(10);
			echo '    <td align="right">';
	  		echo html_icon('phreebooks/stock_id.png', TEXT_VIEW_SHIP_LOG, 'small', 'onclick="loadPopUp(\'yrc\', \'edit\', ' . $result->fields['id'] . ')"') . chr(10);
	  		echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SHIPPING_DELETE_CONFIRM . '\')) window.open(\'index.php?module=shipping&amp;page=popup_label_mgr&amp;method=yrc&amp;sID=' . $result->fields['shipment_id'] . '&amp;action=delete\',\'popup_label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10);
			echo '    </td>';
			echo '  </tr>' . chr(10);
			$result->MoveNext();
		}
	} else {
		echo '  <tr><td align="center" colspan="8">' . TEXT_THERE_ARE_NO_SHIPMENTS_FROM_THIS_CARRIER_TODAY . '</td></tr>';
	}
	?>
 </tbody>
</table>