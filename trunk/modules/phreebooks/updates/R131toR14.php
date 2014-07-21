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
//  Path: /modules/phreebooks/updates/R131toR14.php
//

// This script updates all beta releases to the first production release.

  // Release 1.0 to 1.1
  if ($db_release == '1.0') { // go to Release 1.1
    // change length of purch_order_id field to 24
	$db->Execute("alter table " . TABLE_JOURNAL_MAIN . " change `purch_order_id` `purch_order_id` VARCHAR(24) NULL DEFAULT NULL");

	// add three missing fields to the current status table
	$fields = mysql_list_fields(DB_DATABASE, TABLE_CURRENT_STATUS);
	$columns = mysql_num_fields($fields);
	$field_array = array();
	for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
	if (!in_array('next_cm_num', $field_array)) {
	  $db->Execute("alter table " . TABLE_CURRENT_STATUS . " add next_cm_num VARCHAR(16) NOT NULL");
	  $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_cm_num = 'CM1000'");
	}
	if (!in_array('next_ap_quote_num', $field_array)) {
	  $db->Execute("alter table " . TABLE_CURRENT_STATUS . " add next_ap_quote_num VARCHAR(16) NOT NULL");
	  $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_ap_quote_num = 'RFQ1000'");
	}
	if (!in_array('next_ar_quote_num', $field_array)) {
	  $db->Execute("alter table " . TABLE_CURRENT_STATUS . " add next_ar_quote_num VARCHAR(16) NOT NULL");
	  $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_ar_quote_num = 'Q1000'");
	}
    // update the database version information
	update_version_db();
    $db_release = '1.1';
    if ($code_release == $db_release) return; // end script db is same level as code
  }
  // End Release 1.0 to 1.1


  // Release 1.1 to 1.2
  if ($db_release == '1.1') { // go to Release 1.2
    // add date field to track actual package arrival
	$fields = mysql_list_fields(DB_DATABASE, TABLE_SHIPPING_LOG);
	$columns = mysql_num_fields($fields);
	$field_array = array();
	for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
	if (!in_array('actual_date', $field_array)) {
	  $db->Execute("alter table " . TABLE_SHIPPING_LOG . " ADD actual_date DATE NOT NULL DEFAULT '0000-00-00' after deliver_date");
	}

	$fields = mysql_list_fields(DB_DATABASE, TABLE_JOURNAL_MAIN);
	$columns = mysql_num_fields($fields);
	$field_array = array();
	for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
	if (in_array('recur_remain', $field_array)) {
		$db->Execute("ALTER TABLE " . TABLE_JOURNAL_MAIN . " DROP recur_times");
		$db->Execute("ALTER TABLE " . TABLE_JOURNAL_MAIN . " CHANGE recur_remain recur_id INT( 11 ) NULL DEFAULT NULL");
	}

	$fields = mysql_list_fields(DB_DATABASE, TABLE_INVENTORY_HISTORY);
	$columns = mysql_num_fields($fields);
	$field_array = array();
	for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
	if (in_array('usage_id', $field_array)) {
		$db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY . " DROP usage_id");
		$db->Execute("CREATE TABLE " . TABLE_INVENTORY_COGS_USAGE  ." (
		  id INT(11) NOT NULL auto_increment,
		  journal_main_id INT(11) NOT NULL DEFAULT '0',
		  qty FLOAT NOT NULL DEFAULT '0',
		  inventory_history_id INT(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (id),
		  INDEX (journal_main_id, inventory_history_id) 
		  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT = 'Relates inventory usage with inventory purchase history'");
	}

	if (!mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TABLE_INVENTORY_MS_LIST . "'"))) {
		$db->Execute("CREATE TABLE " . TABLE_INVENTORY_MS_LIST . " (
		  id int(11) NOT NULL auto_increment,
		  sku varchar(15) NOT NULL DEFAULT '',
		  attr_name_0 varchar(16) NULL,
		  attr_name_1 varchar(16) NULL,
		  attr_0 varchar(255) NULL,
		  attr_1 varchar(255) NULL, 
		  PRIMARY KEY (id)
		  ) ENGINE=InnoDB CHARSET=latin1 COMMENT = 'Holds inventory master stock attribute information';");
	}

	if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TABLE_ADDRESS_FORMAT . "'"))) {
		$db->Execute("DROP TABLE " . TABLE_ADDRESS_FORMAT);
	}

	if (!defined('ADDRESS_BOOK_EMAIL_REQUIRED')) {
		$db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) 
			VALUES ('Account Telephone 1 Field Required', 'ADDRESS_BOOK_TELEPHONE1_REQUIRED', '0', 'Whether or not to require telephone 1 field to be entered in accounts setup (vendors, customers, employees)', '7', '8', NULL , '2007-10-04 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =&gt;''false'', 1=&gt; ''true''),'), 
				   ('Account Email Address Field Required', 'ADDRESS_BOOK_EMAIL_REQUIRED', '0', 'Whether or not to require the email address field to be entered in accounts setup (vendors, customers, employees)', '7', '9', NULL , '2006-10-04 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =&gt;''false'', 1=&gt; ''true''),');");
	}

	$fields = mysql_list_fields(DB_DATABASE, TABLE_ZONES);
	$columns = mysql_num_fields($fields);
	$field_array = array();
	for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
	if (in_array('countries_iso_code_2', $field_array)) {
		$db->Execute("ALTER TABLE " . TABLE_ZONES . " DROP countries_iso_code_2, DROP zone_country_id");
		$db->Execute("ALTER TABLE " . TABLE_ZONES . " DROP INDEX countries_iso_code_2");
	}

	$db->Execute("ALTER TABLE " . TABLE_INVENTORY_ASSY_LIST . " ENGINE = innodb");

    // update the database version information
	update_version_db();
	$db_release = '1.2';
    if ($code_release == $db_release) return; // end script db is same level as code
  }
  // End Release 1.1 to 1.2
  
  // Release 1.2 to 1.2.1
  if ($db_release == '1.2') { // go to Release 1.2.1
	// there are no database changes in this release

    // update the database version information
	update_version_db();
	$db_release = '1.2.1';
    if ($code_release == $db_release) return; // end script db is same level as code
  }
  // End Release 1.2 to 1.2.1
  
  // Release 1.2.1 to 1.3
  if ($db_release == '1.2.1') { // go to Release 1.3
	// reset the configuration variables that were not set properly in R1.2
	$db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " set configuration_group_id = '20' 
		where configuration_group_title = 'Website Maintenance'");
	$db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " set configuration_group_id = '19' 
		where configuration_group_title = 'Layout Settings'");
	$db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " set configuration_group_id = '15' 
		where configuration_group_title = 'Sessions'");
	$db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " set configuration_group_id = '13' 
		where configuration_group_title = 'General Ledger Defaults'");
	$db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " set configuration_group_id = '6' 
		where configuration_group_title = 'Module Defaults'");
	$db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " set configuration_group_id = '17' 
		where configuration_group_title = 'Credit Cards'");

	// change the length of the description of table reports for language translations
	$db->Execute("ALTER TABLE " . TABLE_REPORTS . " CHANGE `description` `description` 
		VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

	// change the charset to utf8 for all the tables
	$result = $db->Execute("show tables");
	while (!$result->EOF) {
		$tablename = array_shift($result->fields);
		$db->Execute("ALTER TABLE " . $tablename . " DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$result->MoveNext();
	}

    // update the database version information
	update_version_db();
	$db_release = '1.3';
    if ($code_release == $db_release) return; // end script db is same level as code
  }

  // Release 1.3 to 1.3.1
  if ($db_release == '1.3') { // go to Release 1.3.1

	if (!mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TABLE_ACCOUNTS_NOTES . "'"))) {
	  $db->Execute("CREATE TABLE " . TABLE_ACCOUNTS_NOTES . " (
	    id int(11) NOT NULL auto_increment,
	    notes text,
	    PRIMARY KEY (id)
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds account custom notes for accounts' ;");
	}

	$db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " 
		CHANGE next_po_num next_po_num VARCHAR( 16 ) NOT NULL DEFAULT '1',
		CHANGE next_inv_num next_inv_num VARCHAR( 16 ) NOT NULL DEFAULT '1',
		CHANGE next_check_num next_check_num VARCHAR( 16 ) NOT NULL DEFAULT '1'");

    // update the database version information
	update_version_db();
	$db_release = '1.3.1';
    if ($code_release == $db_release) return; // end script db is same level as code
  }

// The rest of this script is the last beta release updates ******************************

if (!defined('ENABLE_MULTI_CURRENCY')) {
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Enable Multi-Currency Displays', 'ENABLE_MULTI_CURRENCY', '0', 'Enable multiple currencies in user entry screens.<br />If No is selected, only the default currency wil be used.', '1', '19', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Enable Multi-Branch Displays', 'ENABLE_MULTI_BRANCH', '0', 'Enable multiple branch functionality.<br />If No is selected, only one company location will be assumed.', '1', '18', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' );");
}

$fields = mysql_list_fields(DB_DATABASE, TABLE_JOURNAL_MAIN);
$columns = mysql_num_fields($fields);
$field_array[] = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('currencies_code', $field_array)) {
  $db->Execute("ALTER TABLE " . TABLE_JOURNAL_MAIN . " ADD `currencies_code` CHAR(3) NOT NULL DEFAULT '" . DEFAULT_CURRENCY . "' AFTER `total_amount` ,
    ADD `currencies_value` DOUBLE NOT NULL DEFAULT '1.0' AFTER `currencies_code` ;");
}

// add a new field to the current status table
$fields = mysql_list_fields(DB_DATABASE, TABLE_CURRENT_STATUS);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('next_vcm_num', $field_array)) {
  $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_vcm_num VARCHAR(16) NOT NULL AFTER next_cm_num");
  $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_vcm_num = 'VCM1000'");
}

