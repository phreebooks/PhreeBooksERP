<?php
/*
 * PhreeBooks journal class for Journal 20, Vendor Payments (Pay Bills)
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
 * @version    3.x Last Update: 2019-03-06
 * @filesource /lib/controller/module/phreebooks/journals/j20.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journals/common.php", 'jCommon');

class j20 extends jCommon
{
    public $journalID = 20;

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
    public function getDataItem() { }

    /**
     * Customizes the layout for this particular journal
     * @param array $data - Current working structure
     * @param integer $rID - current db record ID
     * @param integer $cID - current customer db record ID
     */
    public function customizeView(&$data, $rID=0, $cID=0)
    {
        $fldKeys = ['id','journal_id','so_po_ref_id','terms','override_user','override_pass','recur_id','recur_frequency','item_array','xChild','xAction','store_id',
            'purch_order_id','invoice_num','waiting','closed','terms_text','post_date','rep_id','currency','currency_rate'];
        $data['fields']['currency']['callback'] = 'totalsCurrency';
        unset($data['divs']['divAttach']);
        if ($cID || $this->action=='bulk') {
            $data['fields']['purch_order_id']['attr']['type'] = 'hidden';
            if (!$rID) { $data['fields']['invoice_num']['attr']['value'] = dbGetValue(BIZUNO_DB_PREFIX."current_status", "next_ref_j20"); }
            $data['fields']['terminal_date']['attr']['type'] = 'hidden';
            // pull out just the pmt rows to build datagrid
            $dgData = [];
            foreach ($this->items as $row) { if ($row['gl_type'] == 'pmt') { $dgData[] = $row; } }
            $dgStructure= $this->action=='bulk' ? $this->dgBankingBulk('dgJournalItem', $this->journalID) : $this->dgBanking('dgJournalItem', $this->journalID);
            $map['credit_amount']= ['type'=>'field', 'index'=>'amount'];
            $data['jsHead']['datagridData'] = formatDatagrid($dgData, 'datagridData', $dgStructure['columns'], $map);
            if ($rID || $cID || $this->action=='bulk') { $data['datagrid']['item'] = $dgStructure; }
            if (isset($data['fields']['waiting']['attr']['checked']) && $data['fields']['waiting']['attr']['checked'] == 'checked') {
                $data['fields']['waiting']= ['attr'=>['type'=>'hidden','value'=>'1']];
            } else {
                $data['fields']['waiting']= ['attr'=>['type'=>'hidden','value'=>'0']];
            }
            if (isset($data['fields']['closed']['attr']['checked']) && $data['fields']['closed']['attr']['checked'] == 'checked') {
                $data['fields']['closed']= ['attr'=>['type'=>'hidden','value'=>'1']];
            } else {
                $data['fields']['closed']= ['attr'=>['type'=>'hidden','value'=>'0']];
            }
            $data['divs']['divDetail'] = ['order'=>50,'type'=>'divs','classes'=>['areaView'],'attr'=>['id'=>'pbDetail'],'divs'=>[
                'billAD'  => ['order'=>20,'type'=>'address','classes'=>['blockView'],'attr'=>['id'=>'address_b'],'content'=>$this->cleanAddress($data['fields'], '_b'),
                    'label'=>lang('pay_to'),'settings'=>['suffix'=>'_b','clear'=>false,'props'=>false,'required'=>true,'store'=>false,'cols'=>false]],
                'props'   => ['order'=>40,'type'=>'fields', 'label'=>lang('details'),'classes'=>['blockView'],'attr'=>['id'=>'pbProps'], 'keys'=>$fldKeys],
                'totals'  => ['order'=>50,'type'=>'totals', 'label'=>lang('totals'), 'classes'=>['blockView'],'attr'=>['id'=>'pbTotals'],'content'=>$data['totals']]]];
            $data['divs']['dgItems'] = ['order'=>60,'type'=>'datagrid','key'=>'item'];
            $data['jsHead']['preSubmit'] = "function preSubmit() {
    var items = new Array();
    var dgData = jq('#dgJournalItem').datagrid('getData');
    for (var i=0; i<dgData.rows.length; i++) if (dgData.rows[i]['checked']) items.push(dgData.rows[i]);
    var serializedItems = JSON.stringify(items);
    jq('#item_array').val(serializedItems);
    if (!formValidate()) return false;
    return true;
}";
            $data['jsReady']['init'] = "ajaxForm('frmJournal');
jq('#post_date').datebox({'onChange': function(date) { totalsGetBegBalance(); } });
jq('#gl_acct_id').combogrid({'onChange': function(newVal, oldVal) { totalsGetBegBalance(); } });";
            if ($this->action=='bulk') {
                unset($data['toolbars']['tbPhreeBooks']['icons']['new']);
                unset($data['toolbars']['tbPhreeBooks']['icons']['recur']);
                unset($data['divs']['divDetail']['divs']['billAD']);
                unset($data['jsReady']['focus']);
                $data['forms']['frmJournal']['attr']['action'] = BIZUNO_AJAX."&p=phreebooks/main/saveBulk&jID=$this->journalID";
            }
            unset($data['jsReady']['focus']);
        } else {
            unset($data['divs']['tbJrnl'], $data['fields']['notes']);
            $data['divs']['divDetail']  = ['order'=>50,'type'=>'html','html'=>"<p>".sprintf(lang('search_open_journal'),lang('contacts_type_v'))."</p>".html5('contactSel', ['attr'=>['value'=>'']])];
            $data['jsBody']['selVendor']= "jq('#contactSel').combogrid({width:200,panelWidth:500,delay:500,iconCls:'icon-search',hasDownArrow:false,
    idField:'contact_id_b',textField:'primary_name_b',mode:'remote',
    url:       '".BIZUNO_AJAX."&p=phreebooks/main/managerRowsBank&jID=".JOURNAL_ID."',
    onBeforeLoad:function (param) { var newValue = jq('#contactSel').combogrid('getValue'); if (newValue.length < 2) return false; },
    onClickRow:function (idx, row) { journalEdit(".JOURNAL_ID.", 0, row.contact_id_b); },
    columns:[[
        {field:'contact_id_b',  hidden:true},
        {field:'primary_name_b',title:'".jsLang('address_book_primary_name')."', width:200},
        {field:'city_b',        title:'".jsLang('address_book_city')."', width:100},
        {field:'state_b',       title:'".jsLang('address_book_state')."', width: 50},
        {field:'total_amount',  title:'".jsLang('total')."', width:100, align:'right', formatter:function (value) {return formatCurrency(value);} }]] });";
            $data['jsReady']['init'] = "ajaxForm('frmJournal');";
            $data['jsReady']['focus']= "bizFocus('contactSel');";
        }
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
     * Get re-post records - applies to journals 2, 17, 18, 20, 22
     * @return array - empty
     */
    public function getRepostData()
    {
        msgDebug("\n  j20 - Checking for re-post records ... end check for Re-post with no action.");
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
            for ($i = 0; $i < count($this->items); $i++) { // fetch the list of paid invoices
                if (isset($this->items[$i]['item_ref_id']) && $this->items[$i]['item_ref_id']) {
                    $temp[$this->items[$i]['item_ref_id']] = true;
                }
            }
            $invoices = array_keys($temp);
            for ($i = 0; $i < count($invoices); $i++) {
//              $stmt = dbGetResult("SELECT m.id, m.journal_id, SUM(i.debit_amount - i.credit_amount) AS total_amount
//                  FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
//                  WHERE m.id={$invoices[$i]} AND i.gl_type<>'ttl'");
//              $result1 = $stmt->fetch(\PDO::FETCH_ASSOC);
                $result1 = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', ['id','journal_id','total_amount'], "id={$invoices[$i]}");
                if (in_array($result1['journal_id'], [7])) { $result1['total_amount'] = -$result1['total_amount']; }
                if ($result1['journal_id']==2) { glFindAPacct($result1); } // special case for payables entered through general journal
                $total_billed = roundAmount($result1['total_amount'], $this->rounding);
                $stmt2 = dbGetResult("SELECT m.journal_id, SUM(i.debit_amount - i.credit_amount) AS total_amount
                    FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
                    WHERE i.item_ref_id={$invoices[$i]} AND i.gl_type='pmt'");
                $result2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
                $paid_total = 0;
                foreach ($result2 as $row) {
                    $total = $row['total_amount'];
                    $paid_total += in_array($row['journal_id'], [17]) ? -$total : $total;
                }
                $total_paid = roundAmount($paid_total, $this->rounding);
                msgDebug("\n    rounding = $this->rounding, raw billed = {$result1['total_amount']} which rounded to $total_billed and total_paid = $total_paid");
                $this->setCloseStatus($invoices[$i], $total_billed == $total_paid ? true : false); // either close or re-open
            }
        } else { // unpost - re-open the purchase/invoices affected
            for ($i = 0; $i < count($this->items); $i++) {
                if ($this->items[$i]['item_ref_id']) { $this->setCloseStatus($this->items[$i]['item_ref_id'], false); }
            }
        }
        return true;
    }
}
