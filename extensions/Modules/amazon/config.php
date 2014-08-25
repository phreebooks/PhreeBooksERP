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
//  Path: /modules/amazon/config.php
//

define('MODULE_AMAZON_VERSION', '3.7');
// Menu Sort Positions
// Security id's
define('SECURITY_ID_AMAZON_INTERFACE',         202);
define('SECURITY_ID_AMAZON_PAYMENT_INTERFACE', 205);
// New Database Tables
// Menu Locations
$mainmenu["banking"]['submenu']["amazon_import"] = array(
  'order'	    => 80,
  'show_in_users_settings' => true,
  'params'      => '',
  'text'        => BOX_AMAZON_PAYMENT_MODULE, 
  'security_id' => SECURITY_ID_AMAZON_PAYMENT_INTERFACE, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=amazon&amp;page=amazon_payment', 'SSL'),
);
$mainmenu["customers"]['submenu']["amazon"] = array(
    'text'        => BOX_AMAZON_MODULE,
    'order'       => 96,
    'security_id' => SECURITY_ID_AMAZON_INTERFACE,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=amazon&amp;page=amazon', 'SSL'),
    'show_in_users_settings' => true,
    'params'	    => '',
);

?>