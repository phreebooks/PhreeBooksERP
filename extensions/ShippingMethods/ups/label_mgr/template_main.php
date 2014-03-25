<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/shipping/methods/ups/label_mgr/template_main.php
//

// start the form
echo html_form('label_mgr', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);

// include hidden fields
echo html_hidden_field('action', '') . chr(10);

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show'] = false;
$toolbar->icon_list['save']['show'] = false;
$toolbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . SHIPPING_DELETE_CONFIRM . '\')) submitToDo(\'delete\')"';
$toolbar->icon_list['print']['params'] = 'onclick="submitToDo(\'label\')"';
$toolbar->icon_list['print']['text'] = SHIPPING_PRINT_LABEL;

// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
	foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}

// add the help file index and build the toolbar
$toolbar->add_help('09.01');
echo $toolbar->build_toolbar();

// Build the page
?>
<h1><?php echo MODULE_SHIPPING_UPS_TEXT_TITLE; ?></h1>
<table border="0">
<tr><td width="50%" valign="top">
<fieldset id="recp">
<legend><?php echo SRV_SHIP_UPS_RECP_INFO; ?></legend>
<table border="0">
	<tr>
		<td class="dataTableContent"><?php echo GEN_PRIMARY_NAME; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_primary_name', $sInfo->ship_primary_name, 'size="33" maxlength="32"', true); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_CONTACT; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_contact', $sInfo->ship_contact, 'size="33" maxlength="32"'); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_ADDRESS1; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_address1', $sInfo->ship_address1, 'size="33" maxlength="32"', true); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_ADDRESS2; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_address2', $sInfo->ship_address2, 'size="33" maxlength="32"'); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_CITY_TOWN; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_city_town', $sInfo->ship_city_town, 'size="25" maxlength="24"', true); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_STATE_PROVINCE; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_state_province', strtoupper($sInfo->ship_state_province), 'size="3" maxlength="2"', true); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_POSTAL_CODE; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_postal_code', $sInfo->ship_postal_code, 'size="11" maxlength="10"', true); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_COUNTRY; ?></td>
		<td class="dataTableContent"><?php echo html_pull_down_menu('ship_country_code', gen_get_countries(), $sInfo->ship_country_code) . chr(10); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo GEN_TELEPHONE1; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_telephone1', $sInfo->ship_telephone1, 'size="17" maxlength="16"', true); ?></td>
	</tr>
</table>
</fieldset>
<fieldset>
<legend><?php echo SRV_SHIP_UPS_EMAIL_NOTIFY; ?></legend>
<table border="0">
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_EMAIL_RECIPIENT; ?></td>
		<td class="dataTableContent">
			<?php echo html_checkbox_field('email_rcp_ship', '0', $sInfo->email_rcp_ship = true, '') . ' ' . TEXT_SHIP . ' ';
			echo html_checkbox_field('email_rcp_excp', '1', $sInfo->email_rcp_excp, '') . ' ' . SHIPPING_TEXT_EXCEPTION . ' ';
			echo html_checkbox_field('email_rcp_dlvr', '2', $sInfo->email_rcp_dlvr, '') . ' ' . SHIPPING_TEXT_DELIVER; ?>
		</td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_EMAIL_RECIPIENT_ADD; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ship_email', $sInfo->ship_email, 'size="33" maxlength="32"'); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_EMAIL_SENDER; ?></td>
		<td class="dataTableContent">
			<?php echo html_checkbox_field('email_sndr_ship', '0', $sInfo->email_sndr_ship, '') . ' ' . TEXT_SHIP . ' ';
			echo html_checkbox_field('email_sndr_excp', '1', $sInfo->email_sndr_excp = true, '') . ' ' . SHIPPING_TEXT_EXCEPTION . ' ';
			echo html_checkbox_field('email_sndr_dlvr', '2', $sInfo->email_sndr_dlvr, '') . ' ' . SHIPPING_TEXT_DELIVER; ?>
		</td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_EMAIL_SENDER_ADD; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('sender_email_address', COMPANY_EMAIL, 'size="33" maxlength="32"'); ?></td>
	</tr>
