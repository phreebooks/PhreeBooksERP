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
//  Path: /modules/phreeform/ajax/dir_operation.php
//
/**************   Check user security   *****************************/
$xml = NULL;
$security_level = validate_ajax_user(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$id        = $_GET['id'];
$ajax_text = '';
if (!isset($_GET['id'])) die;
$dir_details = $db->Execute("select * from " . TABLE_PHREEFORM . " where id = '" . $id . "'");
switch ($_REQUEST['action']) {
  case 'go_up':
	$id = $dir_details->fields['parent_id']; // set the id to the parent to display refreshed page
    break;
  case 'delete':
  	$result = $db->Execute("select id from " . TABLE_PHREEFORM . " where parent_id = '" . $id . "' limit 1");
	if ($result->RecordCount() > 0) {
	  $ajax_text = DOC_CTL_DIR_NOT_EMPTY;
	} else {
	  $db->Execute("delete from " . TABLE_PHREEFORM . " where id = '" . $id . "'");
	  $id = $dir_details->fields['parent_id']; // set the id to the parent to display refreshed page
	  $ajax_text = DOC_CTL_DIR_DELETED;
	}
	break;
  default: die;
}
$xml .= "\t" . xmlEntry("docID",   $id);
$xml .= "\t" . xmlEntry("message", $ajax_text);
echo createXmlHeader() . $xml . createXmlFooter();
die;
?>