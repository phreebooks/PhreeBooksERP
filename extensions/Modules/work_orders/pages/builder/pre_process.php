<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/work_orders/pages/builder/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_WORK_ORDERS_BUILDER);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'inventory/defaults.php');
/**************   page specific initialization  *************************/
$lock_title  = false;
$hide_save   = false;
$criteria    = array();
history_filter('wo_build');
// load the filters
$f0 = $_GET['f0'] = isset($_POST['action']) ? (isset($_POST['f0']) ? '1' : '0') : $_GET['f0']; // show inactive checkbox
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/builder/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'new':
	break;
  case 'save':
  	try{
  		$db->transStart();
		\core\classes\user::validate_security($security_level, 2);
	  	$id          = db_prepare_input($_POST['id']);
		$wo_title    = db_prepare_input($_POST['wo_title']);
		$sku_id      = db_prepare_input($_POST['sku_id']);
		$sku         = db_prepare_input($_POST['sku']);
		$description = db_prepare_input($_POST['description']);
		$allocate    = db_prepare_input($_POST['allocate']);
		$ref_doc     = db_prepare_input($_POST['ref_doc']);
		$ref_spec    = db_prepare_input($_POST['ref_spec']);
		$revision    = db_prepare_input($_POST['revision']);
		// load the steps
		$x = 1;
		$step_list = array();
		while (isset($_POST['task_' . $x])) { // while there are steps rows to read in
		  	$step_list[] = array(
			  'step'    => db_prepare_input($_POST['step_'    . $x]),
			  'task_id' => db_prepare_input($_POST['task_id_' . $x]),
		  	);
		  	$x++;
		  	// validate the task_id, error of no match
		}
		// If the sku and description were entered manually, the sku_id will be blank, find it
		if (!$sku_id) {
		  	$result = $db->Execute("select id from " . TABLE_INVENTORY . " where sku = '" . $sku . "'");
		  	if ($result->RecordCount() == 0) throw new \core\classes\userException(WO_SKU_NOT_FOUND);
		  	$sku_id = $result->fields['id'];
		}
		// error check
		if ((!$sku_id || !$sku || !$wo_title)) throw new \core\classes\userException(WO_SKU_ID_REQUIRED);
		// check the revision, roll if necessary
		if ($id) {
		  	$result = $db->Execute("select revision, last_usage from " . TABLE_WO_MAIN . " where id = " . $id);
		  	if ($result->fields['last_usage'] <> '0000-00-00') { // roll the revision
		    	$revision = $result->fields['revision'] + 1;
				$id = '';
				$bump_rev = true;
		  	}
		} else {
			$bump_rev = false;
		}
		// update/insert the data to the db
		$sql_data_array = array(
		  'sku_id'      => $sku_id,
		  'wo_title'    => $wo_title,
		  'description' => $description,
		  'allocate'    => $allocate,
		  'ref_doc'     => $ref_doc,
		  'ref_spec'    => $ref_spec,
		  'revision'    => $revision,
		);
		if ($id) {
			if (!db_perform(TABLE_WO_MAIN, $sql_data_array, 'update', 'id = ' . $id)) throw new \core\classes\userException("wasn't able to update $id in to table");
		} else {
		    if (!db_perform(TABLE_WO_MAIN, $sql_data_array, 'insert')) throw new \core\classes\userException("wasn't able to insert in to table");
			$id = db_insert_id();
			if ($bump_rev) {
		  	  	$result = $db->Execute("update " . TABLE_WO_MAIN . " set inactive = '1' where id = " . $_POST['id']);
			}
		}
		// update the task list
		if (isset($_POST['id'])) { // delete the previous
		    $db->Execute("delete from " . TABLE_WO_STEPS . " where ref_id = " . $id);
		}
	    while (list($key, $val) = each($step_list)) {
		    $sql_data_array = array(
		      'ref_id'      => $id,
		      'step'        => $val['step'],
		      'task_id'     => $val['task_id'],
		    );
			if (!db_perform(TABLE_WO_STEPS, $sql_data_array, 'insert')) throw new \core\classes\userException("wasn't able to insert in to table");
		}
		$db->transCommit();
		// finish
		gen_add_audit_log(($id  ? sprintf(WO_AUDIT_LOG_BUILDER, TEXT_UPDATE) : sprintf(WO_AUDIT_LOG_BUILDER, TEXT_ADD)) . $task_id);
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS,(isset($_POST['id']) ? TEXT_UPDATED : TEXT_ADDED), TEXT_WORK_ORDER_RECORD , $wo_title),'success');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	} catch(Exception $e) {
		$db->transRollback();
	  	$messageStack->add($e->getMessage(), 'error');
	  	$_REQUEST['action'] = 'edit';
	}
	break;
  case 'copy':
	\core\classes\user::validate_security($security_level, 2);
  	$id    = db_prepare_input($_GET['cID']);
	$title = db_prepare_input($_GET['title']);
	// check for duplicate skus
	$result = $db->Execute("select id from " . TABLE_WO_MAIN . " where wo_title = '" . $title . "'");
	if ($result->Recordcount() > 0) {	// error and reload
		$messageStack->add(WO_BUILDER_ERROR_DUP_TITLE, 'error');
		break;
	}
	$result = $db->Execute("select * from " . TABLE_WO_MAIN . " where id = " . $id);
	$output_array = array();
	foreach ($result->fields as $key => $value) {
		switch ($key) {
			case 'id':	// Remove from write list fields
			case 'revision':
			case 'revision_date':
			case 'last_usage':
				break;
			case 'wo_title': // set the new title
				$output_array[$key] = $title;
				break;
			default:
				$output_array[$key] = $value;
		}
	}
	db_perform(TABLE_WO_MAIN, $output_array, 'insert');
	$new_id = db_insert_id();
	// now copy the steps
	$result = $db->Execute("select step, task_id from " . TABLE_WO_STEPS . " where ref_id = " . $id);
	while (!$result->EOF) {
		$output_array = array(
			'ref_id'  => $new_id,
			'step'    => $result->fields['step'],
			'task_id' => $result->fields['task_id'],
		);
		db_perform(TABLE_WO_STEPS, $output_array, 'insert');
		$result->MoveNext();
	}
	// now continue with newly copied work order by editing it
	gen_add_audit_log(sprintf(WO_AUDIT_LOG_BUILDER, TEXT_COPY), $title);
	$_POST['rowSeq'] = $new_id;	// set item pointer to new record
	$_REQUEST['action'] = 'edit'; // fall through to edit case
  case 'edit':
    $id = db_prepare_input($_POST['rowSeq']);
	if (!$id) throw new \core\classes\userException(sprintf(ERROR_EMPTY_VARIABLE, 'rowSeq'));
	$result = $db->Execute("select id, wo_title, sku_id, description, allocate, ref_doc, ref_spec, revision, last_usage
		from " . TABLE_WO_MAIN . " where id = " . $id);
	foreach ($result->fields as $key => $value) $$key = $value;
	$result = $db->Execute("select id, max(revision) as revision from " . TABLE_WO_MAIN . " where wo_title = '" . $wo_title . "' group by wo_title");
	$highest_rev = $result->fields['revision'];
	// set some filters
	if ($revision < $highest_rev) {
	  	$messageStack->add(WO_CANNOT_SAVE, 'caution');
	  	$hide_save = true;
	}
	if ($revision > 0) $lock_title = true;
	if (!$hide_save && $last_usage <> '0000-00-00') {
	  	$lock_title = true;
	  	$messageStack->add(WO_ROLL_REVISION, 'caution');
	}
	// pull the sku
	$result = $db->Execute("select sku, image_with_path from " . TABLE_INVENTORY . " where id = " . $sku_id);
	$sku             = $result->fields['sku'];
	$image_with_path = $result->fields['image_with_path'];
	// load the steps
	$result = $db->Execute("select id, ref_id, step, task_id
	  from " . TABLE_WO_STEPS . " where ref_id = " . $id . " order by step");
	$step_list = array();
	while (!$result->EOF) {
	  	$task = $db->Execute("select task_name, description from " . TABLE_WO_TASK . " where id = " . $result->fields['task_id'] . " limit 1");
	  	$step_list[] = array(
	  	  'step'      => $result->fields['step'],
	  	  'task_id'   => $result->fields['task_id'],
	  	  'task_name' => $task->fields['task_name'],
	  	  'desc'      => $task->fields['description'],
	  	);
	  	$result->MoveNext();
	}
	break;
  case 'delete':
		\core\classes\user::validate_security($security_level, 4);
    	$id = db_prepare_input($_GET['id']);
		if (!$id) throw new \core\classes\userException(sprintf(ERROR_EMPTY_VARIABLE, 'id'));
		// error check
		$result = $db->Execute("select wo_title, last_usage from " . TABLE_WO_MAIN . " where id = " . $id);
		if ($result->fields['last_usage'] <> '0000-00-00') throw new \core\classes\userException(WO_ERROR_CANNOT_DELETE_BUILDER);
		// finish
		$db->Execute("delete from " . TABLE_WO_MAIN  . " where id = " . $id);
		$db->Execute("delete from " . TABLE_WO_STEPS . " where ref_id = " . $id);
		gen_add_audit_log(sprintf(WO_AUDIT_LOG_BUILDER, TEXT_DELETE) . $result->fields['wo_title']);
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_DELETED, TEXT_WORK_ORDER_RECORD , $result->fields['wo_title']),'success');
    	$_REQUEST['action'] = '';
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

