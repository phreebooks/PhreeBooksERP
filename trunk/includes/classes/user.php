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
	public $language;
	public $languages = array();
	public $companies = array();

	function __construct(){
		global $admin;
		$this->language = new \core\classes\language();
		$this->load_companies();
		$this->is_validated($admin);
	}

	public function __wakeup() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		global $admin;
		$this->language = new \core\classes\language();
		$this->load_companies();
		$this->is_validated($admin);
	}
	
	final static public function get($variable){
		if (isset($_SESSION[$variable])) return $_SESSION[$variable];
		if (isset(self::$variable)) 	return self::$variable;
		return "unknown";
	}

	/**
	 * checks if user is logged in.
	 * @return bool if user is logged in.
	 */

	final public function is_validated (\core\classes\basis &$admin) {
		//allow the user to continu to with the login action.
		if ($_REQUEST['action'] == 'LoadLostPassword') $this->LoadLostPassword();
		if (isset($_SESSION['company']) && $_SESSION['company'] != '' && file_exists(DIR_FS_MY_FILES . $_SESSION['company'] . '/config.php')) {
			$this->LoadLogIn();
		}
		$this->validate_company();
		if ($_REQUEST['action'] == "ValidateUser") return true;
		if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] <> '') return true;
		$this->LoadLogIn();
	}
	
	final static public function validate_company(){
		if ($_REQUEST['action'] == "ValidateUser") $_SESSION['company'] = $_POST['company'];
		if (!isset($_SESSION['company']) && $_SESSION['company'] == '' && !file_exists(DIR_FS_MY_FILES . $_SESSION['company'] . '/config.php')) {
			$messageStack->add(sprintf(TEXT_COMPANY_CONFIG_FILE_DOESNT_EXIST, $_SESSION['company']));
			$this->LoadLogIn();
		}
	}

	/**
	 * returns the current company and sets it in the Session variable.
	 */
	final static public function get_company(){
		if (isset($_SESSION['company'])) return $_SESSION['company'];
		if (isset($_REQUEST['company'])) {
			$_SESSION['company'] = $_REQUEST['company'];
		} else { // find default company
			if(defined('DEFAULT_COMPANY')) {
				$_SESSION['company'] = DEFAULT_COMPANY;
			}else{
				if (isset($_COOKIE['pb_company'])) $_SESSION['company'] = $_COOKIE['pb_company'];
			}
		}
		return $_SESSION['company'];
	}

	/**
	 * method will return current security level, will check if it is set.
	 * this method will not validate nor throw exceptions.
	 * @param unknown_type $token
	 */

	final static function security_level($token){
		if (isset($_SESSION['admin_security'][$token])) return $_SESSION['admin_security'][$token];
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
  		$security_level = $_SESSION['admin_security'][$token];
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
		$contents = @scandir(DIR_FS_MY_FILES);
		if($contents === false) throw new \core\classes\userException("couldn't read or find directory ". DIR_FS_MY_FILES);
		foreach ($contents as $file) {
			if ($file <> '.' && $file <> '..' && is_dir(DIR_FS_MY_FILES . $file)) {
			  	if (file_exists(DIR_FS_MY_FILES   . $file . '/config.php')) {
			  		if (!isset($_SESSION['company'])) $_SESSION['company'] = $file;
					require_once (DIR_FS_MY_FILES . $file . '/config.php');
					$this->companies[$file] = array(
				  	  'id'   => $file,
				  	  'text' => constant($file . '_TITLE'),
					);
			  	}
			}
		}
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
		<?php 
		 echo html_form('login', FILENAME_DEFAULT, 'action=ValidateUser', 'post').chr(10);
		?>
		<div style="margin-left:25%;margin-right:25%;margin-top:50px;">
			  <table class="ui-widget">
		        <thead class="ui-widget-header">
		        <tr height="70">
		          <th style="text-align:right"><img src="modules/phreedom/images/phreesoft_logo.png" alt="Phreedom Business Toolkit" height="50" /></th>
		        </tr>
		        </thead>
		        <tbody class="ui-widget-content">
		        <tr>
		          <td>
				    <table>
					  <tr>
					    <td colspan="2"><?php if(is_object($messageStack)) echo $messageStack->output(); ?></td>
					  </tr>
		              <tr>
		                <td width="35%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_USERNAME; ?>:</td>
		                <td width="65%"><?php echo html_input_field('admin_name', (isset($basis->cInfo->admin_name) ? $basis->cInfo->admin_name : ''), '', true); ?></td>
		              </tr>
		              <tr>
		                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_PASSWORD; ?>:</td>
		                <td><?php echo html_password_field('admin_pass', '', true); ?></td>
		              </tr>
		<?php if (sizeof($this->companies) > 1) { ?>
		              <tr>
		                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo sprintf(TEXT_SELECT_ARGS, TEXT_COMPANY); ?></td>
		                <td><?php echo html_pull_down_menu('company', $this->companies, $this->get_company(), '', true); ?></td>
		              </tr>
		<?php } else{
				echo html_hidden_field('company',  $this->get_company()) . chr(10);
		}?>
		<?php if (sizeof($this->language->languages) > 1) { ?>
		              <tr>
		                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_SELECT_LANGUAGE; ?>: </td>
		                <td><?php echo html_pull_down_menu('language', $this->language->languages, $this->language->language_code, '', true); ?></td>
		              </tr>
		<?php } else{
					echo html_hidden_field('language', $this->language->language_code) . chr(10);
		}?>
		              <tr>
		                <td colspan="2" align="right">&nbsp;
						  <div id="wait_msg" style="display:none;"><?php echo TEXT_FORM_PLEASE_WAIT; ?></div>
						  <?php echo html_submit_field('submit', TEXT_LOGIN); ?>
						</td>
		              </tr>
		              <tr>
		                <td colspan="2"><?php echo '<a href="' . html_href_link(FILENAME_DEFAULT, 'action=LoadLostPassword', 'SSL') . '">' . TEXT_RESEND_PASSWORD . '</a>'; ?></td>
		              </tr>
		              <tr>
		                <td colspan="2">
		<?php echo TEXT_COPYRIGHT; ?> (c) 2008-2015 <a href="http://www.PhreeSoft.com">PhreeSoft</a><br />
		<?php echo sprintf(TEXT_COPYRIGHT_NOTICE, '<a href="' . DIR_WS_MODULES . 'phreedom/language/en_us/manual/ch01-Introduction/license.html">' . TEXT_HERE . '</a>'); ?>
						</td>
		              </tr>
		            </table>
			      </td>
		        </tr>
		        </tbody>
		      </table>
		</div>
		</form>
		<?php 
		die();	
	}
	
	final function LoadLostPassword(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		echo html_form('pw_lost', FILENAME_DEFAULT, 'action=SendLostPassWord') . chr(10);
		?>
		<div style="margin-left:25%;margin-right:25%;margin-top:50px;">
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		   <tr height="70"><th colspan="2" align="right"><img src="modules/phreedom/images/phreesoft_logo.png" alt="Phreedom Business Toolkit" height="50" /></th></tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <tr><td colspan="2"><h2><?php echo TEXT_RESEND_PASSWORD; ?></h2></td></tr>
		  <tr>
		   <td nowrap="nowrap">&nbsp;&nbsp;<?php echo TEXT_EMAIL_ADDRESS . ': '; ?></td>
		   <td><?php echo html_input_field('admin_email', $this->admin_email, 'size="60"'); ?></td>
		  </tr>
		  <tr>
		   <td nowrap="nowrap">&nbsp;&nbsp;<?php echo sprintf(TEXT_SELECT_ARGS, TEXT_COMPANY); ?></td>
		   <td><?php echo html_pull_down_menu('company', $this->companies, $this->company_index, '', true); ?></td>
		  </tr>
		  <tr><td colspan="2" align="right"><?php echo html_submit_field('submit', TEXT_RESEND_PASSWORD) . '&nbsp;&nbsp;'; ?></td></tr>
		 </tbody>
		</table>
		</div>
		</form>
	<?php 	
		die();
	}
	
	
	function __destruct(){
		$_SESSION['companies'] = $this->companies;// @todo do we still need this
	}

}