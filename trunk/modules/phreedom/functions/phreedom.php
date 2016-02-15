<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/phreedom/functions/phreedom.php
//


function load_theme_dropdown() {
  $include_header  = false;
  $include_calendar= false;
  $output          = array();
  $contents        = @scandir(DIR_FS_THEMES);
  if($contents === false) throw new \core\classes\userException("couldn't read or find directory ". DIR_FS_THEMES);
  foreach ($contents as $value) {
	if ($value <> '.' && $value <> '..' && is_dir(DIR_FS_THEMES . $value)) {
	  if (file_exists(DIR_FS_THEMES . $value . '/config.php')) {
		include(DIR_FS_THEMES . $value . '/config.php');
		$output[$value] = array('id' => $value, 'text' => $theme['name']);
	  }
	}
  }
  return $output;
}

function load_menu_dropdown() {
  $output = array();
  if (file_exists(DIR_FS_ADMIN . DIR_WS_THEMES . 'config.php')) {
	include(DIR_FS_ADMIN . DIR_WS_THEMES . 'config.php');
	foreach ($theme_menu_options as $key => $value) $output[] = array('id' => $key, 'text' => $value);
  }
  return $output;
}

function load_colors_dropdown() {
  $output   = array();
  $contents = @scandir(DIR_FS_ADMIN . DIR_WS_THEMES .'/css/');
  if($contents === false) throw new \core\classes\userException("couldn't read or find directory ". DIR_FS_ADMIN . DIR_WS_THEMES .'/css/');
  foreach ($contents as $color) {
	if ($color <> '.' && $color <> '..' && is_dir(DIR_FS_ADMIN . DIR_WS_THEMES . '/css/'.$color)) {
	  $output[$color] = array('id' => $color, 'text' => $color);
	}
  }
  return $output;
}

/**************************** admin functions ***********************************************/

function admin_add_reports($module, $save_path = PF_DIR_MY_REPORTS) {
	if (file_exists(DIR_FS_MODULES . $module . '/language/' . $_SESSION['user']->language . '/reports/')) {
	    $read_path = DIR_FS_MODULES . $module . '/language/' . $_SESSION['user']->language . '/reports/';
	} elseif (file_exists(DIR_FS_MODULES . $module . '/language/en_us/reports/')) {
	    $read_path = DIR_FS_MODULES . $module . '/language/en_us/reports/';
	} else {
	    return; // nothing to import
	}
	$files = @scandir($read_path);
	if($files === false) throw new \core\classes\userException("couldn't read or find directory $read_path");
	foreach ($files as $file) if (strtolower(substr($file, -4)) == '.xml') {
	    	ImportReport('', $file, $read_path, $save_path);
	}
}

/************************ install functions ******************************/
function install_build_co_config_file($company, $key, $value) {
  	$filename = DIR_FS_ADMIN . 'my_files/' . $company . '/config.php';
  	if (file_exists($filename)) { // update
    	$lines = file($filename);
    	$found_it = false;
    	for ($x = 0; $x < count($lines); $x++) {
	  		if (strpos(substr($lines[$x], 0, strpos($lines[$x], ',')), $key)) {
	    		$lines[$x] = "define('{$key}','" . addslashes($value) . "');" . "\n";
	    		$found_it = true;
	    		break;
	  		}
    	}
    	if (!$found_it) $lines[] = "define('{$key}','" . addslashes($value) . "');" . "\n";
  	} else { // create the config file, because it doesn't exist
    	$lines = array();
	    $lines[] = '<?php' . "\n";
    	$lines[] = '/* config.php */' . "\n";
	    $lines[] = "define('{$key}','" . addslashes($value) . "');" . "\n";
  	}
  	$line = implode('', $lines);
  	if (!$handle = @fopen($filename, 'w')) 	throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, $filename));
  	if (!@fwrite($handle, $line)) 			throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, $filename));
  	if (!@fclose($handle)) 					throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, $filename));
  	return true;
}

/***************************** import/export functions ******************************/
function load_module_xml($module) {
	global $admin;
  	if (($result = @file_get_contents(DIR_FS_MODULES . $module . '/' . $module . '.xml')) === false) throw new \core\classes\userException(sprintf(ERROR_READ_FILE, DIR_FS_MODULES . $module . '/' . $module . '.xml'));
  	if (!$output = xml_to_object($result)) throw new \core\classes\userException("xml file is empty for module");
  	// fix some special cases, multi elements with single entries convert to arrays
  	if (is_object($output->Module->Table)) $output->Module->Table = array($output->Module->Table);
  	return $output;
}

