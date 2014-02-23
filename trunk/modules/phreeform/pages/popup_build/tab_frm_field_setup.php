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
//  Path: /modules/phreeform/pages/popup_phreefrom/tab_frm_field_setup.php
//
require_once(DIR_FS_WORKING . 'pages/popup_build/box_html.php'); // box templates
?>
<div title="<?php echo TEXT_FIELD_SETUP;?>" id="tab_field">
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
<tr><td>
  <table id="field_setup_frm" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
  <thead class="ui-widget-header">
    <tr><th id="fieldListHeading" colspan="8"><?php echo PHREEFORM_FLDLIST; ?></th></tr>
    <tr>
      <th width="25%"><?php echo PHREEFORM_DISPNAME; ?></th>
      <th width="10%"><?php echo TEXT_ABSCISSA; ?></th>
      <th width="10%"><?php echo TEXT_ORDINATE; ?></th>
      <th width="10%"><?php echo TEXT_WIDTH;    ?></th>
      <th width="10%"><?php echo TEXT_HEIGHT;   ?></th>
<?php if ($report->serialform) { ?>
      <th width="10%"><?php echo TEXT_BREAK;    ?></th>
      <th width="15%"><?php echo TEXT_TYPE;     ?></th>
<?php } else {?>
      <th width="25%"><?php echo TEXT_TYPE;     ?></th>
<?php } ?>
      <th width="10%"><?php echo TEXT_ACTION;   ?></th>
    </tr>
	</thead>
	<tbody class="ui-widget-content">
<?php for ($i = 0; $i < sizeof($report->fieldlist); $i++) { ?>
    <tr>
	  <td colspan="8" nowrap="nowrap">
	   <div >
		<?php 
		  echo html_input_field('fld_desc[]',   $report->fieldlist[$i]->description, 'size="20" maxlength="25"'); 
		  echo html_input_field('fld_abs[]',    $report->fieldlist[$i]->abscissa,    'size="6" maxlength="4"'); 
		  echo html_input_field('fld_ord[]',    $report->fieldlist[$i]->ordinate,    'size="6" maxlength="4"'); 
		  echo html_input_field('fld_wid[]',    $report->fieldlist[$i]->width,       'size="6" maxlength="4"'); 
		  echo html_input_field('fld_hgt[]',    $report->fieldlist[$i]->height,      'size="6" maxlength="4"'); 
if ($report->serialform) { 
		  echo html_pull_down_menu('fld_brk[]', $sel_yes_no, $report->fieldlist[$i]->rowbreak);
}
		  echo html_pull_down_menu('fld_type_'.$i, gen_build_pull_down($FormEntries), $report->fieldlist[$i]->type, 'onchange="boxLoad(this.value, ' . $i . ')"');
		  echo html_hidden_field('row_id[]', $i) . chr(10);
		  echo html_icon('actions/view-fullscreen.png',     TEXT_MOVE,       'small', 'style="cursor:move"', '', '', 'move_fld_'.$i) . chr(10);
		  echo html_icon('emblems/emblem-unreadable.png',   TEXT_DELETE,     'small', 'class="delete"') . chr(10);
		  echo html_icon('actions/document-properties.png', TEXT_PROPERTIES, 'small', 'onmouseup="boxProperties('.$i.')"') . chr(10);
		?>
		  <div id="fld_box_<?php echo $i; ?>" style="display:none;background-color:#bbd8d8; border:solid 1px #000">
			  <?php echo box_build($report->fieldlist[$i], $i); ?>
		  </div>
	   </div>
	  </td>
    </tr>
<?php } ?>
  </tbody>
</table>
</td>
<td valign="bottom">
<?php echo html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'field_setup_frm\', \'add\')"'); ?>
</td></tr>
</table>
</div>
