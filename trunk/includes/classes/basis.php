<?php
namespace core\classes;
class basis implements \SplSubject {
	public  $_admin_classes		= array();
	public  $_observers;
	public  $module				= 'phreedom';
	public 	$page				= 'main';
	public	$template			= 'template';
	public  $dataBaseConnection = null;
	public  $mainmenu			= array();
	private $events				= array();

	public function __construct() {
		global $mainmenu;
		$this->_observers 		= new \SplObjectStorage();
		$this->journal			= new \core\classes\journal();
		$this->cInfo = array_merge($_GET, $_POST);
		if($this->getNumberOfAdminClasses() == 0 || empty($this->mainmenu)) {
		  	$dirs = @scandir(DIR_FS_MODULES);
		  	if($dirs === false) throw new \core\classes\userException("couldn't read or find directory ".DIR_FS_MODULES);
		  	foreach ($dirs as $dir) { // first pull all module language files, loaded or not
		    	if ($dir == '.' || $dir == '..') continue;
		    	gen_pull_language($dir, 'menu');
		  		if (is_dir(DIR_FS_MODULES . $dir)){
		    		$class = "\\$dir\classes\admin";
			  		$this->attachAdminClasses($dir, new $class);
				}
		  	}
		  	$this->mainmenu = $mainmenu;
	  	}
	}

	public function  __wakeup() {
		print("basis __wakeup is called");
	}

	public function attach(\SplObserver $observer) {
		$this->_observers->attach($observer);
	}

	public function detach(\SplObserver $observer) {
		$this->_observers->detach($observer);
	}

	/**
	 * this method sends a notify to the template page to start sending information in requested format.
	 */

	public function notify() {
		foreach ($this->_observers as $observer) {
			$observer->update($this);
		}
	}

	public function ReturnAdminClasses(){
		return $this->_admin_classes;
	}

	/**
	 * this method returns the number of admin classes stored in its private array
	 * @return integer
	 */

	public function getNumberOfAdminClasses() {
		return sizeof($this->_admin_classes);
	}

	/**
	 * method adds a admin class to its private array.
	 * @param string $moduleName
	 * @param \core\classes\admin $admin_class
	 */

	public function attachAdminClasses($moduleName,\core\classes\admin $admin_class) {
		if (array_search($admin_class, $this->_admin_classes) === false) {
			$this->_admin_classes[$moduleName] = $admin_class;
		}
		uasort($this->_admin_classes, array($this, 'arangeObjectBySortOrder'));
	}

	/**
	 * this method is for sorting a array of objects by the sort_order variable
	 */

	function arangeObjectBySortOrder($a, $b){
		return strcmp($a->sort_order, $b->sort_order);
	}

	/**
	 * this method add the event to the second position of the array.
	 * this will allow the program to finish the first position and then continue with the second.
	 * @param string $event
	 */

	public function fireEvent($event) {
		$this->events = array_slice($this->events, 0, 1) + array($event) + array_slice($this->events, 1);
	}

	/**
	 * this method walks over the event stack.
	 * tries to call before_event, event, after_event on all admin_classes.
	 * then removes event from event stack to prevent it from returning.
	 * @throws exception if the event stack is empty
	 */

	public function startProcessingEvents(){
		global $messageStack;
		if(size($this->events)) throw new exception("trying to start processing events but the events array is empty");
		while (list($key, $event) = each($this->events)) {
			$messageStack->debug("\n starting with event: $event");
			if (!$event) throw new exception("found a empty event in the array.");
			$ActionBefore  = "before_$event";
			foreach ($this->_admin_classes as $module_class){
				if ($module_class->installed && method_exists($module_class, $ActionBefore)) {
					$messageStack->debug("\n class {$module_class->id} has action method $ActionBefore");
					$module_class->$ActionBefore($this);
				}
			}

			foreach ($this->_admin_classes as $module_class){
				if ($module_class->installed && method_exists($module_class, $event)) {
					$messageStack->debug("\n class {$module_class->id} has action method $event");
					$module_class->$event($this);
				}
			}
			$ActionBefore  = "after_$event";
			foreach ($this->_admin_classes as $module_class) {
				if ($module_class->installed && method_exists($module_class, $ActionAfter)) {
					$messageStack->debug("\n class {$module_class->id} has action method $ActionAfter");
					$admin_classes->$ActionAfter($this);
				}
			}
			unset($this->events[$key]);
			reset($this->events);
		}
	}

	/**
	 * This method will add the requested event to the end of the stack.
	 * @param string $event
	 * @throws exception if event is emtpy
	 */
	public function addEventToStack($event){
		if(!$event) throw new exception("in the basis class method addEventToStack we received a empty event.");
		array_push( $this->events, $event);
	}

	/**
	 * empties the event stack and then adds the new event
	 * @param string $event
	 * @throws exception if event is empty
	 */

	public function removeEventsAndAddNewEvent($event){
		if(!$event) throw new exception("in the basis class method  we received a empty event.");
		$this->events = null;
		addEventToStack($event);
	}

	/**
	 * this method empties the event stack
	 */

	public function clearEventsStack(){
		$this->events = null;
	}

	function __destruct(){
		$this->dataBaseConnection = null;
	}
}
?>