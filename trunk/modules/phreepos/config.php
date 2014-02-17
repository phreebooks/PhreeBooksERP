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
//  Path: /modules/phreepos/config.php
//
// Release History
// 1.0 => 2011-04-15 - Initial Release
// 1.1 => rene added starting and closing line (admin main/js_include and language)
//        bugg fix added InventoryProp and processSkuProp to js_include, replaced ORD_TEXT_19_WINDOW_TITLE with MENU_HEADING_PHREEPOS
// 3.3 => 2012-11 compleet rewrite
// 3.4 => 2012-12 added other transactions
// 3.5 => 2013-04 bug fix    
// 3.6 => 2013-05 bug fix and added function to check if payments are set properly before page is loaded
// 3.7 => 2013-05 bug fix changed the js function refreshOrderClock because it was using the wrong row.
// 3.8 => 2013-07 added tax_id to till
// 3.9 => 2014-01 added config option to enable or disable direct printing.
// Module software version information
define('MODULE_PHREEPOS_VERSION', 3.9);
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
if (defined('MODULE_PHREEPOS_STATUS')) {
/*
  // Set the title menu
  $pb_headings[MENU_HEADING_PHREEPOS_ORDER] = array(
    'text' => MENU_HEADING_PHREEPOS, 
    'link' => html_href_link(FILENAME_DEFAULT, 'module=phreepos&amp;page=main&amp;mID=cat_pos', 'SSL'),
  );
*/
  // Set the menus
  $mainmenu["customers"]['submenu']["phreepos"] = array(
  	'order' 	  => 51,
  	'text'        => BOX_PHREEPOS, 
    'security_id' => SECURITY_ID_PHREEPOS,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreepos&amp;page=main', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
  $mainmenu["banking"]['submenu']['phreepos'] = array(
  	'order' 	  => 50,
  	'text'        => BOX_PHREEPOS, 
    'security_id' => '',
    'show_in_users_settings' => false,
    'params'      => '',
  );
  $mainmenu["banking"]['submenu']['phreepos']['submenu']["phreepos_mgr"] = array(
  	'order' 	  => 53,
  	'text'        => BOX_POS_MGR, 
    'security_id' => SECURITY_ID_POS_MGR, 
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreepos&amp;page=pos_mgr&amp;list=1', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
  $mainmenu["banking"]['submenu']['phreepos']['submenu']["phreepos_closing"] = array(
  	'order' 	  => 54,
  	'text'        => BOX_POS_CLOSING, 
    'security_id' => SECURITY_ID_POS_CLOSING, 
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreepos&amp;page=closing', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
  $mainmenu["banking"]['submenu']["receipts"]['submenu']['customer_deposit'] = array(
    'text'        => BOX_CUSTOMER_DEPOSITS,
    'order'       => 60,
    'security_id' => SECURITY_ID_CUSTOMER_DEPOSITS,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreepos&amp;page=deposit&amp;type=c', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
  $mainmenu["banking"]['submenu']["vendor_payments"]['submenu']['vendor_deposit'] = array(
    'text'        => BOX_VENDOR_DEPOSITS,
    'order'       => 60,
    'security_id' => SECURITY_ID_VENDOR_DEPOSITS,
    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreepos&amp;page=deposit&amp;type=v', 'SSL'),
    'show_in_users_settings' => true,
    'params'      => '',
  );
  if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0) {
	  gen_pull_language('phreepos', 'admin');
	  $mainmenu["company"]['submenu']["configuration"]['submenu']["phreepos"] = array(
		'order'	      => MODULE_PHREEPOS_TITLE,
		'text'        => MODULE_PHREEPOS_TITLE,
		'security_id' => SECURITY_ID_CONFIGURATION, 
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreepos&amp;page=admin', 'SSL'),
	    'show_in_users_settings' => false,
		'params'      => '',
	  );
	}  
}

?>