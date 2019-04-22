<?php
/*
 * Module PhreeBooks, main functions
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
 * @version    3.x Last Update: 2019-03-27
 * @filesource /lib/controller/module/phreebooks/main.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journal.php", 'journal'); // pulls functions also
bizAutoLoad(BIZUNO_LIB."controller/module/payment/main.php", 'paymentMain');

class phreebooksMain
{
    public  $moduleID = 'phreebooks';
    public  $journalID= 0;
    public  $gl_type  = '';

    function __construct()
    {
        $this->lang   = getLang($this->moduleID);
        $this->rID    = clean('rID', 'integer', 'get');
        $this->action = clean('bizAction', 'text', 'get');
        if ($this->rID && $this->action <> 'inv') { $_GET['jID'] = $this->journalID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'journal_id', "id=$this->rID"); }
        else { $this->journalID = clean('jID', 'integer', 'get'); }
        if (!defined('JOURNAL_ID')) { define('JOURNAL_ID', $this->journalID); }
        $this->type   = clean('type', ['format'=>'char', 'default'=>in_array($this->journalID, [2,3,4,6,7,17,20,21]) ? 'v' : 'c'], 'get');
        if (!defined('CONTACT_TYPE')) { define('CONTACT_TYPE', $this->type); }
        $this->helpIndex = "phreebooks.main.intro.$this->journalID";
        switch ($this->journalID) {
            case  2: $this->gl_type= 'gl';   break; // General Journal
            case  3: // Vendor RFQ
            case  4: // Vendor PO
            case  6: // Vendor Purchases
            case  7: // Vendor Credit Memos
            case  9: // Customer RFQ
            case 10: // Customer SO
            case 12: // Customer Sales
            case 13: // Customer Credit Memos
            case 19: // Point of Sale
            case 21: $this->gl_type = 'itm'; break; // Point of Purchase
            case 14: $this->gl_type = 'asy'; break; // Inventory Assemblies
            case 15: // Inventory Store Transfers
            case 16: $this->gl_type = 'adj'; break; // Inventory Adjustments
            case 17: // Vendor Receipts
            case 18: $this->gl_type = 'pmt'; break; // Customer Receipts
            case 20: // Vendor Payments
            case 22: $this->gl_type = 'pmt'; break; // Customer Payments
        }
    }

    /**
     * Entry point structure for the PhreeBooks journal manager, handles all journals
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
        $rID   = clean('rID', 'integer', 'get');
        $cID   = clean('cID', 'integer', 'get');
        $mgr   = clean('mgr', 'boolean', 'get');
        $jsBody= $jsReady = '';
        $detail= lang('journal_main_journal_id', $this->journalID);
        if     (in_array($this->journalID, [3, 4, 6, 7])) { $manager = sprintf(lang('tbd_manager'), lang('journal_main_journal_id_6')); }
        elseif (in_array($this->journalID, [9,10,12,13])) { $manager = sprintf(lang('tbd_manager'), lang('journal_main_journal_id_12')); }
        else   { $manager= sprintf(lang('tbd_manager'), lang('journal_main_journal_id', $this->journalID)); }
        if ($this->journalID == 0) { // search
            $jsBody = "jq('#postDateMin').datebox({onChange:function (newDate) { jq('#postDateMin').val(newDate); } });
jq('#postDateMax').datebox({onChange:function (newDate) { jq('#postDateMax').val(newDate); } });";
        } elseif (!$mgr || $rID || $cID) { // get the detail screen
            if ($this->action=='inv') { $jsReady = "setTimeout(function () { journalEdit($this->journalID, 0,    $cID, '$this->action', '', $rID) }, 500);\n"; }
            else                      { $jsReady = "setTimeout(function () { journalEdit($this->journalID, $rID, $cID, '$this->action') }, 500);\n"; }
        }
        $jsReady .= "bizFocus('search', 'dgPhreeBooks');";
        if     (in_array($this->journalID, [3, 4, 6, 7]))       { $submenu = viewSubMenu('vendors'); }
        elseif (in_array($this->journalID, [9,10,12,13]))       { $submenu = viewSubMenu('customers'); }
        elseif (in_array($this->journalID, [0]))                { $submenu = viewSubMenu('tools'); }
        elseif (in_array($this->journalID, [2]))                { $submenu = viewSubMenu('ledger'); }
        elseif (in_array($this->journalID, [14,16]))            { $submenu = viewSubMenu('inventory'); }
        elseif (in_array($this->journalID, [17,18,19,20,21,22])){ $submenu = viewSubMenu('banking'); }
        else                                                    { $submenu = ''; }
        $data = [
            'title'=> $detail,
            'divs'     => [
                'submenu'   => ['order'=>10, 'type'=>'html','html'=>$submenu],
                'phreebooks'=> ['order'=>60, 'type'=>'accordion','key'=>'accJournal']],
            'accordion'=> ['accJournal'=> ['divs'=>[
                'divJournalManager'=> ['order'=>30,'label'=>$manager,'type'=>'datagrid','key' =>'manager'],
                'divJournalDetail' => ['order'=>60,'label'=>$detail, 'type'=>'html',    'html'=>lang('msg_edit_new')]]]],
            'datagrid' => ['manager'=> $this->dgPhreeBooks('dgPhreeBooks', $security)],
            'jsHead'   => ['init' => "jq.cachedScript('".BIZUNO_URL."controller/module/phreebooks/phreebooks.js?ver=".MODULE_BIZUNO_VERSION."');"],
            'jsBody'   => ['init' => $jsBody],
            'jsReady'  => ['init' => $jsReady]];
        if ($this->journalID == 0) { unset($data['accordion']['accJournal']['divs']['divJournalDetail']); }
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     * List the journals filter by users selections
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
        $structure = $this->dgPhreeBooks('dgPhreeBooks', $security);
        if ($this->journalID==0) { $structure['strict'] = true; } // needed to search journal_item and limit rows per id
        $layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
    }

    /**
     * Special datagrid list request for orders independent of period
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRowsOrder(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
        $waiting = clean('waiting', 'integer', 'get');
        $_POST['search'] = getSearch();
        $data = $this->dgPhreeBooks('dgPhreeBooks', $security);
        unset($data['source']['filters']['period']);
        // reset some search criteria
        $data['source']['search'] = [BIZUNO_DB_PREFIX.'journal_main.invoice_num'];
        $data['source']['filters']['search'] = ['order'=>90,'label'=>lang('search'),'attr'=>['type'=>'input','value'=>getSearch()]];
        if ($waiting) {
            $data['source']['filters']['waiting']    = ['order'=>99,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."journal_main.waiting='1'"];
            $data['source']['filters']['method_code']= ['order'=>99,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."journal_main.method_code<>''"];
        }
        $data['source']['sort'] = ['s0'=>['order'=>10,'field'=>BIZUNO_DB_PREFIX."journal_main.invoice_num"]];
        $layout = array_replace_recursive($layout, ['type'=>'datagrid','structure'=>$data]);
    }

    /**
     * Builds a list of matches to a search for customers/vendors with outstanding unpaid invoices, also provides the invoiced total (not necessarily the outstanding amount)
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRowsBank(&$layout=[])
    {
        $output = [];
        $_POST['search'] = $search_text = getSearch();
        switch ($this->journalID) {
            case 17:
            case 20: $jID= '6,7';  break;
            case 18:
            case 22: $jID='12,13'; break;
        }
        if ($this->journalID==20 && validateSecurity('phreebooks', 'j2_mgr', 1)) { $jID .= ',2'; }
        $searchFields = [
            BIZUNO_DB_PREFIX.'journal_main.primary_name_b',
            BIZUNO_DB_PREFIX.'journal_main.primary_name_s',
            BIZUNO_DB_PREFIX.'journal_main.postal_code_b',
            BIZUNO_DB_PREFIX.'journal_main.postal_code_s',
            BIZUNO_DB_PREFIX.'journal_main.invoice_num',
            BIZUNO_DB_PREFIX.'journal_main.purch_order_id'];
        $search = $search_text ? "AND (".implode(" like '%$search_text%' or ", $searchFields)." like '%$search_text%')" : '';
        $rows = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", "closed='0' AND contact_id_b>0 AND journal_id IN ($jID) $search", 'primary_name_b',
            ['id','journal_id','contact_id_b','primary_name_b','city_b','state_b','total_amount']);
        foreach ($rows as $row) {
            if (in_array($row['journal_id'], [7,13])) { $row['total_amount'] = -$row['total_amount']; }
            $row['total_amount'] += getPaymentInfo($row['id'], $row['journal_id']);
            if (empty($output[$row['contact_id_b']])) { $output[$row['contact_id_b']] = $row; }
            else                                      { $output[$row['contact_id_b']]['total_amount'] += $row['total_amount']; }
        }
        $layout = array_replace_recursive($layout, ['type'=>'raw','content'=>json_encode(['total'=>sizeof($output),'rows'=>array_values($output)])]);
    }

    /**
     * Loads the filters from the query to populate the datagrid
     */
    private function managerSettings()
    {
        $data = ['path'=>'phreebooks'.$this->journalID, 'values'=>  [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno','settings','general','max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX.'journal_main.post_date'],
            ['index'=>'order', 'clean'=>'text',   'default'=>'DESC'],
            ['index'=>'period','clean'=>'text',   'default'=>'l'],
            ['index'=>'jID',   'clean'=>'integer','default'=>'a'],
            ['index'=>'status','clean'=>'char',   'default'=>'a'],
            ['index'=>'search','clean'=>'text',   'default'=>'']]];
        if (clean('clr', 'boolean', 'get')) { clearUserCache($data['path']); }
        $this->defaults = updateSelection($data);
    }

    /**
     * Structure to edit a journal entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        $rID       = $this->rID = clean('rID', ['format'=>'integer','default'=>0], 'get');
        $cID       = clean('cID', 'integer', 'get'); // contact record for banking stuff
        $references= (array)explode(":", clean('iID', 'text', 'get'));
        $xAction   = clean('xAction','text', 'get');
        $prefix    =  $rID && $this->action<>'inv' ? "rID_{$rID}_" : "rID_0_";
        $min_level = !$rID || $this->action=='inv' ? 2 : 1; // if converting SO/PO then add else read-only and above
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", $min_level)) { return; }
        if (!$cID && sizeof($references) && !empty($references[0])) { // attempt to pull contact_id from prechecked records
            $cID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'contact_id_b', "id={$references[0]}");
        }
        $default_gl = $this->type=='v' ? getModuleCache('phreebooks', 'settings', 'vendors', 'gl_purchases') : getModuleCache('phreebooks', 'settings', 'customers', 'gl_sales');
        $default_tax= 0;
        $preSubmit  = "function preSubmit() {\n\tjq('#dgJournalItem').edatagrid('saveRow');\n\ttotalUpdate('preSubmit');\n\t
var items = jq('#dgJournalItem').datagrid('getData');\n\tjq('#item_array').val(JSON.stringify(items));\n\t
if (!formValidate()) return false;\n\treturn true;\n}";
        $jsHead     = $jsBody = '';
        $jsReady    = "ajaxForm('frmJournal');\njq('#dgJournalItem').edatagrid('addRow');\n";

        $structure  = dbLoadStructure(BIZUNO_DB_PREFIX.'journal_main', $this->journalID);
        // complete the structure
        $structure['invoice_num']['tip'] = lang('msg_leave_null_to_assign_ref');
        $structure['rep_id']['values']   = viewRoleDropdown();
        if (sizeof(getModuleCache('phreebooks', 'currency', 'iso')) > 1) {
            $structure['currency']['callback']    = 'totalsCurrency';
            $structure['currency']['attr']['type']= 'selCurrency';
            $structure['currency_rate']['attr']['readonly']= 'readonly';
            $structure['currency_rate']['attr']['type'] = 'float';
        }
        $ledger = new journal($rID, $this->journalID, false, $cID, $structure, $this->action);
        $ledger->journal->type  = $this->type;
        $ledger->journal->lang  = $this->lang;
        $ledger->journal->rID   = $this->rID;
        $ledger->journal->totals= $this->loadTotals($this->journalID);

        dbStructureFill($structure, $ledger->main);
        if ($rID > 0 || $cID > 0 || $this->action=='bulk') {
            msgDebug("\nReading unique journal $this->journalID data");
            if ($rID) { $jsReady .= "jq('#spanContactProps_b').show();\n"; }
            $cID    = $structure['contact_id_b']['attr']['value'];
            $defs   = $cID ? dbGetValue(BIZUNO_DB_PREFIX.'contacts', ['gl_account','tax_rate_id'], "id=$cID") : ['gl_account'=>$default_gl,'tax_rate_id'=>$default_tax];
            $jsBody.= "var def_contact_gl_acct = '{$defs['gl_account']}';\nvar def_contact_tax_id  = ".($defs['tax_rate_id'] < 0 ? 0 : $defs['tax_rate_id']).";\n";
        } else { // new entry
            $jsHead .= "var datagridData = ".json_encode(['total'=>0, 'rows'=>[]]).";\n";
            if ($this->action<>'inv') { $jsReady .= "bizCheckBox('AddUpdate_b');\n"; }
            $jsBody .= "var def_contact_gl_acct = '$default_gl';\nvar def_contact_tax_id = ".($default_tax ? $default_tax : 0).";\n";
        }

        // Add some new non-db fields
        $structure['terms_text']     = ['label'=>lang('terms'),'attr'=>['value'=>viewTerms($structure['terms']['attr']['value'], true, $this->type),'readonly'=>'readonly']];
        $structure['terms_edit']     = ['icon'=>'settings','label'=>lang('terms'),'events'=>['onClick'=>"jsonAction('contacts/main/editTerms&type=$this->type', $cID, jq('#terms').val());"]];
        $structure['journal_msg']    = ['html'=>'','attr'=>['type'=>'raw']];
        $structure['override_user']  = ['attr'=>['type'=>'hidden']];
        $structure['override_pass']  = ['attr'=>['type'=>'hidden']];
        $structure['recur_frequency']= ['attr'=>['type'=>'hidden']];
        $structure['item_array']     = ['attr'=>['type'=>'hidden']];
        $structure['xChild']         = ['attr'=>['type'=>'hidden']];
        $structure['xAction']        = ['attr'=>['type'=>'hidden']];

        // Set the Head , set defaults, gather the totals methods, etc.
        $jsHead  .= "bizDefaults.phreebooks = { journalID:$this->journalID,type:'$this->type' };\n";
        $jsHead  .= "var totalsMethods = ".json_encode($ledger->journal->totals).";\n";
        $jsTotals = "function totalUpdate(func) {\n\tvar taxRunning=0;\n\tvar curTotal=0;\n";
        foreach ($ledger->journal->totals as $methID) { $jsTotals .= "\tcurTotal = totals_{$methID}(curTotal);\n"; }
        $jsTotals.= "}";
        $jsHead  .= $jsTotals;
        // strip inactive accounts from the gl chart
        $pbChart  = "var pbChart=[];\njq.each(bizDefaults.glAccounts.rows, function( key, value ) { if (value['inactive'] == '0') { pbChart.push(value); } });";
        // Get the item data
        msgDebug("\nGoing to getDataItem, rID = $rID and cID = $cID");
        $ledger->getDataItem($rID, $cID, $security);
        msgDebug("\nsetting currency to {$structure['currency']['attr']['value']}");
        // uncommenting the following causes ALL numberboxes to format as currency. See total-discount percent field
//      if (sizeof(getModuleCache('phreebooks', 'currency', 'iso')) > 1) { $jsHead .= "setCurrency('{$structure['currency']['attr']['value']}');\n"; }

        $data  = ['type'=>'divHTML',
            'divs'    => [
                'status'   => ['order'=> 5,'styles'=>['float'=>'right'],'type'=>'fields','keys'=>['journal_msg']],
                'tbJrnl'   => ['order'=>10,'type'=>'toolbar','key' =>'tbPhreeBooks'],
                'formBOF'  => ['order'=>15,'type'=>'form',   'key' =>'frmJournal'],
                'divAttach'=> ['order'=>80,'type'=>'attach', 'defaults'=>['path'=>getModuleCache($this->moduleID,'properties','attachPath'),'prefix'=>$prefix]],
                'formEOF'  => ['order'=>99,'type'=>'html',   'html'=>'</form>']],
            'toolbars'=> ['tbPhreeBooks'=>['icons'=>[
                'jSave'=> ['order'=>10,'label'=>lang('save'),  'icon'=>'save','type'=>'menu','hidden'=>$security>1?false:true,
                    'events'=> ['onClick'=>"jq('#frmJournal').submit();"],'child'=>$this->renderMenuSave($security)],
                'recur'=> ['order'=>50,'label'=>lang('recur'), 'tip'=>lang('recur_new'),'hidden'=>$security>1?false:true,'events'=>['onClick'=>"var data=jq('input[name=radioRecur]:checked').val()+':'+jq('#recur_id').val(); windowEdit('phreebooks/main/popupRecur&data='+data, 'winRecur', '".jsLang('recur')."', 450, 450);"]],
                'new'  => ['order'=>60,'label'=>lang('new'),   'hidden'=>$security>1?false:true,'events'=>['onClick'=>"journalEdit($this->journalID, 0);"]],
                'trash'=> ['order'=>70,'label'=>lang('delete'),'hidden'=>$rID && $security==4?false:true,'events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreebooks/main/delete&jID=$this->journalID', $rID);"]],
                'help' => ['order'=>99,'label'=>lang('help'),  'align'=>'right','index' =>$this->helpIndex]]]],
            'forms'   => ['frmJournal'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/main/save&jID=$this->journalID"]]],
            'fields'  => $structure,
            'items'   => $ledger->items,
            'totals'  => $ledger->journal->totals,
            'jsHead'  => ['init'=>$jsHead, 'preSubmit'=>$preSubmit, 'pbChart'=>$pbChart],
            'jsBody'  => ['init'=>$jsBody],
            'jsReady' => ['init'=>$jsReady, 'focus'=>"bizFocus('contactSel_b');"]];
        // Customize the layout for this journalID
        $ledger->customizeView($data, $rID, $cID, $security);
        if (substr($xAction, 0, 8) == 'journal:') { // see if there are any extra actions
            $temp = explode(':', $xAction);
            if (isset($temp[1])) {
                $data['toolbars']['tbPhreeBooks']['icons']['jSave']['events']['onClick'] = "jq('#xAction').val('journal:{$temp[1]}'); jq('#frmJournal').submit();";
                $data['toolbars']['tbPhreeBooks']['icons']['cancel'] = ['order'=>5,'events'=>['onClick'=>"journalEdit({$temp[1]}, 0);"]];
                unset($data['toolbars']['tbPhreeBooks']['icons']['new']);
            }
        }
        msgDebug("\nFinished edit processing!");
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Posts a journal entry to the db
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[])
    {
        $rID = clean('id', 'integer', 'post');
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", $rID?3:2)) { return; }
        $xChild   = clean('xChild', 'text', 'post');
        $xAction  = clean('xAction','text', 'post');
        $recurID  = clean('recur_id', 'integer', 'post');
        $recurFreq= clean('recur_frequency', 'integer', 'post');
        $GLOBALS['bizunoCurrency'] = clean('currency',['format'=>'alpha_num','default'=>getUserCache('profile','currency',false,'USD')],'post');
        $structure = ['journal_main' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_main', $this->journalID)];
        $values = requestData($structure['journal_main'], '', true);
        $values['period']   = calculatePeriod($values['post_date'] ? $values['post_date'] : date('Y-m-d')); // recalc period as post date may have changed
        $values['drop_ship']= clean('drop_ship_s', 'bool', 'post');
        // ***************************** START TRANSACTION *******************************
        // Assume all transactions are bulk transactions, or if no contact id set, treat as bulk.
        // iterate through the ref_id's and check for multiple contacts, perhaps pre-processing
        msgDebug("\n  Started order post invoice_num = {$values['invoice_num']} and id = {$rID}");
        dbTransactionStart();
        $ledger = new journal($rID, $this->journalID);
        $ledger->main['description'] = pullTableLabel('journal_main', 'journal_id', $this->journalID);
        $ledger->main = array_replace($ledger->main, $values);
        // add/update address book, address updates need to be here so recur doesn't keep making new contacts
        if (clean('AddUpdate_b', 'bool', 'post')) { if (!$ledger->updateContact('b')) { return; } }
        if (clean('AddUpdate_s', 'bool', 'post')) {
            if (!$ledger->main['contact_id_s']) { $ledger->main['contact_id_s'] = $ledger->main['contact_id_b']; }
            if (!$ledger->updateContact('s'))   { return; }
        }
        if (in_array($ledger->journalID, [3,4,6,7,9,10,12,13]) && empty($ledger->main['contact_id_b'])) { return msgAdd($this->lang['msg_missing_contact_id']); }
        if (!$this->getItems($ledger))  { return; }
        if (!$this->getTotals($ledger)) { return; }
        msgDebug("\nMapped journal rows = ".print_r($ledger->items, true));
        // ************* POST journal entry *************
        if ($ledger->main['recur_id'] > 0) { // if new record, will contain count, if edit will contain recur_id
            $first_invoice_num   = $ledger->main['invoice_num'];
            if ($ledger->main['id']) { // it's an edit, fetch list of affected records to update if roll is enabled
                $affected_ids = $ledger->get_recur_ids($ledger->main['recur_id'], $ledger->main['id']);
                msgDebug("\nAffected ID's for recurring entry: ".print_r($affected_ids, true));
                for ($i = 0; $i < count($affected_ids); $i++) {
                    $ledger->main = array_replace($ledger->main, $affected_ids[$i]);
                    if ($i > 0) { // Remove row id's for future posts, keep if re-posting single entry
                        for ($j = 0; $j < count($ledger->items); $j++) { $ledger->items[$j]['id'] = ''; }
                    }
                    msgDebug("\n************ Re-posting recur id = {$ledger->main['id']} ******************");
                    if (!$ledger->Post()) { return; }
                    $ledger->postList = []; // reset the postList to prevent reposting prior recurs
                    // test for single post versus rolling into future posts, terminate loop if single post
                    if (empty($recurFreq)) { break; }
                }
            } else { // it's an insert, fetch the next recur id
                $forceInv = $ledger->main['invoice_num'] != '' ? $ledger->main['invoice_num'] : false;
                $ledger->main['recur_id'] = time(); // time stamp the transaction to link together
                $origPost = clone $ledger;
                for ($i=1; $i<=$recurID; $i++) { // number of recurs
                    if (!$ledger->Post()) { return; }
                    if ($i == 1) { $first_invoice_num = $ledger->main['invoice_num']; }
                    if ($i == $recurID) { continue; } // we're done, skip the prep
                    // prepare the next post or prepare to exit if finished
                    $ledger = clone $origPost;
                    switch ($recurFreq) {
                        default:
                        case '1': $day_offset = $i*7;  $month_offset = 0; break; // Weekly
                        case '2': $day_offset = $i*14; $month_offset = 0; break; // Bi-weekly
                        case '3': $day_offset = 0; $month_offset = $i;    break; // Monthly
                        case '4': $day_offset = 0; $month_offset = $i*3;  break; // Quarterly
                    }
                    $ledger->main['post_date']    = localeCalculateDate($ledger->main['post_date'],     $day_offset, $month_offset);
                    $ledger->main['terminal_date']= localeCalculateDate($ledger->main['terminal_date'], $day_offset, $month_offset);
                    $ledger->main['period']       = calculatePeriod($ledger->main['post_date'], false);
                    if ($forceInv) { $forceInv++; $ledger->main['invoice_num'] = $forceInv; }
                    foreach ($ledger->items as $key => $row) {
                        $ledger->items[$key]['post_date'] = $ledger->main['post_date'];
                        $ledger->items[$key]['date_1']    = $ledger->main['terminal_date'];
                    }
                }
            }
            // restore the first values to continue with post process
            $ledger->main['invoice_num'] = $first_invoice_num;
        } else {
            if (!$ledger->Post()) { return; }
        }
        // ************* post-POST processing *************
        if (in_array($this->journalID, array(17, 18, 19))) { // process the payment, must be at end since it's hard to undo, no transaction support
            $processor = new paymentMain();
            if (!$processor->sale($ledger->main['method_code'], $ledger)) { return; }
        }
        msgDebug("\n  Committing order invoice_num = {$ledger->main['invoice_num']} and id = {$ledger->main['id']}");
        dbTransactionCommit();
        // ***************************** END TRANSACTION *******************************
        $_POST['rID'] = $ledger->main['id']; // set the record ID as we now have a successfult transaction
        $this->getAttachments('file_attach', $ledger->main['id'], $ledger->main['so_po_ref_id']);
        $invoiceRef = pullTableLabel('journal_main', 'invoice_num', $ledger->main['journal_id']);
        $billName   = isset($ledger->main['primary_name_b']) ? $ledger->main['primary_name_b'] : $ledger->main['description'];
        $journalRef = pullTableLabel('journal_main', 'journal_id', $ledger->main['journal_id']);
        msgAdd(sprintf(lang('msg_gl_post_success'), $invoiceRef, $ledger->main['invoice_num']), 'success');
        msgLog($journalRef.'-'.lang('save')." $invoiceRef ".$ledger->main['invoice_num']." - $billName (rID={$ledger->main['id']}) ".lang('total').": ".viewFormat($ledger->main['total_amount'], 'currency'));
        $jsonAction = "jq('#accJournal').accordion('select',0); jq('#dgPhreeBooks').datagrid('reload'); jq('#divJournalDetail').html('&nbsp;');";
        if (in_array($ledger->main['journal_id'], [2,3,4,6,9,10,12,20,22])) { $jsonAction = "jq('#jSave').splitbutton('destroy'); ".$jsonAction; } // only for splitbutton, else errors and halts script
        switch ($xAction) { // post processing extra stuff to do
            case 'payment':
                switch ($this->journalID) {
                    case  6: $next_journal = 20; break;
                    case  7: $next_journal = 17; break;
                    case 12: $next_journal = 18; break;
                    case 13: $next_journal = 22; break;
                    default: $next_journal = false;
                }
                if ($next_journal) {
                    $jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit($next_journal, 0, {$ledger->main['contact_id_b']}, 'inv', '', {$ledger->main['id']});";
                }
                break;
            case 'invoice':
                switch ($this->journalID) {
                    case  4: $next_journal =  6; break;
                    case 10: $next_journal = 12; break;
                    default: $next_journal = false;
                }
                if ($next_journal) {
                    $jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit($next_journal, 0, 0, 'inv', '', {$ledger->main['id']});";
                }
                break;
            case 'journal:12':
                $jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit(12, 0);"; break;
            case 'journal:6':
                $jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit(6, 0);"; break;
        }
        switch ($xChild) { // child screens to spawn
            case 'print':
                $formID     = getDefaultFormID($this->journalID);
                $jsonAction .= " winOpen('phreeformOpen', 'phreeform/render/open&group=$formID&date=a&xfld=journal_main.id&xcr=equal&xmin={$ledger->main['id']}');";
                break;
        }
        $layout = array_replace_recursive($layout, ['rID'=>$ledger->main['id'], 'content'=>  ['action'=>'eval','actionData'=>$jsonAction]]);
    }

    /**
     * Structure to pay bills to multiple vendors from a single page view
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function saveBulk(&$layout=[])
    {
        msgDebug("\n  Started saveBulk");
        if (!$security = validateSecurity('phreebooks', "j20_bulk", 2)) { return; }
        $xChild = clean('xChild', 'text', 'post');
        $structure= [
            'journal_main' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_main', $this->journalID),
            'journal_item' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_item', $this->journalID)];
        clearUserCache('phreebooks'.$this->journalID); // clear the manager history for new saves, will then show new post on top.
        // organize selections by contact_id create mini 'item_data' for each contact
        $rows = clean('item_array', 'json', 'post');
        $cIDs = array();
        foreach ($rows as $row) {
            if (!isset($cIDs[$row['contact_id']]['dsc'])) { $cIDs[$row['contact_id']]['dsc'] = 0; }
            if (!isset($cIDs[$row['contact_id']]['ttl'])) { $cIDs[$row['contact_id']]['ttl'] = 0; }
            $cIDs[$row['contact_id']]['dsc']   += clean($row['discount'],'float');
            $cIDs[$row['contact_id']]['ttl']   += clean($row['total'],   'float');
            $cIDs[$row['contact_id']]['rows'][] = $row;
        }
        $post_date = clean('post_date', 'date', 'post');
        $first_chk = $current_chk = $next_chk = clean('invoice_num', 'text', 'post'); // save the first check number for printing
        $first_id  = 0;
        $pmt_total = clean('total_amount', 'float', 'post');
        if (!$first_chk) { return msgAdd("Ref # cannot be blank!"); }
        // ***************************** START TRANSACTION *******************************
        dbTransactionStart();
        foreach ($cIDs as $cID => $items) {
            $address= dbGetRow(BIZUNO_DB_PREFIX.'address_book', "ref_id=$cID AND type='m'");
            $ledger = new journal(0, $this->journalID, $post_date);
            // Fill mains and items
            $_POST['totals_discount']= $items['dsc']; // need to fake out form total for each vendor
            $_POST['total_amount']   = $items['ttl'];
            $_POST['item_array']     = json_encode($items['rows']); // just the rows for this contact
            $current_chk = $next_chk;
            $main = [
                'gl_acct_id'    => clean('gl_acct_id', 'text', 'post'),
                'invoice_num'   => $current_chk,
                'purch_order_id'=> clean('purch_order_id', 'text', 'post'),
                'rep_id'        => clean('rep_id', 'integer', 'post'),
                'contact_id_b'  => $cID,
                'address_id_b'  => $address['address_id'],
                'primary_name_b'=> $address['primary_name'],
                'contact_b'     => $address['contact'],
                'address1_b'    => $address['address1'],
                'address2_b'    => $address['address2'],
                'city_b'        => $address['city'],
                'state_b'       => $address['state'],
                'postal_code_b' => $address['postal_code'],
                'country_b'     => $address['country'],
                'telephone1_b'  => $address['telephone1'],
                'email_b'       => $address['email']];
            $ledger->main = array_replace($ledger->main, $main);
            // pull items
            $map = [
                'ref_id'        => ['type'=>'constant','value'=>$ledger->main['id']],
                'gl_type'       => ['type'=>'constant','value'=>$this->gl_type],
                'post_date'     => ['type'=>'constant','value'=>$ledger->main['post_date']],
                'credit_amount' => ['type'=>'constant','value'=>'0'],
                'debit_amount'  => ['type'=>'field',   'index'=>'total'],
                'date_1'        => ['type'=>'field',   'index'=>'inv_date'],
                'trans_code'    => ['type'=>'field',   'index'=>'inv_num']];
            $ledger->items= requestDataGrid($items['rows'], $structure['journal_item'], $map);
            foreach ($ledger->items as $idx => $row) { $ledger->items[$idx]['item_cnt'] = $idx+1; }
            $ledger->main['description'] = pullTableLabel('journal_main', 'journal_id', $ledger->main['journal_id']);
            $ledger->main['description'].= isset($ledger->main['primary_name_b']) ? ": {$ledger->main['primary_name_b']}" : '';
            // pull totals
            $current_total = 0;
            foreach ($ledger->items as $row) { $current_total += $row['debit_amount'] + $row['credit_amount']; } // subtotal of all rows
            msgDebug("\n\nStarting to build total GL rows, starting subtotal = $current_total");
            $ledger->totals = $this->loadTotals($this->journalID);
            foreach ($ledger->totals as $methID) {
                $path = getModuleCache('phreebooks', 'totals', $methID, 'path');
                $fqcn = "\\bizuno\\$methID";
                bizAutoLoad("{$path}$methID.php", $fqcn);
                $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
                $totalEntry = new $fqcn($totSet);
                if (method_exists($totalEntry, 'glEntry')) { $totalEntry->glEntry($ledger->main, $ledger->items, $current_total); }
            }
            msgDebug("\n\nMapped journal main = ".print_r($ledger->main,  true));
            msgDebug("\n\nMapped journal item = ".print_r($ledger->items, true));
            // check calculated total against submitted total, course error check
            if (round($current_total, 2) <> round($ledger->main['total_amount'], 2)) {
                msgDebug("\nin SaveBulk, failed comparing calc total =  ".round($current_total, 2)." with submitted total = ".round($ledger->main['total_amount'], 2));
                return msgAdd(sprintf($this->lang['err_total_not_match'], round($current_total, 2), round($ledger->main['total_amount'], 2)), 'trap');
            }
            if (!$ledger->Post()) { return; }
            msgDebug("\n  Committing order invoice_num = $current_chk and id = {$ledger->main['id']}");
            $next_chk++; // increment the invoice number
            if (!$first_id) { $first_id = $ledger->main['id']; } // save the first main ['id'] for printing
        }
        dbTransactionCommit();
        // ***************************** END TRANSACTION *******************************
        $invRef = pullTableLabel('journal_main', 'invoice_num', $ledger->main['journal_id']);
        msgAdd(sprintf(lang('msg_gl_post_success'), $invRef, "$first_chk - $current_chk"), 'success');
        msgLog(lang('phreebooks_manager_bulk').'-'.lang('journal_main_invoice_num_20')." $first_chk - $current_chk ".lang('total').": ".viewFormat($pmt_total, 'currency'));
//        $jsonAction = "journalEdit(6, 0);";
        $jsonAction = "jq('#accJournal').accordion('select',0); jq('#dgPhreeBooks').datagrid('reload'); jq('#divJournalDetail').html('&nbsp;');";
        switch ($xChild) { // post processing extra stuff to do
            case 'print':
                $formID     = getDefaultFormID($this->journalID);
                $jsonAction.= " winOpen('phreeformOpen', 'phreeform/render/open&group=$formID&date=a&xfld=journal_main.id&xcr=range&xmin=$first_id&xmax={$ledger->main['id']}');";
                break;
        }
        $layout = array_replace_recursive($layout, ['rID'=>$ledger->main['id'],'content'=>['action'=>'eval','actionData'=>$jsonAction]]);
    }

    /**
     * Structure to delete a single PhreeBooks journal entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!validateSecurity('phreebooks', "j{$this->journalID}_mgr", 4)) { return; }
        $rID     = clean('rID', 'integer', 'get');
        $delRecur= clean('delAll', 'integer', 'get');
        $delOrd  = new journal($rID);
        // Error Check
        if (!$rID) { return msgAdd(lang('err_copy_name_prompt')); }
        if (getUserCache('profile', 'restrict_period') && $delOrd->period <> getModuleCache('phreebooks', 'fy', 'period')) {
            return msgAdd(lang('ORD_ERROR_DEL_NOT_CUR_PERIOD'));
        }
        switch ($this->journalID) { // some rules checking
            case  4: // Purchase Order Journal
            case 10: // Sales Order Journal
                if ($xID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', "id", "so_po_ref_id=$rID")) { return msgAdd(sprintf($this->lang['err_journal_delete'], "(id=$xID) ".lang('journal_main_journal_id_'.$this->journalID))); }
                break;
            case  6: // Purchase Journal
            case  7: // Vendor Credit Memo Journal
            case 12: // Sales/Invoice Journal
            case 13: // Customer Credit Memo Journal
                // first check for main entries that refer to delete id (credit memos)
                if ($xID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', "id", "so_po_ref_id=$rID")) { return msgAdd(sprintf($this->lang['err_journal_delete'], "(id=$xID) ".lang('journal_main_journal_id_'.$this->journalID))); }
                // next check for payments that link to deleted id (payments)
                if ($xID = dbGetValue(BIZUNO_DB_PREFIX.'journal_item', "id", "gl_type='pmt' AND item_ref_id=$rID")) { return msgAdd(sprintf($this->lang['err_journal_delete'], "(id=$xID) ".lang('journal_main_journal_id_'.$this->journalID))); }
                break;
            default:
        }
        // *************** START TRANSACTION *************************
        dbTransactionStart();
        // ************* pre-unPOST processing *************
        if (in_array($this->journalID, [17, 18, 19])) { // refund the payment, must be at start since record is deleted with trans_code
            $method = $delOrd->main['method_code'];
            $methPath = getModuleCache('payment', 'methods', $method, 'path');
            if (!$methPath) { return msgAdd("Cannot apply credit since the method is not installed!"); }
            $fqcn = "\\bizuno\\$method";
            bizAutoLoad("$methPath{$method}.php", $fqcn);
            $pmtSet = getModuleCache('payment','methods',$method,'settings');
            $processor = new $fqcn($pmtSet);
            if ($delOrd->main['post_date'] == date('Y-m-d')) {
                if (method_exists($processor, 'paymentVoid')) { if (!$processor->paymentVoid($delOrd->main['id'])) { return; } }
            } else {
                if (method_exists($processor, 'paymentRefund')) { if (!$processor->paymentRefund($delOrd->main['id'])) { return; } }
            }
        }
        if (isset($delOrd->recur_id) && $delOrd->recur_id > 0 && $delRecur) { // will contain recur_id
            $affected_ids = $delOrd->get_recur_ids($delOrd->recur_id, $delOrd->id);
            foreach  ($affected_ids as $mID) {
                $delRecur = new journal($mID['id']);
                msgDebug("\nunPosting recur id = {$delRecur->main['id']}");
                if (!$delRecur->unPost()) { return dbTransactionRollback(); }
            }
        } else {
            msgDebug("\nunPosting id = {$delOrd->main['id']}");
            if (!$delOrd->unPost()) { return dbTransactionRollback(); }
        }
        dbTransactionCommit();
        // *************** END TRANSACTION *************************
        msgLog(lang('journal_main_journal_id', $this->journalID).' '.lang('delete')." - {$delOrd->main['invoice_num']} (rID=$rID)");
        $files = glob(getModuleCache('phreebooks', 'properties', 'attachPath')."rID_".$rID."_*.*");
        if (is_array($files)) { foreach ($files as $filename) { @unlink($filename); } } // remove attachments
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jq('#accJournal').accordion('select',0); jq('#dgPhreeBooks').datagrid('reload'); jq('#divJournalDetail').html('&nbsp;');"]]);
    }

    /**
     * Gets the datagrid items and removes empty rows
     * @param type $ledger
     * @return type
     */
    private function getItems(&$ledger)
    {
        $ledger->items = []; // reset the item list as we start with just the datagrid
        $structure = dbLoadStructure(BIZUNO_DB_PREFIX.'journal_item', $this->journalID);
        $map = [
            'ref_id'   => ['type'=>'constant', 'value'=>$ledger->main['id']],
            'gl_type'  => ['type'=>'constant', 'value'=>$this->gl_type],
            'post_date'=> ['type'=>'constant', 'value'=>$ledger->main['post_date']]];
        if (!in_array($this->journalID, [2])) {
            $debitCredit = in_array($this->journalID, [3,4,6,13,16,20,21,22]) ? 'debit' : 'credit';
            $map['credit_amount']= $debitCredit=='credit'? ['type'=>'field','index'=>'total'] : ['type'=>'constant','value'=>0];
            $map['debit_amount'] = $debitCredit=='debit' ? ['type'=>'field','index'=>'total'] : ['type'=>'constant','value'=>0];
        }
        if (in_array($this->journalID, [17,18,20,22])) {
            $map['date_1']    = ['type'=>'field','index'=>'post_date'];
            $map['trans_code']= ['type'=>'field','index'=>'invoice_num'];
        }
        $items    = requestDataGrid(clean('item_array', 'json', 'post'), $structure, $map);
        $skipList = ['sku', 'description', 'credit_amount', 'debit_amount']; // if qty=0 or all these are not set or null, row is blank
        $item_cnt = 1;
        foreach ($items as $row) {
            if (!isBlankRow($row, $skipList)) {
                $row['item_cnt']     = $item_cnt;
                $row['debit_amount'] = roundAmount($row['debit_amount']);
                $row['credit_amount']= roundAmount($row['credit_amount']);
                $ledger->items[] = $row;
            }
            $item_cnt++;
        }
        if (empty($ledger->items)) { return msgAdd($this->lang['msg_no_items']); }
        return true;
    }

    /**
     * Pulls posted total values and creates the GL entries
     * @param type $ledger
     */
    private function getTotals(&$ledger)
    {
        $current_total = 0;
        foreach ($ledger->items as $row) { $current_total += $row['debit_amount'] + $row['credit_amount']; } // subtotal of all rows
        msgDebug("\nStarting to build total GL rows, starting subtotal = $current_total");
        $ledger->main['sales_tax'] = $ledger->main['discount'] = 0; // clear the sales tax and discount before building new values
        $ledger->totals = $this->loadTotals($this->journalID);
        foreach ($ledger->totals as $methID) {
            $path = getModuleCache('phreebooks', 'totals', $methID, 'path');
            $fqcn = "\\bizuno\\$methID";
            bizAutoLoad("{$path}$methID.php", $fqcn);
            $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
            $totalEntry = new $fqcn($totSet);
            if (method_exists($totalEntry, 'glEntry')) { $totalEntry->glEntry($ledger->main, $ledger->items, $current_total); }
        }
        // check calculated total against submitted total, course error check
        if (!in_array($this->journalID, array(2,14,15,16)) && number_format($current_total, 2) <> number_format($ledger->main['total_amount'], 2)) {
            msgDebug("\nIn getTotals, failed comparing calc total =  ".number_format($current_total, 2)." with submitted total = ".number_format($ledger->main['total_amount'], 2));
            return msgAdd(sprintf($this->lang['err_total_not_match'], number_format($current_total, 2), number_format($ledger->main['total_amount'], 2)), 'trap');
        }
        return true;
    }

    /**
     * Retrieves the detailed status for a single contact, typically used as a pop up
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function detailStatus(&$layout=[])
    {
        $cID     = clean('rID', 'integer', 'get');
        $stmt    = dbGetResult("SELECT c.type, c.inactive, c.terms, a.notes FROM ".BIZUNO_DB_PREFIX."contacts"." c JOIN ".BIZUNO_DB_PREFIX."address_book"." a ON c.id=a.ref_id WHERE c.id=$cID AND a.type LIKE '%m'");
        $contact = $stmt->fetch(\PDO::FETCH_ASSOC);
        $new_data= calculate_aging($cID);
        // set the customer/vendor status in order of importance
        if     ($contact['inactive'])                           { $statusBg='red';    $statusMsg=lang('inactive');    }
        elseif ($new_data['past_due'] > 0)                      { $statusBg='yellow'; $statusMsg=$this->lang['msg_contact_status_past_due']; }
        elseif ($new_data['total'] > $new_data['credit_limit']) { $statusBg='yellow'; $statusMsg=$this->lang['msg_contact_status_over_limit']; }
        else                                                    { $statusBg='green';  $statusMsg=$this->lang['msg_contact_status_good']; }
        $statusHtml = '<p style="font-weight:bold;text-align:center;background-color:'.$statusBg.'">'.$statusMsg."</p>";
        $data = ['type'=>'popup','attr'=>['id'=>'winStatus'],
            'title'=> sprintf(lang('tbd_summary'), lang('contacts_type', $contact['type'])),
            'divs' => [
                'status' => ['order'=>10,'type'=>'html','html'=>$statusHtml],
                'history'=> ['order'=>30,'label'=>lang('history'),'type'=>'fields','classes'=>['blockView'],'fields'=>$this->getStatusHistory($contact, $new_data)],
                'terms'  => ['order'=>40,'label'=>lang('terms'),  'type'=>'fields','classes'=>['blockView'],'fields'=>$this->getStatusTerms($new_data)],
                'balance'=> ['order'=>50,'label'=>lang('account'),'type'=>'fields','classes'=>['blockView'],'fields'=>$this->getStatusBalance($new_data)]]];
        $notes = !empty($contact['notes']) ? str_replace("\n", "<br />", $contact['notes']) : false;
        if ($notes) {
            $fldNotes = ['notes'=>['label'=>lang('notes'),'attr'=>['type'=>'p','value'=>$notes]]];
            $data['divs']['notes'] = ['order'=>20,'label'=>lang('notes'),  'type'=>'fields','fields'=>$fldNotes];
        }
        $layout = array_replace_recursive($layout, $data);
    }

    private function getStatusTerms($new_data)
    {
        $output = "<p>{$new_data['terms_lang']}</p>";
        if (!empty($new_data['past_due'])) {
            $output .= '<p style="background-color:yellow;">'.sprintf($this->lang['msg_contact_past_due_amount'], viewFormat($new_data['past_due'], 'currency'))."</p>";
        }
        return ['terms'=>['html'=>$output,'attr'=>['type'=>'raw']]];
    }

    private function getStatusHistory($contact, $new_data)
    {
        if ($contact['type']=='v') { $idx='vendors';   $jQuote=3; $jOrder= 4; $jSale= 6; $jRtn= 7; }
        else                       { $idx='customers'; $jQuote=9; $jOrder=10; $jSale=12; $jRtn=13; }
        $output = [];
        $output['inv_orders'] = ['order'=>10,'label'=>$this->lang['status_orders_invoice'],'break'=>true,'html'=>lang('none')."<br />",'attr'=>['type'=>'raw']];
        if (sizeof($new_data['inv_orders']) >1) { $output['inv_orders'] = array_merge($output['inv_orders'], ['values'=>$new_data['inv_orders'],
            'attr'=>['type'=>'select'],'events'=>['onChange'=>"jq(this).combogrid('destroy'); bizWindowClose('winStatus'); journalEdit($jSale, 0, 0, 'inv', '', newVal);"]]); }
        $output['open_quotes'] = ['order'=>20,'label'=>$this->lang['status_open_j9'],'break'=>true,'html'=>lang('none')."<br />",'attr'=>['type'=>'raw']];
        if (sizeof($new_data['open_quotes'])>1) { $output['open_quotes']= array_merge($output['open_quotes'],['values'=>$new_data['open_quotes'],
             'attr'=>['type'=>'select'],'events'=>['onChange'=>"jq(this).combogrid('destroy'); bizWindowClose('winStatus'); journalEdit($jQuote, newVal);"]]); }
        $output['open_orders'] = ['order'=>30,'label'=>$this->lang['status_open_j10'],'break'=>true,'html'=>lang('none')."<br />",'attr'=>['type'=>'raw']];
        if (sizeof($new_data['open_orders'])>1) { $output['open_orders']= array_merge($output['open_orders'],['values'=>$new_data['open_orders'],
             'attr'=>['type'=>'select'],'events'=>['onChange'=>"jq(this).combogrid('destroy'); bizWindowClose('winStatus'); journalEdit($jOrder, newVal);"]]); }
        $output['unpaid_inv'] = ['order'=>40,'label'=>$this->lang['status_open_j12'],'break'=>true,'html'=>lang('none')."<br />",'attr'=>['type'=>'raw']];
        if (sizeof($new_data['unpaid_inv']) >1) { $output['unpaid_inv'] = array_merge($output['unpaid_inv'], ['values'=>$new_data['unpaid_inv'],
            'attr'=>['type'=>'select'], 'events'=>['onChange'=>"jq(this).combogrid('destroy'); bizWindowClose('winStatus'); journalEdit($jSale, newVal);"]]); }
        $output['unpaid_crd'] = ['order'=>50,'label'=>$this->lang['status_open_j13'],'html'=>lang('none')."<br />",'attr'=>['type'=>'raw']];
        if (sizeof($new_data['unpaid_crd']) >1) { $output['unpaid_crd'] = array_merge($output['unpaid_crd'], ['values'=>$new_data['unpaid_crd'],
            'attr'=>['type'=>'select'], 'events'=>['onChange'=>"jq(this).combogrid('destroy'); bizWindowClose('winStatus'); journalEdit($jRtn, newVal);"]]); }
        return $output;
    }

    private function getStatusBalance($new_data)
    {
        return [
            'term0' => ['order'=>10,'label'=>'0-30', 'break'=>true,'attr'=>['type'=>'currency','readonly'=>'readonly','value'=>$new_data['balance_0']]],
            'term30'=> ['order'=>20,'label'=>'31-60','break'=>true,'attr'=>['type'=>'currency','readonly'=>'readonly','value'=>$new_data['balance_30']]],
            'term60'=> ['order'=>30,'label'=>'61-90','break'=>true,'attr'=>['type'=>'currency','readonly'=>'readonly','value'=>$new_data['balance_60']]],
            'term90'=> ['order'=>40,'label'=>$this->lang['over_90'],'break'=>true,'attr'=>['type'=>'currency','readonly'=>'readonly','value'=>$new_data['balance_90']]],
            'total' => ['order'=>50,'label'=>lang('total'),'attr'=>['type'=>'currency','readonly'=>'readonly','value'=>$new_data['total']]],
        ];
    }

    /**
     * This method formats a db search field to the proper mysql syntax.
     * @param string $index - Database field name to search
     * @param array $values - full db path to field, i.e. BIZUNO_DB_PREFIX.journal_main.field_name
     * @param string $type -  type of field to properly create SQL syntax
     * @param string $defSel - Default selection value if not set through $_POST
     * @param string $defMin - Default minimum/equal value if not set through $_POST
     * @param string $defMax - Default maximum value if not set through $_POST
     * @return SQL formatted syntax to be used in search query
     */
    private function searchCriteriaSQL($index, $values=[], $type='text', $defSel='all', $defMin='', $defMax='')
    {
        if (!is_array($values)) { $values = [$values]; }
        $sel = clean($index, ['format'=>'cmd', 'default'=>$defSel], 'post');
        $min = clean($index.'Min', ['format'=>$type, 'default'=>$defMin], 'post');
        $max = clean($index.'Max', ['format'=>$type, 'default'=>$defMax], 'post');
        $sql = [];
        switch ($sel) {
            default:
            case 'all':  break;
            case 'band': foreach ($values as $field) { $sql[] = "($field >= '$min'" . ($max ? " AND $field <= '$max')" : ")"); } break;
            case 'eq':   foreach ($values as $field) { $sql[] = "$field  = '$min'"; }     break;
            case 'not':  foreach ($values as $field) { $sql[] = "$field <> '$min'"; }     break;
            case 'inc':  foreach ($values as $field) { $sql[] = "$field LIKE '%$min%'"; } break;
        }
        msgDebug("\nFinished with index $index and defSel = $defSel and defMin = $defMin and defMax = $defMax and returning sql = ".implode(' AND ', $sql));
        $this->defaults[$index] = "$sel:$min:$max";
        return sizeof($sql) > 1 ? "(".implode(' OR ', $sql).")" : array_shift($sql);
    }

    /**
     * Datagrid manager structure for all PhreeBooks journals
     * @param string $name - datagrid HTML id
     * @param integer $security - Security level to set tool bar and access permissions
     * @return array -Data structure ready to render
     */
    public function dgPhreeBooks($name, $security=0)
    {
        $this->managerSettings();
        msgDebug("\nPhreeBooks Settings = ".print_r($this->defaults, true));
        $dateRange  = dbSqlDates($this->defaults['period']);
        $sqlPeriod  = $dateRange['sql']; // BIZUNO_DB_PREFIX."journal_main.period={$this->defaults['period']}";
        $formID     = explode(':', getDefaultFormID($this->journalID));
        $formGroup  = $this->journalID==18 ? 'cust:j19' : $formID[0].':jjrnlTBD'; // special case for customer receipts, make like POS
        $jHidden    = true;
        $jID_values = [];
        $valid_jIDs = [$this->journalID];
        $jrnl_sql   = BIZUNO_DB_PREFIX."journal_main.journal_id={$this->journalID}";
        $jID_statuses = [['id'=>'a','text'=>lang('all')],['id'=>'0','text'=>lang('open')],['id'=>'1','text'=>lang('closed')]];
        $jrnl_status= '';
        switch ($this->journalID) {
            case  0: $valid_jIDs = []; break; // search
            case  3:
            case  4:
            case  6:
            case  7:
                $jHidden = false;
                switch ($this->defaults['jID']) {
                    default:$valid_jIDs = [3,4,6,7]; break;
                    case 3: $valid_jIDs = [3]; break;
                    case 4: $valid_jIDs = [4]; break;
                    case 6: $valid_jIDs = [6]; break;
                    case 7: $valid_jIDs = [7]; break;
                }

                $jID_values = [['id'=>'a','text'=>lang('all')]];
                if (getUserCache('security', 'j3_mgr')) { $jID_values[] = ['id'=>'3','text'=>lang('journal_main_journal_id_3')]; }
                if (getUserCache('security', 'j4_mgr')) { $jID_values[] = ['id'=>'4','text'=>lang('journal_main_journal_id_4')]; }
                if (getUserCache('security', 'j6_mgr')) { $jID_values[] = ['id'=>'6','text'=>lang('journal_main_journal_id_6')]; }
                if (getUserCache('security', 'j7_mgr')) { $jID_values[] = ['id'=>'7','text'=>lang('journal_main_journal_id_7')]; }
                $jID_statuses[] = ['id'=>'w','text'=>lang('confirmed_waiting')];
                switch ($this->defaults['status']) {
                    case '0': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='0'";  break;
                    case '1': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='1'";  break;
                    case 'w': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.waiting='1'"; break;
                }
                break;
            case  9:
            case 10:
            case 12:
            case 13:
                $jHidden = false;
                switch ($this->defaults['jID']) {
                    default: $valid_jIDs = [9,10,12,13]; break;
                    case  9: $valid_jIDs = [9];  break;
                    case 10: $valid_jIDs = [10]; break;
                    case 12: $valid_jIDs = [12]; break;
                    case 13: $valid_jIDs = [13]; break;
                }
                $jID_values = [['id'=>'a','text'=>lang('all')]];
                if (getUserCache('security', 'j9_mgr'))  { $jID_values[] = ['id'=> '9','text'=>lang('journal_main_journal_id_9')];  }
                if (getUserCache('security', 'j10_mgr')) { $jID_values[] = ['id'=>'10','text'=>lang('journal_main_journal_id_10')]; }
                if (getUserCache('security', 'j12_mgr')) { $jID_values[] = ['id'=>'12','text'=>lang('journal_main_journal_id_12')]; }
                if (getUserCache('security', 'j13_mgr')) { $jID_values[] = ['id'=>'13','text'=>lang('journal_main_journal_id_13')]; }
                $jID_statuses[] = ['id'=>'w','text'=>lang('confirmed_unshipped')];
                switch ($this->defaults['status']) {
                    case '0': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='0'";  break;
                    case '1': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='1'";  break;
                    case 'w': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.waiting='1'"; break;
                    default:  break;
                }
                break;
            default:
        }
        $jrnl_sql = $this->setAllowedJournals($valid_jIDs);
        $data  = ['id'=>$name, 'rows'=>$this->defaults['rows'], 'page'=>$this->defaults['page'],
            'attr'=> ['toolbar'=>"#{$name}Toolbar", 'idField'=>'id', 'url'=>BIZUNO_AJAX."&p=phreebooks/main/managerRows&jID=$this->journalID&type=$this->type",
                'xtraField'=> [['key'=>'jrnlTBD','value'=>"journal_id"],['key'=>'cIDTBD','value'=>"contact_id_b"]]],
            'events' => ['onDblClickRow'=>"function(idx, data){ journalEdit(data.journal_id, data.id); }"],
            'source' => [
                'tables' => ['journal_main'=>['table'=>BIZUNO_DB_PREFIX.'journal_main']],
                'search' => [
                    BIZUNO_DB_PREFIX.'journal_main.description',
                    BIZUNO_DB_PREFIX.'journal_main.primary_name_b',
                    BIZUNO_DB_PREFIX.'journal_main.primary_name_s',
                    BIZUNO_DB_PREFIX.'journal_main.postal_code_b',
                    BIZUNO_DB_PREFIX.'journal_main.postal_code_s',
                    BIZUNO_DB_PREFIX.'journal_main.invoice_num',
                    BIZUNO_DB_PREFIX.'journal_main.purch_order_id',
                    BIZUNO_DB_PREFIX.'journal_main.total_amount'],
                'actions' => [
                    'newJournal'=>['order'=>10,'icon'=>'new',    'events'=>['onClick'=>"journalEdit($this->journalID, 0);"]],
                    'clrSearch' =>['order'=>50,'icon'=>'refresh','events'=>['onClick'=>"hrefClick('phreebooks/main/manager&mgr=1&jID=$this->journalID');"]]],
                'filters' => [
                    'period' => ['order'=>10,'break'=>true,'options'=>['width'=>300],'sql'=>$sqlPeriod,
                        'label'=>lang('period'), 'values'=>dbPeriodDropDown(true, true),'attr'=>['type'=>'select','value'=>$this->defaults['period']]],
                    "jID"    => ['order'=>20,'break'=>true,'sql'=>$jrnl_sql, 'hidden'=>$jHidden,
                        'label'=>lang('journal_main_journal_id'), 'values'=>$jID_values,'attr'=>['type'=>'select','value'=>$this->defaults['jID']]],
                    "status" => ['order'=>30,'break'=>true,'sql'=>$jrnl_status,'hidden'=>$jHidden,
                        'label'=>lang('status'), 'values'=>$jID_statuses,'attr'=>['type'=>'select','value'=>$this->defaults['status']]],
                    'search' => ['order'=>90,'attr'=>['value'=>$this->defaults['search']]]],
                'sort' => ['s0'=> ['order'=>10,'field'=>("{$this->defaults['sort']} {$this->defaults['order']}, ".BIZUNO_DB_PREFIX."journal_main.id DESC")]]],
            'columns' => [
                'id'           => ['order'=>1, 'field'=>'DISTINCT '.BIZUNO_DB_PREFIX.'journal_main.id','attr'=>['hidden'=>true]],
                'contact_id_b' => ['order'=>1, 'field'=>BIZUNO_DB_PREFIX.'journal_main.contact_id_b',  'attr'=>['hidden'=>true]],
                'journal_id'   => ['order'=>1, 'field'=>BIZUNO_DB_PREFIX.'journal_main.journal_id',    'attr'=>['hidden'=>true]],
                'currency'     => ['order'=>1, 'field'=>BIZUNO_DB_PREFIX.'journal_main.currency',      'attr'=>['hidden'=>true]],
                'currency_rate'=> ['order'=>1, 'field'=>BIZUNO_DB_PREFIX.'journal_main.currency_rate', 'attr'=>['hidden'=>true]],
                'attach'       => ['order'=>1, 'field'=>BIZUNO_DB_PREFIX.'journal_main.attach',        'attr'=>['hidden'=>true]],//'alias'=>'id', 'format'=>'attch:'.getModuleCache('phreebooks', 'properties', 'attachPath')."rID_idTBD_"],
                'action' => ['order'=>0, 'label'=>lang('action'), // leave order BEFORE id so it stays first on view, id need to go first in sql as DISTINCT
                    'events' => ['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
                    'actions'=> [
                        'print'      => ['order'=>10,'icon'=>'print',
                            'events' => ['onClick'=>"var jID=jq('#journal_id').val(); winOpen('phreeformOpen', 'phreeform/render/open&group={$formGroup}&date=a&xfld=journal_main.id&xcr=equal&xmin=idTBD');"]],
                        'edit'       => ['order'=>20,'icon'=>'edit',   'label'=>lang('edit'),
                            'events' => ['onClick' => "journalEdit(jrnlTBD, idTBD);"]],
                        'toggle'     => ['order'=>40,'icon'=>'toggle','label'=>lang('toggle_status'),'hidden'=> $security>2?false:true,
                            'events' => ['onClick' => "jsonAction('phreebooks/main/toggleWaiting&jID=jrnlTBD', idTBD);"],
                            'display'=> "row.journal_id=='4' || row.journal_id=='10' || row.journal_id=='12'"],
                        'dates'      => ['order'=>50,'icon'=>'date','label'=>$this->lang['expected_delivery_dates'],
                            'events' => ['onClick' => "windowEdit('phreebooks/main/deliveryDates&rID=idTBD', 'winDelDates', '".$this->lang['expected_delivery_dates']."', 500, 400);"],
                            'display'=> "row.journal_id=='4' || row.journal_id=='10'"],
                        'invoice'    => ['order'=>70,'icon'=>'invoice',   'label'=>$this->lang['set_invoice_num'],
                            'events' => ['onClick' => "var invNum=prompt('".$this->lang['enter_invoice_num']."'); if (invNum) { jsonAction('phreebooks/main/setInvoiceNum&jID=6', idTBD, invNum); }"],
                            'display'=> "row.waiting=='1' && row.journal_id=='6'"],
                        'reference'  => ['order'=>70,'icon'=>'invoice',   'label'=>$this->lang['set_ref_num'],
                            'events' => ['onClick' => "var invNum=prompt('".$this->lang['enter_ref_num']."'); jsonAction('phreebooks/main/setReferenceNum&jID=18', idTBD, invNum);"],
                            'display'=> "row.journal_id=='18' || row.journal_id=='20'"],
                        'purchase'   => ['order'=>70,'icon'=>'purchase','label'=>$this->lang['fill_purchase'],
                            'events' => ['onClick' => "journalEdit(6, 0, cIDTBD, 'inv', '', idTBD);"],
                            'display'=> "row.closed=='0' && (row.journal_id=='3' || row.journal_id=='4')"],
                        'sale'       => ['order'=>70,'icon'=>'sales',   'label'=>$this->lang['fill_sale'],
                            'events' => ['onClick' => "journalEdit(12, 0, cIDTBD, 'inv', '', idTBD);"],
                            'display'=> "row.closed=='0' && (row.journal_id=='9' || row.journal_id=='10')"],
                        'vcred'      => ['order'=>80,'icon'=>'credit',   'label'=>$this->lang['create_credit'],
                            'events' => ['onClick' => "setCrJournal(jrnlTBD, cIDTBD, idTBD);"],
                            'display'=> "row.closed=='0' && (row.journal_id=='6' || row.journal_id=='12')"],
                        'payment'    => ['order'=>80,'icon'=>'payment','label'=>lang('payment'),
                            'events' => ['onClick' => "setPmtJournal(jrnlTBD, cIDTBD, idTBD);"],
                            'display'=> "row.closed=='0' && (row.journal_id=='6' || row.journal_id=='7' || row.journal_id=='12' || row.journal_id=='13')"],
                        'attach'     => ['order'=>85,'icon'=>'attachment','display'=>"row.attach=='1'"], // info only
                        'trash'      => ['order'=>90,'icon'=>'trash','label'=>lang('delete'),'hidden'=>$security==4?false:true,
                            'display'=>"(row.journal_id!='12' && row.journal_id!='6') || (row.journal_id=='12' && (row.closed=='0' || row.total_amount==0)) || (row.journal_id=='6' && (row.closed=='0' || row.total_amount==0))",
                            'events' => ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreebooks/main/delete&jID={$this->journalID}', idTBD);"]]]],
                'post_date' => ['order'=>10, 'field'=>BIZUNO_DB_PREFIX.'journal_main.post_date', 'format'=>'date',
                    'label' => pullTableLabel('journal_main', 'post_date'),'attr'=>['sortable'=>true, 'resizable'=>true]],
                'invoice_num' => ['order'=>20, 'field'=>BIZUNO_DB_PREFIX.'journal_main.invoice_num',
                    'label' => pullTableLabel('journal_main', 'invoice_num', $this->journalID),'attr'=>['sortable'=>true, 'resizable'=>true]],
                'so_po_ref_id' => ['order'=>25, 'field'=>BIZUNO_DB_PREFIX.'journal_main.so_po_ref_id','format'=>'storeID',
                    'label' => pullTableLabel('journal_main', 'so_po_ref_id', $this->journalID),
                    'attr'  => ['width'=>120, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [15]) ? false : true]],
                'purch_order_id' => ['order'=>30, 'field'=>BIZUNO_DB_PREFIX.'journal_main.purch_order_id',
                    'label' => pullTableLabel('journal_main', 'purch_order_id', $this->journalID),
                    'attr'  => ['width'=>120, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [2,14,15,16]) ? true : false]],
                'description' => ['order'=>40, 'field'=>BIZUNO_DB_PREFIX.'journal_main.description',
                    'label' => pullTableLabel('journal_main', 'description', $this->journalID),
                    'attr'  => ['width'=>240, 'sortable'=>true, 'resizable'=>true, 'hidden'=> !in_array($this->journalID, [0,2,14,15,16]) ? true : false]],
                'primary_name_b' => ['order'=>50, 'field'=>BIZUNO_DB_PREFIX.'journal_main.primary_name_b',
                    'label' => pullTableLabel('address_book', 'primary_name', $this->type),
                    'attr'  => ['width'=>240, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [0,2,14,15,16]) || $this->type=='e' ? true : false]],
                'email_b' => ['order'=>60, 'field'=>BIZUNO_DB_PREFIX.'journal_main.email_b',
                    'label' => pullTableLabel('contacts', 'email', $this->type),
                    'attr'  => ['width'=>220, 'sortable'=>true, 'resizable'=>true, 'hidden'=>true]],
                'total_amount'=> ['order'=>70, 'field' => BIZUNO_DB_PREFIX.'journal_main.total_amount',
                    'label' => pullTableLabel('journal_main', 'total_amount'),
                    'events' => ['formatter' => "function(value,row,index) { return formatCurrency(value, true, row.currency, row.currency_rate); }"],
                    'attr'  => ['width'=>80, 'align'=>'right', 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [14,15,16]) ? true : false]],
                'closed'    => ['order'=>90, 'field'=>BIZUNO_DB_PREFIX.'journal_main.closed',
                    'label' => lang('status'),
                    'attr'  => ['width'=>60, 'align'=>'center', 'resizable'=>true, 'hidden'=>in_array($this->journalID,[2,14,15,16])?true:false]]]];
        switch ($this->journalID) {
            case 0: // search journal
                $data['events']['onDblClickRow'] = "function(rowIndex, rowData){ tabOpen('_blank', 'phreebooks/main/manager&rID='+rowData.id); }";
                $data['source']['tables']['journal_item'] = ['table'=>BIZUNO_DB_PREFIX.'journal_item', 'join'=>'JOIN', 'links'=>BIZUNO_DB_PREFIX."journal_main.id=".BIZUNO_DB_PREFIX."journal_item.ref_id"];
                $data['source']['search'][] = BIZUNO_DB_PREFIX.'journal_main.id';
                $data['source']['search'][] = BIZUNO_DB_PREFIX.'journal_item.sku';
                unset($data['source']['actions']['newJournal']);
                unset($data['columns']['id']['attr']['hidden']);
                $data['columns']['action']['actions']['edit']['events']['onClick'] = "tabOpen('_blank', 'phreebooks/main/manager&rID=idTBD');";
                unset($data['columns']['action']['actions']['print']);
                $data['columns']['journal_id'] = ['order'=>15, 'field'=>BIZUNO_DB_PREFIX.'journal_main.journal_id', 'format'=>'j_desc',
                    'label' => pullTableLabel('journal_main', 'journal_id'), 'attr'=>  ['width'=>120, 'resizable'=>true]];

                $journalID = clean('journalID', 'integer', 'post');
                $jVals = $this->selJournals(); // needs to be here for generating $this->blocked_journals
                if     ($journalID)              { $sql = BIZUNO_DB_PREFIX."journal_main.journal_id=$journalID"; }
                elseif ($this->blocked_journals) { $sql = BIZUNO_DB_PREFIX."journal_main.journal_id NOT IN ($this->blocked_journals)"; }
                else                             { $sql = ''; }
                $data['source']['filters']['journalID']= ['order'=>55,'break'=>true,'sql'=>$sql, 'label'=>pullTableLabel('journal_main', 'journal_id'),'values'=>$jVals,'attr'=>['type'=>'select','value'=>$journalID]];

//                $data['source']['filters']['hdcol1'] = ['order'=>56,              'classes'=>['row-default'],'attr'=>['type'=>'label','value'=>lang('fieldname')]];
//                $data['source']['filters']['hdcol2'] = ['order'=>57,              'classes'=>['row-default'],'attr'=>['type'=>'label','value'=>lang('operator')]];
//                $data['source']['filters']['hdMin']  = ['order'=>58,              'classes'=>['row-default'],'attr'=>['type'=>'label','value'=>lang('minimum')]];
//                $data['source']['filters']['hdMax']  = ['order'=>59,'break'=>true,'classes'=>['row-default'],'attr'=>['type'=>'label','value'=>lang('maximum')]];

                $sql1 = $this->searchCriteriaSQL('postDate', BIZUNO_DB_PREFIX.'journal_main.post_date', 'date', 'band', getModuleCache('phreebooks', 'fy', 'period_start'), getModuleCache('phreebooks', 'fy', 'period_end'));
                $temp1 = explode(':', $this->defaults['postDate']);
                if (!$temp1[0]) { $temp1[0] = 'band'; }
                $data['source']['filters']['postDate']    = ['order'=>60,'sql'=>$sql1,'label'=>pullTableLabel('journal_main', 'post_date'),'values'=>selChoices(),'attr'=>['type'=>'select','value'=>$temp1[0]]];
                $data['source']['filters']['postDateMin'] = ['order'=>61,              'attr'=>['type'=>'date', 'value'=>$temp1[1]]];
                $data['source']['filters']['postDateMax'] = ['order'=>62,'break'=>true,'attr'=>['type'=>'date', 'value'=>$temp1[2]]];

                $sql2 = $this->searchCriteriaSQL('refID', [BIZUNO_DB_PREFIX.'journal_main.invoice_num', BIZUNO_DB_PREFIX.'journal_main.purch_order_id']);
                $temp2 = explode(':', $this->defaults['refID']);
                $data['source']['filters']['refID']    = ['order'=>63,'sql'=>$sql2,'label'=>pullTableLabel('journal_main', 'invoice_num'),'values'=>selChoices(),'attr'=>['type'=>'select','value'=>$temp2[0]]];
                $data['source']['filters']['refIDMin'] = ['order'=>64,              'attr'=>['value'=>$temp2[1]]];
                $data['source']['filters']['refIDMax'] = ['order'=>65,'break'=>true,'attr'=>['value'=>$temp2[2]]];

                $sql3 = $this->searchCriteriaSQL('contactID', BIZUNO_DB_PREFIX.'journal_main.primary_name_b');
                $temp3 = explode(':', $this->defaults['contactID']);
                $data['source']['filters']['contactID']    = ['order'=>66,'sql'=>$sql3,'label'=>pullTableLabel('address_book', 'primary_name'),'values'=>selChoices(),'attr'=>['type'=>'select','value'=>$temp3[0]]];
                $data['source']['filters']['contactIDMin'] = ['order'=>67,              'attr'=>['value'=>$temp3[1]]];
                $data['source']['filters']['contactIDMax'] = ['order'=>68,'break'=>true,'attr'=>['value'=>$temp3[2]]];

                $sql4 = $this->searchCriteriaSQL('sku', BIZUNO_DB_PREFIX.'journal_item.sku');
                $temp4 = explode(':', $this->defaults['sku']);
                $data['source']['filters']['sku']    = ['order'=>69,'sql'=>$sql4,'label'=>pullTableLabel('journal_item', 'sku'),'values'=>selChoices(),'attr'=>['type'=>'select','value'=>$temp4[0]]];
                $data['source']['filters']['skuMin'] = ['order'=>70,              'attr'=>['value'=>$temp4[1]]];
                $data['source']['filters']['skuMax'] = ['order'=>71,'break'=>true,'attr'=>['value'=>$temp4[2]]];

                $sql5 = $this->searchCriteriaSQL('amount',  [BIZUNO_DB_PREFIX.'journal_main.total_amount', BIZUNO_DB_PREFIX.'journal_item.debit_amount', BIZUNO_DB_PREFIX.'journal_item.credit_amount']);
                $temp5 = explode(':', $this->defaults['amount']);
                $data['source']['filters']['amount']    = ['order'=>72,'sql'=>$sql5,'label'=>lang('amount'),'values'=>selChoices(),'attr'=>['type'=>'select','value'=>$temp5[0]]];
                $data['source']['filters']['amountMin'] = ['order'=>73,              'attr'=>['value'=>$temp5[1]]];
                $data['source']['filters']['amountMax'] = ['order'=>74,'break'=>true,'attr'=>['value'=>$temp5[2]]];

                $sql6 = $this->searchCriteriaSQL('glAcct',  [BIZUNO_DB_PREFIX.'journal_main.gl_acct_id', BIZUNO_DB_PREFIX.'journal_item.gl_account']);
                $temp6 = explode(':', $this->defaults['glAcct']);
                $data['source']['filters']['glAcct']    = ['order'=>75,'sql'=>$sql6,'label'=>pullTableLabel('journal_main', 'gl_acct_id'),'values'=>selChoices(),'attr'=>['type'=>'select','value'=>$temp6[0]]];
                $data['source']['filters']['glAcctMin'] = ['order'=>76,              'attr'=>['value'=>$temp6[1]]];
                $data['source']['filters']['glAcctMax'] = ['order'=>77,'break'=>true,'attr'=>['value'=>$temp6[2]]];

                $sql7 = $this->searchCriteriaSQL('rID', BIZUNO_DB_PREFIX.'journal_main.id');
                $temp7 = explode(':', $this->defaults['rID']);
                $data['source']['filters']['rID']    = ['order'=>78,'sql'=>$sql7,'label'=>lang('users_admin_id'),'values'=>selChoices(),'attr'=>['type'=>'select','value'=>$temp7[0]]];
                $data['source']['filters']['rIDMin'] = ['order'=>79,              'attr'=>['value'=>$temp7[1]]];
                $data['source']['filters']['rIDMax'] = ['order'=>80,'break'=>true,'attr'=>['value'=>$temp7[2]]];
                unset($data['source']['filters']['period']);
                break;
            case 2:
                unset($data['columns']['closed']);
                break;
            case  3:
            case  4:
            case  6:
            case  7:
                if (getUserCache('security', 'j20_mgr') < 2) { unset($data['columns']['action']['actions']['payment']); }
                $data['columns']['invoice_num']['events'] = ['styler'=>"function(value,row,index) {
                        if      (row.journal_id=='3') { return {style:'background-color:lightblue'}; }
                        else if (row.journal_id=='4') { return {style:'background-color:orange'}; }
                        else if (row.journal_id=='6') { return {style:'background-color:lightgreen'}; }
                        else if (row.journal_id=='7') { return {style:'background-color:pink'}; }
                    }"];
                $data['columns']['closed']['events']['formatter'] = " function(value,row,index){
                        if      (row.journal_id=='3') { return value=='1' ? '".jsLang('closed')."' : ''; }
                        else if (row.journal_id=='4') { return value=='1' ? '".jsLang('closed')."' : ''; }
                        else if (row.journal_id=='6') { return value=='1' ? '".jsLang('paid')."' : ''; }
                        else if (row.journal_id=='7') { return value=='1' ? '".jsLang('paid')."' : ''; }
                    }";
                $data['columns']['closed']['events']['styler'] = "function(value,row,index) {
                        if      (row.journal_id=='4' && row.waiting==1) { return {style:'background-color:yellowgreen'}; }
                        else if (row.journal_id=='6' && row.waiting==1) { return {class:'journal-waiting'}; }
                    }";
                $data['footnotes'] = ['status'=>lang('journal_main_journal_id').':
                        <span style="background-color:lightgreen">'.lang('journal_main_journal_id_6').'</span>
                        <span style="background-color:orange">'    .lang('journal_main_journal_id_4').'</span>
                        <span style="background-color:lightblue">' .lang('journal_main_journal_id_3').'</span>
                        <span style="background-color:pink">'      .lang('journal_main_journal_id_7').'</span>',
                    'jType'=>'<br />'.lang('status').':
                        <span style="background-color:yellowgreen">'.lang('confirmed').'</span>
                        <span class="journal-waiting">'.lang('journal_main_waiting').'</span>'];
                break;
            case  9:
            case 10:
            case 12:
            case 13:
                if (getUserCache('security', 'j18_mgr') < 2) { unset($data['columns']['action']['actions']['payment']); }
                $data['columns']['invoice_num']['events'] = ['styler'=>"function(value,row,index) {
                        if      (row.journal_id== '9') { return {style:'background-color:lightblue'}; }
                        else if (row.journal_id=='10') { return {style:'background-color:orange'}; }
                        else if (row.journal_id=='12') { return {style:'background-color:lightgreen'}; }
                        else if (row.journal_id=='13') { return {style:'background-color:pink'}; }
                    }"];
                $data['columns']['closed']['events']['formatter'] = "function(value,row,index){
                        if      (row.journal_id=='9')  { return value=='1' ? '".jsLang('closed')."' : ''; }
                        else if (row.journal_id=='10') { return value=='1' ? '".jsLang('closed')."' : ''; }
                        else if (row.journal_id=='12') { return value=='1' ? '".jsLang('paid')."' : ''; }
                        else if (row.journal_id=='13') { return value=='1' ? '".jsLang('paid')."' : ''; }
                    }";
                $data['columns']['closed']['events']['styler'] = "function(value,row,index) {
                    if      (row.journal_id=='10' && row.waiting==1) { return {style:'background-color:yellowgreen'}; }
                    else if (row.journal_id=='12' && row.waiting==1) { return {class:'journal-waiting'}; }
                    }";
                $data['footnotes'] = ['status'=>lang('journal_main_journal_id').':
                        <span style="background-color:lightgreen">'.lang('journal_main_journal_id_12').'</span>
                        <span style="background-color:orange">'    .lang('journal_main_journal_id_10').'</span>
                        <span style="background-color:lightblue">' .lang('journal_main_journal_id_9') .'</span>
                        <span style="background-color:pink">'      .lang('journal_main_journal_id_13').'</span>',
                    'jType'=>'<br />'.lang('status').':
                        <span style="background-color:yellowgreen">'.lang('confirmed').'</span>
                        <span class="journal-waiting">'.lang('unshipped').'</span>'];
                break;
            case 14:
                unset($data['columns']['closed']);
                break;
            case 17:
            case 18:
            case 20:
            case 22:
                $data['columns']['closed']['events']['formatter'] = "function(value) { return value=='1'?'".jsLang('reconciled')."':''; }";
                break;
        }
        if (getUserCache('profile', 'restrict_user', false, 0)) { // see if user restrictions are in place
            $uID = getUserCache('profile', 'contact_id', false, 0);
            $data['source']['filters']['restrict_user'] = ['order'=>99,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."journal_main.rep_id='$uID'"];
        }
        return $data;
    }

    /**
     * This method builds the appropriate Save menu choices depending on the journal and state of the order
     * @param integer $security - users security to set visibility
     * @return array - structure for save menu
     */
    private function renderMenuSave($security=0)
    {
        msgDebug("\nbuilding renderMenuSave, security=$security, id=$this->rID");
        $type = false;
        $data = [];
        if ($security < 2) { return $data; } // Read-only, no operations allowed
        switch ($this->journalID) {
            case 2:
                $data = [
                    'optSaveAs' => ['order'=>40,'label'=>lang('save_as'),'child'=>  [
                        'saveAsNew' => ['order'=>10,'label'=>lang('new'),'security'=>3,'events'=>['onClick'=>"saveAction('saveAs','2');"]]]]];
                break;
            case  3:
            case  4:
            case  6: $type = 'v';
            case  9:
            case 10:
            case 12: if (!$type) { $type = 'c'; }
                $data = [
                    'optPrint'   => ['order'=>10,'icon'=>'print','label'=>lang('save_print'),'security'=>3,
                        'hidden' => !in_array($this->journalID, [6]) && $security>1?false:true,
                        'events' => ['onClick'=>"jq('#xChild').val('print'); jq('#frmJournal').submit();"]],
                    'optPayment' => ['order'=>20,'icon'=>'payment','label'=>lang('save_payment'),'security'=>3,
                        'hidden' =>  in_array($this->journalID, [6,12]) && $security>1?false:true,
                        'events' => ['onClick'=>"jq('#xAction').val('payment'); jq('#frmJournal').submit();"]],
                    'optFill'    => ['order'=>30,'icon'=>'fill','label'=>lang('save_fill'),'security'=>2,
                        'hidden' =>  in_array($this->journalID, [4,10]) && $security>1?false:true,
                        'events' => ['onClick'=>"jq('#xAction').val('invoice'); jq('#frmJournal').submit();"]],
                    'optSaveAs'  => ['order'=>40,'label'=>lang('save_as'),'child'=>  [
                        'saveAsQuote'=> ['order'=>10,'icon'=>'quote','label'=>lang('journal_main_journal_id', $type=='v'?3: 9),'security'=>3,
//                            'disabled'=> !in_array($this->journalID, array(3,9)) ? false : true,
                            'events'  => ['onClick'=>"saveAction('saveAs','".($type=='v'?3: 9)."');"]],
                        'saveAsSO'   => ['order'=>20,'icon'=>'order','label'=>lang('journal_main_journal_id', $type=='v'?4:10),'security'=>3,
//                            'disabled'=> !in_array($this->journalID, array(4,10)) ? false : true,
                            'events'  => ['onClick'=>"saveAction('saveAs','".($type=='v'?4:10)."');"]],
                        'saveAsInv'  => ['order'=>30,'icon'=>'sales','label'=>lang('journal_main_journal_id', $type=='v'?6:12),'security'=>3,
//                            'disabled'=> !in_array($this->journalID, array(6,12)) ? false : true,
                            'events'  => ['onClick'=>"saveAction('saveAs','".($type=='v'?6:12)."');"]]]],
                    'optMoveTo'  => ['order'=>50,'label'=>lang('move_to'),'disabled'=>$this->rID?false:true,'child'=>  [
                        'MoveToQuote'=> ['order'=>10,'icon'=>'quote','label'=>lang('journal_main_journal_id', $type=='v'?3: 9),
                            'disabled'=> !in_array($this->journalID, [3,9]) ? false : true,'security'=>3,
                            'events'  => ['onClick'=>"saveAction('moveTo','".($type=='v'?3: 9)."');"]],
                        'MoveToSO'   => ['order'=>20,'icon'=>'order','label'=>lang('journal_main_journal_id', $type=='v'?4:10),
                            'disabled'=> !in_array($this->journalID, [4,10]) ? false : true,'security'=>3,
                            'events'  => ['onClick'=>"saveAction('moveTo','".($type=='v'?4:10)."');"]],
                        'MoveToInv'  => ['order'=>30,'icon'=>$type=='v'?'purchase':'sales','label'=>lang('journal_main_journal_id', $type=='v'?6:12),
                            'disabled'=> !in_array($this->journalID, [6,12]) ? false : true,'security'=>3,
                            'events'  => ['onClick'=>"saveAction('moveTo','".($type=='v'?6:12)."');"]]]]];
                break;
            case 20:
            case 22:
                $data = ['optPrint'=>['order'=>10,'label'=>lang('save_print'),'icon'=>'print','security'=>3,
                    'hidden'=>$security>1?false:true,'events'=>['onClick'=>"jq('#xChild').val('print'); jq('#frmJournal').submit();"]]];
            break;
            default:
        }
        return $data;
    }

    /**
     * Retrieves list of attachments for a given
     * @param string $srcField - path to PhreeBooks uploads folder
     * @param integer $rID - record id used to create filename to search for attachments
     * @param integer $refID - record id of reference journal entry (SO or PO)
     * @return null, saves files on success, message if fails
     */
    private function getAttachments($srcField, $rID, $refID=0)
    {
        $io = new \bizuno\io();
        if ($io->uploadSave($srcField, getModuleCache('phreebooks', 'properties', 'attachPath')."rID_{$rID}_")) {
            dbWrite(BIZUNO_DB_PREFIX.'journal_main', ['attach'=>'1'], 'update', "id=$rID");
        }
        if ($refID) { // if so_po_ref_id, check for attachment to copy over
            $files = glob(getModuleCache('phreebooks', 'properties', 'attachPath')."rID_{$refID}_*");
            if ($files === false || sizeof($files) == 0) { return; }
            foreach ($files as $oldFile) {
                $newFile = str_replace("rID_{$refID}_", "rID_{$rID}_", $oldFile);
                copy($oldFile, $newFile);
            }
            dbWrite(BIZUNO_DB_PREFIX.'journal_main', ['attach'=>'1'], 'update', "id=$rID");
        }
    }

    /**
     * Loads the appropriate journal class to operate on
     * @param integer $jID - journal ID
     * @return object - journal object
     */
    private function getJournal($jID)
    {
        if (empty($jID)) { $jID = 12; }
        $jName = "j".substr('0'.$jID, -2);
        bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journals/$jName.php", $jName);
        $fqcn = "\\bizuno\\$jName";
        return new $fqcn();
    }

    /**
     * Creates the structure for the delivery dates pop up for user entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function deliveryDates(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', 'j'.$this->journalID.'_mgr', 3)) { return; }
        $rID = clean('rID', 'integer', 'get');
        $result = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$rID");
        $items = [];
        foreach ($result as $row) {
            if ($row['gl_type'] != 'itm') { continue; }
            $items[] = ['id'=>$row['id'],'qty'=>$row['qty'],'sku'=>$row['sku'],'description'=>$row['description'],'date_1'=>$row['date_1']];
        }
        $data = ['type'=>'divHTML','divs'=>['divRate'=>['order'=>50,'type'=>'html','html'=>$this->getViewDelDates($items)]]];
        $layout = array_replace_recursive($layout, $data);
    }

    private function getViewDelDates($items)
    {
        $delSave = ['icon'=>'save','events'=>['onClick'=>"divSubmit('phreebooks/main/deliveryDatesSave&jID=$this->journalID', 'winDelDates');"]];
        $output = '<table style="border-collapse:collapse;width:100%;"><thead><tr class="panel-header"><th>'.lang('qty')."</th><th>".lang('sku')."</th><th>".lang('description')."</th><th>".lang('date')."</th></tr></thead><tbody>";
        foreach ($items as $row) {
            $output .= "<tr><td>".$row['qty']."</td><td>".$row['sku']."</td><td>".$row['description']."</td><td>".html5("rID_{$row['id']}", ['classes'=> ['easyui-datebox'], 'attr'=> ['value'=>viewFormat($row['date_1'], 'date')]])."</td></tr>";
        }
        $output .= '</tbody><tfooter><tr><td colspan="4" style="text-align:right">'.html5('delSave', $delSave)."</td></tr></tfooter></table>\n";
        return $output;
    }

    /**
     * Saves the user entered delivery dates (typically for PO's and SO's
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function deliveryDatesSave(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 3)) { return; }
        $request = $_POST;
        foreach ($request as $key => $value) {
            if (strpos($key, "rID_") === 0) {
                $rID = str_replace("rID_", "", $key);
                dbWrite(BIZUNO_DB_PREFIX."journal_item", ['date_1'=>clean($value, 'date')], 'update', "id=$rID");
            }
        }
        msgAdd(lang('msg_database_write'), 'success');
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"bizWindowClose('winDelDates');"]]);
    }

    /**
     * Determines the balance owed for a journal order line item
     * @param type $layout
     * @return type
     */
    public function journalBalance(&$layout=[])
    {
        $rID       = clean('rID',      'integer', 'get');
        $post_date = clean('postDate', ['format'=>'date','default'=>date('Y-m-d')], 'get');
        $gl_account= clean('glAccount',['format'=>'text','default'=>getModuleCache('phreebooks', 'settings', 'vendors', 'gl_cash')], 'get');
        $row       = dbGetRow(BIZUNO_DB_PREFIX."journal_periods", "start_date<='$post_date' AND end_date>='$post_date'");
        if (!$row) { return msgAdd(sprintf(lang('err_gl_post_date_invalid'), $post_date)); }
        $balance   = dbGetValue(BIZUNO_DB_PREFIX."journal_history", 'beginning_balance', "period={$row['period']} AND gl_account='$gl_account'");
        $balance  += dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'SUM(debit_amount - credit_amount) as balance', "gl_account='$gl_account' AND post_date>='{$row['start_date']}' AND post_date<='$post_date'", false);
        // add back the record amount if editing
        if ($rID) { $balance += dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'credit_amount', "ref_id=$rID AND gl_type='ttl'"); }
        msgDebug("returning balance = $balance");
        $layout = array_replace_recursive($layout, ['content'=>['balance'=>$balance]]);
    }

    /**
     * Builds the list of totals from the session cache for a particular journal
     * @param integer $jID - journal ID used as index
     * @return array - list of totals in order of priority
     */
    public function loadTotals($jID)
    {
        $totals = [];
        $methods = sortOrder(getModuleCache('phreebooks', 'totals')); // re-sort by users order preferences
        foreach ($methods as $methID => $settings) {
            if (!isset($settings['status']) || !$settings['status']) { continue; }
            if (!isset($settings['settings']['journals'])) { $settings['settings']['journals'] = []; }
            if (is_string($settings['settings']['journals'])) { $settings['settings']['journals'] = json_decode($settings['settings']['journals']); }
            if (in_array($jID, $settings['settings']['journals'])) { $totals[] = $methID; }
        }
        return $totals;
    }

    public function setInvoiceNum(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('bad_id')); }
        $invNum = clean('data', 'text', 'get');
        if (empty($invNum)) { return msgAdd($this->lang['msg_inv_waiting']); }
        dbWrite(BIZUNO_DB_PREFIX."journal_main", ['waiting'=>'0','invoice_num'=>$invNum], 'update', "id=$rID");
        $layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>"jq('#dgPhreeBooks').datagrid('reload');"]]);
    }

    public function setReferenceNum(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('bad_id')); }
        $refNum = clean('data', 'text', 'get');
        if (empty($refNum)) { return 'Bad reference number'; } // should never happen
        dbWrite(BIZUNO_DB_PREFIX."journal_main", ['invoice_num'=>$refNum], 'update', "id=$rID");
        $layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>"jq('#dgPhreeBooks').datagrid('reload');"]]);
    }

    /**
     * Sets the sql criteria to limit the list of journals to search in the db
     * @param array $jIDs - list of journal ID within scope
     * @return string - sql condition for allowed journals
     */
    private function setAllowedJournals($jIDs=[])
    {
        if (sizeof($jIDs) == 0) { return ''; }
        $valids = [];
        while ($i = array_shift($jIDs)) {
            if (getUserCache('security', "j{$i}_mgr", false, 0)) { $valids[] = $i; }
        }
        if (sizeof($valids) == 0) { return BIZUNO_DB_PREFIX."journal_main.journal_id=-1"; } // return with no results
        $output = sizeof($valids) == 1 ? " = ".array_shift($valids) : " IN (".implode(',', $valids).")";
        return BIZUNO_DB_PREFIX."journal_main.journal_id".$output;
    }

    /**
     * Builds the drop down of available journals for searching
     * @return array drop down formatted list of available journals filtered by security
     */
    private function selJournals()
    {
        $blocked= [];
        $output = [['id'=>0, 'text'=>lang('all')]];
        for ($i = 1; $i < 30; $i++) {
            if (getUserCache('security', "j{$i}_mgr", false, 0)) {
                $output[] = ['id'=>$i, 'text'=>lang("journal_main_journal_id_$i")];
            } else { $blocked[] = $i; }
        }
        $this->blocked_journals = sizeof($blocked) > 0 ? implode(',', $blocked) : false;
        return $output;
    }

    /**
     * Toggles the waiting flag (and database field) for a given journal record
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function toggleWaiting(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('err_copy_name_prompt')); }
        $waiting = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'waiting', "id=$rID");
        $state = $waiting=='0' ? '1' : '0';
        dbWrite(BIZUNO_DB_PREFIX."journal_main", ['waiting'=>$state], 'update', "id=$rID");
        $layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>"jq('#dgPhreeBooks').datagrid('reload');"]]);
    }

    /**
     * creates the HTML for a pop up to set the recur parameters of a journal entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function popupRecur(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 2)) { return; }
        $presets= clean('data','text', 'get');
        $vals = explode(':', $presets);
        $freq = isset($vals[0]) && clean($vals[0], 'integer') ? clean($vals[0], 'integer') : 1;
        $times= isset($vals[1]) && clean($vals[1], 'integer') ? clean($vals[1], 'integer') : 2;
        $html  = "<p>".$this->lang['recur_desc']."</p>";
        $html .= html5('rcrTimes',  ['label'=>$this->lang['recur_times'],    'position'=>'after', 'attr'=>  ['value'=>$times, 'size'=>'3', 'maxlength'=>'2']]);
        $html .= "<p>".$this->lang['recur_frequency']."</p>";
        $html .= html5('radioRecur',['label'=>lang('dates_weekly'),   'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'1', 'checked'=>$freq==1?true:false]])."<br />";
        $html .= html5('radioRecur',['label'=>lang('dates_bi_weekly'),'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'2', 'checked'=>$freq==2?true:false]])."<br />";
        $html .= html5('radioRecur',['label'=>lang('dates_monthly'),  'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'3', 'checked'=>$freq==3?true:false]])."<br />";
        $html .= html5('radioRecur',['label'=>lang('dates_quarterly'),'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'4', 'checked'=>$freq==4?true:false]])."<br />";
        $html .= html5('iconGO',    ['icon'=>'next','events'=>['onClick'=>"jq('#recur_id').val(jq('#rcrTimes').val()); jq('#recur_frequency').val(jq('input[name=radioRecur]:checked').val()); bizWindowClose('winRecur');"]]);
        $layout = array_replace_recursive($layout, ['type'=>'divHTML','divs'=>['winRecur'=>['order'=>50, 'type'=>'html', 'html'=>$html]]]);
    }
}
