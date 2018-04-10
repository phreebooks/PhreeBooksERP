<?php
/*
 * PhreeBooks journal class for Journal 10, Customer Sales Order
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

 * @filesource /lib/controller/module/phreebooks/journals/j10.php
 * 
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/journals/common.php");

class j10 extends jCommon
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
        if (!$this->postInventory())     { return; }
        if (!$this->postJournalHistory()){ return; }
        if (!$this->setStatusClosed())   { return; }
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
        msgDebug("\n*************** end unPosting Journal ******************* id = {$this->main['id']}\n\n");
		return true;
	}

    /**
     * Get re-post records - applies to journals 3, 4, 9, 10
     * @return array - journal id's that need to be re-posted as a result of this post
     */
    public function getRepostData()
    {
		msgDebug("\n  Checking for re-post records ... ");
        return $this->getRepostSale();
	}

	/**
     * Post journal item array to journal history table
     * applies to journal 3, 4, 9, 10
     * @return boolean - true
     */
    private function postJournalHistory()
    {
		msgDebug("\n  Posting Chart Balances... end Posting Chart Balances with no action.");
		return true;
	}

	/**
     * unPosts journal item array from journal history table
     * applies to journal 3, 4, 9, 10
     * @return boolean - true
     */
	private function unPostJournalHistory() {
		msgDebug("\n  unPosting Chart Balances... end unPosting Chart Balances with no action.");
		return true;
	}

	/**
     * Post inventory
     * @return boolean true on success, null on error
     */
	private function postInventory()
    {
		msgDebug("\n  Posting Inventory ...");
		foreach ($this->item as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; } // skip all rows without a SKU
            if (!$this->setInvStatus($row['sku'], 'qty_so', $row['qty'], 0, $row['description'])) { return false; }
		}
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
        foreach ($this->item as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; }
            if (!$this->setInvStatus($row['sku'], 'qty_so', -$row['qty'])) { return; }
        }
		msgDebug("\n  end unPosting Inventory.");
		return true;
	}

    /**
     * Tests for CLOSED checkbox to adjust qty on SO and avoid re-posting. If so all other post values are ignored.
     * @param type $data - Journal POST data (and uPost data to test against
     */
    public function quickPost($data)
    {
        // check uMain vs main for closed box checked.
        if (!empty($data['main']['closed']) && empty($data['uMain']['closed'])) {
            $item_array = $this->getStkBalance($data['main']['id']); // user wants to force close the journal entry
            foreach ($item_array as $row) {
                $bal = $row['ordered'] - $row['processed'];
                if ($bal <= 0) { continue; }
                $type = dbGetValue(BIZUNO_DB_PREFIX.'inventory', 'inventory_type', "sku='{$row['sku']}'");
                if (strpos(COG_ITEM_TYPES, $type) === false) { continue; }
                dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."inventory SET qty_so=qty_so-$bal WHERE sku='{$row['sku']}'");
            }
            dbWrite(BIZUNO_DB_PREFIX.'journal_main', ['closed'=>'1','closed_date'=>date('Y-m-d')], 'update', "id='{$data['main']['id']}'");
            return true;
        }
    }
    
	/**
     * Checks and sets/clears the closed status of a journal entry
     * Affects journals - 4, 10
     * @return boolean true
     */
	private function setStatusClosed()
    {
		// closed can occur many ways including:
		//   forced closure through so/po form (from so/po journal - adjust qty on so/po)
        //     NOTE: this cannot happen here as dependent sales/purchases will re-open the entry
		//   all quantities are reduced to zero (from so/po journal - should be deleted instead but it's possible)
		msgDebug("\n  Checking for closed entry.");
        // determine if all items quantities have been entered as zero
        $item_rows_all_zero = true;
        for ($i = 0; $i < count($this->item); $i++) {
            if ($this->item[$i]['qty'] && $this->item[$i]['gl_type'] == 'itm') { $item_rows_all_zero = false; } // at least one qty is non-zero
        }
        if ($item_rows_all_zero) { $this->setCloseStatus($this->main['id'], true); }
		return true;
	}
}
