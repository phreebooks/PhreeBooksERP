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
//  Path: /modules/doc_ctl/ajax/doc_operation.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_DOC_CONTROL);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'doc_ctl/defaults.php');
require_once(DIR_FS_MODULES . 'doc_ctl/functions/doc_ctl.php');
/**************   page specific initialization  *************************/
$xml       = NULL;
$ajax_text = '';
$id        = (int)$_GET['id'];

if (!isset($_GET['id'])) die;
$doc_details = $admin->DataBase->Execute("select * from " . TABLE_DC_DOCUMENT . " where id = " . $id);
switch ($_REQUEST['action']) {
  case 'bookmark':
	$output = array();
	$bookmarks = explode(":", $doc_details->fields['bookmarks']);
	if (is_array($bookmarks)) foreach ($bookmarks as $value) if ($value) $output[$value] = true;
	$output[$_SESSION['admin_id']] = true;
	$admin->DataBase->Execute("update " . TABLE_DC_DOCUMENT . " set bookmarks = ':" . implode(":",$output) . ":' where  id = " . $id);
    break;
  case 'del_bookmark':
	$output = array();
	$bookmarks = explode(":", $doc_details->fields['bookmarks']);
	if (is_array($bookmarks)) foreach ($bookmarks as $value) if ($value) $output[$value] = true;
	if (isset($output[$_SESSION['admin_id']])) unset($output[$_SESSION['admin_id']]);
	$result = sizeof($output) == 0 ? '' : (':' . implode(":",$output) . ':');
	$admin->DataBase->Execute("update " . TABLE_DC_DOCUMENT . " set bookmarks = '" . $result. "' where  id = " . $id);
    break;
  case 'lock':
	$admin->DataBase->Execute("update " . TABLE_DC_DOCUMENT . " set lock_id = " . $_SESSION['admin_id'] . " where  id = " . $id);
    break;
  case 'del_lock':
	$admin->DataBase->Execute("update " . TABLE_DC_DOCUMENT . " set lock_id = 0 where  id = " . $id);
    break;
  case 'delete':
	// jstree initialization
	$db_config = array(
	  "servername" => DB_SERVER_HOST,
	  "username"   => DB_SERVER_USERNAME,
	  "password"   => DB_SERVER_PASSWORD,
	  "database"   => DB_DATABASE,
	);
	if (extension_loaded("mysqli")) { require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class._database_i.php"); }
	  else { require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class._database.php"); }
	// Tree class
	require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class.tree.php");
	$jstree = new json_tree();

	$file_name = str_pad($id, 8, '0', STR_PAD_LEFT) . '_*.dc'; // delete all revisions
	if (sizeof(glob(DOC_CTL_DIR_MY_DOCS . $file_name) > 0)) foreach (glob(DOC_CTL_DIR_MY_DOCS . $file_name) as $filename) unlink($filename);
	// deleted from database tree
	$_POST['operation'] = $_REQUEST['operation'] = 'remove_node';
	$_POST['id']        = $_REQUEST['id']        = $id;
	$jstree->{'remove_node'}($_REQUEST);
	$id = $doc_details->fields['parent_id']; // set the id to the parent to display refreshed page
	$xml .= "\t" . xmlEntry("action", 'reload_tree');
	break;
  case 'delete_dir':
	// check for directory not empty
	$result = $admin->DataBase->Execute("select id from " . TABLE_DC_DOCUMENT . " where parent_id = " . $id);
	if ($result->RecordCount() > 0) {
	  $ajax_text = TEXT_THE_DIRECTORY_IS_NOT_EMPTY_IT_CANNOT_BE_DELETED;
	  break;
	}
	// jstree initialization
	$db_config = array(
	  "servername" => DB_SERVER_HOST,
	  "username"   => DB_SERVER_USERNAME,
	  "password"   => DB_SERVER_PASSWORD,
	  "database"   => DB_DATABASE,
	);
	if (extension_loaded("mysqli")) { require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class._database_i.php"); }
	  else { require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class._database.php"); }
	// Tree class
	require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class.tree.php");
	$jstree = new json_tree();
	// deleted from database tree
	$_POST['operation'] = $_REQUEST['operation'] = 'remove_node';
	$_POST['id']        = $_REQUEST['id']        = $id;
	$jstree->{'remove_node'}($_REQUEST);
	$id = $doc_details->fields['parent_id']; // set the id to the parent to display refreshed page
	$ajax_text = '';
	$xml .= "\t" . xmlEntry("action", 'reload_tree');
	break;
  default: die;
}
// put the output together
$xml .= "\t" . xmlEntry("docID", $id);
$xml .= "\t" . xmlEntry("msg",   $ajax_text);
echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>