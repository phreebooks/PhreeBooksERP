<?php 
/*
 * PhreeBooks support functions
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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-04-19
 * @filesource /lib/controller/module/phreebooks/functions.php
 */

namespace bizuno;

/**
 * Creates a drop down ready list of choices for bands used in the gl search 
 * @return array - ready for a select DOM element
 */
function selChoices()
{
	return [
        ['id'=>'all', 'text'=>lang('all')],
		['id'=>'band','text'=>lang('range')],
		['id'=>'eq',  'text'=>lang('equal')],
		['id'=>'not', 'text'=>lang('not_equal')],
		['id'=>'inc', 'text'=>lang('contains')]];
}

/**
 * Creates a list of journals to use in a select DOM element
 * @return type
 */
function selGLTypes()
{
	return [
         '0'=>  ['id'=> 0, 'text'=>lang('gl_acct_type_0'), 'asset'=>true],  // Cash
		 '2'=>  ['id'=> 2, 'text'=>lang('gl_acct_type_2'), 'asset'=>true],  // Accounts Receivable
		 '4'=>  ['id'=> 4, 'text'=>lang('gl_acct_type_4'), 'asset'=>true],  // Inventory
		 '6'=>  ['id'=> 6, 'text'=>lang('gl_acct_type_6'), 'asset'=>true],  // Other Current Assets
		 '8'=>  ['id'=> 8, 'text'=>lang('gl_acct_type_8'), 'asset'=>true],  // Fixed Assets
		'10'=>  ['id'=>10, 'text'=>lang('gl_acct_type_10'),'asset'=>false], // Accumulated Depreciation
		'12'=>  ['id'=>12, 'text'=>lang('gl_acct_type_12'),'asset'=>true],  // Other Assets
		'20'=>  ['id'=>20, 'text'=>lang('gl_acct_type_20'),'asset'=>false], // Accounts Payable
		'22'=>  ['id'=>22, 'text'=>lang('gl_acct_type_22'),'asset'=>false], // Other Current Liabilities
		'24'=>  ['id'=>24, 'text'=>lang('gl_acct_type_24'),'asset'=>false], // Long Term Liabilities
		'30'=>  ['id'=>30, 'text'=>lang('gl_acct_type_30'),'asset'=>false], // Income
		'32'=>  ['id'=>32, 'text'=>lang('gl_acct_type_32'),'asset'=>true],  // Cost of Sales
		'34'=>  ['id'=>34, 'text'=>lang('gl_acct_type_34'),'asset'=>true],  // Expenses
		'40'=>  ['id'=>40, 'text'=>lang('gl_acct_type_40'),'asset'=>false], // Equity - Doesn't Close
		'42'=>  ['id'=>42, 'text'=>lang('gl_acct_type_42'),'asset'=>false], // Equity - Gets Closed
		'44'=>  ['id'=>44, 'text'=>lang('gl_acct_type_44'),'asset'=>false]]; // Equity - Retained Earnings
}

/**
 * Processes a value by format, used in PhreeForm
 * @global array $report - report structure
 * @param mixed $value - value to process
 * @param type $format - what to do with the value
 * @return mixed, returns $value if no formats match otherwise the formatted value
 */
