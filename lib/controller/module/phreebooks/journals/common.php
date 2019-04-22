<?php
/*
 * PhreeBooks journal class for Journal 2, General Ledger
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
 * @version    3.x Last Update: 2019-03-18
 * @filesource /lib/controller/module/phreebooks/journals/common.php
 */

namespace bizuno;

class jCommon
{
    private $cogs_entry = [];

    public function __construct()
    {
        $this->rounding     = getModuleCache('phreebooks', 'currency', 'iso')[getUserCache('profile', 'currency', false, 'USD')]['dec_len'];
        $this->isolate_cogs = getModuleCache('phreebooks', 'settings', 'general', 'isolate_stores') ? true : false;
    }

    /**
     * Tests and sets to default fields in the item array that may not be set coming in
     */
    protected function setItemDefaults()
    {
        msgDebug("\nSetting item defaults as part of a post.");
        foreach ($this->items as $key => $row) {
            if (!isset($row['id']))            { $this->items[$key]['id']           = 0; }
            if (!isset($row['ref_id']))        { $this->items[$key]['ref_id']       = 0; }
            if (!isset($row['item_cnt']))      { $this->items[$key]['item_cnt']     = 0; }
            if (!isset($row['item_ref_id']))   { $this->items[$key]['item_ref_id']  = 0; }
            if (!isset($row['debit_amount']))  { $this->items[$key]['debit_amount'] = 0; }
            if (!isset($row['credit_amount'])) { $this->items[$key]['credit_amount']= 0; }
            if (!isset($row['trans_code']))    { $this->items[$key]['trans_code']   = '';}
            if (!isset($row['serialize']))     { $this->items[$key]['serialize']    = 0; }
        }
    }

    /**
     * Post journal main array to table journal_main
     * @return true
     */
    protected function postMain()
    {
        if (!$mID = dbWrite(BIZUNO_DB_PREFIX."journal_main", $this->main)) { return msgAdd('Database error posting main record, see trace!', 'trap'); }
        if (!isset($this->main['id']) || !$this->main['id']) { $this->main['id'] = $mID; }
        return true;
    }

    /**
     * Posts journal rows (item array) to table journal_item
     * @return true
     */
    protected function postItem()
    {
        for ($i = 0; $i < count($this->items); $i++) {
            $this->items[$i]['ref_id'] = $this->main['id'];    // link the rows to the journal main id
            msgDebug("\n  journal item = " . print_r($this->items[$i], true));
            if  (!$iID = dbWrite(BIZUNO_DB_PREFIX."journal_item", $this->items[$i])) { return msgAdd('Database error posting item record, see trace!', 'trap'); }
            if (!isset($this->items[$i]['id']) || !$this->items[$i]['id']) { $this->items[$i]['id'] = $this->items[$i]['id'] = $iID; }
        }
        return true;
    }

    /**
     * deletes journal main array from table journal_main
     * @return true
     */
    protected function unPostMain()
    {
        msgDebug("\n  Deleting Journal main and rows as part of unPost ...");
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_main WHERE id={$this->main['id']}");
        return true;
    }

    /**
     * deletes journal rows (item array) from table journal_item
     * @return true
     */
    protected function unPostItem()
    {
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_item WHERE ref_id={$this->main['id']}");
        return true;
    }

    protected function setJournalHistory()
    {
        $accounts = [];
        $precision = $this->rounding + 2;
        foreach ($this->items as $value) {
            $credit_amount = (isset($value['credit_amount']) && $value['credit_amount']) ? $value['credit_amount'] : '0';
            $debit_amount  = (isset($value['debit_amount'])  && $value['debit_amount'])  ? $value['debit_amount']  : '0';
            if (round($credit_amount, $precision) <> 0 || round($debit_amount, $precision) <> 0) {
                if (!isset($accounts[$value['gl_account']])) { $accounts[$value['gl_account']] = ['debit'=>0, 'credit'=>0]; }
                $accounts[$value['gl_account']]['credit'] += $credit_amount;
                $accounts[$value['gl_account']]['debit']  += $debit_amount;
            }
        }
        foreach ($accounts as $gl_acct => $values) {
            if  (round($values['credit'], $precision) <> 0 || round($values['debit'], $precision) <> 0) {
                $query_data = [
                    'credit_amount'=> "credit_amount + ".$values['credit'],
                    'debit_amount' => "debit_amount + ".$values['debit'],
                    'last_update'  => "'{$this->main['post_date']}'"];
                $result = dbWrite(BIZUNO_DB_PREFIX."journal_history", $query_data, 'update', "gl_account='$gl_acct' AND period={$this->main['period']}", false);
                if ($result <> 1) { return $this->msgPostError(sprintf(lang('err_gl_post_balance'), $values['debit']+$values['credit'], $gl_acct ? $gl_acct : lang('not_specified'))); }
            }
        }
        msgDebug("\n  end Posting Chart Balances.");
        return true;
    }

    /**
     * Deletes journal item values from the journal_history table
     * @return boolean - true in all cases
     */
    protected function unSetJournalHistory()
    {
        for ($i=0; $i<count($this->items); $i++) {
            if (empty($this->items[$i]['credit_amount'])){ $this->items[$i]['credit_amount']= 0; }
            if (empty($this->items[$i]['debit_amount'])) { $this->items[$i]['debit_amount'] = 0; }
            $sql = "UPDATE ".BIZUNO_DB_PREFIX."journal_history SET
                credit_amount=credit_amount-{$this->items[$i]['credit_amount']}, debit_amount=debit_amount-{$this->items[$i]['debit_amount']}
                WHERE gl_account='{$this->items[$i]['gl_account']}' AND period={$this->main['period']}";
            msgDebug("\n    unPost chart balances: credit_amount = {$this->items[$i]['credit_amount']}, debit_amount = {$this->items[$i]['debit_amount']}, acct = {$this->items[$i]['gl_account']}, period = {$this->main['period']}");
            dbGetResult($sql);
        }
        msgDebug("\n  end unPosting Chart Balances.");
        return true;
    }

    /**
     * Builds the cogs journal entries as part of the inventory posting methods
     * @return - adds to the journal_item array
     */
    protected function setInvCogItems()
    {
        foreach ($this->cogs_entry as $gl_acct => $values) {
            $temp_array = [
                'ref_id'        => $this->main['id'],
                'gl_type'       => 'cog',        // code for cost of goods charges
                'description'   => lang('inventory_cogs').' - '.$this->main['invoice_num'],
                'gl_account'    => $gl_acct,
                'credit_amount' => isset($values['credit']) ? $values['credit'] : 0,
                'debit_amount'  => isset($values['debit'])  ? $values['debit']  : 0,
                'post_date'     => $this->main['post_date']];
            $temp_array['id'] = dbWrite(BIZUNO_DB_PREFIX."journal_item", $temp_array);
            $this->items[] = $temp_array;
        }
    }

