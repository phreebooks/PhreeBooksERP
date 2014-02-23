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
//  Path: /modules/xml_builder/classes/xml_builder.php
//
namespace xml_builder\classes;
class xml_builder {
  function __construct() {
    $this->output = new \core\classes\objectInfo();
	$this->files  = array();
  }

  function make_dir_tree($source, $cur_path) {
    $error = false;
	$files = scandir($source . $cur_path);
	foreach ($files as $file) {
	  if ($file == '.' || $file == '..') continue;
	  if (is_file($source . $cur_path . $file)){
	    $this->output->Module->Files->File[$cur_path . $file]->Name = $cur_path . $file;
		if (!isset($this->output->Module->Files->File[$cur_path . $file]->Description)) {
	      $this->output->Module->Files->File[$cur_path . $file]->Description = $file;
		}
	  } else { // If it's a folder, run the function again!
	    $this->make_dir_tree($source, $cur_path . $file . "/");
	  }
	} 
	return $error;
  }

  function make_db_info($tables) {
	$error  = false;
	if (is_array($tables)) foreach ($tables as $table => $contents) {
	  if (!isset($this->output->Module->Table[$table]->Name)) {
	    $this->output->Module->Table[$table]->Name           = $table;
	    $this->output->Module->Table[$table]->TagName        = $table;
	    $this->output->Module->Table[$table]->Description    = $table;
	    $this->output->Module->Table[$table]->CanImport      = '1';
	    $this->output->Module->Table[$table]->Engine         = 'MyISAM';
	    $this->output->Module->Table[$table]->DefaultCharset = 'utf8';
	    $this->output->Module->Table[$table]->Collate        = 'utf8_unicode_ci';
	    $this->output->Module->Table[$table]->PrimaryKey     = '';
	    $this->output->Module->Table[$table]->CustomFields   = '0';
	    $this->output->Module->Table[$table]->LinkTable->Name           = '';
	    $this->output->Module->Table[$table]->LinkTable->PrimaryField   = '';
	    $this->output->Module->Table[$table]->LinkTable->DependentField = '';
	  }
	  $lines = explode("\n", $contents);
	  foreach ($lines as $line) {
	    $line   = trim($line);
		$field  = substr($line, 0, strpos($line, ' '));
		if ($field == ')') $field = 'params'; 
		$line   = trim(substr($line, strpos($line,' ')));
		switch ($field) {
		  case 'CREATE':
		  case 'Create':
		  case 'create': // do noting
		    break;
		  case ')': // properties line
		    break;
		  case 'PRIMARY':
		    $line   = trim(substr(trim($line), strpos(trim($line), ' '))); // remove 'KEY'
			$primary_key = trim(str_replace(array('(','`',')'), '', $line));
			$this->output->Module->Table[$table]->PrimaryKey = $primary_key;
		    break;
		  case 'key':
		  case 'KEY':
		    $index_key = (substr($line, -1) == ',') ? substr($line, 0, -1) : $line;
			$this->output->Module->Table[$table]->Key[$index_key] = $index_key;
		    break;
		  case 'params': // the properties line
		    $line = (substr($line, -1) == ';') ? substr($line, 0, -1) : $line;
		    $temp = explode(' ', $line);
			foreach ($temp as $value) {
			  $t = explode('=', $value);
			  switch ($t[0]) {
			    case 'ENGINE':  $this->output->Module->Table[$table]->Engine         = $t[1]; break;
			    case 'CHARSET': $this->output->Module->Table[$table]->DefaultCharset = $t[1]; break;
			    case 'COLLATE': $this->output->Module->Table[$table]->Collate        = $t[1]; break;
				default:
			  }
			}
			break;
		  default: // Regular field
		    $field  = str_replace('`', '', $field);
			$type   = substr($line, 0, strpos($line, ' '));
		    $line   = trim(substr($line, strpos($line, ' ')));
		    $params = (substr($line, -1) == ',') ? substr($line, 0, -1) : $line;
			if (!isset($this->output->Module->Table[$table]->Field[$field])) {
			  $this->output->Module->Table[$table]->Field[$field]->Name        = $field;
			  $this->output->Module->Table[$table]->Field[$field]->TagName     = $field;
			  $this->output->Module->Table[$table]->Field[$field]->Type        = $type;
			  $this->output->Module->Table[$table]->Field[$field]->Description = $field;
			  $this->output->Module->Table[$table]->Field[$field]->Properties  = $params;
			  $this->output->Module->Table[$table]->Field[$field]->CanImport   = '1';
			  $this->output->Module->Table[$table]->Field[$field]->Required    = '1';
			} else { // just update the table information
			  $this->output->Module->Table[$table]->Field[$field]->Name        = $field;
			  $this->output->Module->Table[$table]->Field[$field]->Type        = $type;
			  $this->output->Module->Table[$table]->Field[$field]->Properties  = $params;
			}
			break;
		}
	  }
	}
/*
CREATE TABLE `assets` (
 `id` int(11) NOT NULL AUTO_INCREMENT, 
 `asset_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
 `inactive` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0', 
 `asset_type` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'si', 
 `purch_cond` enum('n','u') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'n', 
 `serial_number` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '', 
 `account_maintenance` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL, 
 `asset_cost` float NOT NULL DEFAULT '0', 
 `depreciation_method` enum('a','f','l') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'f', 
 `full_price` float NOT NULL DEFAULT '0', 
 `maintenance_date` date NOT NULL DEFAULT '0000-00-00', 
 `terminal_date` date NOT NULL DEFAULT '0000-00-00', 
 `asset_group` varchar(4) COLLATE utf8_unicode_ci DEFAULT '', 
 PRIMARY KEY (`id`) 
 ) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
*/
	return $error;
  }

  function build_xml_string($objModule) {
    $error = false;
	$xmlString = object_to_xml($objModule);
	if ($error) return $error;
	return $xmlString;
  }

}

?>