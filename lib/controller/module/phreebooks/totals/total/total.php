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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-03-18
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
            case  7: $gl_account = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');      break; // 7 - Purchase Credit Memo
            case  9: // Sales Quote
            case 10: // Sales Order
            case 12: // Sales/Invoice
            case 13: $gl_account = getModuleCache('phreebooks', 'settings', 'customers',  'gl_receivables');break; // 13 - Sales Credit Memo
            case 14: $gl_account = getModuleCache('inventory', 'settings',  'phreebooks', 'inv_si');        break; // 14 - Inventory Assembly
            case 15: // Inventory Store Transfer
            case 16: $gl_account = getModuleCache('inventory', 'settings',  'phreebooks', 'cogs_si');       break; // 16 - Inventory Adjustment
            case 18: // Customer Receipts
            case 19: // POS
            case 22: $gl_account = getModuleCache('phreebooks', 'settings', 'customers', 'gl_cash');        break; // 22 - Customer Payments
            case 17: // Vendor Receipts
            case 20: // Cash Distribution
            case 21: $gl_account = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_cash');          break; // 21 - POP
        }
        $this->settings= ['gl_type'=>'ttl','journals'=>'[3,4,6,7,9,10,12,13,16,17,18,19,20,21,22]','gl_account'=>$gl_account,'order'=>90];
        $this->lang    = getMethLang   ($this->moduleID, $this->methodDir, $this->code);
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
     }

    /**
     *
     * @return type
     */
    public function settingsStructure()
    {
        return [
            'gl_type'   => ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_type']]],
            'journals'  => ['attr'=>['type'=>'hidden','value'=>$this->settings['journals']]],
            'gl_account'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['gl_account']]], // set in phreebooks settings
            'order'     => ['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','readonly'=>'readonly','value'=>$this->settings['order']]]];
    }

    /**
     * Return false if no GL entry needed
     * @param array $main
     * @param type $item
     */
    public function glEntry(&$main, &$item, &$begBal=0)
    {
        $total = clean('total_amount', ['format'=>'float','default'=>0], 'post');
        $desc  = $this->lang['title'].': '.clean('primary_name_b', ['format'=>'text','default'=>''], 'post');
        $altDsc= clean('totals_total_desc', 'text', 'post');
        $txID  = clean('totals_total_txid', 'text', 'post');
        $item[]= [
            'id'           => clean("totals_{$this->code}_id", ['format'=>'float','default'=>0], 'post'),
            'ref_id'       => clean('id', 'integer', 'post'),
            'gl_type'      => $this->settings['gl_type'],
            'qty'          => '1',
            'description'  => !empty($altDsc) ? $altDsc : $desc,
            'debit_amount' => in_array($main['journal_id'], [7,9,10,12,14,17,18,19]) ? $total : 0,
            'credit_amount'=> in_array($main['journal_id'], [3,4, 6,13,16,20,21,22]) ? $total : 0,
            'gl_account'   => clean('gl_acct_id', ['format'=>'text','default'=>$this->settings['gl_account']], 'post'),
            'trans_code'   => $txID,
            'post_date'    => $main['post_date']];
        $main['total_amount'] = $total;
    }

    /**
     * Renders the HTML for this method
     * @param array $output - running output buffer
     * @param array $data - source data
     */
    public function render(&$output, $data=[])
    {
          $this->fields = [
            'totals_total_id'  => ['attr'=>['type'=>'hidden']],
            'totals_total_desc'=> ['attr'=>['type'=>'hidden']],
            'totals_total_txid'=> ['attr'=>['type'=>'hidden']],
            'gl_acct_id'       => ['label'=>lang('gl_account'),'attr'=>['type'=>'ledger','value'=>$this->settings['gl_account']]],
            'totals_total_opt' => ['icon'=>'settings', 'size'=>'small','events'=>['onClick'=>"jq('#totals_total_div').toggle('slow');"]],
            'total_amount'     => ['label'=>lang('total'),     'attr'=>['type'=>'currency','value'=>'0','readonly'=>'readonly']]];
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
    bizNumSet('total_amount', newBalance);
    return newBalance;
}";
    }
}
