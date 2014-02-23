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
//  Path: /modules/shipping/pages/admin/template_tab_general.php
//
?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_SHIPPING_PREFS; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
	  <tr>
	    <td colspan="3"><?php echo sprintf(CONTACT_SHIP_FIELD_REQ, GEN_CONTACT); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_ship_contact_req', $sel_yes_no, $_POST['address_book_ship_contact_req'] ? $_POST['address_book_ship_contact_req'] : ADDRESS_BOOK_SHIP_CONTACT_REQ, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo sprintf(CONTACT_SHIP_FIELD_REQ, GEN_ADDRESS1); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_ship_add1_req', $sel_yes_no, $_POST['address_book_ship_add1_req'] ? $_POST['address_book_ship_add1_req'] : ADDRESS_BOOK_SHIP_ADD1_REQ, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo sprintf(CONTACT_SHIP_FIELD_REQ, GEN_ADDRESS2); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_ship_add2_req', $sel_yes_no, $_POST['address_book_ship_add2_req'] ? $_POST['address_book_ship_add2_req'] : ADDRESS_BOOK_SHIP_ADD2_REQ, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo sprintf(CONTACT_SHIP_FIELD_REQ, GEN_CITY_TOWN); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_ship_city_req', $sel_yes_no, $_POST['address_book_ship_city_req'] ? $_POST['address_book_ship_city_req'] : ADDRESS_BOOK_SHIP_CITY_REQ, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo sprintf(CONTACT_SHIP_FIELD_REQ, GEN_STATE_PROVINCE); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_ship_state_req', $sel_yes_no, $_POST['address_book_ship_state_req'] ? $_POST['address_book_ship_state_req'] : ADDRESS_BOOK_SHIP_STATE_REQ, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo sprintf(CONTACT_SHIP_FIELD_REQ, GEN_POSTAL_CODE); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_ship_postal_code_req', $sel_yes_no, $_POST['address_book_ship_postal_code_req'] ? $_POST['address_book_ship_postal_code_req'] : ADDRESS_BOOK_SHIP_POSTAL_CODE_REQ, ''); ?></td>
	  </tr>

	  <tr class="ui-widget-header"><th colspan="4"><?php echo TEXT_PAGKAGE_DEFAULTS; ?></th></tr>
	  <tr>
	    <td colspan="3"><?php echo CD_10_01_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_weight_unit', gen_build_pull_down($shipping_defaults['weight_unit']), $_POST['shipping_default_weight_unit'] ? $_POST['shipping_default_weight_unit'] : SHIPPING_DEFAULT_WEIGHT_UNIT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_10_02_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_currency', gen_get_pull_down(TABLE_CURRENCIES, false, false, 'code', 'title'), $_POST['shipping_default_currency'] ? $_POST['shipping_default_currency'] : SHIPPING_DEFAULT_CURRENCY, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_10_03_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_pkg_dim_unit', gen_build_pull_down($shipping_defaults['dimension_unit']), $_POST['shipping_default_pkg_dim_unit'] ? $_POST['shipping_default_pkg_dim_unit'] : SHIPPING_DEFAULT_PKG_DIM_UNIT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_10_04_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_residential', $sel_checked, $_POST['shipping_default_residential'] ? $_POST['shipping_default_residential'] : SHIPPING_DEFAULT_RESIDENTIAL, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_10_05_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_package_type', gen_build_pull_down($shipping_defaults['package_type']), $_POST['shipping_default_package_type'] ? $_POST['shipping_default_package_type'] : SHIPPING_DEFAULT_PACKAGE_TYPE, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_10_06_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_pickup_service', gen_build_pull_down($shipping_defaults['pickup_service']), $_POST['shipping_default_pickup_service'] ? $_POST['shipping_default_pickup_service'] : SHIPPING_DEFAULT_PICKUP_SERVICE, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_07_DESC; ?></td>
	    <td nowrap="nowrap"><?php echo html_input_field('shipping_default_length', $_POST['shipping_default_length'] ? $_POST['shipping_default_length'] : SHIPPING_DEFAULT_LENGTH, 'size="10" style="text-align:right"') . TEXT_LENGTH; ?></td>
	    <td nowrap="nowrap"><?php echo html_input_field('shipping_default_width',  $_POST['shipping_default_width']  ? $_POST['shipping_default_width']  : SHIPPING_DEFAULT_WIDTH,  'size="10" style="text-align:right"') . TEXT_WIDTH; ?></td>
	    <td nowrap="nowrap"><?php echo html_input_field('shipping_default_height', $_POST['shipping_default_height'] ? $_POST['shipping_default_height'] : SHIPPING_DEFAULT_HEIGHT, 'size="10" style="text-align:right"') . TEXT_HEIGHT; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_10_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_additional_handling_show', $sel_show, $_POST['shipping_default_additional_handling_show'] ? $_POST['shipping_default_additional_handling_show'] : SHIPPING_DEFAULT_ADDITIONAL_HANDLING_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_additional_handling_checked', $sel_checked, $_POST['shipping_default_additional_handling_checked'] ? $_POST['shipping_default_additional_handling_checked'] : SHIPPING_DEFAULT_ADDITIONAL_HANDLING_CHECKED, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_14_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_insurance_show', $sel_show, $_POST['shipping_default_insurance_show'] ? $_POST['shipping_default_insurance_show'] : SHIPPING_DEFAULT_INSURANCE_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_insurance_checked', $sel_checked, $_POST['shipping_default_insurance_checked'] ? $_POST['shipping_default_insurance_checked'] : SHIPPING_DEFAULT_INSURANCE_CHECKED, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_VALUE . ' ' . html_input_field('shipping_default_insurance_value', $_POST['shipping_default_insurance_value'] ? $_POST['shipping_default_insurance_value'] : SHIPPING_DEFAULT_INSURANCE_VALUE, 'size="10" style="text-align:right"'); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_20_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_split_large_shipments_show', $sel_show, $_POST['shipping_default_split_large_shipments_show'] ? $_POST['shipping_default_split_large_shipments_show'] : SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_split_large_shipments_checked', $sel_checked, $_POST['shipping_default_split_large_shipments_checked'] ? $_POST['shipping_default_split_large_shipments_checked'] : SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_CHECKED, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_VALUE . ' ' . html_input_field('shipping_default_split_large_shipments_value', $_POST['shipping_default_split_large_shipments_value'] ? $_POST['shipping_default_split_large_shipments_value'] : SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_VALUE, 'size="10" style="text-align:right"'); ?></td>
	  </tr>
	  <tr class="ui-widget-header"><th colspan="4"><?php echo TEXT_SHIPMENT_DEFAULTS; ?></th></tr>
	  <tr>
	    <td><?php echo CD_10_26_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_delivery_confirmation_show', $sel_show, $_POST['shipping_default_delivery_confirmation_show'] ? $_POST['shipping_default_delivery_confirmation_show'] : SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_delivery_confirmation_checked', $sel_checked, $_POST['shipping_default_delivery_confirmation_checked'] ? $_POST['shipping_default_delivery_confirmation_checked'] : SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_CHECKED, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_VALUE . ' ' . html_pull_down_menu('shipping_default_delivery_confirmation_type', gen_build_pull_down($shipping_defaults['delivery_confirmation']), $_POST['shipping_default_delivery_confirmation_type'] ? $_POST['shipping_default_delivery_confirmation_type'] : SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_TYPE, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_32_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_handling_charge_show', $sel_show, $_POST['shipping_default_handling_charge_show'] ? $_POST['shipping_default_handling_charge_show'] : SHIPPING_DEFAULT_HANDLING_CHARGE_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_handling_charge_checked', $sel_checked, $_POST['shipping_default_handling_charge_checked'] ? $_POST['shipping_default_handling_charge_checked'] : SHIPPING_DEFAULT_HANDLING_CHARGE_CHECKED, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_VALUE . ' ' . html_input_field('shipping_default_handling_charge_value', $_POST['shipping_default_handling_charge_value'] ? $_POST['shipping_default_handling_charge_value'] : SHIPPING_DEFAULT_HANDLING_CHARGE_VALUE, 'size="10" style="text-align:right"'); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_38_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_cod_show', $sel_show, $_POST['shipping_default_cod_show'] ? $_POST['shipping_default_cod_show'] : SHIPPING_DEFAULT_COD_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_cod_checked', $sel_checked, $_POST['shipping_default_cod_checked'] ? $_POST['shipping_default_cod_checked'] : SHIPPING_DEFAULT_COD_CHECKED, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_VALUE . ' ' . html_pull_down_menu('shipping_default_payment_type', gen_build_pull_down($shipping_defaults['cod_funds_code']), $_POST['shipping_default_payment_type'] ? $_POST['shipping_default_payment_type'] : SHIPPING_DEFAULT_PAYMENT_TYPE, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_44_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_saturday_pickup_show', $sel_show, $_POST['shipping_default_saturday_pickup_show'] ? $_POST['shipping_default_saturday_pickup_show'] : SHIPPING_DEFAULT_SATURDAY_PICKUP_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_saturday_pickup_checked', $sel_checked, $_POST['shipping_default_saturday_pickup_checked'] ? $_POST['shipping_default_saturday_pickup_checked'] : SHIPPING_DEFAULT_SATURDAY_PICKUP_CHECKED, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_48_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_saturday_delivery_show', $sel_show, $_POST['shipping_default_saturday_delivery_show'] ? $_POST['shipping_default_saturday_delivery_show'] : SHIPPING_DEFAULT_SATURDAY_DELIVERY_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_saturday_delivery_checked', $sel_checked, $_POST['shipping_default_saturday_delivery_checked'] ? $_POST['shipping_default_saturday_delivery_checked'] : SHIPPING_DEFAULT_SATURDAY_DELIVERY_CHECKED, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_52_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_hazardous_show', $sel_show, $_POST['shipping_default_hazardous_show'] ? $_POST['shipping_default_hazardous_show'] : SHIPPING_DEFAULT_HAZARDOUS_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_hazardous_checked', $sel_checked, $_POST['shipping_default_hazardous_checked'] ? $_POST['shipping_default_hazardous_checked'] : SHIPPING_DEFAULT_HAZARDOUS_MATERIAL_CHECKED, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_56_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_dry_ice_show', $sel_show, $_POST['shipping_default_dry_ice_show'] ? $_POST['shipping_default_dry_ice_show'] : SHIPPING_DEFAULT_DRY_ICE_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_dry_ice_checked', $sel_checked, $_POST['shipping_default_dry_ice_checked'] ? $_POST['shipping_default_dry_ice_checked'] : SHIPPING_DEFAULT_DRY_ICE_CHECKED, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_10_60_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('shipping_default_return_service_show', $sel_show, $_POST['shipping_default_return_service_show'] ? $_POST['shipping_default_return_service_show'] : SHIPPING_DEFAULT_RETURN_SERVICE_SHOW, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_DEFAULT . ' ' . html_pull_down_menu('shipping_default_return_service_checked', $sel_checked, $_POST['shipping_default_return_service_checked'] ? $_POST['shipping_default_return_service_checked'] : SHIPPING_DEFAULT_RETURN_SERVICE_CHECKED, ''); ?></td>
	    <td nowrap="nowrap"><?php echo TEXT_VALUE . ' ' . html_pull_down_menu('shipping_default_return_service', gen_build_pull_down($shipping_defaults['return_label']), $_POST['shipping_default_return_service'] ? $_POST['shipping_default_return_service'] : SHIPPING_DEFAULT_RETURN_SERVICE, ''); ?></td>
	  </tr>
  </tbody>
</table>
</div>
