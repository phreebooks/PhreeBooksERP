<?php
/*
 * PhreeBooks Totals - Discounts by checkbox total
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
 * @filesource /lib/controller/module/phreebooks/totals/discountChk/discountChk.php
 */

namespace bizuno;

class discountChk
{
    public  $code      = 'discountChk';
    public  $moduleID  = 'phreebooks';
    public  $methodDir = 'totals';

    public function __construct()
    {
        $this->jID     = clean('jID', ['format'=>'cmd', 'default'=>'2'], 'get');
        $type          = in_array($this->jID, [17,20,21]) ? 'vendors' : 'customers';
        $this->settings= ['gl_type'=>'dsc','journals'=>'[17,18,20,22]','gl_account'=>getModuleCache('phreebooks','settings',$type,'gl_discount'),'order'=>30];
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

    public function glEntry(&$main, &$items, &$begBal=0)
    {
        $totalDisc = 0;
        $rows = clean('item_array', 'json', 'post');
        foreach ($rows as $row) {
            $discount = clean($row['discount'], 'float');
            if ($discount == 0) { continue; }
            // need to bump up total paid to include discount
            foreach ($items as $key => $item) {
                if (!isset($item['item_ref_id'])) { continue; }
                if ($item['item_ref_id'] == $row['item_ref_id']) { // bump up the pmt to include the discount
                    if (in_array($this->jID, [17,18])) { $items[$key]['credit_amount'] += $discount; }
                    if (in_array($this->jID, [20,22])) { $items[$key]['debit_amount']  += $discount; }
                    break;
                }
            }
            $items[] = [
                'ref_id'       => clean('id', 'integer', 'post'),
                'item_ref_id'  => $row['item_ref_id'],
                'gl_type'      => $this->settings['gl_type'],
                'qty'          => '1',
                'description'  => $row['description'],
                'debit_amount' => in_array($this->jID, [17,18]) ? $discount : 0,
                'credit_amount'=> in_array($this->jID, [20,22]) ? $discount : 0,
                'gl_account'   => clean('totals_discount_gl', ['format'=>'text','default'=>$this->settings['gl_account']], 'post'),
                'post_date'    => $main['post_date']];
            $totalDisc += $discount;
        }
        $main['discount'] = $totalDisc;
//        $begBal += $totalDisc; // don't add this as it has already been included
        msgDebug("\nDiscountChk is returning total discount = ".$totalDisc);
    }

    public function render(&$output, $data=[])
    {
        $this->fields = [
            'totals_discount_gl' => ['label'=>lang('gl_account'),'attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
            'totals_discount_opt'=> ['icon' =>'settings','size'=>'small','events'=>['onClick'=>"jq('#phreebooks_totals_discount').toggle('slow');"]],
            'totals_discount'    => ['label'=>lang('discount'),'attr'=>['type'=>'currency','value'=>'0','readonly'=>'readonly'],'events'=>['onClick'=>"discountType='amt'; totalUpdate();"]]];
        msgDebug("\nSettings for discountChk = ".print_r($this->settings, true));
        if (isset($data['items'])) { foreach ($data['items'] as $row) { // fill in the data if available
            if ($row['gl_type'] == $this->settings['gl_type']) {
                msgDebug("\nGL TYPE MATCH "); // never hits this loop as the dsc row has been removed
                $this->fields['totals_discount_id']['attr']['value'] = $row['id'];
                $this->fields['totals_discount_gl']['attr']['value'] = $row['gl_account'];
            }
        } }
        $output['body'] .= '<div style="text-align:right">';
        $output['body'] .= html5('totals_discount',    $this->fields['totals_discount']);
        $output['body'] .= html5('',                   $this->fields['totals_discount_opt']);
        $output['body'] .= "</div>";
        $output['body'] .= '<div id="phreebooks_totals_discount" style="display:none" class="layout-expand-over">';
        $output['body'] .= html5('totals_discount_gl', $this->fields['totals_discount_gl']);
        $output['body'] .= "</div>";
        $output['jsHead'][] = "function totals_discountChk(begBalance) {
    var totalDisc = 0;
    var rowData = jq('#dgJournalItem').datagrid('getData');
    for (var i=0; i<rowData.rows.length; i++) if (rowData.rows[i]['checked']) {
        var discount = parseFloat(rowData.rows[i].discount);
        if (!isNaN(discount)) totalDisc += discount;
    }
    bizNumSet('totals_discount', totalDisc);
    var newBalance = begBalance - totalDisc;
    var curISO    = jq('#currency').val() ? jq('#currency').val() : bizDefaults.currency.defaultCur;
    var decLen= parseInt(bizDefaults.currency.currencies[curISO].dec_len);
    return parseFloat(newBalance.toFixed(decLen));
}";
    }
}
