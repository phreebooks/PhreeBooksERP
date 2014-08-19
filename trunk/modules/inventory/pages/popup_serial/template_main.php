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
//  Path: /modules/inventory/pages/popup_serial/template_main.php
//
if (!ENABLE_MULTI_CURRENCY) echo html_hidden_field('display_currency', DEFAULT_CURRENCY);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
echo $toolbar->build_toolbar(); 
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-style:none;width:300px">
 <tbody class="ui-widget-content">
  <tr><td><?php echo TEXT_SKU.': '.$sku; ?></td></tr>
  <tr><td><?php echo TEXT_DESCRIPTION.": ".$description; ?></td></tr>
  <tr><td><?php echo html_pull_down_menu('selSerial', $snValues, $snDef, 'size="10" onChange="setReturnValue('.$rowID.')"'); ?></td></tr>
 </tbody>
</table>
