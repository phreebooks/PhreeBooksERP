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
//  Path: /modules/phreedom/functions/phreedom.php
//

function load_company_dropdown($include_select = false) {
  $output = array();
  $i = 1;
  $contents = scandir(DIR_FS_MY_FILES);
  foreach ($contents as $file) {
	if ($file <> '.' && $file <> '..' && is_dir(DIR_FS_MY_FILES . $file)) {
	  if (file_exists(DIR_FS_MY_FILES . $file . '/config.txt')) convert_cfg($file);
	  if (file_exists(DIR_FS_MY_FILES . $file . '/config.php')) {
		require_once (DIR_FS_MY_FILES . $file . '/config.php');
		$output[$i] = array(
		  'text' => constant($file . '_TITLE'), 
		  'file' => $file,
		);
		$i++;
	  }
	}
  }
  if ($include_select) $output[0] = array('text' => TEXT_NONE, 'file' => 'none');
  $the_list = array();
  foreach ($output as $key => $value) {
    $_SESSION['companies'][$key] = $value['file'];
    $the_list[] = array('id' => $key, 'text' => $value['text']);
  }
  return $the_list;
}

function load_language_dropdown($language_directory = 'modules/phreedom/language/') {
  $output   = array();
  $contents = scandir($language_directory);
  foreach ($contents as $lang) {
	if ($lang <> '.' && $lang <> '..' && is_dir($language_directory. $lang) && file_exists($language_directory . $lang . '/language.php')) {
	  if ($config_file = file($language_directory . $lang . '/language.php')) {
	    foreach ($config_file as $line) {
		  if (strstr($line,'\'LANGUAGE\'') !== false) {
		    $start_pos     = strpos($line, ',') + 2;
		    $end_pos       = strpos($line, ')') + 1;
		    $language_name = substr($line, $start_pos, $end_pos - $start_pos);
		    break;
		  }
	    }
	    $output[$lang] = array('id' => $lang, 'text' => $language_name);
	  }
	}
  }
  return $output;
}

function load_theme_dropdown() {
  $include_header  = false;
  $include_calendar= false;
  $output          = array();
  $contents        = scandir(DIR_FS_THEMES);
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
  $contents = scandir(DIR_FS_ADMIN . DIR_WS_THEMES .'/css/');
  foreach ($contents as $color) {
	if ($color <> '.' && $color <> '..' && is_dir(DIR_FS_ADMIN . DIR_WS_THEMES . '/css/'.$color)) {
	  $output[$color] = array('id' => $color, 'text' => $color);
	}
  }
  return $output;
}

function convert_cfg($company) {
  // build the new file
  $lines  = '<?php' . "\n";
  $lines .= "/* config.php */" . "\n";
  $lines .= "define('" . $company . "_TITLE','" . gen_pull_db_config_info($company, 'company_name') . "');" . "\n";
  $lines .= "define('DB_SERVER','"              . gen_pull_db_config_info($company, 'db_server') . "');" . "\n";
  $lines .= "define('DB_SERVER_USERNAME','"     . gen_pull_db_config_info($company, 'db_user') . "');" . "\n";
  $lines .= "define('DB_SERVER_PASSWORD','"     . gen_pull_db_config_info($company, 'db_pw') . "');" . "\n";

  $filename = DIR_FS_ADMIN . 'my_files/' . $company . '/config';
  if (!$handle = @fopen($filename . '.php', 'w')) die('Cannot open file (' . $filename . '.php) for writing, check your permissions. This directory and file needs access from the web server for upgrading PhreeBooks to the latest version.');
  if (!fwrite($handle, $lines)) die('Cannot write to file (' . $filename . '.php), check your permissions.');
  fclose($handle);
  if (!unlink($filename . '.txt')) die('Cannot delete file (' . $filename . '.txt). This file needs to be deleted for security reasons.');
}

function gen_pull_db_config_info($database, $key) {
  $filename = DIR_FS_ADMIN . 'my_files/' . $database . '/config.txt';
  $lines = file($filename);
  for ($x = 0; $x < count($lines); $x++) {
	if (trim(substr($lines[$x], 0, strpos($lines[$x], '='))) == $key) {
	  return trim(substr($lines[$x], strpos($lines[$x],'=') + 1, strpos($lines[$x],';') - strpos($lines[$x],'=') - 1));
	}
  }
  return false;
}

/**************************** admin functions ***********************************************/
function admin_check_versions($module, $prerequisites = NULL) {
  global $messageStack;
  $error = false;
  if (is_array($prerequisites) && sizeof($prerequisites) > 0) {
	foreach ($prerequisites as $mod => $version) {
	  if (!$cur_rev = constant('MODULE_' . strtoupper($mod) . '_VERSION')) {
	    $messageStack->add(sprintf(ERROR_MODULE_NOT_INSTALLED, $module, $mod),'error');
		$error = true;
	  } elseif ($cur_rev < $version) {
	    $messageStack->add(sprintf(ERROR_MODULE_VERSION_TOO_LOW, $module, $mod, $version, $cur_rev),'error');
		$error = true;
	  }
	}
  }
  return $error;
}

