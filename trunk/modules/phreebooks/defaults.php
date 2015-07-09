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
//  Path: /modules/phreebooks/defaults.php
//
// default directory for order attachments
define('PHREEBOOKS_DIR_MY_ORDERS',  DIR_FS_MY_FILES . $_SESSION['company'] . '/phreebooks/orders/');
// default for sorting on PAyBIlls and CUstomer Receipts listings
define('PHREEBOOKS_DEFAULT_BILL_SORT','invoice'); // choices are 'invoice', 'due_date'

/************* DO NOT EDIT BELOW THIS LINE ************************/
// Chart of accounts types
$coa_types_list = array(
  '0'  => array('id' =>  0, 'text' => TEXT_CASH,									'asset' => true),  // Cash
  '2'  => array('id' =>  2, 'text' => TEXT_ACCOUNTS_RECEIVABLE,						'asset' => true),  // Accounts Receivable
  '4'  => array('id' =>  4, 'text' => TEXT_INVENTORY,								'asset' => true),  // Inventory
  '6'  => array('id' =>  6, 'text' => TEXT_OTHER_CURRENT_ASSETS,	 				'asset' => true),  // Other Current Assets
  '8'  => array('id' =>  8, 'text' => TEXT_FIXED_ASSETS,							'asset' => true),  // Fixed Assets
  '10' => array('id' => 10, 'text' => TEXT_ACCUMULATED_DEPRECIATION,				'asset' => false), // Accumulated Depreciation
  '12' => array('id' => 12, 'text' => TEXT_OTHER_ASSETS,							'asset' => true),  // Other Assets 
  '14' => array('id' => 14, 'text' => TEXT_SUSPENSE,								'asset' => true),  // suspense
  '20' => array('id' => 20, 'text' => TEXT_ACCOUNTS_PAYABLE,						'asset' => false), // Accounts Payable
  '22' => array('id' => 22, 'text' => TEXT_OTHER_CURRENT_LIABILITIES,				'asset' => false), // Other Current Liabilities
  '24' => array('id' => 24, 'text' => TEXT_LONG_TERM_LIABILITIES,					'asset' => false), // Long Term Liabilities
  '30' => array('id' => 30, 'text' => TEXT_INCOME,									'asset' => false), // Income
  '32' => array('id' => 32, 'text' => TEXT_COST_OF_SALES,							'asset' => true),  // Cost of Sales
  '34' => array('id' => 34, 'text' => TEXT_EXPENSES,								'asset' => true),  // Expenses
  '40' => array('id' => 40, 'text' => TEXT_EQUITY . ' - ' . TEXT_DOESNT_CLOSE,		'asset' => false), // Equity - Doesn\'t Close
  '42' => array('id' => 42, 'text' => TEXT_EQUITY . ' - ' . TEXT_GETS_CLOSED,		'asset' => false), // Equity - Gets Closed
  '44' => array('id' => 44, 'text' => TEXT_EQUITY . ' - ' . TEXT_RETAINED_EARNINGS,	'asset' => false), // Equity - Retained Earnings
);

$journal_types_list = array(
		'0'  => array('id' => 00, 'text' => TEXT_BEGINNING_BALANCES,			'id_field_name' => TEXT_REFERENCE),			//@todo
		'2'  => array('id' => 02, 'text' => TEXT_GENERAL_JOURNAL,				'id_field_name' => TEXT_REFERENCE),			//@todo
		'3'  => array('id' => 03, 'text' => TEXT_PURCHASE_QUOTES,				'id_field_name' => TEXT_QUOTE_NUMBER),
		'4'  => array('id' => 04, 'text' => TEXT_PURCHASE_ORDERS,				'id_field_name' => TEXT_PO_NUMBER),
		'6'  => array('id' => 06, 'text' => TEXT_PURCHASE_OR_RECEIVE_INVENTORY,	'id_field_name' => TEXT_INVOICE),
		'7'  => array('id' => 07, 'text' => TEXT_VENDOR_CREDIT_MEMOS,			'id_field_name' => TEXT_CREDIT_MEMO),
		'8'  => array('id' => 08, 'text' => TEXT_PAYROLL,						'id_field_name' => TEXT_PAYROL_NUMBER),
		'9'  => array('id' => 09, 'text' => TEXT_SALES_QUOTES,					'id_field_name' => TEXT_QUOTE_NUMBER),
		'10' => array('id' => 10, 'text' => TEXT_SALES_ORDERS,					'id_field_name' => TEXT_SO_NUMBER),
		'12' => array('id' => 12, 'text' => TEXT_SALES_INVOICES,				'id_field_name' => TEXT_INVOICE),			//@todo
		'13' => array('id' => 13, 'text' => TEXT_CUSTOMER_CREDIT_MEMOS,			'id_field_name' => TEXT_CREDIT_MEMO),
		'14' => array('id' => 14, 'text' => TEXT_INVENTORY_ASSEMBLY,			'id_field_name' => TEXT_ASSEMBLY_NUMBER),
		'16' => array('id' => 16, 'text' => TEXT_INVENTORY_ADJUSTMENTS,			'id_field_name' => TEXT_ADJUSTMENT_NUMBER),
		'18' => array('id' => 18, 'text' => TEXT_VENDOR_REFUNDS,				'id_field_name' => TEXT_RECEIPT_NUMBER), 	// @todo dit alleen nog maar leverancier restituties nr 22 is klant betalingen.
		'19' => array('id' => 19, 'text' => TEXT_POINT_OF_SALE,					'id_field_name' => TEXT_RECEIPT),
		'20' => array('id' => 20, 'text' => TEXT_VENDOR_PAYMENTS,				'id_field_name' => TEXT_PAYMENT_NUMBER), 	//@todo dit alleen nog maar leverancier betalingen nr 23 is klant restitutie.
		'21' => array('id' => 21, 'text' => TEXT_POINT_OF_PURCHASE,				'id_field_name' => TEXT_RECEIPT),
		'22' => array('id' => 22, 'text' => TEXT_CUSTOMER_PAYMENTS,				'id_field_name' => TEXT_PAYMENT_NUMBER), 	//@todo dit alleen nog maar klant betalingen nr 18 is leverancier restituties.
		'23' => array('id' => 23, 'text' => TEXT_CUSTOMER_REFUNDS,				'id_field_name' => TEXT_RECEIPT_NUMBER), 	// @todo dit alleen nog maar klant restituties nr 20 is leverancier betalingen.

);

?>