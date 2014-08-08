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
  'si' => INV_TYPES_SI,
  'sr' => INV_TYPES_SR,
  'ms' => INV_TYPES_MS,
  'mb' => INV_TYPES_MB,
  'ma' => INV_TYPES_AS,
  'sa' => INV_TYPES_SA,
  'ns' => INV_TYPES_NS,
  'lb' => INV_TYPES_LB,
  'sv' => INV_TYPES_SV,
  'sf' => INV_TYPES_SF,
  'ci' => INV_TYPES_CI,
  'ai' => INV_TYPES_AI,
  'ds' => INV_TYPES_DS,
);
// used for identifying inventory types in reports and forms that are not selectable by the user
$inventory_types_plus       = $inventory_types;
$inventory_types_plus['ia'] = INV_TYPES_IA;
$inventory_types_plus['mi'] = INV_TYPES_MI;

asort ($inventory_types);
asort ($inventory_types_plus);

$cost_methods = array(
  'f' => INV_TEXT_FIFO,	   // First-in, First-out
  'l' => INV_TEXT_LIFO,	   // Last-in, First-out
  'a' => INV_TEXT_AVERAGE, // Average Costing
); 

$price_mgr_sources = array(
  '0' => TEXT_NOT_USED,	// Do not remove this selection, leave as first entry
  '1' => TEXT_DIR_ENTRY,
  '2' => INV_ENTRY_INV_ITEM_COST,
  '3' => INV_ENTRY_FULL_PRICE,
// Price Level 1 needs to always be at the end (it is pulled from the first row to avoid a circular reference)
// The index can change but must be matched with the javascript to update the price source values.
  '4' => TEXT_PRICE_LVL_1,
);	
$price_mgr_adjustments = array(
  '0' => TEXT_NONE,
  '1' => TEXT_DEC_AMT,
  '2' => TEXT_DEC_PCNT,
  '3' => TEXT_INC_AMT,
  '4' => TEXT_INC_PCNT,
  '5' => TEXT_MARK_UP_BY_PERCENT, // Mark up by Percent
  '6' => TEXT_MARGIN, // Margin by Percent
  '7' => TEXT_TIERED_PRICING, // tiered pricing
);
$price_mgr_rounding = array(
  '0' => TEXT_NONE,
  '1' => TEXT_NEXT_WHOLE,
  '2' => TEXT_NEXT_FRACTION,
  '3' => TEXT_NEXT_INCREMENT,
);

?>