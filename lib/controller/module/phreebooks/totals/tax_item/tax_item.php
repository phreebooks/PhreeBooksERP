<?php
/*
 * PhreeBooks Total method to calculate sales tax at the item level (shipping will not be taxed here)
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-08-11
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
        $this->cType   = 'c'; // @todo is there different language for customer versus vendor? if not then not needed.
        $this->settings= ['gl_type'=>'tax','journals'=>'[3,4,6,7,9,10,12,13,19,21]','order'=>50];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
        $this->fields  = [
            'totals_tax_item'     => ['label'=>pullTableLabel('journal_main', 'tax_rate_id', $this->cType).' '.$this->lang['extra_title'],
                'attr'=>['type'=>'currency','value'=>0,'readonly'=>'readonly']],
            'totals_tax_item_text'=> ['attr' =>['value'=>'textTBD','size'=>16,'readonly'=>'readonly']],
//          'totals_tax_item_gl'  => ['label'=>lang('gl_account'),'attr'=>['type'=>'text','value'=>'glTBD','size'=>5,'readonly'=>'readonly']],
            'totals_tax_item_amt' => ['attr' =>['value'=>'amtTBD', 'size'=>10,'style'=>'text-align:right','readonly'=>'readonly']],
            'totals_tax_item_opt' => ['icon' =>'settings','size'=>'small','events'=>['onClick'=>"jq('#phreebooks_totals_tax_item').toggle('slow');"]]];
    }

    /**
     *
     * @return type
     */
    public function settingsStructure()
    {
        return [
            'gl_type' => ['attr' =>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'=> ['attr' =>['type'=>'hidden','value'=>$this->settings['journals']]],
            'order'   => ['label'=>lang('order'),'options'=>['min'=>5,'max'=>95,'width'=>100],'attr'=>['type'=>'spinner','value'=>$this->settings['order']]]];
    }

    /**
     *
     * @param array $main
     * @param type $item
     * @param type $begBal
     */
    public function glEntry(&$main, &$item, &$begBal=0)
    {
        $type     = in_array($main['journal_id'], [3,4,6,7,21]) ? 'v' : 'c';
        $tax_rates= loadTaxes($type);
        $gl       = [];
        $totalTax = 0;
        $roundAuth= getModuleCache('phreebooks', 'settings', 'general', 'round_tax_auth', 0);
        $isoVals  = getModuleCache('phreebooks', 'currency', 'iso', getDefaultCurrency());
        foreach ($item as $row) {
            if (isset($row['tax_rate_id']) && $row['tax_rate_id'] > 0) {
                foreach ($tax_rates as $key => $value) { if ($row['tax_rate_id'] == $value['id']) { break; } }
                foreach ($tax_rates[$key]['auths'] as $rate) {
                    if (empty($gl[$rate['glAcct']]['amount'])) { $gl[$rate['glAcct']]['amount']= 0;  }
                    if (empty($gl[$rate['glAcct']]['text']))   { $gl[$rate['glAcct']]['text']  = []; }
                    $tax = ($rate['rate'] / 100) * ($row['debit_amount'] + $row['credit_amount'] - $this->getDiscount($item, $row['item_cnt']));
                    if (!in_array($rate['text'], $gl[$rate['glAcct']]['text'])) { $gl[$rate['glAcct']]['text'][] = $rate['text']; }
                    $gl[$rate['glAcct']]['amount'] += $tax; // add it by glAcct
                }
            }
        }
        foreach ($gl as $glAcct => $value) {
            if ($value['amount'] == 0) { continue; }
            if ($roundAuth) { $value['amount'] = roundAmount($value['amount'], $isoVals['dec_len']); }
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
        $main['sales_tax']+= roundAmount($totalTax, $isoVals['dec_len']);
        $begBal           += roundAmount($totalTax, $isoVals['dec_len']);
        msgDebug("\nTaxItem is returning balance = $begBal");
    }

    /**
     *
     * @param type $output
     * @param type $data
     */
    public function render($data=[])
    {
        $hide  = $this->hidden ? ';display:none' : '';
        $html  = '<div style="text-align:right'.$hide.'">';
        $html .= html5('totals_tax_item',$this->fields['totals_tax_item']);
        $html .= html5('',               $this->fields['totals_tax_item_opt']);
        $html .= "</div>";
        $html .= '<div id="phreebooks_totals_tax_item" style="display:none" class="layout-expand-over">';
        $html .= '<table id="tableTaxItem"></table>';
        $html .= "</div>";
        htmlQueue($this->jsTotal($data), 'jsHead');
        return $html;
    }

    /**
     *
     * @param type $data
     * @return type
     */
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
            var taxAuths  = bizDefaults.taxRates.$type.rows[idx].auths;
            if (typeof taxAuths == 'undefined') { continue; }
            var rowBalance= roundCurrency(parseFloat(rowData.rows[rowIndex]['total']));
            if ( isNaN(rowBalance)) { continue; }
// NEED TO FACTOR IN DISCOUNT BY LINE IF THE FIELD IS SET: unit_discount
            var unitDisc  = roundCurrency(parseFloat(rowData.rows[rowIndex]['unit_discount']));
            if (!isNaN(unitDisc))   { rowBalance -= unitDisc; }
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

    /**
     *
     */
    private function getDiscount($item, $item_cnt)
    {
        foreach ($item as $row) {
            if ($row['gl_type']=='dsi' && $row['item_cnt']==$item_cnt) { return $row['debit_amount'] + $row['credit_amount']; }
        }
        return 0;
    }
}
