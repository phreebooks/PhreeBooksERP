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
//  Path: /modules/rma/pages/main/tab_disposition.php
//
?>

<div title="<?php echo TEXT_DISPOSITION;?>" id="tab_disposition">

<fieldset>
  <legend><?php echo TEXT_DETAILS; ?></legend>
  <p><?php echo RMA_DISPOSITION_DESC; ?></p>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
   <tr>
	<td align="right" valign="top"><?php echo TEXT_NOTES; ?></td>
	<td><?php echo html_textarea_field('close_notes', 60, 3, $cInfo->close_notes, '', true); ?></td>
   </tr>
   <tr>
	<td colspan="2">
	 <table class="ui-widget" style="border-collapse:collapse;width:100%">
	  <thead class="ui-widget-header">
        <tr>
          <th><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
          <th><?php echo TEXT_QUANTITY; ?></th>
          <th><?php echo TEXT_SKU; ?></th>
          <th><?php echo TEXT_NOTES; ?></th>
          <th><?php echo TEXT_ACTION; ?></th>
        </tr>
	  </thead>
	 <tbody id="item_table" class="ui-widget-content">
<?php 
if (sizeof($close_details) > 0) {
	for ($i=0; $i<sizeof($close_details); $i++) { ?>
		<tr>
		  <td><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick = "if (confirm(image_delete_msg)) $(this).parent().parent().remove();"'); ?></td>
		  <td><?php echo html_input_field("dis_qty[".($i+1)."]", $close_details[$i]['qty'], 'size="7" maxlength="6" style="text-align:right"'); ?></td>
		  <td nowrap="nowrap" ><?php echo html_input_field("dis_sku[".($i+1)."]", $close_details[$i]['sku'], 'size="24" onfocus="activeField(this, \''.TEXT_SEARCH.'\')" onblur="inactiveField(this, \''.TEXT_SEARCH.'\')"'); ?>
		  <?php echo '&nbsp;' . html_icon('status/folder-open.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="ItemList(\'dis_\','.($i+1).')"'); ?>
		  </td>
		  <td><?php echo html_input_field("dis_notes[".($i+1)."]", $close_details[$i]['notes'], 'size="48"'); ?></td>
		  <td><?php echo html_pull_down_menu("dis_action[".($i+1)."]", gen_build_pull_down($action_codes), $close_details[$i]['action']); ?></td>
		</tr>
<?php
	}
} else {
	$hidden_fields .= '  <script type="text/javascript">addItemRow();</script>' . chr(10);
} ?>
       </tbody>
      </table>
	</td>
   </tr>
   <tr>
    <td colspan="2"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addItemRow()"'); ?></td>
   </tr>
 </tbody>
</table>
</fieldset>
</div>