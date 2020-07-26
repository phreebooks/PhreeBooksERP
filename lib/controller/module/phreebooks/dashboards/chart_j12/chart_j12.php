<?php
/*
 * PhreeBooks dashboard - Sales Summary - chart form
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
 * @version    4.x Last Update: 2020-07-20
 * @filesource /lib/controller/module/phreebooks/dashboards/chart_j12/chart_j12.php
 *
 */

namespace bizuno;

class chart_j12
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'dashboards';
    public $code     = 'chart_j12';
    public $category = 'customers';

    function __construct($settings)
    {
        $this->security= getUserCache('security', 'j12_mgr', false, 0);
        $defaults      = ['jID'=>12,'rows'=>10,'users'=>-1,'roles'=>-1,'reps'=>0,'range'=>'l'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->dates   = localeDates(true, true, true, false, true);
    }

    public function settingsStructure()
    {
        return ['jID'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['jID']]],
            'rows'   => ['attr'=>['type'=>'hidden','value'=>$this->settings['rows']]],
            'users'  => ['label'=>lang('users'),    'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles'  => ['label'=>lang('groups'),   'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']],
            'reps'   => ['label'=>lang('just_reps'),'position'=>'after','attr'=>['type'=>'selNoYes','value'=>$this->settings['reps']]],
            'range'  => ['order'=>10,'break'=>true, 'position'=>'after','label'=>lang('range'),'values'=>viewKeyDropdown($this->dates),'attr'=>['type'=>'select','value'=>$this->settings['range']]]];
    }

    public function render(&$layout=[])
    {
        bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/functions.php", 'phreebooksProcess', 'function');
        $struc = $this->settingsStructure();
        $cData = chartSales($this->settings['jID'], $this->settings['range'], $this->settings['rows'], $this->settings['reps']);
        $output= ['divID'=>$this->code."_chart",'type'=>'pie','attr'=>['chartArea'=>['left'=>'15%'],'title'=>$this->dates[$this->settings['range']]],'data'=>$cData];
        $js    = "var data_{$this->code} = ".json_encode($output).";\n";
        $js   .= "google.charts.load('current', {'packages':['corechart']});\n";
        $js   .= "google.charts.setOnLoadCallback(chart{$this->code});\n";
        $js   .= "function chart{$this->code}() { drawBizunoChart(data_{$this->code}); };";
//      $fltrOrd= " ".ucfirst(lang('sort'))." ".strtoupper($this->settings['order']).(!empty($this->settings['num_rows']) ? " ({$this->settings['num_rows']});" : '');
//      $filter = ucfirst(lang('filter')).":$fltrOrd {$this->status[$this->settings['status']]}; {$this->dates[$this->settings['range']]}";
        $filter = ucfirst(lang('filter')).": {$this->dates[$this->settings['range']]}";
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'range']]]],
                'head' =>['order'=>40,'type'=>'html','html'=>$filter,'hidden'=>getModuleCache('bizuno','settings','general','hide_filters',0)],
                'body' =>['order'=>50,'type'=>'html','html'=>'<div style="width:100%" id="'.$this->code.'_chart"></div>']],
            'fields'=> [$this->code.'range'=>array_merge_recursive($struc['range'], ['events'=>['onChange'=>"dashSubmit('$this->moduleID:$this->code', 0);"]])],
            'jsHead'=> ['init'=>$js]]);
    }

    public function save()
    {
        $menu_id = clean('menuID', 'text', 'get');
        $settings['range'] = clean($this->code.'range', 'cmd', 'post');
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }
}
