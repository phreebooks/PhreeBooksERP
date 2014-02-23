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
//  Path: /modules/phreeform/doc_ctl/pages/main/tab_folder.php
//

$fieldset_content = NULL;
// build the tab toolbar
$docbar = new \core\classes\toolbar;
$docbar->icon_list['cancel']['show']   = false;
$docbar->icon_list['open']['show']     = false;
$docbar->icon_list['save']['show']     = false;
$docbar->icon_list['print']['show']    = false;
if ($id) {
  $docbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . PHREEFORM_DELETE_DIRECTORY . '\')) dirAction(\'delete\')"';
} else {
  $docbar->icon_list['delete']['show'] = false;
}
if ($id) $docbar->icon_list['go_up'] = array(
  'show'   => true, 
  'icon'   => 'actions/go-up.png',
  'params' => 'onclick="dirAction(\'go_up\')"',
  'text'   => 'Up', 
  'order'  => 2,
);
if ($_REQUEST['action'] <> 'search') $fieldset_content .= $docbar->build_toolbar() . chr(10);
// build the table contents
$doc_cnt = 0;
$fieldset_content .= '<table class="ui-widget" style="border-collapse:collapse;width:100%"><tbody class="ui-widget-content">' . chr(10);
$fieldset_content .= '  <tr>' . $list_header . '</tr>' . chr(10);
$odd = true;
while (!$query_result->EOF) {
  if (security_check($query_result->fields['security'])) {
	$folder = ($query_result->fields['doc_type'] == '0') ? true : false;
	$fieldset_content .= '  <tr class="' . ($odd?'odd':'even') . '" style="cursor:pointer">' . chr(10);
	$fieldset_content .= '	<td onclick="fetch_doc(' . $query_result->fields['id'] . ')">' . html_icon(get_mime_image($query_result->fields['doc_ext'], $folder), '', 'small') . '</td>' . chr(10);
	$fieldset_content .= '	<td onclick="fetch_doc(' . $query_result->fields['id'] . ')">' . $query_result->fields['doc_title'] . '</td>' . chr(10);
	$fieldset_content .= '	<td align="right"> ' . '&nbsp;' . '</td>' . chr(10); // action space
	$fieldset_content .= '  </tr>' . chr(10);
	$doc_cnt++;
	$odd = !$odd;
  }
  $query_result->MoveNext();
}
if ($doc_cnt > 0) {
  $fieldset_content .= '</tbody></table>' . chr(10);
  $fieldset_content .= '<div style="height:19px">' . $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_FILES) . chr(10);
  $fieldset_content .= '<div style="float:right">' . $query_split->display_links() . '</div>' . chr(10);
  $fieldset_content .= '</div>' . chr(10);
} else {
  $fieldset_content .= '  <tr>' . chr(10);
  $fieldset_content .= '	<td colspan="3">' . TEXT_EMPTY_FOLDER . '</td>' . chr(10);
  $fieldset_content .= '  </tr>' . chr(10);
  $fieldset_content .= '</tbody></table>' . chr(10);
}
?>
