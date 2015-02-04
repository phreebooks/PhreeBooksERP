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
//  Path: /modules/phreedom/pages/import_export/template_beg_bal.php
//
echo html_form('import_export', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=import_export', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save_bb\')"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('03.04.02');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-style:none;width:100%">
  <tr><td valign="top" >
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr>
		<th><?php echo TEXT_GL_ACCOUNT; ?></th>
		<th><?php echo TEXT_DESCRIPTION; ?></th>
		<th><?php echo TEXT_ACCOUNT_TYPE; ?></th>
		<th nowrap="nowrap"><?php echo TEXT_DEBIT_AMOUNT; ?></th>
		<th nowrap="nowrap"><?php echo TEXT_CREDIT_AMOUNT; ?></th>
	  </tr>
	 </thead>
	 <tbody class="ui-widget-content">
		<?php $i = 0;
		foreach ($glEntry->beg_bal as $coa_id => $values) { ?>
		  <tr>
			<td align="center"><?php echo $coa_id; ?></td>
			<td><?php echo htmlspecialchars($values['desc']); ?></td>
			<td><?php echo $values['type_desc']; ?></td>
		  <?php if ($coa_types[$values['type']]['asset']) { ?>
			<td align="center"><?php echo html_input_field('coa_value['.$i.']', (($values['beg_bal'] <> 0) ? $currencies->precise($values['beg_bal']) : '0'), 'style="text-align:right" size="13" maxlength="12" onchange="updateBalance()"'); ?></td>
			<td align="center" class="ui-state-highlight">&nbsp;</td>
		  <?php } else { ?>
			<td align="center" class="ui-state-highlight">&nbsp;</td>
			<td align="center"><?php echo html_input_field('coa_value['.$i.']', (($values['beg_bal'] <> 0) ? $currencies->precise(-$values['beg_bal']) : '0'), 'style="text-align:right" size="13" maxlength="12" onchange="updateBalance()"'); ?></td>
		  <?php } ?>
		  </tr>
		  <?php $i++;
		} ?>
	 </tbody>
	 <tfoot class="ui-widget-header">
	  <tr>
		<td colspan="3" align="right"><?php echo TEXT_TOTAL; ?></td>
		<td align="right"><?php echo html_input_field('debit_total', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
		<td align="right"><?php echo html_input_field('credit_total', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
	  </tr>
	  <tr>
		<td colspan="4" align="right"><?php echo GL_OUT_OF_BALANCE; ?></td>
		<td align="right"><?php echo html_input_field('balance_total', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
	  </tr>
	 </tfoot>
	</table>
  </td>
  <td valign="top" >
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
		  <tr><th colspan="2"><?php echo TEXT_IMPORT_JOURNAL_ENTRIES; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
		<td><h3><?php echo GL_BB_IMPORT_INVENTORY; ?></h3></td>
		<td align="right"><?php echo html_file_field('file_name_inv'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GL_BB_IMPORT_HELP_MSG; ?></td>
		<td align="right"><?php echo html_button_field('import_inv', GL_BB_IMPORT_INVENTORY, 'onclick="submitToDo(\'import_inv\')"'); ?></td>
	  </tr>
	  <tr><td colspan="2"><hr /></td></tr>
	  <tr>
		<td><h3><?php echo GL_BB_IMPORT_PURCH_ORDERS; ?></h3></td>
		<td align="right"><?php echo html_file_field('file_name_po'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GL_BB_IMPORT_HELP_MSG; ?></td>
		<td align="right"><?php echo html_button_field('import_po', GL_BB_IMPORT_PURCH_ORDERS, 'onclick="submitToDo(\'import_po\')"'); ?></td>
	  </tr>
	  <tr><td colspan="2"><hr /></td></tr>
	  <tr>
		<td><h3><?php echo GL_BB_IMPORT_PAYABLES; ?></h3></td>
		<td align="right"><?php echo html_file_field('file_name_ap'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GL_BB_IMPORT_HELP_MSG; ?></td>
		<td align="right"><?php echo html_button_field('import_ap', GL_BB_IMPORT_PAYABLES, 'onclick="submitToDo(\'import_ap\')"'); ?></td>
	  </tr>
	  <tr><td colspan="2"><hr /></td></tr>
	  <tr>
		<td><h3><?php echo GL_BB_IMPORT_SALES_ORDERS; ?></h3></td>
		<td align="right"><?php echo html_file_field('file_name_so'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GL_BB_IMPORT_HELP_MSG; ?></td>
		<td align="right"><?php echo html_button_field('import_so', GL_BB_IMPORT_SALES_ORDERS, 'onclick="submitToDo(\'import_so\')"'); ?></td>
	  </tr>
	  <tr><td colspan="2"><hr /></td></tr>
	  <tr>
		<td><h3><?php echo GL_BB_IMPORT_RECEIVABLES; ?></h3></td>
		<td align="right"><?php echo html_file_field('file_name_ar'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GL_BB_IMPORT_HELP_MSG; ?></td>
		<td align="right"><?php echo html_button_field('import_ar', GL_BB_IMPORT_RECEIVABLES, 'onclick="submitToDo(\'import_ar\')"'); ?></td>
	  </tr>
	 </tbody>
	</table>
  </td></tr>
</table>
</form>