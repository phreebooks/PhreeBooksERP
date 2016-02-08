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
?>