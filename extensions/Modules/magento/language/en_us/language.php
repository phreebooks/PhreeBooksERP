<?php
// +-----------------------------------------------------------------+
// |                    Phreedom Open Source ERP                     |
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
//  Path: /modules/magento/language/en_us/language.php
//

// Headings 
define('BOX_MAGENTO_ADMIN','Magento Configuration');
// General Defines
define('MAGENTO_CONFIRM_MESSAGE','Your order shipped %s via %s %s, tracking number: %s');
define('MAGENTO_BULK_UPLOAD_SUCCESS','Successfully uploaded %s item(s) to the Magento e-store.');
define('MAGENTO_TEXT_ERROR','Error # ');
define('MAGENTO_IVENTORY_UPLOAD','Magento Upload');
define('MAGENTO_BULK_UPLOAD_TITLE','Bulk Upload');
define('MAGENTO_BULK_UPLOAD_INFO','Bulk upload all products selected to be displayed in the Magento e-commerce site. Images are not included unless the checkbox is checked.');
define('MAGENTO_BULK_UPLOAD_TEXT','Bulk upload products to e-store');
define('MAGENTO_INCLUDE_IMAGES','Include Images');
define('MAGENTO_BULK_UPLOAD_BTN','Bulk Upload');
define('MAGENTO_PRODUCT_SYNC_TITLE','Synchronize Products');
define('MAGENTO_PRODUCT_SYNC_INFO','Synchronize active products from the Phreedom database (set to show in the catalog and active) with current listings from Magento. Any SKUs that should not be listed on Magento are displayed. They need to be removed from Magento manually through the Magento admin interface.');
define('MAGENTO_PRODUCT_SYNC_TEXT','Synchronize products with e-store');
define('MAGENTO_DELETE_MAGENTO','Also delete products in Magento that are not falgged to be there.');
define('MAGENTO_PRODUCT_SYNC_BTN','Synchronize');
define('MAGENTO_SHIP_CONFIRM_TITLE','Confirm Shipments');
define('MAGENTO_SHIP_CONFIRM_INFO','Confirms all shipments on the date selected from the Shipping Manager and sets the status in Magento. Completed orders and partially shipped orders are updated. Email notifications to the customer are not sent.');
define('MAGENTO_SHIP_CONFIRM_TEXT','Send shipment confirmations');
define('MAGENTO_TEXT_CONFIRM_ON','For orders shipped on');
define('MAGENTO_SHIP_CONFIRM_BTN','Confirm Shipments');
// Error Messages
define('MAGENTO_ERROR_NO_ITEMS','No inventory items were selected to upload to the Magento catalog. Looking for the checkbox field named catalog to identify items to be uploaded.');
define('MAGENTO_ERROR_CONFRIM_NO_DATA','No records were found for this date to confirm with Magento.');
define('MAGENTO_ERROR_NO_PRICE_SHEET','Couldn\'t find a default price level for price sheet: ');
define('MAGENTO_INVALID_ACTION','Invalid action requested in Magento interface class. Aborting!');
define('MAGENTO_INVALID_SKU','Error in inventory item id, could not find the record in the database');
// Javascrpt Defines
// Audit Log Messages
define('MAGENTO_UPLOAD_PRODUCT','Magento Product Upload');
define('MAGENTO_BULK_UPLOAD','Magento Bulk Upload');
define('MAGENTO_PRODUCT_SYNC','Magento Product Sync');
define('MAGENTO_SHIP_CONFIRM','Magento Ship Confirmation');
define('MAGENTO_INVENTORY_CATALOG_IS_EMPTY','The field webshop menu / catalog is empty. this field is required when you want to upload a product.');

define('MAGENTO_CATALOG_URL','Magento - product URL.');
define('MAGENTO_CATALOG_MODEL','Magento - product model.');

?>