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
  	}
  	
  	/**
  	 * this function will update the sku's that are in the csv files.
  	 * @param string $lines_array
  	 * @return void|boolean
  	 */
  	function processCSV($lines_array = '') { //Master
  		global $db, $messageStack;
  		if (!$this->cyberParse($lines_array)) return false;  // parse the submitted string, check for errors
  		$count = 0;
  		foreach ($this->records as $row) {
  			$where = '';
  			if (isset($row['sku']) && strlen($row['sku']) > 0) {
  				$where = "sku='{$row['sku']}'";
  			} elseif(isset($row['upc_code']) && strlen($row['upc_code']) > 0) {
  				$where = "upc_code='{$row['upc_code']}'";
  			}elseif(isset($row['description_purchase'])){
  					$where = " b.description_purchase like '%{$row['description_purchase']}%'";
  			}
  			if (isset($row['vendor_id'])){
  				$where .= " b.vendor_id = '{$row['vendor_id']}'";
  			}
  			$valid_fields = array(
  			  'description_short'		=> 'a.description_short',
  			  'description_sales'		=> 'a.description_sales',
  			  'account_sales_income'	=> 'a.account_sales_income',
  			  'account_inventory_wage'	=> 'a.account_inventory_wage',
  			  'account_cost_of_sales'	=> 'a.account_cost_of_sales',
  			  'item_taxable'			=> 'a.item_taxable',
  			  'price_sheet'				=> 'a.price_sheet',
  			  'full_price'				=> 'a.full_price',
  			  'full_price_with_tax'		=> 'a.full_price_with_tax',
  			  'item_weight'				=> 'a.item_weight',
  			  'minimum_stock_level'		=> 'a.minimum_stock_level',
  			  'reorder_quantity'		=> 'a.reorder_quantity',
  			  'lead_time'				=> 'a.lead_time',
  			  'upc_code'				=> 'a.upc_code',
  			  'description_purchase'	=> 'b.description_purchase',
  			  'price_sheet_v'			=> 'b.price_sheet_v',
  			  'purch_taxable'			=> 'b.purch_taxable',
  			  'item_cost'				=> 'b.item_cost',
  			  'vendor_id'				=> 'b.vendor_id',
  			);
  			$sqlData = array();
  			foreach ($valid_fields as $key => $value) if (isset($row[$key])) $sqlData[$value] = $row[$key];
  			$sqlData['last_update'] = date('Y-m-d');
  			if ($where) {
  				$result = db_perform(TABLE_INVENTORY . ' a JOIN '. TABLE_INVENTORY_PURCHASE .' b on a.sku = b.sku ' , $data_array, 'update', $where);
  				if ($result->AffectedRows() > 0) $count++;
  			}
  		}
  		$messageStack->add("successfully imported $count SKU prices.", "success");
  		return;
  	}
	
	function cyberParse($lines) {
		if(!$lines) return false;
		$title_line = trim(array_shift($lines)); // pull header and remove extra white space characters
		$title_line = str_replace('"','',$title_line);
		$titles     = explode(",", $title_line);
		foreach ($lines as $line_num => $line) {
			$subject      = trim($line);
			$parsed_array = $this->csv_string_to_array($subject);
			for ($field_num = 0; $field_num < count($titles); $field_num++) {
				$this->records[$i][$titles[$field_num]] = $parsed_array[$field_num];
			}
		}
		return true;
	}
	

	function csv_string_to_array($str) {
		$results = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", trim($str));
		return preg_replace("/^\"(.*)\"$/", "$1", $results);
	}

}

?>
