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
//  Path: /modules/phreepos/pages/closing/pre_process.php
//
$security_level = validate_user(SECURITY_ID_POS_CLOSING);
define('JOURNAL_ID',2);
/**************  include page specific files    *********************/
gen_pull_language('phreebooks');
require_once(DIR_FS_WORKING . 'classes/tills.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
require_once(DIR_FS_MODULES . 'phreebooks/classes/banking.php');

/**************   page specific initialization  *************************/
$error			 = false;
$till_known 	 = false;
$cleared_items   = array();
$current_cleard_items = unserialize($_POST['current_cleard_items']);
$all_items       = array();
$gl_types 		 = array('pmt','ttl','tpm');
$post_date 		 = ($_POST['post_date']) ? gen_db_date($_POST['post_date']) : '';
$payment_modules = load_all_methods('payment');
$tills           = new tills();
$glEntry		 = new journal();
if(isset($_GET['till_id'])){
	$tills->get_till_info(db_prepare_input($_GET['till_id']));
	$post_date 		 = gen_db_date(gen_locale_date(date('Y-m-d')));
}else if(isset($_POST['till_id'])){
	$tills->get_till_info(db_prepare_input($_POST['till_id']));
}else if($tills->showDropDown() == false){
  	$tills->get_default_till_info();
}else {
	$post_date = '';
	$_REQUEST['action']    = '';
}
if($post_date) $period = gen_calculate_period($post_date);
foreach ($payment_modules as $pmt_class) {
	$class  = $pmt_class['id'];
	$$class = new $class;
}
$glEntry->currencies_code  = DEFAULT_CURRENCY;
$glEntry->currencies_value = 1;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/closing/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	validate_security($security_level, 2);
	$glEntry->journal_id          = JOURNAL_ID;
	$glEntry->post_date           = $post_date;
	$glEntry->period              = $period;
	$glEntry->closed 			  = ($security_level > 2) ? 1 : 0;
	$glEntry->admin_id            = $_SESSION['admin_id'];
	$glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
	$glEntry->recur_id            = db_prepare_input($_POST['recur_id']);
	$glEntry->recur_frequency     = db_prepare_input($_POST['recur_frequency']);
	$glEntry->store_id            = db_prepare_input($_POST['store_id']);
	if ($glEntry->store_id == '') $glEntry->store_id = 0;
	//save new till balance
	$tills->new_balance($currencies->clean_value($_POST['new_balance']));
	if (is_array($_POST['id'])) for ($i = 0; $i < count($_POST['id']); $i++) {
	  $all_items[] = $_POST['id'][$i];
	  $cleared_items[]   = $_POST['id'][$i];
	  $glrows[db_prepare_input($_POST['gl_account_' . $i])] += $currencies->clean_value($_POST['amt_'.$i]) - $currencies->clean_value($_POST['pmt_'.$i]);
	}
	foreach($glrows as $key => $value){
		$value = $value;
		if($value == $currencies->clean_value(0)) continue;
		$value = round($value,  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
		$balance_payments += $value;
		$glEntry->journal_rows[] = array(
			'id'            => '',
			'qty'           => '1',
			'gl_account'    => $key,
			'description'   => PHREEPOS_HANDELING_CASH_DIFFERENCE,
			'debit_amount'  => ($value > 0 ) ? $value : '',
			'credit_amount' => ($value > 0 ) ? ''     : -$value,
			'reconciled'	=> ($security_level > 2) ? $period : 0,
			'post_date'     => $glEntry->post_date);
	}
	$value = $currencies->clean_value($_POST['balance']) - $balance_payments;
	$glEntry->journal_rows[] = array(
		'id'            => '',
		'qty'           => '1',
		'gl_account'    => $tills->gl_acct_id,
		'description'   => PHREEPOS_HANDELING_CASH_DIFFERENCE,
		'debit_amount'  => ($value > 0 ) ? $value : '',
		'credit_amount' => ($value > 0 ) ? ''     : -$value,
		'reconciled'	=> ($security_level > 2) ? $period : 0,
		'post_date'     => $glEntry->post_date);
	if ($currencies->clean_value($_POST['balance'])<> 0){
		$glEntry->journal_rows[] = array(
			'id'            => '',
			'qty'           => '1',
			'gl_account'    => $tills->dif_gl_acct_id,
			'description'   => PHREEPOS_HANDELING_CASH_DIFFERENCE,
			'debit_amount'  => ($currencies->clean_value($_POST['balance']) > 0) ? '' : -$currencies->clean_value($_POST['balance']) ,
			'credit_amount' => ($currencies->clean_value($_POST['balance']) > 0) ? $currencies->clean_value($_POST['balance']) : '' ,
			'reconciled'	=> ($security_level > 2) ? $period : 0,
			'post_date'     => $glEntry->post_date);		
	}
	
	$glEntry->journal_main_array = array(
		'period'              => $glEntry->period,
		'journal_id'          => JOURNAL_ID,
		'post_date'           => $glEntry->post_date,
		'total_amount'        => $currencies->clean_value($_POST['balance']),
		'description'         => GL_ENTRY_TITLE,
		'purchase_invoice_id' => $glEntry->purchase_invoice_id,
		'admin_id'            => $glEntry->admin_id,
		'bill_primary_name'   => PHREEPOS_HANDELING_CASH_DIFFERENCE,
		'store_id'            => $glEntry->store_id,
	);
	$db->transStart();
	if (!$glEntry->Post($glEntry->id ? 'edit' : 'insert', true)) $error = true;
	if ($error) {
		$db->transRollback();
		if (DEBUG) $messageStack->write_debug();
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	}
	$db->transCommit();
	if( !$error ){
		$newrow = $db->Execute("select i.id from " . TABLE_JOURNAL_MAIN . " m join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id where i.gl_account = '" . $tills->gl_acct_id . "' and m.id ='".$glEntry->id."'");
		$cleared_items[] = $newrow->fields['id'];
		$statement_balance = $currencies->clean_value($_POST['statement_balance']);// misschien moet dit opgehaald worden ipv balans waarde van till
		// see if this is an update or new entry
		$sql_data_array = array(
		  'statement_balance' => $statement_balance,
		  'cleared_items'     => serialize(array_merge($cleared_items, $current_cleard_items)),
		);
		$sql = "select id from " . TABLE_RECONCILIATION . " where period = " . $period . " and gl_account = '" . $tills->gl_acct_id . "'";
		$result = $db->Execute($sql);
		if ($result->RecordCount() == 0) {
		  $sql_data_array['period']     = $period;
		  $sql_data_array['gl_account'] = $tills->gl_acct_id;
		  db_perform(TABLE_RECONCILIATION, $sql_data_array, 'insert');
		} else {
		  db_perform(TABLE_RECONCILIATION, $sql_data_array, 'update', "period = " . $period . " and gl_account = '" . $tills->gl_acct_id . "'");
		}
		// set reconciled flag to period for all records that were checked
		$mains = array();
		if (count($cleared_items)) {
		  $sql = "update " . TABLE_JOURNAL_ITEM . " set reconciled = $period where id in (" . implode(',', $cleared_items) . ")";
		  $result = $db->Execute($sql);
		  // check to see if the journal main closed flag should be set or cleared based on all cash accounts
		  $result = $db->Execute("select ref_id from " . TABLE_JOURNAL_ITEM . " where id in (" . implode(",", $cleared_items) . ")");
		  while (!$result->EOF) {
			$mains[] = $result->fields['ref_id'];
			$result->MoveNext();
		  }
		}
		if (count($mains)) { 
		  // closes if any cash records within the journal main that are reconciled
		  $db->Execute("update " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
		    set m.closed = '1' 
			  where i.reconciled > 0 
			  and i.gl_account = '" . $tills->gl_acct_id . "' 
			  and m.id in (" . implode(",", $mains) . ")");
		}
		$messageStack->add(BNK_RECON_POST_SUCCESS,'success');
		gen_add_audit_log(BNK_LOG_ACCT_RECON . $period, $tills->gl_acct_id);
	}
	$post_date = ''; // reset for new form
	if (DEBUG) $messageStack->write_debug();
	break;
  default:
}

/*****************   prepare to display templates  *************************/
if ($post_date){
	$bank_list = array();
	
	// load the payments and deposits that are open
	$sql = "select i.id, m.post_date, i.debit_amount, i.credit_amount, m.purchase_invoice_id, i.gl_type, i.description, m.journal_id, i.gl_account, a.description as gl_name 
		from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
		 join " . TABLE_CHART_OF_ACCOUNTS . " a on i.gl_account = a.id
		where m.gl_acct_id = '" . $tills->gl_acct_id . "' and i.reconciled = 0  and i.gl_type in ('" . implode("','", $gl_types) . "') and m.post_date = '" . $post_date . "'";
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  $previous_total = $bank_list[$result->fields['id']]['dep_amount'] - $bank_list[$result->fields['id']]['pmt_amount'];
	  $new_total      = $previous_total + $result->fields['debit_amount'] - $result->fields['credit_amount'];
	  $bank_list[$result->fields['id']] = array(
		'post_date'  => $result->fields['post_date'],
		'reference'  => $result->fields['gl_type'] == 'pmt' ? $result->fields['gl_account'] . $result->fields['currencies_code'] : TEXT_SALES,
	    'edit'		 => $result->fields['gl_type'] == 'pmt' ? '':'readonly="readonly"',
		'name'       => $result->fields['gl_type'] == 'pmt' ? $result->fields['gl_name'] : $result->fields['description'],
	    'gl_account' => $result->fields['gl_account'],
		'description'=> $result->fields['description'],
		'dep_amount' => ($new_total < 0) ? ''          : $new_total,
		'pmt_amount' => ($new_total < 0) ? -$new_total : '',
		'payment'    => ($new_total < 0) ? 1           : 0,
		'cleared'    => 0,
	  );
	  $result->MoveNext();
	}
	
	// check to see if in partial reconciliation, if so add checked items
	$sql = "select statement_balance, cleared_items from " . TABLE_RECONCILIATION . " 
		where period = " . $period . " and gl_account = '" . $tills->gl_acct_id . "'";
	$result = $db->Execute($sql);
	if ($result->RecordCount() <> 0) { // there are current cleared items in the present accounting period (edit)
	  $statement_balance = $currencies->format($result->fields['statement_balance']);
	  $cleared_items     = unserialize($result->fields['cleared_items']);
	  // load information from general ledger
	  if (count($cleared_items) > 0) {
		$sql = "select i.id, m.post_date, i.debit_amount, i.credit_amount, m.purchase_invoice_id, i.gl_type, i.description, m.journal_id, i.gl_account, a.description as gl_name
			from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
			 join " . TABLE_CHART_OF_ACCOUNTS . " a on i.gl_account = a.id
			where m.gl_acct_id = '" . $tills->gl_acct_id . "' and i.id in (" . implode(',', $cleared_items) . ") and i.gl_type in ('" . implode("','", $gl_types) . "') and m.post_date = '" . $post_date . "'";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
		  if (isset($bank_list[$result->fields['id']])) { // record exists, mark as cleared (shouldn't happen)
			$bank_list[$result->fields['id']]['cleared'] = 1;
		  } else {
			$previous_total = $bank_list[$result->fields['id']]['dep_amount'] - $bank_list[$result->fields['id']]['pmt_amount'];
			$new_total      = $previous_total + $result->fields['debit_amount'] - $result->fields['credit_amount'];
			$bank_list[$result->fields['id']] = array (
			  'post_date'  => $result->fields['post_date'],
			  'reference'  => $result->fields['gl_type'] == 'pmt' ? $result->fields['gl_account'] . $result->fields['currencies_code'] : TEXT_SALES,
			  'edit'	   => $result->fields['gl_type'] == 'pmt' ? '':'readonly="readonly"',
			  'name'       => $result->fields['gl_type'] == 'pmt' ? $result->fields['gl_name'] : $result->fields['description'],
			  'gl_account' => $result->fields['gl_account'],
			  'description'=> $result->fields['description'],
			  'dep_amount' => ($new_total < 0) ? ''          : $new_total,
			  'pmt_amount' => ($new_total < 0) ? -$new_total : '',
			  'payment'    => ($new_total < 0) ? 1           : 0,
			  'cleared'    => 1,
			);
		  }
		  $result->MoveNext();
		} 
	  }
	}
	
	// combine by reference number
	$combined_list = array();
	if (is_array($bank_list)) foreach ($bank_list as $id => $value) {
	//	$index = ($value['payment'] ? 'p_' : 'd_') . $value['reference']; // this will separate deposits from payments with the same referenece 
		$index = $value['reference'];
		if (isset($combined_list[$index])) { // the reference already exists
			$combined_list[$index]['dep_amount'] += $value['dep_amount'];
			$combined_list[$index]['pmt_amount'] += $value['pmt_amount'];
			$combined_list[$index]['name']        = $value['name'];
			if ( ($combined_list[$index]['cleared'] && !$value['cleared'])  ||
			    (!$combined_list[$index]['cleared'] &&  $value['cleared'])) {
			  $combined_list[$index]['cleared'] = 0; // uncheck summary box
			  $combined_list[$index]['partial'] = true; // part of the group is cleared, flag warning
			}
		} else {
			$combined_list[$index]['dep_amount']  = $value['dep_amount'];
			$combined_list[$index]['pmt_amount']  = $value['pmt_amount'];
			$combined_list[$index]['name']        = $value['name'];
			$combined_list[$index]['cleared']     = $value['cleared'];
		}
		// How about the name=description rather than source for sub-items?
		$combined_list[$index]['detail'][]  = array(
			'id'         => $id, 
			'post_date'  => $value['post_date'],
			//'name'       => $value['name'],
			//'description'=> $value['description'],
			'name'		 => $value['description'],
			'dep_amount' => $value['dep_amount'],
			'pmt_amount' => $value['pmt_amount'],
			'payment'    => $value['payment'] ? -$value['pmt_amount'] : $value['dep_amount'],
			'cleared'    => $value['cleared'],
			'edit'    	 => $value['edit'],
			'gl_account' => $value['gl_account'],
		);
		$combined_list[$index]['post_date']  = $value['post_date'];
		$combined_list[$index]['reference']  = $value['reference'];
		$combined_list[$index]['edit']       = $value['edit'];
		$combined_list[$index]['gl_account'] = $value['gl_account'];
	}
	
	// sort by user choice for display
	$sort_value = explode('-',$_GET['list_order']);
	switch ($sort_value[0]) {
		case 'dep_amount': define('RECON_SORT_KEY','dep_amount'); break;
		case 'pmt_amount': define('RECON_SORT_KEY','pmt_amount'); break;
		case 'post_date':  define('RECON_SORT_KEY','post_date');  break;
		default:
		case 'reference':  define('RECON_SORT_KEY','reference');  break;
	}
	define('RECON_SORT_DESC', isset($sort_value[1]) ? true : false);
	function my_sort($a, $b) {
	    if ($a[RECON_SORT_KEY] == $b[RECON_SORT_KEY]) return 0;
		if (RECON_SORT_DESC) {
	    	return ($a[RECON_SORT_KEY] > $b[RECON_SORT_KEY]) ? -1 : 1;
		} else {
	    	return ($a[RECON_SORT_KEY] < $b[RECON_SORT_KEY]) ? -1 : 1;
		}
	}
	usort($combined_list, "my_sort");
	
	// load the end balance
	$till_balance = $currencies->format($tills->balance);
	if (empty($combined_list) && $tills->till_id <> '' ) $messageStack->add('No Items were found for till and period.!','warning');
}

$cal_gl = array(
  'name'      => 'datePost',
  'form'      => 'closingpos',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => ($post_date == '')? gen_locale_date(date('Y-m-d')) :gen_locale_date($post_date),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_POS_CLOSING);

?>