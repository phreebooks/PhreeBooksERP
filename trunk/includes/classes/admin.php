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
//  Path: /includes/classes/admin.php
//
namespace core\classes;
class admin {
	public $id;
	public $text;
	public $description;
	public $sort_order  	= 99;
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	public $dashboards		= array();// holds all classes in a array
	public $methods			= array();// holds all classes in a array
	public $status			= 1.0; // stores the moduel status
	public $version			= 1.0; // stores availible version of the module
	public $installed		= false;
	public $core			= false;

	/**
	 * this is the general construct function called when the class is created.
	 */
	function __construct(){
		if (defined('MODULE_' . strtoupper($this->id) . '_STATUS')){
			$this->installed = true;
			$this->status  = constant('MODULE_' . strtoupper($this->id) . '_STATUS');
		}
		$this->methods 		= $this->return_all_methods('methods');
		$this->dashboards 	= $this->return_all_methods('dashboards');
	}

	/**
	 * this will install a module
	 * @param bool $demo
	 * @param string $path_my_files location to the my_files folder
	 */

	function install($path_my_files, $demo = false) {
		$this->check_prerequisites_versions();
		$this->install_dirs($path_my_files);
		$this->install_update_tables();
		foreach ($this->keys as $key => $value) write_configure($key, $value);
  		if ($demo) $this->load_demo(); // load demo data
  		$this->load_reports();
  		admin_add_reports($this->id);
  		$this->after_install();
		foreach ($this->methods as $method) {
	  		write_configure('MODULE_' . strtoupper($this->id) . '_' . strtoupper($method->id) . '_STATUS', $method->version);
	  		foreach ($method->key as $key) write_configure($key['key'], $key['default']);
	  		if (method_exists($method, 'install')) $method->install();
		}
		foreach ($this->dashboards as $dashboard) {
	    	foreach ($dashboard->key() as $key) write_configure($key['key'], $key['default']);
	    	if (method_exists($dashboard, 'install')) $dashboard->install();
		}
		$this->installed = true;
		$this->status 	 = $this->version;
	}

	/**
	 * this function will be called after you log in.
	 */

  	function initialize() {
  	}

  	/**
  	 * this function will be called when a module is upgraded.
  	 * it will update tables directories and keys
  	 */

