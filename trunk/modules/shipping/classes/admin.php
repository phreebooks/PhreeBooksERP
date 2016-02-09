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
//  Path: /modules/shipping/classes/admin.php
//
namespace shipping\classes;
require_once (DIR_FS_ADMIN . 'modules/shipping/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'shipping';
	public $description = MODULE_SHIPPING_DESCRIPTION;
	public $sort_order  = 5;
	public $version		= '3.6';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_SHIPPING);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.6,
		  'phreebooks' => 3.6,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'ADDRESS_BOOK_SHIP_CONTACT_REQ'                  => '0',
		  'ADDRESS_BOOK_SHIP_ADD1_REQ'                     => '1',
		  'ADDRESS_BOOK_SHIP_ADD2_REQ'                     => '0',
		  'ADDRESS_BOOK_SHIP_CITY_REQ'                     => '1',
		  'ADDRESS_BOOK_SHIP_STATE_REQ'                    => '1',
		  'ADDRESS_BOOK_SHIP_POSTAL_CODE_REQ'              => '1',
		  'SHIPPING_DEFAULT_WEIGHT_UNIT'                   => 'LBS',
		  'SHIPPING_DEFAULT_CURRENCY'                      => 'USD',
		  'SHIPPING_DEFAULT_PKG_DIM_UNIT'                  => 'IN',
		  'SHIPPING_DEFAULT_RESIDENTIAL'                   => '1',
		  'SHIPPING_DEFAULT_PACKAGE_TYPE'                  => '02',
		  'SHIPPING_DEFAULT_PICKUP_SERVICE'                => '01',
		  'SHIPPING_DEFAULT_LENGTH'                        => '8',
		  'SHIPPING_DEFAULT_WIDTH'                         => '6',
		  'SHIPPING_DEFAULT_HEIGHT'                        => '4',
		  'SHIPPING_DEFAULT_ADDITIONAL_HANDLING_SHOW'      => '1',
		  'SHIPPING_DEFAULT_ADDITIONAL_HANDLING_CHECKED'   => '0',
		  'SHIPPING_DEFAULT_INSURANCE_SHOW'                => '1',
		  'SHIPPING_DEFAULT_INSURANCE_CHECKED'             => '0',
		  'SHIPPING_DEFAULT_INSURANCE_VALUE'               => '100.00',
		  'SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_SHOW'    => '1',
		  'SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_CHECKED' => '1',
		  'SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_VALUE'   => '75',
		  'SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_SHOW'    => '1',
		  'SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_CHECKED' => '0',
		  'SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_TYPE'    => '2',
		  'SHIPPING_DEFAULT_HANDLING_CHARGE_SHOW'          => '1',
		  'SHIPPING_DEFAULT_HANDLING_CHARGE_CHECKED'       => '0',
		  'SHIPPING_DEFAULT_HANDLING_CHARGE_VALUE'         => '0.00',
		  'SHIPPING_DEFAULT_COD_SHOW'                      => '1',
		  'SHIPPING_DEFAULT_COD_CHECKED'                   => '0',
		  'SHIPPING_DEFAULT_PAYMENT_TYPE'                  => '1',
		  'SHIPPING_DEFAULT_SATURDAY_PICKUP_SHOW'          => '1',
		  'SHIPPING_DEFAULT_SATURDAY_PICKUP_CHECKED'       => '0',
		  'SHIPPING_DEFAULT_SATURDAY_DELIVERY_SHOW'        => '1',
		  'SHIPPING_DEFAULT_SATURDAY_DELIVERY_CHECKED'     => '0',
		  'SHIPPING_DEFAULT_HAZARDOUS_SHOW'                => '0',
		  'SHIPPING_DEFAULT_HAZARDOUS_CHECKED'             => '0',
		  'SHIPPING_DEFAULT_DRY_ICE_SHOW'                  => '0',
		  'SHIPPING_DEFAULT_DRY_ICE_CHECKED'               => '0',
		  'SHIPPING_DEFAULT_RETURN_SERVICE_SHOW'           => '1',
		  'SHIPPING_DEFAULT_RETURN_SERVICE_CHECKED'        => '0',
		  'SHIPPING_DEFAULT_RETURN_SERVICE'                => '2',
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'shipping',
		);
		// Load tables
		$this->tables = array(
		  TABLE_SHIPPING_LOG => "CREATE TABLE " . TABLE_SHIPPING_LOG . " (
			  id int(11) NOT NULL auto_increment,
			  shipment_id int(11) NOT NULL default '0',
			  ref_id varchar(16) NOT NULL default '0',
			  reconciled smallint(4) NOT NULL default '0',
			  carrier varchar(16) NOT NULL default '',
			  method varchar(8) NOT NULL default '',
			  ship_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  deliver_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  actual_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  deliver_late enum('0','1') NOT NULL default '0',
			  tracking_id varchar(32) NOT NULL default '',
			  cost float NOT NULL default '0',
			  notes varchar(255) NOT NULL default '',
			  PRIMARY KEY  (id),
			  KEY ref_id (ref_id)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	    );
		$this->mainmenu["tools"]->submenu ["shipping"]  = new \core\classes\menuItem (5, sprintf(TEXT_MANAGER_ARGS, TEXT_SHIPPING),	'module=shipping&amp;page=ship_mgr',   SECURITY_ID_SHIPPING_MANAGER, 'MODULE_SHIPPING_STATUS');
		$this->mainmenu["company"]->submenu ["configuration"]->submenu ["shipping"]  = new \core\classes\menuItem (sprintf(TEXT_MODULE_ARGS, TEXT_SHIPPING), sprintf(TEXT_MODULE_ARGS, TEXT_SHIPPING),	'module=shipping&amp;page=admin',   SECURITY_ID_CONFIGURATION, 'MODULE_SHIPPING_STATUS');
	    parent::__construct();
	}

	function install($path_my_files, $demo = false) {
	    global $admin;
	    parent::install($path_my_files, $demo);
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_shipment_num')) $admin->DataBase->query("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_shipment_num VARCHAR(16) NOT NULL DEFAULT '1'");
	}

	function upgrade(\core\classes\basis &$basis) {
	    parent::upgrade($basis);
	    if (version_compare($this->status, '3.2', '<') ) {
		  	if (!$basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_shipment_num')) $basis->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_shipment_num VARCHAR(16) NOT NULL DEFAULT '1'");
		  	if ($basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_shipment_desc')) $basis->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_shipment_desc");
		}
	}

	function delete($path_my_files) {
	    global $admin;
	    parent::delete($path_my_files);
	    if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_shipment_num'))  $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_shipment_num");
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_shipment_desc')) $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_shipment_desc");
	}

}
?>