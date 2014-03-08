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
//  Path: /includes/classes/user.php
//
namespace core\classes;
class user {
	private $language  = 'en_us';
	
	function __construct(){
	}
	
	/** 
	 * returns true if user is logged in. 
	 */
	final static public function is_validated(){
		if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] != ''){
			return true;
		} else {
			return false;	
		}
	}
	
	/**
	 * returns the current language and sets it in the Session variable.
	 */
	
	final static public function get_language(){
		if (isset($_REQUEST['language'])) {
			 $_SESSION['language'] = $_REQUEST['language']; 
		} elseif (!isset($_SESSION['language'])) { 
			$_SESSION['language'] = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : $this->language; 
		}
		return $_SESSION['language'];
	}
	
	/**
	 * function will return current security level, will check if it is set.
	 * this function will not validate nor throw exceptions. 
	 * @param unknown_type $token
	 */
	
	final static function security_level($token){
		if (isset($_SESSION['admin_security'][$token])) return $_SESSION['admin_security'][$token];
		return 0;
	} 
	
	/**
	 * This function returns the current security_level of the requested token.
	 * If token isn't set a exception will be thrown 
	 * @param int $token
	 * @param bool $user_active
	 * @throws Exception
	 */
	
	final static function validate($token = 0, $user_active = false) {
  		$security_level = $_SESSION['admin_security'][$token];
  		if (!in_array($security_level, array(1,2,3,4)) && !$user_active) throw new \Exception(ERROR_NO_PERMISSION, 10, $e);
  		return $user_active ? 1 : $security_level;
	}
	
	/**
	 * This function will check if user has security clearance if not a exception will be throw.
	 * @param int $security_level
	 * @param int $required_level
	 */
	
	final static function validate_security($current_security_level = 0, $required_level = 1) {
		if ($current_security_level < $required_level) throw new \Exception(ERROR_NO_PERMISSION);
	}
	
	/**
	 * this will return a array of permissions
	 * @param string $imploded_permissions
	 * @return array keyed permission levels.
	 */
	final static function parse_permissions($imploded_permissions) {
		$result = array();
		$temp = explode(',', $imploded_permissions);
		if (is_array($temp)) {
	  		foreach ($temp as $imploded_entry) {
				$entry = explode(':', $imploded_entry);
				$result[$entry[0]] = $entry[1];
			}
		}
		return $result;
  	}
	
	
}