function build_sample_xml($structure, $db_table) {
  $output = '';
  $temp = $structure->Module->Table;
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
    $output   .= '<' . $table->TagName . '>' . ' // ' . $table->Description . chr(10);
	foreach ($table->Field as $field) if ($field->CanImport) {
	  $req = ($field->Required) ? ('[' . TEXT_REQUIRED . '] '): '';
	  $output .= '  <' . $field->TagName . '>' . $field->Type . '</' . $field->TagName . '>' . ' // ' . $req . $field->Description . chr(10);
	}
	// check dependent tables and add xml sample
    if (is_object($table->LinkTable)) $table->LinkTable = array($table->LinkTable);
	if (isset($table->LinkTable)) foreach ($table->LinkTable as $subtable) {
      foreach ($temp as $working) if ($subtable->Name == $working->Name) {
	    $output   .= '  <' . $working->TagName . '>' . ' // ' . $working->Description . chr(10);
	    foreach ($working->Field as $field) if ($field->CanImport) {
	      $req = ($field->Required) ? ('[' . TEXT_REQUIRED . '] '): '';
	      $output .= '    <' . $field->TagName . '>' . $field->Type . '</' . $field->TagName . '>' . ' // ' . $req . $field->Description . chr(10);
	    }
        $output   .= '  </' . $working->TagName . '>' . chr(10);
	  }
	}
    $output   .= '</' . $table->TagName . '>' . chr(10);
  }
  return $output;
}

function build_sample_csv($structure, $db_table) {
  $output = '';
  $legend = TEXT_LEGEND . chr(10); // 'Legend:'
  $temp = $structure->Module->Table;
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
	foreach ($table->Field as $field) {
	  if ($field->CanImport) {
	    $req = ($field->Required) ? ('[' . TEXT_REQUIRED . '] '): '';
	    $output .= $field->TagName . ', ';
	    $legend .= '"' . $field->TagName . ': (' . $field->Type . ') - ' . $req . $field->Description . '"' . chr(10);
	  }
	}
	// check dependent tables and add csv sample based on the max number of entries specified
    if (is_object($table->LinkTable)) $table->LinkTable = array($table->LinkTable);
	if (isset($table->LinkTable)) foreach ($table->LinkTable as $subtable) {
      foreach ($temp as $working) if ($subtable->Name == $working->Name) {
	    for($i = 1; $i <= MAX_IMPORT_CSV_ITEMS; $i++) {
		  foreach ($working->Field as $field) {
	        if ($field->CanImport) {
			  $req = ($field->Required) ? ('[' . TEXT_REQUIRED . '] '): '';
	          $output .= $field->TagName . '_' . $i .', ';
	          if ($i == 1) $legend .= '"' . $field->TagName . '_X: (' . $field->Type . ') - ' . $req . $field->Description . '"' . chr(10);
	        }
		  }
		}
	  }
	}
  }
  return $output . chr(10) . chr(10) . $legend;
}

function table_import_xml($structure, $db_table, $filename) {
//echo 'structure = '; print_r($structure); echo '<br>';
  global $admin;
  if (($data = @file_get_contents($_FILES[$filename]['tmp_name'], "r")) === false) throw new \core\classes\userException(sprintf(ERROR_READ_FILE, $_FILES[$filename]['tmp_name']));
  $temp = $structure->Module->Table;
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
	$tbl_active = $table;
	$tbl_tagname = $table->TagName;
	break;
  }
  if (!$result = xml_to_object($data)) return false;
  // fix some special cases, multi elements with single entries convert to arrays
  if (is_object($result->$tbl_tagname)) $result = array($result);
  foreach ($result->$tbl_tagname as $entry) {
    $sql_array = array();
	foreach ($tbl_active->Field as $field) {
	  $tag = $field->TagName;
	  if (isset($entry->$tag)) $sql_array[$field->Name] = $entry->$tag;
	}
//echo 'sql_array to write to table ' . DB_PREFIX . $db_table . ': '; print_r($sql_array); echo '<br>';
	db_perform(DB_PREFIX . $db_table, $sql_array, 'insert');
	// fetch the id for use with dependent tables
	$id = \core\classes\PDO::lastInsertId('id');
	// now look into dependent tables
    if (is_object($tbl_active->LinkTable)) $tbl_active->LinkTable = array($tbl_active->LinkTable);
	if (isset($tbl_active->LinkTable)) foreach ($tbl_active->LinkTable as $subtable) {
	  $sub_sql_array = array();
	  $sub_sql_array[$subtable->DependentField] = $id;
	  $sub_table_name = $subtable->Name;
      foreach ($temp as $working) if ($subtable->Name == $working->Name) {
	    $subtag = $working->TagName;
	    foreach ($working->Field as $field) {
	      $fieldtag = $field->TagName;
		  if (isset($entry->$subtag->$fieldtag)) $sub_sql_array[$field->Name] = $entry->$subtag->$fieldtag;
	    }
	  }
//echo 'sql_array to write to subtable ' . DB_PREFIX . $sub_table_name . ': '; print_r($sub_sql_array); echo '<br><br>';
	  db_perform(DB_PREFIX . $sub_table_name, $sub_sql_array, 'insert');
	}
  }
}

