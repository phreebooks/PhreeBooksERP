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
//  Path: /modules/doc_ctl/pages/main/tab_document.php
//
$fieldset_content = NULL;
// build the tab toolbar
$docbar = new \core\classes\toolbar;
$docbar->add_icon('export', 'onclick="submitSeq(' . $id . ',\'download\', true)"', $order = 10);
$docbar->icon_list['cancel']['show'] = false;
$docbar->icon_list['open']['show']   = false;
$docbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save_doc\')"';
$docbar->icon_list['print']['show']  = false;
$docbar->icon_list['export']['text'] = TEXT_DOWNLOAD_DOCUMENT;
if ($security_level > 3) {
  $docbar->icon_list['delete']['params'] = 'onclick="if (confirm(\'' . DOC_CTL_DELETE_DOCUMENT . '\')) docAction(\'delete\')"';
} else {
  $docbar->icon_list['delete']['show'] = false;
}
if ($bookmarked) {
	$docbar->icon_list['del_bookmark'] = array(
		'show'   => true, 
		'icon'   => 'actions/bookmark-new.png',
		'params' => 'onclick="docAction(\'del_bookmark\')"',
		'text'   => TEXT_REMOVE_BOOKMARK, 
		'order'  => 20,
	);
} else {
	$docbar->icon_list['bookmark'] = array(
		'show'   => true, 
		'icon'   => 'actions/bookmark-new.png',
		'params' => 'onclick="docAction(\'bookmark\')"',
		'text'   => TEXT_BOOKMARK_DOC, 
		'order'  => 20,
	);
}
if ($locked && $_SESSION['admin_id'] == $doc_details->fields['doc_owner']) {
	$docbar->icon_list['del_lock'] = array(
		'show'   => true, 
		'icon'   => 'actions/system-lock-screen.png',
		'params' => 'onclick="docAction(\'del_lock\')"',
		'text'   => TEXT_UNLOCK_DOC, 
		'order'  => 50,
	);
} elseif ($security_level > 1 && !$locked) {
	$docbar->icon_list['lock'] = array(
		'show'   => true, 
		'icon'   => 'actions/system-lock-screen.png',
		'params' => 'onclick="docAction(\'lock\')"',
		'text'   => TEXT_LOCK_DOC, 
		'order'  => 50,
	);
}
if ($security_level > 2 && !$checked_out) {
	$docbar->icon_list['check_out'] = array(
		'show'   => true, 
		'icon'   => 'actions/mail-forward.png',
		'params' => 'onclick="submitSeq(' . $id . ',\'check_out\', true)"',
		'text'   => TEXT_CHECKOUT_DOC, 
		'order'  => 60,
	);
}
if ($security_level > 3 && $checked_out && $checkout_id == $_SESSION['admin_id']) {
	$docbar->icon_list['del_checkout'] = array(
		'show'   => true, 
		'icon'   => 'actions/mail-mark-not-junk.png',
		'params' => 'onclick="submitSeq(' . $id . ',\'del_checkout\')"',
		'text'   => TEXT_CANCEL_CHECKOUT, 
		'order'  => 65,
	);
}
$fieldset_content .= $docbar->build_toolbar() . chr(10);

