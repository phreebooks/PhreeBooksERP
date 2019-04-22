<?php
/*
 * PhreeBooks journal class for Journal 15, Inventory Store Transfer
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
 * @version    3.x Last Update: 2019-04-10
 * @filesource /lib/controller/module/phreebooks/journals/j15.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journals/common.php", 'jCommon');

class j15 extends jCommon
{
    protected $journalID = 15;

    function __construct($main=[], $item=[])
    {
        parent::__construct();
        $this->main = $main;
        $this->items = $item;
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
        $dbData = [];// calculate some form fields that are not in the db
        foreach ($this->items as $key => $row) {
            if ($row['gl_type'] <> 'adj') { continue; } // not an adjustment record
            $values = dbGetRow(BIZUNO_DB_PREFIX."inventory", "sku='{$row['sku']}'");
            $row['qty_stock'] = $values['qty_stock']-$row['qty'];
            $row['balance']   = $values['qty_stock'];
            $row['price']     = viewFormat($values['item_cost'], 'currency');
            $dbData[$key]     = $row;
        }
        $map['credit_amount'] = ['type'=>'field','index'=>'total'];
        $this->dgDataItem = formatDatagrid($dbData, 'datagridData', $structure, $map);
    }

    /**
     * Customizes the layout for this particular journal
     * @param array $data - Current working structure
     * @param integer $rID - current db record ID
     */
    public function customizeView(&$data, $rID=0)
    {
        $fldKeys = ['id','journal_id','recur_id','recur_frequency','item_array',
            'invoice_num','post_date','so_po_ref_id','method_code']; // source store ID, destination store added in extension
        $data['jsHead']['datagridData'] = $this->dgDataItem;
        if (!$rID && getModuleCache('extShipping', 'properties', 'status')) { $data['fields']['waiting']['attr']['value'] ='1'; } // to be seen by ship manager to print label
        $data['datagrid']['item'] = $this->dgAdjust('dgJournalItem');
        $data['fields']['waiting']['attr']['type'] = '1'; // for ship manager
        $data['fields']['so_po_ref_id']['values']  = getModuleCache('bizuno', 'stores');
        $data['fields']['so_po_ref_id']['attr']['type'] = 'select';
        $data['fields']['so_po_ref_id']['events']['onChange'] = "jq('#dgJournalItem').edatagrid('endEdit', curIndex); crmDetail(this.value, '_b');";
        $data['datagrid']['item']['events']['onBeforeEdit']= "function(rowIndex) {
    var edtURL = jq(this).edatagrid('getColumnOption','sku');
    edtURL.editor.options.url = bizunoAjax+'&p=inventory/main/managerRows&clr=1&bID='+jq('#so_po_ref_id').val();
}";
        $data['datagrid']['item']['columns']['qty']['events']['editor']  = "{type:'numberbox',options:{min:0,onChange:function(){ adjCalc('qty'); } } }";
        $data['datagrid']['item']['columns']['total']['events']['editor']= "{type:'numberbox',options:{disabled:true}}";
        $choices = [['id'=>'', 'text'=>lang('select')]];
        if (sizeof(getModuleCache('extShipping', 'carriers'))) {
            foreach (getModuleCache('extShipping', 'carriers') as $settings) {
                if ($settings['status'] && isset($settings['settings']['services'])) {
                    $choices = array_merge_recursive($choices, $settings['settings']['services']);
                }
            }
        }
        $shipMeth = $data['fields']['method_code']['attr']['value'];
        $data['fields']['method_code'] = ['label'=>lang('journal_main_method_code'),'options'=>['width'=>300],'values'=>$choices,'attr'=>['type'=>'select','value'=>$shipMeth]];
        unset($data['toolbars']['tbPhreeBooks']['icons']['print']);
        unset($data['toolbars']['tbPhreeBooks']['icons']['recur']);
        unset($data['toolbars']['tbPhreeBooks']['icons']['payment']);
        $isWaiting = isset($data['fields']['waiting']['attr']['checked']) && $data['fields']['waiting']['attr']['checked'] ? '1' : '0';
        $data['fields']['waiting'] = ['attr'=>['type'=>'hidden','value'=>$isWaiting]];
        $data['divs']['divDetail'] = ['order'=>50,'type'=>'divs','classes'=>['areaView'],'attr'=>['id'=>'pbDetail'],'divs'=>[
            'props' => ['order'=>40,'type'=>'fields','classes'=>['blockView'],'attr'=>['id'=>'pbProps'],'keys'=>$fldKeys],
            'totals'=> ['order'=>50,'type'=>'totals','classes'=>['blockViewR'],'attr'=>['id'=>'pbTotals'],'content'=>$data['totals']]]];
        $data['divs']['dgItems']= ['order'=>60,'type'=>'datagrid','key'=>'item'];
    }

