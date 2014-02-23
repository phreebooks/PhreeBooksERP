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
//  Path: /modules/shipping/pages/admin/template_tab_tools.php
//
?>
<div title="<?php echo TEXT_TOOLS;?>" id="tab_tools">
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
    <tr><th colspan="2"><?php echo SHIPPING_TOOLS_TITLE; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
    <tr>
	  <td colspan="2"><?php echo SHIPPING_TOOLS_CLEAN_LOG_DESC; ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo SHIPPING_METHOD; ?></td>
	  <td><?php echo html_pull_down_menu('carrier', gen_build_pull_down($admin_classes['shipping']->methods, true), $_POST['carrier'] ? $_POST['carrier'] : ''); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo SHIPPING_MONTH; ?></td>
	  <td><?php echo html_pull_down_menu('fy_month', $sel_fy_month, $_POST['fy_month'] ? $_POST['fy_month'] : '01'); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo SHIPPING_YEAR; ?></td>
	  <td><?php echo html_pull_down_menu('fy_year', $sel_fy_year, $_POST['fy_year'] ? $_POST['fy_year'] : date('Y')); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo GEN_BACKUP_COMP_TYPE; ?></td>
	  <td>
	    <?php echo html_radio_field('conv_type', 'zip',  true,  '', '') . GEN_COMP_ZIP . '&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
	    <?php echo html_radio_field('conv_type', 'bz2',  false, '', '') . GEN_COMP_BZ2; ?>
	  </td>
    </tr>
	<tr>
	  <td align="right"><?php echo html_button_field('backup', GEN_ADM_TOOLS_BTN_BACKUP, 'onclick="submitToDo(\'backup\', true)"'); ?></td>
	  <td><?php echo html_button_field('clean',  GEN_ADM_TOOLS_BTN_CLEAN,  'onclick="if (confirm(\'' . GEN_ADM_TOOLS_BTN_CLEAN_CONFIRM . '\')) submitToDo(\'clean\')"'); ?></td>
	</tr>
  </tbody>
</table>
</div>
