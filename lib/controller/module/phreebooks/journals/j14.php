<?php
/*
 * PhreeBooks journal class for Journal 14, Inventory Assembly
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
 * @version    2.x Last Update: 2018-03-08
 * @filesource /lib/controller/module/phreebooks/journals/j14.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/journals/common.php");

class j14 extends jCommon
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
     * Get re-post records - applies to journals 6, 7, 12, 13, 14, 15, 16, 19, 21
     * @return array - journal id's that need to be re-posted as a result of this post
     */
    public function getRepostData()
    {
		msgDebug("\n  Checking for re-post records ... ");
        $out1 = [];
        $out2 = array_merge($out1, $this->getRepostInv());
        $out3 = array_merge($out2, $this->getRepostInvCOG());
//      $out4 = array_merge($out3, $this->getRepostInvAsy());
        $out5 = array_merge($out3, $this->getRepostPayment());
        msgDebug(" end Checking for Re-post.");
        return $out5;
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
     * @return boolean true on success, null on error
     */
	private function postInventory()
    {
		msgDebug("\n  Posting Inventory ...");
		$ref_field = false;
        $ref_closed= false;
		$str_field = 'qty_stock';
		// adjust inventory stock status levels (also fills inv_list array)
		$item_rows_to_process = count($this->item); // NOTE: variable needs to be here because $this->item may grow within for loop (COGS)
// the cogs rows are added after this loop ..... the code below needs to be rewritten
		for ($i = 0; $i < $item_rows_to_process; $i++) {
            if (!in_array($this->item[$i]['gl_type'], ['itm','adj','asy','xfr'])) { continue; }
			if (isset($this->item[$i]['sku']) && $this->item[$i]['sku'] <> '') {
				$inv_list = $this->item[$i];
                $inv_list['price'] = $this->item[$i]['qty'] ? (($this->item[$i]['debit_amount'] + $this->item[$i]['credit_amount']) / $this->item[$i]['qty']) : 0;
				$assy_cost = $this->setAssyCost($inv_list); // for assembly parts list
                if ($assy_cost === false) { return; }// there was an error
			}
		}
		// update inventory status
		foreach ($this->item as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; } // skip all rows without a SKU
			$item_cost = $full_price = 0;
            // commented out as there is a tool now plus this is not the latest cost but based on COGS which may be very different from current price.
//          if ($row['gl_type'] == 'asy' && $row['qty'] > 0) { $item_cost = $row['debit_amount'] / $row['qty']; } // only for the item being assembled
            if (!$this->setInvStatus($row['sku'], $str_field, $row['qty'], $item_cost, $row['description'], $full_price)) { return false; }
		}
		// build the cogs item rows
        $this->setInvCogItems();
		msgDebug("\n  end Posting Inventory.");
		return true;
	}

	/**
     * unPost inventory
     * @return boolean true on success, null on error
     */
	private function unPostInventory()
    {
		msgDebug("\n  unPosting Inventory ...");
        if (!$this->rollbackCOGS()) { return false; }
		for ($i = 0; $i < count($this->item); $i++) {
            if (!isset($this->item[$i]['sku']) || !$this->item[$i]['sku']) { continue; }
            if (!$this->setInvStatus($this->item[$i]['sku'], 'qty_stock', -$this->item[$i]['qty'])) { return; }
        }
        if ($this->main['so_po_ref_id'] > 0) { $this->setInvRefBalances($this->main['so_po_ref_id'], false); }
		dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."inventory_history WHERE ref_id = {$this->main['id']}");
		dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_cogs_usage WHERE journal_main_id={$this->main['id']}");
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_cogs_owed  WHERE journal_main_id={$this->main['id']}");
		msgDebug("\n  end unPosting Inventory.");
		return true;
	}

	/**
     * Checks and sets/clears the closed status of a journal entry
     * Affects journals - 3, 7, 9, 13, 14, 15, 16
     * @param string $action - [default: 'post']
     * @return boolean true
     */
	private function setStatusClosed($action='post')
    {
		msgDebug("\n  Checking for closed entry. action = $action, returning with no action.");
		return true;
	}
}
