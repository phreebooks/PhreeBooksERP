<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/zencart/language/en_us/language.php
//

// Headings
define('TEXT_ZENCART_CONFIGURATION','Zencart Configuration');
// General Defines
define('ZENCART_CONFIRM_MESSAGE','Your order shipped %s via %s %s, tracking number: %s');
define('ZENCART_BULK_UPLOAD_SUCCESS','Successfully uploaded %s item(s) to the ZenCart e-store.');
define('TEXT_ERROR','Error # ');
define('ZENCART_IVENTORY_UPLOAD','ZenCart Upload');
define('ZENCART_BULK_UPLOAD_TITLE','Bulk Upload');
define('ZENCART_BULK_UPLOAD_INFO','Bulk upload all products selected to be displayed in the ZenCart e-commerce site. Images are not included unless the checkbox is checked.');
define('TEXT_BULK_UPLOAD_PRODUCTS_TO_E-STORE','Bulk upload products to e-store');
define('TEXT_INCLUDE_IMAGES','Include Images');
define('ZENCART_BULK_UPLOAD_BTN','Bulk Upload');
define('TEXT_SYNCHRONIZE_PRODUCTS','Synchronize Products');
define('ZENCART_PRODUCT_SYNC_INFO','Synchronize active products from the PhreeBooks database (set to show in the catalog and active) with current listings from ZenCart. Any SKUs that should not be listed on Zencart are displayed. They need to be removed from ZenCart manually through the ZenCart admin interface.');
define('ZENCART_PRODUCT_SYNC_TEXT','Synchronize products with e-store');
define('ZENCART_DELETE_ZENCART','Also delete products in ZenCart that are not falgged to be there.');
define('TEXT_SYNCHRONIZE','Synchronize');
define('ZENCART_SHIP_CONFIRM_TITLE','Confirm Shipments');
define('ZENCART_SHIP_CONFIRM_INFO','Confirms all shipments on the date selected from the Shipping Manager and sets the status in ZenCart. Completed orders and partially shipped orders are updated. Email notifications to the customer are not sent.');
define('TEXT_SEND_SHIPMENT_CONFIRMATIONS','Send shipment confirmations');
define('TEXT_FOR_ORDERS_SHIPPED_ON','For orders shipped on');
define('ZENCART_SHIP_CONFIRM_BTN','Confirm Shipments');
// Error Messages
define('ZENCART_ERROR_NO_ITEMS','No inventory items were selected to upload to the ZenCart catalog. Looking for the checkbox field named catalog to identify items to be uploaded.');
define('ZENCART_ERROR_CONFRIM_NO_DATA','No records were found for this date to confirm with ZenCart.');
define('ZENCART_ERROR_NO_PRICE_SHEET','Couldn\'t find a default price level for price sheet: ');
define('ZENCART_INVALID_ACTION','Invalid action requested in ZenCart interface class. Aborting!');
define('ZENCART_INVALID_SKU','Error in inventory item id, could not find the record in the database');
// Javascrpt Defines
// Audit Log Messages
define('TEXT_UPLOAD_PRODUCT','Product Upload');
define('ZENCART_BULK_UPLOAD','ZenCart Bulk Upload');
define('TEXT_ZENCART_PRODUCT_SYNC','ZenCart Product Sync');
define('ZENCART_SHIP_CONFIRM','ZenCart Ship Confirmation');
define('ZENCART_INVENTORY_CATALOG_IS_EMPTY','The field webshop menu / catalog is empty. this field is required when you want to upload a product.');

define('ZENCART_CATALOG_URL','ZenCart - product URL.');
define('ZENCART_CATALOG_MODEL','ZenCart - product model.');

?>