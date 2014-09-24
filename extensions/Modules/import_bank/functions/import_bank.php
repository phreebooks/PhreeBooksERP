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
//  Path: /modules/import_bank/functions/import_bank.php
//
function bank_import_csv($structure, $filename, $bank_gl_acct) {
  	global $db, $messageStack;
  	include(DIR_FS_WORKING. 'classes/import_banking.php');
  	$bankimport = new impbanking();
  	$data = file($_FILES[$filename]['tmp_name']);
	// read the header and build array
  	if (sizeof($data) < 2) {
	    $messageStack->add('The number of lines in the file is to small, a csv file must contain a header line and at least on input line!','error');
		return false;
  	}
  	$header = csv_explode(trim(array_shift($data)));
  	// build the map structure
  	$temp = $structure->Module->Table;
  	$map_array = array();
  	foreach ($structure->Module->Table as $table) {
		foreach ($table->Field as $field) {
	  		$key = array_search($field->TagName, $header);
	  		if ($key !== false) $map_array[$key] = array('cnt' => 0, 'table' => $table->Name, 'field' => $field->Name);
		}
		break;
  	}
	// 	build dependent map tables
  	$ref_mapping = array();
  	if (is_object($table->LinkTable)) $table->LinkTable = array($table->LinkTable);
  	if (isset($table->LinkTable)) foreach ($table->LinkTable as $subtable) {
    	foreach ($structure->Module->Table as $working) if ($subtable->Name == $working->Name) {
			$ref_mapping[$subtable->Name] = array(
			  'pri_field' => $subtable->PrimaryField,
			  'ref_field' => $subtable->DependentField,
			);
	  		for ($i = 1; $i <= MAX_IMPORT_CSV_ITEMS; $i++) {
				foreach ($working->Field as $field) {
				  	$key = array_search($field->TagName . '_' . $i, $header);
				  	if ($key !== false) $map_array[$key] = array(
				      'cnt'   => $i,
					  'table' => $subtable->Name,
					  'field' => $field->Name,
				  	);
	    		}
	  		}
		}
  	}
  	$countline =0;
  	foreach ($data as $line) {
    	if (!$line  = trim($line)) continue; // blank line
		$line_array = $map_array;
		$sql_array  = array();
		$working    = csv_explode($line);
    	for ($i = 0; $i < sizeof($working); $i++) $line_array[$i]['value'] = $working[$i];
		foreach ($line_array as $value) {
	  		$sql_array[$value['table']][$value['cnt']][$value['field']] = $value['value'];
		}
		$db->transStart();
		foreach ($sql_array as $table => $count) {
	  		foreach ($count as $cnt => $table_array) {
	  			if($table_array['debit_amount']=='' && $table_array['credit_amount']=='' ){
	  				if ( $table_array['debit_credit'] == DEBIT_CREDIT_DESCRIPTION ){
	  			 		$table_array['debit_amount']  = 0;
	  			 		$table_array['credit_amount'] = $table_array['amount'];
	  				}else{
	  			 		$table_array['debit_amount']  = $table_array['amount'];
	  			 		$table_array['credit_amount'] = 0;
	  				}
	  			}
	  			if(isset($table_array['debit_amount']) && isset($table_array['credit_amount'])){
					//	echo 'inserting data: '; print_r($table_array); echo '<br>';
					$bankimport->start_import($table_array['our_account_number'],$table_array['date'],$table_array['account_number_to'],$table_array['credit_amount'],$table_array['debit_amount'],$table_array['description'].' '. $table_array['anouncement'], $bank_gl_acct, $table_array['iban_to']);
					if ($bankimport->_succes == true) $countline++;
	  			}
	  		}
		}
  	}
  	if ( $countline <> 0 ) {
  		if (!$bankimport->update_chart_history_periods($bankimport->first_period)){
  			$db->transRollback();
  			$messageStack->add("Chart of accounts are out of balance. When we try to import the file\n Removed the journals. Please try to import again when balances are fine.");
  		}else{
  			$db->transCommit();
  			$messageStack->add("succesfully posted $countline number of lines",'caution');
  		}
  	}
  	$messageStack->write_debug();
}
?>