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
//  Path: /modules/cp_action/pages/main/template_detail.php
//

// start the form
echo html_form('capa', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post');
$hidden_fields = NULL;

// include hidden fields
echo html_hidden_field('action',   '')         . chr(10);
echo html_hidden_field('rowSeq', $cInfo->id) . chr(10);

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'page')) . 'page=main', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
if (($cInfo->id && $security_level > 2) || (!$cInfo->id && $security_level > 1)) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
  $toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['print']['show']    = false;
//$toolbar->add_help('');
echo $toolbar->build();
?>
<h1><?php echo ($_REQUEST['action'] == 'new') ? TEXT_CREATE_NEW_CORRECTIVE_ACTION : (TEXT_CORRECTIVE_OR_PREVENTATIVE_ACTION . ' - ' . TEXT_CAPA_NUM . '# ' . $cInfo->capa_num); ?></h1>

<fieldset>
  <legend><?php echo TEXT_GENERAL; ?></legend>
  <table>
	<tr>
	  <td align="right"><?php echo TEXT_ACTION_TYPE; ?></td>
	  <td>
	    <?php echo TEXT_CA . ' ' . html_radio_field('capa_type', 'c', (!$cInfo->capa_type || $cInfo->capa_type == 'c') ? true : false); ?>
	    <?php echo TEXT_PA . ' ' . html_radio_field('capa_type', 'p', ($cInfo->capa_type == 'p') ? true : false); ?>
	  </td>
	  <td align="right" valign="top"><?php echo TEXT_REQUESTED_BY; ?></td>
	  <td><?php echo html_pull_down_menu('requested_by', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->requested_by); ?></td>
	  <td align="right"><?php echo TEXT_STATUS; ?></td>
	  <td><?php echo html_pull_down_menu('capa_status', gen_build_pull_down($status_codes), $cInfo->capa_status); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_CAPA_NUM . ' (' . TEXT_ASSIGNED_BY_SYSTEM . ') '; ?></td>
	  <td><?php echo html_input_field('capa_num', $cInfo->capa_num, 'readonly="readonly"'); ?> </td>
	  <td align="right"><?php echo TEXT_ENTERED_BY; ?></td>
	  <td><?php echo html_pull_down_menu('entered_by', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), ($cInfo->entered_by ? $cInfo->entered_by : $_SESSION['admin_id'])); ?></td>
	  <td align="right"><?php echo TEXT_CREATION_DATE; ?></td>
	  <td><?php echo html_calendar_field($cal_date0); ?></td>
	</tr>
	<tr>
	  <td align="right" valign="top"><?php echo TEXT_DESCRIPTION_OF_ISSUE; ?></td>
	  <td colspan="5"><?php echo html_textarea_field('notes_issue', 80, 3, $cInfo->notes_issue, '', true); ?></td>
	</tr>
  </table>
</fieldset>
<fieldset>
  <legend><?php echo TEXT_CUSTOMER_DETAILS; ?></legend>
  <table>
	<tr>
	  <td align="right"><?php echo TEXT_CUSTOMER_NAME; ?></td>
	  <td><?php echo html_input_field('customer_name', $cInfo->customer_name, 'size="33" maxlength="32"', false); ?></td>
	  <td align="right"><?php echo TEXT_CUSTOMER_ID; ?></td>
	  <td><?php echo html_input_field('customer_id', $cInfo->customer_id, 'size="17" maxlength="16"', false); ?></td>
	  <td align="right"><?php echo TEXT_TELEPHONE; ?></td>
	  <td><?php echo html_input_field('customer_telephone', $cInfo->customer_telephone, 'size="22" maxlength="20"'); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_PURCHASE_INVOICE_ID. " #"; ?></td>
	  <td><?php echo html_input_field('customer_invoice', $cInfo->customer_invoice); ?></td>
	  <td align="right"><?php echo TEXT_EMAIL; ?></td>
	  <td><?php echo html_input_field('customer_email', $cInfo->customer_email, 'size="49" maxlength="48"'); ?></td>
	  <td>&nbsp;</td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
	  <td align="right" valign="top"><?php echo TEXT_CUSTOMER_NOTES; ?></td>
	  <td colspan="5"><?php echo html_textarea_field('notes_customer', 80, 3, $cInfo->notes_customer, '', true); ?></td>
	</tr>
  </table>
