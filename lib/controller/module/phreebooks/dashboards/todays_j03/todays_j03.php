<?php
/*
 * PhreeBooks dashboard - Today's Vendor Requests for Quote
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
 * @filesource /lib/controller/module/phreebooks/dashboards/todays_j03/todays_j03.php
 */

namespace bizuno;

class todays_j03
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'dashboards';
    public $code     = 'todays_j03';
    public $category = 'vendors';

    function __construct($settings)
    {
        $this->security= getUserCache('security', 'j3_mgr', false, 0);
        $defaults      = ['jID'=>3,'max_rows'=>20,'users'=>'-1','roles'=>'-1','reps'=>'0','num_rows'=>5,'order'=>'desc'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->trim    = 20; // length to trim primary_name to fit in frame
        $this->noYes   = ['0'  =>lang('no'),        '1'   =>lang('yes')];
        $this->order   = ['asc'=>lang('increasing'),'desc'=>lang('decreasing')];
    }

    public function settingsStructure()
    {
        for ($i = 0; $i <= $this->settings['max_rows']; $i++) { $list_length[] = ['id'=>$i, 'text'=>$i]; }
        return [
            'jID'     => ['attr'=>['type'=>'hidden','value'=>$this->settings['jID']]],
            'max_rows'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['max_rows']]],
            'users'   => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10, 'multiple'=>'multiple']],
            'roles'   => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10, 'multiple'=>'multiple']],
            'reps'    => ['label'=>lang('just_reps'),    'values'=>viewKeyDropdown($this->noYes),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['reps']]],
            'num_rows'=> ['label'=>lang('limit_results'),'values'=>$list_length,'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['num_rows']]],
            'order'   => ['label'=>lang('sort_order'),   'values'=>viewKeyDropdown($this->order),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['order']]]];
    }

    public function render(&$layout=[])
    {
        global $currencies;
        $struc = $this->settingsStructure();
        $filter= "journal_id={$this->settings['jID']} AND post_date='".date('Y-m-d')."'";
        if ($this->settings['reps'] && getUserCache('profile', 'contact_id', false, '0')) {
            if (getUserCache('security', 'admin', false, 0)<3) { $filter.= " AND rep_id='".getUserCache('profile', 'contact_id', false, '0')."'"; }
        }
        if (getUserCache('profile', 'restrict_store', false, -1) > 0) { $filter.= " AND store_id=".getUserCache('profile', 'restrict_store', false, -1).""; }
        $order = $this->settings['order']=='desc' ? 'post_date DESC, invoice_num DESC' : 'post_date, invoice_num';
        $result= dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $filter, $order, ['id','total_amount','currency','currency_rate','post_date','invoice_num', 'primary_name_b'], $this->settings['num_rows']);
        $total = 0;
        if (empty($result)) { $rows[] = "<span>".lang('no_results')."</span>"; }
        else {
            foreach ($result as $entry) { // build the list
                $currencies->iso  = $entry['currency'];
                $currencies->rate = $entry['currency_rate'];
                $row  = '<span style="float:left">'.html5('', ['events'=>['onClick'=>"tabOpen('_blank', 'phreebooks/main/manager&jID={$this->settings['jID']}&rID={$entry['id']}');"],'attr'=>['type'=>'button','value'=>"#{$entry['invoice_num']}"]]);
                $row .= viewText($entry['primary_name_b'], $this->trim).'</span><span style="float:right">'.viewFormat($entry['total_amount'], 'currency').'</span></li>';
                $total += $entry['total_amount'];
                $rows[]= $row;
            }
            $currencies->iso  = getDefaultCurrency();
            $currencies->rate = 1;
            $rows[] = '<div style="float:right"><b>'.viewFormat($total, 'currency').'</b></div><div style="float:left"><b>'.lang('total')."</b></div>";
        }
        $filter = ucfirst(lang('filter')).": ".ucfirst(lang('sort'))." ".strtoupper($this->settings['order']).(!empty($this->settings['num_rows']) ? " ({$this->settings['num_rows']});" : '');
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'num_rows', $this->code.'order', $this->code.'_btn']]]],
                'head' =>['order'=>40,'type'=>'html','html'=>$filter],
                'body' =>['order'=>50,'type'=>'list','key' =>$this->code]],
            'fields'=> [
                $this->code.'num_rows'=> array_merge($struc['num_rows'],['order'=>10,'break'=>true]),
                $this->code.'order'   => array_merge($struc['order'],   ['order'=>20,'break'=>true]),
                $this->code.'_btn'    => ['order'=>90,'attr'=>['type'=>'button','value'=>lang('save')],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]]],
            'lists' => [$this->code=>$rows]]);
    }

    public function save()
    {
        $menu_id  = clean('menuID', 'text', 'get');
        $settings['num_rows']= clean($this->code.'num_rows', 'integer','post');
        $settings['order']   = clean($this->code.'order', 'cmd', 'post');
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }
}
