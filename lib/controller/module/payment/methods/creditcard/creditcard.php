<?php
/*
 * Payment Method - Generic Credit Card, just stores the data
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
 * @filesource /lib/controller/module/payment/methods/creditcard.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');

class creditcard
{
    public $moduleID = 'payment';
    public $methodDir= 'methods';
    public $code     = 'creditcard';

    public function __construct()
    {
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $pmtDef        = getModuleCache($this->moduleID, 'settings', 'general', false, []);
        $this->settings= ['cash_gl_acct'=>$pmtDef['gl_payment_c'],'disc_gl_acct'=>$pmtDef['gl_discount_c'],'prefix'=>'CC','prefixAX'=>'AX','order'=>10];
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        return [
            'cash_gl_acct'=> ['label'=>$this->lang['gl_payment_c_lbl'], 'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_cash_gl_acct",'value'=>$this->settings['cash_gl_acct']]],
            'disc_gl_acct'=> ['label'=>$this->lang['gl_discount_c_lbl'],'position'=>'after','attr'=>['type'=>'ledger','id'=>"{$this->code}_disc_gl_acct",'value'=>$this->settings['disc_gl_acct']]],
            'prefix'  => ['label'=>$this->lang['prefix_lbl'], 'position'=>'after', 'attr'=>['size'=>'5','value'=>$this->settings['prefix']]],
            'prefixAX'=> ['label'=>$this->lang['prefix_amex'],'position'=>'after', 'attr'=>['size'=>'5','value'=>$this->settings['prefix']]],
            'order'   => ['label'=>lang('order'), 'position'=>'after', 'attr'=>  ['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    public function render(&$output, $data, $values=[], $dispFirst=false)
    {
        msgDebug("\nWorking with values = ".print_r($values, true));
        $cc_exp = pullExpDates();
        $this->viewData = [
            'selCards'  => ['attr'=>['type'=>'select'],'events'=>['onChange'=>"creditcardRefNum('stored');"]],
            'save'      => ['label'=>lang('save'),'break'=>true,'attr'=>['type'=>'checkbox','value'=>'1']],
            'name'      => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_name')],
            'number'    => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_number'),'events'=>['onChange'=>"convergeRefNum('number');"]],
            'month'     => ['label'=>lang('payment_expiration'),'options'=>['width'=>130],'values'=>$cc_exp['months'],'attr'=>['type'=>'select','value'=>date('m')]],
            'year'      => ['break'=>true,'options'=>['width'=>70],'values'=>$cc_exp['years'],'attr'=>['type'=>'select','value'=>date('Y')]],
            'cvv'       => ['options'=>['width'=> 45],'label'=>lang('payment_cvv')]];
        if (isset($values['method']) && $values['method']==$this->code && isset($data['fields']['id']['attr']['value'])) { // edit
            $this->viewData['number']['attr']['value'] = isset($values['hint']) ? $values['hint'] : '****';
            $invoice_num = $invoice_amex = $data['fields']['invoice_num']['attr']['value'];
            $gl_account  = $data['fields']['gl_acct_id']['attr']['value'];
            $discount_gl = $this->getDiscGL($data['fields']['id']['attr']['value']);
            $show_s = false;  // since it's an edit, all adjustments need to be made at the gateway, this prevents duplicate charges when re-posting a transaction
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
        }
        $output['jsBody'][] = "
arrPmtMethod['$this->code'] = {cashGL:'$gl_account', discGL:'$discount_gl', ref:'$invoice_num', refAX:'$invoice_amex'};
function payment_$this->code() {
    bizTextSet('invoice_num', arrPmtMethod['$this->code'].ref);
    bizGridSet('gl_acct_id', arrPmtMethod['$this->code'].cashGL);
    bizGridSet('totals_discount_gl', arrPmtMethod['$this->code'].discGL);
}
function creditcardRefNum(type) {
    if (type=='stored') { var ccNum = bizSelGet('{$this->code}selCards'); }
      else { var ccNum = bizTextGet('{$this->code}_number');  }
    var prefix= ccNum.substr(0, 2);
    var newRef = prefix=='37' ? arrPmtMethod['$this->code'].refAX : arrPmtMethod['$this->code'].ref;
    bizTextSet('invoice_num', newRef);
}";
        if ($this->code == $dispFirst) { $output['jsReady'][] = "bizTextSet('invoice_num', '$invoice_num');"; }
        $output['body'] .= html5($this->code.'_action', ['label'=>lang('stored'), 'hidden'=>($show_s?false:true),'attr'=>['type'=>'radio','value'=>'s','checked'=>$checked=='s'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}s').show();"]]).
html5($this->code.'_action', ['label'=>lang('new'),    'hidden'=>($show_n?false:true),'attr'=>['type'=>'radio','value'=>'n','checked'=>$checked=='n'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').show();"]]).
html5($this->code.'_action', ['label'=>$this->lang['at_creditcard'],                    'attr'=>['type'=>'radio','value'=>'w','checked'=>$checked=='w'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').hide();"]]).'<br />';
$output['body'] .= '<div id="div'.$this->code.'s">';
if ($show_s) { $output['body'] .= lang('payment_stored_cards').'<br />'.html5($this->code.'selCards', $this->viewData['selCards']); }
$output['body'] .= '</div>
<div id="div'.$this->code.'n"'.(!$show_s?'':'style=" display:none"').'>'.
    html5($this->code.'_save',  $this->viewData['save']).
    html5($this->code.'_name',  $this->viewData['name']).
    html5($this->code.'_number',$this->viewData['number']).
    html5($this->code.'_month', $this->viewData['month']).
    html5($this->code.'_year',  $this->viewData['year']).
    html5($this->code.'_cvv',   $this->viewData['cvv']).'
</div>';
    }
/*
    public function paymentAuth($fields, $ledger)
    {
        return true;
        $submit_data = [
            'ssl_transaction_type'  => 'ccauthorize',
            'ssl_merchant_id'       => $this->settings['merchant_id'],
            'ssl_user_id'           => $this->settings['user_id'],
            'ssl_pin'               => $this->settings['pin'],
//?         'ssl_track_data'        => '', // The raw Track I or Track II data from the magnetic strip on the card
//            'ssl_account_type'      => '', // Account Type (0 = checking, 1 = saving). Required for debit.
//            'ssl_dukpt'             => '', // This is the value returned by the PIN pad device, which was used to encrypt the cardholder's Personal Identification Number (PIN) using the Derived Unique Key Per Transaction (DUKPT) method. This value cannot be stored. Required.
//            'ssl_key_pointer'       => '', // Triple-DES DUKPT pointer that indicates to Converge which encryption key was used for US Debit transactions. Value must be set to T. Required.
//            'ssl_pin_block'         => '', // The encrypted PIN block as returned from the PIN pad device. This value cannot be stored. Required.
            'ssl_card_number'       => $fields['number'],
            'ssl_exp_date'          => $fields['month'] . substr($fields['year'], -2), // requires 2 digit year
            'ssl_amount'            => $ledger->main['total_amount'],
            'ssl_cvv2cvc2'          => $fields['cvv'],
            'ssl_invoice_number'    => $ledger->main['invoice_num'],
//            'ssl_card_present'      => '', // recommended for POS
//            'ssl_customer_code'     => '', // Customer code for purchasing card transactions
            'ssl_salestax'          => isset($ledger->main['sales_tax']) ? $ledger->main['sales_tax'] : 0,
            'ssl_cvv2cvc2_indicator'=> $fields['cvv'] ? '1' : '9', // if cvv2 exists, present else not present
            'ssl_description'       => $ledger->main['description'],
            'ssl_company'           => str_replace('&', '-', $fields['first_name'].' '.$fields['last_name']),
//            'ssl_first_name'        => $request['bill_first_name'], // recommended for hand-keyed transactions, bizuno uses company
//            'ssl_last_name'         => $request['bill_last_name'], // recommended for hand-keyed transactions, bizuno uses company
            'ssl_avs_address'       => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 20)), // maximum of 20 characters per spec
            'ssl_address2'          => str_replace('&', '-', substr($ledger->main['address2_b'], 0, 20)),
            'ssl_city'              => $ledger->main['city_b'],
            'ssl_state'             => $ledger->main['state_b'],
            'ssl_country'           => $ledger->main['country_b'],
            'ssl_avs_zip'           => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
            'ssl_phone'             => substr(preg_replace("/[^0-9]/", "", $ledger->main['telephone1_b']), 0, 14),
            'ssl_email'             => isset($ledger->main['email_b']) ? $ledger->main['email_b'] : getModuleCache('bizuno', 'settings', 'company', 'email'),
            'ssl_show_form'         => 'FALSE',
            'ssl_result_format'     => 'ASCII',
            ];
        msgDebug("\nConverge sale working with fields = ".print_r($fields, true));
        if (sizeof($submit_data) == 0) { return true; } // nothing to send to gateway
        if (!$resp = $this->queryMerchant($submit_data)) { return; }
        return $resp;
    }

    /**
     * This method will capture payment, if payment was authorized in a prior transaction, a ccComplete is done
     * @param integer $rID - record id from table journal_main to generate the capture, the transaction ID will be pulled from there.
     * @return array - On success, false (with messageStack message) on unsuccessful deletion
     */
