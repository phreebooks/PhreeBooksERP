<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/contacts/classes/dept_types.php
//
namespace contacts\classes;
class dept_types {
    public $extra_buttons = '';
    public $db_table      = TABLE_DEPT_TYPES;
    public $help_path     = '07.07.03';
    public $title         = '';

    public function __construct(){
    	foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
    	$this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
        $this->security_id = \core\classes\user::security_level(SECURITY_ID_CONFIGURATION);
    }

  function btn_save($id = '') {
  	global $admin;
	\core\classes\user::validate_security($this->security_id, 2); // security check
    $description = db_prepare_input($_POST['description']);
	$sql_data_array = array('description' => $description);
    if (!$this->id == '') {
	  db_perform($this->db_table, $sql_data_array, 'update', "id = '" .$this->id . "'");
      gen_add_audit_log(TEXT_DEPARTMENT_TYPE . ' - ' . TEXT_UPDATE, $description);
	} else  {
      db_perform($this->db_table, $sql_data_array);
	  gen_add_audit_log(TEXT_DEPARTMENT_TYPE . ' - ' . TEXT_ADD, $description);
	}
	return true;
  }

  	function btn_delete($id = 0) {
  		global $admin;
		\core\classes\user::validate_security($this->security_id, 4); // security check
		// Check for this department type being used in a department, if so do not delete
		$sql = $admin->DataBase->prepare("SELECT department_type FROM " . TABLE_DEPARTMENTS);
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_ASSOC)){
	  		if ($this->id == $result['department_type']) throw new \core\classes\userException(SETUP_DEPT_TYPES_DELETE_ERROR);
		}
		// OK to delete
		$result = $admin->DataBase->query("SELECT description FROM {$this->db_table} WHERE id = '{$this->id}'");
		$admin->DataBase->exec("DELETE FROM {$this->db_table} WHERE id = '{$this->id}'");
		gen_add_audit_log(TEXT_DEPARTMENT_TYPE . ' - ' . TEXT_DELETE, $result['description']);
		return true;
  	}

  	function build_main_html() {
  		global $admin;
	    $content = array();
		$content['thead'] = array(
			'value' => array(TEXT_DESCRIPTION, TEXT_ACTION),
	  		'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
		);
    	$sql = $admin->DataBase->prepare("SELECT id, description FROM " . $this->db_table);
	    $sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_ASSOC)){
	  		$actions = '';
			if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\'dept_types_edit\', \'' . $result['id'] . '\')"') . chr(10);
	  		if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_DEPT_TYPES_DELETE_INTRO . '\')) subjectDelete(\'dept_types\', ' . $result['id'] . ')"') . chr(10);
	  		$content['tbody'][] = array(
			    array('value' => htmlspecialchars($result['description']),
					  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'dept_types_edit\',\''.$result['id'].'\')"'),
				array('value' => $actions,
					  'params'=> 'align="right"'),
			);
    	}
    	return html_datatable('dept_type_table', $content);
	}

  function build_form_html($action, $id = '') {
    global $admin;
    if ($action <> 'new') {
        $sql = "SELECT description FROM {$this->db_table} where id = '{$this->id}'";
        $result = $admin->DataBase->query($sql);
        foreach ($result as $key => $value) $this->$key = $value;
    }
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? sprintf(TEXT_NEW_ARGS, TEXT_DEPARTMENT_TYPE) : sprintf(TEXT_EDIT_ARGS, TEXT_DEPARTMENT_TYPE)) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
    $output .= '  <tr>' . chr(10);
	$output .= '    <td colspan="2">' . ($action=='new' ? TEXT_PLEASE_ENTER_THE_NEW_DEPARTMENT_TYPE : TEXT_PLEASE_MAKE_ANY_NECESSARY_CHANGES) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_DESCRIPTION . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description', $this->description) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . chr(10);
    return $output;
  }
}
?>