<?php

/**
 * The PCARegistry object
 * Implements the Registry and Singleton design patterns
 * @version 0.1
 * @author Michael Peacock
 */
class registry {

 	/**
 	 * Our array of objects
 	 * @access private
 	 */
    private static $objects = array();

    /**
     * Our array of settings
     * @access private
     */
    private static $settings = array();

    /**
     * The instance of the registry
     * @access private
     */
    private static $instance;

    /**
     * this holds the current module
     * @access private
     */
    private static $module;

    /**
     * this holds the current page
     * @access private
     */
    private static $page;

    /**
     * Private constructor to prevent it being created directly
     * @access private
     */
    private function __construct() {
    	ob_start();
    	session_start();
		ini_set('log_errors','0');
		ini_set('display_errors', '0');
		error_reporting(E_ALL ^ E_NOTICE);
		@ini_set('session.gc_maxlifetime', (SESSION_TIMEOUT_ADMIN < 900 ? (SESSION_TIMEOUT_ADMIN + 900) : SESSION_TIMEOUT_ADMIN));
	    // load general language translation, Check for global define overrides first
		$path = DIR_FS_MODULES . 'phreedom/custom/language/' . $_SESSION['language'] . '/language.php';
		if (file_exists($path)) { require_once($path); }
		$path = DIR_FS_MODULES . 'phreedom/language/' . $_SESSION['language'] . '/language.php';
		if (file_exists($path)) { require_once($path); }
		else { require_once(DIR_FS_MODULES . 'phreedom/language/en_us/language.php'); }
		require_once(DIR_FS_MODULES  . 'phreedom/defaults.php');
		require_once(DIR_FS_INCLUDES . 'common_functions.php');
		require_once(DIR_FS_INCLUDES . 'common_classes.php');
		set_error_handler("PhreebooksErrorHandler");
		set_exception_handler('PhreebooksExceptionHandler');
		spl_autoload_register( 'Phreebooks_autoloader', true, false);
		// do some default tests
		print(get_cfg_var('safe_mode'));
		//if (!get_cfg_var('safe_mode')) trigger_error(INSTALL_ERROR_SAFE_MODE, E_USER_ERROR);
    	if (version_compare(PHP_VERSION, '5.3.0', '<')) trigger_error(INSTALL_ERROR_PHP_VERSION, E_USER_ERROR);

    }

    /**
     * singleton method used to access the object
     * @access public
     * @return
     */
    public static function singleton() {
    	if( !isset( self::$instance ) ) {
        	$obj = __CLASS__;
            self::$instance = new $obj;
        }
        return self::$instance;
    }

    /**
     * prevent cloning of the object: issues an E_USER_ERROR if this is attempted
     */
    public function __clone() {
    	trigger_error( 'Cloning the registry is not permitted', E_USER_ERROR );
    }

    /**
     * Stores an object in the registry
     * @param String $object the name of the object
     * @param String $key the key for the array
     * @return void
     */
    public function storeObject( $object, $key ) {
        self::$objects[ $key ] = new $object( self::$instance );
    }

    /**
     * Gets an object from the registry
     * @param String $key the array key
     * @return object
     */
    public function getObject( $key ) {
    	if( is_object ( self::$objects[ $key ] ) ) {
        	return self::$objects[ $key ];
        }
    }

    /**
     * Stores settings in the registry
     * @param String $data
     * @param String $key the key for the array
     * @return void
     */
    public function storeSetting( $data, $key ) {
    	self::$settings[ $key ] = $data;
    }

    /**
     * Gets a setting from the registry
     * @param String $key the key in the array
     * @return void
	 */
	public function getSetting( $key ) {
		return self::$settings[ $key ];
    }

	/**
     * this sets the current module
     * @param String $module
     * @return void
     */
    public function setModule(str $module){
    	self::$module = $module;
    }

    /**
     * this returns the current module
     * @return string $page
     */
    public function getModule(){
    	return self::$module;
    }


    /**
     * this sets the current page
     * @param String $page
     * @return void
     */
    public function setPage(str $page){
    	self::$page = $page;
    }

    /**
     * this returns the current page
     * @return string $page
     */
    public function getPage(){
    	return self::$page;
    }

	/**
	 * stores the core objects these are required by default.
	 * @return void
	 */
    public function storeCoreObjects(){
  		$dsn = DB_TYPE.":dbname=".$db_company.";host=".DB_SERVER_HOST;
	  	//$dsn = 'mysql:dbname=testdb;host=127.0.0.1';
		try {
		    self::$objects['db'] = new PDO($dsn, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
		} catch (PDOException $e) {
			trigger_error('database connection failed: ' . $e->getMessage() , E_USER_ERROR);
		}
       	self::$objects['db'] = new $object( self::$instance );
       	//load config
       	try{
	    	self::$objects['db']->prepare("select configuration_key, configuration_value from " . DB_PREFIX . "configuration");
	    	self::$objects['db']->execute();
	    	foreach(self::$objects['db']->fetch(PDO::FETCH_LAZY) as $row){
	  			self::storeSetting($row['configuration_value'], $row['configuration_key']);
	  		}
       	}catch (PDOException $e) {
       		trigger_error(LOAD_CONFIG_ERROR . $e->getMessage(), E_USER_ERROR);
		}
    }

    public function startProcess(){
    	global $messageStack;
    	try{
    		try{
	    		$name = $_REQUEST['module'] . "\\" . $_REQUEST['page'];
	    		$class = new $name;
	    		$ModuleActionBefore = $_REQUEST['module'] . "_" . $_REQUEST['page'] . "_before_" . $_REQUEST['action'];
	    		foreach (get_declared_classes() as $module) if (method_exists($module, $ModuleActionBefore)) $module->$ModuleActionBefore();
	    		$ActionBefore = "before_" . $_REQUEST['action'];
	    		if (method_exists($class, $ActionBefore)) $class->$ActionBefore();
	    		if (method_exists($class, $_REQUEST['action'])){
	    			$class->$_REQUEST['action']();
	    		}else{
	    			throw new \core\classes\userException($_REQUEST['action'] . " method is not availeble in $class");
	    		}
	    		$ActionAfter = "after_" . $_REQUEST['action'];
	    		if (method_exists($class, $ActionAfter)) $class->$ActionAfter();
	    		$ModuleActionAfter  = $_REQUEST['module'] . "_" . $_REQUEST['page'] . "_after_"  . $_REQUEST['action'];
	    		foreach (get_declared_classes() as $module) if (method_exists($module, $ModuleActionAfter))  $module->$ModuleActionAfter();
	    		if (method_exists($class, $_REQUEST['display'])) $class->$_REQUEST['display']();
    		}catch(Exception $e) {
	  			$messageStack->add($e->getMessage(), $e->getCode());
	  			if (method_exists($class, $_REQUEST['display'])){
	  				$class->$_REQUEST['display']();
	  			}else{
	  				throw new \core\classes\userException($_REQUEST['display'] . " method is not availeble in $class", '', $e);
	  			}
			}
    	}catch(Exception $e) {
	  		core\page::home();
		}

    }

 	/**
 	 * funtion executes the flush and closes the session.
 	 * @return void
 	 */
    public function __destruct(){
    	while (@ob_end_flush());
    	session_write_close();
    }

}

?>