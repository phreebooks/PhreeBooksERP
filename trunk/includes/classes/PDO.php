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
//  Path: /includes/classes/PDO.php
//
namespace core\classes;
class PDO extends \PDO {
	public $count_queries = 0;
	public $total_query_time = 0;

	public function __construct($dsn, $username="", $password="", $driver_options = array()) {
        parent::__construct($dsn,$username,$password, $driver_options);
    }

	public function query($query) {
		\core\classes\messageStack::debug_log("excecuting query: $query");
		$time_start = explode(' ', microtime());
		$temp =  parent::query($query);
		$time_end = explode (' ', microtime());
		$query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
		$this->total_query_time += $query_time;
		$this->count_queries++;
		return $temp;
	}

	public function prepare ($query, $options = NULL){
		\core\classes\messageStack::debug_log("excecuting query: $query");
		$time_start = explode(' ', microtime());
		$temp =  parent::prepare($query);
		$time_end = explode (' ', microtime());
		$query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
		$this->total_query_time += $query_time;
		$this->count_queries++;
		return $temp;
	}

	public function exec ($query){
		\core\classes\messageStack::debug_log("excecuting query: $query");
		$time_start = explode(' ', microtime());
		$temp =  parent::exec($query);
		$time_end = explode (' ', microtime());
		$query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
		$this->total_query_time += $query_time;
		$this->count_queries++;
		return $temp;
	}

	/**
	 * check is table exists in database
	 * @param string $table_name
	 * @return boolean
	 */
	public function table_exists($table_name) {
		\core\classes\messageStack::debug_log("looking for match {$row['Field']} == $field_name");
		if ($this->query("SHOW TABLES like '$table_name'") != false) return true;
		return false;
	}

	/**
	 * check is field exists in table
	 * @param unknown $table_name
	 * @param unknown $field_name
	 * @return boolean
	 */
	public function field_exists($table_name, $field_name) {
		$result = $this->prepare("DESCRIBE $table_name");
		$result->execute();
		while ($row = $result->fetch(\PDO::FETCH_ASSOC)){
			\core\classes\messageStack::debug_log("looking for match {$row['Field']} == $field_name");
			if  ($row['Field'] == $field_name) return true;
		}
		return false;
	}
	
	/**
	 * function stores configuration values and updates constants and or cache.
	 * @param string $constant
	 * @param string $value
	 */
	
	function write_configure($constant, $value = '') {
		if (!$constant) throw new \core\classes\userException("contant isn't defined for value: $value");
		$result = $this->query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '$constant'");
		if ($result->fetch(\PDO::FETCH_NUM) == 0) {
			$sql_array = array('configuration_key'  => $constant, 'configuration_value'=> $value);
			$sql = $this->prepare("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value) VALUES (:configuration_key, :configuration_value)");
			$sql->execute(array(':configuration_key'=>$constant, ':configuration_value'=>$value));
		} elseif ($result['configuration_value'] <> $value) {
			$this->exec("UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '$value' where configuration_key = '$constant'");
		}
		if (function_exists('apc_load_constants')) {// rebuild cache
			$sql = $this->prepare("select configuration_key, configuration_value from " . TABLE_CONFIGURATION );
			$array = array ();
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
				$array[ $result['configuration_key'] ] = $result['configuration_value'];
			}
			apc_define_constants("configuration", $array, true);
		} else{ // cache not installed just define constant
			define($constant, $value);
		}
	}
	
	/**
	 * function removes constant from configuration values and updates cache if installed.
	 * @param string $constant
	 */
	
	function remove_configure($constant){
		global $admin;
		if (!$constant) throw new \core\classes\userException("There is no constant to remove");
		$this->exec("delete from " . TABLE_CONFIGURATION . " where configuration_key = '$constant'");
		if (function_exists('apc_load_constants')) {// rebuild cache
			$result = $this->prepare("select configuration_key, configuration_value from " . TABLE_CONFIGURATION );
			$array = array ();
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
				$array[ $result['configuration_key'] ] = $result['configuration_value'];
			}
			apc_define_constants("configuration", $array, true);
		}
		return true;
	}

}

?>