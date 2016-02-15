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
//  Path: /modules/work_orders/pages/main/template_build.php
//
echo html_form('work_orders', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'id')), 'post', '');
// include hidden fields
echo html_hidden_field('action',   '')      . chr(10);
echo html_hidden_field('rowSeq', '')      . chr(10);
echo html_hidden_field('id',     $id)     . chr(10);
echo html_hidden_field('wo_id',  $wo_id)  . chr(10);
echo html_hidden_field('sku_id', $sku_id) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
if (!$hide_save && ((!$id && $security_level > 1) || ($id && $security_level > 2))) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
  $toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_help('07.04.WO.04');
echo $toolbar->build();
?>
<h1><?php echo TEXT_BUILD_AND_TRACK_WORK_ORDER . (!$id ? '' : ' - ' . $id); ?></h1>
  <div id="inv_image" title="<?php echo $sku; ?>">
    <?php if ($image) echo html_image(DIR_WS_MY_FILES . $_SESSION['user']->company . '/inventory/images/' . $image, '', 600) . chr(10);
			else echo TEXT_NO_IMAGE; ?>
    <div>
	  <h2><?php echo TEXT_SKU . ': ' . $sku; ?></h2>
	  <p><?php echo '<br />' . $wo_title; ?></p>
    </div>
  </div>

