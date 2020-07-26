<?php
/*
 * PhreeBooks Totals - Tax Other - generic tax collection independent of authority
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
 * @version    4.x Last Update: 2020-06-04
 * @filesource /lib/controller/module/phreebooks/totals/tax_other/tax_other.php
 */

namespace bizuno;

class tax_other
{
    public $code     = 'tax_other';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $hidden   = false;

    public function __construct()
    {
        $this->settings= ['gl_type'=>'glt','journals'=>'[9,10,12,13,19]','gl_account'=>getModuleCache('phreebooks','settings','vendors','gl_liability'),'order'=>75];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        return [
            'gl_type'   => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'  => ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'gl_account'=> ['attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
            'order'     => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    public function glEntry(&$main, &$item, &$begBal=0)
    {
        $tax_other= clean("totals_{$this->code}", ['format'=>'float','default'=>0], 'post');
        if ($tax_other == 0) { return; }
        $isoVals  = getModuleCache('phreebooks', 'currency', 'iso', getDefaultCurrency());
        $desc     = $this->lang['title'].': '.clean('primary_name_b', ['format'=>'text','default'=>''], 'post');
        $item[]   = [
            'id'           => clean("totals_{$this->code}_id", ['format'=>'float','default'=>0], 'post'),
            'ref_id'       => clean('id', 'integer', 'post'),
            'gl_type'      => $this->settings['gl_type'],
            'qty'          => 1,
            'description'  => $desc,
            'debit_amount' => in_array($main['journal_id'], [3,4, 6,13,20,21,22])       ? $tax_other : 0,
            'credit_amount'=> in_array($main['journal_id'], [7,9,10,12,14,16,17,18,19]) ? $tax_other : 0,
            'gl_account'   => clean("totals_{$this->code}_gl", ['format'=>'text','default'=>$this->settings['gl_account']], 'post'),
            'post_date'    => $main['post_date']];
        $main['sales_tax'] += roundAmount($tax_other, $isoVals['dec_len']);
        $begBal += roundAmount($tax_other, $isoVals['dec_len']);
        msgDebug("\nTaxOther is returning balance = $begBal");
    }

    public function render($data)
    {
        $jID   = $data['fields']['journal_id']['attr']['value'];
        $type  = in_array($jID, [3,4,6,7,17,20,21]) ? 'v' : 'c';
        $this->fields = [
            'totals_tax_other_id' => ['label'=>'', 'attr'=>['type'=>'hidden']],
            'totals_tax_other_gl' => ['label'=>lang('gl_account'),'attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
            'totals_tax_other_opt'=> ['icon'=>'settings','size'=>'small','events'=>['onClick'=>"jq('#phreebooks_totals_tax_other').toggle('slow');"]],
            'totals_tax_other'    => ['label'=>pullTableLabel('journal_main', 'tax_rate_id', $type).' '.$this->lang['extra_title'], 'events'=>['onBlur'=>"totalUpdate();"],
                'attr' => ['type'=>'currency','value'=>0]]];
        if (!empty($data['items'])) { foreach ($data['items'] as $row) { // fill in the data if available
            if ($row['gl_type'] == $this->settings['gl_type']) {
                $this->fields['totals_tax_other_id']['attr']['value'] = !empty($row['id']) ? $row['id'] : 0;
                $this->fields['totals_tax_other_gl']['attr']['value'] = $row['gl_account'];
                $this->fields['totals_tax_other']['attr']['value']    = $row['credit_amount'] + $row['debit_amount'];
            }
        } }
        $hide = $this->hidden ? ';display:none' : '';
        $html  = '<div style="text-align:right'.$hide.'">'."\n";
        $html .= html5('totals_tax_other_id', $this->fields['totals_tax_other_id']);
        $html .= html5('totals_tax_other',    $this->fields['totals_tax_other']);
        $html .= html5('',                    $this->fields['totals_tax_other_opt']);
        $html .= "</div>";
        $html .= '<div id="phreebooks_totals_tax_other" style="display:none" class="layout-expand-over">';
        $html .= html5('totals_tax_other_gl', $this->fields['totals_tax_other_gl']);
        $html .= "</div>";
        htmlQueue("function totals_tax_other(begBalance) {
    var newBalance = begBalance;
    var salesTax = parseFloat(bizNumGet('totals_tax_other'));
    bizNumSet('totals_tax_other', salesTax);
    newBalance += salesTax;
    var curISO    = jq('#currency').val() ? jq('#currency').val() : bizDefaults.currency.defaultCur;
    var decLen= parseInt(bizDefaults.currency.currencies[curISO].dec_len);
    return parseFloat(newBalance.toFixed(decLen));
}", 'jsHead');
        return $html;
    }
}
