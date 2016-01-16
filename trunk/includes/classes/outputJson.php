<?php
namespace core\classes;
class outputJson {

	public function update(\SplSubject $basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		global $messageStack;
		if($basis->page == 'json'){
			header_remove();
			header('Content-Type: application/json');
			echo json_encode($basis);
			return true;
		}else{
			return false;
		}

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