	function upgrade() {
		$this->check_prerequisites_versions();
		$this->install_dirs($path_my_files);
		$this->install_update_tables();
		foreach ($this->keys as $key => $value) if(!defined($key)) write_configure($key, $value);
		foreach ($this->methods as $method) {
			if ($method->installed && $method->should_update()){
	    		foreach ($method->key() as $key) if(!defined($key['key'])) write_configure($key['key'], $key['default']);
				if (method_exists($method, 'upgrade')) $method->upgrade();
				write_configure('MODULE_' . strtoupper($this->id) . '_' . strtoupper($method->id) . '_STATUS', $method->version);
				gen_add_audit_log(sprintf(GEN_LOG_INSTALL_SUCCESS, $method->text) . TEXT_UPDATE, $method->version);
	   			$messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $method->id, $method->version), 'success');
			}
		}
		$this->status = $this->version;
	}

	function delete($path_my_files) {
		if ($this->core) throw new \Exception("can not delete core module " .$this->text);
		foreach ($this->methods as $method) {
			if ($method->installed){
	    		if (method_exists($method, 'delete')) $method->delete();
			}
	  	}
		foreach ($this->dashboards as $dashboard) {
	    	$dashboard->delete();
		}
	    $this->remove_tables();
	    $this->remove_dirs($path_my_files);
	    remove_configure('MODULE_' . strtoupper($this->id) . '_STATUS');
	    $this->installed = false;
	}

  	function release_update($version, $path = '') {
    	global $db;
		if (file_exists($path)) { include_once ($path); }
		write_configure('MODULE_' . strtoupper($this->id) . '_STATUS', $version);
		return $version;
  	}

	function load_reports() {
	}

	function load_demo() {
	}

	function should_update(){
		if (!$this->installed) return false;
		if (version_compare($this->version, constant('MODULE_' . strtoupper($this->id) . '_STATUS')) < 0 ) return true;
		else return false;
	}

	/**
	 * This function checks if a module is allowed to install using the prerequisites
	 * @throws \Exception
	 */

	function check_prerequisites_versions() {
		global $admin_classes;
		if (is_array($this->prerequisites) && sizeof($this->prerequisites) > 0) {
			foreach ($this->prerequisites as $module_class => $RequiredVersion) {
		  		if ( $admin_classes[$module_class]->installed == false) throw new \Exception (sprintf(ERROR_MODULE_NOT_INSTALLED, $this->id, $admin_classes[$module_class]->id));
		  		if ( version_compare($admin_classes[$module_class]->version, $RequiredVersion) < 0 ) throw new \Exception (sprintf(ERROR_MODULE_VERSION_TOO_LOW, $this->id, $admin_classes[$module_class]->id, $RequiredVersion, $this->version));
			}
		}
		return true;
	}

	/**
	 * this function installes the required dirs under my_files\mycompany
	 * @throws \Exception
	 */

	function install_dirs($path_my_files) {
		foreach ($this->dirlist as $dir) {
			validate_path($path_my_files . $dir, 0755);
	  	}
	}

	function remove_dirs($path_my_files) {
		foreach(array_reverse($this->dirlist) as $dir) {
			if (!@rmdir($path_my_files . $dir)) throw new \Exception (sprintf(ERROR_CANNOT_REMOVE_MODULE_DIR, $path_my_files . $dir));
	  	}
	}

	/**
	 * This funtion installs the tables.
	 * If table exists nothing will happen.
	 * @throws \Exception
	 */
	function install_update_tables() {
	  	global $db;
	  	foreach ($this->tables as $table => $create_table_sql) {
	    	if (!db_table_exists($table)) {
		  		if (!$db->Execute($create_table_sql)) throw new \Exception (sprintf("Error installing table: %s", $table));
			}
	  	}
	}

	function remove_tables() {
	  	global $db;
	  	foreach ($this->tables as $table) {
			if (db_table_exists($table)){
				if ($db->Execute('DROP TABLE ' . $table)) throw new \Exception (sprintf("Error deleting table: %s", $table));
			}
	  	}
	}

	function add_report_heading($doc_title, $doc_group) {
	  	global $db;
	  	$result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '$doc_group'");
	  	if ($result->RecordCount() < 1) {
	    	$db->Execute("INSERT INTO ".TABLE_PHREEFORM." (parent_id, doc_type, doc_title, doc_group, doc_ext, security, create_date) VALUES
	      	  (0, '0', '" . $doc_title . "', '".$doc_group."', '0', 'u:0;g:0', now())");
	    	return db_insert_id();
	  	} else {
	    	return $result->fields['id'];
	  	}
	}

	function add_report_folder($parent_id, $doc_title, $doc_group, $doc_ext) {
	  	global $db;
	  	if ($parent_id == '') throw new \Exception("parent_id isn't set for document $doc_title");
	  	$result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '$doc_group' and doc_ext = '$doc_ext'");
	  	if ($result->RecordCount() < 1) {
	    	$db->Execute("INSERT INTO ".TABLE_PHREEFORM." (parent_id, doc_type, doc_title, doc_group, doc_ext, security, create_date) VALUES
	      	  (".$parent_id.", '0', '" . $doc_title . "', '".$doc_group."', '".$doc_ext."', 'u:0;g:0', now())");
	  	}
	}

	/**
	 * this loads all methods/dashboards that are in a modules sub folder
	 * @param string $type
	 * @return multitype:|multitype:unknown
	 */
	function return_all_methods($type ='methods') {
	    $choices     = array();
	    $method_dir  = DIR_FS_MODULES . "$this->id/$type/";
	    if ($methods = @scandir($method_dir)) foreach ($methods as $method) {
			if ($method == '.' || $method == '..' || !is_dir($method_dir . $method)) continue;
		  	load_method_language($method_dir, $method);
		  	$class = "\\$this->id\\$type\\$method\\$method";
		  	$choices[$method] = new $class;
	    }
		uasort($choices, "arange_object_by_sort_order");
	    return $choices;
	}
}
?>