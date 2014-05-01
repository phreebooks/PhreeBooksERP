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
//  Path: /modules/sku_pricer/classes/sku_pricer.php
//
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
  		global $db, $messageStack;
  		$rows = $this->csv_to_array($_FILES[$filename]['tmp_name'], $delimiter=',');
  		$messageStack->debug("\nfinished parsing, extracted number of rows = ".sizeof($rows));
		$valid_fields = array(
			'description_short'		=> 'a.description_short',
			'description_sales'		=> 'a.description_sales',
			'account_sales_income'	=> 'a.account_sales_income',
			'account_inventory_wage'=> 'a.account_inventory_wage',
			'account_cost_of_sales'	=> 'a.account_cost_of_sales',
			'item_taxable'			=> 'a.item_taxable',
			'price_sheet'			=> 'a.price_sheet',
			'full_price'			=> 'a.full_price',
			'full_price_with_tax'	=> 'a.full_price_with_tax',
			'item_weight'			=> 'a.item_weight',
			'minimum_stock_level'	=> 'a.minimum_stock_level',
			'reorder_quantity'		=> 'a.reorder_quantity',
			'lead_time'				=> 'a.lead_time',
			'upc_code'				=> 'a.upc_code',
			'description_purchase'	=> 'b.description_purchase',
			'price_sheet_v'			=> 'b.price_sheet_v',
			'purch_taxable'			=> 'b.purch_taxable',
			'item_cost'				=> 'b.item_cost',
			'vendor_id'				=> 'b.vendor_id',
		);
  		$count = 0;
  		foreach ($rows as $row) {
			$where = '';
			if (isset($row['sku']) && strlen($row['sku']) > 0) {
				$where = "b.sku='{$row['sku']}'";
			} elseif (isset($row['upc_code']) && strlen($row['upc_code']) > 0) {
				$where = "a.upc_code='{$row['upc_code']}'";
			} elseif (isset($row['description_purchase'])) {
				$where = " b.description_purchase like '%{$row['description_purchase']}%'";
			}
			if (isset($row['vendor_id'])) $where .= " b.vendor_id = '{$row['vendor_id']}'";
			$query = "";
			foreach ($valid_fields as $key => $value) {
				if (isset($row[$key])) $query .= " $value='" . db_input($row[$key]) . "',";
			}
			$query .= "a.last_update = '". date('Y-m-d')."'";
			if ($where) {
				$sql = "UPDATE ".TABLE_INVENTORY.' a JOIN '.TABLE_INVENTORY_PURCHASE." b ON a.sku=b.sku SET $query WHERE $where";
				$messageStack->debug("\nExecuting sql = $sql");
				$result = $db->Execute($sql);
				if ($result->AffectedRows() > 0) $count++;
			}
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
