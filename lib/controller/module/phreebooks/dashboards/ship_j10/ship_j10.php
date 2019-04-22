<?php
/*
 * PhreeBooks dashboard - Reminder for Customer Sales Orders that are due to ship today
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
 * @version    3.x Last Update: 2019-04-12
 * @filesource /lib/controller/module/phreebooks/dashboards/ship_j10/ship_j10.php
 */

namespace bizuno;

define('DASHBOARD_SHIP_J10_VERSION','3.1.0');

class ship_j10
{
    public  $moduleID = 'phreebooks';
    public  $methodDir= 'dashboards';
    public  $code     = 'ship_j10';
    public  $category = 'customers';
    private $sendEmail= false;
    private $emailList= [];


    function __construct($settings)
    {
        $this->security= getUserCache('security', 'j10_mgr', false, 0);
        $defaults      = ['jID'=>10,'notified'=>"{}",'max_rows'=>20,'users'=>'-1','roles'=>'-1','reps'=>'0','num_rows'=>0,'order'=>'asc'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->trim    = 20; // length to trim primary_name to fit in frame
        $this->noYes   = ['0'  =>lang('no'),        '1'   =>lang('yes')];
        $this->order   = ['asc'=>lang('increasing'),'desc'=>lang('decreasing')];
        $this->today   = date('Y-m-d');
    }

    public function settingsStructure()
    {
        for ($i = 0; $i <= $this->settings['max_rows']; $i++) { $list_length[] = ['id'=>$i, 'text'=>$i]; }
        return [
            'notified'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['jID']]],
            'jID'     => ['attr'=>['type'=>'hidden','value'=>$this->settings['jID']]],
            'max_rows'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['max_rows']]],
            'users'   => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10, 'multiple'=>'multiple']],
            'roles'   => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10, 'multiple'=>'multiple']],
            'reps'    => ['label'=>lang('just_reps'),    'values'=>viewKeyDropdown($this->noYes),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['reps']]],
            'num_rows'=> ['label'=>lang('limit_results'),'values'=>$list_length,'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['num_rows']]],
            'order'   => ['label'=>lang('sort_order'),   'values'=>viewKeyDropdown($this->order),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['order']]]];
    }

    public function render()
    {
        global $currencies;
        bizAutoLoad(BIZUNO_LIB.'controller/module/phreebooks/functions.php', 'getInvoiceInfo', 'function');
        $btn   = ['attr'=>['type'=>'button','value'=>lang('save')],'styles'=>['cursor'=>'pointer'],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]];
        $data  = $this->settingsStructure();
        $html  = '<div>
    <div id="'.$this->code.'_attr" style="display:none"><form id="'.$this->code.'Form" action="">
    <div style="white-space:nowrap">'.html5($this->code.'num_rows',$data['num_rows']).'</div>
    <div style="white-space:nowrap">'.html5($this->code.'order',   $data['order']).'</div>
    <div style="text-align:right;">' .html5($this->code.'_btn', $btn).'</div>