</table>
</fieldset>
<fieldset>
<legend><?php echo SRV_SHIP_UPS_BILL_DETAIL; ?></legend>
<table border="0">
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_BILL_CHARGES_TO; ?></td>
		<td class="dataTableContent"><?php echo html_pull_down_menu('bill_charges', gen_build_pull_down($shipping_defaults['bill_options']), $sInfo->bill_charges); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_THIRD_PARTY; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('bill_acct', $sInfo->bill_acct); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_THIRD_PARTY_ZIP; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('third_party_zip', $sInfo->third_party_zip); ?></td>
	</tr>
</table>
</fieldset>
</td><td width="50%" valign="top">
<fieldset>
<legend><?php echo SHIPPNIG_SUMMARY; ?></legend>
<table border="0">
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_TOTAL_WEIGHT; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('total_weight', $sInfo->total_weight, 'readonly="readonly" size="6" maxlength="5" style="text-align:right"'); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_TOTAL_VALUE; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('total_value', $sInfo->total_value, 'readonly="readonly" size="8" maxlength="7" style="text-align:right"'); ?></td>
	</tr>
</table>
</fieldset>
<fieldset id="recp"><legend><?php echo SHIPPING_SHIPMENT_DETAILS; ?></legend>
<table border="0">
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_TEXT_SHIPMENT_DATE; ?></td>
		<td class="dataTableContent"><?php echo html_calendar_field($cal_ship); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_SERVICE_TYPE; ?></td>
		<td class="dataTableContent"><?php echo html_pull_down_menu('ship_method', gen_build_pull_down($shipping_methods), $sInfo->ship_method); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo TEXT_INVOICE; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('purchase_invoice_id', $sInfo->purchase_invoice_id); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo TEXT_PO_NUMBER; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('so_po_ref_id', $sInfo->so_po_ref_id); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_TEXT_PACKAGE_TYPE; ?>
		<td class="dataTableContent"><?php echo html_pull_down_menu('pkg_type', gen_build_pull_down($shipping_defaults['package_type']), $sInfo->pkg_type); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent"><?php echo SHIPPING_TEXT_PICKUP_SERVICE; ?>
		<td class="dataTableContent"><?php echo html_pull_down_menu('pickup_service', gen_build_pull_down($shipping_defaults['pickup_service']), $sInfo->pickup_service); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent" colspan="2">
			<?php echo html_checkbox_field('residential_address', '1', $sInfo->residential_address);
			echo SHIPPING_RESIDENTIAL_ADDRESS; ?>
		</td>
	</tr>
	<tr>
		<td class="dataTableContent" colspan="2">
			<?php echo html_checkbox_field('additional_handling', '1', $sInfo->additional_handling);
			echo SHIPPING_ADDITIONAL_HANDLING; ?>
		</td>
	</tr>
	<tr>
		<td class="dataTableContent" colspan="2" >
			<?php  echo html_checkbox_field('delivery_confirmation', '1', $sInfo->delivery_confirmation);
			echo SHIPPING_TEXT_DELIVERY_CONFIRM;
			echo html_pull_down_menu('delivery_confirmation_type', gen_build_pull_down($shipping_defaults['delivery_confirmation']), $sInfo->delivery_confirmation_type); ?>
		</td>
	</tr>
	<tr>
		<td class="dataTableContent" colspan="2">
			<?php  echo html_checkbox_field('saturday_delivery', '1', $sInfo->saturday_delivery);
			echo SHIPPING_SATURDAY_DELIVERY; ?>
		</td>
	</tr>
	<tr>
		<td class="dataTableContent" colspan="2">
			<?php echo html_checkbox_field('cod', '1', $sInfo->cod);
			echo SHIPPING_COD_AMOUNT;
			echo html_input_field('total_amount',  $sInfo->total_amount, 'size="6" style="text-align:right"') . '&nbsp;';
			echo html_pull_down_menu('cod_currency', gen_get_pull_down(TABLE_CURRENCIES, false, false, 'code', 'title'), $sInfo->cod_currency) . '&nbsp;';
			echo html_pull_down_menu('cod_payment_type', gen_build_pull_down($shipping_defaults['cod_funds_code']), $sInfo->cod_payment_type); ?>
		</td>
	</tr>
	<tr>
		<td class="dataTableContent" colspan="2">
			<?php echo html_checkbox_field('return_service', '1', $sInfo->return_service);
			echo SHIPPING_TEXT_RETURN_SERVICES;
			echo html_pull_down_menu('return_service_value', gen_build_pull_down($shipping_defaults['return_label']), $sInfo->return_service_value); ?>
		</td>
	</tr>
