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
 * @copyright  2008-2018, PhreeSoft Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-06-29
 * @filesource /lib/controller/module/phreebooks/journals/j10.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/journals/common.php");

class j10 extends jCommon
{
    public $journalID = 10;

	function __construct($main=[], $item=[])
    {
		parent::__construct();
        $this->main = $main;
		$this->item = $item;
	}

/*******************************************************************************************************************/
// START Edit Methods
/*******************************************************************************************************************/
    /**
     * Pulls the data for the specified journal and populates the structure
     * @param array $data - current working structure
     * @param array $structure - table structures
     * @param integer $rID - record id of the transaction to load from the database
     */
    public function getDataMain(&$data, $structure, $rID=0)
    {
        if ($rID)                    { $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id=$rID"); }
//      elseif (sizeof($references)) { $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id={$references[0]}");  } // not used for this journal
        else                         { $dbData = []; } // should never happen but just in case
        if ($dbData['currency'] <> getUserCache('profile', 'currency', false, 'USD')) { // convert to posted currency
            $mainFields = ['discount','sales_tax','total_amount'];
            foreach ($mainFields as $field) { $dbData[$field] = $dbData[$field] * $dbData['currency_rate']; }
        }
        if ($this->action == 'inv') { // clear some fields to convert purchase/sales order or quote to receive/invoice
            $dbData['journal_id']   = $this->journalID;
            $dbData['so_po_ref_id'] = $dbData['id'];
            $dbData['id']           = '';
            $dbData['post_date']    = date('Y-m-d');
            $dbData['terminal_date']= date('Y-m-d'); // get default based on type
            if (in_array($this->journalID, [6]))  { 
                $dbData['purch_order_id']= $dbData['invoice_num'];
                $dbData['terminal_date'] = localeCalculateDate(date('Y-m-d'), 30);
            }
            if (in_array($this->journalID, [12])) {
                $this->soNum = $dbData['invoice_num'];
                if (getModuleCache('extShipping', 'properties', 'status')) { $dbData['waiting'] = '1'; } // set waiting to ship flag
            }
            $dbData['invoice_num']= '';
// @todo this should be a setting as some want the rep to flow from the Sales Order for commissions while others just care about who fills the order.
//						$dbData['rep_id']     = getUserCache('profile', 'contact_id', false, '0');
        }
        dbStructureFill($data['fields']['main'], $dbData);
        // now work the line items
        if ($rID)                    { $data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$rID"); }
//      elseif (sizeof($references)) { $data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id={$references[0]}"); }
        else                         { $data['items'] = []; } // should never happen but just in case
        if ($dbData['currency'] <> getUserCache('profile', 'currency', false, 'USD')) { // convert to posted currency
            $itemFields = ['debit_amount','credit_amount','full_price'];
            foreach ($data['items'] as $idx => $row) { foreach ($itemFields as $field) {
                $data['items'][$idx][$field] = $data['items'][$idx][$field] * $dbData['currency_rate'];
            } }
        }
        if ($dbData['so_po_ref_id'] || $this->action == 'inv') { // complex merge the two by item, keep the rest from the rID only
            if ($this->action == 'inv') {
                $sopo = $data['items'];
                foreach ($data['items'] as $idx => $row) {
                    unset($data['items'][$idx]['id']);
                    unset($data['items'][$idx]['ref_id']);
                    if ($row['gl_type'] == 'itm') { unset($data['items'][$idx]); }
                }
            } else {
                $sopo = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "ref_id={$dbData['so_po_ref_id']}");
            }
            foreach ($sopo as $row) {
                if ($row['gl_type'] <> 'itm') { continue; } // not an item record, skip
                $inList = false;
                foreach ($data['items'] as $idx => $item) {
                    if ($row['item_cnt'] == $item['item_cnt']) {
                        $data['items'][$idx]['bal'] = $row['qty'];
                        $inList = true;
                        break;
                    }
                }
                if (!$inList) { // add unposted so/po row, create row with no quantity on this record
                    $row['price']        = ($row['credit_amount']+$row['debit_amount'])/$row['qty'];
                    $row['credit_amount']= 0;
                    $row['debit_amount'] = 0;
                    $row['total']        = 0;
                    $row['bal']          = $row['qty'];
                    $row['qty']          = '';
                    $row['item_ref_id']  = $row['id'];
                    $row['id']           = '';
                    $data['items'][]     = $row;
                }
            }
            $temp = []; // now sort to get item_cnt in order
            foreach ($data['items'] as $key => $value) { $temp[$key] = $value['item_cnt']; }
            array_multisort($temp, SORT_ASC, $data['items']);
        }
        if (in_array($this->journalID, [4,10])) { // fill qty received for SO and PO
            foreach ($data['items'] as $idx => $row) {
                $data['items'][$idx]['bal'] = dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(qty)", "item_ref_id={$row['id']} AND gl_type='itm'", false);
            }
        }
        $debitCredit = in_array($this->journalID, [3,4,6,13,21]) ? 'debit' : 'credit';
        $dbData = [];
        if (sizeof($data['items']) > 0) {
            foreach ($data['items'] as $row) {
                if ($row['gl_type'] <> 'itm') { continue; } // not an item record, skip
                if (empty($row['bal'])) { $row['bal'] = 0; }
                if (empty($row['qty'])) { $row['qty'] = 0; }
                if (is_null($row['sku'])) { $row['sku'] = ''; } // bug fix for easyui combogrid, doesn't like null value
                $row['description'] = str_replace("\n", " ", $row['description']); // fixed bug with \n in description field
                if (!isset($row['price'])) { $row['price'] = $row['qty'] ? (($row['credit_amount']+$row['debit_amount'])/$row['qty']) : 0; }
                if ($row['item_ref_id']) {
                    $filled    = dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(qty)", "item_ref_id={$row['item_ref_id']} AND gl_type='itm'", false);
                    $row['bal']= $row['bal'] - $filled + $row['qty']; // so/po - filled prior + this order
                }
                if ($row['sku']) { // now fetch some inventory details for the datagrid
                    $inv     = dbGetValue(BIZUNO_DB_PREFIX."inventory", ['qty_stock', 'item_weight'], "sku='{$row['sku']}'");
                    $inv_adj = in_array($this->journalID, [3,4,6,13,21]) ? -$row['qty'] : $row['qty'];
                    $row['qty_stock']  = $inv['qty_stock'] + $inv_adj;
                    $row['item_weight']= $inv['item_weight'];
                }
                $dbData[] = $row;
            }
        }
        if (!in_array($this->journalID, [2])) {
            if ($debitCredit=='credit') { $map['credit_amount']= ['type'=>'field','index'=>'total']; }
            if ($debitCredit=='debit')  { $map['debit_amount'] = ['type'=>'field','index'=>'total']; }
        }
        $data['jsHead']['datagridData'] = formatDatagrid($dbData, 'datagridData', $structure['journal_item'], $map);
    }
    
    /**
     * Tailors the structure for the specific journal
     * @param array $data - current working structure
     * @param integer $rID - Database record id of the journal main record
     * @param integer $security - Users security level
     */
    public function getDataItem(&$data)
    {
        $data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'c');
        $data['fields']['main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');
        $isWaiting = isset($data['fields']['main']['waiting']['attr']['checked']) && $data['fields']['main']['waiting']['attr']['checked'] ? '1' : '0';
        $data['fields']['main']['waiting'] = ['attr'=>  ['type'=>'hidden', 'value'=>$isWaiting]]; // field not used
        $data['divs']['divDetail'] = ['order'=>50,'type'=>'divs','classes'=>['areaView'],'attr'=>['id'=>'pbDetail'],'divs'=>[
            'billAD' => ['order'=>20,'type'=>'address','label'=>lang('bill_to'),'classes'=>['blockView'],'attr'=>['id'=>'address_b'],'content'=>$this->cleanAddress($data['fields']['main'], '_b'),
                'settings'=>['suffix'=>'_b','search'=>true,'copy'=>true,'update'=>true,'validate'=>true,'fill'=>'both','required'=>true,'store'=>false]],
            'shipAD' => ['order'=>30,'type'=>'address','label'=>lang('ship_to'),'classes'=>['blockView'],'attr'=>['id'=>'address_s'],'content'=>$this->cleanAddress($data['fields']['main'], '_s'),
                'settings'=>['suffix'=>'_s','search'=>true,'update'=>true,'validate'=>true,'drop'=>true]],
            'props'  => ['order'=>40,'type'=>'fields','classes'=>['blockView'],'attr'=>['id'=>'pbProps'],'fields'=>$this->getProps($data)],
            'totals' => ['order'=>50,'type'=>'totals','classes'=>['blockViewR'],'attr'=>['id'=>'pbTotals'],'content'=>$data['totals_methods']]]];
        $data['divs']['dgItems']= ['order'=>60,'type'=>'datagrid','key'=>'item'];
        $data['divs']['other']  = ['order'=>70,'type'=>'html','html'=>'<div id="shippingEst"></div><div id="shippingVal"></div>'];
    }

