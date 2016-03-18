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
		if (!isset($_SESSION['user'])) $_SESSION['user'] = new \core\classes\user($this);
		$this->mainmenu["home"] 		= new \core\classes\menuItem (-1, 	TEXT_HOME);
		$this->mainmenu["home"]->icon  	= html_icon('actions/go-home.png', TEXT_HOME, 'small');
		$this->mainmenu["customers"] 	= new \core\classes\menuItem (10, 	TEXT_CUSTOMERS,			'action=LoadMainPage&amp;mID=cat_ar');
		$this->mainmenu["vendors"]  	= new \core\classes\menuItem (20, 	TEXT_VENDORS,			'action=LoadMainPage&amp;mID=cat_ap');
		$this->mainmenu["inventory"]  	= new \core\classes\menuItem (30, 	TEXT_INVENTORY,			'action=LoadMainPage&amp;mID=cat_inv');
		$this->mainmenu["banking"] 		= new \core\classes\menuItem (40, 	TEXT_BANKING, 			'action=LoadMainPage&amp;mID=cat_bnk');
		$this->mainmenu["gl"] 			= new \core\classes\menuItem (50, 	TEXT_GENERAL_LEDGER, 	'action=LoadMainPage&amp;mID=cat_gl');
		$this->mainmenu["employees"]	= new \core\classes\menuItem (60, 	TEXT_EMPLOYEES,			'action=LoadMainPage&amp;mID=cat_hr');
		$this->mainmenu["tools"] 		= new \core\classes\menuItem (70, 	TEXT_TOOLS, 			'action=LoadMainPage&amp;mID=cat_tools');
		$this->mainmenu["quality"] 		= new \core\classes\menuItem (75, 	TEXT_QUALITY, 			'action=LoadMainPage&amp;mID=cat_qa');
		$this->mainmenu["quality"]->required_module = array ('MODULE_CP_ACTION_STATUS' ,'MODULE_DOC_CTL_STATUS');
		$this->mainmenu["company"] 		= new \core\classes\menuItem (90, 	TEXT_COMPANY, 			'action=LoadMainPage&amp;mID=cat_company');
		$this->mainmenu["company"]->submenu ["configuration"]  	= new \core\classes\menuItem (10, 	TEXT_MODULE_ADMINISTRATION,	'module=phreedom&amp;page=admin', 	11);
		$this->mainmenu["logout"]		= new \core\classes\menuItem (999, 	TEXT_LOG_OUT, 			'action=logout');
		$this->mainmenu["logout"]->icon = html_icon('actions/system-log-out.png', TEXT_LOG_OUT, 'small');
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
		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." Type of request = {$_SERVER['HTTP_X_REQUESTED_WITH']} datatype = {$_REQUEST['contentType']} ");
		switch($_REQUEST['contentType']){
			case 'text/html':
			default: 
				$this->observer = new \core\classes\outputPage();
				break;
			case 'xmlhttprequest':
				if ($_REQUEST['dataType'] == 'json') {
					$this->observer = new \core\classes\outputJson();
				}else{
					$this->observer = new \core\classes\outputXml();
				}
				break;
			case 'application/json':	
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
		$_SESSION['user']->is_validated();
		try{
			define('DB_DATABASE', $_SESSION['user']->company);
			require_once(DIR_FS_MY_FILES . $_SESSION['user']->company . '/config.php');
			if(!defined('DB_SERVER_HOST')) define('DB_SERVER_HOST',DB_SERVER);
			$this->DataBase = new \core\classes\PDO(DB_TYPE.":dbname={$_SESSION['user']->company};host=".DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
			$_SESSION['user']->loadConfig($this);
			require(DIR_FS_MODULES . 'phreedom/config.php');
			$this->currencies->load($this);
		}catch (\Exception $e){
			\core\classes\messageStack::add($e);
			$_SESSION['user']->LoadLogIn();
		}
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
		if (array_key_exists($moduleName, $this->classes ) == false) {
			$this->classes [$moduleName] = $admin_class;
			foreach ($admin_class->mainmenu as $key => $menu) {
				if (!isset($this->mainmenu [$key])) $this->mainmenu [$key] = $menu;
				if (is_array($menu->submenu)) {
					foreach ($menu->submenu as $subkey => $submenu) {
						if (!isset($this->mainmenu [$key]->submenu [$subkey])) $this->mainmenu [$key]->submenu [$subkey] = $submenu;
						if (is_array($this->mainmenu [$key]->submenu [$subkey])) {
							foreach ($this->mainmenu [$key]->submenu [$subkey] as $subsubkey => $subsubmenu) {
								if (!isset($this->mainmenu [$key]->submenu [$subkey]->submenu [$subsubkey])) $this->mainmenu [$key]->submenu [$subkey]->submenu [$subsubkey] = $subsubmenu;
							}
						}
					}
				} 
			}
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
		\core\classes\messageStack::debug_log("clearing events stack" );
		$this->events = array();
	}

	function __destruct() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->DataBase = null;
		//print_R($this);
	}
}
?>