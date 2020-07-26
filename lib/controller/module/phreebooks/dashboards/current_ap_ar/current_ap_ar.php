<?php
/*
 * PhreeBooks dashboard - Curernt accounts payable totals and receivables totals
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-06-26
 * @filesource /lib/controller/module/phreebooks/dashboards/current_ap_ar/current_ap_ar.php
 */

namespace bizuno;

class current_ap_ar
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'dashboards';
    public $code     = 'current_ap_ar';
    public $category = 'general_ledger';

    public function __construct($settings=[])
    {
        $this->security      = getUserCache('security', 'j2_mgr', 0);
        $defaults            = ['users'=>'-1','roles'=>'-1'];
        $this->lang          = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->bal_tot_2     = 0;
        $this->bal_tot_3     = 0;
        $this->bal_sheet_data= [];
        $this->settings      = array_replace_recursive($defaults, $settings);
    }

    public function settingsStructure()
    {
        return [
            'users' => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']]];
    }

    public function render()
    {
        $period = getModuleCache('phreebooks', 'fy', 'period');
        $html  = '<div><div id="'.$this->code.'_attr" style="display:none"><div>'.lang('msg_no_settings').'</div></div>';
        // Build content box
        $html .= '<table width="100%" border = "0">';
        // Accounts Receivables
        $html .= "<tr><td colspan=\"2\"><b>".lang('gl_acct_type_20')."</b></td></tr>";
        $html .= $this->add_income_stmt_data(20, $period, $negate=true);
        // Accounts Payables
        $html .= "<tr><td colspan=\"2\"><b>".lang('gl_acct_type_2')."</b></td></tr>";
        $html .= $this->add_income_stmt_data(2, $period, $negate = false);
        $html .= '</table>';
        return $html;
    }

    private function add_income_stmt_data($type, $period, $negate=false)
    {
        $rows = dbGetMulti(BIZUNO_DB_PREFIX."journal_history", "period=$period AND gl_type=$type", "gl_account");
        $total = 0;
        $html  = '';
        foreach ($rows as $row) {
            $title = getModuleCache('phreebooks', 'chart', 'accounts')[$row['gl_account']]['title'];
            $balance = $row['beginning_balance'] + $row['debit_amount'] - $row['credit_amount'];
            if ($negate) { $balance = -$balance; }
            $total += $balance;
            if ($balance) { $html .= "<tr><td>{$row['gl_account']}</td><td>$title</td><td style=\"text-align:right\">".viewFormat($balance, 'currency')."</td></tr>"; }
        }
        $html .= "<tr><td colspan=\"2\" style=\"text-align:right\"><b>".lang('total')."</b></td><td style=\"text-align:right\"><b>".viewFormat($total, 'currency')."</b></td></tr>";
        return $html;
    }
}
