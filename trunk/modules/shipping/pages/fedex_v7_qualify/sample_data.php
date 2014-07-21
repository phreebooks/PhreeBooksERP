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
//  Path: /modules/shipping/methods/fedex_v7/fedex_qualify/data.php
//

$shipto = array();
/*
$shipto[] = array( // Domestic Ground, COD, Prepaid
	'ship_primary_name'   => 'John Smith',
	'ship_address1'       => '456 Peach St',
	'ship_address2'       => '',
	'ship_city_town'      => 'Atlanta',
	'ship_state_province' => 'GA',
	'ship_postal_code'    => '30303',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '909-555-1212',
	'ship_email'          => 'john@home.com',
	'residential_address' => '0',
	'ship_method'         => 'GND',  // see FedEx class for ship type codes
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '01234',
	'bill_charges'        => '0',
	'cod'                 => '1',
	'total_amount'        => '200.00',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '14',
			'width'    => '8',
			'height'   => '8',
			'weight'   => '20',
			'value'    => '200.00',
		),
	),
);
*/
$shipto[] = array( // Domestic Ground, Prepaid
	'ship_primary_name'   => 'Jane Doe',
	'ship_address1'       => '20 FedEx Parkway',
	'ship_address2'       => '2nd Floor Suite 201',
	'ship_city_town'      => 'Kansas City',
	'ship_state_province' => 'MO',
	'ship_postal_code'    => '64112',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '505-555-1212',
	'ship_email'          => 'jane@google.com',
	'residential_address' => '0',
	'ship_method'         => 'GND',  // see FedEx class for ship type codes
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '01244234',
	'bill_charges'        => '0',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '8',
			'width'    => '6',
			'height'   => '6',
			'weight'   => '20',
			'value'    => '25.00',
		),
	),
);

$shipto[] = array( // Domestic Ground, 3rd party billing
	'ship_primary_name'   => 'Jim Saunders',
	'ship_address1'       => '321 Ground Rd',
	'ship_address2'       => '23 Fl. Room 301',
	'ship_city_town'      => 'New York',
	'ship_state_province' => 'NY',
	'ship_postal_code'    => '10042',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '303-555-1212',
	'ship_email'          => 'jim@gmail.com',
	'residential_address' => '0',
	'ship_method'         => 'GND',  // see FedEx class for ship type codes
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '0185423234',
	'bill_charges'        => '1',
	'bill_acct'           => '510087801',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '16',
			'width'    => '12',
			'height'   => '8',
			'weight'   => '20',
			'value'    => '15.00',
		),
	),
);

$shipto[] = array( // Home Delivery, Prepaid
	'ship_primary_name'   => 'Fred Flintstone',
	'ship_address1'       => '456 Rosewell',
	'ship_address2'       => '',
	'ship_city_town'      => 'Atlanta',
	'ship_state_province' => 'GA',
	'ship_postal_code'    => '30328',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '303-555-1212',
	'ship_email'          => 'fred@gmail.com',
	'residential_address' => '1',
	'ship_method'         => 'GND',  // see FedEx class for ship type codes
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '0185423234',
	'bill_charges'        => '0',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '5',
			'width'    => '5',
			'height'   => '108',
			'weight'   => '20',
			'value'    => '10.00',
		),
	),
);

$shipto[] = array( // Express, Priority Overnight, prepaid
	'ship_primary_name'   => 'All State Schools',
	'ship_address1'       => '123 Software Lane',
	'ship_address2'       => '',
	'ship_city_town'      => 'Atlanta',
	'ship_state_province' => 'GA',
	'ship_postal_code'    => '30303',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '281-555-1212',
	'ship_email'          => 'sales@yahoo.com',
	'residential_address' => '0',
	'ship_method'         => '1Dam',  // see FedEx class for ship type codes
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '0122324534',
	'bill_charges'        => '0',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '20',
			'width'    => '8',
			'height'   => '8',
			'weight'   => '10',
			'value'    => '185.00',
		),
	),
);

$shipto[] = array( // Economy 2 Day
	'ship_primary_name'   => 'My Home Network',
	'ship_address1'       => '6050 Rockwell Ave',
	'ship_address2'       => '',
	'ship_city_town'      => 'Anchorage',
	'ship_state_province' => 'AK',
	'ship_postal_code'    => '99501',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '561-555-1212',
	'ship_email'          => 'admin@myhomenet.net',
	'residential_address' => '0',
	'ship_method'         => '2Dpm',  // see FedEx class for ship type codes
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '43456-98',
	'bill_charges'        => '0',
	'package' => array(
		array(
			'pkg_type' => '01', // envelope
			'length'   => '12',
			'width'    => '12',
			'height'   => '12',
			'weight'   => '1',
			'value'    => '15.00',
		),
	),
);

$shipto[] = array( // Express Saver
	'ship_primary_name'   => 'In Stitches',
	'ship_address1'       => '36 Charles Lane',
	'ship_address2'       => '',
	'ship_city_town'      => 'Baltimore',
	'ship_state_province' => 'MD',
	'ship_postal_code'    => '21201',
	'ship_country_code'   => 'US',
	'ship_telephone1'     => '561-555-1212',
	'ship_email'          => 'steve@institches.net',
	'residential_address' => '1',
	'ship_method'         => '3Dpm',  // see FedEx class for ship type codes
	'pkg_dimension_unit'  => 'IN',
	'pkg_weight_unit'     => 'LBS',
	'purchase_invoice_id' => '43456-98',
	'bill_charges'        => '0',
	'package' => array(
		array(
			'pkg_type' => '02',
			'length'   => '10',
			'width'    => '10',
			'height'   => '15',
			'weight'   => '150',
			'value'    => '15.00',
		),
	),
);

?>