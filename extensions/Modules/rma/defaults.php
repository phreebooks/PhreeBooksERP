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
//  Path: /modules/rma/defaults.php
//
// default directory for contact attachments
define('RMA_DIR_ATTACHMENTS',  DIR_FS_MY_FILES . $_SESSION['company'] . '/rma/main/');

$status_codes = array(
  '0'  => TEXT_PLEASE_SELECT, // do not remove from top position
  '1'  => RMA_STATUS_1,
  '2'  => RMA_STATUS_2,
  '3'  => RMA_STATUS_3,
  '4'  => RMA_STATUS_4,
  '5'  => RMA_STATUS_5,
  '6'  => RMA_STATUS_6,
  '7'  => RMA_STATUS_7,
  '8'  => RMA_STATUS_8,
  '9'  => RMA_STATUS_9,
  '10' => RMA_STATUS_10,
  '90' => RMA_STATUS_90,
  '99' => TEXT_CLOSED,
);

$reason_codes = array(
  '0'  => RMA_REASON_0, // do not remove from top position
  '1'  => RMA_REASON_1,
  '2'  => RMA_REASON_2,
  '3'  => RMA_REASON_3,
  '4'  => RMA_REASON_4,
  '5'  => RMA_REASON_5,
  '6'  => RMA_REASON_6,
  '7'  => RMA_REASON_7,
  '80' => RMA_REASON_80,
  '99' => RMA_REASON_99,
);

$action_codes = array(
  '0'  => RMA_ACTION_0, // do not remove from top position
  '1'  => RMA_ACTION_1,
  '2'  => RMA_ACTION_2,
  '3'  => RMA_ACTION_3,
  '4'  => RMA_ACTION_4,
  '5'  => RMA_ACTION_5,
  '6'  => RMA_ACTION_6 ,
  '99' => RMA_ACTION_99,
);

?>