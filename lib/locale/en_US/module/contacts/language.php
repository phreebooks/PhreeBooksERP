<?php
/*
 * Language translation for Contacts module
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-12-19
 * @filesource /locale/en_US/module/contacts/language.php
 */

$lang = [
    'title' => 'Contacts',
    'description' => 'The contacts module manages all customer, vendors, employees, and branches used in the Bizuno Business Toolkit. <b>NOTE: This is a core module and cannot be removed!</b>',
    // Settings
    'primary_name_lbl' => 'Require Primary Name',
      'primary_name_tip' => 'Require Primary Name when creating a new address. For all required fields, posts will not be allowed unless there is data in the field. The exception is shipping addresses and customer/vendor contacts entered in the Customer/Vendor Manager screens.',
    'address1_lbl' => 'Require Address 1',
    'city_lbl' => 'Require City/Town',
    'state_lbl' => 'Require State/Province',
    'postal_code_lbl' => 'Require Postal Code',
    'telephone1_lbl' => 'Require Telephone 1',
    'email_lbl' => 'Require Email',
    // Titles
    'contacts_merge' => 'Merge Contacts',
    'sales_by_month' => 'Sales by Month',
    'purchases_by_month' => 'Purchases by Month',
    // Messages
    'msg_contacts_merge_src' => '<h4>Merge Contacts</h4>Select a contact as the source contact. This contact will be removed after the merge:', 
    'msg_contacts_merge_dest' => 'Select a contact as the destination contact. This contact will remain after the merge:', 
    // Error Messages
    'err_contacts_delete' => 'This record cannot be deleted because there are journal entries involving this contact. Try setting to Inactive.',
    'err_contacts_delete_address' => 'The address cannot be deleted since it is a main address, delete the contact instead!',
    // CRM Defines
    'crm_dg_notes' => 'To enter a valid Contacts entry, the Contact ID and Name/Business must be present. If either is left blank, the record will not be saved.',
    'contacts_crm_new_call' =>'New Call',
    'contacts_crm_call_back' =>'Return Call',
    'contacts_crm_follow_up' =>'Follow Up',
    'contacts_crm_new_lead' =>'New Lead',
    // API
    'conapi_desc' => 'The Contacts API currently supports the base contacts table, one main address and one shipping address for both inserts and updates. Extra custom fields are supported. To import an contacts file:<br>1. Download the contacts template which lists the field headers and descriptions.<br>2. Add your data to your .csv file.<br>3. Select the file and press the import icon.<br>The results will be displayed after the script completes. Any errors will also be displayed.',
    'conapi_template' => 'Step 1: Download the contacts template => ',
    'conapi_import' => 'Step 2: Add your contacts to the template, browse to select the file and press Import. ',
    'conapi_export' => 'OPTIONAL: Export your contacts database table in .csv format for backup => ',
    // Tools
    'close_j9_title' => 'Bulk Close Customer Quotes',
    'close_j9_desc' => 'This tool closes all Customer Quotes prior to the date specified. ',
    'close_j9_label' => 'Close all Customer Quotes Before: ',
    'close_j9_success' => 'The number of journal entries closed was: %s',
    'sync_attach_title' => 'Remove/Repair Attachments',
    'sync_attach_desc' => 'Clean orphaned attachments and repair attachment flag.',
];
