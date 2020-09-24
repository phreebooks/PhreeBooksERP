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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-07-22
 * @filesource /lib/controller/module/bizuno/dashboards/my_messages/my_messages.php
 */

namespace bizuno;

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
            'users' => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']]];
    }

    public function render(&$layout=[])
    {
        if (empty($this->settings['data'])) { $rows[] = "<span>".lang('no_results')."</span>"; }
        else { for ($i=0,$j=1; $i<sizeof($this->settings['data']); $i++,$j++) {
            $content= "&#9679; {$this->settings['data'][$i]}";
            $trash  = html5('', ['icon'=>'trash','size'=>'small','events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) { dashSubmit('$this->moduleID:$this->code', $j); }"]]);
            $rows[] = viewDashList($content, $trash);
        } }
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'_0', $this->code.'_1', $this->code.'_btn']]]],
                'body' =>['order'=>50,'type'=>'list','key'=>$this->code]],
            'fields'=> [
                $this->code.'_0'  =>['order'=>10,'break'=>true,'label'=>$this->lang['send_message_to'],'values'=>listUsers(),'attr'=>['type'=>'select']],
                $this->code.'_1'  =>['order'=>20,'break'=>true,'label'=>lang('message'),'attr'=>['type'=>'text','required'=>true,'size'=>80]],
                $this->code.'_btn'=>['order'=>70,'attr'=>['type'=>'button','value'=>lang('add')],'events'=>['onClick'=>"dashSubmit('$this->moduleID:$this->code', 0);"]]],
            'lists' => [$this->code=>$rows]]);
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
