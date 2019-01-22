<?php
/*
 * Payment Method - Bambora (formerly Beanstream)
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
 * @version    3.x Last Update: 2018-12-10
 * @filesource /lib/controller/module/payment/methods/bambora.php
 *
 * Source Information:
 * @link https://www.bambora.com/en/us/ - Main Website
 */

namespace bizuno;

//if (!defined('PAYMENT_BAMBORA_URL_TOKEN'))   { define('PAYMENT_BAMBORA_URL_TOKEN',  'https://api.na.bambora.com/scripts/tokenization/tokens'); }
//if (!defined('PAYMENT_BAMBORA_URL_PROFILE')) { define('PAYMENT_BAMBORA_URL_PROFILE','https://api.na.bambora.com/v1/profiles'); }
if (!defined('PAYMENT_BAMBORA_URL_PAYMENT')) { define('PAYMENT_BAMBORA_URL_PAYMENT','https://api.na.bambora.com/v1/payments'); }
if (!defined('PAYMENT_BAMBORA_URL_CAPTURE')) { define('PAYMENT_BAMBORA_URL_CAPTURE','https://api.na.bambora.com/v1/payments/RECORD_ID/completions'); }

//if (!defined('PAYMENT_BAMBORA_URL_TOKEN_TEST'))   { define('PAYMENT_BAMBORA_URL_TOKEN_TEST',  'https://api.na.bambora.com/scripts/tokenization/tokens'); }
//if (!defined('PAYMENT_BAMBORA_URL_PROFILE_TEST')) { define('PAYMENT_BAMBORA_URL_PROFILE_TEST','https://api.na.bambora.com/v1/profiles'); }
if (!defined('PAYMENT_BAMBORA_URL_PAYMENT_TEST')) { define('PAYMENT_BAMBORA_URL_PAYMENT_TEST','https://api.na.bambora.com/v1/payments'); }
if (!defined('PAYMENT_BAMBORA_URL_CAPTURE_TEST')) { define('PAYMENT_BAMBORA_URL_CAPTURE_TEST','https://api.na.bambora.com/v1/payments/RECORD_ID/completions'); }

bizAutoLoad(BIZUNO_LIB."controller/module/payment/common.php", 'paymentCommon');
bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');

class bambora extends paymentCommon
{
    public  $moduleID = 'payment';
    public  $methodDir= 'methods';
    public  $code     = 'bambora';
    private $mode     = 'test'; // choices are 'test' (Test) or 'prod' (Production)

    public function __construct()
    {
        $this->settings = array_merge($this->settingsDefaults(), ['auth_type'=>'capture','company_id'=>'','user_id'=>'','merch_id'=>'','passcode'=>'']);
        parent::__construct();
    }

