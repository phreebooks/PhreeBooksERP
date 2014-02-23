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
//  Path: /modules/work_orders/pages/builder/template_tab_gen.php
//
$hidden_fields = '';
?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
  <div id="inv_image" title="<?php echo $wo_title; ?>">
    <?php if ($image_with_path) echo html_image(DIR_WS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $image_with_path, '', 600) . chr(10);
			else echo TEXT_NO_IMAGE; ?>
    <div>
	  <h2><?php echo TEXT_WO_TITLE . ': ' . $wo_title; ?></h2>
	  <p><?php echo '<br />' . $description; ?></p>
    </div>
  </div>

  <table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
   <tbody class="ui-widget-content">
    <tr>
	  <td align="right"><?php echo TEXT_WO_TITLE . '&nbsp;'; ?></td>
	  <td><?php echo html_input_field('wo_title', $wo_title, 'size="33" maxlength="32"', true); ?></td>
	  <td align="right"><?php echo TEXT_REVISION . '&nbsp;'; ?></td>
	  <td>
	    <?php echo html_input_field('revision', $revision ? $revision : '0', 'readonly="readonly" size="4"'); ?>
	    <?php echo '&nbsp;' . TEXT_USE_ALLOCATION . '&nbsp;'; ?>
	    <?php echo html_pull_down_menu('allocate', $yes_no_array, $allocate); ?>
	  </td>
	  <td align="center" rowspan="3">
		<?php if ($image_with_path) { // show image if it is defined
			echo html_image(DIR_WS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $image_with_path, $image_with_path, '', '100', 'rel="#photo1"');
		} else echo '&nbsp;'; ?>
	  </td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_SKU . '&nbsp;'; ?></td>
	  <td><?php echo html_input_field('sku', $sku, ($lock_title ? 'readonly="readonly" ' : '') . 'size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '"', true) . '&nbsp;' . ($lock_title ? '' : html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="itemList()"')); ?></td>
	  <td align="right"><?php echo TEXT_DESCRIPTION . '&nbsp;'; ?></td>
	  <td><?php echo html_input_field('description', $description, 'size="65"', false); ?></td>
	</tr>
	<tr>
	  <td align="right"><?php echo TEXT_DOCUMENTS . '&nbsp;'; ?></td>
	  <td><?php echo html_input_field('ref_doc', $ref_doc, 'size="32"', false); ?></td>
	  <td align="right"><?php echo TEXT_DRAWINGS . '&nbsp;'; ?></td>
	  <td><?php echo html_input_field('ref_spec', $ref_spec, 'size="32"', false); ?></td>
	</tr>
   </tbody>
  </table>
  <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
   <thead class="ui-widget-header">
        <tr>
          <th><?php echo TEXT_STEP; ?></th>
          <th><?php echo TEXT_PROCEDURE; ?></th>
          <th><?php echo TEXT_DESCRIPTION; ?></th>
          <th><?php echo TEXT_ACTION; ?></th>
        </tr>
   </thead>
   <tbody id="wo_table" class="ui-widget-content">
<?php 
if ($step_list) {
	$odd = true;
	for ($j=0, $i=1; $j<count($step_list); $j++, $i++) { ?>
		<tr class="<?php echo $odd?'odd':'even'; ?>">
		  <td align="center"><?php echo html_input_field('step_' . $i, $step_list[$j]['step'], 'readonly="readonly" size="7" maxlength="6" style="text-align:right"'); ?></td>
		  <td nowrap="nowrap" align="center"><?php echo html_input_field('task_' . $i, $step_list[$j]['task_name'], 'size="12" maxlength="15" onfocus="clearField(\'sku_' . $i . '\', \'' . TEXT_SEARCH . '\')" onblur="setField(\'sku_' . $i . '\', \'' . TEXT_SEARCH . '\')"'); ?>
		  <?php echo html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="taskList(' . $i . ')"'); ?>
		  <?php echo html_hidden_field('task_id_' . $i, $step_list[$j]['task_id']); ?>
		  </td>
		  <td align="center"><?php echo html_input_field('desc_' . $i, $step_list[$j]['desc'], 'readonly="readonly" size="65" maxlength="64"'); ?></td>
		  <td align="center">
		    <?php 
		      echo html_icon('actions/go-up.png',             TEXT_MOVE_UP,   'small', 'onclick="moveUpTaskRow(' . $i . ');"');
		      echo html_icon('actions/go-down.png',           TEXT_MOVE_DOWN, 'small', 'onclick="moveDownTaskRow(' . $i . ');"');
		      echo html_icon('actions/edit-undo.png',         TEXT_INSERT,    'small', 'onclick="insertTaskRow(' . $i . ');"');
		      echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE,    'small', 'onclick="if (confirm(\'' . WORK_ORDER_MSG_DELETE_WO . '\')) removeTaskRow(' . $i . ');"');
		    ?>
		  </td>
		</tr>
<?php
	  $odd = !$odd;
	}
} else {
	$hidden_fields .= '  <script type="text/javascript">addTaskRow();</script>' . chr(10);
} ?>
   </tbody>
   <tfoot>
     <tr>
       <td><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addTaskRow()"'); ?></td>
     </tr>
   </tfoot>
  </table>
  <?php echo $hidden_fields; ?>
</div>