</table>
</fieldset>
<!--
<div>
<fieldset>
<legend><?php echo SRV_SHIP_UPS_LTL_FREIGHT; ?></legend>
<table border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td class="dataTableContent"><?php echo TEXT_DESCRIPTION; ?></td>
		<td class="dataTableContent"><?php echo html_input_field('ltl_description', $sInfo->ltl_description, 'size="33" maxlength="32"'); ?></td>
	</tr>
	<tr>
		<td class="dataTableContent" colspan="2"><?php echo SRV_UPS_LTL_CLASS . ' ' . html_pull_down_menu('ltl_class', gen_build_pull_down($ltl_classes), $sInfo->ltl_class); ?></td>
	</tr>
</table>
</fieldset>
</div>
-->
</td></tr></table>
<table border="0">
  <tr>
	<th><?php echo SHIPPING_PACKAGE_DETAILS; ?></th>
  </tr>
  <tr>
    <td id="productList" class="formArea">
	  <table id="item_table" width="100%" border="1">
        <tr>
			<td class="dataTableHeadingContent" align="center">
				<?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?>
			</td>
			<td class="dataTableHeadingContent" align="center"><?php echo TEXT_QUANTITY; ?></td>
			<td class="dataTableHeadingContent" align="center"><?php echo TEXT_WEIGHT;
				echo html_pull_down_menu('pkg_weight_unit', gen_build_pull_down($shipping_defaults['weight_unit']), $sInfo->pkg_weight_unit) . '&nbsp;'; ?>
			</td>
			<td class="dataTableHeadingContent" align="center" colspan="3"><?php echo SHIPPING_TEXT_DIMENSIONS;
				echo html_pull_down_menu('pkg_dimension_unit', gen_build_pull_down($shipping_defaults['dimension_unit']), $sInfo->pkg_dimension_unit); ?>
			</td>
			<td class="dataTableHeadingContent" align="center"><?php echo TEXT_VALUE;
				echo html_pull_down_menu('insurance_currency', $currency_array, $sInfo->insurance_currency); ?></td>
        </tr>
		<?php
		  if (isset($sInfo->package)) {
		  	$rowCnt = 1;
		    foreach ($sInfo->package as $package) { ?>
	          <tr>
			  <td align="center">
			  <?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'style="cursor:pointer" onclick="if (confirm(\'' . SHIPPING_DELETE_CONFIRM . '\')) removeRow(' . $rowCnt . ');"'); ?>
			  </td>
			  <td class="main" align="center"><?php echo html_input_field('qty_' . $rowCnt, '1', 'size="6" maxlength="5" style="text-align:right"'); ?></td>
			  <td class="main" align="center"><?php echo html_input_field('wt_' . $rowCnt, $package['weight'], 'size="5" maxlength="4" style="text-align:right" onchange="updateWeight(' . $rowCnt . ')"'); ?></td>
			  <td class="main" align="center"><?php echo html_input_field('len_' . $rowCnt, $package['length'], 'size="5" maxlength="4" style="text-align:right"'); ?></td>
			  <td class="main" align="center"><?php echo html_input_field('wid_' . $rowCnt, $package['width'], 'size="5" maxlength="4" style="text-align:right"'); ?></td>
			  <td class="main" align="center"><?php echo html_input_field('hgt_' . $rowCnt, $package['height'], 'size="5" maxlength="4" style="text-align:right"'); ?></td>
			  <td class="main" align="center"><?php echo html_input_field('ins_' . $rowCnt, $package['value'], 'size="8" maxlength="7" style="text-align:right" onchange="updateWeight(' . $rowCnt . ')"'); ?></td>
	          </tr>
		      <?php
			  $rowCnt++;
			}
		  } else {
			echo '<script type="text/javascript">addRow();</script>';
		  } ?>
      </table>
	</td>
  </tr>
  <tr>
    <td align="left"><?php echo html_icon('actions/list-add.png', TEXT_ADD, 'medium', 'onclick="addRow()"'); ?></td>
  </tr>
</table>
</form>