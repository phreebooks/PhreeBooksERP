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
//  Path: /modules/inventory/pages/admin/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreedom', 'admin');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'functions/inventory.php');
/**************   page specific initialization  *************************/
$cog_type = explode(',', COG_ITEM_TYPES);
$tabs     = new \inventory\classes\tabs();
$fields   = new \inventory\classes\fields();

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	\core\classes\user::validate_security($security_level, 3); // security check
	// save general tab
	foreach ($admin_classes['inventory']->keys as $key => $default) {
	  $field = strtolower($key);
      if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
    }
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	$messageStack->add(INVENTORY_CONFIG_SAVED,'success');
    break;
  case 'delete':
	\core\classes\user::validate_security($security_level, 4); // security check
    $subject = $_POST['subject'];
    $id      = $_POST['rowSeq'];
	if (!$subject || !$id) break;
    $$subject->btn_delete($id);
	break;
  case 'inv_hist_test':
  case 'inv_hist_fix':
	\core\classes\user::validate_security($security_level, 3); // security check
	$result = $db->Execute("select sku, qty from " . TABLE_INVENTORY_COGS_OWED);
	$owed = array();
	while (!$result->EOF) {
	  $owed[$result->fields['sku']] += $result->fields['qty'];
	  $result->MoveNext();
	}
	// fetch the inventory items that we track COGS and get qty on hand
	$result = $db->Execute("select sku, quantity_on_hand from " . TABLE_INVENTORY . "
	  where inventory_type in ('" . implode("', '", $cog_type) . "') order by sku");
	// for each item, find the history remaining Qty's
	$cnt = 0;
	$repair = array();
	while (!$result->EOF) {
	  // check for quantity on hand not rounded properly
	  $on_hand = round($result->fields['quantity_on_hand'], $currencies->currencies[DEFAULT_CURRENCY]['decimal_precise']);
	  if ($on_hand <> $result->fields['quantity_on_hand']) {
	    $repair[$result->fields['sku']] = $on_hand;
		if ($_REQUEST['action'] <> 'inv_hist_fix') {
		  $messageStack->add(sprintf(INV_TOOLS_STOCK_ROUNDING_ERROR, $result->fields['sku'], $result->fields['quantity_on_hand'], $on_hand), 'error');
		  $cnt++;
		}
	  }
	  // now check with inventory history
	  $sql = "select sum(remaining) as remaining from " . TABLE_INVENTORY_HISTORY . "
		where sku = '" . $result->fields['sku'] . "'";
	    $inv_hist = $db->Execute($sql);
		$cog_qty  = round($inv_hist->fields['remaining'], $currencies->currencies[DEFAULT_CURRENCY]['decimal_precise']);
		$cog_owed = $owed[$result->fields['sku']] ? $owed[$result->fields['sku']] : 0;
		if ($on_hand <> ($cog_qty - $cog_owed)) {
		  $repair[$result->fields['sku']] = $cog_qty - $cog_owed;
		  if ($_REQUEST['action'] <> 'inv_hist_fix') {
		    $messageStack->add(sprintf(INV_TOOLS_OUT_OF_BALANCE, $result->fields['sku'], $on_hand, ($cog_qty - $cog_owed)), 'error');
		    $cnt++;
		  }
	  }
	  $result->MoveNext();
	}
	// flag the differences
	if ($_REQUEST['action'] == 'inv_hist_fix') { // start repair
	  $precision = 1 / pow(10, $currencies->currencies[DEFAULT_CURRENCY]['decimal_precise'] + 1);
	  $result = $db->Execute("update " . TABLE_INVENTORY_HISTORY . " set remaining = 0 where remaining < " . $precision); // remove rounding errors
	  if (sizeof($repair) > 0) {
	    foreach ($repair as $key => $value) {
		  $sql = "update " . TABLE_INVENTORY . " set quantity_on_hand = " . $value . "
		  	where sku = '" . $key . "'";
		  $db->Execute($sql);
		  $messageStack->add(sprintf(INV_TOOLS_BALANCE_CORRECTED, $key, $value), 'success');
		}
	  }
	}
	if ($cnt == 0) $messageStack->add(TEXT_YOUR_INVENTORY_BALANCES_ARE_OK, 'success');
	$default_tab_id = 'tools';
    break;
  case 'inv_on_order_fix':
	\core\classes\user::validate_security($security_level, 3); // security check
    // fetch the inventory items that we track COGS and get qty on SO, PO
	$cnt = 0;
	$fix = 0;
	$inv = array();
	$po  = array();
	$so  = array();
	$items = $db->Execute("select id, sku, quantity_on_order, quantity_on_sales_order from " . TABLE_INVENTORY . "
	  where inventory_type in ('" . implode("', '", $cog_type) . "') order by sku");
	while(!$items->EOF) {
	  $inv[$items->fields['sku']] = array(
	    'id'     => $items->fields['id'],
	    'qty_so' => $items->fields['quantity_on_sales_order'],
		'qty_po' => $items->fields['quantity_on_order'],
	  );
	  $items->MoveNext();
	}
	// fetch the PO's and SO's balances
	$po = inv_status_open_orders($journal_id =  4, $gl_type = 'poo');
	$so = inv_status_open_orders($journal_id = 10, $gl_type = 'soo');
	// compare the results and repair
	if (sizeof($inv) > 0) foreach ($inv as $sku => $balance) {
	  if (!isset($po[$sku])) $po[$sku] = 0;
	  if ($balance['qty_po'] <> $po[$sku]) {
	    $messageStack->add(sprintf(INV_TOOLS_PO_ERROR, $sku, $balance['qty_po'], $po[$sku]), 'caution');
		$db->Execute("update " . TABLE_INVENTORY . " set quantity_on_order = " . $po[$sku] . " where id = " . $balance['id']);
		$fix++;
	  }
	  if (!isset($so[$sku])) $so[$sku] = 0;
	  if ($balance['qty_so'] <> $so[$sku]) {
	    $messageStack->add(sprintf(INV_TOOLS_SO_ERROR, $sku, $balance['qty_so'], $so[$sku]), 'caution');
		$db->Execute("update " . TABLE_INVENTORY . " set quantity_on_sales_order = " . $so[$sku] . " where id = " . $balance['id']);
	    $fix++;
	  }
	  $cnt++;
	}
	$messageStack->Add(sprintf(INV_TOOLS_SO_PO_RESULT, $cnt, $fix),'success');
	gen_add_audit_log(sprintf(INV_TOOLS_AUTDIT_LOG_SO_PO,  $cnt), 'Fixed: ' . $fix);
	$default_tab_id = 'tools';
    break;
  default:
}

/*****************   prepare to display templates  *************************/
// build some general pull down arrays
$sel_yes_no = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_YES),
);
$cost_methods = array(
 array('id' => 'f', 'text' => TEXT_FIFO),
 array('id' => 'l', 'text' => TEXT_LIFO),
 array('id' => 'a', 'text' => TEXT_AVERAGE),
);
$sel_item_cost = array(
 array('id' => '0',  'text' => TEXT_NO),
 array('id' => 'PO', 'text' => TEXT_PURCHASE_ORDERS),
 array('id' => 'PR', 'text' => TEXT_PURCHASES),
);
$sel_sales_tax = ord_calculate_tax_drop_down('c');
$sel_purch_tax = ord_calculate_tax_drop_down('v');
// some pre-defined gl accounts
$cog_chart = gen_coa_pull_down(2, false, true, false, $restrict_types = array(32)); // cogs types only
$inc_chart = gen_coa_pull_down(2, false, true, false, $restrict_types = array(30)); // income types only
$inv_chart = gen_coa_pull_down(2, false, true, false, $restrict_types = array(4, 34)); // inv, expenses types only

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_INVENTORY_ADMINISTRATION);
?>