<?php
/*
 * Contacts dashboard - New Customers
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
 * @version    3.x Last Update: 2018-10-10
 * @filesource /lib/controller/module/phreebooks/dashboards/new_cust/new_cust.php
 */

namespace bizuno;

define('DASHBOARD_NEW_CUST_VERSION','3.1');

class new_cust
{
    public $moduleID  = 'contacts';
    public $methodDir = 'dashboards';
    public $code      = 'new_cust';
    public $category  = 'customers';
    public $noSettings= true;
    
    function __construct($settings)
    {
        $this->security= getUserCache('security', 'mgr_c', false, 0);
        $defaults      = ['users'=>'-1','roles'=>'-1','reps'=>'0'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function settingsStructure()
    {
        return [
            'users' => ['label'=>lang('users'),    'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),   'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']],
            'reps'  => ['label'=>lang('just_reps'),'position'=>'after','attr'=>['type'=>'selNoYes','value'=>$this->settings['reps']]]];
    }

    public function render()
    {
        $js = "function chart{$this->code}() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', ' ');
    data.addColumn('number', ' ');
    data.addRows([
      ['".jslang('today')."',    ".$this->getTotals('c')."],
      ['".jslang('dates_wtd')."',".$this->getTotals('e')."],
      ['".jslang('dates_mtd')."',".$this->getTotals('g')."]
    ]);
    data.setColumnProperties(0, {style:'font-style:bold;text-align:center'});
    var table = new google.visualization.Table(document.getElementById('{$this->code}_chart'));
    table.draw(data, {showRowNumber:false, width:'50%', height:'100%'});
}
google.charts.load('current', {'packages':['table']});
google.charts.setOnLoadCallback(chart{$this->code});\n";
        return '<div style="width:100%;text-align:center" id="'.$this->code.'_chart"></div>'.htmlJS($js);
    }
    
    private function getTotals($range='c')
    {
        $dates = dbSqlDates($range, 'first_date');
        if (!$stmt = dbGetResult("SELECT COUNT(*) AS count FROM ".BIZUNO_DB_PREFIX."contacts WHERE type='c' AND ".$dates['sql'])) { 
            return msgAdd(lang('err_bad_sql'));
        }
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return !empty($result['count']) ? $result['count'] : 0;
    }
}
