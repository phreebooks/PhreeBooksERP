<?php
/*
 * Manages currency and currency exchange rate updates
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
 * @filesource /lib/controller/module/phreebooks/currency.php
 */

namespace bizuno;

class phreebooksCurrency
{
    public $moduleID = 'phreebooks';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
    /**
     * Entry point to manage currencies
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $layout = array_replace_recursive($layout, ['type'=>'divHTML',
            'divs' => ["divCurrency" => ['order'=>50, 'type'=>'accordion','key' =>"accCurrency"]],
            'accordion'=> ['accCurrency'=>  ['divs'=>  [
                'accCurrencyMgr' => ['order'=>30,'label'=>lang('currencies'),'type'=>'datagrid','key'=>'dgCurrency'],
                'accCurrencyDtl' => ['order'=>70,'label'=>lang('details'),   'type'=>'html',    'html'=>'&nbsp;']]]],
            'datagrid' => ['dgCurrency'=>$this->dgCurrency('dgCurrency', $security)]]);
    }

    /**
     * Structure for adding a new ISO currency
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function add(&$layout=[])
    {
        $codes = viewCurrencySel();
        foreach ($codes as $code => $row) { // remove currencies in use
            if (array_key_exists($code, getModuleCache('phreebooks', 'currency', 'iso', false, []))) { unset($codes[$code]); }
        }
        $html  = '<p>'.$this->lang['new_currency_desc']."</p>";
        $html .= html5('currencyNewISO',['values'=>$codes,'attr'=>['type'=>'select', 'value'=>'USD']]);
        $html .= html5('iconGO',['icon'=>'next',
            'events'=>  ['onClick'=>"accordionEdit('accCurrency','dgCurrency','accCurrencyDtl','".lang('details')."','phreebooks/currency/edit&iso='+jq('#currencyNewISO').val()); bizWindowClose('winNewCur');"]]);
        $data = ['type'=>'popup','title'=>$this->lang['new_currency'],'attr'=>['id'=>'winNewCur','width'=>400,'height'=>200],
            'divs' => ['body'=>['order'=>50,'type'=>'html','html'=>$html]]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Structure for editing an existing currency
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $iso = clean('iso', ['format'=>'text', 'default'=>'USD'], 'get');
        if (!$iso) { return; }
        $values = getModuleCache('phreebooks', 'currency', 'iso', $iso, $this->currencySettings($iso));
        if ($iso == getUserCache('profile', 'currency')) { $values['value'] = 1; }
        $data = ['type'=>'divHTML',
            'divs'     => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key'=>'tbCurrency'],
                'formBOF'=> ['order'=>15,'type'=>'form',   'key'=>'frmCurrency'],
                'body'   => ['order'=>50,'type'=>'fields', 'fields'=>$this->getViewCurrency($values, $iso)],
                'formEOF'=> ['order'=>90,'type'=>'html',   'html'=>"</form>"]],
            'toolbars' => ['tbCurrency'=>  ['icons' => [
                "currencySave" => ['order'=>10,'icon'=>'save','label'=>lang('save'),
                    'events'=>  ['onClick'=>"jq('body').addClass('loading'); jq('#frmCurrency').submit();"]]]]],
            'forms'    => ['frmCurrency'=>  ['attr'=>  ['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/currency/save"]]],
            'jsReady'  => ['jsReady'=>"ajaxForm('frmCurrency');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Generates the view for currencies editing
     * @param array $values - current settings
     * @param string $iso - ISO Currency code
     * @return array - view structure
     */
    private function getViewCurrency($values, $iso)
    {
        return [
        'title'  => ['col'=>1,'break'=>true,'label'=>lang('title'),            'attr'=>['value'=>$values['title']]],
        'code'   => ['col'=>1,'break'=>true,'label'=>lang('code'),             'attr'=>['value'=>$values['code'], 'readonly'=>'readonly']],
        'is_def' => ['col'=>1,'break'=>true,'label'=>lang('default'),          'attr'=>['type'=>'checkbox', 'value'=>'1', 'checked'=>getUserCache('profile', 'currency', false, 'USD')==$iso?true:false]],
        'xrate'  => ['col'=>1,'break'=>true,'label'=>lang('exc_rate'),         'attr'=>['value'=>$values['value']]],
        'dec_len'=> ['col'=>2,'break'=>true,'label'=>$this->lang['dec_length'],'attr'=>['value'=>$values['dec_len']]],
        'dec_pt' => ['col'=>2,'break'=>true,'label'=>$this->lang['dec_point'], 'attr'=>['value'=>$values['dec_pt']]],
        'sep'    => ['col'=>2,'break'=>true,'label'=>lang('separator'),        'attr'=>['value'=>$values['sep']]],
        'prefix' => ['col'=>3,'break'=>true,'label'=>lang('prefix'),           'attr'=>['value'=>$values['prefix']]],
        'suffix' => ['col'=>3,'break'=>true,'label'=>lang('suffix'),           'attr'=>['value'=>$values['suffix']]],
        'pfxneg' => ['col'=>3,'break'=>true,'label'=>$this->lang['neg_prefix'],'attr'=>['value'=>isset($values['pfxneg']) ? $values['pfxneg'] : '-']],
        'sfxneg' => ['col'=>3,'break'=>true,'label'=>$this->lang['neg_suffix'],'attr'=>['value'=>isset($values['sfxneg']) ? $values['sfxneg'] : '']]];
    }
    
