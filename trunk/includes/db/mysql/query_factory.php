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
//  Path: /includes/db/mysql/query_factory.php
//

class queryFactory {
	public $count_queries 	  	= 0;
    public $total_query_time 	= 0;
	public $database		  	= '';
	public $db_connected		= false;
	public $link			  	= '';
	public $error_number 		= '';
    public $error_text			= '';

  function connect($zf_host, $zf_user, $zf_password, $zf_database) {
    $this->database = $zf_database;
    // pconnect disabled
    $this->link = @mysql_connect($zf_host, $zf_user, $zf_password, true);
    if ($this->link) {
      if (@mysql_select_db($zf_database, $this->link)) {
        $this->db_connected = true;
		// set the character set
		mysql_query("SET character_set_results='utf8', character_set_client='utf8', character_set_connection='utf8', character_set_database='utf8', character_set_server='utf8'", $this->link);
//		mysql_query("SET NAMES utf8", $this->link); 
//		mysql_query("SET CHARACTER SET utf8", $this->link); 
//		mysql_set_charset('utf8', $this->link); 
        return true;
      } else {
      	throw new \Exception(mysql_error());
      }
    } else {
      throw new \Exception(mysql_error());
    }
    throw new \Exception("database isn't connected.");
  }

  function selectdb($zf_database) {
    @mysql_select_db($zf_database, $this->link);
  }

  function transStart() {
    $this->Execute("START TRANSACTION");
  }

  function transCommit() {
    $this->Execute("COMMIT");
  }

  function transRollback() {
    $this->Execute("ROLLBACK");
  }

  function prepare_input($zp_string) {
    if (function_exists('mysql_real_escape_string')) {
      return mysql_real_escape_string($zp_string);
    } elseif (function_exists('mysql_escape_string')) {
      return mysql_escape_string($zp_string);
    } else {
      return addslashes($zp_string);
    }
  }

  function close() {
    @mysql_close($this->link);
  }

  function set_error($zp_err_num, $zp_err_text, $zp_fatal = true) {
    $this->error_number = $zp_err_num;
    $this->error_text = $zp_err_text;
    if ($zp_fatal && $zp_err_num != 1141) {
      $this->show_error();
      die();
    }
  }

  function show_error() {
    echo $this->error_number . ' ' . $this->error_text;
  }

  function Execute($zf_sql, $zf_limit = false, $zf_cache = false, $zf_cachetime = 0) {
    global $zc_cache, $messageStack;
    if ($zf_limit) {
      $zf_sql = $zf_sql . ' LIMIT ' . $zf_limit;
    }
    if ( $zf_cache AND $zc_cache->sql_cache_exists($zf_sql) AND !$zc_cache->sql_cache_is_expired($zf_sql, $zf_cachetime) ) {
      $obj = new queryFactoryResult;
      $obj->cursor = 0;
      $obj->is_cached = true;
      $obj->sql_query = $zf_sql;
      $zp_result_array = $zc_cache->sql_cache_read($zf_sql);
      $obj->result = $zp_result_array;
      if (sizeof($zp_result_array) > 0 ) {
        $obj->EOF = false;
        while (list($key, $value) = each($zp_result_array[0])) {
          $obj->fields[$key] = $value;
        }
        return $obj;
      } else {
        $obj->EOF = true;
      }
    } elseif ($zf_cache) {
      $zc_cache->sql_cache_expire_now($zf_sql);
      $time_start = explode(' ', microtime());
      $obj = new queryFactoryResult;
      $obj->sql_query = $zf_sql;
      if (!$this->db_connected) throw new \Exception(DB_ERROR_NOT_CONNECTED);
      $zp_db_resource = @mysql_query($zf_sql, $this->link);
      if (!$zp_db_resource) throw new \Exception(@mysql_error());
      $obj->resource = $zp_db_resource;
      $obj->cursor = 0;
      $obj->is_cached = true;
      if ($obj->RecordCount() > 0) {
        $obj->EOF = false;
        $zp_ii = 0;
		while (!$obj->EOF) {
			  $zp_result_array = @mysql_fetch_array($zp_db_resource);
			  if ($zp_result_array) {
				while (list($key, $value) = each($zp_result_array)) {
				  if (!preg_match('/^[0-9]/', $key)) {
					$obj->result[$zp_ii][$key] = $value;
				  }
				}
			  } else {
				$obj->Limit = $zp_ii;
				$obj->EOF = true;
			  }
			  $zp_ii++;
		}
        while (list($key, $value) = each($obj->result[$obj->cursor])) {
          if (!preg_match('/^[0-9]/', $key)) {
            $obj->fields[$key] = $value;
	  	  }
        }
        $obj->EOF = false;
      } else {
	    $obj->EOF = true;
      }
      $zc_cache->sql_cache_store($zf_sql, $obj->result);
      $time_end = explode (' ', microtime());
      $query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
      $this->total_query_time += $query_time;
      $this->count_queries++;
      return($obj);
    } else {
      $time_start = explode(' ', microtime());
      $obj = new queryFactoryResult;
      if (!$this->db_connected) throw new \Exception( DB_ERROR_NOT_CONNECTED);
      $zp_db_resource = @mysql_query($zf_sql, $this->link);
      if (!$zp_db_resource) {
		if (method_exists($messageStack, 'debug')) {
		  $messageStack->debug("\n\nThe failing sql was: " . $zf_sql);
		  $messageStack->debug("\n\nmySQL returned: " . @mysql_errno($this->link) . ' ' . @mysql_error($this->link));
		  if (defined('FILENAME_DEFAULT')) {
		    $messageStack->write_debug();
		    throw new \Exception('The last transaction had a SQL database error.');
		  } else{
		  	echo str_replace("\n", '<br />', $messageStack->debug_info); 
			ob_end_flush();
  			session_write_close();
		  }
		} else { 
		    echo str_replace("\n", '<br />', $messageStack->debug_info); 
			ob_end_flush();
  			session_write_close();
		}
	  }
      $obj->resource = $zp_db_resource;
      $obj->cursor = 0;
      if ($obj->RecordCount() > 0) {
        $obj->EOF = false;
        $zp_result_array = @mysql_fetch_array($zp_db_resource);
        if ($zp_result_array) {
          while (list($key, $value) = each($zp_result_array)) {
            if (!preg_match('/^[0-9]/', $key)) {
              $obj->fields[$key] = $value;
            }
          }
          $obj->EOF = false;
        } else {
          $obj->EOF = true;
        }
      } else {
        $obj->EOF = true;
      }

      $time_end = explode (' ', microtime());
      $query_time = $time_end[1] + $time_end[0] - $time_start[1] - $time_start[0];
      $this->total_query_time += $query_time;
      $this->count_queries++;
//$messageStack->add('query execution time = ' . $query_time . ' and sql = ' . $zf_sql, 'caution');
      return($obj);
    }
  }

