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
//  Path: /modules/phreedom/language/en_us/admin.php
//

// headings
define('TEXT_MY_COMPANY','My Company');
define('TEXT_CONFIGURATION','Configuration');
define('TEXT_DEFAULT_GL_ACCOUNTS','Default GL Accounts');
define('TEXT_EMAIL_PREFERENCES','Email Preferences');
define('TEXT_CUSTOM_TABS', 'Custom Tabs');
define('TEXT_CUSTOM_FIELDS', 'Custom Fields');
define('TEXT_LOCAL','Local');
define('TEXT_DEBUG_AND_TROUBLESHOOTING','Debug and Troubleshooting');
define('TEXT_LEGEND','Legend');
define('TEXT_TAB_TITLE','Tab Title');
define('TEXT_TABS','Tabs');
define('TEXT_REQUIRED','REQUIRED');
define('TEXT_MODULE_DATA_IMPORT_OR_EXPORT','Module Data Import Or Export');
define('TEXT_IMPORT_OR_EXPORT_AND_BEGINNING_BALANCES','Import/Export and Beginning Balances');
define('TEXT_TABLE_STATISTICS','Table Statistics');
define('TEXT_DB_ENGINE','DB Engine');
define('TEXT_NUMBER_OF_ROWS','Number of Rows');
define('TEXT_COLLATION','Collation');
define('TEXT_NEXT_ROW_ID','Next Row ID');
define('TEXT_USE_IN_INVENTORY_FILTER','Use in inventory filter');
define('TEXT_SETTINGS','Settings');

