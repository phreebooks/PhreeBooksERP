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
//  Path: /modules/phreebooks/pages/register/pre_process.php
//
$security_level = validate_user(SECURITY_ID_ACCT_REGISTER);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');

/**************   page specific initialization  *************************/
// retrieve the current status of this periods register
$period = $_GET['search_period'] ? $_GET['search_period'] : CURRENT_ACCOUNTING_PERIOD;
if ($period == 'all') $period = CURRENT_ACCOUNTING_PERIOD; // don't allow the all option
$gl_account = isset($_POST['gl_account']) ? $_POST['gl_account'] : AR_SALES_RECEIPTS_ACCOUNT;

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/register/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  default:
}

/*****************   prepare to display templates  *************************/
// build the array of cash accounts
$result = $db->Execute("select id, description from " . TABLE_CHART_OF_ACCOUNTS. " where account_type = '0' and heading_only = '0' order by id");
$account_array = array();
while (!$result->EOF) {
  if (!$gl_account) $gl_account = $result->fields['id'];
  $text_value = $result->fields['id'] . ' : ' . $result->fields['description'];
  $account_array[] = array('id' => $result->fields['id'], 'text' => $text_value);
  $result->MoveNext();
}

// load the gl account beginning balance
$sql = "select beginning_balance from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
	where account_id = '" . $gl_account . "' and period = " . $period;
$result = $db->Execute($sql);
$beginning_balance = $result->fields['beginning_balance'];

// load the payments and deposits for the current period
$bank_list = array();
$sql = "select i.description, m.id, m.journal_id, m.post_date, m.total_amount, m.purchase_invoice_id, m.bill_primary_name, 
   i.debit_amount, i.credit_amount
   from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
   where m.period = " . $period . " and i.gl_account = '" . $gl_account . "' 
   order by m.post_date, m.journal_id";
$result = $db->Execute($sql);
while (!$result->EOF) {
  switch ($result->fields['journal_id']) {
   case  2:
   case 18:
   case 19:
   case 20:
   case 21:
     $deposit_amount    = $result->fields['debit_amount'];
     $withdrawal_amount = $result->fields['credit_amount'];
     break;
   default:
     $messageStack->add(BNK_BAD_CASH_ACCOUNT,'error');
  }
  $bank_list[] = array(
   'post_date'  => $result->fields['post_date'],
   'reference'  => $result->fields['purchase_invoice_id'],
   'dep_amount' => $deposit_amount,
   'pmt_amount' => $withdrawal_amount,
   'name'       => $result->fields['bill_primary_name'] ? $result->fields['bill_primary_name'] : substr($result->fields['description'], 0, 40));
  $result->MoveNext();
}

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_BANKING_BANK_ACCOUNT_REGISTER);

?>