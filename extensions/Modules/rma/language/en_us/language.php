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
define('TEXT_RECEIVE_CARRIER','Shipment Carrier');
define('TEXT_RECEIVE_TRACKING_NUM','Shipment Tracking #');
define('TEXT_RECEIVING','Receiving');
// Messages
define('RMA_DISPOSITION_DESC','<b>Set status to closed and close when completed!</b>');
define('RMA_MESSAGE_ERROR','There was an error creating/updating the RMA.');
define('RMA_MESSAGE_SUCCESS_ADD','Successfully created RMA # ');
define('RMA_MESSAGE_SUCCESS_UPDATE','Successfully updated RMA # ');
define('RMA_MESSAGE_DELETE','The RMA was successfully deleted.');
define('RMA_ERROR_CANNOT_DELETE','There was an error deleting the RMA.');
// Javascrpt defines
define('RMA_MSG_DELETE_RMA','Are you sure you want to delete this RMA?');
define('RMA_ROW_DELETE_ALERT','Are you sure you want to delete this item row?');
// audit log messages
define('RMA_LOG_USER_ADD','RMA Created - RMA # ');
define('RMA_LOG_USER_UPDATE','RMA Updated - RMA # ');
//  codes for status and RMA reason
define('RMA_STATUS_0','Select Status ...');
define('RMA_STATUS_1','RMA Created/Waiting for Parts');
define('RMA_STATUS_2','Parts Received');
define('RMA_STATUS_3','Receiving Inspection');
define('RMA_STATUS_4','In Disposition');
define('RMA_STATUS_5','In Test/Evaluation');
define('RMA_STATUS_6','Waiting for Credit');
define('RMA_STATUS_7','Closed - Replaced');
define('RMA_STATUS_8','Closed - No Warranty');
define('RMA_STATUS_9','Damage Claim');
define('RMA_STATUS_10','Closed - Returned');
define('RMA_STATUS_90','Closed - Not Received');
define('RMA_STATUS_99','Closed');

define('RMA_REASON_0','Select Reason for RMA ...');
define('RMA_REASON_1','Did Not Need');
define('RMA_REASON_2','Ordered Wrong Part');
define('RMA_REASON_3','Did Not fit');
define('RMA_REASON_4','Defective/Swap out');
define('RMA_REASON_5','Damaged in Shipping');
define('RMA_REASON_6','Refused by Customer');
define('RMA_REASON_7','Duplicate Shipment');
define('RMA_REASON_80','Wrong Connector');
define('RMA_REASON_99','Other (Specify in Notes)');

define('RMA_ACTION_0','Select Action ...');
define('RMA_ACTION_1','Return to Stock');
define('RMA_ACTION_2','Return to Customer');
define('RMA_ACTION_3','Test & Replace');
define('RMA_ACTION_4','Warranty Replace');
define('RMA_ACTION_5','Scrap');
define('RMA_ACTION_6','Test & Credit');
define('RMA_ACTION_99','Other (Specify in Notes)');

?>