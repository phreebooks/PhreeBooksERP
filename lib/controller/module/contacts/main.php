<?php
/*
 * Module contacts main methods
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
 * @filesource /lib/controller/module/contacts/main.php
 */

namespace bizuno;

class contactsMain
{
    public  $moduleID = 'contacts';
    private $reqFields= ['primary_name', 'address1', 'telephone1', 'email'];
    private $restrict_store = true;
    private $defaults = [];

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        $rID = clean('rID', 'integer', 'get');
        if ($rID) { $this->type = dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'type', "id=$rID"); }
        else      { $this->type = clean('type', ['format'=>'char', 'default'=>'c'], 'get'); }
        if (!defined('CONTACT_TYPE')) { define('CONTACT_TYPE', $this->type); }
        $this->securityModule = 'contacts';
        $this->securityMenu   = 'mgr_'.$this->type;
        switch ($this->type) {
            case 'b': $this->securityMenu = 'mgr_c'; // allow access o branches if access to customers
                      $this->helpIndex='40.80'; $this->f0_default='0'; break; // branches
            case 'c': $this->helpIndex='40.30'; $this->f0_default='a'; break; // customers
            case 'e': $this->helpIndex='40.50'; $this->f0_default='0'; break; // employees
            case 'i': $this->helpIndex='40.40'; $this->f0_default='a'; break; // crm
            case 'j': $this->helpIndex='40.70'; $this->f0_default='a'; break; // jobs/projects
            case 'v': $this->helpIndex='40.20'; $this->f0_default='0'; break; // vendors
            default:
        }
        $this->contact = [
            'id'         => 0,
            'type'       => $this->type,
            'gl_account' => $this->type=='v'? getModuleCache('phreebooks', 'settings', 'vendors', 'gl_expense') : getModuleCache('phreebooks', 'settings', 'customers', 'gl_sales'),
            'terms'      => '0',
            'price_sheet'=> '',
            'tax_rate_id'=> clean('tax_rate_id', ['format'=>'integer','default'=>0], 'post'),
            'first_date' => date('Y-m-d'),
            'last_update'=> date('Y-m-d')];
        $this->status_choices = [['id'=>'0','text'=>lang('active')],['id'=>'1','text'=>lang('inactive')],['id'=>'2','text'=>lang('locked')]];
    }

    /**
     * Main manager constructor for all contact types
     * @param array $layout - current working structure
     * @return modified $layout with additions/changes
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID  = clean('rID', 'integer', 'get');
        $view = clean('view', ['format'=>'text','default'=>'page'], 'get');
        $title= sprintf(lang('tbd_manager'), lang('contacts_type', $this->type));
        if     (in_array($this->type, ['c','i'])) { $submenu = viewSubMenu('customers'); }
        elseif ($this->type == 'v')               { $submenu = viewSubMenu('vendors'); }
        else   { $submenu = ''; }
        if ($rID) {
            $jsReady = "jq(document).ready(function() { accordionEdit('accContacts', 'dgContacts', 'divContactDetail', '".jsLang('details')."', 'contacts/main/edit&type=$this->type', $rID); });";
        } else {
            $jsReady = "bizFocus('search_$this->type', 'dgContacts');";
        }
        $data = ['type'=>'page','title'=>$title,
            'divs' => [
                'submenu' =>['order'=>10,'type'=>'html','html'=>$submenu],
                'contacts'=>['order'=>50,'type'=>'accordion','id'=>'accContacts','divs'=>[
                    'divContactManager'=>['order'=>30,'type'=>'datagrid','label'=>$title,         'key' =>'manager'],
                    'divContactDetail' =>['order'=>70,'type'=>'html',    'label'=>lang('details'),'html'=>'&nbsp;']]]],
            'datagrid'=>['manager'=>$this->dgContacts('dgContacts', $this->type, $security)],
            'jsReady'=>['init'=>$jsReady]];
        if ($view == 'div') { // probably a status popup
            $data['type'] = 'divHTML';
            $layout = array_replace_recursive($layout, $data);
        } else {
            $layout = array_replace_recursive($layout, viewMain(), $data);
        }
    }

    /**
     * Gets the results to populate the active contact datagrid
     * @param array $layout -  working structure
     * @return type
     */
    public function managerRows(&$layout=[])
    {
        $type = clean('type',['format'=>'text','default'=>$this->type], 'get'); // reload here for multi type searches
        $rID  = clean('rID', 'integer','get');
        $iID  = clean('ref', 'integer','get');
        $this->restrict_store = clean('store',['format'=>'boolean','default'=>true],'get'); // set store restiction override
        if (strlen($type)>1) { $this->type = 'i'; } // must have CRM access to look at entire contacts table
        if (in_array($this->type, ['b'])) {
            $security = 1; // branches are part of bizuno admin settings and restricted in settings, needs to be read-only here for address searches of branches for users without access to settings
        } else {
            if (!$security = validateSecurity('contacts', "mgr_{$this->type}", 1)) { return; }
        }
        $_POST['search_'.$type] = getSearch(['search_'.$type,'q']);
        if ($rID) { $_POST['search_'.$type] = ''; } // preload hit which is erased if searching is started
        $data = $this->dgContacts('dgContacts', $type, $security, $rID);
        if ($rID) { $data['source']['filters']['rID'] = ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."contacts.id=$rID"]; }
        if ($iID) { // it's the crm table in contacts
            if ($iID==0) { $iID = '-1'; } // prevents listing non-associated crm records for new record
            $data['source']['filters']['rep_id'] = ['order'=>98, 'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."contacts.rep_id=$iID"];
        }
        $data['strict'] = true;
        $layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$data]);
    }

    /**
     * Sets the cache registry settings of current user selections
     * @param char $type - contact type code
     */
    private function managerSettings($type=false)
    {
        if (!$type) { $type = $this->type; }
        $data = ['path'=>'contacts_'.$type, 'values'=>[
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows'), 'method'=>'request'],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX."contacts.short_name"],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'f0_'.$type,'clean'=>'char','default'=>$this->f0_default],
            ['index'=>'search_'.$type,'clean'=>'text','default'=>'']]];
        if (clean('clr', 'boolean', 'get')) { clearUserCache($data['path']); }
        $this->defaults = updateSelection($data);
    }

    /**
     * Editor for all contact types, customized by type specified
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        $structure = dbLoadStructure(BIZUNO_DB_PREFIX."contacts", $this->type);

        // remove after 2019-01-01
        $structure['inactive']['attr']['type'] = 'select';
        $structure['inactive']['attr']['value']= '0';

        // merge data with structure
        $cData = dbGetRow(BIZUNO_DB_PREFIX."contacts", "id=$rID");
        dbStructureFill($structure, $cData);
        $structure['address_book'] = dbLoadStructure(BIZUNO_DB_PREFIX."address_book",$this->type);
        if ($rID) { // set some defaults
            $aValue= dbGetRow(BIZUNO_DB_PREFIX."address_book", "ref_id=$rID AND type='m'");
            dbStructureFill($structure['address_book'], $aValue);
            $title = $structure['short_name']['attr']['value'].' - '.$aValue['primary_name'];
        } else {
            $title = lang('new');
            $structure['gl_account']['attr']['value']= $this->contact['gl_account'];
            $structure['terms']['attr']['value']     = '0';
        }
        $fldGeneral = ['id','type','short_name','inactive','rep_id','tax_rate_id','contact_first','contact_last','price_sheet',
            'flex_field_1','store_id','account_number','gov_id_number','gl_account','terms','terms_text','terms_edit','recordID'];
        // set some special cases
        $structure['type']['attr']['value']= $this->type;
        $structure['short_name']['tooltip']= lang('msg_leave_null_to_assign_ref');
        $structure['inactive']['label']    = lang('status');
        $structure['inactive']['values']   = $this->status_choices;
        $structure['rep_id']['values']     = viewRoleDropdown();
        $structure['tax_rate_id']['values']= viewSalesTaxDropdown($this->type=='b'?'c':$this->type, 'inventory');
        unset($structure['tax_rate_id']['attr']['size']);
        // set some new fields
        $structure['terms_text']= ['col'=>3,'label'=>pullTableLabel("contacts", 'terms', $this->type),
            'attr'=>['value'=>viewTerms($structure['terms']['attr']['value'], true, $this->type), 'readonly'=>'readonly']];
        $structure['terms_edit']= ['icon'=>'settings','col'=>3,'break'=>true,'label'=>lang('terms'),'events'=>['onClick'=>"jsonAction('contacts/main/editTerms&type=$this->type',$rID,jq('#terms').val());"]];
        $structure['recordID']  = ['order'=>99,'html'=>'<p>Record ID: '.$structure['id']['attr']['value']."</p>",'attr'=>['type'=>'raw']];
        if (sizeof(getModuleCache('inventory', 'prices'))) {
            unset($structure['price_sheet']['attr']['size']);
            bizAutoLoad(BIZUNO_LIB."controller/module/inventory/prices.php", 'inventoryPrices');
            $tmp = new inventoryPrices();
            $structure['price_sheet']['values'] = $tmp->quantityList($this->type=='b'?'c':$this->type, true);
        }
        switch ($this->type) {
            case 'c': $formID = 'cust:ltr'; break;
            case 'v': $formID = 'vend:ltr'; break;
            default:  $formID = false;
        }
        $data = ['type'=>'divHTML', 'title'=>$title,
            'divs'     => [
                'toolbar' => ['order'=>10,'type'=>'toolbar','key' =>'tbContacts'],
                'heading' => ['order'=>15,'type'=>'html',   'html'=>"<h1>$title</h1>"],
                'formBOF' => ['order'=>20,'type'=>'form',   'key' =>'frmContact'],
                'tabs'    => ['order'=>50,'type'=>'tabs',   'key' =>'tabContacts'],
                'formEOF' => ['order'=>99,'type'=>'html',   'html'=>'</form>']],
            'toolbars' => [
                'tbContacts' => ['icons' => [
                    'save' => ['order'=>20,'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"if (jq('#frmContact').form('validate')) { jq('body').addClass('loading'); jq('#frmContact').submit(); }"]],
                    'new'  => ['order'=>40,'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"accordionEdit('accContacts', 'dgContacts', 'divContactDetail', '".lang('details')."', 'contacts/main/edit&type=$this->type', 0);"]],
                    'email'=> ['order'=>60,'hidden'=>$rID && $formID?false:true,     'events'=>['onClick'=>"winOpen('phreeformOpen', 'phreeform/render/open&group=$formID&xfld=contacts.id&xcr=equal&xmin=$rID');"]],
                    'trash'=> ['order'=>80,'hidden'=>$rID && $security==4?false:true,'events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('contacts/main/delete&type=$this->type', $rID, 'reset');"]]]],
                'tbAddressb' => ['icons' => [
                    'saveb' => ['order'=>10,'icon'=>'save','label'=>lang('save'),'hidden'=>$rID && $security >1?false:true,'events'=>['onClick'=>"divSubmit('contacts/main/saveAddress&aType=b&rID=$rID', 'addressb');"]],
                    'newb'  => ['order'=>20,'icon'=>'new', 'label'=>lang('new'), 'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressClear('b');"]],
                    'copyb' => ['order'=>30,'icon'=>'copy','label'=>lang('copy'),'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressCopy('m', 'b');"]]]],
                'tbAddresss' => ['icons' => [
                    'saves' => ['order'=>10,'icon'=>'save','label'=>lang('save'),'hidden'=>$rID && $security >1?false:true,'events'=>['onClick'=>"divSubmit('contacts/main/saveAddress&aType=s&rID=$rID', 'addresss');"]],
                    'news'  => ['order'=>20,'icon'=>'new', 'label'=>lang('new'), 'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressClear('s');"]],
                    'copys' => ['order'=>30,'icon'=>'copy','label'=>lang('copy'),'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressCopy('m', 's');"]]]]],
            'tabs'     => ['tabContacts'=>['divs'=>[
                'general' => ['order'=>10,'label'=>lang('general'),'type'=>'divs','divs'=>[
                    'genMain'  => ['order'=>20,'type'=>'fields', 'label'   =>lang('general'),'keys'=>$fldGeneral],
                    'genAddr'  => ['order'=>50,'type'=>'address','content' =>$structure['address_book'],'settings'=>['suffix'=>'m','required'=>true,'cols'=>true]],
                    'getAttach'=> ['order'=>80,'type'=>'attach', 'defaults'=>['path'=>getModuleCache($this->moduleID,'properties','attachPath'),'prefix'=>"rID_{$rID}_"]]]],
                'crm_add' => ['order'=>20,'label'=>lang('contacts'), 'type'=>'html', 'html'=>'',
                    'options'=> ['href'=>"'".BIZUNO_AJAX."&p=contacts/main/crmDetails&rID=$rID'"]],
                'history' => ['order'=>30,'label'=>lang('history'), 'hidden'=>$rID?false:true,'type'=>'html', 'html'=>'',
                    'options'=> ['href'=>"'".BIZUNO_AJAX."&p=contacts/main/history&rID=$rID'"]],
                'bill_add'=> ['order'=>40,'label'=>lang('address_book_type_b'), 'type'=>'html', 'html'=>'',
                    'options'=> ['href'=>"'".BIZUNO_AJAX."&p=contacts/main/getTabAddress&type=b&rID=$rID'"]],
                'ship_add'=> ['order'=>50,'label'=>lang('address_book_type_s'), 'type'=>'html', 'html'=>'',
                    'options'=> ['href'=>"'".BIZUNO_AJAX."&p=contacts/main/getTabAddress&type=s&rID=$rID'"]],
                'notes'   => ['order'=>70,'label'=>lang('notes'),'html'=>'',
                    'options'=> ['href'=>"'".BIZUNO_AJAX."&p=contacts/main/getTabNotes&rID=$rID'"]]]]],
            'forms'    => ['frmContact'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=contacts/main/save&type=$this->type"]]],
            'fields'   => $structure,
            'jsReady'  => ['init'=>"ajaxForm('frmContact');"]];
        customTabs($data, 'contacts', 'tabContacts');
        $this->editCustomType($data, $rID); // customize based on type
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Gets a specific address record for a given ID passed through the GET variable
     * @param array $layout - current working structure
     * @return updated $layout
     */
    public function editAddress(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 3)) { return; }
        if (!$aID = clean('data', 'integer', 'get')) { return msgAdd('No id returned!'); }
        $row    = dbGetRow(BIZUNO_DB_PREFIX."address_book", "address_id=$aID");
        $suffix = $row['type'];
        $data   = ['content'=>['action'=>'eval', 'actionData'=>"addressFill(".json_encode($row).", '$suffix');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    private function editCustomType(&$data, $rID)
    {
        switch ($this->type) {
            case 'c': // Customers
                $data['tabs']['tabContacts']['divs']['payment'] = ['order'=>60,'label'=>lang('payment'),'hidden'=>$rID && getUserCache('profile', 'admin_encrypt', false, '')?false:true,'type'=>'html','html'=>'',
                    'options'=>['href'=>"'".BIZUNO_AJAX."&p=payment/main/manager&rID=$rID'"]];
                break;
            case 'j': // Projects/Jobs
            case 'v': break; // Vendors
            case 'b':
                $data['fields']['gl_account']['label']           = lang('default_gl_account').': '.lang('gl_acct_type_30'); // sales gl acct
                $data['fields']['flex_field_1']['label']         = lang('default_gl_account').': '.lang('sales_discounts'); // discount gl acct
                $data['fields']['account_number']['label']       = lang('default_gl_account').': '.lang('gl_acct_type_2');  // AR gl acct
                $data['fields']['gov_id_number']['label']        = lang('default_gl_account').': '.lang('gl_acct_type_20'); // AP gl acct
                $data['fields']['gl_account']['attr']['type']    = 'ledger';
                $data['fields']['flex_field_1']['attr']['type']  = 'ledger';
                $data['fields']['account_number']['attr']['type']= 'ledger';
                $data['fields']['gov_id_number']['attr']['type'] = 'ledger';
                unset($data['fields']['contact_first'],$data['fields']['contact_last'],$data['fields']['store_id']);
                break;
            case 'e': // Employees
                $fldGeneral = ['id','type','short_name','inactive','contact_first','contact_last',
                    'flex_field_1','store_id','gov_id_number','recordID'];
                $data['tabs']['tabContacts']['divs']['general']['divs']['genMain']['keys'] = $fldGeneral;
                $data['fields']['flex_field_1']['order'] = 15;
//                unset($data['fields']['rep_id'],$data['fields']['account_number'],$data['fields']['tax_rate_id'],$data['fields']['gl_account']);
//                unset($data['fields']['terms'],$data['fields']['terms_edit'],$data['fields']['terms_text']);
                break;
            case 'i': // CRM
                $fldGeneral = ['id','type','short_name','inactive','rep_id','tax_rate_id','contact_first','contact_last',
                    'flex_field_1','store_id','account_number','gov_id_number','gl_account','terms','recordID'];
                $data['tabs']['tabContacts']['divs']['general']['divs']['genMain']['keys'] = $fldGeneral;
                $linkID = !empty($data['fields']['rep_id']['attr']['value']) ? $data['fields']['rep_id']['attr']['value'] : 0;
                $data['fields']['account_number']['attr']['type']= 'hidden';
                $data['fields']['gov_id_number']['attr']['type'] = 'hidden';
                $data['fields']['tax_rate_id']['attr']['type']   = 'hidden';
                $data['fields']['gl_account']['attr']['type']    = 'hidden';
                $data['fields']['terms']['attr']['type']         = 'hidden';
                $data['fields']['account_number']['attr']['type']= 'hidden';
                unset($data['fields']['rep_id']['attr']['size'],$data['fields']['rep_id']['values']);
                $primary_name = $linkID ? dbGetValue(BIZUNO_DB_PREFIX.'address_book', 'primary_name', "ref_id=$linkID AND type='m'") : '';
                $data['jsReady']['repIDi'] = "
jq('#rep_id').combogrid({width:225,panelWidth:825,delay:700,idField:'id',textField:'primary_name',mode:'remote',
    url:    '".BIZUNO_AJAX."&p=contacts/main/managerRows&type=cv&store=0',
    onBeforeLoad:function (param) { var newValue = jq('#rep_id').combogrid('getValue'); if (newValue.length < 3) return false; },
    selectOnNavigation:false,
    columns:[[{field:'id',hidden:true},
        {field:'short_name',  title:'".pullTableLabel(BIZUNO_DB_PREFIX."contacts",    'short_name')."',  width:100},
        {field:'type',        title:'".pullTableLabel(BIZUNO_DB_PREFIX."contacts",    'type')."',        width:100},
        {field:'primary_name',title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'primary_name')."',width:200},
        {field:'address1',    title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'address1')."',    width:200},
        {field:'city',        title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'city')."',        width:100},
        {field:'postal_code', title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'postal_code')."', width:100}
    ]]
}).combogrid('setValue', {id:'$linkID',primary_name:'$primary_name'});";
                break;
        }
        if (in_array($this->type, ['c','v'])) {
            $jIdx = $this->type=='v' ? "j6_mgr" : "j12_mgr";
            if (!validateSecurity('phreebooks', $jIdx, 1, false)) { $data['tabs']['tabContacts']['divs']['history']['hidden'] = true; }
            if (!getModuleCache('extShipping', 'properties', 'status')) { unset($data['tabs']['tabContacts']['divs']['ship_add']); }
        } else {
            unset($data['tabs']['tabContacts']['divs']['crm_add']);
            unset($data['tabs']['tabContacts']['divs']['history']);
            unset($data['tabs']['tabContacts']['divs']['bill_add']);
            unset($data['tabs']['tabContacts']['divs']['ship_add']);
        }
    }

    /**
     * Saves posted data to a contact record
     * @param array $layout - current working structure
     * @param boolean $makeTransaction - [default: true] Makes the save operation a transaction, should only be set to false if this method is part of another transaction
     * @return modified $laylout
     */
    public function save(&$layout=[], $makeTransaction=true)
    {
        $rID  = clean('id', 'integer', 'post');
        $title= clean('short_name', 'text', 'post');
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, $rID?3:2)) { return; }
        if ($makeTransaction) { dbTransactionStart(); } // START TRANSACTION (needs to be here as we need the id to create links
        if (!$result = $this->dbContactSave($this->type, '')) { return; } // Main record
        if (!$rID) { $rID = $result; }
        $_GET['rID'] = $_POST['id'] = $rID; // save for custom processing
        if (!$this->dbAddressSave($rID, 'm', 'm', true)) { return; }   // Address records, main is required
        $this->dbAddressSave($rID, 'b', 'b', false);
        $this->dbAddressSave($rID, 's', 's', false);
        $this->dbCRMSave    ($rID, 'i'); // CRM contact record
        $this->saveLog($layout, $rID);
        if ($makeTransaction) { dbTransactionCommit(); }
        $io = new \bizuno\io();
        if ($io->uploadSave('file_attach', getModuleCache('contacts', 'properties', 'attachPath')."rID_{$rID}_")) {
            dbWrite(BIZUNO_DB_PREFIX.'contacts', ['attach'=>'1'], 'update', "id=$rID");
        }
        msgAdd(lang('msg_record_saved'), 'success'); // doesn't hang if returning to manager
        msgLog(sprintf(lang('tbd_manager'), lang('contacts_type', $this->type))." - ".lang('save')." - $title (rID=$rID)");
        $data = ['content' => ['action'=>'eval','actionData'=>"jq('#accContacts').accordion('select', 0); jq('#dgContacts').datagrid('reload'); jq('#divContactDetail').html('&nbsp;');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Saves just a single address in the db
     * @param array $layout - current working structure
     * @return updated $layout
     */
    public function saveAddress(&$layout=[])
    {
        $rID  = clean('rID',  'integer','get');
        $aType= clean('aType','char',   'get');
        if (!$rID) { return msgAdd(lang('err_bad_id')); }
        switch ($aType) {
            case 'i': // CRM  so it's a contact with main address
                $this->dbCRMSave($rID, 'i');
                $dgID = 'crm_main';
                break;
            default: // just the address record
                $this->dbAddressSave($rID, $aType, $aType, false);
                $dgID = "addressMain$aType";
                break;
        }
        // return to clear address fields and reload datagrid
        $data = ['content' => ['action'=>'eval','actionData'=>"jq('#$dgID').datagrid('reload'); clearAddress('$aType');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Saves a log entry to a specified contact record
     * @param integer $rID - db record id of the contact to update/save log data
     */
    public function saveNotes()
    {
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return; }
        dbWrite(BIZUNO_DB_PREFIX."address_book", ['notes'=> clean('notesm','text','post')], 'update', "ref_id=$rID AND type='m'");
        msgAdd(lang('msg_record_saved'), 'success');
    }

    /**
     * Saves a log entry to a specified contact record
     * @param integer $rID - db record id of the contact to update/save log data
     */
    public function saveLog(&$layout, $id=0)
    {
        $rID = $id ? $id : clean('rID', 'integer', 'get');
        $action= clean('crm_action','text', 'post');
        $note  = clean('crm_note',  'text', 'post');
        if (!$rID || !$note) { return; }
        $values = [
            'contact_id'=> $rID,
            'entered_by'=> clean('crm_rep_id','integer','post'),
            'log_date'  => clean('crm_date',  'date',   'post'),
            'action'    => $action,
            'notes'     => $note];
        dbWrite(BIZUNO_DB_PREFIX."contacts_log", $values);
        $data = ['content'=>['action'=>'eval','actionData'=>"jq('#dgLog').datagrid('reload');"]];
        if (!$id) { msgAdd(lang('msg_record_saved'), 'success'); } // if stand alone
        $layout = array_replace_recursive($layout, $data);

    }

    /**
     * form builder - Merges 2 database contact ID's to a single record
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function merge(&$layout=[])
    {
        $icnSave= ['icon'=>'save','label'=>lang('merge'),
            'events'=>['onClick'=>"jsonAction('contacts/main/mergeSave&type=$this->type', jq('#mergeSrc').val(), jq('#mergeDest').val());"]];
        $props  = ['defaults'=>['type'=>$this->type,'callback'=>''],'attr'=>['type'=>'contact']];
        $html   = "<p>".$this->lang['msg_contacts_merge_src'] ."</p><p>".html5('mergeSrc', $props)."</p>".
                  "<p>".$this->lang['msg_contacts_merge_dest']."</p><p>".html5('mergeDest',$props)."</p>".html5('icnMergeSave', $icnSave);
        $data   = ['type'=>'popup','title'=>$this->lang['contacts_merge'],'attr'=>['id'=>'winMerge'],
            'divs'   => ['body'=>['order'=>50,'type'=>'html','html'=>$html]],
            'jsReady'=> ['init'=>"bizFocus('mergeSrc');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Performs the merge of 2 contacts in the db
     * @param array $layout - current working structure
     * @return modifed $layout
     */
    public function mergeSave(&$layout=[])
    {
        global $io;
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 3)) { return; }
        $srcID = clean('rID', 'integer', 'get'); // record ID to merge
        $destID= clean('data','integer', 'get'); // record ID to keep
        if (!$srcID || !$destID) { return msgAdd("Bad IDs, Source ID = $srcID and Destination ID = $destID"); }
        if ($srcID == $destID)   { return msgAdd("Source and destination cannot be the same!"); }
        $message  = lang('stats').': ';
        $bCnt = dbWrite(BIZUNO_DB_PREFIX."journal_main", ['contact_id_b'=>$destID], 'update', "contact_id_b=$srcID");
        $message .= "journal_main billing = $bCnt; ";
        $sCnt = dbWrite(BIZUNO_DB_PREFIX."journal_main", ['contact_id_s'=>$destID], 'update', "contact_id_s=$srcID");
        $message .= "journal_main shipping = $sCnt; ";
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."address_book WHERE ref_id=$srcID AND type='m'");
        $message .= 'deleted main address; ';
        $aCnt = dbWrite(BIZUNO_DB_PREFIX."address_book", ['ref_id'=>$destID], 'update', "ref_id=$srcID");
        $message .= "address_book = $aCnt; ";
        $cCnt = dbWrite(BIZUNO_DB_PREFIX."contacts_log", ['contact_id'=>$destID], 'update', "contact_id=$srcID");
        $message .= "contacts_log = $cCnt; ";
        $dCnt = dbWrite(BIZUNO_DB_PREFIX."data_security", ['ref_1'=>$destID], 'update', "ref_1=$srcID");
        $message .= "data_security = $dCnt; ";
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."contacts WHERE id=$srcID");
        $message .= 'deleted contact; ';
        // merge the attachments
        msgDebug("\nMoving file at path: ".getModuleCache('contacts', 'properties', 'attachPath')." from rID_{$srcID}_ to rID_{$destID}_");
        $io->fileMove(getModuleCache('contacts', 'properties', 'attachPath'), "rID_{$srcID}_", "rID_{$destID}_");
        msgAdd($message, 'success');
        msgLog(lang("contacts").'-'.lang('merge')." - $srcID => $destID");
        $data = ['content'=>['action'=>'eval','actionData'=>"bizWindowClose('winMerge'); jq('#dgContacts').datagrid('reload');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Deletes a contact records from the database
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 4)) { return; }
        $rID   = clean('rID',  'integer', 'get');
        $action= clean('data', 'text', 'get');
        if (!$rID) { return msgAdd('The record was not deleted, the proper id was not passed!'); }
        // error check, no delete if a journal entry exists
        $block = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'id', "contact_id_b='$rID' OR contact_id_s='$rID' OR store_id='$rID'");
        if ($block) { return msgAdd($this->lang['err_contacts_delete']); }
        $short_name = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'short_name', "id='$rID'");
        $actionData = "jq('#dgContacts').datagrid('reload'); accordionEdit('accContacts','dgContacts','divContactDetail','".jsLang('details')."','contacts/main/edit&type=$this->type', 0);";
        if (isset($action) && $action) {
            $parts = explode(':', $action);
            switch ($parts[0]) {
                case 'reload': $actionData = "jq('#{$parts[1]}').datagrid('reload');"; break; // just reload the datagrid
            }
        }
        $data = ['content'=>['action'=>'eval','actionData'=>$actionData],'dbAction'=>[
            BIZUNO_DB_PREFIX."contacts"     => "DELETE FROM ".BIZUNO_DB_PREFIX."contacts WHERE id=$rID",
            BIZUNO_DB_PREFIX."address_book" => "DELETE FROM ".BIZUNO_DB_PREFIX."address_book WHERE ref_id=$rID",
            BIZUNO_DB_PREFIX."data_security"=> "DELETE FROM ".BIZUNO_DB_PREFIX."data_security WHERE ref_1=$rID",
            BIZUNO_DB_PREFIX."contacts_log" => "DELETE FROM ".BIZUNO_DB_PREFIX."contacts_log WHERE contact_id=$rID"]];
        $files = glob(getModuleCache('contacts', 'properties', 'attachPath')."rID_{$rID}_*.zip");
        if (is_array($files)) { foreach ($files as $filename) { @unlink($filename); } }
        msgLog(lang('contacts_title')." ".lang('delete')." - $short_name (rID=$rID)");
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Deletes a address record for a given address_book id, typically call through ajax
     * @param type $layout - current working structure
     * @return modified layout
     */
    public function deleteAddress(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 4)) { return; }
        $aID = clean('data', 'integer', 'get');
        if (!$aID) { return msgAdd('No id returned!'); }
        $row = dbGetRow(BIZUNO_DB_PREFIX."address_book", "address_id=$aID");
        $type= $row['type'];
        if ($type == 'm') { return (msgAdd($this->lang['err_contacts_delete_address']));  }
        msgLog(lang('address_book').' '.lang('delete')." - {$row['primary_name']} ($aID)");
        $data = ['content' => ['action'=>'eval','actionData'=>"jq('#addressMain$type').datagrid('reload');"],
                 'dbAction'=> [BIZUNO_DB_PREFIX."address_book"=>"DELETE FROM ".BIZUNO_DB_PREFIX."address_book WHERE address_id=$aID"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Ajax call to refresh the history tab of a contact being edited.
     * @param type $layout
     * @return typef'src'
     */
    public function history(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if ($rID) { $row = dbGetRow(BIZUNO_DB_PREFIX.'contacts', "id=$rID"); }
        else      { $row = ['type'=>'c','first_date'=>date('Y-m-d'),'first_date'=>date('Y-m-d')]; }
        msgDebug("\nread row = ".print_r($row, true));
        $first_date  = ['order'=>10,'label'=>pullTableLabel('contacts','first_date'), 'break'=>true,'attr'=>['type'=>'date','value'=>$row['first_date'], 'readonly'=>'readonly']];
        $last_update = ['order'=>20,'label'=>pullTableLabel('contacts','last_update'),'break'=>true,'attr'=>['type'=>'date','value'=>$row['last_update'],'readonly'=>'readonly']];
        $data = ['type'=>'divHTML',
            'divs'    => [
                'props' => ['order'=>20,'type'=>'fields','attr'=>['id'=>'fldProps'],'fields'=>['first_date'=>$first_date, 'last_update'=>$last_update]],
                'dgSoPo'=> ['order'=>30,'type'=>'datagrid','classes'=>['blockView'],'attr'=>['id'=>'dgSoPo'],'key'=>'po_so'],
                'dgInv' => ['order'=>40,'type'=>'datagrid','classes'=>['blockView'],'attr'=>['id'=>'dgInv'], 'key'=>'inv']],
            'datagrid'=> [
                'po_so'=> $this->dgHistory('dgHistory10', $row['type']=='v'?4:10, $rID),
                'inv'  => $this->dgHistory('dgHistory12', $row['type']=='v'?6:12, $rID)]];
        $layout = array_replace_recursive($layout, $data);
        msgDebug("\nlayout is now = ".print_r($layout, true));
    }

    /**
     * Shows the details of a contact record, typically used for popups where no editing will take place
     * @param array $layout - structure
     * @return modified $layout
     */
    public function details(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID    = clean('rID', 'integer','get');
        $prefix = clean('prefix','text', 'get');
        $suffix = clean('suffix','text', 'get');
        $fill   = clean('fill',  'char', 'get');
        if (!$rID) { // biz_id=0, send company information
            $address[] = addressLoad();
            $address[0]['type'] = 'm';
            $data = ['prefix'=>$prefix, 'suffix'=>$suffix, 'fill'=>$fill, 'contact'=>[], 'address'=>$address];
        } else {
            $contact= dbGetRow(BIZUNO_DB_PREFIX."contacts", "id=$rID");
            $type   = $contact['type']=='v' ? 'vendors' : 'customers';
            // Fix a few things
            $contact['terms_text']   = viewTerms($contact['terms'], true, $contact['type']);
            $contact['terminal_date']= getTermsDate($contact['terms'], $contact['type']);
            if (getModuleCache('extShipping', 'properties', 'status') && getModuleCache('extShipping', 'settings', 'general', 'gl_shipping_'.$contact['type'])) {
                $contact['ship_gl_acct_id'] = getModuleCache('extShipping', 'settings', 'general', 'gl_shipping_'.$contact['type']);
            }
            $address= dbGetMulti(BIZUNO_DB_PREFIX."address_book", "ref_id=$rID", "primary_name");
            $data   = ['prefix'=>$prefix, 'suffix'=>$suffix, 'fill'=>$fill, 'contact'=>$contact, 'address'=>$address];
            $data['showStatus'] = getModuleCache('phreebooks', 'settings', $type, 'show_status', 1);
        }
        $layout = array_replace_recursive($layout, ['content'=>$data]);
    }

    /**
     * Builds the contact popup (including hooks and customizations)
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function properties(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd("Bad cID passed!"); }
        compose('contacts', 'main', 'edit', $layout);
        unset($layout['divs']['formBOF'], $layout['divs']['formEOF'], $layout['divs']['toolbar']);
        unset($layout['tabs']['tabContacts']['divs']['general']['divs']['getAttach']);
        unset($layout['jsHead'], $layout['jsReady']);
    }

    /**
     * Sets the registry cache with address book user settings
     * @param char $type - contact type
     */
    private function managerSettingsAddress($type='m')
    {
        $data = ['path'=>'address'.$type, 'values' => [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX.'address_book.primary_name'],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'search_'.$type,'clean'=>'text','default'=>''],
        ]];
        $this->defaults = \bizuno\updateSelection($data);
    }

    /**
     * Gets the address for a given contact id and of given address type
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function managerRowsAddress(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID  = clean('rID',  'integer','get');
        $aType= clean('aType','char',   'get');
        if (!$rID) { return msgAdd('No id returned!'); }
//      $this->managerSettingsAddress($aType); // executed at dgAddress
        $data = ['type'=>'datagrid', 'structure'=>$this->dgAddress($rID, $this->type, $aType, $security)];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * This method saves a contact to table: contacts
     * @param string $request - typically the post variables, leave false to use $_POST variables
     * @param char $cType - [default c] contact type, c (customer), v (vendor), e (employee), b (branch), i (CRM), j (projects)
     * @param string $suffix - [default null] field suffix to extract data from the request data
     * @param boolean $required - [default true] field suffix to extract data from the request data
     * @return $rID - record ID of the create/affected contact record
     */
    public function dbContactSave($cType='c', $suffix='', $required=true)
    {
        $rID  = clean('id'.$suffix, 'integer', 'post');
        $title= clean('primary_name'.$suffix, 'text', 'post');
        msgDebug("\nfound rID = $rID");
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, $rID?3:2)) { return; }
        $values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."contacts"), $suffix);
        $values['type'] = $cType; // force the type or set if a suffix is used
        if (!$rID) { $values = array_merge($this->contact, $values); }
        else       { $values['last_update'] = date('Y-m-d'); }
        msgDebug("\nWorking with suffix $suffix and values = ".print_r($values, true));
        // if contact is not required and these fields are set, do not create/update the contacts table
        if (!$required) { if (isset($values['contact_first']) && empty($values['contact_first']) &&
                              isset($values['contact_last'])  && empty($values['contact_last'])  &&
                              empty($title)) { return; } }
        if (isset($values['short_name']) && $values['short_name']) { // check for duplicate short_names
            $short_name = addslashes($values['short_name']);
            $dup = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'id', "short_name='$short_name' AND type='$cType' AND id<>$rID");
            if ($dup) { return msgAdd(lang('error_duplicate_id')); }
        } elseif (!$rID) { // new contact, auto-increment is set to always on
            $str_field = $this->type=='v' ? 'next_vend_id_num' : 'next_cust_id_num';
            $result = dbGetValue(BIZUNO_DB_PREFIX."current_status", $str_field);
            $values['short_name'] = $result;
            $result++;
            dbWrite(BIZUNO_DB_PREFIX."current_status", [$str_field => $result], 'update');
        } elseif (isset($values['short_name']) && !$values['short_name']) {
            unset($values['short_name']); // existing record and no Contact ID passed, leave it alone
        }
        if (empty($values['inactive'])) { $values['inactive'] = 0; } // fixes bug in conversions and prevents null inactive value
        $result = dbWrite(BIZUNO_DB_PREFIX."contacts", $values, $rID?'update':'insert', "id=$rID");
        if (!$rID) { $rID = $result; }
        $_POST['id'.$suffix] = $rID; // save for customization
        msgDebug("\n  Finished adding/updating contact, id = $rID");
        return $rID;
    }

    /**
     * This method saves an address to table: address_book
     * @param number $cID - contact ID, must be non-zero
     * @param string $aType - address type: m (main), b (billing), s (shipping)
     * @param string $suffix - field suffix to extract data from the request data
     * @return $aID - record ID of the create/affected record
     */
    public function dbAddressSave($cID=0, $aType='m', $suffix='', $required=false)
    {
        if (!$cID) { return msgAdd("Error updating address book, the proper contact ID was not passed!"); }
        $values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."address_book"), $suffix);
        // check for some fields set to indicate if record has data, return false if not
        if (!$required) { if ($this->isBlankForm($values, $this->reqFields)) { return; } }
        $aID = isset($values['address_id']) && $values['address_id'] ? $values['address_id'] : 0;
        $values['ref_id'] = $cID; // link to contact
        $values['type']   = $aType;
        $result = dbWrite(BIZUNO_DB_PREFIX."address_book", $values, $aID?'update':'insert', "address_id=$aID");
        if (!$aID) { $aID = $result; } // set the address id for new inserts
        $_POST['address_id'.$suffix] = $aID; // save for customization
        msgDebug("\n  Finished adding/updating address, address_id = $aID");
        return $aID;
    }

    /**
     * This method saves the crm record data
     * @param string $request
     * @param integer $rID
     * @param string $suffix
     */
    public function dbCRMSave($rID=0, $suffix='')
    {
        $primaryName = clean("primary_name$suffix", 'text', 'post');
        if (!$primaryName) { return; }
        $_POST['rep_id'.$suffix] = $rID; // set link to contact
        if (!$iID = $this->dbContactSave('i', $suffix, false)) { return; }// no changes, so just return
        $this->dbAddressSave($iID, 'm', $suffix, true);
    }

    /**
     * Builds address list datagrid structure
     * @param integer $rID - contact db record id
     * @param char $type - contact type
     * @param char $aType - address type
     * @param integer $security - working security level
     * @return array - datagrid structure, ready to render
     */
    private function dgAddress($rID=0, $type='', $aType='', $security=0)
    {
        $this->managerSettingsAddress($aType);
        return ['id'=>'addressMain'.$aType, 'rows'=>$this->defaults['rows'], 'page'=>$this->defaults['page'],
            'attr'   => ['idField'=>'address_id', 'url'=>BIZUNO_AJAX."&p=contacts/main/managerRowsAddress&type=$type&rID=$rID&aType=$aType"],
            'events' => ['onDblClickRow'=>"function(rowIndex, rowData){ jsonAction('contacts/main/editAddress', $rID, rowData.address_id); }"],
            'source' => [
                'tables'  => ['address_book'=> ['table'=>BIZUNO_DB_PREFIX."address_book"]],
                'search'  => ['primary_name', 'contact', 'telephone1', 'telephone2', 'telephone3', 'telephone4', 'city', 'postal_code', 'email'],
                'filters' => [
                    'search' => ['order'=>90,'attr'=>['id'=>"search_$aType", 'value'=>$this->defaults["search_$aType"]]],
                    'ref_id' => ['order'=>98,'hidden'=> true, 'sql'=>"ref_id=$rID"],
                    'type'   => ['order'=>99,'hidden'=> true, 'sql'=>"type='$aType'"]],
                'sort' =>['s0'=>['order'=>10,'field'=>$this->defaults['sort'].' '.$this->defaults['order']]]],
            'columns' => [
                'address_id' => ['order'=>0,'field'=>'address_id',  'attr'=>['hidden'=>true]],
                'ref_id'     => ['order'=>0,'field'=>'ref_id',      'attr'=>['hidden'=>true]],
                'action'     => ['order'=>1,'label'=>lang('action'),'events'=>['formatter'=>"function(value,row,index){ return addressMain".$aType."Formatter(value,row,index); }"],
                    'actions'=> [
                        'edit' => ['icon'=>'edit', 'size'=>'small', 'order'=>30, 'label'=>lang('edit'), 'hidden'=> $security > 2 ? false : true,
                            'events'=> ['onClick' => "jsonAction('contacts/main/editAddress', $rID, idTBD);"]],
                        'trash'=> ['icon'=>'trash','size'=>'small', 'order'=>90, 'label'=>lang('delete'), 'hidden'=> $security > 3 ? false : true,
                            'events'=> ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('contacts/main/deleteAddress', $rID, idTBD);"]]]],
                'primary_name'=> ['order'=>20, 'field'=>'primary_name', 'label'=>pullTableLabel("address_book", 'primary_name', $type),
                    'attr' => ['width'=>240, 'sortable'=>true, 'resizable'=>true]],
                'address1'    => ['order'=>30, 'field'=>'address1', 'label'=>pullTableLabel("address_book", 'address1', $type),
                    'attr' => ['width'=>200, 'sortable'=>true, 'resizable'=>true]],
                'city'        => ['order'=>40, 'field'=>'city', 'label'=>pullTableLabel("address_book", 'city', $type),
                    'attr' => ['width'=> 80, 'sortable'=>true, 'resizable'=>true]],
                'state'       =>  ['order'=>50, 'field'=>'state', 'label'=>pullTableLabel("address_book",'state', $type),
                    'attr' => ['width'=> 60, 'sortable'=>true, 'resizable'=>true]],
                'postal_code' => ['order'=>60, 'field'=>'postal_code', 'label'=>pullTableLabel("address_book", 'postal_code', $type),
                    'attr' => ['width'=> 60, 'sortable'=>true, 'resizable'=>true]],
                'telephone1'  => ['order'=>70,    'field'=>'telephone1', 'label'=>pullTableLabel("address_book", 'telephone1', $type),
                    'attr' => ['width'=>100, 'sortable'=>true, 'resizable'=>true]]]];
    }

    /**
     * This function builds the datagraid structure for retrieving contacts
     * @param string $name - datagrid div id
     * @param char $type - contact type, c - customers, v - vendors, etc.
     * @param integer $security - access level range 0-4
     * @param string $rID - contact record id for crm retrievals to limit results.
     * @return array $data - structure of the datagrid to render
     */
    private function dgContacts($name, $type, $security=0, $rID=false)
    {
        $this->managerSettings($type);
        $statusValues = array_merge([['id'=>'a','text'=>lang('all')]], $this->status_choices);
        // clean up the filter sqls
        if (!isset($this->defaults['f0_'.$type])) { $this->defaults['f0_'.$type] = 'a'; }
        switch ($this->defaults['f0_'.$type]) {
            default:
            case 'a': $f0_value = ""; break;
            case '0': $f0_value = BIZUNO_DB_PREFIX."contacts.inactive='0'"; break;
            case '1': $f0_value = BIZUNO_DB_PREFIX."contacts.inactive='1'"; break;
            case '2': $f0_value = BIZUNO_DB_PREFIX."contacts.inactive='2'"; break;
        }
        $data = ['id'=>$name, 'rows'=>$this->defaults['rows'], 'page'=>$this->defaults['page'],
            'attr'=> ['idField'=>'id', 'toolbar'=>"#{$name}Toolbar", 'url'=>BIZUNO_AJAX."&p=contacts/main/managerRows&type=$type".($rID?"&rID=$rID":'')],
            'events' => [
                'onDblClickRow'=> "function(rowIndex, rowData){ accordionEdit('accContacts', 'dgContacts', 'divContactDetail', '".jsLang('details')."', 'contacts/main/edit&type=$type', rowData.id); }",
                'rowStyler'    => "function(index, row) { if (row.inactive==1) { return {class:'row-inactive'}; } if (row.inactive==2) { return {style:'background-color:pink'}; }}"],
            'footnotes' => ['codes'=>lang('color_codes').': <span class="row-inactive">&nbsp;'.lang('inactive').'&nbsp;</span>&nbsp;<span style="background-color:pink">&nbsp;'.lang('locked').'&nbsp;</span>'],
            'source'    => [
                'tables' => [
                    'contacts'    =>['table'=>BIZUNO_DB_PREFIX."contacts"],
                    'address_book'=>['table'=>BIZUNO_DB_PREFIX."address_book",'join'=>'join','links'=>BIZUNO_DB_PREFIX."contacts.id=".BIZUNO_DB_PREFIX."address_book.ref_id"]],
                'search' => [
                    BIZUNO_DB_PREFIX."contacts.id",
                    BIZUNO_DB_PREFIX."contacts.short_name",
                    BIZUNO_DB_PREFIX."address_book.primary_name",
                    BIZUNO_DB_PREFIX."address_book.contact",
                    BIZUNO_DB_PREFIX."address_book.telephone1",
                    BIZUNO_DB_PREFIX."address_book.telephone2",
                    BIZUNO_DB_PREFIX."address_book.telephone3",
                    BIZUNO_DB_PREFIX."address_book.telephone4",
                    BIZUNO_DB_PREFIX."address_book.city",
                    BIZUNO_DB_PREFIX."address_book.postal_code"],
                'actions' => [
                    'help'      => ['order'=> 0,'icon'=>'help', 'align' =>'right','index'=>$this->helpIndex],
                    'newContact'=> ['order'=>10,'icon'=>'new',  'events'=>['onClick'=>"accordionEdit('accContacts', 'dgContacts', 'divContactDetail', '".lang('details')."', 'contacts/main/edit&type=$type', 0, '');"]],
                    'clrSearch' => ['order'=>50,'icon'=>'clear','events'=>['onClick'=>"jq('#f0_{$type}').val('$this->f0_default'); bizTextSet('search_$type', ''); ".$name."Reload();"]]],
                'filters' => [
                    "f0_$type"=> ['order'=>10,'break'=>true,'label'=>lang('status'),'sql'=>$f0_value,'values'=>$statusValues,'attr'=>['type'=>'select','value'=>$this->defaults['f0_'.$type]]],
                    'search'  => ['order'=>90,'attr'=>['id'=>"search_$type", 'value'=>$this->defaults['search_'.$type]]],
                    'cType'   => ['order'=>98,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."contacts.type='$type'"],
                    'aType'   => ['order'=>99,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."address_book.type='m'"]],
                'sort' => ['s0'=>['order'=>10,'field'=>($this->defaults['sort'].' '.$this->defaults['order'])]]],
            'columns' => [
                'id'      => ['order'=>0,'field'=>BIZUNO_DB_PREFIX."contacts.id",       'attr'=>['hidden'=>true]],
                'email'   => ['order'=>0,'field'=>BIZUNO_DB_PREFIX."address_book.email",'attr'=>['hidden'=>true]],
                'inactive'=> ['order'=>0,'field'=>BIZUNO_DB_PREFIX."contacts.inactive", 'attr'=>['hidden'=>true]],
                'attach'  => ['order'=>0,'field'=>BIZUNO_DB_PREFIX."contacts.attach",   'attr'=>['hidden'=>true]],
                'action'  => ['order'=>1,'label'=>lang('action'),
                    'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
                    'actions'=> [
                        'edit'  => ['icon'=>'edit', 'order'=>20, 'label'=>lang('edit'),
                            'events'=> ['onClick' => "accordionEdit('accContacts', 'dgContacts', 'divContactDetail', '".lang('details')."', 'contacts/main/edit&type=$type', idTBD);"]],
                        'delete'=> ['icon'=>'trash', 'order'=>60, 'label'=>lang('delete'),'hidden'=>$security>3?false:true,
                            'events'=> ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('contacts/main/delete', idTBD, 'reload:$name');"]],
                        'chart' => ['icon'=>'mimePpt', 'order'=>80, 'label'=>lang('sales'),'hidden'=>in_array($type, ['c','v'])?false:true,
                            'events'=> ['onClick' => "windowEdit('contacts/tools/chartSales&rID=idTBD', 'myChart', '&nbsp;', 600, 500);"]],
                        'attach' => ['order'=>95,'icon'=>'attachment','display'=>"row.attach=='1'"]]],
                'short_name'   => ['order'=>10, 'field'=>BIZUNO_DB_PREFIX.'contacts.short_name',
                    'label' => pullTableLabel("contacts", 'short_name', $type),
                    'attr'  => ['width'=>100, 'sortable'=>true, 'resizable'=>true]],
                'type'         => ['order'=>15, 'field'=>BIZUNO_DB_PREFIX."contacts.type", 'format'=>'contactType',
                    'attr'  => ['width'=>240, 'sortable'=>true, 'resizable'=>true, 'hidden'=>true]],
                'primary_name' => ['order'=>20, 'field'=>BIZUNO_DB_PREFIX.'address_book.primary_name',
                    'label' => pullTableLabel("address_book", 'primary_name', $type),
                    'attr'  => ['width'=>240, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($type,['e','i'])?true:false]],
                'contact_first'=> ['order'=>20, 'field' => BIZUNO_DB_PREFIX.'contacts.contact_first',
                    'label' => pullTableLabel("contacts", 'contact_first', $type),
                    'attr'  => ['width'=>100, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($type,['e','i'])?false:true]],
                'contact_last' => ['order'=>25, 'field'=>BIZUNO_DB_PREFIX.'contacts.contact_last',
                    'label' => pullTableLabel("contacts", 'contact_last', $type),
                    'attr'  => ['width'=>100, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($type,['e','i'])?false:true]],
                'flex_field_1'=> ['order'=>30, 'field'=>BIZUNO_DB_PREFIX.'contacts.flex_field_1',
                    'label' => pullTableLabel("contacts", 'flex_field_1', $type),
                    'attr'  => ['width'=>200, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($type,['i'])?false:true]],
                'address1'     => ['order'=>30, 'field'=>BIZUNO_DB_PREFIX.'address_book.address1',
                    'label' => pullTableLabel("address_book", 'address1', $type),
                    'attr'  => ['width'=>200, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($type,['i'])?true:false]],
                'city'    => ['order'=>40, 'field'=>BIZUNO_DB_PREFIX.'address_book.city',
                    'label' => pullTableLabel("address_book", 'city', $type),
                    'attr'  => ['width'=>80, 'sortable'=>true, 'resizable'=>true]],
                'state'=>  ['order'=>50, 'field'=>BIZUNO_DB_PREFIX.'address_book.state',
                    'label' => pullTableLabel("address_book", 'state', $type),
                    'attr'  => ['width'=>60, 'sortable'=>true, 'resizable'=>true]],
                'postal_code' => ['order'=>60, 'field'=>BIZUNO_DB_PREFIX.'address_book.postal_code',
                    'label' => pullTableLabel("address_book", 'postal_code', $type),
                    'attr'  => ['width'=>60, 'sortable'=>true, 'resizable'=>true]],
                'telephone1'  => ['order'=>70, 'field'=>BIZUNO_DB_PREFIX.'address_book.telephone1',
                    'label' => pullTableLabel("address_book", 'telephone1', $type),
                    'attr'  => ['width'=>100, 'sortable'=>true, 'resizable'=>true]]],
            ];
        if ($type=='c' || $type == 'v') {
            $data['source']['actions']['mergeContact'] = ['order'=>20,'icon'=>'merge','events'=>['onClick'=>"jsonAction('contacts/main/merge&type=$type', 0);"]];
        } elseif (strlen($type)>1) { // search only certain types, all types are listed (i.e. cv)
            $data['source']['filters']['cType']['sql'] = BIZUNO_DB_PREFIX."contacts.type IN ('".implode("','", str_split($type))."')";
//            $data['columns']['type']['attr']['hidden'] = false;
        }
        if (getUserCache('profile', 'restrict_user', false, 0)) { // check for user restrictions
            $uID = getUserCache('profile', 'contact_id', false, 0);
            $data['source']['filters']['restrict_user'] = ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."contacts.rep_id='$uID'"];
        }
        if ($GLOBALS['myDevice'] == 'mobile') {
            $data['columns']['short_name']['attr']['hidden'] = true;
            $data['columns']['flex_field_1']['attr']['hidden'] = true;
            $data['columns']['address1']['attr']['hidden'] = true;
            $data['columns']['state']['attr']['hidden'] = true;
            $data['columns']['postal_code']['attr']['hidden'] = true;
        }
        return $data;
    }

    /**
     *
     * @param string $name - HTML name of the contacts history datagrid
     * @param integer $jID - PhreeBooks journal ID to set search criteria
     * @param integer $rID - Contact database record id
     * @return array - datagrid structure
     */
    private function dgHistory($name, $jID, $rID = 0)
    {
        $rows   = clean('rows', ['format'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')], 'post');
        $page   = clean('page', ['format'=>'integer','default'=>1], 'post');
        $sort   = clean('sort', ['format'=>'text',   'default'=>'post_date'],'post');
        $order  = clean('order',['format'=>'text',   'default'=>'desc'],     'post');
        $gID    = explode(":", getDefaultFormID($jID));
        $jSearch= in_array($jID, [4,10]) ? $jID : ($jID==6 ? '6,7' : '12,13');
        switch ($jID) {
            case  6: $jPmt = 20; break;
            case 12:
            default: $jPmt = 18; break;
        }
        $data = ['id'=>$name, 'strict'=>true, 'rows'=>$rows, 'page'=>$page, 'title'=>sprintf(lang('tbd_history'), lang('journal_main_journal_id', $jID)),
            'attr'   => ['idField'=>'id', 'url'=>BIZUNO_AJAX."&p=contacts/main/managerRowsHistory&type=$this->type&jID=$jID&rID=$rID"],
            'source' => [
                'tables' => ['journal_main'=>['table'=>BIZUNO_DB_PREFIX."journal_main"]],
                'filters' => [
                    'jID'  => ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."journal_main.journal_id IN ($jSearch)"],
                    'rID'  => ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."journal_main.contact_id_b='$rID'"]],
                'sort' => ['s0'=>['order'=>10, 'field'=>("$sort $order")]]],
            'columns' => [
                'id'        => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."journal_main.id",        'attr'=>['hidden'=>true]],
                'closed'    => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."journal_main.closed",    'attr'=>['hidden'=>true]],
                'journal_id'=> ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."journal_main.journal_id",'attr'=>['hidden'=>true]],
                'bal_due'   => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."journal_main.id",'process'=>'paymentRcv','attr'=>['hidden'=>true]],
                'action'    => ['order'=>1, 'label'=>lang('action'),'events'=>['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
                    'actions'=> [
                        'edit'       => ['order'=>20,'icon'=>'edit',    'label'=>lang('edit'),
                            'events' => ['onClick' => "tabOpen('_blank', 'phreebooks/main/manager&rID=idTBD');"]],
                        'print'      => ['order'=>40,'icon'=>'print',   'label'=>lang('print'),
                            'events' => ['onClick'=>"var idx=jq('#$name').datagrid('getRowIndex', idTBD); var jID=jq('#$name').datagrid('getRows')[idx].journal_id; ('fitColumns', true); winOpen('phreeformOpen', 'phreeform/render/open&group={$gID[0]}:j'+jID+'&date=a&xfld=journal_main.id&xcr=equal&xmin=idTBD');"]],
                        'purchase'   => ['order'=>80,'icon'=>'purchase','label'=>lang('fill_purchase'),
                            'events' => ['onClick' => "tabOpen('_blank', 'phreebooks/main/manager&rID=idTBD&jID=6&bizAction=inv');"],
                            'display'=> "row.closed=='0' && (row.journal_id=='3' || row.journal_id=='4')"],
                        'sale'       => ['order'=>80,'icon'=>'sales',   'label'=>lang('fill_sale'),
                            'events' => ['onClick' => "tabOpen('_blank', 'phreebooks/main/manager&rID=idTBD&jID=12&bizAction=inv');"],
                            'display'=> "row.closed=='0' && (row.journal_id=='9' || row.journal_id=='10')"],
                        'payment'    => ['order'=>80,'icon'=>'payment', 'label'=>lang('payment'),
                            'events' => ['onClick' => "var cID=jq('#id').val(); tabOpen('_blank', 'phreebooks/main/manager&rID=0&jID=$jPmt&bizAction=inv&iID=idTBD&cID='+cID);"],
                            'display'=> "row.closed=='0' && (row.journal_id=='6' || row.journal_id=='7' || row.journal_id=='12' || row.journal_id=='13')"]]],
                'invoice_num'   => ['order'=>10, 'field'=>BIZUNO_DB_PREFIX."journal_main.invoice_num",'label'=>pullTableLabel("journal_main", 'invoice_num', $jID),
                    'attr'  => ['width'=>125, 'sortable'=>true, 'resizable'=>true]],
                'purch_order_id'=> ['order'=>20, 'field'=>BIZUNO_DB_PREFIX."journal_main.purch_order_id",'label'=>pullTableLabel("journal_main", 'purch_order_id', $jID),
                    'attr'  => ['width'=>150, 'sortable'=>true, 'resizable'=>true]],
                'post_date'     => ['order'=>30, 'field' => BIZUNO_DB_PREFIX."journal_main.post_date", 'format'=>'date','label' => pullTableLabel("journal_main", 'post_date', $jID),
                    'attr'  => ['width'=>120,'align'=>'center', 'sortable'=>true, 'resizable'=>true]],
                'closed_date'   => ['order'=>40, 'field'=>BIZUNO_DB_PREFIX."journal_main.closed_date",'label'=>in_array($jID, [6, 12]) ? lang('paid') : lang('closed'),
                    'attr'  => ['width'=>120,'align'=>'center', 'sortable'=>true, 'resizable'=>true],
                    'events'=> ['formatter'=>"function(value,row) { return (row.closed=='1' && value!='') ? formatDate(value) : (row.bal_due ? formatCurrency(row.bal_due, false) : ''); }"]],
                'total_amount'  => ['order'=>50, 'field'=>BIZUNO_DB_PREFIX."journal_main.total_amount",'label'=>lang('total'), 'attr'=>['width'=>100, 'align'=>'right', 'sortable'=>true, 'resizable'=>true],
                    'events'=> ['formatter'=>"function(value,row) { return (row.journal_id==7 || row.journal_id==13) ? formatCurrency(-value, false) : formatCurrency(value, false); }"]]]];
        if (in_array($GLOBALS['myDevice'], ['mobile','tablet'])) { $data['columns']['closed_date']['attr']['hidden'] = true; }
        return $data;
    }

    /**
     * Retrieves the details for the CRM tab in contacts
     * @param array $layout - working structure
     */
    public function crmDetails(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID  = clean('rID', 'integer', 'get');
        $type = dbGetValue(BIZUNO_DB_PREFIX."address_book", 'type', "id=$rID");
        $structure = [
            'contacts'    => dbLoadStructure(BIZUNO_DB_PREFIX."contacts",    $type),
            'address_book'=> dbLoadStructure(BIZUNO_DB_PREFIX."address_book",$type)];
        $structure['contacts']['short_name']['label']    = lang('contacts_short_name');
        $structure['contacts']['flex_field_1']['label']  = lang('contacts_flex_field_1', 'i');
        $structure['contacts']['account_number']['label']= 'Facebook ID';
        $structure['contacts']['gov_id_number']['label'] = 'Twitter ID';
        $data = ['type'=>'divHTML',
            'toolbars'=> [
                'tbAddressi'=> ['icons' => [
                    'savei' => ['order'=>10,'icon'=>'save','label'=>lang('save'),'hidden'=>$rID && $security >1?false:true,'events'=>['onClick'=>"divSubmit('contacts/main/saveAddress&aType=i&rID=$rID', 'crmDiv');"]],
                    'newi'  => ['order'=>20,'icon'=>'new', 'label'=>lang('new'), 'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressClear('i');"]],
                    'copyi' => ['order'=>30,'icon'=>'copy','label'=>lang('copy'),'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressCopy('m', 'i');"]]]]],
            'divs'   => [
                'general'=> ['order'=>10,'label'=>lang('general'),'type'=>'divs', 'divs'=>[
                    'crmDG'  => ['order'=>20, 'type'=>'datagrid','key'=>'dgCRM'],
                    'crmTB'  => ['order'=>40, 'type'=>'toolbar', 'key'=>'tbAddressi'],
                    'crmBOF' => ['order'=>41, 'type'=>'html',    'html'=>'<div id="crmDiv">'],
                    'crmMain'=> ['order'=>60, 'type'=>'html',    'html'=>$this->crmXFields($structure['contacts'])],
                    'crmAddr'=> ['order'=>70, 'type'=>'address', 'content'=>$structure['address_book'],'settings'=>['suffix'=>'i','clear'=>false]],
                    'crmEOF' => ['order'=>81, 'type'=>'html',    'html'=>'</div>']]]],
            'datagrid'=>['dgCRM'=>$this->dgContacts('crm_main', 'i', $security, $rID)]];
        // now some adjustments
        $data['datagrid']['dgCRM']['events']['onDblClickRow']  = "function(rowIndex, rowData){ crmDetail(rowData.id, 'i'); }";
        $data['datagrid']['dgCRM']['footnotes']['crm_dg_notes']= $this->lang['crm_dg_notes'];
        $data['datagrid']['dgCRM']['attr']['url']= BIZUNO_AJAX."&p=contacts/main/managerRows&type=i&ref=$rID";
        unset($data['datagrid']['dgCRM']['source']['actions']['newContact']);
        unset($data['datagrid']['dgCRM']['columns']['action']['actions']['download']);
        unset($data['datagrid']['dgCRM']['columns']['action']['actions']['chart']);
        $layout = array_replace_recursive($layout, $data);
    }

    public function getTabAddress(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID  = clean('rID', 'integer','get');
        $type = clean('type','char',   'get');
        $cType= dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'type', "id=$rID");
        $structure = dbLoadStructure(BIZUNO_DB_PREFIX."address_book",$type);
        $data = ['type'=>'divHTML',
            'toolbars'=> [
                'tbAddr'=> ['icons' => [
                    'save' => ['order'=>10,'icon'=>'save','hidden'=>$rID && $security >1?false:true,'events'=>['onClick'=>"divSubmit('contacts/main/saveAddress&aType=$type&rID=$rID', 'tabAddr$type');"]],
                    'new'  => ['order'=>20,'icon'=>'new', 'hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressClear('$type');"]],
                    'copy' => ['order'=>30,'icon'=>'copy','hidden'=>$security >1?false:true,        'events'=>['onClick'=>"addressCopy('m', '$type');"]]]]],
            'divs'   => [
                'general'=> ['order'=>10,'label'=>lang('general'), 'type'=>'divs', 'divs'=>[
                    'dgAddr' => ['order'=>20, 'type'=>'datagrid','key'=>'dgAddress'],
                    'tbAddr' => ['order'=>40, 'type'=>'toolbar', 'key'=>'tbAddr'],
                    'BoF'    => ['order'=>41, 'type'=>'html',    'html'=>'<div id="tabAddr'.$type.'">'],
                    'fldAddr'=> ['order'=>70, 'type'=>'address', 'content'=>$structure,'settings'=>['suffix'=>$type,'clear'=>false]],
                    'EoF'    => ['order'=>91, 'type'=>'html','html'=>'</div>']]]],
            'datagrid'=>['dgAddress'=>$this->dgAddress($rID, $cType, $type, $security)]];
        $layout = array_replace_recursive($layout, $data);
    }

    public function getTabNotes(&$layout=[])
    {
        if (!$security = validateSecurity($this->securityModule, $this->securityMenu, 1)) { return; }
        $rID   = clean('rID', 'integer','get');
        $type  = dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'type', "id=$rID");
        $notes = dbGetValue(BIZUNO_DB_PREFIX.'address_book', 'notes',"ref_id=$rID AND type='m'");
        $fldLog= [
            'crm_date'  => ['order'=>10,'label'=>lang('date'),  'break'=>true,'attr'=>['type'=>'date', 'value'=>viewDate(date('Y-m-d'))]],
            'crm_rep_id'=> ['order'=>20,'label'=>lang('contacts_log_entered_by'),'break'=>true,'values'=>viewRoleDropdown(),'attr'=>['type'=>'select','value'=>getUserCache('profile', 'contact_id', false, '0')]],
            'crm_action'=> ['order'=>30,'label'=>lang('action'),'break'=>true,'values'=>viewKeyDropdown(getModuleCache('contacts', 'crm_actions'), true),'attr'=>['type'=>'select']],
            'crm_note'  => ['order'=>40,'label'=>'','break'=>true,'attr'=>['type'=>'textarea','rows'=>5]]];
        $data  = ['type'=>'divHTML',
            'toolbars'=> [
                'tbNote'=> ['icons'=>['save'=>['order'=>10,'icon'=>'save','hidden'=>$rID && $security >1?false:true,'events'=>['onClick'=>"divSubmit('contacts/main/saveNotes&rID=$rID', 'divNotes');"]]]],
                'tbLog' => ['icons'=>['save'=>['order'=>10,'icon'=>'save','hidden'=>$rID && $security >1?false:true,'events'=>['onClick'=>"divSubmit('contacts/main/saveLog&rID=$rID', 'divLog');"]]]]],
            'divs' => ['divDetail'=>['order'=>50,'type'=>'divs','divs'=>[
                'fldNotes'=> ['order'=>20,'label'=>lang('notes'),'type'=>'divs','classes'=>['blockView'],'attr'=>['id'=>'divNotes'],'divs'=>[
                    'tbNote' => ['order'=>20,'type'=>'toolbar','key'=>'tbNote'],
                    'fldNote'=> ['order'=>50,'type'=>'fields', 'fields'=>["notesm"=>['attr'=>['type'=>'textarea', 'value'=>$notes]]]]]],
                'divLog'  => ['order'=>80,'label'=>lang('contacts_log'),'type'=>'divs','classes'=>['blockView'],'attr'=>['id'=>'divLog'],'divs'=>[
                    'tbLog'  => ['order'=>20,'type'=>'toolbar','key'=>'tbLog'],
                    'fldLog' => ['order'=>50,'type'=>'fields','fields'=>$fldLog],
                    'dgLog'  => ['order'=>80,'type'=>'datagrid','key'=>'dgLog']]]]]],
            'datagrid'=>['dgLog'=>$this->dgLog('dgLog', $rID, $security)]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Adds the extra fields to the address block for CRM records
     * @param array $data - working data with CRM information
     * @return string - table html with additional CRM fields for render
     */
    private function crmXFields($structure)
    {
        return '<table style="border-collapse:collapse;width:100%;">
    <tr>
        <td>'.html5('short_namei',    $structure['short_name']).html5('idi', $structure['id'])."</td>
        <td>".html5('flex_field_1i',  $structure['flex_field_1'])."</td>
    </tr>
    <tr>
        <td>".html5('contact_firsti', $structure['contact_first'])."</td>
        <td>".html5('contact_lasti',  $structure['contact_last'])."</td>
    </tr>
    <tr>
        <td>".html5('account_numberi',$structure['account_number'])."</td>
        <td>".html5('gov_id_numberi', $structure['gov_id_number'])."</td>
    </tr>
</table>\n";
    }

    /**
     * builds the history list of orders used on contact history tab
     * @param array $layout - current working structure
     * @return updated $layout
     */
    public function managerRowsHistory(&$layout=[])
    {
        $jID = clean('jID', 'integer', 'get');
        $rID = clean('rID', 'integer', 'get');
        $structure = $this->dgHistory('dgHistory'.$jID, $jID, $rID);
        $layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
    }

    /**
     * Gets the rows for the contacts log datagrid
     * @param array $layout - working structure
     * @return modified $layout
     */
    public function managerRowsLog(&$layout=[])
    {
        $rID = clean('rID', 'integer', 'get');
        $structure = $this->dgLog('dgLog', $rID);
        $layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
    }

    /**
     * The method deletes a record from the contacts_log table
     * @param integer $rID - typically a $_GET variable but can also be passed to the function in an array
     * @return array with dbAction and content to remove entry from datagrid
     */
    public function deleteLog(&$layout=[])
    {
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd("Bad ID submitted!"); }
        msgLog(lang('contacts_log').' '.lang('delete')." - ($rID)");
        $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval','actionData'=>"jq('#dgLog').datagrid('reload');"],
            'dbAction'=> [BIZUNO_DB_PREFIX."contacts_log"=>"DELETE FROM ".BIZUNO_DB_PREFIX."contacts_log WHERE id=$rID"]]);
    }

    /**
     * Builds the datagrid structure for the contacts log
     * @param string $name - HTML datagrid field name
     * @param integer $rID - database contact record id
     * @param integer $security - users approved security level
     * @return array - datagrid structure
     */
    private function dgLog($name, $rID=0, $security=0)
    {
        $rows   = clean('rows', ['format'=>'integer','default'=>10], 'post');
        $page   = clean('page', ['format'=>'integer','default'=> 1], 'post');
        $sort   = clean('sort', ['format'=>'text',   'default'=>'log_date'],'post');
        $order  = clean('order',['format'=>'text',   'default'=>'desc'],    'post');
        return ['id'=>$name, 'rows'=>$rows, 'page'=>$page,
            'attr'   => ['nowrap'=>false, 'idField'=>'id', 'url'=>BIZUNO_AJAX."&p=contacts/main/managerRowsLog&rID=$rID"],
            'source' => [
                'tables' => ['contacts_log'=>['table'=>BIZUNO_DB_PREFIX."contacts_log"]],
                'filters'=> ['rID'=> ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."contacts_log.contact_id='$rID'"]],
                'sort'   => ['s0' => ['order'=>10,'field'=>"$sort $order"]]],
            'columns' => [
                'id' => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."contacts_log.id",'attr'=>['hidden'=>true]],
                'action' => ['order'=>1,'events'=>['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
                    'actions'=> [
                        'logTrash' => ['order'=>80,'icon'=>'trash','label'=>lang('delete'),'hidden'=>$security==4?false:true,
                            'events' => ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('contacts/main/deleteLog', idTBD);"]]]],
                'log_date'  => ['order'=>10,'field'=>"log_date",  'label'=>lang('date'), 'format'=>'date', 'attr'=>['width'=> 50, 'resizable'=>true]],
                'entered_by'=> ['order'=>20,'field'=>"entered_by",'label'=>lang('contacts_log_entered_by'),'attr'=>['width'=> 50, 'resizable'=>true], 'format'=>'contactID'],
                'action'    => ['order'=>30,'field'=>"action",    'label'=>lang('action'),                 'attr'=>['width'=> 50, 'resizable'=>true], 'format'=>'cache:contacts:crm_actions'],
                'notes'     => ['order'=>40,'field'=>"notes",     'label'=>lang('notes'),                  'attr'=>['width'=>200, 'resizable'=>true]]]];
    }

    /**
     * Returns form submitted values only if set and not null
     * @param array $row - Row from db or submitted form
     * @param array $testList - List of fields to test against
     * @return true if ALL the fields exist and do not have a null or zero value, false otherwise
     */
    function isBlankForm($row, $testList=[])
    {
        foreach ($testList as $field) {
            if (isset($row[$field]) && $row[$field]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets translated text for a specified encoded term passed through ajax
     * @param array $layout - current working structure
     * @return array - modified $layout
     */
    public function termsText(&$layout=[])
    {
        $terms_encoded = clean('enc', ['format'=>'text','default'=>'0'], 'get');
        msgDebug("\n Received encoded terms = $terms_encoded");
        $layout = array_replace_recursive($layout, ['content'=>['text' => viewTerms($terms_encoded)]]);
    }

    /**
     * Builds the editor for contact payment terms
     * @param array $layout - current working structure
     * @param char $defType - contact type
     */
    public function editTerms(&$layout=[], $defType='c')
    {
        $call_back= clean('callBack',['format'=>'text','default'=>'terms'], 'get');
        $data = ['type'=>'popup','title'=>lang('terms'),'attr'=>['id'=>'winTerms'],
            'call_back'=> $call_back,
            'toolbars' => ['tbTerms'=>['icons'=>['next'=>['order'=>20,'events'=>['onClick'=>"termsSave();"]]]]],
            'divs'     => [
                'toolbar'     => ['order'=>80,'type'=>'toolbar','key'=>'tbTerms'],
                'window_terms'=> ['order'=>50,'type'=>'fields','fields'=>$this->getTermsDiv($defType)]],
            'jsBody'   => ['termsInit' => "function termsSave() {
    var type  = jq('#terms_type:checked').val();
    var enc   = type+':'+jq('#terms_disc').val()+':'+jq('#terms_early').val()+':';
    enc += (type=='4' ? jq('#terms_date').val() : jq('#terms_net').val())+':'+cleanCurrency(jq('#credit_limit').val());
    jq('#terms').val(enc);
    jq.ajax({
        url:     '".BIZUNO_AJAX."&p=contacts/main/termsText&enc='+enc,
        success: function(json) { processJson(json); if (json.text) { bizTextSet('terms_text', json.text); bizWindowClose('winTerms'); } }
    });
}"]];
        $layout = array_replace_recursive($layout, $data);
    }

    private function getTermsDiv($defType='c')
    {
        $type   = clean('type',['format'=>'char',   'default'=>$defType],'get');
        $cID    = clean('id',  ['format'=>'integer','default'=>0],       'get');
        $encoded= clean('data',['format'=>'text',   'default'=>false],   'get');
        if     (!$encoded && $cID)      { $encoded = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'terms', "id=$cID"); }
        elseif (!$encoded && $type=='v'){ $encoded = getModuleCache('phreebooks', 'settings', 'vendors', 'terms'); }
        elseif (!$encoded)              { $encoded = getModuleCache('phreebooks', 'settings', 'customers', 'terms'); }
        $terms  = explode(':', $encoded);
        $defNET = isset($terms[3]) && $terms[0]==3 ? $terms[3] : '30';
        $defDOM = isset($terms[3]) && $terms[0]==4 ? clean($terms[3], 'date') : date('Y-m-d');
        $fields = [
            'terms_type'  => ['position'=>'after',      'attr'=>['type'=>'radio','value'=>$terms[0]]],
            'terms_disc'  => ['options'=>['width'=>40], 'attr'=>['value'=>isset($terms[1]) ? $terms[1] : '0', 'maxlength'=>'3']],
            'terms_early' => ['options'=>['width'=>40], 'attr'=>['value'=>isset($terms[2]) ? $terms[2] : '0', 'maxlength'=>'3']],
            'terms_net'   => ['options'=>['width'=>40], 'attr'=>['value'=>$defNET,'maxlength'=>'3']],
            'terms_date'  => ['order'=>61,'break'=>true,'attr'=>['type'=>'date', 'value'=>$defDOM]],
            'credit_limit'=> ['label'=>lang('contacts_terms_credit_limit'),'position'=>'after','styles'=>['text-align'=>'right'],'attr'=>['format' =>'currency','value'=>isset($terms[4])?$terms[4]:'1000']]];
        $custom = ' - '.sprintf(lang('contacts_terms_discount'), html5('terms_disc', $fields['terms_disc']), html5('terms_early', $fields['terms_early'])).' '.sprintf(lang('contacts_terms_net'), html5('terms_net',$fields['terms_net']));
        $output = [
            'radio0'    => ['order'=>10,'break'=>true,'label'=>lang('contacts_terms_default'),'attr'=>['type'=>'radio','id'=>'terms_type','name'=>'terms_type','value'=>0,'checked'=>$terms[0]==0?true:false]],
            'r0Text'    => ['order'=>11,'break'=>true,'html'=>' ['.viewTerms('0', false, $terms[0]).']','attr'=>['type'=>'raw']],
            'radio3'    => ['order'=>20,'label'=>lang('contacts_terms_custom'),               'attr'=>['type'=>'radio','id'=>'terms_type','name'=>'terms_type','value'=>3,'checked'=>$terms[0]==3?true:false]],
            'r1Disc'    => ['order'=>21,'break'=>true,'html'=>$custom,'attr'=>['type'=>'raw']],
            'radio6'    => ['order'=>30,'break'=>true,'label'=>lang('contacts_terms_now'),    'attr'=>['type'=>'radio','id'=>'terms_type','name'=>'terms_type','value'=>6,'checked'=>$terms[0]==6?true:false]],
            'radio2'    => ['order'=>40,'break'=>true,'label'=>lang('contacts_terms_prepaid'),'attr'=>['type'=>'radio','id'=>'terms_type','name'=>'terms_type','value'=>2,'checked'=>$terms[0]==2?true:false]],
            'radio1'    => ['order'=>50,'break'=>true,'label'=>lang('contacts_terms_cod'),    'attr'=>['type'=>'radio','id'=>'terms_type','name'=>'terms_type','value'=>1,'checked'=>$terms[0]==1?true:false]],
            'radio4'    => ['order'=>60,'label'=>lang('contacts_terms_dom'),                  'attr'=>['type'=>'radio','id'=>'terms_type','name'=>'terms_type','value'=>4,'checked'=>$terms[0]==4?true:false]],
            'terms_date'=> $fields['terms_date'],
            'radio5'    => ['order'=>70,'break'=>true,'label'=>lang('contacts_terms_eom'),    'attr'=>['type'=>'radio','id'=>'terms_type','name'=>'terms_type','value'=>5,'checked'=>$terms[0]==5?true:false]],
            'credit'    => $fields['credit_limit']];
        return $output;
    }
}