    /**
     * Structure for saving user edits to a currency
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'admin', 3)) { return; }
        $is_def = clean('is_def', 'boolean', 'post');
        $iso    = clean('code', 'text', 'post');
        $currencies= getModuleCache('phreebooks', 'currency', 'iso', false, []);
        $charts    = getModuleCache('phreebooks', 'chart');
        // clean out bad data
//        $codes = viewCurrencySel();
//        foreach ($currencies as $iso => $currency) { if (!array_key_exists($iso, $codes)) { unset($currencies[$iso]); } }
        $values = [
            'title'  => clean('title', 'text', 'post'),
            'code'   => $iso,
            'value'  => clean('xrate',  ['format'=>'float','default'=>'0'],'post'),
            'prefix' => clean('prefix', ['format'=>'text', 'default'=>''], 'post'),
            'suffix' => clean('suffix', ['format'=>'text', 'default'=>''], 'post'),
            'dec_pt' => clean('dec_pt', ['format'=>'text', 'default'=>''], 'post'),
            'sep'    => clean('sep',    ['format'=>'text', 'default'=>''], 'post'),
            'dec_len'=> clean('dec_len','integer','post'),
            'pfxneg' => clean('pfxneg', ['format'=>'text', 'default'=>''], 'post'),
            'sfxneg' => clean('sfxneg', ['format'=>'text', 'default'=>''], 'post')];
        // check for new default, if so error check journal before replacing
        if ($is_def && getUserCache('profile', 'currency', false, 'USD') != $iso) { // new default
            $id = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'id');
            if ($id) { return msgAdd($this->lang['err_currency_change']); }
            $oldISO = getUserCache('profile', 'currency', false, 'USD');
            setUserCache('profile', 'currency', $iso);
            portalWrite('business', ['currency'=>$iso], 'update', "id=".getUserCache('profile', 'biz_id'));
            dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."journal_main CHANGE currency currency CHAR(3) NOT NULL DEFAULT '$iso'");
            dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory_prices CHANGE currency currency CHAR(3) NOT NULL DEFAULT '$iso'");
            foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $id => $row) { 
                getModuleCache('phreebooks', 'chart', 'accounts', $id)['cur'] = $iso;
            }
            $charts['defaults'][$iso] = $charts['defaults'][$oldISO];
            unset($charts['defaults'][$oldISO]);
            setModuleCache('phreebooks', 'chart', false, $charts);
        }
        $currencies[$iso] = $values;
        setModuleCache('phreebooks', 'currency', 'iso', $currencies);
        dbWriteCache();
        msgAdd(lang('currency').": {$values['title']} ({$values['code']}) - ".lang('msg_settings_saved'), 'success');
        msgLog(lang('currency').": {$values['title']} ({$values['code']}) - ".lang('msg_settings_saved'));
        $actionData = "jq('#dgCurrency').datagrid({rowStyler:function(index, row) { if (row.code=='".getUserCache('profile', 'currency', false, 'USD')."') return {class:'row-default'};}});";
        $actionData.= "jq('#dgCurrency').datagrid('loadData', ".json_encode(array_values(getModuleCache('phreebooks', 'currency', 'iso'))).");";
        $actionData.= "jq('#accCurrency').accordion('select', 0); jq('#accCurrencyDtl').html(''); reloadSessionStorage();";
        $layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>$actionData]]);
    }

    /**
     * Structure for deleting a user currency, test for ability to delete as well
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function delete(&$layout)
    {
        if (!validateSecurity('bizuno', 'admin', 4)) { return; }
        $idx = clean('rID', 'integer', 'get');
        $iso = clean('data', 'text', 'get');
        if (!$iso) { return msgAdd("Bad data!"); }
        // cannot delete default currency
        if ($iso == getUserCache('profile', 'currency', false, 'USD')) { return msgAdd($this->lang['err_currency_delete_default']); }
        // Can't delete a currency or if it was used in ANY journal entry
        $exists = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'id', "currency='$iso'");
        if ($exists) { return msgAdd($this->lang['err_currency_cannot_delete']); }
        $title  = getModuleCache('phreebooks', 'currency', 'iso', $iso)['title'];
        $isoVals= getModuleCache('phreebooks', 'currency', 'iso');
        unset($isoVals[$iso]);
        setModuleCache('phreebooks', 'currency', 'iso', $isoVals);
        msgLog(lang('currency').": $title ($iso) - ".lang('deleted'));
        $actionData = "jq('#dgCurrency').datagrid('loadData', ".json_encode(array_values($isoVals)).");";
        $actionData.= "jq('#accCurrencyDtl').html(''); reloadSessionStorage(); jq('#dgCurrency').datagrid('deleteRow', $idx);";
        $layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>$actionData]]);
    }
    
    /**
     * Sets the structure for currencies to be stored in the session cache and browser cache
     * @param string $iso - ISO currency code
     * @return array - formatted ISO currency ready to put into cache
     */
    public function currencySettings($iso='USD')
    {
        $setting = ['code'=>$iso, 'value'=>1];
        $curData = localeLoadDB();
        foreach ($curData->Locale as $value) {
            if (isset($value->Currency->ISO) && $value->Currency->ISO == $iso) {
                $setting = [
                    'title'  => $value->Currency->Title,
                    'code'   => $value->Currency->ISO,
                    'prefix' => isset($value->Currency->Prefix)        ? $value->Currency->Prefix        : '$',
                    'suffix' => isset($value->Currency->Suffix)        ? $value->Currency->Suffix        : '',
                    'dec_pt' => isset($value->Currency->Decimal)       ? $value->Currency->Decimal       : '.',
                    'sep'    => isset($value->Currency->Thousand)      ? $value->Currency->Thousand      : ',',
                    'dec_len'=> isset($value->Currency->Precision)     ? $value->Currency->Precision     : '2',
                    'pfxneg' => isset($value->Currency->PrefixNegative)? $value->Currency->PrefixNegative: '-',
                    'sfxneg' => isset($value->Currency->SuffixNegative)? $value->Currency->SuffixNegative: '',
                    'value'  => 1];
                break;
            }
        }
        return $setting;
    }

