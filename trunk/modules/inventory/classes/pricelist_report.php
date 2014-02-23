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
//  Path: /modules/phreebooks/classes/pricelist_report.php
//
namespace inventory\classes;
require_once (DIR_FS_MODULES."/inventory/defaults.php");
require_once (DIR_FS_MODULES."/inventory/functions/inventory.php");

// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure. Targeted towards aged receivables.
class pricelist_report {
  function __construct() {
	// List the special fields as an array to substitute out for the sql, must match from the selection menu generation
	$this->special_field_array = array(
	  'price_level_1','price_level_2','price_level_3','price_level_4','price_level_5',
	  'price_qty_1','price_qty_2','price_qty_3','price_qty_4','price_qty_5',
	);
  }

  function load_report_data($report, $Seq, $sql = '', $GrpField = '') {
	global $db;
	// prepare the sql by temporarily replacing calculated fields with real fields
	$sql_fields = substr($sql, strpos($sql,'select ') + 7, strpos($sql, ' from ') - 7);
	$this->sql_field_array = explode(', ', $sql_fields);
	for ($i = 0; $i < count($this->sql_field_array); $i++) {
	  $this->sql_field_karray['c' . $i] = substr($this->sql_field_array[$i], 0, strpos($this->sql_field_array[$i], ' '));
	}
	$sql = $this->replace_special_fields($sql);
	
	$result = $db->Execute($sql);
	if ($result->RecordCount() == 0) return false; // No data so bail now
	// Generate the output data array
	$RowCnt = 0; // Row counter for output data
	$ColCnt = 1;
	$GrpWorking = false;
	while (!$result->EOF) {
		$myrow = $result->fields;
		// Check to see if a total row needs to be displayed
		if (isset($GrpField)) { // we're checking for group totals, see if this group is complete
			if (($myrow[$GrpField] <> $GrpWorking) && $GrpWorking !== false) { // it's a new group so print totals
				$OutputArray[$RowCnt][0] = 'g:' . $GrpWorking;
				foreach($Seq as $offset => $TotalCtl) {
					$OutputArray[$RowCnt][$offset+1] = ProcessData($TotalCtl['grptotal'], $TotalCtl['processing']);
					$Seq[$offset]['grptotal'] = ''; // reset the total
				}
				$RowCnt++; // go to next row
			}
			$GrpWorking = $myrow[$GrpField]; // set to new grouping value
		}
		$myrow = $this->replace_data_fields($myrow, $Seq);
		// if myrow is returned false, the sales order line has been filled.
		if (!$myrow) {
			$ColCnt = 1;
			$result->MoveNext();
			continue; // skip the row, order has been filled
		}
		$OutputArray[$RowCnt][0] = 'd'; // let the display class know its a data element
		foreach($Seq as $key => $TableCtl) { // 
			// insert data into output array and set to next column
			$OutputArray[$RowCnt][$ColCnt] = ProcessData($myrow[$TableCtl['fieldname']], $TableCtl['processing']);
			$ColCnt++;
			if ($TableCtl['total']) { // add to the running total if need be
				$Seq[$key]['grptotal'] += $myrow[$TableCtl['fieldname']];
				$Seq[$key]['rpttotal'] += $myrow[$TableCtl['fieldname']];
			}
		}
		$RowCnt++;
		$ColCnt = 1;
		$result->MoveNext();
	}
	if ($GrpWorking !== false) { // if we collected group data show the final group total
		$OutputArray[$RowCnt][0] = 'g:' . $GrpWorking;
		foreach ($Seq as $TotalCtl) {
			$OutputArray[$RowCnt][$ColCnt] = ($TotalCtl['total'] == '1') ? ProcessData($TotalCtl['grptotal'], $TotalCtl['processing']) : ' ';
			$ColCnt++;
		}
		$RowCnt++;
		$ColCnt = 1;
	}
	// see if we have a total to send
	$ShowTotals = false;
	foreach ($Seq as $TotalCtl) if ($TotalCtl['total']=='1') $ShowTotals = true; 
	if ($ShowTotals) {
		$OutputArray[$RowCnt][0] = 'r:' . $report->title;
		foreach ($Seq as $TotalCtl) {
			if ($TotalCtl['total']) $OutputArray[$RowCnt][$ColCnt] = ProcessData($TotalCtl['rpttotal'], $TotalCtl['processing']);
				else $OutputArray[$RowCnt][$ColCnt] = ' ';
			$ColCnt++;
		}
	}
	return $OutputArray;
  }

