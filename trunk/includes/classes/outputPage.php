<?php
namespace core\classes;
    /**
     * This is our page object
     * It is a seperate object to allow some interesting extra functionality to be added
     * Some ideas: passwording pages, adding page specific css/js files, etc
     */

class outputPage implements \SplObserver {

	// header elements
    private $css_files				= array();
    private $css;
    private $js_files				= array();
    private $include_php_js_files 	= array();
    private $js;
    private $js_override_files		= array();
    private $js_override;
    // page elements
    public  $title 					= '';
    public  $custom_html			= false;
    public  $include_header			= true;
    public  $include_footer			= true;
    public  $include_template		= '';
    private $ModuleAndPage			= "phreedom/main";
    public  $page_title				= TEXT_PHREEBOOKS_ERP;

    /**
     * Constructor...
     */
    function __construct() {
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
       	$this->include_template = DIR_FS_ADMIN .'modules/phreedom/pages/main/template_main.php';
       	$this->js_files[] = "includes/jquery-1.6.2.min.js";
  		$this->js_files[] = "includes/jquery-ui-1.8.16.custom.min.js";
  		$this->js_files[] = "includes/jquery.dataTables.min.js";
  		$this->js_files[] = "https://www.google.com/jsapi";
  		$this->js_files[] = "includes/jquery.easyui.min.js";
  		$this->js_files[] = "includes/common.js";
  		$this->include_php_js_files[] = DIR_FS_ADMIN . DIR_WS_THEMES . '/config.php';
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/stylesheet.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery_datatables.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery-ui.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/easyui.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/icon.css';
    }

    public function print_js_includes($basis){
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
       	//first normal js files
       	foreach($this->js_files as $file){
       		if($file) echo "<script type='text/javascript' src='$file'></script>". chr(13);
       	}
    	foreach($basis->js_files as $file){
       		if($file) echo "<script type='text/javascript' src='$file'></script>". chr(13);
       	}
       	foreach($this->include_php_js_files as $file){
       		if($file) include_once ($file);
       	}
       	foreach($basis->include_php_js_files as $file){
       		if($file) include_once ($file);
       	}
       	//then the override files
       	foreach($basis->js_override_files as $file){
       		if($file) echo "<script type='text/javascript' src='$file'></script>". chr(13);
       	}
       	if (SESSION_AUTO_REFRESH == '1'){
       		echo '  <script type="text/javascript">addLoadEvent(refreshSessionClock);</script>' . chr(10);
       		echo '<script type="text/javascript">addLoadEvent(init);addUnloadEvent(clearSessionClock);</script>'. chr(13);
       	}
       	if($this->js)  echo "  <script type='text/javascrip'>$this->js</script>" . chr(10);
       	if($basis->js) echo "  <script type='text/javascrip'>$basis->js</script>" . chr(10);
    }

    public function print_css_includes($basis){
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
      	foreach($this->css_files as $file){
       		if($file) echo "<link rel='stylesheet' type='text/css' href='$file' />". chr(13);
       	}
    }

    public function print_menu($basis){
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
       	if($basis->include_header){
       		require_once(DIR_FS_ADMIN . DIR_WS_THEMES . '/menu.php');
       	} else{
       		echo "<div>\n";
       	}
    }

    /**
     * this method is called by the basis object when it is done with all actions.
     * @param \SplSubject $subject
     */

    public function update(\SplSubject $basis) {//@todo
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
//    	if ($basis->page != 'json' && $basis->page != 'ajax' && $basis->page == 'mobile') {
    		$this->include_template = DIR_FS_ADMIN . "modules/{$basis->module}/pages/{$basis->page}/{$basis->template}.php";
	    	if ( file_exists(DIR_FS_ADMIN . "modules/{$basis->module}/custom/pages/{$basis->page}/{$basis->template}.php")) {
	    		$this->include_template = DIR_FS_ADMIN . "modules/{$basis->module}/custom/pages/{$basis->page}/{$basis->template}.php";
	    	}
			$this->ModuleAndPage	= "{$basis->module}/{$basis->page}";
			// load the javascript specific, required
			$this->include_php_js_files[] = DIR_FS_ADMIN . "modules/{$basis->module}/pages/{$basis->page}/js_include.php";
			if ( !file_exists(DIR_FS_ADMIN . "modules/{$basis->module}/pages/{$basis->page}/js_include.php")) trigger_error("No js_include file, looking for the file: {$basis->module}/pages/{$basis->page}/js_include.php", E_USER_ERROR);
			// load the jquery and javascript translations
			if      (file_exists("modules/phreedom/custom/language/{$_SESSION['language']}/jquery_i18n.js")) {
				$this->js_files[] = "modules/phreedom/custom/language/{$_SESSION['language']}/jquery_i18n.js";
			} elseif(file_exists("modules/phreedom/language/{$_SESSION['language']}/jquery_i18n.js")) {
				$this->js_files[] = "modules/phreedom/language/{$_SESSION['language']}/jquery_i18n.js";
			} else               $this->js_files[] = "modules/phreedom/language/en_us/jquery_i18n.js";
			//for easyui
			if      (file_exists("includes/easyui/custom/language/{$_SESSION['language']}/easyui_lang.js")) {
				$this->js_files[] = "includes/easyui/custom/language/{$_SESSION['language']}/easyui_lang.js";
			} elseif(file_exists("includes/easyui/language/{$_SESSION['language']}/easyui_lang.js")) {
				$this->js_files[] = "includes/easyui/language/{$_SESSION['language']}/easyui_lang.js";
			} else               $this->js_files[] = "includes/easyui/language/en_us/easyui_lang.js";
			//load the custom javascript if present
			if (file_exists(DIR_FS_ADMIN . "modules/{$basis->module}/custom/pages/{$basis->page}/extra_js.php")) $this->include_php_js_files[] = DIR_FS_ADMIN . "modules/{$basis->module}/custom/pages/{$basis->page}/extra_js.php";
			require('includes/template_index.php');
			return true;
/*		}else{
			return false;
		}*/
    }

    /**
     * this is called when class loses focus
     * it will store common variales in session data
     */

    function __destruct(){
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	$_SESSION[$this->ModuleAndPage]['sf']    = $_REQUEST['sf'];
    	$_SESSION[$this->ModuleAndPage]['so']    = $_REQUEST['so'];
    	$_SESSION[$this->ModuleAndPage]['list']  = $_REQUEST['list'];
    	$_SESSION[$this->ModuleAndPage]['search']= $_REQUEST['search_text'];
    	$_SESSION[$this->ModuleAndPage]['period']= $_REQUEST['search_period'];
    	$_SESSION[$this->ModuleAndPage]['date']  = $_REQUEST['search_date'];
    }
}

?>
