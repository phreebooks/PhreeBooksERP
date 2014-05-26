<?php
namespace core\classes;
class outputXml implements \SplObserver{

	public function update(\SplSubject $cInfo) {
		if($cInfo->page == 'ajax'){
			echo createXmlHeader();
			foreach (get_object_vars($cInfo) as $key => $value) echo xmlEntry($key, $value);
			echo createXmlFooter();
			if (DEBUG) $messageStack->write_debug();
			ob_end_flush();
			session_write_close();
			die;
		}
	}
}
?>