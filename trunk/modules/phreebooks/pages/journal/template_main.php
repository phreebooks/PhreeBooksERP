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
//  Path: /modules/phreebooks/pages/journal/template_main.php
//
echo html_form('journal', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
$hidden_fields = NULL;
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('id', $cInfo->id) . chr(10);
echo html_hidden_field('recur_id', $cInfo->recur_id ? $cInfo->recur_id : 0) . chr(10);	// recur entry flag - number of recurs
echo html_hidden_field('recur_frequency', $cInfo->recur_frequency ? $cInfo->recur_frequency : 0) . chr(10);	// recur entry flag - how often
if (!ENABLE_MULTI_BRANCH) echo html_hidden_field('store_id', '0') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['params']   = 'onclick="OpenGLList()"';
$toolbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . GL_DELETE_ALERT . '\')) submitToDo(\'delete\')"';
if ($security_level < 4) $toolbar->icon_list['delete']['show'] = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
if ($security_level < 2) $toolbar->icon_list['save']['show'] = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('new',   'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"', $order = 2);
$toolbar->add_icon('copy',  'onclick="verifyCopy()"', 9);
if (!$cInfo->id) $toolbar->add_icon('recur', 'onclick="OpenRecurList(this)"', 10);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.06.02');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo GL_ENTRY_TITLE; ?></h1>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
  <tr>
    <td><table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
	 <tbody class="ui-widget-content">
	  <tr>
		<td><?php echo TEXT_REFERENCE . ' ' . html_input_field('purchase_invoice_id', $cInfo->purchase_invoice_id, 'size="21" maxlength="20"'); ?></td>
		<td><?php echo TEXT_POST_DATE . ' ' . html_calendar_field($cal_gl); ?></td>
		<td><?php echo TEXT_AUTO_COMPLETE . ' ' . html_checkbox_field('auto_complete', '1', '1'); ?></td>

	  <?php if (ENABLE_MULTI_BRANCH) { ?>
		<td><?php echo GEN_STORE_ID . ' ' . html_pull_down_menu('store_id', gen_get_store_ids(), $cInfo->store_id ? $cInfo->store_id : $_SESSION['admin_prefs']['def_store_id']); ?></td>
	  <?php } ?>
	  </tr>
	 </tbody>
	</table></td>
  </tr>
  <tr>
	<td><table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
	 <thead class="ui-widget-header">
	  <tr>
		<th width="5%"  align="center"><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
		<th width="15%" align="center"><?php echo TEXT_GL_ACCOUNT; ?></th>
		<th width="50%" align="center"><?php echo TEXT_DESCRIPTION; ?></th>
		<th width="15%" nowrap="nowrap" align="center"><?php echo TEXT_DEBIT_AMOUNT; ?></th>
		<th width="15%" nowrap="nowrap" align="center"><?php echo TEXT_CREDIT_AMOUNT; ?></th>
	  </tr>
	 </thead>
	 <tbody id="item_table" class="ui-widget-content">
	  <?php
		if (!isset($cInfo->id_1)) {
		  $hidden_fields .= '<script type="text/javascript">addGLRow();</script>';
		} else {
		  $i = 1;
		  while (true) {
		    $id = 'id_' . $i;
		    if (!isset($cInfo->$id)) break;
			echo '<tr>' . chr(10);
			echo '  <td align="center">';
			// Hidden fields
			echo html_hidden_field($id, $cInfo->$id) . chr(10);
			// End hidden fields
			echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . INV_MSG_DELETE_INV_ITEM . '\')) removeGLRow(' . $i . ');"');
			echo '  </td>' . chr(10);
			$acct = 'acct_' . $i;
			echo '  <td align="center" nowrap="nowrap">';
			echo html_pull_down_menu($acct, $gl_array_list, $cInfo->$acct, '') . chr(10);
			$desc = 'desc_' . $i;
			echo '  <td>' . html_input_field($desc, $cInfo->$desc, 'size="64" maxlength="64"') . '</td>' . chr(10);
			$debit = 'debit_' . $i;
			echo '  <td align="right">' . html_input_field($debit, $cInfo->$debit, 'style="text-align:right" size="13" maxlength="20" onchange="formatRow(' . $i . ', \'d\')"') . '</td>' . chr(10);
			$credit = 'credit_' . $i;
			echo '  <td align="right">' . html_input_field($credit, $cInfo->$credit, 'style="text-align:right" size="13" maxlength="20" onchange="formatRow(' . $i . ', \'c\')"') . '</td>' . chr(10);
			echo '</tr>' . chr(10);
			echo '<tr>' . chr(10);
			echo '  <td colspan="3">&nbsp;</td>' . chr(10);
			echo '  <td colspan="2" id="msg_' . $i . '">&nbsp;</td>' . chr(10);
			echo '</tr>' . chr(10);
			$i++;
		  }
		} ?>
	 </tbody>
	</table></td>
  </tr>
  <tr>
	<td><table class="ui-widget" style="border-style:none;width:100%">
	 <tbody class="ui-widget-content">
      <tr>
        <td colspan="5" align="left"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addGLRow()"'); ?></td>
      </tr>
      <tr>
	    <td width="70%" colspan="3" align="right"><?php echo GL_TOTALS; ?></td>
	    <td width="15%" align="right"><?php echo html_input_field('debit_total', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
	    <td width="15%" align="right"><?php echo html_input_field('credit_total', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
      </tr>
	  <tr>
	    <td width="20%" colspan="2" align="right">&nbsp;</td>
	    <td width="65%" colspan="2" align="right"><?php echo GL_OUT_OF_BALANCE; ?></td>
	    <td width="15%" align="right"><?php echo html_input_field('balance_total', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
	  </tr>
	 </tbody>
	</table></td>
  </tr>
 </tbody>
</table>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
  <tbody class="ui-widget-content">
    <tr>
      <td><?php echo TEXT_SELECT_FILE_TO_ATTACH . ' ' . html_file_field('file_name'); ?></td>
    </tr>
    <tr>
      <td><div id="show_attach" style="display:none">
		<?php echo html_checkbox_field('rm_attach', '1', ($order->del_attach) ? true : false, '', '') . ' ' . TEXT_DELETE_ATTACHMENT; ?>
    	<?php echo html_button_field('dn_attach', TEXT_DOWNLOAD_ATTACHMENT, 'onclick="downloadAttachment()"'); ?>
	    </div>&nbsp;
	  </td>
    </tr>
  </tbody>
</table>

<?php // display the hidden fields that are not used in this rendition of the form
echo $hidden_fields;
?>
</form>
