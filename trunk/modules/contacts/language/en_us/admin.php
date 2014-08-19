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
//  Path: /modules/contacts/language/en_us/admin.php
//

// Module information
define('TEXT_CONTACTS_MODULE','Contacts module');
define('MODULE_CONTACTS_DESCRIPTION','The contacts module manages all customer, vendors, employees, branches and projects used in the PhreeSoft Business Toolkit.');
// Headings
define('TEXT_CONTACTS_ADMINISTRATION','Contacts Administration');
define('TEXT_BILLING_ADDRESS_BOOK_SETTINGS','Billing Address Book Settings');
// General
define('PB_PF_CONTACT_ID','Contact ID');
define('TEXT_TERMS_TO_LANGUAGE','Terms to Language');
define('COST_TYPE_LBR','Labor');
define('TEXT_MATERIALS','Materials');
define('TEXT_CONTRACTORS','Contractors');
define('TEXT_EQUIPMENT','Equipment');
define('TEXT_OTHER','Other');
define('TEXT_CUSTOMER','Customer');
define('TEXT_VENDOR','Vendor');
define('TEXT_EMPLOYEE','Employee');
define('TEXT_CONTACT_TYPE','Contact Type');
define('NEXT_CUST_ID_NUM_DESC','Next Customer ID');
define('NEXT_VEND_ID_NUM_DESC','Next Vendor ID');
/************************** (Address Book Defaults) ***********************************************/
define('CONTACT_BILL_FIELD_REQ', 'Whether or not to require field: %s to be entered for a new main/billing address (for vendors, customers, and employees)');
/************************** (Departments) ***********************************************/
define('HR_POPUP_WINDOW_TITLE','Departments');
define('TEXT_SUB_DEPARTMENT', 'Subdepartment');
define('HR_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_DEPARTMENT_ID', 'Department ID');
define('HR_INFO_SUBACCOUNT', 'Is this department a subdepartment?');
define('TEXT_YES_ALSO_SELECT_PRIMARY_DEPARTMENT', 'Yes, also select primary department');
define('HR_INFO_ACCOUNT_TYPE', 'Department type');
define('HR_INFO_ACCOUNT_INACTIVE', 'Department inactive');
define('HR_INFO_INSERT_INTRO', 'Please enter the new department with its properties');
define('HR_INFO_NEW_ACCOUNT', 'New Department');
define('HR_INFO_EDIT_ACCOUNT', 'Edit Department');
define('HR_INFO_DELETE_INTRO', 'Are you sure you want to delete this department?');
define('HR_DEPARTMENT_REF_ERROR','The primary department cannot be the same as this subdepartment being saved!');
define('HR_LOG_DEPARTMENTS','Departments - ');
/************************** (Department Types) ***********************************************/
define('TEXT_DEPARTMENT_TYPES', 'Department Types');
define('SETUP_INFO_DEPT_TYPES_NAME', 'Department Type Name');
define('TEXT_PLEASE_ENTER_THE_NEW_DEPARTMENT_TYPE', 'Please enter the new department type');
define('SETUP_DEPT_TYPES_DELETE_INTRO', 'Are you sure you want to delete this department type?');
define('SETUP_DEPT_TYPES_DELETE_ERROR','Cannot delete this department type, it is being use by a department.');
define('SETUP_INFO_HEADING_NEW_DEPT_TYPES', 'New Department Type');
define('SETUP_INFO_HEADING_EDIT_DEPT_TYPES', 'Edit Department Type');
define('SETUP_DEPT_TYPES_LOG','Dept Types - ');
/************************** (Project Costs) ***********************************************/
define('SETUP_TITLE_PROJECTS_COSTS', 'Project Costs');
define('TEXT_SHORT_NAME', 'Short Name');
define('TEXT_COST_TYPE', 'Cost Type');
define('SETUP_INFO_DESC_LONG', 'Long Description (64 chars max)');
define('SETUP_PROJECT_COSTS_INSERT_INTRO', 'Please enter the new project cost with its properties');
define('SETUP_PROJECT_COSTS_DELETE_INTRO', 'Are you sure you want to delete this project cost?');
define('SETUP_INFO_HEADING_NEW_PROJECT_COSTS', 'New Project Cost');
define('SETUP_INFO_HEADING_EDIT_PROJECT_COSTS', 'Edit Project Cost');
define('SETUP_INFO_COST_TYPE','Cost Type');
define('SETUP_PROJECT_COSTS_LOG','Project Costs - ');
define('SETUP_PROJECT_COSTS_DELETE_ERROR','Cannot delete this project cost, it is being use in a journal entry.');
/************************** (Project Phases) ***********************************************/
define('TEXT_PROJECT_PHASES', 'Project Phases');
define('TEXT_COST_BREAKDOWN', 'Cost Breakdown');
define('TEXT_USE_COST_BREAKDOWNS_FOR_THIS_PHASE', 'Use Cost Breakdowns for this phase?');
define('SETUP_PROJECT_PHASES_INSERT_INTRO', 'Please enter the new project phase with its properties');
define('SETUP_PROJECT_PHASES_DELETE_INTRO', 'Are you sure you want to delete this project phase?');
define('SETUP_INFO_HEADING_NEW_PROJECT_PHASES', 'New Project Phase');
define('SETUP_INFO_HEADING_EDIT_PROJECT_PHASES', 'Edit Project Phase');
define('SETUP_PROJECT_PHASESS_LOG','Project Phases - ');
define('SETUP_PROJECT_PHASESS_DELETE_ERROR','Cannot delete this project phase, it is being use in a journal entry.');

?>