function admin_install_dirs($dirlist, $path = DIR_FS_MY_FILES) {
  global $messageStack;
  $error = false;
  if (is_array($dirlist)) foreach ($dirlist as $dir) {
	if (!file_exists($path . $dir)) {
	  if (!@mkdir($path . $dir, 0755, true)) $error = $messageStack->add(sprintf(ERROR_CANNOT_CREATE_MODULE_DIR, $path . $dir), 'error');
    }
  }
  return $error;
}

function admin_remove_dirs($dirlist, $path = DIR_FS_MY_FILES) {
  global $messageStack;
  $error = false;
  if (is_array($dirlist)) {
    $temp = array_reverse($dirlist);
	foreach($temp as $dir) {
	  if (!@rmdir($path . $dir)) $error = $messageStack->add(sprintf(ERROR_CANNOT_REMOVE_MODULE_DIR, $path . $dir), 'error');
	}
  }
  return $error;
}

function admin_install_tables($tables) {
  global $db, $messageStack;
  $error = false;
  foreach ($tables as $table => $create_table_sql) {
    if (!db_table_exists($table)) {
	  if (!$db->Execute($create_table_sql)) $error = $messageStack->add(sprintf("Error installing table: %s", $table), 'error');
	}
  }
  return $error;
}

function admin_remove_tables($tables) {
  global $db;
  $error = false;
  if (is_array($tables)) foreach($tables as $table) {
	if (db_table_exists($table)) $db->Execute('DROP TABLE ' . $table);
  }
  return $error;
}

