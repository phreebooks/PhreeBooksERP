<?php
namespace core\classes;
class outputXml {

	function send_header($basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		echo createXmlHeader();
	}
	
	function send_constants($basis){
		 
	}
	
	public function send(\SplSubject $basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		echo createXmlHeader();
		foreach (get_object_vars($basis) as $key => $value) echo xmlEntry($key, $value);
		echo createXmlFooter();
		return true;
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
	
	function __destruct(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		echo createXmlFooter();
	}
}
?>