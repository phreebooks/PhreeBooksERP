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
//  Path: /modules/phreebooks/pages/journal/pre_process.php
//
$security_level = validate_user(SECURITY_ID_JOURNAL_ENTRY);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'classes/gen_ledger.php');
/**************   page specific initialization  *************************/
define('JOURNAL_ID',2);	// General Journal
$error     = false;
$post_date = ($_POST['post_date']) ? gen_db_date($_POST['post_date']) : date('Y-m-d', time());
$period    = gen_calculate_period($post_date);
$glEntry   = new journal();
$glEntry->id = ($_POST['id'] <> '') ? $_POST['id'] : ''; // will be null unless opening an existing gl entry
// All general journal entries are in the default currency.
$glEntry->currencies_code  = DEFAULT_CURRENCY;
$glEntry->currencies_value = 1;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/journal/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  case 'copy':
	validate_security($security_level, 2);
    // for copy operation, erase the id to force post a new journal entry with same values
	if ($_REQUEST['action'] == 'copy') $glEntry->id = '';
	$glEntry->journal_id          = JOURNAL_ID;
	$glEntry->post_date           = $post_date;
	$glEntry->period              = $period;
	$glEntry->admin_id            = $_SESSION['admin_id'];
	$glEntry->purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
	$glEntry->recur_id            = db_prepare_input($_POST['recur_id']);
	$glEntry->recur_frequency     = db_prepare_input($_POST['recur_frequency']);
	$glEntry->store_id            = db_prepare_input($_POST['store_id']);
	$glEntry->rm_attach           = isset($_POST['rm_attach']) ? true : false;
	if ($glEntry->store_id == '') $glEntry->store_id = 0;

	// process the request, build main record
	$x = 1;
	$total_amount = 0;
	$journal_entry_desc = GL_ENTRY_TITLE;
	while (isset($_POST['acct_' . $x])) { // while there are gl rows to read in
		if (!$_POST['debit_' . $x] && !$_POST['credit_' . $x]) { // skip blank rows
			$x++;
			continue;
		}
		$debit_amount  = ($_POST['debit_' . $x]) ? $currencies->clean_value($_POST['debit_' . $x]) : 0;
		$credit_amount = ($_POST['credit_'. $x]) ? $currencies->clean_value($_POST['credit_'. $x]) : 0;
		$glEntry->journal_rows[] = array(
			'id'            => ($_REQUEST['action'] == 'copy') ? '' : db_prepare_input($_POST['id_' . $x]),
			'qty'           => '1',
			'gl_account'    => db_prepare_input($_POST['acct_' . $x]),
			'description'   => db_prepare_input($_POST['desc_' . $x]),
			'debit_amount'  => $debit_amount,
			'credit_amount' => $credit_amount,
			'post_date'     => $glEntry->post_date);
		$total_amount += $debit_amount;
		if ($x == 1) $journal_entry_desc = db_prepare_input($_POST['desc_' . $x]);
		$x++;
	}

	$glEntry->journal_main_array = array(
		'id'                  => $glEntry->id,
		'period'              => $glEntry->period,
		'journal_id'          => JOURNAL_ID,
		'post_date'           => $glEntry->post_date,
		'total_amount'        => $total_amount,
		'description'         => GL_ENTRY_TITLE,
		'purchase_invoice_id' => $glEntry->purchase_invoice_id,
		'currencies_code'     => DEFAULT_CURRENCY,
		'currencies_value'    => 1,
		'admin_id'            => $glEntry->admin_id,
		'bill_primary_name'   => $journal_entry_desc,
		'recur_id'            => $glEntry->recur_id,
		'store_id'            => $glEntry->store_id,
	);

	// check for errors and prepare extra values
	if (!$glEntry->period) $error = true;	// bad post_date was submitted

	if (!$glEntry->journal_rows) { // no rows entered
		$messageStack->add(GL_ERROR_NO_ITEMS, 'error');
		$error = true;
	}
	// finished checking errors

	if (!$error) {
		// *************** START TRANSACTION *************************
		$db->transStart();
		if ($glEntry->recur_id > 0) { // if new record, will contain count, if edit will contain recur_id
			$first_id                  = $glEntry->id;
			$first_post_date           = $glEntry->post_date;
			$first_purchase_invoice_id = $glEntry->purchase_invoice_id;
			if ($glEntry->id) { // it's an edit, fetch list of affected records to update if roll is enabled
				$affected_ids = $glEntry->get_recur_ids($glEntry->recur_id, $glEntry->id);
				for ($i = 0; $i < count($affected_ids); $i++) {
					$glEntry->id                       = $affected_ids[$i]['id'];
					$glEntry->journal_main_array['id'] = $affected_ids[$i]['id'];
					if ($i > 0) { // Remove row id's for future posts, keep if re-posting single entry
					  for ($j = 0; $j < count($glEntry->journal_rows); $j++) {
					    $glEntry->journal_rows[$j]['id'] = '';
					  }
					  $glEntry->post_date                     = $affected_ids[$i]['post_date'];
					}
					$glEntry->period                          = gen_calculate_period($glEntry->post_date, true);
					$glEntry->journal_main_array['post_date'] = $glEntry->post_date;
					$glEntry->journal_main_array['period']    = $glEntry->period;
					$glEntry->purchase_invoice_id             = $affected_ids[$i]['purchase_invoice_id'];
					if (!$glEntry->validate_purchase_invoice_id()) {
					  $error = true;
					  break;
					} else if (!$glEntry->Post('edit')) {
					  $error = true;
					  break;
					}
					// test for single post versus rolling into future posts, terminate loop if single post
					if (!$glEntry->recur_frequency) break;
				}
			} else { // it's an insert
				// fetch the next recur id
				$glEntry->journal_main_array['recur_id'] = time();
				$day_offset   = 0;
				$month_offset = 0;
				$year_offset  = 0;
				for ($i = 0; $i < $glEntry->recur_id; $i++) {
					if (!$glEntry->validate_purchase_invoice_id()) {
					  $error = true;
					  break;
					} else if (!$glEntry->Post('insert')) {
					  $error = true;
					  break;
					}
					$glEntry->id = '';
					$glEntry->journal_main_array['id'] = $glEntry->id;
					for ($j = 0; $j < count($glEntry->journal_rows); $j++) $glEntry->journal_rows[$j]['id'] = '';
					switch ($glEntry->recur_frequency) {
						default:
						case '1': $day_offset   = ($i+1)*7;  break; // Weekly
						case '2': $day_offset   = ($i+1)*14; break; // Bi-weekly
						case '3': $month_offset = ($i+1)*1;  break; // Monthly
						case '4': $month_offset = ($i+1)*3;  break; // Quarterly
						case '5': $year_offset  = ($i+1)*1;  break; // Yearly
					}
					$glEntry->post_date = gen_specific_date($post_date, $day_offset, $month_offset, $year_offset);
					$glEntry->period = gen_calculate_period($glEntry->post_date, true);
					if (!$glEntry->period && $i < ($glEntry->recur_id - 1)) { // recur falls outside of available periods, ignore last calculation
					  $messageStack->add(ORD_PAST_LAST_PERIOD,'error');
					  $error = true;
					  break;
					}
					$glEntry->journal_main_array['post_date'] = $glEntry->post_date;
					$glEntry->journal_main_array['period'] = $glEntry->period;
					$glEntry->purchase_invoice_id = string_increment($glEntry->journal_main_array['purchase_invoice_id']);
				}
			}
			// restore the first values to continue with post process
			$glEntry->id                                        = $first_id;
			$glEntry->journal_main_array['id']                  = $first_id;
			$glEntry->post_date                                 = $first_post_date;
			$glEntry->journal_main_array['post_date']           = $first_post_date;
			$glEntry->purchase_invoice_id                       = $first_purchase_invoice_id;
			$glEntry->journal_main_array['purchase_invoice_id'] = $first_purchase_invoice_id;
		} else {
			if      (!$glEntry->validate_purchase_invoice_id())         $error = true;
			else if (!$glEntry->Post($glEntry->id ? 'edit' : 'insert')) $error = true;
		}
		if (!$error) {
		  $db->transCommit();
		  if ($glEntry->rm_attach) @unlink(PHREEBOOKS_DIR_MY_ORDERS . 'order_'.$glEntry->id.'.zip');
		  if (is_uploaded_file($_FILES['file_name']['tmp_name'])) {
			$messageStack->debug('Saving file to: '.PHREEBOOKS_DIR_MY_ORDERS.'order_'.$glEntry->id.'.zip');
		  	saveUploadZip('file_name', PHREEBOOKS_DIR_MY_ORDERS, 'order_'.$glEntry->id.'.zip');
		  }
		  if (DEBUG) $messageStack->write_debug();
		  gen_add_audit_log(GL_LOG_ADD_JOURNAL . (($glEntry->id) ? TEXT_EDIT : TEXT_ADD), $glEntry->purchase_invoice_id);
		  gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		}
		// *************** END TRANSACTION *************************
	}
	$db->transRollback();
	$messageStack->add(GL_ERROR_NO_POST, 'error');
    if (DEBUG) $messageStack->write_debug();
	$cInfo = new objectInfo($_POST); // if we are here, there was an error, reload page
	$cInfo->post_date = gen_db_date($_POST['post_date']);
	break;

  case 'delete':
	validate_security($security_level, 4);
  	// check for errors and prepare extra values
	if (!$glEntry->id) {
		$error = true;
	} else {
		$delGL = new journal();
		$delGL->journal($glEntry->id); // load the posted record based on the id submitted
		$recur_id        = db_prepare_input($_POST['recur_id']);
		$recur_frequency = db_prepare_input($_POST['recur_frequency']);
		// *************** START TRANSACTION *************************
		$db->transStart();
		if ($recur_id > 0) { // will contain recur_id
			$affected_ids = $delGL->get_recur_ids($recur_id, $delGL->id);
			for ($i = 0; $i < count($affected_ids); $i++) {
				$delGL->id = $affected_ids[$i]['id'];
				$delGL->journal($delGL->id); // load the posted record based on the id submitted
				if (!$delGL->unPost('delete')) {
				  $error = true;
				  break;
				}
				// test for single post versus rolling into future posts, terminate loop if single post
				if (!$recur_frequency) break;
			}
		} else {
			if (!$delGL->unPost('delete')) $error = true;
		}

		if (!$error) {
			$db->transCommit(); // if not successful rollback will already have been performed
			if (DEBUG) $messageStack->write_debug();
			gen_add_audit_log(GL_LOG_ADD_JOURNAL . TEXT_DELETE, $delGL->purchase_invoice_id);
			gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		} // *************** END TRANSACTION *************************
	}
	$db->transRollback();
	$messageStack->add(GL_ERROR_NO_DELETE, 'error');
    if (DEBUG) $messageStack->write_debug();
	$cInfo = new objectInfo($_POST); // if we are here, there was an error, reload page
	$cInfo->post_date = gen_db_date($_POST['post_date']);
	break;

  case 'edit':
    $oID = (int)$_GET['oID'];
	validate_security($security_level, 2);
   	$cInfo = new objectInfo(array());
	break;

  case 'dn_attach':
	$oID = db_prepare_input($_POST['id']);
	if (file_exists(PHREEBOOKS_DIR_MY_ORDERS . 'order_' . $oID . '.zip')) {
		require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
		$backup = new backup();
		$backup->download(PHREEBOOKS_DIR_MY_ORDERS, 'order_' . $oID . '.zip', true);
	}
	die;
  default:
}

/*****************   prepare to display templates  *************************/
// retrieve the list of gl accounts and fill js arrays
$gl_array_list = gen_coa_pull_down();
$i = 0;
$js_gl_array = 'var js_gl_array = new Array();' . chr(10);
foreach ($gl_array_list as $account) {
  $is_asset = $coa_types_list[$account['type']]['asset'] ? '1' : '0';
  $js_gl_array .= 'js_gl_array['.$i.'] = new glProperties("'.$account['id'].'", "'.$account['text'].'", "'.$is_asset.'");' . chr(10);
  $i++;
}

$cal_gl = array(
  'name'      => 'datePost',
  'form'      => 'journal',
  'fieldname' => 'post_date',
  'imagename' => 'btn_date_1',
  'default'   => gen_locale_date($post_date),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', GL_ENTRY_TITLE);

?>