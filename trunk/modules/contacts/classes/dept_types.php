<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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

class dept_types {
    public $extra_buttons = '';
    public $db_table      = TABLE_DEPT_TYPES;
    public $help_path     = '07.07.03';
    public $title         = '';
    public $error         = false;

    public function __construct(){
    	foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
    	$this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
        $this->security_id = $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION];
    }

  function btn_save($id = '') {
  	global $db, $messageStack;
	if ($this->security_id < 2) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		return false;
	}
    $description = db_prepare_input($_POST['description']);
	$sql_data_array = array('description' => $description);
    if (!$this->id == '') {
	  db_perform($this->db_table, $sql_data_array, 'update', "id = '" .$this->id . "'");
      gen_add_audit_log(SETUP_DEPT_TYPES_LOG . TEXT_UPDATE, $description);
	} else  {
      db_perform($this->db_table, $sql_data_array);
	  gen_add_audit_log(SETUP_DEPT_TYPES_LOG . TEXT_ADD, $description);
	}
	return true;
  }

  function btn_delete($id = 0) {
  	global $db, $messageStack;
	if ($this->security_id < 4) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		return false;
	}
	// Check for this department type being used in a department, if so do not delete
	$result = $db->Execute("select department_type from " . TABLE_DEPARTMENTS);
	while (!$result->EOF) {
	  if ($this->id == $result->fields['department_type']) {
		$messageStack->add(SETUP_DEPT_TYPES_DELETE_ERROR,'error');
		return false;
	  }
	  $result->MoveNext();
	}
	// OK to delete
	$result = $db->Execute("select description from " . $this->db_table . " where id = '" . $this->id . "'");
	$db->Execute("delete from " . $this->db_table . " where id = '" . $this->id . "'");
	gen_add_audit_log(SETUP_DEPT_TYPES_LOG . TEXT_DELETE, $result->fields['description']);
	return true;
  }

  function build_main_html() {
  	global $db, $messageStack;
    $content = array();
	$content['thead'] = array(
	  'value' => array(SETUP_INFO_DEPT_TYPES_NAME, TEXT_ACTION),
	  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select id, description from " . $this->db_table);
    $rowCnt = 0;
	while (!$result->EOF) {
	  $actions = '';
	  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\'dept_types_edit\', \'' . $result->fields['id'] . '\')"') . chr(10);
	  if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_DEPT_TYPES_DELETE_INTRO . '\')) subjectDelete(\'dept_types\', ' . $result->fields['id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['description']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'dept_types_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable('dept_type_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db;
    if ($action <> 'new' && $this->error == false) {
        $sql = "select description from " . $this->db_table . " where id = '" . $this->id . "'";
        $result = $db->Execute($sql);
        foreach ($result->fields as $key => $value) $this->$key = $value;
    }
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? SETUP_INFO_HEADING_NEW_DEPT_TYPES : SETUP_INFO_HEADING_EDIT_DEPT_TYPES) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
    $output .= '  <tr>' . chr(10);
	$output .= '    <td colspan="2">' . ($action=='new' ? SETUP_DEPT_TYPES_INSERT_INTRO : HR_EDIT_INTRO) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_DEPT_TYPES_NAME . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description', $this->description) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . chr(10);
    return $output;
  }
}
?>