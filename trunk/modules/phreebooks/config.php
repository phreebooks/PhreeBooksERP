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
//  Path: /modules/phreebooks/config.php
//
// Release History
// 3.0 => 2011-01-15 - Converted from stand-alone PhreeBooks release
// 3.1 => 2011-04-15 - Bug fixes, managers, code enhancement
// 3.2 => 2011-08-01 - Bug fixes
// 3.3 => 2011-11-15 - Bug fixes, themeroller changes
// 3.4 => 2012-02-15 - Bug fixes
// 3.5 => 2012-10-01 - bug fixes
// 3.6 => 2013-06-30 - bug fixes
// 3.7 => 2014-07-21 - bug fixes, average costing fix
// Module software version information
define('MODULE_PHREEBOOKS_VERSION',    3.7);
// Menu Security id's (refer to master doc to avoid security setting overlap)
define('SECURITY_ID_SEARCH',               4);
define('SECURITY_ID_GEN_ADMIN_TOOLS',     19);
define('SECURITY_ID_SALES_ORDER',         28);
define('SECURITY_ID_SALES_QUOTE',         29);
define('SECURITY_ID_SALES_INVOICE',       30);
define('SECURITY_ID_SALES_CREDIT',        31);
define('SECURITY_ID_SALES_STATUS',        32);
define('SECURITY_ID_POINT_OF_SALE',       33);
define('SECURITY_ID_INVOICE_MGR',         34);
define('SECURITY_ID_QUOTE_STATUS',        35);
define('SECURITY_ID_CUST_CREDIT_STATUS',  40);
define('SECURITY_ID_PURCHASE_ORDER',      53);
define('SECURITY_ID_PURCHASE_QUOTE',      54);
define('SECURITY_ID_PURCHASE_INVENTORY',  55);
define('SECURITY_ID_POINT_OF_PURCHASE',   56);
define('SECURITY_ID_PURCHASE_CREDIT',     57);
define('SECURITY_ID_PURCHASE_STATUS',     58);
define('SECURITY_ID_RFQ_STATUS',          59);
define('SECURITY_ID_VCM_STATUS',          60);
define('SECURITY_ID_PURCH_INV_STATUS',    61);
define('SECURITY_ID_SELECT_PAYMENT',     101);
define('SECURITY_ID_CUSTOMER_RECEIPTS',  102);
define('SECURITY_ID_PAY_BILLS',          103);
define('SECURITY_ID_ACCT_RECONCILIATION',104);
define('SECURITY_ID_ACCT_REGISTER',      105);
define('SECURITY_ID_VOID_CHECKS',        106);
define('SECURITY_ID_CUSTOMER_PAYMENTS',  107);
define('SECURITY_ID_VENDOR_RECEIPTS',    108);
define('SECURITY_ID_RECEIPTS_STATUS',    111);
define('SECURITY_ID_PAYMENTS_STATUS',    112);
define('SECURITY_ID_JOURNAL_ENTRY',      126);
define('SECURITY_ID_GL_BUDGET',          129);
define('SECURITY_ID_JOURNAL_STATUS',     130);
// New Database Tables
define('TABLE_ACCOUNTING_PERIODS',        DB_PREFIX . 'accounting_periods');
define('TABLE_ACCOUNTS_HISTORY',          DB_PREFIX . 'accounts_history');
define('TABLE_CHART_OF_ACCOUNTS',         DB_PREFIX . 'chart_of_accounts');
define('TABLE_CHART_OF_ACCOUNTS_HISTORY', DB_PREFIX . 'chart_of_accounts_history');
define('TABLE_JOURNAL_ITEM',              DB_PREFIX . 'journal_item');
define('TABLE_JOURNAL_MAIN',              DB_PREFIX . 'journal_main');
define('TABLE_TAX_AUTH',                  DB_PREFIX . 'tax_authorities');
define('TABLE_TAX_RATES',                 DB_PREFIX . 'tax_rates');
define('TABLE_RECONCILIATION',            DB_PREFIX . 'reconciliation');

