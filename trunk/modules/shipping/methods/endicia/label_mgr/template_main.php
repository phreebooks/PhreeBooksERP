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
//  Path: /modules/shipping/methods/endicia/label_mgr/template_main.php
//
echo html_form('label_mgr', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['params']  = 'onclick="submitToDo(\'label\')"';
$toolbar->icon_list['print']['text']    = SHIPPING_TEXT_PRINT_LABEL;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('09.01');
echo $toolbar->build_toolbar();
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<?php if ($auto_print) { ?>
  <applet id="qz" name="QZ Print Plugin" code="qz.PrintApplet.class" archive="<?php echo DIR_WS_ADMIN . 'modules/phreedom/includes/jzebra/bz-print.jar'; ?>" width="16px" height="16px"></applet>
  <?php echo html_button_field('print_label', TEXT_PRINT, 'onclick="labelPrint()"'); ?>
<?php } else { ?>
  <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
    <tr><th><?php echo SHIPPING_PACKAGE_DETAILS; ?></th></tr>
    <tr>
      <td id="productList">
	    <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
 		 <thead class="ui-widget-header">
          <tr>
			<th><?php echo TEXT_QUANTITY; ?></th>
			<th><?php echo TEXT_WEIGHT; 
				echo html_pull_down_menu('pkg_weight_unit', gen_build_pull_down($shipping_defaults['weight_unit']), $sInfo->pkg_weight_unit) . '&nbsp;'; ?>
			</th>
			<th colspan="3"><?php echo SHIPPING_TEXT_DIMENSIONS;
				echo html_pull_down_menu('pkg_dimension_unit', gen_build_pull_down($shipping_defaults['dimension_unit']), $sInfo->pkg_dimension_unit); ?>
			</th>
			<th><?php echo TEXT_VALUE; 
				echo html_pull_down_menu('insurance_currency', gen_get_pull_down(TABLE_CURRENCIES, false, false, 'code', 'title'), $sInfo->insurance_currency); ?></th>
          </tr>
		 </thead>
		 <tbody id="item_table" class="ui-widget-content">
	       <tr>
		     <td align="center"><?php echo html_input_field('qty_1', '1', 'readonly="readonly" size="6" maxlength="5" style="text-align:right"'); ?></td>
		     <td align="center"><?php echo html_input_field('wt_1',  $package['weight'], 'size="5" maxlength="4" style="text-align:right"'); ?></td>
		     <td align="center"><?php echo html_input_field('len_1', $package['length'], 'size="5" maxlength="4" style="text-align:right"'); ?></td>
		     <td align="center"><?php echo html_input_field('wid_1', $package['width'],  'size="5" maxlength="4" style="text-align:right"'); ?></td>
		     <td align="center"><?php echo html_input_field('hgt_1', $package['height'], 'size="5" maxlength="4" style="text-align:right"'); ?></td>
		     <td align="center"><?php echo html_input_field('ins_1', $package['value'],  'size="8" maxlength="7" style="text-align:right"'); ?></td>
	       </tr>
		 </tbody>
        </table>
	  </td>
    </tr>
  </table>
  <table>
  <tr><td width="50%" valign="top">
  <div>
    <fieldset>
    <legend><?php echo SHIPPING_RECP_INFO; ?></legend>
    <table>
	  <tr>
		<td><?php echo GEN_PRIMARY_NAME; ?></td>
		<td><?php echo html_input_field('ship_primary_name', $sInfo->ship_primary_name, 'size="33" maxlength="32"', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_CONTACT; ?></td>
		<td><?php echo html_input_field('ship_contact', $sInfo->ship_contact, 'size="33" maxlength="32"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_ADDRESS1; ?></td>
		<td><?php echo html_input_field('ship_address1', $sInfo->ship_address1, 'size="33" maxlength="32"', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_ADDRESS2; ?></td>
		<td><?php echo html_input_field('ship_address2', $sInfo->ship_address2, 'size="33" maxlength="32"'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_CITY_TOWN; ?></td>
		<td><?php echo html_input_field('ship_city_town', $sInfo->ship_city_town, 'size="25" maxlength="24"', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_STATE_PROVINCE; ?></td>
		<td><?php echo html_input_field('ship_state_province', $sInfo->ship_state_province, 'size="3" maxlength="2"', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_POSTAL_CODE; ?></td>
		<td><?php echo html_input_field('ship_postal_code', $sInfo->ship_postal_code, 'size="11" maxlength="10"', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_COUNTRY; ?></td>
		<td><?php echo html_pull_down_menu('ship_country_code', gen_get_countries(), $sInfo->ship_country_code) . chr(10); ?></td>
	  </tr>
	  <tr>
		<td><?php echo GEN_TELEPHONE1; ?></td>
		<td><?php echo html_input_field('ship_telephone1', $sInfo->ship_telephone1, 'size="17" maxlength="16"'); ?></td>
	  </tr>
    </table>
    </fieldset>
  </div>
  </td>
  <td width="50%" valign="top">
  <div>
    <fieldset>
    <legend><?php echo SHIPPING_SHIPMENT_DETAILS; ?></legend>
    <table>
	  <tr>
		<td><?php echo SHIPPING_TEXT_SHIPMENT_DATE; ?></td>
		<td><?php echo html_calendar_field($cal_ship); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SHIPPING_SERVICE_TYPE; ?></td>
		<td><?php echo html_pull_down_menu('ship_method', gen_build_pull_down($shipping_methods), $sInfo->ship_method); ?></td>
	  </tr>
	  <tr>
		<td><?php echo TEXT_INVOICE; ?></td>
		<td><?php echo html_input_field('purchase_invoice_id', $sInfo->purchase_invoice_id); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SHIPPING_TEXT_PACKAGE_TYPE; ?></td>
		<td><?php echo html_pull_down_menu('pkg_type', gen_build_pull_down($shipping_defaults['package_type']), $sInfo->pkg_type ? $sInfo->pkg_type : 'FlatRateEnvelope'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SHIPPING_TEXT_DELIVERY_CONFIRM; ?></td>
		<td><?php echo html_checkbox_field('delivery_confirmation', '1', $sInfo->delivery_confirmation ? $sInfo->delivery_confirmation : true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SHIPPING_COD_AMOUNT . ' ' . html_checkbox_field('cod', '1', $sInfo->cod); ?></td>
		<td><?php echo html_input_field('total_amount',  $sInfo->total_amount, 'size="6" style="text-align:right"') . '&nbsp;'; ?></td>
	  </tr>
    </table>
    </fieldset>
  </div>
  </td></tr>
  </table>
  <?php
} // end if ($auto_print) else ?>

</form>