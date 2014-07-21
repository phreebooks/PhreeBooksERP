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
//  Path: /modules/shipping/methods/item/label_mgr/template_main.php
//

// start the form
echo html_form('label_mgr', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);

// include hidden fields
echo html_hidden_field('action', '') . chr(10);

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show'] = false;
$toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . SHIPPING_DELETE_CONFIRM . '\')) submitToDo(\'delete\')"';
$toolbar->icon_list['print']['show'] = false;

// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
	foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}

// add the help file index and build the toolbar
$toolbar->add_help('09.01');
echo $toolbar->build_toolbar(); 

// Build the page
?>
<h1><?php echo MODULE_SHIPPING_ITEM_TEXT_TITLE; ?></h1>
<div>
<fieldset id="recp">
<legend><?php echo SHIPPING_SHIPMENT_DETAILS; ?></legend>
<table>
	<tr>
		<td><?php echo SHIPPING_TEXT_REFERENCE_ID; ?></td>
		<td><?php echo html_input_field('purchase_invoice_id', $sInfo->purchase_invoice_id); ?></td>
	</tr>
	<tr>
		<td><?php echo SHIPPING_SERVICE_TYPE; ?></td>
		<td><?php echo html_pull_down_menu('ship_method', gen_build_pull_down($shipping_methods), $sInfo->ship_method); ?></td>
	</tr>
	<tr>
		<td><?php echo SHIPPING_TEXT_SHIPMENT_DATE; ?></td>
		<td><?php echo html_calendar_field($cal_ship); ?></td>
	</tr>
	<tr>
		<td><?php echo SHIPPING_TEXT_EXPECTED_DATE; ?></td>
		<td><?php echo html_calendar_field($cal_exp); ?></td>
	</tr>
	<tr>
		<td><?php echo SHIPPING_TEXT_TRACKING_NUM; ?></td>
		<td><?php echo html_input_field('tracking_id', $sInfo->tracking_id, 'size="25" maxlength="24"'); ?></td>
	</tr>
	<tr>
		<td><?php echo SHIPPING_TEXT_COST; ?></td>
		<td><?php echo html_input_field('cost', $currencies->format($sInfo->cost), 'size="15" maxlength="14" style="text-align:right"'); ?></td>
	</tr>
</table>
</fieldset>
</div>
</form>