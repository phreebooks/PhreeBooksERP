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
//  Path: /modules/shipping/methods/item/ship_mgr.php
//
?>
<h1><?php echo $method->text; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr>
    <th colspan="8"><?php echo TEXT_SHIPMENTS_ON . gen_locale_date($date); ?></th>
  </tr>
  <tr>
	<th><?php echo SHIPPING_TEXT_SHIPMENT_ID;   ?></th>
	<th><?php echo SHIPPING_TEXT_REFERENCE_ID;  ?></th>
	<th><?php echo SHIPPING_TEXT_SERVICE;       ?></th>
	<th><?php echo SHIPPING_TEXT_EXPECTED_DATE; ?></th>
	<th><?php echo SHIPPING_TEXT_ACTUAL_DATE;   ?></th>
	<th><?php echo SHIPPING_TEXT_TRACKING_NUM;  ?></th>
	<th><?php echo SHIPPING_TEXT_COST;          ?></th>
	<th><?php echo TEXT_ACTION;                 ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
	<?php 
	$start_date = date('Y-m-d', strtotime("-1 day"));
	$end_date   = date('Y-m-d', strtotime("+1 day"));
	$result = $db->Execute("select id, shipment_id, ref_id, method, deliver_date, deliver_late, actual_date, tracking_id, cost 
		from " . TABLE_SHIPPING_LOG . " where carrier = '" . $method->id . "' 
		  and ship_date like '" . $date . "%'");
	if ($result->RecordCount() > 0) {
		$odd = true;
		while(!$result->EOF) {
			switch ($result->fields['deliver_late']) {
		  		default:
		  		case '0': $bkgnd = ''; break;
		  		case 'T': $bkgnd = ' style="background-color:yellow"';   break;
		  		case 'L': $bkgnd = ' style="background-color:lightred"'; break;
			}
			echo '  <tr class="'.($odd?'odd':'even').'">' . chr(10);
			echo '    <td' . $bkgnd . ' align="center">' . $result->fields['shipment_id'] . '</td>' . chr(10);
			echo '    <td' . $bkgnd . ' align="center">' . $result->fields['ref_id'] . '</td>' . chr(10);
			echo '    <td align="center">' . constant($method->id . '_' . $result->fields['method']) . '</td>' . chr(10);
			echo '    <td align="right">' . ($result->fields['deliver_date'] <> '0000-00-00 00:00:00' ? gen_locale_date($result->fields['deliver_date'], true) : '&nbsp;') . '</td>' . chr(10);
			echo '    <td align="right">' . ($result->fields['actual_date']  <> '0000-00-00 00:00:00' ? gen_locale_date($result->fields['actual_date'], true)  : '&nbsp;') . '</td>' . chr(10);
			echo '    <td align="right">' . $result->fields['tracking_id'] . '</td>' . chr(10);
			echo '    <td align="right">' . $currencies->format_full($result->fields['cost']) . '</td>' . chr(10);
			echo '    <td align="right" nowrap="nowrap">';
			echo html_icon('phreebooks/stock_id.png', 		TEXT_VIEW_SHIP_LOG,	'small', 'onclick="loadPopUp(\'' . $method->id . '\', \'edit\', ' . $result->fields['id'] . ')"') . chr(10);
//			echo html_icon('actions/document-print.png',	TEXT_PRINT,			'small', 'onclick="window.open(\'index.php?module=shipping&page=popup_label_mgr&action=view&method=' . $method->id . '&date=' . $date . '&labels=' . $result->fields['tracking_id'] . '\',\'label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10);
			echo html_icon('emblems/emblem-unreadable.png',	TEXT_DELETE,		'small', 'onclick="if (confirm(\'' . SHIPPING_DELETE_CONFIRM . '\')) window.open(\'index.php?module=shipping&page=popup_label_mgr&method=' . $method->id . '&sID=' . $result->fields['shipment_id'] . '&action=delete\',\'popup_label_mgr\',\'width=800,height=700,resizable=1,scrollbars=1,top=50,left=50\')"') . chr(10);
			echo '    </td>';
			echo '  </tr>' . chr(10);
			$result->MoveNext();
			$odd = !$odd;
		}
	} else {
		echo '  <tr><td align="center" colspan="8">' . SHIPPING_NO_SHIPMENTS . '</td></tr>';
	}
	?>
 </tbody>
</table>