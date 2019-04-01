<?php
/*
 * PhreeBooks Totals - Subtotal class
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
 * @version    3.x Last Update: 2019-03-06
 * @filesource /lib/controller/module/phreebooks/totals/subtotal/subtotal.php
 */

namespace bizuno;

class subtotal
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $code     = 'subtotal';
    public $hidden   = false;
    public $required = true;

    public function __construct()
    {
        $this->settings= ['gl_type'=>'sub','journals'=>'[3,4,6,7,9,10,12,13,16,19,21]','order'=>0];
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
        $fields = ['totals_subtotal'=>['label'=>lang('subtotal'),'attr'=>['type'=>'currency','value'=>0,'readonly'=>'readonly']]];
        $hide   = $this->hidden ? ';display:none' : '';
        $output['body'] .= '<div style="text-align:right'.$hide.'">'.html5('totals_subtotal',$fields['totals_subtotal']).html5('',['icon'=>'blank','size'=>'small'])."</div>\n";
        $output['jsHead'][]= "function totals_subtotal(begBalance) {
    taxRunning = 0;
    var newBalance = begBalance;
    var rowData    = jq('#dgJournalItem').edatagrid('getData');
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
        var amount = roundCurrency(parseFloat(rowData.rows[rowIndex].total));
        if (!isNaN(amount)) newBalance += amount;
    }
    bizNumSet('totals_subtotal', newBalance);
    return newBalance;
}";
    }
}
