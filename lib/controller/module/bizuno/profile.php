<?php
/*
 * This method handles user profiles
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
 * @version    3.x Last Update: 2019-02-18
 * @filesource lib/controller/module/bizuno/profile.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/bizuno/functions.php", 'getIcons', 'function');

class bizunoProfile
{
    public $moduleID = 'bizuno';

    public function __construct()
    {
        $this->lang  = getLang($this->moduleID);
        $this->freqs = ['d'=>$this->lang['daily'],'w'=>$this->lang['weekly'],'m'=>$this->lang['monthly'],'q'=>$this->lang['quarterly'],'y'=>$this->lang['yearly']];
        $this->zones = [
            ['id'=>'America/Los Angeles','text'=>'America/Los Angeles'],
            ['id'=>'America/Denver','text'=>'America/Denver'],
            ['id'=>'America/Chicago','text'=>'America/Chicago'],
            ['id'=>'America/New York','text'=>'America/New York']];
    }

    /**
     * Adds/edits user profiles
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'profile', 1)) { return; }
        $rID     = getUserCache('profile', 'admin_id', false, 0);
        $struc   = dbLoadStructure(BIZUNO_DB_PREFIX."users");
        $dbData  = dbGetRow(BIZUNO_DB_PREFIX."users", "admin_id='$rID'");
        $settings= json_decode($dbData['settings'], true)['profile'];
        unset($dbData['settings']);
        dbStructureFill($struc, $dbData);
        $data = ['title'=>lang('bizuno_profile'),
            'toolbars'=> ['tbProfile' =>['icons'=>['save'=>['order'=>40,'hidden'=>$security<2?true:false,'events'=>['onClick'=>"jq('#frmProfile').submit();"]]]]],
            'forms'   => ['frmProfile'=>['attr' =>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/profile/save"]]],
            'divs'    => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key' =>'tbProfile'],
                'formBOF'=> ['order'=>15,'type'=>'form',   'key' =>'frmProfile'],
                'heading'=> ['order'=>20,'type'=>'html',   'html'=>"<h1>".lang('bizuno_profile')."</h1>"],
                'body'   => ['order'=>50,'type'=>'tabs',   'key' =>'tabProfile'],
                'formEOF'=> ['order'=>90,'type'=>'html',   'html'=>"</form>"]],
            'tabs'    => ['tabProfile'=>['divs'=>[
                'general'  => ['order'=>10,'label'=>lang('general'),'type'=>'fields','fields'=>$this->getViewProfile($struc, $settings)],
                'reminders'=> ['order'=>50,'label'=>$this->lang['reminders'],'type'=>'html','html'=>'','options'=>['href'=>"'".BIZUNO_AJAX."&p=bizuno/profile/reminderManager&uID=".getUserCache('profile', 'admin_id', false, 0)."'"]]]]],
            'jsReady' => ['jsProfile'=>"ajaxForm('frmProfile');"]];
//"<fieldset><legend>".lang('general')."</legend>".
//"<fieldset><legend>".'Google Interface'."</legend>".
//"<fieldset><legend>".lang('password_lost')."</legend>".
//"<fieldset><legend>".lang('profile')."</legend>";
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    private function getViewProfile($fields, $settings)
    {
        $zones = viewTimeZoneSel();
        $docks = [['id'=>'top','text'=>lang('top')],['id'=>'left','text'=>lang('left')]];
        unset($fields['password']['attr']['value']);
        return [
            'title'           => array_merge($fields['title'], ['order'=>10,'break'=>true]),
            'email'           => array_merge($fields['email'], ['order'=>15,'break'=>true]),
            'gmail'           => ['order'=>20,'break'=>true,'label'=>$this->lang['gmail_address'],'tip'=>$this->lang['gmail_address_tip'],'attr'=>['type'=>'email','size'=>50,'value'=>isset($settings['gmail']) ? $settings['gmail'] : '']],
            'gzone'           => ['order'=>25,'break'=>true,'label'=>$this->lang['gmail_zone'],   'tip'=>$this->lang['gmail_zone_tip'],'options'=>['width'=>500],'values'=>$zones,'attr'=>['type'=>'select','value'=>isset($settings['gzone'] )?$settings['gzone'] : '']],
            'password'        => ['order'=>30,'break'=>true,'label'=>$this->lang['password_now'],'attr'=>['type'=>'password']],
            'password_new'    => ['order'=>35,'break'=>true,'label'=>lang('password_new'),       'attr'=>['type'=>'password']],
            'password_confirm'=> ['order'=>40,'break'=>true,'label'=>lang('password_confirm'),   'attr'=>['type'=>'password']],
            'icons'           => ['order'=>45,'break'=>true,'label'=>$this->lang['icon_set'],'values'=>getIcons(), 'attr'=>['type'=>'select','value'=>isset($settings['icons'] )?$settings['icons']:'default']],
            'theme'           => ['order'=>50,'break'=>true,'label'=>lang('theme'),          'values'=>getThemes(),'attr'=>['type'=>'select','value'=>isset($settings['theme']) ?$settings['theme']:'default']],
            'menu'            => ['order'=>55,'break'=>true,'label'=>lang('menu_pos'),       'values'=>$docks, 'attr'=>['type'=>'select','value'=>isset($settings['menu'])  ?$settings['menu'] :'left']],
            'cols'            => ['order'=>60,'break'=>true,'label'=>$this->lang['dashboard_columns'],'attr'=>['value'=>isset($settings['cols'])  ?$settings['cols']  :'3']]];
    }

    /**
     * Saves users profile
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'profile', 3)) { return; }
        setUserCache('profile', 'title', clean('title','text',   'post'));
        setUserCache('profile', 'icons', clean('icons','text',   'post'));
        setUserCache('profile', 'theme', clean('theme','text',   'post'));
        setUserCache('profile', 'menu',  clean('menu', 'text',   'post'));
        setUserCache('profile', 'cols',  clean('cols', 'integer','post'));
        setUserCache('profile', 'gmail', clean('gmail','text',   'post'));
        setUserCache('profile', 'gzone', clean('gzone','text',   'post'));
        $pw_cur= clean('password', 'password', 'post');
        $email = getUserCache('profile', 'email');
        if (strlen($pw_cur) > 0 && biz_validate_user_creds($email, $pw_cur, 'email', false)) { // check, see if reset password
            $pw_new = clean('password_new',    'password','post');
            $pw_eql = clean('password_confirm','password','post');
            bizAutoLoad(BIZUNO_ROOT."portal/guest.php", 'guest');
            $guest  = new guest();
            $pw_enc = $guest->passwordReset($pw_new, $pw_eql);
            if ($pw_enc) { portalWrite('users', ['biz_pass' => $pw_enc], 'update', "biz_user='$email'"); }
        }
        msgLog(lang('bizuno_profile')." - ".lang('update')." $email");
        $data = ['content'=>['action'=>'href','link'=>BIZUNO_HOME."&p=bizuno/profile/edit"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * manager to enter, delete and support the reminder dashboard
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function reminderManager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'profile', 1)) { return; }
        $layout = array_replace_recursive($layout, ['type'=>'divHTML',
            'divs' => ["divReminder" => ['order'=>50, 'type'=>'accordion','key' =>"accReminder"]],
            'accordion'=> ['accReminder'=>  ['divs'=>  [
                'divReminderMgr' => ['order'=>30,'label'=>$this->lang['reminders'],'type'=>'datagrid','key'=>'dgReminder'],
                'divReminderDtl' => ['order'=>70,'label'=>lang('details'),'type'=>'html', 'html'=>'&nbsp;']]]],
            'datagrid' => ['dgReminder'=>$this->dgReminder('dgReminder', $security)]]);
    }

    /**
     * lists the reminders for the user to support the manager
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function reminderManagerRows(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'profile', 1)) { return; }
        $result  = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='reminder'");
        $settings= clean($result['settings'], 'json');
        if (!isset($settings['source'])) { $settings['source'] = []; }
        $output  = [];
        if (is_array($settings['source']) && sizeof($settings['source'] > 0)) { foreach ($settings['source'] as $idx => $values) {
            $output[] = ['id'=>($idx+1),'title'=>$values['title'],'recur'=>$this->freqs[$values['recur']],
                'dateStart'=>viewFormat($values['dateStart'], 'date'),'dateNext'=>viewFormat($values['dateNext'], 'date')];
        } }
        $total = sizeof($output);
        $page = clean('page', ['format'=>'integer','default'=>1], 'post');
        $rows = clean('rows', ['format'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')], 'post');
        $sort = clean('sort', ['format'=>'text',   'default'=>'label'], 'post');
        $order= clean('order',['format'=>'text',   'default'=>'asc'], 'post');
        $temp = [];
        foreach ($output as $key => $value) { $temp[$key] = $value[$sort]; }
        array_multisort($temp, $order=='desc'?SORT_DESC:SORT_ASC, $output);
        $parts = array_slice($output, ($page-1)*$rows, $rows);
        $layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>json_encode(['total'=>$total, 'rows'=>$parts])]);
    }

    /**
     * Editor for reminders
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function reminderEdit(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'profile', 2)) { return; }
        $flds = ['title','dateStart','recur'];
        $fields   = [
            'title'    => ['order'=>10,'break'=>true,'label'=>lang('title'),'attr'=>['value'=>'']],
            'dateStart'=> ['order'=>20,'break'=>true,'label'=>$this->lang['start_date'],'classes'=>['easyui-datebox'],'attr'=>['value'=>date('Y-m-d')]],
            'recur'    => ['order'=>30,'break'=>true,'label'=>$this->lang['frequency'], 'values'=>viewKeyDropdown($this->freqs),'attr'=>['type'=>'select','value'=>'m']]];
        $data = ['type'=>'divHTML',
            'divs'    => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key' =>'tbReminder'],
                'formBOF'=> ['order'=>15,'type'=>'form',   'key' =>'frmReminder'],
                'body'   => ['order'=>50,'type'=>'fields', 'keys'=>$flds],
                'formEOF'=> ['order'=>90,'type'=>'html',   'html'=>"</form>"]],
            'toolbars'=> ['tbReminder'=>['icons'=>['save'=>['order'=>10,'icon'=>'save','label'=>lang('save'),'events'=>['onClick'=>"jq('#frmReminder').submit();"]]]]],
            'forms'   => ['frmReminder'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/profile/reminderSave"]]],
            'fields'  => $fields,
            'jsReady' => ['init'=>"ajaxForm('frmReminder');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Adds a new reminder to the list, not possible to edit, need to delete and re-save a new reminder
     * @param array $layout - structure coming in typically []
     * @return array - $layout modified
     */
    public function reminderSave(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'profile', 2)) { return; }
        if (!$title= clean('title',    'text', 'post')){ return msgAdd("Title cannot be blank"); }
        $dateStart = clean('dateStart',['format'=>'date','default'=>date('Y-m-d')], 'post');
        $recur     = clean('recur',    'char', 'post');
        $dateNext  = $dateStart;
        bizAutoLoad(BIZUNO_LIB."controller/module/bizuno/dashboards/reminder/reminder.php", 'reminder');
        $dashB     = new reminder();
        $result    = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='reminder'");
        $settings  = clean($result['settings'], 'json');
        if (!$result) {
            $dashB->install('bizuno', 'home');
            $settings = [];
        }
        if ($dateStart <= date('Y-m-d')) { // see if any are due, add to current array if so
            $settings['current'][] = ['title'=>$title, 'date'=>$dateStart];
            $dateNext = $dashB->setDateNext($dateStart, $recur);
        }
        $settings['source'][] = ['title'=>$title, 'recur'=>$recur, 'dateStart'=>$dateStart, 'dateNext'=>$dateNext];
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='reminder'");
        msgAdd(lang('msg_record_saved'), 'success');
        msgLog("{$this->lang['reminders']} - ".lang('save')." - $title");
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jq('#accReminder').accordion('select', 0); jq('#dgReminder').datagrid('reload'); jq('#divReminderDtl').html('&nbsp;');"]]);
    }

    /**
     * Deletes a reminder
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function reminderDelete(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'profile', 4)) { return; }
        if (!$rID = clean('rID', 'integer', 'get')) { return msgAdd('The proper id was not passed!'); }
        $result   = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='reminder'");
        $settings = clean($result['settings'], 'json');
        $title    = $settings['source'][($rID-1)]['title'];
        unset($settings['source'][($rID-1)]);
        $settings['source'] = array_values($settings['source']);
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='reminder'");
        msgLog("{$this->lang['reminders']} - ".lang('delete')." - $title");
        $jsData = "jq('#accReminder').accordion('select', 0); jq('#dgReminder').datagrid('reload'); jq('#divReminderDtl').html('&nbsp;');";
        $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval', 'actionData'=>$jsData]]);
    }

    /**
     * Datagrid structure for reminders
     * @param string $name - DOM element id
     * @param integer $security - users security level
     * @return array - datagrid structure
     */
    public function dgReminder($name, $security=0)
    {
        $output = ['id'=>$name, 'rows'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows'), 'page'=>'1',
            'attr'=> ['toolbar'=>"#{$name}Toolbar", 'idField'=>'id', 'url'=>BIZUNO_AJAX."&p=bizuno/profile/reminderManagerRows&uID=".getUserCache('profile', 'admin_id', false, 0).""],
            'source'   => ['actions'=>['reminderNew'=>['order'=>10,'icon'=>'new','events'=>['onClick'=>"accordionEdit('accReminder','dgReminder','divReminderDtl','".jsLang('details')."','bizuno/profile/reminderEdit', 0);"]]]],
            'columns'  => ['id'=>['order'=>0,'attr'=>['hidden'=>true]],
                'action' => ['order'=> 1, 'label'=>lang('action'),
                    'events' => ['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
                    'actions'=> [
                        'delete'=> ['icon'=>'trash','size'=>'small', 'order'=>90, 'hidden'=>$security>3?false:true,
                            'events'=> ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/profile/reminderDelete', idTBD);"]]]],
                'title'    => ['order'=>10,'label'=>lang('title'),            'attr'=>['width'=>300,'resizable'=>true]],
                'recur'    => ['order'=>20,'label'=>$this->lang['frequency'], 'attr'=>['width'=>100,'resizable'=>true]],
                'dateStart'=> ['order'=>30,'label'=>$this->lang['start_date'],'attr'=>['width'=>100,'resizable'=>true]],
                'dateNext' => ['order'=>50,'label'=>$this->lang['next_date'], 'attr'=>['width'=>100,'resizable'=>true]]]];
        if ($GLOBALS['myDevice'] == 'mobile') {
            $output['columns']['recur']['attr']['hidden'] = true;
            $output['columns']['dateStart']['attr']['hidden'] = true;
        }
        return $output;
    }
}
