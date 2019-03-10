<?php
/*
 * PhreeBooks journal methods
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
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-02-28
 * @filesource /lib/controller/module/phreebooks/journal.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/functions.php", 'processPhreeBooks', 'function');

/**
 * Main journal class wrapper, calls appropriate journal class as needed
 * WARNING: transaction must be started prior and committed after all posting/un-posting activities
 */
class journal
{
    public  $updateContact_b= false; // do not automatically add/update contact billing
    public  $updateContact_s= false; // do not automatically add/update contact shipping
    public  $updatePayment  = false; // do not automatically add/update payment information
    private $lowestPeriod   = 999;
    public  $affectedGlAccts= []; // list the gl accounts that are touched for calculating journal balances
    public  $postList       = [];
    public  $main           = [];
    public  $items          = [];

    function __construct($mID=0, $jID=0, $post_date=false, $cID=0, $structure=[], $action=false)
    {
        $this->structure= $structure;
        $this->action   = $action;
        $this->journalID= $jID;
        $this->setDefaults($jID, $post_date);
        $this->getDbData($mID, $cID);
        if (!empty($jID)) {
            $this->journal         = $this->getJournal($jID, $this->main, $this->items);
            $this->journal->action = $action;
            $this->journal->items  = $this->items;
        }
    }

    public function getDataItem($rID, $cID, $security)
    {
        $this->journal->getDataItem($rID, $cID, $security);
        $this->items = $this->journal->items;
    }

    public function customizeView(&$data, $rID, $cID, $security)
    {
        $this->journal->customizeView($data, $rID, $cID, $security);
    }

    /**
     * Handles the main posting of all journals, this is the entry point for any module/extension
     * @param string $action - choices are insert [default] or delete
     * @return boolean - true on success, false on error
     */
    public function Post($action='insert')
    {
        if ($this->updateContact_b) { if (!$this->updateContact('b')) { return; } }
        if ($this->updateContact_s) {
            if (!$this->main['contact_id_s']) { $this->main['contact_id_s'] = $this->main['contact_id_b']; }
            if (!$this->updateContact('s')) { return; }
        }
        if (!$this->preFlightCheck($action)) { return; }
        $this->getPostList([$this->main['id']]); // get array of mIDs that need to be posted, source and referenced records
        if ($this->quickPost()) { return true; } // we were able to meet the post requirements without going through the entire post operation, i.e. simple stuff
        krsort($this->postList); // unpost in reverse order, queue the record data
        msgDebug("\ngetPostList returned with reverse sorted results: ".print_r($this->postList, true));
        foreach ($this->postList as $jEntry) {
            $main = sizeof($jEntry['uMain']) ? $jEntry['uMain'] : $jEntry['main']; // if unpost is different than post, otherwise unpost the post variables
            $item = sizeof($jEntry['uItem']) ? $jEntry['uItem'] : $jEntry['item'];
            $this->lowestPeriod = min($this->lowestPeriod, $main['period']);
            if (!$main['id']) { continue; } // skip as this is record has never been posted
            $this->getAffectedGlAccts($this->affectedGlAccts,$main, $item);
            $ledger = $this->getJournal($main['journal_id'], $main, $item);
            if (!$ledger->unPost()) { return; }
        }
        ksort($this->postList); // now post everything in proper order (by numeric journal entries)
        foreach ($this->postList as $jEntry) { // repost in order
            $this->getAffectedGlAccts($this->affectedGlAccts, $jEntry['main'], $jEntry['item']);
            if ($action=='delete' && $jEntry['main']['id'] == $this->main['id']) { continue; } // don't re-post the record we are deleting
            $ledger = $this->getJournal($jEntry['main']['journal_id'], $jEntry['main'], $jEntry['item']);
            $this->lowestPeriod = min($this->lowestPeriod, $jEntry['main']['period']);
            if (!$ledger->post()) { return; }
            if (!$jEntry['main']['id']) { $this->main['id'] = $ledger->main['id']; } // for new posts, set the id, should only happen once
        }
        if (!$this->updateJournalHistory($this->lowestPeriod)) { return; }
        return true;
    }

