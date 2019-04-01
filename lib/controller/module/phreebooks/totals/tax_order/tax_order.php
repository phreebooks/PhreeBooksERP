<?php
/*
 * PhreeBooks Total method to calculate sales tax at the order level
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
 * @version    3.x Last Update: 2019-03-06
 * @filesource /controller/module/phreebooks/totals/tax_order/tax_order.php
 */

namespace bizuno;

class tax_order
{
    public $code     = 'tax_order';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $hidden   = false;

    public function __construct()
    {
        $this->settings= ['gl_type'=>'tax','journals'=>'[3,4,6,7,9,10,12,13,19,21]','tax_id_c'=>0,'tax_id_v'=>0,'order'=>40];
        $this->cType   = defined('CONTACT_TYPE') ? CONTACT_TYPE : 'c';
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        return [
            'gl_type' => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'tax_id_v'=> ['label'=>$this->lang['tax_id_v'],'position'=>'after','values'=>viewSalesTaxDropdown('v'),'attr'=>['type'=>'select','value'=>$this->settings['tax_id_v']]],
            'tax_id_c'=> ['label'=>$this->lang['tax_id_c'],'position'=>'after','values'=>viewSalesTaxDropdown('c'),'attr'=>['type'=>'select','value'=>$this->settings['tax_id_c']]],
            'order'   => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    public function glEntry(&$main, &$item, &$begBal=0)
    {
        if (empty($main['tax_rate_id'])) { return; } // no tax so don't create gl entry
        $gl       = $rate = [];
        $totalTax = 0;
        $roundAuth= getModuleCache('phreebooks', 'settings', 'general', 'round_tax_auth', 0);
        $rates    = loadTaxes($this->cType);
        foreach ($rates as $rate) { if ($main['tax_rate_id'] == $rate['id']) { break; } }
        if (empty($rate)) { return; }
        foreach ($rate['auths'] as $auth) {
            $tax = ($auth['rate'] / 100) * $begBal;
            if (!isset($gl[$auth['glAcct']]['text']))  { $gl[$auth['glAcct']]['text']  = []; }
            if (!isset($gl[$auth['glAcct']]['amount'])){ $gl[$auth['glAcct']]['amount']= 0;  }
            if (!in_array($auth['text'], $gl[$auth['glAcct']]['text'])) { $gl[$auth['glAcct']]['text'][] = $auth['text']; }
            $gl[$auth['glAcct']]['amount'] += $tax;
        }
        foreach ($gl as $glAcct => $value) {
            if ($value['amount'] == 0) { continue; }
            if ($roundAuth) { $value['amount'] = roundAmount($value['amount']); }
            $item[] = [
                'ref_id'       => clean('id', 'integer', 'post'),
                'gl_type'      => $this->settings['gl_type'],
                'qty'          => '1',
                'description'  => implode(' : ', $value['text']),
                'debit_amount' => in_array(JOURNAL_ID, [3,4, 6,13,20,21,22])       ? $value['amount'] : 0,
                'credit_amount'=> in_array(JOURNAL_ID, [7,9,10,12,14,16,17,18,19]) ? $value['amount'] : 0,
                'gl_account'   => $glAcct,
                'post_date'    => $main['post_date']];
            $totalTax += $value['amount'];
        }
        $main['sales_tax'] += roundAmount($totalTax);
        $begBal += roundAmount($totalTax);
        msgDebug("\nTaxOrder is returning balance = $begBal");
    }

    public function render(&$output, $data=[])
    {
        $jID   = $data['fields']['journal_id']['attr']['value'];
        $type  = in_array($jID, [3,4,6,7,17,20,21]) ? 'v' : 'c';
        $hide  = $this->hidden ? ';display:none' : '';
        $defTax= $type=='v' ? $this->settings['tax_id_v'] : $this->settings['tax_id_c'];
        $tax_id= !empty($data['fields']['tax_rate_id']['attr']['value']) ? $data['fields']['tax_rate_id']['attr']['value'] : $defTax;
        $this->fields = [
            'totals_tax_order'     => ['label'=>pullTableLabel('journal_main','tax_rate_id',$this->cType).' '.$this->lang['extra_title'],'attr'=>['type'=>'currency','value'=>0,'readonly'=>'readonly']],
            'tax_rate_id'          => ['attr' =>['type'=>'tax'],'defaults'=>['value'=>$tax_id,'type'=>$type]],
            'totals_tax_order_text'=> ['attr' =>['value'=>'textTBD','size'=>'16','readonly'=>'readonly']],
            'totals_tax_order_gl'  => ['label'=>lang('gl_account'), 'attr'=>['type'=>'text', 'value'=>'glTBD','readonly'=>'readonly']],
            'totals_tax_order_amt' => ['attr' =>['value'=>'amtTBD','size'=>'15','style'=>'text-align:right','readonly'=>'readonly']],
            'totals_tax_order_opt' => ['icon' =>'settings', 'size'=>'small','events'=> ['onClick'=>"jq('#phreebooks_totals_tax_order').toggle('slow');"]]];
        $output['body'] .= '<div style="text-align:right'.$hide.'">';
        $output['body'] .= html5('totals_tax_order',$this->fields['totals_tax_order']);
        $output['body'] .= html5('',                $this->fields['totals_tax_order_opt'])."<br />";
        $output['body'] .= html5('tax_rate_id',     $this->fields['tax_rate_id']);
        $output['body'] .= "</div>";
        $output['body'] .= '<div id="phreebooks_totals_tax_order" style="display:none" class="layout-expand-over">';
        $output['body'] .= '  <table id="tableTaxOrder"></table>';
        $output['body'] .= "</div>";

        $temp = "<tr><td>".html5('totals_tax_order_text[]',$this->fields['totals_tax_order_text'])."</td>";
        $temp.= "<td>"    .html5('totals_tax_order_gl[]',  $this->fields['totals_tax_order_gl'])  ."</td>";
        $temp.= "<td>"    .html5('totals_tax_order_amt[]', $this->fields['totals_tax_order_amt']) ."</td></tr>";
        $row  = str_replace("\n", "", $temp);
        if (!empty($data['fields']['id']['attr']['value'])) { $output['jsReady'][] = "totalUpdate('total_tax_order Init');"; }
        $output['jsHead'][] = "var taxOrderTD = '".str_replace("'", "\'", $row)."';
function totals_tax_order(begBalance) {
    jq('#tableTaxOrder').find('tr').remove();
    var taxTotal  = 0;
    var taxOutput = new Array();
    var rate_id = jq('#tax_rate_id').val();
    for (var idx=0; idx<bizDefaults.taxRates.$type.rows.length; idx++) {
        if (rate_id != bizDefaults.taxRates.$type.rows[idx].id) { continue; }
        if (typeof bizDefaults.taxRates.$type.rows[idx].auths != 'undefined') {
            var taxAuths = bizDefaults.taxRates.$type.rows[idx].auths;
            if (typeof taxAuths != 'undefined') {
                for (var i=0; i<taxAuths.length; i++) {
                    cID = taxAuths[i].text;
                    taxOutput[cID] = new Object();
                    taxOutput[cID].text   = taxAuths[i].text;
                    taxOutput[cID].glAcct = taxAuths[i].glAcct;
                    taxOutput[cID].amount = (begBalance * (taxAuths[i].rate / 100));
                }
            }
        }
    }
    var cnt = 0;
    for (key in taxOutput) {
        if (taxOutput[key].amount == 0) continue;
        taxTotal += taxOutput[key].amount;
        var tableRow = taxOrderTD;
        tableRow = tableRow.replace('textTBD',taxOutput[key].text);
        tableRow = tableRow.replace('glTBD',  taxOutput[key].glAcct);
        tableRow = tableRow.replace('amtTBD', formatPrecise(taxOutput[key].amount));
        jq('#tableTaxOrder').append(tableRow);
        cnt++;
    }
    if (!cnt) { jq('#tableTaxItem').append('<tr><td>No Details Available</td></tr>'); }
    var newTaxItem = roundCurrency(taxTotal);
    var newBalance = begBalance + newTaxItem;
    if (typeof taxRunning !== 'undefined') { taxRunning += newTaxItem; }
    else { taxRunning = newTaxItem; }
    bizNumSet('totals_tax_order', taxRunning);
    return newBalance;
}";
    }
}
