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
//  Path: /includes/classes/basis.php
//

namespace core\classes;

class basis {
	public  $classes 	= array ();
	public  $_observers = array ();
	public  $module		= 'phreedom';
	public  $page 		= 'main';
	public  $template;
	public  $observer;
	public  $custom_html		= false;
    public  $include_footer		= true;
	public  $DataBase 			= null;
	public  $configuration		= array ();
	public  $user;
	public  $mainmenu 			= array ();
	private $events 			= array ('LoadMainPage');
	public 	$toolbar;
	public  $currencies;
	//for output
	public  $js_files				= array ();
	public  $include_php_js_files	= array ();
	public  $js_override_files		= array ();
	public  $journal;


	public function __construct() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->setObserver();
		$this->user = new \core\classes\user();
		$this->toolbar = new \core\classes\toolbar ();
		$this->currencies = new \core\classes\currencies ();
		$this->setCinfo();
		if ($this->getNumberOfAdminClasses () == 0 || empty ( $this->mainmenu )) {
			$dirs = @scandir ( DIR_FS_MODULES );
			if ($dirs === false) throw new \core\classes\userException ( "couldn't read or find directory " . DIR_FS_MODULES );
			foreach ( $dirs as $dir ) { // first pull all module language files, loaded or not
				if ($dir == '.' || $dir == '..') continue;
				if (is_dir ( DIR_FS_MODULES . $dir )) {
					$class = "\\$dir\classes\admin";
					$this->attachAdminClasses ( $dir, new $class () );
				}
			}
		}
		$this->mainmenu["home"] = array(
				'order' => 0,
				'text'  => TEXT_HOME,
				'link'  => html_href_link(FILENAME_DEFAULT),
				'icon'  => html_icon('actions/go-home.png', TEXT_HOME, 'small'),
		);
		$this->mainmenu["inventory"] = array(
				'order' 		=> MENU_HEADING_INVENTORY_ORDER,
				'text' 			=> TEXT_INVENTORY,
				'security_id'	=> '',
				'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_inv', 'SSL'),
				'params'      	=> '',
		);
		$this->mainmenu["banking"] = array(
				'order'			=> MENU_HEADING_BANKING_ORDER,
				'text' 			=> TEXT_BANKING,
				'security_id' 	=> '',
				'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_bnk', 'SSL'),
				'params'      	=> '',
		);
		$this->mainmenu["gl"] = array(
				'order'			=> MENU_HEADING_GL_ORDER,
				'text' 			=> TEXT_GENERAL_LEDGER,
				'security_id' 	=> '',
				'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_gl', 'SSL'),
				'params'      	=> '',
		);
		$this->mainmenu["tools"] = array(
				'order'			=> MENU_HEADING_TOOLS_ORDER,
				'text' 			=> TEXT_TOOLS,
				'security_id' 	=> '',
				'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_tools', 'SSL'),
				'params'      	=> '',
		);
		$this->mainmenu["company"] = array(
				'order' 		=> MENU_HEADING_COMPANY_ORDER,
				'text' 			=> TEXT_COMPANY,
				'security_id' 	=> '',
				'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_company', 'SSL'),
				'params'      	=> '',
		);
		if (defined('MODULE_CP_ACTION_STATUS') || defined('MODULE_DOC_CTL_STATUS')) $this->mainmenu["quality"] = array(
				'order' 		=> MENU_HEADING_QUALITY_ORDER,
				'text'  		=> TEXT_QUALITY,
				'security_id' 	=> '',
				'link' 			=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_qa', 'SSL'),
				'params'      	=> '',
		);
		$this->mainmenu["logout"] = array(
				'order' 		=> 999,
				'text'  		=> TEXT_LOG_OUT,
				'link'  		=> html_href_link(FILENAME_DEFAULT, 'action=logout', 'SSL'),
				'icon'  		=> html_icon('actions/system-log-out.png', TEXT_LOG_OUT, 'small'),
		);
		$this->set_database();
		$this->checkIfModulesInstalled();
	}

	public function checkIfModulesInstalled(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		foreach ( $this->classes as $module_class ) $module_class->checkInstalled ( $this );
	}

	public function setCinfo(){
		if (json_decode($request) != NULL) {
			$this->cInfo = (object) json_decode($request) ;
		} else {
			$this->cInfo = (object)array_merge ( $_GET, $_POST );
		}
	}
	public function __sleep() {}

	public function __wakeup() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->setObserver();
		$this->set_database();
		$this->checkIfModulesInstalled();
		$this->setCinfo();
	}
	
	private function setObserver(){
		switch(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])){
			default: 
				$this->observer = new \core\classes\outputPage();
				break;
			case 'xmlhttprequest':
				$this->observer = new \core\classes\outputXml();
				break;
			case 'json':	
				$this->observer = new \core\classes\outputJson();
				break;
			case 'mobile':
				$this->observer = new \core\classes\outputMobile();
				break;
		}
		$this->observer->send_header($this);
	}

	/**
	 * this method sends a notify to the template page to start sending information in requested format.
	 */
	public function notify() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		\core\classes\messageStack::debug_log( "calling ". get_class($this->observer)." for output" );
		$this->observer = get_class($observer);

	}
	
	public function set_database(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		\core\classes\messageStack::debug_log("connecting to database {$_SESSION['company']}" );
		define('DB_DATABASE', $_SESSION['company']);
		require_once(DIR_FS_MY_FILES . $_SESSION['company'] . '/config.php');
		if(!defined('DB_SERVER_HOST')) define('DB_SERVER_HOST',DB_SERVER);
		$this->DataBase = new \core\classes\PDO(DB_TYPE.":dbname={$_SESSION['company']};host=".DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		//	if(APC_EXTENSION_LOADED == false || apc_load_constants('configuration') == false) {
		$result = $this->DataBase->prepare("SELECT configuration_key, configuration_value FROM " . DB_PREFIX . "configuration");
		$result->execute();
		while ($row = $result->fetch(\PDO::FETCH_LAZY)){
			$this->configuration[$_SESSION['company']][$row['configuration_key']] = $row['configuration_value'];
			define($row['configuration_key'],$row['configuration_value']);//@todo remove
		}
		require(DIR_FS_MODULES . 'phreedom/config.php');
		$this->currencies->load($this);
		// pull in the custom language over-rides for this module (to pre-define the standard language)
		$path = DIR_FS_MODULES . "{$_REQUEST['module']}/custom/pages/{$_REQUEST['page']}/extra_menus.php";
		if (file_exists($path)) { include($path); }
		\core\classes\messageStack::debug_log("database type ".get_class($this->DataBase));
	}

	public function ReturnAdminClasses() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		return $this->classes;
	}

	/**
	 * this method returns the number of admin classes stored in its private array
	 *
	 * @return integer
	 */
	public function getNumberOfAdminClasses() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		return sizeof ( $this->classes );
	}

	/**
	 * method adds a admin class to its private array.
	 *
	 * @param string $moduleName
	 * @param \core\classes\admin $admin_class
	 */
	public function attachAdminClasses($moduleName, \core\classes\admin $admin_class) {
		\core\classes\messageStack::debug_log("attaching admin class ".get_class($admin_class));
		if (array_search ( $admin_class, $this->classes ) === false) {
			$this->classes [$moduleName] = $admin_class;
				$this->mainmenu = array_merge_recursive($this->mainmenu, $admin_class->mainmenu);
		}
		uasort ( $this->classes, array ( $this, 'arangeObjectBySortOrder') );
	}

	/**
	 * this method is for sorting a array of objects by the sort_order variable
	 */
	function arangeObjectBySortOrder($a, $b) {
		if(is_integer($a->sort_order) && is_integer($b->sort_order)) return $a->sort_order - $b->sort_order;
		return strcmp ( $a->sort_order, $b->sort_order );
	}

	/**
	 * this method add the event to the second position of the array.
	 * this will allow the program to finish the first position and then continue with the second.
	 *
	 * @param string $event
	 */
	public function fireEvent($event) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->removeEventsAndAddNewEvent($event);
	}

	/**
	 * this method walks over the event stack.
	 * tries to call before_event, event, after_event on all admin_classes.
	 * then removes event from event stack to prevent it from returning.
	 *
	 * @throws exception if the event stack is empty
	 */
	public function startProcessingEvents() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if ( count($this->events ) == 0) throw new \Exception ( "trying to start processing events but the events array is empty" );
		while ( $event = array_shift($this->events) ) {
			\core\classes\messageStack::debug_log("starting with event: $event" );
			if (! $event ) break;
			$ActionBefore = "before_$event";
			if( ! in_array($event, array('LoadLogIn', 'ValidateUser', 'LoadLostPassword', 'SendLostPassWord')))
				foreach ( $this->classes as $module_class ) {
				if ($module_class->installed && method_exists ( $module_class, $ActionBefore )) {
					\core\classes\messageStack::debug_log("class {$module_class->id} has action method $ActionBefore" );
					$module_class->$ActionBefore ( $this );
				}
			}
			foreach ( $this->classes as $module_class ) {
				if ($module_class->installed && method_exists ( $module_class, $event )) {
					\core\classes\messageStack::debug_log("class {$module_class->id} has action method $event" );
					$module_class->$event ( $this );
				}
			}
			$ActionAfter = "after_$event";
			if( ! in_array($event, array('logout', 'LoadLogIn'))) foreach ( $this->classes as $module_class ) {
				if ($module_class->installed && method_exists ( $module_class, $ActionAfter )) {
					\core\classes\messageStack::debug_log("class {$module_class->id} has action method $ActionAfter" );
					$module_class->$ActionAfter ( $this );
				}
			}
			ob_flush();
		}
	}

	/**
	 * This method will add the requested event to the end of the stack.
	 *
	 * @param string $event
	 * @throws exception if event is emtpy
	 */
	public function addEventToStack($event) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if (! $event) throw new \exception ( "in the basis class method addEventToStack we received a empty event." );
		\core\classes\messageStack::debug_log("adding event $event to stack" );
		if (! in_array ( $event, (array) $this->events)) array_push ($this->events, $event );
	}

	/**
	 * empties the event stack and then adds the new event
	 *
	 * @param string $event
	 * @throws exception if event is empty
	 */
	public function removeEventsAndAddNewEvent($event) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if (! $event) throw new \exception ( "in the basis class method  we received a empty event." );
		$this->clearEventsStack ();
		$this->addEventToStack ( $event );
	}

	/**
	 * this method empties the event stack
	 */
	public function clearEventsStack() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		\core\classes\messageStack::debug_log("clearing events stack" );
		$this->events = array();
	}

	/**
	 * returns the companies configuration value
	 * @param unknown $configuration_key
	 */

	function returnConfigurationValue($configuration_key) {
		if (array_key_exists ($configuration_key, $this->configuration[ $_SESSION['company'] ])) {
			return $this->configuration[ $_SESSION['company'] ][$configuration_key];
		}
		return null;
	}

	function __destruct() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->DataBase = null;
		//print_R($this);
	}
}
?>