function processPhreeBooks($value, $format = '')
{
	global $report;
	switch ($format) {
        // *********** Statement Processing ***************
		case 'age_00': 
		case 'age_30': 
		case 'age_60': 
		case 'age_90': 
		case 'begBal': 
		case 'endBal':
			if (isset($report->datedefault) && !isset($report->currentValues['aging'])) {
				$dates = explode(":", $report->datedefault); // encoded dates, type:start:end
				$report->currentValues['aging'] = calculate_aging(clean($value, 'integer'), $dates[1], $dates[2]);
				$report->currentValues['aging']['curBal'] = $report->currentValues['aging']['beg_bal']; // set the current balance
			}
            if ($format=='age_00') { return $report->currentValues['aging']['balance_0']; } // aging for level 1
            if ($format=='age_30') { return $report->currentValues['aging']['balance_30']; }// aging for level 2
            if ($format=='age_60') { return $report->currentValues['aging']['balance_60']; }// aging for level 3
            if ($format=='age_90') { return $report->currentValues['aging']['balance_90']; }// aging for level 4
            if ($format=='begBal') { return $report->currentValues['aging']['beg_bal']; }   // beginning balance
            if ($format=='endBal') { return $report->currentValues['aging']['end_bal']; }   // ending balance
			break;
        // ************ Bank Processing *******************
        case 'bnkReg':
            $rID = intval($value);
			$main = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['journal_id', 'total_amount'], "id=$rID");
			return in_array($main['journal_id'], [7,13,18,19,20,21]) ? -$main['total_amount'] : $main['total_amount'];
        // ************ Income Statement Processing *******************
		case 'isCur':  return $report->currentValues['amount'];       // income_statement current period
		case 'isYtd':  return $report->currentValues['amount_ytd'];   // income_statement year to date
		case 'isBdgt': return $report->currentValues['budget'];       // income_statement budget current period
		case 'isBytd': return $report->currentValues['budget_ytd'];   // income_statement budget year to date
		case 'isLcur': return $report->currentValues['ly_amount'];    // income_statement last year current period
		case 'isLytd': return $report->currentValues['ly_amount_ytd'];// income_statement last year to date
		case 'isLBgt': return $report->currentValues['ly_budget'];    // income_statement last year budget current period
		case 'isLBtd': return $report->currentValues['ly_budget_ytd'];// income_statement last year budget year to date
        // ************ Invoice Processing *******************
		case 'invBalance': // needs journal_main.id
            $rID = intval($value);
            if (!$rID) { return ''; }
			$main = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['journal_id', 'total_amount'], "id='$rID'");
			$jID  = $main['journal_id']; 
			$total_inv = in_array($jID, [6,13]) ? -$main['total_amount'] : $main['total_amount'];
			$total_paid= 0;
			$result = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "item_ref_id='$rID' AND gl_type='pmt'");
			foreach ($result as $row) {
                if (in_array($jID, [6,13])) { $total_paid += $row['debit_amount'] - $row['credit_amount']; }
                else { $total_paid += $row['credit_amount'] - $row['debit_amount']; }
			}
			return $total_inv + (in_array($jID, [6,13]) ? $total_paid : -$total_paid);
		case 'invRefNum': // needs journal_main.id
            $rID = intval($value);
			return dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'invoice_num', "id=$rID");
        case 'invUnit':
            $rID = intval($value);
            if (!$rID) { return ''; }
            $row =  dbGetValue(BIZUNO_DB_PREFIX.'journal_item', ['qty','credit_amount','debit_amount'], "id=$rID");
            return !empty($row['qty']) ? ($row['credit_amount'] + $row['debit_amount'])/$row['qty'] : 0;
        case 'paymentDue': // needs journal_main.id
            $rID  = clean($value, 'integer');
            if (!$rID) { return ''; }
			$row  = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['journal_id','total_amount','post_date','terms'], "id=$rID");
            $type = in_array($row['journal_id'], [3,4,6,7]) ? 'v' : 'c';
            $dates= localeDueDate($row['post_date'], $row['terms'], $type);
            $discount = $row['post_date'] <= $dates['early_date'] ? roundAmount($dates['discount'] * $row['total_amount']) : 0;
            if ($format == 'pmtDisc') { return $discount; }
            return $row['total_amount'] - $discount;
		case 'paymentRcv': // needs journal_main.id
            $rID   = clean($value, 'integer');
            if (!$rID) { return ''; }
            $jID   = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'journal_id', "id=$rID");
			$result= dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "item_ref_id=$rID AND gl_type IN ('dsc','pmt')");
			$total_paid = 0;
            foreach ($result as $row) { $total_paid += $row['credit_amount'] - $row['debit_amount']; }
			return in_array($jID, [6,13]) ? -$total_paid : $total_paid;
        case 'paymentRef': // gets the payment transaction code, needs journal_main.id
            $invID = clean($value, 'integer');
			$pmtID = dbGetValue(BIZUNO_DB_PREFIX.'journal_item', 'ref_id', "item_ref_id=$invID");
            if ($pmtID) { return dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'trans_code', "ref_id=$pmtID AND gl_type='ttl'"); }
            else        { return ''; }
        case 'pmtDate': // needs journal_main.id
            $rID   = clean($value, 'integer');
			$result= dbGetValue(BIZUNO_DB_PREFIX.'journal_main', ['post_date','journal_id','terms'], "id=$rID");
            if (!in_array($result['journal_id'], ['3','4','6','7','9','10','12','13'])) { return ''; }
			$temp  = localeDueDate($result['post_date'], $result['terms']);
			return $temp['net_date'];
        case 'pmtDisc': return 'TBD';
 		case 'ship_bal': // pass table journal_item.id and check for quantites remaining to be shipped
            msgDebug("\nEntering ship_bal with value = $value");
			$refID = clean($value, 'integer');
            if (!$refID) { return 0; }
            $qtySO = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'qty', "id=$refID");
			if ($qtySO) {
                $filled = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'SUM(qty) as qty', "item_ref_id=$refID", false);
				return $qtySO - $filled;
            } else { return 0; }
		case 'shipBalVal': // pass table journal_item.id and check for quantites remaining to be shipped
			$refID = clean($value, 'integer');
            $ttlSO = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'debit_amount+credit_amount', "id=$refID", false);
			if ($ttlSO) {
                $invSO = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'SUM(debit_amount+credit_amount) as invSO', "item_ref_id=$refID", false);
				return $ttlSO - $invSO;
            } else { return 0; }
		case 'ship_prior': // pass table journal_item.id and check for quantites shipped prior
            if (!$value) { return 0; }
            if (strpos($value, ':')) {
                $tmp = explode(':', $value);
                $links = ['ref_id'=>$tmp[0], 'item_ref_id'=>$tmp[1]];
            } else {
    			$links = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['ref_id', 'item_ref_id'], "id=$value");
            }
			if ($links['item_ref_id']) {
				return dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'SUM(qty)', "item_ref_id={$links['item_ref_id']} AND ref_id!={$links['ref_id']}", false);
            } else { return 0; }
        case 'soStatus': // pulls the entire Sales Order line items from a given Invoice #, rqd to pass (journal_main.id)
            $rID    = intval($value);
            $invRows= dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$rID AND gl_type='itm'");
            $soID   = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'so_po_ref_id', "id=$rID");
            $soRows = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$soID AND gl_type='itm'");
            foreach (array_keys($soRows) as $idx) { $soRows[$idx]['qty'] = 0; } // erase the qyantity as actuals will be calculated later
            $invID = 0;
            foreach ($invRows as $invRow) { 
                $soRows[$invRow['item_cnt']-1] = $invRow;
                $invID = $invRow['ref_id'];
            } // combine values
            foreach ($report->fieldlist as $TableObject) { if ($TableObject->type <> 'Tbl') { continue; } else { break; } } // get the report table field
            $output = [];
            foreach ($soRows as $row) {
                $rowData = [];
                foreach ($TableObject->settings->boxfield as $cIdx => $col) {
                    $parts = explode('.', $col->fieldname, 2); // strip the table name
                    switch ($parts[1]) {
                        case 'credit_amount': $rowData["r$cIdx"] = $row['item_ref_id'] ? $row['credit_amount'] : 0; break;
                        case 'debit_amount':  $rowData["r$cIdx"] = $row['item_ref_id'] ? $row['debit_amount']  : 0; break;
                        default: 
                            if (!empty($col->processing) && $col->processing == 'ship_prior'){
                                $rowData["r$cIdx"] = "$invID:".($row['item_ref_id'] ? $row['item_ref_id'] : $row['id']); // needs encoding current invoice ID:SO item ID
                            } elseif (!empty($col->processing) && $col->processing == 'ship_bal')  { // reindex so processing will yield proper results
                                if (!$row['sku']) { $rowData["r$cIdx"] = 0; }
                                else { $rowData["r$cIdx"] = $row['item_ref_id'] ? $row['item_ref_id'] : $row['id']; }
                            } else {
                                $rowData["r$cIdx"] = isset($row[$parts[1]]) ? $row[$parts[1]] : '';
                            }
                    }
                }
                $output[] = $rowData;
            }
            msgDebug("\nReturning processed soRows = ".print_r($soRows, true));
            return $output;
        case 'subTotal': 
            $rID = clean($value, 'integer');
			return dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(debit_amount-credit_amount) AS F0", "ref_id=$rID AND gl_type='itm'", false);
        case 'taxJrnl':
            $rID = intval($value);
			$main = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['journal_id', 'sales_tax'], "id=$rID");
			return in_array($main['journal_id'], [7,13,18,19,20,21]) ? -$main['sales_tax'] : $main['sales_tax'];
        case 'ttlJrnl':
            $rID = intval($value);
			$main = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['journal_id', 'total_amount'], "id=$rID");
			return in_array($main['journal_id'], [7,13,18,19,20,21]) ? -$main['total_amount'] : $main['total_amount'];
		default:
	}
}

