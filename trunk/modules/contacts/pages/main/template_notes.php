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
//  Path: /modules/contacts/pages/main/template_notes.php
//
?>

<div title="<?php echo TEXT_NOTES;?>">
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
	    <td align="center"><?php echo html_pull_down_menu('crm_rep_id', $basis->cInfo->sales_rep_array, $basis->cInfo->contact->crm_rep_id ? $basis->cInfo->contact->crm_rep_id : $_SESSION['user']->account_id); ?></td>
	    <td align="center"><?php echo html_calendar_field('crm_date', \core\classes\DateTime::createFromFormat(DATE_FORMAT, $basis->cInfo->contact->crm_date)); ?></td>
	    <td align="center"><?php echo html_pull_down_menu('crm_action', $basis->cInfo->contact->crm_actions, $basis->cInfo->contact->crm_action); ?></td>
	   </tr>
	   <tr>
	    <td colspan="3" align="center"><?php echo '<br />' . html_textarea_field('crm_note', 60, 1, $basis->cInfo->contact->crm_note, ''); ?></td>
	   </tr>
	  </tbody>
	 </table>
	 <table class="ui-widget" style="border-collapse:collapse;width:100%">
	  <thead  class="ui-widget-header">
	   <tr><th colspan="5"><?php echo TEXT_HISTORY; ?></th></tr>
	  </thead>
	  <tbody class="ui-widget-content">
<?php if (sizeof($basis->cInfo->contact->crm_log) > 0) foreach ($basis->cInfo->contact->crm_log as $value) { ?>
	   <tr id="tr_crm_a_<?php echo $value['log_id']; ?>" class="odd">
	    <td><?php echo $security_level < 4 ? '&nbsp;' : html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', "onclick='if (confirm(\"".CRM_ROW_DELETE_ALERT."\")) deleteCRM({$value['log_id']});'"); ?></td>
	    <td><?php echo $value['with']; ?></td>
	    <td><?php echo $basis->cInfo->all_employees[$value['entered_by']]['text']; ?></td>
	    <td><?php echo \core\classes\DateTime::createFromFormat(DATE_FORMAT, $value['log_date']); ?></td>
	    <td><?php echo $basis->cInfo->contact->crm_actions[$value['action']]['text']; ?></td>
	   </tr>
	   <tr id="tr_crm_b_<?php echo $value->log_id; ?>">
	    <td colspan="4"><?php echo htmlspecialchars($value['notes']); ?></td>
	   </tr>
<?php } else { echo '<tr><td colspan="4">'.TEXT_NO_RESULTS_FOUND.'</td></tr>'; } ?>
	  </tbody>
	 </table>
	</div>
    <div style="width:50%"><?php echo html_textarea_field("address[{$basis->cInfo->contact->type}m][notes]", 60, 30, $basis->cInfo->contact->address[$basis->cInfo->contact->type.'m']['notes']); ?></div>
  </fieldset>
</div>