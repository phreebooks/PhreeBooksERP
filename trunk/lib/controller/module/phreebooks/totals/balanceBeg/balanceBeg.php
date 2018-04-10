<?php
/*
 * PhreeBooks totals - Beginning balance
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
 * @filesource /lib/controller/module/phreebooks/totals/balanceBeg/balanceBeg.php
 * 
 */

namespace bizuno;

class balanceBeg
{
	public $code      = 'balanceBeg';
    public $moduleID  = 'phreebooks';
    public $methodDir = 'totals';
    public $required  = true;

	public function __construct()
    {
        $this->settings= ['gl_type'=>'','journals'=>'[20,22]','order'=>0];
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
		// ajax request with GL acct/post_date to get starting balance
		// need to modify post_date and gl_account field to call javascript call
		$this->fields = [
            'totals_balanceBeg' => ['label'=>$this->lang['title'], 'format'=>'currency',
		    'attr' => ['type'=>'text','size'=>'15','value'=>'0','style'=>'text-align:right']],];
		$output['body'] .= '<div style="text-align:right">'."\n".html5('totals_balanceBeg',$this->fields['totals_balanceBeg'])."\n</div>\n";
        $output['jsHead'][] = "function totals_balanceBeg(begBalance) { return cleanCurrency(jq('#totals_balanceBeg').val()); }
function totalsGetBegBalance() {
    var rID      = jq('#id').val();
    var postDate = jq('#post_date').datebox('getValue');
    var glAccount= jq('#gl_acct_id').combogrid('getValue');
    jq.ajax({
        url: '".BIZUNO_AJAX."&p=phreebooks/main/journalBalance&rID='+rID+'&postDate='+postDate+'&glAccount='+glAccount,
        success: function (json) {
            processJson(json);
            if (json.balance) {
                jq('#totals_balanceBeg').val(formatCurrency(''+json.balance));
            } else alert('Balance could not be found!');
            totalUpdate();
       }
    });
}";
        $output['jsReady'][] = "totalsGetBegBalance();
jq('#post_date').datebox({'onSelect': function(date) { totalsGetBegBalance(); }});
jq('#gl_acct_id').combogrid({'onChange': function(newVal, oldVal) { totalsGetBegBalance(); }});";
	}
}