function admin_add_report_heading($doc_title, $doc_group) {
  global $db;
  $result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '$doc_group'");
  if ($result->RecordCount() < 1) {
    $db->Execute("INSERT INTO ".TABLE_PHREEFORM." (parent_id, doc_type, doc_title, doc_group, doc_ext, security, create_date) VALUES 
      (0, '0', '" . $doc_title . "', '".$doc_group."', '0', 'u:0;g:0', now())");
    $id = db_insert_id();
  } else {
    $id = $result->fields['id'];
  }
  return $id;
}

function admin_add_report_folder($parent_id, $doc_title, $doc_group, $doc_ext) {
  global $db;
  $error = false;
  if ($parent_id == '') return true;
  $result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '$doc_group' and doc_ext = '$doc_ext'");
  if ($result->RecordCount() < 1) {
    $db->Execute("INSERT INTO ".TABLE_PHREEFORM." (parent_id, doc_type, doc_title, doc_group, doc_ext, security, create_date) VALUES 
      (".$parent_id.", '0', '" . $doc_title . "', '".$doc_group."', '".$doc_ext."', 'u:0;g:0', now())");
    $id = db_insert_id();
  } else {
    $id = $result->fields['id'];
  }
  return $error;
}

function admin_add_reports($module, $save_path = PF_DIR_MY_REPORTS) {
  $error = false;
  if (file_exists(DIR_FS_MODULES . $module . '/language/' . $_SESSION['language'] . '/reports/')) {
    $read_path = DIR_FS_MODULES . $module . '/language/' . $_SESSION['language'] . '/reports/';
  } elseif (file_exists(DIR_FS_MODULES . $module . '/language/en_us/reports/')) {
    $read_path = DIR_FS_MODULES . $module . '/language/en_us/reports/';
  } else {
    return $error; // nothing to import
  }
  $files = scandir($read_path);
  foreach ($files as $file) if (strtolower(substr($file, -4)) == '.xml') {
    if (!ImportReport('', $file, $read_path, $save_path)) $error = true;
  }
  return $error;
}

/************************ install functions ******************************/
function install_build_co_config_file($company, $key, $value) {
  global $messageStack;
  $filename = DIR_FS_ADMIN . 'my_files/' . $company . '/config.php';
  if (file_exists($filename)) { // update
    $lines = file($filename);
    $found_it = false;
    for ($x = 0; $x < count($lines); $x++) {
	  if (strpos(substr($lines[$x], 0, strpos($lines[$x], ',')), $key)) {
	    $lines[$x] = "define('" . $key . "','" . addslashes($value) . "');" . "\n";
	    $found_it = true;
	    break;
	  }
    }
    if (!$found_it) $lines[] = "define('" . $key . "','" . addslashes($value) . "');" . "\n";
  } else { // create the config file, because it doesn't exist
    $lines = array();
    $lines[] = '<?php' . "\n";
    $lines[] = '/* config.php */' . "\n";
    $lines[] = "define('" . $key . "','" . addslashes($value) . "');" . "\n";
  }
  $line = implode('', $lines);
  if (!$handle = @fopen($filename, 'w')) {
    $messageStack->add(sprintf(MSG_ERROR_CANNOT_WRITE, $filename), 'error');
    return false;
  }
  fwrite($handle, $line);
  fclose($handle);
  return true;
}

/***************************** import/export functions ******************************/
function load_module_xml($module) {
	global $db;
  $error = false;
  $result = trim(file_get_contents(DIR_FS_MODULES . $module . '/' . $module . '.xml'));
  if (!$output = xml_to_object($result)) return false;
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
  global $db;
  $data = file_get_contents($_FILES[$filename]['tmp_name'], "r");
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
	$id = db_insert_id();
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
  global $db, $messageStack;
  $data = file($_FILES[$filename]['tmp_name']);
  // read the header and build array
  if (sizeof($data) < 2) {
    $messageStack->add('The number of lines in the file is to small, a csv file must contain a header line and at least on input line!','error');
	return false;
  }
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
		  $id = db_insert_id();
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
  global $db;
  $output = '';
  $temp   = $structure->Module->Table;
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
    $tag_map = array();
	foreach ($table->Field as $field) $tag_map[$field->Name] = $field->TagName;
	$result = $db->Execute("select * from " . DB_PREFIX . $db_table);
    if ($result->RecordCount() > 0) while (!$result->EOF) {
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
  global $db;
  $output = '';
  $header = false;
  $temp   = $structure->Module->Table;
  foreach ($structure->Module->Table as $table) if ($table->Name == $db_table) {
    $tag_map = array();
	foreach ($table->Field as $field) $tag_map[$field->Name] = $field->TagName;
	$result = $db->Execute("select * from " . DB_PREFIX . $db_table);
    if ($result->RecordCount() > 0) while (!$result->EOF) {
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

/**************************** extra tab/fields functions ***********************************************/
// Syncronizes the fields in the module db with the field parameters 
// (usually only needed for first entry to inventory field builder)
  function xtra_field_sync_list($module = '', $db_table = '') {
	global $db, $messageStack;
	if (!$module || !$db_table) {
	  $messageStack->add('Sync fields called without all necessary parameters!','error');
	}
	// First check to see if inventory field table is synced with actual inventory table
	$temp = $db->Execute("describe " . $db_table);
	while (!$temp->EOF) {
		$table_fields[]=$temp->fields['Field'];
		$temp->MoveNext();
	}
	sort($table_fields);
	$temp = $db->Execute("select field_name from " . TABLE_EXTRA_FIELDS . " where module_id = '" . $module . "' order by field_name");
	while (!$temp->EOF) {
		$field_list[]=$temp->fields['field_name'];
		$temp->MoveNext();
	}
	$needs_sync = false;
	foreach ($table_fields as $key=>$value) {
		if ($value <> $field_list[$key]) {
			$needs_sync = true;
			break;
		}
	}
	if ($needs_sync) {
		if (is_array($field_list)) {
			$add_list = array_diff($table_fields, $field_list);
		} else {
			$add_list = $table_fields;
		}
			$delete_list = '';
		if (is_array($field_list)) $delete_list = array_diff($field_list, $table_fields);
		if (isset($add_list)) {
			foreach ($add_list as $value) { // find the field attributes and copy to field list table
				$myrow = $db->Execute("show fields from " . $db_table . " like '" . $value . "'");
				$Params = array('default' => $myrow->fields['Default']);
				$type = $myrow->fields['Type'];
				if (strpos($type,'(') === false) {
					$data_type = strtolower($type);
				} else {
					$data_type = strtolower(substr($type,0,strpos($type,'(')));
				}
				switch ($data_type) {
					case 'date':      $Params['type'] = 'date'; break;

					case 'time':      $Params['type'] = 'time'; break;
					case 'datetime':  $Params['type'] = 'date_time'; break;
					case 'timestamp': $Params['type'] = 'time_stamp'; break;
					case 'year':      $Params['type'] = 'date'; break;
	
					case 'bigint':
					case 'int':
					case 'mediumint':
					case 'smallint':
					case 'tinyint':
						$Params['type'] = 'integer';
						if ($data_type=='tinyint')   $Params['default'] = '0';
						if ($data_type=='smallint')  $Params['default'] = '1';
						if ($data_type=='mediumint') $Params['default'] = '2';
						if ($data_type=='int')       $Params['default'] = '3';
						if ($data_type=='bigint')    $Params['default'] = '4';
						break;
					case 'decimal':
					case 'double':
					case 'float':
						$Params['type'] = 'decimal';
						if ($data_type=='float')  $Params['default'] = '0';
						if ($data_type=='double') $Params['default'] = '1';
						break;
					case 'tinyblob':
					case 'tinytext':
					case 'char':
					case 'varchar':
					case 'longblob':
					case 'longtext':
					case 'mediumblob':
					case 'mediumtext':
					case 'blob':
					case 'text':
						$Params['type'] = 'text';
						if ($data_type=='varchar' OR $data_type=='char') { // find the actual db length
							$Length = trim(substr($type, strpos($type,'(')+1, strpos($type,')')-strpos($type,'(')-1));
							$Params['length'] = $Length;
						}
						if ($data_type=='tinytext'   OR $data_type=='tinyblob')   $Params['length'] = '255';
						if ($data_type=='text'       OR $data_type=='blob')       $Params['length'] = '65,535';
						if ($data_type=='mediumtext' OR $data_type=='mediumblob') $Params['length'] = '16,777,215';
						if ($data_type=='longtext'   OR $data_type=='longblob')   $Params['length'] = '4,294,967,295';
						break;
					case 'enum':
					case 'set':
						$Params['type'] = 'drop_down';
						$temp = trim(substr($type, strpos($type,'(')+1, strpos($type,')')-strpos($type,'(')-1));
						$selections = explode(',', $temp);
						$defaults = '';
						foreach($selections as $selection) {
							$selection = preg_replace("/'/", '', $selection);
							if ($myrow->fields['Default'] == $selection) $set = 1; else $set = 0;
							$defaults .= $selection . ':' . $selection .':' . $set . ',';
						}
						$defaults = substr($defaults, 0, -1);
						$Params['default'] = $defaults;
						break;
					default:
				}
				$temp = $db->Execute("insert into " . TABLE_EXTRA_FIELDS . " set 
					module_id = '" . $module . "', 
					tab_id = 0, 
					entry_type = '" . $Params['type'] . "', 
					field_name = '" . $value . "', 
					description = '" . $value . "', 
					params = '" . serialize($Params) . "'");  // tab_id = 0 for System category
			}
		}
		if ($delete_list) {
			foreach ($delete_list as $value) {
				$temp = $db->Execute("delete from " . TABLE_EXTRA_FIELDS . " where module_id='" . $module . "' and field_name='" . $value . "'");
			}
		}
	}
	return;
  }

  function xtra_field_get_tabs($module = '') {
    global $db;
    $tab_array = array(0 => TEXT_SYSTEM);
	if (!$module) return $tab_array;
    $result = $db->Execute("select id, tab_name from " . TABLE_EXTRA_TABS . " where module_id = '" . $module . "' order by tab_name");
    while (!$result->EOF) {
      $tab_array[$result->fields['id']] = $result->fields['tab_name'];
      $result->MoveNext();
    }
    return $tab_array;
  }

  function xtra_field_prep_form($form_array) {
	// set the default values
	$form_array['text_length']     = DEFAULT_TEXT_LENGTH;
	$form_array['text_default']    = '';
	$form_array['link_default']    = '';
	$form_array['integer_range']   = '0';
	$form_array['integer_default'] = '0';
	$form_array['decimal_range']   = '0';
	$form_array['decimal_display'] = DEFAULT_REAL_DISPLAY_FORMAT;
	$form_array['decimal_default'] = '';
	$form_array['radio_default']   = '';
	$form_array['date_range']      = '0';
	$form_array['time_range']      = '0';
	$form_array['date_time_range'] = '0';
	$form_array['check_box_range'] = '0';
	switch ($form_array['entry_type']) {
		case 'text':
		case 'html':
			$form_array['text_length']  = $form_array['length'];
			$form_array['text_default'] = $form_array['default'];
			break;
		case 'hyperlink':
		case 'image_link':
		case 'inventory_link':
			$form_array['link_default'] = $form_array['default'];
			break;
		case 'integer':
			$form_array['integer_range']   = $form_array['select'];
			$form_array['integer_default'] = $form_array['default'];
			break;
		case 'decimal':
			$form_array['decimal_range']   = $form_array['select'];
			$form_array['decimal_display'] = $form_array['display'];
			$form_array['decimal_default'] = $form_array['default'];
			break;
		case 'multi_check_box':
		case 'drop_down':
		case 'radio':
		case 'enum':
			$form_array['radio_default'] = $form_array['default'];
			break;
		case 'date':
			$form_array['date_range'] = $form_array['select'];
			break;
		case 'time':
			$form_array['time_range'] = $form_array['select'];
			break;
		case 'date_time':
			$form_array['date_time_range'] = $form_array['select'];
			break;
		case 'check_box':
			$form_array['check_box_range'] = $form_array['select'];
			break;
		case 'time_stamp':
			break;
		default:
	}
	return $form_array;
  }

?>