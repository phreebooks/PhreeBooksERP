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
//  Path: /modules/phreebooks/pages/popup_bar_code/template_main.php
//
echo html_form('bar_code', FILENAME_DEFAULT) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo POPUP_BAR_CODE_TITLE; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="2"><?php echo ORD_BAR_CODE_INTRO; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
	<td align="right"><?php echo TEXT_QUANTITY; ?></td>
	<td><?php echo html_input_field('qty', '1', 'size="6"'); ?></td>
  </tr>
  <tr>
	<td align="right"><?php echo TEXT_UPC_CODE; ?></td>
	<td>
	  <?php echo html_input_field('upc', '', 'size="16"'); ?>
	  <?php echo html_icon('devices/media-floppy.png', TEXT_SAVE, 'small', 'onclick="setReturnItem(true)"'); ?>
	</td>
  </tr>
 </tbody>
</table>
</form>