    /**
     * Handles the main un-posting of all journals, this is the entry point for any module/extension
     * @return boolean - true on success, false on error
     */
    public function unPost()
    {
        msgDebug("\nunPosting Journal... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        return $this->Post('delete');
    }

    /**
     * Loads the appropriate journal class to operate on
     * @param integer $jID - journal ID
     * @param array $main - table journal_main record data, single element
     * @param array $item - table journal_item record data, one or more elements
     * @return object - journal object
     */
    public function getJournal($jID, $main=[], $item=[])
    {
        $jName = $this->getJournalName($jID);
        $fqcn = "\\bizuno\\$jName";
        bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journals/$jName.php", $fqcn);
        return new $fqcn($main, $item);
    }

    /**
     * cleans the journal ID value to the file name but inserting zeros
     * @param integer $jID - Journal ID, no leading spaces
     * @return string - journal name to retrieve proper class file, jxx.php
     */
    private function getJournalName($jID)
    {
        return $jName = "j".substr('0'.$jID, -2);
    }

    /**
     * recursive method to build the list of affected journal main records during a post
     * @param array $mIDs - [default empty []], list of journal_main id records
     */
    private function getPostList($mIDs=[])
    {
        msgDebug("\ngetPostList working with mIDs = ".print_r($mIDs, true));
        foreach ($mIDs as $mID) {
            if (sizeof($this->postList) == 0) { // first time through, post data is local, may need to fetch unPost data if edit
                $data = ['main'=>$this->main, 'item'=>$this->items]; // set the new post data
                $uData= $this->getDbRecord($mID); // make sure to unpost the original post
            } else {
                $data = $this->getDbRecord($mID);
                $uData= ['main'=>[], 'item'=>[]]; // unpost is the same as post for linked journal entries
            }
            $idx    = padRef($data['main']['post_date'], $mID, $data['main']['journal_id']);
            $this->postList[$idx] = ['main'=>$data['main'], 'item'=>$data['item'], 'uMain'=>$uData['main'], 'uItem'=>$uData['item']];
            $ledger = $this->getJournal($data['main']['journal_id'], $data['main'], $data['item']);
            $refIDs = $ledger->getRepostData();
            if (sizeof($refIDs)) { $this->getPostList($refIDs); }
        }
    }

    /**
     * Takes the pre-sorted postList and pulls the first element off the top. This will be the source _POST data with pre values as well
     * @return boolean
     */
    private function quickPost()
    {
        list($k) = array_keys($this->postList);
        $data = $this->postList[$k];
        // return true if quick post was successful and rest of post is not necessary
        $ledger = $this->getJournal($data['main']['journal_id'], $data['main'], $data['item']);
        if (method_exists($ledger, 'quickPost')) { if ($ledger->quickPost($data)) { return true; } }
        return false;
    }

    /**
     *
     * @param integer $rID - main database record ID
     * @param integer $cID - contact database record ID
     */
    private function getDbData($rID=0, $cID=0)
    {
        msgDebug("\nEntering getDbData with journalID = $this->journalID and rID = $rID and cID = $cID and action = $this->action");
        if (in_array($this->journalID, [17,18,20,22])) { // banking journals
            $dbData = $this->action=='bulk' ? $this->jrnlGetBulkData() : $this->jrnlGetPaymentData($rID, $cID);
            $this->main = array_replace_recursive($this->main, $dbData['main']);
            $this->items= $dbData['items'];
        } elseif ($rID > 0 || $this->action=='inv') {
            $mainID     = $this->action=='inv' ? clean('iID', 'integer', 'get') : $rID;
            $this->main = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id=$mainID");
            $GLOBALS['bizunoCurrency'] = $this->main['currency'];
            if ($this->action == 'inv') { // clear some fields to convert purchase/sales order or quote to receive/invoice
                if (in_array($this->journalID, [3,4,6,7])) {
                    $this->main['purch_order_id']= $this->main['invoice_num'];
                } else {
                    $this->main['soNum']    = $this->main['invoice_num'];
                }
                $this->main['journal_id']   = $this->journalID;
                $this->main['so_po_ref_id'] = $this->main['id'];
                $this->main['id']           = '';
                $this->main['post_date']    = date('Y-m-d');
                $this->main['terminal_date']= date('Y-m-d'); // get default based on type
                $this->main['invoice_num']  = '';
                if (in_array($this->journalID, [12]) && getModuleCache('extShipping', 'properties', 'status')) { $this->main['waiting'] = '1'; } // set waiting to ship flag
// @todo this should be a setting as some want the rep to flow from the Sales Order for commissions while others just care about who fills the order.
//              $this->main['rep_id']       = getUserCache('profile', 'contact_id', false, '0');
            }
            $this->items = $mainID ? dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$mainID") : [];
        }
        $this->currencyConvert('toPost');
//      msgDebug("\nLeaving getDbData with main record = " .print_r($this->main,  true));
//      msgDebug("\nRead from db item records = ".print_r($this->items, true));
    }

    /**
     * Pulls the post data from the database based on the main record ID
     * @param integer $mID
     * @return array [main, item]
     */
    private function getDbRecord($mID=0)
    {
        $main = $item = [];
        if ($mID > 0) {
            $main = dbGetRow(BIZUNO_DB_PREFIX."journal_main", "id=$mID");
            $item = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "ref_id=$mID", 'id');
        }
        return ['main'=>$main, 'item'=>$item];
    }

