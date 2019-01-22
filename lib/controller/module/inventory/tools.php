<?php
/*
 * Tools methods for Inventory Module
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
 * @version    3.x Last Update: 2019-01-22
 * @filesource /lib/controller/module/inventory/tools.php
 */

namespace bizuno;

class inventoryTools
{
    public $moduleID = 'inventory';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        $this->inv_types = explode(",", COG_ITEM_TYPES);
    }

    /**
     * Generates a pop up bar chart for monthly sales of inventory items
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function chartSales(&$layout=[])
    {
        $rID = clean('rID', 'integer', 'get');
        $sku = dbGetValue(BIZUNO_DB_PREFIX."inventory", "sku", "id=$rID");
        if (!$rID) { return msgAdd(lang('err_bad_id')); }
        $struc = $this->chartSalesData($sku);
        $iconExp= ['attr'=>['type'=>'button','value'=>'Download Data'],'events'=>['onClick'=>"jq('#frmInventoryChart').submit();"]];
        $output= ['divID'=>"chartInventoryChart",'type'=>'column',
            'attr'=>['title'=>lang('sales')],'data'=>array_values($struc)];
        $action= BIZUNO_AJAX."&p=inventory/tools/chartSalesGo&sku=$sku";
        $js    = "jq.cachedScript('".BIZUNO_URL."../apps/jquery-file-download.js?ver=".MODULE_BIZUNO_VERSION."');\n";
        $js   .= "ajaxDownload('frmInventoryChart');\n";
        $js   .= "var dataInventoryChart = ".json_encode($output).";\n";
        $js   .= "function funcInventoryChart() { drawBizunoChart(dataInventoryChart); };";
        $js   .= "google.charts.load('current', {'packages':['corechart']});\n";
        $js   .= "google.charts.setOnLoadCallback(funcInventoryChart);\n";

        $html  = '<div style="width:100%" id="chartInventoryChart"></div>';
        $html .= '<div style="text-align:right"><form id="frmInventoryChart" action="'.$action.'">'.html5('', $iconExp).'</form></div>';
        $html .= htmlJS($js);
        $layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>$html]);
    }

    private function chartSalesData($sku)
    {
          $dates = localeGetDates(localeCalculateDate(date('Y-m-d'), 0, 0, -1));
        $jIDs = '(12,13)';
        msgDebug("\nDates = ".print_r($dates, true));
          $sql = "SELECT MONTH(m.post_date) AS month, YEAR(m.post_date) AS year, SUM(i.credit_amount+i.debit_amount) AS total
            FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
            WHERE i.sku='$sku' and m.journal_id IN $jIDs AND m.post_date>='{$dates['ThisYear']}-{$dates['ThisMonth']}-01'
              GROUP BY year, month LIMIT 12";
        msgDebug("\nSQL = $sql");
        if (!$stmt = dbGetResult($sql)) { return msgAdd(lang('err_bad_sql')); }
        $result= $stmt->fetchAll(\PDO::FETCH_ASSOC);
        msgDebug("\nresult = ".print_r($result, true));
        $precision = getModuleCache('phreebooks', 'currency', 'iso')[getUserCache('profile', 'currency', false, 'USD')]['dec_len'];
        $struc[] = [lang('date'), lang('total')];
        for ($i = 0; $i < 12; $i++) { // since we have 12 months to work with we need 12 array entries
            $struc[$dates['ThisYear'].$dates['ThisMonth']] = [$dates['ThisYear'].'-'.$dates['ThisMonth'], 0];
            $dates['ThisMonth']++;
              if ($dates['ThisMonth'] == 13) {
                  $dates['ThisYear']++;
                  $dates['ThisMonth'] = 1;
              }
        }
        foreach ($result as $row) {
            if (isset($struc[$row['year'].$row['month']])) { $struc[$row['year'].$row['month']][1] = round($row['total'], $precision); }
          }
        return $struc;
    }

    public function chartSalesGo()
    {
        global $io;
        $sku   = clean('sku', 'text', 'get');
        $struc = $this->chartSalesData($sku);
        $output= [];
        foreach ($struc as $row) { $output[] = implode(",", $row); }
        $io->download('data', implode("\n", $output), "SKU-Sales-$sku.csv");
    }

    /**
     * This function balances the inventory stock levels with the inventory_history table
     * @param string $action (default: fix) - whether to just test or test and repair any discrepancies, choice are 'test' or 'fix'
     */
    public function historyTestRepair($action='test')
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $precision = 1 / pow(10, getModuleCache('bizuno', 'settings', 'locale', 'number_precision', 2));
        $roundPrec = getModuleCache('bizuno', 'settings', 'locale', 'number_precision', 2);
        $action = isset($_GET['data']) ? clean($_GET['data'], 'text') : $action;
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_cogs_owed");
        $owed = [];
        foreach ($result as $row) {
            if (!isset($owed[$row['sku']])) { $owed[$row['sku']] = 0; }
            $owed[$row['sku']] += $row['qty'];
        }
        // fetch the inventory items that we track COGS and get qty on hand
        $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory", "inventory_type IN ('".implode("','", $this->inv_types)."')", "sku", ['sku','qty_stock']);
        $cnt    = 0;
        $repair = [];
        foreach ($result as $row) { // for each item, find the history remaining Qty's
            // check for quantity on hand not rounded properly
            $on_hand = round($row['qty_stock'], $roundPrec);
            if ($on_hand <> $row['qty_stock']) {
                $repair[$row['sku']] = $on_hand;
                if ($action <> 'fix') {
                    $dispVal = round($on_hand, $roundPrec);
                    msgAdd(sprintf($this->lang['inv_tools_stock_rounding_error'], $row['sku'], $row['qty_stock'], $dispVal));
                    $cnt++;
                }
            }
            // now check with inventory history
            $remaining= dbGetValue(BIZUNO_DB_PREFIX."inventory_history", "SUM(remaining) AS remaining", "sku='{$row['sku']}'", false);
            $cog_owed = isset($owed[$row['sku']]) ? $owed[$row['sku']] : 0;
            $cog_diff = round($remaining - $cog_owed, $roundPrec);
            if ($on_hand <> $cog_diff && abs($on_hand-$cog_diff) > 0.01) {
                $repair[$row['sku']] = $cog_diff;
                if ($action <> 'fix') {
                    msgAdd(sprintf($this->lang['inv_tools_out_of_balance'], $row['sku'], $on_hand, $cog_diff));
                    $cnt++;
                }
            }
            msgDebug("\nsku = {$row['sku']}, qty_stock = {$row['qty_stock']}, on_hand = $on_hand, cog_diff = $cog_diff, remaining = $remaining, owed = $cog_owed");
        }
        if ($action == 'fix') {
            dbWrite(BIZUNO_DB_PREFIX."inventory_history", ['remaining'=>0], 'update', "remaining<$precision");
            if (sizeof($repair) > 0) { foreach ($repair as $key => $value) {
                // commented out, the value has already been rounded.
//                $value = round($value, $roundPrec);
                dbWrite(BIZUNO_DB_PREFIX."inventory", ['qty_stock'=>$value], 'update', "sku='$key'");
                msgAdd(sprintf($this->lang['inv_tools_balance_corrected'], $key, $value), 'success');
            } }
        }
        if ($cnt == 0) { msgAdd($this->lang['inv_tools_in_balance'], 'success'); }
        msgLog($this->lang['inv_tools_val_inv']);
    }

    /**
     * Re-aligns table inventory.qty_on alloc with open activities.
     * Here, the function is mostly an entry point that resets all qty_on alloc values to zero, they will
     * be reset to the proper value through mods in the extensions.
     */
    public function qtyAllocRepair()
    {
        dbWrite(BIZUNO_DB_PREFIX.'inventory', ['qty_alloc'=>0], 'update', "qty_alloc<>0");
        msgAdd(lang('msg_database_write'), 'success');
    }

    /**
     * This function balances the open sales orders and purchase orders with the displayed levels from the inventory table
     */
    public function onOrderRepair()
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $skuList = [];
        $jItems = $this->getJournalQty(); // fetch the PO's and SO's balances
        $items  = dbGetMulti(BIZUNO_DB_PREFIX."inventory", "inventory_type IN ('".implode("','",$this->inv_types)."')", 'sku', ['id','sku','qty_so','qty_po']);
        foreach ($items as $row) {
            $adjPO = false;
            if (isset($jItems[4][$row['sku']]) && $jItems[4][$row['sku']] != $row['qty_po']) {
                $adjPO = max(0, round($jItems[4][$row['sku']], 4));
            } elseif (!isset($jItems[4][$row['sku']]) && $row['qty_po'] != 0) {
                $adjPO = 0;
            }
            if ($adjPO !== false) {
                $skuList[] = sprintf('Quantity of SKU: %s on %s was adjusted to %f', $row['sku'], lang('journal_main_journal_id_4'), $adjPO);
                dbWrite(BIZUNO_DB_PREFIX."inventory", ['qty_po'=>$adjPO], 'update', "id={$row['id']}");
            }
            $adjSO = false;
            if (isset($jItems[10][$row['sku']]) && $jItems[10][$row['sku']] != $row['qty_so']) {
                $adjSO = max(0, round($jItems[10][$row['sku']], 4));
            } elseif (!isset($jItems[10][$row['sku']]) && $row['qty_so'] != 0) {
                $adjSO = 0;
            }
            if ($adjSO !== false) {
                $skuList[] = sprintf('Quantity of SKU: %s on %s was adjusted to %f', $row['sku'], lang('journal_main_journal_id_10'), $adjSO);
                dbWrite(BIZUNO_DB_PREFIX."inventory", ['qty_so'=>$adjSO], 'update', "id={$row['id']}");
            }
        }
        msgLog($this->lang['inv_tools_repair_so_po']);
        if (sizeof($skuList) > 0) { return msgAdd(implode("<br />", $skuList), 'caution'); }
        msgAdd($this->lang['inv_tools_so_po_result'], 'success');
    }

    /**
     * Checks order status for order balances, items received/shipped
     * @return array - indexed by journal_id total qty on SO, PO
     */
    private function getJournalQty()
    {
        $item_list = [];
        $orders = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", "closed='0' AND journal_id IN (4,10)", '', ['id', 'journal_id']);
        foreach ($orders as $row) {
            $ordr_items = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "ref_id={$row['id']} AND gl_type='itm'", '', ['id', 'sku', 'qty']);
            foreach ($ordr_items as $item) {
                if (!isset($item_list[$row['journal_id']][$item['sku']])) { $item_list[$row['journal_id']][$item['sku']] = 0; }
                $item_list[$row['journal_id']][$item['sku']] += $item['qty'];
                $filled = dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(qty) AS qty", "item_ref_id={$item['id']}", false);
                if (!$filled) { continue; }
                $newBal = $item_list[$row['journal_id']][$item['sku']] - $filled;
                // in the case when more are received than ordered, don't let qty_po, qty_so go negative (doesn't make sense)
                $item_list[$row['journal_id']][$item['sku']] = max(0, $newBal);
            }
        }
        return $item_list;
    }

    /**
     * Re-prices all assemblies based on current item costs, best done after new item costing has been done completed, through ajax steps
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function priceAssy(&$layout=[])
    {
        $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory", "inventory_type IN ('ma','sa')", 'sku', ['id', 'sku']);
        if (sizeof($result) == 0) { return msgAdd("No assemblies found to process!"); }
        foreach ($result as $row) { $rows[] = ['id'=>$row['id'], 'sku'=>$row['sku']]; }
        msgDebug("\nRows to process = ".print_r($rows, true));
        setUserCache('cron', 'priceAssy', ['cnt'=>0, 'total'=>sizeof($rows), 'rows'=>$rows]);
        $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval', 'actionData'=>"cronInit('priceAssy', 'inventory/tools/priceAssyNext');"]]);
    }

    /**
     * Controller for re-costing assemblies, manages a single SKU per iteration
     * @param type $layout
     */
    public function priceAssyNext(&$layout=[])
    {
        $cron = getUserCache('cron', 'priceAssy');
        $row  = array_shift($cron['rows']);
        $cost = dbGetInvAssyCost($row['id']);
        dbWrite(BIZUNO_DB_PREFIX."inventory", ['item_cost'=>$cost], 'update', "id={$row['id']}");
        $cron['cnt']++;
        if (sizeof($cron['rows']) == 0) {
            msgLog("inventory Tools (re-cost Assemblies) - ({$cron['total']} records)");
            $data = ['content'=>['percent'=>100,'msg'=>"Processed {$cron['total']} SKUs",'baseID'=>'priceAssy','urlID'=>'inventory/tools/priceAssyNext']];
            $allCron = getUserCache('cron');
            unset($allCron['priceAssy']);
            setUserCache('cron', false, $allCron);
        } else { // return to update progress bar and start next step
            $percent = floor(100*$cron['cnt']/$cron['total']);
            setUserCache('cron', 'priceAssy', $cron);
            $data = ['content'=>['percent'=>$percent,'msg'=>"Completed sku {$row['sku']} with price ".viewFormat($cost, 'currency'),'baseID'=>'priceAssy','urlID'=>'inventory/tools/priceAssyNext']];
        }
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * This function extends the PhreeBooks module close fiscal year method
     */
    public function fyCloseHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $html  = "<p>"."Closing the fiscal year for the Inventory module consist of deleting inventory items that are no longer referenced in the general journal during or before the fiscal year being closed. "
                . "To prevent the these inventory items from being removed, check the box below."."</p>";
        $html .= html5('inventory_keep', ['label' => 'Do not delete inventory items that are not referenced during or before this closing fiscal year', 'position'=>'after','attr'=>['type'=>'checkbox','value'=>'1']]);
        $layout['tabs']['tabFyClose']['divs'][$this->lang['title']] = ['order'=>50,'label'=>$this->lang['title'],'type'=>'html','html'=>$html];
    }

    /**
     * Hook to PhreeBooks Close FY method, adds tasks to the queue to execute AFTER PhreeBooks processes the journal
     * @param array $layout - structure coming in
     * @return array - modified $layout
     */
    public function fyClose(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $skip = clean('inventory_keep', 'boolean', 'post');
        if ($skip) { return; } // user wants to keep all records, nothing to do here, move on
        $cron = getUserCache('cron', 'fyClose');
        $cron['taskPost'][] = ['mID'=>$this->moduleID, 'settings'=>['cnt'=>1, 'rID'=>0]]; // ,'method'=>'fyCloseNext']; // assumed method == fyCloseNext, no settings
        setUserCache('cron', 'fyClose', $cron);
    }

    /**
     * Executes a step in the fiscal close procedure, controls all steps for this module
     * @param array $settings - Properties for the fiscal year close operation
     * @return string - message with current status
     */
    public function fyCloseNext($settings=[], &$cron=[])
    {
        $blockSize = 25;
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        if (!isset($cron[$this->moduleID]['total'])) {
            $cron[$this->moduleID]['total'] = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'COUNT(*) AS cnt', "", false);
        }
        $totalBlock = ceil($cron[$this->moduleID]['total'] / $blockSize);
        $output = $this->fyCloseStep($settings['rID'], $blockSize, $cron['msg']);
