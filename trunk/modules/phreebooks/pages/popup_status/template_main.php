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
//  Path: /modules/phreebooks/pages/popup_status/template_main.php
//
echo html_form('status', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div style="text-align:center" <?php echo $inactive_flag; ?>><?php echo $status_text; ?></div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="5"><?php echo ACT_POPUP_TERMS_WINDOW_TITLE; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr><td colspan="5"><?php echo ACT_TERMS_DUE . ': ' . $new_data['terms_lang'] . ACT_TERMS_CREDIT_LIMIT . $currencies->format($new_data['credit_limit']); ?></td></tr>
<?php if ($new_data['past_due'] <> 0) { ?>
  <tr><td colspan="5"><?php echo ACT_AMT_PAST_DUE . $currencies->format($new_data['past_due']); ?></td></tr>
<?php } ?>
 </tbody>
</table>
<table class="ui-widget" style="border-collapse:collapse;width:100%;">
 <thead class="ui-widget-header">
  <tr><th colspan="5"><?php echo ACT_ACT_HISTORY; ?></th></tr>
  <tr>
	<th><?php echo ($type == 'AP') ? AP_AGING_HEADING_1 : AR_AGING_HEADING_1; ?></th>
	<th><?php echo ($type == 'AP') ? AP_AGING_HEADING_2 : AR_AGING_HEADING_2; ?></th>
	<th><?php echo ($type == 'AP') ? AP_AGING_HEADING_3 : AR_AGING_HEADING_3; ?></th>
	<th><?php echo ($type == 'AP') ? AP_AGING_HEADING_4 : AR_AGING_HEADING_4; ?></th>
	<th><?php echo TEXT_TOTAL; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
	<td align="center"><?php echo $currencies->format($new_data['balance_0']); ?></td>
	<td align="center"><?php echo $currencies->format($new_data['balance_30']); ?></td>
	<td align="center"><?php echo $currencies->format($new_data['balance_60']); ?></td>
	<td align="center"><?php echo $currencies->format($new_data['balance_90']); ?></td>
	<td align="center"><?php echo $currencies->format($new_data['total']); ?></td>
  </tr>
 </tbody>
</table>
<table class="ui-widget" style="border-collapse:collapse;width:100%;">
 <thead class="ui-widget-header">
  <tr><th colspan="5"><?php echo TEXT_NOTES; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr><td colspan="5"><?php echo $notes; ?></td></tr>
 </tbody>
</table>
</form>