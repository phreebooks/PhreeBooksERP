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
//  Path: /modules/inventory/pages/popup_inv/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
require(DIR_FS_WORKING . 'defaults.php');
require(DIR_FS_WORKING . 'functions/inventory.php');
/**************   page specific initialization  *************************/
$account_type = isset($_GET['type'])    ? $_GET['type']    : 'c';	// current types are c (customer) and v (vendor)
$rowID        = isset($_GET['rowID'])   ? $_GET['rowID']   : 0;
$store_id     = isset($_GET['storeID']) ? $_GET['storeID'] : 0;
$contactID    = isset($_GET['cID'])     ? $_GET['cID']     : 0;
$assembly     = isset($_GET['asy'])     ? true             : false;
// load the filters
$f0 = isset($_REQUEST['f0']) ? $_REQUEST['f0'] : ''; // show inactive checkbox
$f1 = isset($_REQUEST['f1']) ? $_REQUEST['f1'] : ''; // inventory_type dropdown
$f2 = isset($_REQUEST['f2']) ? $_REQUEST['f2'] : ''; // limit to preferred_vendor checkbox
// save the filters for page jumps
$_GET['f0'] = $f0;
$_GET['f1'] = $f1;
$_GET['f2'] = $f2;
history_filter('inv_popup');
switch ($account_type) {
  default:
  case 'c': $terms_type = 'AR'; break;
  case 'v': $terms_type = 'AP';
}
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_inv/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
// build the type filter list
$type_select_list = array( // add some extra options
  array('id' => '0',   'text' => TEXT_ALL),
  array('id' => 'cog', 'text' => TEXT_INV_MANAGED),
);
foreach ($inventory_types_plus as $key => $value) $type_select_list[] = array('id' => $key,  'text' => $value);

// build the list header
$heading_array = array(
  'a.sku'               => TEXT_SKU,
  'a.description_short' => TEXT_DESCRIPTION,
  'a.full_price'        => ($account_type == 'v') ? INV_ENTRY_INV_ITEM_COST : INV_ENTRY_FULL_PRICE,
  'a.quantity_on_hand'  => INV_HEADING_QTY_ON_HAND,
  'a.quantity_on_order' => INV_HEADING_QTY_ON_ORDER,
);
$extras      = (ENABLE_MULTI_BRANCH) ? array(TEXT_QTY_THIS_STORE) : array();
$result      = html_heading_bar($heading_array, $extras);
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];

// build the list for the page selected
$criteria = array();
if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
  $search_fields = array('a.sku', 'a.description_short', 'p.description_purchase', 'description_sales');
  // hook for inserting new search fields to the query criteria.
  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  $criteria[] = '(' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
}
if (!$f0) $criteria[] = "inactive = '0'"; // inactive flag
if ($f1) { // sort by inventory type
  switch ($f1) {
    case 'cog': 
	  $cog_types = explode(',',COG_ITEM_TYPES);
	  $criteria[] = "a.inventory_type in ('" . implode("','", $cog_types) . "')"; break;
	default:    $criteria[] = "a.inventory_type = '$f1'";                     break;
  }
}
if ($f2 && $contactID) $criteria[] = "p.vendor_id = " . $contactID; // limit to preferred vendor flag
// build search filter string
$search = (sizeof($criteria) > 0) ? (' where ' . implode(' and ', $criteria)) : '';

$field_list = array('a.id as id', 'a.sku as sku', 'inactive', 'inventory_type', 'quantity_on_hand', 'quantity_on_order', 
  'description_short');

// hook to add new fields to the query return results
if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);

$query_raw = "select SQL_CALC_FOUND_ROWS DISTINCT " . implode(', ', $field_list)  . " from " . TABLE_INVENTORY ."  a LEFT OUTER JOIN " . TABLE_INVENTORY_PURCHASE . " p on a.sku = p.sku " . $search . " order by $disp_order";
$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
// the splitPageResults should be run directly after the query that contains SQL_CALC_FOUND_ROWS
$query_split  = new splitPageResults($_REQUEST['list'], '');
if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
	$_REQUEST['list'] = $query_split->current_page_number;
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
	$query_split      = new splitPageResults($_REQUEST['list'], '');
}
history_save('inv_popup');

// check for auto close (if auto fill is turned on and only one result is found, the data will already be there)
$auto_close = (INVENTORY_AUTO_FILL && $query_result->RecordCount() == 1 && $_REQUEST['list'] == 1) ? true : false;
$auto_close = false; // disable until all modules that use this function are ajax compliant

$include_header   = false;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', INV_POPUP_WINDOW_TITLE);

?>