// Defines for login screen
define('TEXT_PHREEBOOKS_LOGIN', 'PhreeBooks Login');
define('TEXT_LOGIN_NAME', 'Username: ');
define('TEXT_LOGIN_PASS', 'Password: ');
define('TEXT_LOGIN_COMPANY','Select Company: ');
define('TEXT_SELECT_LANGUAGE','Select Language');
define('TEXT_SELECT_THEME','Select Theme');
define('TEXT_SELECT_MENU_LOCATION','Select Menu Location');
define('TEXT_SELECT_COLOR_SCHEME','Select Color Scheme');
define('TEXT_RESEND_PASSWORD', 'Resend Password');
define('TEXT_LOGIN','Login');
define('TEXT_FORM_PLEASE_WAIT','Please wait ... If upgrading, this may take a while.');
define('TEXT_COPYRIGHT_NOTICE','This program is free software: you can redistribute it and/or
modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of
the License, or any later version. This program is distributed
in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for
more details. The license that is bundled with this package is
located %s.');

// General
define('TEXT_DEFAULT_CURRENCY','Default Currency');
define('TEXT_NULL_DEFAULT_CURRENCY','Null 0 - Default Currency');
define('TEXT_NULL_POSTED_CURRENCY','Null 0 - Posted Currency');
define('TEXT_POSTED_CURRENCY','Posted Currency');
define('TEXT_ROUND_DECIMAL','Round Decimal Places');
define('TEXT_ROUND_PRECISE','Round Precision');
define('TEXT_USER_NAME','User Name');
define('TEXT_DEFAULT_STORE','Default Store');
define('TEXT_DEFAULT_CASH_ACCOUNT','Default Cash Account');
define('TEXT_RESTRICT_STORE','Restrict Entries to this Store?');
define('TEXT_DEFAULT_AR_ACCOUNT','Default Receivables Account');
define('TEXT_DEFAULT_PAYABLES_ACCOUNT','Default Payables Account');
define('TEXT_RESTRICT_PERIOD','Restrict Posts to Current Period?');
define('TEXT_AUDIT_DB_DATA_BACKUP','Audit Log Database Table Backed Up');
define('TEXT_AUDIT_DB_DATA_CLEAN','Audit Log Database Table Cleaned');
define('TEXT_PHREEBOOKS_SQL_ERROR_TRACE_INFORMATION','PhreeBooks SQL Error Trace Information');
define('TEXT_CRASH_INFORMATION','PhreeBooks has encountered an unexpected error. Click on the button below to download the debug trace file information to send to the PhreeBooks Development Team for troubleshooting assistance.');
define('TEXT_DOWNLOAD_DEBUG_INFORMATION','Download Debug Information');
define('TEXT_CONFIGURATION_VALUES_HAVE_BEEN_SAVED','Configuration values have been saved.');
define('TEXT_CHANGE_VARIOUS_SEQUENCE_NUMBERS','Change Various Sequence Numbers');
define('GEN_ADM_TOOLS_SEQ_DESC','Changes to the sequencing can be made here.<br />NOTE 1: PhreeBooks does not allow duplicate sequences, be sure the new starting sequence will not conflict with any currently posted values.<br />Note 2: The next_deposit_num is generated by the system and uses the current date.<br />Note 3: The next_check_num can be set at the payment screen prior to posting a payment and will continue from the entered value.');
define('TEXT_THEMES_COLORS_TITLE','Themes and Color Schemes');
define('TEXT_THEMES_COLORS_DESC','Set your prefered theme and color scheme. Press the Save icon to switch theme and see it in action.');
define('TEXT_YOUR_PERMISSIONS_DO_NOT_ALLOW_THE_USERS_ROLE_OR_SECURITY_TO_BE_CHANGED','Your permissions do not allow the users role/security to be changed!');
define('GEN_ERROR_NO_THEME_COLORS','A color choice must be made, this theme does not appear to have any! Please select another theme.');
define('ERROR_CANNOT_CREATE_MODULE_DIR','Error creating directory: %s. Check your permissions!');
define('ERROR_CANNOT_REMOVE_MODULE_DIR','Error removing directory: %s. The directory may not exist or may not be empty! It must be removed by hand.');
define('TEXT_BACKUP_OR_CLEAN_AUDIT_LOGS','Backup/Clean Audit Logs');
define('GEN_ADM_TOOLS_CLEAN_LOG_DESC','This operation creates a downloaded backup of your audit log database file. This will help keep the database size down and reduce company backup file sizes. Backing up this log is recommended before cleaning out to preserve PhreeBooks transaction history. <br />INFORMATION: Cleaning out the audit log will leave the current periods data in the database table and remove all other records.');
define('TEXT_BACKUP_AUDIT_LOG','Backup Audit Log');
define('TEXT_CLEAN_OUT_AUDIT_LOG','Clean Out Audit Log');
define('GEN_ADM_TOOLS_BTN_CLEAN_CONFIRM','Are you sure you want to delete these log records?');
define('TEXT_BACKUP_NOW','Backup Now');
define('TEXT_CLEAN_NOW','Clean Now');
define('TEXT_CLEAN_DATA_SECURITY_VALUES','Clean Data Security Values');
define('GEN_ADM_TOOLS_SECURITY_DESC','This tool cleans all data security values with a expiration date prior to a selected date. WARNING: This operation cannot be undone!');
define('TEXT_CLEAN_ALL_VALUES_WITH_EXPIRATION_DATE_BEFORE','Clean all values with expiration date before');
define('TEXT_CLEAN_SECURITY_SUCCESS','Successfully removed %s data security records.');
define('GL_HEADING_BEGINNING_BALANCES','Chart of Accounts - Beginning Balances');
define('TEXT_IMPORT_BEGINNING_BALANCES','Import Beginning Balances');
define('GL_BTN_IMP_BEG_BALANCES','Import Inventory, Accounts Payable, Accounts Receivable Beginning Balances');
define('TEXT_GENERAL_JOURNAL_BEGINNING_BALANCES','General Journal Beginning Balances');
define('GL_UTIL_BEG_BAL_TEXT','For initial set-ups and transfers from another accounting system.');
define('TEXT_ENTER_BEGINNING_BALANCES','Enter Beginning Balances');
define('TEXT_IMPORT_JOURNAL_ENTRIES','Import Journal Entries');
define('TEXT_IMPORT_INVENTORY','Import Inventory');
define('TEXT_IMPORT_ACCOUNTS_PAYABLE','Import Accounts Payable');
define('TEXT_IMPORT_ACCOUNTS_RECEIVABLE','Import Accounts Receivable');
define('TEXT_IMPORT_SALES_ORDERS','Import Sales Orders');
define('TEXT_IMPORT_PURCHASE_ORDERS','Import Purchase Orders');
define('TEXT_REFER_TO_THE_HELP_FILE_FOR_FORMAT_REQUIREMENTS','Refer to the help file for format requirements.');
define('TEXT_IMPORT_OR_EXPORT_DATABASE_TABLES','Import/Export Database Tables');
define('TEXT_IMPORT_EXPORT_INFO','Table Information');
define('GEN_IMPORT_EXPORT_MESSAGE','Importing can be through XML or CSV format. Click on the sample button to download a sample file to use for formatting purposes.');
define('TEXT_SAMPLE_XML','Sample XML');
define('TEXT_SAMPLE_CSV','Sample CSV');
define('GEN_IMPORT_MESSAGE','The list below displays the tables available for import. Select a format, upload a file and press the Import button to continue.');
define('TEXT_SELECT_A_FORMAT_AND_PRESS_THE_EXPORT_BUTTON_TO_CONTINUE','Select a format and press the Export button to continue.');
define('TEXT_TABLES_AVAILABLE_TO','Tables Available to');
/************************** (General) ***********************************************/
define('TEXT_MINIMUM_LENGTH_OF_PASSWORD', 'Minimum length of password');
define('TEXT_MAXIMUM_NUMBER_OF_SEARCH_RESULTS_RETURNED_PER_PAGE', 'Maximum number of search results returned per page');
define('CD_08_03_DESC', 'Automatically check for program updates at login to PhreeBooks.');
define('CD_08_05_DESC', 'Hides messages on successful operations. Only caution and error messages will be displayed.');
define('CD_08_07_DESC', 'Updates the exchange rate for loaded currencies at every login.<br />If disabled, currencies may be manually updated in the Setup => Currencies menu.');
define('CD_08_10_DESC', 'Limits the length of history values shown in customer/vendor accounts for sales/purchases.');
define('CD_15_01_DESC', 'Session Timeout - Enter the time in seconds (default = 3600). Example: 3600= 1 hour<br />Note: Too few seconds can result in timeout issues when adding/editing products, minimum value is 6 minutes.');
define('CD_15_05_DESC', 'When enabled, this option will use ajax to refresh the session timer every 5 minutes preventing the session from expiring and logging out the user. This feature helps prevent dropped posts when PhreeBooks has been inactive and a post results in a login screen.');
define('CD_20_99_DESC', 'Enable trace generation for debug purposes. If Yes is selected, an additional menu will be added to the Tools menu to download the trace information to help debug posting problems.');
define('CD_09_01_DESC', 'Specifies the export preference when exporting reports and forms. Local will save them in the /my_files/reports directory of the webserver for use with all companies. Download will download the file to your browser to save/print on your local machine.');
define('CD_00_01_DESC', 'Sets the display format for displayed and entered dates (default m/d/Y), m - month; d - day; Y - four digit year. Refer to the php.net function <b>date</b> for format requirements.');
define('CD_00_02_DESC', 'Identifies the delimiter used to seperate dates (default /). This must match the delimiter use in the Date format above.');
define('CD_00_03_DESC', 'Sets the display format for formal with time (default m/d/Y h:i:s a). Refer to the php.net date function for format options.');
/************************** (My Company) ***********************************************/
define('TEXT_THE_NAME_OF_MY_COMPANY', 'The name of my company');
define('TEXT_THE_DEFAULT_NAME_OR_IDENTIFIER_TO_USE_FOR_ALL_RECEIVABLE_OPERATIONS', 'The default name or identifier to use for all receivable operations.');
define('TEXT_THE_DEFAULT_NAME_OR_IDENTIFIER_TO_USE_FOR_ALL_PAYABLE_OPERATIONS', 'The default name or identifier to use for all payable operations.');
define('TEXT_FIRST_ADDRESS_LINE', 'First address line');
define('TEXT_SECOND_ADDRESS_LINE', 'Second address line');
define('TEXT_THE_CITY_OR_TOWN_WHERE_THIS_COMPANY_IS_LOCATED', 'The city or town where this company is located');
define('TEXT_THE_STATE_OR_REGION_WHERE_THIS_COMPANY_IS_LOCATED', 'The state or region where this company is located');
define('TEXT_POSTAL_OR_ZIP_CODE_WHERE_THIS_COMPANY_IS_LOCATED', 'Postal or Zip code where this company is located');
define('TEXT_THE_COUNTRY_THIS_COMPANY_IS_LOCATED', 'The country this company is located');
define('TEXT_ENTER_THE_COMPANYS_PRIMARY_TELEPHONE_NUMBER', 'Enter the company\'s primary telephone number');
define('TEXT_SECONDARY_TELEPHONE_NUMBER', 'Secondary telephone number');
define('TEXT_ENTER_THE_COMPANYS_FAX_NUMBER', 'Enter the company\'s fax number');
define('TEXT_ENTER_THE_GENERAL_COMPANY_EMAIL_ADDRESS', 'Enter the general company email address');
define('TEXT_THE_COMPANY_WEBSITE_WITHOUT_THE_HTTP', 'The company website (without the http://)');
define('TEXT_ENTER_THE_COMPANYS_FEDERAL_TAX_ID_NUMBER', 'Enter the company\'s (Federal) tax ID number');
define('CD_01_16_DESC', 'Enter the company ID number. This number is used to identify transactions generated locally versus imported/exported transactions.');
define('CD_01_18_DESC', 'Enable multiple branch functionality.<br />If No is selected, only one company location will be assumed.');
define('CD_01_19_DESC', 'Enable multiple currencies in user entry screens.<br />If No is selected, only the default currency wil be used.');
define('CD_01_20_DESC', 'Automatically switch to the language\'s currency when it is changed');
define('CD_01_25_DESC', 'Whether or not to enable the shipping functions and shipping fields.');
define('TEXT_ALLOW_STORAGE_OF_ENCRYPTED_FIELDS', 'Allow storage of encrypted fields.');
/************************** E-mail Settings ***********************************************/
define('CD_12_01_DESC', 'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br />Servers running on Windows and MacOS should change this setting to <strong>SMTP</strong>.<br /><strong>SMTPAUTH</strong> should only be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail -f"</strong> is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><strong>Qmail</strong> is used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.');
define('TEXT_DEFINES_THE_CHARACTER_SEQUENCE_USED_TO_SEPARATE_MAIL_HEADERS', 'Defines the character sequence used to separate mail headers.');
define('TEXT_SEND_E-MAILS_IN_HTML_FORMAT', 'Send e-mails in HTML format');
define('CD_12_10_DESC', 'Email address of Store Owner.  Used as "display only" when informing customers of how to contact you.');
define('CD_12_11_DESC', 'Address from which email messages will be "sent" by default. Can be over-ridden at compose-time in admin modules.');
define('TEXT_PLEASE_SELECT_THE_ADMIN_EXTRA_EMAIL_FORMAT', 'Please select the Admin extra email format');
define('CD_12_70_DESC', 'Enter the mailbox account name (me@mydomain.com) supplied by your host. This is the account name that your host requires for SMTP authentication. (Only required if using SMTP Authentication for email)');
define('CD_12_71_DESC', 'Enter the password for your SMTP mailbox. (Only required if using SMTP Authentication for email)');
define('CD_12_72_DESC', 'Enter the DNS name of your SMTP mail server. i.e. mail.mydomain.com or 55.66.77.88 (Only required if using SMTP Authentication for email)');
define('CD_12_73_DESC', 'Enter the IP port number that your SMTP mailserver operates on. (Only required if using SMTP Authentication for email)');
define('CD_12_74_DESC', 'What currency conversions do you need for Text emails? (Default = &amp;pound;,�:&amp;euro;,�)');
define('SETUP_SERVER_ADDRESS', 'As of php 5.4 some linux server can not determine your web addres and will refuse to accept your email (smtp_auth) the solution: enter here your website address<br/>How do you know when you encounter this problem.? Turn debug "on" and you should see on of the lines stating something like "Access denied - Invalid HELO name (See RFC2821 4.1.1 .1)"');
/************************** Currencies Settings ***********************************************/
define('TEXT_CURRENCIES', 'Currencies');
define('TEXT_CURRENCY', 'Currency');
define('TEXT_CURRENCY_CODE', 'Currency Code');
define('TEXT_UPDATE_EXCHANGE_RATE','Update Exchange Rate');
define('SETUP_CURR_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_TITLE', 'Title:');
define('TEXT_SYMBOL_LEFT', 'Symbol Left');
define('TEXT_SYMBOL_RIGHT', 'Symbol Right');
define('TEXT_DECIMAL_POINT', 'Decimal Point');
define('TEXT_THOUSANDS_POINT', 'Thousands Point');
define('TEXT_DECIMAL_PLACES', 'Decimal Places:');
define('SETUP_INFO_CURRENCY_DECIMAL_PRECISE', 'Decimal Precision: For use with unit prices and quantities at a higher precision than currency values. This value is typically set to the number of decimal places:');
define('TEXT_VALUE', 'Value');
define('TEXT_PLEASE_ENTER_THE_NEW_CURRENCY_WITH_ITS_RELATED_DATA', 'Please enter the new currency with its related data');
define('SETUP_CURR_DELETE_INTRO', 'Are you sure you want to delete this currency?');
define('SETUP_INFO_HEADING_NEW_CURRENCY', 'New Currency');
define('SETUP_INFO_HEADING_EDIT_CURRENCY', 'Edit Currency');
define('SETUP_INFO_SET_AS_DEFAULT', 'Set as default (requires a manual update of currency values)');
define('SETUP_INFO_CURRENCY_UPDATED', 'The exchange rate for %s (%s) was updated successfully via %s.');
define('SETUP_ERROR_CANNOT_CHANGE_DEFAULT', 'The default currency cannot be changed once entries have been entered in the system!');
define('SETUP_ERROR_CURRENCY_INVALID', 'Error: The exchange rate for %s (%s) was not updated via %s. Is it a valid currency code?');
define('SETUP_WARN_PRIMARY_SERVER_FAILED', 'Warning: The primary exchange rate server (%s) failed for %s (%s) - trying the secondary exchange rate server.');
define('SETUP_LOG_CURRENCY','Currencies - ');
// Encryption defines
define('TEXT_SAVE_CHANGES','Save Changes');
define('TEXT_ENTER_ENCRYPTION_KEY','Enter Encryption Key');
define('GEN_ENCRYPTION_GEN_INFO','Encryption services depend on a key used to encrypt data in the database. DO NOT LOSE THE KEY, otherwise data can not be decrypted!');
define('TEXT_ENTER_THE_ENCRYPTION_KEY_USED_TO_STORE_SECURE_DATA','Enter the Encryption key used to store secure data.');
define('TEXT_ENCRYPTION_KEY','Encryption key ');
define('TEXT_ENCRYPTION_KEY_CONFIRM','Re-enter key ');
define('TEXT_ERROR_ENCRYPTION_KEY_MATCH','The encryption keys do not match!');
define('TEXT_ERROR_WRONG_ENCRYPTION_KEY','You entered an encryption key but it did not match the stored value.');
define('TEXT_THE_ENCRYPTION_KEY_IS_SET','The encryption key has been set.');
define('TEXT_THE_ENCRYPTION_KEY_IS_CHANGED','The encryption key has been changed.');
define('TEXT_CREATE_OR_CHANGE_ENCRYPTION_KEY','Create or Change Encryption Key');
define('GEN_ADM_TOOLS_SET_ENCRYPTION_PW_DESC','Set the encryption key to use if \'Encryption Enabled\' is turned on. If setting for the first time, the old encryption key is blank.');
define('TEXT_OLD_ENCRYPTION_KEY','Old Encryption Key');
define('GEN_ADM_TOOLS_ENCRYPT_PW','New Encryption Key');
define('TEXT_RE-ENTER_NEW_ENCRYPTION_KEY','Re-enter New Encryption Key');
define('TEXT_THE_CURRENT_ENCRYPTED_KEY_DOES_NOT_MATCH_THE_STORED_VALUE','The current encrypted key does not match the stored value!');
// backup defines
define('TEXT_COMPANY_RESTORE','Company Restore');
define('TEXT_START_BACKUP','Start Backup');
define('GEN_BACKUP_GEN_INFO','Please select the backup compression type and options below.');
define('TEXT_COMPRESSION_TYPE','Compression Type');
define('GEN_COMP_BZ2',' bz2 (Linux)');
define('TEXT_COMPRESSION_ZIP',' Zip');
define('GEN_COMP_NONE','None (Database Only)');
define('TEXT_DATABASE_ONLY',' Database Only');
define('TEXT_DATABASE_AND_COMPANY_DATA_FILES',' Database and Company Data Files');
define('GEN_BACKUP_SAVE_LOCAL',' Save a local copy in webserver (my_files/backups) directory');
define('GEN_BACKUP_WARNING','Warning! This operation will delete and re-write the database. Are you sure you want to continue?');
define('GEN_BACKUP_NO_ZIP_CLASS','The zip class cannot be found. PHP needs the zip library installed to back up with zip compression.');
define('TEXT_ERROR_ZIP_FILE','The zip file cannot be created. Check permissions for the directory: ');
define('TEXT_THE_DOWNLOAD_FILE_DOES_NOT_CONTAIN_ANY_DATA','The download file does not contain any data!');
// company manager
define('SETUP_CO_MGR_COPY_CO','New/Copy Company');
define('TEXT_DELETE_COMPANY','Delete Company');
define('TEXT_BASIC_DATA','Basic Data');
define('TEXT_ALL_DATA','All Data');
define('TEXT_DEMO_DATA','Demo Data');
define('SETUP_CO_MGR_COPY_HDR','Enter the database information for the new company. (Must conform to mysql naming conventions, typically 8-12 alphanumeric characters) This name is used as the database name and will be added to the my_files directory to hold company specific data. The database must exist prior to creating the company.');
define('TEXT_DATABASE_SERVER','Database Server ');
define('TEXT_DATABASE_NAME','Database Name ');
define('TEXT_DATABASE_USER_NAME','Database User Name ');
define('TEXT_DATABASE_PASSWORD','Database Password ');
define('TEXT_COMPANY_FULL_NAME','Company Full Name ');
define('SETUP_CO_MGR_MOD_SELECT','Please select the modules to copy/create and the data to copy. To create a new company, select Basic Data or Demo Data:');
define('SETUP_CO_MGR_ERROR_EMPTY_FIELD','Database name and company name cannot be blank!');
define('SETUP_CO_MGR_DUP_DB_NAME','Error - The database name cannot be the same as the current database name!');
define('SETUP_CO_MGR_CANNOT_CONNECT','Error connecting to the new database. Check the username and password.');
define('TEXT_ERROR_CREATING_DATABASE_TABLES','Error creating database tables.');
define('TEXT_SUCCESSFULY_CREATED_NEW_COMPANY','Successfuly created new company');
define('SETUP_CO_MGR_DELETE_SUCCESS','The company was successfully deleted!');
define('SETUP_CO_MGR_LOG','Company Manager - ');
define('TEXT_SELECT_THE_COMPANY_TO_DELETE','Select the company to delete');
define('SETUP_CO_MGR_DELETE_CONFIRM','WARNING: THIS WILL DELETE THE DATABASE AND ALL COMPANY SPECIFIC FILES! ALL DATA WILL BE LOST!');
define('SETUP_CO_MGR_JS_DELETE_CONFIRM','Are you sure you want to delete this company?');
define('TEXT_NO_COMPANY_WAS_SELECTED_TO_DELETE','No company was selected to delete');


define('TEXT_TAB_INSERT_INTRO', 'Please enter the new tab with its properties');
define('TEXT_NEW_FIELD','New Field');
define('INV_INFO_HEADING_EDIT_CATEGORY', 'Edit Tab');
define('EXTRA_TABS_DELETE_INTRO', 'Are you sure you want to delete this tab?\nTabs cannot be deleted if there is a field within the tab.');
define('TEXT_THIS_TAB_NAME_ALREADY_EXISTS_PLEASE_USE_ANOTHER_NAME','This tab name already exists, please use another name.');
define('EXTRA_FIELDS_LOG','Extra Fields (%s)');
define('EXTRA_TABS_LOG','Asset Tabs (%s)');
define('TEXT_TAB_MEMBER', 'Tab Member');
define('INV_FIELD_NAME', 'Field Name:');
define('EXTRA_ERROR_FIELD_BLANK','The asset field name is blank, please enter a field name and re-check all entries in this form!');
define('EXTRA_FIELD_ERROR_DUPLICATE','The field name you entered is already in use, please enter a new field name!');
define('EXTRA_FIELD_RESERVED_WORD','The field name you entered is a MySQL reserved word, please enter a new field name!');
define('EXTRA_FIELD_DELETE_INTRO', 'Are you sure you want to delete this field?');
define('EXTRA_FIELDS_ERROR_NO_TABS','There are no custom tabs, please add at least one custom tab before adding fields.');
/************************** ( Tabs/Fields) ***********************************************/
define('TEXT_DEFAULT_VALUE', 'Default Value');
define('INV_LABEL_MAX_NUM_CHARS', 'Maximum Number of Characters (Length)');
define('INV_LABEL_FIXED_255_CHARS', 'Fixed at 255 Characters Maximum');
define('TEXT_FOR_LENGTHS_LESS_THAN_256_CHARACTERS', 'for lengths less than 256 Characters');
define('TEXT_ENTER_SELECTION_STRING', 'Enter Selection String');
define('TEXT_TEXT_FIELD', 'Text Field');
define('TEXT_HTML_CODE', 'HTML Code');
define('TEXT_HYPER-LINK', 'Hyper-Link');
define('TEXT_IMAGE_FILE_NAME', 'Image File Name');
define('INV_LABEL_INVENTORY_LINK', 'Inventory Link <br>(Link pointing to another inventory item (URL))');
define('TEXT_INTEGER_NUMBER', 'Integer Number');
define('TEXT_INTEGER_RANGE', 'Integer Range');
define('TEXT_DECIMAL_NUMBER', 'Decimal Number');
define('TEXT_DECIMAL_RANGE', 'Decimal Range');
define('INV_LABEL_DEFAULT_DISPLAY_VALUE', 'Display Format (Max,Decimal)');
define('TEXT_DROPDOWN_LIST', 'Dropdown List');
define('TEXT_MULTIPLE_OPTIONS_CHECKBOXES','Multiple Options Checkboxes');
define('TEXT_RADIO_BUTTON', 'Radio Button');
define('INV_LABEL_RADIO_EXPLANATION','Enter choices, separated by commas as:<br />value1:desc1:def1,value2:desc2:def2<br /><u>Key:</u><br />value = The value to place into the database<br />desc = Textual description of the choice<br />def = Default 0 or 1, 1 being the default choice<br />Note: Only 1 default is allowed per list');
define('TEXT_DATE_AND_TIME', 'Date and Time');
define('TEXT_CHECK_BOX_FIELD', 'Check Box <br>(Yes or No Choice)');
define('TEXT_TIME_STAMP', 'Time Stamp');
define('INV_LABEL_TIME_STAMP_VALUE', 'System field to track the last date and time <br> a change to a particular inventory item was made.');
define('INV_FIELD_NAME_RULES','Fieldnames cannot contain spaces or special characters and must be 64 characters or less.');
define('INV_CATEGORY_CANNOT_DELETE','Cannot delete category. It is being used by field: ');
define('INV_CANNOT_DELETE_SYSTEM','Fields in the System category cannot be deleted!');
define('INV_IMAGE_PATH_ERROR','Error in the path specified for the upload image!');
define('INV_IMAGE_FILE_TYPE_ERROR','Error in the uploaded image file. Not an acceptable file type.');
define('INV_IMAGE_FILE_WRITE_ERROR','There was a problem writing the image file to the specified directory.');
define('INV_FIELD_RESERVED_WORD','The field name entered is a reserved word. Please choose a new field name.');
// Audit Log Messages
define('TEXT_USER_LOGIN','User Login');
define('GEN_LOG_LOGIN_FAILED','Failed User Login - reason : %s id -> %s ');
define('TEXT_USER_LOGOFF','User Logoff');
define('TEXT_RE-SENT_PASSWORD_TO_EMAIL','Re-sent Password to email');

?>