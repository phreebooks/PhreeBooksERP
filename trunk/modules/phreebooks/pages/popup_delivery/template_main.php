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
//  Path: /modules/phreebooks/pages/popup_delivery/template_main.php
//
echo html_form('popup_delivery', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\', \'eta_dates\')"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) {
  foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}
switch (JOURNAL_ID) {
  case 4: $toolbar->add_help('07.02.03.04'); break;
  case 6: $toolbar->add_help('07.03.03.04'); break;
}
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo ORD_EXPECTED_DATES . constant('ORD_HEADING_NUMBER_' . JOURNAL_ID) . ' ' . $ordr_items->fields['purchase_invoice_id']; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr>
	<th><?php echo TEXT_QUANTITY; ?></th>
	<th><?php echo TEXT_SKU; ?></th>
	<th><?php echo TEXT_DESCRIPTION; ?></th>
	<th><?php echo ORD_DELIVERY_DATES; ?></th>
	<th><?php echo ORD_NEW_DELIVERY_DATES; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
	$j = 1;
	while (!$ordr_items->EOF) {
		$price = $currencies->format($level_info[0] ? $level_info[0] : (($i == 0) ? $full_price : 0));
		echo '<tr>' . chr(10);
		echo '  <td align="center">' . $ordr_items->fields['qty'] . '</td>' . chr(10);
		echo '  <td align="center">' . $ordr_items->fields['sku'] . '</td>' . chr(10);
		echo '  <td>' . $ordr_items->fields['description'] . '</td>' . chr(10);
		echo '  <td align="center">' . gen_locale_date($ordr_items->fields['date_1']) . '</td>' . chr(10);
		echo '  <td align="center" nowrap="nowrap">';
		echo html_hidden_field('id_' . $j, $ordr_items->fields['id']) . chr(10);
		echo html_calendar_field($cal_gen[$j]) . chr(10);
		echo '  </td>' . chr(10);
		echo '</tr>';
		$j++;
		$ordr_items->MoveNext();
	}
?>
 </tbody>
</table>
</form>
