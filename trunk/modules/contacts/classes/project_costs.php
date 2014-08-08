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
//  Path: /modules/contacts/classes/project_costs.php
//

require_once(DIR_FS_MODULES . 'contacts/defaults.php');

class project_costs {
	public $extra_buttons = '';
	public $db_table      = TABLE_PROJECTS_COSTS;
    public $help_path     = '';
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
    $description_short = db_prepare_input($_POST['description_short']);
	$sql_data_array = array(
	  'description_short' => $description_short,
	  'description_long'  => db_prepare_input($_POST['description_long']),
	  'cost_type'         => db_prepare_input($_POST['cost_type']),
	  'inactive'          => isset($_POST['inactive']) ? '1' : '0',
	);
    if (!$this->id == '') {
	  db_perform($this->db_table, $sql_data_array, 'update', "cost_id = '" . $this->id . "'");
	  gen_add_audit_log(SETUP_PROJECT_COSTS_LOG . TEXT_UPDATE, $description_short);
	} else  {
      db_perform($this->db_table, $sql_data_array);
	  gen_add_audit_log(SETUP_PROJECT_COSTS_LOG . TEXT_ADD, $description_short);
	}
	return true;
  }

  function btn_delete($id = 0) {
  	global $db, $messageStack;
	if ($this->security_id < 4) {
	  $messageStack->add(ERROR_NO_PERMISSION,'error');
	  return false;
	}
/*
	// TBD - Check for this project phase being used in a journal entry, if so do not allow deletion
	$result = $db->Execute("select projects from " . TABLE_JOURNAL_ITEM . " 
		where projects like '%" . $id . "%'");
	while (!$result->EOF) {
	  $cost_ids = explode(':', $result->fields['projects']);
	  for ($i = 0; $i < count($cost_ids); $i++) {
		if ($id == $cost_ids[$i]) {
		  $messageStack->add(SETUP_PROJECT_COSTS_DELETE_ERROR,'error');
		  return false;
		}
	  }
	  $result->MoveNext();
	}
*/
	// OK to delete
	$result = $db->Execute("select description_short from " . $this->db_table . " where cost_id = '" . $this->id . "'");
	$db->Execute("delete from " . $this->db_table . " where cost_id = '" . $this->id . "'");
	gen_add_audit_log(SETUP_PROJECT_COSTSS_LOG . TEXT_DELETE, $result->fields['description_short']);
	return true;
  }

  function build_main_html() {
  	global $db, $messageStack, $project_cost_types;
    $content = array();
	$content['thead'] = array(
	  'value' => array(TEXT_SHORT_NAME, TEXT_COST_TYPE, TEXT_INACTIVE, TEXT_ACTION),
	  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select cost_id, description_short, cost_type, inactive from " . $this->db_table);
    $rowCnt = 0;
	while (!$result->EOF) {
	  $params  = unserialize($result->fields['params']);
	  $actions = '';
	  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="loadPopUp(\'project_costs_edit\', ' . $result->fields['cost_id'] . ')"') . chr(10);
	  if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_PROJECT_COSTS_DELETE_INTRO . '\')) subjectDelete(\'project_costs\', ' . $result->fields['cost_id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['description_short']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result->fields['cost_id'].'\')"'),
		array('value' => $project_cost_types[$result->fields['cost_type']], 
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result->fields['cost_id'].'\')"'),
		array('value' => $result->fields['inactive'] ? TEXT_YES : '',
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result->fields['cost_id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable('proj_cost_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db, $project_cost_types;
    if ($action <> 'new' && $this->error == false) {
        $sql = "select description_short, description_long, cost_type, inactive 
	       from " . $this->db_table . " where cost_id = '" . $this->id . "'";
        $result = $db->Execute($sql);
        foreach ($result->fields as $key => $value) $this->$key = $value;
    }

	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? SETUP_INFO_HEADING_NEW_PROJECT_COSTS : SETUP_INFO_HEADING_EDIT_PROJECT_COSTS) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <td colspan="2">' . ($action=='new' ? SETUP_PROJECT_COSTS_INSERT_INTRO : HR_EDIT_INTRO) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_DESC_SHORT . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description_short', $this->description_short, 'size="17" maxlength="16"') . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_DESC_LONG . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description_long', $this->description_long, 'size="50" maxlength="64"') . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_COST_TYPE . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('cost_type', gen_build_pull_down($project_cost_types), $this->cost_type) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_INACTIVE . '</td>' . chr(10);
	$output .= '    <td>' . html_checkbox_field('inactive', '1', $this->inactive ? true : false) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . chr(10);
    return $output;
  }
}
?>