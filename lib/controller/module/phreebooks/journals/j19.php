<?php
/*
 * PhreeBooks journal class for Journal 19, Customer Point of Sale
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-08-27
 * @filesource /lib/controller/module/phreebooks/journals/j19.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journals/common.php", 'jCommon');

class j19 extends jCommon
{
    public $journalID = 19;

    function __construct($main=[], $item=[])
    {
        parent::__construct();
        $this->main = $main;
        $this->items= $item;
    }

/*******************************************************************************************************************/
// START Edit Methods
/*******************************************************************************************************************/
    /**
     * Tailors the structure for the specific journal
     */
    public function getDataItem()
    {
        $structure = dbLoadStructure(BIZUNO_DB_PREFIX.'journal_item', $this->journalID);
        if ($this->action == 'inv') {
            foreach ($this->items as $idx => $row) { // clear all of the id's
                if ($row['gl_type'] == 'itm') {
                    $this->items[$idx]['item_ref_id'] = $this->items[$idx]['id'];
                    $this->items[$idx]['price'] = $row['qty'] ? (($row['credit_amount']+$row['debit_amount'])/$row['qty']) : 0;
                    $this->items[$idx]['total'] = 0;
                    $this->items[$idx]['bal'] = $row['qty'];
                    $this->items[$idx]['qty'] = 0;
                }
                unset($this->items[$idx]['id']);
                unset($this->items[$idx]['ref_id']);
            }
        }
        if (!empty($this->main['so_po_ref_id'])) { // complex merge the two by item, keep the rest from the rID only
            $sopo = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "ref_id={$this->main['so_po_ref_id']}");
            foreach ($sopo as $row) {
                if ($row['gl_type'] <> 'itm') { continue; } // not an item record, skip
                $inList = false;
                foreach ($this->items as $idx => $item) {
                    if ($row['item_cnt'] == $item['item_cnt']) {
                        $this->items[$idx]['bal'] = $row['qty'];
                        $inList = true;
                        break;
                    }
                }
                if (!$inList) { // add unposted so/po row, create row with no quantity on this record
                    $row['price']        = ($row['credit_amount']+$row['debit_amount'])/$row['qty'];
                    $row['bal']          = $row['qty'];
                    $row['item_ref_id']  = $row['id'];
                    $row['credit_amount']= 0;
                    $row['debit_amount'] = 0;
                    $row['total']        = 0;
                    $row['qty']          = '';
                    $row['id']           = '';
                    $this->items[] = $row;
                }
            }
            $this->items = sortOrder($this->items, 'item_cnt');
        }
        $dbData = [];
        foreach ($this->items as $row) {
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
                $inv     = dbGetValue(BIZUNO_DB_PREFIX."inventory", ['inventory_type', 'qty_stock', 'item_weight'], "sku='{$row['sku']}'");
                $inv_adj = in_array($this->journalID, [3,4,6,13,21]) ? -$row['qty'] : $row['qty'];
                $row['qty_stock']     = $inv['qty_stock'] + $inv_adj;
                $row['inventory_type']= $inv['inventory_type'];
                $row['item_weight']   = $inv['item_weight'];
            }
            $dbData[] = $row;
        }
        $map['credit_amount']= ['type'=>'field','index'=>'total'];
        $map['debit_amount'] = ['type'=>'trash'];
        // add some extra fields needed for validation
        $structure['inventory_type'] = ['attr'=>['type'=>'hidden']];
        $this->dgDataItem = formatDatagrid($dbData, 'datagridData', $structure, $map);
    }

    /**
     * Customizes the layout for this particular journal
     * @param array $data - Current working structure
     * @param integer $rID - current db record ID
     * @param integer $security - users security setting
     */
    public function customizeView(&$data, $rID=0, $cID=0, $security=0)
    {
        $fldKeys = ['id','journal_id','so_po_ref_id','terms','override_user','override_pass','recur_id','recur_frequency','item_array','xChild','xAction','store_id',
            'purch_order_id','invoice_num','waiting','closed','terms_text','terms_edit','post_date','terminal_date','rep_id','currency','currency_rate','sales_order_num'];
        $fldAddr = ['contact_id','address_id','primary_name','contact','address1','address2','city','state','postal_code','country','telephone1','email'];
        if (!empty($data['fields']['so_po_ref_id']['attr']['value'])) {
            $this->main['soNum'] = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'invoice_num', "id={$data['fields']['so_po_ref_id']['attr']['value']}");
        }
        $data['fields']['sales_order_num'] = ['order'=>90,'break'=>true,'label'=>lang('journal_main_invoice_num_10'),'attr'=>['value'=>isset($this->main['soNum'])?$this->main['soNum']:'','readonly'=>'readonly']];
        $data['jsHead']['datagridData'] = $this->dgDataItem;
        $data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'c');
        if ($this->action=='inv') {
            $data['datagrid']['item']['source']['actions']['fillAll'] = ['order'=>30,'icon'=>'select_all','size'=>'large','hidden'=>$security>1?false:true,'events'=>['onClick'=>"phreebooksSelectAll();"]];
        }
        if ($rID || $this->action=='inv') { unset($data['datagrid']['item']['source']['actions']['insertRow']); } // only allow insert for new orders
        $data['fields']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');
        if (!$rID) { // new order
            $data['fields']['closed']= ['attr'=>['type'=>'hidden', 'value'=>'0']];
        } elseif (isset($data['fields']['closed']['attr']['checked']) && $data['fields']['closed']['attr']['checked'] == 'checked') {
            $data['fields']['closed']= ['attr'=>['type'=>'hidden', 'value'=>'1']];
            $data['fields']['journal_msg']['html'] .= '<span style="font-size:20px;color:red">'.lang('paid')."</span>";
        } else {
            $data['fields']['closed']= ['attr'=>['type'=>'hidden', 'value'=>'0']];
            $data['fields']['journal_msg']['html'] .= '<span style="font-size:20px;color:red">'.lang('unpaid')."</span>";
        }
        if (!$rID) { // new order
            $data['fields']['waiting']= ['attr'=>['type'=>'hidden', 'value'=>'1']];
        } elseif (isset($data['fields']['waiting']['attr']['checked']) && $data['fields']['waiting']['attr']['checked'] == 'checked') {
            $data['fields']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
            $data['fields']['journal_msg']['html'] .= ' - <span style="font-size:20px;color:red">'.lang('unshipped')."</span>";
        } else {
            $data['fields']['waiting']= ['attr'=>['type'=>'hidden', 'value'=>'0']];
            $data['fields']['journal_msg']['html'] .= ' - <span style="font-size:20px;color:red">'.lang('shipped')."</span>";
        }
        $data['divs']['divDetail'] = ['order'=>50,'type'=>'divs','classes'=>['areaView'],'divs'=>[
            'billAD' => ['order'=>10,'type'=>'panel','key'=>'billAD', 'classes'=>['block25']],
            'shipAD' => ['order'=>20,'type'=>'panel','key'=>'shipAD', 'classes'=>['block25']],
            'props'  => ['order'=>30,'type'=>'panel','key'=>'props',  'classes'=>['block25']],
            'totals' => ['order'=>40,'type'=>'panel','key'=>'totals', 'classes'=>['block25R']],
            'dgItems'=> ['order'=>50,'type'=>'panel','key'=>'dgItems','classes'=>['block99']],
            'divAtch'=> ['order'=>90,'type'=>'panel','key'=>'divAtch','classes'=>['block50']]]];
        $data['panels']['billAD'] = ['label'=>lang('bill_to'),'type'=>'address','attr'=>['id'=>'address_b'],'fields'=>$fldAddr,
                'settings'=>['suffix'=>'_b','search'=>true,'copy'=>true,'update'=>true,'validate'=>true,'fill'=>'both','required'=>true,'store'=>false,'cols'=>false]];
        $data['panels']['shipAD'] = ['label'=>lang('ship_to'),'type'=>'address','attr'=>['id'=>'address_s'],'fields'=>$fldAddr,
                'settings'=>['suffix'=>'_s','search'=>true,'update'=>true,'validate'=>true,'drop'=>true,'cols'=>false]];
        $data['panels']['props']  = ['label'=>lang('details'),'type'=>'fields', 'keys'   =>$fldKeys];
        $data['panels']['totals'] = ['label'=>lang('totals'), 'type'=>'totals', 'content'=>$data['totals']];
        $data['panels']['dgItems']= ['type'=>'datagrid','key'=>'item'];
        $data['divs']['other']  = ['order'=>70,'type'=>'html','html'=>'<div id="shippingVal"></div>'];
    }