    public function setExcRate(&$layout=[])
    {
        $iso  = clean('excISO', 'text', 'post');
        $rate = clean('excRate','float','post');
        $currencies = getModuleCache('phreebooks', 'currency', 'iso');
        if (empty($currencies[$iso])) { return msgAdd("The ISO submitted ($iso) is not one of your available currencies to update. It can be added in PhreeBooks Settings."); }
        $currencies[$iso]['value'] = $rate;
        setModuleCache('phreebooks', 'currency', 'iso', $currencies);
        msgLog(lang('currency')." $iso ($rate) - ".lang('update'));
        msgAdd("The new rate for $iso ($rate) has been saved!", 'success');
        $layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>"reloadSessionStorage();"]]);
    }
    
    /**
     * Updates all the ISO currencies with oanda (primary) and yahoo (secondary)
     * @param boolean $verbose - [default true] set to false to suppress user messages
     */
    public function update()
    {
        // This method has been deprecated as there are no more sources that let you do this automatically.
        // Use the XE and oanda dashboards to update your currency.
        // This site may have a solution for a future release: https://blog.quandl.com/api-for-currency-data
    }

    /**
     * Datagrid for currency view
     * @param string $name - DOM field name
     * @param integer $security - users security level
     * @return array - datagrid ready to render
     */
    public function dgCurrency($name, $security=0)
    {
        return ['id' => $name,
            'attr'   => ['toolbar'=>"#{$name}Toolbar",'singleSelect'=>true],
            'events' => ['data'=> "dataCurrency",
                'onDblClickRow'=> "function(rowIndex, rowData) { accordionEdit('accCurrency', 'dgCurrency', 'accCurrencyDtl', '".lang('details')."', 'phreebooks/currency/edit&iso='+rowData.code); }",
                'onClickRow'   => "function(rowIndex, rowData) { selectedCurrency = rowData.code; }",
                'rowStyler'    => "function(index, row) { if (row.code=='".getUserCache('profile', 'currency', false, 'USD')."') { return {class:'row-default'}; }}"],
            'source' => ['actions'=>['currencyNew'=>['order'=>10,'icon'=>'new','events'=>['onClick'=>"jsonAction('phreebooks/currency/add');"]]]],
            'columns'=> [
                'action' => ['order'=> 1, 'label'=>lang('action'),
                    'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
                    'actions'=> ['delete'=>['icon'=>'trash','size'=>'small', 'order'=>90, 'hidden'=>$security>3?false:true,
                        'events'=> ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreebooks/currency/delete', indexTBD, jq('#$name').datagrid('getRows')[indexTBD]['code']);"]]]],
                'code'   => ['order'=>10,'label'=>lang('code'),             'attr'=>['width'=> 50,'resizable'=>true]],
                'title'  => ['order'=>20,'label'=>lang('title'),            'attr'=>['width'=>200,'resizable'=>true]],
                'value'  => ['order'=>30,'label'=>lang('exc_rate'),         'attr'=>['width'=>100,'resizable'=>true]],
                'pfxneg' => ['order'=>40,'label'=>$this->lang['neg_prefix'],'attr'=>['width'=>100,'resizable'=>true]],
                'prefix' => ['order'=>50,'label'=>lang('prefix'),           'attr'=>['width'=> 80,'resizable'=>true]],
                'sep'    => ['order'=>60,'label'=>lang('separator'),        'attr'=>['width'=> 80,'resizable'=>true]],
                'dec_pt' => ['order'=>70,'label'=>$this->lang['dec_point'], 'attr'=>['width'=>100,'resizable'=>true]],
                'dec_len'=> ['order'=>80,'label'=>$this->lang['dec_length'],'attr'=>['width'=>100,'resizable'=>true]],
                'suffix' => ['order'=>90,'label'=>lang('suffix'),           'attr'=>['width'=> 80,'resizable'=>true]],
                'sfxneg' => ['order'=>99,'label'=>$this->lang['neg_suffix'],'attr'=>['width'=>100,'resizable'=>true]]],
            'footnotes' => ['codes'=>lang('color_codes').': <span class="row-default">'.lang('default').'</span>']];
    }
}
