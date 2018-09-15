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
 * @copyright  2008-2018, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-08-24
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
		$discount = isset($request["totals_{$this->code}"]) ? clean($request["totals_{$this->code}"], 'currency') : 0;
        if ($discount == 0) { return; }
		$item[] = [
            'id'           => clean($request["totals_{$this->code}_id"], 'float'), // for edits
		    'ref_id'       => $request['id'],
		    'gl_type'      => $this->settings['gl_type'],
		    'qty'          => '1',
		    'description'  => $this->lang['title'].': '.($request['primary_name_b']?$request['primary_name_b']:''),
		    'debit_amount' => in_array($this->jID, [9,10,12,13,19]) ? $discount : 0,
		    'credit_amount'=> in_array($this->jID, [3, 4, 6, 7,21]) ? $discount : 0,
		    'gl_account'   => isset($request["totals_{$this->code}_gl"]) ? $request["totals_{$this->code}_gl"] : $this->settings['gl_account'],
		    'post_date'    => $main['post_date']];
        if (empty($main['discount'])) { $main['discount'] = 0; }
		$main['discount'] += $discount;
		$begBal -= $discount;
		msgDebug("\n{$this->code} is returning balance = ".$begBal);
	}

	public function render(&$output, $data=[])
    {
        $this->fields = [
          "totals_{$this->code}_id" => ['attr'=>['type'=>'hidden']],
		  "totals_{$this->code}_gl" => ['label'=>lang('gl_account'),'attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
		  "totals_{$this->code}_opt"=> ['icon'=>'settings', 'size'=>'small','events'=>['onClick'=>"jq('#phreebooks_totals_".$this->code."').toggle('slow');"]],
		  "totals_{$this->code}_pct"=> ['label'=>lang('percent'),'options'=>['width'=>60],'events'=>['onBlur'=>"discountType='pct'; totalUpdate();"],'attr'=>['type'=>'float','size'=>5]],
		  "totals_{$this->code}"    => ['label'=>$this->lang['title'],'attr'=>['type'=>'currency','size'=>15,'value'=>0],
			'events' => ['onBlur'=>"discountType='amt'; totalUpdate();"]]];
		if (isset($data['items'])) {
            foreach ($data['items'] as $row) { // fill in the data if available
                if ($row['gl_type'] == $this->settings['gl_type']) {
                    $this->fields["totals_{$this->code}_id"]['attr']['value']= isset($row['id']) ? $row['id'] : 0;
                    $this->fields["totals_{$this->code}_gl"]['attr']['value']= $row['gl_account'];
                    $this->fields["totals_{$this->code}"]['attr']['value']   = $row['credit_amount'] + $row['debit_amount'];
                }
            }
        }
        $hide = !empty($this->hidden) ? ';display:none' : '';
		$output['body'] .= '<div style="text-align:right'.$hide.'">'."\n";
		$output['body'] .= html5("totals_{$this->code}_id", $this->fields["totals_{$this->code}_id"]);
		$output['body'] .= html5("totals_{$this->code}_pct",$this->fields["totals_{$this->code}_pct"]);
		$output['body'] .= html5("totals_{$this->code}",    $this->fields["totals_{$this->code}"]);
		$output['body'] .= html5('',                   $this->fields["totals_{$this->code}_opt"]);
		$output['body'] .= "</div>\n";
		$output['body'] .= '<div id="phreebooks_totals_'.$this->code.'" style="display:none" class="layout-expand-over">'."\n";
		$output['body'] .= html5("totals_{$this->code}_gl", $this->fields["totals_{$this->code}_gl"])."\n";
		$output['body'] .= "</div>\n";
        $output['jsHead'][] = "function totals_{$this->code}(begBalance) {
	var newBalance = begBalance;
    var decLen = parseInt(bizDefaults.currency.currencies[currency].dec_len);
    if (discountType=='pct') {
        var percent = parseFloat(jq('#totals_{$this->code}_pct').val());
        if (isNaN(percent)) { percent = 0; }
        var discount = roundCurrency(newBalance * (percent / 100));
        bizTextSet('totals_{$this->code}', discount, 'currency');
    } else { // amt
        var discount = cleanCurrency(jq('#totals_{$this->code}').val());
        var percent = 100 * (1 - ((begBalance - discount) / begBalance));
        percent = percent.toFixed(decLen+1);
        bizTextSet('totals_{$this->code}_pct', percent);
        bizTextSet('totals_{$this->code}', discount, 'currency');
    }
    newBalance -= discount;
	return parseFloat(newBalance.toFixed(decLen));
}";
	}
}
