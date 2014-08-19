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
//  Path: /admin/soap/language/en_us/language.php
//

define('ZENCART_PRODUCT_TAX_CLASS_ID',2); // sets the record id for the default sales tax to use
// General defines for SOAP interface
define('SOAP_NO_USER_PW','The username and password submitted cannot be found in the XML string.');
define('TEXT_THE_USERNAME_SUBMITTED_IS_NOT_VALID','The username submitted is not valid.');
define('TEXT_THE_PASSWORD_SUBMITTED_IS_NOT_VALID','The password submitted is not valid.');
define('TEXT_UNEXPECTED_ERROR','An unexpected error code was returned by the processing server.');
define('SOAP_BAD_LANGUAGE_CODE','The language ISO code submitted could not be found in the Zencart languages table. Expecting to find code = ');
define('SOAP_BAD_PRODUCT_TYPE','The product type name could not be found in the Zencart product_types table. Expecting to find type_name %s for sku %s.');
define('SOAP_BAD_MANUFACTURER','The manufacturers name could not be found in the Zencart manufacturers table. Expecting to find manufacturer name %s for sku %s.');
define('SOAP_BAD_CATEGORY','The category name could not be found or is not unique in the Zencart categories_description table. Expecting to find category name %s for sku %s.');
define('SOAP_BAD_CATEGORY_A','The category name submitted is not at the lowest level in the category tree. This is a Zencart requirement! Category name %s for sku %s.');
define('SOAP_NO_SKU','No SKU was found. A SKU must be present in the submitted XML string!');
define('SOAP_BAD_ACTION','A bad action was submitted.');
define('SOAP_OPEN_FAILED','Error opening the image file to write. Trying to write to: ');
define('SOAP_ERROR_WRITING_IMAGE','Error writing the image file to the Zencart image directory.');
define('SOAP_PU_POST_ERROR','There was an error updating the product in Zencart. Description - ');
define('SOAP_PRODUCT_UPLOAD_SUCCESS','Product SKU %s was uploaded successfully.');
define('SOAP_NO_ORDERS_TO_CONFIRM', 'No orders were uploaded to confirm.');
define('SOAP_CONFIRM_SUCCESS','Order Confirmation was completed successfully. The number of orders updated was: %s');
define('SOAP_NO_SKUS_UPLOADED','No skus were uploaded to syncronize.');
define('SOAP_SKUS_MISSING','The following skus are in Zencart but not flagged to be there by PhreeBooks: ');
define('SOAP_PRODUCTS_IN_SYNC','The product listings between PhreeBooks and ZenCart are in sync.');

?>