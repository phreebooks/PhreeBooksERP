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
//  Path: /modules/accounts/pages/admin/template_tab_tools.php
//
?>
<div title="<?php echo TEXT_TOOLS;?>" id="tab_tools">
<fieldset>
<legend><?php echo INV_TOOLS_VALIDATE_INVENTORY; ?></legend>
<p><?php echo INV_TOOLS_VALIDATE_INV_DESC; ?></p>
  <table>
    <tr>
	  <th><?php echo INV_TOOLS_REPAIR_TEST; ?></th>
	  <th><?php echo INV_TOOLS_REPAIR_FIX; ?></th>
	</tr>
	<tr>
	  <td align="center"><?php echo html_button_field('inv_hist_test', INV_TOOLS_BTN_TEST,   'onclick="submitToDo(\'inv_hist_test\')"'); ?></td>
	  <td align="center"><?php echo html_button_field('inv_hist_fix',  INV_TOOLS_BTN_REPAIR, 'onclick="if (confirm(\'' . INV_TOOLS_REPAIR_CONFIRM . '\')) submitToDo(\'inv_hist_fix\')"'); ?></td>
	</tr>
  </table>
</fieldset>
<fieldset>
<legend><?php echo INV_TOOLS_VALIDATE_SO_PO; ?></legend>
<p><?php echo INV_TOOLS_VALIDATE_SO_PO_DESC; ?></p>
  <table>
    <tr>
	  <th><?php echo INV_TOOLS_REPAIR_SO_PO; ?></th>
	</tr>
	<tr>
	  <td align="center"><?php echo html_button_field('inv_on_order_fix', INV_TOOLS_BTN_SO_PO_FIX, 'onclick="submitToDo(\'inv_on_order_fix\')"'); ?></td>
	</tr>
  </table>
</fieldset>
</div>
