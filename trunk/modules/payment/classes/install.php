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
//  Path: /modules/payment/classes/install.php
//

class payment_admin {
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	
  function __construct() {
	$this->prerequisites = array( // modules required and rev level for this module to work properly
	  'contacts'   => 3.71,
	  'phreedom'   => 3.6,
	  'phreebooks' => 3.6,
	);
  }

  function install($module) {
	$error = false;
	$methods = array('cod','moneyorder'); // pick a couple of modules to install
	foreach ($methods as $method) {
	  require_once(DIR_FS_ADMIN . 'modules/' . $module . '/methods/' . $method . '/' . $method . '.php');
	  $properties = new $method();
	  write_configure('MODULE_' . strtoupper($module) . '_' . strtoupper($method) . '_STATUS', '1');
	  foreach ($properties->key as $key) write_configure($key['key'], $key['default']);
	  if (method_exists($properties, 'install')) $properties->install();
	}
    return $error;
  }

  function initialize($module) {
  }

  function update($module) {
    global $db, $messageStack;
	$error = false;
  	// load all modules
	$method_dir = DIR_FS_ADMIN . 'modules/' . $module . '/methods/';
	$methods = array();
	if ($dir = @dir($method_dir)) {
	  while ($choice = $dir->read()) {
		if (file_exists($method_dir . $choice . '/' . $choice . '.php') && $choice <> '.' && $choice <> '..') {
		  $methods[] = $choice;
		}
	  }
	  $dir->close();//update keys
	  foreach ($methods as $method) {
	    require_once(DIR_FS_ADMIN . 'modules/' . $module . '/methods/' . $method . '/' . $method . '.php');
	    $properties = new $method();
	    foreach ($properties->keys() as $key) {
	    	if(!defined($key['key'])) write_configure($key['key'], $key['default']);
	    }
	  }
	}
	if (!$error) {
	  write_configure('MODULE_' . strtoupper($module) . '_STATUS', constant('MODULE_' . strtoupper($module) . '_VERSION'));
   	  $messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $module, constant('MODULE_' . strtoupper($module) . '_VERSION')), 'success');
	}
	return $error;
  }

  function remove($module) {
	$error = false;
	// load and remove all modules
	$method_dir = DIR_FS_ADMIN . 'modules/' . $module . '/methods/';
	$methods = array();
	if ($dir = @dir($method_dir)) {
	  while ($choice = $dir->read()) {
		if (file_exists($method_dir . $choice . '/' . $choice . '.php') && $choice <> '.' && $choice <> '..') {
		  $methods[] = $choice;
		}
	  }
	  $dir->close();
	  foreach ($methods as $method) {
	    require_once(DIR_FS_ADMIN . 'modules/' . $module . '/methods/' . $method . '/' . $method . '.php');
	    $properties = new $method();
	    remove_configure('MODULE_' . strtoupper($module) . '_' . strtoupper($method) . '_STATUS');
	    foreach ($properties->keys() as $key) remove_configure($key['key']);
	    if (method_exists($properties, 'remove')) $properties->remove();
	  }
	}
	return $error;
  }

  function load_reports($module) {
  }

  function load_demo() {
  }

}
?>