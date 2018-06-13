<?php
/*
 * PhreeBooks Totals - Balance
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
 * @version    2.0 Last Update: 2017-08-27
 * @filesource /lib/controller/module/phreebooks/totals/balance/balance.php
 */

namespace bizuno;

class balance
{
	public $code      = 'balance';
    public $moduleID  = 'phreebooks';
    public $methodDir = 'totals';
    public $required  = true;

	public function __construct()
    {
        $this->settings= ['gl_type'=>'','journals'=>'[2]','order'=>100];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
	}

    public function settingsStructure()
    {
        return [
            'gl_type' => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'order'   => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','readonly'=>'readonly','value'=>$this->settings['order']]]];
	}

	public function render(&$output)
    {
		$this->fields = [
            'total_balance' => ['label'=>$this->lang['title'],//'classes'=>["easyui-numberbox"], // using numberbox causes errors on edit load
		    'styles' => ['text-align'=>'right'], 'attr' => ['size'=>'15', 'value'=>0]],
		  'total_amount' => ['attr'=>  ['type'=>'hidden', 'value'=>0]],
          ];
		$output['body'] .= '<div style="text-align:right">'."\n";
		$output['body'] .= html5('total_balance',$this->fields['total_balance']);
		$output['body'] .= html5('total_amount', $this->fields['total_amount']);
		$output['body'] .= "</div>\n";
        $output['jsHead'][] = "function totals_balance(begBalance) {
    var newBalance = begBalance;
    if (newBalance == 0) jq('#total_balance').css({color:'#000000'}); else jq('#total_balance').css({color:'#FF0000'});
    jq('#total_balance').val(formatCurrency(newBalance));
    var totalDebit = cleanCurrency(jq('#totals_debit' ).val());
    jq('#total_amount').val(formatCurrency(totalDebit));
    return newBalance;
}";
	}
}
