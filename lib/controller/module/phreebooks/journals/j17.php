<?php
/*
 * PhreeBooks journal class for Journal 17, Vendor Refund
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
 * @version    2.x Last Update: 2017-06-01

 * @filesource /lib/controller/module/phreebooks/journals/j17.php
 * 
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/journals/common.php");

class j17 extends jCommon
{

	function __construct($main, $item)
    {
		parent::__construct();
        $this->main = $main;
		$this->item = $item;
	}

/*******************************************************************************************************************/
// START Post Journal Function
/*******************************************************************************************************************/
	public function Post()
    {
        msgDebug("\n/********* Posting Journal main ... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        $this->setItemDefaults(); // makes sure the journal_item fields have a value
        $this->unSetCOGSRows(); // they will be regenerated during the post
        $this->postMain();
        $this->postItem();
        if (!$this->postInventory())         { return; }
        if (!$this->postJournalHistory())    { return; }
        if (!$this->setStatusClosed('post')) { return; }
        msgDebug("\n*************** end Posting Journal ******************* id = {$this->main['id']}\n\n");
		return true;
	}

	public function unPost()
    {
        msgDebug("\n/********* unPosting Journal main ... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        if (!$this->unPostJournalHistory())    { return; }	// unPost the chart values before inventory where COG rows are removed
        if (!$this->unPostInventory())         { return; }
		$this->unPostMain();
        $this->unPostItem();
        if (!$this->setStatusClosed('unPost')) { return; } // check to re-open predecessor entries 
        msgDebug("\n*************** end unPosting Journal ******************* id = {$this->main['id']}\n\n");
		return true;
	}

    /**
     * Get re-post records - applies to journals 2, 17, 18, 20, 22
     * @return array - empty
     */
    public function getRepostData()
    {
		msgDebug("\n  Checking for re-post records ... end check for Re-post with no action.");
        return [];
	}

	/**
     * Post journal item array to journal history table
     * applies to journal 2, 6, 7, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22
     * @return boolean - true
     */
    private function postJournalHistory()
    {
		msgDebug("\n  Posting Chart Balances...");
        if ($this->setJournalHistory()) { return true; }
	}

	/**
     * unPosts journal item array from journal history table
     * applies to journal 2, 6, 7, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22
     * @return boolean - true
     */
	private function unPostJournalHistory() {
		msgDebug("\n  unPosting Chart Balances...");
        if ($this->unSetJournalHistory()) { return true; }
	}

	/**
     * Post inventory
     * applies to journal 2, 3, 9, 17, 18, 20, 22
     * @return boolean true on success, null on error
     */
    private function postInventory()
    {
		msgDebug("\n  Posting Inventory ... end Posting Inventory not requiring any action.");
		return true;
	}

	/**
     * unPost inventory
     * applies to journal 2, 3, 9, 17, 18, 20, 22
     * @return boolean true on success, null on error
     */
	private function unPostInventory()
    {
		msgDebug("\n  unPosting Inventory ... end unPosting Inventory with no action.");
		return true;
	}

	/**
     * Checks and sets/clears the closed status of a journal entry
     * Affects journals - 17, 18, 20, 22
     * @param string $action - [default: 'post']
     * @return boolean true
     */
	private function setStatusClosed($action='post')
    {
		// closed can occur many ways including:
		//   forced closure through so/po form (from so/po journal - adjust qty on so/po)
		//   all quantities are reduced to zero (from so/po journal - should be deleted instead but it's possible)
		//   editing quantities on po/so to match the number received (from po/so journal)
		//   receiving all (or more) po/so items through one or more purchases/sales (from purchase/sales journal)
		msgDebug("\n  Checking for closed entry. action = $action");
        if ($action == 'post') {
            $temp = [];
            for ($i = 0; $i < count($this->item); $i++) { // fetch the list of paid invoices
                if (isset($this->item[$i]['item_ref_id']) && $this->item[$i]['item_ref_id']) {
                    $temp[$this->item[$i]['item_ref_id']] = true;
                }
            }
            $invoices = array_keys($temp);
            for ($i = 0; $i < count($invoices); $i++) {
                $stmt = dbGetResult("SELECT m.id, m.journal_id, SUM(i.debit_amount - i.credit_amount) AS total_amount 
                    FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id 
                    WHERE m.id={$invoices[$i]} AND i.gl_type<>'ttl'");
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($result['journal_id']==2) glFindAPacct($result); // special case for payables entered through general journal
                $total_billed = roundAmount($result['total_amount'], $this->rounding);
                $stmt = dbGetResult("SELECT SUM(i.debit_amount - i.credit_amount) AS total_amount 
                    FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id 
                    WHERE i.item_ref_id={$invoices[$i]} AND i.gl_type='pmt'");
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $total_paid = roundAmount($result['total_amount'], $this->rounding);
                msgDebug("\n    total_billed = $total_billed and total_paid = $total_paid");
                $this->setCloseStatus($invoices[$i], $total_billed == $total_paid ? true : false); // either close or re-open
            }
        } else { // unpost - re-open the purchase/invoices affected
            for ($i = 0; $i < count($this->item); $i++) {
                if ($this->item[$i]['item_ref_id']) { $this->setCloseStatus($this->item[$i]['item_ref_id'], false); }
            }
        }
		return true;
	}
}
