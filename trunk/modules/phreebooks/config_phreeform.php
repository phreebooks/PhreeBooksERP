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
//  Path: /modules/phreebooks/config_phreeform.php
//

$FormProcessing['ordr_qty'] = TEXT_QUANTITY_ORDERED;
$FormProcessing['j_desc']   = TEXT_JOURNAL_DESCRIPTION;
$FormProcessing['coa_type'] = TEXT_CHART_OF_ACCOUNT_TYPE;
$FormProcessing['avg_cost'] = PB_PF_GET_AVG_COST;
$FormProcessing['avg_total']= PB_PF_GET_AVG_TOTAL;
// Extra form processing operations
function pf_process_phreebooks($strData, $Process) {
  switch ($Process) {
	case "ordr_qty": return pb_pull_order_qty($strData);
	case "j_desc":
	  gen_pull_language('phreebooks');
      return isset($journal_types_list[$strData]) ? $journal_types_list[$strData]['text'] : $strData;
	case "coa_type":  return pb_get_coa_type($strData);
	case "avg_cost":  return pb_get_avg_cost($strData);
	case "avg_total": return pb_get_avg_total($strData);
	default: // Do nothing
  }
  return $strData; // No Process recognized, return original value
}

function pb_pull_order_qty($ref_id = 0) {
  global $admin, $ReportID;
  $sql = "select qty from " . TABLE_JOURNAL_ITEM  . " where id = " . (int)$ref_id;
  $result = $admin->DataBase->query($sql);
  if ($result->rowCount() == 0) { // no sales/purchase order, was a direct invoice/POS
	$sql = "select qty from " . TABLE_JOURNAL_ITEM  . " where so_po_item_ref_id = " . (int)$ref_id . " and ref_id = " . $ReportID;
	$result = $admin->DataBase->query($sql);
  }
  return $result->fields['qty'];
}

function pb_get_coa_type($id) {
  require(DIR_FS_MODULES . 'phreebooks/defaults.php');
  return $coa_types_list[$id]['text'];
}

function pb_get_avg_cost($sku) {
	global $admin;
	$result = $admin->DataBase->query("SELECT avg_cost FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' ORDER BY post_date DESC LIMIT 1");
	return number_format($result->fields['avg_cost'], 2);
}

function pb_get_avg_total($history_id) {
	global $admin;
	$result    = $admin->DataBase->query("SELECT remaining, sku FROM ".TABLE_INVENTORY_HISTORY." WHERE id='$history_id'");
	$remaining = $result->fields['remaining'];
	$sku       = $result->fields['sku'];
	$result    = $admin->DataBase->query("SELECT avg_cost FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' ORDER BY post_date DESC LIMIT 1");
	return number_format($remaining * $result->fields['avg_cost'], 2);
}

?>