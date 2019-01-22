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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-10-10
 * @filesource /lib/controller/module/bizuno/dashboards/todays_audit/todays_audit.php
 */

namespace bizuno;

define('DASHBOARD_TODAYS_AUDIT_VERSION','3.1');

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
        $noYes = ['0'=>lang('no'),'1'=>lang('yes')];
        $order = ['asc'=>lang('increasing'),'desc'=>lang('decreasing')];
        for ($i = 0; $i <= $this->settings['max_rows']; $i++) { $list_length[] = ['id'=>$i, 'text'=>$i]; }
        $temps = [0,10,20,25,30,35,40,45,50,55,60];
        foreach ($temps as $value) { $trims[] = ['id'=>$value, 'text'=>$value]; }
        return [
            'max_rows'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['max_rows']]],
            'users'   => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10, 'multiple'=>'multiple']],
            'roles'   => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10, 'multiple'=>'multiple']],
            'reps'    => ['label'=>lang('just_reps'),    'values'=>viewKeyDropdown($noYes),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['reps']]],
            'num_rows'=> ['label'=>lang('limit_results'),'values'=>$list_length,'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['num_rows']]],
            'trim'    => ['label'=>lang('truncate'),     'values'=>$trims,'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['trim']]],
            'order'   => ['label'=>lang('sort_order'),   'values'=>viewKeyDropdown($order),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['order']]]];
    }

    public function render()
    {
        $data = $this->settingsStructure();
        $data['btnSave'] = ['attr'=>['type'=>'button','value'=>lang('save')],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]];
        $html  = '<div>';
        $html .= '  <div id="'.$this->code.'_attr" style="display:none">';
        $html .= '    <form id="'.$this->code.'Form" action="">';
        $html .= '      <div style="white-space:nowrap">'.html5($this->code.'num_rows',$data['num_rows']).'</div>';
        $html .= '      <div style="white-space:nowrap">'.html5($this->code.'order',   $data['order'])   .'</div>';
        $html .= '      <div style="white-space:nowrap">'.html5($this->code.'trim',    $data['trim'])    .'</div>';
        $html .= '      <div style="text-align:right;">' .html5($this->code.'_btn',    $data['btnSave']) .'</div>';
        $html .= '    </form>';
        $html .= '  </div>';
        // Build content box
        $today  = date('Y-m-d'); //localeCalculateDate(date('Y-m-d'), -1); // get yesterday
        $filter = "date>'{$today}'";
        if ($this->settings['reps']) {
            if (getUserCache('security', 'admin', false, 0)<3) { $filter.= " AND user_id='".getUserCache('profile', 'admin_id', false, 0)."'"; }
        }
        $order  = $this->settings['order']=='desc' ? 'date DESC' : 'date';
        $result = dbGetMulti(BIZUNO_DB_PREFIX."audit_log", $filter, $order, ['date','user_id','log_entry'], $this->settings['num_rows']);
        if (sizeof($result) > 0) {
            foreach ($result as $entry) {
                $html .= '  <div>'.substr($entry['date'], 11).($this->settings['reps'] ? ' - ' : ' ('.$this->getTitle($entry['user_id']).') ');
                $html .= viewText($entry['log_entry'], $this->settings['trim']?$this->settings['trim']:999)."</div>\n";
            }
        } else {
            $html .= '  <div>'.lang('no_results')."</div>\n";
        }
        $html .= '</div>';
        return $html;
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
