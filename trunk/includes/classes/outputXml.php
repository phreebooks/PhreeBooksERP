<?php
namespace core\classes;
class outputXml implements \SplObserver{

	public function update(\SplSubject $basis) {
		global $messageStack;
		if($basis->page == 'ajax'){
			echo createXmlHeader();
			foreach (get_object_vars($basis) as $key => $value) echo xmlEntry($key, $value);
			echo createXmlFooter();
			return true;
		}else{
			return false;
		}
	}
}
?>