/**
 * Creates a 8 character reference used to index gl entries for re-posting entries
 * @param integer $ts - date timestamp
 * @param integer $idx - table journal_main record id
 * @param type $jID - journal ID used for balancing vendors vs customer transactions
 * @return string - of format ts:idx with padding
 */
function padRef($ts, $idx, $jID=8)
{
    switch ($jID) {
        case  7: $jID = 12; break; // like a sale
        case 13: $jID =  6; break; // like a purchase
        case 14: $jID =  7; break; // assembly before sale
        case 15:
        case 16: $jID =  8; break; // transfers/adjustments after assemblies and purchases. can be add or subtract, make neutral, after purchases, before sales
        default: // nothing use the journal id as is 
    }
    return str_pad($jID, 2, '0', STR_PAD_LEFT).':'.substr($ts, 0, 10).':'.str_pad($idx, 8, '0', STR_PAD_LEFT);
}

/**
 * tests an order row to determine if it contains actionable data
 * @param array $row - datagrid row containing item information
 * @param array $testList - list of fields to test to decide if row should be skipped
 * @return true if row does not contain useful information, false otherwise
 */
function isBlankRow($row, $testList=[])
{
    $qtyOverride = getModuleCache('phreebooks', 'settings', 'customers', 'include_all') ? true : false;
    if (!isset($row['qty']) || $row['qty'] == 0) { 
        if (isset($row['sku']) && $row['sku'] && $qtyOverride) { 
            return false;
        } else {
            return true;
        }
    }
    foreach ($testList as $field) { if (isset($row[$field]) && $row[$field]) { return false; } }
	return true;
}

/**
 * This function takes a posted banking payment ID and/or a contact ID and retrieves the posted data or list of current 
 * @uses - Used when editing banking information for customers and vendors, handles outstanding invoices for single/bulk payment
 * @param integer $rID - table: journal_main field: id, will be zero for unposted entry, will be journal_main id for editing posted entries
 * @param integer $cID - table: contact field: id, doesn't matter if rID != 0, will be contact id for new entries
 * @return array $output - journal_main, journal_item values if rID; contact info, open invoices if cID 
 */