$fieldset_content .= html_hidden_field('id', $doc_details->fields['id']) . chr(10);
$fieldset_content .= TEXT_DOCUMENT . html_input_field('title', $doc_details->fields['title'], 'size="64"') . chr(10);
// build the table contents
$fieldset_content .= '<table width="100%" cellspacing="0" cellpadding="4">' . chr(10);
$fieldset_content .= '<tr><td colspan="2">' . $dir_path . '</td></tr>' . chr(10);
$fieldset_content .= '<tr><td colspan="2">' . html_textarea_field('description', 75, 3, $doc_details->fields['description'], '', true) . '</td></tr>' . chr(10);
$fieldset_content .= '<tr><td colspan="2">' . TEXT_UPLOAD_FILE . ' ' . html_file_field('docfile') . '</td></tr>' . chr(10);
// column 1
$fieldset_content .= '<tr><td width="50%" valign="top">' . chr(10);
$fieldset_content .= '  <table class="ui-widget" style="border-collapse:collapse;width:100%">';
$fieldset_content .= '  <thead class="ui-widget-header">' . chr(10);
$fieldset_content .= '  <tr>' . chr(10);
$fieldset_content .= '    <th colspan="2">' . TEXT_PROPERTIES . '</th>' . chr(10);
$fieldset_content .= '  </tr>' . chr(10);
$fieldset_content .= '  </thead>' . chr(10);
$fieldset_content .= '  <tbody class="ui-widget-content">' . chr(10);
$fieldset_content .= '  <tr>' . chr(10);
$fieldset_content .= '    <td>' . TEXT_FILENAME . '</td>' . chr(10);
$fieldset_content .= '    <td>' . $doc_details->fields['file_name'] . '</td>' . chr(10);
$fieldset_content .= '  </tr><tr>' . chr(10);
$fieldset_content .= '    <td>' . TEXT_OWNER . '</td>' . chr(10);
$fieldset_content .= '    <td>' . get_owner_name($doc_details->fields['doc_owner']) . '</td>' . chr(10);
$fieldset_content .= '  </tr><tr>' . chr(10);
$fieldset_content .= '    <td>' . TEXT_LOCKED . '</td>' . chr(10);
$fieldset_content .= '    <td>' . ($locked ? TEXT_YES.' - '.get_owner_name($doc_details->fields['lock_id']) : TEXT_NO) . '</td>' . chr(10);
$fieldset_content .= '  </tr><tr>' . chr(10);
$fieldset_content .= '    <td>' . TEXT_CHECKED_OUT . '</td>' . chr(10);
$fieldset_content .= '    <td>' . ($checkout_id > 0 ? TEXT_YES.' - '.get_owner_name($doc_details->fields['checkout_id']) : TEXT_NO)  . '</td>' . chr(10);
$fieldset_content .= '  </tr><tr>' . chr(10);
$fieldset_content .= '    <td>' . TEXT_BOOKMARKED . '</td>' . chr(10);
$fieldset_content .= '    <td>' . (test_bookmark() ? TEXT_YES : TEXT_NO) . '</td>' . chr(10);
$fieldset_content .= '  </tr>' . chr(10);

if ($security_level > 3) {
	if(!$doc_details->fields['security']) $doc_details->fields['security'] = 'u:0;g:0';
	$temp = explode(';', $doc_details->fields['security']);
	$security = array();
	$desc = '';
	foreach ($temp as $value) {
	  $member = explode(':', $value);
	  switch($member[0]) {
	  	case 'u': $desc .= TEXT_USERS.':'; break;
	  	case 'g': $desc .= ', '.TEXT_GROUPS.':'; break;
	  }
	  switch($member[1]) {
	  	case '':
	  	case '0':  $desc .= TEXT_ALL;   break;
	  	case '-1': $desc .= TEXT_NONE;   break;
	  	default:   $desc .= TEXT_SELECT; break;
	  }
	  if ($member[1] == '-1') $member[1] = ''; // make it no access which is null string on pull down
	  $mbr_id = array_shift($member);
	  $security[$mbr_id] = $member;
	}
	$fieldset_content .= '  <tr>' . chr(10);
	$fieldset_content .= '    <td>' . TEXT_SECURITY . '</td>' . chr(10);
	$fieldset_content .= '    <td>' . chr(10);
	$fieldset_content .= '  <div id="down_arrow" style="float:right;"><a href="#" onclick ="return boxShow(\'doc_security\');">';
	$fieldset_content .= html_icon('actions/go-down.png', TEXT_PROPERTIES, $size = 'small', '', '16', '16');
	$fieldset_content .= '  </a></div>' . chr(10);
	$fieldset_content .= '  <div id="up_arrow" style="float:right;display:none"><a href="#" onclick ="return boxHide(\'doc_security\');">';
	$fieldset_content .= html_icon('actions/go-up.png', TEXT_CANCEL, $size = 'small', '', '16', '16');
	$fieldset_content .= '  </a></div>' . $desc . '</td>' . chr(10);
	$fieldset_content .= '  </tr></tbody></table>' . chr(10);

	$fieldset_content .= '<div id="doc_security" style="display:none">' . chr(10); // security table
	$fieldset_content .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . chr(10);
	$fieldset_content .= ' <thead class="ui-widget-header">' . chr(10);
	$fieldset_content .= '  <tr><th>' . TEXT_USERS . '</th><th>' . TEXT_GROUPS . '</th></tr>' . chr(10);
	$fieldset_content .= ' </thead>' . chr(10);
	$fieldset_content .= ' <tbody class="ui-widget-content">' . chr(10);
	$fieldset_content .= '  <tr>' . chr(10);
	$fieldset_content .= '   <td align="center">' . html_checkbox_field('user_all',  '1', (in_array('0', $security['u'], true) ? true : false)) . ' ' . TEXT_ALL_USERS . '</td>' . chr(10);
	$fieldset_content .= '   <td align="center">' . html_checkbox_field('group_all', '1', (in_array('0', $security['g'], true) ? true : false)) . ' ' . TEXT_ALL_GROUPS .'</td>' . chr(10);
	$fieldset_content .= '  </tr>' . chr(10);
	$fieldset_content .= '  <tr>' . chr(10);
	$fieldset_content .= '   <td width="50%" align="center">' . html_pull_down_menu('users[]',  gen_get_pull_down(TABLE_USERS,  true, '1', 'admin_id', 'display_name'), $security['u'], 'multiple="multiple" size="20"') .'</td>' . chr(10);
	$fieldset_content .= '   <td width="50%" align="center">' . html_pull_down_menu('groups[]', gen_get_pull_down(TABLE_DEPARTMENTS, true, '1'), $security['g'], 'multiple="multiple" size="20"') .'</td>' . chr(10);
	$fieldset_content .= '  </tr>' . chr(10);
	$fieldset_content .= ' </tbody>' . chr(10);
	$fieldset_content .= '</table>' . chr(10);
	$fieldset_content .= '</div>' . chr(10);
} else {
	$fieldset_content .= '  </tbody></table>' . chr(10);
}
$fieldset_content .= '</td>' . chr(10);
// column 2
$fieldset_content .= '<td width="50%" valign="top">' . chr(10);
$fieldset_content .= '  <table class="ui-widget" style="border-collapse:collapse;width:100%">' . chr(10);
$fieldset_content .= '  <thead class="ui-widget-header">' . chr(10);
$fieldset_content .= '    <tr><th colspan="4">' . TEXT_HISTORY . '</th></tr>' . chr(10);