  function Execute_return_error($zf_sql) {
	$time_start = explode(' ', microtime());
	$obj = new queryFactoryResult;
	if (!$this->db_connected) $this->set_error('0', DB_ERROR_NOT_CONNECTED);
	$zp_db_resource = @mysql_query($zf_sql, $this->link);
	if (!$zp_db_resource) {
		$this->set_error(@mysql_errno($this->link), @mysql_error($this->link), false);
	} else {
		$this->set_error(0, '', false); // clear the error
	}
	$obj->resource = $zp_db_resource;
	if ($obj->RecordCount() > 0) {
		$obj->EOF = false;
		$zp_result_array = @mysql_fetch_array($zp_db_resource);
		if ($zp_result_array) {
		  while (list($key, $value) = each($zp_result_array)) {
			if (!preg_match('/^[0-9]/', $key)) $obj->fields[$key] = $value;
		  }
		  $obj->EOF = false;
		} else {
		  $obj->EOF = true;
		}
	} else {
		$obj->EOF = true;
	}
	
	$time_end = explode (' ', microtime());
	$query_time = $time_end[1] + $time_end[0] - $time_start[1] - $time_start[0];
	$this->total_query_time += $query_time;
	$this->count_queries++;
	return($obj);
  }

  function insert_ID() {
    return @mysql_insert_id($this->link);
  }

}

class queryFactoryResult {
	public $cursor		= 0;
	public $fields 		= array();
	public $is_cached	= false;
	public $EOF			= false;
	public $result		= array();
	public $resource	= '';
 
  function MoveNext() {
    global $zc_cache;
    $this->cursor++;
    if ($this->is_cached) {
      if ($this->cursor >= sizeof($this->result)) {
        $this->EOF = true;
      } else {
        while(list($key, $value) = each($this->result[$this->cursor])) {
	  	  $this->fields[$key] = $value;
		}
      }
    } else {
      $zp_result_array = @mysql_fetch_array($this->resource);
      if (!$zp_result_array) {
        $this->EOF = true;
      } else {
        while (list($key, $value) = each($zp_result_array)) {
          if (!preg_match('/^[0-9]/', $key)) $this->fields[$key] = $value;
        }
      }
    }
  }

  function RecordCount() { // use for SELECT queries
    return @mysql_num_rows($this->resource);
  }

  function AffectedRows() { // use for INSERT, UPDATE or DELETE queries
    return @mysql_affected_rows();
  }

  function Move($zp_row) {
    global $db;
    if (@mysql_data_seek($this->resource, $zp_row)) {
      $zp_result_array = @mysql_fetch_array($this->resource);
        while (list($key, $value) = each($zp_result_array)) {
          $this->fields[$key] = $value;
        }
      @mysql_data_seek($this->resource, $zp_row);
      $this->EOF = false;
      return;
    } else {
      $this->EOF = true;
    }
  }
}
?>