function jrnlGetPaymentData($rID=0, $cID=0, $preChecked=[])
{
	$output = ['main'=>[],'items'=>[]];
	$itemIdx = 0;
	if ($rID > 0) { // pull posted record info
		$output['main']        = dbGetRow(BIZUNO_DB_PREFIX."journal_main", "id='$rID'");
		$output['main']['type']= in_array($output['main']['journal_id'], [17, 20, 21]) ? 'v' : 'c';
		$cID                   = $output['main']['contact_id_b'];
		$items                 = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "ref_id='$rID'");
		if (sizeof($items) > 0) {
			$debitCredit = in_array(JOURNAL_ID, [20,22]) ? 'debit' : 'credit';
			$temp = [];
			foreach ($items as $key => $row) {
				if (!in_array($row['gl_type'], ['pmt','dsc'])) {
					$output['items'][] = $row; // keep ttl, frt and others for edit to fill details
					continue;
				}
				if (empty($temp[$row['item_ref_id']])) {
                    if (empty($row['discount'])) { $row['discount'] = 0; }
                    if (empty($row['amount']))   { $row['amount']   = 0; }
					$temp[$row['item_ref_id']] = $row;
				}
				switch($row['gl_type']) {
					case 'pmt': 
						$temp[$row['item_ref_id']]['amount']     = $row[$debitCredit.'_amount'];
						$temp[$row['item_ref_id']]['post_date']  = $row['date_1'];
						$temp[$row['item_ref_id']]['invoice_num']= $row['trans_code'];
						break;
					case 'dsc':
						$temp[$row['item_ref_id']]['discount'] = $debitCredit=='debit' ? $row['credit_amount']: $row['debit_amount'];
                        $output['items'][] = $row; // save the discount row for edits
						break;
				}
			}
			foreach ($temp as $row) {
				$row['total']  = $row['amount'] - $row['discount'];
				$row['checked']= true;
				$row['idx']    = $itemIdx; // for edatagrid with checkboxes to key off of
				$itemIdx++;
				$output['items'][] = $row;
			}
		}
	} elseif ($cID > 0) {
		$output['main'] = mapContactToJournal($cID, '_b');
    } else { return false; }
	// pull contact info and open invoices
	$jID = (isset($output['main']['type']) && $output['main']['type']=='v') ? '6,7' : '12,13';
    if ($output['main']['type']=='v' && validateSecurity('phreebooks', 'j2_mgr', 1)) { $jID .= ',2'; }
    $today = date('Y-m-d');
	$criteria = "contact_id_b='$cID' AND journal_id IN ($jID) AND closed='0'";
	$result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $criteria, "post_date");
	msgDebug("\nFound number of open invoices = ".sizeof($result));
	foreach ($result as $row) {
        if (in_array($row['journal_id'], [2])) { glFindAPacct($row); }
        if (in_array($row['journal_id'], [7,13])) { $row['total_amount'] = -$row['total_amount']; } // added jID=13 for cash receipts
		$row['total_amount'] += getPaymentInfo($row['id'], $row['journal_id']);
        if (in_array(JOURNAL_ID, [17,22])) { $row['total_amount'] = -$row['total_amount']; } // need to negate for reverse cash flow
		$dates= localeDueDate($row['post_date'], $row['terms'], $output['main']['type']);
        msgDebug("\npost date = {$row['post_date']} and early date = {$dates['early_date']}");
        $discount = $today <= $dates['early_date'] ? roundAmount($dates['discount'] * $row['total_amount']) : 0;
		$output['items'][] = [
            'idx'         => $itemIdx,
			'id'          => 0,
			'invoice_num' => $row['invoice_num'],
			'contact_id'  => $row['contact_id_b'],
			'primary_name'=> $row['primary_name_b'],
			'item_ref_id' => $row['id'],
			'gl_type'     => 'pmt',
			'waiting'     => in_array($row['journal_id'], [6,7]) ? $row['waiting'] : 0,
			'qty'         => 1,
			'description' => sprintf(lang('phreebooks_pmt_desc_short'), $row['invoice_num'], $row['purch_order_id'] ? $row['purch_order_id'] : lang('none')),
			'amount'      => roundAmount($row['total_amount']),
			'gl_account'  => $row['gl_acct_id'],
			'post_date'   => $row['post_date'],
			'date_1'      => $dates['net_date'],
			'discount'    => $discount,
			'total'       => roundAmount($row['total_amount']) - $discount,
			'checked'     => in_array($row['id'], $preChecked) ? true : false];
		$itemIdx++;
	}
	msgDebug("\nReturning from jrnlGetPaymentData with item array: ".print_r($output, true));
	return $output;
}

/**
 * Extracts the accounts payable account for a posted journal entry to set default in an edit
 * @param array $row - journal_main structure
 * @return array modified $row - ap gl account added
 */
