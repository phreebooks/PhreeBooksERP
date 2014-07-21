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
//  Path: /modules/phreeform/ajax/load_doc_details.php
//
/**************   Check user security   *****************************/
$xml = NULL;
$security_level = validate_ajax_user(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');
/**************   page specific initialization  *************************/
if(!isset($_REQUEST['list'])) $_REQUEST['list'] = 1;
$fieldset_content = 'NULL';
$id = (int)$_GET['id'];
if (!isset($_GET['id'])) die;
$doc_details = $db->Execute("select * from " . TABLE_PHREEFORM . " where id = '" . $id . "'");
if ($id == 0 || $doc_details->fields['doc_type'] == '0') { // folder
  $dir_path     = TEXT_PATH . ': /' . build_dir_path($id);
  $result       = html_heading_bar(array(), array(' ', $dir_path, TEXT_ACTION));
  $list_header  = $result['html_code'];
  $field_list   = array('id', 'doc_type', 'doc_title', 'security');
  $query_raw    = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list)  . " from " . TABLE_PHREEFORM . " where parent_id = '" . $id . "'";
  $query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
  // the splitPageResults should be run directly after the query that contains SQL_CALC_FOUND_ROWS
  $query_split  = new splitPageResults($_REQUEST['list'], '');  
  include (DIR_FS_MODULES . 'phreeform/pages/main/tab_folder.php');
} else { // load document details
  include (DIR_FS_MODULES . 'phreeform/pages/main/tab_report.php');
}
$html  = "<div>";
$html .= $fieldset_content;
$html .= "</div>";

$xml  .= "\t" . xmlEntry("htmlContents", $html);
echo createXmlHeader() . $xml . createXmlFooter();
die;
?>