    /**
     * Sets the default values for a new journal_main record
     * @param integer $jID - Journal ID
     * @param date $post_date - post date in db format (Y-m-d), default is false which converts to today.
     */
    private function setDefaults($jID=0, $post_date=false)
    {
        if (!$post_date) { $post_date = date('Y-m-d'); }
        $termsType  = in_array($this->journalID, [3,4,6,7,17,20,21]) ? 'vendors' : 'customers';
        $this->main = [
            'id'           => 0,  // default to new order
            'journal_id'   => $jID ? $jID : 0,
            'store_id'     => 0,  // default main branch
            'recur_id'     => 0,
            'so_po_ref_id' => 0,
            'invoice_num'  => '', // default to new reference number
            'sales_tax'    => 0,
            'total_amount' => 0,
            'terms'        => getModuleCache('phreebooks', 'settings', $termsType, 'terms'), // default terms
            'gl_acct_id'   => '',
            'currency'     => getUserCache('profile', 'currency', false, 'USD'),
            'currency_rate'=> 1,
            'closed'       => 0,
            'waiting'      => 0,
            'post_date'    => $post_date,
            'terminal_date'=> date('Y-m-d'),
            'period'       => calculatePeriod($post_date),
            'admin_id'     => getUserCache('profile', 'admin_id', false, 0),
            'rep_id'       => getUserCache('profile', 'contact_id', false, '0'),
            'contact_id_b' => 0,
            'contact_id_s' => 0];
        if (in_array($this->journalID, [3,4,6,13,15,21])) { $this->setShip2Biz(); } // pre-set the ship to address
        $this->items = [];
    }

    /**
     *
     * @param type $structure
     */
    private function setShip2Biz()
    {
        $dbData = addressLoad(0, '_s', true);
        foreach ($dbData as $idx => $value) {
            if (array_key_exists($idx, $this->structure)) { $this->main[$idx] = $value; }
        }
    }

