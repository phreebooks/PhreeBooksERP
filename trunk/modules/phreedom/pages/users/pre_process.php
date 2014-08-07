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
//  Path: /modules/phreedom/pages/users/pre_process.php
//
if($_SESSION['admin_id'] == 1) $security_level = 4;
else $security_level = \core\classes\user::validate(SECURITY_ID_USERS);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('contacts');
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
/**************   page specific initialization  *************************/
history_filter('users');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/users/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  case 'fill_all':
  case 'fill_role':
  	try{
		\core\classes\user::validate_security($security_level, 2);
		$admin_id  = db_prepare_input($_POST['rowSeq']);
		$fill_all  = db_prepare_input($_POST['fill_all']);
		$fill_role = db_prepare_input($_POST['fill_role']);
		if ($security_level < 3 && $admin_id) throw new \core\classes\userException(TEXT_YOUR_PERMISSIONS_DO_NOT_ALLOW_THE_USERS_ROLE_OR_SECURITY_TO_BE_CHANGED);
		if ($_REQUEST['action'] == 'fill_role' ) {
		  	$result = $db->Execute("select admin_prefs, admin_security from " . TABLE_USERS . " where admin_id = " . $fill_role);
		  	$admin_security = $result->fields['admin_security'];
		  	if ($admin_id == 1) { // make sure first user cannot lock themselves out
		  		$temp = explode(",", $admin_security);
		  		$settings = array();
		  		foreach ($temp as $key => $value) $settings[] = "$key:$value";
		  		$settings[SECURITY_ID_USERS] = 4;
		  		$admin_security = implode(',', $settings);
		  	}
		  	$temp = unserialize($result->fields['admin_prefs']);  // fake the input to look like role
		  	foreach ($temp as $key => $value) $_POST[$key] = $value;
		} else {
		  	$admin_security = '';
		  	$post_keys = array_keys($_POST);
		  	$temp = array();
	      	foreach ($post_keys as $key) if (strpos($key, 'sID_') === 0) { // it's a security setting post
				$secID = substr($key, 4);
	      		$temp[$secID] =  $fill_all == '-1' ? substr($_POST[$key], 0, 1) : $fill_all;
		  	}
		  	if ($admin_id == 1) $temp[SECURITY_ID_USERS] = 4; // make sure first admin_id (installer) will not lock self out of system
		  	$settings = array();
		  	foreach ($temp as $key => $value) $settings[] = "$key:$value";
		  	$admin_security = implode(',', $settings);
		}
		$prefs = array(
		  'role'            => $fill_role,
		  'def_store_id'    => db_prepare_input($_POST['def_store_id']),
		  'def_cash_acct'   => db_prepare_input($_POST['def_cash_acct']),
		  'def_ar_acct'     => db_prepare_input($_POST['def_ar_acct']),
		  'def_ap_acct'     => db_prepare_input($_POST['def_ap_acct']),
		  'restrict_store'  => isset($_POST['restrict_store'])  ? $_POST['restrict_store']  : '0',
		  'restrict_period' => isset($_POST['restrict_period']) ? $_POST['restrict_period'] : '0',
		);
		$admin_prefs = serialize($prefs);
		// not the most elegent but look for a colon in the second character position
		$sql_data_array = array(
		  'admin_name'     => db_prepare_input($_POST['admin_name']),
		  'is_role'        => '0',
		  'inactive'       => isset($_POST['inactive']) ? '1' : '0',
		  'display_name'   => db_prepare_input($_POST['display_name']),
		  'admin_email'    => db_prepare_input($_POST['admin_email']),
		  'account_id'     => $_POST['account_id'] ? db_prepare_input($_POST['account_id']) : 0,
		  'admin_prefs'    => $admin_prefs,
		  'admin_security' => $admin_security,
		);
		if ($_POST['password_new']) {
		  	$password_new  = db_prepare_input($_POST['password_new']);
		  	$password_conf = db_prepare_input($_POST['password_conf']);
		  	if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) throw new \core\classes\userException(sprintf(TEXT_YOUR_NEW_PASSWORD_MUST_CONTAIN_A_MINIMUM_OF_CHARACTERS_ARGS, ENTRY_PASSWORD_MIN_LENGTH));
		  	if ($password_new != $password_conf) throw new \core\classes\userException(TEXT_THE_PASSWORD_CONFIRMATION_MUST_MATCH_YOUR_NEW_PASSWORD);
		  	$sql_data_array['admin_pass'] = \core\classes\encryption::password($password_new);
		}
		if (!$admin_id) { // check for duplicate user name
		  	$result = $db->Execute("select admin_id from " . TABLE_USERS . " where admin_name = '" . db_prepare_input($_POST['admin_name']) . "'");
		  	if ($result->RecordCount() > 0) throw new \core\classes\userException(ENTRY_DUP_USER_NEW_ERROR);
		}
		if ($admin_id) {
			db_perform(TABLE_USERS, $sql_data_array, 'update', 'admin_id = ' . (int)$admin_id);
			gen_add_audit_log(sprintf(GEN_LOG_USER, TEXT_UPDATE), db_prepare_input($_POST['admin_name']));
	  	} else {
			db_perform(TABLE_USERS, $sql_data_array);
			$admin_id = db_insert_id();
			gen_add_audit_log(sprintf(GEN_LOG_USER, TEXT_ADD), db_prepare_input($_POST['admin_name']));
	  	}
	  	if ($admin_id == $_SESSION['admin_id']) $_SESSION['admin_security'] = \core\classes\user::parse_permissions($admin_security); // update if user is current user
  	}catch(Exception $e){
  		$_REQUEST['action'] = 'edit';
  		$messageStack->add($e->getMessage());
  	}
	$uInfo = new \core\classes\objectInfo($_POST);
	$uInfo->admin_security = $admin_security;
	break;

  case 'copy':
  	try{
		\core\classes\user::validate_security($security_level, 3);
		$admin_id = db_prepare_input($_GET['cID']);
		$new_name = db_prepare_input($_GET['name']);
		// check for duplicate user names
		$result = $db->Execute("select admin_name from " . TABLE_USERS . " where admin_name = '" . $new_name . "'");
		if ($result->Recordcount() > 0) throw new \core\classes\userException(TEXT_THE_USERNAME_IS_ALREADY_IN_USE_PLEASE_SELECT_A_DIFFERENT_NAME);

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
  	}catch(Exception $e){
  		$messageStack->add($e->getMessage());
  	}
  case 'edit':
	if (isset($_POST['rowSeq'])) $admin_id = db_prepare_input($_POST['rowSeq']);
	$result = $db->Execute("select * from " . TABLE_USERS . " where admin_id = " . (int)$admin_id);
	$temp = unserialize($result->fields['admin_prefs']);
	unset($result->fields['admin_prefs']);
	$uInfo = new \core\classes\objectInfo($result->fields);
	if (is_array($temp)) foreach ($temp as $key => $value) $uInfo->$key = $value;
	break;

  case 'delete':
	\core\classes\user::validate_security($security_level, 4);
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
$include_header   = true;
$include_footer   = true;

