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
//  Path: /modules/shipping/defaults.php
//

define('DEFAULT_MOD_DIR', DIR_FS_WORKING . 'methods/');
define('SHIPPING_DEFAULT_LABEL_DIR', DIR_FS_MY_FILES . $_SESSION['company'] . '/shipping/labels/');
define('SHIPPING_DEFAULT_LABEL_WS',  DIR_WS_MY_FILES . $_SESSION['company'] . '/shipping/labels/');
define('SHIPPING_DEFAULT_LTL_CLASS','125');

// Set up choices for dropdown menus for general shipping methods, not all are used for each method
$shipping_defaults = array();
$shipping_defaults['service_levels'] = array( // order determines sequence in pull down
  'GND'    => SHIPPING_GND,
  'GDR'    => SHIPPING_GDR,
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
  'I2DEam' => SHIPPING_I2DEAM,
  'I2Dam'  => SHIPPING_I2DAM,
  'I3D'    => SHIPPING_I3D,
  'IGND'   => SHIPPING_IGND,
);
// Pickup Type Code - conforms to UPS standards per the XML specification
$shipping_defaults['pickup_service'] = array(
  '01' => SHIPPING_DAILY,
  '03' => SHIPPING_CARRIER,
  '06' => SHIPPING_ONE_TIME,
  '07' => SHIPPING_ON_CALL,
  '11' => SHIPPING_RETAIL,
  '19' => SHIPPING_DROP_BOX,
  '20' => SHIPPING_AIR_SRV,
);
// Weight Unit of Measure
// Value: char(3), Values "LBS" or "KGS"
$shipping_defaults['weight_unit'] = array(
  'LBS' => SHIPPING_TEXT_LBS,
  'KGS' => SHIPPING_TEXT_KGS,
);
// Package Dimensions Unit of Measure
$shipping_defaults['dimension_unit'] = array(
  'IN' => SHIPPING_TEXT_IN,
  'CM' => SHIPPING_TEXT_CM,
);
// Package Type
$shipping_defaults['package_type'] = array(
  '01' => SHIPPING_ENVENLOPE,
  '02' => SHIPPING_CUST_SUPP,
  '03' => SHIPPING_TUBE,
  '04' => SHIPPING_PAK,
  '21' => SHIPPING_BOX,
  '24' => SHIPPING_25KG,
  '25' => SHIPPING_10KG,
);
// COD Funds Code
$shipping_defaults['cod_funds_code'] = array(
  '0' => SHIPPING_CASH,
  '1' => SHIPPING_CHECK,
  '2' => SHIPPING_CASHIERS,
  '3' => SHIPPING_MO,
  '4' => SHIPPING_ANY,
);
// Delivery Confirmation
// Package delivery confirmation only allowed for shipments with US origin/destination combination.
$shipping_defaults['delivery_confirmation'] = array(
//'0' => SHIPPING_NO_CONF,
  '1' => SHIPPING_NO_SIG_RQD,
  '2' => SHIPPING_SIG_REQ,
  '3' => SHIPPING_ADULT_SIG,
);
// Return label services
$shipping_defaults['return_label'] = array(
  '0' => SHIPPING_RET_CARRIER,
  '1' => SHIPPING_RET_LOCAL,
  '2' => SHIPPING_RET_MAILS,
);
// Billing options
$shipping_defaults['bill_options'] = array(
  '0' => SHIPPING_SENDER,
  '1' => SHIPPING_RECEIPIENT,
  '2' => SHIPPING_THIRD_PARTY,
  '3' => SHIPPING_COLLECT,
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