<?php
namespace core\classes;
class outputXml implements \SplObserver{

	public function update(\SplSubject $subject) {
		if($_REQUEST['page'] == 'ajax'){
			echo createXmlHeader();
			foreach (get_object_vars($subject) as $key => $value) echo xmlEntry($key, $value);
			echo createXmlFooter();
			ob_end_flush();
			session_write_close();
			die;
		}
	}
}
?>