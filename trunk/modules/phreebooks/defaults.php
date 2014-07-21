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
//  Path: /modules/phreebooks/defaults.php
//
// default directory for order attachments
define('PHREEBOOKS_DIR_MY_ORDERS',  DIR_FS_MY_FILES . $_SESSION['company'] . '/phreebooks/orders/');
// default for sorting on PAyBIlls and CUstomer Receipts listings
define('PHREEBOOKS_DEFAULT_BILL_SORT','invoice'); // choices are 'invoice', 'due_date'

/************* DO NOT EDIT BELOW THIS LINE ************************/
// Chart of accounts types
$coa_types_list = array(
  '0'  => array('id' =>  0, 'text' => COA_00_DESC, 'asset' => true),  // Cash
  '2'  => array('id' =>  2, 'text' => COA_02_DESC, 'asset' => true),  // Accounts Receivable
  '4'  => array('id' =>  4, 'text' => COA_04_DESC, 'asset' => true),  // Inventory
  '6'  => array('id' =>  6, 'text' => COA_06_DESC, 'asset' => true),  // Other Current Assets
  '8'  => array('id' =>  8, 'text' => COA_08_DESC, 'asset' => true),  // Fixed Assets
  '10' => array('id' => 10, 'text' => COA_10_DESC, 'asset' => false), // Accumulated Depreciation
  '12' => array('id' => 12, 'text' => COA_12_DESC, 'asset' => true),  // Other Assets
  '20' => array('id' => 20, 'text' => COA_20_DESC, 'asset' => false), // Accounts Payable
  '22' => array('id' => 22, 'text' => COA_22_DESC, 'asset' => false), // Other Current Liabilities
  '24' => array('id' => 24, 'text' => COA_24_DESC, 'asset' => false), // Long Term Liabilities
  '30' => array('id' => 30, 'text' => COA_30_DESC, 'asset' => false), // Income
  '32' => array('id' => 32, 'text' => COA_32_DESC, 'asset' => true),  // Cost of Sales
  '34' => array('id' => 34, 'text' => COA_34_DESC, 'asset' => true),  // Expenses
  '40' => array('id' => 40, 'text' => COA_40_DESC, 'asset' => false), // Equity - Doesn\'t Close
  '42' => array('id' => 42, 'text' => COA_42_DESC, 'asset' => false), // Equity - Gets Closed
  '44' => array('id' => 44, 'text' => COA_44_DESC, 'asset' => false), // Equity - Retained Earnings
);

?>