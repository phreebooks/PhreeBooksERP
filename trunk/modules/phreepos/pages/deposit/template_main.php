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
//  Path: /modules/phreepos/pages/deposit/template_main.php
//

// start the form
echo html_form('bills_deposit', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'oID'))) . chr(10);

// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('bill_acct_id',    $order->bill_acct_id) . chr(10);	// id of the account in the bill to/remit to
echo html_hidden_field('id',              $order->id) . chr(10);	// db journal entry id, null = new entry; not null = edit
echo html_hidden_field('bill_address_id', $order->bill_address_id) . chr(10);
echo html_hidden_field('bill_telephone1', $order->bill_telephone1) . chr(10);
echo html_hidden_field('bill_email',      $order->bill_email) . chr(10);
echo html_hidden_field('gl_disc_acct_id', '') . chr(10);
if (JOURNAL_ID == 20) echo html_hidden_field('shipper_code',    '') . chr(10);

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
if ($security_level < 2) $toolbar->icon_list['save']['show'] = false;
$toolbar->icon_list['print']['params']  = 'onclick="submitToDo(\'print\')"';
if ($security_level < 2) $toolbar->icon_list['print']['show'] = false;
$toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . 'jID=' . JOURNAL_ID . '&amp;type=' . $type, 'SSL') . '\'"', 2);

// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
  foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}

// add the help file index and build the toolbar
switch (JOURNAL_ID) {
  case 18: $toolbar->add_help('07.05'); break;
  case 20: $toolbar->add_help('07.05'); break;
}
echo $toolbar->build_toolbar(); 

// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div>
  <table width="100%" border="0" cellspacing="2" cellpadding="2">
	<tr>
	  <td valign="top">
<?php
echo (($type == 'c') ? GEN_CUSTOMER_ID : GEN_VENDOR_ID) . ' ' . html_input_field('search', $order->search, 'onfocus="clearField(\'search\', \'' . TEXT_SEARCH . '\')" onblur="setField(\'search\', \'' . TEXT_SEARCH . '\')"'); 
echo '&nbsp;' . html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="billsAcctList(this)"');
?>
	  </td>
	  <td class="main" align="right">
	    <?php echo ((JOURNAL_ID == 20 || !isset($_SESSION['admin_encrypt'])) ? '&nbsp;' : BNK_TEXT_SAVE_PAYMENT_INFO . html_checkbox_field('save_payment', '1', ($order->save_payment ? true : false), '', '')); ?>
	  </td>
	  <td>
	    <?php echo html_pull_down_menu('payment_id', gen_null_pull_down(), '', 'style="visibility:hidden" onchange=\'fillPayment()\'') . chr(10); ?>
	  </td>
	</tr>
	<tr>
	  <td class="main" valign="top">
