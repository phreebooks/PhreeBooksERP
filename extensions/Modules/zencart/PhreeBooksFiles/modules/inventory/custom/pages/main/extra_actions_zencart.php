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
//  Path: /modules/inventory/custom/pages/main/extra_actions.php
//

// This file contains the extra actions added to the maintain inventory module, it is executed
// before the standard switch statement
$id = isset($_POST['rowSeq']) ? db_prepare_input($_POST['rowSeq']) : db_prepare_input($_GET['cID']);
switch ($_REQUEST['action']) {
// Begin - Upload operation added by PhreeSoft to upload products to ZenCart
  	case 'upload_zc':
		$id = db_prepare_input($_POST['rowSeq']);
		require_once(DIR_FS_MODULES . 'zencart/functions/zencart.php');
		$upXML = new \zencart\classes\zencart();
		$upXML->submitXML($id, 'product_ul');
		gen_add_audit_log(TEXT_UPLOAD_PRODUCT, $upXML->sku);
		$_REQUEST['action'] = '';
		break;
  	case 'save':
	  	// check if menu isn't empty when saving.
	  	if(!isset($_POST['inactive']) && isset($_POST['catalog']) && $_POST['category_id'] == ''){
	  		throw new \core\classes\userException(ZENCART_INVENTORY_CATALOG_IS_EMPTY);
	  		$_REQUEST['action'] = 'edit';
	  	}
// End - Upload operation added by PhreeSoft
  default:
}
?>