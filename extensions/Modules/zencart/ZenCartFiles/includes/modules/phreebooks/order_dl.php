<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /admin/includes/modules/phreebooks/order_dl.php

class order_dl extends base {
  var $code;
  var $title;
  var $description;
  var $icon;
  var $enabled;

  // class constructor
  function order_dl() {
    $this->code        = 'order_dl';
    $this->title       = MODULE_PHREEBOOKS_ORDER_DL_TEXT_TITLE;
    $this->description = MODULE_PHREEBOOKS_ORDER_DL_TEXT_DESCRIPTION;
    $this->sort_order  = $this->check();
    $this->icon        = '';
    $this->enabled     = (MODULE_PHREEBOOKS_ORDER_DOWNLOAD_STATUS == 'True') ? true : false;
  }

  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Order Download Operation', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_STATUS', 'False', 'Do you want enable the order downlaod interface to PhreeBooks?', '99', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('PhreeBooks XML URL', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_URL', 'https://', 'The URL of the PhreeBooks server to process order downloads (can be secure or non-secure).', '99', '1', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('PhreeBooks Database Name', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_DB', '', 'The name of the database (company) to send the order to.', '99', '2', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('PhreeBooks ISO Language', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_LANG', 'en_us', 'The PhreeBooks language to use in response messages for error reporting. Leave blank for default (en_us).', '99', '3', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('PhreeBooks Access Username', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_USER', '', 'The username in PhreeBooks to accept downloads (must be set-up in PhreeBooks to match).', '99', '4', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('PhreeBooks Access Password', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PW', '', 'The password in PhreeBooks to accept downloads (must be set-up in PhreeBooks to match).', '99', '5', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Order Prefix', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PREFIX', 'zc', 'This is a prefix to append to the beginning of the order number to avoid duplicates between the Zencart order number and the PhreeBooks order number. PhreeBooks will not allow downloads of ZenCart orders if the order number is in the PhreeBooks database already.', '99', '6', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Store ID', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_STORE_ID', '', 'Specify the store ID to use to record ZenCart sales. It must match a branch ID in PhreeBooks to post properly. If left blank, the store ID will be left blank in PhreeBooks.', '99', '7', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sales Rep ID', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_SALES_REP_ID', '', 'Specify the sales rep name to use for sales reporting. It must match the user ID of an employee/sales rep in the PhreeBooks database to be valid. If left blank, the sales rep fields will be left blank in PhreeBooks.', '99', '8', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('GL Sales Account', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_SALES_GL_ACCOUNT', '', 'The general ledger account to used to record sales. This account must exist in PhreeBooks for the download to work properly. If left blank, the default PhreeBooks Sales account will be used.', '99', '9', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('GL Receivables Account', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_AR_GL_ACCOUNT', '', 'The general ledger account to used for accounts receivable. This account must exist in PhreeBooks for the download to work properly. If left blank, the default PhreeBooks accounts receivable account will be used.', '99', '10', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Update Status on Download', 'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_ORDER_STATUS', '', 'The customers order status can be changed to the following value after the order has been successfully downloaded.', '99', '10', 'zen_cfg_pull_down_order_statuses(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Customer ID Generator', 'PHREEBOOKS_DOWNLOAD_USER_ID_METHOD', 'Email', 'PhreeBooks uses Custoemr IDs to stroe customer accounts. When an order is downloaded, the Id is used to process the transaction. Select the customer data source to be used to generate PhreeBooks IDs.', '99', '11', 'zen_cfg_select_option(array(\'Email\', \'Telephone\'), ', now())");

	// Add the phreebooks field to the table orders
	$result = $db->Execute("describe " . TABLE_ORDERS);
	$field_exists = false;
	while(!$result->EOF) {
		if ($result->fields['Field'] == 'phreebooks') { $field_exists = true; break; }
		$result->MoveNext();
	}
	if (!$field_exists) $db->Execute("alter table " . TABLE_ORDERS . " add phreebooks tinyint(1) default 0 not null");
	// Add the phreebooks_sku field to the table products
	$result = $db->Execute("describe " . TABLE_PRODUCTS);
	$field_exists = false;
	while(!$result->EOF) {
	  if ($result->fields['Field'] == 'phreebooks_sku') { $field_exists = true; break; }
	  $result->MoveNext();
	}
	if (!$field_exists) $db->Execute("alter table " . TABLE_PRODUCTS . " add phreebooks_sku varchar(24) not null");
  }

  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    return array(
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_STATUS',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_URL',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_DB',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_LANG',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_USER',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PW',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_PREFIX',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_STORE_ID',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_SALES_REP_ID',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_SALES_GL_ACCOUNT',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_AR_GL_ACCOUNT',
	  'MODULE_PHREEBOOKS_ORDER_DOWNLOAD_ORDER_STATUS',
	  'PHREEBOOKS_DOWNLOAD_USER_ID_METHOD',
	);
  }
}
?>