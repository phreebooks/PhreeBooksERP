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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-04-09
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
            'order'   => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>3,'readonly'=>'readonly','value'=>$this->settings['order']]]];
    }

    public function render(&$output) {
        $this->fields = [
            'totals_debit' =>['label'=>lang('total_debits'), 'attr'=>['type'=>'currency','value'=>0,'readonly'=>'readonly']],
            'totals_credit'=>['label'=>lang('total_credits'),'attr'=>['type'=>'currency','value'=>0,'readonly'=>'readonly']]];
        $output['body'] .= '<div style="text-align:right">'."
    ".html5('totals_debit', $this->fields['totals_debit'])."<br />
    ".html5('totals_credit',$this->fields['totals_credit'])."</div>\n";
        $output['jsHead'][] = "function totals_debitcredit() {
    var debitAmount = 0;
    var creditAmount= 0;
    var rows = jq('#dgJournalItem').datagrid('getRows');
    for (var rowIndex=0; rowIndex<rows.length; rowIndex++) {
        debit = roundCurrency(parseFloat(rows[rowIndex].debit_amount));
        if (isNaN(debit)) debit = 0;
        debitAmount  += debit;
        credit= roundCurrency(parseFloat(rows[rowIndex].credit_amount));
        if (isNaN(credit)) credit = 0;
        creditAmount += credit;
    }
    bizNumSet('totals_debit', debitAmount);
    bizNumSet('totals_credit',creditAmount);
    return roundCurrency(debitAmount - creditAmount);
}";
    }
}
