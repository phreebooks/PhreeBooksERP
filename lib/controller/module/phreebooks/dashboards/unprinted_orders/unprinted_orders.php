<?php
/*
 * Bizuno extension extShipping dashboard - Unshipped Orders
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
 * @filesource /EXTENSION_PATH/extShipping/dashboards/unprinted_orders/unprinted_orders.php
 */

namespace bizuno;

class unprinted_orders
{
    public  $moduleID = 'phreebooks';
    public  $methodDir= 'dashboards';
    public  $code     = 'unprinted_orders';
    public  $category = 'customers';
    private $trim     = 20; // length to trim primary_name to fit in frame

    function __construct($settings)
    {
        $this->security= getUserCache('security', 'j10_mgr', false, 0);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $defaults      = ['max_rows'=>20,'users'=>-1,'roles'=>-1,'num_rows'=>0,'order'=>1];
        $this->order   = ['asc'=>lang('increasing'),'desc'=>lang('decreasing')];
        $this->settings= array_replace_recursive($defaults, $settings);
    }

    public function settingsStructure()
    {
        return [
            'max_rows'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['max_rows']]],
            'users'   => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10, 'multiple'=>'multiple']],
            'roles'   => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10, 'multiple'=>'multiple']],
            'num_rows'=> ['order'=>10,'break'=>true,'position'=>'after','label'=>lang('limit_results'),'options'=>['min'=>0,'max'=>50,'width'=>100],'attr'=>['type'=>'spinner','value'=>$this->settings['num_rows']]],
            'order'   => ['order'=>20,'break'=>true,'position'=>'after','label'=>lang('sort_order'),   'values'=>viewKeyDropdown($this->order),'attr'=>['type'=>'select','value'=>$this->settings['order']]]];
    }

    /**
     * Generates the structure for the dashboard view
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function render(&$layout=[])
    {
        $struc  = $this->settingsStructure();
        $order  = $this->settings['order']=='desc' ? 'post_date DESC, invoice_num DESC' : 'post_date, invoice_num';
        if ($this->settings['num_rows']) { $order .= " LIMIT ".$this->settings['num_rows']; }
        $filter = "journal_id=12 AND closed='0' AND printed='0'";
        if (getUserCache('profile', 'restrict_store', false, -1) > -1) { $filter .= " AND store_id=".getUserCache('profile', 'restrict_store', false, -1); }
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $filter, $order);
        if (empty($result)) { $rows[] = "<span>".lang('no_results')."</span>"; }
        else {
            foreach ($result as $entry) { // build the list
                $left   = viewDate($entry['post_date']).' - '.viewText($entry['primary_name_b'], $this->trim);
                $right  = html5('', ['icon'=>'email','events'=>['onClick'=>"winOpen('phreeformOpen', 'phreeform/render/open&group=cust:j12&date=a&xfld=journal_main.id&xcr=equal&xmin={$entry['id']}');"]]);
                $action = html5('', ['events'=>['onClick'=>"winHref(bizunoHome+'&p=phreebooks/main/manager&jID=12&rID={$entry['id']}');"],'attr'=>['type'=>'button','value'=>"#{$entry['invoice_num']}"]]);
                $rows[] = viewDashLink($left, $right, $action);
            }
        }
        $filter = ucfirst(lang('filter')).": ".ucfirst(lang('sort'))." ".strtoupper($this->settings['order']).(!empty($this->settings['num_rows']) ? " ({$this->settings['num_rows']});" : '');
        $layout = array_merge_recursive($layout, [
            'divs'   => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'num_rows', $this->code.'order']]]],
                'head' =>['order'=>40,'type'=>'html','html'=>$filter,'hidden'=>getModuleCache('bizuno','settings','general','hide_filters',0)],
                'body' =>['order'=>50,'type'=>'list','key'=>$this->code]],
            'fields' => [
                $this->code.'num_rows'=> array_merge_recursive($struc['num_rows'],['events'=>['onChange'=>"jq('#{$this->code}num_rows').keyup();"]]),
                $this->code.'order'   => array_merge_recursive($struc['order'],   ['events'=>['onChange'=>"dashSubmit('$this->moduleID:$this->code', 0);"]])],
            'lists'  => [$this->code=>$rows],
            'jsReady'=>['init'=>"dashDelay('$this->moduleID:$this->code', 0, '{$this->code}num_rows');"]]);
    }

    public function save()
    {
        $menu_id  = clean('menuID', 'text', 'get');
        $settings = [
            'num_rows'=> clean($this->code.'num_rows','integer', 'post'),
            'order'   => clean($this->code.'order', ['format'=>'text','default'=>'desc'], 'post'),];
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }
}
