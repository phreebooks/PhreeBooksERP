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
//  Path: /modules/shipping/pages/ship_mgr/pre_process.php
//
$security_level = validate_user(SECURITY_ID_SHIPPING_MANAGER);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
/**************   page specific initialization  *************************/
$date        = $_GET['search_date']       ? gen_db_date($_GET['search_date']) : date('Y-m-d');
if ($_REQUEST['search_text'] == TEXT_SEARCH) $_REQUEST['search_text'] = '';
$module_id   = isset($_POST['module_id']) ? $_POST['module_id'] : '';
$row_seq     = isset($_POST['rowSeq'])    ? $_POST['rowSeq']    : '';
$action		 = $_REQUEST['action'];
// load methods
$installed_modules = load_all_methods('shipping');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/ship_mgr/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
if ($module_id) {
  require_once (DIR_FS_WORKING.'methods/'.$module_id.'/'.$module_id.'.php');
  $shipping = new $module_id;
  switch ($_REQUEST['action']) {
    default:
      if (method_exists($shipping, $action)) $shipping->$action();
      break;
    case 'track':     $shipping->trackPackages($date, $row_seq);   break;
    case 'reconcile': $shipping->reconcileInvoice();               break;
    case 'search':
    case 'search_reset':
  }
}
/*****************   prepare to display templates  *************************/
$cal_ship = array(
  'name'      => 'cal',
  'form'      => 'ship_mgr',
  'fieldname' => 'search_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($date),
  'params'    => array('align'=>'left', 'onchange'=>'calendarPage();'),
);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_SHIPPING_MANAGER);
?>