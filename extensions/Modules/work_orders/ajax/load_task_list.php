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
//  Path: /modules/work_orders/ajax/load_task_list.php
//

/**************   Check user security   *****************************/
$xml = NULL;
$security_level = \core\classes\user::validate();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$xml = NULL;
$iID = $_GET['iID'];
if (!$iID) throw new \core\classes\userException("variable iID isn't set");

// Pull the workorder information
$field_list = array();
$query_raw    = "select m.id, m.wo_title, m.description, i.image_with_path
  from " . TABLE_WO_MAIN . " m inner join " . TABLE_INVENTORY . " i on m.sku_id = i.id
  where m.inactive = '0' and i.id = '" . $iID . "'";
$result = $admin->DataBase->query($query_raw);
$id   = $result->fields['id'];
$xml .= xmlEntry("WOid",          $id);
$xml .= xmlEntry("WOTitle",       $result->fields['wo_title']);
$xml .= xmlEntry("WODescription", $result->fields['description']);
if ($result->fields['image_with_path']) { // show image if it is defined
  $image = DIR_WS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $result->fields['image_with_path'];
} else {
  $image = 0;
}
$xml .= xmlEntry("ImageURL", $image);

if ($id) {
  $result = $admin->DataBase->query("select * from " . TABLE_WO_STEPS . " where ref_id = '" . $id . "' order by step");
  while (!$result->EOF) {
    $task = $admin->DataBase->query("select task_name, description from " . TABLE_WO_TASK . " where id = " . $result->fields['task_id'] . " limit 1");
    $xml .= "<Task>\n";
    $xml .= "\t" . xmlEntry("Step",        $result->fields['step']);
    $xml .= "\t" . xmlEntry("Task_id",     $result->fields['task_id']);
    $xml .= "\t" . xmlEntry("Task_name",   $task->fields['task_name']);
    $xml .= "\t" . xmlEntry("Description", $task->fields['description']);
    $xml .= "</Task>\n";
    $result->MoveNext();
  }
} else {
    $xml .= xmlEntry("Message", 'This SKU does not have a work order to build from!');
}

echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>