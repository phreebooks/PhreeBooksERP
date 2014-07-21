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
//  Path: /modules/phreebooks/pages/reconciliation/template_main.php
//
echo html_form('reconciliation', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params']   = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']       = false;
if ($security_level > 1) {
	$toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
	$toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['delete']['show']     = false;
$toolbar->icon_list['print']['show']      = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.05.04');
echo $toolbar->build_toolbar($add_search = false, $add_period = false); 
// Build the page
?>
<h1><?php echo BANKING_HEADING_RECONCILIATION; ?></h1>
<div align="center"><?php  echo TEXT_CASH_ACCOUNT 			 . '&nbsp;' . html_pull_down_menu('gl_account', $account_array, $gl_account, 'onchange="submit();"'); ?> &nbsp;
<?php echo TEXT_INFO_SEARCH_PERIOD_FILTER . '&nbsp;' . html_pull_down_menu('search_period', gen_get_period_pull_down(false), $period, 'onchange="submit();"'); ?></div>
<?php if (ENABLE_MULTI_CURRENCY) echo '<p>'.sprintf(GEN_PRICE_SHEET_CURRENCY_NOTE, $currencies->currencies[DEFAULT_CURRENCY]['title']) .'</p>'. chr(10); ?>
<table class="ui-widget" style="border-collapse:collapse;width:900px;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
  <tr>
<?php
	$heading_array = array(
	  'reference'  => TEXT_REFERENCE,
	  'post_date'  => TEXT_DATE,
	  'dep_amount' => BNK_DEPOSIT_CREDIT,
	  'pmt_amount' => BNK_CHECK_PAYMENT,
	);
	$result      = html_heading_bar($heading_array, array(TEXT_SOURCE, TEXT_CLEAR, '&nbsp;'));
	echo $result['html_code'];
?>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
	<?php $i = 0;
	if (sizeof($combined_list) > 0) {
	  $odd = true;
	  foreach ($combined_list as $values) { 
	  	$bkgnd = ($values['partial']) ? ' style="background-color:yellow"' : '';
	  	$disabled = ($values['cleared'] <> $period && $values['cleared'] > 0) ? 'disabled="disabled" ' : '';
	  	$cleared  = ($values['cleared'] <> $period && $values['cleared'] > 0) ? $values['cleared'].' ' : '';
	?>
		<tr class="<?php echo $odd?'odd':'even'; ?>">
			<td width="16%"><?php echo $values['reference']; ?></td>
			<td width="10%"><?php echo gen_locale_date($values['post_date']); ?></td>
			<td width="15%" align="right"><?php echo $values['dep_amount'] ? $currencies->format($values['dep_amount']) : '&nbsp;'; ?></td>
			<td width="15%" align="right"><?php echo $values['pmt_amount'] ? $currencies->format($values['pmt_amount']) : '&nbsp;'; ?></td>
			<td width="30%"><?php echo htmlspecialchars($values['name']); ?></td>
			<td width="7%" align="center">
				<?php if (sizeof($values['detail']) == 1) {
				  echo $cleared . html_checkbox_field('chk[' . $i . ']', '1', ($values['cleared'] == $period ? true : false), '', $disabled.'onclick="updateBalance()"') . chr(10);
				  echo html_hidden_field('id[' . $i . ']', $values['detail'][0]['id']) . chr(10); 
				  echo html_hidden_field('pmt_' . $i, $values['detail'][0]['payment']) . chr(10); 
				} else {
				  echo $cleared . html_checkbox_field('sum_' . $i, '1', ($values['cleared'] == $period ? true : false), '', $disabled.'onclick="updateSummary(' . $i . ')"') . chr(10);
				} ?>
			</td>
<?php if (sizeof($values['detail']) > 1) { ?>
			<td id="disp_<?php echo $i; ?>" width="7%"<?php echo $bkgnd; ?> style="cursor:pointer" onclick="showDetails('<?php echo $i; ?>')"><?php echo TEXT_DETAILS; ?></td>
<?php } else { ?>
			<td width="7%">&nbsp;</td>
<?php } ?>
		</tr>
<?php 
		if (sizeof($values['detail']) > 1) {
		  $j   = 0;
		  $ref = $i;
		  $even = true;
		  echo '<tr id="detail_' . $i . '" style="display:none"><td colspan="7"><table style="width:100%">' . chr(10);
		  foreach ($values['detail'] as $detail) { 
		  	$disabled = ($detail['cleared'] <> $period && $detail['cleared'] > 0) ? 'disabled="disabled" ' : '';
	  		$cleared  = ($detail['cleared'] <> $period && $detail['cleared'] > 0) ? $detail['cleared'].' ' : '';
		  	?>
		    <tr class="<?php echo $even?'even':'odd'; ?>">
			  <td width="16%"><?php echo '&nbsp;'; ?></td>
			  <td width="10%"><?php echo gen_locale_date($detail['post_date']); ?></td>
			  <td width="15%" align="right"><?php echo $detail['dep_amount'] ? $currencies->format($detail['dep_amount']) : '&nbsp;'; ?></td>
			  <td width="15%" align="right"><?php echo $detail['pmt_amount'] ? $currencies->format($detail['pmt_amount']) : '&nbsp;'; ?></td>
			  <td width="30%"><?php echo htmlspecialchars($detail['name']); ?></td>
			  <td width="7%" align="center">
			    <?php echo $cleared . html_checkbox_field('chk[' . $i . ']', '1', ($detail['cleared'] == $period ? true : false), '', $disabled.'onclick="updateDetail(' . $ref . ')"') . chr(10); ?>
			    <?php echo html_hidden_field('id[' . $i . ']', $detail['id']) . chr(10); ?>
			    <?php echo html_hidden_field('pmt_' . $i, $detail['payment']) . chr(10); ?>
			  </td>
			  <td id="<?php echo 'disp_' . $ref . '_' . $j; ?>" width="7%"><?php echo '&nbsp;'; ?></td>
		    </tr>
<?php
			$i++;
			$j++;
			$even = !$even;
		  }
		  echo '</table></td></tr>' . chr(10);
		} else {
		  $i++;
		}
		$odd = !$odd;
	  }
	} else {
	  echo '<tr><td>' . BNK_NO_GL_ENTRIES . '</td></tr>';
	}
?>
 </tbody>
 <tfoot class="ui-widget-header">
  <tr>
	<td colspan="5" align="right"><?php echo BNK_START_BALANCE . '&nbsp;'; ?></td>
	<td colspan="2" align="right"><?php echo html_input_field('start_balance', $statement_balance, 'style="text-align:right" size="13" onchange="updateBalance()"'); ?></td>
  </tr>
  <tr>
	<td colspan="5" align="right"><?php echo BNK_OPEN_CHECKS . '&nbsp;'; ?></td>
	<td colspan="2" align="right"><?php echo html_input_field('open_checks', '0', 'disabled="disabled" style="text-align:right" size="13"'); ?></td>
  </tr>
  <tr>
	<td colspan="5" align="right"><?php echo BNK_OPEN_DEPOSITS . '&nbsp;'; ?></td>
	<td colspan="2" align="right"><?php echo html_input_field('open_deposits', '0', 'disabled="disabled" style="text-align:right" size="13"'); ?></td>
  </tr>
  <tr>
	<td colspan="5" align="right"><?php echo BNK_GL_BALANCE . '&nbsp;'; ?></td>
	<td colspan="2" align="right"><?php echo html_input_field('gl_balance', $gl_balance, 'disabled="disabled" style="text-align:right" size="13"'); ?></td>
  </tr>
  <tr>
	<td colspan="5" align="right"><?php echo BNK_END_BALANCE . '&nbsp;'; ?></td>
	<td colspan="2" id="balance_total" align="right"><?php echo html_input_field('balance', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
  </tr>
 </tfoot>
</table>
</form>