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
//  Path: /modules/phreebooks/pages/popup_recur/template_main.php
//
echo html_form('recur', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show'] = false;
$toolbar->icon_list['save']['params'] = 'onclick="setReturnRecur()"';
$toolbar->icon_list['delete']['show'] = false;
$toolbar->icon_list['print']['show'] = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
  <tr><td><?php echo ORD_RECUR_INTRO; ?></td></tr>
  <tr><th><?php echo ORD_RECUR_ENTRIES; ?></th></tr>
  <tr><td><?php echo html_input_field('recur_id', '1', 'size="3"'); ?></td></tr>
  <tr><th><?php echo ORD_RECUR_FREQUENCY; ?></th></tr>
  <tr>
	<td>
		<?php
		echo html_radio_field('recur_frequency', 1, false) . ORD_TEXT_WEEKLY . '<br />' . chr(10);
		echo html_radio_field('recur_frequency', 2, false) . ORD_TEXT_BIWEEKLY . '<br />' . chr(10);
		echo html_radio_field('recur_frequency', 3, true) . ORD_TEXT_MONTHLY . '<br />' . chr(10);
		echo html_radio_field('recur_frequency', 4, false) . ORD_TEXT_QUARTERLY . '<br />' . chr(10);
		echo html_radio_field('recur_frequency', 5, false) . ORD_TEXT_YEARLY . chr(10);
		?>
	</td>
  </tr>
 </tbody>
</table>
</form>