/*******************************************************************************************************************/
// START Post Journal Function
/*******************************************************************************************************************/
    public function Post()
    {
        msgDebug("\n/********* Posting Journal main ... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        $this->setItemDefaults(); // makes sure the journal_item fields have a value
        $this->unSetCOGSRows(); // they will be regenerated during the post
        if (!$this->postMain())              { return; }
        if (!$this->postItem())              { return; }
        if (!$this->postInventory())         { return; }
        if (!$this->postJournalHistory())    { return; }
        if (!$this->setStatusClosed('post')) { return; }
        msgDebug("\n*************** end Posting Journal ******************* id = {$this->main['id']}\n\n");
        return true;
    }

    public function unPost()
    {
        msgDebug("\n/********* unPosting Journal main ... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        if (!$this->unPostJournalHistory())    { return; }    // unPost the chart values before inventory where COG rows are removed
        if (!$this->unPostInventory())         { return; }
        if (!$this->unPostMain())              { return; }
        if (!$this->unPostItem())              { return; }
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
        msgDebug("\n  j19 - Checking for re-post records ... ");
        $out1 = [];
        $out2 = array_merge($out1, $this->getRepostInv());
        $out3 = array_merge($out2, $this->getRepostInvCOG());
        $out4 = array_merge($out3, $this->getRepostPayment());
        msgDebug("\n  j19 - End Checking for Re-post.");
        return $out4;
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
        $item_rows_to_process = count($this->items); // NOTE: variable needs to be here because $this->items may grow within for loop (COGS)
// the cogs rows are added after this loop ..... the code below needs to be rewritten
        for ($i = 0; $i < $item_rows_to_process; $i++) {
            if (!in_array($this->items[$i]['gl_type'], ['itm','adj','asy','xfr'])) { continue; }
            if (isset($this->items[$i]['sku']) && $this->items[$i]['sku'] <> '') {
                $inv_list = $this->items[$i];
                $inv_list['price'] = $this->items[$i]['qty'] ? (($this->items[$i]['debit_amount'] + $this->items[$i]['credit_amount']) / $this->items[$i]['qty']) : 0;
                $inv_list['qty'] = -$inv_list['qty'];
                if (!$this->calculateCOGS($inv_list)) { return false; }
            }
        }
        if ($this->main['so_po_ref_id'] > 0) { $this->setInvRefBalances($this->main['so_po_ref_id']); }
        // update inventory status
        foreach ($this->items as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; } // skip all rows without a SKU
            $item_cost = $full_price = 0;
            $row['qty'] = -$row['qty'];
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
        for ($i = 0; $i < count($this->items); $i++) {
            if (!isset($this->items[$i]['sku']) || !$this->items[$i]['sku']) { continue; }
            if (!$this->setInvStatus($this->items[$i]['sku'], 'qty_stock', $this->items[$i]['qty'])) { return; }
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
        // closed can occur many ways including:
        //   forced closure through so/po form (from so/po journal - adjust qty on so/po)
        //   all quantities are reduced to zero (from so/po journal - should be deleted instead but it's possible)
        //   editing quantities on po/so to match the number received (from po/so journal)
        //   receiving all (or more) po/so items through one or more purchases/sales (from purchase/sales journal)
        msgDebug("\n  Checking for closed entry. action = $action");
        if ($this->main['so_po_ref_id']) {    // make sure there is a reference po/so to check
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