</form></div>';
        // Build content box

        $filter= "m.journal_id={$this->settings['jID']} AND m.closed='0' AND i.gl_type='itm' AND i.date_1<='$this->today'";
        if ($this->settings['reps'] && getUserCache('profile', 'contact_id', false, '0')) {
            if (getUserCache('security', 'admin', false, 0)<3) { $filter.= " AND m.rep_id='".getUserCache('profile', 'contact_id', false, '0')."'"; }
        }
        if (getUserCache('profile', 'restrict_store', false, -1) > 0) { $filter.= " AND m.store_id=".getUserCache('profile', 'restrict_store', false, -1).""; }
        $order = "ORDER BY " . ($this->settings['order']=='desc' ? 'm.post_date DESC, m.invoice_num DESC' : 'm.post_date, m.invoice_num');
        $sql   = "SELECT m.id, m.journal_id, m.post_date, m.primary_name_b, m.invoice_num, m.total_amount, m.currency, m.currency_rate, i.id AS iID, i.qty
            FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id WHERE $filter $order";
        $stmt  = dbGetResult($sql);
        $result= $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        $total = $rID = 0;
        $output = [];
        foreach ($result as $row) {
            // Check for already shipped
            $lineTotal = dbGetValue(BIZUNO_DB_PREFIX.'journal_item', 'SUM(qty)', "item_ref_id={$row['iID']}", false);
            if ($lineTotal >= $row['qty']) { continue; } // filled
            if ($row['id'] == $rID) { continue; } // prevent dups
            $output[] = $row;
            $rID = $row['id'];
        }
        $html .= html5('', ['classes'=>['easyui-datalist'],'attr'=>['type'=>'ul']])."\n";
        if (sizeof($output) > 0) {
            bizAutoLoad(BIZUNO_LIB."model/mail.php", 'bizunoMailer');
            // get the notified list
            $settings = getModuleCache($this->moduleID, $this->methodDir, $this->code, []);
            $notified = $settings['settings']['notified'];
            if (empty($notified['date']) || $notified['date'] <> $this->today) { $notified = ['date'=>$this->today, 'rIDs'=>[]]; }
            msgDebug("\nNotified = ".print_r($notified, true));
            foreach ($output as $entry) {
                $currencies->iso  = $entry['currency'];
                $currencies->rate = $entry['currency_rate'];
                $html .= html5('', ['attr'=>['type'=>'li']]).'<span style="float:left">';
                $html .= html5('', ['events'=>['onClick'=>"tabOpen('_blank', 'phreebooks/main/manager&jID={$this->settings['jID']}&rID={$entry['id']}');"],'attr'=>['type'=>'button','value'=>"#{$entry['invoice_num']}"]]);
                $html .= viewDate($entry['post_date'])." - ".viewText($entry['primary_name_b'], $this->trim).'</span></li>';
//              $html .= viewDate($entry['post_date'])." - ".viewText($entry['primary_name_b'], $this->trim).'</span><span style="float:right">'.viewFormat($entry['total_amount'], 'currency').'</span></li>';
                $total += $entry['total_amount'];
                $this->notifyCheck($notified, $entry);
            }
            if ($this->sendEmail && !empty($this->emailList)) { $this->notifyEmail(); }
            $currencies->iso  = getUserCache('profile', 'currency', false, 'USD');
            $currencies->rate = 1;
//          $html .= '<li><div style="float:right"><b>'.viewFormat($total, 'currency').'</b></div><div style="float:left"><b>'.lang('total')."</b></div></li>";
            $settings['settings']['notified'] = $notified;
            setModuleCache($this->moduleID, $this->methodDir, $this->code, $settings);
        } else {
            $html .= "<li><span>".lang('no_results')."</span></li>";
        }
        $html .= '</ul></div>';
        return $html;
    }

    public function save()
    {
        $menu_id  = clean('menuID', 'text', 'get');
        $settings['num_rows']= clean($this->code.'num_rows','integer','post');
        $settings['order']   = clean($this->code.'order',   'cmd', 'post');
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }

    private function notifyCheck(&$notified, $entry)
    {
        if ($notified['date'] == $this->today && in_array($entry['id'], $notified['rIDs'])) { return; } // notified already
        msgDebug("\nAdding record {$entry['id']} un-notified invoice # {$entry['invoice_num']} with customer: {$entry['primary_name_b']}");
        $notified['rIDs'][]= $entry['id'];
        $this->emailList[] = ['invNum'=>$entry['invoice_num'], 'name'=>$entry['primary_name_b']];
        $this->sendEmail   = true;
    }

    private function notifyEmail()
    {
        $html = '';
        msgDebug("\nEmail list before email: ".print_r($this->emailList, true));
        foreach ($this->emailList as $row) { $html .= "SO #{$row['invNum']}: {$row['name']}<br />"; }
        $fromEmail = 'do-not-reply@phreesoft.com';
        $toEmail   = getModuleCache('bizuno', 'settings', 'company', 'email');
        $toName    = getModuleCache('bizuno', 'settings', 'company', 'contact');
        $msgSubject= sprintf($this->lang['email_subject'], viewFormat($this->today, 'date'));
        $msgBody   = sprintf($this->lang['email_body'], $html);
        $mail    = new bizunoMailer($toEmail, $toName, $msgSubject, $msgBody, $fromEmail);
        $mail->sendMail();
        msgAdd($msgBody);
        msgLog($msgSubject);
    }
}
