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
//  Path: /modules/inventory/language/en_us/language.php
//

define('INV_HEADING_NEW_ITEM', 'New Inventory Item');

define('TEXT_STOCK_ITEM','Stock Item');
define('TEXT_SERIALIZED_ITEM','Serialized Item');
define('INV_TYPES_MS','Master Stock Item');
define('INV_TYPES_MB','Master Stock Assembly');
define('INV_TYPES_AS','Item Assembly');
define('TEXT_SERIALIZED_ASSEMBLY','Serialized Assembly');
define('TEXT_NON-STOCK_ITEM','Non-stock Item');
define('INV_TYPES_LB','Labor');
define('INV_TYPES_SV','Service');
define('INV_TYPES_SF','Flat Rate - Service');
define('TEXT_CHARGE_ITEM','Charge Item');
define('INV_TYPES_AI','Activity Item');
define('INV_TYPES_DS','Description');
define('INV_TYPES_IA','Item Assembly Part');
define('INV_TYPES_MI','Master Stock Sub Item');

define('TEXT_FIFO','FIFO');
define('TEXT_LIFO','LIFO');
define('TEXT_AVERAGE','Average');
define('TEXT_GREATER_THAN','Larger than');
define('TEXT_DIR_ENTRY','Direct Entry');
define('TEXT_ITEM_COST','Item Cost');
define('TEXT_RETAIL_PRICE','Retail Price');
define('TEXT_PRICE_LVL_1','Price Level 1');
define('TEXT_DEC_AMT','Decrease by Amount');
define('TEXT_DEC_PCNT','Decrease by Percent');
define('TEXT_INC_AMT','Increase by Amount');
define('TEXT_INC_PCNT','Increase by Percent');
define('TEXT_NEXT_WHOLE','Next Dollar');
define('TEXT_NEXT_FRACTION','Constant Cents');
define('TEXT_NEXT_INCREMENT','Next Increment');
define('INV_XFER_SUCCESS','Successfully transfered %s pieces of sku %s');
define('TEXT_INV_MANAGED','Controlled Stock');
define('INV_DATE_ACCOUNT_CREATION', 'Creation date');
define('INV_DATE_LAST_UPDATE', 'Last Update');
define('INV_DATE_LAST_JOURNAL_DATE', 'Last Entry Date');
define('TEXT_SKU_HISTORY','SKU History');
define('INV_OPEN_PO','Open Purchase Orders');
define('INV_OPEN_SO','Open Sales Orders');
define('TEXT_PURCHASES_BY_MONTH','Purchases By Month');
define('TEXT_SALES_BY_MONTH','Sales By Month');
define('INV_NO_RESULTS','No Results Found');
define('INV_PO_NUMBER','PO Number');
define('INV_SO_NUMBER','SO Number');
define('TEXT_PO_DATE','PO Date');
define('TEXT_SO_DATE','SO Date');
define('INV_PO_RCV_DATE','Receive Date');
define('INV_SO_SHIP_DATE','Ship Date');
define('TEXT_REQUIRED_DATE','Required Date');
define('TEXT_PURCHASE_COST','Purchase Cost');
define('TEXT_SALES_INCOME','Sales Income');
define('INV_ENTRY_PURCH_TAX','Default Purchase Tax');
define('TEXT_LAST_MONTH','Last Month');
define('TEXT_3_MONTHS','3 Months');
define('TEXT_6_MONTHS','6 Months');
define('TEXT_12_MONTHS','12 Months');
define('TEXT_WHERE_USED','Where Used');
define('TEXT_CURRENT_COST','Current Assembly Cost');
define('JS_INV_TEXT_ASSY_COST','The current price to assemble this SKU is: ');
define('JS_INV_TEXT_USAGE','This SKU is used in the following assemblies: ');
define('JS_INV_TEXT_USAGE_NONE','This SKU is not used in any assemblies.');
define('TEXT_UPC_CODE','UPC Code');
define('TEXT_SKU_ACTIVITY','SKU Activity');
define('TEXT_SALES_DESCRIPTION','Sales Description');
define('INV_ASSY_HEADING_TITLE', 'Assemble/Disassemble Inventory');
define('TEXT_INVENTORY_REVALUATION', 'Inventory Re-valuation');
define('INV_POPUP_WINDOW_TITLE', 'Inventory Items');
define('TEXT_INVENTORY_ADJUSTMENTS','Inventory Adjustments');
define('TEXT_ADJUSTMENT_ACCOUNT','Adjustment Account');
define('INV_BULK_SKU_ENTRY_TITLE','Bulk SKU Pricing Entry');
define('TEXT_TRANSFER_INVENTORY_BETWEEN_STORES','Transfer Inventory Between Stores');

