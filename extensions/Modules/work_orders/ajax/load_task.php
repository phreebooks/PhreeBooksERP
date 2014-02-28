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
//  Path: /modules/work_orders/ajax/load_task.php
//
$security_level = \core\classes\user::validate();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$xml = NULL;
$id  = $_GET['id'];
if (!$id) throw new \Exception("variable id isn't set");

$result = $db->Execute("select * from " . TABLE_WO_TASK . " where id = '" . $id . "' limit 1");
$xml .= xmlEntry("id",          $result->fields['id']);
$xml .= xmlEntry("task_name",   $result->fields['task_name']);
$xml .= xmlEntry("description", $result->fields['description']);
$xml .= xmlEntry("ref_doc",     $result->fields['ref_doc']);
$xml .= xmlEntry("ref_spec",    $result->fields['ref_spec']);
$xml .= xmlEntry("dept_id",     $result->fields['dept_id']);
$xml .= xmlEntry("job_time",    $result->fields['job_time']);
$xml .= xmlEntry("job_unit",    $result->fields['job_unit']);
$xml .= xmlEntry("mfg",         $result->fields['mfg']);
$xml .= xmlEntry("qa",          $result->fields['qa']);
$xml .= xmlEntry("data_entry",  $result->fields['data_entry']);
$xml .= xmlEntry("erp_entry",   $result->fields['erp_entry']);

// error check
echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>