function glFindAPacct(&$row)
{
    msgDebug("\nIn glFindAPacct");
    if (empty($row['id'])) { return ''; }
	$iRows = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "ref_id='{$row['id']}'");
	foreach ($iRows as $item) {
		$type = getModuleCache('phreebooks', 'chart', 'accounts')[$item['gl_account']]['type'];
        msgDebug("\ngl_account = {$item['gl_account']} and type = $type");
        if ($type <> 20) { continue; } // Accounts Payable type gl account
        if (empty($row['gl_acct_id'])) {
            $row['gl_acct_id']   = $item['gl_account'];
            $row['total_amount'] = $item['debit_amount'] + $item['credit_amount'];
        } else {
            msgAdd("More than one Accounts Payable account has been found for ref # {$row['invoice_num']}. When paying a vendor from a post to the general journal there can only be one line item assigned to an Accounts Payable type account. The general journal entry needs to be fixed!");
            $row['gl_acct_id'] = ''; // clear the GL since there are more than 1
            return;
        }
	}
}

function getPaymentInfo($mID, $jID) {
    $paid = dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(debit_amount)-SUM(credit_amount) AS credits", "item_ref_id=$mID AND gl_type='pmt'", false);
    if (!$paid) { $paid = 0; }
    if (in_array($jID, [6,7])) { $paid = -$paid; }
    msgDebug("\nPaid array = ".print_r($paid, true));
    return $paid;
}

/**
 * Loads records to create a bulk payment
 * @return array - list of payments that need to be made
 */
function jrnlGetBulkData()
{
    $output = ['main'=>[], 'items'=>[]];
	$itemIdx = 0;
	$post_date = localeCalculateDate(date('Y-m-d'), 1);
	$jID = '6,7';
    if (validateSecurity('phreebooks', 'j2_mgr', 1)) { $jID .= ',2'; }
	$criteria = "journal_id IN ($jID) AND closed='0' AND post_date<'$post_date' AND contact_id_b>0";
	$result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $criteria, "post_date");
	msgDebug("\nFound number of open invoices = ".sizeof($result));
	foreach ($result as $row) {
        if (in_array($row['journal_id'], [2])) { glFindAPacct($row); }
        if (in_array($row['journal_id'], [7,13])) { $row['total_amount'] = -$row['total_amount']; } // added jID=13 for cash receipts
        $paid = getPaymentInfo($row['id'], $row['journal_id']);
        $dates= localeDueDate($row['post_date'], $row['terms'], 'v');
        $discount = $row['post_date'] <= $dates['early_date'] ? roundAmount($dates['discount'] * $row['total_amount']) : 0;
		$output['items'][] = [
            'idx'         => $itemIdx,
			'id'          => 0,
			'inv_num'     => $row['invoice_num'],
			'contact_id'  => $row['contact_id_b'],
		    'primary_name'=> $row['primary_name_b'],
			'item_ref_id' => $row['id'],
			'gl_type'     => 'pmt',
		    'waiting'     => $row['waiting'],
			'qty'         => 1,
			'description' => sprintf(lang('phreebooks_pmt_desc_short'), $row['invoice_num'], $row['purch_order_id'] ? $row['purch_order_id'] : lang('none')),
			'amount'      => $row['total_amount'] + $paid,
			'gl_account'  => $row['gl_acct_id'],
			'inv_date'    => $row['post_date'],
			'date_1'      => $dates['net_date'],
			'discount'    => $discount,
			'total'       => $row['total_amount'] + $paid - $discount,
			'checked'     => $row['waiting'] ? false : true];
		$itemIdx++;
	}
	msgDebug("\nReturning from jrnlGetBulkData with item array: ".print_r($output, true));
	return $output;
}

/**
 * This function maps the contacts record and main address information to the journal_main table fields.
 * @param integer $cID - table contact field id of contact to retrieve 
 * @param string $suffix - specifies billing or shipping fields to populate 
 * @return array $output -  mapped fields of contact to journal
 */
function mapContactToJournal($cID = 0, $suffix='_b')
{
	if (!$cID) {
		msgAdd("function mapContactToJournal - Failed mapping contact to journal record");
		return [];
	}
	$result = dbGetRow(BIZUNO_DB_PREFIX."address_book", "ref_id='$cID' AND type LIKE '%m'");
	$output = [
        'contact_id'.$suffix  => $cID,
		'address_id'.$suffix  => $result['address_id'],
		'primary_name'.$suffix=> $result['primary_name'],
		'contact'.$suffix     => $result['contact'],
		'address1'.$suffix    => $result['address1'],
		'address2'.$suffix    => $result['address2'],
		'city'.$suffix        => $result['city'],
		'state'.$suffix       => $result['state'],
		'postal_code'.$suffix => $result['postal_code'],
		'country'.$suffix     => $result['country'],
		'telephone1'.$suffix  => $result['telephone1'],
		'email'.$suffix       => $result['email']];
	$result = dbGetRow(BIZUNO_DB_PREFIX."contacts", "id='$cID'");
	$output['type']    = $result['type'];
	$output['terms']   = isset($result['terms']) && $result['terms'] ? $result['terms'] : '0';
	$output['currency']= isset($result['currencyISO']) && $result['currencyISO'] ? $result['currencyISO'] : getUserCache('profile', 'currency', false, 'USD');
	return $output;
}

