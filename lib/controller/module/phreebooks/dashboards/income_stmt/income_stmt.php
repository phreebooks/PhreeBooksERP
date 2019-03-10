<?php
/*
 * PhreeBooks dsahboard - mini income statement
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
 * @copyright  2008-2019, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-03-22, Inc.
 * @filesource /lib/controller/module/phreebooks/dashboards/income_stmt/income_stmt.php
 */

namespace bizuno;

define('DASHBOARD_INCOME_STMT_VERSION','3.1');

class income_stmt
{
    public  $moduleID = 'phreebooks';
    public  $methodDir= 'dashboards';
    public  $code     = 'income_stmt';
    public  $category = 'general_ledger';
    private $netIncome= 0;

    function __construct($settings=[])
    {
        $this->security= getUserCache('security', 'j2_mgr', false, 0);
        $defaults      = ['users'=>'-1','roles'=>'-1'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function settingsStructure()
    {
        return [
            'users' => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']]];
    }

    function render()
    {
        $period = getModuleCache('phreebooks', 'fy', 'period');
        $html  = '<div><div id="'.$this->code.'_attr" style="display:none"><div>'.lang('msg_no_settings').'</div></div>';
        // Build content box
        $html .= '<table width="100%" border = "0">';
        // Sales
        $html .= "<tr><td colspan=\"2\"><b>".lang('gl_acct_type_30')."</b></td></tr>";
        $html .= $this->add_income_stmt_data(30, $period, $negate=true);
        // COGS
        $html .= "<tr><td colspan=\"2\"><b>".lang('gl_acct_type_32')."</b></td></tr>";
        $html .= $this->add_income_stmt_data(32, $period, $negate = false);
        // Expenses
        $html .= "<tr><td colspan=\"2\"><b>".lang('gl_acct_type_34')."</b></td></tr>";
        $html .= $this->add_income_stmt_data(34, $period, $negate = false);
        // Net Income
        $html .= "<tr><td colspan=\"2\" style=\"text-align:right\"><b>".lang('net_income')."</b></td><td style=\"text-align:right\"><b>".viewFormat($this->netIncome, 'currency')."</b></td></tr>";
        $html .= '</table>';
        return $html;
    }

    function add_income_stmt_data($type, $period, $negate=false)
    {
        $rows = dbGetMulti(BIZUNO_DB_PREFIX."journal_history", "period=$period AND gl_type=$type", "gl_account");
        $total = 0;
        $html  = '';
        foreach ($rows as $row) {
            $title = getModuleCache('phreebooks', 'chart', 'accounts')[$row['gl_account']]['title'];
            $balance = $row['debit_amount'] - $row['credit_amount'];
            if ($negate) { $balance = -$balance; }
            $total += $balance;
            if ($balance) { $html .= "<tr><td>{$row['gl_account']}</td><td>$title</td><td style=\"text-align:right\">".viewFormat($balance, 'currency')."</td></tr>"; }
        }
        $html .= "<tr><td colspan=\"2\" style=\"text-align:right\"><b>".lang('total')."</b></td><td style=\"text-align:right\"><b>".viewFormat($total, 'currency')."</b></td></tr>";
        $this->netIncome += $negate ? $total : -$total;
        return $html;
    }
}
