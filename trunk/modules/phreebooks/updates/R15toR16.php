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
//  Path: /modules/phreebooks/updates/R15toR16.php
//

// This script updates Release 1.5 to Release 1.6, it is included as part of the updater script

// *************************** IMPORTANT *********************************//

// This release changes a field name in the journal main table which will break some reports and forms.
// This script automatically fixes the reports but the forms must be altered manually or re-imported
// from the installed default forms. Specifically, the field `dept_rep_id` needs to be changed to `rep_id`
// for seven default forms (Customer Quotes, Invoice/Packing Slip, Invoice, Packing Slip, Purchase Order,
// Request For Quote, and Sales Order) and any custom or add-on forms that used this field.
// In the above forms, the field is named `Sales Rep` for receivables and `Buyer` for payables.

//************************* END OF IMPORTANT *****************************//

// Release 1.5 to 1.6

// change the name of the field dept_rep_id to admin_id, add rep_id field
$fields = mysql_list_fields(DB_DATABASE, TABLE_JOURNAL_MAIN);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('admin_id', $field_array)) {
  $admin->DataBase->query("ALTER TABLE " . TABLE_JOURNAL_MAIN . " CHANGE dept_rep_id admin_id INT( 11 ) NOT NULL DEFAULT '0'");
  $admin->DataBase->query("ALTER TABLE " . TABLE_JOURNAL_MAIN . " ADD rep_id INT( 11 ) NOT NULL DEFAULT '0' AFTER admin_id");
  $admin->DataBase->query("ALTER TABLE " . TABLE_JOURNAL_ITEM . " ADD full_price DOUBLE NOT NULL DEFAULT '0' AFTER taxable");
  $admin->DataBase->query("ALTER TABLE " . TABLE_CONTACTS . " ADD price_sheet VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER special_terms");

  // fix the reports (forms must be re-imported or changed manually because the field name is serialized)
  $result = $admin->DataBase->query("select id, fieldname from " . TABLE_REPORT_FIELDS . " where fieldname like '%.dept_rep_id'");
  while (!$result->EOF) {
    $newfield = str_replace('dept_rep_id', 'rep_id', $result->fields['fieldname']);
	$admin->DataBase->query("update " . TABLE_REPORT_FIELDS . " set fieldname = '" . $newfield . "' where id = " . $result->fields['id']);
	$result->MoveNext();
  }
}

if (!defined('AR_SHOW_CONTACT_STATUS')) {
  // convert employee type set to 'b' (Both) to type 'es' (Employee, Sales) in table contacts
  $admin->DataBase->query("update " . TABLE_CONTACTS . " set gl_type_account = 'es' where type = 'e' and gl_type_account = 'b'");
  $admin->DataBase->query("INSERT INTO " .  TABLE_CONFIGURATION . "
           ( `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
    VALUES ( 'Show popup with customer account status on order screens', 'AR_SHOW_CONTACT_STATUS', '0', 'This feature displays a customer status popup on the order screens when a customer is selected from the contact search popup. It displays balances, account aging as well as the active status of the account.', '2', '40', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' ),
           ( 'Show popup with vendor account status on order screens', 'AP_SHOW_CONTACT_STATUS', '0', 'This feature displays a vendor status popup on the order screens when a vendor is selected from the contact search popup. It displays balances, account aging as well as the active status of the account.', '3', '40', NULL , now(), NULL , 'cfg_keyed_select_option(array(0 =>\'No\', 1=>\'Yes\'),' );");
  // convert the contacts sales account links from admin id's to employee contact id's
  $result = $admin->DataBase->query("select admin_id, account_id from " . TABLE_USERS);
  while (!$result->EOF) {
    if ($result->fields['account_id'] > 0) {
	  $admin->DataBase->query("update " . TABLE_CONTACTS . " set dept_rep_id = " . $result->fields['account_id'] . "
	    where dept_rep_id = " . $result->fields['admin_id']);
	}
    $result->MoveNext();
  }
  $admin->DataBase->query("ALTER TABLE " . TABLE_JOURNAL_MAIN . " CHANGE shipper_code shipper_code VARCHAR( 20 ) NOT NULL DEFAULT ':'");
}

$fields = mysql_list_fields(DB_DATABASE, TABLE_TAX_RATES);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('type', $field_array)) {
  $admin->DataBase->query("ALTER TABLE " . TABLE_TAX_RATES . " ADD type VARCHAR(1) NOT NULL DEFAULT 'c' AFTER tax_rate_id");
  $admin->DataBase->query("ALTER TABLE " . TABLE_TAX_AUTH  . " ADD type VARCHAR(1) NOT NULL DEFAULT 'c' AFTER tax_auth_id");
}

$dest_dir = DIR_FS_MY_FILES . 'backups';
validate_path($dest_dir);

?>