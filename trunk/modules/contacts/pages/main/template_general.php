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
//  Path: /modules/contacts/pages/main/template_general.php
//
?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
  <fieldset>
    <legend><?php echo ACT_CATEGORY_CONTACT; ?></legend>
    <table>
      <tr>
        <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_SHORT_NAME') . ($cInfo->auto_type == false ? '' : ' ' . ACT_ID_AUTO_FILL ); ?></td>
        <td><?php echo html_input_field('short_name', $cInfo->short_name, 'size="21" maxlength="20"', $cInfo->auto_type == false ? true : false); ?></td>
        <td align="right"><?php echo TEXT_INACTIVE; ?></td>
        <td><?php echo html_checkbox_field('inactive', '1', $cInfo->inactive); ?></td>
        <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_REP_ID'); ?></td>
        <td><?php echo html_pull_down_menu('dept_rep_id', $sales_rep_array, $cInfo->dept_rep_id ? $cInfo->dept_rep_id : '0'); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo GEN_FIRST_NAME; ?></td>
        <td><?php echo html_input_field('contact_first', $cInfo->contact_first, 'size="33" maxlength="32"', false); ?></td>
        <td align="right"><?php echo GEN_MIDDLE_NAME; ?></td>
        <td><?php echo html_input_field('contact_middle', $cInfo->contact_middle, 'size="33" maxlength="32"', false); ?></td>
        <td align="right"><?php echo GEN_LAST_NAME; ?></td>
        <td><?php echo html_input_field('contact_last', $cInfo->contact_last, 'size="33" maxlength="32"', false); ?></td>
      </tr>
      <tr>
       <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_GL_ACCOUNT_TYPE'); ?></td>
       <td><?php echo html_pull_down_menu('gl_type_account', gen_coa_pull_down(), $_REQUEST['action']=='new' ? AR_DEF_GL_SALES_ACCT : $cInfo->gl_type_account); ?></td>
       <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_ACCOUNT_NUMBER'); ?></td>
       <td><?php echo html_input_field('account_number', $cInfo->account_number, 'size="17" maxlength="16"'); ?></td>
       <td align="right"><?php echo TEXT_DEFAULT_PRICE_SHEET; ?></td>
       <td><?php echo html_pull_down_menu('price_sheet', get_price_sheet_data(), $cInfo->price_sheet); ?></td>
      </tr>
      <tr>
       <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_ID_NUMBER'); ?></td>
       <td><?php echo html_input_field('gov_id_number', $cInfo->gov_id_number, 'size="17" maxlength="16"'); ?></td>
	   <td align="right"><?php echo INV_ENTRY_ITEM_TAXABLE; ?></td>
       <td><?php echo html_pull_down_menu('tax_id', $tax_rates, $cInfo->tax_id); ?></td>
       <td><?php  echo ACT_CATEGORY_PAYMENT_TERMS; ?></td>
	   <td><?php 
    	  echo html_hidden_field('terms', $cInfo->special_terms) . chr(10);
	      echo html_input_field('terms_text', gen_terms_to_language($cInfo->special_terms, true, $cInfo->terms_type), 'readonly="readonly" size="20"') . '&nbsp;' . chr(10);
    	  echo html_icon('apps/accessories-text-editor.png', ACT_TERMS_DUE, 'small', 'style="cursor:pointer" onclick="TermsList()"'); ?>
	   </td>
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