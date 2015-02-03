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
//  Path: /modules/xml_builder/pages/main/template_main.php
//
echo html_form('xml_builder', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\', true)"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build();
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <tbody class="ui-widget-content">
  <tr>
    <td><?php echo 'Plese select the module to build the xml information file from:'; ?></td>
  </tr>
  <tr>
	<td align="center"><?php echo html_input_field('mod') . '<br /><br />'; ?></td>
  </tr>
  <tr>
    <td><?php echo 'After a module has been selected, press the Save icon to execute the script.'; ?></td>
  </tr>
 </tbody>
</table>
</form>