<?php 
echo (JOURNAL_ID == 20 ? TEXT_REMIT_TO : TEXT_BILL_TO) . chr(10);
echo            html_pull_down_menu('bill_to_select',    gen_null_pull_down(), '', 'onchange=\'fillAddress("bill")\'') . chr(10);
echo '<br />' . html_input_field('bill_primary_name',    $order->bill_primary_name, 'size="33" maxlength="32" onfocus="clearField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')" onblur="setField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_contact',         $order->bill_contact, 'size="33" maxlength="32" onfocus="clearField(\'bill_contact\', \'' . GEN_CONTACT . '\')" onblur="setField(\'bill_contact\', \'' . GEN_CONTACT . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_address1',        $order->bill_address1, 'size="33" maxlength="32" onfocus="clearField(\'bill_address1\', \'' . GEN_ADDRESS1 . '\')" onblur="setField(\'bill_address1\', \'' . GEN_ADDRESS1 . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_address2',        $order->bill_address2, 'size="33" maxlength="32" onfocus="clearField(\'bill_address2\', \'' . GEN_ADDRESS2 . '\')" onblur="setField(\'bill_address2\', \'' . GEN_ADDRESS2 . '\')"') . chr(10);
echo '<br />' . html_input_field('bill_city_town',       $order->bill_city_town, 'size="25" maxlength="24" onfocus="clearField(\'bill_city_town\', \'' . GEN_CITY_TOWN . '\')" onblur="setField(\'bill_city_town\', \'' . GEN_CITY_TOWN . '\')"') . chr(10);
echo            html_input_field('bill_state_province',  $order->bill_state_province, 'size="3" maxlength="5" onfocus="clearField(\'bill_state_province\', \'' . GEN_STATE_PROVINCE . '\')" onblur="setField(\'bill_state_province\', \'' . GEN_STATE_PROVINCE . '\')"') . chr(10);
echo            html_input_field('bill_postal_code',     $order->bill_postal_code, 'size="11" maxlength="10" onfocus="clearField(\'bill_postal_code\', \'' . GEN_POSTAL_CODE . '\')" onblur="setField(\'bill_postal_code\', \'' . GEN_POSTAL_CODE . '\')"') . chr(10);
echo '<br />' . html_pull_down_menu('bill_country_code', gen_get_countries(), $order->bill_country_code) . chr(10); 
?>
	  </td>
	  <td valign="top">
		<table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td class="main" align="right"><?php echo ((JOURNAL_ID == 18) ? BNK_TEXT_DEPOSIT_ID : BNK_TEXT_PAYMENT_ID) . '&nbsp;'; ?></td>
			<td class="main" align="right"><?php echo html_input_field('purchase_invoice_id', $next_inv_ref, 'style="text-align:right"'); ?></td>
		  </tr>
		  <tr>
			<td class="main" align="right"><?php echo TEXT_DATE . '&nbsp;'; ?></td>
			<td class="main" align="right"><?php echo html_calendar_field($cal_bills); ?></td>
		  </tr>
		  <tr>
			<td class="main" align="right"><?php echo TEXT_REFERENCE . '&nbsp;'; ?></td>
			<td class="main" align="right"><?php echo html_input_field('purch_order_id', $order->purch_order_id, 'style="text-align:right"'); ?></td>
		  </tr>
		  <tr>
			<td class="main" align="right"><?php echo BNK_CASH_ACCOUNT . '&nbsp;'; ?></td>
			<td class="main" align="right"><?php echo html_pull_down_menu('gl_acct_id', $gl_array_list, $order->gl_acct_id, 'onchange="loadNewBalance(this.value)"'); ?></td>
		  </tr>
		  <tr>
			<td class="main" align="right">
			  <?php echo TEXT_TOTAL; ?>
			  <?php echo (ENABLE_MULTI_CURRENCY) ? ' (' . DEFAULT_CURRENCY . ')' : ''; ?>
			</td>
			<td align="right">
				<?php 
				echo html_input_field('total', $order->total_amount, 'readonly="readonly" size="15" maxlength="20" style="text-align:right"');
				?>
			</td>
		  </tr>
		</table>
	  </td>
<?php if (JOURNAL_ID == 18) { ?>
	  <td valign="top">
	    <fieldset>
          <legend><?php echo TEXT_PAYMENT_METHOD; ?></legend>
		  <div style="position: relative; height: 160px;">
<?php echo html_pull_down_menu('shipper_code', $payment_modules, $order->shipper_code, 'onchange="activateFields()"') . chr(10);
	$count = 0;
	foreach ($payment_modules as $pmt_class) {
		$value = $pmt_class['id'];
		echo '          <div id="pm_' . $count . '" style="visibility:hidden; position:absolute; top:22px; left:1px">' . chr(10);
		// fetch the html inside of module
		$disp_fields = $$value->selection();
		for($i=0; $i<count($disp_fields['fields']); $i++) {
		  echo $disp_fields['fields'][$i]['title'] . '<br />' . chr(10);
		  echo $disp_fields['fields'][$i]['field'] . '<br />' . chr(10);
		}
		echo '          </div>' . chr(10);
		$count++;
	}
	echo html_hidden_field('acct_balance', $currencies->format($acct_balance)) . chr(10);
	echo html_hidden_field('end_balance',  $currencies->format($acct_balance)) . chr(10);
?>
		  </div>
		</fieldset>
	  </td>
<?php } elseif (JOURNAL_ID == 20) { ?>
	  <td align="right" valign="top">
		<table border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td class="main" align="right"><?php echo BNK_ACCOUNT_BALANCE . '&nbsp;'; ?></td>
			<td class="main" align="right"><?php echo html_input_field('acct_balance', $currencies->format($acct_balance), 'readonly="readonly" size="15" maxlength="20" style="text-align:right"'); ?></td>
		  </tr>
		  <tr>
			<td class="main" align="right"><?php echo BNK_BALANCE_AFTER_CHECKS . '&nbsp;'; ?></td>
			<td class="main" align="right"><?php echo html_input_field('end_balance', $currencies->format($acct_balance), 'readonly="readonly" size="15" maxlength="20" style="text-align:right"'); ?></td>
		  </tr>
		</table>
	  </td>
<?php } // end if (JOURNAL_ID == 20) ?>
	</tr>
  </table>
</div>

<div>
  <table id="item_table"><tr><td></td></tr></table><!-- null table to get cleared -->
  <table align="center" border="1" cellpadding="1" cellspacing="1">
  	<tr>
	  <th align="center"><?php echo TEXT_DESCRIPTION; ?></th>
	  <th align="center"><?php echo TEXT_GL_ACCOUNT; ?></th>
	  <th align="center"><?php echo constant('BNK_' . JOURNAL_ID . '_AMOUNT_PAID') . (ENABLE_MULTI_CURRENCY ? ' (' . DEFAULT_CURRENCY . ')' : ''); ?></th>
	</tr>
	<?php 
		echo '<tr>' . chr(10);
		echo '  <td class="main" align="center">' . chr(10);
		// Hidden fields
		echo html_hidden_field('id_1',   $order->id_1)   . chr(10);
		// End hidden fields
		echo html_input_field('desc_1', $order->desc_1, 'size="64" maxlength="64"');
		echo '  </td>' . chr(10);
		echo '  <td class="main" align="center">' . html_pull_down_menu('acct_1', $gl_array_list, $order->acct_1, '') . '</td>' . chr(10);
		echo '  <td class="main" align="center">' . html_input_field('total_1', $currencies->format($order->total_1), 'size="11" maxlength="20" onchange="updateDepositPrice()" style="text-align:right"') . '</td>' . chr(10);
		echo '</tr>' . chr(10);
	?>
  </table>
</div>
</form>