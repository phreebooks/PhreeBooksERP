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
//  Path: /modules/phreebooks/pages/bills/template_main.php
//
echo html_form('bills_form', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'oID'))) . chr(10);
// include hidden fields
echo html_hidden_field('action',            '') . chr(10);
echo html_hidden_field('id',              $order->id) . chr(10); // db journal entry id, null = new entry; not null = edit
echo html_hidden_field('bill_acct_id',    $order->bill_acct_id) . chr(10); // id of the account in the bill to/remit to
echo html_hidden_field('bill_address_id', $order->bill_address_id) . chr(10);
echo html_hidden_field('bill_telephone1', $order->bill_telephone1) . chr(10);
if (JOURNAL_ID == 18) {
  echo html_hidden_field('acct_balance',  '0') . chr(10);
  echo html_hidden_field('end_balance',   '0') . chr(10);
} elseif (JOURNAL_ID == 20) {
  echo html_hidden_field('shipper_code',  '') . chr(10);
}
if (!ENABLE_MULTI_BRANCH) echo html_hidden_field('store_id', '0') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['params']   = 'onclick="OpenOrdrList(this)"';
$toolbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . TEXT_DELETE_ENTRY . '\')) submitToDo(\'delete\')"';
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['print']['params']  = 'onclick="submitToDo(\'print\')"';
if ($security_level < 4) $toolbar->icon_list['delete']['show'] = false;
if ($security_level < 2) $toolbar->icon_list['save']['show']   = false;
if ($security_level < 2) $toolbar->icon_list['print']['show']  = false;
$toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . 'jID=' . JOURNAL_ID . '&amp;type=' . $type, 'SSL') . '\'"', 2);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
switch (JOURNAL_ID) {
  case 18: $toolbar->add_help('07.05.02'); break;
  case 20: $toolbar->add_help('07.05.01'); break;
}
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo constant('ORD_TEXT_' . JOURNAL_ID . '_' . strtoupper($type) . '_WINDOW_TITLE'); ?></h1>
<div>
 <table class="ui-widget" style="border-style:none;width:100%">
  <tbody class="ui-widget-content">
	<tr>
	  <td valign="top">
<?php
echo (($type == 'c') ? GEN_CUSTOMER_ID : GEN_VENDOR_ID) . ' ' . html_input_field('search', $order->search, 'onfocus="clearField(\'search\', \'' . TEXT_SEARCH . '\')" onblur="setField(\'search\', \'' . TEXT_SEARCH . '\');"'); 
echo '&nbsp;' . html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="AccountList()"');
?>
	  </td>
	  <td align="right">
	    <?php echo ((JOURNAL_ID == 20 || !isset($_SESSION['admin_encrypt'])) ? '&nbsp;' : BNK_TEXT_SAVE_PAYMENT_INFO . html_checkbox_field('save_payment', '1', ($order->save_payment ? true : false), '', '')); ?>
	  </td>
	  <td>
	    <?php echo html_pull_down_menu('payment_id', gen_null_pull_down(), '', 'style="visibility:hidden" onchange=\'fillPayment()\'') . chr(10); ?>
	  </td>
	</tr>
	<tr>
	  <td valign="top">
