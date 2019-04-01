<?php
/*
 * PhreeBooks Total method to calculate sales tax at the item level (shipping will not be taxed)
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
 * @filesource /controller/module/phreebooks/totals/tax_item/tax_item.php
 */

namespace bizuno;

class tax_item
{
    public $code     = 'tax_item';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $hidden   = false;

    public function __construct()
    {
        $this->cType   = defined('CONTACT_TYPE') ? CONTACT_TYPE : 'c';
        $this->settings= ['gl_type'=>'tax','journals'=>'[3,4,6,7,9,10,12,13,19,21]','order'=>50];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
        $this->fields = [
            'totals_tax_item' => ['label'=>pullTableLabel('journal_main', 'tax_rate_id', $this->cType).' '.$this->lang['extra_title'],
                'attr'=>['type'=>'currency','value'=>0,'readonly'=>'readonly']],
            'totals_tax_item_text'=> ['attr' =>['value'=>'textTBD','size'=>'16','readonly'=>'readonly']],
            'totals_tax_item_gl'  => ['label'=>lang('gl_account'),'attr'=>['type'=>'text', 'value'=>'glTBD','size'=>'5','readonly'=>'readonly']],
            'totals_tax_item_amt' => ['attr' =>['value'=>'amtTBD','size'=>'10','style'=>'text-align:right','readonly'=>'readonly']],
            'totals_tax_item_opt' => ['icon' =>'settings','size'=>'small','events'=>['onClick'=>"jq('#phreebooks_totals_tax_item').toggle('slow');"]]];
    }

