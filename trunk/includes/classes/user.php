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
//  Path: /includes/classes/user.php
//
namespace core\classes;
class user {
	public $admin_id;
	public $language;
	public $company;
	private $config  = array();
	private $last_activity;
	public $languages = array();
	public $companies = array();
	private $SESSION_TIMEOUT = 360;

	function __construct(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if (empty($_SESSION['language']) || !is_object($_SESSION['language'])) $_SESSION['language'] = new \core\classes\language();
		if ($this->last_activity == '') $this->last_activity = time();
		if (defined('DEFAULT_COMPANY')) {
			$this->company =  DEFAULT_COMPANY;
		}else{
			if (isset($_COOKIE['pb_company'])) $this->company =  $_COOKIE['pb_company'];
		}
		$this->load_companies();
	}

	public function __wakeup() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if (empty($_SESSION['language']) || !is_object($_SESSION['language'])) $_SESSION['language'] = new \core\classes\language();
		$cookie_exp = 2592000 + time(); // one month
		setcookie('pb_company' , $this->company,  $cookie_exp);
		setcookie('pb_language', $_SESSION['language']->language_code, $cookie_exp);
	}
	
	final static public function get($variable){
		if (isset(self::$variable)) 	return self::$variable;
		return "unknown";
	}

	/**
	 * checks if user is logged in.
	 * @return bool if user is logged in.
	 */

	final public function is_validated () {
		//allow the user to continu to with the login action.
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if ((time() - $this->last_activity) >  max ( $this->config['SESSION_TIMEOUT_ADMIN'], 360)) $this->logout();
		$this->last_activity = time();
		if ($_REQUEST['action'] == 'LoadLostPassword') $this->LoadLostPassword();
		$this->validate_company();
		if ($_REQUEST['action'] == "ValidateUser") return true;
		if ($this->admin_id <> '') return true;
		$this->LoadLogIn();
	}
	
	final public function validate_company(){
		if ($_REQUEST['action'] == "ValidateUser") $this->company = $_POST['company'];
		if ( $this->company == '') {
			if (!file_exists(DIR_FS_MY_FILES . $this->company. '/config.php')) {
				\core\classes\messageStack::debug_log("company file doesn't exist");
				\core\classes\messageStack::add(sprintf(TEXT_COMPANY_CONFIG_FILE_DOESNT_EXIST, $this->company));
			}
			$this->LoadLogIn();
		}
	}

	/**
	 * method will return current security level, will check if it is set.
	 * this method will not validate nor throw exceptions.
	 * @param unknown_type $token
	 */

	final static function security_level($token){
		if ($token == 0 || $token == '') return 1;
		if (isset($_SESSION['user']->admin_security[$token])) return $_SESSION['user']->admin_security[$token];
		return 0;
	}

	/**
	 * This method returns the current security_level of the requested token.
	 * If token isn't set a exception will be thrown
	 * @param int $token
	 * @param bool $user_active
	 * @throws Exception
	 */

	final static function validate($token = 0, $user_active = false) {
  		$security_level = $_SESSION['user']->admin_security[$token];
  		if (!in_array($security_level, array(1,2,3,4)) && !$user_active) throw new \core\classes\userException(ERROR_NO_PERMISSION, 10, $e);
  		return $user_active ? 1 : $security_level;
	}

	/**
	 * This method will check if user has security clearance if not a exception will be throw.
	 * @param int $security_level
	 * @param int $required_level
	 */

	final static function validate_security($current_security_level = 0, $required_level = 1) {
		if ($current_security_level < $required_level) throw new \core\classes\userException(ERROR_NO_PERMISSION);
	}

	/**
	 * This method will check if user has security clearance if not a exception will be throw.
	 * If token isn't set a exception will be thrown
	 * @param number $token
	 * @param number $required_level
	 * @param bool $user_active
	 * @throws \core\classes\userException
	 */
	final static function validate_security_by_token($token = 0, $required_level = 1, $user_active = false) {
		if (self::validate($token = 0, $user_active = false) < $required_level) throw new \core\classes\userException(ERROR_NO_PERMISSION);
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

  	final function load_companies() {
  		if (count ($this->companies) != 0 && $this->company != '') return;
		$contents = @scandir(DIR_FS_MY_FILES);
		if($contents === false) throw new \core\classes\userException("couldn't read or find directory ". DIR_FS_MY_FILES);
		foreach ($contents as $file) {
			if ($file <> '.' && $file <> '..' && is_dir(DIR_FS_MY_FILES . $file)) {
			  	if (file_exists(DIR_FS_MY_FILES   . $file . '/config.php')) {
					require_once (DIR_FS_MY_FILES . $file . '/config.php');
					if ($this->company == '') $this->company = $file;
					$this->companies[$file] = array(
				  	  'id'   => $file,
				  	  'text' => constant($file . '_TITLE'),
					);
			  	}
			}
		}
	}
	
	function loadConfig (\core\classes\basis &$basis) {
		if(count($this->config) == 0){
			$result = $basis->DataBase->prepare("SELECT configuration_key, configuration_value FROM " . DB_PREFIX . "configuration ");
			$result->execute();
			while ($row = $result->fetch(\PDO::FETCH_LAZY)){
				$this->config[$row['configuration_key']] = $row['configuration_value'];
			}
			/*/ load user config 
			$result = $basis->DataBase->prepare("SELECT configuration_key, configuration_value FROM " . DB_PREFIX . "configuration where user = '$this->admin_id'");
			$result->execute();
			while ($row = $result->fetch(\PDO::FETCH_LAZY)){
				$this->config[$row['configuration_key']] = $row['configuration_value'];
			}	*/		
		}
		foreach ($this->config as $key => $value) define($key,$value);
	}
	
	function getConfig($constant){
		if(count($this->config) != 0){
			return $this->config[$constant];
		}
		return;
	}
	
	/**
	 * update config key for current session.
	 * @param string $constant
	 * @param unknown $value
	 */
	function updateConfig($constant, $value){
		if (!$constant) throw new \core\classes\userException("contant isn't defined for value: $value");
		$this->config[$constant] = $value;
		define($constant,$value);
	}
	
	/**
	* remove config key for current session.
	* @param string $constant
	*/
	function removeConfig($constant){
		if (!$constant) throw new \core\classes\userException("contant isn't defined");
		unset($this->config[$constant]);
	}
	
	function get_ip_address() {
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$ip = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_CLIENT_IP')) {
				$ip = getenv('HTTP_CLIENT_IP');
			} else {
				$ip = getenv('REMOTE_ADDR');
			}
		}
		return $ip;
	}
	
	final function LoadLogIn(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		?> <script type='text/javascript'>
				document.title = '<?php echo TEXT_PHREEBOOKS_ERP; ?>';
				window.onload = function(){
						input = document.getElementById('admin_name');
						input.focus();
						input.select();
				}
		 </script>
		<style>
			body{padding: 25%;}
		</style>
		<?php //@todo test
		 echo html_form('login', FILENAME_DEFAULT, 'action=ValidateUser', 'post').chr(10);
		 echo \core\classes\htmlElement::hidden('previous', $_SERVER['QUERY_STRING'])?>
		 <div style="margin-left:25%;margin-right:25%;margin-top:50px;">
		    <div class="easyui-panel" title="<?php echo TEXT_LOGIN?>" width='500px' top='200px' left='500px'>
			    <img src="modules/phreedom/images/phreesoft_logo.png" alt="Phreedom Business Toolkit" height="50" style="float:right;"/>
              	<?php 	echo \core\classes\htmlElement::textbox('admin_name', TEXT_USERNAME, (isset($basis->cInfo->admin_name) ? $basis->cInfo->admin_name : ''), '', true) . '<br/>' . chr(13); 
              		  	echo \core\classes\htmlElement::password('admin_pass', TEXT_PASSWORD) . '<br/>' . chr(13);
              		  	if (sizeof($this->companies) > 1) {
              		  		echo \core\classes\htmlElement::combobox('company', sprintf(TEXT_SELECT_ARGS, TEXT_COMPANY), $this->companies, $this->company, '', true) . '<br/>' . chr(13); 
						} else{	
							echo \core\classes\htmlElement::hidden('company',  $this->company) . '<br/>' . chr(13);
						}
						if (sizeof($_SESSION['language']->languages) > 1) { 
							echo \core\classes\htmlElement::combobox('language', sprintf(TEXT_SELECT_ARGS, TEXT_LANGUAGE),$_SESSION['language']->languages, $_SESSION['language']->language_code, '', true) . '<br/>' . chr(13); 
						} else{
							echo \core\classes\htmlElement::hidden('language', $_SESSION['language']->language_code)  . '<br/>' . chr(13);
						}
						echo \core\classes\htmlElement::submit('submit', TEXT_LOGIN, "$('#login').form('submit');") . '<br/>' . chr(13); 
						echo '<a href="' . html_href_link(FILENAME_DEFAULT, 'action=LoadLostPassword', 'SSL') . '">' . TEXT_RESEND_PASSWORD . '</a> <br/>' . chr(13);
              			echo TEXT_COPYRIGHT; ?> (c) 2008-2015 <a href="http://www.PhreeSoft.com">PhreeSoft</a><br />
				<?php echo sprintf(TEXT_COPYRIGHT_NOTICE, '<a href="' . DIR_WS_MODULES . 'phreedom/language/en_us/manual/ch01-Introduction/license.html">' . TEXT_HERE . '</a>'); ?>
            </div>
        </div>
		</form>
		</body>
		<?php 
		die();	
	}
	
	final function LoadLostPassword(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		echo html_form('pw_lost', FILENAME_DEFAULT, 'action=SendLostPassWord') . chr(10);
		//@todo rewrite and test
		?>
		<div style="margin-left:25%;margin-right:25%;margin-top:50px;">
			<div class="easyui-panel" title="<?php echo TEXT_RESEND_PASSWORD?>" width='500px' top='200px' left='500px'>
			    <img src="modules/phreedom/images/phreesoft_logo.png" alt="Phreedom Business Toolkit" height="50" style="float:right;"/>
              	<?php 	echo \core\classes\htmlElement::textbox('admin_email', TEXT_EMAIL_ADDRESS, 'size="51" maxlength="50" data-options="validType:\'email\'"', (isset($basis->cInfo->admin_email) ? $basis->cInfo->admin_email : ''), true) . '<br/>' . chr(13);
						if (sizeof($this->companies) > 1) {
              		  		echo \core\classes\htmlElement::combobox('company', sprintf(TEXT_SELECT_ARGS, TEXT_COMPANY), $this->companies, $this->company, '', true) . '<br/>' . chr(13); 
						} else{	
							echo \core\classes\htmlElement::hidden('company',  $this->company) . '<br/>' . chr(13);
						}
						echo \core\classes\htmlElement::submit('submit', TEXT_RESEND_PASSWORD, "$('#login').form('submit');") . '<br/>' . chr(13);?>
		  	</div>
		</div>
		</form>
	<?php 	
		die();
	}
	
	function logout(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		gen_add_audit_log(TEXT_USER_LOGOFF . " -> id: {$this->admin_id} name: {$this->display_name}");
		session_destroy();
		$this->LoadLogIn();
	}
	
	function __destruct(){
//		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		
	}

}