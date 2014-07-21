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
//  Path: /modules/shipping/methods/ups/ups_qualify/data.php
//

$shipto = array();
$shipto[] = array( // first entry of five
	'ship_primary_name'   => 'John Smith',
	'ship_address1'       => '830 W ARROW HWY',
	'ship_address2'       => '',
	'ship_city_town'      => 'SAN DIMAS',
	'ship_state_province' => 'CA',
	'ship_postal_code'    => '91773',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '909-555-1212',
	'ship_email'          => 'john@home.com',
	'residential_address' => '1',
	'ship_method'         => 'GND',  // see UPS class for ship type codes, GND Ground, 1Dam Next Day, etc
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '01234',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '14',
			'width'    => '8',
			'height'   => '8',
			'weight'   => '10',
			'value'    => '1885.00', // at least one with a high value < $999
		),
	),
);

$shipto[] = array( // second entry of five
	'ship_primary_name'   => 'Jane Doe',
	'ship_address1'       => '10250 COTTONWOOD PARK DR',
	'ship_address2'       => '',
	'ship_city_town'      => 'ALBUQUERQUE',
	'ship_state_province' => 'NM',
	'ship_postal_code'    => '87114',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '505-555-1212',
	'ship_email'          => 'jane@google.com',
	'residential_address' => '0',
	'ship_method'         => '2Dpm',  // see UPS class for ship type codes, GND Ground, 1Dam Next Day, etc
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '01244234',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '8',
			'width'    => '6',
			'height'   => '6',
			'weight'   => '2',
			'value'    => '12.00',
		),
	),
);

$shipto[] = array( // third entry of five
	'ship_primary_name'   => 'Jim Saunders',
	'ship_address1'       => '645 FLATIRONS MARKETPLACE DR',
	'ship_address2'       => '',
	'ship_city_town'      => 'Broomfield',
	'ship_state_province' => 'CO',
	'ship_postal_code'    => '80021',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '303-555-1212',
	'ship_email'          => 'jim@gmail.com',
	'residential_address' => '0',
	'ship_method'         => '1Dam',  // see UPS class for ship type codes, GND Ground, 1Dam Next Day, etc
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '0185423234',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '16',
			'width'    => '12',
			'height'   => '8',
			'weight'   => '35',
			'value'    => '402.00',
		),
	),
);

$shipto[] = array( // fourth entry of five
	'ship_primary_name'   => 'All State Schools',
	'ship_address1'       => '4508-F GARTH RD STE F',
	'ship_address2'       => '',
	'ship_city_town'      => 'BAYTOWN',
	'ship_state_province' => 'TX',
	'ship_postal_code'    => '77521',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '281-555-1212',
	'ship_email'          => 'sales@yahoo.com',
	'residential_address' => '0',
	'ship_method'         => '3Dpm',  // see UPS class for ship type codes, GND Ground, 1Dam Next Day, etc
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '0122324534',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '20',
			'width'    => '8',
			'height'   => '8',
			'weight'   => '8',
			'value'    => '185.00',
		),
	),
);

$shipto[] = array( // fifth entry of five
	'ship_primary_name'   => 'My Home Netowrk',
	'ship_address1'       => '520 Linton Blvd # 103',
	'ship_address2'       => '',
	'ship_city_town'      => 'Delray Beach',
	'ship_state_province' => 'FL',
	'ship_postal_code'    => '33445',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '561-555-1212',
	'ship_email'          => 'admin@myhomenet.net',
	'residential_address' => '1',
	'ship_method'         => 'GND',  // see UPS class for ship type codes, GND Ground, 1Dam Next Day, etc
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '43456-98',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '10',
			'width'    => '10',
			'height'   => '10',
			'weight'   => '1',
			'value'    => '15.00',
		),
	),
);

$deleteID = array( // 5 cases required
	'1Z12345E0390817264',
	'1Z12345E0193075279',
	'1Z12345E0392508488',
	'1Z12345E1290420899',
);

?>