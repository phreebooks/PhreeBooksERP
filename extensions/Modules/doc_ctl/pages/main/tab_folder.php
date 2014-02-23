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
//  Path: /modules/doc_ctl/pages/main/tab_folder.php
//
$fieldset_content = NULL;
// build the tab toolbar (if not the home dir)
if ($doc_details->fields['type'] <> 'drive') {
  $dirbar = new \core\classes\toolbar;
  $dirbar->icon_list['cancel']['show'] = false;
  $dirbar->icon_list['open']['show']   = false;
  $dirbar->icon_list['save']['show']   = false;
  $dirbar->icon_list['print']['show']  = false;
  if ($security_level > 3) {
    $dirbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . DOC_CTL_DELETE_DIRECTORY . '\')) docAction(\'delete_dir\')"';
  } else {
    $dirbar->icon_list['delete']['show'] = false;
  }
  $fieldset_content .= $dirbar->build_toolbar() . chr(10);
}
// build the table contents
$fieldset_content .= html_hidden_field('id', $id) . chr(10);
$fieldset_content .= '<table width="100%" cellspacing="0" cellpadding="1">' . chr(10);
$fieldset_content .= '  <tr>' . $list_header . '</tr>' . chr(10);
$odd = true;
$found_one = false;
if ($query_result->RecordCount() > 0) {
  while (!$query_result->EOF) {
  	if (!$query_result->fields['security']) $query_result->fields['security'] = 'u:0;g:0'; // allow all
  	if (security_check($query_result->fields['security'])) {
  	  $fieldset_content .= '  <tr class="' . ($odd?'odd':'even') . '" style="cursor:pointer">' . chr(10);
	  $fieldset_content .= '	<td onclick="fetch_doc(' . $query_result->fields['id'] . ')">' . html_icon(get_mime_image($query_result->fields['doc_ext'], $query_result->fields['type']), '', 'small') . '</td>' . chr(10);
	  $fieldset_content .= '	<td onclick="fetch_doc(' . $query_result->fields['id'] . ')">' . $query_result->fields['title'] . '</td>' . chr(10);
	  $fieldset_content .= '	<td align="right"> ' . chr(10);
	  $fieldset_content .= '	</td>' . chr(10);
	  $fieldset_content .= '  </tr>' . chr(10);
	  $odd = !$odd;
	  $found_one = true;
   	}
	$query_result->MoveNext();
  }
}
if ($found_one) {
  $fieldset_content .= '</table>' . chr(10);
  $fieldset_content .= '<div style="float:right">' . $query_split->display_links() . '</div>' . chr(10);
  $fieldset_content .= '<div>' . $query_split->display_count(TEXT_DISPLAY_NUMBER . DOC_CTL_ITEMS) . '</div>' . chr(10);
} else {
  $fieldset_content .= '  <tr>' . chr(10);
  $fieldset_content .= '	<td colspan="3">' . DOC_CTL_EMPTY_FOLDER . '</td>' . chr(10);
  $fieldset_content .= '  </tr>' . chr(10);
  $fieldset_content .= '</table>' . chr(10);
}
?>