define('TEXT_QUANTITY_ON_HAND_SHORT', 'Qty on Hand');
define('TEXT_QUANTITY_ON_HAND', 'Quantity on Hand');
define('TEXT_SERIAL_NUMBER', 'Serial Number');
define('INV_HEADING_QTY_TO_ASSY', 'Qty to Assemble');
define('TEXT_QUANTITY_ON_ORDER_SHORT', 'Qty on Order');
define('TEXT_QUANTITY_IN_STOCK_SHORT', 'Qty in Stock');
define('TEXT_QTY_THIS_STORE','Qty this Branch');
define('INV_HEADING_QTY_ON_SO', 'Qty on Sales Order');
define('INV_HEADING_QTY_ON_ALLOC', 'Qty Allocated');
define('TEXT_QUANTITY_ON_SALES_ORDER', 'Quantity on Sales Order');
define('TEXT_QUANTITY_ON_ALLOCATION', 'Quantity on Allocation');
define('TEXT_PREFERRED_VENDOR', 'Preferred Vendor');
define('TEXT_LEAD_TIME_DAYS', 'Lead Time (days)');
define('TEXT_QUANTITY_ON_PURCHASE_ORDER', 'Quantity on Purchase Order');
define('INV_ASSY_PARTS_REQUIRED','Components required for this assembly');
define('TEXT_QTY_REMAINING','Qty Remaining');
define('INV_TEXT_UNIT_COST','Unit Cost');
define('TEXT_CURRENT_VALUE','Current Value');
define('INV_TEXT_NEW_VALUE','New Value');

define('TEXT_ADJUSTMENT_QUANTITY_SHORT','Adj Qty');
define('TEXT_REASON_FOR_ADJUSTMENT','Reason for Adjustment');
define('TEXT_ADJUSTMENT_VALUE_SHORT', 'Adj. Value');
define('TEXT_ROUNDING', 'Rounding');
define('INV_RND_VALUE', 'Rnd. Value');
define('TEXT_BILL_OF_MATERIALS','Bill of Materials');
define('INV_ADJ_DELETE_ALERT', 'Are you sure you want to delete this Inventory Adjustment?');
define('INV_MSG_DELETE_INV_ITEM', 'Are you sure you want to delete this inventory item?');

define('TEXT_TRANSFER_FROM_STORE_ID','Transfer From Store ID');
define('INV_XFER_TO_STORE','To Store ID');
define('TEXT_TRANSFER_QUANTITY','Transfer Quantity');
define('INV_XFER_ERROR_SAME_STORE_ID','The source and destination store ID\'s are the same, the transfer was not performed!');
define('INV_XFER_ERROR_NOT_ENOUGH_SKU','Transfer of inventory item %s was skipped, there is not enough in stock!');

define('INV_ENTER_SKU','Enter the SKU, item type and cost method then press Continue<br />Maximum SKU length is %s characters (%s for Master Stock)');
define('INV_MS_ATTRIBUTES','Master Stock Attributes');
define('TEXT_ATTRIBUTE_1','Attribute 1');
define('TEXT_ATTRIBUTE_2','Attribute 2');
define('INV_TEXT_ATTRIBUTES','Attributes');
define('INV_MS_CREATED_SKUS','The followng SKUs will be created');