/*
    public function sale($fields, $ledger)
    {
        return true;
        msgDebug("\nConverge sale working with fields = ".print_r($fields, true));
        $submit_data = [];
        switch ($fields['action']) {
            case 's': // saved card, already decoded, just process like new card
            case 'n': // new card
                $submit_data = [
                    'ssl_transaction_type'  => 'ccsale',
                    'ssl_merchant_id'       => $this->settings['merchant_id'],
                    'ssl_user_id'           => $this->settings['user_id'],
                    'ssl_pin'               => $this->settings['pin'],
//?                    'ssl_track_data'        => '', // The raw Track I or Track II data from the magnetic strip on the card
//                    'ssl_account_type'      => '', // Account Type (0 = checking, 1 = saving). Required for debit.
//                    'ssl_dukpt'             => '', // This is the value returned by the PIN pad device, which was used to encrypt the cardholder's Personal Identification Number (PIN) using the Derived Unique Key Per Transaction (DUKPT) method. This value cannot be stored. Required.
//                    'ssl_key_pointer'       => '', // Triple-DES DUKPT pointer that indicates to Converge which encryption key was used for US Debit transactions. Value must be set to T. Required.
//                    'ssl_pin_block'         => '', // The encrypted PIN block as returned from the PIN pad device. This value cannot be stored. Required.
                    'ssl_card_number'       => $fields['number'],
                    'ssl_exp_date'          => $fields['month'] . substr($fields['year'], -2), // requires 2 digit year
                    'ssl_amount'            => $ledger->main['total_amount'],
                    'ssl_cvv2cvc2'          => $fields['cvv'],
                    'ssl_invoice_number'    => $ledger->main['invoice_num'],
//                    'ssl_card_present'      => '', // recommended for POS
//                    'ssl_customer_code'     => '', // Customer code for purchasing card transactions
                    'ssl_salestax'          => isset($ledger->main['sales_tax']) ? $ledger->main['sales_tax'] : 0,
                    'ssl_cvv2cvc2_indicator'=> $fields['cvv'] ? '1' : '9', // if cvv2 exists, present else not present
                    'ssl_description'       => $ledger->main['description'],
                    'ssl_company'           => str_replace('&', '-', $fields['first_name'].' '.$fields['last_name']),
//                    'ssl_first_name'        => $request['bill_first_name'], // recommended for hand-keyed transactions, bizuno uses company
//                    'ssl_last_name'         => $request['bill_last_name'], // recommended for hand-keyed transactions, bizuno uses company
                    'ssl_avs_address'       => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 20)), // maximum of 20 characters per spec
                    'ssl_address2'          => str_replace('&', '-', substr($ledger->main['address2_b'], 0, 20)),
                    'ssl_city'              => $ledger->main['city_b'],
                    'ssl_state'             => $ledger->main['state_b'],
                    'ssl_country'           => $ledger->main['country_b'],
                    'ssl_avs_zip'           => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
                    'ssl_phone'             => substr(preg_replace("/[^0-9]/", "", $ledger->main['telephone1_b']), 0, 14),
                    'ssl_email'             => isset($ledger->main['email_b']) ? $ledger->main['email_b'] : getModuleCache('bizuno', 'settings', 'company', 'email'),
                    'ssl_show_form'         => 'FALSE',
                    'ssl_result_format'     => 'ASCII',
                    ];
                break;
            case 'w': // website capture, just post it
                msgAdd($this->lang['msg_capture_manual'].' '.$this->lang['msg_website']);
                break;
        }
        msgDebug("\nConverge sale working with fields = ".print_r($fields, true));
        if (sizeof($submit_data) == 0) { return true; } // nothing to send to gateway
        if (!$resp = $this->queryMerchant($submit_data)) { return; }
        return $resp;
    }
*/
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
