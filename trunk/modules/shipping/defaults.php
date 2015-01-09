<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |

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
//  Path: /modules/shipping/defaults.php
//

define('DEFAULT_MOD_DIR', DIR_FS_WORKING . 'methods/');
define('SHIPPING_DEFAULT_LABEL_DIR', DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/');
define('SHIPPING_DEFAULT_LABEL_WS',  DIR_WS_MY_FILES . $_SESSION['company'] . '/shipping/labels/');
define('SHIPPING_DEFAULT_LTL_CLASS','125');

// Set up choices for dropdown menus for general shipping methods, not all are used for each method
$shipping_defaults = array();
$shipping_defaults['service_levels'] = array( // order determines sequence in pull down
  'GND'    => TEXT_GROUND,
  'GDR'    => TEXT_GROUND_RESIDENTIAL,
  'GndFrt' => SHIPPING_GNDFRT,
  'EcoFrt' => SHIPPING_ECOFRT,
  '1DEam'  => SHIPPING_1DEAM,
  '1Dam'   => SHIPPING_1DAM,
  '1Dpm'   => SHIPPING_1DPM,
  '1DFrt'  => SHIPPING_1DFRT,
  '2Dam'   => SHIPPING_2DAM,
  '2Dpm'   => SHIPPING_2DPM,
  '2DFrt'  => SHIPPING_2DFRT,
  '3Dam'   => SHIPPING_3DAM,
  '3Dpm'   => SHIPPING_3DPM,
  '3DFrt'  => SHIPPING_3DFRT,
  'I2DEam' => TEXT_WORLDWIDE_EARLY_EXPRESS,
  'I2Dam'  => TEXT_WORLDWIDE_EXPRESS,
  'I3D'    => TEXT_WORLDWIDE_EXPEDITED,
  'IGND'   => SHIPPING_IGND,
);
// Pickup Type Code - conforms to UPS standards per the XML specification
$shipping_defaults['pickup_service'] = array(
  '01' => TEXT_DAILY_PICKUP,
  '03' => TEXT_CARRIER_CUSTOMER_COUNTER,
  '06' => TEXT_REQUEST_ONE_TIME_PICKUP,
  '07' => TEXT_ON_CALL_AIR,
  '11' => TEXT_SUGGESTED_RETAIL_RATES,
  '19' => TEXT_DROP_BOX_OR_CENTER,
  '20' => TEXT_AIR_SERVICE_CENTER,
);
// Weight Unit of Measure
// Value: char(3), Values "LBS" or "KGS"
$shipping_defaults['weight_unit'] = array(
  'LBS' => TEXT_POUND_SHORT,
  'KGS' => TEXT_KILOGRAMS_SHORT,
);
// Package Dimensions Unit of Measure
$shipping_defaults['dimension_unit'] = array(
  'IN' => TEXT_INCHES_SHORT,
  'CM' => TEXT_CENTIMETERS_SHORT,
);
// Package Type
$shipping_defaults['package_type'] = array(
  '01' => TEXT_ENVELOPE_OR_LETTER,
  '02' => TEXT_CUSTOMER_SUPPLIED,
  '03' => TEXT_CARRIER_TUBE,
  '04' => TEXT_CARRIER_PAK,
  '21' => TEXT_CARRIER_BOX,
  '24' => SHIPPING_25KG,
  '25' => SHIPPING_10KG,
);
// COD Funds Code
$shipping_defaults['cod_funds_code'] = array(
  '0' => TEXT_CASH,
  '1' => TEXT_CHECK,
  '2' => TEXT_CASHIERS_CHECK,
  '3' => TEXT_MONEY_ORDER,
  '4' => TEXT_ANY,
);
// Delivery Confirmation
// Package delivery confirmation only allowed for shipments with US origin/destination combination.
$shipping_defaults['delivery_confirmation'] = array(
//'0' => TEXT_NO_DELIVERY_CONFIRMATION,
  '1' => TEXT_NO_SIGNATURE_REQUIRED,
  '2' => TEXT_SIGNATURE_REQUIRED,
  '3' => TEXT_ADULT_SIGNATURE_REQUIRED,
);
// Return label services
$shipping_defaults['return_label'] = array(
  '0' => TEXT_CARRIER_RETURN_LABEL,
  '1' => TEXT_PRINT_LOCAL_RETURN_LABEL,
  '2' => TEXT_CARRIER_PRINTS_AND_MAILS_RETURN_LABEL,
);
// Billing options
$shipping_defaults['bill_options'] = array(
  '0' => TEXT_SENDER,
  '1' => TEXT_RECEIPIENT,
  '2' => TEXT_THIRD_PARTY,
  '3' => TEXT_COLLECT,
);
$ltl_classes = array(
  '0' => TEXT_SELECT,
  '050' => '50',
  '055' => '55',
  '060' => '60',
  '065' => '65',
  '070' => '70',
  '077' => '77.5',
  '085' => '85',
  '092' => '92.5',
  '100' => '100',
  '110' => '110',
  '125' => '125',
  '150' => '150',
  '175' => '175',
  '200' => '200',
  '250' => '250',
  '300' => '300',
);

?>