$include_header   = true;
$include_footer   = true;

switch ($_REQUEST['action']) {
  case 'new':
  case 'edit':
    define('PAGE_TITLE', BOX_WORK_ORDERS_BUILDER);
    $include_template = 'template_detail.php';
    break;
  default:
    // build the list header
	$heading_array = array(
	  'm.wo_title'      => TEXT_WO_TITLE,
	  'i.sku'           => TEXT_SKU,
	  'm.description'   => TEXT_DESCRIPTION,
	  'm.revision'      => TEXT_REVISION,
	  'm.revision_date' => TEXT_REVISION_DATE,
	);
	$result      = html_heading_bar($heading_array);
	$list_header = $result['html_code'];
	$disp_order  = $result['disp_order'];
	// build the list for the page selected
    if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
      $search_fields = array('m.wo_title', 'i.sku', 'm.description');
	  // hook for inserting new search fields to the query criteria.
	  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  	  $criteria[] = '(' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
	}
	if (!$f0) $criteria[] = "m.inactive = '0'"; // inactive flag
	// build search filter string
	$search = (sizeof($criteria) > 0) ? (' where ' . implode(' and ', $criteria)) : '';
	$field_list = array('m.id', 'm.inactive', 'm.wo_title', 'i.sku', 'm.sku_id', 'm.description', 'm.revision',
	  'm.revision_date');
	// hook to add new fields to the query return results
	if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
    $query_raw = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list) . "
	  from " . TABLE_WO_MAIN . " m inner join " . TABLE_INVENTORY . " i on m.sku_id = i.id" . $search . " order by $disp_order, m.revision DESC";
    $query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    $query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
    history_save('wo_build');

	// build highest rev level list, reset results
	$rev_list = array();
	while (!$query_result->EOF) {
	  $rev_list[$query_result->fields['wo_title']] = max($query_result->fields['revision'], $rev_list[$query_result->fields['wo_title']]);
	  $query_result->MoveNext();
	}
	$query_result->Move(0);
	$query_result->MoveNext();
	define('PAGE_TITLE', BOX_WORK_ORDERS_BUILDER);
    $include_template = 'template_main.php';
	break;
}

?>