/**
 * This function automatically updates the period and sets the new constants in the configuration db table
 * @param boolean $verbose
 * @return boolean
 */
function periodAutoUpdate($verbose=true)
{
	$period = calculatePeriod(date('Y-m-d'), false);
    if (!getModuleCache('phreebooks', 'fy', 'period') || $period == getModuleCache('phreebooks', 'fy', 'period')) { return true; } // we're in the current period
	if (!$period) { // we're outside of the defined fiscal years
        if ($verbose) { msgAdd(sprintf(lang('err_gl_post_date_invalid'), $period), 'trap'); }
        $tmpSec = getUserCache('security', 'admin', false, 0);
        setUserCache('security', 'admin', 3);
        require_once(BIZUNO_LIB."controller/module/phreebooks/tools.php");
        $tools = new phreebooksTools();
        $tools->fyAdd(); // auto-add new fiscal year
        setUserCache('security', 'admin', $tmpSec); // restore user permissions
		return true;
	} else {
        $props = getPeriodInfo($period);
        setModuleCache('phreebooks', 'fy', false, $props);
		msgLog(sprintf(lang('msg_period_changed'), $period));
        if ($verbose) { msgAdd(sprintf(lang('msg_period_changed'), $period), 'success'); }
	}
	return true;
}

/**
 * Retrieves fiscal year period details
 * @param integer $period - period to get data on
 * @return array - details of requested fiscal year period information
 */
function getPeriodInfo($period)
{
    $values     = dbGetRow  (BIZUNO_DB_PREFIX."journal_periods", "period='$period'");
    $period_min = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", "MIN(period)", "fiscal_year={$values['fiscal_year']}", false);
    $period_max = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", "MAX(period)", "fiscal_year={$values['fiscal_year']}", false);
    $fy_max     = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", ['MAX(fiscal_year) AS fiscal_year', 'MAX(period) AS period'], "", false);
    $output = [
        'period'       => $period,
        'period_start' => $values['start_date'],
        'period_end'   => $values['end_date'],
        'fiscal_year'  => $values['fiscal_year'],
        'period_min'   => $period_min,
        'period_max'   => $period_max,
        'fy_max'       => $fy_max['fiscal_year'],
        'fy_period_max'=> $fy_max['period']];
    msgDebug("\nCalculating period information, returning with values: ".print_r($output, true));
    return $output;
}

/**
 * Determines the fiscal calendar period based on a passed date
 * @param string $post_date - date to retrieve period information
 * @param boolean $verbose - [default true] set to false to suppress user messages
 * @return integer - fiscal year period based on the submitted date
 */
function calculatePeriod($post_date, $verbose=true)
{
    if (!getModuleCache('phreebooks', 'fy', 'period')) { return '999'; }
	$post_time_stamp         = strtotime($post_date);
	$period_start_time_stamp = strtotime(getModuleCache('phreebooks', 'fy', 'period_start'));
	$period_end_time_stamp   = strtotime(getModuleCache('phreebooks', 'fy', 'period_end'));
	if (($post_time_stamp >= $period_start_time_stamp) && ($post_time_stamp <= $period_end_time_stamp)) {
		return getModuleCache('phreebooks', 'fy', 'period', false, 0);
	} else {
		$period = dbGetValue(BIZUNO_DB_PREFIX.'journal_periods', 'period', "start_date<='$post_date' AND end_date>='$post_date'");
		if (!$period) { // post_date is out of range of defined accounting periods
            return msgAdd(sprintf(lang('err_gl_post_date_invalid'), $post_date));
		}
        if ($verbose) { msgAdd(lang('msg_gl_post_date_out_of_period'), 'caution'); }
		return $period;
	}
}

/**
 * Loads the tax rate information from the database and creates a structure for the session cache
 * @param char $type - choices are c for customers or v for vendors
 * @param string $date - [default Y-m-d] date to use to limit results to start date before passed date
 * @return array - list of valid tax rates 
 */
function loadTaxes($type, $date=false)
{
    if (!$date) { $date = date('Y-m-d'); }
	$output  = [];
    $taxRates= getModuleCache('phreebooks', 'sales_tax', $type, false, []);
	foreach ($taxRates as $row) {
        if (empty($row['rate'])) { $row['rate'] = $row['tax_rate']; } // delete after 4/15/2018, now set in registry
        if (!is_array($row['settings'])) { $row['settings'] = json_decode($row['settings'], true); } // delete after 4/15/2018, now set in registry
        $output[] = ['id'=>$row['id'],'text'=>$row['title'],'tax_rate'=>$row['rate']." %",'status'=>$row['status'],'auths'=>$row['settings']];
    }
    array_unshift($output, ['id'=>'0', 'text'=>lang('none'), 'status'=>0, 'tax_rate'=>"0 %", 'auths'=>[]]);
	return $output;
}

