<?php
/*
 * PhreeBooks Totals - Fee total
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
 * @version    3.x Last Update: 2019-03-20
 * @filesource /lib/controller/module/phreebooks/totals/fee_order/fee_order.php
 */

namespace bizuno;

class fee_order
{
    public $code     = 'fee_order';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $hidden   = false;

    public function __construct()
    {
        if (!defined('JOURNAL_ID')) { define('JOURNAL_ID', 2); }
        $type          = in_array(JOURNAL_ID, [3,4,6,7,21]) ? 'vendors' : 'customers';
        $this->settings= ['gl_type'=>'fee','journals'=>'[3,4,6,7,9,10,12,13,19,21]','gl_account'=>getModuleCache('phreebooks','settings',$type,'gl_discount'),'order'=>70];
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
            'order'     => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    public function glEntry(&$main, &$item, &$begBal=0)
    {
        $fee_order= clean("totals_{$this->code}", ['format'=>'float','default'=>0], 'post');
        if ($fee_order == 0) { return; }
        $desc     = $this->lang['title'].': '.clean('primary_name_b', ['format'=>'text','default'=>''], 'post');
        $item[]   = [
            'id'           => clean("totals_{$this->code}_id", ['format'=>'float','default'=>0], 'post'),
            'ref_id'       => clean('id', 'integer', 'post'),
            'gl_type'      => $this->settings['gl_type'],
            'qty'          => '1',
            'description'  => $desc,
            'debit_amount' => in_array(JOURNAL_ID, [9,10,12,13,19]) ? $fee_order : 0,
            'credit_amount'=> in_array(JOURNAL_ID, [3, 4, 6, 7,21]) ? $fee_order : 0,
            'gl_account'   => clean("totals_{$this->code}_gl", ['format'=>'text','default'=>$this->settings['gl_account']], 'post'),
            'post_date'    => $main['post_date']];
        $begBal += $fee_order;
        msgDebug("\nDiscount is returning balance = ".$begBal);
    }

    public function render(&$output, $data)
    {
        $this->fields = [
            'totals_fee_order_id' => ['label'=>'', 'attr'=>['type'=>'hidden']],
            'totals_fee_order_gl' => ['label'=>lang('gl_account'),'attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
            'totals_fee_order_opt'=> ['icon' =>'settings','size'=>'small','events'=>['onClick'=>"jq('#phreebooks_totals_fee_order').toggle('slow');"]],
            'totals_fee_order_pct'=> ['label'=>lang('percent'),'lblStyle'=>['min-width'=>'60px'],'options'=>['width'=>60,'value'=>0],'attr'=>['type'=>'float','size'=>5],
                'events'=>['onBlur'=>"feeType='pct'; totalUpdate();"]],
            'totals_fee_order'    => ['label'=>$this->lang['label'],'lblStyle'=>['min-width'=>'60px'],'attr'=>['type'=>'currency','value'=>0],
                'events'=>['onBlur'=>"feeType='amt'; totalUpdate();"]]];
        if (isset($data['items'])) {
            foreach ($data['items'] as $row) { // fill in the data if available
                if ($row['gl_type'] == $this->settings['gl_type']) {
                    $this->fields['totals_fee_order_id']['attr']['value'] = $row['id'];
                    $this->fields['totals_fee_order_gl']['attr']['value'] = $row['gl_account'];
                    $this->fields['totals_fee_order']['attr']['value']    = $row['credit_amount'] + $row['debit_amount'];
                }
            }
        }
        $hide = $this->hidden ? ';display:none' : '';
        $output['body'] .= '<div style="text-align:right'.$hide.'">'."\n";
        $output['body'] .= html5('totals_fee_order_id', $this->fields['totals_fee_order_id']);
        $output['body'] .= html5('totals_fee_order_pct',$this->fields['totals_fee_order_pct']);
        $output['body'] .= html5('totals_fee_order',    $this->fields['totals_fee_order']);
        $output['body'] .= html5('',                    $this->fields['totals_fee_order_opt']);
        $output['body'] .= "</div>\n";
        $output['body'] .= '<div id="phreebooks_totals_fee_order" style="display:none" class="layout-expand-over">';
        $output['body'] .= html5('totals_fee_order_gl', $this->fields['totals_fee_order_gl']);
        $output['body'] .= "</div>\n";
        $output['jsHead'][] = "function totals_fee_order(begBalance) {
    var newBalance= parseFloat(begBalance);
    var curISO    = jq('#currency').val() ? jq('#currency').val() : bizDefaults.currency.defaultCur;
    var decLen    = parseInt(bizDefaults.currency.currencies[curISO].dec_len);
    if (feeType=='pct') {
        var percent = parseFloat(jq('#totals_{$this->code}_pct').val());
        if (isNaN(percent)) { percent = 0; }
        var fee = roundCurrency(newBalance * (percent / 100));
        bizNumSet('totals_{$this->code}', fee);
        feeType= '';
    } else { // amt
        var fee= bizNumGet('totals_{$this->code}');
        if (isNaN(fee)) { fee = 0; }
        var percent = begBalance ? 100 * (1 - ((begBalance - fee) / begBalance)) : 0;
        percent     = percent.toFixed(decLen+1);
        bizNumSet('totals_{$this->code}_pct', percent);
        bizNumSet('totals_{$this->code}', fee);
    }
    newBalance += fee;
    return parseFloat(newBalance.toFixed(decLen));
}";
    }
}
