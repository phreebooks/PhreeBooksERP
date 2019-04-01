<?php
/*
 * Payment Method - PayPal
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
 * @version    3.x Last Update: 2019-03-21
 * @filesource /lib/controller/module/payment/methods/paypal.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');

// for API Signature
if (!defined('PAYMENT_PAYPAL_URL'))        { define('PAYMENT_PAYPAL_URL', "https://api-3t.paypal.com/nvp"); }
if (!defined('PAYMENT_PAYPAL_URL_TEST')){ define('PAYMENT_PAYPAL_URL_TEST', "https://api-3t.sandbox.paypal.com/nvp"); }
// for API Certificate
//if (!defined('PAYMENT_PAYPAL_CERT_URL'))     { define('PAYMENT_PAYPAL_CERT_URL', "https://api.paypal.com/nvp"); }
//if (!defined('PAYMENT_PAYPAL_CERT_URL_TEST')){ define('PAYMENT_PAYPAL_CERT_URL_TEST', "https://api.sandbox.paypal.com/nvp"); }


class paypal
{
    private $mode     = 'prod'; // choices are test (Test) and prod (Production)
    public  $moduleID = 'payment';
    public  $methodDir= 'methods';
    public  $code     = 'paypal';
    public  $sandbox  = [
        'username'  => 'flintstone_api1.phreesoft.com',
        'password'  => '4DS3PAE7GW26YH9C',
        'signature' => 'A-l6fYxQvt9MpbUpkGw-mppIrNfLAFQ4b41PZJnYNmah0EUN6a1aqRU4',
        'AppID'     => 'APP-80W284485P519543T',
        'first_name'=> 'Fred',
        'last_name' => 'Flintstone',
        'number'    => '4032039921024040',
        'month'     => '06',
        'year'      => '2020',
//      'type'      => 'VISA',
        'cvv'       => ''];

    public function __construct()
    {
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $pmtDef        = getModuleCache($this->moduleID, 'settings', 'general', false, []);
        $this->settings= ['cash_gl_acct'=>$pmtDef['gl_payment_c'],'disc_gl_acct'=>$pmtDef['gl_discount_c'],'order'=>10,'user'=>'','pass'=>'','signature'=>'',
            'auth_type'=>'Sale','prefix'=>'CC','allowRefund'=>'0'];
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        $noYes = [['id'=>'0','text'=>lang('no')], ['id'=>'1','text'=>lang('yes')]];
        $auths = [['id'=>'Sale','text'=>lang('capture')], ['id'=>'Authorization','text'=>lang('authorize')]];
        return [
            'cash_gl_acct'=> ['label'=>$this->lang['gl_payment_c_lbl'], 'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_cash_gl_acct",'value'=>$this->settings['cash_gl_acct']]],
            'disc_gl_acct'=> ['label'=>$this->lang['gl_discount_c_lbl'],'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_disc_gl_acct",'value'=>$this->settings['disc_gl_acct']]],
            'order'       => ['label'=>lang('order'), 'position'=>'after', 'attr'=>  ['type'=>'integer', 'size'=>'3','value'=>$this->settings['order']]],
            'user'        => ['label'=>$this->lang['user'],       'position'=>'after', 'attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['user']]],
            'pass'        => ['label'=>$this->lang['pass'],       'position'=>'after', 'attr'=>['type'=>'text','value'=>$this->settings['pass']]],
            'signature'   => ['label'=>$this->lang['signature'],  'position'=>'after', 'attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['signature']]],
            'auth_type'   => ['label'=>$this->lang['auth_type'],  'values'=>$auths,    'attr'=>['type'=>'select','value'=>$this->settings['auth_type']]],
            'prefix'      => ['label'=>$this->lang['prefix_lbl'], 'position'=>'after', 'attr'=>['size'=>'5','value'=>$this->settings['prefix']]],
            'allowRefund' => ['label'=>$this->lang['allow_refund'],'values'=>$noYes,   'attr'=>['type'=>'select','value'=>$this->settings['allowRefund']]]];
    }

    public function render(&$output, $data, $values=[], $dispFirst=false)
    {
        msgDebug("\nWorking with values = ".print_r($values, true));
        $cc_exp = pullExpDates();
        $this->viewData = [
            'trans_code'=> ['attr'=>['type'=>'hidden']],
            'selCards'  => ['attr'=>['type'=>'select'],'events'=>['onChange'=>"paypalRefNum('stored');"]],
            'save'      => ['label'=>lang('save'),'break'=>true,'attr'=>['type'=>'checkbox','value'=>'1']],
            'name'      => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_name')],
            'number'    => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_number'),'events'=>['onChange'=>"convergeRefNum('number');"]],
            'month'     => ['label'=>lang('payment_expiration'),'options'=>['width'=>130],'values'=>$cc_exp['months'],'attr'=>['type'=>'select','value'=>date('m')]],
            'year'      => ['break'=>true,'options'=>['width'=>70],'values'=>$cc_exp['years'],'attr'=>['type'=>'select','value'=>date('Y')]],
            'cvv'       => ['options'=>['width'=>45],'label'=>lang('payment_cvv'),'attr'=>['type'=>'text','size'=>'4']]];
        if (isset($values['method']) && $values['method']==$this->code && !empty($data['fields']['id']['attr']['value'])) { // edit
            $this->viewData['number']['attr']['value'] = isset($values['hint']) ? $values['hint'] : '****';
            $invoice_num = $data['fields']['invoice_num']['attr']['value'];
            $gl_account  = $data['fields']['gl_acct_id']['attr']['value'];
            $discount_gl = $this->getDiscGL($data['fields']['id']['attr']['value']);
        } else { // defaults
            $invoice_num = $this->settings['prefix'].date('Ymd');
            $gl_account  = $this->settings['cash_gl_acct'];
            $discount_gl = $this->settings['disc_gl_acct'];
        }
        $checked = 'n';
        $cID = isset($data['fields']['contact_id_b']['attr']['value']) ? $data['fields']['contact_id_b']['attr']['value'] : 0;
        if ($cID) { // find if stored values
            bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');
            $encrypt = new encryption();
            $this->viewData['selCards']['values'] = $encrypt->viewCC('contacts', $cID);
            if (sizeof($this->viewData['selCards']['values']) == 0) {
                $this->viewData['selCards']['hidden'] = true;
                $show_s = false;
            } else {
                $checked = 's';
                $show_s = true;
            }
        } else { $show_s = false; }
        if (isset($values['trans_code']) && $values['trans_code']) {
            $this->viewData['trans_code']['attr']['value'] = $values['trans_code'];
            $checked = 'c';
            $show_c = true;
        } else { $show_c = false; }
        $output['jsBody'][] = "
arrPmtMethod['$this->code'] = {cashGL:'$gl_account', discGL:'$discount_gl', ref:'$invoice_num'};
function payment_$this->code() {
    bizTextSet('invoice_num', arrPmtMethod['$this->code'].ref);
    bizGridSet('gl_acct_id', arrPmtMethod['$this->code'].cashGL);
    bizGridSet('totals_discount_gl', arrPmtMethod['$this->code'].discGL);
}
function paypalRefNum(type) {
    if (type=='stored') { var ccNum = bizSelGet('{$this->code}selCards'); }
      else { var ccNum = bizTextGet('{$this->code}_number');  }
    var newRef = arrPmtMethod['$this->code'].ref;
    bizTextSet('invoice_num', newRef);
}";
        if ($this->code == $dispFirst) { $output['jsReady'][] = "bizTextSet('invoice_num', '$invoice_num');"; }
        $output['body'] .= html5($this->code.'_action', ['label'=>lang('capture'),'hidden'=>($show_c?false:true),'attr'=>['type'=>'radio','value'=>'c','checked'=>$checked=='c'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}c').show();"]]).
html5($this->code.'_action', ['label'=>lang('stored'),'hidden'=>($show_s?false:true),'attr'=>  ['type'=>'radio','value'=>'s','checked'=>$checked=='s'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}s').show();"]]).
html5($this->code.'_action', ['label'=>lang('new'),'attr'=>  ['type'=>'radio','value'=>'n','checked'=>$checked=='n'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').show();"]]).
html5($this->code.'_action', ['label'=>$this->lang['at_paypal'],'attr'=>  ['type'=>'radio','value'=>'w'],
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

    public function sale($fields, $ledger)
    {
        $submit_data = [];
        switch ($fields['action']) {
            case 'c': // capture previously authorized transaction

return msgAdd("Capture code has not been written for PayPal, this transaction must be completed at www.PayPal.com!", 'caution');

                $submit_data = [
                    'VERSION'      => '64.0',
// method?            'METHOD'       => '',
                    'USER'         => $this->settings['user'],
                    'PWD'          => $this->settings['pass'],
                    'SIGNATURE'    => $this->settings['signature'],
                    'BUTTONSOURCE' => "Phreesoft_SP", // also called the bn code. !!! DO NOT CHANGE !!! PhreeSoft Partner Code
// index name?      'TRANSACTIONID'=> $fields['txID'], // Unique identifier returned on the original transaction
                    'AMT'          => $ledger->main['total_amount'], // amount of capture, must be less than or equal to auth amount
                    ];
                msgDebug("\nfields = ".print_r($submit_data, true));
                $desc['hint'] = isset($desc['hint']) ? $desc['hint'] : '****';
                break;
            case 's': //saved card, update $fields with stored data and continue
                $encrypt = new encryption();
                if (!$encrypt->decryptCC($fields['id'], $fields)) { return; }
            case 'n': // new card
                if ($this->mode == 'test') {
                    $fields = array_merge($fields, $this->sandbox); // overwrite the credentials for sandbox
                } else {
                    $fields['username'] = $this->settings['user'];
                    $fields['password'] = $this->settings['pass'];
                    $fields['signature']= $this->settings['signature'];
                }
                $submit_data = [
                    'VERSION'        => '64.0',
                    'USER'           => $fields['username'],
                    'PWD'            => $fields['password'],
                    'SIGNATURE'      => $fields['signature'],
                    'METHOD'         => 'DoDirectPayment',
                    'PAYMENTACTION'  => $this->settings['auth_type'],
                    'RETURNFMFDETAILS'=> 1,
                    'SOFTDESCRIPTOR'    => getModuleCache('bizuno','settings','company', 'primary_name'),
                    'SOFTDESCRIPTORCITY'=> getModuleCache('bizuno','settings','company', 'telephone1'),
//                  'CREDITCARDTYPE' => $card_type,
                    'ACCT'           => $fields['number'],
                    'EXPDATE'        => $fields['month'] . $fields['year'],
                    'CVV2'           => $fields['cvv'],
                    'EMAIL'          => $ledger->main['email_b'],
                    'FIRSTNAME'      => $fields['first_name'],
                    'LASTNAME'       => $fields['last_name'],
                    'STREET'         => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 20)),
                    'STREET2'        => str_replace('&', '-', substr($ledger->main['address2_b'], 0, 20)),
                    'CITY'           => $ledger->main['city_b'],
                    'STATE'          => $ledger->main['state_b'],
                    'COUNTRYCODE'    => clean($ledger->main['country_b'], 'ISO2'),
                    'ZIP'            => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
                    'AMT'            => $ledger->main['total_amount'],
                    'CURRENCYCODE'   => getUserCache('profile', 'currency', false, 'USD'),
                    'SHIPPINGAMT'    => isset($ledger->main['freight']) ? $ledger->main['freight'] : 0,
                    'TAXAMT'         => $ledger->main['sales_tax'] ? $ledger->main['sales_tax'] : 0,
                    'DESC'           => $ledger->main['description'],
                    'INVNUM'         => $ledger->main['id'], // needs to be unique so use db record ID
                    'BUTTONSOURCE'   => "Phreesoft_SP", // !!! DO NOT CHANGE !!! PhreeSoft Partner Code
//                  'IPADDRESS'      => get_ip_address(),
//                    'PAYERID'        => $ledger->main['contact_id_b'],
//                    'PHONENUM'       => $ledger->main['telephone1_b'],
//                  'SHIPTONAME'     => $ledger->main['primary_name_s'],
//                  'SHIPTOSTREET'   => $ledger->main['address1_s'],
//                  'SHIPTOSTREET2'  => $ledger->main['address2_s'],
//                  'SHIPTOCITY'     => $ledger->main['city_s'],
//                  'SHIPTOSTATE'    => $ledger->main['state_s'],
//                  'SHIPTOZIP'      => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_s']),
//                  'SHIPTOCOUNTRY'  => $ledger->main['country_s'],
//                  'SHIPTOPHONENUM' => $ledger->main['telephone1_s'],
        ];
                break;
            case 'w': // website capture, just post it
                msgAdd($this->lang['msg_capture_manual'].' '.$this->lang['msg_website'], 'caution');
                break;
        }
        msgDebug("\nPayPal sale working with fields = ".print_r($fields, true));
    if (sizeof($submit_data) == 0) { return true; } // nothing to send to gateway, assume all is good
        if (!$resp = $this->queryMerchant($submit_data)) { return; }
        return $resp;
    }

    public function paymentVoid($rID=0)
    {
        msgAdd($this->lang['msg_delete_manual'].' '.$this->lang['msg_website']);
        return true;
    }

    /**
     * @method paymentDelete - This method will delete/void a payment made BEFORE the processor commits the payment, typically must be run the same day as the sale
     * @param string $request - data from which to pull transaction and perform delete
     * @return boolean - true on success, false (with messageStack message) on unsuccessful deletion
     */
    public function paymentDelete($request=[])
    {
        msgAdd($this->lang['msg_delete_manual'].' '.$this->lang['msg_website']);
        return true;
    }

    /**
     * @method paymentRefund  This method will refund a payment made AFTER the processor commits the payment, typically must be run any day after the sale
     * @param string $request - data from which to pull transaction and perform refund/credit
     * @return boolean - true on success, false (with messageStack message) on unsuccessful deletion
     */
    public function paymentRefund($request=[])
    {
        msgAdd($this->lang['msg_refund_manual'].' '.$this->lang['msg_website']);
        return true;
    }

    /**
     * Sends request to PayPal.
     * @param array $request - request data for the post
     * @return false on error, array with results on success
     */
    private function queryMerchant($request=  [])
    {
        $output = [];
        msgDebug("\nRequest to send to PayPal: ".print_r($request, true));
        $url = $this->mode=='test' ? PAYMENT_PAYPAL_URL_TEST : PAYMENT_PAYPAL_URL;
        $channel = new \bizuno\io();
        $result = $channel->cURLGet($url, http_build_query($request));
        parse_str($result, $output);
        msgDebug("\nReceived back from PayPal: ".print_r($output, true));
        if (isset($output['L_LONGMESSAGE0'])) {
            msgAdd(sprintf($this->lang['err_process_decline'], $output['L_LONGMESSAGE0']));
            return;
        } elseif (isset($output['ACK']) && ($output['ACK']=='Success' || $output['ACK']=='SuccessWithWarning')) {
            msgAdd(sprintf($this->lang['msg_approval_success'], $output['ACK'], lang('AVS_'.$output['AVSCODE']), $this->lang['CVV_'.$output['CVV2MATCH']]), 'success');
            return ['txID'=>$output['TRANSACTIONID'], 'txTime'=>$output['TIMESTAMP'], 'avs'=>$output['AVSCODE'], 'cvv'=>$output['CVV2MATCH']];
        }
        msgAdd($this->lang['err_process_failed'], 'error');
        msgLog($this->lang['err_process_failed'].' - '.print_r($result, true), 'error');
        return;
    }

    private function getDiscGL($data)
    {
        if (isset($data['journal_item'])) { foreach ($data['journal_item'] as $row) {
            if ($row['gl_type'] == 'dsc') { return $row['gl_account']; }
        } }
        return $this->settings['disc_gl_acct']; // not found, return default
    }
}