    public function settingsStructure()
    {
        return [
            'gl_type' => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'order'   => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    public function glEntry(&$main, &$item, &$begBal=0)
    {
        // @todo the below should use the function $tax_rates = loadTaxes($this->cType);
        // this should also be broken into common sub functions as they are shared with tax_order and shipping
        $tax_rates= dbGetMulti(BIZUNO_DB_PREFIX."tax_rates", "type='$this->cType'");
        $gl = [];
        $totalTax = 0;
        $roundAuth= getModuleCache('phreebooks', 'settings', 'general', 'round_tax_auth', 0);
        foreach ($item as $row) {
            if (isset($row['tax_rate_id']) && $row['tax_rate_id'] > 0) {
                foreach ($tax_rates as $key => $value) { if ($row['tax_rate_id'] == $value['id']) { break; } }
                $rates = json_decode($tax_rates[$key]['settings'], true);
                foreach ($rates as $rate) {
                    // calculate the tax
                    $tax = ($rate['rate'] / 100) * ($row['debit_amount'] + $row['credit_amount']);
                    // add it to the $gl by glAcct
                    if (!isset($gl[$rate['glAcct']]['amount'])) { $gl[$rate['glAcct']]['amount'] = 0; }
                    if (!isset($gl[$rate['glAcct']]['text']))   { $gl[$rate['glAcct']]['text'] = []; }
                    if (!in_array($rate['text'], $gl[$rate['glAcct']]['text'])) { $gl[$rate['glAcct']]['text'][] = $rate['text']; }
                    $gl[$rate['glAcct']]['amount'] += $tax;
                }
            }
        }
        foreach ($gl as $glAcct => $value) {
            if ($value['amount'] == 0) { continue; }
            if ($roundAuth) { $value['amount'] = roundAmount($value['amount']); }
            $item[] = [
                'ref_id'       => clean('id', 'integer', 'post'),
                'gl_type'      => $this->settings['gl_type'],
                'qty'          => '1',
                'description'  => implode(' : ', $value['text']),
                'debit_amount' => in_array($main['journal_id'], [3,4, 6,13,20,21,22])       ? $value['amount'] : 0,
                'credit_amount'=> in_array($main['journal_id'], [7,9,10,12,14,16,17,18,19]) ? $value['amount'] : 0,
                'gl_account'   => $glAcct,
                'post_date'    => $main['post_date']];
            $totalTax += $value['amount'];
        }
        $main['sales_tax'] += roundAmount($totalTax);
        $begBal += roundAmount($totalTax);
        msgDebug("\nTaxItem is returning balance = $begBal");
    }

    public function render(&$output, $data=[])
    {
        $hide = $this->hidden ? ';display:none' : '';
        $output['body'] .= '<div style="text-align:right'.$hide.'">';
        $output['body'] .= html5('totals_tax_item',$this->fields['totals_tax_item']);
        $output['body'] .= html5('',               $this->fields['totals_tax_item_opt']);
        $output['body'] .= "</div>";
        $output['body'] .= '<div id="phreebooks_totals_tax_item" style="display:none" class="layout-expand-over">';
        $output['body'] .= '<table id="tableTaxItem"></table>';
        $output['body'] .= "</div>";
        $output['jsHead'][] = $this->jsTotal($data);
    }

    public function jsTotal($data=[])
    {
        $jID = $data['fields']['journal_id']['attr']['value'];
        $type= in_array($jID, [3,4,6,7,17,20,21]) ? 'v' : 'c';
        $row = "<tr><td>".html5('totals_tax_item_text[]',$this->fields['totals_tax_item_text'])."</td>";
//      $row.= "<td>"    .html5('totals_tax_item_gl[]',  $this->fields['totals_tax_item_gl'])  ."</td>";
        $row.= "<td>"    .html5('totals_tax_item_amt[]', $this->fields['totals_tax_item_amt']) ."</td></tr>";
        $temp= str_replace("\n", "", $row);
        return "var taxItemTD = '".str_replace("'", "\'", $temp)."';
function totals_tax_item(begBalance) {
    jq('#tableTaxItem').find('tr').remove();
    var taxTotal  = 0;
    var taxOutput = new Array();
    var rowData   = jq('#dgJournalItem').edatagrid('getData');
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
        rate_id = rowData.rows[rowIndex]['tax_rate_id'];
        for (var idx=0; idx<bizDefaults.taxRates.$type.rows.length; idx++) {
            if (rate_id != bizDefaults.taxRates.$type.rows[idx].id) { continue; }
            if (typeof bizDefaults.taxRates.$type.rows[idx].auths == 'undefined') { continue; }
            var taxAuths = bizDefaults.taxRates.$type.rows[idx].auths;
            if (typeof taxAuths == 'undefined') { continue; }
            var rowBalance = roundCurrency(parseFloat(rowData.rows[rowIndex]['total']));
            if (isNaN(rowBalance)) { continue; }
            for (var i=0; i<taxAuths.length; i++) {
                cID = taxAuths[i].text;
                if (typeof taxOutput[cID] == 'undefined') taxOutput[cID] = new Object();
                taxOutput[cID].text   = taxAuths[i].text;
//              taxOutput[cID].glAcct = taxAuths[i].glAcct;
                if (typeof taxOutput[cID].amount == 'undefined') taxOutput[cID].amount = 0;
                taxOutput[cID].amount += (rowBalance * (taxAuths[i].rate / 100));
            }
        }
    }
    var cnt = 0;
    for (key in taxOutput) {
        taxTotal += taxOutput[key].amount;
        var tableRow = taxItemTD;
        tableRow = tableRow.replace('textTBD',taxOutput[key].text);
        tableRow = tableRow.replace('glTBD',  taxOutput[key].glAcct);
        tableRow = tableRow.replace('amtTBD', formatPrecise(taxOutput[key].amount));
        jq('#tableTaxItem').append(tableRow);
        cnt++;
    }
    if (!cnt) { jq('#tableTaxItem').append('<tr><td>No Details Available</td></tr>'); }
    var newTaxItem = roundCurrency(taxTotal);
    var newBalance = begBalance + newTaxItem;
    if (typeof taxRunning !== 'undefined') { taxRunning += newTaxItem; }
    else { taxRunning = newTaxItem; }
    bizNumSet('totals_tax_item', newTaxItem);
    return newBalance;
}";
    }
}
