<?php
/*
 * PhreeBooks Totals - shipping
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
 * @version    3.x Last Update: 2019-03-19
 * @filesource /lib/controller/module/phreebooks/totals/shipping/shipping.php
 */

namespace bizuno;

class shipping
{
    public $code     = 'shipping';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $hidden   = false;

    public function __construct()
    {
        if (!defined('JOURNAL_ID')) { define('JOURNAL_ID', 2); }
        $this->cType   = defined('CONTACT_TYPE') ? CONTACT_TYPE : 'c';
        $glV           = getModuleCache('extShipping','settings','general','gl_shipping_v', getModuleCache('phreebooks','settings','vendors',  'gl_expense'));
        $glC           = getModuleCache('extShipping','settings','general','gl_shipping_c', getModuleCache('phreebooks','settings','customers','gl_sales'));
        $this->settings= ['gl_type'=>'frt','journals'=>'[3,4,6,7,9,10,12,13,19,21]','gl_account'=>in_array(JOURNAL_ID, [3,4,6,7,21]) ? $glV : $glC,'order'=>60];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        return [
            'gl_type'   => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'  => ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'gl_account'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_account']]], // set in extShipping settings
            'order'     => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    public function glEntry(&$main, &$item, &$begBal=0)
    {
        $shipping = clean('freight', ['format'=>'float','default'=>0], 'post');
//      if ($shipping == 0) return; // this will discard bill recipient, 3rd party and resi information if paid by customer
        $billType = clean('totals_shipping_bill_type', ['format'=>'alpha_num','default'=>'sender'], 'post');
        $desc = "title:".$this->lang['title'];
        $desc.= ";resi:".clean('totals_shipping_resi', 'bool', 'post');
        $desc.= ";type:".$billType;
        if ($billType <> 'sender') { $desc .= ":".clean('totals_shipping_bill_acct', 'alpha_num', 'post'); }
        $item[] = [
            'id'           => clean("totals_{$this->code}_id", ['format'=>'float','default'=>0], 'post'),
            'ref_id'       => clean('id', 'integer', 'post'),
            'gl_type'      => $this->settings['gl_type'],
            'qty'          => 1,
            'description'  => $desc,
            'debit_amount' => in_array($main['journal_id'], [3, 4, 6,13,21]) ? $shipping : 0,
            'credit_amount'=> in_array($main['journal_id'], [7, 9,10,12,19]) ? $shipping : 0,
            'gl_account'   => clean("totals_{$this->code}_gl", ['format'=>'text','default'=>$this->settings['gl_account']], 'post'),
            'post_date'    => $main['post_date']];
        $main['freight']    = $shipping;
        $main['method_code']= clean('method_code', ['format'=>'cmd','default'=>''], 'post');
        $begBal += $shipping;
        $taxShipping   = getModuleCache('phreebooks', 'settings', 'general', 'shipping_taxed') ? 1 : 0;
        if ($taxShipping && isset($main['contact_id_b']) && $main['contact_id_b']) {
            $shipTax = $this->getShippingTaxGL($shipping, $main, $item);
            $begBal += roundAmount($shipTax);
            $main['sales_tax'] += roundAmount($shipTax);
        }
        msgDebug("\nShipping is returning balance = $begBal");
    }

    public function render(&$output, $data=[]) {
        $billingTypes = ['sender'=>lang('sender'),'3rdparty'=>$this->lang['third_party'],'recip'=>lang('recipient'),'collect'=>lang('collect'),'other'=>lang('other')];
        $choices = [['id'=>'', 'text'=>lang('select')]];
        $carriers= sortOrder(getModuleCache('extShipping', 'carriers'));
        foreach ($carriers as $carrier) {
            if ($carrier['status'] && isset($carrier['settings']['services'])) {
                $choices = array_merge_recursive($choices, $carrier['settings']['services']);
            }
        }
        $this->fields = [
            'totals_shipping_id' => ['label'=>'', 'attr'=>  ['type'=>'hidden']],
            'totals_shipping_gl' => ['label'=>lang('gl_account'),'attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
            'totals_shipping_bill_type'=> ['label'=>$this->lang['ship_bill_to'], 'values'=>viewKeyDropdown($billingTypes),'attr'=>['type'=>'select']],
            'totals_shipping_bill_acct'=> ['label'=>$this->lang['ship_bill_acct_num'],'events'=>['onChange'=>"bizSelSet('totals_shipping_bill_type', '3rdparty');"]],
            'totals_shipping_resi'=> ['label'=>lang('residential_address'),'attr'=>['type'=>'checkbox', 'value'=>'1']],
            'totals_shipping_opt' => ['icon'=>'settings','size'=>'small','events'=> ['onClick'=>"jq('#totals_shipping_div').toggle('slow');"]],
            'method_code' => ['options'=>['width'=>300], 'values'=>$choices, 'attr'=>['type'=>'select']],
            'totals_shipping_est' =>['attr'=>['type'=>'button','value'=>lang('rate_quote')],'events'=>['onClick'=>"shippingEstimate(".JOURNAL_ID.");"]],
            'freight' => ['label'=>$this->lang['label'],'lblStyle'=>['min-width'=>'60px'],'attr'=>['type'=>'currency','value'=>0],
                'events'=>['onChange'=>"bizTextSet('freight', newVal, 'currency'); if (formatCurrency(newVal)!=oldVal) { totalUpdate('total shipping'); }"]]];
        $resi = getModuleCache('extShipping', 'settings', 'general', 'resi_checked', 1);
        if ($resi) { $this->fields['totals_shipping_resi']['attr']['checked'] = 'checked'; }
        if (isset($data['items'])) {
            foreach ($data['items'] as $row) { // fill in the data if available
                if ($row['gl_type'] == $this->settings['gl_type']) {
                    $settings = explode(";", $row['description']);
                    foreach ($settings as $setting) {
                        $value = explode(":", $setting);
                        if ($value[0] == 'resi') {
                            if (isset($value[1]) && $value[1]==0) { unset($this->fields['totals_shipping_resi']['attr']['checked']); }
                        }
                        if ($value[0] == 'type') {
                            $this->fields['totals_shipping_bill_type']['attr']['value'] = isset($value[1]) ? $value[1] : 'sender';
                            $this->fields['totals_shipping_bill_acct']['attr']['value'] = isset($value[2]) ? $value[2] : '';
                        }
                    }
                    $this->fields['totals_shipping_id']['attr']['value'] = isset($row['id']) ? $row['id'] : 0;
                    $this->fields['totals_shipping_gl']['attr']['value'] = $row['gl_account'];
                    $this->fields['freight']['attr']['value'] = $row['credit_amount'] + $row['debit_amount'];
                }
            }
        }
        if (!empty($data['fields']['method_code']['attr']['value'])) {
            $this->fields['method_code']['attr']['value']= $data['fields']['method_code']['attr']['value'];
        }
        $hide = $this->hidden ? ';display:none' : '';
        $output['body'] .= '<div style="clear:both;text-align:right'.$hide.'">';
        $output['body'] .= html5('totals_shipping_id',$this->fields['totals_shipping_id']);
        $output['body'] .= html5('',                  $this->fields['totals_shipping_est']);
        $output['body'] .= html5('freight',           $this->fields['freight']);
        $output['body'] .= html5('',                  $this->fields['totals_shipping_opt'])."<br />";
        $output['body'] .= "</div>";
        if ($this->hidden) { $output['body'] .= $this->lang['label'].'<br />'; }
        $output['body'] .= '<div style="text-align:right">'.html5('method_code',$this->fields['method_code'])."</div>";
        $output['body'] .= '<div id="totals_shipping_div" style="display:none" class="layout-expand-over">';
        $output['body'] .= html5('totals_shipping_resi',      $this->fields['totals_shipping_resi'])."<br />";
        $output['body'] .= html5('totals_shipping_bill_type', $this->fields['totals_shipping_bill_type'])."<br />";
        $output['body'] .= html5('totals_shipping_bill_acct', $this->fields['totals_shipping_bill_acct'])."<br />";
        $output['body'] .= html5('totals_shipping_gl',        $this->fields['totals_shipping_gl']);
        $output['body'] .= "</div>";
        $output['jsHead'][] = $this->jsTotal($data);
    }

    public function jsTotal($data=[])
    {
        // @todo Deprecate the taxShipping setting from extShipping, s/b set at PhreeBooks settings
        $taxShipping= getModuleCache('phreebooks', 'settings', 'general', 'shipping_taxed') ? 1 : 0;
        $jID        = $data['fields']['journal_id']['attr']['value'];
        $type       = in_array($jID, [3,4,6,7,17,20,21]) ? 'v' : 'c';
        return "function totals_shipping(begBalance) {
    var newBalance = begBalance;
    var shipping   = parseFloat(bizNumGet('freight'));
    var taxShipping= $taxShipping;
    if (isNaN(shipping)) { shipping = 0; }
    bizNumSet('freight', shipping);
    newBalance += shipping;

    if (!taxShipping) return newBalance;
    var taxTotal  = 0;
    var taxOutput = new Array();
    for (var idx=0; idx<bizDefaults.taxRates.$type.rows.length; idx++) {
        if (def_contact_tax_id != bizDefaults.taxRates.$type.rows[idx].id) { continue; }
        if (typeof bizDefaults.taxRates.$type.rows[idx].auths != 'undefined') {
            var taxAuths = bizDefaults.taxRates.$type.rows[idx].auths;
            if (typeof taxAuths != 'undefined') {
                for (var i=0; i<taxAuths.length; i++) {
                    cID = taxAuths[i].text;
                    taxOutput[cID] = new Object();
                    taxOutput[cID].amount = (shipping * (taxAuths[i].rate / 100));
                }
            }
        }
    }
    for (key in taxOutput) {
        if (taxOutput[key].amount == 0) continue;
        taxTotal += taxOutput[key].amount;
    }
    newTaxTotal = roundCurrency(taxTotal);
    taxRunning += newTaxTotal;
    return newBalance + newTaxTotal;
}";
    }

    /**
     * Calculates and creates a journal item entry for sales tax on shipping at the contacts rate
     * @param type $shipping
     * @param type $main
     * @param type $item
     * @return int
     */
    private function getShippingTaxGL($shipping, $main, &$item)
    {
        msgDebug("\nEntering getShippingTaxGL with shipping = $shipping");
        if (!empty($main['tax_rate_id'])) {
            $taxID = $main['tax_rate_id']; // pull from main record as it has been set
        } else {
          $taxID = dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'tax_rate_id', "id={$main['contact_id_b']}"); // causes bad behavior is contact record is not set properly
        }
        if (!$taxID || $taxID==-1) { return 0; } // return if no tax or per inventory item,
        $gl      = [];
        $totalTax= 0;
        $rates   = loadTaxes($this->cType);
        while ($rate = array_shift($rates)) { if ($rate['id'] == $taxID) { break; } }
        if (!$rate) { return msgAdd($this->lang['msg_no_tax_found']); }
        foreach ($rate['auths'] as $auth) {
            $tax = ($auth['rate'] / 100) * $shipping;
            if (!isset($gl[$auth['glAcct']]['text']))  { $gl[$auth['glAcct']]['text']  = []; }
            if (!isset($gl[$auth['glAcct']]['amount'])){ $gl[$auth['glAcct']]['amount']= 0;  }
            if (!in_array($auth['text'], $gl[$auth['glAcct']]['text'])) { $gl[$auth['glAcct']]['text'][] = $auth['text']; }
            $gl[$auth['glAcct']]['amount'] += $tax;
        }
        msgDebug("\nbuilding the GL entry with values: ".print_r($gl, true));
        foreach ($gl as $glAcct => $value) {
            if ($value['amount'] == 0) { continue; }
            $item[] = [
                'ref_id'       => $main['id'],
                'gl_type'      => 'tax',
                'qty'          => '1',
                'description'  => implode(' : ', $value['text']),
                'debit_amount' => in_array($main['journal_id'], [3, 4, 6, 7,21]) ? $value['amount'] : 0,
                'credit_amount'=> in_array($main['journal_id'], [9,10,12,13,19]) ? $value['amount'] : 0,
                'gl_account'   => $glAcct,
                'post_date'    => $main['post_date']];
            $totalTax += $value['amount'];
        }
        return $totalTax;
    }
}
