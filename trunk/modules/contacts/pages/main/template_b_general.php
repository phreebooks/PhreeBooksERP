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
//  Path: /modules/contacts/pages/main/template_b_general.php
//
?>
<div title="<?php echo TEXT_GENERAL;?>">
  <fieldset>
    <legend><?php echo TEXT_CONTACT_INFORMATION; ?></legend>
    <table>
      <tr>
        <td align="right"><?php echo TEXT_BRANCH_ID; ?></td>
        <td><?php echo html_input_field('short_name', $basis->cInfo->contact->short_name, 'size="21" maxlength="20"', $basis->cInfo->contact->auto_type == false ? true : false); ?></td>
        <td align="right">&nbsp;</td>
        <td>&nbsp;</td>
        <td align="right"><?php echo TEXT_INACTIVE; ?></td>
        <td><?php echo html_checkbox_field('inactive', '1', $basis->cInfo->contact->inactive); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo TEXT_FIRST_NAME; ?></td>
        <td><?php echo html_input_field('contact_first', $basis->cInfo->contact->contact_first, 'size="33" maxlength="32"', false); ?></td>
        <td align="right"><?php echo TEXT_MIDDLE_NAME; ?></td>
        <td><?php echo html_input_field('contact_middle', $basis->cInfo->contact->contact_middle, 'size="33" maxlength="32"', false); ?></td>
        <td align="right"><?php echo TEXT_LAST_NAME; ?></td>
        <td><?php echo html_input_field('contact_last', $basis->cInfo->contact->contact_last, 'size="33" maxlength="32"', false); ?></td>
      </tr>
    </table>
  </fieldset>

<?php // *********************** Mailing/Main Address (only one allowed) ****************************** ?>
  <fieldset>
    <legend><?php echo TEXT_MAIN_MAILING_ADDRESS; ?></legend>
    <table id="<?php echo $basis->cInfo->contact->type; ?>m_address_form" class="ui-widget" style="border-collapse:collapse;width:100%;">
      <?php $basis->cInfo->contact->draw_address_fields($basis->cInfo->contact->type.'m', false, true, false, false); ?>
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
      <th><?php echo TEXT_FILE_NAME; ?></th>
      <th><?php echo TEXT_ACTION; ?></th>
     </tr>
<?php
if (sizeof($basis->cInfo->contact->attachments) > 0) {
  	foreach ($basis->cInfo->contact->attachments as $key => $value) {
    	echo '<tr>';
    	echo ' <td>' . html_checkbox_field('rm_attach_'.$key, '1', false) . '</td>' . chr(10);
    	echo ' <td>' . $value . '</td>' . chr(10);
    	echo ' <td>' . html_button_field('dn_attach_'.$key, TEXT_DOWNLOAD, "onclick='submitSeq({$key}, \"download\", true)'") . '</td>';
    	echo '</tr>' . chr(10);
  	}
} else {
  	echo '<tr><td colspan="3">' . TEXT_NO_DOCUMENTS_HAVE_BEEN_FOUND . '</td></tr>';
} ?>
    </tbody>
   </table>
   </fieldset>
  </div>
</div>