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
//  Path: /modules/rma/pages/main/tab_general.php
//
?>

<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
<fieldset>
  <legend><?php echo TEXT_GENERAL; ?></legend>
<div style="float:right;width:40%">
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
	<tr>
	  <td align="right"><?php echo TEXT_CALLER_NAME; ?></td>
	  <td><?php echo html_input_field('caller_name', $cInfo->caller_name, 'size="33"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_TELEPHONE; ?></td>
	  <td><?php echo html_input_field('caller_telephone1', $cInfo->caller_telephone1, 'size="33"'); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo GEN_EMAIL; ?></td>
	  <td><?php echo html_input_field('caller_email', $cInfo->caller_email, 'size="33"'); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_CUSTOMER_ID; ?></td>
	  <td><?php echo html_input_field('contact_id', $cInfo->contact_id, ''); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo GEN_PRIMARY_NAME; ?></td>
	  <td><?php echo html_input_field('contact_name', $cInfo->contact_name, 'size="33"'); ?></td>
	</tr>
  </tbody>
 </table>
</div>
<div style="float:right;width:30%">
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
	<tr>
	  <td align="right" valign="top"><?php echo TEXT_ENTERED_BY; ?></td>
	  <td valign="top"><?php echo html_pull_down_menu('entered_by', $user_choices, ($cInfo->entered_by ? $cInfo->entered_by : $_SESSION['admin_id'])); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_STATUS; ?></td>
	  <td><?php echo html_pull_down_menu('status', gen_build_pull_down($status_codes), $cInfo->status); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_CREATION_DATE; ?></td>
	  <td><?php echo html_calendar_field($cal_create); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_CLOSED; ?></td>
	  <td><?php echo html_calendar_field($cal_close); ?></td>
	</tr>
  </tbody>
 </table>
</div>
<div style="width:30%">
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
	<tr>
	  <td align="right"><?php echo TEXT_PURCHASE_INVOICE_ID; ?></td>
	  <td><?php echo html_input_field('purchase_invoice_id', $cInfo->purchase_invoice_id); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_ORIG_PO_SO; ?></td>
	  <td><?php echo html_input_field('purch_order_id', $cInfo->purch_order_id); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_INVOICE_DATE; ?></td>
	  <td><?php echo html_calendar_field($cal_invoice); ?></td>
	</tr>
  </tbody>
 </table>
</div>
<div style="clear:both">
 <table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
  <tbody class="ui-widget-content">
	<tr>
	  <td><?php echo TEXT_REASON_FOR_RETURN; ?></td>
	  <td><?php echo TEXT_NOTES; ?></td>
	</tr>
	<tr>
	  <td valign="top"><?php echo html_pull_down_menu('return_code', gen_build_pull_down($reason_codes), $cInfo->return_code); ?></td>
	  <td><?php echo html_textarea_field('caller_notes', 60, 3, $cInfo->caller_notes, '', true); ?></td>
	</tr>
  </tbody>
 </table>
</div>
</fieldset>
<?php // *********************** Attachments  ************************************* ?>
   <fieldset>
   <legend><?php echo TEXT_ATTACHMENTS; ?></legend>
   <table class="ui-widget" style="margin-left:auto;margin-right:auto">
    <thead class="ui-widget-header">
     <tr><th colspan="3"><?php echo TEXT_ATTACHMENTS; ?></th></tr>
    </thead>
    <tbody class="ui-widget-content">
     <tr><td colspan="3"><?php echo TEXT_SELECT_FILE_TO_ATTACH . ' ' . html_file_field('file_name'); ?></td></tr>
     <tr  class="ui-widget-header">
      <th><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
      <th><?php echo TEXT_FILENAME; ?></th>
      <th><?php echo TEXT_ACTION; ?></th>
     </tr>
<?php 
if (sizeof($attachments) > 0) { 
  foreach ($attachments as $key => $value) {
    echo '<tr>';
    echo ' <td>' . html_checkbox_field('rm_attach_'.$key, '1', false) . '</td>' . chr(10);
    echo ' <td>' . $value . '</td>' . chr(10);
    echo ' <td>' . html_button_field('dn_attach_'.$key, TEXT_DOWNLOAD, 'onclick="submitSeq(' . $key . ', \'download\', true)"') . '</td>';
    echo '</tr>' . chr(10);
  }
} else {
  echo '<tr><td colspan="3">' . TEXT_NO_DOCUMENTS . '</td></tr>'; 
} ?>
    </tbody>
   </table>
   </fieldset>
</div>