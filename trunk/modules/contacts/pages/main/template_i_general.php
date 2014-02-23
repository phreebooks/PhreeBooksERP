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
//  Path: /modules/contacts/pages/main/template_i_general.php
//
// some setup
$acct_def = (!$cInfo->dept_rep_id) ? array() : array(array('id'=>$cInfo->dept_rep_id, 'text'=>gen_get_contact_name($cInfo->dept_rep_id)));
// *********************** Display account information ****************************** ?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
  <fieldset>
    <legend><?php echo ACT_CATEGORY_CONTACT; ?></legend>
    <table>
      <tr>
       <td align="right"><?php echo ACT_SHORT_NAME; ?></td>
       <td>
	     <?php echo html_input_field('short_name', $cInfo->short_name, 'size="21" maxlength="20"', true); ?>
         <?php echo ' ' . TEXT_INACTIVE . ' '; ?>
         <?php echo html_checkbox_field('inactive', '1', $cInfo->inactive); ?>
	   </td>
       <td align="right"><?php echo TEXT_TITLE; ?></td>
       <td><?php echo html_input_field('contact_middle', $cInfo->contact_middle, 'size="33" maxlength="32"', false); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo GEN_FIRST_NAME; ?></td>
        <td><?php echo html_input_field('contact_first', $cInfo->contact_first, 'size="33" maxlength="32"', false); ?></td>
        <td align="right"><?php echo GEN_LAST_NAME; ?></td>
        <td><?php echo html_input_field('contact_last', $cInfo->contact_last, 'size="33" maxlength="32"', false); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo ACT_ACCOUNT_NUMBER; ?></td>
        <td><?php echo html_input_field('account_number', $cInfo->account_number, 'size="17" maxlength="16"'); ?></td>
        <td align="right"><?php echo ACT_ID_NUMBER; ?></td>
        <td><?php echo html_input_field('gov_id_number', $cInfo->gov_id_number, 'size="17" maxlength="16"'); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo TEXT_LINK_TO . ' '; ?></td>
        <td><?php echo html_combo_box('dept_rep_id', $acct_def, $cInfo->dept_rep_id, 'onkeyup="loadContacts()"'); ?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </fieldset>

<?php // *********************** Mailing/Main Address (only one allowed) ****************************** ?>
  <fieldset>
    <legend><?php echo ACT_CATEGORY_M_ADDRESS; ?></legend>
    <table id="<?php echo $type; ?>m_address_form" class="ui-widget" style="border-collapse:collapse;width:100%;">
      <?php echo draw_address_fields($cInfo, $type.'m', false, true, false); ?>
    </table>
  </fieldset>
<?php // *********************** Attachments  ************************************* ?>
  <div>
   <fieldset>
   <legend><?php echo TEXT_ATTACHMENTS; ?></legend>
   <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
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
if (sizeof($cInfo->attachments) > 0) {
  foreach ($cInfo->attachments as $key => $value) {
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
</div>