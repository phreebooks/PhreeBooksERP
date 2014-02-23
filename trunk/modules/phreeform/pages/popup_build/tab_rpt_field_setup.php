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
//  Path: /modules/phreeform/pages/popup_phreefrom/tab_rpt_field_setup.php
//

?>
<div title="<?php echo TEXT_FIELD_SETUP;?>" id="tab_field">
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
<tr><td>
  <table id="field_setup" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
    <thead class="ui-widget-header">
	<tr><th id="fieldListHeading" colspan="10"><?php echo TEXT_FIELD_LIST; ?></th></tr>
    <tr>
      <th><?php echo PHREEFORM_TBLFNAME; ?></th>
      <th><?php echo PHREEFORM_DISPNAME; ?></th>
      <th><?php echo TEXT_COLUMN . '<br />' . TEXT_BREAK; ?></th>
      <th><?php echo TEXT_COLUMN . '<br />' . TEXT_WIDTH; ?></th>
      <th><?php echo TEXT_TOTAL  . '<br />' . TEXT_WIDTH; ?></th>
      <th><?php echo TEXT_SHOW   . '<br />' . TEXT_FIELD; ?></th>
      <th><?php echo PHREEFORM_TEXTPROC; ?></th>
      <th><?php echo TEXT_TOTAL  . '<br />' . TEXT_COLUMN; ?></th>
      <th><?php echo TEXT_ALIGN; ?></th>
      <th><?php echo TEXT_ACTION; ?></th>
    </tr>
	</thead>
	<tbody class="ui-widget-content">
<?php for ($i = 0; $i < sizeof($report->fieldlist); $i++) { ?>
    <tr>
	  <td><?php echo html_combo_box('fld_fld[]', CreateSpecialDropDown($report), $report->fieldlist[$i]->fieldname, 'onclick="updateFieldList(this)"', '220px', '', 'fld_combo_' . $i); ?></td>
      <td><?php echo html_input_field('fld_desc[]', $report->fieldlist[$i]->description, 'size="20" maxlength="25"'); ?></td>
	  <td><?php echo html_pull_down_menu('fld_brk[]',  $nyChoice,   $report->fieldlist[$i]->columnbreak, 'onchange="calculateWidth()"'); ?></td>
	  <td><?php echo html_input_field('fld_clmn[]', ($report->fieldlist[$i]->columnwidth ? $report->fieldlist[$i]->columnwidth : PF_DEFAULT_COLUMN_WIDTH), 'size="4" maxlength="3" onchange="calculateWidth()"'); ?></td>
	  <td align="center"><?php echo '&nbsp;'; ?></td>
      <td><?php echo html_pull_down_menu('fld_vis[]',  $nyChoice,   $report->fieldlist[$i]->visible, 'onchange="calculateWidth()"'); ?></td>
      <td><?php echo html_pull_down_menu('fld_proc[]', $pFields,    $report->fieldlist[$i]->processing); ?></td>
      <td><?php echo html_pull_down_menu('fld_tot[]',  $nyChoice,   $report->fieldlist[$i]->total); ?></td>
	  <td><?php echo html_pull_down_menu('fld_algn[]', $kFontAlign, $report->fieldlist[$i]->align); ?></td>
      <td nowrap="nowrap" align="right">
		<?php 
		  echo html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_fld_' . $i) . chr(10);
		  echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'class="delete" calculateWidth();"') . chr(10);
		?>
	  </td>
    </tr>
<?php } ?>
    </tbody>
  </table>
</td>
<td valign="bottom">
<?php echo html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'field_setup\', \'add\')"'); ?>
</td></tr>
</table>
<?php echo PHREEFORM_FIELD_HELP; ?>
</div>
