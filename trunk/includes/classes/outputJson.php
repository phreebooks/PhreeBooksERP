<?php
namespace core\classes;
class outputJson {
	
	function send_header($basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		header('Content-Type: application/json');
	}
	function send_constants($basis){	 
	}
	function send_menu($basis){
		echo '<!-- json menu-->';
	}
	
	public function send() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		global $admin;
		header_remove();
		header('Content-Type: application/json');
		echo json_encode($admin->cInfo);
		return true;
	}

	function send_footer($basis){
		echo '<!-- json footer-->';
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
}
?>