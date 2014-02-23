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
//  Path: /modules/work_orders/language/en_us/language.php
//

// Headings 
define('WO_POPUP_TASK_WINDOW_TITLE','Work Order Tasks');
define('HEADING_WORK_ORDER_MODULE_NEW','Create Work Order');
define('HEADING_WORK_ORDER_MODULE_EDIT','Edit Work Order');
define('HEADING_WORK_ORDER_MODULE_BUILD','Build and Track Work Order');
// General Defines
define('TEXT_APPROVALS','Approvals');
define('TEXT_BUILD','Build Work Order');
define('TEXT_CLOSE_DATE','Close Date');
define('TEXT_COMPLETE','Complete');
define('TEXT_DATA_ENTRY','Data Entry');
define('TEXT_DAYS','Days');
define('TEXT_DOCUMENTS','Ref Documents');
define('TEXT_DRAWINGS','Ref Drawings');
define('TEXT_ENTRY_VALUE','Entry Value');
define('TEXT_ERP_ENTRY','ERP Entry');
define('TEXT_HOURS','Hours');
define('TEXT_MFG','Mfg');
define('TEXT_MFG_INIT','Mfg Sign-off');
define('TEXT_MINUTES','Minutes');
define('TEXT_NA','N/A');
define('TEXT_PRIORITY','Priority');
define('TEXT_PROCEDURE','Procedure');
define('TEXT_QA','QA');
define('TEXT_QA_INIT','QA Sign-off');
define('TEXT_REVISION','Revision');
define('TEXT_REVISION_DATE','Rev Date');
define('TEXT_SPECIAL_NOTES','Work Order Notes and Comments');
define('TEXT_STEP','Step');
define('TEXT_TASKS','Tasks');
define('TEXT_TASK_DESC','Task Description');
define('TEXT_TASK_NAME','Task Name');
define('TEXT_TASK_SEQUENCE','Task Sequence');
define('TEXT_TASK_STATUS','Task Status');
define('TEXT_TASK_TIME','Task Time');
define('TEXT_USE_ALLOCATION','Use Allocation');
define('TEXT_WORK_ORDERS','Work Orders');
define('TEXT_WORK_ORDERS_TASK','Work Order Tasks');
define('TEXT_WO_ID','Work Order ID');
define('TEXT_WO_TITLE','Work Order Title');
define('TEXT_WO_HISTORY','History (last %s results)');
// Error and Information Messages
define('WO_MESSAGE_BUILDER_ERROR','There was and error adding/updating the work order record. Please check the data and retry.');
define('WO_ERROR_CANNOT_DELETE_BUILDER','This work order has been used in the system. It cannot be deleted.');
define('WO_BUILDER_ERROR_DUP_TITLE','The Work Order Title has already been used. Please retry!');
define('WO_SKU_NOT_FOUND','The SKU entered cannot be found in the inventory database table!');
define('WO_INSUFFICIENT_INVENTORY','Not enough of the following parts to build the sku entered. The shortages include:');
define('WO_MESSAGE_SUCCESS_MAIN_UPDATE','The work order record was updated successfully!');
define('WO_MESSAGE_SUCCESS_MAIN_ADD','The work order record was added successfully!');
define('WO_MESSAGE_SUCCESS_MAIN_DELETE','The work order was deleted successfully.');
define('WO_MESSAGE_STEP_UPDATE_SUCCESS','Work Order step %s was successfully updated.');
define('WO_MESSAGE_SUCCESS_COMPLETE','Work Order %s has been successfully completed.');
define('WO_MESSAGE_MAIN_ERROR','There was and error adding/updating the work order record. Please check the data and retry.');
define('WO_MFG_PASSWORD_BAD','The manufacturing password doesn\'t match the user name.');
define('WO_QA_PASSWORD_BAD','The quality assurance password doesn\'t match the user name.');
define('WO_DATA_VALUE_BLANK','A data value entry is required but was left blank.');
define('WO_DB_UPDATE_ERROR','There was an error updating the database.');
define('WO_MESSAGE_SUCCESS_UPDATE','The work order task was updated successfully!');
define('WO_MESSAGE_SUCCESS_ADD','The work order task was added successfully!');
define('WO_MESSAGE_ERROR','There was and error adding/updating the work order task. Please check the data and retry.');
define('WO_SKU_ID_REQUIRED','The SKU and Work Order Title are required fields.');
define('WO_CANNOT_SAVE','This work order has a higher revision, changes to this work order cannot be saved.');
define('WO_ROLL_REVISION','This work order has been used in the system, any changes will roll the revision to the next level.');
define('WO_ERROR_CANNOT_DELETE','The work order task cannot be deleted because it is being used in a work order! See work order # ');
define('WO_DUPLICATE_TASK_ID','The record was not changed, a Task ID with this name is already in the system!');
define('WO_TASK_ID_MISSING','Both the Task ID and the Description fields are required!');
define('WO_TEXT_PARTS_SHORTAGE','(%s of %s) needed of sku %s - %s');
// Audit log defines
define('WO_AUDIT_LOG_MAIN','WO Main (%s) - ');
define('WO_AUDIT_LOG_BUILDER','WO Builder (%s) - ');
define('WO_AUDIT_LOG_TASK','WO Task ($s) - ');
define('WO_AUDIT_LOG_STEP_COMPLETE','WO Step %s - Complete');
define('WO_AUDIT_LOG_WO_COMPLETE','WO %s - Complete');
// Javascrpt defines
define('WORK_ORDER_MSG_DELETE_WO','Are you sure you want to delete this entry?');

?>