    /**
     * This function takes a posted banking payment ID and/or a contact ID and retrieves the posted data or list of current
     * @uses - Used when editing banking information for customers and vendors, handles outstanding invoices for single/bulk payment
     * @param integer $rID - table: journal_main field: id, will be zero for unposted entry, will be journal_main id for editing posted entries
     * @param integer $cID - table: contact field: id, doesn't matter if rID != 0, will be contact id for new entries
     * @return array $output - journal_main, journal_item values if rID; contact info, open invoices if cID
     */
    private function jrnlGetPaymentData($rID=0, $cID=0)
    {
        $preChecked= (array)explode(":", clean('iID', 'text', 'get'));
        $output = ['main'=>[],'items'=>[]];
        $itemIdx= 0;
        $type   = in_array($this->journalID, [17, 20, 21]) ? 'v' : 'c';
        if ($rID > 0) { // pull posted record info
            $output['main']        = dbGetRow(BIZUNO_DB_PREFIX."journal_main", "id='$rID'");
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
        } else { return $output; }
        // pull contact info and open invoices
        $jID    = $type=='v' ? '6,7' : '12,13';
        if ($type=='v' && validateSecurity('phreebooks', 'j2_mgr', 1)) { $jID .= ',2'; }
        $today  = date('Y-m-d');
        $criteria = "contact_id_b='$cID' AND journal_id IN ($jID) AND closed='0'";
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $criteria, "post_date");
        msgDebug("\nFound number of open invoices = ".sizeof($result));
        foreach ($result as $row) {
            if (in_array($row['journal_id'], [2])) { glFindAPacct($row); }
            if (in_array($row['journal_id'], [7,13])) { $row['total_amount'] = -$row['total_amount']; } // added jID=13 for cash receipts
            $row['total_amount'] += getPaymentInfo($row['id'], $row['journal_id']);
            if (in_array(JOURNAL_ID, [17,22])) { $row['total_amount'] = -$row['total_amount']; } // need to negate for reverse cash flow
            $dates= localeDueDate($row['post_date'], $row['terms'], $type);
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
                'payment'     => $this->guessMethod($row['id']),
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
     * Loads records to create a bulk payment
     * @return array - list of payments that need to be made
     */
    public function jrnlGetBulkData()
    {
        $output   = ['main'=>[], 'items'=>[]];
        $itemIdx  = 0;
        $post_date= localeCalculateDate(date('Y-m-d'), 1);
        $jID      = '6,7';
        if (validateSecurity('phreebooks', 'j2_mgr', 1)) { $jID .= ',2'; }
        $criteria = "journal_id IN ($jID) AND closed='0' AND post_date<'$post_date' AND contact_id_b>0";
        $result   = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $criteria, "post_date");
        msgDebug("\nFound number of open invoices = ".sizeof($result));
        foreach ($result as $row) {
            if (in_array($row['journal_id'], [2])) { glFindAPacct($row); }
            if (in_array($row['journal_id'], [7,13])) { $row['total_amount'] = -$row['total_amount']; } // added jID=13 for cash receipts
            $paid    = getPaymentInfo($row['id'], $row['journal_id']);
            $dates   = localeDueDate($row['post_date'], $row['terms'], 'v');
            $discount= $row['post_date'] <= $dates['early_date'] ? roundAmount($dates['discount'] * $row['total_amount']) : 0;
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
     * performs some miscellaneous operations to validate the input data
     * @param string $action - post type action, choices are insert or delete
     * @return boolean - true on success, false on error
     */
    private function preFlightCheck($action)
    {
        if ($this->main['so_po_ref_id']) { // see if post date is BEFORE linked ref date, this causes un-closed references
            $refDate = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'post_date', "id={$this->main['so_po_ref_id']}");
            msgDebug("\nChecking post dates, this = {$this->main['post_date']} and so_po_ref id = $refDate");
            if ($refDate > $this->main['post_date']) { return msgAdd(lang('err_gl_bad_post_date')); }
        }
        // Check for same po_num by same customer
        if (getModuleCache('phreebooks', 'settings', 'customers', 'ck_dup_po', false) && in_array($this->main['journal_id'], [9,10,12,13]) && !empty($this->main['purch_order_id'])) {
            $filter[] = "contact_id_b = {$this->main['contact_id_b']} AND journal_id='{$this->main['journal_id']}'";
            $filter[] = "purch_order_id='".addslashes($this->main['purch_order_id'])."'";
            if (!empty($this->main['id'])) { $filter[] = "id<>{$this->main['id']}"; }
            $dup = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'id', implode(' AND ', $filter));
            if ($dup) { return msgAdd(sprintf(lang('err_gl_invoice_num_dup'), lang('journal_main_purch_order_id'), $this->main['purch_order_id'])); }
        }
        $this->currencyConvert('toDefault');
        if ($action!=='delete') { if (!$this->setInvoice()) { return; } }
        return true;
    }

