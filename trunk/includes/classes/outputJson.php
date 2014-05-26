<?php
namespace core\classes;
class outputJson implements \SplObserver{

	public function update(\SplSubject $cInfo) {
		if($cInfo->page == 'json'){
			header('Content-Type: application/json');
			echo json_encode($cInfo);
			if (DEBUG) $messageStack->write_debug();
			ob_end_flush();
			session_write_close();
			die;
		}
	}
}
?>