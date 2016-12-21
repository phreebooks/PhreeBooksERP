<?php
namespace core\classes;
    /**
     * This is our page object
     * It is a seperate object to allow some interesting extra functionality to be added
     * Some ideas: passwording pages, adding page specific css/js files, etc
     */

class outputInlineForm  {

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
    }
    
    function send_header (\core\classes\basis $basis){
    	//no header neaded
    }
    
    function send_constants (\core\classes\basis $basis){ 
    }
    
    function send_menu (\core\classes\basis $basis){
    }

    public function print_menu (\core\classes\basis $basis){ }

	public function send_footer (\core\classes\basis $basis){
    }
    /**
     * this method is called by the basis object when it is done with all actions.
     * @param \SplSubject $subject
     */

    public function update (\core\classes\basis $basis) {//@todo
    	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    }
    
}

?>
