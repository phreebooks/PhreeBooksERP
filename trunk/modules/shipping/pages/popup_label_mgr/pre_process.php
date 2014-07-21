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
//  Path: /modules/shipping/pages/popup_label_mgr/pre_process.php
//
$security_level = validate_user(SECURITY_ID_SHIPPING_MANAGER);
/**************   page specific initialization  *************************/
gen_pull_language('contacts');
require_once(DIR_FS_WORKING . 'defaults.php');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_label_mgr/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

$method = $_GET['method'];
$cal_ship = array(
  'name'      => 'dateShipped',
  'form'      => 'label_mgr',
  'fieldname' => 'ship_date',
  'imagename' => 'deliver_date',
  'default'   => isset($sInfo->ship_date) ? gen_locale_date($sInfo->ship_date) : date(DATE_FORMAT),
  'params'    => array('align' => 'left'),
);
$cal_exp = array(
  'name'      => 'dateExpected',
  'form'      => 'label_mgr',
  'fieldname' => 'deliver_date',
  'imagename' => 'btn_date_2',
  'default'   => isset($sInfo->deliver_date) ? gen_locale_date($sInfo->deliver_date) : date(DATE_FORMAT),
  'params'    => array('align' => 'left'),
);

load_method_language(DIR_FS_MODULES . 'shipping/methods/', $method);
include_once (DIR_FS_MODULES . 'shipping/methods/' . $method . '/label_mgr/pre_process.php');

?>