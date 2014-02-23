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
//  Path: /modules/contacts/pages/main/template_j_general.php
//

// *********************** Display contact information ******************************
$cal_j_gen1 = array(
  'name'      => 'dateFrom',
  'form'      => 'contacts',
  'fieldname' => 'contact_first',
  'imagename' => 'btn_j_gen1',
  'default'   => isset($cInfo->contact_first) ? $cInfo->contact_first : '',
);
$cal_j_gen2 = array(
  'name'      => 'dateTo',
  'form'      => 'contacts',
  'fieldname' => 'contact_last',
  'imagename' => 'btn_j_gen2',
  'default'   => isset($cInfo->contact_last) ? $cInfo->contact_last : '',
);
?>
<script type="text/javascript">
<?php echo js_calendar_init($cal_j_gen1); ?>
<?php echo js_calendar_init($cal_j_gen2); ?>
</script>

<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
  <fieldset>
    <legend><?php echo ACT_CATEGORY_CONTACT; ?></legend>
    <table>
      <tr>
        <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_SHORT_NAME'); ?></td>
        <td><?php echo html_input_field('short_name', $cInfo->short_name, 'size="21" maxlength="20"', true); ?></td>
        <td align="right"><?php echo TEXT_INACTIVE; ?></td>
        <td>
	      <?php echo html_checkbox_field('inactive', '1', $cInfo->inactive) . ' ';
            echo constant('ACT_' . strtoupper($type) . '_ACCOUNT_NUMBER') . ' ';
            echo html_radio_field('account_number', 1, ($cInfo->account_number == '1' ? true : false)) . TEXT_YES . chr(10);
            echo html_radio_field('account_number', 2, (($cInfo->account_number == '' || $cInfo->account_number == '2') ? true : false)) . TEXT_NO  . chr(10);
          ?>
	   </td>
      </tr>
      <tr>
        <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_REP_ID'); ?></td>
        <td>
		  <?php
			$default_selection = ($_REQUEST['action'] == 'new' ? AR_DEF_GL_SALES_ACCT : $cInfo->dept_rep_id);
			$selection_array = gen_get_rep_ids('c');
			echo html_pull_down_menu('dept_rep_id', $selection_array, $default_selection);
		  ?>
	    </td>
        <td align="right"><?php echo TEXT_START_DATE; ?></td>
        <td><?php echo html_calendar_field($cal_j_gen1); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo constant('ACT_' . strtoupper($type) . '_ID_NUMBER'); ?></td>
        <td><?php echo html_input_field('gov_id_number', $cInfo->gov_id_number, 'size="17" maxlength="16"'); ?></td>
        <td align="right"><?php echo TEXT_END_DATE; ?></td>
        <td><?php echo html_calendar_field($cal_j_gen2); ?></td>
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