<?php
/*
 * Bizuno dashboard - My Messages
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
 * @filesource /lib/controller/module/bizuno/dashboards/my_messages/my_messages.php
 */

namespace bizuno;

define('DASHBOARD_MY_MESSAGES_VERSION','3.1');

class my_messages
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'my_messages';
    public $category = 'general';
    
    function __construct($settings)
    {
        $this->security = 4; // full access
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $defaults      = ['users'=>'-1','roles'=>'-1'];
        $this->settings= array_replace_recursive($defaults, $settings);
    }

    public function settingsStructure()
    {
        return [
            'users' => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10, 'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10, 'multiple'=>'multiple']]];
    }

    public function render()
    {
        $data = [
            $this->code.'_0'     =>['label'=>$this->lang['send_message_to'],'values'=>listUsers(),'attr'=>['type'=>'select']],
            $this->code.'_1'     =>['label'=>lang('message'),'attr'=>['required'=>'true','size'=>80],'classes'=>['easyui-validatebox']],
            $this->code.'_button'=>['attr' =>['type'=>'button','value'=>lang('send')],'styles'=>['cursor'=>'pointer'],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]]];
        $html  = '<div>';
        $html .= '  <div id="'.$this->code.'_attr" style="display:none">';
        $html .= '    <form id="'.$this->code.'Form" action="">';
        $html .= '      <div style="white-space:nowrap">'.html5($this->code.'_0',      $data[$this->code.'_0']).'</div>';
        $html .= '      <div style="white-space:nowrap">'.html5($this->code.'_1',      $data[$this->code.'_1']).'</div>';
        $html .= '      <div style="text-align:right;">' .html5($this->code.'_button', $data[$this->code.'_button']).'</div>';
        $html .= '    </form>';
        $html .= '  </div>';
        // Build content box
        $index = 1;
        if (!isset($this->settings['data'])) { unset($this->settings['users']); unset($this->settings['roles']); $this->settings = ['data' => $this->settings]; } // OLD WAY
        $html .= html5('', ['classes'=>['easyui-datalist'],'attr'=>['type'=>'ul']])."\n";
        if (!empty($this->settings['data'])) {
            foreach ($this->settings['data'] as $entry) {
                $html .= html5('', ['attr'=>['type'=>'li']]).'<span style="float:left">';
                $html .= "&#9679; $entry".'</span><span style="float:right">'.html5('', ['icon'=>'trash','size'=>'small','events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) dashboardAttr('$this->moduleID:$this->code', $index);"]]).'</span></li>';
                $index++;
            }
        } else {
            $html .= '<li><span>'.lang('no_results')."</span></li>";
        }
        $html .= '</ul></div>';
        return $html;
    }

    public function save()
    {
        $rmID   = clean('rID', 'integer', 'get');
        $userID = clean($this->code.'_0', 'integer', 'post');
        $message= clean($this->code.'_1', 'text', 'post');
        if (!$rmID && $message == '') { return; } // do nothing if no title or url entered
        // if add, get the users settings and append
        if ($userID > 0) {
            $settings = json_decode(dbGetValue(BIZUNO_DB_PREFIX."users_profiles", 'settings', "user_id=$userID AND dashboard_id='$this->code'"), true);
            if (!isset($settings['data'])) { unset($settings['users']); unset($settings['roles']); $settings=['data'=>$settings]; } // OLD WAY
            $title = dbGetValue(BIZUNO_DB_PREFIX."users", 'title', "admin_id=".getUserCache('profile', 'admin_id', false, 0)); 
            $settings['data'][] = viewDate(date('Y-m-d'))." $title: $message";
            $cnt = dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=$userID AND dashboard_id='$this->code'");
            if (!$cnt) { msgAdd($this->lang['msg_no_user_found']); }
        }
        if ($rmID) { // else if del, get current user and delete entry
            $settings   = json_decode(dbGetValue(BIZUNO_DB_PREFIX."users_profiles", 'settings', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code'"), true);
            if (!isset($settings['data'])) { unset($settings['users']); unset($settings['roles']); $settings=['data'=>$settings]; } // OLD WAY
            array_splice($settings['data'], $rmID - 1, 1);
            dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code'");
        }
        return true;
    }
}
