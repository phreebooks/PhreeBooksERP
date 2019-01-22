<?php
/*
 * Payment Method - Authorize.net
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
 * @filesource /lib/controller/module/payment/methods/authorizenet.php
 *
 * Source Information:
 * @link http://developer.authorize.net/api/ - Main Website
 * @link https://developer.authorize.net/api/reference/index.html - API Documentation
 */

namespace bizuno;

if (!defined('PAYMENT_AUTHORIZENET_URL'))      { define('PAYMENT_AUTHORIZENET_URL', 'https://secure2.authorize.net/gateway/transact.dll'); }
if (!defined('PAYMENT_AUTHORIZENET_URL_TEST')) { define('PAYMENT_AUTHORIZENET_URL_TEST', 'https://test.authorize.net/gateway/transact.dll'); }
bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');

class authorizenet
{
    public  $moduleID  = 'payment';
    public  $methodDir = 'methods';
    public  $code      = 'authorizenet';
    private $mode      = 'prod'; // choices are 'test' (Test) or 'prod' (Production)
    private $delimiter = '|'; // The default delimiter is a comma
    private $encapChar = '*';  // The divider to encapsulate response fields

    public function __construct()
    {
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $pmtDef        = getModuleCache($this->moduleID, 'settings', 'general', false, []);
        $this->settings= ['cash_gl_acct'=>$pmtDef['gl_payment_c'],'disc_gl_acct'=>$pmtDef['gl_discount_c'],'order'=>10,'user_id'=>'','txn_key'=>'',
            'auth_type'=>'Authorize/Capture','prefix'=>'CC','prefixAX'=>'AX','allowRefund'=>'0'];
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        $noYes = [['id'=>'0','text'=>lang('no')], ['id'=>'1','text'=>lang('yes')]];
        $auths = [['id'=>'Authorize/Capture','text'=>lang('capture')], ['id'=>'Authorize','text'=>lang('authorize')]];
        return [
            'cash_gl_acct'=> ['label'=>$this->lang['gl_payment_c_lbl'], 'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_cash_gl_acct",'value'=>$this->settings['cash_gl_acct']]],
            'disc_gl_acct'=> ['label'=>$this->lang['gl_discount_c_lbl'],'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_disc_gl_acct",'value'=>$this->settings['disc_gl_acct']]],
            'order'       => ['label'=>lang('order'), 'position'=>'after', 'attr'=>  ['type'=>'integer', 'size'=>'3','value'=>$this->settings['order']]],
            'user_id'     => ['label'=>$this->lang['user_id'],    'position'=>'after', 'attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['user_id']]],
            'txn_key'     => ['label'=>$this->lang['txn_key'],    'position'=>'after', 'attr'=>['type'=>'text','value'=>$this->settings['txn_key']]],
            'auth_type'   => ['label'=>$this->lang['auth_type'],  'values'=>$auths,    'attr'=>['type'=>'select','value'=>$this->settings['auth_type']]],
            'prefix'      => ['label'=>$this->lang['prefix_lbl'], 'position'=>'after', 'attr'=>['size'=>'5','value'=>$this->settings['prefix']]],
            'prefixAX'    => ['label'=>$this->lang['prefix_amex'],'position'=>'after', 'attr'=>['size'=>'5','value'=>$this->settings['prefixAX']]],
            'allowRefund' => ['label'=>$this->lang['allow_refund'],'values'=>$noYes,   'attr'=>['type'=>'select','value'=>$this->settings['allowRefund']]]];
    }

    public function render(&$output, $data, $values=[], $dispFirst=false)
    {
        msgDebug("\nWorking with values = ".print_r($values, true));
        $cc_exp = pullExpDates();
        $this->viewData = [
            'trans_code'=> ['attr'=>['type'=>'hidden']],
            'selCards'  => ['attr'=>['type'=>'select'],'events'=>['onChange'=>"authorizenetRefNum('stored');"]],
            'save'      => ['label'=>lang('save'),'break'=>true,'attr'=>['type'=>'checkbox', 'value'=>'1']],
            'name'      => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_name')],
            'number'    => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_number'),'events'=>['onChange'=>"convergeRefNum('number');"]],
            'month'     => ['label'=>lang('payment_expiration'),'options'=>['width'=>130],'values'=>$cc_exp['months'],'attr'=>['type'=>'select','value'=>date('m')]],
            'year'      => ['break'=>true,'options'=>['width'=>70],'values'=>$cc_exp['years'],'attr'=>['type'=>'select','value'=>date('Y')]],
            'cvv'       => ['options'=>['width'=> 45],'label'=>lang('payment_cvv')]];
        if (isset($values['method']) && $values['method']==$this->code && !empty($data['fields']['id']['attr']['value'])) { // edit
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
    bizTextSet('invoice_num', arrPmtMethod['$this->code'].ref);
    bizGridSet('gl_acct_id', arrPmtMethod['$this->code'].cashGL);
    bizGridSet('totals_discount_gl', arrPmtMethod['$this->code'].discGL);
}
function authorizenetRefNum(type) {
    if (type=='stored') { var ccNum = jq('#{$this->code}selCards').val(); }
      else { var ccNum = bizTextGet('{$this->code}_number');  }
    var prefix= ccNum.substr(0, 2);
    var newRef = prefix=='37' ? arrPmtMethod['$this->code'].refAX : arrPmtMethod['$this->code'].ref;
    bizTextSet('invoice_num', newRef);
}";
        if ($this->code == $dispFirst) { $output['jsReady'][] = "bizTextSet('invoice_num', '$invoice_num');"; }
        $output['body'] .= html5($this->code.'_action', ['label'=>lang('capture'),'hidden'=>($show_c?false:true),'attr'=>['type'=>'radio','value'=>'c','checked'=>$checked=='c'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}c').show();"]]).
html5($this->code.'_action', ['label'=>lang('stored'), 'hidden'=>($show_s?false:true),'attr'=>['type'=>'radio','value'=>'s','checked'=>$checked=='s'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}s').show();"]]).
html5($this->code.'_action', ['label'=>lang('new'),    'hidden'=>($show_n?false:true),'attr'=>['type'=>'radio','value'=>'n','checked'=>$checked=='n'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').show();"]]).
html5($this->code.'_action', ['label'=>$this->lang['at_authorizenet'],                    'attr'=>['type'=>'radio','value'=>'w','checked'=>$checked=='w'?true:false],
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

    public function paymentAuth($fields, $ledger)
    {
        $refs = $this->guessInv($ledger);
        $submit_data = [
            'x_type'        => 'AUTH_ONLY',
            'x_amount'      => $ledger->main['total_amount'],
            'x_card_num'    => $fields['number'],
            'x_exp_date'    => $fields['month'] . substr($fields['year'], -2),
            'x_invoice_num' => $refs['inv'],
            'x_po_num'      => $refs['po'],
            'x_first_name'  => $fields['first_name'],
            'x_last_name'   => $fields['last_name'],
            'x_company'     => $ledger->main['primary_name_b'],
            'x_address'     => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 20)),
            'x_city'        => $ledger->main['city_b'],
            'x_state'       => $ledger->main['state_b'],
            'x_zip'         => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
            'x_country'     => $ledger->main['country_b'],
            'x_phone'       => substr(preg_replace("/[^0-9]/", "", $ledger->main['telephone1_b']), 0, 14),
            'x_email'       => isset($ledger->main['email_b']) ? $ledger->main['email_b'] : getModuleCache('bizuno', 'settings', 'company', 'email'),
            'x_description' => $ledger->main['description']];
        if (!empty($fields['cvv'])) { $submit_data['x_card_code'] = $fields['cvv']; }
        msgDebug("\nAuthorize.net sale working with fields = ".print_r($fields, true));
        if (sizeof($submit_data) == 0) { return true; } // nothing to send to gateway
        if (!$resp = $this->queryMerchant($submit_data)) { return; }
        return $resp;
    }

    /**
     * @method sale - This method will capture payment, if payment was authorized in a prior transaction, a ccComplete is done
     * @param integer $rID - record id from table journal_main to generate the capture, the transaction ID will be pulled from there.
     * @return array - On success, false (with messageStack message) on unsuccessful deletion
     */
    public function sale($fields, $ledger)
    {
        $refs = $this->guessInv($ledger);
        $submit_data = [];
        switch ($fields['action']) {
            case 'c': // capture previously authorized transaction
                $submit_data = [
                    'x_type'        => 'PRIOR_AUTH_CAPTURE',
                    'x_trans_id'    => $fields['txID'], // Unique identifier returned on the original transaction
                    'x_amount'      => $ledger->main['total_amount'],
                    ];
                break;
            case 's': // saved card, already decoded, just process like new card
            case 'n': // new card
                $submit_data = [
                    'x_type'        => 'AUTH_CAPTURE', // 'AUTH_ONLY', 'AUTH_CAPTURE', 'PRIOR_AUTH_CAPTURE'
                    'x_amount'      => $ledger->main['total_amount'],
                    'x_card_num'    => $fields['number'],
                    'x_exp_date'    => $fields['month'] . substr($fields['year'], -2),
                    'x_invoice_num' => $refs['inv'],
                    'x_po_num'      => $refs['po'],
                    'x_first_name'  => $fields['first_name'],
                    'x_last_name'   => $fields['last_name'],
                    'x_company'     => $ledger->main['primary_name_b'],
                    'x_address'     => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 20)),
                    'x_city'        => $ledger->main['city_b'],
                    'x_state'       => $ledger->main['state_b'],
                    'x_zip'         => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
                    'x_country'     => $ledger->main['country_b'],
                    'x_phone'       => substr(preg_replace("/[^0-9]/", "", $ledger->main['telephone1_b']), 0, 14),
                    'x_email'       => isset($ledger->main['email_b']) ? $ledger->main['email_b'] : getModuleCache('bizuno', 'settings', 'company', 'email'),
                    'x_description' => $ledger->main['description']];
                if (!empty($fields['cvv'])) { $submit_data['x_card_code'] = $fields['cvv']; }
                break;
            case 'w': // website capture, just post it
                msgAdd($this->lang['msg_capture_manual'].' '.$this->lang['msg_website']);
                break;
        }
//        msgDebug("\nAuthorize.net sale working with fields = ".print_r($fields, true));
        if (sizeof($submit_data) == 0) { return true; } // nothing to send to gateway
        if (!$resp = $this->queryMerchant($submit_data)) { return; }
        return $resp;
    }

    /**
     * @method paymentVoid will delete/void a payment made BEFORE the processor commits the payment, typically must be run the same day as the sale
     * @param integer $rID Record id from table journal_main to generate the void
     * @return array merchant response On success, false (with messageStack message) on unsuccessful deletion
     */
    public function paymentVoid($rID=0)
    {
        if (!$rID) { return msgAdd('Bad record ID passed'); }
        $txID = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'trans_code', "ref_id=$rID AND gl_type='ttl'");
        if (!$txID || !$this->settings['allowRefund']) { msgAdd(lang('err_cc_no_transaction_id'), 'caution'); return true; }
        $submit_data = [
            'x_type'     => 'VOID',
            'x_trans_id' => $txID, // Unique identifier returned on the original transaction.
            ];
        return $this->queryMerchant($submit_data);
    }

    /**
     * @method paymentRefund This method will refund a payment made AFTER the batch is processed, typically must be run any day after the sale
     * @param integer $rID - record id from table journal_main to generate the refund
     * @param float $amount - amount to be refunded (leave blank for full amount)
     * @return array - On success, false (with messageStack message) on unsuccessful deletion
     */
    public function paymentRefund($rID=0, $amount=false)
    {
        if (!$rID) { return msgAdd('Bad record ID passed'); }
        $results = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['debit_amount', 'credit_amount', 'trans_code'], "ref_id=$rID AND gl_type='ttl'");
        $max_amount = $results['debit_amount'] + $results['credit_amount'];
        if ($amount === false) { $amount = $max_amount; }
        if ($amount > $max_amount)  { return msgAdd(lang('err_cc_amount_too_big')); }
        if (floatval($amount) <= 0) { return msgAdd(lang('err_cc_amount_negative')); }
        if (!$results['trans_code'] || !$this->settings['allowRefund']) { msgAdd(lang('err_cc_no_transaction_id'), 'caution'); return true; }
        $submit_data = [
            'x_type'     => 'CREDIT',
            'x_trans_id' => $results['trans_code'], // Unique identifier returned on the original transaction.
            'x_amount'   => number_format($amount, 2, '.', ''), // Amount to be refunded in full or partial. Must be less or equal to the original purchase, if not supplied original full amount is refunded.
            ];
        return $this->queryMerchant($submit_data);
    }

    private function queryMerchant($submit_data=[])
    {
        global $io;
        $txnReq = array_merge([
            'x_login'          => $this->settings['user_id'],
            'x_tran_key'       => $this->settings['txn_key'],
            'x_relay_response' => 'FALSE',
            'x_delim_data'     => 'TRUE',
            'x_delim_char'     => $this->delimiter,  // The default delimiter is a comma
            'x_encap_char'     => $this->encapChar,  // The divider to encapsulate response fields
            'x_version'        => '3.1',  // 3.1 is required to use CVV codes
            'x_method'         => 'CC',
            'x_currency_code'  => getUserCache('profile', 'currency', false, 'USD'),
            ], $submit_data);
        if ($this->mode=='test') { $txnReq['x_test_request'] = 'TRUE'; }
        msgDebug("Request submit_data = ".print_r($txnReq, true));
        $url = $this->mode=='test' ? PAYMENT_AUTHORIZENET_URL_TEST : PAYMENT_AUTHORIZENET_URL; 
        $output = [];
        foreach ($txnReq as $key => $value) {
            if ($key != 'x_delim_char' && $key != 'x_encap_char') {
                $value = str_replace([$this->delimiter, $this->encapChar,'"',"'",'&amp;','&', '='], '', $value);
            }
            $output[] = "$key=".urlencode($value);
        }
        $request = implode('&', $output);
        // Post order info data to Authorize.net via CURL - Requires that PHP has CURL support installed
        if (!$result = $io->cURLGet($url, $request, 'post')) { return; }
        if (substr($result,0,1) == $this->encapChar) { $result = substr($result,1); }
        $result = preg_replace('/.{*}' . $this->encapChar . '$/', '', $result);
        $resp = explode($this->encapChar . $this->delimiter . $this->encapChar, $result);
        msgDebug("\nResponse back from Authorize.net = ".print_r($resp, true));
        if (isset($resp[0]) && $resp[0] == '2') { // declined
            msgAdd(sprintf($this->lang['err_process_decline'], $resp[0], $resp[3]));
            msgLog(sprintf($this->lang['err_process_decline'], $resp[0], $resp[3]));
            return;
        } elseif (isset($resp[0]) && $resp[0] == '1') { // success
            if (isset($resp[38]) && $resp[38] != 'M') {
                msgAdd(sprintf($this->lang['err_cvv_mismatch'], $this->lang['CVV_'.$resp[38]]), 'caution');
            }
            if (isset($resp[5]) && !in_array($resp[5], ['X','Y'])) {
                msgAdd(sprintf($this->lang['err_avs_mismatch'], $this->lang['AVS_'.$resp[5]]), 'caution');
            }
            $cvv = isset($resp[38]) && $resp[38] ? $this->lang['CVV_'.$resp[38]] : 'n/a';
            msgAdd(sprintf($this->lang['msg_approval_success'], $resp[3], $resp[4], $cvv), 'success');
            return ['txID'=>$resp[6], 'txTime'=>date('Y-m-d h:i:s'), 'code'=>$resp[4]];
        } // else other error
        msgAdd($this->lang['err_process_failed'].' - '.$resp[3]);
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

    /**
     * Tries to guess the invoice number and po number of the first pmt record of the item array
     * @param type $ledger
     * @return type
     */
    private function guessInv($ledger)
    {
        $refs = ['inv'=>$ledger->main['invoice_num'], 'po'=>$ledger->main['invoice_num']];
        if (empty($ledger->item)) { return $refs; }
        foreach ($ledger->item as $row) {
            if ($row['gl_type'] <> 'pmt') { continue; } // just the first row
            $vals = explode(' ', $row['description']);
            if (!empty($vals[1])) { $refs['inv']= $vals[1]; }
            if (!empty($vals[3])) { $refs['po'] = $vals[3]; }
            break;
        }
        return $refs;
    }
}
