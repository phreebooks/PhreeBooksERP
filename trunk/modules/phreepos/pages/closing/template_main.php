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
//  Path: /modules/phreepos/pages/closing/template_main.php
//
echo html_form('closingpos', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('statement_balance', $statement_balance)  . chr(10);
echo html_hidden_field('current_cleard_items', serialize($cleared_items))  . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '&amp;module=phreepos&amp;page=closing', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']       = false;
$toolbar->icon_list['save']['show']   = false;
if (empty($combined_list) ) {
	$toolbar->add_icon('continue', 'onclick="submitToDo(\'till_change\')"', $order = 10);
	$toolbar->icon_list['cancel']['params']   = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
}else if ($security_level > 1) {
		$toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
		$toolbar->icon_list['save']['show']   = true;
}

$toolbar->icon_list['delete']['show']     = false;
$toolbar->icon_list['print']['show']      = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.05.04');
echo $toolbar->build_toolbar($add_search = false, $add_period = false); 
// Build the page
?>
<h1><?php echo POS_HEADING_CLOSING; ?></h1>
<?php if (empty($combined_list) ){ ?>
<fieldset id="search_part" align="center">
	<ol>
<?php  
echo '<li><label>' . TEXT_TILL . ' ' . html_pull_down_menu('till_id', $tills->till_array(true) , $tills->till_id) . '</label></li>'; 
echo '<li><label>' . TEXT_DATE . ' ' . html_calendar_field($cal_gl) . '</label></li>';
?> 
	</ol>
</fieldset>
<?php }else{
echo html_hidden_field('till_id',   $tills->till_id) . chr(10);
echo html_hidden_field('post_date', gen_locale_date($post_date))      . chr(10);
?>

<table class="ui-widget" style="border-collapse:collapse;width:900px;margin-left:auto;margin-right:auto;">
 <thead class="ui-widget-header">
  <tr>
<?php
	$heading_array = array();
	$result      = html_heading_bar($heading_array, array(TEXT_REFERENCE, TEXT_DATE, TEXT_SOURCE, TEXT_AMOUNT, TEXT_CLEAR, '&nbsp;'));
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
	?>
		<tr class="<?php echo $odd?'odd':'even'; ?>">
			<td width="16%"><?php echo $values['reference']; ?></td>
			<td width="10%"><?php echo gen_locale_date($values['post_date']); ?></td>
			<td width="30%"><?php echo htmlspecialchars($values['name']); ?></td>
			<td width="15%" align="right"><?php if($security_level > 2) echo $currencies->format($values['dep_amount']-$values['pmt_amount']); ?></td>
			<td width="7%" align="center">
				<?php if (sizeof($values['detail']) == 1) {
				  echo html_input_field('amt_' . $i, $currencies->format(0),$values['edit'].  'style="text-align:right" size="13" onchange="updateBalance()"') . chr(10);
				  echo html_hidden_field('id[' . $i . ']', $values['detail'][0]['id']) . chr(10); 
				  echo html_hidden_field('pmt_' . $i, $currencies->format($values['detail'][0]['payment'])) . chr(10);
				  echo html_hidden_field('gl_account_' . $i, $values['detail'][0]['gl_account']) . chr(10);  
				} else {
				  echo html_input_field('samt_' . $i, $currencies->format(0),$values['edit']. 'style="text-align:right" size="13" onchange="updateSummary('.$i.')"') . chr(10);
				} ?>
			</td>
<?php if (sizeof($values['detail']) > 1 && $security_level > 2) { ?>
			<td id="disp_<?php echo $i; ?>" width="7%"<?php echo $bkgnd; ?> style="cursor:pointer" onclick="showDetails('<?php echo $i; ?>')"><?php echo TEXT_DETAILS; ?></td>
<?php } else { ?>
			<td width="7%">&nbsp;</td>
<?php } ?>
		</tr>
<?php 
		if (sizeof($values['detail']) > 1) {
		  $j   = 0;
		  $ref = $i;
		  echo '<tr id="detail_' . $i . '" style="display:none"><td colspan="7"><table style="width:100%">' . chr(10);
		  foreach ($values['detail'] as $detail) { ?>
		    <tr class="<?php echo $odd?'even':'odd'; ?>">
			  <td width="16%"><?php echo '&nbsp;'; ?></td>
			  <td width="10%"><?php echo gen_locale_date($detail['post_date']); ?></td>
			  <td width="30%"><?php echo htmlspecialchars($detail['name']); ?></td>
			  <td width="15%" align="right"><?php if($security_level > 2) echo $currencies->format($detail['payment']); ?></td>
			  <td width="7%" align="center">
			  <?php 
			  	echo html_input_field('amt_' . $i, $currencies->format(0),$detail['edit']. 'style="text-align:right" size="13" onchange="updateDetail('.$ref.')"') . chr(10);
			    echo html_hidden_field('id[' . $i . ']', $detail['id']) . chr(10); 
			    echo html_hidden_field('pmt_' . $i, $currencies->format($detail['payment'])) . chr(10); 
			    echo html_hidden_field('gl_account_' . $i, $detail['gl_account']) . chr(10);
			    ?>
			  </td>
			  <td id="<?php echo 'disp_' . $ref . '_' . $j; ?>" width="7%"><?php echo '&nbsp;'; ?></td>
		    </tr>
<?php
			$i++;
			$j++;
			$odd = !$odd;
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
 <?php 
 	$i=0;	
 	foreach($currencies->currencies as $key => $currency){ 
 		echo '<tr onclick="show('.$i.')">';
 		echo   '<td colspan="1" align="left">'. TEXT_SHOW_COUNT_HELP .'</td>';
		echo   '<td colspan="4" align="right">'. NEW_BALANCE .' ' .$currency['text'].'&nbsp;</td>';
		echo   '<td colspan="2" align="right">'.html_hidden_field('currencies_value_'.$i, $currency['value']);
		echo   html_input_field('new_balance_'.$i, '0', 'style="text-align:right" size="13" onchange="updateBalance()"').'</td>';
  		echo '</tr>';
  		echo '<tr id="curr_' . $i . '" style="display:none"><td colspan="7"><table style="width:100%">' . chr(10);
  		echo ' <tr><td>0,01 * '  . html_input_field('t_'. $key .'_001', '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>0,10 * '  . html_input_field('t_'. $key .'_01',  '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>1,00 * '  . html_input_field('t_'. $key .'_1',   '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>10,00 * ' . html_input_field('t_'. $key .'_10',  '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>100,00 * '. html_input_field('t_'. $key .'_100', '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') . '</td></tr>';
  		echo ' <tr><td>0,02 * '  . html_input_field('t_'. $key .'_002', '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>0,20 * '  . html_input_field('t_'. $key .'_02',  '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>2,00 * '  . html_input_field('t_'. $key .'_2',   '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>20,00 * ' . html_input_field('t_'. $key .'_20',  '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>200,00 * '. html_input_field('t_'. $key .'_200', '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') . '</td></tr>';
  		echo ' <tr><td>0,05 * '  . html_input_field('t_'. $key .'_005', '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>0,50 * '  . html_input_field('t_'. $key .'_05',  '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>5,00 * '  . html_input_field('t_'. $key .'_5',   '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>50,00 * ' . html_input_field('t_'. $key .'_50',  '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') .
  			 '</td><td>500,00 * '. html_input_field('t_'. $key .'_500', '','style="text-align:right; width:50px" size="4" onchange="updateCurr(\''. $key .'\','.$i.')"') . '</td></tr>';
  		
  		echo '</table></td></tr>' . chr(10);
  		$i++;
 	}
 	echo   html_hidden_field('new_balance', '0');
 	if($security_level > 2){
 ?>

  <tr>
	<td colspan="5" align="right"><?php echo PAYMENTS_SHOULD_BE . '&nbsp;'; ?></td>
	<td colspan="2" align="right"><?php echo html_input_field('open_checks', '0', 'disabled="disabled" style="text-align:right" size="13"'); ?></td>
  </tr>
  <tr>
	<td colspan="5" align="right"><?php echo PAYMENTS_RECEIVED . '&nbsp;'; ?></td>
	<td colspan="2" align="right"><?php echo html_input_field('open_deposits', '0', 'disabled="disabled" style="text-align:right" size="13"'); ?></td>
  </tr>
  <tr>
	<td colspan="5" align="right"><?php echo TILL_BALANCE . '&nbsp;'; ?></td>
	<td colspan="2" align="right"><?php echo html_input_field('till_balance', $till_balance, 'disabled="disabled" style="text-align:right" size="13"'); ?></td>
  </tr>
  <tr>
	<td colspan="5" align="right"><?php echo TILL_END_BALANCE . '&nbsp;'; ?></td>
	<td colspan="2" id="balance_total" align="right"><?php echo html_input_field('balance', '0', 'readonly="readonly" style="text-align:right" size="13"'); ?></td>
  </tr>
  <?php }else{
  		echo   html_hidden_field('open_checks', '0');
  		echo   html_hidden_field('open_deposits', '0');
  		echo   html_hidden_field('till_balance', '0');
  		echo   html_hidden_field('balance', '0');  	
 	}?>
 </tfoot>
</table>
<?php }?>
</form>
