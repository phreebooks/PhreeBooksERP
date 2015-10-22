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
//  Path: /modules/inventory/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_MAINTAIN_INVENTORY);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'functions/inventory.php');
/**************   page specific initialization  *************************/
$processed   = false;
$criteria    = array();
$fields		 = new \inventory\classes\fields();
$type        = isset($_REQUEST['inventory_type']) ? $_REQUEST['inventory_type'] : null; // default to stock item
history_filter('inventory');
$first_entry = isset($_GET['add']) ? true : false;
// load the filters
$f0 = $_GET['f0'] = isset($_POST['action']) ? (isset($_POST['f0']) ? '1' : '0') : $_GET['f0']; // show inactive checkbox
$f1 = $_GET['f1'] = isset($_POST['f1']) ? $_POST['f1'] : $_GET['f1']; // inventory_type dropdown
$id = isset($_POST['rowSeq']) ? db_prepare_input($_POST['rowSeq']) : db_prepare_input($_GET['cID']);
// getting the right inventory type.
if (!isset($_REQUEST['inventory_type'])){
	if(isset($_REQUEST['cID'])) $result = $admin->DataBase->query("SELECT inventory_type FROM ".TABLE_INVENTORY." WHERE id='{$_REQUEST['cID']}'");
	else if (isset($_REQUEST['rowSeq'])) $result = $admin->DataBase->query("SELECT inventory_type FROM ".TABLE_INVENTORY." WHERE id='{$_REQUEST['rowSeq']}'");
	else $result = $admin->DataBase->query("SELECT inventory_type FROM ".TABLE_INVENTORY." WHERE sku='{$_REQUEST['sku']}'");
	if ($result->fetch(\PDO::FETCH_NUM)>0) $type = $result->fields['inventory_type'];
	else $type ='si';
}
$temp = '\inventory\classes\type\\'. $type;
$cInfo = new $temp;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  	try{
		\core\classes\user::validate_security($security_level, 2); // security check
		$cInfo->save();
  	}catch(Exception $e){
  		$messageStack->add($e->getMessage());
		$_REQUEST['action'] = 'edit';
  	}
	break;
  case 'download':
   	    $cID   = db_prepare_input($_POST['id']);
  	    $imgID = db_prepare_input($_POST['rowSeq']);
	    $filename = 'inventory_'.$cID.'_'.$imgID.'.zip';
	    if (file_exists(INVENTORY_DIR_ATTACHMENTS . $filename)) {
	       $backup = new \phreedom\classes\backup();
	       $backup->download(INVENTORY_DIR_ATTACHMENTS, $filename, true);
	    }
	    ob_end_flush();
  		session_write_close();
        die;
  case 'dn_attach': // download from list, assume the first document only
        $cID   = db_prepare_input($_POST['rowSeq']);
  	    $result = $admin->DataBase->query("select attachments from ".TABLE_INVENTORY." where id = $cID");
  	    $attachments = unserialize($result->fields['attachments']);
  	    foreach ($attachments as $key => $value) {
		   	$filename = 'inventory_'.$cID.'_'.$key.'.zip';
		   	if (file_exists(INVENTORY_DIR_ATTACHMENTS . $filename)) {
		      	require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
		      	$backup = new \phreedom\classes\backup();
		      	$backup->download(INVENTORY_DIR_ATTACHMENTS, $filename, true);
		      	ob_end_flush();
  				session_write_close();
		      	die;
		   	}
  	    }
  case 'reset':
  		$_SESSION['filter_field']	 = null;
  		$_REQUEST['filter_field']	 = null;
  		$_SESSION['filter_criteria'] = null;
  		$_REQUEST['filter_criteria'] = null;
  		$_SESSION['filter_value'] 	 = null;
  		$_REQUEST['filter_value'] 	 = null;
		break;
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  case 'new':
  default:
}

/*****************   prepare to display templates  *************************/
// build the type filter list
$type_select_list = array( // add some extra options
  array('id' => '0',   'text' => TEXT_ALL),
  array('id' => 'cog', 'text' => TEXT_CONTROLLED_STOCK),
);

foreach ($inventory_types_plus as $key => $value) $type_select_list[] = array('id' => $key,  'text' => $value);
// generate the vendors and fill js arrays for dynamic pull downs
$vendors = gen_get_contact_array_by_type('v');
$js_vendor_array = "var js_vendor_array = new Array();" . chr(10);
for ($i = 0; $i < count($vendors); $i++) {
  $js_vendor_array .= "js_vendor_array[$i] = new dropDownData('{$vendors[$i]['id']}', '{$vendors[$i]['text']}');" . chr(10);
}
// generate the pricesheets and fill js arrays for dynamic pull downs
$pur_pricesheets = get_price_sheet_data('v');
$js_pricesheet_array = "var js_pricesheet_array = new Array();" . chr(10);
for ($i = 0; $i < count($pur_pricesheets); $i++) {
  $js_pricesheet_array .= "js_pricesheet_array[$i] = new dropDownData('{$pur_pricesheets[$i]['id']}', '{$pur_pricesheets[$i]['text']}');" . chr(10);
}

// load the tax rates
$tax_rates        = ord_calculate_tax_drop_down('c');
$purch_tax_rates  = ord_calculate_tax_drop_down('v',false);
// generate a rate array parallel to the drop down for javascript
$js_tax_rates = 'var tax_rates = new Array(' . count($tax_rates) . ');' . chr(10);
for ($i = 0; $i < count($tax_rates); $i++) {
  $js_tax_rates .= 'tax_rates[' . $i . '] = new tax("' . $tax_rates[$i]['id'] . '", "' . $tax_rates[$i]['text'] . '", "' . $tax_rates[$i]['rate'] . '");' . chr(10);
}

// load gl accounts
$gl_array_list    = gen_coa_pull_down();


?>