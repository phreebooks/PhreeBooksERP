<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2010 PhreeSoft, LLC                               |
// | http://www.PhreeSoft.com                                        |
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
//  Path: /admin/soap/language/dutch/language.php
//
define('ZENCART_PRODUCT_TAX_CLASS_ID',2); // sets the record id for the default sales tax to use
// General defines for SOAP interface
define('SOAP_NO_USER_PW','The username and password submitted cannot be found in the XML string.');
define('SOAP_USER_NOT_FOUND','De gebruikers naam is niet geldig.');
define('SOAP_PASSWORD_NOT_FOUND','Het wachtwoord is niet geldig.');
define('SOAP_UNEXPECTED_ERROR','Een onverwachte error code is geretourneerd door de server.');
define('SOAP_BAD_LANGUAGE_CODE','De taal ISO code is niet gevonden in de Zencart languages tabel. Gezocht naar code = ');
define('SOAP_BAD_PRODUCT_TYPE','Het product type is niet gevonden in de Zencart product_types tabel. Gezocht naar type_name %s voor artikel %s.');
define('SOAP_BAD_MANUFACTURER','De naam van de Fabricant is niet gevonden in de Zencart manufacturers tabel. Gezocht naar Fabricant %s voor artikel %s.');
define('SOAP_BAD_CATEGORY','De categorie naam is niet gevonden of is niet unique in de Zencart categories_description tabel. Gezocht naar categorie naam %s voor artikel %s.');
define('SOAP_BAD_CATEGORY_A','De categorie %s kan geen artikelen bevatten alleen sub menu\'s. Kies een van de sub items voor artikel %s');
define('SOAP_NO_SKU','Artikelnummer is niet gevonden. Een artikel nummer moet aanwezig zijn in de XML string!');
define('SOAP_BAD_ACTION','Ongeldige actie is aangevraagd.');
define('SOAP_OPEN_FAILED','Error bij het openen van het afbeeldingsbestand: ');
define('SOAP_ERROR_WRITING_IMAGE','Error bij het schrijven van het bestand naar de Zencart afbeeldings directory.');
define('SOAP_PU_POST_ERROR','There was an error updating the product in Zencart. Description - ');
define('SOAP_PRODUCT_UPLOAD_SUCCESS','Product SKU %s was uploaded successfully.');
define('SOAP_NO_ORDERS_TO_CONFIRM', 'No orders were uploaded to confirm.');
define('SOAP_CONFIRM_SUCCESS','Order Confirmation was completed successfully. The number of orders updated was: %s');
define('SOAP_NO_SKUS_UPLOADED','No skus were uploaded to syncronize.');
define('SOAP_SKUS_MISSING','The following skus are in Zencart but not flagged to be there by PhreeBooks: ');
define('SOAP_PRODUCTS_IN_SYNC','The product listings between PhreeBooks and ZenCart are in sync.');

?>