<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
  <tr>
	<td align="right"><?php echo TEXT_SKU; ?> </td>
    <td><?php echo html_input_field('sku', $sku, ($id ? 'readonly="readonly" ' : '') . 'size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '"') . '&nbsp;';
	  if (!$id) echo html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="WOList()"'); ?></td>
	<td align="right"><?php echo TEXT_QUANTITY; ?></td>
	<td><?php echo html_input_field('qty', $qty, ($id ? 'readonly="readonly" ' : '') . 'size="5" style="text-align:right"'); ?></td>
    <td align="right"><?php echo TEXT_POST_DATE; ?></td>
	<td><?php echo html_input_field('post_date', \core\classes\DateTime::createFromFormat(DATE_FORMAT, $post_date), 'readonly="readonly" size="12"'); ?></td>
<?php if ($image) {
		echo '<td rowspan="4">';
		echo html_image(DIR_WS_MY_FILES . $_SESSION['user']->company . '/inventory/images/' . $image, $image, '', '100', 'rel="#photo1"');
		echo '</td>';
} ?>
  </tr>
  <tr>
	<td align="right"><?php echo TEXT_WORK_ORDER_TITLE; ?> </td>
    <td><?php echo html_input_field('wo_title', $wo_title, 'readonly="readonly" size="33"') . '&nbsp;'; ?></td>
	<td align="right"><?php echo TEXT_PRIORITY; ?></td>
	<td><?php echo html_input_field('priority', $priority, 'readonly="readonly" size="5" style="text-align:right"'); ?></td>
	<td align="right"><?php echo TEXT_CLOSE; ?></td>
	<td><?php echo html_checkbox_field('closed', '1', $closed ? true : false, '', 'disabled="disabled"') . ($closed ? (' ' . \core\classes\DateTime::createFromFormat(DATE_FORMAT, $close_date)) : ''); ?></td>
  </tr>
  <tr>
    <td colspan="6"><?php echo TEXT_WORK_ORDER_NOTES_AND_COMMENTS; ?></td>
  </tr>
  <tr>
	<td colspan="6"><?php echo html_textarea_field('notes', 80, 3, $notes, $params = ''); ?></td>
  </tr>
 </tbody>
</table>
<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
 <thead class="ui-widget-header">
	<tr>
	  <th><?php echo TEXT_STEP; ?></th>
	  <th><?php echo TEXT_TASK_NAME; ?></th>
	  <th><?php echo TEXT_TASK_DESCRIPTION; ?></th>
	  <th><?php echo TEXT_MFG_INIT; ?></th>
	  <th><?php echo TEXT_QA_INIT; ?></th>
	  <th><?php echo TEXT_ACTION; ?></th>
	</tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
	$last_complete = 0;
	$working_step  = 0;
	$odd = true;
    foreach ($step_list as $value) {
	  if ($value['complete'] == '1') $last_complete = $value['step'];
	  if ($value['step'] == $last_complete + 1) $working_step = $value['step'];
	  $mfg_id = ($value['mfg'] == '1') ? get_user_name($value['mfg_id']) : '-';
	  $qa_id  = ($value['qa'] == '1')  ? get_user_name($value['qa_id'])  : '-';
	  $mfg_approved = $value['mfg_id'] <> 0 ? true : false;
	  $qa_approved  = $value['qa_id'] <> 0  ? true : false;
	  if ($value['step'] == $working_step) {
	    echo '  <tr class="' . ($odd?'odd':'even') . '">' . chr(10);
	    echo '    <th align="center">' . $value['step'] . '</th>' . chr(10);
	    echo '    <td>' . $value['task_name'] . '</td>' . chr(10);
	    echo '    <td colspan="4">' . $value['description'] . '</td>' . chr(10);
	    echo '  </tr>' . chr(10);
		if ($value['data_entry'] == '1') {
	      echo '  <tr>' . chr(10);
	      echo '    <th colspan="2">' . TEXT_ENTRY_VALUE . '</th>' . chr(10);
	      echo '    <td colspan="4">' . html_input_field('data_value', $data_value ? $data_value : $value['data_value'], 'size="65" maxlength="64"') . '</td>' . chr(10);
	      echo '  </tr>' . chr(10);
		}
	    echo '  <tr>' . chr(10);
	    echo '    <th colspan="3" class="ui-state-highlight">' . TEXT_APPROVALS . '</th>' . chr(10);
	    if ($value['mfg'] == '1') {
		  if (!$mfg_approved) {
	        echo '    <td align="center">' . TEXT_MFG . '&nbsp;' . html_pull_down_menu('user_mfg', $user_list, $user_mfg) . '&nbsp;' . html_password_field('pw_mfg', '') . '</td>' . chr(10);
	      } else {
	        echo '    <td align="center">' . TEXT_MFG . ' - ' . get_user_name($value['mfg_id']) . '</td>' . chr(10);
		  }
		} else {
	      echo '    <td align="center">' . TEXT_MFG . ' - ' . TEXT_NA . '</td>' . chr(10);
	    }
	    if ($value['qa'] == '1') {
		  if (!$qa_approved) {
	        echo '    <td align="center">' . TEXT_QA . '&nbsp;' . html_pull_down_menu('user_qa', $user_list, $user_qa) . '&nbsp;' . html_password_field('pw_qa', '') . '</td>' . chr(10);
	      } else {
	        echo '    <td align="center">' . TEXT_QA . ' - ' . get_user_name($value['qa_id']) . '</td>' . chr(10);
		  }
	    } else {
	      echo '    <td align="center">' . TEXT_QA . ' - ' . TEXT_NA . '</td>' . chr(10);
	    }
	    echo '    <td align="center">' . html_button_field('complete', TEXT_UPDATE, 'onclick="submitSeq(' . $value['step'] . ', \'save_step\')"') . '</td>' . chr(10);
	    echo '  </tr>' . chr(10);
	  } else {
	    echo '  <tr class="' . ($odd?'odd':'even') . '">' . chr(10);
	    echo '    <td align="center">' . $value['step'] . '</td>' . chr(10);
	    echo '    <td>' . $value['task_name']    . '</td>' . chr(10);
		if ($value['data_entry'] == '1') {
	      echo '    <td>' . $value['description'] . '<br />' . TEXT_ENTRY_VALUE . ' ';
	      echo html_input_field('data_temp', $data_value ? $data_value : $value['data_value'], 'readonly="readonly" size="65" maxlength="64"');
	      echo '    </td>' . chr(10);
		} else {
	      echo '    <td>' . $value['description']  . '</td>' . chr(10);
		}
	    if ($value['mfg'] == '1' && $value['complete'] == '1') {
	      echo '    <td align="center">' . get_user_name($value['mfg_id']) . '</td>' . chr(10);
	    } else {
	      echo '    <td align="center">&nbsp;</td>' . chr(10);
	    }
	    if ($value['qa'] == '1' && $value['complete'] == '1') {
	      echo '    <td align="center">' . get_user_name($value['qa_id']) . '</td>' . chr(10);
	    } else {
	      echo '    <td align="center">&nbsp;</td>' . chr(10);
	    }
	    echo '    <td>' . '&nbsp;' . '</td>' . chr(10);
	    echo '  </tr>' . chr(10);
	  }
	  $odd = !$odd;
	}
?>
 </tbody>
</table>
</form>
