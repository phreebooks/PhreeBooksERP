<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/zencart/pages/main/pre_process.php
//
$security_level = validate_user(SECURITY_ID_ZENCART_INTERFACE);
/**************  include page specific files    *********************/
gen_pull_language('shipping');
gen_pull_language('inventory');
require_once(DIR_FS_MODULES . 'inventory/defaults.php'); 
require_once(DIR_FS_MODULES . 'shipping/defaults.php'); 
require_once(DIR_FS_WORKING . 'functions/zencart.php'); 
require_once(DIR_FS_MODULES . 'inventory/functions/inventory.php'); 
require_once(DIR_FS_WORKING . 'classes/zencart.php'); 
require_once(DIR_FS_WORKING . 'classes/bulk_upload.php'); 

/**************   page specific initialization  *************************/
$error     = false;
$ship_date = $_POST['ship_date'] ? gen_db_date($_POST['ship_date']) : date('Y-m-d');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'custom/zencart/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'upload':
    $upXML = new zencart();
	$id    = db_prepare_input($_POST['rowSeq']);
	if ($upXML->submitXML($id, 'product_ul')) gen_add_audit_log(ZENCART_UPLOAD_PRODUCT, $upXML->sku);
	break;
  case 'bulkupload':
    $upXML = new bulk_upload();
    $inc_image = isset($_POST['include_images']) ? true : false;
	if ($upXML->bulkUpload($inc_image)) {
		gen_add_audit_log(ZENCART_BULK_UPLOAD);
		write_configure('MODULE_ZENCART_LAST_UPDATE', date('Y-m-d H:i:s'));
	}
    break;
  case 'sync':
    $upXML = new zencart();
	if ($upXML->submitXML(0, 'product_sync')) gen_add_audit_log(ZENCART_PRODUCT_SYNC);
	break;
  case 'confirm':
    $upXML = new zencart();
	$upXML->post_date = $ship_date;
	if ($upXML->submitXML(0, 'confirm')) gen_add_audit_log(ZENCART_SHIP_CONFIRM, $ship_date);
    break;
  default:
}

/*****************   prepare to display templates  *************************/
$cal_zc = array(
  'name'      => 'shipDate',
  'form'      => 'zencart',
  'fieldname' => 'ship_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($ship_date),
  'params'    => array('align' => 'left'),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_ZENCART_MODULE);

?>