if (defined('MODULE_PHREEBOOKS_STATUS')) {
	// banking customers
	$mainmenu["banking"]['submenu']["receipts"] = array(
  		'order'		  => 5,
	  	'text'        => MENU_HEADING_CUSTOMERS,
	  	'security_id' => SECURITY_ID_RECEIPTS_STATUS,
	  	'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=18&amp;list=1', 'SSL'),
  	  	'show_in_users_settings' => false,
	  	'params'      => '',
	);
	if(!isset($_SESSION['admin_security'][SECURITY_ID_RECEIPTS_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_RECEIPTS_STATUS] > 0) $mainmenu["banking"]['submenu']["receipts"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;jID=18&amp;type=c', 'SSL');
	$mainmenu["banking"]['submenu']["receipts"]['submenu']["new_receipts"] = array(
		'order'		  => 5,
	  	'text'        => ORD_TEXT_18_C_WINDOW_TITLE,
	  	'security_id' => SECURITY_ID_CUSTOMER_RECEIPTS,
	  	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;jID=18&amp;type=c', 'SSL'),
	  	'show_in_users_settings' => true,
	  	'params'      => '',
	);
	$mainmenu["banking"]['submenu']["receipts"]['submenu']["receipts_mgr"] = array(
	  	'order'		  => 10,
	  	'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_18_C_WINDOW_TITLE),
	  	'security_id' => SECURITY_ID_RECEIPTS_STATUS,
	  	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=18&amp;list=1', 'SSL'),
	  	'show_in_users_settings' => true,
	  	'params'      => '',
	);
	$mainmenu["banking"]['submenu']["receipts"]['submenu']["payments"] = array(
		'order'		  => 15,
		'text'        => ORD_TEXT_20_C_WINDOW_TITLE,
		'security_id' => SECURITY_ID_CUSTOMER_PAYMENTS,
		'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;jID=20&amp;type=c', 'SSL'),
		'show_in_users_settings' => true,
		'params'      => '',
	);
	
	// banking vendors
	$mainmenu["banking"]['submenu']["vendor_payments"] = array(
  		'order'		  => 25,
	    'text'        => MENU_HEADING_VENDORS,//ORD_TEXT_PAYMENTS_TITLE,
	    'security_id' => SECURITY_ID_PAYMENTS_STATUS,
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=20&amp;list=1', 'SSL'),
  	    'show_in_users_settings' => false,
	    'params'      => '',
	);
	if(!isset($_SESSION['admin_security'][SECURITY_ID_PAYMENTS_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_PAYMENTS_STATUS] > 0) $mainmenu["banking"]['submenu']["vendor_payments"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;jID=20&amp;type=v', 'SSL');
	$mainmenu["banking"]['submenu']["vendor_payments"]['submenu']["pay_bills"] = array(
	  	'order'		  => 15,
	    'text'        => ORD_TEXT_20_V_WINDOW_TITLE,
	    'security_id' => SECURITY_ID_PAY_BILLS,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;jID=20&amp;type=v', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["banking"]['submenu']["vendor_payments"]['submenu']["select_for_payment"] = array(
	  	'order'		  => 20,
	    'text'        => BOX_BANKING_SELECT_FOR_PAYMENT,
	    'security_id' => SECURITY_ID_SELECT_PAYMENT,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bulk_bills', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["banking"]['submenu']["vendor_payments"]['submenu']["pay_bills_mgr"] = array(
	    'order'		  => 25,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_20_V_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_PAYMENTS_STATUS,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=20&amp;list=1', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["banking"]['submenu']["vendor_payments"]['submenu']["receipts"] = array(
	  	'order'		  => 30,
	    'text'        => ORD_TEXT_18_V_WINDOW_TITLE,
	    'security_id' => SECURITY_ID_VENDOR_RECEIPTS,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=bills&amp;jID=18&amp;type=v', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);

	$mainmenu["banking"]['submenu']["register"] = array(
	    'order'		  => 60,
	    'text'        => BOX_BANKING_BANK_ACCOUNT_REGISTER,
	    'security_id' => SECURITY_ID_ACCT_REGISTER,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=register', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["banking"]['submenu']["reconciliation"] = array(
	    'order'		  => 70,
	    'text'        => BOX_BANKING_ACCOUNT_RECONCILIATION,
	    'security_id' => SECURITY_ID_ACCT_RECONCILIATION,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=reconciliation', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
/*
	$mainmenu["banking"]['submenu'][] = array(
	    'order'		  => 40,
	    'text'        => BOX_BANKING_VOID_CHECKS,
	    'security_id' => SECURITY_ID_VOID_CHECKS,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
*/
	$mainmenu["customers"]['submenu']["quotes"] = array(
  		'order'		  => 20,
	  	'text'        => ORD_TEXT_9_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=9', 'SSL'),
  	    'show_in_users_settings' => false,
	    'params'      => '',
	);
	if(!isset($_SESSION['admin_security'][SECURITY_ID_QUOTE_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_QUOTE_STATUS] > 0)  $mainmenu["customers"]['submenu']["quotes"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=9&amp;list=1', 'SSL');
	$mainmenu["customers"]['submenu']["quotes"]['submenu']["new_quote"] = array(
	  	'order'		  => 20,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_9_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_SALES_QUOTE,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=9', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["customers"]['submenu']["quotes"]['submenu']["quote_mgr"] = array(
	  	'order'		  => 25,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_9_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_QUOTE_STATUS,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=9&amp;list=1', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["customers"]['submenu']["orders"] = array(
      	'order'		  => 30,
	  	'text'        => ORD_TEXT_10_WINDOW_TITLE, 
	  	'link'        =>'',//        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=10&amp;list=1', 'SSL'),
  	  	'show_in_users_settings' => false,
		'params'      => '',
	); 
    if(isset($_SESSION['admin_security'][SECURITY_ID_SALES_STATUS]) && $_SESSION['admin_security'][SECURITY_ID_SALES_STATUS] > 0)  $mainmenu["customers"]['submenu']["orders"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=10&amp;list=1', 'SSL');
  	$mainmenu["customers"]['submenu']["orders"]['submenu']["new_order"]= array(
  	    'order'		  => 30,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_10_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_SALES_ORDER, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=10', 'SSL'),
  	    'show_in_users_settings' => true,
	    'params'      => '',
  	  
	);
	$mainmenu["customers"]['submenu']["orders"]['submenu']["order_mgr"] = array(
		'order'		  => 35,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_10_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_SALES_STATUS, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=10&amp;list=1', 'SSL'),
	    'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["customers"]['submenu']["invoices"] = array(
  	  	'order'		  => 40,
	    'text'        => ORD_TEXT_12_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=12&amp;list=1', 'SSL'),
  		'show_in_users_settings' => false,
	    'params'      => '',
	); 
	if(!isset($_SESSION['admin_security'][SECURITY_ID_INVOICE_MGR]) || $_SESSION['admin_security'][SECURITY_ID_INVOICE_MGR] > 0) $mainmenu["customers"]['submenu']["invoices"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=12&amp;list=1', 'SSL');
	$mainmenu["customers"]['submenu']["invoices"]['submenu']["new_invoice"] = array(
	  	'order'		  => 40,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_12_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_SALES_INVOICE,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=12', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["customers"]['submenu']["invoices"]['submenu']["invoice_mgr"] = array(
	  	'order'		  => 50,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_12_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_INVOICE_MGR,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=12&amp;list=1', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["customers"]['submenu']["credits"] = array(
  	  	'order'		  => 55,
	    'text'        => ORD_TEXT_13_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=13&amp;list=1', 'SSL'),
  		'show_in_users_settings' => false,
	    'params'      => '',
	); 
	if(!isset($_SESSION['admin_security'][SECURITY_ID_CUST_CREDIT_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_CUST_CREDIT_STATUS] > 0) $mainmenu["customers"]['submenu']["credits"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=13&amp;list=1', 'SSL');
  	$mainmenu["customers"]['submenu']["credits"]['submenu']["new_credit"] = array(
  	  	'order'		  => 55,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_13_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_SALES_CREDIT, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=13', 'SSL'),
  		'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["customers"]['submenu']["credits"]['submenu']["credit_mgr"] = array(
	  	'order'		  => 65,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_13_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_CUST_CREDIT_STATUS, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=13&amp;list=1', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["vendors"]['submenu']["quotes"] = array(
  		'order'		  => 20,
	    'text'        => ORD_TEXT_3_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=3&amp;list=1', 'SSL'),
  		'show_in_users_settings' => false,
	    'params'      => '',
	);
	if(!isset($_SESSION['admin_security'][SECURITY_ID_RFQ_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_RFQ_STATUS] > 0) $mainmenu["vendors"]['submenu']["quotes"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=3&amp;list=1', 'SSL');
	$mainmenu["vendors"]['submenu']["quotes"]['submenu']["new_quote"] = array(
	  	'order'		  => 20,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_3_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_PURCHASE_QUOTE,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=3', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["vendors"]['submenu']["quotes"]['submenu']["quote_mgr"] = array(
	  	'order'		  => 25,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_3_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_RFQ_STATUS, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=3&amp;list=1', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["vendors"]['submenu']["orders"] = array(
  	  	'order'		  => 20,
    	'text'        => ORD_TEXT_4_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=4&amp;list=1', 'SSL'),
  		'show_in_users_settings' => false,
	    'params'      => '',
	); 
	if(!isset($_SESSION['admin_security'][SECURITY_ID_PURCHASE_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_PURCHASE_STATUS] > 0) $mainmenu["vendors"]['submenu']["orders"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=4&amp;list=1', 'SSL');
  	$mainmenu["vendors"]['submenu']["orders"]['submenu']["new_order"] = array(
  	    'order'		  => 30,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_4_WINDOW_TITLE),  
	    'security_id' => SECURITY_ID_PURCHASE_ORDER,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=4', 'SSL'),
  		'show_in_users_settings' => true,
	    'params'      => '',
	  );
	$mainmenu["vendors"]['submenu']["orders"]['submenu']["order_mgr"] = array(
		'order'		  => 35,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_4_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_PURCHASE_STATUS, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=4&amp;list=1', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["vendors"]['submenu']["invoices"]= array(
  	  	'order'		  => 40,
    	'text'        => ORD_TEXT_6_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=6&amp;list=1', 'SSL'),
  		'show_in_users_settings' => false,
	    'params'      => '',
	);
	if(!isset($_SESSION['admin_security'][SECURITY_ID_PURCH_INV_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_PURCH_INV_STATUS] > 0) $mainmenu["vendors"]['submenu']["invoices"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=6&amp;list=1', 'SSL');
	$mainmenu["vendors"]['submenu']["invoices"]['submenu']["new_invoice"] = array(
	  	'order'		  => 40,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_6_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_PURCHASE_INVENTORY, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=6', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["vendors"]['submenu']["invoices"]['submenu']["invoice_mgr"] = array(
	  	'order'		  => 45,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_6_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_PURCH_INV_STATUS,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=6&amp;list=1', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["vendors"]['submenu']["credits"] = array(
  	  	'order'		  => 50,
    	'text'        => ORD_TEXT_7_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=7&amp;list=1', 'SSL'),
  		'show_in_users_settings' => false,
	    'params'      => '',
	);
	if(!isset($_SESSION['admin_security'][SECURITY_ID_PURCHASE_CREDIT]) || $_SESSION['admin_security'][SECURITY_ID_PURCHASE_CREDIT] > 0) $mainmenu["vendors"]['submenu']["credits"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=7&amp;list=1', 'SSL'); 
	$mainmenu["vendors"]['submenu']["credits"]['submenu']["new_credit"] = array(
	  	'order'		  => 50,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_7_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_PURCHASE_CREDIT,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=orders&amp;jID=7', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["vendors"]['submenu']["credits"]['submenu']["credit_mgr"] = array(
	  	'order'		  => 55,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_7_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_VCM_STATUS, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=7&amp;list=1', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["gl"]['submenu']["journals"] = array(
  	  	'order'		  => 5,
    	'text'        => ORD_TEXT_2_WINDOW_TITLE, 
	    'link'        => '',//html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=2&amp;list=1', 'SSL'),
  		'show_in_users_settings' => false,
	    'params'      => '',
	);
	if(!isset($_SESSION['admin_security'][SECURITY_ID_JOURNAL_STATUS]) || $_SESSION['admin_security'][SECURITY_ID_JOURNAL_STATUS] > 0) $mainmenu["gl"]['submenu']["journals"]['link'] = html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=2&amp;list=1', 'SSL'); 
	$mainmenu["gl"]['submenu']["journals"]['submenu']["new_journal"] = array(
	  	'order'		  => 5,
	    'text'        => sprintf(BOX_TEXT_NEW_TITLE, ORD_TEXT_2_WINDOW_TITLE), 
	    'security_id' => SECURITY_ID_JOURNAL_ENTRY, 
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=journal', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
	$mainmenu["gl"]['submenu']["journals"]['submenu']["journal_mgr"] = array(
	  	'order'		  => 10,
	    'text'        => sprintf(BOX_STATUS_MGR, ORD_TEXT_2_WINDOW_TITLE),
	    'security_id' => SECURITY_ID_JOURNAL_STATUS,
	    'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=status&amp;jID=2&amp;list=1', 'SSL'),
		'show_in_users_settings' => true,
	    'params'      => '',
	);
  	$mainmenu["gl"]['submenu']["search"] = array(
    	'order'		  => 15,
    	'text'        => TEXT_SEARCH,
    	'security_id' => SECURITY_ID_SEARCH, 
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=search&amp;journal_id=-1', 'SSL'),
  		'show_in_users_settings' => true,
    	'params'      => '',
  	);
  	$mainmenu["gl"]['submenu']["budget"] = array(
  		'order'		  => 50,
    	'text'        => BOX_GL_BUDGET, 
    	'security_id' => SECURITY_ID_GL_BUDGET, 
    	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=budget', 'SSL'),
  		'show_in_users_settings' => true,
    	'params'      => '',
  	);
	$mainmenu["gl"]['submenu']["admin_tools"] = array(
	 		'order'		  => 70,
	   	'text'        => BOX_HEADING_ADMIN_TOOLS,
	   	'security_id' => SECURITY_ID_GEN_ADMIN_TOOLS, 
	   	'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=admin_tools', 'SSL'),
		'show_in_users_settings' => true,
	   	'params'      => '',
	);
  	if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 0){
  		gen_pull_language('phreebooks', 'admin');
		$mainmenu["company"]['submenu']["configuration"]['submenu']["phreebooks"] = array(
	  		'order'	      => MODULE_PHREEBOOKS_TITLE,
	  		'text'        => MODULE_PHREEBOOKS_TITLE,
	  		'security_id' => SECURITY_ID_CONFIGURATION, 
	  		'link'        => html_href_link(FILENAME_DEFAULT, 'module=phreebooks&amp;page=admin', 'SSL'),
			'show_in_users_settings' => false,
	  		'params'      => '',
		);
  	}
}

?>