<?php
/*
 * PhreeBooks dashboard - Summary sales/purchases by week/month
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
 * @version    4.x Last Update: 2020-04-07
 * @filesource /lib/controller/module/phreebooks/dashboards/summary_6_12/summary_6_12.php
 */

namespace bizuno;

class summary_6_12
{
    public  $moduleID = 'phreebooks';
    public  $methodDir= 'dashboards';
    public  $code     = 'summary_6_12';
    public  $category = 'vendors';

    function __construct($settings=[])
    {
        $this->security= getUserCache('security', 'j2_mgr', false, 0);
        $defaults      = ['users'=>-1,'roles'=>-1,'range'=>'l'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->dates   = localeDates(true, true, true, false, true);
    }

    public function settingsStructure()
    {
        return [
            'users' => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']],
            'range' => ['order'=>10,'break'=>true,'position'=>'after','label'=>lang('range'),'values'=>viewKeyDropdown($this->dates),'attr'=>['type'=>'select','value'=>$this->settings['range']]]];
    }

    /**
     *
     * @return type
     */
    public function render(&$layout=[])
    {
        $total_v= $total_c = 0;
        $iconExp= ['attr'=>['type'=>'button','value'=>lang('download')],'events'=>['onClick'=>"jq('#sum_6_12').submit();"]];
        $struc  = $this->settingsStructure();
        $data   = $this->dataSales($this->settings['range']);
        $action = BIZUNO_AJAX."&bizRt=phreebooks/tools/jrnlData&code=6_12&range={$this->settings['range']}";
        $js     = "jq.cachedScript('".BIZUNO_URL."../apps/jquery-file-download.js?ver=".MODULE_BIZUNO_VERSION."');
ajaxDownload('sum_6_12');
function chart{$this->code}() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', '".jsLang('date')."');
    data.addColumn('string', '".jsLang('purchases')."');
    data.addColumn('string', '".jsLang('sales')."');
    data.addRows([";
        foreach ($data as $date => $values) {
            $total_v += $values['v'];
            $total_c += $values['c'];
            $js .= "['".viewFormat($date, 'date')."','".viewFormat($values['v'],'currency')."','".viewFormat($values['c'],'currency')."'],";
        }
        $js .= "['".jslang('total')."','".viewFormat($total_v,'currency')."','".viewFormat($total_c,'currency')."']]);
    data.setColumnProperties(0, {style:'font-style:bold;font-size:22px;text-align:center'});
    var table = new google.visualization.Table(document.getElementById('{$this->code}_chart'));
    table.draw(data, {showRowNumber:false, width:'90%', height:'100%'});
}
google.charts.load('current', {'packages':['table']});
google.charts.setOnLoadCallback(chart{$this->code});\n";
        $filter = ucfirst(lang('filter')).": {$this->dates[$this->settings['range']]}";
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin' =>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'range']]]],
                'head'  =>['order'=>40,'type'=>'html','html'=>$filter,'hidden'=>getModuleCache('bizuno','settings','general','hide_filters',0)],
                'body'  =>['order'=>50,'type'=>'html','html'=>'<div style="width:100%" id="'.$this->code.'_chart"></div>'],
                'export'=>['order'=>95,'type'=>'html','html'=>'<form id="sum_6_12" action="'.$action.'">'.html5('', $iconExp).'</form>']],
            'fields'=> [$this->code.'range'=>array_merge_recursive($struc['range'], ['events'=>['onChange'=>"dashSubmit('$this->moduleID:$this->code', 0);"]])],
            'jsHead'=> ['init'=>$js]]);
    }

    /**
     *
     * @param type $range
     * @return type
     */
    public function dataSales($range='l')
    {
        msgDebug("\nEntering dataSales range = $range");
        $dates  = dbSqlDates($range);
        $arrIncs= $this->createDateRange($dates['start_date'], $dates['end_date']);
        $incKeys= array_keys($arrIncs);
        $crit   = $dates['sql']." AND journal_id IN (12,13)";
        $this->setData($arrIncs, $incKeys, 'c', $crit);
        $crit   = $dates['sql']." AND journal_id IN (6,7)";
        $this->setData($arrIncs, $incKeys, 'v', $crit);
        msgDebug("\nreturning with results = ".print_r($arrIncs, true));
        return $arrIncs;
    }

    /**
     *
     * @param type $startDate
     * @param type $endDate
     * @param type $inc
     * @return int
     */
    private function createDateRange($startDate, $endDate, $inc='w')
    {
        msgDebug("\nEntering createDateRange, start = $startDate, end = $endDate");
        $begin    = new \DateTime($startDate);
        $end      = new \DateTime($endDate);
        $interval = new \DateInterval($inc=='m'?'P1M':'P7D'); // 1 Month : 1 Day
        $dateRange= new \DatePeriod($begin, $interval, $end);
        $range    = [];
        foreach ($dateRange as $date) { $range[$date->format('Y-m-d')] = ['c'=>0,'v'=>0]; }
        return $range;
    }

    /**
     *
     * @param type $arrIncs
     * @param type $incKeys
     * @param type $type
     * @param type $crit
     */
    private function setData(&$arrIncs, $incKeys, $type, $crit)
    {
        $result  = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $crit, 'post_date', ['journal_id','post_date','total_amount']);
        foreach ($result as $row) {
            $value = in_array($row['journal_id'], [7,13]) ? -$row['total_amount'] : $row['total_amount'];
            foreach ($incKeys as $key => $date) {
                if ($row['post_date'] < $date) {
                    $idx = $incKeys[$key-1];
                    $arrIncs[$idx][$type] += $value;
                    break;
                }
            }
        }
    }

    /**
     *
     */
    public function save()
    {
        $menu_id = clean('menuID', 'text', 'get');
        $this->settings['range']= clean($this->code.'range','cmd','post');
        if (getUserCache('security', 'admin', false, 0) > 2) {
            $this->settings['rep']  = clean($this->code.'rep', 'cmd', 'post');
        }
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($this->settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }
}
