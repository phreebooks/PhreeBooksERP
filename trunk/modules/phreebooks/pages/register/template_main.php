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
//  Path: /modules/phreebooks/pages/register/template_main.php
//
echo html_form('register', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.05.04');
$toolbar->search_period = $period;
$toolbar->period_strict = false; // hide the All option in period selection
echo $toolbar->build_toolbar($add_search = false, $add_period = true); 
// Build the page
?>
<h1><?php echo BANKING_HEADING_REGISTER; ?></h1>
<div align="center"><?php echo TEXT_CASH_ACCOUNT . '&nbsp;' . html_pull_down_menu('gl_account', $account_array, $gl_account, 'onchange="submit();"'); ?></div>
<?php if (ENABLE_MULTI_CURRENCY) echo '<p> ' . sprintf(GEN_PRICE_SHEET_CURRENCY_NOTE, $currencies->currencies[DEFAULT_CURRENCY]['title']) . '</p>'; ?>
<table class="ui-widget" style="border-collapse:collapse;width:900px;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
  <tr>
	<th><?php echo TEXT_DATE; ?></th>
	<th><?php echo TEXT_REFERENCE; ?></th>
	<th><?php echo TEXT_TYPE; ?></th>
	<th><?php echo TEXT_DESCRIPTION; ?></th>
	<th><?php echo TEXT_DEPOSIT?></th>
	<th><?php echo TEXT_PAYMENT; ?></th>
	<th><?php echo TEXT_BALANCE; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo TEXT_BEGINNING_BALANCE; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td align="right"><?php echo $currencies->format($beginning_balance); ?></td>
  </tr>
  <?php 
  $i = 0;
  $odd = true;
  if (is_array($bank_list)) foreach ($bank_list as $values) { 
	$beginning_balance += $values['dep_amount'] - $values['pmt_amount'];
  ?>
	<tr class="<?php echo $odd?'odd':'even'; ?>">
		<td id="td_<?php echo $i; ?>_4"><?php echo gen_locale_date($values['post_date']); ?></td>
		<td id="td_<?php echo $i; ?>_1"><?php echo $values['reference']; ?></td>
		<td id="td_<?php echo $i; ?>_2"><?php echo $values['pmt_amount'] ? BNK_TEXT_WITHDRAWAL : TEXT_DEPOSIT; ?></td>
		<td id="td_<?php echo $i; ?>_5"><?php echo htmlspecialchars($values['name']); ?></td>
		<td id="td_<?php echo $i; ?>_6" align="right"><?php echo $values['dep_amount'] ? $currencies->format($values['dep_amount']) : '&nbsp;'; ?></td>
		<td id="td_<?php echo $i; ?>_3" align="right"><?php echo $values['pmt_amount'] ? $currencies->format($values['pmt_amount']) : '&nbsp;'; ?></td>
		<td id="td_<?php echo $i; ?>_0" align="right"><?php echo $currencies->format($beginning_balance); ?></td>
	</tr>
	<?php
	$i++;
	$odd = !$odd;
  } ?>
 </tbody>
 <tfoot class="ui-widget-header">
  <tr>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo TEXT_ENDING_BALANCE; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td><?php echo '&nbsp;'; ?></td>
	<td align="right"><?php echo $currencies->format($beginning_balance); ?></td>
  </tr>
 </tfoot>
</table>
</form>