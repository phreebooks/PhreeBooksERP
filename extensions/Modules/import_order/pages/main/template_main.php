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
//  Path: /modules/import_order/pages/main/template_main.php
//
// start the form
echo html_form('import_order', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \''.html_href_link(FILENAME_DEFAULT, '', 'SSL').'\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build();
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table style="border-style:none;margin-left:auto;margin-right:auto">
  <tr>
    <td><?php echo IMPORT_ORDER_SELECT; ?></td>
  </tr>
  <tr>
	<td align="center"><?php echo html_file_field('file_name') . '<br /><br />'; ?></td>
  </tr>
  <tr>
    <td><?php echo IMPORT_ORDER_DIRECTIONS; ?></td>
  </tr>
</table>
</form>