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
//  Path: /modules/phreeform/pages/popup_phreefrom/tab_crit_setup.php
//

$notes       = NULL;
$extra_stuff = NULL;
?>
<div title="<?php echo TEXT_CRITERIA_SETUP;?>" id="tab_crit">
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
	<tr>
	  <td>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		<thead class="ui-widget-header"><tr><th colspan="3"><?php echo PHREEFORM_DATEINFO; ?></th></tr></thead>
		<tbody class="ui-widget-content">
		<tr>
		  <td width="33%" valign="top"><?php
			echo PHREEFORM_DATELIST . '<br /><br />';
			echo PHREEFORM_DATEINST . '<br /><br />';
			echo html_checkbox_field('periods_only', 'z', (strpos($report->datelist, 'z') === false) ? false : true) . PB_USE_ACCOUNTING_PERIODS; ?><br />
		  </td>
		  <td width="33%"><?php
			echo html_checkbox_field('date_range[]', 'a', (strpos($report->datelist, 'a') === false) ? false : true) . $DateChoices['a'] . '<br />';
			echo html_checkbox_field('date_range[]', 'b', (strpos($report->datelist, 'b') === false) ? false : true) . $DateChoices['b'] . '<br />';
			echo html_checkbox_field('date_range[]', 'c', (strpos($report->datelist, 'c') === false) ? false : true) . $DateChoices['c'] . '<br />';
			echo html_checkbox_field('date_range[]', 'd', (strpos($report->datelist, 'd') === false) ? false : true) . $DateChoices['d'] . '<br />';
			echo html_checkbox_field('date_range[]', 'e', (strpos($report->datelist, 'e') === false) ? false : true) . $DateChoices['e'] . '<br />';
			echo html_checkbox_field('date_range[]', 'l', (strpos($report->datelist, 'l') === false) ? false : true) . $DateChoices['l'];
			?>
		  </td>
		  <td width="33%">
			<?php 
			echo html_checkbox_field('date_range[]', 'f', (strpos($report->datelist, 'f') === false) ? false : true) . $DateChoices['f'] .'<br />';
			echo html_checkbox_field('date_range[]', 'g', (strpos($report->datelist, 'g') === false) ? false : true) . $DateChoices['g'] .'<br />';
			echo html_checkbox_field('date_range[]', 'h', (strpos($report->datelist, 'h') === false) ? false : true) . $DateChoices['h'] .'<br />';
			echo html_checkbox_field('date_range[]', 'i', (strpos($report->datelist, 'i') === false) ? false : true) . $DateChoices['i'] .'<br />';
			echo html_checkbox_field('date_range[]', 'j', (strpos($report->datelist, 'j') === false) ? false : true) . $DateChoices['j'] .'<br />';
			echo html_checkbox_field('date_range[]', 'k', (strpos($report->datelist, 'k') === false) ? false : true) . $DateChoices['k'];
			?>
		  </td>
		</tr>
		<tr>
		  <td><?php echo PHREEFORM_DATEDEF; ?></td>
		  <td colspan="2"><?php echo html_pull_down_menu('date_default', gen_build_pull_down($DateChoices), $report->datedefault); ?></td>
		</tr>
		<tr>
		  <td><?php echo PHREEFORM_DATEFNAME; ?></td>
		  <td colspan="4"><?php echo html_pull_down_menu('date_field', $kFields, $report->datefield, 'onclick="updateFieldList(this)"'); ?></td>
		</tr>
		</tbody></table>
	  </td>
	  <td><?php echo '&nbsp;'; ?></td>
	</tr>
	<?php if ($report->reporttype == 'rpt') { ?>
	<tr>
	  <td>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		<thead class="ui-widget-header">
		<tr><th colspan="20"><?php echo PHREEFORM_GRPLIST; ?></th></tr>
		<tr>
		  <th><?php echo PHREEFORM_TBLFNAME;   ?></th>
		  <th><?php echo PHREEFORM_DISPNAME;   ?></th>
		  <th><?php echo TEXT_DEFAULT;      ?></th>
		  <th><?php echo TEXT_GROUP_PAGE_BREAK; ?></th>
		  <th><?php echo PHREEFORM_TEXTPROC;   ?></th>
		  <th><?php echo TEXT_ACTION;       ?></th>
		</tr>
		</thead>
		<tbody id="group_setup" class="ui-widget-content">
		<?php for ($i = 0; $i < sizeof($report->grouplist); $i++) { ?>
		  <tr>
			<td><?php echo html_pull_down_menu('grp_fld[]', $kFields, $report->grouplist[$i]->fieldname, 'onclick="updateFieldList(this)"'); ?></td>
			<td><?php echo html_input_field   ('grp_desc[]',$report->grouplist[$i]->description); ?></td>
			<td><?php echo html_pull_down_menu('grp_def[]', $nyChoice, $report->grouplist[$i]->default); ?></td>
			<td><?php echo html_pull_down_menu('grp_brk[]', $nyChoice, $report->grouplist[$i]->page_break); ?></td>
			<td><?php echo html_pull_down_menu('grp_prc[]', $pFields, $report->grouplist[$i]->processing); ?></td>
			<td align="right">
			  <?php 
		  	    echo html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_grp_' . $i) . chr(10);
				echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'class="delete"'); 
			  ?>
			</td>
		  </tr>
		<?php } ?>
		</tbody>
		</table>
	  </td>
	  <td valign="bottom"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'group_setup\', \'add\')"'); ?></td>
	</tr>
	<?php } ?>
	<tr>
	  <td>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		<thead class="ui-widget-header">
		<tr><th colspan="20"><?php echo PHREEFORM_SORTLIST; ?></th></tr>
		<tr>
		  <th><?php echo PHREEFORM_TBLFNAME; ?></th>
		  <th><?php echo PHREEFORM_DISPNAME; ?></th>
		  <th><?php echo TEXT_DEFAULT;       ?></th>
		  <th><?php echo TEXT_ACTION;        ?></th>
		</tr>
		</thead>
		<tbody id="sort_setup" class="ui-widget-content">
		<?php for ($i = 0; $i < sizeof($report->sortlist); $i++) { ?>
		  <tr>
			<td><?php echo html_pull_down_menu('sort_fld[]', $kFields, $report->sortlist[$i]->fieldname, 'onclick="updateFieldList(this)"'); ?></td>
			<td><?php echo html_input_field('sort_desc[]',   $report->sortlist[$i]->description); ?></td>
			<td><?php echo html_pull_down_menu('srt_def[]',  $nyChoice, $report->sortlist[$i]->default); ?></td>
			<td align="right">
			  <?php 
		  	    echo html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_sort_' . $i) . chr(10);
				echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'class="delete"'); 
			  ?>
			</td>
		  </tr>
		  <?php } ?>
		</tbody>
		</table>
	  </td>
	  <td valign="bottom"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'sort_setup\', \'add\')"'); ?></td>
	</tr>
	<tr>
	  <td>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		<thead class="ui-widget-header">
		<tr><th colspan="20"><?php echo TEXT_CRITERIA; ?></th></tr>
		<tr>
		  <th><?php echo PHREEFORM_TBLFNAME;   ?></th>
		  <th><?php echo PHREEFORM_DISPNAME;   ?></th>
		  <th><?php echo TEXT_SHOW;         ?></th>
		  <th><?php echo PHREEFORM_CRITTYPE;   ?></th>
		  <th><?php echo TEXT_MIN_VALUE; ?></th>
		  <th><?php echo TEXT_MAX_VALUE; ?></th>
		  <th><?php echo TEXT_ACTION;       ?></th>
		</tr>
		</thead>
		<tbody id="crit_setup" class="ui-widget-content">
		<?php for ($i = 0; $i < sizeof($report->filterlist); $i++) { ?>
		  <tr>
			<td><?php echo html_pull_down_menu('crit_fld[]',  $kFields,  $report->filterlist[$i]->fieldname, 'onclick="updateFieldList(this)"'); ?></td>
			<td><?php echo html_input_field   ('crit_desc[]', $report->filterlist[$i]->description); ?></td>
			<td><?php echo html_pull_down_menu('crit_vis[]',  $nyChoice, $report->filterlist[$i]->visible); ?></td>
			<td><?php echo html_pull_down_menu('crit_def[]',  $fFields,  $report->filterlist[$i]->type); ?></td>
			<td><?php echo html_input_field   ('crit_min[]',  $report->filterlist[$i]->min_val, 'size="10"'); ?></td>
			<td><?php echo html_input_field   ('crit_max[]',  $report->filterlist[$i]->max_val, 'size="10"'); ?></td>
			<td align="right">
			  <?php 
		  	    echo html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_crit_' . $i) . chr(10);
				echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'class="delete"'); 
			  ?>
			</td>
		  </tr>
		<?php } ?>
		</tbody></table>
	  </td>
	  <td valign="bottom"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'crit_setup\', \'add\')"'); ?></td>
	</tr>
	</table>
  <?php if ($notes) echo '<u><b>' . TEXT_NOTES . '</b></u>' . $notes; ?>
</div>
