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
//  Path: /includes/classes/basis.php
//

namespace core\classes;

class basis implements \SplSubject {
	public  $classes 	= array ();
	public  $_observers = array ();
	public  $module		= 'phreedom';
	public  $page 		= 'main';
	public  $template;
	public  $observer	= 'core\classes\outputPage';
	public  $custom_html		= false;
    public  $include_header		= true;
    public  $include_footer		= true;
	public  $DataBase = null;
	public  $configuration		= array ();
	public  $mainmenu 			= array ();
	private $events 			= array ('LoadMainPage');
	//for output
	public  $js_files				= array ();
	public  $include_php_js_files	= array ();
	public  $js_override_files		= array ();


	public function __construct() {
		global $currencies;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->journal = new \core\classes\journal ();
		$this->cInfo = (json_decode($request) != NULL) ? (object) json_decode($request) : (object)array_merge ( $_GET, $_POST );
//		$this->events = $this->cInfo->action;
		if ($this->getNumberOfAdminClasses () == 0 || empty ( $this->mainmenu )) {
			$dirs = @scandir ( DIR_FS_MODULES );
			if ($dirs === false) throw new \core\classes\userException ( "couldn't read or find directory " . DIR_FS_MODULES );
			foreach ( $dirs as $dir ) { // first pull all module language files, loaded or not
				if ($dir == '.' || $dir == '..') continue;
				gen_pull_language ( $dir, 'menu' );
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
				'text' 		=> TEXT_INVENTORY,
				'security_id' => '',
				'link' 		=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_inv', 'SSL'),
				'params'      => '',
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
				'text' 		=> TEXT_COMPANY,
				'security_id' => '',
				'link' 		=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_company', 'SSL'),
				'params'      => '',
		);
		if (defined('MODULE_CP_ACTION_STATUS') || defined('MODULE_DOC_CTL_STATUS')) $this->mainmenu["quality"] = array(
				'order' 		=> MENU_HEADING_QUALITY_ORDER,
				'text'  		=> TEXT_QUALITY,
				'security_id' => '',
				'link' 		=> html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;mID=cat_qa', 'SSL'),
				'params'      => '',
		);
		$this->mainmenu["logout"] = array(
				'order' => 999,
				'text'  => TEXT_LOG_OUT,
				'link'  => html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;action=logout', 'SSL'),
				'icon'  => html_icon('actions/system-log-out.png', TEXT_LOG_OUT, 'small'),
		);
		foreach ( $this->classes as $module_class ) {
			$this->mainmenu = array_merge_recursive($this->mainmenu, $module_class->mainmenu);
		}
	}

	public function __sleep() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->DataBase = null;
	}

	public function __wakeup() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->cInfo = (json_decode($request) != NULL) ? (object) json_decode($request) : (object)array_merge ( $_GET, $_POST );
//		$this->events = $this->cInfo->action;
	}

	public function attach(\SplObserver $observer) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		\core\classes\messageStack::debug_log("attaching observer".get_class($observer));
		$this->_observers[get_class($observer)] = $observer;
	}

	public function detach(\SplObserver $observer) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		unset($this->_observers[ get_class($observer) ]);
	}

	/**
	 * this method sends a notify to the template page to start sending information in requested format.
	 */
	public function notify() {
		global $messageStack;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		foreach ( $this->_observers as $key => $observer ) {
			\core\classes\messageStack::debug_log( "calling ". get_class($observer)." for output" );
			$this->observer = get_class($observer);
			$observer->update ( $this );
		}
	}

	public function returnCurrentObserver(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		\core\classes\messageStack::debug_log("returning object of {$this->observer}");
		return $this->_observers[$this->observer];
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
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if (array_search ( $admin_class, $this->classes ) === false) {
			$this->classes [$moduleName] = $admin_class;
		}
		uasort ( $this->classes, array (
				$this,
				'arangeObjectBySortOrder'
		) );
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
	public function startProcessingEvents() {//die(print_r($this));
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
		}
		$this->notify();
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
	function __destruct() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->DataBase = null;
	}
}
?>