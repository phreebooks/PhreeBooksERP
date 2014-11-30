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
//  Path: /modules/phreebooks/language/en_us/admin.php
//

// Module information
define('MODULE_PHREEBOOKS_TITLE','PhreeBooks Module');
define('MODULE_PHREEBOOKS_DESCRIPTION','The phreebooks module provides double entry accounting. Functions include Purchase Orders, Sales Orders, Invoicing, Journal Entries and more. <b>NOTE: This is a core module and should not be removed!</b>');
// Headings
// Installation
define('MODULE_PHREEBOOKS_NOTES_1','PRIORITY MEDIUM: Enter company information (Company -> Module Administration -> My Company tab)');
define('MODULE_PHREEBOOKS_NOTES_2','PRIORITY LOW: Set up mail server settings (Company -> Module Administration -> Email tab)');
define('MODULE_PHREEBOOKS_NOTES_3','PRIORITY HIGH: Change or import chart of accounts from default settings (Company -> Module Administration -> PhreeBooks Module Properties -> Chart of Accounts tab)');
define('MODULE_PHREEBOOKS_NOTES_4','PRIORITY MEDIUM: Update default general ledger accounts for customer and vendors, after loading GL accounts (Company -> Module Administration -> PhreeBooks Module Properties -> Customers/Vendors tab)');
define('MODULE_PHREEBOOKS_NOTES_5','PRIORITY HIGH: Set up sales and puchase taxes, after loading GL accounts (Company -> Module Administration -> PhreeBooks Module Properties -> Taxes tabs)');
// General Defines
define('TEXT_DEFAULT_GL_ACCOUNTS','Default GL Accounts');
define('TEXT_PAYMENT_TERMS','Payment Terms');
define('TEXT_ACCOUNT_AGING','Account Aging');
define('TEXT_GENERAL_JOURNAL','General Journal Settings');
define('TEXT_NUMBER','Number');
define('TEXT_BOTH', 'Both');
define('TEXT_SINGLE_MODE','Single Line Entry');
define('TEXT_DOUBLE_MODE','Double Line Entry');
// PhreeForm processing Titles
define('PB_PF_JOURNAL_DESC','Journal Description');
define('PB_PF_ORDER_QTY','Quantity Ordered');
define('PB_PF_COA_TYPE_DESC','Chart of Account Type');
define('PB_PF_GET_AVG_COST','Inv Average Cost');
define('PB_PF_GET_AVG_TOTAL','Inv Average Total');
// Chart of Account Type definitions
define('COA_00_DESC','Cash');
define('COA_02_DESC','Accounts Receivable');
define('COA_04_DESC','Inventory');
define('COA_06_DESC','Other Current Assets');
define('COA_08_DESC','Fixed Assets');
define('COA_10_DESC','Accumulated Depreciation');
define('COA_12_DESC','Other Assets');
define('COA_20_DESC','Accounts Payable');
define('COA_22_DESC','Other Current Liabilities');
define('COA_24_DESC','Long Term Liabilities');
define('COA_30_DESC','Income');
define('COA_32_DESC','Cost of Sales');
define('COA_34_DESC','Expenses');
define('COA_40_DESC','Equity - Doesn\'t Close');
define('COA_42_DESC','Equity - Gets Closed');
define('COA_44_DESC','Equity - Retained Earnings');
// Form Group Definitions
define('PB_PF_BANK_CHECK','Bank Checks');
define('PB_PF_BANK_DEP_SLIP','Bank Deposit Slips');
define('PB_PF_COLLECT_LTR','Collection Letters');
define('PB_PF_CUST_CRD_MEMO','Credit Memos - Customer');
define('PB_PF_CUST_LABEL','Labels - Customer');
define('PB_PF_CUST_QUOTE','Customer Quotes');
define('PB_PF_CUST_STATEMENT','Customer Statements');
define('PB_PF_DEP_SLIP','Deposit Slips');
define('PB_PF_INV_PKG_SLIP','Invoices/Packing Slips');
define('PB_PF_PURCH_ORDER','Purchase Orders');
define('PB_PF_SALES_ORDER','Sales Orders');
define('PB_PF_SALES_REC','Sales Receipts');
define('PB_PF_VENDOR_CRD_MEMO','Credit Memos - Vendor');
define('PB_PF_VENDOR_LABEL','Labels - Vendor');
define('PB_PF_VENDOR_QUOTE','Vendor Quotes');
define('PB_PF_VENDOR_STATEMENT','Vendor Statements');
/************************** (PhreeBooks Utilities) ***********************************************/
define('GEN_ADM_TOOLS_AR','Customer/Receivables');
define('GEN_ADM_TOOLS_AP','Vendors/Payables');
define('GEN_ADM_TOOLS_RE_POST_FAILED','No journals were selected to re-post, no action was taken.');
define('GEN_ADM_TOOLS_RE_POST_SUCCESS','The selected journals were re-posted successfully. The number of records re-posted was: %s');
define('GEN_ADM_TOOLS_AUDIT_LOG_RE_POST','Re-post Journals: ');
define('GEN_ADM_TOOLS_REPOST_HEADING','Re-post Journal Entries');
define('GEN_ADM_TOOLS_REPOST_DESC','<b>BE SURE TO BACKUP YOUR DATA BEFORE YOU RE-POST ANY JOURNALS!</b><br />Note 1: Re-posting journals can take some time, you may want to limit the re-posts by entering a smaller date range or a limited number of journals.');
define('GEN_ADM_TOOLS_REPOST_CONFIRM','Are you sure you want to re-post the selected journals?\n\nYOU SHOULD BACKUP YOUR COMPANY BEFORE DOING THIS!');
define('GEN_ADM_TOOLS_BNK_ETC','Banking/Inventory/Other');
define('GEN_ADM_TOOLS_DATE_RANGE','Re-post Date Range');
define('GEN_ADM_TOOLS_START_DATE','Start Date');
define('GEN_ADM_TOOLS_END_DATE','End Date');
define('GEN_ADM_TOOLS_BTN_REPOST','Re-post Journals');
define('GEN_ADM_TOOLS_INVENTORY_OWED','Re-post Inventory Owed');
define('GEN_ADM_TOOLS_INVENTORY_DESC','Your inventory_cogs_owed table currently has %s entries in it. This tool will attempt to re-post all entries in the inventory_cogs_owed table. The inventory_cogs_owed table should not have any entries in it unless the stock levels are negative. If the table has items listed and the stock levels are zero or greater, this tool will attempt to fix the table by re-posting the pending journal entries. There have been issues in R3.6 and earlier that have caused this table to fill up waiting for inventory to show up and certain cases when partial stock is available. You can run a report to see a list of the items in your cogs_owed table.');
define('GEN_ADM_TOOLS_OWED_FIX','Re-post Inventory Owed');
define('GEN_ADM_TOOLS_BTN_OWED','Start Re-post');
define('GEN_ADM_TOOLS_OWED_CONFIRM','Are you sure you want to re-post the inventory owed?\n\nYOU SHOULD BACKUP YOUR COMPANY BEFORE DOING THIS!');

