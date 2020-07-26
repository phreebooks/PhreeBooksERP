<?php
/*
 * Bizuno dashboard - Audit/Activity Log
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
 * @version    4.x Last Update: 2020-07-23
 * @filesource /lib/controller/module/bizuno/dashboards/todays_audit/todays_audit.php
 */

namespace bizuno;

class todays_audit
{
    public  $moduleID = 'bizuno';
    public  $methodDir= 'dashboards';
    public  $code     = 'todays_audit';
    public  $category = 'general';
    private $titles   = [];

    function __construct($settings)
    {
        $this->security= getUserCache('security', 'profile', 0);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $defaults      = ['max_rows'=>20,'users'=>'-1','roles'=>'-1','reps'=>'0','num_rows'=>5,'trim'=>20,'order'=>'desc'];
        $this->settings= array_replace_recursive($defaults, $settings);
    }

    public function settingsStructure()
    {
        $order = viewKeyDropdown(['asc'=>lang('increasing'),'desc'=>lang('decreasing')]);
        return [
            'max_rows'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['max_rows']]],
            'users'   => ['label'=>lang('users'),    'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10, 'multiple'=>'multiple']],
            'roles'   => ['label'=>lang('groups'),   'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10, 'multiple'=>'multiple']],
            'reps'    => ['label'=>lang('just_reps'),'position'=>'after','attr'=>['type'=>'selNoYes','value'=>$this->settings['reps']]],
            'num_rows'=> ['order'=>10,'break'=>true, 'position'=>'after','label'=>lang('limit_results'),'options'=>['min'=>0,'max'=>50,'width'=>100],'attr'=>['type'=>'spinner','value'=>$this->settings['num_rows']]],
            'trim'    => ['order'=>20,'break'=>true, 'position'=>'after','label'=>lang('truncate'),     'options'=>['min'=>0,'max'=>99,'width'=>100],'attr'=>['type'=>'spinner','value'=>$this->settings['trim']]],
            'order'   => ['order'=>30,'break'=>true, 'position'=>'after','label'=>lang('sort_order'),   'values' =>$order,'attr'=>['type'=>'select','value'=>$this->settings['order']]]];
    }

    public function render(&$layout=[])
    {
        $struc  = $this->settingsStructure();
        $today  = date('Y-m-d');
        $filter = "date>'{$today}'";
        if ($this->settings['reps']) {
            if (getUserCache('security', 'admin', false, 0)<3) { $filter.= " AND user_id='".getUserCache('profile', 'admin_id', false, 0)."'"; }
        }
        $order  = $this->settings['order']=='desc' ? 'date DESC' : 'date';
        $result = dbGetMulti(BIZUNO_DB_PREFIX."audit_log", $filter, $order, ['date','user_id','log_entry'], $this->settings['num_rows']);
        if (empty($result)) { $rows[] = "<span>".lang('no_results')."</span>"; }
        else {
            foreach ($result as $entry) { // build the list
                $left   = substr($entry['date'], 11).($this->settings['reps'] ? ' - ' : ' ('.$this->getTitle($entry['user_id']).') ');
                $left  .= viewText($entry['log_entry'], $this->settings['trim']?$this->settings['trim']:999);
                $right  = '';
                $action = '';
                $rows[] = viewDashLink($left, $right, $action);
            }
        }
        $layout = array_merge_recursive($layout, [
            'divs'   => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'num_rows', $this->code.'order', $this->code.'trim']]]],
                'body' =>['order'=>50,'type'=>'list','key'=>$this->code]],
            'fields' => [
                $this->code.'num_rows'=> array_merge_recursive($struc['num_rows'],['events'=>['onChange'=>"jq('#{$this->code}num_rows').keyup();"]]),
                $this->code.'trim'    => array_merge_recursive($struc['trim'],    ['events'=>['onChange'=>"jq('#{$this->code}num_rows').keyup();"]]),
                $this->code.'order'   => array_merge_recursive($struc['order'],   ['events'=>['onChange'=>"dashSubmit('$this->moduleID:$this->code', 0);"]])],
            'lists'  => [$this->code=>$rows],
            'jsReady'=>['init'=>"dashDelay('$this->moduleID:$this->code', 0, '{$this->code}num_rows'); dashDelay('$this->moduleID:$this->code', 0, '{$this->code}trim');"]]);
      }

    public function save()
    {
        $menu_id = clean('menuID', 'text', 'get');
        $settings['num_rows']= clean($this->code.'num_rows', 'integer','post');
        $settings['order']   = clean($this->code.'order', 'text',   'post');
        $settings['trim']    = clean($this->code.'trim', 'integer','post');
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }

    private function getTitle($id) {
        if (!$id) { return $id; }
        if (isset($this->titles[$id])) { return $this->titles[$id]; }
        $title = dbGetValue(BIZUNO_DB_PREFIX.'users', 'title', "admin_id='$id'");
        $this->titles[$id] = $title ? $title : $id;
        return $this->titles[$id];
    }
}