    public function settingsStructure()
    {
        $data = parent::settingsCommon();
        // Unique to this payment method
        $auths = [['id'=>'capture','text'=>lang('capture')], ['id'=>'authorize','text'=>lang('authorize')]];
        $data['auth_type'] = ['label'=>$this->lang['auth_type'],  'values'=>$auths,   'attr'=>['type'=>'select',            'value'=>$this->settings['auth_type']]];
        $data['company_id']= ['label'=>$this->lang['company_id'],'position'=>'after', 'attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['company_id']]];
        $data['user_id']   = ['label'=>$this->lang['user_id'],   'position'=>'after', 'attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['user_id']]];
        $data['merch_id']  = ['label'=>$this->lang['merch_id'],  'position'=>'after', 'attr'=>['type'=>'integer',           'value'=>$this->settings['merch_id']]];
        $data['passcode']  = ['label'=>$this->lang['passcode'],  'position'=>'after', 'attr'=>['type'=>'text',              'value'=>$this->settings['passcode']]];
        return $data;
    }

    public function render(&$output, $data, $values=[], $dispFirst=false)
    {
        parent::renderCommon($output, $data, $values, $dispFirst);

    }

    public function paymentAuth($fields, $ledger=[])
    {
        $submit_data = [
            ];
        msgDebug("\nBambora sale working with fields = ".print_r($fields, true));
        if (sizeof($submit_data) == 0) { return true; } // nothing to send to gateway
        $url = $this->mode=='test' ? PAYMENT_BAMBORA_URL_TEST : PAYMENT_BAMBORA_URL;
        if (!$resp = $this->queryMerchant($url, $submit_data)) { return; }
        return $resp;
    }

    /**
     * @method sale - This method will capture payment, if payment was authorized in a prior transaction, a ccComplete is done
     * @param integer $rID - record id from table journal_main to generate the capture, the transaction ID will be pulled from there.
     * @return array - On success, false (with messageStack message) on unsuccessful deletion
     */
    public function sale($fields, $ledger)
    {
        msgDebug("\nEntering Bambora sale with fields = ".print_r($fields, true));
        msgDebug("\nEntering Bambora sale with settings = ".print_r($this->settings, true));
        $auth_code = base64_encode($this->settings['merch_id'].":".$this->settings['passcode']);
        switch ($fields['action']) {
            case 'c': // capture previously authorized transaction
                $capData = ['amount' => $ledger->main['total_amount'],'payment_method' => 'card'];
                $pmtURL = $this->mode=='test' ? PAYMENT_BAMBORA_URL_PAYMENT_TEST : PAYMENT_BAMBORA_URL_PAYMENT;
                $capURL = "$pmtURL/{$fields['txID']}/completions";
                if (!$capResp = $this->queryMerchant($capURL, $capData)) { return; }
                $fields = array_merge($fields, $capResp);
                break;
            case 's': // saved card, already decoded, just process like new card
            case 'n': // new card
                $complete = $this->settings['auth_type'] == 'authorize' ? false : true;
                $pmtData = [
                    'order_number' => $ledger->main['invoice_num'],
                    'amount' => $ledger->main['total_amount'],
                    'payment_method' => 'card',
//                  'customer_ip' => '',
                    'comments' => $ledger->main['description'],
                    'billing' => [
                        'name' => $ledger->main['primary_name_b'],
                        'address_line1' => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 64)),
//                      'address_line2' => str_replace('&', '-', substr($ledger->main['address2_b'], 0, 64)),
                        'city' => $ledger->main['city_b'],
                        'province' => $ledger->main['state_b'],
                        'country' => clean($ledger->main['country_b'], ['format'=>'country','option'=>'ISO2']), // 2 characters
                        'postal_code' => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
                        'phone_number' => substr(preg_replace("/[^0-9]/", "", $ledger->main['telephone1_b']), 0, 14),
                        'email_address' => isset($ledger->main['email_b']) ? $ledger->main['email_b'] : getModuleCache('bizuno', 'settings', 'company', 'email'),
                    ],
//                  'shipping' => ['name'=>'','address_line1'=>'','city'=>'','province'=>'','country'=>'','postal_code'=>'','phone_number'=>'','email_address'=>''],
                    'card' => [
                        'number'      => $fields['number'],
                        'name'        => str_replace('&', '-', $fields['first_name'].' '.$fields['last_name']),
                        'expiry_month'=> $fields['month'], // 2 characterrs
                        'expiry_year' => substr($fields['year'], -2), // 2 characters
                        'cvd'         => $fields['cvv'],
                        'complete'    => $complete, //  boolean default: true - description: set to false for Pre-Authorize, and true to complete a payment
                    ],
                ];
                $pmtURL = $this->mode=='test' ? PAYMENT_BAMBORA_URL_PAYMENT_TEST : PAYMENT_BAMBORA_URL_PAYMENT;
                $pmtOpts= ["Authorization: Passcode $auth_code"];
                if (!$pmtResp = $this->queryMerchant($pmtURL, $pmtData, $pmtOpts)) { return; }
                $fields = array_merge($fields, $pmtResp);
                break;
            case 'w': // website capture, just post it
                msgAdd($this->lang['msg_capture_manual'].' '.$this->lang['msg_website'], 'caution');
                break;
        }
        msgDebug("\nBambora sale finished with fields = ".print_r($fields, true));
        return $fields;
    }

