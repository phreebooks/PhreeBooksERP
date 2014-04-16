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
//  Path: /modules/phreepos/ajax/other_transactions.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_PHREEPOS);
define('JOURNAL_ID',2);
/**************  include page specific files    *********************/
gen_pull_language('contacts');
gen_pull_language('phreebooks');
gen_pull_language('inventory');
gen_pull_language('phreeform');
require_once(DIR_FS_MODULES . 'inventory/defaults.php');
require_once(DIR_FS_MODULES . 'phreeform/defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
/**************   page specific initialization  *************************/
$order           = new \core\classes\journal();
define('ORD_ACCT_ID',GEN_CUSTOMER_ID);
define('GL_TYPE','sos');
define('DEF_INV_GL_ACCT',AR_DEF_GL_SALES_ACCT);
$order->gl_acct_id = AR_DEFAULT_GL_ACCT;
define('DEF_GL_ACCT_TITLE',ORD_AR_ACCOUNT);
define('POPUP_FORM_TYPE','pos:rcpt');
$account_type    = 'c';
$transaction     = new \phreepos\classes\other_transactions();
$tills           = new \phreepos\classes\tills();
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'phreepos/custom/ajax/other_transactions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
	\core\classes\user::validate_security($security_level, 2); // security check		
	$transaction->get_transaction_info($_POST['Other_trans_type']);
	$tills->get_till_info($_POST['ot_till_id']);
	// currency values (convert to DEFAULT_CURRENCY to store in db)
	$order->currencies_code     = db_prepare_input($_POST['ot_currencies_code']);
	$order->currencies_value    = db_prepare_input($_POST['ot_currencies_value']);
	// load journal main data	
	$order->journal_main_array = array(
		'period'              => CURRENT_ACCOUNTING_PERIOD,
		'journal_id'          => JOURNAL_ID,
		'post_date'           => date('Y-m-d'),
		'total_amount'        => $total_amount,
		'description'         => GL_ENTRY_TITLE,
		'bill_primary_name'   => $transaction->description,
		'purchase_invoice_id' => '',
		'admin_id'            => $_SESSION['admin_id'],
		'store_id'            => $tills->store_id,
		'total_amount'		  => ($currencies->clean_value(db_prepare_input($_POST['ot_amount']), $order->currencies_code) / $order->currencies_value),
	);
	switch($transaction->type){
		case 'cash_in':
			$credit_amount = '';
			$debit_amount  = $currencies->clean_value(db_prepare_input($_POST['ot_amount']), $order->currencies_code) / $order->currencies_value;
			$tills->adjust_balance($debit_amount);
			break;
		default:
			$debit_amount  = '';
			$credit_amount = $currencies->clean_value(db_prepare_input($_POST['ot_amount']), $order->currencies_code) / $order->currencies_value;
			$tills->adjust_balance(-$credit_amount);
	}
	$order->journal_rows[] = array(
			'id'            => '',
			'qty'           => '1',
			'gl_type'		=> 'ttl',
			'gl_account'    => $tills->gl_acct_id,
			'description'   => (db_prepare_input($_POST['ot_desc']) == '')? $transaction->description : db_prepare_input($_POST['ot_desc']),
			'debit_amount'  => $debit_amount,
			'credit_amount' => $credit_amount,
			'post_date'     => date('Y-m-d'));
	
	if($transaction->type == 'expenses'){
		$tax = $currencies->clean_value(db_prepare_input($_POST['ot_tax']), $order->currencies_code) / $order->currencies_value;
		$tax_auths      = gen_build_tax_auth_array();
		$order->journal_rows[] = array(
			'id'            => '',
			'qty'           => '1',
		    'gl_type'		=> 'tax',
			'gl_account'    => $tax_auths[$_POST['ot_rate']]['account_id'],
			'description'   => $transaction->description,
			'taxable'       => db_prepare_input($_POST['ot_rate']),
			'debit_amount'  => $tax,
			'credit_amount' => $debit_amount,
			'post_date'     => date('Y-m-d'));
		$order->journal_rows[] = array(
			'id'            => '',
			'qty'           => '1',
			'gl_type'		=> 'sos',
			'gl_account'    => $transaction->gl_acct_id,
			'description'   => $transaction->description,
			'debit_amount'  => $credit_amount - $tax,
			'credit_amount' => $debit_amount,
			'post_date'     => date('Y-m-d'));
	}else{
		$order->journal_rows[] = array(
			'id'            => '',
			'qty'           => '1',
			'gl_type'		=> 'sos',
			'gl_account'    => $transaction->gl_acct_id,
			'description'   => $transaction->description,
			'debit_amount'  => $credit_amount,
			'credit_amount' => $debit_amount,
			'post_date'     => date('Y-m-d'));
	}
	
	
	$order->Post('insert', true);
	if ( DEBUG )           $messageStack->write_debug();
						$xml .= "\t" . xmlEntry("action",			$_REQUEST['action']);
//if ($order->errormsg)	$xml .= "\t" . xmlEntry("error", 			$order->errormsg);
echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;
?>