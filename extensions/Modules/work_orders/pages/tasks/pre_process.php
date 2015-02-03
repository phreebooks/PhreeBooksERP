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
//  Path: /modules/work_orders/pages/tasks/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_WORK_ORDERS_TASK);
/**************  include page specific files    *********************/
require(DIR_FS_WORKING . 'defaults.php');
require(DIR_FS_WORKING . 'functions/work_orders.php');
/**************   page specific initialization  *************************/
$processed   = false;
history_filter('wo_tasks');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/tasks/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  	try{
		\core\classes\user::validate_security($security_level, 2);
	  	$id          = db_prepare_input($_POST['id']);
		$task_name   = db_prepare_input($_POST['task_name']);
		$description = db_prepare_input($_POST['description']);
		$ref_doc     = db_prepare_input($_POST['ref_doc']);
		$ref_spec    = db_prepare_input($_POST['ref_spec']);
		$dept_id     = db_prepare_input($_POST['dept_id']);
		$job_time    = db_prepare_input($_POST['job_time']);
		$job_unit    = db_prepare_input($_POST['job_unit']);
		$mfg         = $_POST['mfg']        ? '1' : '0';
		$qa          = $_POST['qa']         ? '1' : '0';
		$data_entry  = $_POST['data_entry'] ? '1' : '0';
		$erp_entry   = $_POST['erp_entry']  ? '1' : '0';
		// error check
		if (!$task_name || !$description) throw new \core\classes\userException(WO_TASK_ID_MISSING);

		$result = $admin->DataBase->query("select id from " . TABLE_WO_TASK . " where task_name = '" . $task_name . "'");
		if ($result->Recordcount() > 0) {
		  	if ($result->fields['id'] <> $id) throw new \core\classes\userException(WO_DUPLICATE_TASK_ID);
		}
		// write the data

		$sql_data_array = array(
		  'task_name'   => $task_name,
		  'description' => $description,
		  'ref_doc'     => $ref_doc,
		  'ref_spec'    => $ref_spec,
		  'dept_id'     => $dept_id,
		  'job_time'    => $job_time,
		  'job_unit'    => $job_unit,
		  'mfg'         => $mfg,
		  'qa'          => $qa,
		  'data_entry'  => $data_entry,
		  'erp_entry'   => $erp_entry,
		);
		if ($id) {
		    if (!db_perform(TABLE_WO_TASK, $sql_data_array, 'update', 'id = ' . $id)) throw new \core\classes\userException("unable to update $id in the database");
			gen_add_audit_log(sprintf(WO_AUDIT_LOG_TASK, TEXT_UPDATE) . $task_name);
		} else {
		    if (!db_perform(TABLE_WO_TASK, $sql_data_array, 'insert')) throw new \core\classes\userException("unable to insert in the database");
			gen_add_audit_log(sprintf(WO_AUDIT_LOG_TASK, TEXT_ADD) . $task_name);
		}
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS,($id ? TEXT_UPDATED : TEXT_ADDED), TEXT_WORK_ORDER_TASK , $task_name),'success');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
  	}catch(Exception $e){
  		$messageStack->add($e->getMessage());
  	}
	break;
  case 'delete':
	$id = db_prepare_input($_GET['cID']);
	// check to see if the task is used in any defined work orders. If so don't let it be deleted.
	$result = $admin->DataBase->query("select ref_id from " . TABLE_WO_JOURNAL_ITEM . " where task_id = " . $id);
	if ($result->rowCount() == 0) {
	  	$admin->DataBase->exec("delete from " . TABLE_WO_TASK . " where id = " . $id);
	  	gen_add_audit_log(sprintf(WO_AUDIT_LOG_TASK, TEXT_DELETE), $id);
	  	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	} else {
	  	$messageStack->add(sprintf(WO_ERROR_CANNOT_DELETE . $result->fields['ref_id']), 'error');
	}
	break;
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
$yes_no_array = array(
  array('id' => '0', 'text' => TEXT_NO),
  array('id' => '1', 'text' => TEXT_YES),
);

// build departments
$result = $admin->DataBase->query("select id, description from " . TABLE_DEPARTMENTS . " order by description");
$departments = array(array('id' => '', 'text' => TEXT_PLEASE_SELECT));
while (!$result->EOF) {
  $departments[$result->fields['id']] = array('id' => $result->fields['id'], 'text' => $result->fields['description']);
  $result->MoveNext();
}

// build the list header
$heading_array = array(
  'task_name'   => TEXT_TASK_NAME,
  'description' => TEXT_DESCRIPTION,
  'ref_doc'     => TEXT_DOCUMENT_LIST,
  'ref_spec'    => TEXT_DRAWINGS,
  'dept_id'     => TEXT_DEPARTMENT,
);
$result      = html_heading_bar($heading_array);
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];
// build the list for the page selected
if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
  $search_fields = array('task_name', 'description', 'ref_doc', 'ref_spec');
  // hook for inserting new search fields to the query criteria.
  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  $search = " where " . implode(" like %{$_REQUEST['search_text']}%' or ", $search_fields) . " like '%{$_REQUEST['search_text']}%";
} else {
  $search = '';
}
$field_list = array('id', 'task_name', 'description', 'ref_doc', 'ref_spec','dept_id', 'mfg', 'qa', 'data_entry');
// hook to add new fields to the query return results
if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);

$query_raw    = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list)  . " from " . TABLE_WO_TASK . $search . " order by $disp_order";
$query_result = $admin->DataBase->query($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
$query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
history_save('wo_tasks');

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_WORK_ORDERS_TASK);

?>