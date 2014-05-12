<?php
namespace core\classes;
class outputJson implements \SplObserver{

	public function update(\SplSubject $subject) {
		if($_REQUEST['page'] == 'json'){
			header('Content-Type: application/json');
			echo json_encode($subject);
			ob_end_flush();
			session_write_close();
			die;
		}
	}
}
?>