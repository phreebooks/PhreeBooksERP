<?php
/*
 * PhreeBooks journal class for Journal 6, Vendor Purchase
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
 * @copyright  2008-2018, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-06-29
 * @filesource /lib/controller/module/phreebooks/journals/j06.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/journals/common.php");

class j06 extends jCommon
{
    public $journalID = 6;

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
        if ($rID) { $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id=$rID"); }
        elseif ($this->action == 'inv') { $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id=".clean('iID', 'integer', 'get')); }
        else      { $dbData = []; } // should never happen but just in case
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
        if ($rID) { $data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$rID"); }
        elseif ($this->action == 'inv') { $data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=".clean('iID', 'integer', 'get')); }
        else      { $data['items'] = []; } // should never happen but just in case
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
        if ($debitCredit=='credit') { $map['credit_amount']= ['type'=>'field','index'=>'total']; }
        if ($debitCredit=='debit')  { $map['debit_amount'] = ['type'=>'field','index'=>'total']; }
        $data['jsHead']['datagridData'] = formatDatagrid($dbData, 'datagridData', $structure['journal_item'], $map);
    }
    
    /**
     * Tailors the structure for the specific journal
     * @param array $data - current working structure
     * @param integer $rID - Database record id of the journal main record
     * @param integer $security - Users security level
     */
    public function getDataItem(&$data, $rID=0, $cID=0, $security=0)
    {
        $data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'v');
        $data['fields']['main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');
        if (!$rID) { // new order
            $data['fields']['main']['closed'] = ['attr'=>['type'=>'hidden', 'value'=>'0']];
        } elseif (isset($data['fields']['main']['closed']['attr']['checked']) && $data['fields']['main']['closed']['attr']['checked'] == 'checked') {
            $data['fields']['main']['closed'] = ['attr'=>['type'=>'hidden', 'value'=>'1']];
            $data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('paid')."</span>";
        } else {
            $data['fields']['main']['closed'] = ['attr'=>['type'=>'hidden', 'value'=>'0']];
            $data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('unpaid')."</span>";
        }
        if ($rID || $this->action=='inv') { 
            $data['datagrid']['item']['source']['actions']['fillAll'] = ['order'=>10, 'html'=>  ['icon'=>'select_all','size'=>'large','hidden'=>$security>1?false:true,'events'=>  ['onClick'=>"phreebooksSelectAll();"]]];
        }
        $data['fields']['main']['invoice_num']['tooltip'] = lang('err_gl_invoice_num_empty');
        unset($data['toolbars']['tbPhreeBooks']['icons']['print']);
        $data['fields']['main']['purch_order_id']['attr']['readonly'] = 'readonly';
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
		msgDebug("\n  j06 - Checking for re-post records ... ");
        $out1 = [];
        $out2 = array_merge($out1, $this->getRepostInvAvg()); // journal 6 only
        $out3 = array_merge($out2, $this->getRepostInv());
        $out4 = array_merge($out3, $this->getRepostInvCOG());
        $out5 = array_merge($out4, $this->getRepostPayment());
        msgDebug("\n  j06 - End Checking for Re-post.");
        return $out5;
	}

    /**
     * 
     * @return type
     */
    private function getRepostInvAvg()
    {
        $output= [];
        $skus  = [];
        $cogsTypes = explode(',',COG_ITEM_TYPES);
        foreach ($this->item as $row) { if (isset($row['sku']) && $row['sku'] <> '') { $skus[] = $row['sku']; } }
        if (sizeof($skus) > 0) {
            $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory", "sku IN ('".implode("','", $skus)."') AND inventory_type IN ('".implode("','", $cogsTypes)."') AND cost_method='a'");
            $askus = [];
            foreach ($result as $row) { $askus[] = $row['sku']; }
            if (!sizeof($askus)) { return $output; }
            $sql = "SELECT m.id, m.post_date FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
                WHERE i.sku IN ('".implode("', '", $askus)."') AND m.post_date>'{$this->main['post_date']}' AND journal_id IN (6,12)";
            $stmt2  = dbGetResult($sql);
            $result2= $stmt2->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result2 as $row) {
                msgDebug("\n    getRepostInvAvg is queing average cost id = ".$row['id']);
                $idx = padRef($row['post_date'], $row['id'], 8); // for average costing we need to keep the date sequence so make them all neutral
                $output[$idx] = $row['id'];
            }
        }
        return $output;
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
        if (isset($this->main['so_po_ref_id']) && $this->main['so_po_ref_id'] > 0) {
            $refJournal = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['journal_id', 'closed'], "id={$this->main['so_po_ref_id']}");
            // if the so/po was closed manually, don't adjust here as it was already accounted for in the so/po re-post
            $ref_closed= $refJournal['closed'];
            if (in_array($refJournal['journal_id'], [4, 10])) { // only adjust if a sales order or purchase order. fixes bug for quotes
                $ref_field = $this->main['journal_id']==6 ? 'qty_po' : 'qty_so';
            }
        }
		// adjust inventory stock status levels (also fills inv_list array)
		$item_rows_to_process = count($this->item); // NOTE: variable needs to be here because $this->item may grow within for loop (COGS)
// the cogs rows are added after this loop ..... the code below needs to be rewritten
		for ($i = 0; $i < $item_rows_to_process; $i++) {
            if (!in_array($this->item[$i]['gl_type'], ['itm','adj','asy','xfr'])) { continue; }
			if (isset($this->item[$i]['sku']) && $this->item[$i]['sku'] <> '') {
				$inv_list = $this->item[$i];
                $inv_list['price'] = $this->item[$i]['qty'] ? (($this->item[$i]['debit_amount'] + $this->item[$i]['credit_amount']) / $this->item[$i]['qty']) : 0;
                if (!$this->calculateCOGS($inv_list)) { return false; }
			}
		}
        if ($this->main['so_po_ref_id'] > 0) { $this->setInvRefBalances($this->main['so_po_ref_id']); }
		// update inventory status
		foreach ($this->item as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; } // skip all rows without a SKU
			$item_cost = $full_price = 0;
            if (getModuleCache('inventory', 'settings', 'general', 'auto_cost') == 'PR' && $row['qty']) { 
                $item_cost = $row['debit_amount'] / $row['qty'];
            }
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
     * Affects journals - 6, 12, 19, 21
     * @param string $action - [default: 'post']
     * @return boolean true
     */
	private function setStatusClosed($action='post')
    {
		msgDebug("\n  Checking for closed entry. action = $action");
        if ($this->main['so_po_ref_id']) {	// make sure there is a reference po/so to check
            $this->getStkBalance($this->main['so_po_ref_id'], $this->main['id']);
            $ordr_diff = false;
            if (isset($this->so_po_balance_array) && sizeof($this->so_po_balance_array) > 0) {
                foreach ($this->so_po_balance_array as $counts) { if ($counts['ordered'] > $counts['processed']) { $ordr_diff = true; } }
            }
            if ($ordr_diff) { // open it, there are still items to be processed
                $this->setCloseStatus($this->main['so_po_ref_id'], false);
            } else { // close the order
                $this->setCloseStatus($this->main['so_po_ref_id'], true);
            }
        }
        // close if the invoice/inv receipt total is zero
        if (roundAmount($this->main['total_amount'], $this->rounding) == 0) { // zero balance, close it as no payment is needed 
            $this->setCloseStatus($this->main['id'], true);
        } elseif ($this->main['closed']) { // if edit and was closed and no longer closed, re-open it, [then should be opened earlier, how do we know here?]
            $this->setCloseStatus($this->main['id'], false);
        }
		return true;
	}
}
