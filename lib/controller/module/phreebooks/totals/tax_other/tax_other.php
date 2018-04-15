<?php
/*
 * PhreeBooks Totals - Tax Other - generic tax collection independent of authority
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
 * @version    2.x Last Update: 2018-01-10
 * @filesource /lib/controller/module/phreebooks/totals/tax_other/tax_other.php
 */

namespace bizuno;

class tax_other
{
	public $code     = 'tax_other';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $hidden   = false;

	public function __construct()
    {
	    if (!defined('JOURNAL_ID')) { define('JOURNAL_ID', 2); }
        $this->settings= ['gl_type'=>'glt','journals'=>'[9,10,12,13,19]','gl_account'=>getModuleCache('phreebooks','settings','vendors','gl_liability'),'order'=>75];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
	}

    public function settingsStructure()
    {
        return [
            'gl_type'   => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'  => ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'gl_account'=> ['jsBody' => htmlComboGL($this->code.'_gl_account'), 'attr' => ['value' => $this->settings['gl_account']]],
            'order'     => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
	}

	public function glEntry($request, &$main, &$item, &$begBal=0)
    {
		$tax_other = !empty($request['totals_tax_other']) ? clean($request['totals_tax_other'], 'currency') : 0;
        if ($tax_other == 0) { msgDebug("\nNo tax other, returning without making a gl entry!"); return; }
		$item[] = [
            'id'           => clean($request['totals_tax_other_id'], 'float'), // for edits
			'ref_id'       => $request['id'],
			'gl_type'      => $this->settings['gl_type'],
			'qty'          => '1',
			'description'  => $this->lang['title'].': '.($request['primary_name_b']?$request['primary_name_b']:''),
			'debit_amount' => 0,
			'credit_amount'=> $tax_other,
			'gl_account'   => isset($request['totals_tax_other_gl']) ? $request['totals_tax_other_gl'] : $this->settings['gl_account'],
			'post_date'    => $main['post_date']];
		$main['sales_tax'] += roundAmount($tax_other);
		$begBal += roundAmount($tax_other);
		msgDebug("\nTaxOther is returning balance = $begBal");
	}

	public function render(&$output, $data)
    {
		$this->fields = [
            'totals_tax_other_id' => ['label'=>'', 'attr'=>  ['type'=>'hidden']],
			'totals_tax_other_gl' => ['label'=>lang('gl_account'), 'jsBody'=>htmlComboGL('totals_tax_other_gl'),
				'attr'   => ['size'=>'5', 'value'=>$this->settings['gl_account']]],
			'totals_tax_other_opt'=> ['icon'=>'settings', 'size'=>'small',
				'events' => ['onClick'=>"jq('#phreebooks_totals_tax_other').toggle('slow');"]],
			'totals_tax_other'    => ['label'=>lang('inventory_tax_rate_id_c').' '.$this->lang['extra_title'], 'format'=>'currency',
				'attr' => ['size'=>'15', 'value'=>'0', 'style'=>'text-align:right'], 'events'=> ['onBlur'=>"totalUpdate();"]]];
        if (!empty($data['items'])) { foreach ($data['items'] as $row) { // fill in the data if available
            if ($row['gl_type'] == $this->settings['gl_type']) {
                $this->fields['totals_tax_other_id']['attr']['value'] = $row['id'];
                $this->fields['totals_tax_other_gl']['attr']['value'] = $row['gl_account'];
                $this->fields['totals_tax_other']['attr']['value']    = $row['credit_amount'] + $row['debit_amount'];
            }
        } }
		$hide = $this->hidden ? ';display:none' : '';
		$output['body'] .= '<div style="text-align:right'.$hide.'">'."\n";
		$output['body'] .= html5('totals_tax_other_id', $this->fields['totals_tax_other_id'])."\n";
		$output['body'] .= html5('',                    $this->fields['totals_tax_other_opt'])."\n";
		$output['body'] .= html5('totals_tax_other',    $this->fields['totals_tax_other']) ."\n";
		$output['body'] .= "</div>\n";
		$output['body'] .= '<div id="phreebooks_totals_tax_other" style="display:none" class="layout-expand-over">'."\n";
		$output['body'] .= html5('totals_tax_other_gl', $this->fields['totals_tax_other_gl'])."\n";
		$output['body'] .= "</div>\n";
        $output['jsHead'][] = "function totals_tax_other(begBalance) {
	var newBalance = begBalance;
    var salesTax = cleanCurrency(jq('#totals_tax_other').val());
    jq('#totals_tax_other').val(formatCurrency(salesTax));
    newBalance += salesTax;
    var decLen= parseInt(bizDefaults.currency.currencies[currency].dec_len);
	return parseFloat(newBalance.toFixed(decLen));
}";
	}
}