    /**
     * Adjusts the balances SO/PO based on the Sale/Purchase counts
     * @param integer $refID - journal_main reference ID to be adjusted
     * @return type
     */
    protected function setInvRefBalances($refID=0, $post=true)
    {
        if (!$refID) { return; }
        // adjust po/so inventory, if necessary, based on min of qty on ordered and qty shipped/received
        $refJournal = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'journal_id', "id=$refID");
        if (!in_array($refJournal, [4, 10])) { return; } // only adjust if a sales order or purchase order. fixes bug for quotes
        $db_field = $refJournal==4 ? 'qty_po' : 'qty_so';
        foreach ($this->items as $row) {
            if (isset($row['item_ref_id']) && $row['item_ref_id']) {
                $qty = dbGetValue(BIZUNO_DB_PREFIX.'journal_item', 'qty', "id={$row['item_ref_id']}");
                $adj = min($row['qty'], $qty);
                if ($post) { $adj = -$adj; }
                $this->setInvStatus($row['sku'], $db_field, $adj);
            }
        }
    }

    /**
     *
     * @param type $ref_id
     * @param type $id
     * @param type $post
     * @return type
     */
    protected function getStkBalance($ref_id, $id='', $post=true)
    {
        msgDebug("\n    Starting to load SO/PO balances ...");
        $item_array = [];
        if ($ref_id) { // start by retrieving the po/so item list
            $stmt1  = dbGetResult("SELECT id, sku, qty FROM ".BIZUNO_DB_PREFIX."journal_item WHERE ref_id=$ref_id AND gl_type='itm'");
            $result1= $stmt1->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result1 as $row) { if ($row['sku']) { $item_array[$row['id']] = ['sku'=>$row['sku'], 'ordered' => $row['qty'], 'processed' => 0]; } }
            // retrieve the total number of units processed (received/shipped) less this order (may be multiple sales/purchases)
            $sql = "SELECT i.item_ref_id AS id, i.sku, i.qty FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
                WHERE m.so_po_ref_id=$ref_id AND i.gl_type='itm'";
            if (!$post && $id) { $sql .= " AND m.id <> $id"; } // unposting so don't include current id (journal_id = 6 or 12)
            $stmt2  = dbGetResult($sql);
            $result2= $stmt2->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result2 as $row) { if ($row['sku']) {
                if (!isset($item_array[$row['id']]['processed'])) { $item_array[$row['id']] = ['ordered'=>0, 'processed'=>$row['qty']]; }
                else                                              { $item_array[$row['id']]['processed'] += $row['qty']; }
            } }
        }
        $this->so_po_balance_array = $item_array;
        msgDebug("\nFinished loading SO/PO balances = ".print_r($item_array, true));
        return $item_array;
    }

    /**
     *
     * @return array
     */
    protected function getRepostInv()
    {
        $output = [];
        if (!$this->main['id']) { return $output; }
        for ($i = 0; $i < count($this->items); $i++) {
            if (!isset($this->items[$i]['sku']) || !$this->items[$i]['sku']) { continue; }
            // check to see if any future postings relied on this record, queue to re-post if so.
            $id = dbGetValue(BIZUNO_DB_PREFIX."inventory_history", 'id', "ref_id={$this->main['id']} AND sku='{$this->items[$i]['sku']}'");
            if (!$id) { continue; }
            $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_cogs_usage", "inventory_history_id=$id");
            foreach ($result as $row) {
                if ($row['journal_main_id'] <> $this->main['id']) {
                    msgDebug("\n    getRepostInv is queing for cogs usage id = " . $row['journal_main_id']);
                    $mainRow = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['post_date', 'journal_id'], "id=".$row['journal_main_id']);
                    $idx = padRef($mainRow['post_date'], $row['journal_main_id'], $mainRow['journal_id']);
                    $output[$idx] = $row['journal_main_id'];
                }
            }
        }
        return $output;
    }

    /**
     * Gathers repost id's from the cost of goods owed table to report now that stock is here.
     * @return type
     */
    protected function getRepostInvCOG()
    {
        $output = [];
        foreach ($this->items as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; }
            if (($row['qty']>0 && in_array($this->main['journal_id'], [6,13,14,15,16])) || ($row['qty'] < 0 && in_array($this->main['journal_id'], [7, 12]))) {
                $crit      = "sku='{$row['sku']}'". ($this->isolate_cogs ? " AND store_id={$this->main['store_id']}" : '');
                $result    = dbGetMulti(BIZUNO_DB_PREFIX."journal_cogs_owed", $crit, "post_date, id");
                $remaining = dbGetValue(BIZUNO_DB_PREFIX."inventory_history", "SUM(remaining) as remaining", "sku='{$row['sku']}' AND remaining>0", false);
                $working_qty = $row['qty'] + $remaining;
                foreach ($result as $owed) {
                    if ($working_qty >= $owed['qty']) { // repost this journal entry and remove the owed record since we will repost all the negative quantities necessary
                        if ($owed['journal_main_id'] <> $this->main['id']) { // prevent infinite loop
                            msgDebug("\n    getRepostInvCOG is queing for cogs owed, id = {$owed['journal_main_id']} to re-post.");
                            $mainRow = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['post_date', 'journal_id'], "id=".$owed['journal_main_id']);
                            $idx = padRef($owed['post_date'], $owed['journal_main_id'], $mainRow['journal_id']); // owed will always be from sales
                            $output[$idx] = $owed['journal_main_id'];
                        }
                        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_cogs_owed WHERE id={$owed['id']}");
                    }
                    $working_qty -= $owed['qty'];
                    if ($working_qty <= 0) { break; }
                }
            }
        }
        return $output;
    }

    /**
     *
     * @return array
     */
    protected function getRepostSale()
    {
        $output = [];
        if (!$this->main['id']) { return $output; }
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", "so_po_ref_id={$this->main['id']}");
        foreach ($result as $row) {
            msgDebug("\n    check for re post is queing id = {$row['id']}");
            $idx = padRef($row['post_date'], $row['id'], $row['journal_id']);
            $output[$idx] = $row['id'];
        }
        return $output;
    }

    /**
     *
     * @return array
     */
    protected function getRepostPayment()
    {
        $output = [];
        if (!$this->main['id']) { return $output; }
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "item_ref_id='{$this->main['id']}' AND gl_type='pmt'");
        foreach ($result as $row) {
            msgDebug("\n    getRepostPayment is queing id = " . $row['ref_id']);
            $mainRow = dbGetValue(BIZUNO_DB_PREFIX."journal_main", ['post_date', 'journal_id'], "id=".$row['ref_id']);
            $idx = padRef($row['post_date'], $row['ref_id'], $mainRow['journal_id']);
            $output[$idx] = $row['ref_id'];
        }
        return $output;
    }

    /**
     *
     * @return boolean
     */
    private function testItemOrders()
    {
        $tolerance = 1 / pow(10, $this->rounding); // i.e. 1 cent in USD
        $diff = $ttlRow = $ttlDbt = $ttlCrt = 0;
        foreach ($this->items as $key => $row) {
            $diff += $row['debit_amount'] - $row['credit_amount'];
            if ($row['gl_type'] == 'ttl') {
                $ttlRow = $key;
                $ttlDbt = $row['debit_amount'];
                $ttlCrt = $row['credit_amount'];
            }
        }
        if (abs($diff) > $tolerance) {
            msgDebug("\nIn testItemOrders failed comparing calc total = ".($ttlDbt + $ttlCrt)." with submitted total = {$this->main['total_amount']}");
            return msgAdd(sprintf($this->lang['err_total_not_match'], ($ttlDbt + $ttlCrt), $this->main['total_amount']), 'trap');
        }
        $adjDbt = $ttlDbt ? ($ttlDbt - $diff) : 0;
        $adjCrt = $ttlCrt ? ($ttlCrt + $diff) : 0;
        if (abs(abs($adjDbt + $adjCrt) - abs($this->main['total_amount'])) > 0.0001) {
            msgDebug("\nCorrected ttl item: debit: $ttlDbt and credit: $ttlCrt diff: $diff, with adjustment debit: $adjDbt, credit: $adjCrt");
            $this->main['total_amount'] = $adjDbt + $adjCrt;
            $this->items[$ttlRow]['debit_amount']  = $adjDbt;
            $this->items[$ttlRow]['credit_amount'] = $adjCrt;
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function testItemBanking()
    {
        foreach ($this->items as $row) { // now check payments to make sure the post date is later than the invoice date
            if ($row['gl_type']=='pmt' && $row['date_1']>$row['post_date']) { return msgAdd(lang('err_gl_bad_post_date')); }
        }
        // need to round amount owed (pmt) and total_amount to currency precision or bank balances will drift
        $diff = 0;
        foreach ($this->items as $key => $row) {
            $this->items[$key]['debit_amount'] = round($row['debit_amount'], $this->rounding);
            $this->items[$key]['credit_amount']= round($row['credit_amount'], $this->rounding);
            msgDebug("\nDebit = {$this->items[$key]['debit_amount']}, Credit = {$this->items[$key]['credit_amount']}");
            $diff += $this->items[$key]['debit_amount'] - $this->items[$key]['credit_amount'];
            if ($row['gl_type'] == 'ttl') {
                $ttlRow = $key;
                $ttlDbt = $this->items[$key]['debit_amount'];
                $ttlCrt = $this->items[$key]['credit_amount'];
            }
        }
        // adjust the total_amount and ttl item row
        $adjDbt = $ttlDbt ? ($ttlDbt - $diff) : 0;
        $adjCrt = $ttlCrt ? ($ttlCrt + $diff) : 0;
        msgDebug("\nprecision = $this->rounding and adjDebit = $adjDbt, adjCreit = $adjCrt");
        $this->main['total_amount'] = $adjDbt + $adjCrt;
        $this->items[$ttlRow]['debit_amount']  = $adjDbt;
        $this->items[$ttlRow]['credit_amount'] = $adjCrt;
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function testItemPOS()
    {
        // This needs a new row entered as tax has more digits than the payment which is currency precision
        return true;
    }

    /**
     *
     * @param type $sku
     * @param type $desc
     * @param type $item_cost
     * @param type $full_price
     * @return type
     */
    private function setInvSave($sku, $desc, $item_cost=0, $full_price=0)
    {
        $sql_array = [
            'sku'                 => $sku,
            'inventory_type'      => 'si',
            'description_short'   => $desc,
            'description_purchase'=> $desc,
            'description_sales'   => $desc,
            'gl_sales'            => getModuleCache('inventory', 'settings', 'phreebooks', 'sales_si'),
            'gl_inv'              => getModuleCache('inventory', 'settings', 'phreebooks', 'inv_si'),
            'gl_cogs'             => getModuleCache('inventory', 'settings', 'phreebooks', 'cogs_si'),
            'tax_rate_id_c'       => getModuleCache('inventory', 'settings', 'general', 'tax_rate_id_c'),
            'tax_rate_id_v'       => getModuleCache('inventory', 'settings', 'general', 'tax_rate_id_v'),
            'item_cost'           => $item_cost,
            'cost_method'         => getModuleCache('method_si', 'inventory', 'phreebooks'),
            'full_price'          => $full_price,
            'creation_date'       => date('Y-m-d h:i:s'),
            ];
        return dbWrite(BIZUNO_DB_PREFIX."inventory", $sql_array);
    }

    // *********  inventory support functions  **********
    /**
     *
     * @param string $sku
     * @param string $field
     * @param float $adjustment
     * @param float $item_cost
     * @param string $desc
     * @param double $full_price
     * @return boolean
     */
    function setInvStatus($sku, $field, $adjustment, $item_cost=0, $desc='', $full_price=0)
    {
        if (!$sku || $adjustment == 0) { return true; }
        msgDebug("\n    setInvStatus, SKU = $sku, field = $field, adjustment = $adjustment, and item_cost = $item_cost");
        // catch sku's that are not in the inventory database but have been requested to post
        $result = dbGetValue(BIZUNO_DB_PREFIX."inventory", ['id', 'inventory_type'], "sku='$sku'");
        if (!$result) {
            if (getModuleCache('inventory', 'settings', 'general', 'auto_add')) {
                $this->setInvSave($sku, $desc, $item_cost, $full_price);
                $result['inventory_type'] = 'si';
            } else {
                return $this->msgPostError(lang('err_gl_inv_status_no_sku') . $sku);
            }
        }
        $type = $result['inventory_type'];
        // only update items that are to be tracked in inventory (non-stock are tracked for PO/SO only)
        if (strpos(COG_ITEM_TYPES, $type) !== false || ($type == 'ns' && $field <> 'qty_stock')) {
            $sql = "UPDATE ".BIZUNO_DB_PREFIX."inventory SET `$field` = $field + $adjustment";
            if ($item_cost) { $sql .= ", item_cost=$item_cost"; }
            $sql .= ", last_journal_date=NOW() WHERE sku='$sku'";
            $result = dbGetResult($sql);
        }
        return true;
    }

    /**
     *
     * @param array $item
     * @param float $return_cogs
     * @return boolean|int
     */
    function calculateCOGS($item, $return_cogs = false)
    {
        msgDebug("\n    Calculating COGS, item data = ".print_r($item, true));
        $cogs = 0;
        // fetch the additional inventory item fields we need
        $defaults = dbGetRow(BIZUNO_DB_PREFIX."inventory", "sku='{$item['sku']}'");
        // catch sku's that are not in the inventory database but have been requested to post, error
        if (!$defaults) {
            if (!getModuleCache('inventory', 'settings', 'general', 'auto_add')) { return $this->msgPostError(lang('err_gl_cogs_calculation')); }
            $item_cost  = 0;
            $full_price = 0;
            switch ($this->main['journal_id']) {
                case  6:
                case  7: $item_cost  = $item['price']; break;
                case 12:
                case 13: $full_price = $item['price']; break;
                default: return $this->msgPostError(lang('err_gl_cogs_calculation'));
            }
            $id = $this->setInvSave($item['sku'], $item['description'], $item_cost, $full_price);
            $defaults = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id=$id"); // re-load now that item was created
        }
        // only calculate cogs for certain inventory_types
        msgDebug("\nCOG_ITEM_TYPES = ".COG_ITEM_TYPES." and inventory_type = {$defaults['inventory_type']}");
        if (strpos(COG_ITEM_TYPES, $defaults['inventory_type']) === false) {
            msgDebug(". Exiting COGS, no work to be done with this SKU.");
            return true;
        }
        if ($this->isolate_cogs) { $defaults['qty_stock'] = $this->getStoreQtyStock($item['sku'], $defaults['qty_stock']); }
        // catch sku's that are serialized and the quantity is not one, error
        if ($defaults['serialize'] && abs($item['qty']) <> 1) { return $this->msgPostError(lang('err_gl_inv_serial_qty')); }
        if ($defaults['serialize'] && !$item['trans_code'])   { return $this->msgPostError(lang('err_gl_inv_serial_no_value')); }

        if ($item['qty'] > 0) { // for positive quantities, inventory received, customer credit memos, unbuild assembly
            // if insert, enter SYSTEM ENTRY COGS cost only if inv on hand is negative
            // update will never happen because the entries are removed during the unpost operation.
            switch ($this->main['journal_id']) {
                case  6:
                    if ($defaults['cost_method'] == 'a') { $item['avg_cost'] = $this->setAvgCost($item['sku'], $item['price'], $item['qty']); }
                    break;
                case 12: // for negative sales/invoices and customer credit memos the price needs to be the last unit_cost,
                case 13: // not the invoice price (customers price)
                    $item['price'] = $this->calculateCost($item['sku'], 1, $item['trans_code']);
                    $cogs = -($item['qty'] * $item['price']);
                    break;
                case 14: // for un-build assemblies cogs will not be zero
                    $cogs = -($item['qty'] * $this->calculateCost($item['sku'], 1, $item['trans_code'])); // use negative last cost (unbuild assy)
                    break;
                case 15:
                default: // for all other journals, use the cost as entered to calculate added inventory
            }
            // adjust remaining quantities for inventory history since stock was negative
            $history_array = [
                'ref_id'     => $this->main['id'],
                'store_id'   => $this->main['store_id'],
                'journal_id' => $this->main['journal_id'],
                'sku'        => $item['sku'],
                'qty'        => $item['qty'],
                'remaining'  => $item['qty'],
                'unit_cost'  => $item['price'],
                'avg_cost'   => isset($item['avg_cost']) ? $item['avg_cost'] : $item['price'],
                'post_date'  => $this->main['post_date'],
                ];
            if ($defaults['serialize']) { // check for duplicate serial number
                $result = dbGetValue(BIZUNO_DB_PREFIX.'inventory_history', ["id", "unit_cost"], "sku='{$item['sku']}' AND remaining>0 AND trans_code='{$item['trans_code']}'");
                if ($result) { return $this->msgPostError(lang('err_gl_inv_serial_cogs')); }
                $history_array['trans_code'] = $item['trans_code'];
            }
            msgDebug("\n      Inserting into inventory history = ".print_r($history_array, true));
            $result = dbWrite(BIZUNO_DB_PREFIX.'inventory_history', $history_array);
            if (!$result) { return $this->msgPostError(lang('err_gl_inv_history')); }
        } else { // for negative quantities, i.e. sales, negative inv adjustments, assemblies, vendor credit memos
            // if insert, calculate COGS pulling from one or more history records (inv may go negative)
            // update should never happen because COGS is backed out during the unPost inventory function
            $working_qty = -$item['qty']; // quantity needs to be positive
            $history_ids = []; // the id's used to calculated cogs from the inventory history table
            $queue_sku = false;
            if ($defaults['cost_method'] == 'a') {
                msgDebug("\nFinding average cost");
                $remaining = dbGetValue(BIZUNO_DB_PREFIX.'inventory_history', "SUM(remaining) as remaining", "sku='{$item['sku']}' AND remaining>0 AND store_id='{$this->main['store_id']}'", false);
                if ($remaining < $working_qty) { $queue_sku = true; } // not enough of this SKU so just queue it up until stock arrives
                $avg_cost = $this->getAvgCost($item['sku'], $working_qty);
            }
            if ($defaults['serialize']) { // there should only be one record with one remaining quantity
                $result = dbGetMulti(BIZUNO_DB_PREFIX.'inventory_history', "sku='".$item['sku']."' AND remaining>0 AND trans_code='{$item['trans_code']}'");
                if (sizeof($result) <> 1) { return $this->msgPostError(lang('err_gl_inv_serial_cogs')); }
            } else {
                $sql = "sku='{$item['sku']}' AND remaining>0"; // AND post_date <= '$this->main['post_date'] 23:59:59'"; // causes re-queue to owed table for negative inventory posts and rcv after sale date
                if ($this->isolate_cogs) {
                    $store_id = $this->main['journal_id']==15 ? $this->main['so_po_ref_id'] : $this->main['store_id'];
                    $sql .= " AND store_id='$store_id'";
                }
                $sql .= " ORDER BY ".($defaults['cost_method']=='l' ? 'post_date DESC, id DESC' : 'post_date, id');
                $result = dbGetMulti(BIZUNO_DB_PREFIX.'inventory_history', $sql);
            }
            if (!$queue_sku) { foreach ($result as $row) { // loops until either qty is zero and/or inventory history is exhausted
                msgDebug("\n    working with row = ".print_r($row, true));
                if ($defaults['cost_method'] == 'a') { // Average cost
                    switch ($this->main['journal_id']) {
                        case  7: // vendor credit memo, just need the difference in return price from average price
                        case 14: // assembly, just need the difference in assemble price from piece price
                                 $cost = $avg_cost - $item['price']; break;
                        default: $cost = $avg_cost; break;
                    }
                } else {  // FIFO, LIFO
                    switch ($this->main['journal_id']) {
                        case  7: // vendor credit memo, just need the difference in return price from purchase price
                        case 14: // assembly, just need the difference in assemble price from piece price
                                 $cost = $row['unit_cost'] - $item['price']; break;
                        default: $cost = $row['unit_cost']; break; // for the specific history record
                    }
                }
                //     Calculate COGS and adjust remaining levels based on costing method and history
                //       there are two possibilities, inventory is in stock (deduct from inventory history)
                //       or inventory is out of stock (balance goes negative, COGS to be calculated later)
                if ($working_qty <= $row['remaining']) { // this history record has enough to fill request
                    $cost_qty = $working_qty;
                    $working_qty = 0;
                    $exit_loop = true;
                } else { // qty will span more than one history record, just calculate for this record
                    $cost_qty = $row['remaining'];
                    $working_qty -= $row['remaining'];
                    $exit_loop = false;
                }
                // save the history record id used along with the quantity for roll-back purposes
                $history_ids[] = ['id' => $row['id'], 'qty' => $cost_qty]; // how many from what id
                msgDebug("\n    found cost = $cost, history unit row[unit_cost] = {$row['unit_cost']}, item[price] = {$item['price']}");
                $cogs += $cost * $cost_qty;
                dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."inventory_history SET remaining = remaining - $cost_qty WHERE id=".$row['id']);
                if ($exit_loop) { break; }
            } }
            for ($i = 0; $i < count($history_ids); $i++) {
                $sql_data_array = [
                    'inventory_history_id' => $history_ids[$i]['id'],
                    'qty'                  => $history_ids[$i]['qty'],
                    'journal_main_id'      => $this->main['id'],
                    ];
                dbWrite(BIZUNO_DB_PREFIX.'journal_cogs_usage', $sql_data_array);
            }
            // see if there is quantity left to account for but nothing left in inventory (less than zero inv balance)
            if ($working_qty > 0) {
                if ($defaults['cost_method'] == 'a' || !getModuleCache('inventory', 'settings', 'general', 'allow_neg_stock')) {
                    return $this->msgPostError(lang('err_gl_inv_negative'));
                }
                // for now, estimate the cost based on the unit_price of the item, will be re-posted (corrected) when product arrives
                switch ($this->main['journal_id']) {
                    case  7: // vendor credit memo, just need the difference in return price from purchase price
                    case 14: // assembly, just need the difference in assemble price from piece price
//                        $cost = $defaults['cost_method']=='a' ? ($avg_cost - $item['price']) : ($defaults['item_cost'] - $item['price']); // avg costing not allowed to go negative
                        $cost = $defaults['item_cost'] - $item['price']; break;
                    default:
//                        $cost = $defaults['cost_method']=='a' ? $avg_cost : $defaults['item_cost']; // for the specific history record
                        $cost = $defaults['item_cost']; break;
                }
                $cogs += $cost * $working_qty;
                // queue the journal_main_id to be re-posted later after inventory is received
                $sql_data_array = [
                    'journal_main_id' => $this->main['id'],
                    'sku'             => $item['sku'],
                    'qty'             => $working_qty,
                    'post_date'       => $this->main['post_date'],
                    'store_id'        => $this->main['store_id']];
                msgDebug("\n    Adding journal_cogs_owed, SKU = {$item['sku']}, qty = $working_qty");
                dbWrite(BIZUNO_DB_PREFIX."journal_cogs_owed", $sql_data_array);
            }
        }

        if ($this->main['journal_id'] == 15) { $cogs = 0; } // no cogs for transfers
        $this->sku_cogs = $cogs;
        if ($return_cogs) {
            msgDebug("\n    Returning from COGS calculation with calculated value = $cogs");
            return $cogs; // just calculate cogs and adjust inv history
        }

         msgDebug("\n    Adding COGS to array (if not zero), sku = " . $item['sku'] . " with calculated value = " . $cogs);
        if ($cogs) {
            // credit inventory cost of inventory
            $cogs_acct = $defaults['gl_inv'];
            if ($cogs >= 0 ) {
                if (!isset($this->cogs_entry[$cogs_acct]['credit'])) { $this->cogs_entry[$cogs_acct]['credit'] = 0; }
                $this->cogs_entry[$cogs_acct]['credit'] += $cogs;
            } else {
                if (!isset($this->cogs_entry[$cogs_acct]['debit'])) { $this->cogs_entry[$cogs_acct]['debit'] = 0; }
                $this->cogs_entry[$cogs_acct]['debit']  += -$cogs;
            }
            // debit cogs account for income statement
            if ($this->main['journal_id'] == 16) { $this->setGLAcctItemTtl($defaults['gl_cogs']); }
            $cogs_acct = $defaults['gl_cogs'];
            if ($cogs >= 0 ) {
                if (!isset($this->cogs_entry[$cogs_acct]['debit'])) { $this->cogs_entry[$cogs_acct]['debit'] = 0; }
                $this->cogs_entry[$cogs_acct]['debit']  += $cogs;
            } else {
                if (!isset($this->cogs_entry[$cogs_acct]['credit'])) { $this->cogs_entry[$cogs_acct]['credit'] = 0; }
                $this->cogs_entry[$cogs_acct]['credit'] += -$cogs;
            }
        }
        msgDebug(" ... Finished calculating COGS.");
        return true;
    }

    /**
     *
     * @param type $sku
     * @param type $qty
     * @param type $serial_num
     * @return int
     */
    function calculateCost($sku, $qty=1, $serial_num='')
    {
        msgDebug("\n    Calculating SKU cost, SKU = $sku and QTY = $qty");
        $cogs = 0;
        $defaults = dbGetRow(BIZUNO_DB_PREFIX."inventory", "sku='$sku'");
        if (sizeof($defaults) == 0) { return $cogs; } // not in inventory, return no cost
        if (strpos(COG_ITEM_TYPES, $defaults['inventory_type']) === false) { return $cogs; }// this type not tracked in cog, return no cost
        if ($defaults['cost_method'] == 'a') { return $qty * $this->getAvgCost($sku, $qty); }
        if ($defaults['serialize']) { // there should only be one record
            $unit_cost = dbGetValue(BIZUNO_DB_PREFIX."inventory_history", "unit_cost", "sku='$sku' AND trans_code='$serial_num'");
            return $unit_cost;
        }
        $sql = "SELECT remaining, unit_cost FROM ".BIZUNO_DB_PREFIX."inventory_history"." WHERE sku='$sku' AND remaining>0";
        if (sizeof(getModuleCache('bizuno', 'stores')) > 0) { $sql .= " AND store_id='{$this->main['store_id']}'"; }
        $sql .= " ORDER BY id" . ($defaults['cost_method'] == 'l' ? ' DESC' : '');
        $stmt = dbGetResult($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $working_qty = abs($qty);
        foreach ($result as $row) { // loops until either qty is zero and/or inventory history is exhausted
            if ($working_qty <= $row['remaining']) { // this history record has enough to fill request
                $cogs += $row['unit_cost'] * $working_qty;
                $working_qty = 0;
                break; // exit loop
            }
            $cogs += $row['unit_cost'] * $row['remaining'];
            $working_qty -= $row['remaining'];
        }
        if ($working_qty > 0) { $cogs += $defaults['item_cost'] * $working_qty; } // leftovers, use default cost
        msgDebug(" ... Finished calculating cost: $cogs");
        return $cogs;
    }

    /**
     *
     * @param type $sku
     * @param type $price
     * @param type $qty
     * @return int
     */
    function setAvgCost($sku='', $price=0, $qty=1)
    {
        $filter = "ref_id<>{$this->main['id']} AND sku='$sku' AND remaining>0 AND post_date<='{$this->main['post_date']}'";
        if ($this->main['store_id'] > 0) { $filter .= " AND store_id='{$this->main['store_id']}'"; }
        $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_history", $filter, "post_date, id");
        $total_stock = 0;
        $last_cost   = 0;
        foreach ($result as $row) {
            $total_stock += $row['remaining'];
            $last_cost    = $row['avg_cost']; // just keep the cost from the last recordas this keeps the avg value of the post date
        }
        if ($total_stock == 0 && $qty == 0) { return 0; }
        $avg_cost = (($last_cost * $total_stock) + ($price * $qty)) / ($total_stock + $qty);
        return $avg_cost;
    }

    /**
     *
     * @param type $sku
     * @param type $qty
     * @return type
     */
    function getAvgCost($sku, $qty=1)
    {
        msgDebug("\n      Entering getAvgCost for sku: $sku and qty: $qty ... ");
        $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_history", "sku='$sku' AND remaining>0 AND store_id='{$this->main['store_id']}'", "post_date");
        $last_cost = isset($result[0]['avg_cost']) ? $result[0]['avg_cost'] : 0;
        $last_qty = 0;
        $ready_to_exit = false;
        foreach ($result as $row) {
            $qty -= $row['remaining'];
            $post_date = substr($row['post_date'], 0, 10);
            if ($qty <= 0) { $ready_to_exit = true; }
            if ($ready_to_exit && $post_date > $this->main['post_date']) { // will get the last purchase cost before the sale post date
                msgDebug("Exiting early with history post_date = $post_date getAvgCost with cost = ".($last_qty > 0 ? $row['avg_cost'] : $last_cost));
                return $last_qty > 0 ? $row['avg_cost'] : $last_cost;
            }
            $last_cost = $row['avg_cost'];
            $last_qty = $qty;
        }
        msgDebug("Exiting getAvgCost with cost = $last_cost");
        return $last_cost;
    }

    /**
     * Searches item array for the ttl row and returns the gl account
     * @param type $glAcct
     */
    private function setGLAcctItemTtl(&$glAcct)
    {
        foreach ($this->items as $row) {
            if ($row['gl_type'] == 'ttl') { $glAcct = $row['gl_account']; }
        }
    }

    // Rolling back cost of goods sold required to unpost an entry involves only re-setting the inventory history.
    // The cogs records and costing is reversed in the unPost_chart_balances function.
    /**
     *
     * @return boolean
     */
    protected function rollbackCOGS()
    {
        msgDebug("\n    Rolling back COGS ... ");
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_cogs_usage", "journal_main_id='{$this->main['id']}'");
        foreach ($result as $entry) {
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."inventory_history SET remaining=remaining+{$entry['qty']} WHERE id={$entry['inventory_history_id']}");
        }
        msgDebug(" Rolled back ".sizeof($result)." records ... Exiting COGS.");
        return true;
    }

    /**
     *
     * @param array $inv_list
     * @return boolean
     */
    protected function setAssyCost($inv_list)
    {
        $sku = $inv_list['sku'];
        $qty = $inv_list['qty'];
        msgDebug("\n    Calculating Assembly item list, SKU = $sku");
        $sku_id = dbGetValue(BIZUNO_DB_PREFIX."inventory", "id", "sku='$sku'");
        if (!$sku_id) { return $this->msgPostError(lang('err_gl_invalid_sku')); }
        $sql = "SELECT a.sku, a.description, a.qty, i.inventory_type, i.qty_stock, i.gl_inv, i.item_cost AS price
            FROM ".BIZUNO_DB_PREFIX."inventory_assy_list a JOIN ".BIZUNO_DB_PREFIX."inventory i ON a.sku = i.sku WHERE a.ref_id=$sku_id";
        $stmt = dbGetResult($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (sizeof($result) == 0) { return $this->msgPostError(lang('err_gl_invalid_sku_bom') . $sku); }
        $assy_cost = 0;
        foreach ($result as $row) {
            $row['trans_code'] = ''; // don't know if this is needed but can cause undefined error if not present
            if ($row['qty_stock'] < ($qty * $row['qty']) && strpos(COG_ITEM_TYPES, $row['inventory_type']) !== false) {
                msgDebug("\n    Not enough of SKU = {$row['sku']} needed ".($qty * $row['qty'])." and had ".$row['qty_stock']);
                return $this->msgPostError(lang('err_gl_inv_low_stock') . $row['sku']);
            }
            $row['qty'] = -($qty * $row['qty']);
            $row['id']  = $this->items[0]['id'];  // placeholder ref_id
            if (strpos(COG_ITEM_TYPES, $row['inventory_type']) === false) { // not tracked in cogs
                msgDebug("\n    NOT tracked in inventory");
                $item_cost = -$row['qty'] * $row['price'];
            } else {
                msgDebug("\n    tracked in inventory");
                if ($qty > 0) { $row['price'] = 0; } // remove unit_price for builds, leave for unbuilds (to calc delta COGS)
                $item_cost = $this->calculateCOGS($row, true);
            }
            msgDebug("\n    testing item_cost = ".($item_cost===false ? 'false' : $item_cost));
            if ($item_cost === false) { return false; }// error in cogs calculation
            $assy_cost += $item_cost;
            // generate inventory assembly part record and insert into db
            $temp_array = [
                'ref_id'       => $this->main['id'],
                'gl_type'      => 'asi',    // assembly item code
                'sku'          => $row['sku'],
                'qty'          => $row['qty'],
                'description'  => $row['description'],
                'gl_account'   => $row['gl_inv'],
                'debit_amount' => $qty<0 ? -$item_cost : 0,
                'credit_amount'=> $qty>0 ?  $item_cost : 0,
                'post_date'    => $this->main['post_date']];
            $temp_array['id'] = dbWrite(BIZUNO_DB_PREFIX."journal_item", $temp_array);
            msgDebug("\nAdding to assembly item to this->items = ".print_r($temp_array, true));
            $this->items[] = $temp_array;
            if ($qty < 0) { // unbuild assy, update ref_id pointer in inventory history record of newly added item (just like a receive)
                dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."inventory_history SET ref_id={$temp_array['id']}
                    WHERE sku='{$temp_array['sku']}' AND ref_id={$row['id']}");
            }
        }
        // update assembled item with total cost
        $id = $this->items[0]['id'];
        if ($qty < 0) { // the item to assemble should be the first item record
            $this->items[0]['credit_amount'] = -$assy_cost;
            $fields = ['credit_amount' => -$assy_cost];
        } else {
            $this->items[0]['debit_amount'] = $assy_cost;
            $fields = ['debit_amount' => $assy_cost];
        }
        dbWrite(BIZUNO_DB_PREFIX."journal_item", $fields, 'update', "id=$id");
        $inv_list['price'] = $assy_cost / $qty; // insert the assembly cost of materials - unit price
        // Adjust inventory levels for assembly, if unbuild, also calcuate COGS differences
        if ($this->calculateCOGS($inv_list, $return_cogs = ($qty < 0) ? false : true) === false) { return false; }
        return true;
    }

    /**
     *
     * @param type $sku
     * @return type
     */
    private function getStoreQtyStock($sku)
    {
        $store_bal = dbGetValue(BIZUNO_DB_PREFIX."inventory_history", "SUM(remaining)", "store_id={$this->main['store_id']} AND sku='$sku'", false);
        $qty_owed  = dbGetValue(BIZUNO_DB_PREFIX."journal_cogs_owed", "SUM(qty)", "store_id={$this->main['store_id']} AND sku='$sku'", false);
        return ($store_bal - $qty_owed);
    }

    /**
     *
     */
    protected function unSetCOGSRows()
    {
        msgDebug("\n  Removing system generated gl rows. Started with ".count($this->items)." rows ");
        // remove these types of rows since they are regenerated as part of the Post
        $removal_gl_types = ['cog', 'asi'];
        $temp_rows = [];
        foreach ($this->items as $value) { if (!in_array($value['gl_type'], $removal_gl_types)) { $temp_rows[] = $value; } }
        $this->items = $temp_rows;
        msgDebug(" and ended with ".count($this->items)." rows.");
    }

    /**
     *
     * @param type $id
     * @param type $closed
     */
    protected function setCloseStatus($id, $closed=true)
    {
        $dbData = ['closed'=>$closed?'1':'0', 'closed_date'=>$closed?$this->main['post_date']:''];
        dbWrite(BIZUNO_DB_PREFIX."journal_main", $dbData, 'update', "id=$id");
        msgDebug("\n  Record ID: {$this->main['id']}".($closed ? " Closed Record ID: " : " Opened Record ID: ").$id);
    }

        /**
     * Cleans and extracts the address from the journal main database row
     * @param array $data - current working structure
     * @param string $suffix - form field suffix to extract from
     * @return array - cleaned address ready to render
     */
    protected function cleanAddress($data, $suffix='')
    {
        $addStruc= dbLoadStructure(BIZUNO_DB_PREFIX.'address_book');
        $output  = [];
        foreach (array_keys($addStruc) as $field) {
            if (isset($data[$field.$suffix])) {
                $output[$field] = $data[$field.$suffix];
                $output[$field]['label'] = $addStruc[$field]['label'];
            }
        }
        $output['contact_id'] = !empty($data['contact_id'.$suffix]) ? $data['contact_id'.$suffix] : $addStruc['id'];
        return $output;
    }

    /**
     *
     * @param type $message
     */
    function msgPostError($message)
    {
        msgDebug("\nReturning with fail message: $message");
        msgAdd($message);
        return false; // for testing purposes, this needs to be here
    }

    /**
     * Creates the datagrid structure for banking line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
    protected function dgBanking($name, $journalID=20) {
        return ['id'=>$name, 'type'=>'edatagrid',
            'attr'   => ['rownumbers'=>true, 'checkOnSelect'=>false, 'selectOnCheck'=>false, 'remoteSort'=>false, 'multiSort'=>true],
            'events' => ['data'=> 'datagridData',
                'onLoadSuccess'=> "function(data) { for (var i=0; i<data.rows.length; i++) if (data.rows[i].checked) jq('#$name').datagrid('checkRow', i); totalUpdate(); }",
                'onClickRow'   => "function(rowIndex) { curIndex = rowIndex; }",
                'onBeginEdit'  => "function(rowIndex) { bankingEdit(rowIndex); }",
                'onCheck'      => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked: true} }); totalUpdate(); }",
                'onCheckAll'   => "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('checkRow',i); }",
                'onUncheck'    => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked:false} }); totalUpdate(); }",
                'onUncheckAll' => "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('uncheckRow',i); }",
                'rowStyler'    => "function(idx, row) { if (row.waiting==1) { return {class:'journal-waiting'}; }}"],
            'columns'=> [
                'id'         => ['order'=> 0,'attr' =>['hidden'=>'true']],
                'ref_id'     => ['order'=> 0,'attr' =>['hidden'=>'true']],
                'gl_account' => ['order'=> 0,'attr' =>['hidden'=>'true']],
                'item_ref_id'=> ['order'=> 0,'attr' =>['hidden'=>'true']],
                'invoice_num'=> ['order'=>10,'label'=>pullTableLabel('journal_main', 'invoice_num', '12'),
                    'attr'  => ['sortable'=>true, 'resizable'=>true, 'width'=>100,  'align'=>'center']],
                'post_date'  => ['order'=>20,'label'=>pullTableLabel('journal_main', 'post_date', '12'),
                    'attr'  => ['type'=>'date','sortable'=>true,'resizable'=>true,'width'=>100,'align'=>'center']],
                'date_1'     => ['order'=>30,'label'=>pullTableLabel('journal_item', 'date_1', $journalID),
                    'attr'  => ['type'=>'date','sortable'=>true,'resizable'=>true,'width'=>100,'align'=>'center']],
                'description'=> ['order'=>40,'label'=>lang('notes'), 'attr'=>['width'=>350,'resizable'=>true,'editor'=>'text']],
                'amount'     => ['order'=>50,'label'=>lang('amount_due'),'attr'=>['type'=>'currency','width'=> 100,'resizable'=>true,'align'=>'right'],
                    'events'=> ['formatter'=>"function(value,row){ return formatCurrency(value); }"]],
                'discount'   => ['order'=>60,'label'=>lang('discount'), 'styles'=>['text-align'=>'right'],
                    'attr'  => ['type'=>'currency','resizable'=>true,'width'=>100,  'align'=>'right'],
                    'events'=> ['editor'=>dgEditCurrency("bankingCalc('disc');"),  'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
                'total'      => ['order'=>70,'label'=>lang('total'), 'styles'=>['text-align'=>'right'],
                    'attr'  => ['type'=>'currency','resizable'=>true,'width'=>100,'align'=>'right'],
                    'events'=> ['editor'=>dgEditCurrency("bankingCalc('direct');"),'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
                'pay'        => ['order'=>90,'attr'=>['checkbox'=>true]]]];
    }

    /**
     * Creates the datagrid structure for banking bulk pay line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
    protected function dgBankingBulk($name, $journalID=20)
    {
        return ['id'=>$name, 'type'=>'edatagrid',
            'attr'   => ['rownumbers'=>true, 'checkOnSelect'=>false, 'selectOnCheck'=>false, 'remoteSort'=>false, 'multiSort'=>true], // multiSort is cool as it allows multiple columns to be sorted but may become confusing
            'events' => ['data'=>'datagridData',
                'onLoadSuccess'=> "function(data) { for (var i=0; i<data.rows.length; i++) if (data.rows[i].checked) jq('#$name').datagrid('checkRow', i);
    jq('#$name').datagrid('fitColumns');
    totalUpdate();
}",
                'onClickRow'   => "function(rowIndex) { curIndex = rowIndex; }",
                'onBeginEdit'  => "function(rowIndex) { bankingEdit(rowIndex); }",
                'onCheck'      => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked: true} }); totalUpdate(); }",
                'onCheckAll'   => "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('checkRow',i); }",
                'onUncheck'    => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked:false} }); totalUpdate(); }",
                'onUncheckAll' => "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('uncheckRow',i); }",
                'rowStyler'    => "function(idx, row) { if (row.waiting==1) { return {class:'journal-waiting'}; }}"],
            'columns'=> [
                'id'          => ['order'=> 0,'attr' =>['hidden'=>'true']],
                'item_ref_id' => ['order'=> 0,'attr' =>['hidden'=>'true']],
                'contact_id'  => ['order'=> 0,'attr' =>['hidden'=>'true']],
                'inv_date'    => ['order'=>10,'label'=>pullTableLabel('journal_main', 'post_date', '12'),'attr'=>['type'=>'date','width'=>100,'sortable'=>true,'resizable'=>true,'align'=>'center']],
                'primary_name'=> ['order'=>20,'label'=>pullTableLabel('journal_main', 'primary_name_b', '12'),'attr'=>['width'=>220,'sortable'=>true,'resizable'=>true]],
                'inv_num'     => ['order'=>30,'label'=>pullTableLabel('journal_main', 'invoice_num', '12'),'attr'=>['width'=>120,'sortable'=>true,'resizable'=>true, 'align'=>'center']],
                'amount'      => ['order'=>40,'label'=>lang('amount_due'),'attr'=>['type'=>'currency', 'width'=>100,'resizable'=>true, 'align'=>'right'],
                    'events'=> ['formatter'=>"function(value,row){ return formatCurrency(value); }"]],
                'description' => ['order'=>50,'label'=>lang('notes'),     'attr'=>['width'=>220,'resizable'=>true,'editor'=>'text']],
                'date_1'      => ['order'=>60,'label'=>pullTableLabel('journal_item', 'date_1', $journalID),'attr'=>['type'=>'date','width'=>90,'sortable'=>true, 'resizable'=>true, 'align'=>'center']],
                'discount'    => ['order'=>70,'label'=>lang('discount'),'styles'=>['text-align'=>'right'],
                    'attr'  => ['type'=>'currency','width'=>80, 'resizable'=>true, 'align'=>'right'],
                    'events'=>  ['editor'=>dgEditCurrency("bankingCalc('disc');"),'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
                'total'       => ['order'=>80,'label'=>lang('total'),'styles'=>['text-align'=>'right'],
                    'attr'  => ['type'=>'currency','width'=>90, 'resizable'=>true, 'align'=>'right'],
                    'events'=> ['editor'=>dgEditCurrency("bankingCalc('direct');"),'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
                'pay'         => ['order'=>90,'attr'=>['checkbox'=>true]]]];
    }

    /**
     * Creates the grid structure for customer/vendor order items
     * @param string $name - DOM field name
     * @param char $type - choices are c (customers) or v (vendors)
     * @return array - grid structure
     */
    protected function dgOrders($name, $type) {
        $on_hand    = pullTableLabel('inventory', 'qty_stock');
        $gl_account = $type=='v' ? 'gl_inv'    : 'gl_sales';
        $inv_field  = $type=='v' ? 'item_cost' : 'full_price';
        $inv_title  = $type=='v' ? lang('cost'): lang('price');
        $hideItemTax= true;
        foreach ($this->totals as $methID) { if ($methID == 'tax_item') { $hideItemTax = false; } }
        $data = ['id'=> $name, 'type'=>'edatagrid',
            'attr'   => ['toolbar'=>"#{$name}Toolbar", 'rownumbers'=>true, 'idField'=>'id', 'singleSelect'=>true, 'fitColumns'=>true],
            'events' => ['data'=> "datagridData",
                'onLoadSuccess'=> "function(row) { totalUpdate('dgOrders LoadSuccess'); }",
                'onClickRow'   => "function(rowIndex, row) { curIndex = rowIndex; }",
                'onBeforeEdit' => "function(rowIndex) { var edtURL=jq(this).edatagrid('getColumnOption','sku'); edtURL.editor.options.url='".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1&bID='+jq('#store_id').val(); }",
                'onBeginEdit'  => "function(rowIndex) { ordersEditing(rowIndex); }",
                'onDestroy'    => "function(rowIndex) { totalUpdate('dgOrders onDestroy'); curIndex = undefined; }",
                'onAdd'        => "function(rowIndex) { setFields(rowIndex); }"],
            'source' => ['actions'=>[
                'newItem'  =>['order'=>10,'icon'=>'add',   'size'=>'large','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]],
                'insertRow'=>['order'=>20,'icon'=>'cancel','size'=>'large','label'=>lang('insert'),'events'=>['onClick'=>" var idx=prompt('".jsLang($this->lang['insert_row'])."');
if (idx!=null) {
    jq('#$name').edatagrid('addRow', {index:idx,row:{'qty':'1','sku':'','description':'','gl_account':def_contact_gl_acct,'tax_rate_id':def_contact_tax_id}});
    jq('#$name').edatagrid('loadData', jq('#$name').edatagrid('getData')); }"]]]], // this is needed to force a redraw of the datagrid
            'columns'=> [
                'id'            => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'ref_id'        => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'item_ref_id'   => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'pkg_length'    => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'pkg_width'     => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'pkg_height'    => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'inventory_type'=> ['order'=>0, 'attr'=>['hidden'=>'true']],
                'item_weight'   => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'qty_stock'     => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'full_price'    => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'trans_code'    => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'attach'        => ['order'=>0, 'attr'=>['hidden'=>'true', 'value'=>'0']],
                'date_1'        => ['order'=>0, 'attr'=>['hidden'=>'true']],
                'action'        => ['order'=>1, 'label'=>lang('action'),'events'=>['formatter'=>"function(value,row,index) { return {$name}Formatter(value,row,index); }"],
                    'actions' => [
                        'settings'=> ['order'=>10,'icon'=>'settings','events'=>['onClick'=>"var rowIndex=jq('#$name').datagrid('getRowIndex', jq('#$name').datagrid('getSelected')); inventoryProperties(rowIndex);"]],
                        'price'   => ['order'=>40,'icon'=>'price',   'events'=>['onClick'=>"var rowIndex=jq('#$name').datagrid('getRowIndex', jq('#$name').datagrid('getSelected')); inventoryGetPrice(rowIndex, '$type');"]],
                        'trash'   => ['order'=>90,'icon'=>'trash',   'events'=>['onClick'=>"jq('#$name').edatagrid('destroyRow');"],
                            'display' => "typeof row.item_ref_id==='undefined' || row.item_ref_id=='0' || row.item_ref_id==''"]]],
                'sku'=> ['order'=>30, 'label'=>pullTableLabel('journal_item', 'sku', $this->journalID),
                    'attr' => ['width'=>150, 'sortable'=>true, 'resizable'=>true, 'align'=>'center', 'value'=>''],
                    'events'=>  ['editor'=>"{type:'combogrid',options:{ url:'".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1',
                        width:150, panelWidth:550, delay:500, idField:'sku', textField:'sku', mode:'remote',
                        onLoadSuccess: function () { jq.parser.parse(jq(this).datagrid('getPanel'));
                            var skuEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'sku'});
                            var g = jq(skuEditor.target).combogrid('grid');
                            var r=g.datagrid('getData');
                            if (r.rows.length==1) { var cbValue = jq(skuEditor.target).combogrid('getValue');
                                if (!cbValue) { return; }
                                if (r.rows[0].sku==cbValue || r.rows[0].upc_code==cbValue) {
                                    jq(skuEditor.target).combogrid('hidePanel'); orderFill(r.rows[0], '$type');
                                }
                            }
                        },
                        onClickRow: function (idx, data) { orderFill(data, '$type'); },
                        columns:[[{field:'sku', title:'".jsLang('sku')."', width:100},
                            {field:'description_short',title:'".jsLang('description')."', width:200},
                            {field:'qty_stock', title:'$on_hand', width:90,align:'right'},
                            {field:'$inv_field', title:'$inv_title', width:90,align:'right'},
                            {field:'$gl_account', hidden:true}, {field:'item_weight', hidden:true}]]}}"]],
                'description'  => ['order'=>40, 'label'=>lang('description'),'attr'=>['width'=>400,'editor'=>dgEditText(),'resizable'=>true]],
                'gl_account'   => ['order'=>50, 'label'=>pullTableLabel('journal_item', 'gl_account', $this->journalID),'attr'=>['width'=>100,'resizable'=>true,'align'=>'center'],
                    'events'   => ['editor'=>dgEditGL()]],
                'tax_rate_id'  => ['order'=>60, 'label'=>pullTableLabel('journal_main', 'tax_rate_id', $this->type),'attr'=>['hidden'=>$hideItemTax,'width'=>150,'resizable'=>true,'align'=>'center'],
                    'events'   => ['editor'=>dgEditTax($name, 'tax_rate_id', $type, "totalUpdate('dgOrders onChangeTax');"),
                    'formatter'=> "function(value,row){ return getTextValue(bizDefaults.taxRates.$type.rows, value); }"]],
                'price'        => ['order'=>70, 'label'=>lang('price'), 'attr'=>['width'=>80,'value'=>0,'resizable'=>true,'align'=>'right'],
                    'events'   => ['editor'=>dgEditCurrency("ordersCalc('price');", true),'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
                'total'        => ['order'=>80, 'label'=>lang('total'), 'attr'=>['width'=>80,'value'=>0,'resizable'=>true,'align'=>'right'],
                    'events'   => ['editor'=>dgEditCurrency("ordersCalc('total');"),'formatter'=>"function(value,row){ return formatCurrency(value); }"]]]];
        switch ($this->journalID) {
            case  3: $qty1 = lang('qty');      $qty2 = lang('received'); $ord1 = 20; $ord2 = 25; break;
            case  4: $qty1 = lang('qty');      $qty2 = lang('received'); $ord1 = 20; $ord2 = 25; break;
            case  6: $qty1 = lang('received'); $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
            case  7: $qty1 = lang('returned'); $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
            case  9: $qty1 = lang('qty');      $qty2 = lang('invoiced'); $ord1 = 20; $ord2 = 25; break;
            case 10: $qty1 = lang('qty');      $qty2 = lang('invoiced'); $ord1 = 20; $ord2 = 25; break;
            default:
            case 12: $qty1 = lang('qty');      $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
            case 13: $qty1 = lang('returned'); $qty2 = lang('shipped');  $ord1 = 25; $ord2 = 20; break;
            case 19: $qty1 = lang('qty');      $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
            case 21: $qty1 = lang('qty');      $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
        }
        $data['columns']['qty'] = ['order'=>$ord1,'label'=>$qty1,'attr'=>['value'=>1,'width'=>80,'resizable'=>true,'align'=>'center'],
            'events'=>['editor'=>dgEditNumber("ordersCalc('qty');"),'formatter'=>"function(value,row){ return formatNumber(value); }"]];
        $data['columns']['bal'] = ['order'=>$ord2,'label'=>$qty2,'attr'=>['width'=>80,'resizable'=>true,'align'=>'center','hidden'=>($this->rID || $this->action=='inv')?false:true],
            'events'=>['formatter'=>"function(value,row){ return formatNumber(value); }"]];
        // restrict prices if not allowed
        if (!validateSecurity('inventory', 'prices_'.$type, 2, false)) { // dis-allow editing of price columns
            $data['columns']['price']['events']['editor'] = "{type:'numberbox',options:{readonly:true }}";
            $data['columns']['total']['events']['editor'] = "{type:'numberbox',options:{readonly:true }}";
        }
        return $data;
    }
}