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
//  Path: /modules/work_orders/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_WORK_ORDERS);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'inventory/defaults.php');
require_once(DIR_FS_WORKING . 'functions/work_orders.php');
/**************   page specific initialization  *************************/
$processed   = false;
$hide_save   = false;
$image       = false;
$step_list   = array();
$store_id    = 0;
$post_date   = ($_POST['post_date'])  ? gen_db_date($_POST['post_date'])  : date('Y-m-d');
$close_date  = ($_POST['close_date']) ? $_POST['close_date'] : '';
history_filter('wo_main');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'new':
	break;
  case 'save':
  case 'print':
  	try{
		\core\classes\user::validate_security($security_level, 2);
	  	$id       = db_prepare_input($_POST['id']);
		$sku      = db_prepare_input($_POST['sku']);
		$sku_id   = db_prepare_input($_POST['sku_id']);
		$qty      = db_prepare_input($_POST['qty']);
		$wo_id    = db_prepare_input($_POST['wo_id']);
		$priority = db_prepare_input($_POST['priority']);
		$wo_title = db_prepare_input($_POST['wo_title']);
		$notes    = db_prepare_input($_POST['notes']);
		$closed   = isset($_POST['closed']) ? '1' : '0';
		// error check
		if (!$sku_id || !$sku || !$qty) throw new \Exception(WO_SKU_ID_REQUIRED);
		if ($closed && $close_date == '') $close_date = date('Y-m-d');
		if (!$closed) $close_date = '';
		// update/insert the data to the db
		if ($id) { // if id is present only allow update of select fields
			$result   = $db->Execute("select qty, wo_id from " . TABLE_WO_JOURNAL_MAIN . " where id = " . $id);
			$temp     = $db->Execute("select allocate from " . TABLE_WO_MAIN . " where id = '" . $result->fields['wo_id'] . "'");
			$allocate = $temp->fields['allocate'];
			// only if qty changed, update allocation
			if ($allocate && $result->fields['qty'] <> $qty) allocation_adjustment($sku_id, $qty, $result->fields['qty']);
			// if closing, remove allocation
			if ($allocate && $closed) allocation_adjustment($sku_id, 0, $result->fields['qty']);
		    $sql_data_array = array(
		      'qty'        => $qty,
			  'priority'   => $priority,
			  'notes'      => $notes,
			  'closed'     => $closed,
			  'close_date' => $close_date,
			);
		    if (!db_perform(TABLE_WO_JOURNAL_MAIN, $sql_data_array, 'update', 'id = ' . $id)) throw new \Exception("wasn't able to update table id $id");
		} else {
			$result = $db->Execute("select next_wo_num from " . TABLE_CURRENT_STATUS);
			$wo_num = $result->fields['next_wo_num'];
		    $sql_data_array = array(
		      'wo_num'     => $wo_num,
		      'sku_id'     => $sku_id,
		      'qty'        => $qty,
		      'wo_id'      => $wo_id,
		      'priority'   => $priority,
		      'wo_title'   => $wo_title,
		      'post_date'  => $post_date,
		      'notes'      => $notes,
		      'closed'     => $closed,
			  'close_date' => $close_date,
		    );
		    if (!db_perform(TABLE_WO_JOURNAL_MAIN, $sql_data_array, 'insert'))throw new \Exception("wasn't able to insert");
			$id = db_insert_id();
			$result   = $db->Execute("update " . TABLE_CURRENT_STATUS . " set next_wo_num = '" . string_increment($wo_num) . "'");
			$temp     = $db->Execute("select allocate from " . TABLE_WO_MAIN . " where id = '" . $wo_id . "'");
			$allocate = $temp->fields['allocate'];
			if ($allocate) allocation_adjustment($sku_id, $qty); // add allocation
		}
		// load and create the intial task list (only if new)
		if (!$_POST['id']) {
		  	$result = $db->Execute("select step, task_id  
		  	  from " . TABLE_WO_STEPS . " where ref_id = " . $wo_id . " order by step");
		  	$step_list = array();
		  	while (!$result->EOF) {
		    	$task = $db->Execute("select task_name, mfg, qa, data_entry from " . TABLE_WO_TASK . " where id = " . $result->fields['task_id'] . " limit 1");
		    	$sql_data_array = array(
		    	  'ref_id'     => $id,
		    	  'step'       => $result->fields['step'],
		    	  'task_id'    => $result->fields['task_id'],
		    	  'task_name'  => $task->fields['task_name'],
		    	  'mfg'        => $task->fields['mfg'],
		    	  'qa'         => $task->fields['qa'],
		    	  'data_entry' => $task->fields['data_entry'],
				  'complete'   => '0', // set initial complete to zero
		    	);
		    	if (!db_perform(TABLE_WO_JOURNAL_ITEM, $sql_data_array, 'insert')) throw new \Exception("wasn't able to insert");
		    	$result->MoveNext();
		  	}
		  	// update the last usage for the template
		  	$db->Execute("update " . TABLE_WO_MAIN . " set last_usage = '" . date('Y-m-d') . "' where id = " . $wo_id);
		}
		// finish
		gen_add_audit_log($id  ? sprintf(WO_AUDIT_LOG_MAIN, TEXT_UPDATE) . $wo_id : sprintf(WO_AUDIT_LOG_MAIN, TEXT_ADD) . $wo_id);
		$messageStack->add($id ? WO_MESSAGE_SUCCESS_MAIN_UPDATE : WO_MESSAGE_SUCCESS_MAIN_ADD, 'success');
		if ($_REQUEST['action'] == 'save') gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	} catch(Exception $e) {
	  $messageStack->add($e->getMessage(), 'error');
	  $_REQUEST['action'] = 'edit';
	}
	break;

  case 'save_step':
  	try{
  		$db->transStart();
		$id         = db_prepare_input($_POST['id']); // the id of the work order
	    $step       = db_prepare_input($_POST['rowSeq']);
		$user_mfg   = db_prepare_input($_POST['user_mfg']);
		$user_qa    = db_prepare_input($_POST['user_qa']);
		$pw_mfg     = db_prepare_input($_POST['pw_mfg']);
		$pw_qa      = db_prepare_input($_POST['pw_qa']);
		$data_value = db_prepare_input($_POST['data_value']);
		$notes      = db_prepare_input($_POST['notes']);
		$sql_data_array = array(); // start the update field list
		// error check
		if (!$id || !$step) throw new \Exception("variable 'id' or 'rowSeq' isn't defined");
		if (isset($_POST['user_mfg']) && $user_mfg > 0) { // the mfg signoff is required and present
			$sql = "select admin_pass from " . TABLE_USERS . " where admin_id = '" . $user_mfg . "'";
	      	$result = $db->Execute($sql);
	      	\core\classes\encryption::validate_password($pw_mfg, $result->fields['admin_pass']);
		    $sql_data_array['mfg_id']   = $user_mfg;
		    $sql_data_array['mfg_date'] = date('Y-m-d H:i:s');
		}
		if (isset($_POST['user_qa']) && $user_qa> 0) { // the qa signoff is required and present
		  	$sql = "select admin_pass from " . TABLE_USERS . " where admin_id = '" . $user_qa . "'";
	      	$result = $db->Execute($sql);
	      	\core\classes\encryption::validate_password($pw_qa, $result->fields['admin_pass']);
		    $sql_data_array['qa_id']   = $user_qa;
		    $sql_data_array['qa_date'] = date('Y-m-d H:i:s');
		}
		if (isset($_POST['data_value'])) {
		  	if ($data_value == '') throw new \Exception(WO_DATA_VALUE_BLANK);
	      	$sql_data_array['data_value'] = $data_value;
		}
		// set the step to completed in the item db
		if (sizeof($sql_data_array) > 0) if (!db_perform(TABLE_WO_JOURNAL_ITEM, $sql_data_array, 'update', "ref_id = $id and step = $step")) throw new \Exception(WO_DB_UPDATE_ERROR);
		if ($notes) if (!db_perform(TABLE_WO_JOURNAL_MAIN, array('notes'=>$notes), 'update', "id = $id")) throw new \Exception(WO_DB_UPDATE_ERROR);
		// check to see if the step is complete
		$result    = $db->Execute("select * from " . TABLE_WO_JOURNAL_ITEM . " where ref_id = $id and step = $step");
		$task_id   = $result->fields['task_id'];
		$set_close = true;
		if ($result->fields['mfg']        && $result->fields['mfg_id']     == '0') $set_close = false;
		if ($result->fields['qa']         && $result->fields['qa_id']      == '0') $set_close = false;
		if ($result->fields['data_entry'] && $result->fields['data_value'] ==  '') $set_close = false;
		if ($set_close) {
			// check to see if an erp entry is needed
			$result = $db->Execute("select erp_entry from " . TABLE_WO_TASK . " where id = '$task_id'");
			if ($result->fields['erp_entry']) { // process the request, build main record
				$result = $db->Execute("select wo_num, sku_id, qty from " . TABLE_WO_JOURNAL_MAIN . " where id = " . $id);
				$wo_num = $result->fields['wo_num'];
				$qty    = $result->fields['qty'];
				$sku_id = $result->fields['sku_id'];
				$result = $db->Execute("select sku, description_short, account_inventory_wage from " . TABLE_INVENTORY . " where id = '" . $sku_id . "'");
				$sku    = $result->fields['sku'];
				$desc   = $result->fields['description_short'];
				$acct   = $result->fields['account_inventory_wage'];
				gen_pull_language('inventory');
				gen_pull_language('phreebooks');
				require_once(DIR_FS_MODULES . 'inventory/functions/inventory.php');
				require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
				define('JOURNAL_ID', 14); // Inventory Assemblies Journal
				define('GL_TYPE', '');
				$glEntry = new \core\classes\journal();
				$glEntry->id                  = '';
				$glEntry->admin_id            = $_SESSION['admin_id'];
				$glEntry->journal_id          = JOURNAL_ID;
				$glEntry->post_date           = date('Y-m-d');
				$glEntry->period              = gen_calculate_period($glEntry->post_date);
				if (!$glEntry->period) break;
				$glEntry->purchase_invoice_id = $wo_num;
				$glEntry->store_id            = isset($_POST['store_id']) ? $_POST['store_id'] : 0;
				$glEntry->closed              = '1'; // closes by default
				$glEntry->journal_main_array  = $glEntry->build_journal_main_array();
				// build journal entry based on adding or subtracting from inventory, debit/credit will be calculated by COGS
				$glEntry->journal_rows[] = array(
				  'gl_type'          => 'asy',
				  'sku'              => $sku,
				  'qty'              => $qty,
				  'description'      => $desc,
				  'gl_account'       => $acct,
			//	  'serialize_number' => $serial,
				);
				$glEntry->Post('insert');
				gen_add_audit_log(INV_LOG_ASSY . TEXT_SAVE, $sku, $qty);
				$messageStack->add(INV_POST_ASSEMBLY_SUCCESS . $sku, 'success');
			}
			$db->Execute("update " . TABLE_WO_JOURNAL_ITEM . " set complete = '1', admin_id = " . $_SESSION['admin_id'] . "  
			  where ref_id = $id and step = $step");
			// check to see if the work order is complete
			$result = $db->Execute("select max(step) as max_step from " . TABLE_WO_JOURNAL_ITEM . " where ref_id = $id");
			if ($step == $result->fields['max_step']) {
				$db->Execute("update " . TABLE_WO_JOURNAL_MAIN . " 
				  set closed = '1', close_date = '" . date('Y-m-d H:i:s') . "' where id = " . $id);
			   	// check to un-allocate inventory
				$result   = $db->Execute("select qty, sku_id, wo_id from " . TABLE_WO_JOURNAL_MAIN . " where id = $id");
				$temp     = $db->Execute("select allocate from ".TABLE_WO_MAIN." where id = '".$result->fields['wo_id']."'");
				$allocate = $temp->fields['allocate'];
				if ($allocate) allocation_adjustment($result->fields['sku_id'], 0, $result->fields['qty']);
				gen_add_audit_log(sprintf(WO_AUDIT_LOG_WO_COMPLETE, $id));
				$messageStack->add(sprintf(WO_MESSAGE_SUCCESS_COMPLETE, $id),'success');
				gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
			}
		}
		$db->transCommit();	// post the chart of account values
		gen_add_audit_log(sprintf(WO_AUDIT_LOG_STEP_COMPLETE, $step));
		$messageStack->add(sprintf(WO_MESSAGE_STEP_UPDATE_SUCCESS, $step),'success');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=build&id=' . $id, 'SSL'));
	} catch(Exception $e) {
		$db->transRollback();
	  	$messageStack->add($e->getMessage());
	  	$_REQUEST['action'] = 'build';
	  	$_POST['rowSeq'] = $id; // make it look like an edit
	}	
	if (DEBUG) $messageStack->write_debug();
    // fall through like build to reload
  case 'edit':
  case 'build':
    $id = isset($_POST['rowSeq']) ? $_POST['rowSeq'] : $_GET['id'];
	if (!$id) throw new \Exception("varible rowSeq or id isn't set");
	$result = $db->Execute("select * from " . TABLE_WO_JOURNAL_MAIN . " where id = " . $id);
	foreach ($result->fields as $key => $value) $$key = $value;
	// load the sku
	$result = $db->Execute("select sku, image_with_path from " . TABLE_INVENTORY . " where id = " . $sku_id);
	$sku    = $result->fields['sku'];
	$image  = $result->fields['image_with_path'];
	// load the steps with status
	$result = $db->Execute("select step, task_id, task_name, mfg, mfg_id, qa, qa_id, data_entry, data_value, complete 
	  from " . TABLE_WO_JOURNAL_ITEM . " where ref_id = " . $id . " order by step");
	while (!$result->EOF) {
	  $task = $db->Execute("select description from " . TABLE_WO_TASK . " where id = " . $result->fields['task_id'] . " limit 1");
	  $step_list[] = array(
	    'step'        => $result->fields['step'],
	    'task_name'   => $result->fields['task_name'],
	    'description' => $task->fields['description'],
	    'mfg'         => $result->fields['mfg'],
	    'mfg_id'      => $result->fields['mfg_id'],
	    'qa'          => $result->fields['qa'],
	    'qa_id'       => $result->fields['qa_id'],
	    'data_entry'  => $result->fields['data_entry'],
	    'data_value'  => $result->fields['data_value'],
	    'complete'    => $result->fields['complete'],
	  );
	  $result->MoveNext();
	}
	break;
  case 'delete':
	\core\classes\user::validate_security($security_level, 4);
    $id = db_prepare_input($_GET['id']);
	if (!$id) throw new \Exception("the requird varible 'id' isn't set");
	// check to un-allocate inventory
	$result   = $db->Execute("select qty, sku_id, wo_id from " . TABLE_WO_JOURNAL_MAIN . " where id = " . $id);
	$temp     = $db->Execute("select allocate from " . TABLE_WO_MAIN . " where id = '" . $result->fields['wo_id'] . "'");
	$allocate = $temp->fields['allocate'];
	if ($allocate) allocation_adjustment($result->fields['sku_id'], 0, $result->fields['qty']);
	$db->Execute("delete from " . TABLE_WO_JOURNAL_MAIN  . " where id = " . $id);
	$db->Execute("delete from " . TABLE_WO_JOURNAL_ITEM  . " where ref_id = " . $id);
	gen_add_audit_log(sprintf(WO_AUDIT_LOG_MAIN, TEXT_DELETE) . $result->fields['wo_title']);
	$messageStack->add(WO_MESSAGE_SUCCESS_MAIN_DELETE,'success');
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
$include_header   = true;
$include_footer   = true;
$cal_date = array(
  'name'      => 'dateReference',
  'form'      => 'work_orders',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($post_date),
  'params'    => array('align' => 'left'),
);
switch ($_REQUEST['action']) {
  case 'new':
  case 'edit':
    // build priority drop-down
	$priority_list = array();
	for ($i = 1; $i < 10; $i++) $priority_list[] = array('id' => $i, 'text' => $i);
	define('PAGE_TITLE', ($_REQUEST['action'] == 'edit') ? HEADING_WORK_ORDER_MODULE_EDIT : HEADING_WORK_ORDER_MODULE_NEW);
    $include_template = 'template_new.php';
    break;
  case 'build':
    $user_list = wo_build_users();
    define('PAGE_TITLE', HEADING_WORK_ORDER_MODULE_BUILD);
    $include_template = 'template_build.php';
    break;
  default:
    // build the list header
	$heading_array = array(
	  'm.wo_num'     => TEXT_WO_ID,
	  'm.priority'   => TEXT_PRIORITY,
	  'm.post_date'  => TEXT_POST_DATE,
	  'm.qty'        => TEXT_QUANTITY,
	  'i.sku'        => TEXT_SKU,
	  'm.wo_title'   => TEXT_WO_TITLE,
	  'm.closed'     => TEXT_CLOSED,
	  'm.close_date' => TEXT_CLOSE_DATE,
	);
	$result      = html_heading_bar($heading_array);
  	$list_header = $result['html_code'];
	$disp_order  = $result['disp_order'];
	// build the list for the page selected
    if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
      $search_fields = array('m.id', 'i.sku', 'm.wo_title');
	  // hook for inserting new search fields to the query criteria.
	  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
	  $search = ' where ' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\'';
    } else {
	  $search = '';
	}
	$field_list = array('m.id', 'm.wo_num', 'm.priority', 'm.wo_title', 'i.sku', 'm.qty', 'm.sku_id', 'm.post_date', 'm.closed', 'm.close_date');
	// hook to add new fields to the query return results
	if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
    $query_raw = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list) . " 
	  from " . TABLE_WO_JOURNAL_MAIN . " m inner join " . TABLE_INVENTORY . " i on m.sku_id = i.id" . $search . " order by $disp_order, m.closed, m.id DESC";
    $query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    $query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
    history_save('wo_main');
    
    define('PAGE_TITLE', BOX_WORK_ORDERS_MODULE);
    $include_template = 'template_main.php';
	break;
}

?>