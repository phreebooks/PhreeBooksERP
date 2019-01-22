<?php
/*
 * PhreeBooks dashboard - Open Customer Requests for Quote
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
 * @filesource /lib/controller/module/phreebooks/dashboards/open_j09/open_j09.php
 */

namespace bizuno;

define('DASHBOARD_OPEN_J09_VERSION','3.1');

class open_j09
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'dashboards';
    public $code     = 'open_j09';
    public $category = 'customers';
    
    function __construct($settings)
    {
        $this->security= getUserCache('security', 'j9_mgr', false, 0);
        $defaults      = ['jID'=>9,'max_rows'=>20,'users'=>'-1','roles'=>'-1','reps'=>'0','num_rows'=>5,'limit'=>1,'order'=>'desc'];
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
            'limit'   => ['label'=>lang('hide_future'),  'values'=>viewKeyDropdown($this->noYes),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['limit']]],
            'order'   => ['label'=>lang('sort_order'),   'values'=>viewKeyDropdown($this->order),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['order']]]];
    }

    function render()
    {
        global $currencies;
        $btn   = ['attr'=>['type'=>'button','value'=>lang('save')],'styles'=>['cursor'=>'pointer'],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]];
        $data  = $this->settingsStructure();
        $html  = '<div>';
        $html .= '  <div id="'.$this->code.'_attr" style="display:none"><form id="'.$this->code.'Form" action="">';
        $html .= '    <div style="white-space:nowrap">'.html5($this->code.'num_rows',$data['num_rows']).'</div>';
        $html .= '    <div style="white-space:nowrap">'.html5($this->code.'order',   $data['order'])   .'</div>';
        $html .= '    <div style="white-space:nowrap">'.html5($this->code.'limit',   $data['limit'])   .'</div>';
        $html .= '    <div style="text-align:right;">' .html5($this->code.'_btn', $btn).'</div>';
        $html .= '  </form></div>';
        // Build content box
        $filter = "journal_id={$this->settings['jID']} AND closed='0'";
        if ($this->settings['reps'] && getUserCache('profile', 'contact_id', false, '0')) {
            if (getUserCache('security', 'admin', false, 0)<3) { $filter.= " AND rep_id='".getUserCache('profile', 'contact_id', false, '0')."'"; }
        }
        if (isset($this->settings['limit']) && $this->settings['limit']=='1') { $filter.= " AND post_date<='".date('Y-m-d')."'"; }
        if (getUserCache('profile', 'restrict_store', false, -1) > 0) { $filter.= " AND store_id=".getUserCache('profile', 'restrict_store', false, -1).""; }
        $order  = $this->settings['order']=='desc' ? 'post_date DESC, invoice_num DESC' : 'post_date, invoice_num';
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $filter, $order, ['id','total_amount','currency','currency_rate','post_date','invoice_num', 'primary_name_b'], $this->settings['num_rows']);
        $total = 0;
        $html .= html5('', ['classes'=>['easyui-datalist'],'attr'=>['type'=>'ul']])."\n";
        if (sizeof($result) > 0) {
            foreach ($result as $entry) {
                $currencies->iso  = $entry['currency'];
                $currencies->rate = $entry['currency_rate'];
                $html .= html5('', ['attr'=>['type'=>'li']]).'<span style="float:left">';
                $html .= html5('', ['events'=>['onClick'=>"tabOpen('_blank', 'phreebooks/main/manager&jID={$this->settings['jID']}&rID={$entry['id']}');"],'attr'=>['type'=>'button','value'=>"#{$entry['invoice_num']}"]]);
                $html .= viewDate($entry['post_date'])." - ".viewText($entry['primary_name_b'], $this->trim).'</span><span style="float:right">'.viewFormat($entry['total_amount'], 'currency').'</span></li>';
                $total += $entry['total_amount'];
            }
            $currencies->iso  = getUserCache('profile', 'currency', false, 'USD');
            $currencies->rate = 1;
            $html .= '<li><div style="float:right"><b>'.viewFormat($total, 'currency').'</b></div><div style="float:left"><b>'.lang('total')."</b></div></li>\n";
        } else {
            $html .= '<li><span>'.lang('no_results')."</span></li>";
        }
        $html .= '</ul></div>';
        return $html;
      }

    function save()
    {
        $menu_id  = clean('menuID', 'text', 'get');
        $settings['num_rows']= clean($this->code.'num_rows','integer','post');
        $settings['order']   = clean($this->code.'order',   'cmd', 'post');
        $settings['limit']   = clean($this->code.'limit',   'integer','post');
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }
}