define('GEN_ADM_TOOLS_REPAIR_CHART_HISTORY','Validate and Repair General Ledger Account Balances');
define('GEN_ADM_TOOLS_REPAIR_CHART_DESC','This operation validates and repairs the chart of account balances. If the trial balance or balance sheet are not in balance, this is where to start. First validate the balances to see if there is an error and repair if necessary.');
define('GEN_ADM_TOOLS_REPAIR_TEST','Test Chart Balances');
define('GEN_ADM_TOOLS_REPAIR_FIX','Fix Chart Balance Errors');
define('GEN_ADM_TOOLS_BTN_TEST','Test GL Balances');
define('GEN_ADM_TOOLS_BTN_REPAIR','Repair GL Balance Errors');
define('GEN_ADM_TOOLS_REPAIR_CONFIRM','Are you sure you want to repair the general ledger balances?\n\nYOU SHOULD PRINT FINANCIAL SATEMENTS AND BACKUP YOUR COMPANY BEFORE DOING THIS!');
define('GEN_ADM_TOOLS_REPAIR_BALANCE_ERROR_MSG','The starting balance in period %s for account %s is: %s compaired with: %s');
define('GEN_ADM_TOOLS_REPAIR_ERROR_MSG','The total of adjustments in period %s for account %s values is: %s compaired with: %s. This can be fixed by reposting and than running the repaire balance error');
define('GEN_ADM_TOOLS_REPAIR_SUCCESS','Your chart of accounts are in balance.');
define('GEN_ADM_TOOLS_REPAIR_ERROR','You should repair the chart balance. NOTE: BACKUP BEFORE YOU REPAIR THE CHART OF ACCOUNTS BALANCES!');
define('GEN_ADM_TOOLS_REPAIR_COMPLETE','The chart balances have been repaired.');
define('GEN_ADM_TOOLS_REPAIR_LOG_ENTRY','Repaired GL balances');