function table_import_csv($structure, $db_table, $filename) {
//echo 'structure = '; print_r($structure); echo '<br>';
  global $admin;
  $data = file($_FILES[$filename]['tmp_name']);
  // read the header and build array
  if (sizeof($data) < 2) throw new \core\classes\userException('The number of lines in the file is to small, a csv file must contain a header line and at least on input line!');
  $header = csv_explode(trim(array_shift($data)));
  foreach ($header as $key => $value) $header[$key] = trim($value);
//echo 'header = '; print_r($header); echo '<br>';
  // build the map structure
  $temp = $structure->Module->Table;
  $map_array = array();
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
	foreach ($table->Field as $field) {
	  $key = array_search($field->TagName, $header);
	  if ($key !== false) $map_array[$key] = array('cnt' => 0, 'table' => $table->Name, 'field' => $field->Name);
	}
	break;
  }
  // build dependent map tables
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
  foreach ($data as $line) {
    if (!$line  = trim($line)) continue; // blank line
	$line_array = $map_array;
	$sql_array  = array();
	$working    = csv_explode($line);
    for ($i = 0; $i < sizeof($working); $i++) $line_array[$i]['value'] = $working[$i];
	foreach ($line_array as $value) {
	  $sql_array[$value['table']][$value['cnt']][$value['field']] = $value['value'];
	}
	foreach ($sql_array as $table => $count) {
	  foreach ($count as $cnt => $table_array) {
//echo 'inserting data: '; print_r($table_array); echo '<br>';
	    if ($cnt == 0) { // main record, fetch id afterwards
	      db_perform(DB_PREFIX . $table, $table_array, 'insert');
		  $id = \core\classes\PDO::lastInsertId('id');
		} else { // dependent table
		  $data_present = false;
		  foreach ($table_array as $value) if (gen_not_null($value)) $data_present = true;
		  if ($data_present) {
		    $table_array[$ref_mapping[$table]['ref_field']] = $id;
	        db_perform(DB_PREFIX . $table, $table_array, 'insert');
		  }
		}
	  }
	}
  }
}

function table_export_xml($structure, $db_table) {
  global $admin;
  $output = '';
  $temp   = $structure->Module->Table;
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
    $tag_map = array();
	foreach ($table->Field as $field) $tag_map[$field->Name] = $field->TagName;
	$result = $admin->DataBase->query("select * from " . DB_PREFIX . $db_table);
    if ($result->fetch(\PDO::FETCH_NUM) > 0) while (!$result->EOF) {
	  $output   .= '<' . $table->TagName . '>' . chr(10);
	  foreach ($result->fields as $key => $value) {
	    $output .= '  <' . $tag_map[$key] . '>' . $value . '</' . $tag_map[$key] . '>' . chr(10);
	  }
      $output   .= '</' . $table->TagName . '>' . chr(10);
	  $result->MoveNext();
	}
  }
  return $output;
}

function table_export_csv($structure, $db_table) {
  global $admin;
  $output = '';
  $header = false;
  $temp   = $structure->Module->Table;
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
    $tag_map = array();
	foreach ($table->Field as $field) $tag_map[$field->Name] = $field->TagName;
	$result = $admin->DataBase->query("select * from " . DB_PREFIX . $db_table);
    if ($result->fetch(\PDO::FETCH_NUM) > 0) while (!$result->EOF) {
	  if (!$header) { // output the header
	    $temp    = array();
		foreach ($result->fields as $key => $value) $temp[] = $tag_map[$key];
	    $output .= implode(',', $temp) . chr(10);
	    $header  = true;
	  }
	  $temp = array();
	  foreach ($result->fields as $key => $value) {
	    $temp[] = (strpos($value, ',') !== false) ? ('"'.$value.'"') : $value;
	  }
	  $output .= implode(',', $temp) . chr(10);
	  $result->MoveNext();
	}
  }
  return $output;
}

function csv_explode($str, $delim = ',', $enclose = '"', $preserve = false){
	$results = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", trim($str));
	return preg_replace("/^\"(.*)\"$/", "$1", $results);
}

?>