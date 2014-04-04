<?php
namespace core\classes;
    /**
     * This is our page object
     * It is a seperate object to allow some interesting extra functionality to be added
     * Some ideas: passwording pages, adding page specific css/js files, etc
     */
class page {

	// header elements
    private $css_files			= array();
    private $css;
    private $js_files			= array();
    private $include_php_js_files = array();
    private $js;
    private $js_override_files	= array();
    private $js_override;
    // page elements
    public  $title = '';
    public $custom_html		= false;
    public $include_header		= false;
    public $include_footer		= false;
    public $include_template	= 'phreedom/pages/main/template_main';
    private $ModuleAndPage		= "phreedom/main";
    public  $page_title			= '';

    /**
     * Constructor...
     */
    function __construct() {
       	require_once(DIR_FS_ADMIN . DIR_WS_THEMES . '/config.php');
       	$this->js_files[] = "includes/jquery-1.6.2.min.js";
  		$this->js_files[] = "includes/jquery-ui-1.8.16.custom.min.js";
  		$this->js_files[] = "includes/jquery.dataTables.min.js";
  		$this->js_files[] = "https://www.google.com/jsapi";
  		$this->js_files[] = "includes/jquery.easyui.min.js";
  		$this->js_files[] = "includes/common.js";
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/stylesheet.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery_datatables.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery-ui.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/'.MY_COLORS.'/easyui.css';
  		$this->css_files[] = DIR_WS_THEMES.'css/icon.css';
    }

    public function print_js_includes(){
       	//first normal js files
       	foreach($this->js_files as $file){
       		echo "<script type='text/javascript' src='$file'></script>";
       	}
       	foreach($this->include_php_js_files as $file){
       		include_once ($file);
       	}
       	//then the override files
       	foreach($this->js_override_files as $file){
       		echo "<script type='text/javascript' src='$file'></script>";
       	}
       	if (SESSION_AUTO_REFRESH == '1') echo '  <script type="text/javascript">addLoadEvent(refreshSessionClock);</script>' . chr(10);
       	echo '<script type="text/javascript">addLoadEvent(init);addUnloadEvent(clearSessionClock);</script>';
    }

    public function print_css_includes(){
      	foreach($this->css_files as $file){
       		echo "<link rel='stylesheet' type='text/css' href='$file' />";
       	}
    }

    public function print_menu(){
       	if($this->include_header){
       		require_once(DIR_FS_ADMIN . DIR_WS_THEMES . '/menu.php');
       	} else{
       		echo "<div>\n";
       	}
    }

    public function loadPage ($Module, $Page, $template){
    	$this->include_template = DIR_FS_ADMIN . "modules/$Module/pages/$Page/$template.php";
    	if ( file_exists(DIR_FS_ADMIN . "modules/$module/custom/pages/$page/$template")) {
    		$this->include_template = DIR_FS_ADMIN . "modules/$module/custom/pages/$page/$template";
    	}
		$this->ModuleAndPage	= "$Module/$Page";
		// load the javascript specific, required
		$this->include_php_js_files[] = DIR_FS_ADMIN . "modules/$Module/pages/$Page/js_include.php";
		if(!file_exists(DIR_FS_ADMIN . "modules/$Module/pages/$Page/js_include.php")) trigger_error("No js_include file, looking for the file: $Module/pages/$Page/js_include.php", E_USER_ERROR);
		//load the custom javascript if present
		if (file_exists(DIR_FS_ADMIN . "modules/$Module/custom/pages/$Page/extra_js.php")) $this->include_php_js_files[] = DIR_FS_ADMIN . "modules/$Module/custom/pages/$Page/extra_js.php";
    }

    /**
     * this is called when class loses focus
     * it will store common variales in session data
     */

    function __destruct(){
    	$_SESSION[$this->ModuleAndPage]['sf']    = $_REQUEST['sf'];
    	$_SESSION[$this->ModuleAndPage]['so']    = $_REQUEST['so'];
    	$_SESSION[$this->ModuleAndPage]['list']  = $_REQUEST['list'];
    	$_SESSION[$this->ModuleAndPage]['search']= $_REQUEST['search_text'];
    	$_SESSION[$this->ModuleAndPage]['period']= $_REQUEST['search_period'];
    	$_SESSION[$this->ModuleAndPage]['date']  = $_REQUEST['search_date'];
    }
}

?>
