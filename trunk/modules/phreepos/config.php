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
//  Path: /modules/phreepos/config.php
//
// Release History
// 1.0 => 2011-04-15 - Initial Release
// 1.1 => rene added starting and closing line (admin main/js_include and language)
//        bugg fix added InventoryProp and processSkuProp to js_include, replaced ORD_TEXT_19_WINDOW_TITLE with TEXT_POINT_OF_SALE
// 3.3 => 2012-11 compleet rewrite
// 3.4 => 2012-12 added other transactions
// 3.5 => 2013-04 bug fix
// 3.6 => 2013-05 bug fix and added function to check if payments are set properly before page is loaded
// 3.7 => 2013-05 bug fix changed the js function refreshOrderClock because it was using the wrong row.
// 3.8 => 2013-07 added tax_id to till
// 3.9 => 2014-01 added config option to enable or disable direct printing.
// Module software version information
// Menu Sort Positions
//define('MENU_HEADING_PHREEPOS_ORDER', 40);
// Menu Security id's (refer to master doc to avoid security setting overlap)
define('SECURITY_ID_PHREEPOS',           38);
define('SECURITY_ID_POS_MGR',            39);
define('SECURITY_ID_POS_CLOSING',       113);
define('SECURITY_ID_CUSTOMER_DEPOSITS', 109);
define('SECURITY_ID_VENDOR_DEPOSITS',   110);
// New Database Tables
define('TABLE_PHREEPOS_TILLS',    			DB_PREFIX . 'phreepos_tills');
define('TABLE_PHREEPOS_OTHER_TRANSACTIONS',	DB_PREFIX . 'phreepos_other_trans');


?>