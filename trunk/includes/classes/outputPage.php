<?php
namespace core\classes;
    /**
     * This is our page object
     * It is a seperate object to allow some interesting extra functionality to be added
     * Some ideas: passwording pages, adding page specific css/js files, etc
     */

class outputPage  {

	// header elements
    private $css_files				= array();
    private $css;
    private $js_files				= array();
    private $include_php_js_files 	= array();
    private $js;
    private $js_override_files		= array();
    private $js_override;
    private $menu_send				= false;
    // page elements
    public  $title 					= '';
    public  $custom_html			= false;
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
       	$this->js_files[] = "includes/jquery.dataTables.min.js";// @todo only nessecery
    }
    
    function send_header($basis){
    	//header_remove();
    	header("Content-type: text/html; charset=".CHARSET);
    	if ($force_reset_cache) { header("Cache-Control: no-cache, must-revalidate"); header("Expires: ".date('D, j M \2\0\0\0 G:i:s T')); }
    	echo "<!DOCTYPE html>";
    	echo "<html ".HTML_PARAMS.">";
    	echo "<head>";
    	echo "<meta http-equiv='Content-Type' content='text/html; charset=".CHARSET."' />";
    	echo "<title>".TEXT_PHREEBOOKS_ERP."</title>";
    	echo "<link rel='stylesheet' type='text/css' href='".DIR_WS_THEMES.'css/'.MY_COLORS.'/stylesheet.css'."' />". chr(13);
    	echo "<link rel='stylesheet' type='text/css' href='".DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery_datatables.css'."' />". chr(13);
    	echo "<link rel='stylesheet' type='text/css' href='".DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery-ui.css'."' />". chr(13);
    	echo "<link rel='stylesheet' type='text/css' href='".DIR_WS_THEMES.'css/'.MY_COLORS.'/easyui.css'."' />". chr(13);
    	echo "<link rel='stylesheet' type='text/css' href='".DIR_WS_THEMES.'css/icon.css'."' />". chr(13);
    	echo "<script type='text/javascript' src='includes/common.js'></script>". chr(13);
    	echo "<script type='text/javascript' src='includes/jquery-2.2.0.min.js'></script>". chr(13);
    	echo "<script type='text/javascript' src='includes/jquery.easyui.min.js'></script>". chr(13);
//    	echo "<script type='text/javascript' src='https://www.google.com/jsapi'></script>". chr(13);
    	//setting easyui defaults.
		echo "<script type='text/javascript' >
				$.fn.validatebox.defaults.invalidMessage = '".TEXT_INVALID_VALUE."';
				$.fn.validatebox.defaults.missingMessage = '".TEXT_THIS_FIELD_IS_REQUIRED."';		
			 </script>";
		echo "</head><body>";
    	ob_flush();
    }
    
    function send_menu($basis){
    	if ($this->menu_send) return;
    	usort($basis->mainmenu, array($this,'sortByOrder'));
    	echo '<!-- Pull Down Menu -->' . chr(10);
    	switch (MY_MENU) {
    		case 'left': echo '<div id="smoothmenu" class="ddsmoothmenu-v" style="float:left">'.chr(10); break;
    		case 'top':
    		default:     echo '<div id="smoothmenu" class="ddsmoothmenu">'.chr(10); break;
    	}
    	echo '  <ul>' . chr(10);
    	foreach($basis->mainmenu as $menu_item)	$menu_item->output();
    	echo '  </ul>' . chr(10);
    	echo '<br style="clear:left" />'.chr(10);
    	echo '</div>'.chr(10);
    	switch (MY_MENU) {
    		case 'left': echo '<div style="float:left;margin-left:auto;margin-right:auto;">'.chr(10); break;
    		case 'top':
    		default:     echo '<div>'.chr(10); break;
    	}?>
    	<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/ddsmoothmenu.css'; ?>" />
    	<script type="text/javascript" src="themes/default/ddsmoothmenu.js">
    	/***********************************************
    	 * Smooth Navigational Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
    	* This notice MUST stay intact for legal use
    	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
    	***********************************************/
    	</script>
    	<script type="text/javascript">
   		ddsmoothmenu.init({
  		mainmenuid: "smoothmenu",
    		orientation: '<?php echo MY_MENU=='left'?'v':'h';?>',
    		classname: '<?php echo MY_MENU=='left'?'ddsmoothmenu-v':'ddsmoothmenu';?>',
    		contentsource: "markup"
    	})
		var date_format         = '<?php echo DATE_FORMAT; ?>';
  		var date_delimiter      = '<?php echo DATE_DELIMITER; ?>';
  		var inactive_text_color = '#cccccc';
  		var decimal_places      = <?php  echo $basis->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']; ?>;
  		var decimal_precise     = <?php  echo $basis->currencies->currencies[DEFAULT_CURRENCY]['decimal_precise']; ?>;
  		var decimal_point       = "<?php echo $basis->currencies->currencies[DEFAULT_CURRENCY]['decimal_point']; ?>"; // leave " for ' separator
  		var thousands_point     = "<?php echo $basis->currencies->currencies[DEFAULT_CURRENCY]['thousands_point']; ?>";
  		var formatted_zero      = "<?php echo $basis->currencies->format(0); ?>";
    	</script> 
    	<?php 
    	ob_flush();
    	$this->menu_send = true;
    }

	function sortByOrder($a, $b) {
		if (is_integer($a->order) && is_integer($b->order)) return $a->order - $b->order;
		return strcmp($a->order, $b->order);
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

    public function print_menu($basis){
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
       	if($basis->include_header){
       		require_once(DIR_FS_ADMIN . DIR_WS_THEMES . '/menu.php');
       	} else{
       		echo "<div>\n";
       	}
    }

	public function send_footer($basis){
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
       	$image_path = defined('FOOTER_LOGO') ? FOOTER_LOGO : (DIR_WS_ADMIN . 'modules/phreedom/images/phreesoft_logo.png');
       	echo '<div style="clear:both;text-align:center;font-size:9px">';
       	echo "<a href='http://www.PhreeSoft.com' target='_blank'>". html_image($image_path, TEXT_PHREEDOM_INFO, NULL, '64') ."</a><br />";
       	echo COMPANY_NAME.' | '.TEXT_ACCOUNTING_PERIOD.': '.CURRENT_ACCOUNTING_PERIOD.' | '.TEXT_PHREEDOM_INFO." ({$basis->classes['phreedom']->version}) ";
       	if ($basis->module <> 'phreedom') echo "({$basis->module} {$basis->classes[$basis->module]->version}) ";
       	echo '<br />' . TEXT_COPYRIGHT .  ' &copy;' . date('Y') . ' <a href="http://www.PhreeSoft.com" target="_blank">PhreeSoft&trade;</a>';
       	echo '(' . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . ' ms) ' . $basis->DataBase->count_queries . ' SQLs (' . (int)($basis->DataBase->total_query_time * 1000).' ms) </div>';
       	ob_flush();
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
			if      (file_exists("modules/phreedom/custom/language/{$_SESSION['language']->language_code}/jquery_i18n.js")) {
				$this->js_files[] = "modules/phreedom/custom/language/{$_SESSION['language']->language_code}/jquery_i18n.js";
			} elseif(file_exists("modules/phreedom/language/{$_SESSION['language']->language_code}/jquery_i18n.js")) {
				$this->js_files[] = "modules/phreedom/language/{$_SESSION['language']->language_code}/jquery_i18n.js";
			} else               $this->js_files[] = "modules/phreedom/language/en_us/jquery_i18n.js";
			//for easyui
			if      (file_exists("includes/easyui/custom/language/{$_SESSION['language']->language_code}/easyui_lang.js")) {
				$this->js_files[] = "includes/easyui/custom/language/{$_SESSION['language']->language_code}/easyui_lang.js";
			} elseif(file_exists("includes/easyui/language/{$_SESSION['language']->language_code}/easyui_lang.js")) {
				$this->js_files[] = "includes/easyui/language/{$_SESSION['language']->language_code}/easyui_lang.js";
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
     * returns the current template
     * @return string
     */
    function get_template(){
    	if (empty($this->include_template) || $this->include_template == '' ){
    		return DIR_FS_ADMIN .'modules/phreedom/pages/main/template_main.php';
    	}
		return $this->include_template;
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