/*******************************************************************************************************************/
// START Post Journal Function
/*******************************************************************************************************************/
    public function Post()
    {
        msgDebug("\n/********* Posting Journal main ... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        if (!$this->journalTransfer($this->items)) { return; }
        $this->main['description'] .= " ({$this->items[0]['qty']}) {$this->item[0]['description']}".(sizeof($this->item)>2 ? ' +++' : '');
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
        msgDebug("\n  j15 - Checking for re-post records ... ");
        $out1 = [];
        $out2 = array_merge($out1, $this->getRepostInv());
        $out3 = array_merge($out2, $this->getRepostInvCOG());
//      $out4 = array_merge($out3, $this->getRepostInvAsy());
        $out5 = array_merge($out3, $this->getRepostPayment());
        msgDebug("\n  j15 - End Checking for Re-post.");
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
        $item_rows_to_process = count($this->items); // NOTE: variable needs to be here because $this->items may grow within for loop (COGS)
// the cogs rows are added after this loop ..... the code below needs to be rewritten
        for ($i = 0; $i < $item_rows_to_process; $i++) {
            if (!in_array($this->items[$i]['gl_type'], ['itm','adj','asy','xfr'])) { continue; }
            if (isset($this->items[$i]['sku']) && $this->items[$i]['sku'] <> '') {
                $inv_list = $this->items[$i];
                $inv_list['price'] = $this->items[$i]['qty'] ? (($this->items[$i]['debit_amount'] + $this->items[$i]['credit_amount']) / $this->items[$i]['qty']) : 0;
                if (!$this->calculateCOGS($inv_list)) { return; }
            }
        }
        // update inventory status
        foreach ($this->items as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; } // skip all rows without a SKU
            $item_cost = $full_price = 0;
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
            if (!$this->setInvStatus($this->items[$i]['sku'], 'qty_stock', -$this->items[$i]['qty'])) { return; }
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

        /**
     * This method takes the line items from a transfer operation and builds the new 'effective' line items
     * @param array $item - the list of items to transfer, after initial processing
     */
    private function journalTransfer(&$item)
    {
        msgDebug("\nAdding rows for Inventory Store Transfer");
        $srcStoreID = clean('so_po_ref_id','integer', 'post');
        $destStoreID= clean('store_id',    'integer', 'post');
        if ($srcStoreID == $destStoreID) { return msgAdd($this->lang['err_gl_xfr_same_store']); }
        // take the line items and create a negative list for the receiving store
        $output = [];
        foreach ($item as $row) {
            unset($row['id']); // when reposting, this causes duplicate ID errors if not cleared
            $row['qty'] = -$row['qty'];
            $row['gl_type'] = 'xfr';
            $tmp = $row['credit_amount']; // swap debits and credits
            $row['credit_amount'] = $row['debit_amount'];
            $row['debit_amount'] = $tmp;
            $output[] = $row;
        }
        $item = array_merge($item, $output);
        return true;
    }

    /**
     * Creates the datagrid structure for inventory adjustments line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
    private function dgAdjust($name)
    {
        $on_hand  = jsLang('inventory', 'qty_stock');
        $on_order = jsLang('inventory', 'qty_po');
        $store_id = getUserCache('profile', 'store_id', false, 0);
        return ['id' => $name,'type'=>'edatagrid',
            'attr'   => ['toolbar'=>"#{$name}Toolbar", 'rownumbers'=>true, 'singleSelect'=>true, 'idField'=>'id'],
            'events' => ['data'=> "datagridData",
                'onLoadSuccess'=> "function(row) { totalUpdate(); }",
                'onClickRow'   => "function(rowIndex) { curIndex = rowIndex; }",
                'onBeforeEdit' => "function(rowIndex) {
    var edtURL = jq(this).edatagrid('getColumnOption','sku');
    edtURL.editor.options.url = '".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1&f0=a&bID='+jq('#store_id').val();
}",
                'onBeginEdit'  => "function(rowIndex) { curIndex = rowIndex; jq('#$name').edatagrid('editRow', rowIndex); }",
                'onDestroy'    => "function(rowIndex) { totalUpdate(); curIndex = undefined; }",
                'onAdd'        => "function(rowIndex) { setFields(rowIndex); }"],
            'source' => [
                'actions' => ['newItem' =>['order'=>10,'icon'=>'add','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]],
            'columns'=> [
                'id'         => ['order'=>0, 'attr'=>  ['hidden'=>true]],
                'gl_account' => ['order'=>0, 'attr'=>  ['hidden'=>true]],
                'unit_cost'  => ['order'=>0, 'attr'=>  ['editor'=>'text', 'hidden'=>true]],
                'action'     => ['order'=>1, 'label'=>lang('action'),'events'=>['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
                    'actions'=> ['trash' => ['order'=>80,'icon'=>'trash','events'=>['onClick'=>"jq('#$name').edatagrid('destroyRow');"]]]],
                'sku'        => ['order'=>20, 'label'=>lang('sku'),'attr'=>['width'=>120,'sortable'=>true,'resizable'=>true,'align'=>'center'],
                    'events' => ['editor'=>"{type:'combogrid',options:{
                        width: 150, panelWidth: 540, delay: 500, idField: 'sku', textField: 'sku', mode: 'remote',
                        url:        '".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1&f0=a&bID=$store_id',
                        onClickRow: function (idx, data) { adjFill(data); },
                        columns:[[{field:'sku',              title:'".jsLang('sku')."',width:100},
                                  {field:'description_short',title:'".jsLang('description')."',width:200},
                                  {field:'qty_stock',        title:'$on_hand', align:'right',width:90},
                                  {field:'qty_po',           title:'$on_order',align:'right',width:90}]]
                    }}"]],
                'qty_stock'  => ['order'=>30,'label'=>$on_hand,'attr'=>['width'=>100,'disabled'=>true,'resizable'=>true,'align'=>'center'],
                    'events' => ['editor'=>"{type:'numberbox',options:{disabled:true}}",'formatter'=>"function(value,row){ return formatNumber(value); }"]],
                'qty'        => ['order'=>40,'label'=>lang('journal_item_qty', $this->journalID),'attr' =>['width'=>100,'resizable'=>true,'align'=>'center'],
                    'events' => ['editor'=>"{type:'numberbox',options:{onChange:function(){ adjCalc('qty'); } } }",'formatter'=>"function(value,row){ return formatNumber(value); }"]],
                'balance'    => ['order'=>50, 'label'=>lang('balance'),'styles'=>['text-align'=>'right'],
                    'attr'   => ['width'=>100, 'disabled'=>true, 'resizable'=>true, 'align'=>'center'],
                    'events' => ['editor'=>"{type:'numberbox',options:{disabled:true}}",'formatter'=>"function(value,row){ return formatNumber(value); }"]],
                'total'      => ['order'=>60, 'label'=>lang('total'),'format'=>'currency',
                    'attr'   => ['width'=>120,'resizable'=>true,'align'=>'center'],
                    'events' => ['editor'=>"{type:'numberbox'}",'formatter'=>"function(value,row){ return formatNumber(value); }"]],
                'description'=> ['order'=>70,'label'=>lang('description'),'attr'=>['width'=>250,'editor'=>'text','resizable'=>true]]]];
    }
}
