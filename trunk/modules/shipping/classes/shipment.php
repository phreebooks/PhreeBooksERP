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
//  Path: /modules/shipping/classes/shipment.php
//
namespace shipping\classes;
class shipment {
	var $additional_handling        = SHIPPING_DEFAULT_ADDITIONAL_HANDLING_CHECKED;
	var $cod                        = SHIPPING_DEFAULT_COD_CHECKED;
	var $cod_currency               = SHIPPING_DEFAULT_CURRENCY;
	var $cod_payment_type           = SHIPPING_DEFAULT_PAYMENT_TYPE;
	var $delivery_confirmation      = SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_CHECKED;
	var $delivery_confirmation_type = SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_TYPE;
	var $dry_ice                    = SHIPPING_DEFAULT_DRY_ICE_CHECKED;
	var $handling_charge            = SHIPPING_DEFAULT_HANDLING_CHARGE_CHECKED;
	var $handling_charge_currency   = SHIPPING_DEFAULT_CURRENCY;
	var $handling_charge_value      = SHIPPING_DEFAULT_HANDLING_CHARGE_VALUE;
	var $hazardous_material         = SHIPPING_DEFAULT_HAZARDOUS_MATERIAL_CHECKED;
	var $insurance                  = SHIPPING_DEFAULT_INSURANCE_CHECKED;
	var $insurance_currency         = SHIPPING_DEFAULT_CURRENCY;
	var $insurance_value            = SHIPPING_DEFAULT_INSURANCE_VALUE;
	var $pickup_service             = SHIPPING_DEFAULT_PICKUP_SERVICE;
	var $pkg_dimension_unit         = SHIPPING_DEFAULT_PKG_DIM_UNIT;
	var $pkg_height                 = SHIPPING_DEFAULT_HEIGHT;
	var $pkg_length                 = SHIPPING_DEFAULT_LENGTH;
	var $pkg_type                   = SHIPPING_DEFAULT_PACKAGE_TYPE;
	var $pkg_weight_unit            = SHIPPING_DEFAULT_WEIGHT_UNIT;
	var $pkg_width                  = SHIPPING_DEFAULT_WIDTH;
	var $residential_address        = SHIPPING_DEFAULT_RESIDENTIAL;
	var $return_service             = SHIPPING_DEFAULT_RETURN_SERVICE_CHECKED;
	var $return_service_value       = SHIPPING_DEFAULT_RETURN_SERVICE;
	var $saturday_pickup            = SHIPPING_DEFAULT_SATURDAY_PICKUP_CHECKED;
	var $saturday_delivery          = SHIPPING_DEFAULT_SATURDAY_DELIVERY_CHECKED;
	var $ship_city_town             = COMPANY_CITY_TOWN;
	var $ship_state_province        = COMPANY_ZONE;
	var $ship_postal_code           = COMPANY_POSTAL_CODE;
	var $ship_country_code          = COMPANY_COUNTRY;
	var $ship_to_city               = '';
	var $ship_to_state              = '';
	var $ship_to_postal_code        = '';
	var $ship_to_country_code       = COMPANY_COUNTRY;
	var $split_large_shipments      = SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_CHECKED;
	var $split_large_shipments_unit = SHIPPING_DEFAULT_WEIGHT_UNIT;
	var $split_large_shipments_value= SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_VALUE;

// class constructor
  function __construct() {
	$this->arrOutput     = array();
	$this->terminal_date = date('m/d/Y', time());
  }
  
  function split_shipment($pkg) {
	$package = array();
// TBD convert both to same units to compare
	if ($pkg->pkg_weight == 0) return false;
	$num_packages = (($pkg->split_large_shipments) ? ceil($pkg->pkg_weight / $pkg->split_large_shipments_value) : 1);
	for ($i=0; $i<$num_packages; $i++) {
	  $package[] = array(
		'PackageTypeCode'       => $pkg->pkg_type,
		'DimensionUnit'         => $pkg->pkg_dimension_unit,
		'Length'                => $pkg->pkg_length,
		'Width'                 => $pkg->pkg_width,
		'Height'                => $pkg->pkg_height,
		'WeightUnit'            => $pkg->pkg_weight_unit,
		'Weight'                => ceil($pkg->pkg_weight / $num_packages),
		'DeliveryConfirmation'  => (($pkg->delivery_confirmation) ? $pkg->delivery_confirmation_type : ''),
		'InsuranceCurrencyCode' => (($pkg->insurance) ? $pkg->insurance_currency : ''),
		'InsuranceValue'        => (($pkg->insurance) ? $pkg->insurance_value : ''),
		'AdditionalHandling'    => $pkg->additional_handling,
	  );
	}
	$this->num_packages = $num_packages;
	return $package;
  }
}
?>