if (!defined('HIDE_SUCCESS_MESSAGES')) {
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Hide Success Messages', 'HIDE_SUCCESS_MESSAGES', '0', 'Hides messages on successful operations.<br />Only caution and error messages will be displayed.', '8', '5', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Show Full GL Account Names', 'SHOW_FULL_GL_NAMES', '1', 'Determines how to display the general ledger accounts in pull-down menus.<br />Number - GL Account Number Only.<br />Description - GL Account Description Only.<br />Both - Both gl number and name will be displayed.', '13', '5', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'Number\', 1=>\'Description\', 2=>\'Both\'),' ),
           ( 'Auto Update Currency', 'AUTO_UPDATE_CURRENCY', '1', 'Updates the exchange rate for loaded currencies at every login.<br />If disabled, currencies may be manually updated in the Setup => Currencies menu.', '8', '7', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' );");
}

if (!defined('AUTO_UPDATE_PERIOD')) {
  $db->Execute('delete from ' . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_LANGUAGE'");
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Auto-change Accounting Period', 'AUTO_UPDATE_PERIOD', '1', 'Automatically changes the current accounting period based on the server date and current fiscal calendar. If not enabled, the current accounting period must be manually changed in the General Ledger => Utilities menu.', '13', '1', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 => \'No\', 1=> \'Yes\'),' ),
           ( 'Add Tax to Customer Shipping Charges', 'AR_ADD_SALES_TAX_TO_SHIPPING', '0', 'If enabled, shipping charges will be added to the calculation of sales tax. If not enabled, shipping will not be taxed.', '2', '30', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 => \'No\', 1=> \'Yes\'),' ),
           ( 'Add Tax to Vendor Shipping Charges', 'AP_ADD_SALES_TAX_TO_SHIPPING', '0', 'If enabled, shipping charges will be added to the calculation of sales tax. If not enabled, shipping will not be taxed.', '3', '30', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 => \'No\', 1=> \'Yes\'),' );");
}

