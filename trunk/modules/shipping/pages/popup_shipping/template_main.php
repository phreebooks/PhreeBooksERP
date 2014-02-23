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
//  Path: /modules/shipping/pages/popup_shipping/template_main.php
//
echo html_form('step1', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('continue', 'onclick="submitToDo(\'rates\')"', $order = 10);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('09');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo SHIPPING_ESTIMATOR_OPTIONS; ?></h1>
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo SHIPPING_TEXT_SHIPPER; ?></td>
	<td><?php echo SHIPPING_TEXT_SHIPMENT_DATE . ' ' . html_calendar_field($cal_ship); ?></td>
  </tr>
  <tr>
	<td><?php echo SHIPPING_TEXT_SHIP_FROM_CITY; ?>
<?php echo html_input_field('ship_city_town', $pkg->ship_city_town, $parameters = ''); ?>
	</td>
	<td><?php echo SHIPPING_TEXT_SHIP_TO_CITY; ?>
<?php 
echo html_input_field('ship_to_city', $pkg->ship_to_city, $parameters = '');
echo html_checkbox_field('residential_address', '1', $pkg->residential_address);
echo SHIPPING_RESIDENTIAL_ADDRESS;
?>
	</td>
  </tr>
  <tr>
	<td>
<?php echo SHIPPING_TEXT_SHIP_FROM_STATE;
echo html_input_field('ship_state_province', $pkg->ship_state_province, $parameters = ''); ?></td>
	<td>
<?php echo SHIPPING_TEXT_SHIP_TO_STATE;
echo html_input_field('ship_to_state', $pkg->ship_to_state, $parameters = ''); ?>
	</td>
  </tr>
  <tr>
	<td>
<?php echo SHIPPING_TEXT_SHIP_FROM_ZIP;
echo html_input_field('ship_postal_code', $pkg->ship_postal_code, $parameters = ''); ?>
	</td>
	<td>
<?php echo SHIPPING_TEXT_SHIP_TO_ZIP;
echo html_input_field('ship_to_postal_code', $pkg->ship_to_postal_code, $parameters = ''); ?>
	</td>
  </tr>
  <tr>
	<td>
<?php echo SHIPPING_TEXT_SHIP_FROM_COUNTRY;
$country_list = gen_get_countries();
echo html_pull_down_menu('ship_country_code', $country_list, $pkg->ship_country_code) . chr(10);
?>
	 </td>
	 <td>
<?php echo SHIPPING_TEXT_SHIP_TO_COUNTRY;
echo html_pull_down_menu('ship_to_country_code', $country_list, $pkg->ship_to_country_code) . chr(10); ?>
	 </td>
  </tr>
  <tr>
	<td valign="top">
	  <table class="ui-widget" style="border-style:none;width:100%">
	   <thead class="ui-widget-header">
		<tr><th><?php echo SHIPPING_TEXT_PACKAGE_INFORMATION; ?></th></tr>
	   </thead>
	   <tbody class="ui-widget-content">
		<tr><td>
		<?php echo SHIPPING_TEXT_PACKAGE_TYPE; 
		echo html_pull_down_menu('pkg_type', gen_build_pull_down($shipping_defaults['package_type']), $pkg->pkg_type, $parameters = '', $required = false); ?>
		</td></tr>
		<tr><td>
		<?php echo SHIPPING_TEXT_PICKUP_SERVICE;
		echo html_pull_down_menu('pickup_service', gen_build_pull_down($shipping_defaults['pickup_service']), $pkg->pickup_service, $parameters = '', $required = false); ?>
		</td></tr>
		<tr><td>
		<?php echo SHIPPING_TEXT_DIMENSIONS . ' ' . TEXT_LENGTH;
		echo html_input_field('pkg_length', $pkg->pkg_length, 'size="4"');
		echo TEXT_WIDTH; 
		echo html_input_field('pkg_width', $pkg->pkg_width, 'size="4"');
		echo TEXT_HEIGHT; 
		echo html_input_field('pkg_height', $pkg->pkg_height, 'size="4"') . '&nbsp;';
		echo html_pull_down_menu('pkg_dimension_unit', gen_build_pull_down($shipping_defaults['dimension_unit']), $pkg->pkg_dimension_unit, $parameters = '', $required = false); ?>
		</td></tr>
		<tr><td>
		<?php echo TEXT_WEIGHT;
		echo html_hidden_field('pkg_total', $pkg->pkg_total);
		echo html_hidden_field('pkg_item_count', $pkg->pkg_item_count);
		echo html_input_field('pkg_weight', $pkg->pkg_weight, 'size="5"') . '&nbsp;';
		echo html_pull_down_menu('pkg_weight_unit', gen_build_pull_down($shipping_defaults['weight_unit']), $pkg->pkg_weight_unit, $parameters = '', $required = false) . '&nbsp;';
		if (SHIPPING_DEFAULT_ADDITIONAL_HANDLING_SHOW) {
			echo html_checkbox_field('additional_handling', '1', $pkg->additional_handling);
			echo SHIPPING_ADDITIONAL_HANDLING;
		} else echo '&nbsp;'; ?>
		</td></tr>
		<tr><td>
		<?php  if (SHIPPING_DEFAULT_INSURANCE_SHOW) {
			echo html_checkbox_field('insurance', '1', $pkg->insurance);
			echo SHIPPING_INSURANCE_AMOUNT;
			echo html_input_field('insurance_value', $pkg->insurance_value, 'size="6"') . '&nbsp;';
			echo html_pull_down_menu('insurance_currency', gen_get_pull_down(TABLE_CURRENCIES, false, false, 'code', 'title'), $pkg->insurance_currency, $parameters = '', $required = false);
		} else echo '&nbsp;'; ?>
		</td></tr>
		<tr><td colspan="2">
		<?php  if (SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_SHOW) {
			echo html_checkbox_field('split_large_shipments', '1', $pkg->split_large_shipments);
			echo SHIPPING_SPLIT_LARGE_SHIPMENTS;
			echo html_input_field('split_large_shipments_value', $pkg->split_large_shipments_value, 'size="5"') . '&nbsp;';
			echo html_pull_down_menu('split_large_shipments_unit', gen_build_pull_down($shipping_defaults['weight_unit']), $pkg->split_large_shipments_unit, $parameters = '', $required = false);
			echo SHIPPING_TEXT_PER_BOX;
		} else echo '&nbsp;'; ?>
		</td></tr>
		<tr><td colspan="2">
		<?php  if (SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_SHOW) {
			echo html_checkbox_field('delivery_confirmation', '1', $pkg->delivery_confirmation);
			echo SHIPPING_TEXT_DELIVERY_CONFIRM;
			echo html_pull_down_menu('delivery_confirmation_type', gen_build_pull_down($shipping_defaults['delivery_confirmation']), $pkg->delivery_confirmation_type, $parameters = '', $required = false);
		} else echo '&nbsp;'; ?>
		</td></tr>
	   </tbody>
	  </table>
	</td>
	<td valign="top" class="dataTableContent">
	  <table class="ui-widget" style="border-style:none;width:100%">
	   <thead class="ui-widget-header">
		<tr><th align="center"><?php echo SHIPPING_SPECIAL_OPTIONS; ?></th></tr>
	   </thead>
	   <tbody class="ui-widget-content">
		<tr><td colspan="2">
		<?php  if (SHIPPING_DEFAULT_HANDLING_CHARGE_SHOW) {
			echo html_checkbox_field('handling_charge', '1', $pkg->handling_charge);
			echo SHIPPING_HANDLING_CHARGE;
			echo html_input_field('handling_charge_value', $pkg->handling_charge_value, 'size="6"') . '&nbsp;';
			echo html_pull_down_menu('handling_charge_currency', $currency_array, $pkg->handling_charge_currency, $parameters = '', $required = false);
		} else echo '&nbsp;'; ?>
		</td></tr>
		<tr><td colspan="2">
		<?php  if (SHIPPING_DEFAULT_COD_SHOW) {
			echo html_checkbox_field('cod', '1', $pkg->cod);
			echo SHIPPING_COD_AMOUNT;
			echo html_input_field('cod_amount',  $pkg->cod_amount, 'size="6"') . '&nbsp;';
			echo html_pull_down_menu('cod_currency', $currency_array, $pkg->cod_currency, $parameters = '', $required = false) . '&nbsp;';
			echo html_pull_down_menu('cod_payment_type', gen_build_pull_down($shipping_defaults['cod_funds_code']), $pkg->cod_payment_type, $parameters = '', $required = false);
		} else echo '&nbsp;'; ?>
		</td></tr>
		<tr><td>
		<?php  if (SHIPPING_DEFAULT_SATURDAY_PICKUP_SHOW) {
			echo html_checkbox_field('saturday_pickup', '1', $pkg->saturday_pickup);
			echo SHIPPING_SATURDAY_PICKUP;
		} else echo '&nbsp;'; ?>
		</td></tr>
		<tr><td>
		<?php  if (SHIPPING_DEFAULT_SATURDAY_DELIVERY_SHOW) {
			echo html_checkbox_field('saturday_delivery', '1', $pkg->saturday_delivery);
			echo SHIPPING_SATURDAY_DELIVERY;
		} else echo '&nbsp;'; ?>
		</td></tr>
		<?php  if (SHIPPING_DEFAULT_HAZARDOUS_SHOW) {
			echo '<tr><td>';
			echo html_checkbox_field('hazardous_material', '1', $pkg->hazardous_material);
			echo SHIPPING_HAZARDOUS_MATERIALS;
			echo '</td></tr>';
		} ?>
		<?php  if (SHIPPING_DEFAULT_DRY_ICE_SHOW) {
			echo '<tr><td>';
			echo html_checkbox_field('dry_ice', '1', $pkg->dry_ice);
			echo SHIPPING_TEXT_DRY_ICE;
			echo '</td></tr>';
		} ?>
		<?php  if (SHIPPING_DEFAULT_RETURN_SERVICE_SHOW) {
			echo '<tr><td>';
			echo html_checkbox_field('return_service', '1', $pkg->return_service);
			echo SHIPPING_TEXT_RETURN_SERVICES;
			echo html_pull_down_menu('return_service_value', gen_build_pull_down($shipping_defaults['return_label']), $pkg->return_service_value, $parameters = '', $required = false);
			echo '</td></tr>';
		} ?>
		<?php  if (defined('SHIPPING_DEFAULT_LTL_CLASS')) {
			echo '<tr><td>';
			echo SHIPPING_LTL_FREIGHT_CLASS;
			echo html_pull_down_menu('ltl_class', gen_build_pull_down($ltl_classes), SHIPPING_DEFAULT_LTL_CLASS, $parameters = '', $required = false);
			echo '</td></tr>';
		} ?>
	   </tbody>
	  </table>
	</td>
  </tr>
  <tr class="ui-widget-header"><th colspan="2" align="center"><?php echo SHIPPING_TEXT_METHODS; ?></th></tr>
<?php 
foreach ($admin_classes['shipping']->methods as $method) {
	if ($method->installed){
  		echo '  <tr><td colspan="2">';
  		echo html_checkbox_field('ship_method_' . $method->id, '1', '1') . ' ' . $method->text;
  		echo '</td></tr>' . chr(10);
	}
}
?>
</tbody>
</table>
</form>