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
//  Path: /modules/inventory/language/en_us/admin.php
//

// Module information
define('MODULE_INVENTORY_TITLE','Inventory Module');
define('MODULE_INVENTORY_DESCRIPTION','The inventory module contains all of the functionality to store and manipulated product and service items used in your company.<br/> This includes items for internal and external use as was well as products you sell.');
// Headings
define('BOX_INV_ADMIN','Inventory Administration');
define('INV_HEADING_FIELD_PROPERTIES', 'Field Type and Properties (Select One)');
// General Defines
define('TEXT_DEFAULT_GL_ACCOUNTS','Default GL Accounts');
define('TEXT_INVENTORY_TYPES','Inventory Type');
define('TEXT_SALES_ACCOUNT','Sales GL Account');
define('TEXT_INVENTORY_ACCOUNT','Inventory GL Account');
define('TEXT_COGS_ACCOUNT','Cost of Sales Account');
define('TEXT_COST_METHOD','Cost Method');
define('TEXT_STOCK_ITEMS','Stock');
define('TEXT_MS_ITEMS','Master Stock');
define('TEXT_ASSY_ITEMS','Assemblies');
define('TEXT_SERIAL_ITEMS','Serialized');
define('TEXT_NS_ITEMS','Non-Stock');
define('TEXT_SRV_ITEMS','Service');
define('TEXT_LABOR_ITEMS','Labor');
define('TEXT_ACT_ITEMS','Activity');
define('TEXT_CHARGE_ITEMS','Charge');
// install messages
define('MODULE_INVENTORY_NOTES_1','PRIORITY MEDIUM: Set default general ledger accounts for inventory types, after loading GL accounts (Company -> Inventory Module Properties -> Inventory tab)');
/************************** (Inventory Defaults) ***********************************************/
define('CD_05_50_DESC', 'Determines the default sales tax rate to use when adding inventory items. NOTE: This value is applied to inventory Auto-Add but can be changed in the Inventory => Maintain screen. The tax rates are selected from the table tax_rates and must be setup through Setup => Sales tax Rates.');
define('CD_05_52_DESC', 'Determines the default purchase tax rate to use when adding inventory items. NOTE: This value is applied to inventory Auto-Add but can be changed in the Inventory => Maintain screen. The tax rates are selected from the table tax_rates and must be setup through Setup => Purchase tax Rates.');
define('CD_05_55_DESC', 'Allows the automatic creation of inventory items in the order screens. SKUs are not required in PhreeBooks for non-trackable inventory types. This feature allows for the automatic creation of SKUs in the inventory database table. The inventory type used will be stock items. The GL accounts used will be the default accounts and costing method for stock items.');
define('CD_05_60_DESC', 'Allows an ajax call to fill in possible choices as data is entered into the SKU field. This feature is helpful when the SKUs are known and expedites filling in order forms. May slow down SKU entries when bar code scanners are used.');
define('CD_05_65_DESC', 'When enabled, PhreeBooks looks for a SKU length in the order form equal to the Bar Code Length value and when the length is reached, attempts to match with an inventory item. This allow fast entry of items when using bar code readers.');
define('CD_05_70_DESC', 'Sets the number of characters to expect when reading inventory bar code values. PhreeBooks only searches when the number of characters has been reached. Typical values are 12 and 13 characters.');
define('CD_05_75_DESC', 'When enabled, PhreeBooks will update the item cost in the inventory table with either the PO price or Purchase/Receive price. Usefule for on the fly PO/Purchases and updating prices from the order screen without having to update the inventory tables first.');

define('INV_TOOLS_VALIDATE_SO_PO','Validate Inventory Quantity on Order Values');
define('INV_TOOLS_VALIDATE_SO_PO_DESC','This operation tests to make sure your inventory quantity on Purchase Order and quantity of Sales Order match with the journal entries. The calculated values from the journal entries override the value in the inventory table.');
define('INV_TOOLS_REPAIR_SO_PO','Test and Repair Inventory Quantity on Order Values');
define('INV_TOOLS_BTN_SO_PO_FIX','Begin Test and Repair');
define('INV_TOOLS_PO_ERROR','SKU: %s had a quantity on Purchase Order of %s and should be %s. The inventory table balance was fixed.');
define('INV_TOOLS_SO_ERROR','SKU: %s had a quantity on Sales Order of %s and should be %s. The inventory table balance was fixed.');
define('INV_TOOLS_SO_PO_RESULT','Finished processing Inventory order quantities. The total number of items processed was %s. The number of records with errors was %s.');
define('INV_TOOLS_AUTDIT_LOG_SO_PO','Inv Tools - Repair SO/PO Qty (%s)');
define('INV_TOOLS_VALIDATE_INVENTORY','Validate Inventory Displayed Stock');
define('INV_TOOLS_VALIDATE_INV_DESC','This operation tests to make sure your inventory quantities listed in the inventory database and displayed in the inventory screens are the same as the quantities in the inventory history database as calculated by PhreeBooks when inventory movements occur. The only items tested are the ones that are tracked in the cost of goods sold calculation. Repairing inventory balances will correct the quantity in stock and leave the inventory history data alone. ');
define('INV_TOOLS_REPAIR_TEST','Test Inventory Balances with COGS History');
define('INV_TOOLS_REPAIR_FIX','Repair Inventory Balances with COGS History');
define('INV_TOOLS_REPAIR_CONFIRM','Are you sure you want to repair the inventory stock on hand to match the PhreeBooks COGS history calculated values?');
define('INV_TOOLS_BTN_TEST','Verify Stock Balances');
define('INV_TOOLS_BTN_REPAIR','Sync Qty in Stock');
define('INV_TOOLS_OUT_OF_BALANCE','SKU: %s -> stock indicates %s on hand but COGS history list %s available');
define('INV_TOOLS_IN_BALANCE','Your inventory balances are OK.');
define('INV_TOOLS_STOCK_ROUNDING_ERROR','SKU: %s -> Stock indicates %s on hand but is less than your precision. Please repair your inventory balances, the stock on hand will be rounded to %s.');
define('INV_TOOLS_BALANCE_CORRECTED','SKU: %s -> The inventory stock on hand has been changed to %s.');
define('NEXT_SKU_NUM_DESC','Next SKU');

?>