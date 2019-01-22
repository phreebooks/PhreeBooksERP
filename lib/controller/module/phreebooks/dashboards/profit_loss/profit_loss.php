<?php
/*
 * PhreeBooks dashboard - Profit/Loss Summary
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
 * @version    3.x Last Update: 2017-08-28
 * @filesource /lib/controller/module/phreebooks/dashboards/profit_loss/profit_loss.php
 * 
 */

namespace bizuno;

class profit_loss
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'dashboards';
    public $code     = 'profit_loss';
    public $category = 'general_ledger';
    public $noSettings= true;
    
    function __construct($settings)
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

    public function render()
    {
        $period = getModuleCache('phreebooks', 'fy', 'period');
        $cData[] = [lang('type'), lang('total')]; // headings
        $sales   = $this->getValue(30, $period, $negate=true);
        $cogs    = $this->getValue(32, $period, false);
        $cData[] = [lang('gl_acct_type_32'), ['v'=>$cogs, 'f'=>viewFormat($cogs, 'currency')]];
        $expenses= $this->getValue(34, $period, false);
        $cData[] = [lang('gl_acct_type_34'), ['v'=>$expenses, 'f'=>viewFormat($expenses, 'currency')]];
        $netInc = $sales - $cogs - $expenses;
        $cData[] = [lang('net_income'), ['v'=>$netInc, 'f'=>viewFormat($netInc, 'currency')]]; // Net Income

        $output = ['divID'=>$this->code."_chart",'type'=>'pie','attr'=>['pieHole'=>'0.3','title'=>sprintf('Total Sales: %s', viewFormat($sales, 'currency'))],'data'=>$cData];
        $js    = "var data_{$this->code} = ".json_encode($output).";\n";
        $js   .= "google.charts.load('current', {'packages':['corechart']});\n";
        $js   .= "google.charts.setOnLoadCallback(chart{$this->code});\n";
        $js   .= "function chart{$this->code}() { drawBizunoChart(data_{$this->code}); };";
        return '<div style="width:100%" id="'.$this->code.'_chart"></div>' . htmlJS($js);
    }

    function getValue($type, $period, $negate=false)
    {
        $rows = dbGetMulti(BIZUNO_DB_PREFIX."journal_history", "period=$period AND gl_type=$type", "gl_account");
        $total = 0;
        foreach ($rows as $row) {
            $total += $negate ? $row['credit_amount'] - $row['debit_amount'] : $row['debit_amount'] - $row['credit_amount'];
        }
        return $total;
    }
}
