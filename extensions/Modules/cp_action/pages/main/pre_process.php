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
//  Path: /modules/cp_action/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_CAPA_MGT);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
/**************   page specific initialization  *************************/
$cInfo         = new \core\classes\objectInfo();
$creation_date = $_POST['creation_date'] ? gen_db_date($_POST['creation_date']) : date('Y-m-d');
history_filter();
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	\core\classes\user::validate_security($security_level, 2);
  	$id = db_prepare_input($_POST['rowSeq']);
	// check for errors, process

	// write the data
	$sql_data_array = array(
	    'capa_type'           => db_prepare_input($_POST['capa_type']),
	    'requested_by'        => db_prepare_input($_POST['requested_by']),
	    'capa_status'         => db_prepare_input($_POST['capa_status']),
	    'entered_by'          => db_prepare_input($_POST['entered_by']),
		'creation_date'       => $creation_date,
	    'analyze_due'         => $_POST['analyze_due']  ? gen_db_date($_POST['analyze_due'])  : '',
	    'analyze_date'        => $_POST['analyze_date'] ? gen_db_date($_POST['analyze_date']) : '',
	    'repair_due'          => $_POST['repair_due']   ? gen_db_date($_POST['repair_due'])   : '',
	    'repair_date'         => $_POST['repair_date']  ? gen_db_date($_POST['repair_date'])  : '',
	    'audit_due'           => $_POST['audit_due']    ? gen_db_date($_POST['audit_due'])    : '',
	    'audit_date'          => $_POST['audit_date']   ? gen_db_date($_POST['audit_date'])   : '',
	    'closed_due'          => $_POST['closed_due']   ? gen_db_date($_POST['closed_due'])   : '',
	    'closed_date'         => $_POST['closed_date']  ? gen_db_date($_POST['closed_date'])  : '',
	    'action_date'         => $_POST['action_date']  ? gen_db_date($_POST['action_date'])  : '',
	    'notes_issue'         => db_prepare_input($_POST['notes_issue']),
	    'customer_name'       => db_prepare_input($_POST['customer_name']),
	    'customer_id'         => db_prepare_input($_POST['customer_id']),
	    'customer_telephone'  => db_prepare_input($_POST['customer_telephone']),
	    'customer_invoice'    => db_prepare_input($_POST['customer_invoice']),
	    'customer_email'      => db_prepare_input($_POST['customer_email']),
	    'notes_customer'      => db_prepare_input($_POST['notes_customer']),
	    'analyze_due_id'      => db_prepare_input($_POST['analyze_due_id']),
	    'analyze_close_id'    => db_prepare_input($_POST['analyze_close_id']),
	    'repair_due_id'       => db_prepare_input($_POST['repair_due_id']),
	    'repair_close_id'     => db_prepare_input($_POST['repair_close_id']),
	    'audit_due_id'        => db_prepare_input($_POST['audit_due_id']),
	    'audit_close_id'      => db_prepare_input($_POST['audit_close_id']),
	    'closed_due_id'       => db_prepare_input($_POST['closed_due_id']),
	    'closed_close_id'     => db_prepare_input($_POST['closed_close_id']),
	    'notes_investigation' => db_prepare_input($_POST['notes_investigation']),
	    'agreed_by'           => db_prepare_input($_POST['agreed_by']),
	    'notes_action'        => db_prepare_input($_POST['notes_action']),
	    'capa_closed'         => db_prepare_input($_POST['capa_closed']),
	    'next_capa_num'       => db_prepare_input($_POST['next_capa_num']),
	    'notes_audit'         => db_prepare_input($_POST['notes_audit']),
	  );

	  if ($id) {
	    	if (!db_perform(TABLE_CAPA, $sql_data_array, 'update', 'id = ' . $id)) throw new \Exception(CAPA_MESSAGE_ERROR);
		 	gen_add_audit_log(CAPA_LOG_USER_UPDATE . $_POST['capa_num']);
		  	$capa_num = $_POST['capa_num'];
	  } else {
	    	// fetch the CAPA number
			$result   = $db->Execute("select next_capa_num from " . TABLE_CURRENT_STATUS);
			$capa_num = $result->fields['next_capa_num'];
			$sql_data_array['capa_num'] = $capa_num;
	    	if (!db_perform(TABLE_CAPA, $sql_data_array, 'insert')) throw new \Exception(CAPA_MESSAGE_ERROR);
		  	$id = db_insert_id();
		  	$next_num = string_increment($capa_num);
		  	$db->Execute("update " . TABLE_CURRENT_STATUS . " set next_capa_num = '" . $next_num . "'");
		  	gen_add_audit_log(CAPA_LOG_USER_ADD . $capa_num);
	  }
	  $messageStack->add(($_POST['rowSeq'] ? CAPA_MESSAGE_SUCCESS_UPDATE : CAPA_MESSAGE_SUCCESS_ADD) . $capa_num, 'success');
	break;

  case 'edit':
    $id = db_prepare_input($_POST['rowSeq']);
	$result = $db->Execute("select * from " . TABLE_CAPA . " where id = " . $id);
	$cInfo = new \core\classes\objectInfo($result->fields);
	break;

  case 'delete':
	\core\classes\user::validate_security($security_level, 4);
  	$id     = db_prepare_input($_GET['cID']);
	$result = $db->Execute("select capa_num from " . TABLE_CAPA . " where id = " . $id);
	if ($result->RecordCount() == 0) throw new \Exception(CAPA_ERROR_CANNOT_DELETE);
	$db->Execute("delete from " . TABLE_CAPA . " where id = " . $id);
	gen_add_audit_log(CAPA_MESSAGE_DELETE, $result->fields['capa_num']);
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('cID', 'action')), 'SSL'));
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
$cal_date0 = array(
  'name'      => 'createDate',
  'form'      => 'capa',
  'fieldname' => 'creation_date',
  'imagename' => 'btn_date_0',
  'default'   => isset($cInfo->creation_date) ? gen_locale_date($cInfo->creation_date) : '',
  'params'    => array('align' => 'left'),
);
$cal_date1 = array(
  'name'      => 'analyzeDue',
  'form'      => 'capa',
  'fieldname' => 'analyze_due',
  'imagename' => 'btn_date_1',
  'default'   => isset($cInfo->analyze_due) ? gen_locale_date($cInfo->analyze_due) : '',
  'params'    => array('align' => 'left'),
);
$cal_date2 = array(
  'name'      => 'repairDue',
  'form'      => 'capa',
  'fieldname' => 'repair_due',
  'imagename' => 'btn_date_2',
  'default'   => isset($cInfo->repair_due) ? gen_locale_date($cInfo->repair_due) : '',
  'params'    => array('align' => 'left'),
);
$cal_date3 = array(
  'name'      => 'auditDue',
  'form'      => 'capa',
  'fieldname' => 'audit_due',
  'imagename' => 'btn_date_3',
  'default'   => isset($cInfo->audit_due) ? gen_locale_date($cInfo->audit_due) : '',
  'params'    => array('align' => 'left'),
);
$cal_date4 = array(
  'name'      => 'closedDue',
  'form'      => 'capa',
  'fieldname' => 'closed_due',
  'imagename' => 'btn_date_4',
  'default'   => isset($cInfo->closed_due) ? gen_locale_date($cInfo->closed_due) : '',
  'params'    => array('align' => 'left'),
);
$cal_date5 = array(
  'name'      => 'analyzeDate',
  'form'      => 'capa',
  'fieldname' => 'analyze_date',
  'imagename' => 'btn_date_5',
  'default'   => isset($cInfo->analyze_date) ? gen_locale_date($cInfo->analyze_date) : '',
  'params'    => array('align' => 'left'),
);
$cal_date6 = array(
  'name'      => 'repairDate',
  'form'      => 'capa',
  'fieldname' => 'repair_date',
  'imagename' => 'btn_date_6',
  'default'   => isset($cInfo->repair_date) ? gen_locale_date($cInfo->repair_date) : '',
  'params'    => array('align' => 'left'),
);
$cal_date7 = array(
  'name'      => 'auditDate',
  'form'      => 'capa',
  'fieldname' => 'audit_date',
  'imagename' => 'btn_date_7',
  'default'   => isset($cInfo->audit_date) ? gen_locale_date($cInfo->audit_date) : '',
  'params'    => array('align' => 'left'),
);
$cal_date8 = array(
  'name'      => 'closedDate',
  'form'      => 'capa',
  'fieldname' => 'closed_date',
  'imagename' => 'btn_date_8',
  'default'   => isset($cInfo->closed_date) ? gen_locale_date($cInfo->closed_date) : '',
  'params'    => array('align' => 'left'),
);
$cal_date9 = array(
  'name'      => 'actionDate',
  'form'      => 'capa',
  'fieldname' => 'action_date',
  'imagename' => 'btn_date_9',
  'default'   => isset($cInfo->action_date) ? gen_locale_date($cInfo->action_date) : '',
  'params'    => array('align' => 'left'),
);

