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
//  Path: /modules/xml_builder/pages/main/pre_process.php
//
// This script updates the xml module information file
$security_level = validate_user(SECURITY_ID_XML_BUILDER);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'classes/xml_builder.php');
require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
/**************   page specific initialization  *************************/
$working  = new xml_builder();
$mod_xml  = new backup();
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	validate_security($security_level, 2);
  	// read the input variables
	$mod       = $_POST['mod'];
	$mod_admin = $mod . '_admin';
	require_once(DIR_FS_MODULES . $mod . '/classes/install.php');
	$mod_info  = new $mod_admin;
	// read the existing xml file to set as base, if it exists
	if (file_exists(DIR_FS_MODULES . $mod . '/' . $mod . '.xml')) {
	  $working->output = xml_to_object(file_get_contents(DIR_FS_MODULES . $mod . '/' . $mod . '.xml'));
	  // fix some lists
	  if (!is_array($working->output->Module->Table)) $working->output->Module->Table = array($working->output->Module->Table);
	  $temp = array();
	  foreach ($working->output->Module->Table as $tkey => $table) {
	    $tname = $table->Name;
		$temp[$tname] = $working->output->Module->Table[$tkey]; // copy most of the info
	    // index keys
		if (isset($table->Key)) {
		  if (!is_array($table->Key)) $table->Key = array($table->Key);
		  foreach ($table->Key as $kkey => $index) {
		    $kname = $index->Name;
		    $temp[$tname]->Key[$kname] = $table->Key[$kkey];
		    unset($temp[$tname]->Key[$kkey]); // will be set next
		  }
		}
		// fields
	    if (!is_array($table->Field)) $table->Field = array($table->Field);
		foreach ($table->Field as $fkey => $field) {
		  $fname = $field->Name;
		  $temp[$tname]->Field[$fname] = $table->Field[$fkey];
		  unset($temp[$tname]->Field[$fkey]); // will be set next
		}
	  }
	  $working->output->Module->Table = $temp;
	  // convert files
	  $temp = array();
	  if (is_array($working->output->Module->Files->File)) foreach ($working->output->Module->Files->File as $file) {
	    $fname                      = $file->Name;
		$temp[$fname]->Name         = $file->Name;
		$temp[$fname]->Description  = $file->Description;
	  }
	  $working->output->Module->Files->File = $temp;
	} else { // intialize some values
	  $working->output->Module->Name        = $mod;
	  $working->output->Module->Description = $mod;
	  $working->output->Module->Path        = 'modules/' . $mod;
	}
//echo 'core object = '; print_r($working->output); echo '<br><br>';
	// read the dirs
	if ($working->make_dir_tree(DIR_FS_MODULES . $mod . '/', '')) $error = true;
	// read the db
	if ($working->make_db_info($mod_info->tables)) $error = true;
	// build the output string
//echo 'result object = '; print_r($working->output); echo '<br><br>';
	$xmlString  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
	$xmlString .= $working->build_xml_string($working->output);
//echo 'writing: ' . htmlspecialchars($xmlString) . '<br>';
	// store it in a file
	$handle   = fopen(DIR_FS_MY_FILES . $mod . '.xml', "w");
	$contents = fwrite($handle, $xmlString, strlen($xmlString));
	fclose($handle);
	// zip it and download
	$mod_xml->source_dir  = DIR_FS_MY_FILES;
	$mod_xml->source_file = $mod . '.xml';
	$mod_xml->dest_dir    = DIR_FS_MY_FILES;
	$mod_xml->dest_file   = $mod . '_xml_info.zip';
	$mod_xml->make_zip('file', $mod . '.xml');
	$mod_xml->download(DIR_FS_MY_FILES, $mod . '_xml_info.zip');
	break;
  default:
}
/*****************   prepare to display templates  *************************/
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', XML_BUILDER_PAGE_TITLE);

?>