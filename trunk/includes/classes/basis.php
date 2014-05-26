<?php
namespace core\classes;
class basis implements \SplSubject {
	public  $_admin_classes	= array();
	public  $_observers;
	public  $module			= 'phreedom';
	public 	$page			= 'main';
	public	$template		= 'template';
	public  $dataBaseConnection = null;
	public  $mainmenu			= array();

	public function __construct() {
		global $mainmenu;
		$this->_observers 		= new \SplObjectStorage();
		//$this->_admin_classes	= new \ArrayObject();
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
	 * this function is for sorting a array of objects by the sort_order variable
	 */

	function arangeObjectBySortOrder($a, $b){
		return strcmp($a->sort_order, $b->sort_order);
	}

	public function fireEvent($event) {
		global $messageStack;
		if (strlen($event) == 0) $event = $this->action;
		else $this->action = $event;
		$messageStack->debug("\n event fired: $event");
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

		foreach ($this->_admin_classes as $module_class) {
			if ($module_class->installed && method_exists($module_class, $ActionAfter)) {
				$messageStack->debug("\n class {$module_class->id} has action method $ActionAfter");
				$admin_classes->$ActionAfter($this);
			}
		}
	}

	function __destruct(){
		$this->dataBaseConnection = null;
	}
}
?>