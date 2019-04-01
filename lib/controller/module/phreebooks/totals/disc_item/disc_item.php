<?php
/*
 * PhreeBooks Total method to allow discounts at the item level
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
 * @version    3.x Last Update: 2019-03-22
 * @filesource /controller/module/phreebooks/totals/disc_item/disc_item.php
 */

namespace bizuno;

class disc_item
{
    public $code     = 'disc_item';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';

    public function __construct()
    {
        $this->jID     = clean('jID', ['format'=>'cmd', 'default'=>2], 'get');
        $this->type    = in_array($this->jID, [3,4,6,7,21]) ? 'vendors' : 'customers';
        $this->settings= ['gl_type'=>'dsi','journals'=>'[3,4,6,7,9,10,12,13,19,21]','gl_account'=>getModuleCache('phreebooks','settings',$this->type,'gl_discount'),'order'=>30];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        return [
            'gl_type'   => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'  => ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'gl_account'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_account']]],
            'order'     => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>3,'value'=>$this->settings['order']]]];
    }

    public function glEntry(&$main, &$item, &$begBal=0)
    {
        $totalDisc = 0;
        $postItm = clean('item_array', 'json', 'post');
        foreach ($postItm['rows'] as $idx => $row) {
            msgDebug("\nWorking with row = ".print_r($row, true));
            if (empty($row['unit_discount'])) { continue; }
            $item[] = [
                'ref_id'       => clean('id', 'integer', 'post'),
                'gl_type'      => $this->settings['gl_type'],
                'item_cnt'     => $idx + 1,
                'qty'          => 1,
                'description'  => $this->lang['title'],
                'debit_amount' => in_array($this->jID, [9,10,12,13,19]) ? $row['unit_discount'] : 0,
                'credit_amount'=> in_array($this->jID, [3, 4, 6, 7,21]) ? $row['unit_discount'] : 0,
                'gl_account'   => clean("totals_{$this->code}_gl", ['format'=>'text','default'=>$this->settings['gl_account']], 'post'),
                'post_date'    => $main['post_date']];
            $totalDisc += $row['unit_discount'];
        }
        if (empty($main['discount'])) { $main['discount'] = 0; }
        $main['discount'] += roundAmount($totalDisc);
        $begBal -= $totalDisc;
        msgDebug("\ndisc_item applied discount = $totalDisc and is returning balance = $begBal");
    }

    public function render(&$output, $data=[])
    {
        $this->fields = [
          "totals_{$this->code}_id" => ['attr'=>['type'=>'hidden']],
          "totals_{$this->code}_gl" => ['label'=>lang('gl_account'),'attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
          "totals_{$this->code}_opt"=> ['icon'=>'settings','size'=>'small','events'=>['onClick'=>"jq('#phreebooks_totals_".$this->code."').toggle('slow');"]],
          "totals_{$this->code}"    => ['label'=>$this->lang['label'],'lblStyle'=>['min-width'=>'60px'],'attr'=>['type'=>'currency','value'=>0,'readonly'=>'readonly']]];
        $output['body'] .= '<div style="text-align:right">'."\n";
        $output['body'] .= html5("totals_{$this->code}_id",$this->fields["totals_{$this->code}_id"]);
        $output['body'] .= html5("totals_{$this->code}",   $this->fields["totals_{$this->code}"]);
        $output['body'] .= html5('',                       $this->fields["totals_{$this->code}_opt"]);
        $output['body'] .= "</div>\n".'<div id="phreebooks_totals_'.$this->code.'" style="display:none" class="layout-expand-over">'."\n";
        $output['body'] .= html5("totals_{$this->code}_gl", $this->fields["totals_{$this->code}_gl"])."\n</div>\n";
        $output['jsHead'][] = "function totals_{$this->code}(begBalance) {
    var newBalance= begBalance;
    var rowData   = jq('#dgJournalItem').edatagrid('getData');
    var discount  = 0;
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
        if (isNaN(rowData.rows[rowIndex].unit_discount)) rowData.rows[rowIndex].unit_discount = 0;
        discount += parseFloat(rowData.rows[rowIndex].unit_discount);
    }
    newBalance   -= discount;
    bizNumSet('totals_{$this->code}', discount);
    var curISO    = jq('#currency').val() ? jq('#currency').val() : bizDefaults.currency.defaultCur;
    var decLen    = parseInt(bizDefaults.currency.currencies[curISO].dec_len);
    return parseFloat(newBalance.toFixed(decLen));
}";
    }
}
