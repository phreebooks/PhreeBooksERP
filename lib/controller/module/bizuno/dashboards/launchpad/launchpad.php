<?php
/*
 * Bizuno dashboard - Launchpad to menu links
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
 * @version    3.x Last Update: 2019-07-18
 * @filesource /lib/controller/module/bizuno/dashboards/launchpad/launchpad.php
 */

namespace bizuno;

class launchpad
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'launchpad';
    public $category = 'bizuno';

    function __construct($settings)
    {
        $this->security= 4;
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->settings= $settings;
    }

    public function render(&$layout=[])
    {
        $this->choices = [['id'=>'', 'text'=>lang('select')]];
        $tmp1 = getUserCache('menuBar');
        $menu1= sortOrderLang($tmp1['child']);
        $this->listMenus($menu1);
        $tmp2 = getUserCache('quickBar');
        $menu2= sortOrderLang($tmp2['child']);
        $this->listMenus($menu2, lang('settings'));
        $data = ['delete_icon'=>['icon'=>'trash', 'size'=>'small']];
        // build the delete list inside of the settings
        $html = $body = '';
        if (is_array($this->settings)) { foreach ($this->settings as $idx => $value) {
            $parts = explode(':', $value, 2);
            if (sizeof($parts) > 1) { $parts[0] = $parts[1]; } // for legacy
            $props = $this->findIdx($menu1, $parts[0]);
            if (!$props) { $props = $this->findIdx($menu2, $parts[0]); } // try the quickBar
            $data['delete_icon']['events'] = ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) dashboardAttr('$this->moduleID:$this->code', ($idx+1));"];
            $html  .= '<div><div style="float:right;height:17px;">'.html5('delete_icon', $data['delete_icon']).'</div>';
            $html  .= '<div style="min-height:17px;">'.lang($props['label']).'</div></div>';
            // build the body part while we're here
            $btnHTML= html5('', ['icon'=>$props['icon']]).'<br />'.lang($props['label']);
            $body  .= html5('', ['styles'=>['width'=>'100px','height'=>'100px'],'events'=>['onClick'=>$props['events']['onClick']],'attr'=>['type'=>'button','value'=>$btnHTML]])."&nbsp;";
        } } else { $body .= "<span>".lang('no_results')."</span>"; }
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'_0', $this->code.'_btn', $this->code.'_del']]]],
                'body' =>['order'=>50,'type'=>'html','html'=>$body]],
            'fields'=> [
                $this->code.'_0'  =>['order'=>10,'break'=>true,'label'=>lang('select'),'values'=>$this->choices,'attr'=>['type'=>'select']],
                $this->code.'_btn'=>['order'=>70,'attr'=>['type'=>'button','value'=>lang('add')],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]],
                $this->code.'_del'=>['order'=>90,'html'=>$html,'attr'=>['type'=>'raw']]]]);
    }

    private function listMenus($source, $cat=false)
    {
        if (empty($source)) { return; }
        foreach ($source as $menu) {
            msgDebug("\nmenu list = ".print_r($menu, true));
            if (!isset($menu['child'])) { continue; }
            foreach ($menu['child'] as $idx => $submenu) {
                msgDebug("\nsubmenu list = ".print_r($submenu, true));
                if (empty($submenu['security'])) { continue; }
                if (!isset($submenu['hidden']) || !$submenu['hidden']) {
                    $label = $cat ? $cat : lang($menu['label']);
                    $this->choices[] = ['id'=>"$idx", 'text'=>"$label - ".lang($submenu['label'])];
                    if (isset($submenu['child'])) { $this->listMenus($menu['child']); }
                }
            }
        }
    }

    private function findIdx($source, $key='')
    {
        $props = false;
        foreach ($source as $menu) {
            if (!isset($menu['child'])) { continue; }
            foreach ($menu['child'] as $idx => $submenu) {
                if ($key == $idx) { return $submenu; }
                if (isset($submenu['child'])) {
                    $props = $this->findIdx($menu['child'], $key);
                    if ($props) { return $props; }
                }
            }
        }
        return $props;
    }

    public function save()
    {
        $menu_id= clean('menuID', 'text_single', 'get');
        $rmID   = clean('rID', 'integer', 'get');
        $add_id = clean($this->code.'_0', 'text', 'post');
        if (!$rmID && $add_id == '') { return; } // do nothing if no label or url entered
        // fetch the current settings
        $result = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND menu_id='$menu_id' AND dashboard_id='$this->code'");
        if ($rmID) { // remove element
            $settings   = json_decode($result['settings']);
            unset($settings[($rmID-1)]);
        } elseif ($result['settings']) { // append new menu
            $settings   = json_decode($result['settings']);
            $settings[] = $add_id;
        } else { // first entry
            $settings = [$add_id];
        }
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode(array_values($settings))], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
        return $result['id'];
    }
}
