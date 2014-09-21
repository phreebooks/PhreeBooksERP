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
//  Path: /modules/phreeform/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
require(DIR_FS_WORKING . 'defaults.php');
require(DIR_FS_WORKING . 'functions/phreeform.php');

/**************   page specific initialization  *************************/
$processed   = false;
history_filter();
$group  = isset($_GET['group'])   ? $_GET['group']                     : false;
$rID    = isset($_POST['rowSeq']) ? db_prepare_input($_POST['rowSeq']) : db_prepare_input($_GET['docID']);
$list   = isset($_REQUEST['list'])? $_REQUEST['list']                  : $_POST['list'];
$tab    = $_GET['tab'];
$groups = build_groups();
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'copy':
  case 'rename':
    $doc_title = db_prepare_input($_POST['newName']);
    $report    = get_report_details($rID);
	$report->title = $doc_title;
	if ($_REQUEST['action'] == 'rename') {
	  $sql_array = array(
	    'doc_title'   => $doc_title,
	    'last_update' => date('Y-m-d'),
	  );
	  db_perform(TABLE_PHREEFORM, $sql_array, 'update', 'id = ' . $rID);
	  $message = sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_SAVED, TEXT_REPORT , $doc_title);
	} else {
	  $result = $admin->DataBase->query("select * from " . TABLE_PHREEFORM . " where id = '" . $rID . "'");
	  $sql_array = array(
	    'parent_id'   => $result->fields['parent_id'],
	    'doc_title'   => $doc_title,
	    'doc_group'   => $report->groupname,
	    'doc_ext'     => $report->reporttype,
	    'security'    => $report->security,
	    'create_date' => date('Y-m-d'),
	  );
	  db_perform(TABLE_PHREEFORM, $sql_array, 'insert');
	  $rID     = db_insert_id();
	  $message = sprintf(TEXT_SUCCESSFULLY_ARGS, TEXT_COPIED, TEXT_REPORT , $doc_title);
	}
	$filename = PF_DIR_MY_REPORTS . 'pf_' . $rID;
	$output   = object_to_xml($report);
	if (!$handle = @fopen($filename, 'w')) {
	  $admin->DataBase->query("delete from " . TABLE_PHREEFORM . " where id = " . $rID);
	  throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, $filename));
	}
	if (!@fwrite($handle, $output)) throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, 	$filename));
	if (!@fclose($handle)) 			throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, $filename));
	$messageStack->add($message, 'success');
	break;
  case 'export':
    $result = $admin->DataBase->query("select doc_title from " . TABLE_PHREEFORM . " where id = '" . $rID . "'");
	$filename        = PF_DIR_MY_REPORTS . 'pf_' . $rID;
	$source_filename = str_replace(' ', '',  $result->fields['doc_title']);
	$source_filename = str_replace('/', '_', $source_filename) . '.xml';
	$backup_filename = str_replace(' ', '',  $result->fields['doc_title']);
	$backup_filename = str_replace('/', '_', $backup_filename) . '.zip';
	$dest_dir        = DIR_FS_MY_FILES . 'backups/';
	if (!class_exists('ZipArchive')) throw new \core\classes\userException(PHREEFORM_NO_ZIP);
	$zip = new \ZipArchive;
	$res = $zip->open($dest_dir . $backup_filename, \ZipArchive::CREATE);
	if ($res === false) 									throw new \core\classes\userException(TEXT_ERROR_ZIP_FILE . $dest_dir);
	if (($temp = @file_get_contents($filename)) === false)	throw new \core\classes\userException(sprintf(ERROR_READ_FILE, $filename));
	$res = $zip->addFromString($source_filename, $temp);
	$zip->close();
	// download file and exit script
	if (($contents = @file_get_contents($dest_dir . $backup_filename)) === false)  throw new \core\classes\userException(sprintf(ERROR_READ_FILE, $backup_filename));
	unlink($dest_dir . $backup_filename); // delete zip file in the temp dir
	header("Content-type: application/zip");
	header("Content-disposition: attachment; filename=" . $backup_filename . "; size=" . strlen($contents));
	header('Pragma: cache');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header('Connection: close');
	header('Expires: ' . date('r', time() + 60 * 60));
	header('Last-Modified: ' . date('r', time()));
	print $contents;
	exit();
    break;
  case 'go_first':    $_REQUEST['list'] = 1;       						$_REQUEST['action'] = 'search'; break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); 	$_REQUEST['action'] = 'search'; break;
  case 'go_next':     $_REQUEST['list']++;         						$_REQUEST['action'] = 'search'; break;
  case 'go_last':     $_REQUEST['list'] = 99999;   						$_REQUEST['action'] = 'search'; break;
  case 'search':
  case 'search_reset':
  case 'go_page':                                  $_REQUEST['action'] = 'search'; break;
  default:
}

/*****************   prepare to display templates  *************************/
$result = $admin->DataBase->query('select id, parent_id, doc_type, doc_title, doc_group, security from ' . TABLE_PHREEFORM . '
	order by doc_title, id, parent_id');
$toc_array    = array();
$toc_array[-1][] = array('id' => 0, 'doc_type' => '0', 'doc_title' => TEXT_HOME); // home dir
while (!$result->EOF) {
  if (security_check($result->fields['security'])) {
    $toc_array[$result->fields['parent_id']][] = array(
	  'id'        => $result->fields['id'],
	  'doc_type'  => $result->fields['doc_type'],
	  'doc_title' => $result->fields['doc_title'],
	  'show'      => $result->fields['doc_group'] == $tab ? true : false,
    );
  }
  $result->MoveNext();
}

$toggle_list = false;
if ($group) {
  $result = $admin->DataBase->query("select id from " . TABLE_PHREEFORM . " where doc_group = '" . $group . "'");
  if ($result->RecordCount() > 0) $toggle_list = buildToggleList($result->fields['id']);
}

switch ($_REQUEST['action']) { // figure which detail page to load
  case 'search':
  case 'view':
  	$result      = html_heading_bar(array(),array(' ', TEXT_REPORT_TITLE, TEXT_ACTION));
	$list_header = $result['html_code'];
	// build the list for the page selected
	if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
	  $search_fields = array('doc_title');
	  $search = ' where ' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\'';
	} else {
	  $search = '';
	}
	$field_list = array('id', 'doc_title', 'doc_ext');
	$query_raw = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list)  . " from " . TABLE_PHREEFORM . $search;
	$query_result = $admin->DataBase->query($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    $query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
    history_save();
    $div_template = DIR_FS_WORKING . 'pages/main/' . ($id ? 'tab_report.php' : 'tab_folder.php');
	break;
  case 'home':
  default:
	$div_template = DIR_FS_WORKING . 'pages/main/tab_home.php';
}

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_REPORTS);

?>