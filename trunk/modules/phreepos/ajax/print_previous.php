<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/phreepos/ajax/save_pos.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_PHREEPOS);
define('JOURNAL_ID','19');
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');
/**************   page specific initialization  *************************/
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'phreepos/custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
if(isset($_GET['oID'])){
	$journal_id = $_GET['oID'];
}else {
	$order = $admin->DataBase->query("select MAX(id) AS id from " . TABLE_JOURNAL_MAIN . "
	    where journal_id = '" . JOURNAL_ID . "' and admin_id = '".$_SESSION['admin_id']."'");
	$journal_id = $order->fields['id'];
}
//print
$result = $admin->DataBase->query("select id from " . TABLE_PHREEFORM . " where doc_group = '{$order->popup_form_type}' and doc_ext = 'frm'");
if ($result->fetch(\PDO::FETCH_NUM) == 0) throw new \core\classes\userException("No form was found for this type ({$order->popup_form_type})");

if ($result->fetch(\PDO::FETCH_NUM) > 1) if(DEBUG) $massage .= "More than one form was found for this type ({$order->popup_form_type}). Using the first form found.";
$rID    = $result->fields['id']; // only one form available, use it
$report = get_report_details($rID);
$title  = $report->title;
$report->datedefault = 'a';
$report->xfilterlist[0]->fieldname = 'journal_main.id';
$report->xfilterlist[0]->default   = 'EQUAL';
$report->xfilterlist[0]->min_val   = $journal_id;
$output = BuildForm($report, $delivery_method = 'S'); // force return with report
if ($output === true) {
  	if(DEBUG) $massage .='direct printing fault.';
} else if (!is_array($output) ){ // if it is a array then it is not a sequential report
	// fetch the receipt and prepare to print
	$receipt_data = str_replace("\r", "", addslashes($output)); // for javascript multi-line
	foreach (explode("\n",$receipt_data) as $value){
		if(!empty($value)){
	  		$xml .= "<receipt_data>\n";
	       	$xml .= "\t" . xmlEntry("line", $value);
	    	$xml .= "</receipt_data>\n";
		}
	}
}
				 $xml .= "\t" . xmlEntry("action",$_REQUEST['action']);
				 $xml .= "\t" . xmlEntry("open_cash_drawer", false);
				 $xml .= "\t" . xmlEntry("order_id", $journal_id);
if ($massage)  	 $xml .= "\t" . xmlEntry("massage", $massage);
echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>