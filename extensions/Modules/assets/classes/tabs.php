<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008-2014 PhreeSoft                               |
// | http://www.PhreeSoft.com                                        |
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
//  Path: /modules/assets/classes/tabs.php
//

class tabs {
  function tabs() {
	$this->help_path   = '';
  }

  function btn_save($id = '') {
  	global $db, $messageStack;
	if ($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] < 2) {
	  $messageStack->add_session(ERROR_NO_PERMISSION,'error');
	  return false;
	}
    $tab_name = db_prepare_input($_POST['tab_name']);
	$sql_data_array = array(
	  'module_id'   => 'assets',
	  'tab_name'    => db_prepare_input($_POST['tab_name']),
	  'description' => db_prepare_input($_POST['description']),
	  'sort_order'  => db_prepare_input($_POST['sort_order']),
	);
    if ($id) {
	  db_perform(TABLE_EXTRA_TABS, $sql_data_array, 'update', "id = " . $id);
      gen_add_audit_log(sprintf(EXTRA_TABS_LOG, TEXT_UPDATE), $tab_name);
	} else {
	  // Test for duplicates.
	  $result = $db->Execute("select id from " . TABLE_EXTRA_TABS . " where module_id='assets' and tab_name='" . $tab_name . "'");
	  if ($result->RecordCount() > 0) {
	  	$messageStack->add(EXTRA_TABS_DELETE_ERROR,'error');
		return false;
	  }
	  $sql_data_array['id'] = db_prepare_input($_POST['rowSeq']);
      db_perform(TABLE_EXTRA_TABS, $sql_data_array);
	  gen_add_audit_log(sprintf(EXTRA_TABS_LOG, TEXT_ADD), $tab_name);
	}
	return true;
  }

  function btn_delete($id = 0) {
  	global $db, $messageStack;
	if ($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] < 4) {
	  $messageStack->add_session(ERROR_NO_PERMISSION,'error');
	  return false;
	}
	$result = $db->Execute("select field_name from " . TABLE_EXTRA_FIELDS . " where id = '" . $id . "'");
	if ($result->RecordCount() > 0) {
	  $messageStack->add(ASSETS_CATEGORY_CANNOT_DELETE . $result->fields['field_name'],'error');
	  return false;
	}
	$result = $db->Execute("select tab_name from " . TABLE_EXTRA_TABS . " where id = '" . $id . "'");
	$db->Execute("delete from " . TABLE_EXTRA_TABS . " where id = '" . $id . "'");
	gen_add_audit_log(sprintf(EXTRA_TABS_LOG, TEXT_DELETE), $result->fields['tab_name']);
	return true;
  }

  function build_main_html() {
  	global $db, $messageStack;
    $content = array();
	$content['thead'] = array(
	  'value' => array(TEXT_TITLE, TEXT_DESCRIPTION, TEXT_SORT_ORDER, TEXT_ACTION),
	  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select id, tab_name, description, sort_order from ".TABLE_EXTRA_TABS." where module_id='assets'");
    $rowCnt = 0;
	while (!$result->EOF) {
	  $actions = '';
	  if ($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\'tabs_edit\', \'' . $result->fields['id'] . '\')"') . chr(10);
	  if ($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . EXTRA_TABS_DELETE_INTRO . '\')) subjectDelete(\'tabs\', ' . $result->fields['id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['tab_name']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'tabs_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => htmlspecialchars($result->fields['description']), 
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'tabs_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $result->fields['sort_order'],
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'tabs_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable('tab_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db;
    $sql = "select id, tab_name, description, sort_order from ".TABLE_EXTRA_TABS." where id = " . $id;
    $result = $db->Execute($sql);
	if ($action == 'new') {
	  $cInfo = '';
	} else {
      $cInfo = new objectInfo($result->fields);
	}
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? TEXT_NEW_TAB : TEXT_EDIT_TAB) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <td colspan="2">' . ($action=='new' ? EXTRA_TABS_INSERT_INTRO : SETUP_CURR_EDIT_INTRO) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_TAB_NAME . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('tab_name', $cInfo->tab_name) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_DESCRIPTION . '</td>' . chr(10);
	$output .= '    <td>' . html_textarea_field('description', 30, 10, $cInfo->description) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_SORT_ORDER . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('sort_order', $cInfo->sort_order) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . chr(10);
    return $output;
  }
}

?>