    /**
     * Configures the journal entry properties (other than address and items)
     * @param array $data - current working structure
     * @return array - List of fields to show with the structure
     */
    private function getProps($data)
    {
        $data['fields']['main']['sales_order_num'] = ['label'=>lang('journal_main_invoice_num_10'),'attr'=>['value'=>isset($this->soNum)?$this->soNum:'','readonly'=>'readonly']];
        return [
            'id'             => $data['fields']['main']['id'],
            'journal_id'     => $data['fields']['main']['journal_id'],
            'so_po_ref_id'   => $data['fields']['main']['so_po_ref_id'],
            'terms'          => $data['fields']['main']['terms'],
            'override_user'  => $data['override_user'],
            'override_pass'  => $data['override_pass'],
            'recur_id'       => $data['fields']['main']['recur_id'],
            'recur_frequency'=> $data['recur_frequency'],
            'item_array'     => $data['item_array'],
            'xChild'         => ['attr'=>['type'=>'hidden']],
            'xAction'        => ['attr'=>['type'=>'hidden']],
            // Displayed
            'purch_order_id' => array_merge(['break'=>true], $data['fields']['main']['purch_order_id']),
            'terms_text'     => $data['terms_text'],
            'terms_edit'     => array_merge(['break'=>true], $data['terms_edit']),
            'invoice_num'    => array_merge(['break'=>true], $data['fields']['main']['invoice_num']),
            'waiting'        => array_merge(['break'=>true], $data['fields']['main']['waiting']),
            'post_date'      => array_merge(['break'=>true], $data['fields']['main']['post_date']),
            'terminal_date'  => array_merge(['break'=>true], $data['fields']['main']['terminal_date']),
            'store_id'       => array_merge(['break'=>true], $data['fields']['main']['store_id']),
            'rep_id'         => array_merge(['break'=>true], $data['fields']['main']['rep_id']),
            'currency'       => array_merge(['break'=>true], $data['fields']['main']['currency']),
            'closed'         => array_merge(['break'=>true], $data['fields']['main']['closed'])];
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
		msgDebug("\n  j10 - Checking for re-post records ... ");
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
