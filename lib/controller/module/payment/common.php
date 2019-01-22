<?php
/*
 * Payment module - Common methods
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
 * @version    3.x Last Update: 2018-12-19
 * @filesource /lib/controller/module/payment/common.php
 */

namespace bizuno;

class paymentCommon
{

    function __construct()
    {
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());        
    }
    
    protected function settingsDefaults()
    {
        $pmtDef = getModuleCache($this->moduleID, 'settings', 'general', false, []);
        return ['cash_gl_acct'=>$pmtDef['gl_payment_c'],'disc_gl_acct'=>$pmtDef['gl_discount_c'],'order'=>10,
            'prefix'=>'CC','prefixAX'=>'AX','allowRefund'=>'0'];
    }
    
    public function settingsCommon()
    {
        $noYes = [['id'=>'0','text'=>lang('no')], ['id'=>'1','text'=>lang('yes')]];
        return [
            'cash_gl_acct'=> ['label'=>$this->lang['gl_payment_c_lbl'], 'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_cash_gl_acct",'value'=>$this->settings['cash_gl_acct']]],
            'disc_gl_acct'=> ['label'=>$this->lang['gl_discount_c_lbl'],'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_disc_gl_acct",'value'=>$this->settings['disc_gl_acct']]],
            'order'       => ['label'=>lang('order'), 'position'=>'after', 'attr'=>  ['type'=>'integer', 'size'=>'3','value'=>$this->settings['order']]],
            'prefix'      => ['label'=>$this->lang['prefix_lbl'], 'position'=>'after', 'attr'=>['size'=>'5','value'=>$this->settings['prefix']]],
            'prefixAX'    => ['label'=>$this->lang['prefix_amex'],'position'=>'after', 'attr'=>['size'=>'5','value'=>$this->settings['prefixAX']]],
            'allowRefund' => ['label'=>$this->lang['allow_refund'],'values'=>$noYes,   'attr'=>['type'=>'select','value'=>$this->settings['allowRefund']]]];
    }

    public function renderCommon(&$output, $data, $values=[], $dispFirst=false)
    {
        msgDebug("\nWorking with values = ".print_r($values, true));
        $exp = pullExpDates();
        $this->viewData = [
            'trans_code'=> ['attr'=>['type'=>'hidden']],
            'selCards'  => ['attr'=>['type'=>'select'],  'events'=>['onChange'=>"{$this->code}RefNum('stored');"]],
            'save'      => ['label'=>lang('save'),                'break'=>true,'attr'=>['type'=>'checkbox', 'value'=>'1']],
            'name'      => ['label'=>lang('payment_name') ,       'break'=>true,'attr'=>['size'=>'24']],
            'number'    => ['label'=>lang('payment_number'),      'break'=>true,'attr'=>['size'=>'19'], 'events'=>  ['onChange'=>"{$this->code}RefNum('number');"]],
            'month'     => ['label'=>lang('payment_expiration'),  'values'=>$exp['months'],'attr'=>  ['type'=>'select']],
            'year'      => ['values'=>$exp['years'],              'break'=>true,'attr'=>['type'=>'select']],
            'cvv'       => ['label'=>lang('payment_cvv'),                       'attr'=>['size'=>'5', 'maxlength'=>'4']]];
        if (isset($values['method']) && $values['method']==$this->code 
                && isset($data['fields']['id']['attr']['value']) && $data['fields']['id']['attr']['value']) { // edit
            $this->viewData['number']['attr']['value'] = isset($values['hint']) ? $values['hint'] : '****';
            $invoice_num = $invoice_amex = $data['fields']['invoice_num']['attr']['value'];
            $gl_account  = $data['fields']['gl_acct_id']['attr']['value'];
            $discount_gl = $this->getDiscGL($data['fields']['id']['attr']['value']);
            $show_s = false;  // since it's an edit, all adjustments need to be made at the gateway, this prevents duplicate charges when re-posting a transaction
            $show_c = false;
            $show_n = false;
            $checked = 'w';
        } else { // defaults
            $invoice_num = $this->settings['prefix'].date('Ymd');
            $invoice_amex= $this->settings['prefixAX'].date('Ymd');
            $gl_account  = $this->settings['cash_gl_acct'];
            $discount_gl = $this->settings['disc_gl_acct'];
            $show_n = true;
            $checked = 'n';
            $cID = isset($data['fields']['contact_id_b']['attr']['value']) ? $data['fields']['contact_id_b']['attr']['value'] : 0;
            if ($cID) { // find if stored values
                $encrypt = new encryption();
                $this->viewData['selCards']['values'] = $encrypt->viewCC('contacts', $cID);
                if (sizeof($this->viewData['selCards']['values']) == 0) {
                    $this->viewData['selCards']['hidden'] = true;
                    $show_s = false;
                } else {
                    $checked = 's';
                    $show_s = true;
                    $first_prefix = $this->viewData['selCards']['values'][0]['text'];
                    $invoice_num = substr($first_prefix, 0, 2)=='37' ? $invoice_amex : $invoice_num;
                }
            } else { $show_s = false; }
            if (isset($values['trans_code']) && $values['trans_code']) {
                $invoice_num = isset($values['hint']) && substr($values['hint'], 0, 2)=='37' ? $invoice_amex : $invoice_num;
                $this->viewData['trans_code']['attr']['value'] = $values['trans_code'];
                $checked = 'c';
                $show_c = true;
            } else { $show_c = false; }
        }
        $output['jsBody'][] = "
arrPmtMethod['$this->code'] = {cashGL:'$gl_account', discGL:'$discount_gl', ref:'$invoice_num', refAX:'$invoice_amex'};
function payment_$this->code() {
    jq('#invoice_num').val(arrPmtMethod['$this->code'].ref);
    jq('#gl_acct_id').combogrid('setValue', arrPmtMethod['$this->code'].cashGL);
    jq('#totals_discount_gl').combogrid('setValue', arrPmtMethod['$this->code'].discGL);
}
function {$this->code}RefNum(type) {
    if (type=='stored') {
        var ccNum = jq('#{$this->code}selCards option:selected').text();
    } else {
        var ccNum = jq('#{$this->code}_number').val();
    }
    var prefix= ccNum.substr(0, 2);
    var newRef = prefix=='37' ? arrPmtMethod['$this->code'].refAX : arrPmtMethod['$this->code'].ref;
    jq('#invoice_num').val(newRef);
}";
        if ($this->code == $dispFirst) { $output['jsReady'][] = "jq('#invoice_num').val('$invoice_num');"; }
        $output['body'] .= html5($this->code.'_action', ['label'=>lang('capture'),'hidden'=>($show_c?false:true),'attr'=>['type'=>'radio','value'=>'c','checked'=>$checked=='c'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}c').show();"]]).
html5($this->code.'_action', ['label'=>lang('stored'), 'hidden'=>($show_s?false:true),'attr'=>['type'=>'radio','value'=>'s','checked'=>$checked=='s'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}s').show();"]]).
html5($this->code.'_action', ['label'=>lang('new'),    'hidden'=>($show_n?false:true),'attr'=>['type'=>'radio','value'=>'n','checked'=>$checked=='n'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').show();"]]).
html5($this->code.'_action', ['label'=>$this->lang["at_{$this->code}"],                    'attr'=>['type'=>'radio','value'=>'w','checked'=>$checked=='w'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').hide();"]]).'<br />';
$output['body'] .= '<div id="div'.$this->code.'c"'.($show_c?'':'style=" display:none"').'>';
if ($show_c) {
    $output['body'] .= html5($this->code.'trans_code',$this->viewData['trans_code']).sprintf(lang('msg_capture_payment'), viewFormat($values['total'],'currency'));
}
$output['body'] .= '</div><div id="div'.$this->code.'s"'.(!$show_c?'':'style=" display:none"').'>';
if ($show_s) { $output['body'] .= lang('payment_stored_cards').'<br />'.html5($this->code.'selCards', $this->viewData['selCards']); }
$output['body'] .= '</div>
<div id="div'.$this->code.'n"'.(!$show_c&&!$show_s?'':'style=" display:none"').'>'.
    html5($this->code.'_save',  $this->viewData['save']).
    html5($this->code.'_name',  $this->viewData['name']).
    html5($this->code.'_number',$this->viewData['number']).
    html5($this->code.'_month', $this->viewData['month']).
    html5($this->code.'_year',  $this->viewData['year']).
    html5($this->code.'_cvv',   $this->viewData['cvv']).'
</div>';
    }

    private function getDiscGL($data)
    {
        if (isset($data['fields'])) {
            foreach ($data['fields'] as $row) {
                if ($row['gl_type'] == 'dsc') { return $row['gl_account']; }
            }
        }
        return $this->settings['disc_gl_acct']; // not found, return default
    }
}