/**
 * Validates a fiscal year and creates entries in the journal_periods, used when creating a new fiscal year
 * @param integer $next_fy - Fiscal year to create
 * @param integer $next_period - first period of next fiscal year
 * @param string $next_start_date - first date of next fiscal year
 * @param integer $num_periods - number of periods in fiscal year [default 12]
 * @return integer - next period (for successive adds)
 */
function setNewFiscalYear($next_fy, $next_period, $next_start_date, $num_periods=12)
{
	$periods = [];
	for ($i = 0; $i < $num_periods; $i++) {
		$fy_array = [
            'period'     => $next_period,
			'fiscal_year'=> $next_fy,
			'start_date' => $next_start_date,
			'end_date'   => localeCalculateDate($next_start_date, $day_offset = -1, $month_offset = 1),
			'date_added' => date('Y-m-d'),
			'last_update'=> date('Y-m-d')];
		$periods[] = "('".implode("', '", $fy_array)."')";
		$next_period++;
		$next_start_date = localeCalculateDate($next_start_date, $day_offset = 0, $month_offset = 1);
	}
	dbGetResult("INSERT INTO ".BIZUNO_DB_PREFIX."journal_periods VALUES ".implode(",\n",$periods));
	return $next_period--;
}

/**
 * Loads the journal_history table when adding a new fiscal year or chart of accounts value
 */
function buildChartOfAccountsHistory()
{
	if (!$max_period = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", "MAX(period) AS period", '', false)) {
		die ('table journal_periods is not set!');
	}
	$records = [];
	foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $glAccount) { if (!isset($glAccount['heading']) || !$glAccount['heading']) {
		$account_id = $glAccount['id'];
		for ($i = 0, $j = 1; $i < $max_period; $i++, $j++) {
			$record_found = dbGetValue(BIZUNO_DB_PREFIX."journal_history", "id", "gl_account='$account_id' AND period=$j");
            if (!$record_found) { $records[] = "('$account_id', '{$glAccount['type']}', '$j', NOW())"; }
		}
    } }
	if (sizeof($records) > 0) {
        dbGetResult("INSERT INTO ".BIZUNO_DB_PREFIX."journal_history (gl_account, gl_type, period, last_update) VALUES ".implode(",\n",$records));
    }
}

/**
 * this function creates the journal_history table records for a new GL account
 * @param string $glAcct - GL Account number
 * @param string $glType - GL Account type
 * @param integer $period - [Default: 1] Starting period
 */
function insertChartOfAccountsHistory($glAcct='', $glType='', $period=1)
{
    if (!$glAcct) { return msgAdd("Bad parameters sent to insertChartOfAccountsHistory()"); }
	$max_period = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", "MAX(period) AS period", '', false);
	$records = array();
	for ($i=$period; $i<=$max_period; $i++) {
		$record_found = dbGetValue(BIZUNO_DB_PREFIX."journal_history", "id", "gl_account='$glAcct' AND period=$i");
        if (!$record_found) { $records[] = "('$glAcct', '$glType', '$i', NOW())"; }
	}
	if (sizeof($records) > 0) {
        dbGetResult("INSERT INTO ".BIZUNO_DB_PREFIX."journal_history (gl_account, gl_type, period, last_update) VALUES ".implode(",\n",$records));
    }
}

function chartSales($jID, $range='c', $pieces=10, $reps=false)
{
    require(BIZUNO_LIB.'controller/module/phreeform/functions.php');
    // Calculate the range to collect data
    switch ($jID) {
        default:
        case 12: $type='c'; $filter = "journal_id IN (12,13)";
    }
    $dates = phreeformSQLDate($range);
    $filter .= " AND ".$dates['sql'];
    if ($reps && getUserCache('profile', 'contact_id', false, '0')) {
        if (getUserCache('security', 'admin', false, 0)<3) { $filter.= " AND rep_id='".getUserCache('profile', 'contact_id', false, '0')."'"; }
    }
    if (getUserCache('profile', 'restrict_store', false, -1) > -1) { $filter.= " AND store_id=".getUserCache('profile', 'restrict_store'); }
    $result= dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $filter, '', ['id','journal_id','total_amount','contact_id_b']);
    $totals = [];
    foreach ($result as $row) {
        if (!isset($totals[$row['contact_id_b']])) { $totals[$row['contact_id_b']] = 0; }
        $totals[$row['contact_id_b']] += $row['journal_id']==13 ? -$row['total_amount'] : $row['total_amount'];
    }
    arsort($totals);
    $cnt = 1;
    $runningTotal = 0;
    $struc[] = [lang('customer'), lang('total')]; // headings
    msgDebug("\nFound total invoices count = ".sizeof($totals));
    foreach ($totals as $cID => $total) {
        if ($cnt < $pieces-1) {
            $name = dbGetValue(BIZUNO_DB_PREFIX.'address_book', 'primary_name', "ref_id=$cID AND type='m'");
            if (defined('DEMO_MODE')) { $name = randomNames($type); }
            $struc[] = [$name, $total];
        } else { $runningTotal += $total; }
        $cnt++;
    }
    $struc[] = [lang('other'), $runningTotal];
    msgDebug("\nOutput = ".print_r($struc, true));
    return $struc;
}

