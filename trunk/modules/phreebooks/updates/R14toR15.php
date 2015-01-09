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
//  Path: /modules/phreebooks/updates/R14toR15.php
//

// This script updates Release 1.4 to Release 1.5

// *************************** IMPORTANT *********************************//

// This release resets the item_taxable flag in table inventory to the first tax_rate_id from table
// tax_rates. If the item_taxable flag is set to '0', it will change to null (the same net effect), but
// if the flag was set to '1', it will take the first tax_rate_id by id from the db. To override this 
// setting, uncomment the define below and set the value to the tax_rate_id prefered from table tax_rates.
// i.e. remove the '//' and change the number 1 to the tax_rate_id value from your db.

// define ('INVENTORY_DEFAULT_TAX_ID', 1);

//************************* END OF IMPORTANT *****************************//

// Release 1.4 to 1.5

// add field inactive to table users
$fields = mysql_list_fields(DB_DATABASE, TABLE_USERS);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('inactive', $field_array)) {
  $admin->DataBase->query("ALTER TABLE " . TABLE_USERS . " ADD inactive ENUM('0','1') NOT NULL DEFAULT '0' AFTER admin_name");
}

if (!defined('INVENTORY_AUTO_ADD')) {
  $admin->DataBase->query("ALTER TABLE " . TABLE_INVENTORY . " CHANGE `item_taxable` `item_taxable` INT( 11 ) NOT NULL DEFAULT '0'");
  $admin->DataBase->query("ALTER TABLE " . TABLE_JOURNAL_ITEM . " CHANGE `taxable` `taxable` INT( 11 ) NOT NULL DEFAULT '0'");
  $admin->DataBase->query("UPDATE " . TABLE_CONFIGURATION . " SET `set_function` = 'cfg_pull_down_tax_rate_list(' WHERE `configuration_key` = 'AP_ADD_SALES_TAX_TO_SHIPPING' LIMIT 1");
  $admin->DataBase->query("UPDATE " . TABLE_CONFIGURATION . " SET `set_function` = 'cfg_pull_down_tax_rate_list(' WHERE `configuration_key` = 'AR_ADD_SALES_TAX_TO_SHIPPING' LIMIT 1");
  if (!defined('INVENTORY_DEFAULT_TAX_ID')) {
    $result = $admin->DataBase->query("select tax_rate_id from " . TABLE_TAX_RATES . " order by tax_rate_id limit 1");
    define ('INVENTORY_DEFAULT_TAX_ID', $result->fields['tax_rate_id']);
  }
  $admin->DataBase->query("update " . TABLE_INVENTORY . " set item_taxable = '" . INVENTORY_DEFAULT_TAX_ID . "' where item_taxable = '2'");
  $admin->DataBase->query("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Enable Automatic Creation of Inventory Items', 'INVENTORY_AUTO_ADD', '0', 'Allows the automatic creation of inventory items in the order screens.<br /><br /> SKUs are not required in PhreeBooks for non-trackable inventory types. This feature allows for the automatic creation of SKUs in the inventory database table. The inventory type used will be stock for inventory type GL accounts and non-stock for all other type accounts. The GL account used to determine the type is the account selected with the line item in the order screen.', '5', '55', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Default Tax Rate For New Inventory Items', 'INVENTORY_DEFAULT_TAX', '" . INVENTORY_DEFAULT_TAX_ID . "', 'Determines the default tax rate to use when adding inventory items.<br /><br />NOTE: This value is applied to inventory Auto-Add but can be changed in the Inventory => Maintain screen. The tax rates are selected from the table tax_rates and must be setup through Setup => Sales tax Rates.', '5', '50', NULL , now(), NULL , 'cfg_pull_down_tax_rate_list(' );");

  require(DIR_FS_MODULES . 'orders/functions/phreebooks.php');
  // load the tax_rates id and rate
  $tax_rates = ord_calculate_tax_drop_down();
  // set non-taxable to no tax id (enum '0' turns to 1 when changing field type to integer)
  $admin->DataBase->query("update " . TABLE_JOURNAL_ITEM . " set taxable = 0 where taxable = 1");

  $result = $admin->DataBase->query("select id from " . TABLE_JOURNAL_MAIN . " where tax_auths = '0'");
  $clean_array = array();
  while (!$result->EOF) {
    $clean_array[] = $result->fields['id'];
	$result->MoveNext();
  }
  $admin->DataBase->query("update " . TABLE_JOURNAL_ITEM . " set taxable = 0 where ref_id in (" . implode(',', $clean_array) . ")");
  
  $result = $admin->DataBase->query("select distinct tax_auths from " . TABLE_JOURNAL_MAIN);
  while (!$result->EOF) {
    $rate_id = 0;
    foreach ($tax_rates as $key => $value) { // insert the rate id if it exists
	  if ($tax_rates[$key]['auths'] == $result->fields['tax_auths']) $rate_id = $tax_rates[$key]['id'];
	}
    $records = $admin->DataBase->query("select id from " . TABLE_JOURNAL_MAIN . " where tax_auths = '" . $result->fields['tax_auths'] . "'");
    $fill_array = array();
    while (!$records->EOF) {
      $fill_array[] = $records->fields['id'];
	  $records->MoveNext();
    }
	$search_ids = implode(',', $fill_array);
	$admin->DataBase->query("update " . TABLE_JOURNAL_ITEM . " set taxable = 0 where taxable = 1 and ref_id in (" . $search_ids . ")");
	$admin->DataBase->query("update " . TABLE_JOURNAL_ITEM . " set taxable = " . $rate_id . " where taxable = 2 and ref_id in (" . $search_ids . ")");
	$result->MoveNext();
  }
  // add field to reconcile journal accounts
  $admin->DataBase->query("ALTER TABLE " . TABLE_JOURNAL_ITEM . " ADD `reconciled` BOOL NOT NULL DEFAULT '0' AFTER `gl_type`");
  $admin->DataBase->query("ALTER TABLE " . TABLE_JOURNAL_ITEM . " ADD INDEX ( `reconciled` )");

}