define('GL_UTIL_HEADING_TITLE', 'General Journal Maintenance, Setup and Utilities');
define('GL_UTIL_PERIOD_LEGEND','Accounting Periods and Fiscal Years');
define('GL_UTIL_PURGE_ALL','Purge all Journal Transactions (re-start)');
define('GL_UTIL_FISCAL_YEAR_TEXT','Fiscal period calendar dates can be modified here. Please note that fiscal year dates cannot be changed for any period up to and including the last general journal entry in the system.');
define('GL_UTIL_PURGE_DB','Delete all Journal Entries (type \'purge\' in the text box and press purge button)<br />');
define('GL_UTIL_PURGE_DB_CONFIRM','Are you sure you want to clear all journal entries?');
define('GL_UTIL_PURGE_CONFIRM','Deleted all journal records and cleaned up databases.');
define('GL_UTIL_PURGE_FAIL','No journal entries were affected!');
define('GL_CURRENT_PERIOD','Current Accounting Period is: ');
define('GL_WARN_ADD_FISCAL_YEAR','Are you sure you want to add fiscal year: ');
define('GL_ERROR_FISCAL_YEAR_SEQ','The last period of the modified fiscal year does not align with the start date of the next fiscal year. The start date of the next fiscal year has been modified and should be reviewed.');
define('GL_WARN_CHANGE_ACCT_PERIOD','Enter the accounting period to make current:');
define('GL_ERROR_BAD_ACCT_PERIOD','The accounting period selected has not been setup. Either re-enter the period or add a fiscal year to continue.');
define('GL_ERROR_NO_BALANCE','Cannot update beginning balances because debits and credits do not match!');
define('GL_ERROR_UPDATE_COA_HISTORY','Error updating Chart of Accounts History after setting beginning balances!');
define('GL_BEG_BAL_ERROR_0',' found on line ');
define('GL_BEG_BAL_ERROR_1','Invalid chart of account id found on line ');
define('GL_BEG_BAL_ERROR_2','No invoice number found on line %d. Flagged as waiting for payment!');
define('GL_BEG_BAL_ERROR_3','Exiting import. No invoice number found on line ');
define('GL_BEG_BAL_ERROR_4','Exiting script. Bad date format found on line %d. Expecting format: ');
define('GL_BEG_BAL_ERROR_5','Skipping line. Zero total amount found on line ');
define('GL_BEG_BAL_ERROR_6','Invalid chart of account id found on line ');
define('GL_BEG_BAL_ERROR_7','Skipping inventory item. Zero quantity found on line ');
define('GL_BEG_BAL_ERROR_8','Failed updating sku %s, the process was terminated.');
define('GL_BEG_BAL_ERROR_9','Failed updating account %s, the process was terminated.');
define('GEN_ADM_TOOLS_POST_SEQ_SUCCESS','Successfully posted the current order number changes.');
define('GEN_ADM_TOOLS_AUDIT_LOG_SEQ','Current Order Status - Update');
define('GEN_ADM_TOOLS_TITLE','Administrative Tools and Utilities');

