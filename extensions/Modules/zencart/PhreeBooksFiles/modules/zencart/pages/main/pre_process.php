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
//  Path: /modules/zencart/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_ZENCART_INTERFACE);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'inventory/defaults.php');
require_once(DIR_FS_MODULES . 'shipping/defaults.php');
require_once(DIR_FS_WORKING . 'functions/zencart.php');
require_once(DIR_FS_MODULES . 'inventory/functions/inventory.php');
require_once(DIR_FS_WORKING . 'classes/zencart.php');
require_once(DIR_FS_WORKING . 'classes/bulk_upload.php');

/**************   page specific initialization  *************************/
$ship_date = $_POST['ship_date'] ? \core\classes\DateTime::db_date_format($_POST['ship_date']) : date('Y-m-d');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'custom/zencart/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  	case 'upload':
	  	try{
	    	$upXML = new \zencart\classes\zencart();
			$id    = db_prepare_input($_POST['rowSeq']);
			$upXML->submitXML($id, 'product_ul');
			gen_add_audit_log(TEXT_UPLOAD_PRODUCT, $upXML->sku);
	  	}catch(Exception $e) {
	  		$messageStack->add($e->getMessage());
		}
		break;
  	case 'bulkupload':
	  	try{
	  		$inc_image = isset($_POST['include_images']) ? true : false;
	    	\zencart\classes\bulk_upload($inc_image);
			gen_add_audit_log(TEXT_BULK_UPLOAD);
			write_configure('MODULE_ZENCART_LAST_UPDATE', date('Y-m-d H:i:s'));
	  	}catch(Exception $e) {
	  		$messageStack->add($e->getMessage());
		}
	    break;
  	case 'sync':
	  	try{
	    	$upXML = new \zencart\classes\zencart();
			$upXML->submitXML(0, 'product_sync');
			gen_add_audit_log(TEXT_ZENCART_PRODUCT_SYNC);
		}catch(Exception $e) {
	  		$messageStack->add($e->getMessage());
		}
		break;
  	case 'confirm':
	  	try{
		    $upXML = new \zencart\classes\zencart();
			$upXML->post_date = $ship_date;
			$upXML->submitXML(0, 'confirm');
			gen_add_audit_log(ZENCART_SHIP_CONFIRM, $ship_date);
		}catch(Exception $e) {
	  		$messageStack->add($e->getMessage());
		}
	    break;
  	default:
}

/*****************   prepare to display templates  *************************/
$cal_zc = array(
  'name'      => 'shipDate',
  'form'      => 'zencart',
  'fieldname' => 'ship_date',
  'imagename' => 'btn_date_1',
  'default'   => \core\classes\DateTime::createFromFormat(DATE_FORMAT, $ship_date),
  'params'    => array('align' => 'left'),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_ZENCART_INTERFACE);

?>