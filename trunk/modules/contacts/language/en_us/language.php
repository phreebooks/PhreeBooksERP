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
//  Path: /modules/contacts/language/en_us/language.php
//
//
// Titles and Headings
define('TEXT_MONTHLY_SALES','Monthly Sales');
// Account table fields - common to all account types
define('TEXT_CONTACT_SEARCH', 'Contact Search');
define('ACT_POPUP_TERMS_WINDOW_TITLE', 'Payment Terms');
// General defines
define('ACT_CATEGORY_I_ADDRESS','Add/Edit Contact');
define('TEXT_BUYER','Buyer');
define('ACT_SHORT_NAME','Contact ID');
define('TEXT_CONTACTS','Contacts');
define('TEXT_EMPLOYEE','Employee');
define('TEXT_LINK_TO','Link To:');
define('TEXT_NEW_CONTACT','New Contact');
define('TEXT_SALES_REP','Sales Rep');
define('TEXT_TRANSFER_ADDRESS','Transfer Address');
define('TEXT_NEW_CALL','New Call');
define('TEXT_RETURNED_CALL','Returned Call');
define('TEXT_FOLLOW_UP_CALL','Follow Up');
define('TEXT_NEW_LEAD','New Lead');

// Address/contact identifiers
define('TEXT_NAME_OR_COMPANY', 'Name or Company');
define('TEXT_EMPLOYEE_NAME', 'Employee Name');
define('TEXT_ATTENTION', 'Attention');
define('TEXT_ADDRESS1', 'Address1');
define('TEXT_ADDRESS2', 'Address2');
define('TEXT_CITY_TOWN', 'City');
define('TEXT_STATE_PROVINCE', 'State');
define('TEXT_POSTAL_CODE', 'ZipCode');
define('TEXT_COUNTRY', 'Country');
define('TEXT_COUNTRY_ISO_CODE', 'Country ISO Code');
define('TEXT_FIRST_NAME','First Name');
define('TEXT_MIDDLE_NAME','Middle Name');
define('TEXT_LAST_NAME','Last Name');
define('GEN_TELEPHONE1', 'Telephone');
define('TEXT_ALTERNATIVE_TELEPHONE_SHORT', 'Alt Telephone');
define('TEXT_FAX','Fax');
define('TEXT_MOBILE_PHONE', 'Mobile Phone');
define('GEN_ACCOUNT_ID', 'Account ID');
define('GEN_CUSTOMER_ID', 'Customer ID:');
define('GEN_VENDOR_ID', 'Vendor ID:');
define('TEXT_FACEBOOK_ID','Facebook ID');
define('TEXT_TWITTER_ID','Twitter ID');
define('TEXT_WEBSITE','Website');
define('TEXT_LINK_TO_EMPLOYEE_ACCOUNT','Link to Employee Account');
// Targeted defines (to differentiate wording differences for different account types)
// Text specific to branch contacts
define('ACT_B_TYPE_NAME','Branches');
define('ACT_B_HEADING_TITLE', 'Branches');
define('ACT_B_SHORT_NAME', 'Branch ID');
define('ACT_B_FIRST_DATE','Creation Date: ');
define('ACT_B_PAGE_TITLE_EDIT','Edit Branch');
// Text specific to Customer contacts (default)
define('ACT_C_TYPE_NAME','Customers');
define('ACT_C_HEADING_TITLE', 'Customers');
define('ACT_C_SHORT_NAME', 'Customer ID');
define('ACT_C_GL_ACCOUNT_TYPE','Sales GL Account');
define('ACT_C_ID_NUMBER','Resale License Number');
define('ACT_C_REP_ID','Sales Rep ID');
define('ACT_C_ACCOUNT_NUMBER','Account Number');
define('ACT_C_FIRST_DATE','Customer Since: ');
define('ACT_C_LAST_DATE1','Last Invoice Date: ');
define('ACT_C_LAST_DATE2','Last Payment Date: ');
define('ACT_C_PAGE_TITLE_EDIT','Edit Customer');
// Text specific to Employee contacts
define('ACT_E_TYPE_NAME','Employees');
define('ACT_E_HEADING_TITLE', 'Employees');
define('ACT_E_SHORT_NAME', 'Employee ID');
define('ACT_E_GL_ACCOUNT_TYPE','Employee Type');
define('ACT_E_ID_NUMBER','Social Security Number');
define('ACT_E_REP_ID','Department ID');
define('TEXT_HIRE_DATE','Hire Date');
define('ACT_E_LAST_DATE1','Last Raise Date: ');
define('ACT_E_LAST_DATE2','Termination Date: ');
define('ACT_E_PAGE_TITLE_EDIT','Edit Employee');
// Text specific to PhreeCRM
define('ACT_I_TYPE_NAME','Contacts');
define('ACT_I_HEADING_TITLE','PhreeCRM');
define('ACT_I_SHORT_NAME','Contact');
define('ACT_I_PAGE_TITLE_EDIT','Edit Contact');
// Text specific to Projects
define('ACT_J_TYPE_NAME','Projects');
define('ACT_J_HEADING_TITLE', 'Projects');
define('ACT_J_SHORT_NAME', 'Project ID');
define('ACT_J_ID_NUMBER','Reference PO');
define('ACT_J_REP_ID','Sales Rep ID');
define('ACT_J_PAGE_TITLE_EDIT','Edit Project');
define('ACT_J_ACCOUNT_NUMBER','Break Into Phases');
// Text specific to Vendor contacts
define('ACT_V_TYPE_NAME','Vendors');
define('ACT_V_HEADING_TITLE', 'Vendors');
define('ACT_V_SHORT_NAME', 'Vendor ID');
define('ACT_V_GL_ACCOUNT_TYPE','Purchase GL Account');
define('ACT_V_ID_NUMBER','Federal EIN');
define('ACT_V_REP_ID','Purchase Rep ID');
define('ACT_V_ACCOUNT_NUMBER','Account Number');
define('ACT_V_FIRST_DATE','Vendor Since: ');
define('ACT_V_LAST_DATE1','Last Invoice Date: ');
define('ACT_V_LAST_DATE2','Last Payment Date: ');
define('ACT_V_PAGE_TITLE_EDIT','Edit Vendor');
// Category headings
define('TEXT_CONTACT_INFORMATION','Contact Information');
define('ACT_CATEGORY_M_ADDRESS','Main Mailing Address');
define('ACT_CATEGORY_S_ADDRESS','Shipping Addresses');
define('ACT_CATEGORY_B_ADDRESS','Billing Addresses');
define('ACT_CATEGORY_P_ADDRESS','Credit Card Payment Information');
define('ACT_CATEGORY_PAYMENT_TERMS','Payment Terms');
define('TEXT_ADDRESS_BOOK','Address Book');
define('TEXT_EMPLOYEE_ROLES','Employee Roles');
define('ACT_ACT_HISTORY','Account History');
define('TEXT_ORDER_HISTORY','Order History');
define('ACT_SO_HIST','Sales Order History (Most Recent %s Results)');
define('TEXT_PO_HISTORY_ARGS','Purchase Order History (Most Recent %s Results)');
define('TEXT_INVOICE_HISTORY_ARGS','Invoice History (Most Recent %s Results)');
define('ACT_SO_NUMBER','SO Number');
define('ACT_PO_NUMBER','PO Number');
define('TEXT_INVOICE_NUMBER','Invoice Number');
define('ACT_PAYMENT_MESSAGE','Enter the payment information to be stored in PhreeBooks.');
define('TEXT_CARDHOLDER_NAME','Cardholder Name');
define('TEXT_CREDIT_CARD_NUMBER','Credit Card Number');
define('ACT_PAYMENT_CREDIT_CARD_EXPIRES','Credit Card Expiration Date');
define('TEXT_CARD_HINT','Card Hint');
define('TEXT_EXPIRE_SHORT','Exp');
define('TEXT_SECURITY_CODE','Security Code');
// Account Terms
define('ACT_SPECIAL_TERMS','Special Terms');
define('TEXT_TERMS_DUE','Terms (Due)');
define('TEXT_DEFAULT','Default');
define('TEXT_USE_DEFAULT_TERMS', 'Use Default Terms');
define('TEXT_CASH_ON_DELIVERY_SHORT','COD');
define('TEXT_CASH_ON_DELIVERY','Cash On Delivery');
define('TEXT_PREPAID','Prepaid');
define('ACT_SPECIAL_TERMS', 'Due in number of days');
define('TEXT_DUE_END_OF_MONTH','Due end of month');
define('TEXT_DUE_ON_SPECIFIED_DATE','Due on specified date');
define('TEXT_DUE_ON', 'Due on');
define('TEXT_DISCOUNT', 'Discount ');
define('TEXT_PERCENT', ' percent. ');
define('TEXT_PERCENT_SHORT', '% ');
define('TEXT_DUE_IN','Due in ');
define('TEXT_DAY_S', ' day(s). ');
define('ACT_TERMS_NET','Net ');
define('ACT_TERMS_STANDARD_DAYS', ' day(s). ');
define('TEXT_CREDIT_LIMIT', 'Credit limit: ');
define('TEXT_AMOUNT_PAST_DUE','Amount Past Due');
// misc information messages
define('RECORD_NUM_REF_ONLY','record ID (Reference only) = ');
define('TEXT_LEAVE_BLANK_FOR_SYSTEM_GENERATED_ID','(Leave blank for system generated ID)');
define('ACT_WARN_DELETE_ADDRESS','Are you sure you want to delete this address?');
define('ACT_WARN_DELETE_ACCOUNT', 'Are you sure you want to delete this account?');
define('ACT_WARN_DELETE_PAYMENT', 'Are you sure you want to delete this payment record?');
define('ACT_ERROR_CANNOT_DELETE','Cannot delete this contact because a journal record contains this account');
define('ACT_ERROR_CANNOT_DELETE_EMPLOYEE','Cannot delete this employee because it is used by a user.');
define('ACT_ERROR_DUPLICATE_ACCOUNT','The account ID already exists in the system, please enter a new id.');
define('TEXT_THE_ACCOUNT_YOU_ARE_LOOKING_FOR_COULD_NOT_BE_FOUND','The account you are looking for could not be found!');
define('ACT_BILLING_MESSAGE','These fields are not required unless a billing address is being added.');
define('ACT_SHIPPING_MESSAGE','These fields are not required unless a shipping address is being added.');
define('ACT_NO_ENCRYPT_KEY_ENTERED','CAUTION: The encryption key has not been entered. Stored credit card information will not be displayed and values entered here will not be saved!');
define('TEXT_PAYMENT_REF','Payment Ref');
define('TEXT_OPEN_ORDERS','Open Orders');
define('TEXT_OPEN_INVOICES','Open Invoices');
define('TEXT_WARNING_NO_ENCRYPTION_KEY','A payment was specified but the encryption key has not been entered. The payment address was saved but the payment information was not.');
define('ACT_ERROR_DUPLICATE_CONTACT','The contact ID already exists in the system, please enter a new contact ID.');
define('CRM_ROW_DELETE_ALERT','Are you sure you want to remove this CRM note?');
// java script errors
define('TEXT_THE_ID_ENTRY_CANNOT_BE_EMPTY', 'The \'ID\' entry cannot be blank.');

?>