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
//  Path: /modules/phreebooks/classes/backorders_report.php
//
namespace phreebooks\classes;
// this file contains special function calls to generate the data array needed to build reports not possible
// with the current reportbuilder structure. Targeted towards aged receivables.
class backorders_report {
  function __construct() {
	// List the special fields as an array to substitute out for the sql, must match from the selection menu generation
	$this->special_field_array = array('qty_backorder');
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
//echo 'orig myrow = '; print_r($myrow); echo '<br /><br />';
		$myrow = $this->replace_data_fields($myrow, $Seq);
		// if myrow is returned false, the sales order line has been filled.
		if (!$myrow) {
			$ColCnt = 1;
			$result->MoveNext();
			continue; // skip the row, order has been filled
		}
//echo 'new myrow = '; print_r($myrow); echo '<br /><br />';
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
// echo 'output array = '; print_r($OutputArray); echo '<br />'; exit();
	return $OutputArray;
  }

  function build_table_drop_down() {
	$output = array();
	return $output;
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => 'journal_main.id',                  'text' => RW_BO_RECORD_ID);
	$output[] = array('id' => 'journal_main.period',              'text' => TEXT_PERIOD);
	$output[] = array('id' => 'journal_main.post_date',           'text' => TEXT_POST_DATE);
	$output[] = array('id' => 'journal_main.store_id',            'text' => RW_BO_STORE_ID);
	$output[] = array('id' => 'journal_main.freight',             'text' => RW_BO_FRT_TOTAL);
	$output[] = array('id' => 'journal_main.ship_carrier',        'text' => RW_BO_FRT_CARRIER);
	$output[] = array('id' => 'journal_main.ship_service',        'text' => RW_BO_FRT_SERVICE);
	$output[] = array('id' => 'journal_main.sales_tax',           'text' => RW_BO_SALES_TAX);
//	$output[] = array('id' => 'journal_main.tax_auths',           'text' => RW_BO_TAX_AUTH);
	$output[] = array('id' => 'journal_main.total_amount',        'text' => RW_BO_INV_TOTAL);
	$output[] = array('id' => 'journal_main.currencies_code',     'text' => RW_BO_CUR_CODE);
	$output[] = array('id' => 'journal_main.currencies_value',    'text' => RW_BO_CUR_EXC_RATE);
	$output[] = array('id' => 'journal_main.purchase_invoice_id', 'text' => RW_BO_INV_NUM);
	$output[] = array('id' => 'journal_main.purch_order_id',      'text' => RW_BO_PO_NUM);
	$output[] = array('id' => 'journal_main.rep_id',              'text' => RW_BO_SALES_REP);
	$output[] = array('id' => 'journal_main.gl_acct_id',          'text' => RW_BO_AR_ACCT);
	$output[] = array('id' => 'journal_main.bill_acct_id',        'text' => RW_BO_BILL_ACCT_ID);
	$output[] = array('id' => 'journal_main.bill_address_id',     'text' => RW_BO_BILL_ADD_ID);
	$output[] = array('id' => 'journal_main.bill_primary_name',   'text' => RW_BO_BILL_PRIMARY_NAME);
	$output[] = array('id' => 'journal_main.bill_contact',        'text' => RW_BO_BILL_CONTACT);
	$output[] = array('id' => 'journal_main.bill_address1',       'text' => RW_BO_BILL_ADDRESS1);
	$output[] = array('id' => 'journal_main.bill_address2',       'text' => RW_BO_BILL_ADDRESS2);
	$output[] = array('id' => 'journal_main.bill_city_town',      'text' => RW_BO_BILL_CITY);
	$output[] = array('id' => 'journal_main.bill_state_province', 'text' => RW_BO_BILL_STATE);
	$output[] = array('id' => 'journal_main.bill_postal_code',    'text' => RW_BO_BILL_ZIP);
	$output[] = array('id' => 'journal_main.bill_country_code',   'text' => RW_BO_BILL_COUNTRY);
	$output[] = array('id' => 'journal_main.bill_telephone1',     'text' => RW_BO_BILL_TELE1);
	
	$output[] = array('id' => 'journal_item.qty',                 'text' => RW_BO_QTY_ORDERED);
	$output[] = array('id' => 'journal_item.sku',                 'text' => TEXT_SKU);
	$output[] = array('id' => 'journal_item.description',         'text' => TEXT_DESCRIPTION);
	$output[] = array('id' => 'journal_item.quantity_on_hand',    'text' => RW_QTY_IN_STOCK);
	$output[] = array('id' => 'journal_item.qty_backorder',       'text' => RW_BO_QTY_BACKORDER);
	return $output;
  }

  function replace_special_fields($sql) {
  	$preg_array = array();
  	for ($i = 0; $i < count ($this->special_field_array); $i++ ) {
	  $preg_array[] = '/' . $this->special_field_array[$i] . '/';
	}
	return preg_replace($preg_array, TABLE_JOURNAL_ITEM . '.id', $sql);
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
	global $db;
	$new_data = array();
	// fetch qty on order
	$result = $db->Execute("select qty from " . TABLE_JOURNAL_ITEM . " where id = " . $id);
	$order_qty = $result->fields['qty'];
	// Fetch qty invoiced
	$sql = "select sum(qty) as qty_shipped_prior from " . TABLE_JOURNAL_ITEM . " 
		where so_po_item_ref_id = " . $id . " and gl_type = 'sos'";
	$result = $db->Execute($sql);
	$new_data['qty_backorder']  = $order_qty - $result->fields['qty_shipped_prior'];
	if ($new_data['qty_backorder'] == 0) return false; // skip the row as all quantities have shipped
	return $new_data;
  }
}
?>