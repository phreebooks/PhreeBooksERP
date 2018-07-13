<?php
/*
 * PhreeBooks total method for order total - last total operation
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
 * @version    2.x Last Update: 2018-06-14
 * @filesource /controller/module/phreebooks/totals/total/total.php
 */

namespace bizuno;

class total
{
	public $code     = 'total';
    public $moduleID = 'phreebooks';
    public $methodDir= 'totals';
    public $hidden   = false;
    public $required = true;

	public function __construct()
    {
		if (!defined('JOURNAL_ID')) { define('JOURNAL_ID', 2); }
		switch (JOURNAL_ID) {
			case  2: $gl_account = ''; break; // General Journal
			case  3: // Purchase Quote
			case  4: // Purchase Order
			case  6: // Purchase/Receive
            case  7: $gl_account = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');     break; // Purchase Credit Memo
			case  9: // Sales Quote
			case 10: // Sales Order
			case 12: // Sales/Invoice
			case 13: $gl_account = getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');break; // Sales Credit Memo
            case 14: $gl_account = getModuleCache('inventory', 'settings', 'phreebooks', 'inv_si');        break; // Inventory Assembly
			case 15: // Inventory Store Transfer
            case 16: $gl_account = getModuleCache('inventory', 'settings', 'phreebooks', 'cogs_si');       break; // Inventory Adjustment
			case 19: // POS
			case 20: // Cash Receipts
            case 22: $gl_account = getModuleCache('phreebooks', 'settings', 'customers', 'gl_cash');       break; // Customer Payments
			case 17: // Vendor Receipts - gl_cash
			case 18: // Cash Distribution
            case 21: $gl_account = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_cash');         break; // POP
		}
        $this->settings= ['gl_type'=>'ttl','journals'=>'[3,4,6,7,9,10,12,13,16,17,18,19,20,21,22]','gl_account'=>$gl_account,'order'=>90];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
	}

    public function settingsStructure()
    {
        return [
            'gl_type'   => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'  => ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'gl_account'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_account']]], // set in phreebooks settings
            'order'     => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','readonly'=>'readonly','value'=>$this->settings['order']]]];
	}

	/**
     * Return false if no gl entry needed
     * @param type $request
     * @param array $main
     * @param type $item
     */
    public function glEntry($request, &$main, &$item)
    { 
		$total = isset($request['total_amount']) ? clean($request['total_amount'], 'currency') : 0;
		$desc  = 'title:'.(isset($main['primary_name_b'])?$main['primary_name_b']:'');
		$desc  = isset($request['totals_total_desc']) && $request['totals_total_desc'] ? clean($request['totals_total_desc'], 'text') : $desc;
		$txID  = isset($request['totals_total_txid']) ? clean($request['totals_total_txid'], 'text') : '';
		$item[] = [
            'id'           => isset($request['totals_total_id']) ? clean($request['totals_total_id'], 'integer') : 0, // for edits
			'ref_id'       => $main['id'],
			'gl_type'      => $this->settings['gl_type'],
			'qty'          => '1',
			'description'  => $desc,
			'debit_amount' => in_array($main['journal_id'], [7,9,10,12,14,17,18,19]) ? $total : 0,
			'credit_amount'=> in_array($main['journal_id'], [3,4, 6,13,16,20,21,22]) ? $total : 0,
			'gl_account'   => isset($main['gl_acct_id']) && $main['gl_acct_id'] ? $main['gl_acct_id'] : $this->settings['gl_account'],
			'trans_code'   => $txID,
			'post_date'    => $main['post_date']];
		$main['total_amount'] = $total;
	}
	
	public function render(&$output, $data=[])
    {
	  	$this->fields = [
            'totals_total_id'   => ['label'=>'','attr'=>  ['type'=>'hidden']],
			'totals_total_desc' => ['label'=>'','attr'=>  ['type'=>'hidden']],
			'totals_total_txid' => ['label'=>'','attr'=>  ['type'=>'hidden']],
			'gl_acct_id' => ['label'=>lang('gl_account'), 'jsBody'=>htmlComboGL('gl_acct_id'),
				'attr'  => ['type'=>'text', 'size'=>'5', 'value'=>$this->settings['gl_account']]],
			'totals_total_opt' => ['icon'=>'settings', 'size'=>'small',
				'events'=> ['onClick'=>"jq('#totals_total_div').toggle('slow');"]],
			'total_amount' => ['label'=>lang('total'), 'format'=>'currency',
				'attr' => ['size'=>'15', 'value'=>'0', 'style'=>'text-align:right', 'readonly'=>'readonly']],
                ];
		if (isset($data['items'])) { foreach ($data['items'] as $row) { // fill in the data if available
			if ($row['gl_type'] == $this->settings['gl_type']) {
				$this->fields['totals_total_id']['attr']['value']  = isset($row['id']) ? $row['id'] : 0;
				$this->fields['totals_total_desc']['attr']['value']= isset($row['description']) ? $row['description'] : '';
				$this->fields['totals_total_txid']['attr']['value']= isset($row['trans_code']) ? $row['trans_code'] : '';
				$this->fields['gl_acct_id']['attr']['value']       = $row['gl_account'];
				$this->fields['total_amount']['attr']['value']     = $row['credit_amount'] + $row['debit_amount'];
			}
        } }
        $hide = $this->hidden ? ';display:none' : '';
		$output['body'] .= '<div style="text-align:right'.$hide.'">'."\n";
		$output['body'] .= html5('totals_total_id',  $this->fields['totals_total_id']);
		$output['body'] .= html5('totals_total_desc',$this->fields['totals_total_desc']);
		$output['body'] .= html5('totals_total_txid',$this->fields['totals_total_txid']);
		$output['body'] .= html5('total_amount',     $this->fields['total_amount']);
		$output['body'] .= html5('',                 $this->fields['totals_total_opt']);
		$output['body'] .= "</div>\n";
		$output['body'] .= '<div id="totals_total_div" style="display:none" class="layout-expand-over">'."\n";
		$output['body'] .= html5('gl_acct_id',       $this->fields['gl_acct_id'])."\n";
		$output['body'] .= "</div>\n";
        $output['jsHead'][] = "function totals_total(begBalance) {
    var newBalance = begBalance;
    jq('#total_amount').val(formatCurrency(newBalance));
    return newBalance;
}";
	}
}