define('NEXT_AR_QUOTE_NUM_DESC','Next Customer Quote Number');
define('NEXT_AP_QUOTE_NUM_DESC','Next Vendor Quote Number');
define('NEXT_DEPOSIT_NUM_DESC','Next Deposit Number');
define('NEXT_SO_NUM_DESC','Next Sales Order Number');
define('NEXT_PO_NUM_DESC','Next Purchase Order Number');
define('NEXT_CHECK_NUM_DESC','Next Check Number');
define('NEXT_INV_NUM_DESC','Next Sales/Invoice Number');
define('NEXT_CM_NUM_DESC','Next Credit Memo Number');
define('NEXT_VCM_NUM_DESC','Next Vendor Credit Memo Number');
/************************** (General Defaults) ***********************************************/
define('CD_13_01_DESC', 'Automatically changes the current accounting period based on the server date and current fiscal calendar. If not enabled, the current accounting period must be manually changed in the General Ledger => Utilities menu.');
define('CD_13_05_DESC', 'Determines how to display the general ledger accounts in pull-down menus.<br />Number - GL Account Number Only.<br />Description - GL Account Description Only.<br />Both - Both gl number and name will be displayed.');
define('CD_01_50_DESC', 'This feature adds two additional fields to the order screens to enter an order level discount value or percent. If disabled, the fields will not be displayed on the order screens.');
define('CD_01_52_DESC', 'Enabling this feature will cause PhreeBooks to round calculated taxes by authority prior to adding up all applicable authorities. For tax rates with a single authority, this will only keep math precision errors from entering the journal. For multi-authority tax rates, this could cause too much or too little tax from being collected. If not sure, leave set to No.');
define('CD_01_55_DESC', 'If set to Yes, this option will enable data entry on order forms for USB and supported bar code readers.');
define('CD_01_75_DESC', 'If set to Yes, this option uses a single line order screen without displayed fields for full price and discount. The single line screen uses GL account numbers versus allowing full GL account numbers/descriptions in two line mode.');
define('ALLOW_NEGATIVE_INVENTORY_DESC','Allow sales of inventory items resulting in negative stock levels? PhreeBooks allows this and the journal entry resulting in inventory going negative is re-posted when inventory is received to accurately calculate costs.');
/************************** (Customer Defaults) ***********************************************/
define('CD_02_01_DESC', 'Default Accounts Receivables account. Typically an Accounts Receivable type account.');
define('CD_02_02_DESC', 'Default account to use for sales transactions. Typically an Income type account.');
define('CD_02_03_DESC', 'Default account to use for receipts to when customers pay invoices. Typically a Cash type account.');
define('CD_02_04_DESC', 'Default account to use for discounts to when customers pay on early schedule with a discount applied. Typically a Income type account.');
define('CD_02_05_DESC', 'Default account to use for freight charges. Typically an Income type account.');
define('CD_02_06_DESC', 'Default account to use for cash receipts on a customer deposits. Typically a Cash type account.');
define('CD_02_07_DESC', 'Default account to use for the credit holding for a customer deposits. Typically an Other Current Liabilities type account.');

define('CD_02_10_DESC', 'Early payment discounts. Leave percent zero or early days zero to disable early payment discounts.');
define('CD_02_11_DESC', 'Check customer credit limit when processing orders.');
define('CD_02_12_DESC', 'Default amount to use for customer credit limit. (%s)');
define('CD_02_13_DESC', 'Percent (%) discount if paid in');
define('CD_02_14_DESC', 'days. Total due in');
define('CD_02_15_DESC', 'days.');
define('APPLY_CUSTOMER_CREDIT_LIMIT_DESC','Whether to require admin aproval for orders over the customers credit limit.');

define('CD_02_16_DESC', 'Sets the start date for account aging.');
define('CD_02_17_DESC', 'Determines the number of days for the first warning of past due invoices. The period starts from the Account Aging Start Date Field.');
define('CD_02_18_DESC', 'Determines the number of days for the second warning of past due invoices. The period starts from the Account Aging Start Date Field.');
define('CD_02_19_DESC', 'Determines the number of days for the third warning of past due invoices. The period starts from the Account Aging Start Date Field.');
define('CD_02_20_DESC', 'Text heading used on reports to show aging for due date number 1.');
define('CD_02_21_DESC', 'Text heading used on reports to show aging for due date number 2.');
define('CD_02_22_DESC', 'Text heading used on reports to show aging for due date number 3.');
define('CD_02_23_DESC', 'Text heading used on reports to show aging for due date number 4.');

