<?php
/*
 * Bizuno dashboard - My ToDo
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
 * @version    3.x Last Update: 2020-01-17
 * @filesource /lib/controller/module/bizuno/dashboards/my_to_do/my_to_do.php
 */

namespace bizuno;

class my_to_do
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'my_to_do';
    public $category = 'general';

    function __construct($settings)
    {
        $this->security= 4;
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

    public function render(&$layout=[])
    {
        if (empty($this->settings['data'])) { $rows[] = "<span>".lang('no_results')."</span>"; }
        else { for ($i=0,$j=1; $i<sizeof($this->settings['data']); $i++,$j++) {
            $row  = '<span style="float:left">&#9679; '."{$this->settings['data'][$i]}</span>";
            $row .= '<span style="float:right">'.html5('', ['icon'=>'trash','size'=>'small','events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) dashboardAttr('$this->moduleID:$this->code', $j);"]]).'</span>';
            $rows[] = $row;
        } }
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'_0', $this->code.'_btn']]]],
                'body' =>['order'=>50,'type'=>'list','key'=>$this->code]],
            'fields'=> [
                $this->code.'_0'  =>['order'=>10,'break'=>true,'label'=>lang('note'),'attr'=>['type'=>'text','required'=>true,'size'=>50]],
                $this->code.'_btn'=>['order'=>70,'attr'=>['type'=>'button','value'=>lang('add')],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]]],
            'lists' => [$this->code=>$rows]]);
    }

    public function save()
    {
        $menu_id  = clean('menuID', 'cmd', 'get');
        $rmID     = clean('rID', 'integer', 'get');
        $add_entry= clean($this->code.'_0', 'text', 'post');
        if (!$rmID && $add_entry == '') { return; } // do nothing if no title or url entered
        // fetch the current settings
        $result = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND menu_id='$menu_id' AND dashboard_id='$this->code'");
        $settings = json_decode($result['settings'], true);
        if ($rmID) { array_splice($settings['data'], $rmID - 1, 1); }
        else       { $settings['data'][] = $add_entry; }
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
        return $result['id'];
    }
}
