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
//  Path: /modules/amazon/pages/amazon_payment/template_main.php
//
echo html_form('amazon', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \''.html_href_link(FILENAME_DEFAULT, '', 'SSL').'\'"';
$toolbar->icon_list['open']['show']     = false;
if ($security_level > 1) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'import\')"';
} else {
  $toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build_toolbar();
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
 <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
  <thead class="ui-widget-header">
   <tr><td><?php echo AMAZON_IMPORT_PAYMENT; ?></td></tr>
   </thead>
   <tbody class="ui-widget-content">
   <tr><td align="center"><?php echo html_file_field($upload_name) . '<br /><br />'; ?></td></tr>
  </tbody>
 </table>
</form>