    /**
     * Converts the currency values to the default ISO currency
     * @param string $action - Not Used currently
     * @return boolean null - affects values in journal_main and journal_item
     */
    public function currencyConvert($action=false)
    {
        msgDebug("\nEntering currencyConvert with action = $action");
        if ( $this->main['currency'] == getUserCache('profile', 'currency', false, 'USD')) { return msgDebug(", returning as the currency is already the default!"); } // is already default currency
        if (empty($this->main['currency_rate']) || strlen($this->main['currency'])<>3) { // helps fix invalid currencies, set to default
            $this->main['currency'] = getUserCache('profile', 'currency', false, 'USD');
            $this->main['currency_rate'] = 1;
        }
        $mainFields = ['discount','freight','sales_tax','total_amount']; // table journal_main
        $itemFields = ['debit_amount','credit_amount','full_price','amount','discount','total']; // table journal_item
        switch ($action) {
            case 'toPost':
                msgDebug(", converting to currency: {$this->main['currency']} and rate {$this->main['currency_rate']}");
                foreach ($mainFields as $field) { $this->main[$field] = $this->main[$field] * $this->main['currency_rate']; }
                foreach ($this->items as $idx => $row) { foreach ($itemFields as $field) {
                    if (!isset($this->items[$idx][$field])) { continue; }
                    $this->items[$idx][$field] = $this->items[$idx][$field] * $this->main['currency_rate'];
                } }
                break;
            case 'toDefault':
                msgDebug(", converting from currency: {$this->main['currency']} and rate {$this->main['currency_rate']}");
                foreach ($mainFields as $field) { if (isset($this->main[$field])) { $this->main[$field] = $this->main[$field] / $this->main['currency_rate']; } }
                foreach ($this->items as $idx => $row) { foreach ($itemFields as $field) {
                    if (isset($row[$field])) { $this->items[$idx][$field] = $this->items[$idx][$field] / $this->main['currency_rate']; } } }
                break;
        }
    }

    /**
     * Updates the contact record if the check box is selected on an order form
     * @param char $type - suffix on the post variables to extract the data, choices are b [default] and s
     * @return integer - database contact record ID
     */
    public function updateContact($type='b')
    {
        // allow bypass if no address info passed
        if (!isset($this->main['primary_name_'.$type]) || $this->main['primary_name_'.$type] == '') { return true; }
        $cID   = isset($this->main['contact_id_'.$type]) ? $this->main['contact_id_'.$type] : 0;
        $aID   = isset($this->main['address_id_'.$type]) ? $this->main['address_id_'.$type] : 0;
        $cType = in_array($this->main['journal_id'], [3,4,6,7,17,20,21]) ? 'v' : 'c';
        if ($aID) { $aType = dbGetValue(BIZUNO_DB_PREFIX."address_book", 'type', "address_id=$aID"); }
            else  { $aType = $type=='b' ? ($cID ? 'b' : 'm') : 's'; }
        bizAutoLoad(BIZUNO_LIB."controller/module/contacts/main.php", 'contactsMain');
        $contact = new contactsMain(); // should not need to pass variables
        $_POST['id_'.$type]    = $this->main['id_'.$type]    = $cID; // map the journal fields to contact fields
        // By commenting these, the terms and rep DO NOT UPDATE the contat record. This must be done through the Contacts Manager
//        $_POST['terms_'.$type] = $this->main['terms_'.$type] = $this->main['terms'];
//        $_POST['rep_id_'.$type] = !empty($this->main['rep_id']) ? $this->main['rep_id'] : ''; // map the rep ID
        msgDebug("\nAdding/updating contact with this->main = ".print_r($this->main, true));
        $success = $contact->dbContactSave($cType, "_$type");
        if (!$success) { return; } // record creation failed (permission, problem, etc), stop here
        else { $cID = $success; }
        unset($this->main['id_'.$type]); // unset map variables
        unset($this->main['terms_'.$type]);
        $this->main['contact_id_'.$type] = $cID;
        $this->main['address_id_'.$type] = $contact->dbAddressSave($cID, $aType, "_$type", true);
        msgLog(sprintf(lang('tbd_manager'), lang('contacts_type', $cType))." ".lang('save')." - {$this->main['primary_name_'.$type]} (rID=$cID)");
        return $cID;
    }

