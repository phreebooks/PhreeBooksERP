<?php
/*
 * Payment Method - Paymentech (Chase)
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
 * @version    3.x Last Update: 2019-06-24
 * @filesource /lib/controller/module/payment/methods/paymentech.php
 *
 * Source Information:
 * @copyright 2013 Paymentech, Incorporated, Two Concourse Parkway, Suite 800, Atlanta, GA 30328
 * @link https://secure.paymentech.com/devcenter/home - Main Website
 * @link https://secure.paymentech.com/devcenter/products - Developer Center
 *
 */

namespace bizuno;

if (!defined('PAYMENT_PAYMENTECH_URL'))     { define('PAYMENT_PAYMENTECH_URL',     'https://ws1.chasepaymentech.com/PaymentechGateway'); }
if (!defined('PAYMENT_PAYMENTECH_URL_TEST')){ define('PAYMENT_PAYMENTECH_URL_TEST','https://wsvar1.chasepaymentech.com/PaymentechGateway'); }
if (!defined('PAYMENT_PAYMENTECH_WSDL'))    { define('PAYMENT_PAYMENTECH_WSDL',    'TBD'); }

bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');

class paymentech
{
    public  $moduleID = 'payment';
    public  $methodDir= 'methods';
    public  $code     = 'paymentech';
    private $mode     = 'prod'; // choices are 'test' (Test) or 'prod' (Production)

