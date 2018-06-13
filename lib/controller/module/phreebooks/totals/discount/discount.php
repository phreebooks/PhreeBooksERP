<?php
/*
 * PhreeBooks Totals - Discount at order level
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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2017-09-06
 * @filesource /lib/controller/module/phreebooks/totals/discount/discount.php
 */

namespace bizuno;

class discount
{
	public $code     = 'discount';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';

	public function __construct()
    {
	    $this->jID     = clean('jID', ['format'=>'cmd', 'default'=>'2'], 'get');
        $type          = in_array($this->jID, [3,4,6,7,21]) ? 'vendors' : 'customers';
        $this->settings= ['gl_type'=>'dsc','journals'=>'[3,4,6,7,9,10,12,13,19,21]','gl_account'=>getModuleCache('phreebooks','settings',$type,'gl_discount'),'order'=>30];
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

	public function glEntry($request, &$main, &$item, &$begBal=0)
    {
		$discount = isset($request['totals_discount']) ? clean($request['totals_discount'], 'currency') : 0;
        if ($discount == 0) { return; }
		$item[] = [
            'id'           => clean($request['totals_discount_id'], 'float'), // for edits
		    'ref_id'       => $request['id'],
		    'gl_type'      => $this->settings['gl_type'],
		    'qty'          => '1',
		    'description'  => $this->lang['title'].': '.($request['primary_name_b']?$request['primary_name_b']:''),
		    'debit_amount' => in_array($this->jID, [9,10,12,13,19]) ? $discount : 0,
		    'credit_amount'=> in_array($this->jID, [3, 4, 6, 7,21]) ? $discount : 0,
		    'gl_account'   => isset($request['totals_discount_gl']) ? $request['totals_discount_gl'] : $this->settings['gl_account'],
		    'post_date'    => $main['post_date'],
            ];
		$main['discount'] = $discount;
		$begBal -= $discount;
		msgDebug("\nDiscount is returning balance = ".$begBal);
	}

	public function render(&$output, $data=[])
    {
        $this->fields = [
          'totals_discount_id' => ['label'=>'', 'attr'=>  ['type'=>'hidden']],
		  'totals_discount_gl' => ['label'=>lang('gl_account'), 'jsBody'=>htmlComboGL('totals_discount_gl'),
		  	'attr'   => ['size'=>'5', 'value'=>$this->settings['gl_account']]],
		  'totals_discount_opt'=> ['icon'=>'settings', 'size'=>'small',
		    'events' => ['onClick'=>"jq('#phreebooks_totals_discount').toggle('slow');"]],
		  'totals_discount_pct'=> ['label'=>lang('percent'), 'attr'=>  ['type'=>'text', 'size'=>'5'],
		    'events' => ['onBlur'=>"discountType='pct'; totalUpdate();"],],
		  'totals_discount'    => ['label'=>lang('discount'),'format'=>'currency','attr'=>['size'=>'15','value'=>0,'style'=>'text-align:right'],
			'events' => ['onBlur'=>"discountType='amt'; totalUpdate();"]]];
		if (isset($data['items'])) {
            $subtotal = 0;
            foreach ($data['items'] as $row) { // fill in the data if available
                if ($row['gl_type'] == 'itm') { // need to calculate the subtotal
                    $subtotal += $row['credit_amount'] + $row['debit_amount'];
                }
                if ($row['gl_type'] == $this->settings['gl_type']) {
                    $this->fields['totals_discount_id']['attr']['value'] = isset($row['id']) ? $row['id'] : 0;
                    $this->fields['totals_discount_gl']['attr']['value'] = $row['gl_account'];
                    $this->fields['totals_discount']['attr']['value'] = $row['credit_amount'] + $row['debit_amount'];
                }
            }
            $this->fields['totals_discount']['attr']['value'] = $data['fields']['main']['discount']['attr']['value'];
            if ($subtotal) {
                $this->fields['totals_discount_pct']['attr']['value'] = 100 * $data['fields']['main']['discount']['attr']['value'] / $subtotal;
            } else {
                $this->fields['totals_discount_pct']['attr']['value'] = 0;
            }
        }
        $hide = !empty($this->hidden) ? ';display:none' : '';
		$output['body'] .= '<div style="text-align:right'.$hide.'">'."\n";
		$output['body'] .= html5('totals_discount_id', $this->fields['totals_discount_id'])."\n";
		$output['body'] .= html5('totals_discount_pct',$this->fields['totals_discount_pct'])."\n";
		$output['body'] .= html5('',                   $this->fields['totals_discount_opt'])."\n";
		$output['body'] .= html5('totals_discount',    $this->fields['totals_discount']) ."\n";
		$output['body'] .= "</div>\n";
		$output['body'] .= '<div id="phreebooks_totals_discount" style="display:none" class="layout-expand-over">'."\n";
		$output['body'] .= html5('totals_discount_gl', $this->fields['totals_discount_gl'])."\n";
		$output['body'] .= "</div>\n";
        $output['jsHead'][] = "function totals_discount(begBalance) {
	var newBalance = begBalance;
    if (discountType=='pct') {
        var percent = parseFloat(jq('#totals_discount_pct').val());
        if (isNaN(percent)) { percent = 0; }
        var discount = roundCurrency(newBalance * (percent / 100));
        jq('#totals_discount').val(formatCurrency(discount));
    } else { // amt
        var discount = cleanCurrency(jq('#totals_discount').val());
        var percent = 100 * (1 - ((begBalance - discount) / begBalance));
        jq('#totals_discount_pct').val(+percent.toFixed(2));
        jq('#totals_discount').val(formatCurrency(jq('#totals_discount').val()));
    }
    newBalance -= discount;
    var decLen= parseInt(bizDefaults.currency.currencies[currency].dec_len);
	return parseFloat(newBalance.toFixed(decLen));
}
function pbTotDscPct() {

}
function pbTotDscAmt() {

}";
	}
}