define('INV_ENTRY_INVENTORY_TYPE', 'Inventory Type');
define('INV_ENTRY_INVENTORY_DESC_SHORT', 'Short Description');
define('TEXT_PURCHASE_DESCRIPTION', 'Purchase Description');
define('INV_ENTRY_IMAGE_PATH','Relative Image Path');
define('TEXT_SELECT_IMAGE','Select Image');
define('TEXT_SALES_INCOME_ACCOUNT', 'Sales/Income Account');
define('INV_ENTRY_ACCT_INV', 'Inventory/Wage Account');
define('INV_ENTRY_ACCT_COS', 'Cost of Sales Account');
define('INV_ENTRY_INV_ITEM_COST','Item Cost');
define('TEXT_FULL_PRICE', 'Full Price');
define('INV_ENTRY_FULL_PRICE_WT', 'Full Price with tax');
define('INV_MARGIN','Margin');
define('TEXT_ITEM_WEIGHT', 'Item Weight');
define('TEXT_MINIMUM_STOCK_LEVEL', 'Minimum Stock Level');
define('TEXT_REORDER_QUANTITY', 'Reorder Quantity');
define('INV_ENTRY_INVENTORY_COST_METHOD', 'Cost Method');
define('TEXT_SERIALIZE_ITEM', 'Serialize Item');
define('INV_MASTER_STOCK_ATTRIB_ID','ID (Max 2 Chars)');
define('TEXT_CUSTOMER_DETAILS','Customer Details');
define('TEXT_VENDOR_DETAILS','Vendor Details');
define('TEXT_ITEM_DETAILS','Item Details');
define('TEXT_ADJ_ITEMS','%s Items');
define('TEXT_MULTIPLE_ADJUSTMENTS','Multiple Adjustments');
define('TEXT_TRANSFERS','Transfers');
define('TEXT_FROM_BRANCH','From Branch');
define('TEXT_DEST_BRANCH','To Branch');
define('TEXT_TRANSFER_REASON','Reason for Transfer');
define('TEXT_TRANSFER_ACCT','Transfer Account');
define('TEXT_AVERAGE_USAGE','Average Usage (not including this month)');
define('TEXT_PACKAGE_QUANTITY','Package Quantity');
define('INV_MSG_DELETE_VENDOR_ROW','Are you sure you want to delete this vendor.');
define('INV_ERROR_CANNOT_DELETE','The inventory item cannot be deleted because there are journal entries in the system matching the sku');
define('INV_ERROR_BAD_SKU','There was an error with the item assembly list, please validate sku values and check quantities. Failing sku was: ');
define('INV_ERROR_SKU_INVALID','SKU is invalid. Please check the sku and default item inventory default gl accounts for missing information or errors.');
define('INV_ERROR_NEGATIVE_BALANCE','Error unbuilding inventory, not enough stock on hand to unbuild the requested quantity!');
define('INV_DESCRIPTION', 'Description: ');
define('TEXT_USE_DEFAULT_PRICE_SHEET','Use Default Price Sheet Settings');
define('INV_POST_SUCCESS','Succesfully Posted Inventory Adjustment Ref # ');
define('INV_POST_ASSEMBLY_SUCCESS','Successfully assembled SKU: ');
define('INV_NO_PRICE_SHEETS','No price sheets have been defined!');
define('ORD_INV_STOCK_LOW','Not enough stock on hand of this item.');
define('ORD_INV_STOCK_BAL','The number of units in stock is: ');
define('ORD_INV_OPEN_POS','The following open POs are in the system:');
define('ORD_INV_STOCK_STATUS','Store: %s PO: %s Qty: %s Due: %s');
define('ORD_JS_SKU_NOT_UNIQUE','No unique matches for row %s could be found. Either the SKU search field resulted in multiple matches or no matches were found.');
define('SRVCS_DUPLICATE_SHEET_NAME','The price sheet name already exists. Please enter a new sheet name.');
define('INV_ERROR_DELETE_HISTORY_EXISTS','Cannot delete this inventory item since there is a record in the inventory_history table.');
define('INV_ERROR_DELETE_ASSEMBLY_PART','Cannot delete this inventory item since it is part of an assembly.');
define('INV_ADJ_QTY_ZERO','Cannot adjust inventory with a zero quantity!');
define('INV_MS_ERROR_DELETE_HISTORY_EXISTS','Cannot delete sku %s since there is a record in the inventory_history table.');
define('INV_MS_ERROR_DELETE_ASSEMBLY_PART','Cannot delete sku %s since it is part of an assembly. Will mark as inactive.');
define('INV_MS_ERROR_CANNOT_DELETE','The sku %s cannot be deleted because there are matching journal entries. Will mark as inactive.');
// java script errors and messages
define('AJAX_INV_NO_INFO','Not enough information was passed to retrieve the item details');
define('JS_SKU_BLANK', '* The new item needs a SKU or UPC Code\n');
define('JS_COGS_AUTO_CALC','Note: For negative quantities, the unit price will be calculated by the system.');
define('JS_NO_SKU_ENTERED','A SKU value is required.\n');
define('JS_ASSY_VALUE_ZERO','A non-zero assembly quantity is required.\n');
define('JS_NOT_ENOUGH_PARTS','Not enough inventory to assemble the desired quantities');
define('JS_MS_INVALID_ENTRY','Both ID and Description are required fields. Please enter both values and press OK.');
define('JS_ERROR_NO_SHEET_NAME','The price sheet name cannot be empty.');
// audit log messages
define('INV_LOG_ADJ','Inventory Adjustment - ');
define('TEXT_INVENTORY_ASSEMBLY','Inventory Assembly');
define('INV_LOG_FIELDS','Inventory Fields - ');
define('INV_LOG_INVENTORY','Inventory Item - ');
define('INV_LOG_PRICE_MGR','Inventory Price Manager - ');
define('INV_LOG_TRANSFER','Inv Transfer from %s to %s');
define('PRICE_SHEETS_LOG','Price Sheet - ');
define('PRICE_SHEETS_LOG_BULK','Bulk Price Manager - ');
// Price sheets defines
define('PRICE_SHEET_NEW_TITLE','Create a New Price Sheet');
define('PRICE_SHEET_EDIT_TITLE','Edit Price Sheet - ');
define('TEXT_PRICE_SHEET_NAME','Price Sheet Name');
define('TEXT_USE_AS_DEFAULT','Use as Default');
define('TEXT_PRICE_SHEETS','Price Sheets');
define('TEXT_SALES_PRICE_SHEETS','Sales Price Sheets');
define('TEXT_SHEET_NAME','Sheet Name');
define('TEXT_REVISION','Rev. Level');
define('TEXT_EFFECTIVE_DATE','Effective Date');
define('TEXT_BULK_EDIT','Load Item Pricing');
define('TEXT_SPECIAL_PRICING','Special Pricing');
define('PRICE_SHEET_MSG_DELETE','Are you sure you want to delete this price sheet?');
define('PRICE_SHEET_DEFAULT_DELETED','The default price sheet as been deleted, please select a new price sheet!');
define('TEXT_AVERAGE_USE','Average use (excluding this month)');
define('TEXT_MS_HELP','When saving the %s written in one of the descriptions will be replaced by the description of that field.');
define('JS_MS_COMMA_NOT_ALLOWED','Comma is not allowed in the description.');
define('JS_MS_COLON_NOT_ALLOWED','Colon is not allowed in the description.');
define('INV_CALCULATING_ERROR', 'When Phreebooks has to calculate the full price with tax it will come to = ');
define('INV_WHAT_TO_CALCULATE','enter 1 to recalculate the margin \nenter 2 to recalculate the sales prices');
define('INV_CHEAPER_ELSEWHERE','sku %s is cheaper elsewhere.');
define('INV_IMAGE_DUPLICATE_NAME','The name of the image is already used in the database, Change the file name to continu. ');
?>