//if ($settings['cnt'] > 4) { $output['finished'] = true; }
        if (!$output['finished']) { // more to process, re-queue
            $settings['cnt']++;
            msgDebug("\nRequeuing inventory with rID = {$output['rID']}");
            array_unshift($cron['taskPost'], ['mID'=>$this->moduleID, 'settings'=>['cnt'=>$settings['cnt'], 'rID'=>$output['rID']]]);
        } else { // we're done, run the sync attachments tool
            msgDebug("\nFinished inventory, checking attachments");
            $this->syncAttachments();
        }
        // Need to add these results to a log that can be downloaded from the backup folder.
        return "Finished processing block {$settings['cnt']} of $totalBlock for module $this->moduleID: deleted {$output['deleted']} records";
    }

    /**
     * Just executes a single step
     * @param integer $rID - starting record id for this step
     * @param integer $blockSize - number of records to delete in a single step
     * @return array - status and data for the next step, number of records deleted
     */
    private function fyCloseStep($rID, $blockSize, &$msg=[])
    {
        $count = 0;
        $result= dbGetMulti(BIZUNO_DB_PREFIX.'inventory', "id>$rID", 'id', ['id','sku','inactive','description_short'], $blockSize);
        foreach ($result as $row) {
            $rID = $row['id']; // set the highest rID for next iteration
            if (!$row['inactive']) { continue; }
            if (!$row['sku']) {
                msgAdd("There is not SKU value for record {$row['id']}, This should never happen! The record will be skipped.");
                continue;
            }
            $exists = dbGetValue(BIZUNO_DB_PREFIX.'journal_item', 'ref_id', "sku='{$row['sku']}'");
            if (!$exists) {
                $msg[] = "Deleting inventory id={$row['id']}, {$row['sku']} - {$row['description_short']}";
                msgDebug("\nDeleting inventory id={$row['id']}, {$row['sku']} - {$row['description_short']}");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."inventory_prices    WHERE inventory_id={$row['id']}");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."inventory_history   WHERE sku='{$row['sku']}'");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."inventory_assy_list WHERE ref_id={$row['id']}");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."inventory           WHERE id={$row['id']}");
                $count++;
            }
        }
        return ['rID'=>$rID, 'finished'=>sizeof($result)<$blockSize ? true : false, 'deleted'=>$count];
    }

    /**
     * Synchronizes actual attachment files with the flag in the inventory table
     */
    private function syncAttachments()
    {
        $io = new \bizuno\io();
        $files = $io->folderRead(getModuleCache('inventory', 'properties', 'attachPath'));
        foreach ($files as $attachment) {
            $tID = substr($attachment, 4); // remove rID_
            $rID = substr($tID, 0, strpos($tID, '_'));
            $exists = dbGetRow(BIZUNO_DB_PREFIX.'inventory', "id=$rID");
            if (!$exists) {
                msgDebug("\nDeleting attachment for rID = $rID and file: $attachment");
                $io->fileDelete(getModuleCache('inventory', 'properties', 'attachPath')."/$attachment");
            } elseif (!$exists['attach']) {
                msgDebug("\nSetting attachment flag for id = $rID and file: $attachment");
                dbWrite(BIZUNO_DB_PREFIX.'inventory', ['attach'=>'1'], 'update', "id=$rID");
            }
        }
    }
}
