<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/phreebooks/pages/admin_tools/template_main.php
//
echo html_form('admin_tools', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('01');
echo $toolbar->build_toolbar();
// Build the page
?>
<h1><?php echo TEXT_ADMINISTRATIVE_TOOLS; ?></h1>
<fieldset>
<legend><?php echo TEXT_ACCOUNTING_PERIODS_AND_FISCAL_YEARS; ?></legend>
 <table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
    <tr>
	  <td width="33%" valign="top">
		<?php echo '<p>' . TEXT_CURRENT_ACCOUNTING_PERIOD_IS . ': ' . $period . '</p>';
		echo '<p>' . GL_UTIL_FISCAL_YEAR_TEXT . '</p>'; ?>
	  </td>
	  <td width="33%" valign="top"><table>
	    <tr>
		  <th><?php echo TEXT_FISCAL_YEAR; ?>
		  <?php echo html_pull_down_menu('fy', get_fiscal_year_pulldown(), $fy, 'onchange="submit()"'); ?></th>
	    </tr>
	    <tr>
		  <td><table id="item_table" class="ui-widget" style="border-collapse:collapse;width:100%">
		   <thead class="ui-widget-header">
		    <tr>
			  <th><?php echo TEXT_PERIOD; ?></th>
			  <th><?php echo TEXT_START_DATE; ?></th>
			  <th><?php echo TEXT_END_DATE; ?></th>
		    </tr>
		   </thead>
		   <tbody class="ui-widget-content">
		    <?php
		  $i = 0;
		  foreach ($fy_array as $key => $value) {
			echo '<tr><td width="33%" align="center">' . $key . html_hidden_field('per_' . $i, $key) . '</td>' . chr(10);
			if ($key > $max_period) { // only allow changes if nothing has been posted above this period
				echo '<td width="33%" nowrap="nowrap">' . html_calendar_field($cal_start[$i]) . '</td>' . chr(10);
				echo '<td width="33%" nowrap="nowrap">' . html_calendar_field($cal_end[$i]) . '</td>' . chr(10);
			} else {
				echo '<td width="33%" align="center" nowrap="nowrap">' . html_input_field('start_' . $i, gen_locale_date($value['start']), 'readonly="readonly"', false, 'text', false) . '</td>' . chr(10);
				echo '<td width="33%" align="center" nowrap="nowrap">' . html_input_field('end_' . $i, gen_locale_date($value['end']), 'readonly="readonly"', false, 'text', false) . '</td>' . chr(10);
			}
			echo '</tr>' . chr(10);
			$i++;
		  } ?>
		   </tbody>
		  </table></td>
	    </tr>
	  </table></td>
	  <td width="33%" valign="top" align="right">
		<?php echo html_hidden_field('period', '') . chr(10);
		echo '<p>' . html_button_field('change', TEXT_CHANGE_CURRENT_ACCOUNTING_PERIOD, 'onclick="fetchPeriod()"') . '</p>' . chr(10);
		echo '<p>' . html_button_field('update', TEXT_UPDATE_FISCAL_YEAR_CHANGES, 'onclick="submitToDo(\'update\')"') . '</p>' . chr(10);
		echo '<p>' . html_button_field('new', TEXT_GENERATE_NEXT_FISCAL_YEAR, 'onclick="confirmNewYear()"') . '</p>' . chr(10);
		?>
	  </td>
    </tr>
  </tbody>
  </table>
</fieldset>

<fieldset>
<legend><?php echo TEXT_RE-POST_JOURNAL_ENTRIES; ?></legend>
<p><?php echo GEN_ADM_TOOLS_REPOST_DESC; ?></p>
 <table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
    <tr>
	  <th colspan="2"><?php echo TEXT_CUSTOMER_RECEIVABLES; ?></th>
	  <th colspan="2"><?php echo TEXT_VENDORS_PAYABLES; ?></th>
	  <th colspan="2"><?php echo TEXT_BANKING_OR_INVENTORY_OR_OTHER; ?></th>
	  <th colspan="2"><?php echo TEXT_RE-POST_DATE_RANGE; ?></th>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_9','1', false); ?></td>
	  <td><?php echo $journal_types_list[9]['text']	. ' (09)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_3','1', false); ?></td>
	  <td><?php echo $journal_types_list[3]['text']	. ' (03)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_2','1', false); ?></td>
	  <td><?php echo $journal_types_list[2]['text']	. ' (02)'; ?></td>
  	  <td colspan="2"><?php echo TEXT_START_DATE; ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_10','1', false); ?></td>
	  <td><?php echo $journal_types_list[10]['text'] . ' (10)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_4', '1', false); ?></td>
	  <td><?php echo $journal_types_list[4]['text']  . ' (04)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_8', '1', false); ?></td>
	  <td><?php echo $journal_types_list[8]['text']  . ' (08)'; ?></td>
	  <td colspan="2"><?php echo html_calendar_field($cal_repost_start); ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_12','1', false); ?></td>
	  <td><?php echo $journal_types_list[12]['text'] . ' (12)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_6', '1', false); ?></td>
	  <td><?php echo $journal_types_list[6]['text'] . ' (06)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_14','1', false); ?></td>
	  <td><?php echo $journal_types_list[14]['text'] . ' (14)'; ?></td>
  	  <td colspan="2"><?php echo TEXT_END_DATE; ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_13','1', false); ?></td>
	  <td><?php echo $journal_types_list[13]['text'] . ' (13)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_7', '1', false); ?></td>
	  <td><?php echo $journal_types_list[7]['text']	 . ' (07)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_16','1', false); ?></td>
	  <td><?php echo $journal_types_list[16]['text'] . ' (16)'; ?></td>
	  <td colspan="2"><?php echo html_calendar_field($cal_repost_end); ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_19', '1', false); ?></td>
	  <td><?php echo $journal_types_list[19]['text'] . ' (19)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_21', '1', false); ?></td>
	  <td><?php echo $journal_types_list[21]['text'] . ' (21)'; ?></td>
	  <td><?php echo  html_checkbox_field('jID_18', '1', false); ?></td>
	  <td><?php echo $journal_types_list[18]['text'] . ' (18)'; ?></td>
	</tr>
	<tr>
	  <td colspan="2">&nbsp;</td>
	  <td colspan="2">&nbsp;</td>
	  <td><?php echo  html_checkbox_field('jID_20', '1', false); ?></td>
	  <td><?php echo $journal_types_list[20]['text'] . ' (20)'; ?></td>
	  <td colspan="2" align="right"><?php echo html_button_field('repost', TEXT_RE-POST_JOURNALS, 'onclick="if (confirm(\'' . GEN_ADM_TOOLS_REPOST_CONFIRM . '\')) submitToDo(\'repost\')"'); ?></td>
	</tr>
  </tbody>
 </table>
