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
namespace sku_pricer\classes;
class sku_pricer {
	public $records    = array();
	
  	function __construct() {
  	}

  function processCSV($lines_array = '') {
	global $db, $messageStack;
	if (!$this->cyberParse($lines_array)) return false;  // parse the submitted string, check for errors
	$count = 0;
	foreach ($this->records as $row) {
		$where = '';
		if (isset($row['sku'])) {
			$where = " sku = '" . $row['sku'] . "'";
	  	}elseif(isset($row['upc_code'])){ 
			$where = " upc_code = '" . $row['upc_code'] . "'";
	  	}elseif(isset($row['description_purchase']) && $row['vendor_id']){
	  		$where = " description_purchase like '%" . $row['description_purchase'] . "%' and vendor_id = '".$row['vendor_id']."'";
		}
		
		if($row['full_price']) 				$data_array['full_price']				= $row['full_price'] ;
		if($row['item_cost']) 				$data_array['item_cost'] 				= $row['item_cost'] ;
		if($row['purch_package_quantity']) 	$data_array['purch_package_quantity'] 	= $row['purch_package_quantity'] ;
		$data_array['last_update'] = date('Y-m-d');
	
	  	if ($where) {
	  		$result = db_perform(TABLE_INVENTORY, $data_array, 'update', $where);
	  		if ($result->AffectedRows() > 0) $count++;
	  	}
	}
	$messageStack->add("successfully imported $count SKU prices.", "success");
	return;
  }

  function cyberParse($lines) {
  	$i 			= 0;
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
	   //$i++;
	}
	print_r($this->records);
	return true;
  }

  function csv_string_to_array($str) {
	$results = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", trim($str));
	return preg_replace("/^\"(.*)\"$/", "$1", $results);
  }
  function __destruct()
  { //print_r($this);
  } 
}

?>