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
//  Path: /modules/phreeform/pages/popup_phreefrom/tab_db_setup.php
//

?>
<div title="<?php echo TEXT_DATABASE_SETUP;?>" id="tab_db">
  <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
	<tr>
	  <td>
		<table id="table_setup" class="ui-widget" style="border-collapse:collapse;width:100%">
		<thead class="ui-widget-header">
		<tr>
		  <th colspan="20"><?php echo TEXT_DATABASE_TABLES; ?></th>
		</tr>
		<tr>
		  <th><?php echo TEXT_JOIN_TYPE;      ?></th>
		  <th><?php echo TEXT_TABLE_NAME;     ?></th>
		  <th><?php echo TEXT_TABLE_CRITERIA; ?></th>
		  <th><?php echo TEXT_ACTION;         ?></th>
		</tr>
		</thead>
		<tbody class="ui-widget-content">
		<tr>
		  <td><?php echo '&nbsp;'; ?></td>
		  <td><?php 
		    echo html_pull_down_menu('table[]', $kTables, $report->tables[0]->tablename, 'onchange="fieldLoad()"');
		    echo html_hidden_field('joinopt[]');
		    echo html_hidden_field('table_crit[]');
		      ?>
		  </td>
		  <td colspan="2"><?php echo PHREEFORM_SPECIAL_REPORT . ' ' . html_input_field('special_class', $report->special_class); ?></td>
		</tr>
		<?php for ($i = 1; $i < sizeof($report->tables); $i++) { ?>
		  <tr>
			<td><?php echo html_pull_down_menu('joinopt[]', $joinOptions, $report->tables[$i]->joinopt, ''); ?></td>
			<td><?php echo html_pull_down_menu('table[]',   $kTables,     $report->tables[$i]->tablename, 'onchange="fieldLoad()"'); ?></td>
			<td nowrap="nowrap"><?php echo ' on ' . html_input_field('table_crit[]', $report->tables[$i]->relationship, 'size="80"');  ?></td>
			<td align="right">
			  <?php 
		  	    echo html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_table_' . $i) . chr(10);
				echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'class="delete"'); 
			  ?>
			</td>
		  </tr>
		<?php } ?>
		</tbody>
		</table>
	  </td>
	  <td valign="bottom"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'table_setup\', \'add\')"'); ?></td>
	</tr>
	<tr>
	  <td colspan="3"><?php echo html_button_field('db_validate', TEXT_VALIDATE_RELATIONSHIPS, 'onclick="validateDB()"'); ?></td>
	</tr>
	<tr>
	  <td colspan="3"><?php echo PHREEFORM_DB_LINK_HELP; ?></td>
	</tr>
  </tbody>
  </table>
</div>
