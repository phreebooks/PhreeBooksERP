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
//  Path: /modules/phreedom/pages/roles/pre_process.php
//
$security_level = validate_user(SECURITY_ID_ROLES);
/**************   include page specific files    *********************/
gen_pull_language($module, 'admin');
//gen_pull_language('contacts');
//require_once(DIR_FS_WORKING . 'functions/phreedom.php');
//require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
/**************  page specific initialization  *************************/
$error  = false;
history_filter('roles');
/***************   hook for custom actions   ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/roles/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  case 'fill_all': 
	validate_security($security_level, 2);
  	$admin_id = db_prepare_input($_POST['rowSeq']);
	$fill_all = db_prepare_input($_POST['fill_all']);
	$prefs = array(
	  'def_store_id'    => db_prepare_input($_POST['def_store_id']),
	  'def_cash_acct'   => db_prepare_input($_POST['def_cash_acct']),
	  'def_ar_acct'     => db_prepare_input($_POST['def_ar_acct']),
	  'def_ap_acct'     => db_prepare_input($_POST['def_ap_acct']),
	  'restrict_store'  => isset($_POST['restrict_store'])  ? '1' : '0',
	  'restrict_period' => isset($_POST['restrict_period']) ? '1' : '0',
	);
	// not the most elegent but look for a colon in the second character position
	$post_keys = array_keys($_POST);
	$admin_security = '';
    foreach ($post_keys as $key) {
	  if (strpos($key, 'sID_') === 0) { // it's a security setting post
		if ($admin_security) $admin_security .= ',';
		$admin_security .= substr($key, 4) . ':' . (($fill_all == '-1') ? substr($_POST[$key], 0, 1) : $fill_all);
	  }
	}
	$sql_data_array = array(
	  'admin_name'     => db_prepare_input($_POST['admin_name']),
	  'is_role'        => '1',
	  'inactive'       => isset($_POST['inactive']) ? '1' : '0',
	  'admin_prefs'    => serialize($prefs),
	  'admin_security' => $admin_security,
	);
	if (!$admin_id) { // check for duplicate user name
	  $result = $db->Execute("select admin_id from " . TABLE_USERS . " 
	    where admin_name = '" . db_prepare_input($_POST['admin_name']) . "'");
	  if ($result->RecordCount() > 0) {
		$error = $messageStack->add(ENTRY_DUP_USER_NEW_ERROR, 'error');
	  }
	}
	if (!$error) {
	  if ($admin_id) {
		db_perform(TABLE_USERS, $sql_data_array, 'update', 'admin_id = ' . (int)$admin_id);
		gen_add_audit_log(sprintf(GEN_LOG_USER, TEXT_UPDATE), db_prepare_input($_POST['admin_name']));
	  } else {
		db_perform(TABLE_USERS, $sql_data_array);
		$admin_id = db_insert_id();
		gen_add_audit_log(sprintf(GEN_LOG_USER, TEXT_ADD), db_prepare_input($_POST['admin_name']));
	  }
	  if ($admin_id == $_SESSION['admin_id']) $_SESSION['admin_security'] = gen_parse_permissions($admin_security); // update if user is current user
	} elseif ($error) {
	  $_REQUEST['action'] = 'edit';
	}
	$uInfo = new objectInfo($_POST);
	$uInfo->admin_security = $admin_security;
	break;

  case 'copy':
	validate_security($security_level, 2);
  	$admin_id = db_prepare_input($_GET['cID']);
	$new_name = db_prepare_input($_GET['name']);
	// check for duplicate user names
	$result = $db->Execute("select admin_name from " . TABLE_USERS . " where admin_name = '" . $new_name . "'");
	if ($result->Recordcount() > 0) {	// error and reload
	  $messageStack->add(GEN_ERROR_DUPLICATE_ID, 'error');
	  break;
	}
	$result   = $db->Execute("select * from " . TABLE_USERS . " where admin_id = " . $admin_id);
	$old_name = $result->fields['admin_name'];
	// clean up the fields (especially the system fields, retain the custom fields)
	$output_array = array();
	foreach ($result->fields as $key => $value) {
	  switch ($key) {
		case 'admin_id':	// Remove from write list fields
		case 'display_name':
		case 'admin_email':
		case 'admin_pass':
		case 'account_id':
		  break;
		case 'admin_name': // set the new user name
		  $output_array[$key] = $new_name;
		  break;
		default:
		  $output_array[$key] = $value;
	  }
	}
	db_perform(TABLE_USERS, $output_array, 'insert');
	$new_id = db_insert_id();
	$messageStack->add(GEN_MSG_COPY_SUCCESS, 'success');
	// now continue with newly copied item by editing it
	gen_add_audit_log(sprintf(GEN_LOG_USER, TEXT_COPY), $old_name . ' => ' . $new_name);
	$_POST['rowSeq'] = $new_id;	// set item pointer to new record
	$_REQUEST['action'] = 'edit'; // fall through to edit case

  case 'edit':
	if (isset($_POST['rowSeq'])) $admin_id = db_prepare_input($_POST['rowSeq']);
	$result = $db->Execute("select * from " . TABLE_USERS . " where admin_id = " . (int)$admin_id);
	$temp = unserialize($result->fields['admin_prefs']);
	unset($result->fields['admin_prefs']);
	$uInfo = new objectInfo($result->fields);
	foreach ($temp as $key => $value) $uInfo->$key = $value;
	break;

  case 'delete':
	validate_security($security_level, 4);
  	$admin_id = (int)db_prepare_input($_POST['rowSeq']);
	// fetch the name for the audit log
	$result = $db->Execute("select admin_name from " . TABLE_USERS . " where admin_id = " . $admin_id);
	$db->Execute("delete from " . TABLE_USERS . " where admin_id = " . $admin_id);
	gen_add_audit_log(sprintf(GEN_LOG_USER, TEXT_DELETE), $result->fields['admin_name']);
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
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
$fill_all_values = array(
  array('id' => '-1', 'text' => GEN_HEADING_PLEASE_SELECT),
  array('id' => '0',  'text' => TEXT_NONE),
  array('id' => '1',  'text' => TEXT_READ_ONLY),
  array('id' => '2',  'text' => TEXT_ADD),
  array('id' => '3',  'text' => TEXT_EDIT),
  array('id' => '4',  'text' => TEXT_FULL),
);

$include_header   = true;
$include_footer   = true;

switch ($_REQUEST['action']) {
  case 'new':
  case 'edit':
  case 'fill_all':
    $include_template = 'template_detail.php';
	$role_name = isset($uInfo->admin_name) ? (' - ' . $uInfo->admin_name) : '';
    define('PAGE_TITLE', HEADING_TITLE_ROLE_DETAIL . $role_name);
	break;
  default:
	// build the list header
	$heading_array = array(
	  'admin_name'   => GEN_USERNAME,
	  'inactive'     => TEXT_INACTIVE,
	);
	$result      = html_heading_bar($heading_array);
	$list_header = $result['html_code'];
	$disp_order  = $result['disp_order'];
	// build the list for the page selected
	if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
	  $search_fields = array('admin_name', 'admin_email', 'display_name');
	  // hook for inserting new search fields to the query criteria.
	  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
	  $search = ' and (' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
	} else {
	  $search = '';
	}
	$field_list = array('admin_id', 'inactive', 'display_name', 'admin_name', 'admin_email');
	// hook to add new fields to the query return results
	if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
	$query_raw    = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list) . " from " . TABLE_USERS . " where 
	  is_role = '1'" . $search . " order by $disp_order";
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    // the splitPageResults should be run directly after the query that contains SQL_CALC_FOUND_ROWS
    $query_split  = new splitPageResults($_REQUEST['list'], '');
    if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
    	$_REQUEST['list'] = $query_split->current_page_number;
    	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    	$query_split  = new splitPageResults($_REQUEST['list'], '');
    }
    history_save('roles');
    
	$include_template = 'template_main.php';
	define('PAGE_TITLE', BOX_HEADING_ROLES);
}

?>