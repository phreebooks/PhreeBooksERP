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
	 * checks if user is logged in.
	 * @return bool if user is logged in.
	 */

	final static public function is_validated(){
		global $page_template;
		if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] == ''){
			//allow the user to continu to with the login action.
			if (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('validateLogin','pw_lost_sub','pw_lost_req'))){
				self::load_companies();
				self::load_languages();
				if (!isset($_SESSION['company'])) {
					if (isset($_REQUEST['company'])) {
						$_SESSION['company'] = $_REQUEST['company'];
					} else { // find default company
						$_SESSION['company'] = defined('DEFAULT_COMPANY') ? DEFAULT_COMPANY : '';
						if (isset($_COOKIE['pb_company'])) $_SESSION['company'] = $_COOKIE['pb_company'];
					}
				}
				if ( $_SESSION['company'] == ''){
					reset($_SESSION['companies']);
					$_SESSION['company'] = key($_SESSION['companies']);
				}
				if (!isset($_SESSION['language'])) {
					if (isset($_REQUEST['language'])) {
						$_SESSION['language'] = $_REQUEST['language'];
					} else {
						$_SESSION['language'] = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en_us';
						if (isset($_COOKIE['pb_language'])) $_SESSION['language'] = $_COOKIE['pb_language'];
					}
				}
				if ( $_SESSION['language'] == ''){
					reset($_SESSION['languages']);
					$_SESSION['language'] = key($_SESSION['languages']);
				}
				// load general language translation, Check for global define overrides first
				$path = DIR_FS_MODULES . "phreedom/custom/language/{$_SESSION['language']}/language.php";
				if (file_exists($path)) { require_once($path);}
				$path = DIR_FS_MODULES . "phreedom/language/{$_SESSION['language']}/language.php";
				if (file_exists($path)) { require_once($path);}
				else { require_once(DIR_FS_MODULES . "phreedom/language/en_us/language.php");}
				$template = $_REQUEST['action'] == 'pw_lost_req' ? 'template_pw_lost' : 'template_login';
				throw new \core\classes\userException(SORRY_YOU_ARE_LOGGED_OUT, "phreedom", "main", $template);
			}
		}
		$path = DIR_FS_MODULES . "phreedom/custom/language/{$_SESSION['language']}/language.php";
		if (file_exists($path)) { require_once($path); }
		$path = DIR_FS_MODULES . "phreedom/language/{$_SESSION['language']}/language.php";
		if (file_exists($path)) { require_once($path); }
		else { require_once(DIR_FS_MODULES . "phreedom/language/en_us/language.php"); }
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
  		if (!in_array($security_level, array(1,2,3,4)) && !$user_active) throw new \core\classes\userException(ERROR_NO_PERMISSION, 10, $e);
  		return $user_active ? 1 : $security_level;
	}

	/**
	 * This function will check if user has security clearance if not a exception will be throw.
	 * @param int $security_level
	 * @param int $required_level
	 */

	final static function validate_security($current_security_level = 0, $required_level = 1) {
		if ($current_security_level < $required_level) throw new \core\classes\userException(ERROR_NO_PERMISSION);
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

  	final static function load_companies() {
		$contents = @scandir(DIR_FS_MY_FILES);
		if($contents === false) throw new \core\classes\userException("couldn't read or find directory ". DIR_FS_MY_FILES);
		foreach ($contents as $file) {
			if ($file <> '.' && $file <> '..' && is_dir(DIR_FS_MY_FILES . $file)) {
			  	if (file_exists(DIR_FS_MY_FILES   . $file . '/config.php')) {
					require_once (DIR_FS_MY_FILES . $file . '/config.php');
					$_SESSION['companies'][$file] = array(
				  	  'id'   => $file,
				  	  'text' => constant($file . '_TITLE'),
					);
			  	}
			}
		}
	}

	final static function load_languages() {//@todo rewrite for other language files and loading of core language
		$contents = @scandir('modules/phreedom/language/');
		if($contents === false) throw new \core\classes\userException("couldn't read or find directory modules/phreedom/language/");
		foreach ($contents as $lang) {
			if ($lang <> '.' && $lang <> '..' && is_dir('modules/phreedom/language/'. $lang) && file_exists("modules/phreedom/language/$lang/language.php")) {
		  		if ($config_file = file("modules/phreedom/language/$lang/language.php")) {
		  			foreach ($config_file as $line) {
		  				if (strstr($line,'\'LANGUAGE\'') !== false) {
			    			$start_pos     = strpos($line, ',') + 2;
			    			$end_pos       = strpos($line, ')') + 1;
				    		$language_name = substr($line, $start_pos, $end_pos - $start_pos);
				    		break;
			  			}
		  			}
		  			$_SESSION['languages'][$lang] = array('id' => $lang, 'text' => $language_name);
		  		}
			}
		}
	}

}