<?php 
echo JOURNAL_ID == 18 ? TEXT_RECEIVE_FROM : TEXT_PAY_TO;
echo html_pull_down_menu('bill_to_select', gen_null_pull_down(), '', 'onchange=\'fillAddress("bill")\'') . chr(10);
echo '<br />' . html_input_field('bill_primary_name', $order->bill_primary_name, 'size="33" maxlength="32" onfocus="clearField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')" onblur="setField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_contact', $order->bill_contact, 'size="33" maxlength="32" onfocus="clearField(\'bill_contact\', \'' . GEN_CONTACT . '\')" onblur="setField(\'bill_contact\', \'' . GEN_CONTACT . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_address1', $order->bill_address1, 'size="33" maxlength="32" onfocus="clearField(\'bill_address1\', \'' . GEN_ADDRESS1 . '\')" onblur="setField(\'bill_address1\', \'' . GEN_ADDRESS1 . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_address2', $order->bill_address2, 'size="33" maxlength="32" onfocus="clearField(\'bill_address2\', \'' . GEN_ADDRESS2 . '\')" onblur="setField(\'bill_address2\', \'' . GEN_ADDRESS2 . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_city_town', $order->bill_city_town, 'size="25" maxlength="24" onfocus="clearField(\'bill_city_town\', \'' . GEN_CITY_TOWN . '\')" onblur="setField(\'bill_city_town\', \'' . GEN_CITY_TOWN . '\')"') . chr(10);
echo html_input_field('bill_state_province', $order->bill_state_province, 'size="3" maxlength="5" onfocus="clearField(\'bill_state_province\', \'' . GEN_STATE_PROVINCE . '\')" onblur="setField(\'bill_state_province\', \'' . GEN_STATE_PROVINCE . '\')"') . chr(10);
echo html_input_field('bill_postal_code', $order->bill_postal_code, 'size="11" maxlength="10" onfocus="clearField(\'bill_postal_code\', \'' . GEN_POSTAL_CODE . '\')" onblur="setField(\'bill_postal_code\', \'' . GEN_POSTAL_CODE . '\')"') . chr(10);
echo '<br />' . html_pull_down_menu('bill_country_code', gen_get_countries(), $order->bill_country_code) . chr(10); 
echo '<br />' . html_input_field('bill_email', $order->bill_email, 'size="40" maxlength="64" onfocus="clearField(\'bill_email\', \'' . GEN_EMAIL . '\')" onblur="setField(\'bill_email\', \'' . GEN_EMAIL . '\')"') . chr(10);
?>
	  </td>
	  <td valign="top">
		<table class="ui-widget" style="border-style:none;">
		 <tbody class="ui-widget-content">
		  <tr>
			<td align="right"><?php echo ((JOURNAL_ID == 18) ? BNK_TEXT_DEPOSIT_ID : BNK_TEXT_PAYMENT_ID) . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_input_field('purchase_invoice_id', $order->purchase_invoice_id, 'style="text-align:right"'); ?></td>
		  </tr>
		  <tr>
			<td align="right"><?php echo TEXT_DATE . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_calendar_field($cal_bills); ?></td>
		  </tr>
	  <?php if (ENABLE_MULTI_BRANCH) { ?>
		  <tr>
		    <td align="right"><?php echo GEN_STORE_ID . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_pull_down_menu('store_id', gen_get_store_ids(), $order->store_id ? $order->store_id : $_SESSION['admin_prefs']['def_store_id']); ?></td>
		  </tr>
	  <?php } ?>
		  <tr>
			<td align="right"><?php echo TEXT_REFERENCE . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_input_field('purch_order_id', $order->purch_order_id, 'style="text-align:right"'); ?></td>
		  </tr>
		  <tr>
            <td align="right"><?php echo JOURNAL_ID==20 ? TEXT_BUYER : TEXT_SALES_REP; ?></td>
            <td align="right"><?php echo html_pull_down_menu('rep_id', gen_get_rep_ids($account_type), $order->rep_id ? $order->rep_id : $default_sales_rep); ?></td>
		  </tr>
		  <tr>
			<td align="right"><?php echo BNK_CASH_ACCOUNT . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_pull_down_menu('gl_acct_id', $gl_array_list, $order->gl_acct_id, 'onchange="loadNewBalance()"'); ?></td>
		  </tr>
		  <tr>
			<td align="right"><?php echo BNK_DISCOUNT_ACCOUNT . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_pull_down_menu('gl_disc_acct_id', $gl_array_list, $order->gl_disc_acct_id, ''); ?></td>
		  </tr>
		  <tr>
			<td align="right">
			  <?php echo TEXT_TOTAL; ?>
			  <?php echo (ENABLE_MULTI_CURRENCY) ? ' (' . DEFAULT_CURRENCY . ')' : ''; ?>
			</td>
			<td align="right">
				<?php 
				echo html_input_field('total', $order->total_amount, 'readonly="readonly" size="15" maxlength="20" style="text-align:right"');
				?>
			</td>
		  </tr>
		 </tbody>
		</table>
	  </td>
<?php if (JOURNAL_ID == 18) { ?>
	  <td valign="top">
	    <fieldset>
          <legend><?php echo TEXT_PAYMENT_METHOD; ?></legend>
		  <div style="position: relative; height: 160px;">
<?php 
  echo html_pull_down_menu('shipper_code', $payment_modules, $order->shipper_code, 'onchange="activateFields()"') . chr(10);
  $count = 0;
  foreach ($payment_modules as $pmt_class) {
	$value = $pmt_class['id'];
	echo '          <div id="pm_' . $count . '" style="visibility:hidden; position:absolute; top:22px; left:1px">' . chr(10);
	// fetch the html inside of module
	$disp_fields = $$value->selection();
	for($i = 0; $i < sizeof($disp_fields['fields']); $i++) {
	  echo $disp_fields['fields'][$i]['title'] . '<br />' . chr(10);
	  echo $disp_fields['fields'][$i]['field'] . '<br />' . chr(10);
	}
	echo '          </div>' . chr(10);
	$count++;
  }
?>
		  </div>
		</fieldset>
	  </td>
<?php 
  } elseif (JOURNAL_ID == 20) { ?>
	  <td align="right" valign="top">
		<table class="ui-widget" style="border-style:none;width:100%">
		 <tbody class="ui-widget-content">
		  <tr>
			<td align="right"><?php echo BNK_ACCOUNT_BALANCE . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_input_field('acct_balance', $currencies->format($acct_balance), 'readonly="readonly" size="15" maxlength="20" style="text-align:right"'); ?></td>
		  </tr>
		  <tr>
			<td align="right"><?php echo BNK_BALANCE_AFTER_CHECKS . '&nbsp;'; ?></td>
			<td align="right"><?php echo html_input_field('end_balance', $currencies->format($acct_balance), 'readonly="readonly" size="15" maxlength="20" style="text-align:right"'); ?></td>
		  </tr>
		 </tbody>
		</table>
	  </td>
<?php } // end if (JOURNAL_ID == 20) ?>
	</tr>
  </tbody>
 </table>
</div>

<div>
  <table class="ui-widget" style="border-collapse:collapse;width:100%">
   <thead class="ui-widget-header">
	<tr>
	  <th align="center"><?php echo BNK_INVOICE_NUM; ?></th>
	  <th align="center"><?php echo BNK_DUE_DATE; ?></th>
	  <th align="center"><?php echo BNK_AMOUNT_DUE . (ENABLE_MULTI_CURRENCY ? ' (' . DEFAULT_CURRENCY . ')' : ''); ?></th>
	  <th align="center"><?php echo TEXT_NOTES; ?></th>
	  <th align="center"><?php echo TEXT_DISCOUNT . (ENABLE_MULTI_CURRENCY ? ' (' . DEFAULT_CURRENCY . ')' : ''); ?></th>
	  <th align="center"><?php echo constant('BNK_' . JOURNAL_ID . '_AMOUNT_PAID') . (ENABLE_MULTI_CURRENCY ? ' (' . DEFAULT_CURRENCY . ')' : ''); ?></th>
	  <th align="center"><?php echo TEXT_PAY; ?></th>
	</tr>
	</thead>
 	<tbody id="item_table" class="ui-widget-content">
	<?php if ($order->id_1) {
	  $i = 1;
	  while (true) {
		$id_num    = 'id_'    . $i;
		$inv_num   = 'inv_'   . $i;
		$prcnt_num = 'prcnt_' . $i;
		$early_num = 'early_' . $i;
		$acct_num  = 'acct_'  . $i;
		$due_num   = 'due_'   . $i;
		$amt_num   = 'amt_'   . $i;
		$desc_num  = 'desc_'  . $i;
		$dscnt_num = 'dscnt_' . $i;
		$total_num = 'total_' . $i;
		$pay_num   = 'pay_'   . $i;
		if (!isset($order->$id_num)) break; // no more rows to build, exit loop
		$extra_params = ($order->$inv_num) ? '' : 'readonly="readonly" ';
		echo '<tr' . (($extra_params) ? ' class="ui-state-error"' : '') . '>' . chr(10);
		echo '<td align="center">' . chr(10);
		echo html_input_field($inv_num, $order->$inv_num, 'readonly="readonly" size="15"') . chr(10);
		// Hidden fields
		echo html_hidden_field($id_num,    $order->$id_num)    . chr(10);
		echo html_hidden_field($prcnt_num, $order->$prcnt_num) . chr(10);
		echo html_hidden_field($early_num, $order->$early_num) . chr(10);
		echo html_hidden_field($acct_num,  $order->$acct_num)  . chr(10);
		// End hidden fields
		echo '</td>' . chr(10);
		echo '<td align="center">' . html_input_field($due_num,   gen_locale_date($order->$due_num), 'readonly="readonly" size="15"') . '</td>' . chr(10);
		echo '<td align="center">' . html_input_field($amt_num,   $currencies->format($currencies->clean_value($order->$amt_num)), 'readonly="readonly" size="12" style="text-align:right"') . '</td>' . chr(10);
		echo '<td align="center">' . html_input_field($desc_num,  $order->$desc_num, $extra_params . 'size="64" maxlength="64"') . '</td>' . chr(10);
		echo '<td align="center">' . html_input_field($dscnt_num, $currencies->format($currencies->clean_value($order->$dscnt_num)), $extra_params . 'size="15" maxlength="20" onchange="updateRowTotal(' . $i . ')" style="text-align:right"') . '</td>' . chr(10);
		echo '<td align="center">' . html_input_field($total_num, $currencies->format($currencies->clean_value($order->$total_num)), $extra_params . 'size="15" maxlength="20" onchange="updateUnitPrice(' . $i . ')" style="text-align:right"') . '</td>' . chr(10);
		echo '<td align="center">' . (($extra_params) ? '&nbsp;' : html_checkbox_field($pay_num, '1', ($order->$pay_num ? true : false), '', 'onclick="updatePayValues(' . $i . ')"')) . '</td>' . chr(10);
		echo '</tr>' . chr(10);
		$i++;
	  }
	} ?>
	</tbody>
  </table>
</div>
</form>
