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
//  Path: /modules/rma/language/en_us/language.php
//

// Headings
define('TEXT_RETURN_MATERIAL_AUTHORIZATIONS','Return Material Authorizations');
define('MENU_HEADING_NEW_RMA','Create New RMA');

// General Defines
define('TEXT_RMAS','RMAs');
define('TEXT_RMA_ID','RMA Num');
define('TEXT_ASSIGNED_BY_SYSTEM','(Assigned by System)');
define('TEXT_CREATION_DATE','Created');
define('TEXT_PURCHASE_INVOICE_ID','Sales/Invoice #');
define('TEXT_INVOICE_DATE','Invoice Date');
define('TEXT_ORIG_PO_SO','Original SO/PO #');
define('TEXT_CALLER_NAME','Caller Name');
define('TEXT_CUSTOMER_ID','Customer ID');
define('TEXT_TELEPHONE','Telephone');
define('TEXT_DATE_MANUFACTURE','Manufacturer DLC');
define('TEXT_DATE_WARRANTY_EXPIRES','Date Warranty Expires');
define('TEXT_DETAILS','Details');
define('TEXT_DISPOSITION','Disposition');
define('TEXT_REASON_FOR_RETURN','Reason for Return');
define('TEXT_ENTERED_BY','Entered By');
define('TEXT_DATE_RECEIVED','Date Received');
define('TEXT_RECEIVED_BY','Received By');
define('TEXT_SHIPMENT_CARRIER','Shipment Carrier');
define('TEXT_RECEIVE_TRACKING_NUM','Shipment Tracking #');
define('TEXT_RECEIVING','Receiving');
// Messages
define('RMA_DISPOSITION_DESC','<b>Set status to closed and close when completed!</b>');
define('TEXT_THERE_WAS_AN_ERROR_CREATING_OR_UPDATING_THE_RMA','There was an error creating/updating the RMA.');
define('RMA_MESSAGE_SUCCESS_ADD','Successfully created RMA # ');
define('RMA_MESSAGE_SUCCESS_UPDATE','Successfully updated RMA # ');
define('RMA_MESSAGE_DELETE','The RMA was successfully deleted.');
define('TEXT_THERE_WAS_AN_ERROR_DELETING_THE_RMA','There was an error deleting the RMA.');
// Javascrpt defines
define('RMA_MSG_DELETE_RMA','Are you sure you want to delete this RMA?');
define('RMA_ROW_DELETE_ALERT','Are you sure you want to delete this item row?');
// audit log messages
define('RMA_LOG_USER_ADD','RMA Created - RMA # ');
define('RMA_LOG_USER_UPDATE','RMA Updated - RMA # ');
//  codes for status and RMA reason
define('RMA_STATUS_0','Select Status ...');
define('TEXT_RMA_CREATED_AND_WAITING_FOR_PARTS','RMA Created/Waiting for Parts');
define('TEXT_PARTS_RECEIVED','Parts Received');
define('TEXT_RECEIVING_INSPECTION','Receiving Inspection');
define('TEXT_IN_DISPOSITION','In Disposition');
define('TEXT_IN_TEST_OR_EVALUATION','In Test/Evaluation');
define('TEXT_WAITING_FOR_CREDIT','Waiting for Credit');
define('TEXT_CLOSED_AND_REPLACED','Closed - Replaced');
define('TEXT_CLOSED_NO_WARRANTY','Closed - No Warranty');
define('TEXT_DAMAGE_CLAIM','Damage Claim');
define('TEXT_CLOSED_AND_RETURNED','Closed - Returned');
define('TEXT_CLOSED_NOT_RECEIVED','Closed - Not Received');
define('RMA_STATUS_99','Closed');

define('RMA_REASON_0','Select Reason for RMA ...');
define('TEXT_DID_NOT_NEED','Did Not Need');
define('TEXT_ORDERED_WRONG_PART','Ordered Wrong Part');
define('TEXT_DID_NOT_FIT','Did Not fit');
define('TEXT_DEFECTIVE_OR_SWAP_OUT','Defective/Swap out');
define('TEXT_DAMAGED_IN_SHIPPING','Damaged in Shipping');
define('TEXT_REFUSED_BY_CUSTOMER','Refused by Customer');
define('TEXT_DUPLICATE_SHIPMENT','Duplicate Shipment');
define('TEXT_WRONG_CONNECTOR','Wrong Connector');
define('TEXT_OTHER_SPECIFY_IN_NOTES','Other (Specify in Notes)');

define('RMA_ACTION_0','Select Action ...');
define('TEXT_RETURN_TO_STOCK','Return to Stock');
define('TEXT_RETURN_TO_CUSTOMER','Return to Customer');
define('TEXT_TEST_AND_REPLACE','Test & Replace');
define('TEXT_WARRANTY_REPLACE','Warranty Replace');
define('TEXT_SCRAP','Scrap');
define('TEXT_TEST_AND_CREDIT','Test & Credit');
define('RMA_ACTION_99','Other (Specify in Notes)');

?>