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
//  Path: /modules/phreebooks/pages/popup_delivery/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files  *********************/
/**************   page specific initialization  *************************/
$oID = (int)$_GET['oID'];
define('JOURNAL_ID',(int)$_GET['jID']);
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_delivery/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	$i = 1;
	while(true) {
	  if (!isset($_POST['eta_date_' . $i])) break;
	  if ($_POST['eta_date_' . $i] <> '') {
		$new_date = gen_db_date($_POST['eta_date_' . $i]);
		$rID = $_POST['id_' . $i];
		$db->Execute("update " . TABLE_JOURNAL_ITEM . " set date_1 = '" . $new_date . "' where id = " . $rID);
	  }
	  $i++;
	}
	gen_add_audit_log(ORD_DELIVERY_DATES . TEXT_EDIT, $result->fields['purchase_invoice_id']);
	break;
  default:
}
/*****************   prepare to display templates  *************************/
$gl_type = (JOURNAL_ID == 4 || JOURNAL_ID == 6) ? 'poo' : 'soo';
$sql = " select m.purchase_invoice_id, i.id, i.sku, i.qty, i.description, i.date_1 
	from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
	where i.ref_id = " . $oID . " and i.gl_type = '" . $gl_type . "'";
$ordr_items = $db->Execute($sql);
$num_items  = $ordr_items->RecordCount();

$include_header   = false;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', ORD_EXPECTED_DATES);

?>