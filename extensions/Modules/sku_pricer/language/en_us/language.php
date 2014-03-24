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
//  Path: /modules/sku_pricer/language/en_us/language.php
//

// The module was written without multi-language support. 
// If multi-language support is required, This file should hold the translations.
// Headings 
define('SKU_PRICER_PAGE_TITLE','SKU Price Importer');
// General Defines
define('SKU_PRICER_SELECT','Plese select the csv file to import');
define('SKU_PRICER_DIRECTIONS','After a file has been selected, press the Save icon to execute the script.<br />
		The file format (with header):<br />
		field1,field2,field2,field4,...<br>
		data1,data2,data3,data4, ...<br />
		Where field is the inventory field name, data is the db formatted value.<br />
		Most fields are importable unless they are controlled by the PhreeBooks module.<br />
		The file must contain either the SKU or UPC code to properly update the correct record.');
// Error Messages
// Javascrpt Defines
// Audit Log Messages

?>