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
//  Path: /modules/cp_action/language/en_us/language.php
//

// Headings
define('BOX_CAPA_MAINTAIN','Corrective/Preventative Action');
define('MENU_HEADING_NEW_CAPA','Create New Corrective Action');
// General Defines
define('TEXT_ACTION_EFFECTIVE','Was Action Taken Effective?');
define('TEXT_ACTION_TAKEN','Action Taken');
define('TEXT_AGREED_TO_BY','Agreed To By');
define('TEXT_AGREED_DATE','Agreed Date');
define('TEXT_ASSIGNED_BY_SYSTEM','(Assigned By System)');
define('TEXT_ASSIGNED_DATE','Assigned Date');
define('TEXT_ASSIGNED_TO','Assigned To');
define('TEXT_AUDIT','Audit');
define('TEXT_AUDIT_NOTES','Audit Notes');
define('TEXT_CA','CA');
define('TEXT_CAPAS','CAPAs');
define('TEXT_CAPA_ID','CAPA Num');
define('TEXT_CAPA_NOTES','Description of Issue');
define('TEXT_ACTION_TYPE','Action Type');
define('TEXT_PURCHASE_INVOICE_ID','Sales/Invoice #');
define('TEXT_CREATION_DATE','Creation Date');
define('TEXT_CLOSED_BY','Closed By');
define('TEXT_CLOSED_DATE','Closed Date');
define('TEXT_CUSTOMER_TELEPHONE','Telephone');
define('TEXT_CUSTOMER_EMAIL','Email');
define('TEXT_CUSTOMER_NOTES','Customer Notes');
define('TEXT_CUSTOMER_ID','Customer ID');
define('TEXT_CUSTOMER_INFO','Customer Information');
define('TEXT_CUSTOMER_NAME','Customer Name');
define('TEXT_DETAILS','Details');
define('TEXT_ENTERED_BY','Entered By');
define('TEXT_IMPLEMENTATION','Implementation');
define('TEXT_INVESTIGATION','Investigation');
define('TEXT_INVESTIGATION_TITLE','Investigation');
define('TEXT_NEW_CAPA_NUMBER','If No, New CA/PA Number');
define('TEXT_PA','PA');
define('TEXT_REQUESTED_BY','Requested By');
//************ CAPA defines *************/
define('MODULE_CAPA_GEN_INFO','CAPA Module Administration tools. Please select an action below.');
define('MODULE_CAPA_INSTALL_INFO','Install Return Material Authorization Module');
define('MODULE_CAPA_REMOVE_INFO','Remove Return Material Authorization Module');
define('MODULE_CAPA_REMOVE_CONFIRM','Are you sure you want to remove the CAPA Module?');
define('CAPAS_ERROR_DELETE_MSG','The database files have been deleted. To completely remove the module, remove all files in the directory /my_files/custom/cp_action and the configuration file /my_files/custom/extra_menus/cp_action.php');
define('CAPA_MSG_DELETE_CAPA','Are you sure you want to delete this Corrective/Preventative Action?');
// audit log messages
define('CAPA_LOG_USER_ADD','CAPA Created - CAPA # ');
define('CAPA_LOG_USER_UPDATE','CAPA Updated - CAPA # ');
define('CAPA_MESSAGE_SUCCESS_ADD','Successfully created CAPA # ');
define('CAPA_MESSAGE_SUCCESS_UPDATE','Successfully updated CAPA # ');
define('CAPA_MESSAGE_ERROR','There was an error creating/updating the CAPA.');
define('CAPA_MESSAGE_DELETE','The CAPA was successfully deleted.');
define('CAPA_ERROR_CANNOT_DELETE','There was an error deleting the CAPA.');
//  codes for status and CAPA reason
define('CAPA_STATUS_0','Select Status ...');
define('CAPA_STATUS_1','CA/PA Created');
define('CAPA_STATUS_2','In Process');
define('CAPA_STATUS_3','Created & Assigned');
define('CAPA_STATUS_4','Investigated');
define('CAPA_STATUS_5','Implemented');
define('CAPA_STATUS_6','Audited');
define('CAPA_STATUS_90','Closed Unsuccessful');
define('CAPA_STATUS_99','Closed Successful');

?>