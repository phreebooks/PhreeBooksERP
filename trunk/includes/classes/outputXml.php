<?php
namespace core\classes;
class outputXml {

	function send_header($basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		echo createXmlHeader();
	}
	
	function send_constants($basis){
		 
	}
	
	public function update(\SplSubject $basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		echo createXmlHeader();
		foreach (get_object_vars($basis) as $key => $value) echo xmlEntry($key, $value);
		echo createXmlFooter();
		return true;
	}
}
?>