$include_header   = true;
$include_footer   = true;

switch ($_REQUEST['action']) {
  case 'new':
    // set some defaults
    $cInfo->creation_date = date('Y-m-d');
	$cInfo->capa_status   = '1';
    define('PAGE_TITLE', MENU_HEADING_NEW_CAPA);
    $include_template = 'template_detail.php';
    break;
  case 'edit':
    define('PAGE_TITLE', BOX_CAPA_MAINTAIN);
    $include_template = 'template_detail.php';
    break;
  default:
    // build the list header
	$heading_array = array(
	  'capa_num'      => TEXT_CAPA_ID,
	  'creation_date' => TEXT_CREATION_DATE,
	  'notes_issue'   => TEXT_DESCRIPTION,
	  'capa_status'   => TEXT_STATUS,
	  'closed_date'   => TEXT_CLOSED_DATE,
	);
	$result      = html_heading_bar($heading_array);
	$list_header = $result['html_code'];
	$disp_order  = $result['disp_order'];

	// build the list for the page selected
    if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
      $search_fields = array('capa_num', 'purchase_invoice_id', 'notes_issue', 'caller_name', 'caller_telephone1');
	  // hook for inserting new search fields to the query criteria.
	  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
	  $search = ' where ' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\'';
    } else { $search = ''; }

	$field_list = array('id', 'capa_num', 'capa_status', 'notes_issue', 'creation_date', 'closed_date');

	// hook to add new fields to the query return results
	if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);

    $query_raw    = "select SQL_CALC_FOUND_ROWS ".implode(', ', $field_list)." from ".TABLE_CAPA." $search order by $disp_order, capa_num";
    $query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    $query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
	if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
	   	$_REQUEST['list'] = $query_split->current_page_number;
		$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
		$query_split      = new \core\classes\splitPageResults($_REQUEST['list'], '');
	}
	history_save();
    define('PAGE_TITLE', BOX_CAPA_MAINTAIN);
    $include_template = 'template_main.php';
	break;
}

?>