define('CD_02_24_DESC', 'Determines whether or not to calculate finance charges on past due invoices.');
define('CD_02_30_DESC', 'If enabled, shipping charges will be added to the calculation of sales tax. If not enabled, shipping will not be taxed.');
define('CD_02_35_DESC', 'If set to Yes, this option will automatically assign an ID to new customer/vendor when they are created.');
define('CD_02_40_DESC', 'This feature displays a customer status popup on the order screens when a customer is selected from the contact search popup. It displays balances, account aging as well as the active status of the account.');
define('CD_02_50_DESC', 'If order level discounts are enabled (in PhreeBooks Module > General), this switch determines whether the sales tax is calculated before, Yes, (or after, No) the discount is applied to Sales Orders, Sales/Invoices, and Customer Quotes. (Default is NO)');
/************************** (Vendor Defaults) ***********************************************/
define('CD_03_01_DESC', 'Default account to use for received items. This account can be over written through the individual item record. Typically an Inventory or Expense type account.');
define('CD_03_02_DESC', 'Default account to use for all purchases unless specified in the individual vendor record. Typically a Accounts Payable type account.');
define('CD_03_03_DESC', 'Default account to use for payments to when vendor invoices are paid. Typically a Cash type account.');
define('CD_03_04_DESC', 'Default account to use for freight charges for shipments from vendors. Typically an Expense type account.');
define('CD_03_05_DESC', 'Default account to use for purchase discounts paid with early discount payment terms.  Typically an Accounts Payable type account.');
define('CD_03_06_DESC', 'Default account to use for cash paid to vendors for deposits. Typically a Cash type account.');
define('CD_03_07_DESC', 'Default account to use for vendor deposits. Typically an Other Current Assets type account.');
define('CD_03_11_DESC', 'Default terms for payment');
define('CD_03_12_DESC', 'Default amount to use for vendor credit limit. (%s)');
define('CD_03_30_DESC', 'If enabled, shipping charges will be added to the calculation of sales tax. If not enabled, shipping will not be taxed.');
define('CD_03_35_DESC', 'If set to true, this option will automatically assign an ID to new vendors when they are created.');
define('CD_03_40_DESC', 'This feature displays a vendor status popup on the order screens when a vendor is selected from the contact search popup. It displays balances, account aging as well as the active status of the account.');
define('CD_03_50_DESC', 'If order level discounts are enabled, this switch determines whether the sales tax is calculated before or after the discount is applied to Purchase Orders, Purchases, and Vendor Quotes.');
/************************** (Chart of Accounts) ***********************************************/
define('GL_SELECT_STD_CHART','Select a standard chart: ');
define('GL_CHART_REPLACE','Replace current chart of accounts');
define('GL_CHART_IMPORT_DESC','or custom chart to import: ');
define('GL_CHART_DELETE_WARNING','NOTE: Current chart of accounts cannot be deleted if journal entries are present!');
define('GL_JOURNAL_NOT_EMTPY','The general journal is not empty, the current chart of accounts cannot be deleted!');
define('GL_ACCOUNT_DUPLICATE','The gl account: %s already exists!. The account will not be added.');
define('GL_INFO_HEADING_ONLY', 'This account is a heading and cannot accept posted values?');
define('GL_INFO_PRIMARY_ACCT_ID', 'If this account is a sub-account, select primary account:');
define('ERROR_ACCT_TYPE_REQ','The GL Account Type is required!');
define('GL_ERROR_CANT_MAKE_HEADING','This account has a balance. It cannot be converted to a header account.');
define('GL_POPUP_WINDOW_TITLE','Chart of Accounts');
define('GL_HEADING_ACCOUNT_NAME', 'Account ID');
define('GL_HEADING_SUBACCOUNT', 'Subaccount');
define('GL_EDIT_INTRO', 'Please make any necessary changes');
define('GL_INFO_ACCOUNT_TYPE', 'Account type (Required)');
define('GL_INFO_ACCOUNT_INACTIVE', 'Account inactive');
define('GL_INFO_INSERT_INTRO', 'Please enter the new GL account with its properties');
define('GL_INFO_NEW_ACCOUNT', 'New Account');
define('GL_INFO_EDIT_ACCOUNT', 'Edit Account');
define('GL_INFO_DELETE_INTRO', 'Are you sure you want to delete this account?\nAccounts cannot be deleted if there is a journal entry against the account.');
define('GL_DISPLAY_NUMBER_OF_COA', TEXT_DISPLAY_NUMBER . 'accounts');
define('GL_ERROR_CANT_DELETE','This account cannot be deleted because there are journal entries against it.');
define('GL_LOG_CHART_OF_ACCOUNTS','Chart of Accounts - ');
/************************** (Sales/Purchase Authorities) ***********************************************/
define('SETUP_TITLE_TAX_AUTHS_VEND', 'Purchase Tax Authorities');
define('SETUP_TITLE_TAX_AUTHS', 'Sales Tax Authorities');
define('SETUP_TAX_DESC_SHORT', 'Short Name');
define('SETUP_TAX_GL_ACCT', 'GL Account ID');
define('SETUP_TAX_RATE', 'Tax Rate (percent)');
define('SETUP_TAX_AUTH_EDIT_INTRO', 'Please make any necessary changes');
define('SETUP_INFO_DESC_SHORT', 'Short Name (15 chars max)');
define('SETUP_INFO_DESC_LONG', 'Long Description (64 chars max)');
define('SETUP_INFO_GL_ACCOUNT', 'GL Account to record tax:');
define('SETUP_INFO_VENDOR_ID', 'Vendor to submit funds to:');
define('SETUP_INFO_TAX_RATE', 'Tax rate (in percent)');
define('SETUP_TAX_AUTH_INSERT_INTRO', 'Please enter the new tax authority with its properties');
define('SETUP_TAX_AUTH_DELETE_INTRO', 'Are you sure you want to delete this tax authority?');
define('SETUP_TAX_AUTHS_DELETE_ERROR','Cannot delete this tax authority, it is being use in a journal entry.');
define('SETUP_INFO_HEADING_NEW_TAX_AUTH', 'New Tax Authority');
define('SETUP_INFO_HEADING_EDIT_TAX_AUTH', 'Edit Tax Authority');
define('SETUP_TAX_AUTHS_LOG','Tax Authorities - ');
define('SETUP_DISPLAY_NUMBER_OF_TAX_AUTH', TEXT_DISPLAY_NUMBER . 'tax authorities');
/************************** (Sales/Purchase Tax Rates) ***********************************************/
define('SETUP_TITLE_TAX_RATES', 'Sales Tax Rates');
define('SETUP_TITLE_TAX_RATES_VEND', 'Purchase Tax Rates');
define('SETUP_HEADING_TAX_FREIGHT', 'Tax Freight');
define('SETUP_HEADING_TOTAL_TAX', 'Total Tax (percent)');
define('SETUP_TAX_EDIT_INTRO', 'Please make any necessary changes');
define('SETUP_INFO_TAX_AUTHORITIES', 'Tax Authorities');
define('SETUP_INFO_TAX_AUTH_ADD', 'Select a tax authority to add');
define('SETUP_INFO_TAX_AUTH_DELETE', 'Select a tax authority to remove');
define('SETUP_INFO_FREIGHT_TAXABLE', 'Freight Taxable');
define('SETUP_TAX_INSERT_INTRO', 'Please enter the new tax rate with its properties');
define('SETUP_TAX_DELETE_INTRO', 'Are you sure you want to delete this tax rate?');
define('SETUP_HEADING_NEW_TAX_RATE', 'New Tax Rate');
define('SETUP_HEADING_EDIT_TAX_RATE', 'Edit Tax Rate');
define('SETUP_DISPLAY_NUMBER_OF_TAX_RATES', TEXT_DISPLAY_NUMBER . 'tax rates');
define('SETUP_TAX_RATES_LOG','Tax Rates - ');

?>