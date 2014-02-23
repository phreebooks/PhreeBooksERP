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
//  Path: /modules/phreedom/pages/admin/template_tab_tools.php
//
?>
<div title="<?php echo TEXT_TOOLS;?>" id="tab_tools">
<fieldset>
<legend><?php echo GEN_ADM_TOOLS_SEQ_HEADING; ?></legend>
<p><?php echo GEN_ADM_TOOLS_SEQ_DESC; ?></p>
  <table>
<?php 
  foreach ($status_fields as $field_name) {
    $desc = strtoupper($field_name) . '_DESC';
	echo '    <tr>' . chr(10);
	echo '      <td>' . (defined($desc) ? constant($desc) : $field_name) . '</td>' . chr(10);
	echo '      <td>' .  html_input_field($field_name, $status_values->fields[$field_name]) . '</td>' . chr(10);
	echo '    </tr>' . chr(10);
  }
?>
	<tr>
	  <td colspan="2" align="right"><?php echo html_button_field('ordr_nums', GEN_ADM_TOOLS_BTN_SAVE, 'onclick="submitToDo(\'ordr_nums\')"'); ?></td>
    </tr>
  </table>
</fieldset>

<fieldset>
<legend><?php echo GEN_ADM_TOOLS_SECURITY_HEADING; ?></legend>
  <p><?php echo GEN_ADM_TOOLS_SECURITY_DESC; ?></p>
  <p align="center">
    <?php echo TEXT_CLEAN_BEFORE . ' ' . html_calendar_field($cal_clean); ?>
	<?php echo html_button_field('clean_security', GEN_ADM_TOOLS_BTN_CLEAN, 'onclick="submitToDo(\'clean_security\')"'); ?>
  </p>
</fieldset>

</div>
