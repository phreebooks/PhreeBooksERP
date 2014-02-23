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
//  Path: /modules/phreeform/pages/popup_phreefrom/tab_security_setup.php
//
?>
<div title="<?php echo TEXT_PROPERTIES;?>"  id="tab_prop">
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
    <tr><th colspan="2"><?php echo TEXT_REPORT_PROPERTIES; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
<?php if ($report->reporttype == 'rpt') { ?>
	<tr>
	  <td><?php echo TEXT_TRUNC; ?></td>
	  <td>
        <?php echo html_radio_field('truncate', '1', ($report->truncate == '1') ? true : false) . TEXT_YES; ?>
	    <?php echo html_radio_field('truncate', '0', ($report->truncate <> '1') ? true : false) . TEXT_NO; ?>
	  </td>
	</tr>
	<tr>
	  <td><?php echo TEXT_TOTAL_ONLY; ?></td>
	  <td>
        <?php echo html_radio_field('totalonly', '1', ($report->totalonly == '1') ? true : false) . TEXT_YES; ?>
	    <?php echo html_radio_field('totalonly', '0', ($report->totalonly <> '1') ? true : false) . TEXT_NO; ?>
	  </td>
	</tr>
<?php } elseif ($report->reporttype == 'frm') { ?>
    <tr>
      <td><?php echo PHREEFORM_SERIAL_FORM; ?></td>
      <td><?php echo html_checkbox_field('serialform', '1', $report->serialform ? true : false, '', ''); ?></td>
    </tr>
	<tr>
	  <td><?php echo TEXT_SET_PRINTED_FLAG . '<sup>1</sup>'; $notes .= '<br /><sup>1 </sup>' . PHREEFORM_PRINTED_NOTE; ?></td>
	  <td><?php echo html_pull_down_menu('setprintedfield', $kFields, $report->setprintedfield, 'onclick="updateFieldList(this)"'); ?></td>
	</tr>
	<tr>
	  <td><?php echo TEXT_PAGE_BREAK_FIELD; ?></td>
	  <td><?php echo html_pull_down_menu('formbreakfield', $kFields, $report->formbreakfield, 'onclick="updateFieldList(this)"'); ?></td>
	</tr>
<?php } ?>
    <tr>
      <td width="50%"><?php echo TEXT_GROUP_MEMBER; ?></td>
	  <td width="50%"><?php echo $rFields; ?></td>
    </tr>
    <tr>
      <td><?php echo TEXT_FILENAME_SOURCE; ?></td>
	  <td><?php echo TEXT_PREFIX . html_input_field('filename_prefix', $report->filenameprefix, 'size="24"'); ?></td>
    </tr>
    <tr>
      <td><?php echo '&nbsp;'; ?></td>
	  <td><?php echo TEXT_SOURCE_FIELD . html_pull_down_menu('filename_field', $kFields, $report->filenamefield, 'onclick="updateFieldList(this)"'); ?></td>
    </tr>
  </tbody>
</table>
<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
    <tr><th colspan="2"><?php echo TEXT_SECURITY;  ?></th></tr>
    <tr><th><?php echo TEXT_USERS;  ?></th><th><?php echo TEXT_GROUPS; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
    <tr>
	  <td align="center"><?php echo html_checkbox_field('user_all',  '1', (in_array('0', $security['u'], true) ? true : false)) . ' ' . TEXT_ALL_USERS; ?></td>
	  <td align="center"><?php echo html_checkbox_field('group_all', '1', (in_array('0', $security['g'], true) ? true : false)) . ' ' . TEXT_ALL_GROUPS; ?></td>
    </tr>
    <tr>
	  <td width="50%" align="center"><?php echo html_pull_down_menu('users[]',  gen_get_pull_down(TABLE_USERS,  true, '1', 'admin_id', 'display_name'), $security['u'], 'multiple="multiple" size="20"'); ?></td>
	  <td width="50%" align="center"><?php echo html_pull_down_menu('groups[]', gen_get_pull_down(TABLE_DEPARTMENTS, true, '1'), $security['g'], 'multiple="multiple" size="20"'); ?></td>
    </tr>
  </tbody>
</table>
  <?php echo $notes; ?>
</div>