    /**
     * @method paymentVoid will delete/void a payment made BEFORE the processor commits the payment, typically must be run the same day as the sale
     * @param integer $rID Record id from table journal_main to generate the void
     * @return array merchant response On success, false (with messageStack message) on unsuccessful deletion
     */
    public function paymentVoid($rID=0)
    {
        if (!$rID) { return msgAdd('Bad record ID passed'); }
        $fields = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['trans_code', 'debit_amount', 'credit_amount'], "ref_id=$rID AND gl_type='ttl'");
        if (!$fields['trans_code'] || !$this->settings['allowRefund']) { msgAdd(lang('err_cc_no_transaction_id'), 'caution'); return true; }
        $voidData = ['amount' => $fields['debit_amount'] + $fields['credit_amount']];
        $pmtURL  = $this->mode=='test' ? PAYMENT_BAMBORA_URL_PAYMENT_TEST : PAYMENT_BAMBORA_URL_PAYMENT;
        $voidURL = "$pmtURL/{$fields['trans_code']}/void";
        return $this->queryMerchant($voidURL, $voidData);
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
        if (!$rID) { return msgAdd('Bad record ID passed'); }
        $rfndData = ['amount' => $amount];
        $pmtURL  = $this->mode=='test' ? PAYMENT_BAMBORA_URL_PAYMENT_TEST : PAYMENT_BAMBORA_URL_PAYMENT;
        $rfndURL = "$pmtURL/{$results['trans_code']}/refund";
        return $this->queryMerchant($rfndURL, $rfndData);
    }

    private function queryMerchant($url, $data=[], $opts=[])
    {
        global $io;
        msgDebug("\nRequest to send to Bambora: ".print_r($data, true));
        $jsonData= json_encode($data);
        $reqOpts = ['CURLOPT_HTTPHEADER'=>array_merge($opts, ['Content-Type: application/json', 'Content-Length: '.strlen($jsonData)])];
        if (!$strJSON = $io->cURLGet($url, $jsonData, 'post', $reqOpts)) { return; }
        msgDebug("\nReceived raw data back from Bambora: ".print_r($strJSON, true));
        $resp = json_decode($strJSON);
        msgDebug("\njson decoded: ".print_r($resp, true));
        if (!empty($resp->code) && $resp->code != 1) {
            msgLog(sprintf($this->lang['err_process_decline'], $resp->code, $resp->message));
            return msgAdd(sprintf($this->lang['err_process_decline'], $resp->code, $resp->message));;
        } elseif (isset($resp->approved) && $resp->approved == 1) {
            // bambora doesn't return standard codes, will decline if no match, otherwise approve
            if (isset($resp->card->cvd_result) && $resp->card->cvd_result != 1) {
                msgAdd(sprintf($this->lang['err_cvv_mismatch'], $this->lang['CVV_'.$resp->card->cvd_result]));
            }
            if (!empty($resp->card->avs->id) && !in_array($resp->card->avs->id, ['X','Y'])) {
                msgAdd(sprintf($this->lang['err_avs_mismatch'], $this->lang['AVS_'.$resp->card->avs->id]));
            }
            $cvv = isset($resp->card->cvd_result) ? $this->lang['CVV_'.$resp->card->cvd_result] : 'n/a';
            msgAdd(sprintf($this->lang['msg_approval_success'], $resp->message, $resp->id, $cvv), 'success');
            return ['txID'=>$resp->id, 'txTime'=>$resp->created, 'code'=>$resp->id];
        }
        msgAdd($this->lang['err_process_failed'].' - '.$resp->message);
    }
}
