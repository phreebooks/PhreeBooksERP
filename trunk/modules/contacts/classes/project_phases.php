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
//  Path: /modules/contacts/classes/project_phases.php
//
namespace contacts\classes;
require_once(DIR_FS_MODULES . 'contacts/defaults.php');

class project_phases {
    public $extra_buttons = '';
    public $db_table      = TABLE_PROJECTS_PHASES;
    public $help_path     = '';
    public $title         = '';

    public function __construct(){
    	foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
    	$this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
        $this->security_id = \core\classes\user::security_level(SECURITY_ID_CONFIGURATION);
    }

	function btn_save($id = '') {
	  	global $admin;
		\core\classes\user::validate_security($this->security_id, 2); // security check
	    $description_short = db_prepare_input($_POST['description_short']);
		$sql_data_array = array(
		  'description_short' => $description_short,
		  'description_long'  => db_prepare_input($_POST['description_long']),
		  'cost_type'         => db_prepare_input($_POST['cost_type']),
		  'cost_breakdown'    => isset($_POST['cost_breakdown']) ? '1' : '0',
		  'inactive'          => isset($_POST['inactive'])       ? '1' : '0',
		);
	    if (!$this->id == '') {
		  	db_perform($this->db_table, $sql_data_array, 'update', "phase_id = '" . $this->id . "'");
		  	gen_add_audit_log(TEXT_PROJECT_PHASE . ' - ' . TEXT_UPDATE, $description_short);
		} else  {
	      	db_perform($this->db_table, $sql_data_array);
			gen_add_audit_log(TEXT_PROJECT_PHASE . ' - ' . TEXT_ADD, $description_short);
		}
		return true;
	}

	function btn_delete($id = 0) {
	  	global $admin;
		\core\classes\user::validate_security($this->security_id, 4); // security check
		$result = $admin->DataBase->query("SELECT description_short FROM {$this->db_table} WHERE phase_id = '{$this->id}'");
		$admin->DataBase->exec("DELETE FROM {$this->db_table} WHERE phase_id = '{$this->id}'");
		gen_add_audit_log(TEXT_PROJECT_PHASE . ' - ' . TEXT_DELETE, $result['description_short']);
		return true;
	}

	function build_main_html() {
	  	global $admin;
	    $content = array();
		$content['thead'] = array(
		  'value' => array(TEXT_SHORT_NAME, TEXT_DESCRIPTION, TEXT_COST_TYPE, TEXT_COST_BREAKDOWN, TEXT_INACTIVE, TEXT_ACTION),
		  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
		);
	    $result = $admin->DataBase->prepare("SELECT phase_id, description_short, description_long, cost_type, cost_breakdown, inactive FROM {$this->db_table}");
	    $sql->execute();
	    $project_costs = new \contacts\classes\project_costs();
		while ($result = $sql->fetch(\PDO::FETCH_ASSOC)){
			$actions = '';
			if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="loadPopUp(\'project_phases_edit\', ' . $result['phase_id'] . ')"') . chr(10);
			if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_PROJECT_PHASES_DELETE_INTRO . '\')) subjectDelete(\'project_phases\', ' . $result['phase_id'] . ')"') . chr(10);
			$content['tbody'][] = array(
			  array('value' => htmlspecialchars($result['description_short']),
					'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result['cost_id'].'\')"'),
			  array('value' => htmlspecialchars($result['description_long']),
					'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result['cost_id'].'\')"'),
			  array('value' => $project_costs->cost_types[$result['cost_type']],
					'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result['cost_id'].'\')"'),
			  array('value' => $result['cost_breakdown'] ? TEXT_YES : '',
					'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result['cost_id'].'\')"'),
			  array('value' => $result['inactive'] ? TEXT_YES : '',
					'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'project_costs_edit\',\''.$result['cost_id'].'\')"'),
			  array('value' => $actions,
					'params'=> 'align="right"'),
			);
	    }
	    return html_datatable('proj_phase_table', $content);
	}

  function build_form_html($action, $id = '') {
    global $admin;
    if ($action <> 'new') {
        $sql = "SELECT description_short, description_long, cost_type, cost_breakdown, inactive
	       FROM {$this->db_table} where phase_id = '{$this->id}'";
        $result = $admin->DataBase->query($sql);
        foreach ($result as $key => $value) $this->$key = $value;
    }
    $project_costs = new \contacts\classes\project_costs();
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? sprintf(TEXT_NEW_ARGS, TEXT_PROJECT_PHASE) : sprintf(TEXT_EDIT_ARGS, TEXT_PROJECT_PHASE)) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <td colspan="2">' . ($action=='new' ? SETUP_PROJECT_PHASES_INSERT_INTRO : TEXT_PLEASE_MAKE_ANY_NECESSARY_CHANGES) . '</td>' . chr(10);
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
	$output .= '    <td>' . TEXT_COST_TYPE . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('cost_type', gen_build_pull_down($project_costs->cost_types), $this->cost_type) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . \core\classes\htmlElement::checkbox('cost_breakdown', TEXT_USE_COST_BREAKDOWNS_FOR_THIS_PHASE, '1', $this->cost_breakdown? true :false ) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>'. \core\classes\htmlElement::checkbox('inactive', TEXT_INACTIVE, '1', $this->inactive ? true : false).'</td>' . chr(10);
	$output .= '    <td></td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . chr(10);
    return $output;
  }
}
?>