if (sizeof($doc_history) > 0) {
  $fieldset_content .= '  <tr>' . chr(10);
  $fieldset_content .= '    <th>' . TEXT_REVISION . '</th>' . chr(10);
  $fieldset_content .= '    <th>' . TEXT_SIZE     . '</th>' . chr(10);
  $fieldset_content .= '    <th>' . TEXT_CREATE_DATE . '</th>' . chr(10);
  $fieldset_content .= '    <th>' . TEXT_LAST_VIEW  . '</th>' . chr(10);
  $fieldset_content .= '  </tr>' . chr(10); 
  $fieldset_content .= '  </thead>' . chr(10);
  $fieldset_content .= '  <tbody class="ui-widget-content">' . chr(10);
  for ($i = 0; $i < sizeof($doc_history); $i++) {
    $fieldset_content .= '  <tr>' . chr(10);
    $fieldset_content .= '    <td align="center">' . $i . '</td>' . chr(10);
    $fieldset_content .= '    <td align="right">' . $doc_history[$i]['size']  . '</td>' . chr(10);
    $fieldset_content .= '    <td align="center">' . date(DATE_FORMAT, $doc_history[$i]['mtime']) . '</td>' . chr(10);
    $fieldset_content .= '    <td align="center">' . date(DATE_FORMAT, $doc_history[$i]['atime']) . '</td>' . chr(10);
    $fieldset_content .= '  </tr>' . chr(10); 
  }
} else {
  $fieldset_content .= '  </thead>' . chr(10);
  $fieldset_content .= '  <tbody class="ui-widget-content">' . chr(10);
}
$fieldset_content .= '    <tr><th colspan="4">' . TEXT_THUMBNAIL . '</th></tr>' . chr(10);
$fieldset_content .= '    <tr><td colspan="4" align="center">' . TEXT_IMG_NOT_AVAILABLE . '</th></tr>' . chr(10);
$fieldset_content .= '  </tbody></table>' . chr(10);
$fieldset_content .= '</td>' . chr(10);
// end table
$fieldset_content .= '</tr></table>' . chr(10);

?>