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
//  Path: /modules/doc_ctl/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_DOC_CONTROL);
/**************  include page specific files    *********************/
require(DIR_FS_WORKING . 'defaults.php');
require(DIR_FS_WORKING . 'functions/doc_ctl.php');
/**************   page specific initialization  *************************/
$processed   = false;
if ($_REQUEST['search_text'] == TEXT_SEARCH) $_REQUEST['search_text'] = '';
if (!$_REQUEST['action'] && $_REQUEST['search_text'] <> '') $_REQUEST['action'] = 'search'; // if enter key pressed and search not blank
$tab    = isset($_GET['tab'])     ? $_GET['tab']                       : 'home';
$doc_id = isset($_POST['rowSeq']) ? db_prepare_input($_POST['rowSeq']) : db_prepare_input($_GET['docID']);
if(!isset($_REQUEST['list'])) $_REQUEST['list'] = 1;
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save_doc':
	$id          = db_prepare_input($_POST['id']);
	$title       = db_prepare_input($_POST['title']);
	$description = db_prepare_input($_POST['description']);
	$users  = 'u:-1';
	$groups = 'g:-1';
	if (isset($_POST['user_all'])) $users = 'u:0';
	  elseif (isset($_POST['users']) && $_POST['users'][0] <> '') $users = 'u:' . implode(':', $_POST['users']);
	if (isset($_POST['group_all'])) $groups = 'g:0';
	  elseif (isset($_POST['users']) && $_POST['groups'][0] <> '') $groups = 'g:' . implode(':', $_POST['groups']);
	$security    = $users . ';' . $groups;
	// error checking
	if (!$id) throw new \core\classes\userException(sprintf(ERROR_EMPTY_VARIABLE, "id"));
	// retrieve some information about the document
	$result   = $db->Execute("select revision from " . TABLE_DC_DOCUMENT . " where id = " . $id);
	$revision = $result->fields['revision'];
	// save the file
	$new_file = false;
	if ($_FILES['docfile']['tmp_name']) { // there was an uploaded file
	  $new_file = true;
	  dc_validate_upload('docfile');
	  $file_name = str_pad($id, 8, '0', STR_PAD_LEFT) . '_' . $revision .'.dc';
	  // if an old file exists, bump the revision
	  if (file_exists(DOC_CTL_DIR_MY_DOCS . $file_name)) {
	    $revision++; // bump the revision
		$new_file = false;
	    $file_name = str_pad($id, 8, '0', STR_PAD_LEFT) . '_' . $revision .'.dc';
	  }
	  if (!copy($_FILES['docfile']['tmp_name'], DOC_CTL_DIR_MY_DOCS . $file_name)) throw new \core\classes\userException(sprintf(DOC_CTL_FILE_WRITE_ERROR, DOC_CTL_DIR_MY_DOCS));
	}
	// insert/update db
	$sql_array = array(
	  'title'       => $title,
	  'description' => $description,
	  'security'    => $security,
	);
	if ($_FILES['docfile']['tmp_name']) {
	  $sql_array['checkout_id'] = 0;
	  $sql_array['revision']    = $revision;
	  $sql_array['doc_owner']   = $_SESSION['admin_id'];
	  $sql_array['file_name']   = $_FILES['docfile']['name'];
	  $sql_array['doc_ext']     = substr($_FILES['docfile']['name'], strrpos($_FILES['docfile']['name'], '.') + 1);
	  $sql_array['doc_size']    = $_FILES['docfile']['size'];
	  $sql_array['last_update'] = date('Y-m-d');
	}
	if ($new_file) $sql_array['create_date'] = date('Y-m-d');
	db_perform(TABLE_DC_DOCUMENT, $sql_array, 'update', 'id = ' . $id);
	break;
  case 'del_checkout':
	$db->Execute("update " . TABLE_DC_DOCUMENT . " set checkout_id = 0 where id = " . $doc_id);
    break;
  case 'check_out':
	$db->Execute("update " . TABLE_DC_DOCUMENT . " set checkout_id = " . $_SESSION['admin_id'] . " where id = " . $doc_id);
	// now perform the download
  case 'download':
	$doc_details = $db->Execute("select file_name, revision from " . TABLE_DC_DOCUMENT . " where id = '" . $doc_id . "'");
	$filename = str_pad($doc_id, 8, '0', STR_PAD_LEFT) . '_' . $doc_details->fields['revision'] . '.dc';
	if (($contents  = @file_get_contents(DOC_CTL_DIR_MY_DOCS . $filename)) === false) throw new \core\classes\userException(sprintf(ERROR_READ_FILE, 		$filename));
	header('Content-type: text/plain');
	header('Content-Length: ' . strlen($contents));
	header('Content-Disposition: attachment; filename=' . urlencode($doc_details->fields['file_name']));
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $contents;
	exit();

  case 'go_first':    $_REQUEST['list'] = 1;      					   $_REQUEST['action'] = 'search'; break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); $_REQUEST['action'] = 'search'; break;
  case 'go_next':     $_REQUEST['list']++;         					   $_REQUEST['action'] = 'search'; break;
  case 'go_last':     $_REQUEST['list'] = 99999;  					   $_REQUEST['action'] = 'search'; break;
  case 'search':
  case 'search_reset':
  case 'go_page':     $_REQUEST['action'] = 'search'; break;
  default:
}

/*****************   prepare to display templates  *************************/
switch ($_REQUEST['action']) { // figure which detail page to load
  case 'search':
  case 'view':
	$result      = html_heading_bar(array(), array(' ', TEXT_CONTENTS_OF, TEXT_ACTION));
	$list_header = $result['html_code'];
	// build the list for the page selected
	if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
	  $search_fields = array('title', 'type');
	  $search = " where " . implode(" like %{$_REQUEST['search_text']}%' or ", $search_fields) . " like '%{$_REQUEST['search_text']}%";
	} else {
	  $search = '';
	}
	$field_list = array('id', 'title', 'type');
	$query_raw = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list)  . " from " . TABLE_DC_DOCUMENT . $search;
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
	$query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
	$div_template = DIR_FS_WORKING . 'pages/main/' . ($id ? 'tab_document.php' : 'tab_folder.php');
	break;
  case 'home':
  default:
	$div_template = DIR_FS_WORKING . 'pages/main/tab_home.php';
}

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php'; // include display template (required)
define('PAGE_TITLE', TEXT_DOCUMENT_CONTROL);

?>