if (!defined('ENABLE_ORDER_DISCOUNT')) {
  $admin->DataBase->query("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Enable percent/value discounts on order totals', 'ENABLE_ORDER_DISCOUNT', '0', 'This feature adds two additional fields to the order screens to enter an order level discount value or percent. If disabled, the fields will not be displayed on the order screens.', '1', '55', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Calculate Customer Sales Tax Before Discount Applied', 'AR_TAX_BEFORE_DISCOUNT', '1', 'If order level discounts are enabled, this switch determines whether the sales tax is calculated before or after the discount is applied to Sales Orders, Sales/Invoices, and Customer Quotes.', '2', '50', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'After Discount\', 1=>\'Before Discount\'),' ),
           ( 'Calculate Vendor Sales Tax Before Discount Applied', 'AP_TAX_BEFORE_DISCOUNT', '1', 'If order level discounts are enabled, this switch determines whether the sales tax is calculated before or after the discount is applied to Purchase Orders, Purchases, and Vendor Quotes.', '3', '50', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'After Discount\', 1=>\'Before Discount\'),' );");
}

if (!defined('SINGLE_LINE_ORDER_SCREEN')) {
  $admin->DataBase->query("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Use Single Line Item Order Screen', 'SINGLE_LINE_ORDER_SCREEN', '1', 'If set to true, this option uses a single line order screen without displayed fields for full price and discount. The single line screen uses GL account numbers versus allowing full GL account numbers/descriptions in two line mode.', '1', '75', NULL , now(), NULL , 'cfg_keyed_select_option(array(1=>\'Single Line Item Mode\', 0=>\'Two Line Item Mode\'),' );");
}

?>