  function build_table_drop_down() {
	$output = array();
	return $output;
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => 'id',                'text' => 'Record ID');
	$output[] = array('id' => 'sku',               'text' => 'SKU');
	$output[] = array('id' => 'description_short', 'text' => 'Description');
	$output[] = array('id' => 'inactive',          'text' => 'Inactive');
	$output[] = array('id' => 'catalog',           'text' => 'Catalog Item');
	$output[] = array('id' => 'item_cost',         'text' => 'Item Cost');
	$output[] = array('id' => 'full_price',        'text' => 'Full Price');
	$output[] = array('id' => 'price_level_1',     'text' => 'Price Level 1');
	$output[] = array('id' => 'price_level_2',     'text' => 'Price Level 2');
	$output[] = array('id' => 'price_level_3',     'text' => 'Price Level 3');
	$output[] = array('id' => 'price_level_4',     'text' => 'Price Level 4');
	$output[] = array('id' => 'price_level_5',     'text' => 'Price Level 5');
	$output[] = array('id' => 'price_qty_1',       'text' => 'Qty Level 1');
	$output[] = array('id' => 'price_qty_2',       'text' => 'Qty Level 2');
	$output[] = array('id' => 'price_qty_3',       'text' => 'Qty Level 3');
	$output[] = array('id' => 'price_qty_4',       'text' => 'Qty Level 4');
	$output[] = array('id' => 'price_qty_5',       'text' => 'Qty Level 5');
	return $output;
  }

  function replace_special_fields($sql) {
  	$preg_array = array();
  	for ($i = 0; $i < count ($this->special_field_array); $i++ ) {
	  $preg_array[] = '/' . $this->special_field_array[$i] . '/';
	}
	return preg_replace($preg_array, 'id', $sql);
  }

  function replace_data_fields($myrow, $Seq) {
	foreach ($Seq as $key => $TableCtl) { // We need to find the id number to calculate the special fields
	  if (in_array($this->sql_field_karray[$TableCtl['fieldname']], $this->special_field_array)) {
	    $id = $myrow[$TableCtl['fieldname']];
		break;
	  }
	}
    $new_data = $this->calulate_special_fields($id);
	if (!$new_data) return false;
	foreach ($myrow as $key => $value) { 
	  for ($i = 0; $i < count($this->special_field_array); $i++) {
	    if ($this->sql_field_karray[$key] == $this->special_field_array[$i]) $myrow[$key] = $new_data[$this->special_field_array[$i]];
	  }
	}
	return $myrow;
  }

  function calulate_special_fields($id) {
	global $db, $currencies;
	$new_data = array();
	// get the inventory prices
	$inventory = $db->Execute("select item_cost, full_price, price_sheet from ".TABLE_INVENTORY." where id = '$id'");
	// determine what price sheet to use, priority: inventory, default
	if ($inventory->fields['price_sheet'] <> '') {
		$sheet_name = $inventory->fields['price_sheet'];
	} else {
		$default_sheet = $db->Execute("select sheet_name from " . TABLE_PRICE_SHEETS . "
			where type = 'c' and default_sheet = '1'");
		$sheet_name = ($default_sheet->RecordCount() == 0) ? '' : $default_sheet->fields['sheet_name'];
	}
	// determine the sku price ranges from the price sheet in effect
	$levels = false;
	if ($sheet_name <> '') {
		$sql = "select id, default_levels from " . TABLE_PRICE_SHEETS . "
		    where inactive = '0' and type = 'c' and sheet_name = '" . $sheet_name . "' and 
		    (expiration_date is null or expiration_date = '0000-00-00' or expiration_date >= '" . date('Y-m-d') . "')";
		$price_sheets = $db->Execute($sql);
		// retrieve special pricing for this inventory item
		$result = $db->Execute("select price_sheet_id, price_levels from " . TABLE_INVENTORY_SPECIAL_PRICES . "
			where price_sheet_id = '" . $price_sheets->fields['id'] . "' and inventory_id = " . $id);
		$special_prices = array();
		while (!$result->EOF) {
			$special_prices[$result->fields['price_sheet_id']] = $result->fields['price_levels'];
			$result->MoveNext();
		}
		$levels = isset($special_prices[$price_sheets->fields['id']]) ? $special_prices[$price_sheets->fields['id']] : $price_sheets->fields['default_levels'];
	}
	$new_data = array(
	  'price_level_1' => '',
	  'price_qty_1'   => '',
	  'price_level_2' => '',
	  'price_qty_2'   => '',
	  'price_level_3' => '',
	  'price_qty_3'   => '',
	  'price_level_4' => '',
	  'price_qty_4'   => '',
	  'price_level_5' => '',
	  'price_qty_5'   => '',
	);
	if ($levels) {
		$prices = inv_calculate_prices($inventory->fields['item_cost'], $inventory->fields['full_price'], $levels);
		if (is_array($prices)) foreach ($prices as $key => $value) {
			$new_data['price_level_'.($key+1)] = $currencies->clean_value($value['price']);
			$new_data['price_qty_'  .($key+1)] = $value['qty'];
		}
	}
	return $new_data;
  }
}
?>