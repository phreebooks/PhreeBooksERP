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
//  Path: /modules/inventory/defaults.php
//
define('INVENTORY_DIR_ATTACHMENTS',  DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/attachments/');
define('MAX_INVENTORY_SKU_LENGTH', 24); // database is currently set for a maximum of 24 characters
define('MAX_NUM_PRICE_LEVELS', 5);
// the inventory type indexes should not be changed or the inventory module won't work.
// system generated types (not to be displayed are: ai - assembly item, mi - master stock with attributes)
$inventory_types = array(
  'si' => TEXT_STOCK_ITEM,
  'sr' => TEXT_SERIALIZED_ITEM,
  'ms' => TEXT_MASTER_STOCK_ITEM,
  'mb' => TEXT_MASTER_STOCK_ASSEMBLY,
  'ma' => TEXT_ITEM_ASSEMBLY,
  'sa' => TEXT_SERIALIZED_ASSEMBLY,
  'ns' => TEXT_NON-STOCK_ITEM,
  'lb' => TEXT_LABOR,
  'sv' => TEXT_SERVICE,
  'sf' => TEXT_FLAT_RATE_SERVICE,
  'ci' => TEXT_CHARGE_ITEM,
  'ai' => TEXT_ACTIVITY,
  'ds' => TEXT_DESCRIPTION,
);
// used for identifying inventory types in reports and forms that are not selectable by the user
$inventory_types_plus       = $inventory_types;
$inventory_types_plus['ia'] = TEXT_ITEM_ASSEMBLY_PART;
$inventory_types_plus['mi'] = TEXT_MASTER_STOCK_SUB_ITEM;

asort ($inventory_types);
asort ($inventory_types_plus);

$cost_methods = array(
  'f' => TEXT_FIFO,	   // First-in, First-out
  'l' => TEXT_LIFO,	   // Last-in, First-out
  'a' => TEXT_AVERAGE, // Average Costing
);

$price_mgr_sources = array(
  '0' => TEXT_NOT_USED,	// Do not remove this selection, leave as first entry
  '1' => TEXT_DIR_ENTRY,
  '2' => TEXT_ITEM_COST,
  '3' => TEXT_FULL_PRICE,
// Price Level 1 needs to always be at the end (it is pulled from the first row to avoid a circular reference)
// The index can change but must be matched with the javascript to update the price source values.
  '4' => TEXT_PRICE_LVL_1,
);
$price_mgr_adjustments = array(
  '0' => TEXT_NONE,
  '1' => TEXT_DECREASE_BY_AMOUNT,
  '2' => TEXT_DECREASE_BY_PERCENT,
  '3' => TEXT_INCREASE_BY_AMOUNT,
  '4' => TEXT_INCREASE_BY_PERCENT,
  '5' => TEXT_MARK_UP_BY_PERCENT, // Mark up by Percent
  '6' => TEXT_MARGIN, // Margin by Percent
  '7' => TEXT_TIERED_PRICING, // tiered pricing
);
$price_mgr_rounding = array(
  '0' => TEXT_NONE,
  '1' => TEXT_NEXT_WHOLE,
  '2' => TEXT_CONSTANT_CENTS,
  '3' => TEXT_NEXT_INCREMENT,
);

?>