<?php
/*
 * Methods related to locale taxes, authorities and rates
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
 * @version    3.x Last Update: 2018-12-24
 * @filesource /lib/controller/module/phreebooks/tax.php
 */

namespace bizuno;

class phreebooksTax
{
    public $moduleID = 'phreebooks';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }

    /**
     * Main entry point to manage sales taxes
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $type = clean('type', ['format'=>'char', 'default'=>'c'], 'get');
        $layout = array_replace_recursive($layout,  ['type'=>'divHTML',
            'divs'     => ["tax$type"=>['order'=>50,'type'=>'accordion','key'=>"accTax$type"]],
            'accordion'=> ["accTax$type"=>['divs'=>[
                "divTax{$type}Manager"=>['order'=>30,'label'=>pullTableLabel('inventory', 'tax_rate_id', $type),'type'=>'datagrid','key'=>"dgTax$type"],
                "divTax{$type}Detail" =>['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;']]]],
            'datagrid' => ["dgTax$type" => $this->dgTax("dgTax$type", $type, $security)]]);
    }

    /**
     * Lists of user defined taxes for a specified type
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $type = clean('type', 'char', 'get');
        $structure = $this->dgTax('tax_main', $type, getUserCache('security', 'admin', false, 0));
        $layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
    }

    /**
     * Sets the session variables with the users current filter settings
     * @param char $type - choices are c (customer) and v (vendor)
     */
    private function managerSettings($type='')
    {
        $data = ['path'=>'tax'.$type, 'values' => [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX."tax_rates.title"],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'f0_'.$type,'clean'=>'char','default'=>'0'],
            ['index'=>'search','clean'=>'text',   'default'=>'']]];
        $this->defaults = updateSelection($data);
    }
    /**
     * Structure for editing sales tax rates
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $type= clean('type',['format'=>'char','default'=>'c'], 'get');
        $rID = clean('rID', 'integer', 'get');
        $struc = dbLoadStructure(BIZUNO_DB_PREFIX."tax_rates", $type);
        if ($rID) {
            $dbData = dbGetRow(BIZUNO_DB_PREFIX."tax_rates", "id=$rID");
            dbStructureFill($struc, $dbData);
            $rates = $this->getRateDetail($dbData['settings']); // already encoded
        } else {
            $rates = $this->getRateDetail(false);
            $struc['start_date']['attr']['value']= date('Y-m-d');
            $struc['end_date']['attr']['value']  = localeCalculateDate(date('Y-m-d'), 0, 0, 10);
        }
        unset($struc['settings']);
        $data = ['type'=>'divHTML',
            'divs'     => [
                'toolbar'=>['order'=>10,'type'=>'toolbar','key'=>'tbTax'],
                'body'   =>['order'=>50,'type'=>'divs','divs'=>[
                    'formBOF' => ['order'=>15,'type'=>'form',    'key'   =>"frmTax$type"],
                    'body'    => ['order'=>50,'type'=>'fields',  'fields'=>$this->getViewTax($struc, $type)],
                    'datagrid'=> ['order'=>70,'type'=>'datagrid','key'   =>'dgTaxVendors'],
                    'formEOF' => ['order'=>95,'type'=>'html',    'html'  =>"</form>"]]]],
            'toolbars'=> ['tbTax'=>['icons'=>[
                "taxSave$type" => ['order'=>20,'icon'=>'save','label'=>lang('save'),'events'=>['onClick'=>"taxPreSubmit$type('$type'); jq('#frmTax$type').submit();"]],
                "taxNew$type"  => ['order'=>40,'icon'=>'new', 'label'=>lang('new'), 'events'=>['onClick'=>"accordionEdit('accTax$type','dgTax$type','divTax{$type}Detail','".jsLang('details')."', 'phreebooks/tax/edit&type=$type', 0);"]]]]],
            'forms'   => ["frmTax$type"=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/tax/save&type=$type"]]],
            'datagrid'=> ['dgTaxVendors'=>$this->dgTaxVendors("dgTaxVendors$type", $type, $rates)],
            'fields'  => $struc,
            'jsHead'  => ['pbChart'=>"var pbChart=bizDefaults.glAccounts.rows;"],
            'jsBody'  => ['init'   =>$this->getJsBody($type)],
            'jsReady' => ['init'   =>"ajaxForm('frmTax$type');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    private function getJsBody($type)
    {
        return "function taxTotal$type(newVal) {
    var total = 0;
    if (typeof curIndex == 'undefined') return;
    jq('#dgTaxVendors$type').datagrid('getRows')[curIndex]['rate'] = newVal;
    var items = jq('#dgTaxVendors$type').datagrid('getData');
    for (var i=0; i<items['rows'].length; i++) {
        total += parseFloat(items['rows'][i]['rate']);
    }
    var footer= jq('#dgTaxVendors$type').datagrid('getFooterRows');
    footer[0]['rate'] = formatNumber(total);
    jq('#dgTaxVendors$type').datagrid('reloadFooter');
}
function taxPreSubmit{$type}(type) {
    jq('#dgTaxVendors$type').edatagrid('saveRow');
    var items = jq('#dgTaxVendors$type').datagrid('getData');
    var serializedItems = JSON.stringify(items);
    jq('#settings'+type).val(serializedItems);
}";
    }

    private function getViewTax($struc, $type)
    {
        return ['id'    .$type => array_merge($struc['id']),
            'settings'  .$type => array_merge($struc['type']),
            'title'     .$type => array_merge($struc['title'],     ['break'=>true]),
            'inactive'  .$type => array_merge($struc['inactive'],  ['break'=>true]),
            'start_date'.$type => array_merge($struc['start_date'],['col'=>2,'break'=>true]),
            'end_date'  .$type => array_merge($struc['end_date'],  ['col'=>2,'break'=>true])];
    }

    /**
     * Structure for saving user defined sales tax information
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[])
    {
        $type = clean('type', ['format'=>'char','default'=>'c'], 'get');
        $values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."tax_rates"), $type);
        $rID = isset($values['id']) ? $values['id'] : 0;
        if (!validateSecurity('bizuno', 'admin', $rID?3:2)) { return; }
        $settings = clean($values['settings'], 'json');
        $values['type'] = $type;
        $values['settings'] = json_encode($settings['rows']);
        $values['tax_rate'] = $settings['footer'][0]['rate'];
        msgDebug("\n  writing values = ".print_r($values, true));
        dbWrite(BIZUNO_DB_PREFIX."tax_rates", $values, $rID?'update':'insert', "id=$rID");
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jq('#accTax$type').accordion('select', 0); jq('#dgTax$type').datagrid('reload'); jq('#divTax{$type}Detail').html('&nbsp;');"]]);
    }

    /**
     * Structure for deleting a sales tax rate
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'admin', 4)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return; }
        if (dbGetRow(BIZUNO_DB_PREFIX."journal_item", "tax_rate_id='$rID'")) { return msgAdd($this->lang['err_tax_rate_delete']); }
        $row = dbGetRow(BIZUNO_DB_PREFIX."tax_rates", "id='$rID'");
        msgLog(lang('journal_item_tax_rate_id').'-'.lang('delete')."-".$row['title']);
        $layout = array_replace_recursive($layout, [
            'dbAction'=> [BIZUNO_DB_PREFIX."tax_rates"=>"DELETE FROM ".BIZUNO_DB_PREFIX."tax_rates WHERE id='$rID'"],
            'content' => ['action'=>'eval', 'actionData'=>"jq('#dgTax{$row['type']}').datagrid('reload');"]]);
    }

    /**
     * Retrieves the details of a selected rate and builds the structure to display on the edit screen
     * @param type $settings
     * @return type
     */
    private function getRateDetail($settings=[])
    {
        $rows = json_decode($settings, true);
        if (!is_array($rows) || !$rows) { $rows = ['rows'=>  []]; }
        if (!isset($rows['rows'])) { $rows= ['rows' => $rows]; }
        $total = 0;
        foreach ($rows['rows'] as $idx => $row) {
            if (!isset($row['cTitle'])) {
                $rows['rows'][$idx]['cTitle'] = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'short_name', "id='{$row['cID']}'");
            }
            $total += $row['rate'];
        }
        $rows['total'] = sizeof($rows['rows']);
        $rows['footer']= [['glAcct'=>lang('total'),'rate'=>viewFormat($total, 'number')]];
        return json_encode($rows);
    }

    /**
     * Re-assigns the default tax rate from an old ID to a new ID, helpful tool to update specific contacts when tax rates change within a locale
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function bulkChange(&$layout=[])
    {
        $type  = clean('type', ['format'=>'char','default'=>'c'], 'get');
//      bizAutoLoad(BIZUNO_LIB . "controller/module/phreebooks/functions.php", 'loadTaxes', 'function');
//      $taxAll= loadTaxes($type);
        $subjects = [['id'=>'c','text'=>lang('contacts')],['id'=>'i','text'=>lang('inventory')]];
        $icnGo = ['icon'=>'next', 'label'=>lang('go'),
            'events'=>  ['onClick'=>"var data='&type=$type&subject='+jq('#subject').val()+'&srcID='+jq('#taxSrc').combogrid('getValue')+'&destID='+jq('#taxDest').combogrid('getValue');
                jsonAction('phreebooks/tax/bulkChangeSave'+data);"]];
        $html  = $this->lang['tax_bulk_src'] .'<br /><input id="taxSrc" name="taxSrc"><br />'.
                 $this->lang['tax_bulk_dest'].'<br /><input id="taxDest" name="taxDest"><br />'.
                 $this->lang['tax_subject']  .'<br />'.html5('subject', ['values'=>$subjects,'attr'=>['type'=>'select']]).'<br />'.html5('', $icnGo).'<br />';
        $jsBody= "function taxBulkChange(id, taxData) {
    jq('#'+id).combogrid({data:taxData,width:120,panelWidth:210,idField:'id',textField:'text',
        rowStyler:function(idx, row) { if (row.status==1) { return {class:'journal-waiting'}; } else if (row.status==2) { return {class:'row-inactive'}; }  },
        columns:[[{field:'id',hidden:true},
            {field:'text',    width:120,title:'".jsLang('journal_main_tax_rate_id')."'},
            {field:'tax_rate',width:70, title:'".jsLang('amount')."',align:'center'}]]
    });
}";
        $jsReady= "taxBulkChange('taxSrc', bizDefaults.taxRates.$type.rows);\ntaxBulkChange('taxDest', bizDefaults.taxRates.$type.rows);";
        $data = ['type'=>'popup','title'=>$this->lang['tax_bulk_title'],'attr'=>['id'=>'winTaxChange'],
            'divs'   => ['body'=> ['order'=>50,'type'=>'html', 'html'=>$html]],
            'jsBody' => ['taxJSBody'=> $jsBody],
            'jsReady'=> ['taxJSRdy' => $jsReady]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Performs the bulk change of a contacts/inventory tax rate into another in the db
     * @param array $layout - current working structure
     * @return modifed $layout
     */
    public function bulkChangeSave(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $type   = clean('type',   'char',    'get');
        $subject= clean('subject','char',    'get'); // either contact (c) or inventory (i)
        $srcID  = clean('srcID',  'integer', 'get'); // record ID to merge
        $destID = clean('destID', 'integer', 'get'); // record ID to keep
//      if (!$srcID || !$destID){ return msgAdd("Bad IDs, Source ID = $srcID and Destination ID = $destID"); } // commented out to allow None as a choice
        if ($srcID == $destID)  { return msgAdd("Source and destination cannot be the same!"); }
        $cnt = 0;
        if ($subject == 'i') {
            $field = $type=='v' ? 'tax_rate_id_v' : 'tax_rate_id_c';
            $cnt = dbWrite(BIZUNO_DB_PREFIX.'inventory', [$field => $destID], 'update', "$field = $srcID");
        } else {
            $cnt = dbWrite(BIZUNO_DB_PREFIX.'contacts', ['tax_rate_id'=>$destID], 'update', "tax_rate_id=$srcID");
        }
        msgAdd(sprintf($this->lang['tax_bulk_success'], $cnt), 'success');
        $table = $subject=='i' ? lang('inventory') : lang('contacts');
        msgLog(lang("phreebooks").'-'.$this->lang['tax_bulk_title']." $table: $srcID => $destID)");
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"bizWindowClose('winTaxChange');"]]);
    }

    /**
     * Datagrid for main tax listing
     * @param string $name - DOM field name
     * @param char $type - choice are c (customer) or v (vendor)
     * @param integer $security - users security level for visibility
     * @return array
     */
    private function dgTax($name, $type, $security = 0)
    {
        $this->managerSettings($type);
        // clean up the filter sqls
        $statusValues = [['id'=>'a','text'=>lang('all')],['id'=>'0','text'=>lang('active')], ['id'=>'1','text'=>lang('inactive')]];
         switch ($this->defaults['f0_'.$type]) {
            default:
            case 'a': $f0_value = ""; break;
            case '0': $f0_value = BIZUNO_DB_PREFIX."tax_rates.inactive='0'"; break;
            case '1': $f0_value = BIZUNO_DB_PREFIX."tax_rates.inactive='1'"; break;
        }
        $data = ['id'=>$name,'rows'=>$this->defaults['rows'],'page'=>$this->defaults['page'],
            'attr'   => ['toolbar'=>"#{$name}Bar", 'idField'=>'id', 'url'=>BIZUNO_AJAX."&p=phreebooks/tax/managerRows&type=$type"],
            'events' => [
                'onDblClickRow'=> "function(rowIndex, rowData) { accordionEdit('accTax$type', 'dgTax$type', 'divTax{$type}Detail', '".lang('details')."', 'phreebooks/tax/edit&type=$type', rowData.id); }",
                'rowStyler'    => "function(index, row) { if (row.inactive==1) { return {class:'row-inactive'}; } else {
                    if (typeof row.start_date=='undefined' || typeof row.end_date=='undefined') { return; }
                    if (compareDate(dbDate(row.start_date)) == 1 || compareDate(dbDate(row.end_date)) == -1) { return {class:'journal-waiting'}; } } }"],
            'source' => [
                'tables' => ['tax_rates'=>['table'=>BIZUNO_DB_PREFIX."tax_rates"]],
                'search' => [BIZUNO_DB_PREFIX."tax_rates.title", BIZUNO_DB_PREFIX."tax_rates.description"],
                'actions'=> [
                    "newTax$type" => ['order'=>10,'label'=>lang('New'),'icon'=>'new',  'events'=>['onClick'=>"accordionEdit('accTax$type', 'dgTax$type', 'divTax{$type}Detail', '".lang('details')."', 'phreebooks/tax/edit&type=$type', 0);"]],
                    "clrSrch$type"=> ['order'=>30,'label'=>lang('New'),'icon'=>'clear','events'=>['onClick'=>"jq('#f0_{$type}').val('0'); jq('#search_$type').val(''); ".$name."Reload();"]],
                    "blkTax$type" => ['order'=>80,'icon'=>'merge','events'=>['onClick'=>"jsonAction('phreebooks/tax/bulkChange&type=$type', 0);"]]],
                'filters'=> [
                    "f0_$type"    => ['order'=>10, 'sql'=>$f0_value,'label'=>lang('status'),'values'=>$statusValues,'attr'=>['type'=>'select','value'=>$this->defaults['f0_'.$type]]],
                    "search$type" => ['order'=>90, 'attr'=>['value'=>$this->defaults['search']]],
                    "type$type"   => ['order'=>99, 'hidden'=>true, 'sql'=>"type='$type'"]],
                'sort' => ['s0'   => ['order'=>10, 'field'=>($this->defaults['sort'].' '.$this->defaults['order'])]]],
                'footnotes'=> ['status'=>lang('status').':<span class="journal-waiting">'.$this->lang['not_current'].'</span><span class="row-inactive">'.lang('inactive').'</span>'],
                'columns'  => [
                    'id'      => ['order'=>0,'field'=>BIZUNO_DB_PREFIX."tax_rates.id",      'attr'=>['hidden'=>true]],
                    'inactive'=> ['order'=>0,'field'=>BIZUNO_DB_PREFIX."tax_rates.inactive",'attr'=>['hidden'=>true]],
                    'action'  => ['order'=>1,'label'=>lang('action'),'events'=>['formatter'=>$name.'Formatter'],
                        'actions'   => [
                            'edit'  => ['icon'=>'edit', 'order'=>70,'events'=>['onClick'=>"accordionEdit('accTax$type', 'dgTax$type', 'divTax{$type}Detail', '".lang('details')."', 'phreebooks/tax/edit&type=$type', idTBD);"]],
                            'delete'=> ['icon'=>'trash','order'=>90,'hidden'=>$security>3?false:true,
                                'events'=> ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreebooks/tax/delete', idTBD);"]]]],
                    'title'     => ['order'=>10, 'label' => pullTableLabel(BIZUNO_DB_PREFIX."tax_rates", 'title'), 'field'=>BIZUNO_DB_PREFIX."tax_rates.title",
                        'attr' =>  ['width'=>320, 'sortable'=>true, 'resizable'=>true]],
                    'start_date'=> ['order'=>20, 'format'=>'date', 'label'=>pullTableLabel(BIZUNO_DB_PREFIX."tax_rates", 'start_date'), 'field'=>BIZUNO_DB_PREFIX."tax_rates.start_date",
                        'attr' =>  ['type'=>'date', 'width'=>160, 'sortable'=>true, 'resizable'=>true]],
                    'end_date'  => ['order'=>30, 'format'=>'date', 'label'  => pullTableLabel(BIZUNO_DB_PREFIX."tax_rates", 'end_date'), 'field'=>BIZUNO_DB_PREFIX."tax_rates.end_date",
                        'attr' =>  ['type'=>'date', 'width'=>160, 'sortable'=>true, 'resizable'=>true]],
                    'tax_rate'  => ['order'=>40, 'label'=>pullTableLabel(BIZUNO_DB_PREFIX."tax_rates", 'tax_rate'), 'field'=>BIZUNO_DB_PREFIX."tax_rates.tax_rate",
                        'attr' =>  ['width'=>160, 'sortable'=>true, 'resizable'=>true]]]];
        return $data;
    }

    /**
     * Creates the datagrid structure to list vendors (authorities) for a given tax rate
     * @param string $name - DOM field ID
     * @param char $type - choices are c (customer) or v (vendor)
     * @param array $rates - current user specified tax rates available for the specified type
     * @return array - ready to render
     */
    private function dgTaxVendors($name, $type, $rates)
    {
        $data = ['id' => $name,'type'=>'edatagrid',
            'attr'    => ['toolbar'=>"#{$name}Toolbar",'rownumbers'=>true,'showFooter'=>true],
            'events'  => ['data'=> $rates,
                'onClickRow'  => "function(rowIndex) { jq('#$name').edatagrid('editRow', rowIndex); }",
                'onAdd'       => "function(rowIndex, row) {  }",
                'onBeforeEdit'=> "function(rowIndex) { curIndex = rowIndex; }",
                'onEndEdit'   => "function (idx,row) {
    var ed = jq('#$name').datagrid('getEditor', {index:idx,field:'cID'});
    jq('#$name').datagrid('getRows')[idx]['cTitle'] = jq(ed.target).combobox('getText');
}"],
            'source' => ['actions'=>['new'=>['order'=>10,'icon'=>'add','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]],
            'columns'=> [
                'action' => ['order'=>1,'label'=>lang('action'),'events'=>['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
                    'actions'=>['invVendTrash'=>['icon'=>'trash','order'=>20,'events'=>['onClick'=>"jq('#$name').edatagrid('destroyRow');"]]]],
                'cID'=> ['order'=>10,'label'=>pullTableLabel("contacts", 'short_name', 'v'),
                        'attr'  => ['width'=>150,'sortable'=>true,'resizable'=>true,'align'=>'center'],
                        'events'=> ['formatter'=>"function(value, row) { return row.cTitle; }",
                            'editor'=>"{type:'combogrid',options:{width:130,panelWidth:750,delay:900,idField:'id',textField:'primary_name',mode:'remote',
    url:'".BIZUNO_AJAX."&p=contacts/main/managerRows&clr=1&type=v',selectOnNavigation:false,columns: [[
      {field:'id',          hidden:true},
      {field:'short_name',  width:100,title:'".jsLang('contacts_short_name')."'},
      {field:'primary_name',width:200,title:'".jsLang('address_book_primary_name')."'},
      {field:'city',        width:100,title:'".jsLang('address_book_city')."'},
      {field:'state',       width: 50,title:'".jsLang('address_book_state')."'},
    ]] }}"]],
                'text'  => ['order'=>20,'label'=>lang('description'),'attr'=>['width'=>240,'resizable'=>true,'editor'=>'text']],
                'glAcct'=> ['order'=>30,'label'=>lang('gl_account'), 'attr'=>['width'=>100,'resizable'=>true,'align' =>'center'],'events'=>['editor'=>dgEditGL()]],
                'rate'  => ['order'=>40,'label'=>lang('tax_rates_tax_rate'),'attr'=>['width'=>100,'resizable'=>true],
                    'events'=>['editor'=>"{type:'numberbox',options:{onChange:function(newVal){taxTotal$type(newVal);}} }"]]]];
        return $data;
    }
}
