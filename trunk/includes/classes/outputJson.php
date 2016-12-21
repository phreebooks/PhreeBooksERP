<?php
namespace core\classes;
class outputJson {
	
	function send_header (\core\classes\basis $basis){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		header('Content-Type: application/json');
	}
	
	function send_constants (\core\classes\basis $basis){	 
	}
	
	function send_menu (\core\classes\basis $basis){
		echo '<!-- json menu-->';
	}
	
	public function update (\core\classes\basis $basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		header_remove();
		\core\classes\messageStack::debug_log("returning " . print_r($basis->cInfo, true));
		header('Content-Type: application/json');
		echo json_encode($basis->cInfo);
		return true;
	}

	function send_footer (\core\classes\basis $basis){
		echo '<!-- json footer-->';
	}
}
?>