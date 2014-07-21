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
//  Path: /modules/shipping/pages/popup_tracking/template_main.php
//
echo html_form('popup_tracking', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('');
echo $toolbar->build_toolbar();
// Build the page
?>
<h1><?php echo SHIPPING_SHIPMENT_DETAILS; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="2">&nbsp;</th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo SHIPPING_TEXT_SHIPMENT_ID . SHIPPING_SET_BY_SYSTEM; ?></td>
	<td><?php echo html_input_field('shipment_id', $cInfo->shipment_id, 'readonly="readonly"'); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_REFERENCE_ID; ?></td>
	<td><?php echo html_input_field('ref_id', $cInfo->ref_id); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_CARRIER; ?></td>
	<td><?php echo html_pull_down_menu('carrier', $methods, $cInfo->carrier, 'onchange="buildFreightDropdown()"'); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_SERVICE; ?></td>
	<td><?php echo html_pull_down_menu('method', $carrier_methods, $cInfo->method); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_TRACKING_NUM; ?></td>
	<td><?php echo html_input_field('tracking_id', $cInfo->tracking_id); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_SHIPMENT_DATE; ?></td>
	<td><?php echo html_calendar_field($cal_ship); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_EXPECTED_DATE; ?></td>
	<td><?php echo html_calendar_field($cal_del); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_COST; ?></td>
	<td><?php echo html_input_field('cost', $cInfo->cost); ?></td>
  </tr>
 </tbody>
</table>
</form>