<?php
/*
 * Methods related to the chart of accounts used in PhreeBooks
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
 * @version    4.x Last Update: 2020-05-27
 * @filesource /lib/controller/module/phreebooks/chart.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/functions.php", 'phreebooksProcess', 'function');

class phreebooksChart
{
    public $moduleID = 'phreebooks';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }

    /**
     * Entry point for maintaining general ledger chart of accounts
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $coa_blocked = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'id') ? true : false;
        $charts = [];
        $fields = [
            'sel_coa'     => ['values'=>$charts,'attr'=>['type'=>'select','size'=>10]],
            'file_coa'    => ['label'=>$this->lang['coa_upload_file'], 'attr'=>['type'=>'file']],
            'btn_coa_pre' => ['icon'=>'preview','size'=>'large','events'=>['onClick'=>"winOpen('popupGL', 'phreebooks/chart/preview&chart='+bizSelGet('sel_coa'), 800, 600);"]],
            'btn_coa_imp' => ['icon'=>'import', 'size'=>'large','events'=>['onClick'=>"if (confirm('".$this->lang['msg_gl_replace_confirm']."')) jsonAction('phreebooks/chart/import', 0, bizSelGet('sel_coa'));"]],
            'btn_coa_upl' => ['attr'=>['type'=>'button', 'value'=>$this->lang['btn_coa_upload']], 'events'=>['onClick'=>"if (confirm('".$this->lang['msg_gl_replace_confirm']."')) jq('#frmGlUpload').submit();"]]];
        if (!$coa_blocked) { $fields['sel_coa']['values'] = localeLoadCharts(); }
        $jsHead = "var dgChartData = jq.extend(true, {}, bizDefaults.glAccounts);
function chartRefresh() {
    jq('#accGL').accordion('select', 0);
    jq('#dgChart').datagrid('loadData', jq.extend(true, {}, bizDefaults.glAccounts));
}";
        $data = ['type'=>'divHTML',
            'divs'     => ['gl'=>['order'=>50,'type'=>'accordion','key'=>"accGL"]],
            'accordion'=> ['accGL'=>['divs'=>[
                'divGLManager'=> ['order'=>30,'label'=>lang('phreebooks_chart_of_accts'),'type'=>'divs','divs'=>[
                    'selCOA'  => ['order'=>10,'label'=>$this->lang['coa_import_title'],  'type'=>'divs','divs'=>[
                        'desc'   => ['order'=>10,'type'=>'html',  'html'=>"<p>".$this->lang['coa_import_desc']."</p>"],
                        'formBOF'=> ['order'=>15,'type'=>'form',  'key' =>'frmGlUpload'],
                        'body'   => ['order'=>50,'type'=>'fields','keys'=>array_keys($fields)],
                        'formEOF'=> ['order'=>95,'type'=>'html',  'html'=>"</form>"]]],
                    'dgChart' => ['order'=>50,'type'=>'datagrid', 'key' =>'dgChart']]],
                'divGLDetail' => ['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'']]]],
            'forms'    => ['frmGlUpload'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/chart/upload"]]],
            'datagrid' => ['dgChart'=>$this->dgChart('dgChart', $security)],
            'fields'   => $fields,
            'jsHead'   => ['chart' => $jsHead], // clone object
            'jsReady'  => ['init'=>"jq('#dgChart').datagrid({data:dgChartData}).datagrid('clientPaging');", 'selCOA'=> !$coa_blocked ? "ajaxForm('frmGlUpload');" : '']];
        if ($coa_blocked) {
            $data['accordion']['accGL']['divs']['divGLManager']['divs']['selCOA'] = ['order'=>10,'type'=>'html','html'=>"<p>".$this->lang['coa_import_blocked']."</p>"];
        }
        $layout = array_replace_recursive($layout, $data);
    }

    private function getViewMgr($coa_blocked)
    {
        return $output;
    }

    /**
     * structure to review a sample chart of accounts, only visible until first GL entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function preview(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $chart   = clean('chart', 'path', 'get');
        if (!file_exists(BIZUNO_LIB.$chart)) { return msgAdd("Bad path to chart!"); }
        $accounts= parseXMLstring(file_get_contents(BIZUNO_LIB.$chart));
        if (is_object($accounts->account)) { $accounts->account = [$accounts->account]; } // in case of only one chart entry
        $output  = [];
        if (is_array($accounts->account)) { foreach ($accounts->account as $row) {
            $output[] = ['id'=>$row->id, 'type'=>lang("gl_acct_type_".trim($row->type)), 'title'=>$row->title,
                'heading'=> isset($row->heading_only)    && $row->heading_only    ? lang('yes')           : '',
                'primary'=> isset($row->primary_acct_id) && $row->primary_acct_id ? $row->primary_acct_id : ''];
        } }
        $jsReady = "var winChart = ".json_encode($output).";
jq('#dgPopupGL').datagrid({ pagination:false,data:winChart,columns:[[{field:'id',title:'".jsLang('gl_account')."',width: 50},{field:'type',title:'" .jsLang('type')."',width:100},{field:'title',title:'".jsLang('title')."',width:200} ]] });";
        $layout = array_replace_recursive($layout, ['type'=>'page', 'title'=>$this->lang['btn_coa_preview'],
            'divs'=>['divLabel'=>['order'=>60,'type'=>'html','html'=>"<table id=\"dgPopupGL\"></table>"]],
            'jsReady' => ['init'=>$jsReady]]);
    }

    /**
     * Structure for chart of accounts editor
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $rID = clean('rID', ['format'=>'text','default'=>'0'], 'get'); // default to new gl account
        if ($rID) { $val = getModuleCache('phreebooks', 'chart', 'accounts')[$rID]; }
        $fields = [
            'gl_previous'=> ['order'=>10,'col'=>1,'break'=>true,'label'=>lang('gl_account'),'attr'=>['type'=>$rID?'text':'hidden','readonly'=>'readonly', 'value'=>isset($val['id'])?$val['id']:'']],
            'gl_desc'    => ['order'=>20,'col'=>1,'break'=>true,'label'=>lang('title'),     'attr'=>['size'=>60, 'value'=>isset($val['title'])?$val['title']:'']],
            'gl_inactive'=> ['order'=>30,'col'=>1,'break'=>true,'label'=>lang('inactive'),  'attr'=>['type'=>'checkbox','checked'=>!empty($val['inactive'])?true:false]],
            'gl_type'    => ['order'=>40,'col'=>1,'break'=>true,'options'=>['width'=>250],'label'=>lang('type'),'values'=>selGLTypes(),'attr'=>['type'=>'select','value'=>isset($val['type'])?$val['type']:'']],
//          'gl_cur'     => ['order'=>50,'col'=>1,'break'=>true,'label'=>lang('currency'),  'attr'=>['type'=>'selCurrency','value'=>isset($val['cur']) ?$val['cur'] :'']],
            'gl_account' => ['order'=>10,'col'=>$rID?2:1,'break'=>true,'label'=>$this->lang['new_gl_account']],
            'gl_header'  => ['order'=>20,'col'=>2,'break'=>true,'label'=>lang('heading'),'attr'=>['type'=>'checkbox','checked'=>!empty($val['heading'])?true:false]],
            'gl_parent'  => ['order'=>30,'col'=>2,'break'=>true,'label'=>$this->lang['primary_gl_acct'],'heading'=>true,'attr'=>['type'=>'ledger','value'=>isset($val['parent'])?$val['parent']:'']],
            'gl_default' => ['order'=>40,'col'=>2,'break'=>true,'label'=>lang('default'),   'attr'=>['type'=>'checkbox','checked'=>!empty($val['default'])?true:false]]];
        $data = ['type'=>'divHTML',
            'divs'    => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key' =>'tbGL'],
                'formBOF'=> ['order'=>15,'type'=>'form',   'key' =>'frmGLEdit'],
                'body'   => ['order'=>50,'type'=>'fields', 'keys'=>array_keys($fields)],
                'formEOF'=> ['order'=>95,'type'=>'html',   'html'=>"</form>"]],
            'toolbars'=> ['tbGL'=>['icons'=>[
                "glSave"=> ['order'=>10,'icon'=>'save','label'=>lang('save'),'events'=>['onClick'=>"jq('#frmGLEdit').submit();"]],
                "glNew" => ['order'=>20,'icon'=>'new', 'label'=>lang('new'), 'events'=>['onClick'=>"accordionEdit('accGL', 'dgChart', 'divGLDetail', '".lang('details')."', 'phreebooks/chart/edit', 0);"]]]]],
            'forms'   => ['frmGLEdit'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/chart/save"]]],
            'fields'  => $fields,
            'jsBody'  => ["ajaxForm('frmGLEdit');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Structure for saving user changes of the chart of accounts
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $acct    = clean('gl_account', 'text',   'post'); // 1234
        $inactive= clean('gl_inactive','boolean','post') ? true : false; // on
        $previous= clean('gl_previous','text',   'post'); // TBD
        $desc    = clean('gl_desc',    'text',   'post'); // asdf
        $type    = clean('gl_type',    'integer','post'); // 8
        $heading = clean('gl_header',  'boolean','post'); // on
        $parent  = clean('gl_parent',  'text',   'post'); // 1150
        $isEdit  = $previous ? true : false;
        if (!$acct && !$isEdit)     { return msgAdd($this->lang['chart_save_01']); }
        if (!$desc)                 { return msgAdd($this->lang['chart_save_02']); }
        If (!$acct && $previous)    { $acct = $previous; } // not an account # change, set it to what it was
        $glAccounts = getModuleCache('phreebooks', 'chart', 'accounts');
        $used = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'id', "gl_account='$acct'");
        if ($used && !$isEdit)      { return msgAdd($this->lang['chart_save_03']); }
        if ($type == 44 && !$isEdit){ foreach ($glAccounts as $row) { if ($row['type'] == 44) { return msgAdd($this->lang['chart_save_04']); } } }
        if ($used && $heading)      { return msgAdd($this->lang['chart_save_05']); }
        if ($parent && empty($glAccounts[$parent]['heading'])) { return msgAdd(sprintf($this->lang['chart_save_06'], $parent)); }
        $glAccounts["$acct"] = ['id'=>"$acct", 'title'=>$desc, 'type'=>$type, 'cur'=>getDefaultCurrency()];
        $glAccounts["$acct"]['inactive']= $inactive? '1' : '0';
        $glAccounts["$acct"]['heading'] = $heading ? '1' : '0';
        $glAccounts["$acct"]['parent']  = $parent  ? $parent : '';
        if ($isEdit && ($previous <> $acct)) { // update journal and all affected tables
            dbWrite(BIZUNO_DB_PREFIX."contacts",       ['gl_account'=>$acct], 'update', "gl_account='$previous'");
            dbWrite(BIZUNO_DB_PREFIX."inventory",      ['gl_sales'  =>$acct], 'update', "gl_sales='$previous'");
            dbWrite(BIZUNO_DB_PREFIX."inventory",      ['gl_inv'    =>$acct], 'update', "gl_inv='$previous'");
            dbWrite(BIZUNO_DB_PREFIX."inventory",      ['gl_cogs'   =>$acct], 'update', "gl_cogs='$previous'");
            dbWrite(BIZUNO_DB_PREFIX."journal_history",['gl_account'=>$acct, 'gl_type'=>$type], 'update', "gl_account='$previous'");
            dbWrite(BIZUNO_DB_PREFIX."journal_item",   ['gl_account'=>$acct], 'update', "gl_account='$previous'");
            dbWrite(BIZUNO_DB_PREFIX."journal_main",   ['gl_acct_id'=>$acct], 'update', "gl_acct_id='$previous'");
            unset($glAccounts[$previous]);
        } elseif ($isEdit && $glAccounts[$previous]['type'] <> $type) {
            dbWrite(BIZUNO_DB_PREFIX."journal_history",['gl_type'=>$type],    'update', "gl_account='$previous'");
        }
        ksort($glAccounts, SORT_STRING);
        setModuleCache('phreebooks', 'chart', 'accounts', $glAccounts);
        $this->checkDefault($acct, $type, getDefaultCurrency());
        if (!$isEdit) { insertChartOfAccountsHistory($acct, $type); } // build the journal_history entries
        // send confirm and reload browser cache (and page since datagrid doesn't reload properly)
        msgLog(lang('gl_account')." - ".lang('save'));
        msgAdd(lang('gl_account')." - ".lang('save'), 'success');
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"reloadSessionStorage(chartRefresh);"]]);
    }

    /**
     * Structure for deleting a chart of accounts record.
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $rID = clean('rID', 'text', 'get');
        if (!$rID) { return msgAdd(lang('bad_data')); }
        // Can't delete gl account if it was used in a journal entry
        $glAccounts= getModuleCache('phreebooks', 'chart', 'accounts');
        $glRecord  = $glAccounts[$rID];
        if (dbGetValue(BIZUNO_DB_PREFIX."journal_main",'id',"gl_acct_id='$rID'")) { return msgAdd(sprintf($this->lang['err_gl_chart_delete'], 'journal_main')); }
        if (dbGetValue(BIZUNO_DB_PREFIX."journal_item",'id',"gl_account='$rID'")) { return msgAdd(sprintf($this->lang['err_gl_chart_delete'], 'journal_item')); }
        if (dbGetValue(BIZUNO_DB_PREFIX."contacts",    'id',"gl_account='$rID'")) { return msgAdd(sprintf($this->lang['err_gl_chart_delete'], 'contacts')); }
        if (dbGetValue(BIZUNO_DB_PREFIX."inventory",   'id',"gl_sales='$rID' OR gl_inv='$rID' OR gl_cogs='$rID'")) { return msgAdd(sprintf($this->lang['err_gl_chart_delete'], 'inventory')); }
        if (!getModuleCache('phreebooks', 'chart', 'defaults', getDefaultCurrency())[44]) { return msgAdd("Sorry, you cannot delete your retained earnings account."); }
        $maxPeriod = dbGetValue(BIZUNO_DB_PREFIX."journal_history", 'MAX(period) as period', "", false);
        if (dbGetValue(BIZUNO_DB_PREFIX."journal_history", "beginning_balance", "gl_account='$rID' AND period=$maxPeriod")) { return msgAdd("The GL account cannot be deleted if the last fiscal year ending balance is not zero!"); }
        unset($glAccounts[$rID]);
        // remove acct from journal_history table
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_history WHERE gl_account='$rID'");
        setModuleCache('phreebooks', 'chart', 'accounts', $glAccounts);
        msgLog(lang('phreebooks_chart_of_accts').' - '.lang('delete')." (".$glRecord['id'].') '.$glRecord['title']);
        msgAdd(lang('phreebooks_chart_of_accts').' - '.lang('delete')." (".$glRecord['id'].') '.$glRecord['title'], 'success');
        $layout = array_replace_recursive($layout, ['content'=>  ['action'=>'eval','actionData'=>"reloadSessionStorage(chartRefresh);"]]);
    }

    /**
     * Imports the user selected GL chart of accounts
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function import(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)){ return; }
        $chart = clean('data', 'path', 'get');
        if (dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'id'))  { return msgAdd($this->lang['coa_import_blocked']); }
        if (!$this->chartInstall(BIZUNO_LIB.$chart))            { return; }
        dbGetResult("TRUNCATE ".BIZUNO_DB_PREFIX."journal_history");
        buildChartOfAccountsHistory();
        msgAdd($this->lang['msg_gl_replace_success'], 'success');
        msgLog($this->lang['msg_gl_replace_success']);
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"reloadSessionStorage(chartRefresh);"]]);
    }

    /**
     * Uploads a chart of accounts xml file to import
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function upload(&$layout)
    {
        global $io;
        if (!$security = validateSecurity('bizuno', 'admin', 4))  { return; }
        if (!$io->validateUpload('file_coa', 'xml', 'xml', true)) { return; }
        $chart = $_FILES['file_coa']['tmp_name'];
        if (dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'id'))    { return msgAdd($this->lang['coa_import_blocked']); }
        if (!$this->chartInstall($chart))                         { return; }
        dbGetResult("TRUNCATE ".BIZUNO_DB_PREFIX."journal_history");
        buildChartOfAccountsHistory();
        msgAdd($this->lang['msg_gl_replace_success'], 'success');
        msgLog($this->lang['msg_gl_replace_success']);
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"reloadSessionStorage(chartRefresh);"]]);
    }

    /**
     * Installs a chart of accounts, only valid during Bizuno installation and changing chart of accounts
     * @param string $chart - relative path to chart to install
     * @return user message with status
     */
    public function chartInstall($chart)
    {
        if (!dbTableExists(BIZUNO_DB_PREFIX."journal_main") || dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'id')) { return msgAdd(lang('coa_import_blocked')); }
        msgDebug("\nTrying to load chart at path: $chart");
        if (!file_exists($chart)) { return msgAdd("Bad path to chart!", 'trap'); }
        $accounts = parseXMLstring(file_get_contents($chart));
        if (empty($accounts)) { return msgAdd('Invalid chart of accounts. Is the XML properly formed?'); }
        if (is_object($accounts->account)) { $accounts->account = [$accounts->account]; } // in case of only one chart entry
        $output = [];
        $defRE  = '';
        $curISO = getDefaultCurrency();
        if (is_array($accounts->account)) { foreach ($accounts->account as $row) {
            $tmp = ['id'=>trim($row->id), 'type'=>trim($row->type), 'cur'=>$curISO, 'title'=>trim($row->title)];
            if (!empty($row->heading_only))   { $tmp['heading']= 1; }
            if (!empty($row->primary_acct_id)){ $tmp['parent'] = $row->primary_acct_id; }
            $output['accounts'][$row->id] = $tmp;
            if ($row->type == 44) { $defRE = $row->id; } // keep the retained earnings account
        } }
        if (is_array($accounts->defaults->type)) { foreach ($accounts->defaults->type as $row) { // set the defaults
            $typeID = trim($row->id);
            $output['defaults'][$curISO][$typeID] = $typeID==44 ? $defRE : trim($row->account);
        } }
        setModuleCache('phreebooks', 'chart', false, $output);
        return true;
    }

    /**
     * Grid structure for chart of accounts
     * @param string $name - DOM field name
     * @param integer $security - users security level to control visibility
     * @return array - structure of the grid
     */
    private function dgChart($name, $security=0)
    {
        return ['id'   => $name,
            'attr'     => ['toolbar'=>"#{$name}Bar",'idField'=>'id','remoteFilter'=>false,'remoteSort'=>false],
            'events'   => [//'data'=> "dgChartData",
                'onDblClickRow'=> "function(rowIndex, rowData) { accordionEdit('accGL', 'dgChart', 'divGLDetail', '".lang('details')."', 'phreebooks/chart/edit', rowData.id); }",
                'rowStyler'    => "function(index, row) { if (row.default=='1') { return {class:'row-default'}; }}"],
            'source'   => ['actions'=>['newGL'=>['order'=>10,'icon'=>'new','events'=>['onClick'=>"accordionEdit('accGL', 'dgChart', 'divGLDetail', '".jsLang('details')."', 'phreebooks/chart/edit', 0);"]]]],
            'footnotes'=> ['codes'=>lang('color_codes').': <span class="row-inactive">'.lang('inactive').'</span>'],
            'columns'  => [
                'inactive'=> ['order'=> 0,'attr'=>['hidden'=>true]],
                'default' => ['order'=> 0,'attr'=>['hidden'=>true]],
                'action'  => ['order'=> 1,'label'=>lang('action'),'events'=>['formatter'=>$name.'Formatter'],
                    'actions'    => ['glEdit' => ['order'=>30,'icon'=>'edit','events'=>['onClick'=>"accordionEdit('accGL', 'dgChart', 'divGLDetail', '".jsLang('details')."', 'phreebooks/chart/edit', idTBD);"]],
                        'glTrash'=> ['order'=>90,'icon'=>'trash','size'=>'small','hidden'=> $security>3?false:true,
                            'events'=> ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreebooks/chart/delete', 'idTBD');"]]]],
                'id'      => ['order'=>20,'label'=>lang('gl_account'),'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true],
                    'events'=>['styler'=>"function(value, row) { if (row.inactive==1) return {class:'row-inactive'}; }",'sorter'=>"function(a,b){return parseInt(a) > parseInt(b) ? 1 : -1;}"]],
                'title'   => ['order'=>30,'label'=>lang('title'),     'attr'=>['width'=>200,'sortable'=>true,'resizable'=>true]],
                'type'    => ['order'=>40,'label'=>lang('type'),      'attr'=>['width'=>150,'sortable'=>true,'resizable'=>true]],
                'cur'     => ['order'=>50,'label'=>lang('currency'),  'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true,'align'=>'center']],
                'heading' => ['order'=>60,'label'=>lang('heading'),   'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true,'align'=>'center'],
                    'events'=>['formatter'=>"function(value,row){ return value=='1' ? '".jsLang('yes')."' : ''; }"]],
                'parent'  => ['order'=>70,'label'=>$this->lang['primary_gl_acct'],'attr'=>['width'=> 80,'sortable'=>true,'align'=>'center'],
                    'events'=>['formatter'=>"function(value,row){ return value ? value : ''; }"]]]];
    }

    /**
     * Checks and repairs GL defaults to make sure they exist and are set to an active account
     * @param string $acct - GL account value
     * @param integer $type - GL account type
     * @param string $currency - ISO Currency value, defaults to user cache currency
     */
    private function checkDefault($acct, $type, $currency=false)
    {
        $default = clean('gl_default', 'boolean','post') ? true : false;
        if (empty($currency)) { $currency = getDefaultCurrency(); }
        $glDefaults = getModuleCache('phreebooks', 'chart', 'defaults');
        if ($default || empty($glDefaults[$currency][$type])) { // user set as default OR for some reason the default account has been cleared
            $glDefaults[$currency][$type] = $acct;
            setModuleCache('phreebooks', 'chart', 'defaults', $glDefaults);
        }
    }
}
