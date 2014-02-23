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
//  Path: /modules/contacts/pages/main/template_notes.php
//
$cal_c_gen = array(
  'name'      => 'dateReference',
  'form'      => 'contacts',
  'fieldname' => 'crm_date',
  'imagename' => 'btn_c_gen',
  'default'   => gen_locale_date($cInfo->crm_date),
  'params'    => array('align' => 'left'),
);
?>
<script type="text/javascript">
<!--
<?php echo js_calendar_init($cal_c_gen); ?>
</script>

<div title="<?php echo TEXT_NOTES;?>" id="tab_notes">
  <fieldset>
    <legend><?php echo TEXT_NOTES; ?></legend>
	<div style="float:right;width:50%">
	 <table class="ui-widget" style="border-collapse:collapse;width:100%">
	  <thead  class="ui-widget-header">
	   <tr>
	    <th><?php echo TEXT_SALES_REP; ?></th>
	    <th><?php echo TEXT_DATE; ?></th>
	    <th><?php echo TEXT_ACTION; ?></th>
	   </tr>
	  </thead>
	  <tbody class="ui-widget-content">
	   <tr>
	    <td align="center"><?php echo html_pull_down_menu('crm_rep_id', $sales_rep_array, $cInfo->crm_rep_id ? $cInfo->crm_rep_id : $_SESSION['account_id']); ?></td>
	    <td align="center"><?php echo html_calendar_field($cal_c_gen); ?></td>
	    <td align="center"><?php echo html_pull_down_menu('crm_action', gen_build_pull_down($crm_actions), $cInfo->crm_action); ?></td>
	   </tr>
	   <tr>
	    <td colspan="3" align="center"><?php echo '<br />' . html_textarea_field('crm_note', 60, 1, $cInfo->crm_note, ''); ?></td>
	   </tr>
	  </tbody>
	 </table>
	 <table class="ui-widget" style="border-collapse:collapse;width:100%">
	  <thead  class="ui-widget-header">
	   <tr><th colspan="4"><?php echo TEXT_HISTORY; ?></th></tr>
	  </thead>
	  <tbody class="ui-widget-content">
<?php if (sizeof($cInfo->crm_log) > 0) foreach ($cInfo->crm_log as $value) { ?>
	   <tr id="tr_crm_a_<?php echo $value->log_id; ?>" class="odd">
	    <td><?php echo $security_level < 4 ? '&nbsp;' : html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.CRM_ROW_DELETE_ALERT.'\')) deleteCRM('.$value->log_id.');"'); ?></td>
	    <td><?php echo $reps[$value->entered_by]; ?></td>
	    <td><?php echo gen_locale_date($value->log_date); ?></td>
	    <td><?php echo htmlspecialchars($crm_actions[$value->action]); ?></td>
	   </tr>
	   <tr id="tr_crm_b_<?php echo $value->log_id; ?>">
	    <td colspan="4"><?php echo htmlspecialchars($value->notes); ?></td>
	   </tr>
<?php } else { echo '<tr><td colspan="4">'.ACT_NO_RESULTS.'</td></tr>'; } ?>
	  </tbody>
	 </table>
	</div>
    <div style="width:50%"><?php echo html_textarea_field('address['.$type.'m][notes]', 60, 30, $cInfo->address[$type.'m']['notes']); ?></div>
  </fieldset>
</div>