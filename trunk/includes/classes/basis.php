<?php
namespace core\classes;
class basis implements \SplSubject {
	private $admin_classes = array();
	private $_observers;

	public function __construct() {
		$this->_observers 		= new \SplObjectStorage();
		$this->_admin_classes	= new \SplObjectStorage();
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
		uasort($this->_admin_classes, '$this->arangeObjectBySortOrder'');
	}

	/**
	 * this function is for sorting a array of objects by the sort_order variable
	 */

	function arangeObjectBySortOrder($a, $b){
		return strcmp($a->sort_order, $b->sort_order);
	}

	public function fireEvent($event) {
		global $messageStack;
		$ActionBefore  = "before_$event";
		foreach ($this->_admin_classes as $module_class){
			if ($module_class->installed && method_exists($module_class, $ActionBefore)) {
				$messageStack->debug("class {$module_class->id} has action method $ActionBefore");
				$module_class->$ActionBefore(&$this);
			}
		}

		foreach ($this->_admin_classes as $module_class){
			if ($module_class->installed && method_exists($module_class, $event)) {
				$messageStack->debug("class {$module_class->id} has action method $event");
				$module_class->$event(&$this);
			}
		}

		foreach ($this->_admin_classes as $module_class) {
			if ($module_class->installed && method_exists($module_class, $ActionAfter)) {
				$messageStack->debug("class {$module_class->id} has action method $ActionAfter");
				$admin_classes->$ActionAfter(&$this);
			}
		}
	}
}
?>