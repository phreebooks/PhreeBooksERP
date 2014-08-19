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
//  Path: /modules/doc_ctl/ajax/load_doc_details.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_DOC_CONTROL);
/**************  include page specific files    *********************/
require(DIR_FS_MODULES . 'doc_ctl/defaults.php');
require(DIR_FS_MODULES . 'doc_ctl/functions/doc_ctl.php');
/**************   page specific initialization  *************************/
$fieldset_content = 'NULL';
$id = (int)$_GET['id'];
if (!isset($_GET['id'])) die;

$doc_details = $db->Execute("select * from " . TABLE_DC_DOCUMENT . " where id = '" . $id . "'");
if ($id == -1) { // home page
	include (DIR_FS_MODULES . 'doc_ctl/pages/main/tab_home.php');
} elseif ($id == 0 || $doc_details->fields['type'] == 'drive' || $doc_details->fields['type'] == 'folder') { // folder
	$dir_path     = TEXT_CONTENTS_OF. ': ' . '/' . build_dir_path($id);
	$result       = html_heading_bar(array(), array(' ', $dir_path, TEXT_ACTION));
	$list_header  = $result['html_code'];
	$field_list   = array('id', 'file_name', 'title', 'type', 'doc_ext', 'description', 'security');
	$query_raw    = "select " . implode(', ', $field_list)  . " from " . TABLE_DC_DOCUMENT . " where parent_id = '" . $id . "' order by position";
	$query_split  = new \core\classes\splitPageResults($_GET['list'], MAX_DISPLAY_SEARCH_RESULTS, $query_raw, $query_numrows);
	$query_result = $db->Execute($query_raw);
	include (DIR_FS_MODULES . 'doc_ctl/pages/main/tab_folder.php');
} else { // load document details
	$dir_path     = TEXT_PATH . ': /' . build_dir_path($id);
	$dir_path     = substr($dir_path, 0, strrpos($dir_path, '/'));
	$bookmarked   = test_bookmark();
	$locked       = $doc_details->fields['lock_id'] > 0 ? true : false;
	$checkout_id  = $doc_details->fields['checkout_id'];
	$checked_out  = get_owner_name($checkout_id);
	$doc_history  = get_doc_history($id, $doc_details->fields['revision']);
	include (DIR_FS_MODULES . 'doc_ctl/pages/main/tab_document.php');
}
// put the output together
$html = "<div>";
$html .= $fieldset_content;
$html .= "</div>";
$xml .= "\t" . xmlEntry("htmlContents", $html);
// error check

echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>