$db->Execute("ALTER TABLE " . TABLE_RECONCILIATION . " CHANGE `statement_balance` `statement_balance` DOUBLE NOT NULL DEFAULT '0'");

if (!defined('ENABLE_SHIPPING_FUNCTIONS')) {
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_CONTACT_REQUIRED'");
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_ADDRESS1_REQUIRED'");
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_ADDRESS2_REQUIRED'");
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_CITY_TOWN_REQUIRED'");
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_STATE_PROVINCE_REQUIRED'");
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_POSTAL_CODE_REQUIRED'");
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_EMAIL_REQUIRED'");
  $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = '11' WHERE configuration_key = 'ADDRESS_BOOK_TELEPHONE1_REQUIRED'");
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Shipping Address1 Field Required', 'ADDRESS_BOOK_SHIP_ADD1_REQ', '1', 'Whether or not to require address 1 field to be entered in shipping fields.', '11', '10', NULL , '2008-02-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Shipping Address2 Field Required', 'ADDRESS_BOOK_SHIP_ADD2_REQ', '0', 'Whether or not to require address 1 field to be entered in shipping fields.', '11', '11', NULL , '2008-02-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Shipping Contact Field Required', 'ADDRESS_BOOK_SHIP_CONTACT_REQ', '0', 'Whether or not to require the contact field to be entered in shipping fields.', '11', '12', NULL , '2008-02-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Shipping City/Town Field Required', 'ADDRESS_BOOK_SHIP_CITY_REQ', '1', 'Whether or not to require the city/town field to be entered in shipping fields.', '11', '13', NULL , '2008-02-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Shipping State/Province Field Required', 'ADDRESS_BOOK_SHIP_STATE_REQ', '1', 'Whether or not to require the state/province field to be entered in shipping fields.', '11', '14', NULL , '2008-02-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Shipping Postal Code Field Required', 'ADDRESS_BOOK_SHIP_POSTAL_CODE_REQ', '1', 'Whether or not to require the postal code field to be entered in shipping fields.', '11', '15', NULL , '2008-02-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Enable Shipping Functions', 'ENABLE_SHIPPING_FUNCTIONS', '1', 'Whether or not to enable the shipping functions and shipping fields.', '1', '25', NULL , '2008-02-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' );");
  $db->Execute("ALTER TABLE " . TABLE_ADDRESS_BOOK . " ADD `telephone1` VARCHAR( 20 ) NULL DEFAULT '' AFTER `country_code` ,
		ADD `telephone2` VARCHAR( 20 ) NULL DEFAULT '' AFTER `telephone1` ,
		ADD `telephone3` VARCHAR( 20 ) NULL DEFAULT '' AFTER `telephone2` ,
		ADD `telephone4` VARCHAR( 20 ) NULL DEFAULT '' AFTER `telephone3` ,
		ADD `email` VARCHAR( 48 ) NULL DEFAULT '' AFTER `telephone4` ,
		ADD `website` VARCHAR( 48 ) NULL DEFAULT '' AFTER `email`");
  $db->Execute("ALTER TABLE " . TABLE_ADDRESS_BOOK . " CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT ''");
  $db->Execute("ALTER TABLE " . TABLE_ACCOUNTS_HISTORY . " CHANGE `purchase_invoice_id` `purchase_invoice_id` VARCHAR( 24 ) NULL DEFAULT ''");
  $db->Execute("ALTER TABLE " . TABLE_ACCOUNTS_HISTORY . " CHANGE `journal_id` `journal_id` INT(2) NOT NULL DEFAULT '0'");

}

$fields = mysql_list_fields(DB_DATABASE, TABLE_ACCOUNTS);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (in_array('telephone1', $field_array)) {
  $sql = "select c.id, c.telephone1, c.telephone2, c.fax, c.email, c.website, a.address_id 
		from " . TABLE_ACCOUNTS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.id = a.ref_id 
		where a.type like '%m'";
  $result = $db->Execute($sql);
  if ($result->RecordCount() > 0) {
	while(!$result->EOF) {
	  $db->Execute("update " . TABLE_ADDRESS_BOOK . " set 
		  telephone1 = '" . $result->fields['telephone1'] . "', 
		  telephone2 = '" . $result->fields['telephone2'] . "', 
		  telephone3 = '" . $result->fields['fax'] . "', 
		  email      = '" . $result->fields['email'] . "', 
		  website    = '" . $result->fields['website'] . "' 
		where address_id = " . $result->fields['address_id']);
	  $result->MoveNext();
	}
  }
  $db->Execute("ALTER TABLE " . TABLE_ACCOUNTS . " DROP `telephone1` ,
	DROP `telephone2` , DROP `fax` , DROP `email` , DROP `website` ;");

  $sql = "select id, notes from " . TABLE_ACCOUNTS_NOTES;
  $result = $db->Execute($sql);
  while(!$result->EOF) {
	$db->Execute("update " . TABLE_ADDRESS_BOOK . " set 
		notes = '" . $result->fields['notes'] . "' 
		where ref_id = " . $result->fields['address_id'] . " and type like '%m'");
	$result->MoveNext();
  }
  $db->Execute("DROP TABLE " . TABLE_ACCOUNTS_NOTES);
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Enable Encryption of Information', 'ENABLE_ENCRYPTION', '0', 'Whether or not allow storage of encrypted fields.', '1', '30', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Encrypted Encryption value', 'ENCRYPTION_VALUE', '0', 'Encrypted key value.', '99', '2', NULL , now(), NULL , NULL );");

  $db->Execute("CREATE TABLE " . TABLE_DATA_SECURITY . " (
    id int(11) NOT NULL auto_increment,
    module varchar(32) NOT NULL DEFAULT '',
    ref_1 int(11) NOT NULL DEFAULT '0',
    ref_2 int(11) NOT NULL DEFAULT '0',
    hint varchar(255) NOT NULL DEFAULT '',
    enc_value varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Encryption data for storage';");


  $db->Execute("ALTER TABLE " . TABLE_JOURNAL_MAIN . " ADD bill_telephone1 VARCHAR(20) NULL AFTER bill_country_code,
    ADD bill_email VARCHAR(48) NULL AFTER bill_telephone1");
  $db->Execute("ALTER TABLE " . TABLE_JOURNAL_MAIN . " ADD ship_telephone1 VARCHAR(20) NULL AFTER ship_country_code ,
    ADD ship_email VARCHAR(48) NULL AFTER ship_telephone1");

}

// convert the reports to new format and modify db
$fields = mysql_list_fields(DB_DATABASE, TABLE_REPORTS);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (in_array('papersize', $field_array)) {
	$result = $db->Execute("select * from " . TABLE_REPORTS);
	while (!$result->EOF) {
		$data_array['narrative']        = $result->fields['narrative'];
		$data_array['papersize']        = $result->fields['papersize'];
		$data_array['paperorientation'] = $result->fields['paperorientation'];
		$data_array['margintop']        = $result->fields['margintop'];
		$data_array['marginbottom']     = $result->fields['marginbottom'];
		$data_array['marginleft']       = $result->fields['marginleft'];
		$data_array['marginright']      = $result->fields['marginright'];
		$data_array['coynamefont']      = $result->fields['coynamefont'];
		$data_array['coynamefontsize']  = $result->fields['coynamefontsize'];
		$data_array['coynamefontcolor'] = $result->fields['coynamefontcolor'];
		$data_array['coynamealign']     = $result->fields['coynamealign'];
		$data_array['coynameshow']      = $result->fields['coynameshow'];
		$data_array['title1desc']       = $result->fields['title1desc'];
		$data_array['title1font']       = $result->fields['title1font'];
		$data_array['title1fontsize']   = $result->fields['title1fontsize'];
		$data_array['title1fontcolor']  = $result->fields['title1fontcolor'];
		$data_array['title1fontalign']  = $result->fields['title1fontalign'];
		$data_array['title1show']       = $result->fields['title1show'];
		$data_array['title2desc']       = $result->fields['title2desc'];
		$data_array['title2font ']      = $result->fields['title2font'];
		$data_array['title2fontsize']   = $result->fields['title2fontsize'];
		$data_array['title2fontcolor']  = $result->fields['title2fontcolor'];
		$data_array['title2fontalign']  = $result->fields['title2fontalign'];
		$data_array['title2show']       = $result->fields['title2show'];
		$data_array['filterfont']       = $result->fields['filterfont'];
		$data_array['filterfontsize']   = $result->fields['filterfontsize'];
		$data_array['filterfontcolor']  = $result->fields['filterfontcolor'];
		$data_array['filterfontalign']  = $result->fields['filterfontalign'];
		$data_array['datafont']         = $result->fields['datafont'];
		$data_array['datafontsize']     = $result->fields['datafontsize'];
		$data_array['datafontcolor']    = $result->fields['datafontcolor'];
		$data_array['datafontalign']    = $result->fields['datafontalign'];
		$data_array['totalsfont']       = $result->fields['totalsfont'];
		$data_array['totalsfontsize']   = $result->fields['totalsfontsize'];
		$data_array['totalsfontcolor']  = $result->fields['totalsfontcolor'];
		$data_array['totalsfontalign']  = $result->fields['totalsfontalign'];
		$db->Execute("insert into " . TABLE_REPORT_FIELDS . " set 
			reportid = " . $result->fields['id'] . ", 
			entrytype = 'pagelist',
			params = '" . serialize($data_array) . "'");
		$result->MoveNext();
	}

    $db->Execute("ALTER TABLE " . TABLE_REPORTS . " DROP `narrative` ,
		DROP `col1width` , DROP `col2width` , DROP `col3width` , DROP `col4width` , 
		DROP `col5width` , DROP `col6width` , DROP `col7width` , DROP `col8width` , 
		DROP `papersize` , DROP `paperorientation` , 
		DROP `margintop` , DROP `marginbottom` , DROP `marginleft` , DROP `marginright` , 
		DROP `coynamefont` , DROP `coynamefontsize` , DROP `coynamefontcolor` , DROP `coynamealign` , DROP `coynameshow` , 
		DROP `title1desc` , DROP `title1font` , DROP `title1fontsize` , DROP `title1fontcolor` , DROP `title1fontalign` , DROP `title1show` ,
		DROP `title2desc` , DROP `title2font` , DROP `title2fontsize` , DROP `title2fontcolor` , DROP `title2fontalign` , DROP `title2show` ,
		DROP `filterfont` , DROP `filterfontsize` , DROP `filterfontcolor` , DROP `filterfontalign` , 
		DROP `datafont` ,   DROP `datafontsize` ,   DROP `datafontcolor` , DROP `datafontalign` , 
		DROP `totalsfont` , DROP `totalsfontsize` , DROP `totalsfontcolor` , DROP `totalsfontalign` ;");
}

if (!defined('DEBUG')) {
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . " 
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) 
    VALUES ( 'Enable Debug Trace File Generation', 'DEBUG', '0', 'Enable trace generation for debug purposes.<br />If Yes is selected, an additional menu will be added to the Tools menu to download the trace information to help debug posting problems.', '20', '99', NULL , '2008-01-21 00:00:00', NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' );");
}

if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TABLE_ACCOUNTS . "'"))) {
  $db->Execute("RENAME TABLE " . TABLE_ACCOUNTS . " TO " . TABLE_CONTACTS . ";");
}

// fields added for branch support
$fields = mysql_list_fields(DB_DATABASE, TABLE_INVENTORY_HISTORY);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
//if (!in_array('branch_id', $field_array)) {
if (false) {
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY   . " ADD store_id INT(11) NOT NULL DEFAULT '0' AFTER ref_id");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY   . " ADD INDEX (store_id)");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_COGS_OWED . " ADD store_id INT(11) NOT NULL DEFAULT '0' AFTER journal_main_id");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_COGS_OWED . " ADD INDEX (store_id)");
}
?>