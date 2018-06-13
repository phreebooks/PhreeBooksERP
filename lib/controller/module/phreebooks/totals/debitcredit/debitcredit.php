<?php
/*
 * PhreeBooks Totals - Debits/Credits total
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
 * @version    2.0 Last Update: 2018-06-04
 * @filesource /lib/controller/module/phreebooks/totals/debitcredit/debitcredit.php
 */

namespace bizuno;

class debitCredit
{
	public $code      = 'debitcredit';
    public $moduleID  = 'phreebooks';
    public $methodDir = 'totals';
    public $required  = true;

	public function __construct()
    {
        $this->settings= ['gl_type'=>'','journals'=>'[2]','order'=>0];
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

	public function render(&$output) {
		$this->fields = [
            'totals_debit' => ['label'=>lang('total_debits'),
                'styles'=>  ['text-align'=>'right'], 'attr' => ['size'=>'15', 'value'=>'0']],
		    'totals_credit'=> ['label'=>lang('total_credits'),
                'styles'=>  ['text-align'=>'right'], 'attr' => ['size'=>'15', 'value'=>'0']]];
		$output['body'] .= '<div style="text-align:right">'."
	".html5('totals_debit', $this->fields['totals_debit'])."<br />
	".html5('totals_credit',$this->fields['totals_credit'])."</div>\n";
        $output['jsHead'][] = "function totals_debitcredit() {
	var debitAmount = 0;
	var creditAmount= 0;
	var rows = jq('#dgJournalItem').datagrid('getRows');
	for (var rowIndex=0; rowIndex<rows.length; rowIndex++) {
		var debit  = jq('#dgJournalItem').edatagrid('getEditor',{index:rowIndex,field:'debit_amount'});
		var amount = cleanCurrency(debit  ? debit.target.val()  : rows[rowIndex].debit_amount);
		if (isNaN(amount)) amount = 0;
		debitAmount  += amount;
		var credit = jq('#dgJournalItem').edatagrid('getEditor',{index:rowIndex,field:'credit_amount'});
		var amount = cleanCurrency(credit ? credit.target.val() : rows[rowIndex].credit_amount);
		if (isNaN(amount)) amount = 0;
		creditAmount += amount;
	}
	jq('#totals_debit' ).val(formatCurrency(debitAmount));
	jq('#totals_credit').val(formatCurrency(creditAmount));
	return debitAmount - creditAmount;
}";
	}
}
