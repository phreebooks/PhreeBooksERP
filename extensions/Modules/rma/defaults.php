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
//  Path: /modules/rma/defaults.php
//
// default directory for contact attachments
define('RMA_DIR_ATTACHMENTS',  DIR_FS_MY_FILES . $_SESSION['user']->company . '/rma/main/');

$status_codes = array(
  '0'  => TEXT_PLEASE_SELECT, // do not remove from top position
  '1'  => TEXT_RMA_CREATED_AND_WAITING_FOR_PARTS,
  '2'  => TEXT_PARTS_RECEIVED,
  '3'  => TEXT_RECEIVING_INSPECTION,
  '4'  => TEXT_IN_DISPOSITION,
  '5'  => TEXT_IN_TEST_OR_EVALUATION,
  '6'  => TEXT_WAITING_FOR_CREDIT,
  '7'  => TEXT_CLOSED_AND_REPLACED,
  '8'  => TEXT_CLOSED_NO_WARRANTY,
  '9'  => TEXT_DAMAGE_CLAIM,
  '10' => TEXT_CLOSED_AND_RETURNED,
  '90' => TEXT_CLOSED_NOT_RECEIVED,
  '99' => TEXT_CLOSED,
);

$reason_codes = array(
  '0'  => TEXT_PLEASE_SELECT, // do not remove from top position
  '1'  => TEXT_DID_NOT_NEED,
  '2'  => TEXT_ORDERED_WRONG_PART,
  '3'  => TEXT_DID_NOT_FIT,
  '4'  => TEXT_DEFECTIVE_OR_SWAP_OUT,
  '5'  => TEXT_DAMAGED_IN_SHIPPING,
  '6'  => TEXT_REFUSED_BY_CUSTOMER,
  '7'  => TEXT_DUPLICATE_SHIPMENT,
  '80' => TEXT_WRONG_CONNECTOR,
  '99' => TEXT_OTHER_SPECIFY_IN_NOTES,
);

$action_codes = array(
  '0'  => sprintf(TEXT_SELECT_ARGS, TEXT_ACTION), // do not remove from top position
  '1'  => TEXT_RETURN_TO_STOCK,
  '2'  => TEXT_RETURN_TO_CUSTOMER,
  '3'  => TEXT_TEST_AND_REPLACE,
  '4'  => TEXT_WARRANTY_REPLACE,
  '5'  => TEXT_SCRAP,
  '6'  => TEXT_TEST_AND_CREDIT ,
  '99' => TEXT_OTHER_SPECIFY_IN_NOTES,
);

?>