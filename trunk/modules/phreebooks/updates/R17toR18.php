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
//  Path: /modules/phreebooks/updates/R17toR18.php
//

// This script updates Release 1.7 to Release 1.8, it is included as part of the updater script

// *************************** IMPORTANT *********************************//


//************************* END OF IMPORTANT *****************************//
// Release 1.7 to 1.8

// NEED TO INCORPORATE INTO FIRST CHANGE ***************
if (!defined('INVENTORY_AUTO_FILL')) {
  $db->execute("DROP TABLE IF EXISTS " . DB_PREFIX . "configuration_group");
  $db->execute("update " . TABLE_CONFIGURATION . " set set_function = 'cfg_keyed_select_option(array(0 =>\'" . TEXT_NO . "\', 1=>\'" . TEXT_YES . "\'),' where configuration_key = 'ENABLE_ENCRYPTION'");
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
           ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'CD_08_15_TITLE', 'PDF_APP', 'TCPDF', 'CD_08_15_DESC', '8', '15', NULL , now(), NULL , 'cfg_keyed_select_option(array(\'FPDF\' => \'FPDF\', \'TCPDF\' => \'TCPDF\'),' ),
           ( 'CD_05_60_TITLE', 'INVENTORY_AUTO_FILL', '0', 'CD_05_60_TITLE', '5', '60', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'" . TEXT_NO . "\', 1=>\'" . TEXT_YES . "\'),' );");
  // update the configuration file to handle translations
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_00_01_TITLE', configuration_description = 'CD_00_01_DESC' where configuration_key = 'CURRENT_ACCOUNTING_PERIOD'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_00_02_TITLE', configuration_description = 'CD_00_02_DESC' where configuration_key = 'CURRENT_ACCOUNTING_PERIOD_START'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_00_03_TITLE', configuration_description = 'CD_00_03_DESC' where configuration_key = 'CURRENT_ACCOUNTING_PERIOD_END'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_00_04_TITLE', configuration_description = 'CD_00_04_DESC' where configuration_key = 'MODULE_SHIPPING_INSTALLED'");
  /************************** Group ID 1 (My Company) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_01_TITLE', configuration_description = 'CD_01_01_DESC' where configuration_key = 'COMPANY_NAME'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_02_TITLE', configuration_description = 'CD_01_02_DESC' where configuration_key = 'AR_CONTACT_NAME'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_03_TITLE', configuration_description = 'CD_01_03_DESC' where configuration_key = 'AP_CONTACT_NAME'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_04_TITLE', configuration_description = 'CD_01_04_DESC' where configuration_key = 'COMPANY_ADDRESS1'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_05_TITLE', configuration_description = 'CD_01_05_DESC' where configuration_key = 'COMPANY_ADDRESS2'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_06_TITLE', configuration_description = 'CD_01_06_DESC' where configuration_key = 'COMPANY_CITY_TOWN'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_07_TITLE', configuration_description = 'CD_01_07_DESC' where configuration_key = 'COMPANY_ZONE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_08_TITLE', configuration_description = 'CD_01_08_DESC' where configuration_key = 'COMPANY_POSTAL_CODE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_09_TITLE', configuration_description = 'CD_01_09_DESC' where configuration_key = 'COMPANY_COUNTRY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_10_TITLE', configuration_description = 'CD_01_10_DESC' where configuration_key = 'COMPANY_TELEPHONE1'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_11_TITLE', configuration_description = 'CD_01_11_DESC' where configuration_key = 'COMPANY_TELEPHONE2'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_12_TITLE', configuration_description = 'CD_01_12_DESC' where configuration_key = 'COMPANY_FAX'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_13_TITLE', configuration_description = 'CD_01_13_DESC' where configuration_key = 'COMPANY_EMAIL'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_14_TITLE', configuration_description = 'CD_01_14_DESC' where configuration_key = 'COMPANY_WEBSITE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_15_TITLE', configuration_description = 'CD_01_15_DESC' where configuration_key = 'TAX_ID'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_16_TITLE', configuration_description = 'CD_01_16_DESC' where configuration_key = 'COMPANY_ID'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_18_TITLE', configuration_description = 'CD_01_18_DESC' where configuration_key = 'ENABLE_MULTI_BRANCH'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_19_TITLE', configuration_description = 'CD_01_19_DESC' where configuration_key = 'ENABLE_MULTI_CURRENCY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_20_TITLE', configuration_description = 'CD_01_20_DESC' where configuration_key = 'USE_DEFAULT_LANGUAGE_CURRENCY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_25_TITLE', configuration_description = 'CD_01_25_DESC' where configuration_key = 'ENABLE_SHIPPING_FUNCTIONS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_30_TITLE', configuration_description = 'CD_01_30_DESC' where configuration_key = 'ENABLE_ENCRYPTION'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_50_TITLE', configuration_description = 'CD_01_50_DESC' where configuration_key = 'ENABLE_ORDER_DISCOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_55_TITLE', configuration_description = 'CD_01_55_DESC' where configuration_key = 'ENABLE_BAR_CODE_READERS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_01_75_TITLE', configuration_description = 'CD_01_75_DESC' where configuration_key = 'SINGLE_LINE_ORDER_SCREEN'");
  /************************** Group ID 2 (Customer Defaults) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_01_TITLE', configuration_description = 'CD_02_01_DESC' where configuration_key = 'AR_DEFAULT_GL_ACCT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_02_TITLE', configuration_description = 'CD_02_02_DESC' where configuration_key = 'AR_DEF_GL_SALES_ACCT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_03_TITLE', configuration_description = 'CD_02_03_DESC' where configuration_key = 'AR_SALES_RECEIPTS_ACCOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_04_TITLE', configuration_description = 'CD_02_04_DESC' where configuration_key = 'AR_DISCOUNT_SALES_ACCOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_05_TITLE', configuration_description = 'CD_02_05_DESC' where configuration_key = 'AR_DEF_FREIGHT_ACCT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_10_TITLE', configuration_description = 'CD_02_10_DESC' where configuration_key = 'AR_PAYMENT_TERMS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_11_TITLE', configuration_description = 'CD_02_11_DESC' where configuration_key = 'AR_USE_CREDIT_LIMIT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_12_TITLE', configuration_description = 'CD_02_12_DESC' where configuration_key = 'AR_CREDIT_LIMIT_AMOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_13_TITLE', configuration_description = 'CD_02_13_DESC' where configuration_key = 'AR_NUM_DAYS_DUE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_14_TITLE', configuration_description = 'CD_02_14_DESC' where configuration_key = 'AR_PREPAYMENT_DISCOUNT_DAYS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_15_TITLE', configuration_description = 'CD_02_15_DESC' where configuration_key = 'AR_PREPAYMENT_DISCOUNT_PERCENT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_16_TITLE', configuration_description = 'CD_02_16_DESC' where configuration_key = 'AR_ACCOUNT_AGING_START'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_17_TITLE', configuration_description = 'CD_02_17_DESC' where configuration_key = 'AR_AGING_PERIOD_1'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_18_TITLE', configuration_description = 'CD_02_18_DESC' where configuration_key = 'AR_AGING_PERIOD_2'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_19_TITLE', configuration_description = 'CD_02_19_DESC' where configuration_key = 'AR_AGING_PERIOD_3'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_20_TITLE', configuration_description = 'CD_02_20_DESC' where configuration_key = 'AR_AGING_HEADING_1'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_21_TITLE', configuration_description = 'CD_02_21_DESC' where configuration_key = 'AR_AGING_HEADING_2'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_22_TITLE', configuration_description = 'CD_02_22_DESC' where configuration_key = 'AR_AGING_HEADING_3'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_23_TITLE', configuration_description = 'CD_02_23_DESC' where configuration_key = 'AR_AGING_HEADING_4'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_24_TITLE', configuration_description = 'CD_02_24_DESC' where configuration_key = 'AR_CALCULATE_FINANCE_CHARGE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_30_TITLE', configuration_description = 'CD_02_30_DESC' where configuration_key = 'AR_ADD_SALES_TAX_TO_SHIPPING'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_35_TITLE', configuration_description = 'CD_02_35_DESC' where configuration_key = 'AUTO_INC_CUST_ID'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_40_TITLE', configuration_description = 'CD_02_40_DESC' where configuration_key = 'AR_SHOW_CONTACT_STATUS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_02_50_TITLE', configuration_description = 'CD_02_50_DESC' where configuration_key = 'AR_TAX_BEFORE_DISCOUNT'");
  /************************** Group ID 3 (Vendor Defaults) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_01_TITLE', configuration_description = 'CD_03_01_DESC' where configuration_key = 'AP_DEFAULT_INVENTORY_ACCOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_02_TITLE', configuration_description = 'CD_03_02_DESC' where configuration_key = 'AP_DEFAULT_PURCHASE_ACCOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_03_TITLE', configuration_description = 'CD_03_03_DESC' where configuration_key = 'AP_PURCHASE_INVOICE_ACCOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_04_TITLE', configuration_description = 'CD_03_04_DESC' where configuration_key = 'AP_DEF_FREIGHT_ACCT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_05_TITLE', configuration_description = 'CD_03_05_DESC' where configuration_key = 'AP_DISCOUNT_PURCHASE_ACCOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_10_TITLE', configuration_description = 'CD_03_10_DESC' where configuration_key = 'AP_CREDIT_LIMIT_AMOUNT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_11_TITLE', configuration_description = 'CD_03_11_DESC' where configuration_key = 'AP_DEFAULT_TERMS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_12_TITLE', configuration_description = 'CD_03_12_DESC' where configuration_key = 'AP_NUM_DAYS_DUE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_13_TITLE', configuration_description = 'CD_03_13_DESC' where configuration_key = 'AP_PREPAYMENT_DISCOUNT_PERCENT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_14_TITLE', configuration_description = 'CD_03_14_DESC' where configuration_key = 'AP_PREPAYMENT_DISCOUNT_DAYS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_15_TITLE', configuration_description = 'CD_03_15_DESC' where configuration_key = 'AP_AGING_START_DATE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_16_TITLE', configuration_description = 'CD_03_16_DESC' where configuration_key = 'AP_AGING_DATE_1'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_17_TITLE', configuration_description = 'CD_03_17_DESC' where configuration_key = 'AP_AGING_DATE_2'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_18_TITLE', configuration_description = 'CD_03_18_DESC' where configuration_key = 'AP_AGING_DATE_3'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_19_TITLE', configuration_description = 'CD_03_19_DESC' where configuration_key = 'AP_AGING_HEADING_1'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_20_TITLE', configuration_description = 'CD_03_20_DESC' where configuration_key = 'AP_AGING_HEADING_2'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_21_TITLE', configuration_description = 'CD_03_21_DESC' where configuration_key = 'AP_AGING_HEADING_3'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_22_TITLE', configuration_description = 'CD_03_22_DESC' where configuration_key = 'AP_AGING_HEADING_4'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_30_TITLE', configuration_description = 'CD_03_30_DESC' where configuration_key = 'AP_ADD_SALES_TAX_TO_SHIPPING'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_35_TITLE', configuration_description = 'CD_03_35_DESC' where configuration_key = 'AUTO_INC_VEND_ID'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_40_TITLE', configuration_description = 'CD_03_40_DESC' where configuration_key = 'AP_SHOW_CONTACT_STATUS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_03_50_TITLE', configuration_description = 'CD_03_50_DESC' where configuration_key = 'AP_TAX_BEFORE_DISCOUNT'");
  /************************** Group ID 4 (Employee Defaults) ***********************************************/
  /************************** Group ID 5 (Inventory Defaults) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_01_TITLE', configuration_description = 'CD_05_01_DESC' where configuration_key = 'INV_STOCK_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_02_TITLE', configuration_description = 'CD_05_02_DESC' where configuration_key = 'INV_STOCK_DEFAULT_INVENTORY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_03_TITLE', configuration_description = 'CD_05_03_DESC' where configuration_key = 'INV_STOCK_DEFAULT_COS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_04_TITLE', configuration_description = 'CD_05_04_DESC' where configuration_key = 'INV_STOCK_DEFAULT_COSTING'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_05_TITLE', configuration_description = 'CD_05_05_DESC' where configuration_key = 'INV_MASTER_STOCK_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_06_TITLE', configuration_description = 'CD_05_06_DESC' where configuration_key = 'INV_MASTER_STOCK_DEFAULT_INVENTORY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_07_TITLE', configuration_description = 'CD_05_07_DESC' where configuration_key = 'INV_MASTER_STOCK_DEFAULT_COS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_08_TITLE', configuration_description = 'CD_05_08_DESC' where configuration_key = 'INV_MASTER_STOCK_DEFAULT_COSTING'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_11_TITLE', configuration_description = 'CD_05_11_DESC' where configuration_key = 'INV_ASSY_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_12_TITLE', configuration_description = 'CD_05_12_DESC' where configuration_key = 'INV_ASSY_DEFAULT_INVENTORY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_13_TITLE', configuration_description = 'CD_05_13_DESC' where configuration_key = 'INV_ASSY_DEFAULT_COS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_14_TITLE', configuration_description = 'CD_05_14_DESC' where configuration_key = 'INV_ASSY_DEFAULT_COSTING'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_16_TITLE', configuration_description = 'CD_05_16_DESC' where configuration_key = 'INV_SERIALIZE_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_17_TITLE', configuration_description = 'CD_05_17_DESC' where configuration_key = 'INV_SERIALIZE_DEFAULT_INVENTORY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_18_TITLE', configuration_description = 'CD_05_18_DESC' where configuration_key = 'INV_SERIALIZE_DEFAULT_COS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_19_TITLE', configuration_description = 'CD_05_19_DESC' where configuration_key = 'INV_SERIALIZE_DEFAULT_COSTING'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_21_TITLE', configuration_description = 'CD_05_21_DESC' where configuration_key = 'INV_NON_STOCK_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_22_TITLE', configuration_description = 'CD_05_22_DESC' where configuration_key = 'INV_NON_STOCK_DEFAULT_INVENTORY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_23_TITLE', configuration_description = 'CD_05_23_DESC' where configuration_key = 'INV_NON_STOCK_DEFAULT_COS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_31_TITLE', configuration_description = 'CD_05_31_DESC' where configuration_key = 'INV_SERVICE_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_32_TITLE', configuration_description = 'CD_05_32_DESC' where configuration_key = 'INV_SERVICE_DEFAULT_INVENTORY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_33_TITLE', configuration_description = 'CD_05_33_DESC' where configuration_key = 'INV_SERVICE_DEFAULT_COS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_36_TITLE', configuration_description = 'CD_05_36_DESC' where configuration_key = 'INV_LABOR_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_37_TITLE', configuration_description = 'CD_05_37_DESC' where configuration_key = 'INV_LABOR_DEFAULT_INVENTORY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_38_TITLE', configuration_description = 'CD_05_38_DESC' where configuration_key = 'INV_LABOR_DEFAULT_COS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_41_TITLE', configuration_description = 'CD_05_41_DESC' where configuration_key = 'INV_ACTIVITY_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_42_TITLE', configuration_description = 'CD_05_42_DESC' where configuration_key = 'INV_CHARGE_DEFAULT_SALES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_50_TITLE', configuration_description = 'CD_05_50_DESC' where configuration_key = 'INVENTORY_DEFAULT_TAX'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_55_TITLE', configuration_description = 'CD_05_55_DESC' where configuration_key = 'INVENTORY_AUTO_ADD'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_05_60_TITLE', configuration_description = 'CD_05_60_DESC' where configuration_key = 'INVENTORY_AUTO_FILL'");
  /************************** Group ID 6 (Special Cases (Payment, Shippping, Price Sheets) **************/
  /************************** Group ID 7 (User Account Defaults) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_07_17_TITLE', configuration_description = 'CD_07_17_DESC' where configuration_key = 'ENTRY_PASSWORD_MIN_LENGTH'");
  /************************** Group ID 8 (General Settings) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_08_01_TITLE', configuration_description = 'CD_08_01_DESC' where configuration_key = 'MAX_DISPLAY_SEARCH_RESULTS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_08_03_TITLE', configuration_description = 'CD_08_03_DESC' where configuration_key = 'CFG_AUTO_UPDATE_CHECK'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_08_05_TITLE', configuration_description = 'CD_08_05_DESC' where configuration_key = 'HIDE_SUCCESS_MESSAGES'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_08_07_TITLE', configuration_description = 'CD_08_07_DESC' where configuration_key = 'AUTO_UPDATE_CURRENCY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_08_10_TITLE', configuration_description = 'CD_08_10_DESC' where configuration_key = 'LIMIT_HISTORY_RESULTS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_08_15_TITLE', configuration_description = 'CD_08_15_DESC' where configuration_key = 'PDF_APP'");
  /************************** Group ID 9 (Import/Export Settings) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_09_01_TITLE', configuration_description = 'CD_09_01_DESC' where configuration_key = 'IE_RW_EXPORT_PREFERENCE'");
  /************************** Group ID 10 (Shipping Defaults) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_01_TITLE', configuration_description = 'CD_10_01_DESC' where configuration_key = 'SHIPPING_DEFAULT_WEIGHT_UNIT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_02_TITLE', configuration_description = 'CD_10_02_DESC' where configuration_key = 'SHIPPING_DEFAULT_CURRENCY'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_03_TITLE', configuration_description = 'CD_10_03_DESC' where configuration_key = 'SHIPPING_DEFAULT_PKG_DIM_UNIT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_04_TITLE', configuration_description = 'CD_10_04_DESC' where configuration_key = 'SHIPPING_DEFAULT_RESIDENTIAL'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_05_TITLE', configuration_description = 'CD_10_05_DESC' where configuration_key = 'SHIPPING_DEFAULT_PACKAGE_TYPE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_06_TITLE', configuration_description = 'CD_10_06_DESC' where configuration_key = 'SHIPPING_DEFAULT_PICKUP_SERVICE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_07_TITLE', configuration_description = 'CD_10_07_DESC' where configuration_key = 'SHIPPING_DEFAULT_LENGTH'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_08_TITLE', configuration_description = 'CD_10_08_DESC' where configuration_key = 'SHIPPING_DEFAULT_WIDTH'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_09_TITLE', configuration_description = 'CD_10_09_DESC' where configuration_key = 'SHIPPING_DEFAULT_HEIGHT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_10_TITLE', configuration_description = 'CD_10_10_DESC' where configuration_key = 'SHIPPING_DEFAULT_ADDITIONAL_HANDLING_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_12_TITLE', configuration_description = 'CD_10_12_DESC' where configuration_key = 'SHIPPING_DEFAULT_ADDITIONAL_HANDLING_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_14_TITLE', configuration_description = 'CD_10_14_DESC' where configuration_key = 'SHIPPING_DEFAULT_INSURANCE_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_16_TITLE', configuration_description = 'CD_10_16_DESC' where configuration_key = 'SHIPPING_DEFAULT_INSURANCE_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_18_TITLE', configuration_description = 'CD_10_18_DESC' where configuration_key = 'SHIPPING_DEFAULT_INSURANCE_VALUE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_20_TITLE', configuration_description = 'CD_10_20_DESC' where configuration_key = 'SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_22_TITLE', configuration_description = 'CD_10_22_DESC' where configuration_key = 'SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_24_TITLE', configuration_description = 'CD_10_24_DESC' where configuration_key = 'SHIPPING_DEFAULT_SPLIT_LARGE_SHIPMENTS_VALUE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_26_TITLE', configuration_description = 'CD_10_26_DESC' where configuration_key = 'SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_28_TITLE', configuration_description = 'CD_10_28_DESC' where configuration_key = 'SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_30_TITLE', configuration_description = 'CD_10_30_DESC' where configuration_key = 'SHIPPING_DEFAULT_DELIVERY_COMFIRMATION_TYPE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_32_TITLE', configuration_description = 'CD_10_32_DESC' where configuration_key = 'SHIPPING_DEFAULT_HANDLING_CHARGE_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_34_TITLE', configuration_description = 'CD_10_34_DESC' where configuration_key = 'SHIPPING_DEFAULT_HANDLING_CHARGE_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_36_TITLE', configuration_description = 'CD_10_36_DESC' where configuration_key = 'SHIPPING_DEFAULT_HANDLING_CHARGE_VALUE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_38_TITLE', configuration_description = 'CD_10_38_DESC' where configuration_key = 'SHIPPING_DEFAULT_COD_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_40_TITLE', configuration_description = 'CD_10_40_DESC' where configuration_key = 'SHIPPING_DEFAULT_COD_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_42_TITLE', configuration_description = 'CD_10_42_DESC' where configuration_key = 'SHIPPING_DEFAULT_PAYMENT_TYPE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_44_TITLE', configuration_description = 'CD_10_44_DESC' where configuration_key = 'SHIPPING_DEFAULT_SATURDAY_PICKUP_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_46_TITLE', configuration_description = 'CD_10_46_DESC' where configuration_key = 'SHIPPING_DEFAULT_SATURDAY_PICKUP_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_48_TITLE', configuration_description = 'CD_10_48_DESC' where configuration_key = 'SHIPPING_DEFAULT_SATURDAY_DELIVERY_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_50_TITLE', configuration_description = 'CD_10_50_DESC' where configuration_key = 'SHIPPING_DEFAULT_SATURDAY_DELIVERY_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_52_TITLE', configuration_description = 'CD_10_52_DESC' where configuration_key = 'SHIPPING_DEFAULT_HAZARDOUS_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_54_TITLE', configuration_description = 'CD_10_54_DESC' where configuration_key = 'SHIPPING_DEFAULT_HAZARDOUS_MATERIAL_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_56_TITLE', configuration_description = 'CD_10_56_DESC' where configuration_key = 'SHIPPING_DEFAULT_DRY_ICE_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_58_TITLE', configuration_description = 'CD_10_58_DESC' where configuration_key = 'SHIPPING_DEFAULT_DRY_ICE_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_60_TITLE', configuration_description = 'CD_10_60_DESC' where configuration_key = 'SHIPPING_DEFAULT_RETURN_SERVICE_SHOW'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_62_TITLE', configuration_description = 'CD_10_62_DESC' where configuration_key = 'SHIPPING_DEFAULT_RETURN_SERVICE_CHECKED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_10_64_TITLE', configuration_description = 'CD_10_64_DESC' where configuration_key = 'SHIPPING_DEFAULT_RETURN_SERVICE'");
  /************************** Group ID 11 (Address Book Defaults) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_02_TITLE', configuration_description = 'CD_11_02_DESC' where configuration_key = 'ADDRESS_BOOK_CONTACT_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_03_TITLE', configuration_description = 'CD_11_03_DESC' where configuration_key = 'ADDRESS_BOOK_ADDRESS1_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_04_TITLE', configuration_description = 'CD_11_04_DESC' where configuration_key = 'ADDRESS_BOOK_ADDRESS2_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_05_TITLE', configuration_description = 'CD_11_05_DESC' where configuration_key = 'ADDRESS_BOOK_CITY_TOWN_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_06_TITLE', configuration_description = 'CD_11_06_DESC' where configuration_key = 'ADDRESS_BOOK_STATE_PROVINCE_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_07_TITLE', configuration_description = 'CD_11_07_DESC' where configuration_key = 'ADDRESS_BOOK_POSTAL_CODE_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_08_TITLE', configuration_description = 'CD_11_08_DESC' where configuration_key = 'ADDRESS_BOOK_TELEPHONE1_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_09_TITLE', configuration_description = 'CD_11_09_DESC' where configuration_key = 'ADDRESS_BOOK_EMAIL_REQUIRED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_10_TITLE', configuration_description = 'CD_11_10_DESC' where configuration_key = 'ADDRESS_BOOK_SHIP_ADD1_REQ'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_11_TITLE', configuration_description = 'CD_11_11_DESC' where configuration_key = 'ADDRESS_BOOK_SHIP_ADD2_REQ'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_12_TITLE', configuration_description = 'CD_11_12_DESC' where configuration_key = 'ADDRESS_BOOK_SHIP_CONTACT_REQ'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_13_TITLE', configuration_description = 'CD_11_13_DESC' where configuration_key = 'ADDRESS_BOOK_SHIP_CITY_REQ'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_14_TITLE', configuration_description = 'CD_11_14_DESC' where configuration_key = 'ADDRESS_BOOK_SHIP_STATE_REQ'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_11_15_TITLE', configuration_description = 'CD_11_15_DESC' where configuration_key = 'ADDRESS_BOOK_SHIP_POSTAL_CODE_REQ'");
  /************************** Group ID 12 (E-mail Settings) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_01_TITLE', configuration_description = 'CD_12_01_DESC' where configuration_key = 'EMAIL_TRANSPORT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_02_TITLE', configuration_description = 'CD_12_02_DESC' where configuration_key = 'EMAIL_LINEFEED'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_03_TITLE', configuration_description = 'CD_12_03_DESC' where configuration_key = 'SEND_EMAILS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_04_TITLE', configuration_description = 'CD_12_04_DESC' where configuration_key = 'EMAIL_USE_HTML'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_05_TITLE', configuration_description = 'CD_12_05_DESC' where configuration_key = 'ENTRY_EMAIL_ADDRESS_CHECK'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_06_TITLE', configuration_description = 'CD_12_06_DESC' where configuration_key = 'EMAIL_ARCHIVE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_07_TITLE', configuration_description = 'CD_12_07_DESC' where configuration_key = 'EMAIL_FRIENDLY_ERRORS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_10_TITLE', configuration_description = 'CD_12_10_DESC' where configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_11_TITLE', configuration_description = 'CD_12_11_DESC' where configuration_key = 'EMAIL_FROM'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_12_TITLE', configuration_description = 'CD_12_12_DESC' where configuration_key = 'EMAIL_SEND_MUST_BE_STORE'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_15_TITLE', configuration_description = 'CD_12_15_DESC' where configuration_key = 'ADMIN_EXTRA_EMAIL_FORMAT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_40_TITLE', configuration_description = 'CD_12_40_DESC' where configuration_key = 'CONTACT_US_LIST'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_50_TITLE', configuration_description = 'CD_12_50_DESC' where configuration_key = 'CONTACT_US_STORE_NAME_ADDRESS'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_70_TITLE', configuration_description = 'CD_12_70_DESC' where configuration_key = 'EMAIL_SMTPAUTH_MAILBOX'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_71_TITLE', configuration_description = 'CD_12_71_DESC' where configuration_key = 'EMAIL_SMTPAUTH_PASSWORD'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_72_TITLE', configuration_description = 'CD_12_72_DESC' where configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_73_TITLE', configuration_description = 'CD_12_73_DESC' where configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_12_74_TITLE', configuration_description = 'CD_12_74_DESC' where configuration_key = 'CURRENCIES_TRANSLATIONS'");
  /************************** Group ID 13 (General Ledger Settings) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_13_01_TITLE', configuration_description = 'CD_13_01_DESC' where configuration_key = 'AUTO_UPDATE_PERIOD'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_13_05_TITLE', configuration_description = 'CD_13_05_DESC' where configuration_key = 'SHOW_FULL_GL_NAMES'");
  /************************** Group ID 15 (Sessions Settings) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_15_01_TITLE', configuration_description = 'CD_15_01_DESC' where configuration_key = 'SESSION_TIMEOUT_ADMIN'");
  /************************** Group ID 17 (Credit Card Settings) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_01_TITLE', configuration_description = 'CD_17_01_DESC' where configuration_key = 'CC_OWNER_MIN_LENGTH'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_02_TITLE', configuration_description = 'CD_17_02_DESC' where configuration_key = 'CC_NUMBER_MIN_LENGTH'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_03_TITLE', configuration_description = 'CD_17_03_DESC' where configuration_key = 'CC_ENABLED_VISA'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_04_TITLE', configuration_description = 'CD_17_04_DESC' where configuration_key = 'CC_ENABLED_MC'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_05_TITLE', configuration_description = 'CD_17_05_DESC' where configuration_key = 'CC_ENABLED_AMEX'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_06_TITLE', configuration_description = 'CD_17_06_DESC' where configuration_key = 'CC_ENABLED_DISCOVER'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_07_TITLE', configuration_description = 'CD_17_07_DESC' where configuration_key = 'CC_ENABLED_DINERS_CLUB'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_08_TITLE', configuration_description = 'CD_17_08_DESC' where configuration_key = 'CC_ENABLED_JCB'");
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_17_09_TITLE', configuration_description = 'CD_17_09_DESC' where configuration_key = 'CC_ENABLED_AUSTRALIAN_BANKCARD'");
  /************************** Group ID 19 (Layout Settings) ***********************************************/
  /************************** Group ID 20 (Website Maintenence) ***********************************************/
  $db->execute("update " . TABLE_CONFIGURATION . " set configuration_title = 'CD_20_99_TITLE', configuration_description = 'CD_20_99_DESC' where configuration_key = 'DEBUG'");
  /************************** Group ID 99 (Alternate (non-displayed Settings) *********************************/
}

if (!defined('INVENTORY_DEFAULT_PURCH_TAX')) {
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
           ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'CD_05_52_TITLE', 'INVENTORY_DEFAULT_PURCH_TAX', '0', 'CD_05_52_DESC', '5', '52', NULL , now(), NULL , 'cfg_pull_down_tax_rate_list(' );");
  $db->execute("ALTER TABLE " . TABLE_INVENTORY . " ADD purch_taxable INT(11) NOT NULL DEFAULT '0' AFTER item_taxable");
}
?>