switch ($_REQUEST['action']) {
  case 'new':
  case 'edit':
  case 'fill_all':
  case 'fill_role':
	$fill_all_values = array(
	  array('id' => '-1', 'text' => TEXT_PLEASE_SELECT),
	  array('id' => '0',  'text' => TEXT_NONE),
	  array('id' => '1',  'text' => TEXT_READ_ONLY),
	  array('id' => '2',  'text' => TEXT_ADD),
	  array('id' => '3',  'text' => TEXT_EDIT),
	  array('id' => '4',  'text' => TEXT_FULL),
	);
	$fill_all_roles = array(array('id' => '0', 'text' => TEXT_NONE));
	$result = $db->Execute("select admin_id, admin_name from " . TABLE_USERS . " where is_role = '1'");
	while (!$result->EOF) {
	  $fill_all_roles[] = array('id' => $result->fields['admin_id'], 'text' => $result->fields['admin_name']);
	  $result->MoveNext();
	}
    $include_template = 'template_detail.php';
	$role_name = isset($uInfo->admin_name) ? (' - ' . $uInfo->admin_name) : '';
    define('PAGE_TITLE', TEXT_USER_INFORMATION . $role_name);
	break;
  default:
	// build the list header
	$heading_array = array(
	  'admin_name'   => TEXT_USERNAME,
	  'inactive'     => TEXT_INACTIVE,
	  'display_name' => TEXT_DISPLAY_NAME,
	  'admin_email'  => TEXT_EMAIL,
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
	$query_raw    = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list) . " from " . TABLE_USERS . " where is_role = '0'" . $search . " order by $disp_order";
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    $query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
    history_save('users');

	$include_template = 'template_main.php';
	define('PAGE_TITLE', TEXT_USERS);
}

?>