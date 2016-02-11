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
//  Path: /modules/contacts/defaults.php
//
// default directory for contact attachments
define('CONTACTS_DIR_ATTACHMENTS',  DIR_FS_MY_FILES . $_SESSION['company'] . '/contacts/main/');

$employee_types = array(
  'e' => TEXT_EMPLOYEE,
  's' => TEXT_SALES_REP,
  'b' => TEXT_BUYER,
);

$project_cost_types = array(
 'LBR' => TEXT_LABOR,
 'MAT' => TEXT_MATERIALS,
 'CNT' => TEXT_CONTRACTORS,
 'EQT' => TEXT_EQUIPMENT,
 'OTH' => TEXT_OTHER,
);

$crm_actions = array(
  ''     => TEXT_NONE,
  'new'  => sprintf(TEXT_NEW_ARGS, TEXT_CALL),
  'ret'  => TEXT_RETURNED_CALL,
  'flw'  => TEXT_FOLLOW_UP_CALL,
  'inac' => TEXT_INACTIVE,
  'lead' => sprintf(TEXT_NEW_ARGS, TEXT_LEAD),
  'mail_in'  => TEXT_EMAIL_RECEIVED,
  'mail_out' => TEXT_EMAIL_SEND,
);

?>
