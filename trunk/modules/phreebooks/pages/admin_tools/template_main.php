<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
<h1><?php echo GEN_ADM_TOOLS_TITLE; ?></h1>
<fieldset>
<legend><?php echo GL_UTIL_PERIOD_LEGEND; ?></legend>
 <table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
    <tr>
	  <td width="33%" valign="top">
		<?php echo '<p>' . GL_CURRENT_PERIOD . $period . '</p>'; 
		echo '<p>' . GL_UTIL_FISCAL_YEAR_TEXT . '</p>'; ?>
	  </td>
	  <td width="33%" valign="top"><table>
	    <tr>
		  <th><?php echo GL_FISCAL_YEAR; ?>
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
		echo '<p>' . html_button_field('change', GL_BTN_CHG_ACCT_PERIOD, 'onclick="fetchPeriod()"') . '</p>' . chr(10);
		echo '<p>' . html_button_field('update', GL_BTN_UPDATE_FY, 'onclick="submitToDo(\'update\')"') . '</p>' . chr(10);
		echo '<p>' . html_button_field('new', GL_BTN_NEW_FY, 'onclick="confirmNewYear()"') . '</p>' . chr(10);
		?>
	  </td>
    </tr>
  </tbody>
  </table>
</fieldset>

<fieldset>
<legend><?php echo GEN_ADM_TOOLS_REPOST_HEADING; ?></legend>
<p><?php echo GEN_ADM_TOOLS_REPOST_DESC; ?></p>
 <table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
    <tr>
	  <th colspan="2"><?php echo GEN_ADM_TOOLS_AR; ?></th>
	  <th colspan="2"><?php echo GEN_ADM_TOOLS_AP; ?></th>
	  <th colspan="2"><?php echo GEN_ADM_TOOLS_BNK_ETC; ?></th>
	  <th colspan="2"><?php echo GEN_ADM_TOOLS_DATE_RANGE; ?></th>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_9', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J09; ?></td>
	  <td><?php echo  html_checkbox_field('jID_3', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J03; ?></td>
	  <td><?php echo  html_checkbox_field('jID_2', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J02; ?></td>
  	  <td colspan="2"><?php echo GEN_ADM_TOOLS_START_DATE; ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_10', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J10; ?></td>
	  <td><?php echo  html_checkbox_field('jID_4', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J04; ?></td>
	  <td><?php echo  html_checkbox_field('jID_8', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J08; ?></td>
	  <td colspan="2"><?php echo html_calendar_field($cal_repost_start); ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_12', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J12; ?></td>
	  <td><?php echo  html_checkbox_field('jID_6', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J06; ?></td>
	  <td><?php echo  html_checkbox_field('jID_14', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J14; ?></td>
  	  <td colspan="2"><?php echo GEN_ADM_TOOLS_END_DATE; ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_13', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J13; ?></td>
	  <td><?php echo  html_checkbox_field('jID_7', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J07; ?></td>
	  <td><?php echo  html_checkbox_field('jID_16', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J16; ?></td>
	  <td colspan="2"><?php echo html_calendar_field($cal_repost_end); ?></td>
	</tr>
	<tr>
	  <td><?php echo  html_checkbox_field('jID_19', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J19; ?></td>
	  <td><?php echo  html_checkbox_field('jID_21', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J21; ?></td>
	  <td><?php echo  html_checkbox_field('jID_18', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J18; ?></td>
	</tr>
	<tr>
	  <td colspan="2">&nbsp;</td>
	  <td colspan="2">&nbsp;</td>
	  <td><?php echo  html_checkbox_field('jID_20', '1', false); ?></td>
	  <td><?php echo GEN_ADM_TOOLS_J20; ?></td>
	  <td colspan="2" align="right"><?php echo html_button_field('repost', GEN_ADM_TOOLS_BTN_REPOST, 'onclick="if (confirm(\'' . GEN_ADM_TOOLS_REPOST_CONFIRM . '\')) submitToDo(\'repost\')"'); ?></td>
	</tr>
  </tbody>
 </table>
</fieldset>

<fieldset>
<legend><?php echo GEN_ADM_TOOLS_INVENTORY_OWED; ?></legend>
<p><?php echo sprintf(GEN_ADM_TOOLS_INVENTORY_DESC, $cogs_owed); ?></p>
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
    <tr><th><?php echo GEN_ADM_TOOLS_OWED_FIX; ?></th></tr>
	<tr><td align="center"><?php echo html_button_field('inv_owed_fix', GEN_ADM_TOOLS_BTN_OWED, 'onclick="if (confirm(\'' . GEN_ADM_TOOLS_OWED_CONFIRM . '\')) submitToDo(\'inv_owed_fix\')"'); ?></td></tr>
  </tbody>
 </table>
</fieldset>

<fieldset>
<legend><?php echo GEN_ADM_TOOLS_REPAIR_CHART_HISTORY; ?></legend>
<p><?php echo GEN_ADM_TOOLS_REPAIR_CHART_DESC; ?></p>
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
    <tr>
	  <th><?php echo GEN_ADM_TOOLS_REPAIR_TEST; ?></th>
	  <th><?php echo GEN_ADM_TOOLS_REPAIR_FIX; ?></th>
	</tr>
	<tr>
	  <td align="center"><?php echo html_button_field('coa_hist_test', GEN_ADM_TOOLS_BTN_TEST, 'onclick="submitToDo(\'coa_hist_test\')"'); ?></td>
	  <td align="center"><?php echo html_button_field('coa_hist_fix', GEN_ADM_TOOLS_BTN_REPAIR, 'onclick="if (confirm(\'' . GEN_ADM_TOOLS_REPAIR_CONFIRM . '\')) submitToDo(\'coa_hist_fix\')"'); ?></td>
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
	      echo html_submit_field('purge_db', GL_BTN_PURGE_DB, 'onclick="if (confirm(\'' . GL_UTIL_PURGE_DB_CONFIRM . '\')) submitToDo(\'purge_db\')"');
	    ?>
	  </td>
    </tr>
  </tbody>
 </table>
</fieldset>
<?php } ?>


















