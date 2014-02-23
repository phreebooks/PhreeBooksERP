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
//  Path: /modules/rma/pages/main/tab_receiving.php
//
?>

<div title="<?php echo TEXT_RECEIVING;?>" id="tab_receiving">
<fieldset>
  <legend><?php echo TEXT_DETAILS; ?></legend>
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
	<tr>
	  <td align="right"><?php echo TEXT_RECEIVE_DATE; ?></td>
	  <td><?php echo html_calendar_field($cal_rcv); ?></td>
	  <td align="right"><?php echo TEXT_RECEIVE_CARRIER; ?></td>
	  <td><?php echo html_input_field('receive_carrier', $cInfo->receive_carrier); ?> </td>
	</tr>
	<tr>
	  <td align="right" valign="top"><?php echo TEXT_RECEIVED_BY; ?></td>
	  <td valign="top"><?php echo html_pull_down_menu('received_by', $user_choices, ($cInfo->received_by ? $cInfo->received_by : '')); ?></td>
	  <td align="right"><?php echo TEXT_RECEIVE_TRACKING_NUM; ?></td>
	  <td><?php echo html_input_field('receive_tracking', $cInfo->receive_tracking); ?> </td>
	</tr>
	<tr>
	  <td align="right" valign="top"><?php echo TEXT_NOTES; ?></td>
	  <td colspan="3"><?php echo html_textarea_field('receive_notes', 60, 3, $cInfo->receive_notes, '',true); ?></td>
	</tr>
   </tbody>
  </table>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
   <tr>
	<td colspan="2">
	 <table class="ui-widget" style="border-collapse:collapse;width:100%">
	  <thead class="ui-widget-header">
        <tr>
          <th><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
          <th><?php echo TEXT_QUANTITY; ?></th>
          <th><?php echo TEXT_SKU; ?></th>
          <th><?php echo TEXT_DESCRIPTION; ?></th>
          <th><?php echo TEXT_DATE_MANUFACTURE; ?></th>
          <th><?php echo TEXT_DATE_WARRANTY; ?></th>
        </tr>
	  </thead>
	 <tbody id="rcv_table" class="ui-widget-content">
<?php 
if (sizeof($receive_details) > 0) {
	for ($i=0; $i<count($receive_details); $i++) { ?>
		<tr>
		  <td align="center"><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick = "if (confirm(image_delete_msg)) $(this).parent().parent().remove();"'); ?></td>
		  <td align="center"><?php echo html_input_field("rcv_qty[".($i+1)."]", $receive_details[$i]['qty'], 'size="7" maxlength="6" style="text-align:right"'); ?></td>
		  <td nowrap="nowrap" align="center"><?php echo html_input_field("rcv_sku[".($i+1)."]", $receive_details[$i]['sku'], 'size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" onfocus="activeField(this, \''.TEXT_SEARCH.'\')" onblur="inactiveField(this, \''.TEXT_SEARCH.'\')"'); ?>
		  <?php echo '&nbsp;' . html_icon('status/folder-open.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="ItemList(\'rcv_\','.($i+1).')"'); ?>
		  </td>
		  <td><?php echo html_input_field("rcv_desc[".($i+1)."]",  $receive_details[$i]['desc'], 'size="32"'); ?></td>
		  <td><?php echo html_input_field("rcv_mfg[".($i+1)."]",   $receive_details[$i]['mfg'],  'size="32"'); ?></td>
		  <td><?php echo html_input_field("rcv_wrnty[".($i+1)."]", $receive_details[$i]['wrnty'],'size="32"'); ?></td>
		</tr>
<?php
	}
} else {
	$hidden_fields .= '  <script type="text/javascript">addRcvRow();</script>' . chr(10);
} ?>
       </tbody>
      </table>
	</td>
   </tr>
   <tr>
    <td colspan="2"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addRcvRow()"'); ?></td>
   </tr>
 </tbody>
</table>
</fieldset>
</div>