    /**
     * Creates/validates the reference number during a post of a new record
     * @return boolean - true on success, false on error
     */
    private function setInvoice()
    {
        msgDebug("\n  Start validating invoice_num ... ");
        $str_field = '';
        $filter = [];
        if (!empty($this->main['invoice_num'])) { // entered a so/po/invoice value, check for dups
            switch ($this->main['journal_id']) {
                case 17: // allow for duplicates in the following journals
                case 18:
                case 22: msgDebug("specified ID and dups allowed, returning OK."); return true; // deposit ticket ID
                case 20: // force the increment of the ref num as this is for payments,  i.e. checks
                    $next_ref = $this->main['invoice_num'];
                    $str_field= 'next_ref_j20';
                    break;
                case  6: // purchases alow duplicate invoice numbers except from same vendor
                    $filter[] = "contact_id_b='{$this->main['contact_id_b']}'";
                    break;
                default:
            }
            $next_ref = $this->main['invoice_num'];
            msgDebug("\nspecified ID, check for dups and increment if necessary");
        } else { // generate a new order/invoice value
            switch ($this->main['journal_id']) { // select the field to fetch the next number
                case  6: if (!$this->main['waiting']) { return msgAdd(lang('err_gl_invoice_num_empty')); }
                case 14: // Allow dups. Otherwise it increments WO-### and other extensions ref's and will cause dups
                case 15:
                case 16: return true;
                case 17: $str_field = 'next_ref_j18'; break;
                case 18: return 'DP'.date('Ymd'); // reference field was left blank, generate a default value of today
                case 19: $str_field = 'next_ref_j12'; break;
                case 21:
                case 22: $str_field = 'next_ref_j20'; break;
                default: $str_field = 'next_ref_j'.$this->main['journal_id'];
            }
            $next_ref = dbGetValue(BIZUNO_DB_PREFIX."current_status", $str_field);
            if (!$next_ref) { return msgAdd(sprintf(lang('GL_ERROR_CANNOT_FIND'), lang('db_next_id'), BIZUNO_DB_PREFIX."current_status")); }
            $this->main['invoice_num'] = $next_ref;
            msgDebug(" generated ID, returning ID# $next_ref");
        }
        // check for dups
        $filter[] = "journal_id='{$this->main['journal_id']}'";
        $filter[] = "invoice_num='".addslashes($next_ref)."'";
        if ($this->main['id']) { $filter[] = "id<>{$this->main['id']}"; }
        $dup = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'id', implode(' AND ', $filter));
        if ($dup) { return msgAdd(sprintf(lang('err_gl_invoice_num_dup'), pullTableLabel("journal_main", 'invoice_num', $this->main['journal_id']), $next_ref)); }
        if (strlen($str_field) > 0) {
            $next_ref++; // use the built in php string increment
            $next_ref = dbWrite(BIZUNO_DB_PREFIX."current_status", [$str_field => $next_ref], 'update');
        }
        return true;
    }

    /**
     * Builds an array of affected gl accounts to reduce the db updates during a post
     * @param array $affectedGlAccts - working gl account list, returned by reference
     * @param array $main - journal_main table record
     * @param array $item - journal_item table record
     * @return null
     */
    private function getAffectedGlAccts(&$affectedGlAccts, $main, $item)
    {
        // For now add the Retained Earnings account since it is also the rounding account
        if (!$re_acct = getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[44]) { msgAdd(lang('err_gl_no_retained_earnings_acct'), 'caution'); }
        if (!in_array($re_acct, $affectedGlAccts)) { $affectedGlAccts[] = $re_acct; }
        if (!in_array($main['gl_acct_id'], $affectedGlAccts)) { $affectedGlAccts[] = $main['gl_acct_id']; }
        foreach ($item as $row) {
            if (!isset($row['gl_account']) || !$row['gl_account']) { continue; } // fixes bug if GL account is not specified, flagged later
            if (!in_array($row['gl_account'], $affectedGlAccts)) { $affectedGlAccts[] = $row['gl_account']; }
            if (isset($row['sku']) && $row['sku']) { // get the cogs data
                $result = dbGetValue(BIZUNO_DB_PREFIX.'inventory', ['gl_inv','gl_cogs'], "sku='{$row['sku']}'");
                if (!empty($result['gl_inv'])  && !in_array($result['gl_inv'],  $affectedGlAccts)) { $affectedGlAccts[] = $result['gl_inv']; }
                if (!empty($result['gl_cogs']) && !in_array($result['gl_cogs'], $affectedGlAccts)) { $affectedGlAccts[] = $result['gl_cogs']; }
            }
        }
    }

    // *********  chart of account support functions  **********
    /**
     * Updates the journal_history table beginning balances and and verifies journal is in balance
     * @note This needs to be run for all journal entries as the Move To and Save As operations may cause an imbalance if skipped. i.e. BUG with Move To from J12 to J09
     * @param integer $period - Period to start update
     * @return boolean - True on success, False on error
     */
    public function updateJournalHistory($period)
    {
        $fy_props = $period <> getModuleCache('phreebooks', 'fy', 'period') ? getPeriodInfo($period) : getModuleCache('phreebooks', 'fy');
        msgDebug("\n  Updating chart history for fiscal year: {$fy_props['fiscal_year']} and period: $period");
        for ($i = $period; $i <= $fy_props['period_max']; $i++) { // update future months
            if (!$this->setGlBalance($i)) { return; }
            if ($i+1 > $fy_props['fy_period_max']) { break; } // end of periods in db, updates will not do anything
            $affected_acct_string = (is_array($this->affectedGlAccts)) ? implode("', '", $this->affectedGlAccts) : '';
            $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_history", "gl_account IN ('$affected_acct_string') AND period=$i");
            foreach ($result as $row) {
                $next_bb = $row['beginning_balance']+$row['debit_amount']-$row['credit_amount'];
                dbWrite(BIZUNO_DB_PREFIX."journal_history", ['beginning_balance'=>$next_bb], 'update', "period=".($i+1)." AND gl_account='{$row['gl_account']}'");
            }
        }
        // see if there is another fiscal year to roll into
        if ($fy_props['fy_period_max'] > $fy_props['period_max']) { // close balances for end of this fiscal year and roll post into next fiscal year
            // select retained earnings account
            $re_acct = getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[44];
            $acct_string = $this->getGLtoClose(); // select list of accounts that need to be closed, adjusted
            // fetch the totals for the closed accounts
            $retained_earnings = dbGetValue(BIZUNO_DB_PREFIX."journal_history", "SUM(beginning_balance+debit_amount-credit_amount)", "gl_account IN ('$acct_string') AND period={$fy_props['period_max']}", false);
            // clear out the expense, sales, cogs, and other year end accounts that need to be closed
            // needs to be before writing retained earnings account, since retained earnings is part of acct_string
            dbWrite(BIZUNO_DB_PREFIX."journal_history", ['beginning_balance'=>0], 'update', "gl_account IN ('$acct_string') AND period=".($fy_props['period_max'] + 1));
            // update the retained earnings account
            dbWrite(BIZUNO_DB_PREFIX."journal_history", ['beginning_balance'=>$retained_earnings], 'update', "gl_account='$re_acct' AND period=".($fy_props['period_max'] + 1));
            // now continue rolling in current post into next fiscal year
            if (!$this->updateJournalHistory($fy_props['period_max'] + 1)) { return; }
        }
        // all historical chart of account balances from period on should be OK at this point.
        msgDebug("\n  end Updating chart history periods. Fiscal Year: {$fy_props['fiscal_year']}");
        return true;
    }

    /**
     * This method handles the update to the journal for a given period, also adjusts for rounding errors, if necessary.
     * @param integer $period - fiscal year period to update.
     * @return boolean - True on success, False on error
     */
    private function setGlBalance($period)
    {
        $result = dbGetValue(BIZUNO_DB_PREFIX."journal_history", ["SUM(beginning_balance) AS balance, SUM(debit_amount) AS debit", "SUM(credit_amount) AS credit"], "period=$period", false);
        $adjustment = $result['balance'] + $result['debit'] - $result['credit'];
        msgDebug("\n    Validating balances for period: $period, adjustment = $adjustment and balance = {$result['balance']} and debits = {$result['debit']} and credits = {$result['credit']}");
        $adj_gl_account = getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[44]; // retained earnings for now
        $tolerance   = 0.02; // i.e. 2 cents in USD
        $balance_total= round($result['balance'],4);
        $debit_total  = round($result['debit'],  4);
        $credit_total = round($result['credit'], 4);
        if (abs($debit_total - $credit_total) > $tolerance || abs($adjustment) > $tolerance) {
            return msgAdd(sprintf(lang('err_gl_out_of_balance'), $balance_total, $debit_total, $credit_total, $period), 'trap');
        }
        if (abs($balance_total) > 0.001) { // Rounding errors in beginning balance
            msgDebug("\n\n\n      Adjusting balance for beginning balance not zero, adjustment = {$result['balance']} and adjusting gl account = $adj_gl_account");
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_history SET debit_amount=debit_amount-{$result['balance']} WHERE period=".($period-1)." AND gl_account='$adj_gl_account'");
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_history SET beginning_balance=beginning_balance-{$result['balance']}, debit_amount=debit_amount-{$result['balance']} WHERE period=$period AND gl_account='$adj_gl_account'");
        }
        if (abs($debit_total - $credit_total) > 0.001) { // Rounding errors in current period
            msgDebug("\n\n\n      Adjusting rounding error for current period, adjustment = ".($result['debit'] - $result['credit'])." and adjusting gl account = $adj_gl_account");
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_history SET debit_amount=debit_amount-".($result['debit'] - $result['credit'])." WHERE period=$period AND gl_account='$adj_gl_account'");
        }
        msgDebug(" ... End Validating trial balance.");
        return true;
    }

    /**
     * retrieves an CSV list of gl accounts that needs to be closed at end of fiscal year
     * @return string - comma seperated list of gl accounts
     */
    private function getGLtoClose()
    {
        $acct_list = [];
        foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $row) {
            if (in_array($row['type'], [30,32,34,42,44])) { $acct_list[] = $row['id']; }
        }
        return implode("','",$acct_list);
    }

    /**
     * Special case when re-posting and the post date is changed, need to fetch original post date
     * from original record to include in original transaction
     * @param integer $recur_id - recur ID identifier
     * @param integer $id - main record ID of first record
     * @return array - List of ID's, invoice_numbers and dates of current and future transactions.
     */
    public function get_recur_ids($recur_id, $id=0)
    {
        $output = [];
        if ($id) {
            $post_date = dbGetValue(BIZUNO_DB_PREFIX."journal_main", "post_date", "id=$id");
            $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", "recur_id=$recur_id AND post_date>='$post_date'", "post_date", ['id','post_date','period','invoice_num','terminal_date']);
            foreach ($result as $row) { $output[] = [
                'id'           => $row['id'],
                'post_date'    => $row['post_date'],
                'period'       => $row['period'],
                'invoice_num'  => $row['invoice_num'],
                'terminal_date'=> $row['terminal_date']];
            }
        }
        return $output;
    }

    private function guessMethod($rID=0)
    {
        $output= '';
        $item  = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'description', "ref_id='$rID' && gl_type='ttl'");
        $tmp   = explode(';', $item);
        foreach ($tmp as $piece) {
            if (strpos($piece, 'method:') === 0) { $output = substr($piece, 7); }
        }
        return $output;
    }
}