    public function __construct()
    {
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $pmtDef        = getModuleCache($this->moduleID, 'settings', 'general', false, []);
        $this->settings= ['cash_gl_acct'=>$pmtDef['gl_payment_c'],'disc_gl_acct'=>$pmtDef['gl_discount_c'],'order'=>10,'merchant_id'=>'','user_id'=>'',
            'pin'=>'','auth_type'=>'Authorize/Capture','prefix'=>'CC','prefixAX'=>'AX','allowRefund'=>'0'];
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
            'order'       => ['label'=>lang('order'),               'position'=>'after','attr'=>['type'=>'integer', 'size'=>'3','value'=>$this->settings['order']]],
            'merchant_bin'=> ['label'=>$this->lang['merchant_bin'], 'position'=>'after','attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['merchant_id']]],
            'merchant_id' => ['label'=>$this->lang['merchant_id'],  'position'=>'after','attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['merchant_id']]],
            'user_name'   => ['label'=>$this->lang['username'],     'position'=>'after','attr'=>['type'=>'text', 'size'=>'20','value'=>$this->settings['user_id']]],
            'user_pass'   => ['label'=>$this->lang['password'],     'position'=>'after','attr'=>['type'=>'text','value'=>$this->settings['pin']]],
            'auth_type'   => ['label'=>$this->lang['auth_type'],    'values'=>$auths,   'attr'=>['type'=>'select','value'=>$this->settings['auth_type']]],
            'prefix'      => ['label'=>$this->lang['prefix_lbl'],   'position'=>'after','attr'=>['size'=>'5','value'=>$this->settings['prefix']]],
            'allowRefund' => ['label'=>$this->lang['allow_refund'], 'values'=>$noYes,   'attr'=>['type'=>'select','value'=>$this->settings['allowRefund']]]];
    }

    public function render(&$output, $data, $values=[], $dispFirst=false)
    {
        msgDebug("\nWorking with values = ".print_r($values, true));
        $cc_exp = pullExpDates();
        $this->viewData = [
            'trans_code'=> ['attr'=>['type'=>'hidden']],
            'selCards'  => ['attr'=>['type'=>'select'],'events'=>['onChange'=>"paymentechRefNum('stored');"]],
            'save'      => ['label'=>lang('save'),'break'=>true,'attr'=>['type'=>'checkbox','value'=>'1']],
            'payment_id'=> ['attr'=>['type'=>'hidden']], // hidden
            'name'      => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_name')],
            'number'    => ['options'=>['width'=>200],'break'=>true,'label'=>lang('payment_number'),'events'=>['onChange'=>"paymentechRefNum('number');"]],
            'month'     => ['label'=>lang('payment_expiration'),'options'=>['width'=>130],'values'=>$cc_exp['months'],'attr'=>['type'=>'select','value'=>date('m')]],
            'year'      => ['break'=>true,'options'=>['width'=>70],'values'=>$cc_exp['years'],'attr'=>['type'=>'select','value'=>date('Y')]],
            'cvv'       => ['options'=>['width'=> 45],'label'=>lang('payment_cvv')]];
        if (!empty($values['method']) && $values['method']==$this->code && !empty($data['fields']['id']['attr']['value'])) { // edit
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
                    $show_s      = false;
                } else {
                    $checked     = 's';
                    $show_s      = true;
                    $first_prefix= $this->viewData['selCards']['values'][0]['hint'];
                    $invoice_num = substr($first_prefix, 0, 2)=='37' ? $invoice_amex : $invoice_num;
                }
            } else { $show_s = false; }
            if (!empty($values['trans_code'])) {
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
function paymentechRefNum(type) {
    if (type=='stored') { var ccNum = jq('#{$this->code}selCards').val(); }
      else { var ccNum = jq('#{$this->code}_number').val();  }
    var prefix= ccNum.substr(0, 2);
    var newRef = prefix=='37' ? arrPmtMethod['$this->code'].refAX : arrPmtMethod['$this->code'].ref;
    bizTextSet('invoice_num', newRef);
}";
        if ($this->code == $dispFirst) { $output['jsReady'][] = "bizTextSet('invoice_num', '$invoice_num');"; }
        $output['body'] .= html5($this->code.'_action', ['label'=>lang('capture'),'hidden'=>($show_c?false:true),'attr'=>['type'=>'radio','value'=>'c','checked'=>$checked=='c'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}c').show();"]]).
html5($this->code.'_action', ['label'=>$this->lang['stored'], 'hidden'=>($show_s?false:true),'attr'=>['type'=>'radio','value'=>'s','checked'=>$checked=='s'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}n').hide(); jq('#div{$this->code}s').show();"]]).
html5($this->code.'_action', ['label'=>lang('new'),    'hidden'=>($show_n?false:true),'attr'=>['type'=>'radio','value'=>'n','checked'=>$checked=='n'?true:false],
    'events'=>  ['onChange'=>"jq('#div{$this->code}c').hide(); jq('#div{$this->code}s').hide(); jq('#div{$this->code}n').show();"]]).
html5($this->code.'_action', ['label'=>$this->lang['at_paymentech'],                    'attr'=>['type'=>'radio','value'=>'w','checked'=>$checked=='w'?true:false],
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
            'transType'                => 'A', // 'AC' for capture
            'bin'                      => $this->settings['merchant_bin'], // Assigned by Chase Paymentech. 000001 Stratus, 000002 PNS
            'merchantID'               => $this->settings['merchant_id'], // Gateway merchant account number assigned by Chase Paymentech
            'terminalID'               => $this->settings['terminal_id'], // Merchant Terminal ID assigned by Chase Paymentech
            'orbitalConnectionUsername'=> $this->settings['user_name'],
            'orbitalConnectionPassword'=> $this->settings['user_pass'],
            'version'                  => PAYMENT_PAYMENTECH_WSDL,
            'industryType'             => 'EC',
            'ccAccountNum'             => $fields['number'],
            'ccExp'                    => $fields['month'] . substr($fields['year'], -2), // Format: YYYYMM
            'amount'                   => $ledger->main['total_amount'],
            'ccCardVerifyPresenceInd'  => strlen($fields['cvv'])>0 ? '1' : '9', // if cvv2 exists, present else not present
            'ccCardVerifyNum'          => $fields['cvv'],
            'orderID'                  => $refs['inv'],
//          'comments'                 => '', // Free-form comments
            'taxInd'                   => !empty($ledger->main['sales_tax']) ? '1' : '0', // 0 Not provided, 1 Included, 2 Non-Taxable
            'taxAmount'                => !empty($ledger->main['sales_tax']) ? $ledger->main['sales_tax'] : 0,
            'ssl_description'          => $ledger->main['description'],
            'avsName'                  => str_replace('&', '-', $fields['first_name'].' '.$fields['last_name']),
            'avsAddress1'              => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 20)), // maximum of 20 characters per spec
            'avsAddress2'              => str_replace('&', '-', substr($ledger->main['address2_b'], 0, 20)),
            'avsCity'                  => $ledger->main['city_b'],
            'avsState'                 => $ledger->main['state_b'],
            'avsCountryCode'           => $ledger->main['country_b'], // ISO 2 format
            'avsZip'                   => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
            'avsPhone'                 => substr(preg_replace("/[^0-9]/", "", $ledger->main['telephone1_b']), 0, 14),
            ];
        msgDebug("\nPaymentech sale working with fields = ".print_r($fields, true));
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
        msgDebug("\nPaymentech sale working with fields = ".print_r($fields, true));
        $submit_data = [];
        switch ($fields['action']) {
            case 'c': // capture previously authorized transaction
//                $code = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['trans_code', 'debit_amount'], "ref_id={$ledger->main['id']} AND gl_type='ttl'");
                $submit_data = [
                    'ssl_transaction_type'=> 'CCCOMPLETE',
                    'ssl_merchant_id'     => $this->settings['merchant_id'],
                    'ssl_user_id'         => $this->settings['user_id'],
                    'ssl_pin'             => $this->settings['pin'],
                    'ssl_txn_id'          => $fields['txID'], // Unique identifier returned on the original transaction
                    'ssl_amount'          => $ledger->main['total_amount'], // amount of capture, must be less than or equal to auth amount
                    ];
                msgDebug("\nfields = ".print_r($submit_data, true));
//                $desc['hint']  = isset($desc['hint']) ? $desc['hint'] : '****';
                break;
            case 's': // saved card, already decoded, just process like new card
            case 'n': // new card
                $refs = $this->guessInv($ledger);
                $submit_data = [
                    'transType'                => 'AC', // 'A' for authorize
                    'bin'                      => $this->settings['merchant_bin'], // Assigned by Chase Paymentech. 000001 Stratus, 000002 PNS
                    'merchantID'               => $this->settings['merchant_id'], // Gateway merchant account number assigned by Chase Paymentech
                    'terminalID'               => $this->settings['terminal_id'], // Merchant Terminal ID assigned by Chase Paymentech
                    'orbitalConnectionUsername'=> $this->settings['user_name'],
                    'orbitalConnectionPassword'=> $this->settings['user_pass'],
                    'version'                  => PAYMENT_PAYMENTECH_WSDL,
                    'industryType'             => 'EC',
                    'ccAccountNum'             => $fields['number'],
                    'ccExp'                    => $fields['month'] . substr($fields['year'], -2), // Format: YYYYMM
                    'amount'                   => $ledger->main['total_amount'],
                    'ccCardVerifyPresenceInd'  => strlen($fields['cvv'])>0 ? '1' : '9', // if cvv2 exists, present else not present
                    'ccCardVerifyNum'          => $fields['cvv'],
                    'orderID'                  => $refs['inv'],
//                  'comments'                 => '', // Free-form comments
                    'taxInd'                   => !empty($ledger->main['sales_tax']) ? '1' : '0', // 0 Not provided, 1 Included, 2 Non-Taxable
                    'taxAmount'                => !empty($ledger->main['sales_tax']) ? $ledger->main['sales_tax'] : 0,
                    'ssl_description'          => $ledger->main['description'],
                    'avsName'                  => str_replace('&', '-', $fields['first_name'].' '.$fields['last_name']),
                    'avsAddress1'              => str_replace('&', '-', substr($ledger->main['address1_b'], 0, 20)), // maximum of 20 characters per spec
                    'avsAddress2'              => str_replace('&', '-', substr($ledger->main['address2_b'], 0, 20)),
                    'avsCity'                  => $ledger->main['city_b'],
                    'avsState'                 => $ledger->main['state_b'],
                    'avsCountryCode'           => $ledger->main['country_b'], // ISO 2 format
                    'avsZip'                   => preg_replace("/[^A-Za-z0-9]/", "", $ledger->main['postal_code_b']),
                    'avsPhone'                 => substr(preg_replace("/[^0-9]/", "", $ledger->main['telephone1_b']), 0, 14),
                    ];
                break;
            case 'w': // website capture, just post it
                msgAdd($this->lang['msg_capture_manual'].' '.$this->lang['msg_website'], 'caution');
                break;
        }
        msgDebug("\nPaymentech sale working with fields = ".print_r($fields, true));
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
            'ssl_transaction_type'=> 'ccvoid',
            'ssl_merchant_id'     => $this->settings['merchant_id'],
            'ssl_user_id'         => $this->settings['user_id'],
            'ssl_pin'             => $this->settings['pin'],
            'ssl_txn_id'          => $txID]; // Unique identifier returned on the original transaction.
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
            'ssl_transaction_type'=> 'ccreturn',
            'ssl_merchant_id'     => $this->settings['merchant_id'],
            'ssl_user_id'         => $this->settings['user_id'],
            'ssl_pin'             => $this->settings['pin'],
            'ssl_txn_id'          => $results['trans_code'], // Unique identifier returned on the original transaction.
            'ssl_amount'          => number_format($amount, 2, '.', '')]; // Amount to be refunded in full or partial. Must be less or equal to the original purchase, if not supplied original full amount is refunded.
        return $this->queryMerchant($submit_data);
    }

    private function queryMerchant($request=[])
    {
        global $io;
        ini_set("soap.wsdl_cache_enabled", "0");
        if ($this->config->get('fedex_pro_test')) {
            $client = new SoapClient(PATH_TO_TEST_RATE_WSDL, ['trace' => 1]);
        } else {
            $client = new SoapClient(PATH_TO_RATE_WSDL, ['trace' => 1]);
        }

        // RESPONSE STARTING AT PAGE 167 OF DOCS
        // SAMPLE RESPONSE AT PAGE 315


        try {
         $response = $client->NewOrder($request);
         if (in_array($response->HighestSeverity, array('SUCCESS', 'NOTE', 'WARNING'))) {
            if (isset($response->RateReplyDetails) && is_array($response->RateReplyDetails)) {

            }
          } else { //handle errors
              $this->log->write('FedEx trap response: '.htmlspecialchars($client->__getLastResponse()));
          }
        } catch (SoapFault $exception) {
            $this->log->write("[soap fault] ({$exception->faultcode}) {$exception->faultstring}");
            //$this->log->write("Request is " . );
            $error .= 'Oops, we had a problem retrieving a freight quote from FedEx. Please retry the shipping rate quote again by clicking \'Modify\' in Step 3: Delivery Details and press Continue to re-try the quote.';
        }

        msgDebug("\nSending to url:".ODFL_RATE_URL." the string: ".print_r($strXML, true));
        $soapClient= new \SoapClient(ODFL_RATE_URL);
        $results   = $soapClient->getLTLRateEstimate($strXML);
        msgDebug ("\nReceived back from ODFL wsdl: ".print_r($results, true));
        if ($results->return->success) {
            if (strpos($this->settings['service_types'], 'ECF') !== false) $arrRates['ECF'] = [
                'title'  => $this->lang['ECF'],
                'gl_acct'=> $this->settings['gl_acct'],
                'book'   => 0, // $results->return->rateEstimate->grossFreightCharge,
                'cost'   => $results->return->rateEstimate->netFreightCharge,
                'quote'  => $results->return->rateEstimate->netFreightCharge,
                'note'   => sprintf($this->lang['ltl_std'], $results->return->destinationCities->serviceDays),
            ];
            if (strpos($this->settings['service_types'], 'GDF') !== false) $arrRates['GDF'] = [
                'title'  => $this->lang['GDF'],
                'gl_acct'=> $this->settings['gl_acct'],
                'book'   => 0, // $results->return->rateEstimate->grossFreightCharge,
                'cost'   => $results->return->rateEstimate->netFreightCharge + $results->return->rateEstimate->guaranteedServiceOption,
                'quote'  => $results->return->rateEstimate->netFreightCharge + $results->return->rateEstimate->guaranteedServiceOption,
                'note'   => sprintf($this->lang['ltl_guar'], $results->return->destinationCities->serviceDays),
            ];
        } else {
            msgAdd("ODFL Error: ".print_r($results->return->errorMessages, true));
        }



        $tags = '';
        foreach ($request as $key => $value) { if ($value <> '') { $tags .= "<$key>".urlencode(str_replace('&', '+', $value))."</$key>"; } }
        $data = "xmldata=<txn>$tags</txn>";
        msgDebug("\nRequest to send to Paymentech: $data");
        $url = $this->mode=='test' ? PAYMENT_PAYMENTECH_URL_TEST : PAYMENT_PAYMENTECH_URL;
        if (!$strXML = $io->cURLGet($url, $data)) { return; }
        msgDebug("\nReceived raw data back from Paymentech: ".print_r($strXML, true));
        $resp = parseXMLstring($strXML);
        msgDebug("\nReceived back from Paymentech: ".print_r($resp, true));
        if (isset($resp->errorCode)) {
            msgAdd(sprintf($this->lang['err_process_decline'], $resp->errorCode, $resp->errorMessage));
            msgLog(sprintf($this->lang['err_process_decline'], $resp->errorCode, $resp->errorMessage));
            return false;
        } elseif (isset($resp->ssl_result) && $resp->ssl_result == '0') { // update the db with the transaction ID
            if (!empty($resp->ssl_cvv2_response) && $resp->ssl_cvv2_response != 'M') {
                msgAdd(sprintf($this->lang['err_cvv_mismatch'], $this->lang['CVV_'.$resp->ssl_cvv2_response]));
            }
            if (!empty($resp->ssl_avs_response) && !in_array($resp->ssl_avs_response, ['X','Y'])) {
                msgAdd(sprintf($this->lang['err_avs_mismatch'], $this->lang['AVS_'.$resp->ssl_avs_response]));
            }
            $cvv = !empty($resp->ssl_cvv2_response) ? $this->lang['CVV_'.$resp->ssl_cvv2_response] : 'n/a';
            msgAdd(sprintf($this->lang['msg_approval_success'], $resp->ssl_result_message, $resp->ssl_approval_code, $cvv), 'success');
            return ['txID'=>$resp->ssl_txn_id, 'txTime'=>$resp->ssl_txn_time, 'code'=>$resp->ssl_approval_code];
        }
        msgAdd($this->lang['err_process_failed'].' - '.$resp->ssl_result_message);
    }

    /**
     * Tries to guess the discount from the journal item  array
     * @param array $data - journal item rows to extract the discount charges
     * @return type
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
