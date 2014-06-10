<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
$FormProcessing['j_desc']   = PB_PF_JOURNAL_DESC;
$FormProcessing['coa_type'] = TEXT_CHART_OF_ACCOUNT_TYPE;
// Extra form processing operations
function pf_process_phreebooks($strData, $Process) {
  switch ($Process) {
	case "ordr_qty": return pb_pull_order_qty($strData);
	case "j_desc":
	  gen_pull_language('phreebooks');
      return defined('GEN_ADM_TOOLS_J' . str_pad($strData, 2, '0', STR_PAD_LEFT)) ? constant('GEN_ADM_TOOLS_J' . str_pad($strData, 2, '0', STR_PAD_LEFT)) : $strData;
	case "coa_type": return pb_get_coa_type($strData);
	default: // Do nothing
  }
  return $strData; // No Process recognized, return original value
}

function pb_pull_order_qty($ref_id = 0) {
  global $db, $ReportID;
  $sql = "select qty from " . TABLE_JOURNAL_ITEM  . " where id = " . (int)$ref_id;
  $result = $db->Execute($sql);
  if ($result->RecordCount() == 0) { // no sales/purchase order, was a direct invoice/POS
	$sql = "select qty from " . TABLE_JOURNAL_ITEM  . " where so_po_item_ref_id = " . (int)$ref_id . " and ref_id = " . $ReportID;
	$result = $db->Execute($sql);
  }
  return $result->fields['qty'];
}

function pb_get_coa_type($id) {
  require(DIR_FS_MODULES . 'phreebooks/defaults.php');
  return $coa_types_list[$id]['text'];
}

?>