</fieldset>

<fieldset>
<legend><?php echo TEXT_RE-POST_INVENTORY_OWED; ?></legend>
<p><?php echo sprintf(GEN_ADM_TOOLS_INVENTORY_DESC, $cogs_owed); ?></p>
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
    <tr><th><?php echo GEN_ADM_TOOLS_OWED_FIX; ?></th></tr>
	<tr><td align="center"><?php echo html_button_field('inv_owed_fix', TEXT_START_RE-POST, 'onclick="if (confirm(\'' . GEN_ADM_TOOLS_OWED_CONFIRM . '\')) submitToDo(\'inv_owed_fix\')"'); ?></td></tr>
  </tbody>
 </table>
</fieldset>

<fieldset>
<legend><?php echo TEXT_VALIDATE_AND_REPAIR_GENERAL_LEDGER_ACCOUNT_BALANCES; ?></legend>
<p><?php echo GEN_ADM_TOOLS_REPAIR_CHART_DESC; ?></p>
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
    <tr>
	  <th><?php echo TEXT_TEST_CHART_BALANCES; ?></th>
	  <th><?php echo TEXT_FIX_CHART_BALANCE_ERRORS; ?></th>
	</tr>
	<tr>
	  <td align="center"><?php echo html_button_field('coa_hist_test', TEXT_TEST_GL_BALANCES, 'onclick="submitToDo(\'coa_hist_test\')"'); ?></td>
	  <td align="center"><?php echo html_button_field('coa_hist_fix', TEXT_REPAIR_GL_BALANCE_ERRORS, 'onclick="if (confirm(\'' . GEN_ADM_TOOLS_REPAIR_CONFIRM . '\')) submitToDo(\'coa_hist_fix\')"'); ?></td>
	</tr>
  </tbody>
 </table>
</fieldset>

<?php if ($security_level == 4) { ?>
<fieldset>
<legend><?php echo GL_UTIL_PURGE_ALL; ?></legend>
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
    <tr>
	  <td><?php echo GL_UTIL_PURGE_DB; ?></td>
	  <td valign="top" align="right">
	    <?php echo html_input_field('purge_confirm', '', 'size="10" maxlength="10"') . ' ';
	      echo html_submit_field('purge_db', TEXT_PURGE_JOURNAL_ENTRIES, 'onclick="if (confirm(\'' . GL_UTIL_PURGE_DB_CONFIRM . '\')) submitToDo(\'purge_db\')"');
	    ?>
	  </td>
    </tr>
  </tbody>
 </table>
</fieldset>
<?php } ?>


