</fieldset>
<fieldset>
  <legend><?php echo TEXT_DETAILS; ?></legend>
  <table>
	<tr>
	  <th>&nbsp;</th>
	  <th><?php echo TEXT_ASSIGNED_TO;   ?></th>
	  <th><?php echo TEXT_ASSIGNED_DATE; ?></th>
	  <th><?php echo TEXT_CLOSED_BY;     ?></th>
	  <th><?php echo TEXT_CLOSED_DATE;   ?></th>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_INVESTIGATION; ?></td>
	  <td><?php echo html_pull_down_menu('analyze_due_id',   gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->analyze_due_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date1); ?></td>
	  <td><?php echo html_pull_down_menu('analyze_close_id', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->analyze_close_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date5); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_IMPLEMENTATION; ?></td>
	  <td><?php echo html_pull_down_menu('repair_due_id',   gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->repair_due_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date2); ?></td>
	  <td><?php echo html_pull_down_menu('repair_close_id', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->repair_close_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date6); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_AUDIT; ?></td>
	  <td><?php echo html_pull_down_menu('audit_due_id', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->audit_due_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date3); ?></td>
	  <td><?php echo html_pull_down_menu('audit_close_id', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->audit_close_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date7); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_CLOSED_DATE; ?></td>
	  <td><?php echo html_pull_down_menu('closed_due_id', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->closed_due_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date4); ?></td>
	  <td><?php echo html_pull_down_menu('closed_close_id', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->closed_close_id); ?></td>
	  <td><?php echo html_calendar_field($cal_date8); ?></td>
	</tr>
  </table>
</fieldset>
<fieldset>
  <legend><?php echo TEXT_INVESTIGATION; ?></legend>
  <table>
	<tr><th><?php echo TEXT_INVESTIGATION; ?></th></tr>
	<tr><td><?php echo html_textarea_field('notes_investigation', 80, 3, $cInfo->notes_investigation, '', true); ?></td></tr>
  </table>
</fieldset>
<fieldset>
  <legend><?php echo TEXT_ACTION_TAKEN; ?></legend>
  <table>
	<tr>
	  <td align="right"><?php echo TEXT_AGREED_TO_BY; ?></td>
	  <td><?php echo html_pull_down_menu('agreed_by', gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name'), $cInfo->agreed_by); ?></td>
	  <td align="right"><?php echo TEXT_AGREED_DATE; ?></td>
	  <td><?php echo html_calendar_field($cal_date9); ?></td>
	</tr>
	<tr>
	  <td align="right" valign="top"><?php echo TEXT_ACTION; ?></td>
	  <td colspan="3"><?php echo html_textarea_field('notes_action', 80, 3, $cInfo->notes_action, '', true); ?></td>
	</tr>
  </table>
</fieldset>

<fieldset>
  <legend><?php echo TEXT_AUDIT; ?></legend>
  <table>
	<tr>
	  <td align="right">&nbsp;</td>
	  <td align="right"><?php echo TEXT_WAS_ACTION_TAKEN_EFFECTIVE . '?'; ?></td>
	  <td>
	    <?php echo TEXT_YES . ' ' . html_radio_field('capa_closed', 'y', (!$cInfo->capa_closed || $cInfo->capa_closed == 'y') ? true : false); ?>
	    <?php echo TEXT_NO  . ' ' . html_radio_field('capa_closed', 'n', ($cInfo->capa_closed == 'n') ? true : false); ?>
	  </td>
	  <td align="right"><?php echo TEXT_NEW_CAPA_NUMBER; ?></td>
	  <td><?php echo html_input_field('next_capa_num', $cInfo->next_capa_num, 'size="12" maxlength="10"'); ?></td>
	</tr>
	<tr>
	  <td align="right" valign="top"><?php echo TEXT_AUDIT_NOTES; ?></td>
	  <td colspan="4"><?php echo html_textarea_field('notes_audit', 80, 3, $cInfo->notes_audit, '', true); ?></td>
	</tr>
  </table>
</fieldset>
<?php echo $hidden_fields; ?>
</form>