/**
 * This function calculates the contact aging entries for purchase/sales order and purchases/invoices
 * @param integer $id - contact id to find aging
 * @param string $bb_date - 
 * @param string $eb_date - 
 * @return array $output - aging results
 */
function calculate_aging($id, $bb_date=false, $eb_date=false)
{
    if (!$id) { return []; }
	$result = dbGetValue(BIZUNO_DB_PREFIX."contacts", ['type', 'terms'], "id=$id");
	$idx    = $result['type'] == 'v' ? 'vendors' : 'customers';
	$today  = date('Y-m-d');
    if (!$bb_date) { $bb_date = $today; }
    if (!$eb_date) { $eb_date = localeCalculateDate($today, 1); }
	$term_date= localeDueDate($today, $result['terms'], $idx);
	$due_days = $term_date['due_days'];
	$due_date = localeCalculateDate($today, -$due_days);
	$late_30  = localeCalculateDate($today, -30);
	$late_60  = localeCalculateDate($today, -60);
	$late_90  = localeCalculateDate($today, -90);
    msgDebug("\nType=$idx, Today=$today, BB Date=$bb_date, EB Date=$eb_date, DueDate=$due_date, Late30=$late_30, Late60=$late_60, Late90=$late_90");
	$output   = [
        'inv_orders'  => [['id'=>'0', 'text'=>lang('select')]],
		'open_quotes' => [['id'=>'0', 'text'=>lang('select')]],
		'open_orders' => [['id'=>'0', 'text'=>lang('select')]],
		'unpaid_inv'  => [['id'=>'0', 'text'=>lang('select')]],
		'unpaid_crd'  => [['id'=>'0', 'text'=>lang('select')]],
		'balance_0'   => 0, 
		'balance_30'  => 0, 
		'balance_60'  => 0, 
		'balance_90'  => 0,
		'total'       => 0,
		'past_due'    => 0,
	    'beg_bal'     => 0,
	    'end_bal'     => 0,
	    'credit_limit'=> $term_date['credit_limit'],
		'terms_lang'  => viewTerms($result['terms'], false, $result['type'], $inc_limit=true),
        ];
	$inv_jid  = ($result['type'] == 'v') ? '3,4,6,7' : '9,10,12,13';
	$open_jID = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", "contact_id_b=$id AND journal_id IN ($inv_jid) AND closed='0'", "post_date");
//	$inv_jid  = ($type == 'v') ? '6,7'  : '12,13';
//	$pmt_jid  = ($type == 'v') ? '20,21,22' : '17,18,19';
	foreach ($open_jID as $row) {
		$text = $row['invoice_num']." (".viewDate($row['post_date'])." - ".viewFormat($row['total_amount'], 'currency').")";
		$entry = ['id'=>$row['id'], 'text'=>$text];
		msgDebug("\n Found aging record".print_r($entry, true));
		switch ($row['journal_id']) {
			case  3:
			case  9: $output['inv_orders'][] = $entry; $output['open_quotes'][] = $entry; break;
			case  4:
			case 10: $output['inv_orders'][] = $entry; $output['open_orders'][] = $entry; break;
			case  6:
			case 12: $output['unpaid_inv'][] = $entry; break;
			case  7:
			case 13: $output['unpaid_crd'][] = $entry; break;
		}
		if (in_array($row['journal_id'], [6,7,12,13])) {
		  $total_billed = in_array($row['journal_id'], [7,13]) ? -$row['total_amount'] : $row['total_amount'];
		  $post_date    = $row['post_date'];
		  $result = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ["SUM(debit_amount) AS debits", "SUM(credit_amount) AS credits"], "item_ref_id='".$row['id']."' AND gl_type='pmt'", false);
          if (!$result) { $result = ['debits'=>0, 'credits'=>0]; }
		  $balance = $total_billed - ($idx=='vendors' ? $result['debits']-$result['credits'] : $result['credits']-$result['debits']);
          if     ($post_date < $bb_date) { msgDebug("\nAdding BegBal = $balance"); $output['beg_bal']    += $balance; }
          if     ($post_date < $eb_date) { msgDebug("\nAdding EndBal = $balance"); $output['end_bal']    += $balance; }
          if     ($post_date < $due_date){ msgDebug("\nAdding PastDue= $balance"); $output['past_due']   += $balance; }
		  if     ($post_date < $late_90) { msgDebug("\nAdding Late90 = $balance"); $output['balance_90'] += $balance; $output['total'] += $balance; }
		  elseif ($post_date < $late_60) { msgDebug("\nAdding Late60 = $balance"); $output['balance_60'] += $balance; $output['total'] += $balance; }
		  elseif ($post_date < $late_30) { msgDebug("\nAdding Late30 = $balance"); $output['balance_30'] += $balance; $output['total'] += $balance; }
		  elseif ($post_date <= $today)  { msgDebug("\nAdding Late00 = $balance"); $output['balance_0']  += $balance; $output['total'] += $balance; } // else it's in the future
		}
	}
	return $output;
}
