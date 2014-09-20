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
//  Path: /modules/sku_pricer/classes/sku_pricer.php
//
namespace sku_pricer\classes;
class sku_pricer {
	public $records = array();

  	function __construct() {
  		$this->records = array();
  	}

  	/**
  	 * this function will update the sku's that are in the csv files.
  	 * @param string $lines_array
  	 * @return void|boolean
  	 */
  	function processCSV($filename) { //Master
  		global $admin, $messageStack;
  		$rows = $this->csv_to_array($_FILES[$filename]['tmp_name'], $delimiter=',');
  		$messageStack->debug("\nfinished parsing, extracted number of rows = ".sizeof($rows));
		$valid_fields = array(
			'description_short'		=> 'description_short',
			'description_sales'		=> 'description_sales',
			'description_purchase'	=> 'description_purchase',
			'account_sales_income'	=> 'account_sales_income',
			'account_inventory_wage'=> 'account_inventory_wage',
			'account_cost_of_sales'	=> 'account_cost_of_sales',
			'item_taxable'			=> 'item_taxable',
			'price_sheet'			=> 'price_sheet',
			'full_price'			=> 'full_price',
			'full_price_with_tax'	=> 'full_price_with_tax',
			'item_weight'			=> 'item_weight',
			'minimum_stock_level'	=> 'minimum_stock_level',
			'reorder_quantity'		=> 'reorder_quantity',
			'lead_time'				=> 'lead_time',
			'upc_code'				=> 'upc_code',
			'item_cost'				=> 'item_cost',
			'price_sheet_v'			=> 'price_sheet_v',
			'purch_taxable'			=> 'purch_taxable',
			'item_cost'				=> 'item_cost',
			'vendor_id'				=> 'vendor_id',
		);
		$purch_fields = array(
			'description_purchase'	=> 'description_purchase',
			'price_sheet_v'			=> 'price_sheet_v',
			'purch_taxable'			=> 'purch_taxable',
			'item_cost'				=> 'item_cost',
			'vendor_id'				=> 'vendor_id',
		);
  		$count = 0;
  		foreach ($rows as $row) {
			$where = false;
			if (isset($row['sku']) && strlen($row['sku']) > 0) {
				$where = "sku='{$row['sku']}'";
			} elseif (isset($row['upc_code']) && strlen($row['upc_code']) > 0) {
				$where = "upc_code='{$row['upc_code']}'";
			}
			if (!$where) {
				$messageStack->add("No search field was found. Either the SKU or UPC Code field must be included in the csv file.", 'error');
				break; // no valid search fields
			}
			$query = "";
			foreach ($valid_fields as $key => $value) if (isset($row[$key])) $query .= " $value='".db_input($row[$key])."',";
			$query .= "last_update='".date('Y-m-d')."'";
			$sql = "UPDATE ".TABLE_INVENTORY." SET $query WHERE $where";
			$messageStack->debug("\nExecuting sql = $sql");
			$result = $admin->DataBase->Execute($sql);
			if ($result->AffectedRows() > 0) $count++;
			// now update the purchase table (need the sku, upc_code will not work)
			if (!isset($row['sku'])) {
				$result = $admin->DataBase->Execute("SELECT sku from ".TABLE_INVENTORY." WHERE upc_code='{$row['upc_code']}'");
				$row['sku'] = $result->fields['sku'];
				$where = "sku='{$row['sku']}'";
			}
			if (isset($row['vendor_id']) && $row['vendor_id']) $where .= " AND vendor_id = '{$row['vendor_id']}'";
			$query = "";
			foreach ($purch_fields as $key => $value) if (isset($row[$key])) $query .= " $value='".db_input($row[$key])."',";
			$query = substr($query, 0, strlen($query)-1); // remove last comma
			$sql = "UPDATE ".TABLE_INVENTORY_PURCHASE." SET $query WHERE $where";
			$messageStack->debug("\nExecuting sql = $sql");
			$result = $admin->DataBase->Execute($sql);
		}
		if (DEBUG) $messageStack->write_debug();
		$messageStack->add("Total lines processed: ".sizeof($rows).". Total affected rows = $count.", "success");
  	}

  	function csv_to_array($filename='', $delimiter=',') {
  		if(!file_exists($filename) || !is_readable($filename)) return FALSE;
  		$header = NULL;
  		$data = array();
  		if (($handle = fopen($filename, 'r')) !== FALSE) {
  			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
  				if (!$header) $header = $row;
  				else $data[] = array_combine($header, $row);
  			}
  			fclose($handle);
  		}
  		return $data;
  	}

}

?>
