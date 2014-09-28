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
//  Path: /modules/phreebooks/updates/R16toR17.php
//

// This script updates Release 1.6 to Release 1.7, it is included as part of the updater script

// *************************** IMPORTANT *********************************//


//************************* END OF IMPORTANT *****************************//

// Release 1.6 to 1.7
// may have been left out if svn update from 1.3.1 to 1.4 fro final release
$fields = mysql_list_fields(DB_DATABASE, TABLE_INVENTORY_HISTORY);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('store_id', $field_array)) {
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY   . " ADD store_id INT(11) NOT NULL DEFAULT '0' AFTER ref_id");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY   . " ADD INDEX (store_id)");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_COGS_OWED . " ADD store_id INT(11) NOT NULL DEFAULT '0' AFTER journal_main_id");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_COGS_OWED . " ADD INDEX (store_id)");
}

// to fix bug in R1.6 new installs where this field was left out
$fields = mysql_list_fields(DB_DATABASE, TABLE_TAX_AUTH);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('type', $field_array)) {
  $db->Execute("ALTER TABLE " . TABLE_TAX_AUTH  . " ADD type VARCHAR(1) NOT NULL DEFAULT 'c' AFTER tax_auth_id");
}

$fields = mysql_list_fields(DB_DATABASE, TABLE_INVENTORY_HISTORY);
$columns = mysql_num_fields($fields);
$field_array = array();
for ($i = 0; $i < $columns; $i++) $field_array[] = mysql_field_name($fields, $i);
if (!in_array('journal_id', $field_array)) {
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY  . " ADD journal_id INT(2) NOT NULL DEFAULT '6' AFTER store_id");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY  . " ADD INDEX (journal_id)");
}

if (!defined('ENABLE_BAR_CODE_READERS')) {
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD upc_code varchar(13) NOT NULL DEFAULT '' AFTER lead_time");
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
           ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'Enable bar code readers', 'ENABLE_BAR_CODE_READERS', '0', 'If set to true, this option will enable data entry on order forms for USB and supported bar code readers.', '1', '55', NULL , now(), NULL , 'cfg_keyed_select_option(array(1=>\'Yes\', 0=>\'No\'),' );");
}

if (!defined('AUTO_INC_CUST_ID')) {
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
           ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'Auto Increment Customer ID', 'AUTO_INC_CUST_ID', '0', 'If set to true, this option will automatically assign an ID to new customers when they are created.', '2', '35', NULL , now(), NULL , 'cfg_keyed_select_option(array(1=>\'Yes\', 0=>\'No\'),' );");
  $db->Execute("alter table " . TABLE_CURRENT_STATUS . " add next_cust_id_num VARCHAR(16) NOT NULL");
  $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_cust_id_num = 'C10000'");
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
           ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'Auto Increment Vendor ID', 'AUTO_INC_VEND_ID', '0', 'If set to true, this option will automatically assign an ID to new vendors when they are created.', '3', '35', NULL , now(), NULL , 'cfg_keyed_select_option(array(1=>\'Yes\', 0=>\'No\'),' );");
  $db->Execute("alter table " . TABLE_CURRENT_STATUS . " add next_vend_id_num VARCHAR(16) NOT NULL");
  $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_vend_id_num = 'V10000'");
  // increase SKU length to 24 characters
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY .           " CHANGE sku sku VARCHAR(24) NOT NULL DEFAULT ''");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_COGS_OWED . " CHANGE sku sku VARCHAR(24) NOT NULL DEFAULT ''");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_HISTORY .   " CHANGE sku sku VARCHAR(24) NOT NULL DEFAULT ''");
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY_MS_LIST .   " CHANGE sku sku VARCHAR(24) NOT NULL DEFAULT ''");
  $db->Execute("ALTER TABLE " . TABLE_JOURNAL_ITEM .        " CHANGE sku sku VARCHAR(24) DEFAULT NULL");
  // add inventory field for sales description
  $db->Execute("ALTER TABLE " . TABLE_INVENTORY . " ADD description_sales VARCHAR(255) NULL AFTER description_purchase");
  $db->Execute("UPDATE " . TABLE_INVENTORY . " SET description_sales = description_short");
  // add index for faster queries on order popups
  $db->Execute("ALTER TABLE " . TABLE_JOURNAL_ITEM . " ADD INDEX (so_po_item_ref_id)");
}

if (!defined('LIMIT_HISTORY_RESULTS')) {
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
          ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'Limit Customer/Vendor History Results', 'LIMIT_HISTORY_RESULTS', '20', 'Limits the length of history values shown in customer/vendor accounts for sales/purchases.', '8', '10', NULL , now(), NULL , '' );");
}

?>